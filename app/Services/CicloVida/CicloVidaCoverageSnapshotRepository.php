<?php

namespace App\Services\CicloVida;

use App\Support\CicloVidaCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicloVidaCoverageSnapshotRepository
{
    public function snapshotableFilters(array $query): ?array
    {
        $keys = ['course_key', 'departamento', 'municipio', 'ips', 'genero', 'zona', 'estado_actual'];
        $filters = [];

        foreach ($keys as $key) {
            $value = trim((string) ($query[$key] ?? ''));
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }

        if (trim((string) ($query['module_key'] ?? '')) !== '') {
            return null;
        }

        $include = filter_var($query['include_non_measurable'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $hide = filter_var($query['hide_empty'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (($include ?? true) !== true || ($hide ?? false) !== false) {
            return null;
        }

        if (count($filters) > 1) {
            return null;
        }

        return $filters;
    }

    public function findForRequest(Request $request): ?array
    {
        $filters = $this->snapshotableFilters($request->query());
        if ($filters === null) {
            return null;
        }

        try {
            $from = Carbon::parse((string) $request->query('desde'))->toDateString();
            $to = Carbon::parse((string) $request->query('hasta'))->toDateString();
        } catch (\Throwable $e) {
            return null;
        }

        $row = DB::table('ciclo_vida_coverage_snapshots')
            ->whereDate('range_from', $from)
            ->whereDate('range_to', $to)
            ->where('filter_key', $this->filterKey($filters))
            ->orderByDesc('updated_at')
            ->first();

        $usedClosestSnapshot = false;
        if (!$row) {
            $row = $this->findClosestPeriodicSnapshot($from, $to, $filters);
            $usedClosestSnapshot = (bool) $row;
        }

        if (!$row) {
            return null;
        }

        $payload = json_decode((string) $row->payload_json, true);
        if (!is_array($payload)) {
            return null;
        }

        $payload['meta'] = $payload['meta'] ?? [];
        $payload['meta']['snapshot'] = [
            'preset_key' => $row->preset_key,
            'generated_at' => (string) ($row->generated_at ?? $row->updated_at),
            'range_from' => (string) $row->range_from,
            'range_to' => (string) $row->range_to,
            'requested_from' => $from,
            'requested_to' => $to,
            'closest_match' => $usedClosestSnapshot,
        ];

        return $payload;
    }

    public function store(string $presetKey, string $from, string $to, array $payload, array $filters = []): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $filterKey = $this->filterKey($filters);
        $filterJson = json_encode($filters, JSON_UNESCAPED_UNICODE);

        DB::transaction(function () use ($presetKey, $from, $to, $json, $filterKey, $filterJson): void {
            DB::table('ciclo_vida_coverage_snapshots')
                ->where('preset_key', $presetKey)
                ->whereDate('range_from', $from)
                ->whereDate('range_to', $to)
                ->where('filter_key', $filterKey)
                ->delete();

            DB::statement(
                'INSERT INTO ciclo_vida_coverage_snapshots
                    (preset_key, range_from, range_to, filter_key, filter_json, payload_json, generated_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), GETDATE())',
                [$presetKey, $from, $to, $filterKey, $filterJson, $json]
            );
        });
    }

    public function requestPayload(string $from, string $to, array $filters = []): array
    {
        return array_merge([
            'desde' => $from,
            'hasta' => $to,
            'include_non_measurable' => true,
            'hide_empty' => false,
        ], $filters);
    }

    public function commonSingleFilterScopes(CicloVidaCoverageAnalyzer $analyzer): array
    {
        $options = $analyzer->advancedFilterOptions();
        $scopes = [
            ['label' => 'default', 'filters' => []],
        ];

        foreach (array_keys(CicloVidaCatalog::courses()) as $courseKey) {
            $scopes[] = [
                'label' => 'course:'.$courseKey,
                'filters' => ['course_key' => $courseKey],
            ];
        }

        $map = [
            'departments' => 'departamento',
            'municipalities' => 'municipio',
            'ips' => 'ips',
            'genders' => 'genero',
            'zones' => 'zona',
            'states' => 'estado_actual',
        ];

        foreach ($map as $sourceKey => $filterKey) {
            foreach (($options[$sourceKey] ?? []) as $item) {
                $value = trim((string) ($item['value'] ?? ''));
                if ($value === '') {
                    continue;
                }

                $scopes[] = [
                    'label' => $filterKey.':'.$value,
                    'filters' => [$filterKey => $value],
                ];
            }
        }

        return $scopes;
    }

    public function presets(): array
    {
        $today = now()->startOfDay();

        return [
            'days_7' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            'days_30' => [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            'days_90' => [$today->copy()->subDays(89)->toDateString(), $today->toDateString()],
            'days_120' => [$today->copy()->subDays(119)->toDateString(), $today->toDateString()],
            'month_current' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'month_previous' => [$today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'year_current' => [$today->copy()->startOfYear()->toDateString(), $today->toDateString()],
        ];
    }

    protected function findClosestPeriodicSnapshot(string $from, string $to, array $filters): ?object
    {
        try {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }

        $filterKey = $this->filterKey($filters);

        $isYearToDate = $fromDate->equalTo($toDate->copy()->startOfYear());
        if ($isYearToDate) {
            return DB::table('ciclo_vida_coverage_snapshots')
                ->where('preset_key', 'year_current')
                ->where('filter_key', $filterKey)
                ->whereDate('range_from', $fromDate->toDateString())
                ->whereDate('range_to', '<=', $toDate->toDateString())
                ->orderByDesc('range_to')
                ->orderByDesc('updated_at')
                ->first();
        }

        $isMonthToDate = $fromDate->equalTo($toDate->copy()->startOfMonth());
        if ($isMonthToDate) {
            return DB::table('ciclo_vida_coverage_snapshots')
                ->where('preset_key', 'month_current')
                ->where('filter_key', $filterKey)
                ->whereDate('range_from', $fromDate->toDateString())
                ->whereDate('range_to', '<=', $toDate->toDateString())
                ->orderByDesc('range_to')
                ->orderByDesc('updated_at')
                ->first();
        }

        return null;
    }

    protected function filterKey(array $filters): string
    {
        if ($filters === []) {
            return 'default';
        }

        ksort($filters);

        return md5(json_encode($filters, JSON_UNESCAPED_UNICODE));
    }
}
