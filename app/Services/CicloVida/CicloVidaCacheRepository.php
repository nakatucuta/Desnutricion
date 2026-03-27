<?php

namespace App\Services\CicloVida;

use Carbon\Carbon;
use App\Support\CicloVidaCatalog;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CicloVidaCacheRepository
{
    protected array $statsGroups = [
        'atencion_integral' => [
            'label' => 'Atencion integral',
            'modules' => ['medica', 'enfermeria', 'salud_bucal', 'odontologia_general'],
        ],
        'proteccion_especifica' => [
            'label' => 'Proteccion especifica',
            'modules' => ['fluor', 'placa', 'sellantes', 'detartraje', 'hemoglobina', 'hematocrito', 'lactancia', 'vitamina_a', 'hierro'],
        ],
        'tamizajes_cancer' => [
            'label' => 'Tamizajes de cancer',
            'modules' => ['citologia_cuello_uterino', 'adn_vph', 'inspeccion_cuello_uterino', 'mamografia', 'psa', 'tacto_rectal', 'tamizaje_colon'],
        ],
        'riesgo_cardiovascular' => [
            'label' => 'Riesgo cardiovascular',
            'modules' => ['riesgo_cardiometabolico', 'r202_juventud', 'r202_adultez', 'r202_vejez'],
        ],
        'ssr_r202' => [
            'label' => 'SSR y R202',
            'modules' => [
                'asesoria_anticoncepcion',
                'metodo_planificacion',
                'planificacion_r202',
                'r202_ssr',
                'prueba_treponemica',
                'prueba_rapida_vih',
                'prueba_rapida_hepatitis_b',
                'prueba_rapida_hepatitis_c',
            ],
        ],
    ];

    public function eventTable(
        string $courseKey,
        array|string $moduleKeys,
        Request $request,
        array $options = []
    ) {
        [$from, $to] = $this->resolveRange($request, $options['default_days'] ?? 120);
        $moduleKeys = (array) $moduleKeys;

        $builder = DB::table('ciclo_vida_cache_records as r')
            ->where('r.course_key', $courseKey)
            ->whereIn('r.module_key', $moduleKeys)
            ->where('r.record_type', $options['record_type'] ?? 'event')
            ->whereBetween('r.event_date', [$from, $to->copy()->subDay()]);

        if (!empty($options['where'])) {
            foreach ($options['where'] as $column => $value) {
                if (is_array($value)) {
                    $builder->whereIn($column, $value);
                } else {
                    $builder->where($column, $value);
                }
            }
        }

        if (!empty($options['where_raw'])) {
            foreach ($options['where_raw'] as $item) {
                $builder->whereRaw($item['sql'], $item['bindings'] ?? []);
            }
        }

        $dataSource = (clone $builder)->select([
            DB::raw('r.event_date as fechaConsulta'),
            DB::raw('r.event_date as fechaAtencion'),
            'r.tipo_identificacion as tipoIdentificacion',
            'r.identificacion',
            'r.primer_nombre as primerNombre',
            'r.segundo_nombre as segundoNombre',
            'r.primer_apellido as primerApellido',
            'r.segundo_apellido as segundoApellido',
            'r.codigo_servicio as codigoConsulta',
            'r.codigo_servicio as codigoCups',
            'r.descripcion_servicio as descrip',
            'r.diagnostico_principal as diagnosticoPrincipal',
            'r.finalidad',
            DB::raw('r.finalidad as finalidadConsulta'),
            'r.ips_primaria as ips_Prim',
            'r.edad',
            'r.edad_meses as edadMeses',
            'r.rango_edad as rangoEdad',
            'r.fecha_nacimiento as fechaNacimiento',
            'r.codigo_ips as codigoIps',
            'r.module_key as moduleKey',
        ]);

        $kpis = $this->kpisFromBuilder(clone $builder);

        $payload = DataTables::of($dataSource)
            ->filter(function (Builder $query) use ($request): void {
                $search = trim((string) $request->input('search.value', ''));
                if ($search === '') {
                    return;
                }

                $like = '%'.$search.'%';
                $query->where(function (Builder $q) use ($like): void {
                    $q->orWhere('r.tipo_identificacion', 'like', $like)
                        ->orWhere('r.identificacion', 'like', $like)
                        ->orWhere('r.primer_nombre', 'like', $like)
                        ->orWhere('r.segundo_nombre', 'like', $like)
                        ->orWhere('r.primer_apellido', 'like', $like)
                        ->orWhere('r.segundo_apellido', 'like', $like)
                        ->orWhere('r.codigo_servicio', 'like', $like)
                        ->orWhere('r.descripcion_servicio', 'like', $like)
                        ->orWhere('r.diagnostico_principal', 'like', $like)
                        ->orWhere('r.finalidad', 'like', $like)
                        ->orWhere('r.ips_primaria', 'like', $like)
                        ->orWhere('r.rango_edad', 'like', $like)
                        ->orWhereRaw('CONVERT(varchar(10), r.event_date, 23) LIKE ?', [$like]);
                });
            }, true)
            ->order(function (Builder $query) use ($request): void {
                $map = [
                    'fechaConsulta' => 'r.event_date',
                    'fechaAtencion' => 'r.event_date',
                    'tipoIdentificacion' => 'r.tipo_identificacion',
                    'identificacion' => 'r.identificacion',
                    'primerNombre' => 'r.primer_nombre',
                    'segundoNombre' => 'r.segundo_nombre',
                    'primerApellido' => 'r.primer_apellido',
                    'segundoApellido' => 'r.segundo_apellido',
                    'codigoConsulta' => 'r.codigo_servicio',
                    'codigoCups' => 'r.codigo_servicio',
                    'descrip' => 'r.descripcion_servicio',
                    'diagnosticoPrincipal' => 'r.diagnostico_principal',
                    'finalidadConsulta' => 'r.finalidad',
                    'finalidad' => 'r.finalidad',
                    'ips_Prim' => 'r.ips_primaria',
                    'edad' => 'r.edad',
                    'edadMeses' => 'r.edad_meses',
                    'rangoEdad' => 'r.rango_edad',
                    'fechaNacimiento' => 'r.fecha_nacimiento',
                    'codigoIps' => 'r.codigo_ips',
                    'moduleKey' => 'r.module_key',
                ];

                $applied = false;
                foreach ((array) $request->input('order', []) as $order) {
                    $index = (int) data_get($order, 'column', -1);
                    $dataKey = (string) data_get($request->input('columns'), "{$index}.data", '');
                    $column = $map[$dataKey] ?? null;
                    if ($column === null) {
                        continue;
                    }

                    $direction = strtolower((string) data_get($order, 'dir', 'asc')) === 'desc' ? 'desc' : 'asc';
                    $query->orderBy($column, $direction);
                    $applied = true;
                }

                if (!$applied) {
                    $query->orderBy('r.event_date', 'desc');
                }
            })
            ->toArray();

        $payload['kpis'] = $kpis;

        return response()->json($payload);
    }

    public function alertTable(string $courseKey, Request $request)
    {
        [$from, $to, $exact] = $this->resolveMaterializedCut(
            $courseKey,
            'alertas',
            $request,
            (int) data_get(config("ciclosvida.courses.{$courseKey}"), 'refresh.days', 120)
        );

        $draw = (int) $request->input('draw', 1);
        if ($from === null || $to === null) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'kpis' => ['total' => 0, 'pacientes' => 0, 'ips' => 0],
                'notice' => 'Aun no hay un corte materializado de alertas disponible.',
            ]);
        }

        $builder = DB::table('ciclo_vida_cache_records as r')
            ->where('r.course_key', $courseKey)
            ->where('r.module_key', 'alertas')
            ->whereIn('r.record_type', ['alert', 'event'])
            ->where('r.range_start', $from->toDateString())
            ->where('r.range_end', $to->toDateString());

        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        if ($length <= 0) {
            $length = 10;
        }

        $recordsTotal = (clone $builder)->count();

        $search = trim((string) data_get($request->input('search'), 'value', ''));
        $filtered = clone $builder;
        if ($search !== '') {
            $like = '%'.$search.'%';
            $filtered->where(function (Builder $query) use ($like): void {
                $query->orWhere('r.tipo_identificacion', 'like', $like)
                    ->orWhere('r.identificacion', 'like', $like)
                    ->orWhere('r.primer_apellido', 'like', $like)
                    ->orWhere('r.segundo_apellido', 'like', $like)
                    ->orWhere('r.primer_nombre', 'like', $like)
                    ->orWhere('r.segundo_nombre', 'like', $like)
                    ->orWhere('r.ips_primaria', 'like', $like)
                    ->orWhere('r.codigo_ips', 'like', $like)
                    ->orWhere('r.descripcion_servicio', 'like', $like);
            });
        }

        $recordsFiltered = (clone $filtered)->count();
        $data = $filtered
            ->orderBy('r.id', 'desc')
            ->offset($start)
            ->limit($length)
            ->get([
                'r.tipo_identificacion as tipoIdentificacion',
                'r.identificacion',
                DB::raw("LTRIM(RTRIM(COALESCE(r.primer_apellido,'') + ' ' + COALESCE(r.segundo_apellido,''))) as apellidos"),
                DB::raw("LTRIM(RTRIM(COALESCE(r.primer_nombre,'') + ' ' + COALESCE(r.segundo_nombre,''))) as nombres"),
                'r.fecha_nacimiento as fechaNacimiento',
                'r.edad as edadAnios',
                'r.edad_meses as edadMeses',
                'r.ips_primaria as ips_Prim',
                'r.codigo_ips as codigoHabilitacion',
                'r.descripcion_servicio as descrip',
            ]);

        $payload = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'kpis' => $this->alertSummary($courseKey, $from, $to),
        ];
        $payload['cut'] = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'exact' => $exact,
        ];
        if (!$exact) {
            $payload['notice'] = 'Mostrando el ultimo corte materializado disponible para alertas.';
        }

        return response()->json($payload);
    }

    public function alertNotificationData(string $courseKey, Request $request): array
    {
        [$from, $to, $exact] = $this->resolveMaterializedCut(
            $courseKey,
            'alertas',
            $request,
            (int) data_get(config("ciclosvida.courses.{$courseKey}"), 'refresh.days', 120)
        );

        if ($from === null || $to === null) {
            return [
                'rows' => collect(),
                'cut' => null,
                'exact' => false,
            ];
        }

        $rows = DB::table('ciclo_vida_cache_records as r')
            ->where('r.course_key', $courseKey)
            ->where('r.module_key', 'alertas')
            ->whereIn('r.record_type', ['alert', 'event'])
            ->where('r.range_start', $from->toDateString())
            ->where('r.range_end', $to->toDateString())
            ->orderBy('r.primer_apellido')
            ->orderBy('r.segundo_apellido')
            ->orderBy('r.primer_nombre')
            ->orderBy('r.segundo_nombre')
            ->orderBy('r.descripcion_servicio')
            ->get([
                'r.tipo_identificacion as tipoIdentificacion',
                'r.identificacion',
                'r.primer_nombre as primerNombre',
                'r.segundo_nombre as segundoNombre',
                'r.primer_apellido as primerApellido',
                'r.segundo_apellido as segundoApellido',
                'r.fecha_nacimiento as fechaNacimiento',
                'r.edad as edadAnios',
                'r.edad_meses as edadMeses',
                'r.ips_primaria as ips_Prim',
                'r.codigo_ips as codigoHabilitacion',
                'r.descripcion_servicio as descrip',
            ]);

        return [
            'rows' => $rows,
            'cut' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'exact' => $exact,
        ];
    }

    public function summary(string $courseKey, Request $request)
    {
        [$from, $to] = $this->resolveRange($request, 120);

        $items = DB::table('ciclo_vida_cache_summaries as s')
            ->where('s.course_key', $courseKey)
            ->where('s.range_start', $from->toDateString())
            ->where('s.range_end', $to->toDateString())
            ->select([
                's.module_key as area',
                's.total_records as total',
                's.unique_patients as pacientes',
                's.unique_ips as ips',
                's.unique_services as fechas',
            ])
            ->get()
            ->map(function ($item) use ($courseKey) {
                try {
                    $item->area = CicloVidaCatalog::module($courseKey, $item->area)['label'];
                } catch (\Throwable $e) {
                }

                return $item;
            });

        $totales = [
            'total' => (int) $items->sum('total'),
            'pacientes' => (int) $items->sum('pacientes'),
            'ips' => (int) $items->sum('ips'),
            'fechas' => (int) $items->sum('fechas'),
        ];

        return response()->json([
            'ok' => true,
            'desde' => $from->toDateString(),
            'hasta' => $to->toDateString(),
            'areas' => $items,
            'totales' => $totales,
        ]);
    }

    public function dashboard(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request, 120);
        $toInclusive = $to->copy()->subDay();

        $base = $this->dashboardBaseBuilder($request, $from, $toInclusive);

        $global = (clone $base)
            ->selectRaw("
                COUNT(1) as total,
                COUNT(DISTINCT r.course_key) as cursos,
                COUNT(DISTINCT r.module_key) as modulos,
                COUNT(DISTINCT CONCAT(COALESCE(r.tipo_identificacion,''), '|', COALESCE(r.identificacion,''))) as pacientes,
                COUNT(DISTINCT NULLIF(r.ips_primaria,'')) as ips,
                COUNT(DISTINCT COALESCE(NULLIF(g.municipio,''), 'Sin municipio')) as municipios,
                COUNT(DISTINCT COALESCE(NULLIF(g.departamento,''), 'Sin departamento')) as departamentos,
                COUNT(DISTINCT COALESCE(NULLIF(r.descripcion_servicio,''), NULLIF(r.codigo_servicio,''))) as servicios,
                COUNT(DISTINCT r.event_date) as dias
            ")
            ->first();

        $courseRows = (clone $base)
            ->select([
                'r.course_key',
                DB::raw('COUNT(1) as total'),
                DB::raw("COUNT(DISTINCT CONCAT(COALESCE(r.tipo_identificacion,''), '|', COALESCE(r.identificacion,''))) as pacientes"),
                DB::raw('COUNT(DISTINCT r.ips_primaria) as ips'),
                DB::raw('COUNT(DISTINCT r.module_key) as modulos'),
            ])
            ->groupBy('r.course_key')
            ->get()
            ->keyBy('course_key');

        $latestCuts = DB::table('ciclo_vida_cache_runs as cr')
            ->select([
                'cr.course_key',
                DB::raw('MAX(cr.finished_at) as finished_at'),
            ])
            ->where('cr.status', 'success')
            ->groupBy('cr.course_key')
            ->get()
            ->keyBy('course_key');

        $courses = collect(CicloVidaCatalog::courses())
            ->map(function (array $course, string $courseKey) use ($courseRows, $latestCuts) {
                $row = $courseRows->get($courseKey);
                $finishedAt = $latestCuts->get($courseKey)->finished_at ?? null;

                return [
                    'key' => $courseKey,
                    'slug' => $course['slug'] ?? $courseKey,
                    'label' => $course['label'] ?? $courseKey,
                    'ageLabel' => $course['age_label'] ?? null,
                    'icon' => $course['icon'] ?? 'fas fa-layer-group',
                    'color' => $course['color'] ?? 'bg-secondary',
                    'total' => (int) ($row->total ?? 0),
                    'pacientes' => (int) ($row->pacientes ?? 0),
                    'ips' => (int) ($row->ips ?? 0),
                    'modulos' => (int) ($row->modulos ?? 0),
                    'updatedAt' => $finishedAt ? Carbon::parse((string) $finishedAt)->format('Y-m-d H:i') : null,
                ];
            })
            ->values();

        $moduleRows = (clone $base)
            ->select([
                'r.module_key',
                DB::raw('COUNT(1) as total'),
            ])
            ->groupBy('r.module_key')
            ->orderByDesc(DB::raw('COUNT(1)'))
            ->get();

        $modules = $moduleRows
            ->map(function ($row) {
                return [
                    'key' => $row->module_key,
                    'label' => $this->moduleLabel((string) $row->module_key),
                    'value' => (int) $row->total,
                ];
            })
            ->take(12)
            ->values();

        $ips = (clone $base)
            ->select([
                DB::raw("COALESCE(NULLIF(r.ips_primaria,''), 'Sin IPS') as label"),
                DB::raw('COUNT(1) as value'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(r.ips_primaria,''), 'Sin IPS')"))
            ->orderByDesc(DB::raw('COUNT(1)'))
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->value])
            ->values();

        $municipalities = (clone $base)
            ->select([
                DB::raw("COALESCE(NULLIF(g.municipio,''), 'Sin municipio') as label"),
                DB::raw('COUNT(1) as value'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(g.municipio,''), 'Sin municipio')"))
            ->orderByDesc(DB::raw('COUNT(1)'))
            ->limit(12)
            ->get()
            ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->value])
            ->values();

        $departments = (clone $base)
            ->select([
                DB::raw("COALESCE(NULLIF(g.departamento,''), 'Sin departamento') as label"),
                DB::raw('COUNT(1) as value'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(g.departamento,''), 'Sin departamento')"))
            ->orderByDesc(DB::raw('COUNT(1)'))
            ->limit(12)
            ->get()
            ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->value])
            ->values();

        $services = (clone $base)
            ->select([
                DB::raw("COALESCE(NULLIF(r.descripcion_servicio,''), COALESCE(NULLIF(r.codigo_servicio,''), 'Sin servicio')) as label"),
                DB::raw('COUNT(1) as value'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(r.descripcion_servicio,''), COALESCE(NULLIF(r.codigo_servicio,''), 'Sin servicio'))"))
            ->orderByDesc(DB::raw('COUNT(1)'))
            ->limit(12)
            ->get()
            ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->value])
            ->values();

        $ageRanges = (clone $base)
            ->select([
                DB::raw("COALESCE(NULLIF(r.rango_edad,''), 'Sin rango') as label"),
                DB::raw('COUNT(1) as value'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(r.rango_edad,''), 'Sin rango')"))
            ->orderByDesc(DB::raw('COUNT(1)'))
            ->get()
            ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->value])
            ->values();

        $trendRows = (clone $base)
            ->select([
                'r.course_key',
                DB::raw("CONVERT(char(7), r.event_date, 120) as period"),
                DB::raw('COUNT(1) as total'),
            ])
            ->groupBy('r.course_key', DB::raw("CONVERT(char(7), r.event_date, 120)"))
            ->orderBy(DB::raw("CONVERT(char(7), r.event_date, 120)"))
            ->get();

        $periods = $trendRows->pluck('period')->unique()->values()->all();
        $series = $courses
            ->map(function (array $course) use ($periods, $trendRows) {
                $values = [];
                foreach ($periods as $period) {
                    $row = $trendRows->first(fn ($item) => $item->course_key === $course['key'] && $item->period === $period);
                    $values[] = (int) ($row->total ?? 0);
                }

                return [
                    'courseKey' => $course['key'],
                    'label' => $course['label'],
                    'color' => $this->chartColor($course['key']),
                    'data' => $values,
                ];
            })
            ->values();

        $categories = collect($this->statsGroups)
            ->map(function (array $group) use ($moduleRows) {
                $keys = collect($group['modules']);
                $value = (int) $moduleRows
                    ->whereIn('module_key', $keys)
                    ->sum('total');

                return [
                    'label' => $group['label'],
                    'value' => $value,
                ];
            })
            ->filter(fn (array $item) => $item['value'] > 0)
            ->values();

        $courseCategoryRows = (clone $base)
            ->select([
                'r.course_key',
                'r.module_key',
                DB::raw('COUNT(1) as total'),
            ])
            ->groupBy('r.course_key', 'r.module_key')
            ->get();

        $courseCategorySeries = collect($this->statsGroups)
            ->map(function (array $group, string $groupKey) use ($courses, $courseCategoryRows) {
                return [
                    'key' => $groupKey,
                    'label' => $group['label'],
                    'color' => match ($groupKey) {
                        'atencion_integral' => '#2563eb',
                        'proteccion_especifica' => '#16a34a',
                        'tamizajes_cancer' => '#f59e0b',
                        'riesgo_cardiovascular' => '#dc2626',
                        'ssr_r202' => '#7c3aed',
                        default => '#64748b',
                    },
                    'data' => $courses->map(function (array $course) use ($courseCategoryRows, $group): int {
                        return (int) $courseCategoryRows
                            ->where('course_key', $course['key'])
                            ->whereIn('module_key', $group['modules'])
                            ->sum('total');
                    })->values()->all(),
                ];
            })
            ->filter(fn (array $series) => collect($series['data'])->sum() > 0)
            ->values();

        $filterBase = $this->dashboardBaseBuilder(
            $request,
            $from,
            $toInclusive,
            ['modulo', 'ips', 'municipio', 'departamento', 'rango_edad']
        );

        $filterOptions = [
            'courses' => collect(CicloVidaCatalog::courses())
                ->map(fn (array $course, string $key) => ['value' => $key, 'label' => $course['label']])
                ->values(),
            'groups' => collect($this->statsGroups)
                ->map(fn (array $group, string $key) => ['value' => $key, 'label' => $group['label']])
                ->values(),
            'modules' => (clone $filterBase)
                ->select('r.module_key')
                ->distinct()
                ->orderBy('r.module_key')
                ->get()
                ->map(fn ($row) => ['value' => $row->module_key, 'label' => $this->moduleLabel((string) $row->module_key)])
                ->values(),
            'ips' => (clone $filterBase)
                ->select(DB::raw("COALESCE(NULLIF(r.ips_primaria,''), 'Sin IPS') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'departments' => (clone $filterBase)
                ->select(DB::raw("COALESCE(NULLIF(g.departamento,''), 'Sin departamento') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'municipalities' => (clone $filterBase)
                ->select(DB::raw("COALESCE(NULLIF(g.municipio,''), 'Sin municipio') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'ageRanges' => (clone $filterBase)
                ->select(DB::raw("COALESCE(NULLIF(r.rango_edad,''), 'Sin rango') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'selected' => [
                'curso' => (string) $request->query('curso', ''),
                'linea' => (string) $request->query('linea', ''),
                'modulo' => (string) $request->query('modulo', ''),
                'ips' => (string) $request->query('ips', ''),
                'departamento' => (string) $request->query('departamento', ''),
                'municipio' => (string) $request->query('municipio', ''),
                'rango_edad' => (string) $request->query('rango_edad', ''),
            ],
        ];

        $highlights = [
            [
                'label' => 'Curso con mayor actividad',
                'value' => data_get($courses->sortByDesc('total')->first(), 'label', 'Sin datos'),
                'support' => number_format((int) data_get($courses->sortByDesc('total')->first(), 'total', 0), 0, ',', '.').' atenciones',
            ],
            [
                'label' => 'Municipio principal',
                'value' => data_get($municipalities->first(), 'label', 'Sin datos'),
                'support' => number_format((int) data_get($municipalities->first(), 'value', 0), 0, ',', '.').' registros',
            ],
            [
                'label' => 'IPS principal',
                'value' => data_get($ips->first(), 'label', 'Sin datos'),
                'support' => number_format((int) data_get($ips->first(), 'value', 0), 0, ',', '.').' registros',
            ],
            [
                'label' => 'Servicio mas frecuente',
                'value' => data_get($services->first(), 'label', 'Sin datos'),
                'support' => number_format((int) data_get($services->first(), 'value', 0), 0, ',', '.').' registros',
            ],
        ];

        return [
            'ok' => true,
            'desde' => $from->toDateString(),
            'hasta' => $toInclusive->toDateString(),
            'kpis' => [
                'total' => (int) ($global->total ?? 0),
                'pacientes' => (int) ($global->pacientes ?? 0),
                'cursos' => (int) ($global->cursos ?? 0),
                'modulos' => (int) ($global->modulos ?? 0),
                'ips' => (int) ($global->ips ?? 0),
                'municipios' => (int) ($global->municipios ?? 0),
                'departamentos' => (int) ($global->departamentos ?? 0),
                'servicios' => (int) ($global->servicios ?? 0),
                'dias' => (int) ($global->dias ?? 0),
            ],
            'courses' => $courses,
            'trend' => [
                'labels' => $periods,
                'series' => $series,
            ],
            'modules' => $modules,
            'ips' => $ips,
            'municipalities' => $municipalities,
            'departments' => $departments,
            'services' => $services,
            'ageRanges' => $ageRanges,
            'categories' => $categories,
            'courseCategories' => [
                'labels' => $courses->pluck('label')->values(),
                'series' => $courseCategorySeries,
            ],
            'filters' => $filterOptions,
            'highlights' => $highlights,
        ];
    }

    protected function dashboardBaseBuilder(
        Request $request,
        Carbon $from,
        Carbon $toInclusive,
        array $ignoreFilters = []
    ): Builder {
        $query = DB::table('ciclo_vida_cache_records as r')
            ->leftJoinSub($this->geoLookupSubquery(), 'g', function ($join): void {
                $join->on('r.tipo_identificacion', '=', 'g.tipo_identificacion')
                    ->on('r.identificacion', '=', 'g.identificacion');
            })
            ->where('r.record_type', 'event')
            ->whereBetween('r.event_date', [$from->toDateString(), $toInclusive->toDateString()]);

        $this->applyDashboardFilters($query, $request, $ignoreFilters);

        return $query;
    }

    protected function geoLookupSubquery(): Builder
    {
        return DB::query()
            ->from(DB::raw('sga..maestroidentificaciones as x'))
            ->join(DB::raw('sga..maestroafiliados as afi'), 'x.numeroCarnet', '=', 'afi.numeroCarnet')
            ->leftJoin(DB::raw('sga..municipios as m'), function ($join): void {
                $join->on('afi.codigoDepartamento', '=', 'm.codigoDepartamento')
                    ->on('afi.codigoMunicipio', '=', 'm.codigoMunicipio');
            })
            ->leftJoin(DB::raw('sga..departamentos as d'), 'afi.codigoDepartamento', '=', 'd.codigo')
            ->select([
                'x.tipoIdentificacion as tipo_identificacion',
                'x.identificacion',
                DB::raw("MAX(COALESCE(NULLIF(m.descrip,''), 'Sin municipio')) as municipio"),
                DB::raw("MAX(COALESCE(NULLIF(d.descrip,''), 'Sin departamento')) as departamento"),
            ])
            ->groupBy('x.tipoIdentificacion', 'x.identificacion');
    }

    protected function applyDashboardFilters(Builder $query, Request $request, array $ignoreFilters = []): void
    {
        $ignore = array_fill_keys($ignoreFilters, true);

        $course = trim((string) $request->query('curso', ''));
        if ($course !== '' && !isset($ignore['curso'])) {
            $query->where('r.course_key', $course);
        }

        $line = trim((string) $request->query('linea', ''));
        if ($line !== '' && !isset($ignore['linea']) && isset($this->statsGroups[$line])) {
            $query->whereIn('r.module_key', $this->statsGroups[$line]['modules']);
        }

        $module = trim((string) $request->query('modulo', ''));
        if ($module !== '' && !isset($ignore['modulo'])) {
            $query->where('r.module_key', $module);
        }

        $ips = trim((string) $request->query('ips', ''));
        if ($ips !== '' && !isset($ignore['ips'])) {
            if ($ips === 'Sin IPS') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('r.ips_primaria')->orWhere('r.ips_primaria', '');
                });
            } else {
                $query->where('r.ips_primaria', $ips);
            }
        }

        $department = trim((string) $request->query('departamento', ''));
        if ($department !== '' && !isset($ignore['departamento'])) {
            $query->whereRaw("COALESCE(NULLIF(g.departamento,''), 'Sin departamento') = ?", [$department]);
        }

        $municipality = trim((string) $request->query('municipio', ''));
        if ($municipality !== '' && !isset($ignore['municipio'])) {
            $query->whereRaw("COALESCE(NULLIF(g.municipio,''), 'Sin municipio') = ?", [$municipality]);
        }

        $ageRange = trim((string) $request->query('rango_edad', ''));
        if ($ageRange !== '' && !isset($ignore['rango_edad'])) {
            if ($ageRange === 'Sin rango') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('r.rango_edad')->orWhere('r.rango_edad', '');
                });
            } else {
                $query->where('r.rango_edad', $ageRange);
            }
        }
    }

    protected function kpisFromBuilder(Builder $builder): array
    {
        $kpi = DB::query()
            ->fromSub(
                $builder->select([
                    'r.tipo_identificacion',
                    'r.identificacion',
                    'r.ips_primaria',
                    'r.codigo_servicio',
                    'r.event_date',
                ]),
                'q'
            )
            ->selectRaw("
                COUNT(1) as total,
                COUNT(DISTINCT CONCAT(COALESCE(q.tipo_identificacion,''), '|', COALESCE(q.identificacion,''))) as pacientes,
                COUNT(DISTINCT q.ips_primaria) as ips,
                COUNT(DISTINCT q.codigo_servicio) as cups,
                COUNT(DISTINCT q.event_date) as fechas
            ")
            ->first();

        return [
            'total' => (int) ($kpi->total ?? 0),
            'pacientes' => (int) ($kpi->pacientes ?? 0),
            'ips' => (int) ($kpi->ips ?? 0),
            'cups' => (int) ($kpi->cups ?? 0),
            'fechas' => (int) ($kpi->fechas ?? 0),
        ];
    }

    protected function resolveRange(Request $request, int $defaultDays): array
    {
        try {
            $from = Carbon::parse((string) $request->query('desde', now()->subDays($defaultDays)->toDateString()))->startOfDay();
            $to = Carbon::parse((string) $request->query('hasta', now()->addDay()->toDateString()))->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->subDays($defaultDays)->startOfDay();
            $to = now()->addDay()->startOfDay();
        }

        if ($to->lessThanOrEqualTo($from)) {
            $to = $from->copy()->addDay();
        }

        return [$from, $to];
    }

    protected function resolveMaterializedCut(
        string $courseKey,
        string $moduleKey,
        Request $request,
        int $defaultDays
    ): array {
        [$requestedFrom, $requestedTo] = $this->resolveRange($request, $defaultDays);

        $exact = DB::table('ciclo_vida_cache_runs')
            ->where('course_key', $courseKey)
            ->where('module_key', $moduleKey)
            ->where('status', 'success')
            ->where('range_start', $requestedFrom->toDateString())
            ->where('range_end', $requestedTo->toDateString())
            ->orderByDesc('id')
            ->first(['range_start', 'range_end']);

        if ($exact) {
            return [
                Carbon::parse((string) $exact->range_start)->startOfDay(),
                Carbon::parse((string) $exact->range_end)->startOfDay(),
                true,
            ];
        }

        $latest = DB::table('ciclo_vida_cache_runs')
            ->where('course_key', $courseKey)
            ->where('module_key', $moduleKey)
            ->where('status', 'success')
            ->orderByDesc('id')
            ->first(['range_start', 'range_end']);

        if (!$latest) {
            return [null, null, false];
        }

        return [
            Carbon::parse((string) $latest->range_start)->startOfDay(),
            Carbon::parse((string) $latest->range_end)->startOfDay(),
            false,
        ];
    }

    protected function alertSummary(string $courseKey, Carbon $from, Carbon $to): array
    {
        $summary = DB::table('ciclo_vida_cache_summaries')
            ->where('course_key', $courseKey)
            ->where('module_key', 'alertas')
            ->where('range_start', $from->toDateString())
            ->where('range_end', $to->toDateString())
            ->first();

        return [
            'total' => (int) ($summary->total_records ?? 0),
            'pacientes' => (int) ($summary->unique_patients ?? 0),
            'ips' => (int) ($summary->unique_ips ?? 0),
        ];
    }

    protected function moduleLabel(string $moduleKey): string
    {
        foreach (CicloVidaCatalog::courses() as $courseKey => $course) {
            $module = data_get($course, "modules.{$moduleKey}");
            if (is_array($module)) {
                return (string) ($module['label'] ?? $moduleKey);
            }
        }

        return $moduleKey;
    }

    protected function chartColor(string $courseKey): string
    {
        return match ($courseKey) {
            'primera_infancia' => '#2563eb',
            'infancia' => '#16a34a',
            'adolescencia' => '#0891b2',
            'juventud' => '#f59e0b',
            'adultez' => '#dc2626',
            'vejez' => '#0f766e',
            default => '#64748b',
        };
    }
}
