<?php

namespace App\Http\Controllers;

use App\Services\CicloVida\CicloVidaCacheRepository;
use App\Services\CicloVida\CicloVidaCoverageAnalyzer;
use App\Services\CicloVida\CicloVidaReportDesigner;
use App\Support\CicloVidaCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; 

class CicloVidaController extends Controller
{
    private array $etapas = [
        'primera-infancia' => [
            'titulo' => 'Primera infancia',
            'descripcion' => '0 a 5 años: crecimiento, desarrollo temprano, vacunas, nutrición.',
            'color' => 'bg-primary',
            'icono' => 'fas fa-baby',
        ],
        'infancia' => [
            'titulo' => 'Infancia',
            'descripcion' => '6 a 11 años: escolaridad, hábitos saludables, desarrollo psicosocial.',
            'color' => 'bg-success',
            'icono' => 'fas fa-child',
        ],
        'adolescencia' => [
            'titulo' => 'Adolescencia',
            'descripcion' => '12 a 17 años: cambios físicos, salud mental, educación sexual.',
            'color' => 'bg-info',
            'icono' => 'fas fa-user-friends',
        ],
        'juventud' => [
            'titulo' => 'Juventud',
            'descripcion' => '18 a 28 años: estilos de vida, salud sexual y reproductiva, prevención.',
            'color' => 'bg-warning',
            'icono' => 'fas fa-user-graduate',
        ],
        'adultez' => [
            'titulo' => 'Adultez',
            'descripcion' => '29 a 59 años: control de riesgos crónicos, trabajo, familia.',
            'color' => 'bg-danger',
            'icono' => 'fas fa-user-tie',
        ],
        'vejez' => [
            'titulo' => 'Vejez',
            'descripcion' => '60+ años: envejecimiento saludable, funcionalidad, cuidados.',
            'color' => 'bg-teal',
            'icono' => 'fas fa-blind',
        ],
    ];

    public function index()
    {
        $etapas = $this->catalogCourseCards();
        $quickCards = [
            [
                'title' => 'Estadisticas',
                'description' => 'Tablero consolidado con tendencias, graficas, top de IPS, modulos y comparativo entre cursos de vida.',
                'icon' => 'fas fa-chart-pie',
                'color' => 'grad-indigo',
                'route' => route('ciclosvida.stats.index'),
            ],
            [
                'title' => 'Informacion general',
                'description' => 'Vista ejecutiva con enfoque, rango etario, resumen operativo y accesos dinamicos por cada curso de vida.',
                'icon' => 'fas fa-layer-group',
                'color' => 'grad-emerald',
                'route' => route('ciclosvida.general.index'),
            ],
            [
                'title' => 'Reportes',
                'description' => 'Disenador flexible con plantillas, vista previa y exportacion en Excel, CSV y JSON para todos los cursos de vida.',
                'icon' => 'fas fa-file-export',
                'color' => 'grad-rose',
                'route' => route('ciclosvida.reports.index'),
            ],
            [
                'title' => 'Cobertura y brechas',
                'description' => 'Analiza atenciones realizadas, oportunidades normativas y faltantes por curso de vida con filtros territoriales y operativos.',
                'icon' => 'fas fa-table-cells-large',
                'color' => 'grad-cyan',
                'route' => route('ciclosvida.coverage.index'),
            ],
        ];

        return view('ciclo_vidas.home', compact('etapas', 'quickCards'));
    }

    public function dashboardData(Request $request)
    {
        return response()->json($this->cacheRepo()->dashboard($request));
    }

    public function statistics()
    {
        $etapas = $this->catalogCourseCards();
        $desde = now()->subDays(120)->startOfDay()->toDateString();
        $hasta = now()->toDateString();
        $focusAreas = [
            [
                'title' => 'Atencion integral por curso de vida',
                'description' => 'Seguimiento de consultas medicas, odontologicas y otras atenciones trazadoras segun Resolucion 3280 de 2018.',
                'icon' => 'fas fa-stethoscope',
            ],
            [
                'title' => 'Proteccion especifica y SSR',
                'description' => 'Consolida acciones de proteccion especifica, salud sexual y reproductiva, pruebas rapidas y metodos de planificacion.',
                'icon' => 'fas fa-shield-heart',
            ],
            [
                'title' => 'Tamizajes prioritarios',
                'description' => 'Prioriza riesgo cardiovascular, cuello uterino, mama, prostata y colon con enfoque util para gestion.',
                'icon' => 'fas fa-chart-line',
            ],
        ];
        $normativeLinks = $this->normativeLinks();

        return view('ciclo_vidas.index', compact('etapas', 'desde', 'hasta', 'focusAreas', 'normativeLinks'));
    }

