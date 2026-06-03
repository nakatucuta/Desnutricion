<?php

namespace App\Console\Commands;

use App\Services\CicloVida\CicloVidaCacheRefresher;
use App\Support\CicloVidaCatalog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshCicloVidaCache extends Command
{
    protected $signature = 'ciclosvida:cache-refresh
                            {course=primera_infancia : Curso de vida configurado}
                            {--module=* : Uno o varios modulos a refrescar}
                            {--from= : Fecha inicial YYYY-MM-DD}
                            {--to= : Fecha final exclusiva YYYY-MM-DD}
                            {--days= : Ventana relativa si no se envian fechas}
                            {--resume : Omite modulos que ya terminaron con success para el mismo rango}
                            {--incremental : Refresca eventos solo desde el ultimo corte exitoso del modulo}';

    protected $description = 'Materializa la informacion de ciclos de vida en tablas cache locales.';

    public function handle(CicloVidaCacheRefresher $refresher): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', env('CICLO_VIDA_CACHE_MEMORY_LIMIT', '1536M'));

        $courseKey = (string) $this->argument('course');
        $moduleKeys = array_values(array_filter((array) $this->option('module')));
        if (empty($moduleKeys)) {
            $moduleKeys = CicloVidaCatalog::materializedModules($courseKey);
        }

        if ($this->option('incremental') && empty($this->option('from')) && empty($this->option('to'))) {
            return $this->handleIncremental($refresher, $courseKey, $moduleKeys);
        }

        [$from, $to] = $this->resolveDateRange($courseKey);

        if ($this->option('resume')) {
            $done = $this->resumableSkipModules($courseKey, $from, $to);
            $moduleKeys = array_values(array_filter($moduleKeys, fn (string $key) => !in_array($key, $done, true)));
        }

        $this->info("Refrescando {$courseKey} desde {$from->toDateString()} hasta {$to->toDateString()}");

        if (empty($moduleKeys)) {
            $this->info('No hay modulos pendientes para este rango.');

            return self::SUCCESS;
        }

        try {
            $results = $refresher->refreshCourse($courseKey, $moduleKeys, $from, $to);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Curso', 'Modulo', 'Estado', 'Registros'],
            collect($results)->map(fn (array $row) => [
                $row['course'],
                $row['module'],
                $row['status'],
                $row['records'],
            ])->all()
        );

        return self::SUCCESS;
    }

    protected function handleIncremental(CicloVidaCacheRefresher $refresher, string $courseKey, array $moduleKeys): int
    {
        $this->info("Refrescando {$courseKey} en modo incremental");

        $summary = [];
        foreach ($moduleKeys as $moduleKey) {
            [$from, $to, $mode] = $this->resolveIncrementalDateRange($courseKey, $moduleKey);

            if ($to->lessThanOrEqualTo($from)) {
                $summary[] = [$courseKey, $moduleKey, 'skipped', 0, 'sin ventana nueva'];
                continue;
            }

            $this->line("Modulo {$moduleKey}: {$mode} desde {$from->toDateString()} hasta {$to->toDateString()}");

            try {
                $results = $refresher->refreshCourse($courseKey, [$moduleKey], $from, $to);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            foreach ($results as $row) {
                $summary[] = [
                    $row['course'],
                    $row['module'],
                    $row['status'],
                    $row['records'],
                    $mode,
                ];
            }
        }

        $this->table(
            ['Curso', 'Modulo', 'Estado', 'Registros', 'Modo'],
            $summary
        );

        return self::SUCCESS;
    }

    protected function resolveDateRange(string $courseKey): array
    {
        $fromInput = $this->option('from');
        $toInput = $this->option('to');

        if (!empty($fromInput) && !empty($toInput)) {
            return [
                Carbon::parse((string) $fromInput)->startOfDay(),
                Carbon::parse((string) $toInput)->startOfDay(),
            ];
        }

        $days = (int) ($this->option('days')
            ?: data_get(config("ciclosvida.courses.{$courseKey}"), 'refresh.days', 120));

        return [
            now()->subDays(max($days, 1))->startOfDay(),
            now()->addDay()->startOfDay(),
        ];
    }

    protected function resolveIncrementalDateRange(string $courseKey, string $moduleKey): array
    {
        $module = (array) data_get(config("ciclosvida.courses.{$courseKey}.modules"), $moduleKey, []);
        $recordType = (string) ($module['record_type'] ?? 'event');

        if ($recordType === 'alert') {
            [$from, $to] = $this->resolveDateRange($courseKey);

            return [$from, $to, 'alert-replace'];
        }

        $latest = DB::table('ciclo_vida_cache_runs')
            ->where('course_key', $courseKey)
            ->where('module_key', $moduleKey)
            ->where('status', 'success')
            ->orderByDesc('range_end')
            ->orderByDesc('id')
            ->first(['range_end']);

        if ($latest && !empty($latest->range_end)) {
            return [
                Carbon::parse((string) $latest->range_end)->startOfDay(),
                now()->addDay()->startOfDay(),
                'incremental',
            ];
        }

        [$from, $to] = $this->resolveDateRange($courseKey);

        return [$from, $to, 'initial-window'];
    }

    protected function resumableSkipModules(string $courseKey, Carbon $from, Carbon $to): array
    {
        $rows = DB::select(
            "
            WITH latest AS (
                SELECT
                    module_key,
                    status,
                    ROW_NUMBER() OVER (PARTITION BY module_key ORDER BY id DESC) AS rn
                FROM ciclo_vida_cache_runs
                WHERE course_key = ?
                  AND range_start = ?
                  AND range_end = ?
            )
            SELECT module_key
            FROM latest
            WHERE rn = 1
              AND status IN ('success', 'running')
            ",
            [$courseKey, $from, $to]
        );

        return collect($rows)
            ->pluck('module_key')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
