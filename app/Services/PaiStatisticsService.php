<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PaiStatisticsService
{
    private const EXCLUDED_BIOLOGIC_ID_RANGE = [28, 54];

    public function build(array $filters): array
    {
        $year = $this->validYear($filters['year'] ?? null);
        [$start, $end] = $this->dateRange($year, $filters);
        $scope = $this->scope($year, $start, $end, $filters);

        $totals = (clone $scope)
            ->selectRaw('COUNT_BIG(*) as total_doses')
            ->selectRaw('COUNT(DISTINCT v.afiliado_id) as unique_people')
            ->first();

        $municipalities = (clone $scope)
            ->selectRaw("COALESCE(NULLIF(LTRIM(RTRIM(u.municipio)), ''), 'SIN INFORMACION') as label")
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw("COALESCE(NULLIF(LTRIM(RTRIM(u.municipio)), ''), 'SIN INFORMACION')")
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $providers = (clone $scope)
            ->selectRaw("COALESCE(NULLIF(LTRIM(RTRIM(u.codigohabilitacion)), ''), 'SIN CODIGO') as code")
            ->selectRaw("MIN(COALESCE(NULLIF(LTRIM(RTRIM(u.name)), ''), 'IPS SIN NOMBRE')) as label")
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw("COALESCE(NULLIF(LTRIM(RTRIM(u.codigohabilitacion)), ''), 'SIN CODIGO')")
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $biologics = (clone $scope)
            ->whereNotBetween('v.vacunas_id', self::EXCLUDED_BIOLOGIC_ID_RANGE)
            ->whereRaw("LTRIM(RTRIM(ISNULL(v.docis, ''))) <> ''")
            ->selectRaw("COALESCE(NULLIF(LTRIM(RTRIM(rv.nombre)), ''), 'SIN BIOLOGICO') as label")
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw("COALESCE(NULLIF(LTRIM(RTRIM(rv.nombre)), ''), 'SIN BIOLOGICO')")
            ->orderByDesc('total')
            ->get();

        $monthlyRows = (clone $scope)
            ->selectRaw('DATEPART(month, v.fecha_vacuna) as month_number')
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw('DATEPART(month, v.fecha_vacuna)')
            ->orderBy('month_number')
            ->get()
            ->keyBy(fn ($row) => (int) $row->month_number);

        $sex = (clone $scope)
            ->selectRaw($this->sexExpression() . ' as label')
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw($this->sexExpression())
            ->orderByDesc('total')
            ->get();

        $assignment = $this->assignmentSummary($scope, $year);
        $assignmentByProvider = $this->assignmentByProvider($scope, $year);

        return [
            'ok' => true,
            'filters' => [
                'year' => $year,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'municipio' => trim((string) ($filters['municipio'] ?? '')),
                'vaccinator_code' => trim((string) ($filters['vaccinator_code'] ?? '')),
                'regimen' => trim((string) ($filters['regimen'] ?? '')),
                'biologico_id' => (int) ($filters['biologico_id'] ?? 0),
            ],
            'catalogs' => $this->catalogs($year),
            'totals' => [
                'doses' => (int) ($totals->total_doses ?? 0),
                'people' => (int) ($totals->unique_people ?? 0),
                'assigned_doses' => $assignment['assigned'],
                'assigned_percentage' => $assignment['percentage'],
            ],
            'charts' => [
                'municipalities' => $this->rows($municipalities),
                'providers' => $this->rows($providers, true),
                'biologics' => $this->rows($biologics),
                'monthly' => $this->monthlySeries($monthlyRows, $start, $end),
                'sex' => $this->rows($sex),
                'assignment' => [
                    ['label' => 'IPS asignadas', 'total' => $assignment['assigned']],
                    ['label' => 'IPS no asignadas', 'total' => $assignment['not_assigned']],
                    ['label' => 'Sin IPS primaria', 'total' => $assignment['missing_primary']],
                ],
                'assignment_by_provider' => $assignmentByProvider,
            ],
            'assignment' => $assignment,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function scope(int $year, Carbon $start, Carbon $end, array $filters): Builder
    {
        $query = DB::table('vacunas as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->leftJoin('afiliados as a', 'a.id', '=', 'v.afiliado_id')
            ->leftJoin('referencia_vacunas as rv', 'rv.id', '=', 'v.vacunas_id')
            ->whereNotNull('v.fecha_vacuna')
            ->whereBetween('v.fecha_vacuna', [$start->toDateString(), $end->toDateString()]);

        $municipality = $this->normalize($filters['municipio'] ?? '');
        if ($municipality !== '') {
            $query->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(u.municipio, '')))) = ?", [$municipality]);
        }

        $vaccinatorCode = $this->normalize($filters['vaccinator_code'] ?? '');
        if ($vaccinatorCode !== '') {
            $query->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(u.codigohabilitacion, '')))) = ?", [$vaccinatorCode]);
        }

        $regime = $this->normalize($filters['regimen'] ?? '');
        if ($regime !== '') {
            $query->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(v.regimen, '')))) = ?", [$regime]);
        }

        $biologicId = (int) ($filters['biologico_id'] ?? 0);
        if ($biologicId > 0) {
            $query->where('v.vacunas_id', $biologicId);
        }

        return $query;
    }

    private function assignmentSummary(Builder $scope, int $year): array
    {
        $total = (int) (clone $scope)->count();

        $assignedQuery = clone $scope;
        $this->applyAssignmentExistsFilter($assignedQuery, $year);
        $assigned = (int) $assignedQuery->count();

        $missingPrimary = (int) (clone $scope)
            ->whereRaw("LTRIM(RTRIM(ISNULL(v.ips_primaria_codigo, ''))) = ''")
            ->count();

        $notAssigned = max($total - $assigned - $missingPrimary, 0);

        return [
            'total' => $total,
            'assigned' => $assigned,
            'not_assigned' => $notAssigned,
            'missing_primary' => $missingPrimary,
            'percentage' => self::assignmentPercentage($assigned, $total),
            'denominator' => 'Todas las dosis aplicadas dentro de los filtros',
        ];
    }

    private function assignmentByProvider(Builder $scope, int $year): array
    {
        $providerCodeSql = "COALESCE(NULLIF(LTRIM(RTRIM(u.codigohabilitacion)), ''), 'SIN CODIGO')";

        $totals = (clone $scope)
            ->selectRaw("COALESCE(NULLIF(LTRIM(RTRIM(u.codigohabilitacion)), ''), 'SIN CODIGO') as code")
            ->selectRaw("MIN(COALESCE(NULLIF(LTRIM(RTRIM(u.name)), ''), 'IPS SIN NOMBRE')) as label")
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw($providerCodeSql)
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        if ($totals->isEmpty()) {
            return [];
        }

        $providerCodes = $totals->pluck('code')->map(fn ($code) => (string) $code)->all();
        $assignedQuery = clone $scope;
        $this->applyAssignmentExistsFilter($assignedQuery, $year);

        $assignedByCode = $assignedQuery
            ->whereIn(DB::raw($providerCodeSql), $providerCodes)
            ->selectRaw($providerCodeSql . ' as code')
            ->selectRaw('COUNT_BIG(*) as assigned')
            ->groupByRaw($providerCodeSql)
            ->pluck('assigned', 'code');

        return $totals
            ->map(function ($row) use ($assignedByCode) {
                $total = (int) $row->total;
                $assigned = (int) ($assignedByCode->get((string) $row->code) ?? 0);

                return [
                    'code' => (string) $row->code,
                    'label' => (string) $row->label,
                    'total' => $total,
                    'assigned' => $assigned,
                    'percentage' => self::assignmentPercentage($assigned, $total),
                ];
            })
            ->values()
            ->all();
    }

    private function applyAssignmentExistsFilter(Builder $query, int $year): void
    {
        $query->whereExists(function ($referenceQuery) use ($year) {
            $referenceQuery
                ->selectRaw('1')
                ->from('pai_ips_referenciadas as pir')
                ->where('pir.vigencia', $year)
                ->where('pir.activo', true)
                ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(pir.ips_vacunadora_codigo, '')))) = UPPER(LTRIM(RTRIM(ISNULL(u.codigohabilitacion, ''))))")
                ->where(function ($municipalityQuery) {
                    $municipalityQuery
                        ->whereRaw("LTRIM(RTRIM(ISNULL(pir.municipio, ''))) = ''")
                        ->orWhereRaw("UPPER(LTRIM(RTRIM(ISNULL(pir.municipio, '')))) = UPPER(LTRIM(RTRIM(ISNULL(u.municipio, ''))))");
                })
                ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(pir.ips_primaria_codigo, '')))) = UPPER(LTRIM(RTRIM(ISNULL(v.ips_primaria_codigo, ''))))");
        });
    }

    private function catalogs(int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();
        $base = DB::table('vacunas as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->whereBetween('v.fecha_vacuna', [$start->toDateString(), $end->toDateString()]);

        $municipalities = (clone $base)
            ->whereRaw("LTRIM(RTRIM(ISNULL(u.municipio, ''))) <> ''")
            ->selectRaw('UPPER(LTRIM(RTRIM(u.municipio))) as value')
            ->distinct()
            ->orderBy('value')
            ->pluck('value')
            ->all();

        $providers = (clone $base)
            ->whereRaw("LTRIM(RTRIM(ISNULL(u.codigohabilitacion, ''))) <> ''")
            ->selectRaw('UPPER(LTRIM(RTRIM(u.codigohabilitacion))) as code')
            ->selectRaw("MIN(COALESCE(NULLIF(LTRIM(RTRIM(u.name)), ''), 'IPS SIN NOMBRE')) as name")
            ->selectRaw("MIN(COALESCE(NULLIF(LTRIM(RTRIM(u.municipio)), ''), '')) as municipio")
            ->groupByRaw('UPPER(LTRIM(RTRIM(u.codigohabilitacion)))')
            ->orderBy('name')
            ->get()
            ->map(fn ($row) => [
                'code' => (string) $row->code,
                'name' => (string) $row->name,
                'municipio' => (string) $row->municipio,
            ])
            ->all();

        $regimes = (clone $base)
            ->whereRaw("LTRIM(RTRIM(ISNULL(v.regimen, ''))) <> ''")
            ->selectRaw('UPPER(LTRIM(RTRIM(v.regimen))) as value')
            ->distinct()
            ->orderBy('value')
            ->pluck('value')
            ->all();

        $biologics = DB::table('referencia_vacunas')
            ->select('id', 'nombre')
            ->whereNotBetween('id', self::EXCLUDED_BIOLOGIC_ID_RANGE)
            ->whereRaw("LTRIM(RTRIM(ISNULL(nombre, ''))) <> ''")
            ->orderBy('nombre')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->nombre])
            ->all();

        return compact('municipalities', 'providers', 'regimes', 'biologics');
    }

    private function monthlySeries($rows, Carbon $start, Carbon $end): array
    {
        $labels = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $series = [];
        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create($start->year, $month, 1);
            if ($date->endOfMonth()->lt($start) || $date->startOfMonth()->gt($end)) {
                continue;
            }
            $series[] = ['label' => $labels[$month], 'total' => (int) ($rows->get($month)->total ?? 0)];
        }

        return $series;
    }

    private function rows($rows, bool $withCode = false): array
    {
        return $rows->map(function ($row) use ($withCode) {
            $item = ['label' => (string) $row->label, 'total' => (int) $row->total];
            if ($withCode) {
                $item['code'] = (string) ($row->code ?? '');
            }
            return $item;
        })->values()->all();
    }

    private function sexExpression(): string
    {
        return "CASE
            WHEN UPPER(LTRIM(RTRIM(ISNULL(a.sexo, '')))) IN ('F', 'FEMENINO', 'MUJER') THEN 'Mujer'
            WHEN UPPER(LTRIM(RTRIM(ISNULL(a.sexo, '')))) IN ('M', 'MASCULINO', 'HOMBRE') THEN 'Hombre'
            ELSE 'Sin informacion'
        END";
    }

    private function dateRange(int $year, array $filters): array
    {
        $start = trim((string) ($filters['start_date'] ?? ''));
        $end = trim((string) ($filters['end_date'] ?? ''));

        try {
            $startDate = $start !== '' ? Carbon::parse($start)->startOfDay() : Carbon::create($year, 1, 1)->startOfDay();
            $endDate = $end !== '' ? Carbon::parse($end)->endOfDay() : Carbon::create($year, 12, 31)->endOfDay();
        } catch (\Throwable $e) {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        }

        if ((int) $startDate->year !== $year) {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
        }
        if ((int) $endDate->year !== $year) {
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
    }

    public static function assignmentPercentage(int $assigned, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        $safeAssigned = max(0, min($assigned, $total));
        return round(($safeAssigned / $total) * 100, 2);
    }

    private function validYear($value): int
    {
        $year = (int) ($value ?: now()->year);
        return $year >= 2000 && $year <= 2100 ? $year : (int) now()->year;
    }

    private function normalize($value): string
    {
        return mb_strtoupper(trim((string) $value), 'UTF-8');
    }
}