    public function generalInfo()
    {
        $courseMeta = collect(CicloVidaCatalog::courses())
            ->map(function (array $course, string $courseKey) {
                return [
                    'key' => $courseKey,
                    'slug' => $course['slug'] ?? $courseKey,
                    'label' => $course['label'] ?? $courseKey,
                    'description' => $course['description'] ?? '',
                    'ageLabel' => $course['age_label'] ?? '',
                    'icon' => $course['icon'] ?? 'fas fa-layer-group',
                    'color' => $course['color'] ?? 'bg-secondary',
                    'menuRoute' => route($course['menu_route']),
                    'groups' => collect(CicloVidaCatalog::menuGroups($courseKey))
                        ->map(function (array $group) {
                            return [
                                'title' => $group['title'] ?? 'Grupo',
                                'items' => collect($group['items'] ?? [])
                                    ->pluck('short_label')
                                    ->filter()
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $desde = now()->subDays(120)->startOfDay()->toDateString();
        $hasta = now()->toDateString();
        $normativeLinks = $this->normativeLinks();
        $companyLogo = asset('vendor/adminlte/dist/img/logo.png');

        return view('ciclo_vidas.informacion_general', compact('courseMeta', 'desde', 'hasta', 'normativeLinks', 'companyLogo'));
    }

    public function reports()
    {
        $designer = app(CicloVidaReportDesigner::class);
        $page = $designer->lightweightPageData();

        return view('ciclo_vidas.reportes', [
            'filters' => $page['filters'],
            'fieldGroups' => $page['fieldGroups'],
            'templates' => $page['templates'],
            'desde' => now()->subDays(120)->startOfDay()->toDateString(),
            'hasta' => now()->toDateString(),
            'companyLogo' => asset('vendor/adminlte/dist/img/logo.png'),
            'previewUrl' => route('ciclosvida.reports.preview'),
            'exportBaseUrl' => route('ciclosvida.reports.export', ['format' => '__FORMAT__']),
            'advancedFiltersUrl' => route('ciclosvida.reports.filters'),
        ]);
    }

    public function reportFilters()
    {
        return response()->json(app(CicloVidaReportDesigner::class)->advancedFilterOptions());
    }

    public function reportPreview(Request $request)
    {
        return response()->json(app(CicloVidaReportDesigner::class)->preview($request));
    }

    public function reportExport(Request $request, string $format)
    {
        return app(CicloVidaReportDesigner::class)->export($request, $format);
    }

    // Menú de opciones para "Primera Infancia"
    public function coverage()
    {
        $page = app(CicloVidaCoverageAnalyzer::class)->pageData();

        return view('ciclo_vidas.cobertura_brechas', [
            'filters' => $page['filters'],
            'ruleLegend' => $page['ruleLegend'],
            'desde' => now()->subDays(120)->startOfDay()->toDateString(),
            'hasta' => now()->toDateString(),
            'companyLogo' => asset('img/logo.png'),
            'dataUrl' => route('ciclosvida.coverage.data'),
            'advancedFiltersUrl' => route('ciclosvida.coverage.filters'),
        ]);
    }

    public function coverageFilters()
    {
        return response()->json(app(CicloVidaCoverageAnalyzer::class)->advancedFilterOptions());
    }

    public function coverageData(Request $request)
    {
        return response()->json(app(CicloVidaCoverageAnalyzer::class)->analyze($request));
    }

    public function menuPrimeraInfancia()
    {
        return $this->renderCourseMenu('primera_infancia');
    }

    public function menuInfancia()
    {
        return $this->renderCourseMenu('infancia');
    }

    public function menuAdolescencia()
    {
        return $this->renderCourseMenu('adolescencia');
    }

    public function menuJuventud()
    {
        return $this->renderCourseMenu('juventud');
    }

    public function menuAdultez()
    {
        return $this->renderCourseMenu('adultez');
    }

    public function menuVejez()
    {
        return $this->renderCourseMenu('vejez');
    }

    public function show(string $slug)
    {
        $course = CicloVidaCatalog::courseBySlug($slug);
        $etapa = [
            'slug' => $slug,
            'titulo' => $course['label'],
            'descripcion' => $course['description'],
        ];

        // Rango por defecto: año actual (hasta exclusivo)
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();

        // Vista detalle (tu DataTable server-side)
        return view('ciclo_vidas.show', [
            'etapa' => $etapa,
            'desde' => $desde,
            'hasta' => $hasta,
            'dataUrl' => route('ciclosvida.data', $slug),
            'tableTitle' => 'Detalle de atenciones · '.$course['label'],
            'dataSourceLabel' => 'Datos materializados por curso de vida',
        ]);
    }

public function data(Request $request, string $slug)
{
    $course = CicloVidaCatalog::courseBySlug($slug);

    if ($course['key'] !== 'primera_infancia') {
        return DataTables::of(DB::query()->selectRaw('1')->whereRaw('1=0'))->toJson();
    }

    return $this->cacheRepo()->eventTable($course['key'], ['medica'], $request);

    if ($slug !== 'primera-infancia') {
        return \Yajra\DataTables\Facades\DataTables::of(DB::query()->selectRaw('1')->whereRaw('1=0'))->toJson();
    }

    try {
        $desde = \Carbon\Carbon::parse($request->query('desde'))->startOfDay()->toDateString();
        $hasta = \Carbon\Carbon::parse($request->query('hasta'))->startOfDay()->toDateString();
    } catch (\Throwable $e) {
        $desde = now()->subDays(60)->startOfDay()->toDateString();
        $hasta = now()->addDay()->startOfDay()->toDateString();
    }
    foreach ([$desde, $hasta] as $v) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return response()->json(['error' => 'Rango de fechas inválido.'], 422);
        }
    }

    $fnName   = '[PRUEBA_DESNUTRICION].[dbo].[fn_pi_consultas_0a5]';
    // Si tienes el cache local: $cupsName = '[PRUEBA_DESNUTRICION].[dbo].[refCups_cache]';
    $cupsName = '[SGA].[dbo].[refCups]';

    DB::connection('sqlsrv_1')->statement('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;');

    $fnFrom = DB::raw("$fnName('$desde', '$hasta') AS V");

    $builder = DB::connection('sqlsrv_1')
        ->table($fnFrom)
        ->leftJoin(DB::raw($cupsName.' AS C'), 'V.codigoConsulta', '=', 'C.codigo')
        ->select([
            DB::raw('V.fechaAtencion AS fechaConsulta'),
            'V.tipoIdentificacion',
            'V.identificacion',
            'V.primerNombre',
            'V.segundoNombre',
            'V.primerApellido',
            'V.segundoApellido',
            'V.codigoConsulta',
            DB::raw('C.descrip AS descrip'),
            'V.diagnosticoPrincipal',
            'V.finalidadConsulta',
            'V.ips_Prim',
            'V.edad',
        ]);

    // === Filtro GLOBAL de búsqueda (corrige el buscador de DataTables) ===
    $dt = \Yajra\DataTables\Facades\DataTables::of($builder)
        ->filter(function ($query) use ($request) {
            $kw = (string) data_get($request->input(), 'search.value', '');
            if ($kw === '') return;

            // Escapar comodines básicos
            $kw = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $kw);
            $like = "%{$kw}%";

            $query->where(function ($q) use ($like) {
                $q->orWhere('V.tipoIdentificacion', 'like', $like)
                  ->orWhere('V.identificacion', 'like', $like)
                  ->orWhere('V.primerNombre', 'like', $like)
                  ->orWhere('V.segundoNombre', 'like', $like)
                  ->orWhere('V.primerApellido', 'like', $like)
                  ->orWhere('V.segundoApellido', 'like', $like)
                  ->orWhere('V.codigoConsulta', 'like', $like)
                  ->orWhere('C.descrip', 'like', $like)
                  ->orWhere('V.diagnosticoPrincipal', 'like', $like)
                  ->orWhere('V.finalidadConsulta', 'like', $like)
                  ->orWhere('V.ips_Prim', 'like', $like)
                  // fecha YYYY-MM-DD
                  ->orWhereRaw("CONVERT(varchar(10), V.fechaAtencion, 23) LIKE ?", [$like]);
            });
        }, true) // true => reemplaza el filtro global por éste
        // === Orden para columnas con alias ===
        ->orderColumn('fechaConsulta', 'V.fechaAtencion $1')
        ->orderColumn('descrip', 'C.descrip $1');

    // KPIs (igual que antes)
    $kpis = DB::connection('sqlsrv_1')->query()
        ->fromSub(
            DB::connection('sqlsrv_1')->table($fnFrom)->select([
                'tipoIdentificacion','identificacion','ips_Prim','codigoConsulta'
            ]),
            'Q'
        )
        ->selectRaw("
            COUNT(1) AS total,
            COUNT(DISTINCT CAST(Q.tipoIdentificacion AS varchar(12)) + '-' + CAST(Q.identificacion AS varchar(32))) AS pacientes,
            COUNT(DISTINCT Q.ips_Prim) AS ips,
            COUNT(DISTINCT Q.codigoConsulta) AS cups
        ")
        ->first();

    $payload = $dt->toArray();
    $payload['kpis'] = [
        'total'     => (int) ($kpis->total ?? 0),
        'pacientes' => (int) ($kpis->pacientes ?? 0),
        'ips'       => (int) ($kpis->ips ?? 0),
        'cups'      => (int) ($kpis->cups ?? 0),
    ];

    return response()->json($payload);
}




    /**
     * NUEVO MÉTODO:
     * Pantalla placeholder para cualquier opción del menú de Primera Infancia.
     * Reemplaza el método anterior pero con otro nombre, y centraliza todo en este controlador.
     */
    public function piPlaceholder(Request $request)
    {
        $key = $request->get('key', 'en_construccion');
        $title = $key;

        try {
            $title = CicloVidaCatalog::module('primera_infancia', $key)['label'];
        } catch (\Throwable $e) {
        }

        return view('ciclo_vidas.placeholder', [
            'key' => $key,
            'titulo' => $title,
        ]);

        // 'key' via ->defaults('key','...') en las rutas
        $key = $request->get('key', 'en_construccion');

        return view('ciclo_vidas.placeholder', [
            'key' => $key,
            'titulo' => match ($key) {
                'medica'             => 'Atención en salud médica',
                'enfermeria'         => 'Atención por enfermería',
                'bucal_fluor_sem1'   => 'Flúor · Primer semestre',
                'bucal_fluor_sem2'   => 'Flúor · Segundo semestre',
                'bucal_placa_sem1'   => 'Control de placa · Primer semestre',
                'bucal_placa_sem2'   => 'Control de placa · Segundo semestre',
                'bucal_sellantes'    => 'Sellantes',
                'nutri_hemoglobina'  => 'Tamizaje de hemoglobina',
                'nutri_lactancia'    => 'Apoyo a lactancia materna (R202)',
                'nutri_vitamina_a'   => 'Vitamina A (R202)',
                'nutri_hierro'       => 'Hierro (R202)',
                default              => 'En construcción',
            },
        ]);
    }



public function enfermeria()
{
    // Definir textos de etapa para la cabecera
    $etapa = [
        'slug'        => 'enfermeria',
        'titulo'      => 'Atención por enfermería',
        'descripcion' => 'Procedimientos y seguimientos realizados por enfermería.',
    ];

    // Rango por defecto: año actual (hasta exclusivo = mañana)
    $desde = now()->startOfYear()->toDateString();
    $hasta = now()->addDay()->toDateString();

    // Reutilizamos la misma vista “tipo 1” pero específica de enfermería
    return view('ciclo_vidas.enfermeria', compact('etapa', 'desde', 'hasta'));
}

  
public function enfermeriaData(Request $request)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['enfermeria'], $request);

    // 1) Rango de fechas (exclusivo en 'hasta')
    try {
        $desde = Carbon::parse($request->query('desde'))->toDateString();
        $hasta = Carbon::parse($request->query('hasta'))->toDateString(); // exclusivo
    } catch (\Throwable $e) {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();
    }

    // 2) Base SOLO con filtros (sin select aún) — reutilizable
    $periodBase = DB::connection('sqlsrv_1')
        ->table('PRUEBA_DESNUTRICION.dbo.vw_enfermeria_atenciones as V')
        ->whereBetween('V.fechaAtencion', [$desde, $hasta]);

    // 3) DataSource para DataTables (aquí sí seleccionamos columnas)
    $dataSource = (clone $periodBase)->select([
        DB::raw('CAST(V.fechaAtencion AS date) as fechaConsulta'), // alias para el front
        'V.tipoIdentificacion',
        'V.identificacion',
        'V.primerNombre',
        'V.segundoNombre',
        'V.primerApellido',
        'V.segundoApellido',
        'V.codigoConsulta',
        'V.descrip',
        'V.diagnosticoPrincipal',
        'V.finalidadConsulta',
        'V.ips_Prim',
        'V.edad',
    ]);

    // 4) KPIs sobre un subquery (evita mezclar COUNT con otras columnas)
    $kpis = DB::connection('sqlsrv_1')->query()
        ->fromSub((clone $periodBase), 'Q')
        ->selectRaw("
            COUNT(1)                                                   AS total,
            COUNT(DISTINCT CONCAT(Q.tipoIdentificacion,'-',Q.identificacion)) AS pacientes,
            COUNT(DISTINCT Q.ips_Prim)                                 AS ips,
            COUNT(DISTINCT Q.codigoConsulta)                           AS cups
        ")
        ->first();

    // 5) DataTables con búsqueda global y orden de columnas seguro
    $payload = DataTables::of($dataSource)
        ->filter(function ($query) use ($request) {
            $search = $request->input('search.value');
            if (!empty($search)) {
                $search = str_replace(['%', '_'], ['\%','\_'], $search);
                $query->where(function ($q) use ($search) {
                    $q->where('V.tipoIdentificacion', 'like', "%{$search}%")
                      ->orWhere('V.identificacion', 'like', "%{$search}%")
                      ->orWhere('V.primerNombre', 'like', "%{$search}%")
                      ->orWhere('V.segundoNombre', 'like', "%{$search}%")
                      ->orWhere('V.primerApellido', 'like', "%{$search}%")
                      ->orWhere('V.segundoApellido', 'like', "%{$search}%")
                      ->orWhere('V.codigoConsulta', 'like', "%{$search}%")
                      ->orWhere('V.descrip', 'like', "%{$search}%")
                      ->orWhere('V.diagnosticoPrincipal', 'like', "%{$search}%")
                      ->orWhere('V.finalidadConsulta', 'like', "%{$search}%")
                      ->orWhere('V.ips_Prim', 'like', "%{$search}%");
                });
            }
        })
        ->order(function ($query) use ($request) {
            $order = $request->input('order.0');
            $columns = [
                0  => 'V.fechaAtencion',   // Fecha
                1  => 'V.tipoIdentificacion',
                2  => 'V.identificacion',
                3  => 'V.primerNombre',
                4  => 'V.segundoNombre',
                5  => 'V.primerApellido',
                6  => 'V.segundoApellido',
                7  => 'V.codigoConsulta',
                8  => 'V.descrip',
                9  => 'V.diagnosticoPrincipal',
                10 => 'V.finalidadConsulta',
                11 => 'V.ips_Prim',
                12 => 'V.edad',
            ];
            if (is_array($order) && isset($order['column'], $order['dir'])) {
                $idx = (int)$order['column'];
                $dir = strtolower($order['dir']) === 'asc' ? 'asc' : 'desc';
                if (isset($columns[$idx])) {
                    $query->orderBy($columns[$idx], $dir);
                    return;
                }
            }
            $query->orderBy('V.fechaAtencion', 'desc');
        })
        ->toArray();

    // 6) Adjunta KPIs al JSON
    $payload['kpis'] = [
        'total'     => (int) ($kpis->total ?? 0),
        'pacientes' => (int) ($kpis->pacientes ?? 0),
        'ips'       => (int) ($kpis->ips ?? 0),
        'cups'      => (int) ($kpis->cups ?? 0),
    ];

    return response()->json($payload);
}




public function pibucal()
{
    $etapa = [
        'slug'        => 'bucal',
        'titulo'      => 'Atención en salud bucal',
        'descripcion' => 'Flúor, control de placa, sellantes y más.',
    ];

    // Rango por defecto: año en curso [inicio, mañana)
    $desde = now()->startOfYear()->toDateString();
    $hasta = now()->addDay()->toDateString(); // exclusivo

    return view('ciclo_vidas.bucal', compact('etapa','desde','hasta'));
}

public function bucalData(Request $request)
{
    return $this->cacheRepo()->eventTable(
        'primera_infancia',
        ['odontologia_general', 'fluor', 'placa', 'sellantes'],
        $request
    );

    // 1) Fechas (hasta exclusivo)
    try {
        $desde = Carbon::parse($request->query('desde'))->toDateString();
        $hasta = Carbon::parse($request->query('hasta'))->toDateString();
    } catch (\Throwable $e) {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();
    }

    // 2) Base con filtro de periodo (no seleccionamos aún)
    $periodBase = DB::connection('sqlsrv_1')
        ->table('PRUEBA_DESNUTRICION.dbo.vw_bucal_atenciones as V')
        ->whereBetween('V.fechaAtencion', [$desde, $hasta]);

    // 3) DataSource para DataTables
    $dataSource = (clone $periodBase)->select([
        DB::raw('CAST(V.fechaAtencion AS date) as fechaConsulta'), // alias para el front
        'V.tipoIdentificacion',
        'V.identificacion',
        'V.primerNombre',
        'V.segundoNombre',
        'V.primerApellido',
        'V.segundoApellido',
        'V.codigoConsulta',
        'V.descrip',
        'V.diagnosticoPrincipal',
        'V.finalidadConsulta',
        'V.ips_Prim',
        'V.edad',
    ]);

    // 4) KPIs (en subconsulta para evitar GROUP BY de columnas no agregadas)
    $kpis = DB::connection('sqlsrv_1')->query()
        ->fromSub((clone $periodBase), 'Q')
        ->selectRaw("
            COUNT(1)                                                   AS total,
            COUNT(DISTINCT CONCAT(Q.tipoIdentificacion,'-',Q.identificacion)) AS pacientes,
            COUNT(DISTINCT Q.ips_Prim)                                 AS ips,
            COUNT(DISTINCT Q.codigoConsulta)                           AS cups
        ")
        ->first();

    // 5) DataTables con búsqueda global y orden seguro
    $payload = DataTables::of($dataSource)
        ->filter(function ($query) use ($request) {
            $search = $request->input('search.value');
            if (!empty($search)) {
                $search = str_replace(['%', '_'], ['\%','\_'], $search);
                $query->where(function ($q) use ($search) {
                    $q->where('V.tipoIdentificacion', 'like', "%{$search}%")
                      ->orWhere('V.identificacion', 'like', "%{$search}%")
                      ->orWhere('V.primerNombre', 'like', "%{$search}%")
                      ->orWhere('V.segundoNombre', 'like', "%{$search}%")
                      ->orWhere('V.primerApellido', 'like', "%{$search}%")
                      ->orWhere('V.segundoApellido', 'like', "%{$search}%")
                      ->orWhere('V.codigoConsulta', 'like', "%{$search}%")
                      ->orWhere('V.descrip', 'like', "%{$search}%")
                      ->orWhere('V.diagnosticoPrincipal', 'like', "%{$search}%")
                      ->orWhere('V.finalidadConsulta', 'like', "%{$search}%")
                      ->orWhere('V.ips_Prim', 'like', "%{$search}%");
                });
            }
        })
        ->order(function ($query) use ($request) {
            $order = $request->input('order.0');
            $columns = [
                0  => 'V.fechaAtencion',
                1  => 'V.tipoIdentificacion',
                2  => 'V.identificacion',
                3  => 'V.primerNombre',
                4  => 'V.segundoNombre',
                5  => 'V.primerApellido',
                6  => 'V.segundoApellido',
                7  => 'V.codigoConsulta',
                8  => 'V.descrip',
                9  => 'V.diagnosticoPrincipal',
                10 => 'V.finalidadConsulta',
                11 => 'V.ips_Prim',
                12 => 'V.edad',
            ];
            if (is_array($order) && isset($order['column'], $order['dir'])) {
                $idx = (int)$order['column'];
                $dir = strtolower($order['dir']) === 'asc' ? 'asc' : 'desc';
                if (isset($columns[$idx])) {
                    $query->orderBy($columns[$idx], $dir);
                    return;
                }
            }
            $query->orderBy('V.fechaAtencion', 'desc');
        })
        ->toArray();

    // 6) Adjunta KPIs
    $payload['kpis'] = [
        'total'     => (int) ($kpis->total ?? 0),
        'pacientes' => (int) ($kpis->pacientes ?? 0),
        'ips'       => (int) ($kpis->ips ?? 0),
        'cups'      => (int) ($kpis->cups ?? 0),
    ];

    return response()->json($payload);
}



public function pifluor()
{
    // Fechas por defecto (año actual)
    $desde = now()->startOfYear()->toDateString();
    $hasta = now()->addDay()->toDateString(); // exclusivo

    $etapa = [
        'titulo'      => 'Salud bucal · Flúor (1er semestre)',
        'descripcion' => 'Aplicación de flúor (CUPS 997106).',
        'slug'        => 'pi-bucal-fluor-sem1',
    ];

    return view('ciclo_vidas.fluor', compact('desde','hasta','etapa'));
}

public function pifluorData(Request $request)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['fluor'], $request);

    try {
        $desde = Carbon::parse($request->query('desde'))->toDateString();
        $hasta = Carbon::parse($request->query('hasta'))->toDateString(); // exclusivo
    } catch (\Throwable $e) {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();
    }

    // Builder base sobre la vista
    $base = DB::connection('sqlsrv_1')
        ->table('PRUEBA_DESNUTRICION.dbo.vw_bucal_fluor_sem1 AS V')
        ->whereBetween('V.fechaAtencion', [$desde, $hasta]);

    // KPIs (consulta separada para no mezclar agregados y columnas)
    $kpis = (clone $base)->selectRaw("
            COUNT(1) AS total,
            COUNT(DISTINCT CONCAT(V.tipoIdentificacion,'-',V.identificacion)) AS pacientes,
            COUNT(DISTINCT V.ips_Prim) AS ips,
            COUNT(DISTINCT V.codigoCups) AS cups
        ")->first();

    // Select para DataTables (sin agregados)
    $data = (clone $base)->select([
        DB::raw("CONVERT(varchar(10), V.fechaAtencion, 23) AS fechaAtencion"),
        'V.tipoIdentificacion',
        'V.identificacion',
        'V.primerNombre',
        'V.segundoNombre',
        'V.primerApellido',
        'V.segundoApellido',
        'V.codigoCups',
        'V.descrip',
        'V.diagnosticoPrincipal',
        'V.finalidad',
        'V.ips_Prim',
        'V.edad',
    ]);

    // Respuesta DataTables (server-side + búsqueda global)
    $dt = DataTables::of($data)
        ->orderColumn('fechaAtencion', 'fechaAtencion $1') // permite ordenar por fecha
        ->toArray();

    $dt['kpis'] = [
        'total'     => (int) ($kpis->total ?? 0),
        'pacientes' => (int) ($kpis->pacientes ?? 0),
        'ips'       => (int) ($kpis->ips ?? 0),
        'cups'      => (int) ($kpis->cups ?? 0),
    ];

    return response()->json($dt);
}



public function piplaca()
{
    // Fechas por defecto
    $desde = now()->startOfYear()->toDateString();
    $hasta = now()->addDay()->toDateString(); // exclusivo

    $etapa = [
        'titulo'      => 'Salud bucal · Placa (1er semestre)',
        'descripcion' => 'Controles/atenciones por placa (CUPS 997002).',
        'slug'        => 'pi-bucal-placa-sem1',
    ];

    return view('ciclo_vidas.placa', compact('desde','hasta','etapa'));
}

public function piplacaData(Request $request)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['placa'], $request);

    // Rango de fechas (hasta exclusivo)
    try {
        $desde = \Carbon\Carbon::parse($request->query('desde'))->toDateString();
        $hasta = \Carbon\Carbon::parse($request->query('hasta'))->toDateString();
    } catch (\Throwable $e) {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();
    }

    // Base: tu vista con alias V
    $base = DB::connection('sqlsrv_1')
        ->table('PRUEBA_DESNUTRICION.dbo.vw_bucal_placa_sem1 as V')
        ->whereBetween('V.fechaAtencion', [$desde, $hasta]);

    // KPIs (consulta separada, sin GROUP BY)
    $kpis = (clone $base)->selectRaw("
            COUNT(1) AS total,
            COUNT(DISTINCT CONCAT(V.tipoIdentificacion,'-',V.identificacion)) AS pacientes,
            COUNT(DISTINCT V.ips_Prim) AS ips,
            COUNT(DISTINCT V.codigoCups) AS cups
        ")->first();

    // Select para la tabla (alias planos, sin 'V.' en los nombres)
    $data = (clone $base)->select([
        DB::raw("CONVERT(varchar(10), V.fechaAtencion, 23) AS fechaAtencion"),
        'V.tipoIdentificacion',
        'V.identificacion',
        'V.primerNombre',
        'V.segundoNombre',
        'V.primerApellido',
        'V.segundoApellido',
        'V.codigoCups',
        'V.descrip',
        'V.diagnosticoPrincipal',
        'V.finalidad',
        'V.ips_Prim',
        'V.edad',
    ]);

    // Mapa de columnas -> expresiones para buscar (todas en texto minúsculas)
    $searchables = [
        "LOWER(CONVERT(varchar(10), V.fechaAtencion, 23))", // fecha YYYY-MM-DD
        "LOWER(V.tipoIdentificacion)",
        "LOWER(V.identificacion)",
        "LOWER(V.primerNombre)",
        "LOWER(V.segundoNombre)",
        "LOWER(V.primerApellido)",
        "LOWER(V.segundoApellido)",
        "LOWER(V.codigoCups)",
        "LOWER(V.descrip)",
        "LOWER(V.diagnosticoPrincipal)",
        "LOWER(V.finalidad)",
        "LOWER(V.ips_Prim)",
        "LOWER(CAST(V.edad AS varchar(10)))",
    ];

    // DataTables con filtro global personalizado
    $dt = DataTables::of($data)
        // Orden seguro por fecha
        ->orderColumn('fechaAtencion', 'V.fechaAtencion $1')
        // ⬇️ Sobrescribimos el filtro global para evitar el SQL inválido
        ->filter(function ($query) use ($request, $searchables) {
            $search = $request->input('search.value');
            if ($search !== null && $search !== '') {
                $needle = mb_strtolower($search, 'UTF-8');
                $query->where(function ($q) use ($searchables, $needle) {
                    foreach ($searchables as $expr) {
                        $q->orWhereRaw("$expr LIKE ?", ["%{$needle}%"]);
                    }
                });
            }
        }, /*globalSearchOnly*/ true) // <- reemplaza el global search por completo
        // Adjuntamos KPIs al JSON de respuesta
        ->with([
            'kpis' => [
                'total'     => (int)($kpis->total ?? 0),
                'pacientes' => (int)($kpis->pacientes ?? 0),
                'ips'       => (int)($kpis->ips ?? 0),
                'cups'      => (int)($kpis->cups ?? 0),
            ],
        ]);

    return $dt->toJson();
}



 
public function pisellante()
    {
        // Fechas por defecto:
        $desde = now()->startOfYear()->toDateString();  // p.ej. 2025-01-01
        $hasta = now()->addDay()->toDateString();       // exclusivo (mañana)

        return view('ciclo_vidas.sellante', compact('desde', 'hasta'));
    }

  public function pisellanteData(Request $request)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['sellantes'], $request);

    // 1) Rango de fechas (hasta exclusivo)
    try {
        $desde = \Carbon\Carbon::parse($request->query('desde'))->toDateString();
        $hasta = \Carbon\Carbon::parse($request->query('hasta'))->toDateString();
    } catch (\Throwable $e) {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();
    }

    // 2) Fuente
    $base = DB::connection('sqlsrv_1')
        ->table('PRUEBA_DESNUTRICION.dbo.vw_bucal_sellantes as V')
        ->whereBetween('V.fechaAtencion', [$desde, $hasta]);

    // 3) KPIs
    $kpis = (clone $base)->selectRaw("
            COUNT(1) AS total,
            COUNT(DISTINCT CONCAT(V.tipoIdentificacion,'-',V.identificacion)) AS pacientes,
            COUNT(DISTINCT V.ips_Prim) AS ips,
            COUNT(DISTINCT V.codigoCups) AS cups
        ")->first();

    // 4) Select principal
    $data = (clone $base)->select([
        DB::raw("CONVERT(varchar(10), V.fechaAtencion, 23) AS fechaAtencion"),
        'V.tipoIdentificacion',
        'V.identificacion',
        'V.primerNombre',
        'V.segundoNombre',
        'V.primerApellido',
        'V.segundoApellido',
        'V.codigoCups',
        'V.descrip',
        'V.diagnosticoPrincipal',
        'V.finalidad',
        'V.ips_Prim',
        'V.edad',
    ]);

    // 5) DataTables + buscador global (con comillas dobles => OK en PHP)
    return DataTables::of($data)
        ->orderColumn('fechaAtencion', 'V.fechaAtencion $1')
        ->filter(function ($query) use ($request) {
            $search = $request->input('search.value');
            if ($search !== null && $search !== '') {
                $search = mb_strtolower($search, 'UTF-8');
                $query->where(function ($q) use ($search) {
                    // fecha (YYYY-MM-DD)
                    $q->orWhereRaw("LOWER(CONVERT(varchar(10), V.fechaAtencion, 23)) LIKE ?", ["%{$search}%"]);
                    // columnas de texto
                    $q->orWhereRaw("LOWER(ISNULL(V.tipoIdentificacion, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.identificacion, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.primerNombre, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.segundoNombre, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.primerApellido, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.segundoApellido, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.codigoCups, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.descrip, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.diagnosticoPrincipal, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.finalidad, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.ips_Prim, '')) LIKE ?", ["%{$search}%"]);
                    // numérico a texto
                    $q->orWhereRaw("LOWER(ISNULL(CAST(V.edad AS varchar(10)), '')) LIKE ?", ["%{$search}%"]);
                });
            }
        })
        ->with([
            'kpis' => [
                'total'     => (int)($kpis->total ?? 0),
                'pacientes' => (int)($kpis->pacientes ?? 0),
                'ips'       => (int)($kpis->ips ?? 0),
                'cups'      => (int)($kpis->cups ?? 0),
            ],
        ])
        ->make(true);
}


public function piphemoglobina()
{
    return view('ciclo_vidas.hemoglobina', [
        'desde' => now()->startOfYear()->toDateString(),
        'hasta' => now()->addDay()->toDateString(), // exclusivo
        'etapa' => [
            'titulo' => 'Hemoglobina',
            'descripcion' => 'Atenciones por hemoglobina (RIPS AP/nAP)',
        ],
    ]);
}
public function piphemoglobinaData(Request $request)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['hemoglobina'], $request);

