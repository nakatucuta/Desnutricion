<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncUserMunicipios extends Command
{
    protected $signature = 'users:sync-municipios
        {--dry-run : Simula cambios sin guardar}
        {--only-missing : Solo actualiza usuarios sin municipio}
        {--force-refresh : Recalcula incluso si el municipio ya existe}';

    protected $description = 'Sincroniza users.municipio desde SGA usando codigohabilitacion.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyMissing = (bool) $this->option('only-missing');
        $forceRefresh = (bool) $this->option('force-refresh');

        $codes = User::query()
            ->select('codigohabilitacion')
            ->whereNotNull('codigohabilitacion')
            ->whereRaw("LTRIM(RTRIM(ISNULL(codigohabilitacion, ''))) <> ''")
            ->distinct()
            ->orderBy('codigohabilitacion')
            ->pluck('codigohabilitacion')
            ->filter()
            ->map(fn ($code) => trim((string) $code))
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            $this->warn('No se encontraron codigos de habilitacion para sincronizar.');
            return self::SUCCESS;
        }

        $catalog = [];
        foreach ($codes as $code) {
            $catalog[$code] = $this->lookupMunicipioByCode($code);
        }

        $updated = 0;
        $skipped = 0;
        $missing = 0;

        User::query()
            ->select(['id', 'name', 'codigohabilitacion', 'municipio'])
            ->whereNotNull('codigohabilitacion')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($catalog, $dryRun, $onlyMissing, $forceRefresh, &$updated, &$skipped, &$missing) {
                foreach ($users as $user) {
                    $code = trim((string) $user->codigohabilitacion);
                    if ($code === '') {
                        $skipped++;
                        continue;
                    }

                    $municipio = $catalog[$code] ?? '';
                    if ($municipio === '') {
                        $missing++;
                        $this->line("SIN MUNICIPIO: {$user->id} | {$user->name} | {$code}");
                        continue;
                    }

                    $currentMunicipio = trim((string) $user->municipio);
                    if ($onlyMissing && $currentMunicipio !== '') {
                        $skipped++;
                        continue;
                    }

                    if (!$forceRefresh && $currentMunicipio !== '' && Str::upper($currentMunicipio) === Str::upper($municipio)) {
                        $skipped++;
                        continue;
                    }

                    $user->municipio = $municipio;
                    if (!$dryRun) {
                        $user->save();
                    }

                    $updated++;
                    $this->line("ACTUALIZADO: {$user->id} | {$user->name} | {$code} => {$municipio}");
                }
            });

        $this->newLine();
        $this->info("Proceso terminado. Actualizados: {$updated}, Omitidos: {$skipped}, Sin coincidencia: {$missing}");
        if ($dryRun) {
            $this->warn('Se ejecuto en modo simulacion, no se guardaron cambios.');
        }

        return self::SUCCESS;
    }

    private function lookupMunicipioByCode(string $code): string
    {
        $row = DB::connection('sqlsrv_1')
            ->table(DB::raw('sga..refips as A'))
            ->leftJoin(DB::raw('sga..municipios as B'), function ($join): void {
                $join->on('A.codigoDepartamento', '=', 'B.codigoDepartamento')
                    ->on('A.codigoMunicipio', '=', 'B.codigoMunicipio');
            })
            ->whereRaw("LTRIM(RTRIM(ISNULL(A.codigo, ''))) = ?", [trim($code)])
            ->selectRaw("LTRIM(RTRIM(ISNULL(B.descrip, ''))) as municipio")
            ->first();

        return trim((string) ($row->municipio ?? ''));
    }
}
