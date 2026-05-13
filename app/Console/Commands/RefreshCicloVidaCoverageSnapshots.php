<?php

namespace App\Console\Commands;

use App\Services\CicloVida\CicloVidaCoverageAnalyzer;
use App\Services\CicloVida\CicloVidaCoverageSnapshotRepository;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class RefreshCicloVidaCoverageSnapshots extends Command
{
    protected $signature = 'ciclosvida:coverage-snapshots-refresh {--preset=* : Preset(s) to refresh}';

    protected $description = 'Precalcula snapshots de cobertura y brechas para rangos pesados del toolbar.';

    public function handle(CicloVidaCoverageAnalyzer $analyzer, CicloVidaCoverageSnapshotRepository $snapshots): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', env('CICLO_VIDA_CACHE_MEMORY_LIMIT', '1536M'));

        $allPresets = $snapshots->presets();
        $selected = array_values(array_filter((array) $this->option('preset')));

        if ($selected !== []) {
            $allPresets = array_intersect_key($allPresets, array_flip($selected));
        }

        if ($allPresets === []) {
            $this->warn('No hay presets validos para refrescar.');
            return self::SUCCESS;
        }

        foreach ($allPresets as $presetKey => [$from, $to]) {
            $this->info("Procesando {$presetKey}: {$from}..{$to}");

            $request = Request::create('/ciclos-vida/cobertura-brechas/data', 'GET', [
                'desde' => $from,
                'hasta' => $to,
                'include_non_measurable' => true,
                'hide_empty' => false,
            ]);

            $payload = $analyzer->analyze($request);
            $snapshots->store($presetKey, $from, $to, $payload);
        }

        $this->info('Snapshots de cobertura actualizados.');
        return self::SUCCESS;
    }
}
