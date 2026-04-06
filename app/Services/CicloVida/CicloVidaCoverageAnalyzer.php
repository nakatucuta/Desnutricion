<?php

namespace App\Services\CicloVida;

use App\Support\CicloVidaCatalog;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CicloVidaCoverageAnalyzer
{
    protected array $dueCountsMemo = [];

    public function pageData(): array
    {
        return [
            'filters' => [
                'courses' => collect(CicloVidaCatalog::courses())
                    ->map(fn (array $course, string $key) => [
                        'value' => $key,
                        'label' => $course['label'] ?? $key,
                    ])
                    ->values()
                    ->all(),
                'modules' => $this->moduleOptions(),
                'moduleCatalog' => $this->moduleCatalogByCourse(),
            ],
            'ruleLegend' => collect($this->coverageRules())
                ->map(fn (array $rule, string $key) => [
                    'key' => $key,
                    'label' => $rule['label'],
                ])
                ->values()
                ->all(),
        ];
    }

    public function advancedFilterOptions(): array
    {
        return Cache::remember('ciclosvida.coverage.filters.v1', now()->addMinutes(30), function (): array {
            $base = DB::query()->fromSub($this->patientProfileSubquery(), 'p');
            $locations = (clone $base)
                ->selectRaw("
                    COALESCE(NULLIF(p.departamento,''), 'Sin departamento') as department_label,
                    COALESCE(NULLIF(p.municipio,''), 'Sin municipio') as municipality_label
                ")
                ->distinct()
                ->orderBy('department_label')
                ->orderBy('municipality_label')
                ->get();

            $municipalityMap = $locations
                ->groupBy('department_label')
                ->map(function ($items) {
                    return $items
                        ->pluck('municipality_label')
                        ->unique()
                        ->sort()
                        ->values()
                        ->map(fn ($label) => ['value' => $label, 'label' => $label])
                        ->all();
                })
                ->all();

            return [
                'genders' => $this->distinctOptions($base, "COALESCE(NULLIF(p.genero,''), 'Sin genero')"),
                'departments' => $this->distinctOptions($base, "COALESCE(NULLIF(p.departamento,''), 'Sin departamento')"),
                'municipalities' => $this->distinctOptions($base, "COALESCE(NULLIF(p.municipio,''), 'Sin municipio')"),
                'municipality_map' => $municipalityMap,
                'ips' => $this->distinctOptions($base, "COALESCE(NULLIF(p.ips_primaria,''), 'Sin IPS')"),
                'zones' => $this->distinctOptions($base, "COALESCE(NULLIF(p.zona,''), 'Sin zona')"),
                'states' => $this->distinctOptions($base, "COALESCE(NULLIF(p.estado_actual,''), 'Sin estado')"),
            ];
        });
    }

    public function analyze(Request $request): array
    {
        $this->dueCountsMemo = [];
        $filters = $this->normalizeFilters($request);
        $cacheKey = 'ciclosvida.coverage.analysis.'.md5(json_encode($filters, JSON_UNESCAPED_UNICODE));

        return Cache::remember($cacheKey, now()->addMinutes(10), fn (): array => $this->performAnalysis($filters));
    }

    protected function performAnalysis(array $filters): array
    {
        $catalogRows = $this->catalogRows($filters['course_key'], $filters['module_key']);
        $courseCatalog = $this->courseCatalog($filters['course_key']);

        $realizedGlobalStats = $this->realizedGlobalSummary($filters);
        $realizedCourseStats = $this->realizedCourseStats($filters);
        $realizedModuleStats = $this->realizedModuleStats($filters);

        $measurableRows = array_values(array_filter($catalogRows, fn (array $row) => $row['measurable']));
        [$gapRows, $gapCourseStats, $gapSummary] = $this->missingStats($filters, $measurableRows, $catalogRows);

        $realizedCourses = [];
        foreach ($courseCatalog as $courseKey => $course) {
            $courseRows = array_values(array_filter($catalogRows, fn (array $row) => $row['course_key'] === $courseKey));
            $moduleDetails = [];

            foreach ($courseRows as $row) {
                $realized = $realizedModuleStats[$courseKey][$row['module_key']] ?? [
                    'total_attentions' => 0,
                    'unique_patients' => 0,
                    'unique_ips' => 0,
                    'unique_municipalities' => 0,
                ];

                $moduleDetails[] = [
                    'course_key' => $courseKey,
                    'module_key' => $row['module_key'],
                    'label' => $row['label'],
                    'short_label' => $row['short_label'],
                    'description' => $row['description'],
                    'total_attentions' => (int) $realized['total_attentions'],
                    'unique_patients' => (int) $realized['unique_patients'],
                    'unique_ips' => (int) $realized['unique_ips'],
                    'unique_municipalities' => (int) $realized['unique_municipalities'],
                    'avg_per_patient' => (int) $realized['unique_patients'] > 0
                        ? round(((int) $realized['total_attentions']) / ((int) $realized['unique_patients']), 2)
                        : null,
                ];
            }

            $modulesWithActivity = collect($moduleDetails)
                ->filter(fn (array $module) => (int) $module['total_attentions'] > 0)
                ->count();

            $realizedCourses[] = [
                'course_key' => $courseKey,
                'label' => $course['label'],
                'age_label' => $course['age_label'],
                'icon' => $course['icon'],
                'color' => $course['color'],
                'total_attentions' => (int) ($realizedCourseStats[$courseKey]['total_attentions'] ?? 0),
                'unique_patients' => (int) ($realizedCourseStats[$courseKey]['unique_patients'] ?? 0),
                'unique_ips' => (int) ($realizedCourseStats[$courseKey]['unique_ips'] ?? 0),
                'unique_municipalities' => (int) ($realizedCourseStats[$courseKey]['unique_municipalities'] ?? 0),
                'avg_per_patient' => (int) ($realizedCourseStats[$courseKey]['unique_patients'] ?? 0) > 0
                    ? round(
                        ((int) ($realizedCourseStats[$courseKey]['total_attentions'] ?? 0))
                        / ((int) ($realizedCourseStats[$courseKey]['unique_patients'] ?? 0)),
                        2
                    )
                    : null,
                'modules_with_activity' => $modulesWithActivity,
                'total_modules' => count($moduleDetails),
                'modules' => $moduleDetails,
            ];
        }

        $missingCourses = [];
        foreach ($courseCatalog as $courseKey => $course) {
            $courseRows = array_values(array_filter($catalogRows, fn (array $row) => $row['course_key'] === $courseKey));
            $moduleDetails = [];

            foreach ($courseRows as $row) {
                $realized = $realizedModuleStats[$courseKey][$row['module_key']] ?? [
                    'total_attentions' => 0,
                    'unique_patients' => 0,
                ];

                $gap = $gapRows[$courseKey][$row['module_key']] ?? null;

                $moduleDetails[] = [
                    'course_key' => $courseKey,
                    'module_key' => $row['module_key'],
                    'label' => $row['label'],
                    'short_label' => $row['short_label'],
                    'description' => $row['description'],
                    'measurable' => $row['measurable'],
                    'status' => $row['measurable'] ? 'Exacta' : 'No estandarizable',
                    'method' => $row['measurable']
                        ? ($gap['method'] ?? $row['rule_label'])
                        : 'Criterio clinico o periodicidad no parametrizada',
                    'recorded_attentions' => (int) $realized['total_attentions'],
                    'recorded_patients' => (int) $realized['unique_patients'],
                    'target_patients' => $gap['target_patients'] ?? null,
                    'patients_with_missing' => $gap['patients_with_missing'] ?? null,
                    'patients_covered' => isset($gap['target_patients'], $gap['patients_with_missing'])
                        ? max(((int) $gap['target_patients']) - ((int) $gap['patients_with_missing']), 0)
                        : null,
                    'expected_attentions' => $gap['expected_attentions'] ?? null,
                    'valid_attentions' => $gap['valid_attentions'] ?? null,
                    'missing_attentions' => $gap['missing_attentions'] ?? null,
                    'excess_attentions' => $gap['excess_attentions'] ?? null,
                    'coverage' => $gap['coverage'] ?? null,
                ];
            }

            $courseGap = $gapCourseStats[$courseKey] ?? [
                'target_patients' => 0,
                'patients_with_missing' => 0,
                'expected_attentions' => 0,
                'valid_attentions' => 0,
                'missing_attentions' => 0,
                'excess_attentions' => 0,
                'measurable_modules' => 0,
            ];

            $missingCourses[] = [
                'course_key' => $courseKey,
                'label' => $course['label'],
                'age_label' => $course['age_label'],
                'icon' => $course['icon'],
                'color' => $course['color'],
                'target_patients' => (int) $courseGap['target_patients'],
                'patients_with_missing' => (int) $courseGap['patients_with_missing'],
                'patients_covered' => max(((int) $courseGap['target_patients']) - ((int) $courseGap['patients_with_missing']), 0),
                'expected_attentions' => (int) $courseGap['expected_attentions'],
                'valid_attentions' => (int) $courseGap['valid_attentions'],
                'missing_attentions' => (int) $courseGap['missing_attentions'],
                'excess_attentions' => (int) $courseGap['excess_attentions'],
                'coverage' => (int) $courseGap['expected_attentions'] > 0
                    ? round(((int) $courseGap['valid_attentions'] / (int) $courseGap['expected_attentions']) * 100, 1)
                    : null,
                'measurable_modules' => (int) $courseGap['measurable_modules'],
                'modules_with_gap' => collect($moduleDetails)
                    ->filter(fn (array $module) => (int) ($module['missing_attentions'] ?? 0) > 0)
                    ->count(),
                'modules' => $moduleDetails,
            ];
        }

        return [
            'ok' => true,
            'meta' => [
                'from' => $filters['from'],
                'to' => $filters['to'],
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'filters' => $this->activeFilterPills($filters),
                'notes' => [
                    'Las atenciones realizadas salen del cache materializado por curso de vida.',
                    'Las atenciones faltantes se calculan de forma exacta solo en modulos con regla normativa parametrizada desde la fecha de nacimiento.',
                    'Los modulos por criterio clinico o sin periodicidad operativa quedan marcados como no estandarizables.',
                ],
            ],
            'summary' => [
                'total_realized' => (int) ($realizedGlobalStats['total_attentions'] ?? 0),
                'total_realized_patients' => (int) ($realizedGlobalStats['unique_patients'] ?? 0),
                'total_realized_ips' => (int) ($realizedGlobalStats['unique_ips'] ?? 0),
                'total_realized_municipalities' => (int) ($realizedGlobalStats['unique_municipalities'] ?? 0),
                'total_expected' => (int) ($gapSummary['expected_attentions'] ?? 0),
                'total_missing' => (int) ($gapSummary['missing_attentions'] ?? 0),
                'total_valid' => (int) ($gapSummary['valid_attentions'] ?? 0),
                'coverage' => (int) ($gapSummary['expected_attentions'] ?? 0) > 0
                    ? round(((int) $gapSummary['valid_attentions'] / (int) $gapSummary['expected_attentions']) * 100, 1)
                    : null,
                'target_patients' => (int) ($gapSummary['target_patients'] ?? 0),
                'patients_with_missing' => (int) ($gapSummary['patients_with_missing'] ?? 0),
                'measurable_modules' => (int) ($gapSummary['measurable_modules'] ?? 0),
                'non_measurable_modules' => (int) ($gapSummary['non_measurable_modules'] ?? 0),
            ],
            'realized' => [
                'courses' => $realizedCourses,
            ],
            'missing' => [
                'courses' => $missingCourses,
            ],
        ];
    }

    protected function realizedGlobalSummary(array $filters): array
    {
        $row = DB::query()
            ->fromSub($this->eventAnalyticsBase($filters), 'q')
            ->selectRaw('
                COUNT(1) as total_attentions,
                COUNT(DISTINCT q.patient_key) as unique_patients,
                COUNT(DISTINCT q.ips_primaria) as unique_ips,
                COUNT(DISTINCT q.municipio) as unique_municipalities
            ')
            ->first();

        return [
            'total_attentions' => (int) ($row->total_attentions ?? 0),
            'unique_patients' => (int) ($row->unique_patients ?? 0),
            'unique_ips' => (int) ($row->unique_ips ?? 0),
            'unique_municipalities' => (int) ($row->unique_municipalities ?? 0),
        ];
    }

    protected function realizedCourseStats(array $filters): array
    {
        $rows = DB::query()
            ->fromSub($this->eventAnalyticsBase($filters), 'q')
            ->selectRaw('
                q.course_key,
                COUNT(1) as total_attentions,
                COUNT(DISTINCT q.patient_key) as unique_patients,
                COUNT(DISTINCT q.ips_primaria) as unique_ips,
                COUNT(DISTINCT q.municipio) as unique_municipalities
            ')
            ->groupBy('q.course_key')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->course_key] = [
                'total_attentions' => (int) $row->total_attentions,
                'unique_patients' => (int) $row->unique_patients,
                'unique_ips' => (int) $row->unique_ips,
                'unique_municipalities' => (int) $row->unique_municipalities,
            ];
        }

        return $result;
    }

    protected function realizedModuleStats(array $filters): array
    {
        $rows = DB::query()
            ->fromSub($this->eventAnalyticsBase($filters), 'q')
            ->selectRaw('
                q.course_key,
                q.module_key,
                COUNT(1) as total_attentions,
                COUNT(DISTINCT q.patient_key) as unique_patients,
                COUNT(DISTINCT q.ips_primaria) as unique_ips,
                COUNT(DISTINCT q.municipio) as unique_municipalities
            ')
            ->groupBy('q.course_key', 'q.module_key')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->course_key][$row->module_key] = [
                'total_attentions' => (int) $row->total_attentions,
                'unique_patients' => (int) $row->unique_patients,
                'unique_ips' => (int) $row->unique_ips,
                'unique_municipalities' => (int) $row->unique_municipalities,
            ];
        }

        return $result;
    }

    protected function missingStats(array $filters, array $measurableRows, array $catalogRows): array
    {
        if ($measurableRows === []) {
            return [[], [], [
                'target_patients' => 0,
                'patients_with_missing' => 0,
                'expected_attentions' => 0,
                'valid_attentions' => 0,
                'missing_attentions' => 0,
                'measurable_modules' => 0,
                'non_measurable_modules' => count(array_filter($catalogRows, fn (array $row) => !$row['measurable'])),
            ]];
        }

        $actualCounts = $this->actualCountsPerPatient($filters, $measurableRows);
        $rowTotals = [];
        $courseTotals = [];
        $global = [
            'target_patients' => 0,
            'patients_with_missing' => 0,
            'expected_attentions' => 0,
            'valid_attentions' => 0,
            'missing_attentions' => 0,
            'excess_attentions' => 0,
            'measurable_modules' => count($measurableRows),
            'non_measurable_modules' => count(array_filter($catalogRows, fn (array $row) => !$row['measurable'])),
        ];

        $globalTargetPatients = [];
        $globalPatientsWithGap = [];
        $courseTargetPatients = [];
        $coursePatientsWithGap = [];

        foreach ($measurableRows as $row) {
            $dueCounts = $this->dueCountsPerPatient($filters, $row);

            foreach ($dueCounts as $patientKey => $due) {
                $actual = $actualCounts[$patientKey][$row['course_key']][$row['module_key']] ?? 0;
                $valid = min($due, $actual);
                $missing = max($due - $actual, 0);
                $excess = max($actual - $due, 0);

                $bucket = &$rowTotals[$row['course_key']][$row['module_key']];
                if (!is_array($bucket)) {
                    $bucket = [
                        'method' => $row['rule_label'],
                        'target_patients' => 0,
                        'patients_with_missing' => 0,
                        'expected_attentions' => 0,
                        'valid_attentions' => 0,
                        'missing_attentions' => 0,
                        'excess_attentions' => 0,
                    ];
                }

                $bucket['target_patients']++;
                if ($missing > 0) {
                    $bucket['patients_with_missing']++;
                }
                $bucket['expected_attentions'] += $due;
                $bucket['valid_attentions'] += $valid;
                $bucket['missing_attentions'] += $missing;
                $bucket['excess_attentions'] += $excess;

                $courseBucket = &$courseTotals[$row['course_key']];
                if (!is_array($courseBucket)) {
                    $courseBucket = [
                        'target_patients' => 0,
                        'patients_with_missing' => 0,
                        'expected_attentions' => 0,
                        'valid_attentions' => 0,
                        'missing_attentions' => 0,
                        'excess_attentions' => 0,
                        'measurable_modules' => 0,
                    ];
                }

                $courseBucket['expected_attentions'] += $due;
                $courseBucket['valid_attentions'] += $valid;
                $courseBucket['missing_attentions'] += $missing;
                $courseBucket['excess_attentions'] += $excess;

                $global['expected_attentions'] += $due;
                $global['valid_attentions'] += $valid;
                $global['missing_attentions'] += $missing;
                $global['excess_attentions'] += $excess;

                $globalTargetPatients[$patientKey] = true;
                $courseTargetPatients[$row['course_key']][$patientKey] = true;

                if ($missing > 0) {
                    $globalPatientsWithGap[$patientKey] = true;
                    $coursePatientsWithGap[$row['course_key']][$patientKey] = true;
                }
            }
        }

        $global['target_patients'] = count($globalTargetPatients);
        $global['patients_with_missing'] = count($globalPatientsWithGap);

        foreach ($measurableRows as $row) {
            $courseKey = $row['course_key'];
            $courseTotals[$courseKey]['target_patients'] = count($courseTargetPatients[$courseKey] ?? []);
            $courseTotals[$courseKey]['patients_with_missing'] = count($coursePatientsWithGap[$courseKey] ?? []);
        }

        foreach ($measurableRows as $row) {
            $courseTotals[$row['course_key']]['measurable_modules'] = ($courseTotals[$row['course_key']]['measurable_modules'] ?? 0) + 1;

            $bucket = &$rowTotals[$row['course_key']][$row['module_key']];
            if (!is_array($bucket)) {
                $bucket = [
                    'method' => $row['rule_label'],
                    'target_patients' => 0,
                    'patients_with_missing' => 0,
                    'expected_attentions' => 0,
                    'valid_attentions' => 0,
                    'missing_attentions' => 0,
                    'excess_attentions' => 0,
                ];
            }

            $bucket['coverage'] = $bucket['expected_attentions'] > 0
                ? round(($bucket['valid_attentions'] / $bucket['expected_attentions']) * 100, 1)
                : null;
        }

        return [$rowTotals, $courseTotals, $global];
    }

    protected function dueCountsPerPatient(array $filters, array $row): array
    {
        $memoKey = md5(json_encode([
            'filters' => [
                'from' => $filters['from'],
                'to' => $filters['to'],
                'departamento' => $filters['departamento'],
                'municipio' => $filters['municipio'],
                'ips' => $filters['ips'],
                'genero' => $filters['genero'],
                'zona' => $filters['zona'],
                'estado_actual' => $filters['estado_actual'],
            ],
            'rule' => $row['rule'] ?? [],
        ], JSON_UNESCAPED_UNICODE));

        if (array_key_exists($memoKey, $this->dueCountsMemo)) {
            return $this->dueCountsMemo[$memoKey];
        }

        $windows = $this->ruleDueBirthWindows($filters, $row['rule'] ?? []);
        if ($windows === []) {
            return $this->dueCountsMemo[$memoKey] = [];
        }

        $union = null;

        foreach ($windows as $window) {
            $segment = DB::query()
                ->fromSub($this->patientProfileSubquery($filters, [
                    'oldest_birth_date' => $window['start'],
                    'latest_birth_date' => $window['end'],
                ]), 'p')
                ->whereNotNull('p.fecha_nacimiento')
                ->whereDate('p.fecha_nacimiento', '>=', $window['start'])
                ->whereDate('p.fecha_nacimiento', '<=', $window['end']);

            $this->applyGenderPopulationFilter($segment, $row['rule'] ?? [], 'p.genero');

            $segment->selectRaw("CONCAT(COALESCE(p.tipo_identificacion,''), '|', COALESCE(p.identificacion,'')) as patient_key");

            if ($union === null) {
                $union = $segment;
            } else {
                $union->unionAll($segment);
            }
        }

        if ($union === null) {
            return $this->dueCountsMemo[$memoKey] = [];
        }

        $rows = DB::query()
            ->fromSub($union, 'd')
            ->selectRaw('d.patient_key, COUNT(1) as due_count')
            ->groupBy('d.patient_key')
            ->get();

        $result = [];
        foreach ($rows as $rowData) {
            $result[$rowData->patient_key] = (int) $rowData->due_count;
        }

        return $this->dueCountsMemo[$memoKey] = $result;
    }

    protected function actualCountsPerPatient(array $filters, array $measurableRows): array
    {
        $base = $this->eventAnalyticsBase($filters);
        $modulePairs = collect($measurableRows)
            ->map(fn (array $row) => [$row['course_key'], $row['module_key']])
            ->values()
            ->all();

        $rows = DB::query()
            ->fromSub($base, 'q')
            ->where(function (Builder $query) use ($modulePairs): void {
                foreach ($modulePairs as [$courseKey, $moduleKey]) {
                    $query->orWhere(function (Builder $nested) use ($courseKey, $moduleKey): void {
                        $nested->where('q.course_key', $courseKey)
                            ->where('q.module_key', $moduleKey);
                    });
                }
            })
            ->selectRaw('q.patient_key, q.course_key, q.module_key, COUNT(1) as total')
            ->groupBy('q.patient_key', 'q.course_key', 'q.module_key')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->patient_key][$row->course_key][$row->module_key] = (int) $row->total;
        }

        return $result;
    }

    protected function eventAnalyticsBase(array $filters): Builder
    {
        $query = DB::table('ciclo_vida_cache_records as r')
            ->leftJoinSub($this->patientProfileSubquery($filters), 'p', function ($join): void {
                $join->on('r.tipo_identificacion', '=', 'p.tipo_identificacion')
                    ->on('r.identificacion', '=', 'p.identificacion');
            })
            ->where('r.record_type', 'event')
            ->whereBetween('r.event_date', [$filters['from'], $filters['to']]);

        if ($filters['course_key'] !== '') {
            $query->where('r.course_key', $filters['course_key']);
        }

        if ($filters['module_key'] !== '') {
            $query->where('r.module_key', $filters['module_key']);
        }

        $this->applyPopulationFilters($query, $filters, 'p');

        return $query->select([
            'r.course_key',
            'r.module_key',
            DB::raw("CONCAT(COALESCE(r.tipo_identificacion,''), '|', COALESCE(r.identificacion,'')) as patient_key"),
            DB::raw("COALESCE(NULLIF(p.ips_primaria,''), 'Sin IPS') as ips_primaria"),
            DB::raw("COALESCE(NULLIF(p.municipio,''), 'Sin municipio') as municipio"),
        ]);
    }

    protected function populationBaseQuery(array $filters, array $measurableRows): Builder
    {
        $birthWindow = $this->birthWindowFromRows($measurableRows, $filters);
        $query = DB::query()
            ->fromSub($this->patientProfileSubquery($filters, $birthWindow), 'p')
            ->whereNotNull('p.fecha_nacimiento');

        return $query->select([
            'p.tipo_identificacion',
            'p.identificacion',
            'p.fecha_nacimiento',
            'p.genero',
            'p.estado_actual',
            'p.departamento',
            'p.municipio',
            'p.zona',
            'p.ips_primaria',
        ]);
    }

    protected function applyPopulationFilters(Builder $query, array $filters, string $alias): void
    {
        if ($filters['departamento'] !== '') {
            $query->whereRaw("COALESCE(NULLIF({$alias}.departamento,''), 'Sin departamento') = ?", [$filters['departamento']]);
        }

        if ($filters['municipio'] !== '') {
            $query->whereRaw("COALESCE(NULLIF({$alias}.municipio,''), 'Sin municipio') = ?", [$filters['municipio']]);
        }

        if ($filters['ips'] !== '') {
            $query->whereRaw("COALESCE(NULLIF({$alias}.ips_primaria,''), 'Sin IPS') = ?", [$filters['ips']]);
        }

        if ($filters['genero'] !== '') {
            $query->whereRaw("COALESCE(NULLIF({$alias}.genero,''), 'Sin genero') = ?", [$filters['genero']]);
        }

        if ($filters['zona'] !== '') {
            $query->whereRaw("COALESCE(NULLIF({$alias}.zona,''), 'Sin zona') = ?", [$filters['zona']]);
        }

        if ($filters['estado_actual'] !== '') {
            $query->whereRaw("COALESCE(NULLIF({$alias}.estado_actual,''), 'Sin estado') = ?", [$filters['estado_actual']]);
        }
    }

    protected function applySourceProfileFilters(Builder $query, array $filters): void
    {
        if (($filters['departamento'] ?? '') !== '') {
            if ($filters['departamento'] === 'Sin departamento') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('d.descrip')->orWhere('d.descrip', '');
                });
            } else {
                $query->where('d.descrip', $filters['departamento']);
            }
        }

        if (($filters['municipio'] ?? '') !== '') {
            if ($filters['municipio'] === 'Sin municipio') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('m.descrip')->orWhere('m.descrip', '');
                });
            } else {
                $query->where('m.descrip', $filters['municipio']);
            }
        }

        if (($filters['ips'] ?? '') !== '') {
            if ($filters['ips'] === 'Sin IPS') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('mig.descrip')->orWhere('mig.descrip', '');
                });
            } else {
                $query->where('mig.descrip', $filters['ips']);
            }
        }

        if (($filters['genero'] ?? '') !== '') {
            if ($filters['genero'] === 'Sin genero') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('afi.genero')->orWhere('afi.genero', '');
                });
            } else {
                $query->where('afi.genero', $filters['genero']);
            }
        }

        if (($filters['zona'] ?? '') !== '') {
            if ($filters['zona'] === 'Sin zona') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('afi.zona')->orWhere('afi.zona', '');
                });
            } else {
                $query->where('afi.zona', $filters['zona']);
            }
        }

        if (($filters['estado_actual'] ?? '') !== '') {
            $query->where(function (Builder $builder) use ($filters): void {
                $builder->where('ea.estado', $filters['estado_actual'])
                    ->orWhereRaw('CAST(afi.estadoActual AS varchar(20)) = ?', [$filters['estado_actual']]);
            });
        }
    }

    protected function patientProfileSubquery(array $filters = [], ?array $birthWindow = null): Builder
    {
        $query = DB::query()
            ->from(DB::raw('sga..maestroidentificaciones as x'))
            ->join(DB::raw('sga..maestroafiliados as afi'), 'x.numeroCarnet', '=', 'afi.numeroCarnet')
            ->leftJoin(DB::raw('sga..refEstadoActual as ea'), 'afi.estadoActual', '=', 'ea.codigo')
            ->leftJoin(DB::raw('sga..municipios as m'), function ($join): void {
                $join->on('afi.codigoDepartamento', '=', 'm.codigoDepartamento')
                    ->on('afi.codigoMunicipio', '=', 'm.codigoMunicipio');
            })
            ->leftJoin(DB::raw('sga..departamentos as d'), 'afi.codigoDepartamento', '=', 'd.codigo')
            ->leftJoin(DB::raw('sga..maestroIps as mi'), 'x.numeroCarnet', '=', 'mi.numeroCarnet')
            ->leftJoin(DB::raw('sga..maestroIpsGru as mig'), 'mi.idGrupoIps', '=', 'mig.id')
            ->select([
                'x.tipoIdentificacion as tipo_identificacion',
                'x.identificacion',
                DB::raw('MAX(afi.fechaNacimiento) as fecha_nacimiento'),
                DB::raw('MAX(afi.genero) as genero'),
                DB::raw("MAX(COALESCE(NULLIF(ea.estado,''), NULLIF(CAST(afi.estadoActual AS varchar(20)), ''), 'Sin estado')) as estado_actual"),
                DB::raw("MAX(COALESCE(NULLIF(d.descrip,''), 'Sin departamento')) as departamento"),
                DB::raw("MAX(COALESCE(NULLIF(m.descrip,''), 'Sin municipio')) as municipio"),
                DB::raw("MAX(COALESCE(NULLIF(afi.zona,''), 'Sin zona')) as zona"),
                DB::raw("MAX(COALESCE(NULLIF(mig.descrip,''), 'Sin IPS')) as ips_primaria"),
            ])
            ->groupBy('x.tipoIdentificacion', 'x.identificacion');

        $this->applySourceProfileFilters($query, $filters);

        if (is_array($birthWindow)) {
            if (!empty($birthWindow['oldest_birth_date'])) {
                $query->whereDate('afi.fechaNacimiento', '>=', $birthWindow['oldest_birth_date']);
            }

            if (!empty($birthWindow['latest_birth_date'])) {
                $query->whereDate('afi.fechaNacimiento', '<=', $birthWindow['latest_birth_date']);
            }
        }

        return $query;
    }

    protected function distinctOptions(Builder $base, string $expression): array
    {
        return (clone $base)
            ->select(DB::raw($expression.' as label'))
            ->distinct()
            ->orderBy('label')
            ->get()
            ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
            ->values()
            ->all();
    }

    protected function normalizeFilters(Request $request): array
    {
        try {
            $from = Carbon::parse((string) $request->query('desde', now()->subDays(29)->toDateString()))->startOfDay();
            $to = Carbon::parse((string) $request->query('hasta', now()->toDateString()))->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->subDays(29)->startOfDay();
            $to = now()->startOfDay();
        }

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'course_key' => trim((string) $request->query('course_key', '')),
            'module_key' => trim((string) $request->query('module_key', '')),
            'departamento' => trim((string) $request->query('departamento', '')),
            'municipio' => trim((string) $request->query('municipio', '')),
            'ips' => trim((string) $request->query('ips', '')),
            'genero' => trim((string) $request->query('genero', '')),
            'zona' => trim((string) $request->query('zona', '')),
            'estado_actual' => trim((string) $request->query('estado_actual', '')),
            'include_non_measurable' => filter_var($request->query('include_non_measurable', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'hide_empty' => filter_var($request->query('hide_empty', false), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ];
    }

    protected function catalogRows(?string $courseFilter, ?string $moduleFilter): array
    {
        $rules = $this->coverageRules();
        $rows = [];

        foreach (CicloVidaCatalog::courses() as $courseKey => $course) {
            if ($courseFilter !== '' && $courseFilter !== null && $courseKey !== $courseFilter) {
                continue;
            }

            $moduleOrder = 0;
            foreach (($course['modules'] ?? []) as $moduleKey => $module) {
                if (($module['record_type'] ?? 'event') !== 'event') {
                    continue;
                }

                if (empty($module['materialized'])) {
                    continue;
                }

                if ($moduleKey === 'datos_generales') {
                    continue;
                }

                if ($moduleFilter !== '' && $moduleFilter !== null && $moduleKey !== $moduleFilter) {
                    continue;
                }

                $rule = $rules[$courseKey.'.'.$moduleKey] ?? null;
                $rows[] = [
                    'course_key' => $courseKey,
                    'module_key' => $moduleKey,
                    'label' => (string) ($module['label'] ?? $moduleKey),
                    'short_label' => (string) ($module['short_label'] ?? $module['label'] ?? $moduleKey),
                    'description' => (string) ($module['description'] ?? ''),
                    'measurable' => is_array($rule),
                    'rule' => $rule,
                    'rule_label' => $rule['label'] ?? 'No estandarizable',
                    'sort_order' => $moduleOrder++,
                ];
            }
        }

        return $rows;
    }

    protected function courseCatalog(?string $courseFilter): array
    {
        $courses = [];
        foreach (CicloVidaCatalog::courses() as $courseKey => $course) {
            if ($courseFilter !== '' && $courseFilter !== null && $courseKey !== $courseFilter) {
                continue;
            }

            $courses[$courseKey] = [
                'label' => (string) ($course['label'] ?? $courseKey),
                'age_label' => (string) ($course['age_label'] ?? ''),
                'icon' => (string) ($course['icon'] ?? 'fas fa-layer-group'),
                'color' => (string) ($course['color'] ?? 'bg-secondary'),
            ];
        }

        return $courses;
    }

    protected function moduleOptions(): array
    {
        $rules = $this->coverageRules();

        return collect(CicloVidaCatalog::courses())
            ->flatMap(function (array $course, string $courseKey) use ($rules) {
                return collect($course['modules'] ?? [])
                    ->filter(fn (array $module) => ($module['record_type'] ?? 'event') === 'event' && !empty($module['materialized']))
                    ->reject(fn (array $module, string $moduleKey) => $moduleKey === 'datos_generales')
                    ->map(function (array $module, string $moduleKey) use ($courseKey, $rules) {
                        return [
                            'value' => $moduleKey,
                            'label' => $module['short_label'] ?? $module['label'] ?? $moduleKey,
                            'measurable' => isset($rules[$courseKey.'.'.$moduleKey]),
                        ];
                    });
            })
            ->groupBy('value')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'value' => $first['value'],
                    'label' => $first['label'],
                    'measurable' => $items->contains(fn ($item) => $item['measurable']),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    protected function moduleCatalogByCourse(): array
    {
        $rules = $this->coverageRules();
        $catalog = ['all' => []];

        foreach (CicloVidaCatalog::courses() as $courseKey => $course) {
            $catalog[$courseKey] = [];

            foreach (($course['modules'] ?? []) as $moduleKey => $module) {
                if (($module['record_type'] ?? 'event') !== 'event' || empty($module['materialized']) || $moduleKey === 'datos_generales') {
                    continue;
                }

                $item = [
                    'value' => $moduleKey,
                    'label' => $module['short_label'] ?? $module['label'] ?? $moduleKey,
                    'measurable' => isset($rules[$courseKey.'.'.$moduleKey]),
                ];

                $catalog[$courseKey][] = $item;
                $catalog['all'][$moduleKey] = $catalog['all'][$moduleKey] ?? $item;
            }
        }

        $catalog['all'] = collect($catalog['all'])
            ->sortBy('label')
            ->values()
            ->all();

        foreach ($catalog as $key => $items) {
            if ($key === 'all') {
                continue;
            }

            $catalog[$key] = collect($items)
                ->sortBy('label')
                ->values()
                ->all();
        }

        return $catalog;
    }

    protected function activeFilterPills(array $filters): array
    {
        $map = [
            'course_key' => 'Curso',
            'module_key' => 'Atencion',
            'departamento' => 'Departamento',
            'municipio' => 'Municipio',
            'ips' => 'IPS',
            'genero' => 'Genero',
            'zona' => 'Zona',
            'estado_actual' => 'Estado',
        ];

        return collect($map)
            ->filter(fn (string $label, string $key) => ($filters[$key] ?? '') !== '')
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'value' => $filters[$key],
            ])
            ->values()
            ->all();
    }

    protected function coverageRules(): array
    {
        return [
            'infancia.medica' => [
                'type' => 'exact_years',
                'ages' => [6, 8, 10],
                'label' => 'Edades trazadoras: 6, 8 y 10 anos',
            ],
            'infancia.enfermeria' => [
                'type' => 'exact_years',
                'ages' => [7, 9, 11],
                'label' => 'Edades trazadoras: 7, 9 y 11 anos',
            ],
            'infancia.salud_bucal' => [
                'type' => 'periodic_years',
                'start_age' => 6,
                'end_age' => 11,
                'every' => 1,
                'label' => 'Una vez por ano entre 6 y 11 anos',
            ],
            'infancia.hemoglobina' => [
                'type' => 'exact_years',
                'ages' => [10, 11],
                'label' => 'Una vez en cada edad objetivo: 10 y 11 anos',
            ],
            'infancia.hematocrito' => [
                'type' => 'exact_years',
                'ages' => [10, 11],
                'label' => 'Una vez en cada edad objetivo: 10 y 11 anos',
            ],
            'adolescencia.medica' => [
                'type' => 'exact_years',
                'ages' => [12, 14, 16],
                'label' => 'Edades trazadoras: 12, 14 y 16 anos',
            ],
            'adolescencia.enfermeria' => [
                'type' => 'exact_years',
                'ages' => [13, 15, 17],
                'label' => 'Edades trazadoras: 13, 15 y 17 anos',
            ],
            'adolescencia.salud_bucal' => [
                'type' => 'periodic_years',
                'start_age' => 12,
                'end_age' => 17,
                'every' => 1,
                'label' => 'Una vez por ano entre 12 y 17 anos',
            ],
            'adolescencia.hemoglobina' => [
                'type' => 'exact_years',
                'ages' => [12, 13],
                'label' => 'Una vez en cada edad objetivo: 12 y 13 anos',
            ],
            'adolescencia.hematocrito' => [
                'type' => 'exact_years',
                'ages' => [12, 13],
                'label' => 'Una vez en cada edad objetivo: 12 y 13 anos',
            ],
            'juventud.salud_bucal' => [
                'type' => 'periodic_years',
                'start_age' => 18,
                'end_age' => 28,
                'every' => 2,
                'label' => 'Una vez cada 2 anos entre 18 y 28 anos',
            ],
            'juventud.riesgo_cardiometabolico' => [
                'type' => 'periodic_years',
                'start_age' => 18,
                'end_age' => 28,
                'every' => 5,
                'label' => 'Una vez cada 5 anos entre 18 y 28 anos',
            ],
            'adultez.salud_bucal' => [
                'type' => 'periodic_years',
                'start_age' => 29,
                'end_age' => 59,
                'every' => 2,
                'label' => 'Una vez cada 2 anos entre 29 y 59 anos',
            ],
            'adultez.placa' => [
                'type' => 'periodic_years',
                'start_age' => 29,
                'end_age' => 59,
                'every' => 2,
                'label' => 'Una vez cada 2 anos entre 29 y 59 anos',
            ],
            'adultez.riesgo_cardiometabolico' => [
                'type' => 'periodic_years',
                'start_age' => 29,
                'end_age' => 59,
                'every' => 5,
                'label' => 'Una vez cada 5 anos entre 29 y 59 anos',
            ],
            'adultez.mamografia' => [
                'type' => 'periodic_years',
                'start_age' => 50,
                'end_age' => 59,
                'every' => 2,
                'gender' => 'female',
                'label' => 'Mujeres de 50 a 59 anos, cada 2 anos',
            ],
            'adultez.psa' => [
                'type' => 'periodic_years',
                'start_age' => 50,
                'end_age' => 59,
                'every' => 5,
                'gender' => 'male',
                'label' => 'Hombres de 50 a 59 anos, cada 5 anos',
            ],
            'adultez.tacto_rectal' => [
                'type' => 'periodic_years',
                'start_age' => 50,
                'end_age' => 59,
                'every' => 5,
                'gender' => 'male',
                'label' => 'Hombres de 50 a 59 anos, cada 5 anos',
            ],
            'adultez.tamizaje_colon' => [
                'type' => 'periodic_years',
                'start_age' => 50,
                'end_age' => 59,
                'every' => 2,
                'label' => 'Una vez cada 2 anos desde los 50 anos',
            ],
            'vejez.medica' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => null,
                'every' => 3,
                'label' => 'Una vez cada 3 anos desde los 60 anos',
            ],
            'vejez.salud_bucal' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => null,
                'every' => 2,
                'label' => 'Una vez cada 2 anos desde los 60 anos',
            ],
            'vejez.placa' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => null,
                'every' => 2,
                'label' => 'Una vez cada 2 anos desde los 60 anos',
            ],
            'vejez.riesgo_cardiometabolico' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => null,
                'every' => 5,
                'label' => 'Una vez cada 5 anos desde los 60 anos',
            ],
            'vejez.mamografia' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => 69,
                'every' => 2,
                'gender' => 'female',
                'label' => 'Mujeres de 60 a 69 anos, cada 2 anos',
            ],
            'vejez.psa' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => 75,
                'every' => 5,
                'gender' => 'male',
                'label' => 'Hombres de 60 a 75 anos, cada 5 anos',
            ],
            'vejez.tacto_rectal' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => 75,
                'every' => 5,
                'gender' => 'male',
                'label' => 'Hombres de 60 a 75 anos, cada 5 anos',
            ],
            'vejez.tamizaje_colon' => [
                'type' => 'periodic_years',
                'start_age' => 60,
                'end_age' => 75,
                'every' => 2,
                'label' => 'Una vez cada 2 anos desde los 60 hasta los 75 anos',
            ],
        ];
    }

    protected function birthWindowFromRows(array $measurableRows, array $filters): array
    {
        $minAge = null;
        $maxAge = null;

        foreach ($measurableRows as $row) {
            $rule = $row['rule'];
            if (!is_array($rule)) {
                continue;
            }

            if ($rule['type'] === 'exact_years' && !empty($rule['ages'])) {
                $ruleMin = min(array_map('intval', $rule['ages']));
                $ruleMax = max(array_map('intval', $rule['ages']));
            } elseif ($rule['type'] === 'periodic_years') {
                $ruleMin = (int) ($rule['start_age'] ?? 0);
                $ruleMax = array_key_exists('end_age', $rule) ? $rule['end_age'] : null;
            } else {
                continue;
            }

            $minAge = $minAge === null ? $ruleMin : min($minAge, $ruleMin);
            if ($ruleMax !== null) {
                $maxAge = $maxAge === null ? (int) $ruleMax : max($maxAge, (int) $ruleMax);
            }
        }

        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);

        return [
            'latest_birth_date' => $minAge === null ? null : $to->copy()->subYears($minAge)->toDateString(),
            'oldest_birth_date' => $maxAge === null ? null : $from->copy()->subYears($maxAge + 1)->toDateString(),
        ];
    }

    protected function ruleDueBirthWindows(array $filters, array $rule): array
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->startOfDay();
        $windows = [];

        switch ($rule['type'] ?? null) {
            case 'exact_years':
                foreach ((array) ($rule['ages'] ?? []) as $age) {
                    $age = (int) $age;
                    $windows[] = [
                        'start' => $from->copy()->subYears($age)->toDateString(),
                        'end' => $to->copy()->subYears($age)->toDateString(),
                    ];
                }
                break;

            case 'exact_months':
                foreach ((array) ($rule['months'] ?? []) as $month) {
                    $month = (int) $month;
                    $windows[] = [
                        'start' => $from->copy()->subMonths($month)->toDateString(),
                        'end' => $to->copy()->subMonths($month)->toDateString(),
                    ];
                }
                break;

            case 'periodic_years':
                $startAge = (int) ($rule['start_age'] ?? 0);
                $endAge = array_key_exists('end_age', $rule) && $rule['end_age'] !== null ? (int) $rule['end_age'] : 120;
                $every = max(1, (int) ($rule['every'] ?? 1));

                for ($age = $startAge; $age <= $endAge; $age += $every) {
                    $windows[] = [
                        'start' => $from->copy()->subYears($age)->toDateString(),
                        'end' => $to->copy()->subYears($age)->toDateString(),
                    ];
                }
                break;

            case 'periodic_months':
                $startMonth = (int) ($rule['start_month'] ?? 0);
                $endMonth = array_key_exists('end_month', $rule) && $rule['end_month'] !== null ? (int) $rule['end_month'] : 1440;
                $every = max(1, (int) ($rule['every'] ?? 1));

                for ($month = $startMonth; $month <= $endMonth; $month += $every) {
                    $windows[] = [
                        'start' => $from->copy()->subMonths($month)->toDateString(),
                        'end' => $to->copy()->subMonths($month)->toDateString(),
                    ];
                }
                break;
        }

        return $windows;
    }

    protected function expectedOccurrences(Carbon $birthDate, Carbon $from, Carbon $to, array $rule): int
    {
        $count = 0;

        switch ($rule['type']) {
            case 'exact_years':
                foreach ((array) ($rule['ages'] ?? []) as $age) {
                    $dueDate = $birthDate->copy()->addYears((int) $age)->startOfDay();
                    if ($dueDate->betweenIncluded($from, $to)) {
                        $count++;
                    }
                }
                break;

            case 'exact_months':
                foreach ((array) ($rule['months'] ?? []) as $month) {
                    $dueDate = $birthDate->copy()->addMonths((int) $month)->startOfDay();
                    if ($dueDate->betweenIncluded($from, $to)) {
                        $count++;
                    }
                }
                break;

            case 'periodic_years':
                $startAge = (int) ($rule['start_age'] ?? 0);
                $endAge = array_key_exists('end_age', $rule) ? $rule['end_age'] : null;
                $every = max(1, (int) ($rule['every'] ?? 1));

                for ($age = $startAge; ; $age += $every) {
                    if ($endAge !== null && $age > (int) $endAge) {
                        break;
                    }

                    $dueDate = $birthDate->copy()->addYears($age)->startOfDay();
                    if ($dueDate->gt($to)) {
                        break;
                    }

                    if ($dueDate->betweenIncluded($from, $to)) {
                        $count++;
                    }
                }
                break;

            case 'periodic_months':
                $startMonth = (int) ($rule['start_month'] ?? 0);
                $endMonth = array_key_exists('end_month', $rule) ? $rule['end_month'] : null;
                $every = max(1, (int) ($rule['every'] ?? 1));

                for ($month = $startMonth; ; $month += $every) {
                    if ($endMonth !== null && $month > (int) $endMonth) {
                        break;
                    }

                    $dueDate = $birthDate->copy()->addMonths($month)->startOfDay();
                    if ($dueDate->gt($to)) {
                        break;
                    }

                    if ($dueDate->betweenIncluded($from, $to)) {
                        $count++;
                    }
                }
                break;
        }

        return $count;
    }

    protected function matchesGenderRule(string $gender, array $rule): bool
    {
        if (empty($rule['gender'])) {
            return true;
        }

        $normalized = strtoupper(Str::ascii(trim($gender)));

        if ($rule['gender'] === 'female') {
            return in_array($normalized, ['F', 'FEMENINO', 'MUJER', 'MUJERES'], true);
        }

        if ($rule['gender'] === 'male') {
            return in_array($normalized, ['M', 'MASCULINO', 'HOMBRE', 'HOMBRES'], true);
        }

        return true;
    }

    protected function applyGenderPopulationFilter(Builder $query, array $rule, string $column): void
    {
        if (empty($rule['gender'])) {
            return;
        }

        $normalized = "UPPER(LTRIM(RTRIM(COALESCE({$column}, ''))))";

        if ($rule['gender'] === 'female') {
            $query->whereRaw("{$normalized} IN ('F', 'FEMENINO', 'MUJER', 'MUJERES')");
            return;
        }

        if ($rule['gender'] === 'male') {
            $query->whereRaw("{$normalized} IN ('M', 'MASCULINO', 'HOMBRE', 'HOMBRES')");
        }
    }
}
