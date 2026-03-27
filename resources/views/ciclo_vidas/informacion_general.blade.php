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
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap align-items-end">
            <div class="mr-3 mb-2">
                <label class="mb-1 font-weight-bold">Rango de fecha</label>
                <div id="daterange" class="form-control d-inline-block" style="width: 290px; cursor: pointer;">
                    <i class="far fa-calendar-alt"></i>
                    <span class="ml-2"></span>
                    <i class="fa fa-caret-down float-right mt-1"></i>
                </div>
            </div>
            <div class="mb-2">
                <button id="btnInfoRefresh" class="btn btn-primary px-4">
                    <i class="fas fa-sync-alt"></i> Actualizar resumen
                </button>
            </div>
            <div class="ml-auto mb-2">
                <div class="cv-info-note" id="rangeNote">
                    <i class="fas fa-info-circle"></i>
                    Corte dinamico para resumen operativo transversal.
                </div>
            </div>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
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
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function () {
            moment.locale('es');

            const dataUrl = @json(route('ciclosvida.dashboard.data'));
            const startDefault = moment(@json($desde), 'YYYY-MM-DD');
            const endDefault = moment(@json($hasta), 'YYYY-MM-DD');

            const numberFmt = (value) => Number(value || 0).toLocaleString('es-CO');

            function setLabel(start, end) {
                $('#daterange span').text(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
            }

            $('#daterange').daterangepicker({
                startDate: startDefault,
                endDate: endDefault,
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    customRangeLabel: 'Personalizado'
                },
                ranges: {
                    'Ultimos 30 dias': [moment().subtract(29, 'days'), moment()],
                    'Ultimos 90 dias': [moment().subtract(89, 'days'), moment()],
                    'Ultimos 120 dias': [moment().subtract(119, 'days'), moment()],
                    'Ano actual': [moment().startOf('year'), moment()]
                }
            }, setLabel);

            setLabel(startDefault, endDefault);

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

            function loadInfo() {
                const drp = $('#daterange').data('daterangepicker');
                const params = {
                    desde: drp.startDate.format('YYYY-MM-DD'),
                    hasta: drp.endDate.clone().add(1, 'day').format('YYYY-MM-DD')
                };

                $('#btnInfoRefresh').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando');

                $.getJSON(dataUrl, params)
                    .done(function (payload) {
                        renderKpis(payload.kpis || {});
                        renderCourses(payload.courses || []);
                        $('#rangeNote').html('<i class="fas fa-info-circle"></i> Corte activo: ' + (payload.desde || params.desde) + ' a ' + (payload.hasta || drp.endDate.format('YYYY-MM-DD')));
                    })
                    .fail(function () {
                        $('#rangeNote').html('<i class="fas fa-exclamation-circle"></i> No fue posible actualizar la informacion general.');
                    })
                    .always(function () {
                        $('#btnInfoRefresh').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar resumen');
                    });
            }

            $('#btnInfoRefresh').on('click', loadInfo);
            loadInfo();
        });
    </script>
@stop
