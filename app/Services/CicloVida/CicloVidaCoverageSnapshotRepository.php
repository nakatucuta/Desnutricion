<?php

namespace App\Services\CicloVida;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicloVidaCoverageSnapshotRepository
{
    public function hasOnlyDefaultFilters(array $query): bool
    {
        $keys = ['course_key', 'module_key', 'departamento', 'municipio', 'ips', 'genero', 'zona', 'estado_actual'];
        foreach ($keys as $key) {
            if (trim((string) ($query[$key] ?? '')) !== '') {
                return false;
            }
        }

        $include = filter_var($query['include_non_measurable'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $hide = filter_var($query['hide_empty'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return ($include ?? true) === true && ($hide ?? false) === false;
    }

    public function findForRequest(Request $request): ?array
    {
        if (!$this->hasOnlyDefaultFilters($request->query())) {
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
            ->orderByDesc('updated_at')
            ->first();

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
        ];

        return $payload;
    }

    public function store(string $presetKey, string $from, string $to, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        DB::transaction(function () use ($presetKey, $from, $to, $json): void {
            DB::table('ciclo_vida_coverage_snapshots')
                ->where('preset_key', $presetKey)
                ->whereDate('range_from', $from)
                ->whereDate('range_to', $to)
                ->delete();

            DB::statement(
                'INSERT INTO ciclo_vida_coverage_snapshots
                    (preset_key, range_from, range_to, payload_json, generated_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, GETDATE(), GETDATE(), GETDATE())',
                [$presetKey, $from, $to, $json]
            );
        });
    }

    public function presets(): array
    {
        $today = now()->startOfDay();

        return [
            'days_7' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            'days_30' => [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            'days_90' => [$today->copy()->subDays(89)->toDateString(), $today->toDateString()],
            'days_120' => [$today->copy()->subDays(119)->toDateString(), $today->toDateString()],
            'month_current' => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
            'month_previous' => [$today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'year_current' => [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()],
        ];
    }
}
