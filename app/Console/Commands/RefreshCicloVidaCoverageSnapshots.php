<?php

namespace App\Console\Commands;

use App\Services\CicloVida\CicloVidaCoverageAnalyzer;
use App\Services\CicloVida\CicloVidaCoverageSnapshotRepository;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class RefreshCicloVidaCoverageSnapshots extends Command
{
    protected $signature = 'ciclosvida:coverage-snapshots-refresh
                            {--preset=* : Preset(s) to refresh}
                            {--include-single-filters : Also generate snapshots with one common filter at a time}';

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

        $scopes = [['label' => 'default', 'filters' => []]];
        if ((bool) $this->option('include-single-filters')) {
            $scopes = $snapshots->commonSingleFilterScopes($analyzer);
        }

        foreach ($allPresets as $presetKey => [$from, $to]) {
            foreach ($scopes as $scope) {
                $this->info("Procesando {$presetKey} [{$scope['label']}]: {$from}..{$to}");

                $payloadRequest = $snapshots->requestPayload($from, $to, $scope['filters']);
                $request = Request::create('/ciclos-vida/cobertura-brechas/data', 'GET', $payloadRequest);
                $payload = $analyzer->analyze($request);
                $payload['meta'] = $payload['meta'] ?? [];
                $payload['meta']['request_filters'] = $payloadRequest;

                $snapshots->store($presetKey, $from, $to, $payload, $scope['filters']);
            }
        }

        $this->info('Snapshots de cobertura actualizados.');

        return self::SUCCESS;
    }
}