    // 1) Fechas (hasta exclusivo)
    try {
        $desde = \Carbon\Carbon::parse($request->query('desde'))->toDateString();
        $hasta = \Carbon\Carbon::parse($request->query('hasta'))->toDateString();
    } catch (\Throwable $e) {
        $desde = now()->startOfYear()->toDateString();
        $hasta = now()->addDay()->toDateString();
    }

    // 2) Base
    $base = DB::connection('sqlsrv_1')
        ->table('PRUEBA_DESNUTRICION.dbo.vw_nutri_hemoglobina as V')
        ->whereBetween('V.fechaAtencion', [$desde, $hasta])
        ->whereBetween('V.edadMeses', [6, 23]);

    // 3) KPIs
    $kpis = (clone $base)->selectRaw("
            COUNT(1) AS total,
            COUNT(DISTINCT CONCAT(V.tipoIdentificacion,'-',V.identificacion)) AS pacientes,
            COUNT(DISTINCT V.ips_Prim) AS ips,
            COUNT(DISTINCT V.codigoCups) AS cups
        ")->first();

    // 4) Select principal
    $data = (clone $base)->select([
        DB::raw("CONVERT(varchar(10), V.fechaAtencion, 23) AS fechaAtencion"),
        'V.tipoIdentificacion',
        'V.identificacion',
        'V.primerNombre',
        'V.segundoNombre',
        'V.primerApellido',
        'V.segundoApellido',
        'V.codigoCups',
        'V.descrip',
        'V.diagnosticoPrincipal',
        'V.finalidad',
        'V.ips_Prim',
        'V.edad',
        'V.edadMeses',
        'V.rangoEdad',
    ]);

    // 5) DataTables + buscador global seguro para SQL Server
    return DataTables::of($data)
        ->orderColumn('fechaAtencion', 'V.fechaAtencion $1')
        ->filter(function ($query) use ($request) {
            $search = $request->input('search.value');
            if ($search !== null && $search !== '') {
                $search = mb_strtolower($search, 'UTF-8');
                $query->where(function ($q) use ($search) {
                    // Fecha
                    $q->orWhereRaw("LOWER(CONVERT(varchar(10), V.fechaAtencion, 23)) LIKE ?", ["%{$search}%"]);
                    // Texto
                    $q->orWhereRaw("LOWER(ISNULL(V.tipoIdentificacion, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.identificacion, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.primerNombre, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.segundoNombre, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.primerApellido, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.segundoApellido, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.codigoCups, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.descrip, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.diagnosticoPrincipal, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.finalidad, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.ips_Prim, '')) LIKE ?", ["%{$search}%"]);
                    $q->orWhereRaw("LOWER(ISNULL(V.rangoEdad, '')) LIKE ?", ["%{$search}%"]);
                    // Numérico -> texto
                    $q->orWhereRaw("LOWER(ISNULL(CAST(V.edad AS varchar(10)), '')) LIKE ?", ["%{$search}%"]);
                });
            }
        })
        ->with([
            'kpis' => [
                'total'     => (int)($kpis->total ?? 0),
                'pacientes' => (int)($kpis->pacientes ?? 0),
                'ips'       => (int)($kpis->ips ?? 0),
                'cups'      => (int)($kpis->cups ?? 0),
            ],
        ])
        ->make(true);
}



