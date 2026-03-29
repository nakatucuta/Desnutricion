@extends('adminlte::page')

@section('title', 'Estadisticas de ciclos de vida')

@section('content_header')
    <div class="cv-hero">
        <div class="cv-hero-copy">
            <span class="cv-chip">Estadisticas integradas de cursos de vida</span>
            <h1 class="mb-2">Tablero estadistico central</h1>
            <p class="mb-0">
                Consolida atenciones, tamizajes, proteccion especifica y seguimiento por curso de vida,
                alineado con el enfoque de curso de vida y la Ruta de Promocion y Mantenimiento.
            </p>
        </div>
        <div class="cv-hero-meta">
            <div class="cv-meta-card">
                <small>Base normativa</small>
                <strong>Resolucion 3280 de 2018</strong>
            </div>
            <div class="cv-meta-card">
                <small>Visualizacion</small>
                <strong>Atenciones materializadas por curso</strong>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div id="cvStatsLoading" class="cv-stats-loading" aria-live="polite" aria-busy="true">
        <div class="cv-stats-loading__backdrop"></div>
        <div class="cv-stats-loading__panel">
            <div class="cv-stats-loading__grid"></div>
            <div class="cv-stats-loading__orb">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="cv-stats-loading__brand">
                <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo institucional">
            </div>
            <h3>Construyendo tablero estadistico</h3>
            <p>Estamos integrando indicadores, graficas territoriales y comparativos por curso de vida.</p>
            <div class="cv-stats-loading__status">
                <span class="cv-stats-loading__dot"></span>
                <span id="cvStatsLoadingText">Consultando estadisticas materializadas...</span>
            </div>
        </div>
    </div>

    <div class="cv-toolbar card shadow-sm mb-4">
        <div class="card-body">
            <div class="cv-filter-grid">
                <div>
                    @include('ciclo_vidas.partials.date_range_toolbar', [
                        'pickerId' => 'daterange',
                        'wrapperClass' => 'cv-date-toolbar--stats',
                    ])
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Curso de vida</label>
                    <select id="filterCurso" class="form-control">
                        <option value="">Todos los cursos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Linea estrategica</label>
                    <select id="filterLinea" class="form-control">
                        <option value="">Todas las lineas</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Modulo</label>
                    <select id="filterModulo" class="form-control">
                        <option value="">Todos los modulos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Departamento</label>
                    <select id="filterDepartamento" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Municipio</label>
                    <select id="filterMunicipio" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">IPS / Grupo</label>
                    <select id="filterIps" class="form-control">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Rango etario</label>
                    <select id="filterRangoEdad" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>
            <div class="cv-toolbar-actions mt-3">
                <div class="d-flex flex-wrap">
                    <button id="btnDashboard" class="btn btn-primary px-4 mr-2 mb-2">
                        <i class="fas fa-chart-pie"></i> Aplicar filtros
                    </button>
                    <button id="btnClearFilters" class="btn btn-outline-secondary px-4 mb-2">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
                <div class="cv-note mb-2">
                    <i class="fas fa-info-circle"></i>
                    Entra al tablero al instante, elige el rango que necesitas y luego aplica filtros para consultar solo el corte que vas a analizar.
                </div>
            </div>
            <div id="activeFiltersSummary" class="cv-filter-summary mt-2">Selecciona un rango y presiona Aplicar filtros para construir el tablero.</div>
        </div>
    </div>

    <div class="row mb-4">
        @foreach ($focusAreas as $focus)
            <div class="col-12 col-lg-4 mb-3">
                <div class="cv-focus-card h-100">
                    <div class="cv-focus-icon"><i class="{{ $focus['icon'] }}"></i></div>
                    <div>
                        <h5 class="mb-1">{{ $focus['title'] }}</h5>
                        <p class="mb-0">{{ $focus['description'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row" id="globalKpis">
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-blue">
                <small>Total de atenciones</small>
                <h3 id="kpiTotal">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-green">
                <small>Pacientes unicos</small>
                <h3 id="kpiPacientes">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-cyan">
                <small>Cursos con actividad</small>
                <h3 id="kpiCursos">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-amber">
                <small>Modulos activos</small>
                <h3 id="kpiModulos">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-slate">
                <small>IPS / Grupo</small>
                <h3 id="kpiIps">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-indigo">
                <small>Municipios</small>
                <h3 id="kpiMunicipios">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-emerald">
                <small>Departamentos</small>
                <h3 id="kpiDepartamentos">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="cv-kpi cv-kpi-rose">
                <small>Servicios/CUPS</small>
                <h3 id="kpiServicios">0</h3>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="highlightCards">
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-highlight-card">
                <small>Curso lider</small>
                <h5 id="highlightCourse">Sin datos</h5>
                <span id="highlightCourseSupport">0 atenciones</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-highlight-card">
                <small>Municipio principal</small>
                <h5 id="highlightMunicipality">Sin datos</h5>
                <span id="highlightMunicipalitySupport">0 registros</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-highlight-card">
                <small>IPS principal</small>
                <h5 id="highlightIps">Sin datos</h5>
                <span id="highlightIpsSupport">0 registros</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-highlight-card">
                <small>Servicio mas frecuente</small>
                <h5 id="highlightService">Sin datos</h5>
                <span id="highlightServiceSupport">0 registros</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header border-0 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="card-title mb-1">Resumen comparativo por curso de vida</h3>
                    <small class="text-muted">Lectura consolidada para comparar el comportamiento de cada curso y entrar luego a su detalle operativo.</small>
                </div>
                <span class="badge badge-light px-3 py-2 mt-2 mt-md-0" id="dashboardRangeLabel">Selecciona un rango y aplica filtros</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row" id="courseCards">
                @foreach ($etapas as $slug => $etapa)
                    <div class="col-12 col-md-6 col-xl-4 mb-3">
                        <a href="{{ route($etapa['route_name']) }}" class="cv-course-link">
                            <div class="cv-course-card" data-course="{{ $etapa['course_key'] }}">
                                <div class="d-flex align-items-start">
                                    <div class="cv-course-icon {{ $etapa['color'] }}">
                                        <i class="{{ $etapa['icono'] }}"></i>
                                    </div>
                                    <div class="ml-3 flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h4 class="mb-1">{{ $etapa['titulo'] }}</h4>
                                            <i class="fas fa-arrow-right text-muted"></i>
                                        </div>
                                        <p class="mb-2 text-muted">{{ $etapa['descripcion'] }}</p>
                                        <div class="cv-course-stats">
                                            <span><strong class="js-total">0</strong> atenciones</span>
                                            <span><strong class="js-pacientes">0</strong> pacientes</span>
                                            <span><strong class="js-modulos">0</strong> modulos</span>
                                        </div>
                                        <small class="text-muted d-block mt-2">Ultimo refresh: <span class="js-updated">Sin carga</span></small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Tendencia de atenciones por curso</h3>
                    <small class="text-muted">Compara la evolucion mensual del volumen de atenciones entre los seis cursos de vida.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Lineas utiles para gestion</h3>
                    <small class="text-muted">Distribucion agrupada por grandes bloques operativos de la ruta.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap doughnut-wrap"><canvas id="categoryChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Comparativo por curso y linea estrategica</h3>
                    <small class="text-muted">Cruza los cursos de vida con bloques utiles para gestion: atencion integral, proteccion especifica, tamizajes, riesgo cardiovascular y SSR.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="courseCategoryChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Modulos con mayor actividad</h3>
                    <small class="text-muted">Ayuda a identificar rapidamente donde se concentra la demanda inducida y el volumen operativo.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="moduleChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">IPS / grupos con mas atenciones</h3>
                    <small class="text-muted">Ranking para seguimiento operativo, concentracion de atenciones y priorizacion institucional.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="ipsChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Municipios con mayor actividad</h3>
                    <small class="text-muted">Apoya focalizacion territorial, seguimiento municipal y priorizacion de acciones con prestadores.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="municipalityChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Distribucion por rango etario materializado</h3>
                    <small class="text-muted">Resume como se concentra el volumen sobre los distintos grupos etarios capturados en el cache.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="ageRangeChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Servicios y procedimientos mas frecuentes</h3>
                    <small class="text-muted">Permite reconocer rapidamente CUPS o actividades que dominan el comportamiento del periodo.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="serviceChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title mb-1">Lectura por departamento</h3>
                    <small class="text-muted">Corte util para concentracion territorial, control de dispersion geografica y seguimiento del aseguramiento.</small>
                </div>
                <div class="card-body">
                    <div class="chart-wrap"><canvas id="departmentChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title mb-1">Referentes normativos oficiales</h3>
            <small class="text-muted">Indicadores y visualizaciones priorizados para apoyar la gestion sobre la Ruta de Promocion y Mantenimiento en curso de vida.</small>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($normativeLinks as $link)
                    <div class="col-12 col-lg-4 mb-3">
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="cv-norm-link">
                            <div class="cv-norm-card h-100">
                                <h5 class="mb-2">{{ $link['label'] }}</h5>
                                <p class="mb-2">{{ $link['description'] }}</p>
                                <span>Ir a fuente oficial <i class="fas fa-external-link-alt ml-1"></i></span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <style>
        .content-wrapper, .content, .container-fluid { background: #f4f7fb !important; }
        .cv-hero {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 1rem;
            padding: 1.5rem 1.75rem;
            border-radius: 26px;
            background:
                radial-gradient(circle at top left, rgba(56,189,248,.25), transparent 32%),
                radial-gradient(circle at bottom right, rgba(16,185,129,.18), transparent 28%),
                linear-gradient(135deg, #0f172a, #1d4ed8 55%, #0f766e);
            color: #fff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .22);
        }
        .cv-hero h1 { font-size: 2.2rem; font-weight: 800; }
        .cv-hero p { max-width: 780px; color: rgba(255,255,255,.82); }
        .cv-chip {
            display: inline-flex;
            align-items: center;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            font-size: .82rem;
            letter-spacing: .03em;
            margin-bottom: .9rem;
        }
        .cv-hero-meta {
            display: grid;
            grid-template-columns: 1fr;
            gap: .85rem;
            align-content: center;
        }
        .cv-meta-card {
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.12);
        }
        .cv-meta-card small {
            display: block;
            color: rgba(255,255,255,.72);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .2rem;
        }
        .cv-stats-loading {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .cv-stats-loading.is-visible {
            display: flex;
        }
        .cv-stats-loading__backdrop {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(56,189,248,.28), transparent 30%),
                radial-gradient(circle at bottom right, rgba(16,185,129,.24), transparent 32%),
                rgba(15, 23, 42, .74);
            backdrop-filter: blur(12px);
        }
        .cv-stats-loading__panel {
            position: relative;
            width: min(540px, 100%);
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,.14);
            background: linear-gradient(145deg, rgba(15, 23, 42, .96), rgba(30, 41, 59, .92));
            box-shadow: 0 30px 80px rgba(15, 23, 42, .42);
            padding: 2rem 1.8rem 1.7rem;
            text-align: center;
            color: #fff;
        }
        .cv-stats-loading__panel::before {
            content: '';
            position: absolute;
            inset: -30% auto auto -10%;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,.38), transparent 70%);
            pointer-events: none;
        }
        .cv-stats-loading__panel::after {
            content: '';
            position: absolute;
            right: -70px;
            bottom: -70px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16,185,129,.28), transparent 72%);
            pointer-events: none;
        }
        .cv-stats-loading__grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(148, 163, 184, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: linear-gradient(180deg, transparent, rgba(255,255,255,.75), transparent);
            pointer-events: none;
        }
        .cv-stats-loading__orb {
            position: relative;
            width: 104px;
            height: 104px;
            margin: 0 auto 1rem;
        }
        .cv-stats-loading__orb span {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: rgba(96, 165, 250, .95);
            border-right-color: rgba(45, 212, 191, .75);
            animation: cvStatsSpin 1.45s linear infinite;
        }
        .cv-stats-loading__orb span:nth-child(2) {
            inset: 12px;
            border-top-color: rgba(34, 211, 238, .9);
            border-right-color: rgba(110, 231, 183, .7);
            animation-duration: 1.05s;
            animation-direction: reverse;
        }
        .cv-stats-loading__orb span:nth-child(3) {
            inset: 26px;
            border-top-color: rgba(250, 204, 21, .95);
            border-right-color: rgba(96, 165, 250, .72);
            animation-duration: .9s;
        }
        .cv-stats-loading__brand {
            position: relative;
            z-index: 1;
            margin-bottom: .8rem;
        }
        .cv-stats-loading__brand img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(0,0,0,.35));
        }
        .cv-stats-loading__panel h3 {
            position: relative;
            z-index: 1;
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .45rem;
        }
        .cv-stats-loading__panel p {
            position: relative;
            z-index: 1;
            margin: 0 auto 1.1rem;
            max-width: 360px;
            color: rgba(226, 232, 240, .86);
        }
        .cv-stats-loading__status {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            padding: .7rem 1rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, .45);
            border: 1px solid rgba(148, 163, 184, .18);
            color: #e2e8f0;
            font-weight: 600;
        }
        .cv-stats-loading__dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22d3ee;
            box-shadow: 0 0 0 0 rgba(34, 211, 238, .7);
            animation: cvStatsPulse 1.5s ease-out infinite;
        }
        body.cv-stats-loading-lock {
            overflow: hidden;
        }
        @keyframes cvStatsSpin {
            to { transform: rotate(360deg); }
        }
        @keyframes cvStatsPulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 211, 238, .7); }
            70% { box-shadow: 0 0 0 14px rgba(34, 211, 238, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 211, 238, 0); }
        }
        .cv-toolbar, .cv-course-card, .cv-focus-card, .cv-kpi, .cv-cycle-card {
            border-radius: 20px;
        }
        .cv-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .95rem 1rem;
        }
        .cv-date-toolbar--stats .cv-date-toolbar__main {
            min-width: 100%;
            flex-basis: 100%;
        }
        .cv-date-toolbar--stats .cv-date-toolbar__action,
        .cv-date-toolbar--stats .cv-date-toolbar__note {
            display: none;
        }
        .cv-toolbar-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cv-note {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .7rem 1rem;
            border-radius: 999px;
            background: #eef6ff;
            color: #0f3f74;
            font-size: .92rem;
        }
        .cv-filter-summary {
            color: #475569;
            font-size: .92rem;
        }
        .cv-focus-card {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            padding: 1.15rem 1.2rem;
            background: linear-gradient(135deg, #ffffff, #f8fbff);
            border: 1px solid #e6edf7;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .05);
        }
        .cv-focus-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .cv-focus-card p { color: #64748b; }
        .cv-kpi {
            padding: 1.1rem 1.2rem;
            min-height: 130px;
            color: #fff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
        }
        .cv-kpi small {
            display: block;
            color: rgba(255,255,255,.78);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .7rem;
        }
        .cv-kpi h3 { font-size: 2rem; font-weight: 800; margin: 0; }
        .cv-kpi-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .cv-kpi-green { background: linear-gradient(135deg, #059669, #16a34a); }
        .cv-kpi-cyan { background: linear-gradient(135deg, #0891b2, #06b6d4); }
        .cv-kpi-amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .cv-kpi-slate { background: linear-gradient(135deg, #334155, #0f172a); }
        .cv-kpi-indigo { background: linear-gradient(135deg, #4338ca, #6366f1); }
        .cv-kpi-emerald { background: linear-gradient(135deg, #0f766e, #14b8a6); }
        .cv-kpi-rose { background: linear-gradient(135deg, #be123c, #f43f5e); }
        .cv-highlight-card {
            border-radius: 18px;
            border: 1px solid #e6edf7;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
            padding: 1.05rem 1.15rem;
            height: 100%;
        }
        .cv-highlight-card small {
            display: block;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .45rem;
        }
        .cv-highlight-card h5 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: .35rem;
        }
        .cv-highlight-card span {
            color: #475569;
            font-size: .9rem;
        }
        .cv-course-link { text-decoration: none !important; }
        .cv-course-card {
            padding: 1.1rem 1.2rem;
            border: 1px solid #e7edf6;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
            transition: transform .16s ease, box-shadow .16s ease;
        }
        .cv-course-card:hover, .cv-cycle-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .1);
        }
        .cv-course-icon, .cv-cycle-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.35rem;
            flex: 0 0 58px;
        }
        .cv-course-stats {
            display: flex;
            flex-wrap: wrap;
            gap: .85rem 1rem;
            color: #475569;
            font-size: .92rem;
        }
        .cv-course-stats strong { color: #0f172a; }
        .chart-wrap {
            position: relative;
            height: 340px;
        }
        .cv-norm-link { text-decoration: none !important; }
        .cv-norm-card {
            padding: 1.15rem 1.2rem;
            border-radius: 18px;
            border: 1px solid #e7edf6;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
            transition: transform .16s ease, box-shadow .16s ease;
            color: #0f172a;
        }
        .cv-norm-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(15, 23, 42, .08);
        }
        .cv-norm-card p { color: #64748b; }
        .cv-norm-card span { color: #2563eb; font-weight: 600; }
        .doughnut-wrap {
            height: 320px;
        }
        @media (max-width: 991px) {
            .cv-hero {
                grid-template-columns: 1fr;
            }
            .cv-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .chart-wrap {
                height: 300px;
            }
        }
        @media (max-width: 575px) {
            .cv-filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    @include('ciclo_vidas.partials.date_range_shared_script')

    <script>
        $(function () {
            moment.locale('es');

            const dashboardUrl = @json(route('ciclosvida.dashboard.data'));
            const rangePicker = window.CicloVidaDateRange.init({
                pickerSelector: '#daterange',
                start: @json($desde),
                end: @json($hasta),
                endExclusive: false
            });
            const numberFmt = (value) => Number(value || 0).toLocaleString('es-CO');
            const $statsLoading = $('#cvStatsLoading');
            const $statsLoadingText = $('#cvStatsLoadingText');
            const charts = {};
            let hasLoadedDashboard = false;

            function showStatsLoading(message) {
                if (message) {
                    $statsLoadingText.text(message);
                }
                $('body').addClass('cv-stats-loading-lock');
                $statsLoading.addClass('is-visible');
            }

            function hideStatsLoading() {
                $('body').removeClass('cv-stats-loading-lock');
                $statsLoading.removeClass('is-visible');
            }

            function destroyChart(key) {
                if (charts[key]) {
                    charts[key].destroy();
                    charts[key] = null;
                }
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function syncSelectOptions(selector, items, selected, placeholder) {
                const $select = $(selector);
                const options = [`<option value="">${escapeHtml(placeholder)}</option>`];

                (items || []).forEach(function (item) {
                    options.push(`<option value="${escapeHtml(item.value)}">${escapeHtml(item.label)}</option>`);
                });

                $select.html(options.join(''));
                $select.val(selected || '');
            }

            function renderKpis(kpis) {
                $('#kpiTotal').text(numberFmt(kpis.total));
                $('#kpiPacientes').text(numberFmt(kpis.pacientes));
                $('#kpiCursos').text(numberFmt(kpis.cursos));
                $('#kpiModulos').text(numberFmt(kpis.modulos));
                $('#kpiIps').text(numberFmt(kpis.ips));
                $('#kpiMunicipios').text(numberFmt(kpis.municipios));
                $('#kpiDepartamentos').text(numberFmt(kpis.departamentos));
                $('#kpiServicios').text(numberFmt(kpis.servicios));
            }

            function renderHighlights(items) {
                const safe = items || [];
                const map = [
                    ['#highlightCourse', '#highlightCourseSupport'],
                    ['#highlightMunicipality', '#highlightMunicipalitySupport'],
                    ['#highlightIps', '#highlightIpsSupport'],
                    ['#highlightService', '#highlightServiceSupport']
                ];

                map.forEach(function (pair, index) {
                    const item = safe[index] || {};
                    $(pair[0]).text(item.value || 'Sin datos');
                    $(pair[1]).text(item.support || '0 registros');
                });
            }

            function renderCourseCards(courses) {
                $('[data-course]').each(function () {
                    const key = $(this).data('course');
                    const course = courses.find(item => item.key === key) || {};
                    $(this).find('.js-total').text(numberFmt(course.total));
                    $(this).find('.js-pacientes').text(numberFmt(course.pacientes));
                    $(this).find('.js-modulos').text(numberFmt(course.modulos));
                    $(this).find('.js-updated').text(course.updatedAt || 'Sin carga');
                });
            }

            function renderTrendChart(trend) {
                destroyChart('trend');
                charts.trend = new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: trend.labels || [],
                        datasets: (trend.series || []).map(item => ({
                            label: item.label,
                            data: item.data,
                            borderColor: item.color,
                            backgroundColor: item.color + '22',
                            fill: false,
                            tension: .28,
                            borderWidth: 3,
                            pointRadius: 3
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            function renderCategoryChart(items) {
                destroyChart('category');
                charts.category = new Chart(document.getElementById('categoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: items.map(item => item.label),
                        datasets: [{
                            data: items.map(item => item.value),
                            backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        cutout: '62%'
                    }
                });
            }

            function renderHorizontalChart(key, canvasId, items, color) {
                destroyChart(key);
                charts[key] = new Chart(document.getElementById(canvasId), {
                    type: 'bar',
                    data: {
                        labels: items.map(item => item.label),
                        datasets: [{
                            label: 'Atenciones',
                            data: items.map(item => item.value),
                            backgroundColor: color
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            function renderCourseCategoryChart(payload) {
                destroyChart('courseCategory');
                charts.courseCategory = new Chart(document.getElementById('courseCategoryChart'), {
                    type: 'bar',
                    data: {
                        labels: payload.labels || [],
                        datasets: (payload.series || []).map(item => ({
                            label: item.label,
                            data: item.data,
                            backgroundColor: item.color
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            function renderFilters(filters) {
                const selected = filters.selected || {};
                syncSelectOptions('#filterCurso', filters.courses || [], selected.curso, 'Todos los cursos');
                syncSelectOptions('#filterLinea', filters.groups || [], selected.linea, 'Todas las lineas');
                syncSelectOptions('#filterModulo', filters.modules || [], selected.modulo, 'Todos los modulos');
                syncSelectOptions('#filterDepartamento', filters.departments || [], selected.departamento, 'Todos los departamentos');
                syncSelectOptions('#filterMunicipio', filters.municipalities || [], selected.municipio, 'Todos los municipios');
                syncSelectOptions('#filterIps', filters.ips || [], selected.ips, 'Todas las IPS');
                syncSelectOptions('#filterRangoEdad', filters.ageRanges || [], selected.rango_edad, 'Todos los rangos');

                const labelOf = function (items, value) {
                    if (!value) {
                        return null;
                    }

                    const found = (items || []).find(item => item.value === value);
                    return found ? found.label : value;
                };

                const active = [
                    ['Curso', labelOf(filters.courses, selected.curso)],
                    ['Linea', labelOf(filters.groups, selected.linea)],
                    ['Modulo', labelOf(filters.modules, selected.modulo)],
                    ['Departamento', labelOf(filters.departments, selected.departamento)],
                    ['Municipio', labelOf(filters.municipalities, selected.municipio)],
                    ['IPS', labelOf(filters.ips, selected.ips)],
                    ['Rango', labelOf(filters.ageRanges, selected.rango_edad)]
                ].filter(item => item[1]);

                $('#activeFiltersSummary').text(
                    active.length
                        ? 'Filtros activos: ' + active.map(item => item[0] + ': ' + item[1]).join(' | ')
                        : (
                            hasLoadedDashboard
                                ? 'Sin filtros adicionales. Mostrando todos los cursos para el rango seleccionado.'
                                : 'Selecciona un rango y presiona Aplicar filtros para construir el tablero.'
                        )
                );
            }

            function collectParams() {
                return {
                    desde: rangePicker.getStart().format('YYYY-MM-DD'),
                    hasta: rangePicker.getEndExclusive().format('YYYY-MM-DD'),
                    curso: $('#filterCurso').val() || '',
                    linea: $('#filterLinea').val() || '',
                    modulo: $('#filterModulo').val() || '',
                    departamento: $('#filterDepartamento').val() || '',
                    municipio: $('#filterMunicipio').val() || '',
                    ips: $('#filterIps').val() || '',
                    rango_edad: $('#filterRangoEdad').val() || ''
                };
            }

            function loadDashboard() {
                const params = collectParams();

                showStatsLoading('Consultando estadisticas materializadas...');
                $('#btnDashboard').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando');
                $('#btnClearFilters').prop('disabled', true);

                $.getJSON(dashboardUrl, params)
                    .done(function (payload) {
                        hasLoadedDashboard = true;
                        renderKpis(payload.kpis || {});
                        renderHighlights(payload.highlights || []);
                        renderCourseCards(payload.courses || []);
                        renderTrendChart(payload.trend || { labels: [], series: [] });
                        renderCategoryChart(payload.categories || []);
                        renderCourseCategoryChart(payload.courseCategories || { labels: [], series: [] });
                        renderHorizontalChart('modules', 'moduleChart', payload.modules || [], '#1d4ed8');
                        renderHorizontalChart('ips', 'ipsChart', payload.ips || [], '#0f766e');
                        renderHorizontalChart('municipalities', 'municipalityChart', payload.municipalities || [], '#0284c7');
                        renderHorizontalChart('ageRanges', 'ageRangeChart', payload.ageRanges || [], '#dc2626');
                        renderHorizontalChart('services', 'serviceChart', payload.services || [], '#7c3aed');
                        renderHorizontalChart('departments', 'departmentChart', payload.departments || [], '#f59e0b');
                        renderFilters(payload.filters || { selected: {} });
                        $('#dashboardRangeLabel').text('Corte: ' + (payload.desde || params.desde) + ' a ' + (payload.hasta || rangePicker.getEndInclusive().format('YYYY-MM-DD')));
                    })
                    .fail(function () {
                        $('#dashboardRangeLabel').text('No fue posible cargar el tablero');
                    })
                    .always(function () {
                        $('#btnDashboard').prop('disabled', false).html('<i class="fas fa-chart-pie"></i> Aplicar filtros');
                        $('#btnClearFilters').prop('disabled', false);
                        hideStatsLoading();
                    });
            }

            $('#btnDashboard').on('click', loadDashboard);
            $('#btnClearFilters').on('click', function () {
                $('#filterCurso, #filterLinea, #filterModulo, #filterDepartamento, #filterMunicipio, #filterIps, #filterRangoEdad').val('');
                loadDashboard();
            });
        });
    </script>
@stop
