@extends('adminlte::page')

@section('title', 'Informacion general de ciclos de vida')

@section('content_header')
    <div class="cv-info-hero">
        <div>
            <span class="cv-info-chip">Informacion general</span>
            <h1 class="mb-2">Panorama operativo por curso de vida</h1>
            <p class="mb-0">
                Consulta el alcance de cada curso, su rango etario, las lineas operativas priorizadas
                y un resumen dinamico del volumen materializado para orientar seguimiento, demanda inducida y gestion.
            </p>
        </div>
        <div class="cv-info-actions">
            <div class="cv-info-brand">
                <img src="{{ $companyLogo }}" alt="Escudo de la empresa" class="cv-info-brand__logo">
                <div>
                    <small>Analitica institucional</small>
                    <strong>INOVA</strong>
                </div>
            </div>
            <a href="{{ route('ciclosvida.index') }}" class="btn btn-light btn-lg shadow-sm">
                <i class="fas fa-th-large mr-2"></i>Volver a cursos
            </a>
            <a href="{{ route('ciclosvida.stats.index') }}" class="btn btn-outline-light btn-lg">
                <i class="fas fa-chart-line mr-2"></i>Ir a estadisticas
            </a>
        </div>
    </div>
@stop

@section('content')
    <div id="cvGeneralLoading" class="cv-general-loading is-visible" aria-live="polite" aria-busy="true">
        <div class="cv-general-loading__backdrop"></div>
        <div class="cv-general-loading__panel">
            <div class="cv-general-loading__grid"></div>
            <div class="cv-general-loading__orb">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="cv-general-loading__brand">
                <img src="{{ $companyLogo }}" alt="Escudo institucional">
            </div>
            <h3>Cargando panorama operativo</h3>
            <p>Estamos consolidando indicadores, modulos y brechas por curso de vida para presentar la informacion completa.</p>
            <div class="cv-general-loading__status">
                <span class="cv-general-loading__dot"></span>
                <span id="cvGeneralLoadingText">Sincronizando informacion general...</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            @include('ciclo_vidas.partials.date_range_toolbar', [
                'pickerId' => 'daterange',
                'applyButtonId' => 'btnInfoRefresh',
                'applyLabel' => 'Actualizar resumen',
                'applyIcon' => 'fas fa-sync-alt',
                'noteClass' => 'cv-info-note',
                'note' => '<span id="rangeNote"><i class="fas fa-info-circle"></i> Corte dinamico para resumen operativo transversal.</span>',
            ])
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-xl mb-3">
            <div class="cv-info-kpi bg-primary">
                <small>Atenciones materializadas</small>
                <h3 id="kpiTotal">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl mb-3">
            <div class="cv-info-kpi bg-success">
                <small>Pacientes unicos</small>
                <h3 id="kpiPacientes">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl mb-3">
            <div class="cv-info-kpi bg-info">
                <small>Cursos con actividad</small>
                <h3 id="kpiCursos">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl mb-3">
            <div class="cv-info-kpi bg-warning">
                <small>Modulos activos</small>
                <h3 id="kpiModulos">0</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl mb-3">
            <div class="cv-info-kpi bg-secondary">
                <small>IPS / Grupo</small>
                <h3 id="kpiIps">0</h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title mb-1">Lectura dinamica por curso de vida</h3>
            <small class="text-muted">Selecciona un curso para revisar su contexto, volumen materializado, modulos trazadores y atenciones faltantes.</small>
        </div>
        <div class="card-body">
            <div class="cv-course-selector mb-4" id="courseSelector">
                @foreach ($courseMeta as $index => $course)
                    <button
                        type="button"
                        class="btn cv-course-pill {{ $index === 0 ? 'is-active' : '' }}"
                        data-course-switch="{{ $course['key'] }}">
                        <i class="{{ $course['icon'] }} mr-2"></i>{{ $course['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="cv-course-spotlight">
                <div class="cv-course-spotlight__hero">
                    <div class="d-flex align-items-start">
                        <div id="spotlightIcon" class="cv-course-info-icon bg-secondary mr-3">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="cv-course-badges mb-2">
                                <span id="spotlightAge"><i class="far fa-clock mr-1"></i>Sin rango</span>
                                <span id="spotlightUpdated"><i class="fas fa-database mr-1"></i>Sin actualizacion</span>
                            </div>
                            <h3 id="spotlightTitle" class="mb-1">Curso de vida</h3>
                            <p id="spotlightDescription" class="mb-0 text-muted">Selecciona un curso para explorar su lectura dinamica.</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="cv-spotlight-kpi">
                            <small>Atenciones realizadas</small>
                            <strong id="spotlightTotal">0</strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="cv-spotlight-kpi">
                            <small>Pacientes unicos</small>
                            <strong id="spotlightPatients">0</strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="cv-spotlight-kpi">
                            <small>IPS / Grupo</small>
                            <strong id="spotlightIps">0</strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="cv-spotlight-kpi">
                            <small>Modulos activos</small>
                            <strong id="spotlightModules">0</strong>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-xl-7 mb-3">
                        <div class="cv-course-panel h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1">Modulos con mayor movimiento</h5>
                                    <small class="text-muted">Resume rapidamente las atenciones mas frecuentes del curso seleccionado.</small>
                                </div>
                            </div>
                            <div id="spotlightModulesList" class="cv-module-bars"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5 mb-3">
                        <div class="cv-course-panel h-100 cv-course-panel--alerts">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1">Atenciones faltantes</h5>
                                    <small class="text-muted">Brecha del curso segun las alertas materializadas para seguimiento y demanda inducida.</small>
                                </div>
                                <span id="spotlightMissingState" class="badge badge-light">Sin corte</span>
                            </div>
                            <div class="cv-missing-grid">
                                <button type="button" class="cv-missing-card cv-missing-card--action" data-open-missing-detail="alerts">
                                    <small>Atenciones faltantes</small>
                                    <strong id="spotlightMissingTotal">0</strong>
                                </button>
                                <button type="button" class="cv-missing-card cv-missing-card--action" data-open-missing-detail="patients">
                                    <small>Pacientes en alerta</small>
                                    <strong id="spotlightMissingPatients">0</strong>
                                </button>
                                <div class="cv-missing-card">
                                    <small>IPS con brecha</small>
                                    <strong id="spotlightMissingIps">0</strong>
                                </div>
                            </div>
                            <p id="spotlightMissingNote" class="cv-missing-note mb-0">Sin corte disponible.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @foreach ($courseMeta as $course)
            <div class="col-12 col-xl-6">
                <div class="card cv-course-info shadow-sm mb-4" data-course="{{ $course['key'] }}">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-start">
                                <div class="cv-course-info-icon {{ $course['color'] }}">
                                    <i class="{{ $course['icon'] }}"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="mb-1">{{ $course['label'] }}</h3>
                                    <div class="cv-course-badges">
                                        <span><i class="far fa-clock mr-1"></i>{{ $course['ageLabel'] }}</span>
                                        <span><i class="fas fa-stream mr-1"></i>{{ count($course['groups']) }} bloques</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ $course['menuRoute'] }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-right mr-1"></i>Ver opciones
                            </a>
                        </div>

                        <p class="text-muted mb-3">{{ $course['description'] }}</p>

                        <div class="row cv-course-metrics">
                            <div class="col-6 col-md-3 mb-3">
                                <div class="cv-metric-box">
                                    <small>Atenciones</small>
                                    <strong class="js-total">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="cv-metric-box">
                                    <small>Pacientes</small>
                                    <strong class="js-pacientes">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="cv-metric-box">
                                    <small>Modulos con carga</small>
                                    <strong class="js-modulos">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="cv-metric-box">
                                    <small>Ultima actualizacion</small>
                                    <strong class="js-updated">Sin carga</strong>
                                </div>
                            </div>
                        </div>

                        <div class="cv-group-grid">
                            @foreach ($course['groups'] as $group)
                                <div class="cv-group-card">
                                    <h6>{{ $group['title'] }}</h6>
                                    <div class="cv-chip-list">
                                        @foreach ($group['items'] as $item)
                                            <span class="badge badge-light">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="modal fade cv-detail-modal" id="missingDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title mb-1" id="missingDetailTitle">Detalle de alertas</h4>
                        <div class="cv-detail-meta">
                            <span class="badge badge-light" id="missingDetailCourse">Curso</span>
                            <span class="badge badge-warning" id="missingDetailCut">Corte</span>
                            <span class="badge badge-info" id="missingDetailMode">Vista</span>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="cv-detail-toolbar">
                        <p id="missingDetailDescription">Consulta detallada del curso seleccionado.</p>
                        <div class="cv-detail-kpis">
                            <div class="cv-detail-kpi">
                                <small>Registros</small>
                                <strong id="missingDetailRecords">0</strong>
                            </div>
                            <div class="cv-detail-kpi">
                                <small>Pacientes</small>
                                <strong id="missingDetailPatients">0</strong>
                            </div>
                            <div class="cv-detail-kpi">
                                <small>IPS</small>
                                <strong id="missingDetailIps">0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="missingDetailTable" class="table table-striped table-hover nowrap w-100">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title mb-1">Como leer esta seccion</h3>
            <small class="text-muted">Referencia rapida para equipos operativos, prestadores y seguimiento interno.</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-4 mb-3">
                    <div class="cv-reading-card h-100">
                        <i class="fas fa-compass"></i>
                        <h5>Enfoque por etapa</h5>
                        <p class="mb-0">Cada curso se organiza por momento vital, actividades trazadoras y tamizajes utiles para su grupo etario.</p>
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-3">
                    <div class="cv-reading-card h-100">
                        <i class="fas fa-database"></i>
                        <h5>Datos dinamicos</h5>
                        <p class="mb-0">Los totales y fechas de actualizacion salen del cache materializado, por eso cargan rapido y son comparables entre cursos.</p>
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-3">
                    <div class="cv-reading-card h-100">
                        <i class="fas fa-route"></i>
                        <h5>Ruta de uso</h5>
                        <p class="mb-0">Desde aqui entiendes el alcance del curso; desde las opciones operativas entras al detalle nominal; y desde estadisticas vas a graficas y tendencias.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title mb-1">Referentes normativos oficiales</h3>
            <small class="text-muted">Base conceptual y operativa para el enfoque por curso de vida y priorizacion de actividades.</small>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <style>
        .content-wrapper, .content, .container-fluid { background: #f4f7fb !important; }
        .cv-info-hero {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 1rem;
            padding: 1.5rem 1.75rem;
            border-radius: 26px;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.22), transparent 32%),
                radial-gradient(circle at bottom right, rgba(14,165,233,.16), transparent 30%),
                linear-gradient(135deg, #0f172a, #1e3a8a 55%, #0f766e);
            color: #fff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .22);
        }
        .cv-info-hero h1 {
            color: #fff !important;
        }
        .cv-info-chip {
            display: inline-flex;
            align-items: center;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            font-size: .82rem;
            letter-spacing: .03em;
            margin-bottom: .9rem;
        }
        .cv-info-hero h1 { font-size: 2.15rem; font-weight: 800; }
        .cv-info-hero p { color: rgba(255,255,255,.84); }
        .cv-info-actions {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
            gap: .85rem;
        }
        .cv-info-brand {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .85rem 1rem;
            border-radius: 18px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.12);
        }
        .cv-info-brand__logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255,255,255,.9);
            padding: .35rem;
        }
        .cv-info-brand small {
            display: block;
            color: rgba(255,255,255,.72);
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .cv-info-brand strong {
            color: #fff;
            font-size: 1rem;
            letter-spacing: .08em;
        }
        .cv-general-loading {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .cv-general-loading.is-visible {
            display: flex;
        }
        .cv-general-loading__backdrop {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.28), transparent 30%),
                radial-gradient(circle at bottom right, rgba(20,184,166,.24), transparent 32%),
                rgba(15, 23, 42, .74);
            backdrop-filter: blur(12px);
        }
        .cv-general-loading__panel {
            position: relative;
            width: min(520px, 100%);
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,.14);
            background: linear-gradient(145deg, rgba(15, 23, 42, .96), rgba(30, 41, 59, .92));
            box-shadow: 0 30px 80px rgba(15, 23, 42, .42);
            padding: 2rem 1.8rem 1.7rem;
            text-align: center;
            color: #fff;
        }
        .cv-general-loading__panel::before {
            content: '';
            position: absolute;
            inset: -30% auto auto -10%;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,.38), transparent 70%);
            pointer-events: none;
        }
        .cv-general-loading__panel::after {
            content: '';
            position: absolute;
            right: -70px;
            bottom: -70px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20,184,166,.28), transparent 72%);
            pointer-events: none;
        }
        .cv-general-loading__grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(148, 163, 184, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: linear-gradient(180deg, transparent, rgba(255,255,255,.75), transparent);
            pointer-events: none;
        }
        .cv-general-loading__orb {
            position: relative;
            width: 104px;
            height: 104px;
            margin: 0 auto 1rem;
        }
        .cv-general-loading__orb span {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: rgba(96, 165, 250, .95);
            border-right-color: rgba(45, 212, 191, .75);
            animation: cvSpin 1.45s linear infinite;
        }
        .cv-general-loading__orb span:nth-child(2) {
            inset: 12px;
            border-top-color: rgba(34, 211, 238, .9);
            border-right-color: rgba(165, 180, 252, .7);
            animation-duration: 1.05s;
            animation-direction: reverse;
        }
        .cv-general-loading__orb span:nth-child(3) {
            inset: 26px;
            border-top-color: rgba(250, 204, 21, .95);
            border-right-color: rgba(96, 165, 250, .72);
            animation-duration: .9s;
        }
        .cv-general-loading__brand {
            position: relative;
            z-index: 1;
            margin-bottom: .8rem;
        }
        .cv-general-loading__brand img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(0,0,0,.35));
        }
        .cv-general-loading__panel h3 {
            position: relative;
            z-index: 1;
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .45rem;
        }
        .cv-general-loading__panel p {
            position: relative;
            z-index: 1;
            margin: 0 auto 1.1rem;
            max-width: 360px;
            color: rgba(226, 232, 240, .86);
        }
        .cv-general-loading__status {
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
        .cv-general-loading__dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22d3ee;
            box-shadow: 0 0 0 0 rgba(34, 211, 238, .7);
            animation: cvPulse 1.5s ease-out infinite;
        }
        body.cv-loading-lock {
            overflow: hidden;
        }
        @keyframes cvSpin {
            to { transform: rotate(360deg); }
        }
        @keyframes cvPulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 211, 238, .7); }
            70% { box-shadow: 0 0 0 14px rgba(34, 211, 238, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 211, 238, 0); }
        }
        .cv-info-note {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .7rem 1rem;
            border-radius: 999px;
            background: #eef6ff;
            color: #0f3f74;
            font-size: .92rem;
        }
        .cv-info-kpi {
            border-radius: 20px;
            padding: 1.1rem 1.2rem;
            min-height: 122px;
            color: #fff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
        }
        .cv-info-kpi small {
            display: block;
            color: rgba(255,255,255,.82);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .7rem;
        }
        .cv-info-kpi h3 { font-size: 2rem; font-weight: 800; margin: 0; }
        .cv-course-selector {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
        }
        .cv-course-pill {
            border-radius: 999px;
            border: 1px solid #d9e4f2;
            background: #fff;
            color: #334155;
            font-weight: 700;
            padding: .55rem 1rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
        }
        .cv-course-pill.is-active {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border-color: #60a5fa;
            color: #1d4ed8;
        }
        .cv-course-spotlight {
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            border: 1px solid #e4edf8;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
            padding: 1.3rem;
        }
        .cv-course-spotlight__hero {
            margin-bottom: 1.25rem;
        }
        .cv-spotlight-kpi {
            height: 100%;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 1rem 1.05rem;
        }
        .cv-spotlight-kpi small,
        .cv-missing-card small {
            display: block;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .4rem;
            font-size: .78rem;
        }
        .cv-spotlight-kpi strong,
        .cv-missing-card strong {
            display: block;
            color: #0f172a;
            font-size: 1.4rem;
            font-weight: 800;
        }
        .cv-course-panel {
            height: 100%;
            border-radius: 20px;
            background: #fff;
            border: 1px solid #e5edf7;
            padding: 1.15rem;
        }
        .cv-course-panel--alerts {
            background: linear-gradient(180deg, #fff8f2, #ffffff);
        }
        .cv-module-bars {
            display: grid;
            gap: .85rem;
        }
        .cv-module-bar__row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .75rem;
            align-items: center;
        }
        .cv-module-bar__label {
            font-weight: 700;
            color: #1e293b;
        }
        .cv-module-bar__value {
            color: #475569;
            font-weight: 700;
        }
        .cv-module-bar__track {
            grid-column: 1 / -1;
            height: 10px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .cv-module-bar__fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #2563eb, #14b8a6);
        }
        .cv-missing-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .85rem;
            margin-bottom: 1rem;
        }
        .cv-missing-card {
            border-radius: 18px;
            background: rgba(255,255,255,.9);
            border: 1px solid #fde2d2;
            padding: .95rem 1rem;
        }
        .cv-missing-card--action {
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }
        .cv-missing-card--action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            border-color: #93c5fd;
        }
        .cv-missing-card--action:focus {
            outline: none;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .2);
        }
        .cv-missing-note {
            color: #7c2d12;
            font-size: .92rem;
        }
        .cv-detail-modal .modal-content {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .18);
        }
        .cv-detail-modal .modal-header {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            border-bottom: 0;
        }
        .cv-detail-modal .close {
            color: #fff;
            opacity: .85;
        }
        .cv-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .cv-detail-meta .badge {
            font-size: .82rem;
            padding: .55rem .7rem;
        }
        .cv-detail-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .cv-detail-toolbar p {
            margin-bottom: 0;
            color: #64748b;
        }
        .cv-detail-kpis {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }
        .cv-detail-kpi {
            min-width: 145px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e6edf7;
            padding: .85rem .95rem;
        }
        .cv-detail-kpi small {
            display: block;
            color: #64748b;
            margin-bottom: .25rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .cv-detail-kpi strong {
            font-size: 1.05rem;
            color: #0f172a;
            font-weight: 800;
        }
        #missingDetailTable_wrapper .dataTables_filter input,
        #missingDetailTable_wrapper .dataTables_length select {
            border-radius: 10px;
            border: 1px solid #dbe6f2;
            padding: .35rem .55rem;
        }
        .cv-course-info {
            border-radius: 22px;
            border: 1px solid #e6edf7;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
            overflow: hidden;
        }
        .cv-course-info-icon {
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
        .cv-course-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem .9rem;
            color: #475569;
            font-size: .87rem;
        }
        .cv-course-metrics .cv-metric-box {
            height: 100%;
            border-radius: 16px;
            padding: .9rem 1rem;
            background: #f8fafc;
            border: 1px solid #e6edf7;
        }
        .cv-metric-box small {
            display: block;
            color: #64748b;
            margin-bottom: .3rem;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .cv-metric-box strong {
            display: block;
            font-size: 1.1rem;
            color: #0f172a;
            font-weight: 800;
        }
        .cv-group-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .95rem;
        }
        .cv-group-card {
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border: 1px solid #e6edf7;
            padding: 1rem 1rem .85rem;
        }
        .cv-group-card h6 {
            font-size: .95rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .75rem;
        }
        .cv-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }
        .cv-chip-list .badge {
            font-size: .8rem;
            font-weight: 600;
            padding: .5rem .7rem;
            border: 1px solid #dbe6f2;
            color: #334155;
        }
        .cv-reading-card {
            border-radius: 18px;
            border: 1px solid #e6edf7;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
            padding: 1.1rem 1.15rem;
        }
        .cv-reading-card i {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #fff;
            margin-bottom: .9rem;
        }
        .cv-reading-card p { color: #64748b; }
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
        @media (max-width: 1199px) {
            .cv-group-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 991px) {
            .cv-info-hero {
                grid-template-columns: 1fr;
            }
            .cv-info-actions {
                align-items: stretch;
            }
            .cv-missing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @include('ciclo_vidas.partials.date_range_shared_script')
    <script>
        $(function () {
            moment.locale('es');

            const dataUrl = @json(route('ciclosvida.dashboard.data'));
            const rangePicker = window.CicloVidaDateRange.init({
                pickerSelector: '#daterange',
                start: @json($desde),
                end: @json($hasta),
                endExclusive: false
            });
            const courseMeta = @json($courseMeta);
            const alertDataUrlBase = @json(url('/ciclos-vida'));
            let missingDetailTable = null;
            const $loadingOverlay = $('#cvGeneralLoading');
            const $loadingText = $('#cvGeneralLoadingText');

            const numberFmt = (value) => Number(value || 0).toLocaleString('es-CO');

            function buildAlertDataUrl(courseSlug) {
                return `${alertDataUrlBase}/${courseSlug}/modulos/alertas/data`;
            }

            function showGeneralLoading(message) {
                if (message) {
                    $loadingText.text(message);
                }
                $('body').addClass('cv-loading-lock');
                $loadingOverlay.addClass('is-visible');
            }

            function hideGeneralLoading() {
                $('body').removeClass('cv-loading-lock');
                $loadingOverlay.removeClass('is-visible');
            }

            function renderKpis(kpis) {
                $('#kpiTotal').text(numberFmt(kpis.total));
                $('#kpiPacientes').text(numberFmt(kpis.pacientes));
                $('#kpiCursos').text(numberFmt(kpis.cursos));
                $('#kpiModulos').text(numberFmt(kpis.modulos));
                $('#kpiIps').text(numberFmt(kpis.ips));
            }

            function renderCourses(courses) {
                $('[data-course]').each(function () {
                    const key = $(this).data('course');
                    const course = courses.find(item => item.key === key) || {};
                    $(this).find('.js-total').text(numberFmt(course.total));
                    $(this).find('.js-pacientes').text(numberFmt(course.pacientes));
                    $(this).find('.js-modulos').text(numberFmt(course.modulos));
                    $(this).find('.js-updated').text(course.updatedAt || 'Sin carga');
                });
            }

            function renderSpotlight(courseDetails, selectedKey) {
                const detail = (courseDetails || []).find(item => item.key === selectedKey) || (courseDetails || [])[0];
                if (!detail) {
                    return;
                }

                const meta = (courseMeta || []).find(item => item.key === detail.key) || {};
                $('#spotlightTitle').text(detail.label || 'Curso de vida');
                $('#spotlightDescription').text(detail.description || meta.description || 'Sin descripcion disponible.');
                $('#spotlightAge').html('<i class="far fa-clock mr-1"></i>' + (detail.ageLabel || meta.ageLabel || 'Sin rango'));
                $('#spotlightUpdated').html('<i class="fas fa-database mr-1"></i>' + (detail.updatedAt || 'Sin actualizacion'));
                $('#spotlightTotal').text(numberFmt(detail.total));
                $('#spotlightPatients').text(numberFmt(detail.pacientes));
                $('#spotlightIps').text(numberFmt(detail.ips));
                $('#spotlightModules').text(numberFmt(detail.modulos));

                const iconClass = detail.icon || meta.icon || 'fas fa-layer-group';
                const colorClass = detail.color || meta.color || 'bg-secondary';
                $('#spotlightIcon').attr('class', 'cv-course-info-icon mr-3 ' + colorClass);
                $('#spotlightIcon').html('<i class="' + iconClass + '"></i>');

                const missing = detail.missing || {};
                $('#spotlightMissingTotal').text(numberFmt(missing.total));
                $('#spotlightMissingPatients').text(numberFmt(missing.pacientes));
                $('#spotlightMissingIps').text(numberFmt(missing.ips));
                $('#spotlightMissingState')
                    .text(missing.exact ? 'Corte exacto' : 'Ultimo corte disponible')
                    .toggleClass('badge-warning', !missing.exact)
                    .toggleClass('badge-light', !!missing.exact);
                $('#spotlightMissingNote').text(
                    missing.updated_at
                        ? 'Actualizado: ' + missing.updated_at + (missing.exact ? '' : ' · usando el ultimo corte disponible de alertas.')
                        : 'Este curso aun no tiene un corte materializado de alertas disponible.'
                );

                const modules = detail.topModules || [];
                const maxValue = Math.max(...modules.map(item => Number(item.value || 0)), 1);
                $('#spotlightModulesList').html(
                    modules.length
                        ? modules.map(item => `
                            <div class="cv-module-bar">
                                <div class="cv-module-bar__row">
                                    <span class="cv-module-bar__label">${item.label}</span>
                                    <span class="cv-module-bar__value">${numberFmt(item.value)}</span>
                                    <div class="cv-module-bar__track">
                                        <div class="cv-module-bar__fill" style="width:${Math.max(10, Math.round((Number(item.value || 0) / maxValue) * 100))}%"></div>
                                    </div>
                                </div>
                            </div>
                        `).join('')
                        : '<p class="text-muted mb-0">No hay modulos con actividad para este corte.</p>'
                );

                $('[data-course-switch]').removeClass('is-active');
                $('[data-course-switch="' + detail.key + '"]').addClass('is-active');
            }

            function getActiveCourseKey() {
                return $('[data-course-switch].is-active').data('course-switch') || courseMeta[0]?.key;
            }

            function getActiveCourseMeta() {
                return (courseMeta || []).find(item => item.key === getActiveCourseKey()) || courseMeta[0] || null;
            }

            function getAlertColumns(viewMode) {
                if (viewMode === 'patients') {
                    return [
                        { data: 'tipoIdentificacion', title: 'Tipo ID' },
                        { data: 'identificacion', title: 'Identificacion' },
                        { data: 'apellidos', title: 'Apellidos' },
                        { data: 'nombres', title: 'Nombres' },
                        { data: 'fechaNacimiento', title: 'F. Nac.' },
                        { data: 'edadAnios', title: 'Edad (a)' },
                        { data: 'motivosAlerta', title: 'Motivo de alerta' },
                        { data: 'actividadesPendientes', title: 'Actividades pendientes' },
                        { data: 'ips_Prim', title: 'IPS / Grupo' },
                        { data: 'totalAlertas', title: 'Total alertas' },
                    ];
                }

                return [
                    { data: 'tipoIdentificacion', title: 'Tipo ID' },
                    { data: 'identificacion', title: 'Identificacion' },
                    { data: 'apellidos', title: 'Apellidos' },
                    { data: 'nombres', title: 'Nombres' },
                    { data: 'fechaNacimiento', title: 'F. Nac.' },
                    { data: 'edadAnios', title: 'Edad (a)' },
                    { data: 'motivoAlerta', title: 'Atencion faltante' },
                    { data: 'ips_Prim', title: 'IPS / Grupo' },
                    { data: 'codigoHabilitacion', title: 'Cod. Hab.' },
                ];
            }

            function renderDetailTableHeader(columns) {
                $('#missingDetailTable thead').html(
                    '<tr>' + columns.map(column => `<th>${column.title}</th>`).join('') + '</tr>'
                );
            }

            function openMissingDetail(viewMode) {
                const selectedCourse = getActiveCourseMeta();
                if (!selectedCourse) {
                    return;
                }

                const currentDetail = (window.__courseDetailsPayload || []).find(item => item.key === selectedCourse.key) || {};
                const columns = getAlertColumns(viewMode);
                const modeLabel = viewMode === 'patients' ? 'Pacientes en alerta' : 'Atenciones faltantes';

                $('#missingDetailTitle').text(modeLabel + ' · ' + (selectedCourse.label || 'Curso de vida'));
                $('#missingDetailCourse').text(selectedCourse.label || 'Curso');
                $('#missingDetailMode').text(modeLabel);
                $('#missingDetailCut').text($('#rangeNote').text().replace(/\s+/g, ' ').trim() || 'Corte activo');
                $('#missingDetailDescription').text(
                    viewMode === 'patients'
                        ? 'Agrupa el corte de alertas por paciente para priorizar demanda inducida y seguimiento.'
                        : 'Lista detallada de actividades pendientes del curso seleccionado en el corte activo.'
                );
                $('#missingDetailRecords').text(numberFmt(currentDetail.missing?.total || 0));
                $('#missingDetailPatients').text(numberFmt(currentDetail.missing?.pacientes || 0));
                $('#missingDetailIps').text(numberFmt(currentDetail.missing?.ips || 0));

                renderDetailTableHeader(columns);

                if (missingDetailTable) {
                    missingDetailTable.destroy();
                    $('#missingDetailTable tbody').empty();
                }

                missingDetailTable = $('#missingDetailTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: false,
                    scrollX: true,
                    autoWidth: false,
                    pageLength: 10,
                    order: [[viewMode === 'patients' ? 9 : 6, 'desc']],
                    ajax: {
                        url: buildAlertDataUrl(selectedCourse.slug),
                        data: function (d) {
                            d.desde = rangePicker.getStart().format('YYYY-MM-DD');
                            d.hasta = rangePicker.getEndExclusive().format('YYYY-MM-DD');
                            d.view_mode = viewMode;
                        }
                    },
                    columns,
                    columnDefs: [
                        {
                            targets: 6,
                            width: '320px'
                        }
                    ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                    }
                });

                $('#missingDetailModal').modal('show');
            }

            function loadInfo() {
                const params = {
                    desde: rangePicker.getStart().format('YYYY-MM-DD'),
                    hasta: rangePicker.getEndExclusive().format('YYYY-MM-DD')
                };

                showGeneralLoading('Sincronizando informacion general...');
                $('#btnInfoRefresh').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando');

                $.getJSON(dataUrl, params)
                    .done(function (payload) {
                        window.__courseDetailsPayload = payload.courseDetails || [];
                        renderKpis(payload.kpis || {});
                        renderCourses(payload.courses || []);
                        renderSpotlight(payload.courseDetails || [], getActiveCourseKey());
                        $('#rangeNote').html('<i class="fas fa-info-circle"></i> Corte activo: ' + (payload.desde || params.desde) + ' a ' + (payload.hasta || rangePicker.getEndInclusive().format('YYYY-MM-DD')));
                    })
                    .fail(function () {
                        $('#rangeNote').html('<i class="fas fa-exclamation-circle"></i> No fue posible actualizar la informacion general.');
                    })
                    .always(function () {
                        $('#btnInfoRefresh').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar resumen');
                        hideGeneralLoading();
                    });
            }

            $('#btnInfoRefresh').on('click', loadInfo);
            $('[data-course-switch]').on('click', function () {
                renderSpotlight(window.__courseDetailsPayload || [], $(this).data('course-switch'));
            });
            $('[data-open-missing-detail]').on('click', function () {
                openMissingDetail($(this).data('open-missing-detail'));
            });

            loadInfo();
        });
    </script>
@stop