  public function pialerta()
    {
        return view('ciclo_vidas.alerta');
    }

    /**
     * Devuelve JSON para DataTables (client-side)
     * - Consulta la TVF con tus parámetros
     * - Agrega "apellidos" y "nombres"
     * - Normaliza "fechaNacimiento" a YYYY-MM-DD
     * - Mantiene "descrip" tal cual (la vista pinta los badges desde aquí)
     */
   public function pialertaData(Request $r)
{
    return $this->cacheRepo()->alertTable('primera_infancia', $r);

    try {
        @set_time_limit(300);

        // === Filtros ===
        $desde  = $r->input('desde', now()->subDays(30)->format('Y-m-d'));
        $hasta  = $r->input('hasta', now()->format('Y-m-d'));
        $filtra = (int) $r->input('filtraEdad', 1);
        $emin   = (int) $r->input('edadMin', 0);
        $emax   = (int) $r->input('edadMax', 5);

        // === DataTables ===
        $draw   = (int) $r->input('draw', 1);
        $start  = max(0, (int) $r->input('start', 0));
        $length = (int) $r->input('length', 10);
        if ($length <= 0) $length = 10;

        $search = trim((string) data_get($r->input('search'), 'value', ''));

        // Solo tomamos el primer orden
        $orderReq = (array) $r->input('order', []);
        $orderCol = 2; // por defecto apellidos
        $orderDirBit = 0; // 0 ASC, 1 DESC
        if (!empty($orderReq)) {
            $col = (int) data_get($orderReq[0], 'column', 2);
            $dir = strtolower((string) data_get($orderReq[0], 'dir', 'asc'));
            // Mapear índice de tu DataTable a 0..9 del SP
            // 0 tipoIdentificacion, 1 identificacion, 2 apellidos, 3 nombres, 4 fechaNacimiento,
            // 5 edadAnios, 6 edadMeses, 7 ips_Prim, 8 codigoHabilitacion, 9 descrip
            $allowed = [0,1,2,3,4,5,6,7,8,9];
            $orderCol = in_array($col, $allowed, true) ? $col : 2;
            $orderDirBit = ($dir === 'desc') ? 1 : 0;
        }

        // === Llamada única al SP y lectura de 3 resultsets ===
        $pdo = DB::connection('sqlsrv_1')->getPdo();
        $stmt = $pdo->prepare("
            EXEC PRUEBA_DESNUTRICION.dbo.sp_pi_alertas_paged
                 @Desde              = ?,
                 @HastaExclusivo     = ?,
                 @AplicarFiltroEdad  = ?,
                 @EdadMinAnios       = ?,
                 @EdadMaxAnios       = ?,
                 @Search             = ?,
                 @OrderCol           = ?,
                 @OrderDir           = ?,
                 @Offset             = ?,
                 @Fetch              = ?
        ");

        $stmt->execute([
            $desde, $hasta, $filtra, $emin, $emax,
            $search === '' ? null : $search,
            $orderCol, $orderDirBit, $start, $length
        ]);

        // 1) total_count
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $recordsTotal = (int) ($row['total_count'] ?? 0);

        // 2) filtered_count
        $stmt->nextRowset();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $recordsFiltered = (int) ($row['filtered_count'] ?? $recordsTotal);

        // 3) datos página
        $stmt->nextRowset();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Normaliza fecha a YYYY-MM-DD por si llega como datetime
        $data = array_map(function(array $r){
            if (!empty($r['fechaNacimiento'])) {
                try { $r['fechaNacimiento'] = (new \DateTime($r['fechaNacimiento']))->format('Y-m-d'); } catch (\Throwable $e) {}
            }
            // La vista usa 'apellidos' y 'nombres' (ya vienen del SP),
            // pero mantenemos compatibilidad si no llegan:
            if (!isset($r['apellidos'])) {
                $r['apellidos'] = trim(($r['primerApellido'] ?? '').' '.($r['segundoApellido'] ?? ''));
            }
            if (!isset($r['nombres'])) {
                $r['nombres'] = trim(($r['primerNombre'] ?? '').' '.($r['segundoNombre'] ?? ''));
            }
            return $r;
        }, $rows ?: []);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        \Log::error('pialertaData error: '.$e->getMessage(), ['ex'=>$e]);
        return response()->json([
            'draw' => (int) $r->input('draw', 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => true,
            'message' => $e->getMessage(),
        ], 500);
    }
}



// Opcional: ponlo como private function dentro del controlador
private function sanitizeEmails(array $emails): array
{
    $out = [];
    foreach ($emails as $e) {
        if (!is_string($e)) continue;
        $e = trim($e);

        // quitar comillas y espacios raros
        $e = str_replace(["'", '"', '’', '‘', '“', '”', '´', '`', ' '], '', $e);

        // normalizar Ñ/acentos en el *nombre* no aplica en e-mail; si vienen en usuario, los quitamos
        $e = mb_strtolower($e);

        // validación final
        if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
            $out[] = $e;
        } else {
            Log::warning('Email inválido filtrado', ['email' => $e]);
        }
    }
    // únicos
    return array_values(array_unique($out));
}

public function pialertaEmail(Request $r)
{
    @set_time_limit(1000);

    $desde  = $r->input('desde', now()->subDays(30)->format('Y-m-d'));
    $hasta  = $r->input('hasta', now()->format('Y-m-d'));
    $filtra = (int) $r->input('filtraEdad', 1);
    $emin   = (int) $r->input('edadMin', 0);
    $emax   = (int) $r->input('edadMax', 5);

    Log::info('pialertaEmail params', compact('desde','hasta','filtra','emin','emax'));

    try {
        $alertData = $this->cacheRepo()->alertNotificationData('primera_infancia', $r);
        $rows = collect($alertData['rows'] ?? []);
        $cut = $alertData['cut'] ?? null;
        $totalRows = $rows->count();
        Log::info('pialertaEmail rows count', [
            'count' => $totalRows,
            'cut' => $cut,
            'exact' => $alertData['exact'] ?? false,
        ]);

        if ($totalRows === 0) {
            return response()->json([
                'ok' => true,
                'msg' => 'No hay un corte materializado de alertas disponible para enviar o el corte no tiene pendientes.',
                'rows' => 0,
                'recipients' => 0,
            ]);
        }

        // destinatarios a partir de codigohabilitacion
        $codigos = $rows->pluck('codigoHabilitacion')->filter()->unique()->values();
        $destinatariosRaw = DB::table('users')
            ->whereIn('codigohabilitacion', $codigos)
            ->pluck('email')->all();

        $destinatarios = $this->sanitizeEmails($destinatariosRaw);
        Log::info('pialertaEmail recipients', ['codigos' => $codigos->count(), 'destinatarios' => count($destinatarios)]);

        if (empty($destinatarios)) {
            return response()->json(['ok' => true, 'msg' => 'No se encontraron correos válidos para los códigos de habilitación.', 'rows' => $totalRows, 'recipients' => 0]);
        }

        // HTML del correo (vista si existe; si no, fallback)
        if (view()->exists('emails.alerta_pi')) {
            $html = view('emails.alerta_pi', [
                'desde' => $cut['from'] ?? $desde,
                'hasta' => $cut['to'] ?? $hasta,
                'items' => $rows,
            ])->render();
        } else {
            $html = '<html><body>'
                  . '<p><strong>Rango materializado:</strong> '.e($cut['from'] ?? $desde).' al '.e($cut['to'] ?? $hasta).'</p>'
                  . '<p>Pacientes con actividades pendientes:</p>'
                  . '<table border="1" cellpadding="6" cellspacing="0">'
                  . '<thead><tr>'
                  . '<th>Identificación</th><th>Nombre</th><th>Edad (años)</th>'
                  . '<th>IPS Primaria</th><th>Actividad</th>'
                  . '</tr></thead><tbody>';
            // Ojo: si son muchos, este HTML será grande. Si prefieres, limita aquí.
            $limite = 2000; // ajusta si quieres acortar el cuerpo
            $c = 0;
            foreach ($rows as $i) {
                if ($c++ >= $limite) break;
                $html .= '<tr>'
                      . '<td>'.e($i->tipoIdentificacion.' '.$i->identificacion).'</td>'
                      . '<td>'.e(trim("{$i->primerApellido} {$i->segundoApellido} {$i->primerNombre} {$i->segundoNombre}")).'</td>'
                      . '<td>'.e($i->edadAnios).'</td>'
                      . '<td>'.e($i->ips_Prim).'</td>'
                      . '<td>'.e($i->descrip).'</td>'
                      . '</tr>';
            }
            $html .= '</tbody></table>';
            if ($totalRows > $limite) {
                $html .= '<p><em>Mostrando '.$limite.' de '.$totalRows.' registros.</em></p>';
            }
            $html .= '</body></html>';
        }

        $fromAddress = config('mail.from.address', 'no-reply@example.com');
        $fromName    = config('mail.from.name', 'Notificador PI');
        $subject     = 'Alerta PI - Pacientes sin atenciones ('.$totalRows.')';

        // Enviar en lotes por BCC (y un TO "neutro" para cumplir con algunos servidores)
        $loteSize = 40;
        $enviados = 0;
        foreach (array_chunk($destinatarios, $loteSize) as $lote) {
            try {
                Mail::send([], [], function ($m) use ($lote, $subject, $html, $fromAddress, $fromName) {
                    $m->from($fromAddress, $fromName)
                      ->to($fromAddress)            // TO técnico (algunos SMTP lo requieren)
                      ->bcc($lote)                  // Destinatarios reales ocultos
                      ->subject($subject)
                      ->html($html);                // <— ESTA ES LA CLAVE (no setBody)
                });
                $enviados += count($lote);
            } catch (\Throwable $ex) {
                Log::error('pialertaEmail lote fallo', ['error' => $ex->getMessage(), 'lote' => $lote]);
                // Fallback: uno por uno del lote que falló
                foreach ($lote as $mail) {
                    try {
                        Mail::send([], [], function ($m) use ($mail, $subject, $html, $fromAddress, $fromName) {
                            $m->from($fromAddress, $fromName)
                              ->to($mail)
                              ->subject($subject)
                              ->html($html);        // <— también aquí
                        });
                        $enviados++;
                    } catch (\Throwable $ex2) {
                        Log::error('pialertaEmail individual fallo', ['error' => $ex2->getMessage(), 'email' => $mail]);
                    }
                }
            }
        }

        return response()->json([
            'ok'         => true,
            'msg'        => "Correos enviados: {$enviados}",
            'rows'       => $totalRows,
            'recipients' => $enviados,
            'cut'        => $cut,
        ]);

    } catch (\Throwable $e) {
        Log::error('pialertaEmail EXCEPTION', ['msg' => $e->getMessage(), 'code' => $e->getCode(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['ok' => false, 'msg' => 'Error enviando correos: '.$e->getMessage()], 500);
    }
}


public function PIlactancia(Request $r)
{
    // Títulos y texto de la cabecera
    $etapa = [
        'titulo'      => 'Lactancia Materna — Promoción y Apoyo',
        'descripcion' => 'Detalle de atenciones registradas en maestro R202, filtradas por periodo (AAAAMM) y 0–5 años.'
    ];

    // Rango por defecto: últimos 30 días (hasta exclusivo = hoy)
    $desde = now()->subDays(30)->format('Y-m-d');
    $hasta = now()->format('Y-m-d'); // exclusivo

    return view('ciclo_vidas.lactancia', compact('etapa','desde','hasta'));
}

public function PIlactanciaData(Request $r)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['lactancia'], $r);

    try {
        @set_time_limit(300);

        $desde   = $r->input('desde', now()->subDays(30)->format('Y-m-d'));
        $hastaEx = $r->input('hasta', now()->format('Y-m-d')); // exclusivo

        Log::info('PIlactanciaData params', ['desde'=>$desde, 'hastaExclusivo'=>$hastaEx]);

        $sql = "
            SET NOCOUNT ON;
            SET ARITHABORT ON;
            SET ANSI_WARNINGS ON;
            SET QUOTED_IDENTIFIER ON;

            SELECT *
            FROM PRUEBA_DESNUTRICION.dbo.fn_pi_lactancia(
                CONVERT(date, ?),
                CONVERT(date, ?)
            )
            ORDER BY primerApellido, segundoApellido, primerNombre, segundoNombre, fechaConsulta
            OPTION (RECOMPILE, MAXDOP 1);
        ";

        $rows = collect(DB::connection('sqlsrv_1')->select($sql, [$desde, $hastaEx]));

        // ==== KPIs ====
        $kpiTotal     = $rows->count();
        $kpiPacientes = $rows->map(function($x){ return ($x->tipoIdentificacion ?? '').'|'.($x->identificacion ?? ''); })
                             ->filter()->unique()->count();
        $kpiIps       = $rows->pluck('ips_Prim')->filter()->unique()->count();
        $kpiFechas    = $rows->pluck('fechaConsulta')->filter()->unique()->count();

        $kpis = [
            'total'     => $kpiTotal,
            'pacientes' => $kpiPacientes,
            'ips'       => $kpiIps,
            'fechas'    => $kpiFechas,
        ];

        return DataTables::of($rows)
            ->with(['kpis' => $kpis])
            ->toJson();

    } catch (\Throwable $e) {
        Log::error('PIlactanciaData error: '.$e->getMessage(), ['ex'=>$e]);

        return response()->json([
            'draw' => (int)$r->input('draw'),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function pivitaminaa(Request $r)
{
    $etapa = [
        'titulo'      => 'Vitamina A — Suministro',
        'descripcion' => 'Detalle de registros R202 (sumVitA = 1) para población 0–5 años, por periodo.'
    ];

    // rango por defecto = últimos 30 días (hasta exclusivo = hoy)
    $desde = now()->subDays(30)->format('Y-m-d');
    $hasta = now()->format('Y-m-d'); // exclusivo

    return view('ciclo_vidas.vitaminaa', compact('etapa','desde','hasta'));
}

public function pivitaminaaData(Request $r)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['vitamina_a'], $r);

    try {
        @set_time_limit(300);

        $desde   = $r->input('desde', now()->subDays(30)->format('Y-m-d'));
        $hastaEx = $r->input('hasta', now()->format('Y-m-d')); // exclusivo

        Log::info('pivitaminaaData params', ['desde'=>$desde, 'hastaExclusivo'=>$hastaEx]);

        $sql = "
            SET NOCOUNT ON;
            SET ARITHABORT ON;
            SET ANSI_WARNINGS ON;
            SET QUOTED_IDENTIFIER ON;

            SELECT *
            FROM PRUEBA_DESNUTRICION.dbo.fn_pi_vitamina_a(
                CONVERT(date, ?),
                CONVERT(date, ?)
            )
            ORDER BY fechaConsulta DESC, primerApellido, segundoApellido, primerNombre
            OPTION (RECOMPILE, MAXDOP 1);
        ";

        $rows = collect(DB::connection('sqlsrv_1')->select($sql, [$desde, $hastaEx]));

        // KPIs
        $kpiTotal     = $rows->count();
        $kpiPacientes = $rows->map(fn($x) => ($x->tipoIdentificacion ?? '').'|'.($x->identificacion ?? ''))->filter()->unique()->count();
        $kpiIps       = $rows->pluck('ips_Prim')->filter()->unique()->count();
        $kpiFechas    = $rows->pluck('fechaConsulta')->filter()->unique()->count();

        $kpis = [
            'total'     => $kpiTotal,
            'pacientes' => $kpiPacientes,
            'ips'       => $kpiIps,
            'fechas'    => $kpiFechas,
        ];

        return DataTables::of($rows)
            ->with(['kpis' => $kpis])
            ->toJson();

    } catch (\Throwable $e) {
        Log::error('pivitaminaaData error: '.$e->getMessage(), ['ex'=>$e]);

        return response()->json([
            'draw' => (int)$r->input('draw'),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => $e->getMessage(),
        ], 500);
    }
}



public function PIhierro()
{
    // Por defecto: año actual (desde 1º de enero hasta mañana como exclusivo)
    $desde = now()->startOfYear()->toDateString();           // YYYY-MM-DD
    $hasta = now()->addDay()->toDateString();                // exclusivo (mañana)

    return view('ciclo_vidas.hierro', [
        'etapa' => [
            'titulo'      => 'Suministro de Hierro (0-5 años)',
            'descripcion' => 'Nominal R202 · período por rango de fecha (año actual por defecto)',
        ],
        'desde' => $desde,
        'hasta' => $hasta,
    ]);
}

public function PIhierroData(Request $r)
{
    return $this->cacheRepo()->eventTable('primera_infancia', ['hierro'], $r);

    @set_time_limit(300);

    $draw   = (int) $r->input('draw', 0);
    $start  = (int) $r->input('start', 0);
    $length = (int) $r->input('length', 10);
    if ($length <= 0) { $length = 10; }

    $orderDir = strtolower($r->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
    $orderIdx = (int) $r->input('order.0.column', 0);
    $cols = [
        0=>'fechaConsulta',1=>'tipoIdentificacion',2=>'identificacion',3=>'primerNombre',
        4=>'segundoNombre',5=>'primerApellido',6=>'segundoApellido',7=>'descrip',
        8=>'ips_Prim',9=>'fechaNacimiento',10=>'edad',11=>'rangoEdad',12=>'codigoIps'
    ];
    $orderCol = $cols[$orderIdx] ?? 'fechaConsulta';

    // Fechas entrada → periodo ints
    $desde = $r->input('desde', now()->startOfYear()->format('Y-m-d'));
    $hasta = $r->input('hasta', now()->addDay()->format('Y-m-d')); // exclusivo
    $desde = preg_replace('/[^0-9\-]/', '', $desde);
    $hasta = preg_replace('/[^0-9\-]/', '', $hasta);
    $pIni  = (int) date('Ym', strtotime($desde));
    $pFin  = (int) date('Ym', strtotime($hasta)); // incluye mes de "hasta (exclusivo)"

    // Búsqueda global opcional
    $search = trim((string) $r->input('search.value', ''));
    $whereSearch = '';
    $paramsSearch = [];
    if ($search !== '') {
        $whereSearch = " AND (
               tipoIdentificacion LIKE ?
            OR identificacion     LIKE ?
            OR primerNombre       LIKE ?
            OR primerApellido     LIKE ?
            OR ips_Prim           LIKE ?
        )";
        $like = '%'.$search.'%';
        $paramsSearch = [$like,$like,$like,$like,$like];
    }

    try {
        $cx = DB::connection('sqlsrv_1');

        // Totales
        $recordsTotal = (int) ($cx->selectOne(
            "SELECT total = COUNT_BIG(*) FROM PRUEBA_DESNUTRICION.dbo.v_pi_hierro WITH (NOLOCK)"
        )->total ?? 0);

        $recordsFiltered = (int) ($cx->selectOne(
            "SET NOCOUNT ON;
             SELECT total = COUNT_BIG(*)
             FROM PRUEBA_DESNUTRICION.dbo.v_pi_hierro WITH (NOLOCK)
             WHERE periodoYYYYMM BETWEEN ? AND ? $whereSearch;",
            array_merge([$pIni,$pFin], $paramsSearch)
        )->total ?? 0);

        // KPIs
        $k = $cx->selectOne(
            "SET NOCOUNT ON;
             SELECT 
                  total     = COUNT_BIG(*)
                 ,pacientes = COUNT(DISTINCT (
                                   COALESCE(CAST(tipoIdentificacion AS NVARCHAR(10)),'')
                                   + '|' + COALESCE(CAST(identificacion AS NVARCHAR(50)) ,'')
                                 ))
                 ,ips       = COUNT(DISTINCT CAST(COALESCE(ips_Prim,'') AS NVARCHAR(200)))
                 ,fechas    = COUNT(DISTINCT fechaConsulta)
             FROM PRUEBA_DESNUTRICION.dbo.v_pi_hierro WITH (NOLOCK)
             WHERE periodoYYYYMM BETWEEN ? AND ? $whereSearch;",
            array_merge([$pIni,$pFin], $paramsSearch)
        );
        $kpis = [
            'total'     => (int)($k->total ?? 0),
            'pacientes' => (int)($k->pacientes ?? 0),
            'ips'       => (int)($k->ips ?? 0),
            'fechas'    => (int)($k->fechas ?? 0),
        ];

        // Datos paginados
        $rows = $cx->select(
            "SET NOCOUNT ON;
             SELECT
                 fechaConsulta, tipoIdentificacion, identificacion,
                 primerNombre, segundoNombre, primerApellido, segundoApellido,
                 descrip, ips_Prim, fechaNacimiento, edad, rangoEdad, codigoIps
             FROM PRUEBA_DESNUTRICION.dbo.v_pi_hierro WITH (NOLOCK)
             WHERE periodoYYYYMM BETWEEN ? AND ? $whereSearch
             ORDER BY $orderCol $orderDir
             OFFSET ? ROWS FETCH NEXT ? ROWS ONLY;",
            array_merge([$pIni,$pFin], $paramsSearch, [$start, $length])
        );

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
            'kpis'            => $kpis,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('PIhierroData error: '.$e->getMessage(), ['ex'=>$e]);
        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => 0,
            'recordsFiltered' => 0,
            'data'            => [],
            'kpis'            => ['total'=>0,'pacientes'=>0,'ips'=>0,'fechas'=>0],
            'error'           => true,
            'message'         => $e->getMessage(),
        ], 200);
    }
}

public function piResumenGenerales(Request $r)
{
    return $this->cacheRepo()->summary('primera_infancia', $r);

    try {
        // Rango por defecto: año en curso… mañana (exclusivo)
        $desde = $r->input('desde', now()->startOfYear()->format('Y-m-d'));
        $hasta = $r->input('hasta', now()->addDay()->format('Y-m-d'));

        // Normaliza y deriva periodos INT YYYYMM
        $desde = preg_replace('/[^0-9\-]/', '', $desde);
        $hasta = preg_replace('/[^0-9\-]/', '', $hasta);
        $pIni  = (int) date('Ym', strtotime($desde));
        $pFin  = (int) date('Ym', strtotime($hasta));

        // Módulos -> vistas. Ajusta los nombres si tus vistas difieren.
        // Requisito: cada vista debe tener al menos: periodoYYYYMM (INT), fechaConsulta (DATE),
        // tipoIdentificacion, identificacion, ips_Prim
        $areas = [
            'Hierro'      => 'PRUEBA_DESNUTRICION.dbo.v_pi_hierro',
            'Bucal'       => 'PRUEBA_DESNUTRICION.dbo.v_pitablero_bucal',
            'Enfermería'  => 'PRUEBA_DESNUTRICION.dbo.v_pi_enfermeria',
        ];

        $cx = DB::connection('sqlsrv_1');

        $resumen = [];
        $totales = ['total'=>0,'pacientes'=>0,'ips'=>0,'fechas'=>0];

        foreach ($areas as $area => $vista) {
            try {
                // KPIs por módulo (solo lecturas y COUNTs rápidos)
                $k = $cx->selectOne(
                    "SET NOCOUNT ON;
                     SELECT 
                          total     = COUNT_BIG(*)
                         ,pacientes = COUNT(DISTINCT (
                                           COALESCE(CAST(tipoIdentificacion AS NVARCHAR(10)),'')
                                           + '|' + COALESCE(CAST(identificacion AS NVARCHAR(50)) ,'')
                                         ))
                         ,ips       = COUNT(DISTINCT CAST(COALESCE(ips_Prim,'') AS NVARCHAR(200)))
                         ,fechas    = COUNT(DISTINCT fechaConsulta)
                     FROM {$vista} WITH (NOLOCK)
                     WHERE periodoYYYYMM BETWEEN ? AND ?;",
                    [$pIni, $pFin]
                );

                $item = [
                    'area'      => $area,
                    'total'     => (int)($k->total ?? 0),
                    'pacientes' => (int)($k->pacientes ?? 0),
                    'ips'       => (int)($k->ips ?? 0),
                    'fechas'    => (int)($k->fechas ?? 0),
                ];
            } catch (\Throwable $exArea) {
                // Si la vista no existe o falla, ese área sale con ceros pero no cae todo
                Log::warning("PI resumen: módulo {$area} falló", ['e'=>$exArea->getMessage()]);
                $item = ['area'=>$area,'total'=>0,'pacientes'=>0,'ips'=>0,'fechas'=>0];
            }

            // Acumula y guarda
            $totales['total']     += $item['total'];
            $totales['pacientes'] += $item['pacientes'];
            $totales['ips']       += $item['ips'];
            $totales['fechas']    += $item['fechas'];
            $resumen[] = $item;
        }

        return response()->json([
            'ok'      => true,
            'desde'   => $desde,
            'hasta'   => $hasta,
            'pIni'    => $pIni,
            'pFin'    => $pFin,
            'areas'   => $resumen,
            'totales' => $totales,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('piResumenGenerales error: '.$e->getMessage(), ['ex'=>$e]);
        return response()->json(['ok'=>false,'msg'=>$e->getMessage()], 200);
    }
}

public function pidatogenerales()
{
    return view('ciclo_vidas.datosgenerales');
}

public function showModule(string $slug, string $moduleKey)
{
    $course = CicloVidaCatalog::courseBySlug($slug);
    $module = CicloVidaCatalog::module($course['key'], $moduleKey);

    $etapa = [
        'slug' => $slug,
        'titulo' => $module['label'],
        'descripcion' => trim(($course['label'] ?? '').' · '.($module['description'] ?? $course['description'] ?? '')),
    ];

    $defaultDays = (int) data_get($course, 'refresh.days', 120);
    $desde = now()->subDays(max($defaultDays, 1))->startOfDay()->toDateString();
    $hasta = now()->addDay()->toDateString();

    return view('ciclo_vidas.show', [
        'etapa' => $etapa,
        'desde' => $desde,
        'hasta' => $hasta,
        'dataUrl' => route('ciclosvida.module.data', ['slug' => $slug, 'moduleKey' => $moduleKey]),
        'course' => $course,
        'module' => $module + ['key' => $moduleKey],
        'tableTitle' => 'Detalle de atenciones · '.$module['label'],
        'dataSourceLabel' => 'Datos materializados por curso de vida',
        'pageNotice' => empty($module['materialized'])
            ? 'La estructura de este modulo ya quedo preparada para el curso de vida, pero aun no tiene una fuente materializada cargada.'
            : null,
    ]);
}

public function moduleData(Request $request, string $slug, string $moduleKey)
{
    $course = CicloVidaCatalog::courseBySlug($slug);
    $module = CicloVidaCatalog::module($course['key'], $moduleKey);

    if (($module['record_type'] ?? 'event') === 'alert') {
        return $this->cacheRepo()->alertTable($course['key'], $request);
    }

    if ($moduleKey === 'datos_generales') {
        return $this->cacheRepo()->summary($course['key'], $request);
    }

    return $this->cacheRepo()->eventTable(
        $course['key'],
        [$module['cache_module_key'] ?? $moduleKey],
        $request,
        ['record_type' => $module['record_type'] ?? 'event']
    );
}

public function moduleInfo(string $slug, string $moduleKey)
{
    $course = CicloVidaCatalog::courseBySlug($slug);
    $module = CicloVidaCatalog::module($course['key'], $moduleKey);

    return view('ciclo_vidas.module_info', [
        'course' => $course,
        'module' => $module + ['key' => $moduleKey],
    ]);
}

private function renderCourseMenu(string $courseKey)
{
    $course = CicloVidaCatalog::course($courseKey);
    $groups = CicloVidaCatalog::menuGroups($courseKey);

    return view('ciclo_vidas.pi_menu', [
        'course' => $course,
        'groups' => $groups,
    ]);
}

private function cacheRepo(): CicloVidaCacheRepository
{
    return app(CicloVidaCacheRepository::class);
}

private function catalogCourseCards(): array
{
    return collect(CicloVidaCatalog::courses())
        ->mapWithKeys(function (array $course, string $courseKey) {
            $groupCount = count($course['groups'] ?? []);
            $moduleCount = collect($course['groups'] ?? [])
                ->pluck('items')
                ->flatten()
                ->filter()
                ->unique()
                ->count();

            return [
                $course['slug'] => [
                    'titulo' => $course['label'],
                    'descripcion' => $course['description'],
                    'color' => $course['color'],
                    'icono' => $course['icon'],
                    'route_name' => $course['menu_route'],
                    'course_key' => $courseKey,
                    'age_label' => $course['age_label'] ?? '',
                    'group_count' => $groupCount,
                    'module_count' => $moduleCount,
                ],
            ];
        })
        ->all();
}

private function normativeLinks(): array
{
    return [
        [
            'label' => 'Resolucion 3280 de 2018',
            'url' => 'https://www.minsalud.gov.co/salud/publica/Paginas/normograma-promocion-y-prevencion.aspx',
            'description' => 'Base de la Ruta Integral de Atencion para la Promocion y el Mantenimiento de la Salud.',
        ],
        [
            'label' => 'Enfoque de ciclo de vida',
            'url' => 'https://www.minsalud.gov.co/proteccionsocial/Paginas/ciclovida.aspx',
            'description' => 'Referencia conceptual para vulnerabilidades, oportunidades y priorizacion por etapa.',
        ],
        [
            'label' => 'Tamizajes prioritarios',
            'url' => 'https://www.minsalud.gov.co/salud/publica/ssr/Paginas/Cancer-de-cuello-uterino.aspx',
            'description' => 'Apoya la priorizacion de cuello uterino, mama y otros tamizajes diferenciales.',
        ],
    ];
}

}
