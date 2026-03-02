@extends('adminlte::page')

@section('title', 'Dashboard Seguimiento')

@section('content_header')
@stop

@section('content')
@php
    $query = request()->query();
    $prestadorSeleccionado = collect($prestadores)->firstWhere('id', $filters['prestador_id']);
    $rows113 = $rows->where('evento', '113')->values();
    $rows412 = $rows->where('evento', '412')->values();
@endphp

<div class="sg-wrap">
    <section class="sg-hero">
        <div class="sg-hero__brand">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="sg-hero__logo">
            <div>
                <h1 class="sg-hero__title">Centro de Inteligencia de Seguimientos</h1>
                <p class="sg-hero__subtitle">Monitoreo de prestadores, brechas de seguimiento y cobertura en tiempo real.</p>
            </div>
        </div>
        <div class="sg-hero__meta">
            <div class="sg-chip">Anio: <strong>{{ $filters['anio'] }}</strong></div>
            <div class="sg-chip">Eventos: <strong>113 y 412</strong></div>
            <div class="sg-chip">Estado: <strong>{{ str_replace('_', ' ', $filters['estado']) }}</strong></div>
        </div>
    </section>

    <section class="sg-panel">
        <form method="GET" action="{{ route('grafica.barras') }}" class="sg-filters">
            <div class="sg-field">
                <label>Anio</label>
                <select class="form-control" name="anio">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ (int)$filters['anio'] === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sg-field">
                <label>Prestador</label>
                <select class="form-control" name="prestador_id">
                    <option value="">Todos</option>
                    @foreach($prestadores as $prestador)
                        <option value="{{ $prestador->id }}" {{ (int)$filters['prestador_id'] === (int)$prestador->id ? 'selected' : '' }}>
                            {{ $prestador->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sg-field">
                <label>Estado</label>
                <select class="form-control" name="estado">
                    <option value="con_sin" {{ $filters['estado'] === 'con_sin' ? 'selected' : '' }}>Con y sin seguimiento</option>
                    <option value="solo_sin" {{ $filters['estado'] === 'solo_sin' ? 'selected' : '' }}>Solo sin seguimiento</option>
                    <option value="solo_con" {{ $filters['estado'] === 'solo_con' ? 'selected' : '' }}>Solo completos</option>
                    <option value="alto_riesgo" {{ $filters['estado'] === 'alto_riesgo' ? 'selected' : '' }}>Solo alto riesgo</option>
                </select>
            </div>

            <div class="sg-field">
                <label>Orden</label>
                <select class="form-control" name="orden">
                    <option value="sin_desc" {{ $filters['orden'] === 'sin_desc' ? 'selected' : '' }}>Mas sin seguimiento</option>
                    <option value="cobertura_asc" {{ $filters['orden'] === 'cobertura_asc' ? 'selected' : '' }}>Cobertura menor</option>
                    <option value="cobertura_desc" {{ $filters['orden'] === 'cobertura_desc' ? 'selected' : '' }}>Cobertura mayor</option>
                    <option value="nombre_asc" {{ $filters['orden'] === 'nombre_asc' ? 'selected' : '' }}>Nombre A-Z</option>
                    <option value="nombre_desc" {{ $filters['orden'] === 'nombre_desc' ? 'selected' : '' }}>Nombre Z-A</option>
                </select>
            </div>

            <div class="sg-field sg-field--wide">
                <label>Busqueda</label>
                <input class="form-control" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nombre o identificacion">
            </div>

            <div class="sg-actions">
                <button class="btn sg-btn sg-btn--primary" type="submit">
                    <i class="fas fa-filter mr-1"></i> Aplicar filtros
                </button>
                <a class="btn sg-btn sg-btn--ghost" href="{{ route('grafica.barras') }}">
                    <i class="fas fa-undo mr-1"></i> Limpiar
                </a>
                <a class="btn sg-btn sg-btn--excel" href="{{ route('grafica.barras.export.excel', $query) }}">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
                <a class="btn sg-btn sg-btn--pdf" href="{{ route('grafica.barras.export.pdf', $query) }}">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </div>
        </form>
    </section>

    <section class="sg-kpis">
        <article class="sg-kpi">
            <span>Prestadores analizados</span>
            <strong>{{ number_format($kpis['total_prestadores']) }}</strong>
        </article>
        <article class="sg-kpi">
            <span>Casos asignados</span>
            <strong>{{ number_format($kpis['total_asignados']) }}</strong>
        </article>
        <article class="sg-kpi">
            <span>Con seguimiento</span>
            <strong>{{ number_format($kpis['total_con_seguimiento']) }}</strong>
        </article>
        <article class="sg-kpi">
            <span>Sin seguimiento</span>
            <strong>{{ number_format($kpis['total_sin_seguimiento']) }}</strong>
        </article>
        <article class="sg-kpi">
            <span>Prestadores con alerta</span>
            <strong>{{ number_format($kpis['prestadores_con_alerta']) }}</strong>
        </article>
        <article class="sg-kpi">
            <span>Cobertura global</span>
            <strong>{{ number_format($kpis['cobertura_global_pct'], 2) }}%</strong>
        </article>
    </section>

    <section class="sg-table-card" style="margin-top:12px;">
        <div class="sg-table-head" style="margin-bottom:0;">
            <h3>Graficas del dashboard</h3>
            <button type="button" id="toggleChartsBtn" class="btn sg-btn sg-btn--ghost">
                <i class="fas fa-chart-area mr-1"></i> Mostrar graficas
            </button>
        </div>
    </section>

    <section class="sg-charts sg-charts--collapsed" id="chartsSection">
        <div class="sg-card"><canvas id="chartCobertura"></canvas></div>
        <div class="sg-card"><canvas id="chartEvento"></canvas></div>
        <div class="sg-card"><canvas id="chartTopSin"></canvas></div>
        <div class="sg-card"><canvas id="chartEstado"></canvas></div>
        <div class="sg-card sg-card--wide"><canvas id="chartClasificacion"></canvas></div>
    </section>

    <section class="sg-table-card">
        <div class="sg-table-head">
            <h3>Informe interactivo de prestadores</h3>
            <div>
                @if($prestadorSeleccionado)
                    <span class="sg-pill">Prestador: {{ $prestadorSeleccionado->name }}</span>
                @endif
                <span class="sg-pill">Registros totales: {{ $rows->count() }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="sg-event-card">
                    <div class="sg-event-card__head">
                        <h4>Evento 113</h4>
                        <span class="sg-pill sg-pill--count">{{ $rows113->count() }} registros</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="dashboard-table-113">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Prestador</th>
                                    <th>Asignados</th>
                                    <th>Con seguimiento</th>
                                    <th>Sin seguimiento</th>
                                    <th>Cobertura %</th>
                                    <th>Nivel riesgo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows113 as $row)
                                    <tr class="{{ $row->total_sin_seguimientos > 0 ? 'sg-row-alert' : 'sg-row-ok' }}">
                                        <td>{{ $row->id }}</td>
                                        <td>
                                            <a href="#" class="sg-prestador-link" data-id="{{ $row->id }}" data-evento="{{ $row->evento }}" data-anio="{{ $filters['anio'] }}">
                                                {{ $row->name }}
                                            </a>
                                        </td>
                                        <td>{{ number_format($row->cant_casos_asignados) }}</td>
                                        <td>{{ number_format($row->casos_con_seguimiento) }}</td>
                                        <td>{{ number_format($row->total_sin_seguimientos) }}</td>
                                        <td>{{ number_format($row->cobertura_pct, 2) }}%</td>
                                        <td>{{ $row->nivel_riesgo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sg-event-card">
                    <div class="sg-event-card__head">
                        <h4>Evento 412</h4>
                        <span class="sg-pill sg-pill--count">{{ $rows412->count() }} registros</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="dashboard-table-412">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Prestador</th>
                                    <th>Asignados</th>
                                    <th>Con seguimiento</th>
                                    <th>Sin seguimiento</th>
                                    <th>Cobertura %</th>
                                    <th>Nivel riesgo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows412 as $row)
                                    <tr class="{{ $row->total_sin_seguimientos > 0 ? 'sg-row-alert' : 'sg-row-ok' }}">
                                        <td>{{ $row->id }}</td>
                                        <td>
                                            <a href="#" class="sg-prestador-link" data-id="{{ $row->id }}" data-evento="{{ $row->evento }}" data-anio="{{ $filters['anio'] }}">
                                                {{ $row->name }}
                                            </a>
                                        </td>
                                        <td>{{ number_format($row->cant_casos_asignados) }}</td>
                                        <td>{{ number_format($row->casos_con_seguimiento) }}</td>
                                        <td>{{ number_format($row->total_sin_seguimientos) }}</td>
                                        <td>{{ number_format($row->cobertura_pct, 2) }}%</td>
                                        <td>{{ $row->nivel_riesgo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="sg-detail-mask" id="detailMask"></div>
<aside class="sg-detail-drawer" id="prestadorDetalleDrawer" aria-hidden="true">
    <div class="sg-detail-drawer__head">
        <h5>Pacientes sin seguimiento</h5>
        <button type="button" class="btn sg-btn sg-btn--ghost sg-detail-close" id="closeDetailBtn">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="sg-detail-drawer__meta">
        <span class="sg-pill" id="detalle-meta"></span>
    </div>
    <div class="sg-detail-drawer__body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>Fecha/Semana</th>
                        <th>Identificacion</th>
                        <th>Nombre completo</th>
                    </tr>
                </thead>
                <tbody id="detalle-body">
                    <tr><td colspan="3" class="text-center">Seleccione un prestador para ver detalle.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</aside>
</div>
@stop

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
    .sg-wrap{font-family:'Sora',sans-serif;padding:6px 2px 20px;background:linear-gradient(135deg,#f8fafc 0%,#eef6ff 30%,#f2fbf7 100%);}
    .sg-hero{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:18px 20px;border-radius:16px;background:#0f172a;color:#e2e8f0;box-shadow:0 14px 36px rgba(2,6,23,.2);}
    .sg-hero__brand{display:flex;align-items:center;gap:14px;}
    .sg-hero__logo{width:58px;height:58px;object-fit:contain;background:#fff;border-radius:14px;padding:5px;}
    .sg-hero__title{font-size:1.2rem;font-weight:800;margin:0;}
    .sg-hero__subtitle{font-size:.85rem;opacity:.88;margin:4px 0 0;}
    .sg-hero__meta{display:flex;gap:8px;flex-wrap:wrap;}
    .sg-chip{font-size:.75rem;background:rgba(148,163,184,.2);padding:7px 10px;border-radius:999px;}
    .sg-panel{margin-top:14px;background:#fff;border:1px solid #dbe6f3;border-radius:16px;padding:14px 14px 6px;}
    .sg-filters{display:grid;grid-template-columns:repeat(6,minmax(130px,1fr));gap:10px;align-items:end;}
    .sg-field label{font-size:.72rem;font-weight:700;color:#334155;margin-bottom:4px;display:block;}
    .sg-field--wide{grid-column:span 2;}
    .sg-actions{display:flex;gap:8px;flex-wrap:wrap;grid-column:span 6;padding-top:2px;}
    .sg-btn{border-radius:10px;font-weight:700;font-size:.82rem;}
    .sg-btn--primary{background:#0ea5e9;color:#fff;border:none;}
    .sg-btn--ghost{background:#fff;border:1px solid #cbd5e1;color:#334155;}
    .sg-btn--excel{background:#16a34a;color:#fff;border:none;}
    .sg-btn--pdf{background:#dc2626;color:#fff;border:none;}
    .sg-kpis{display:grid;grid-template-columns:repeat(6,minmax(120px,1fr));gap:10px;margin-top:12px;}
    .sg-kpi{background:#fff;border-radius:14px;padding:12px;border:1px solid #dce7f5;box-shadow:0 4px 14px rgba(15,23,42,.05);}
    .sg-kpi span{display:block;color:#475569;font-size:.74rem;}
    .sg-kpi strong{font-size:1.15rem;color:#0f172a;}
    .sg-event-card{background:linear-gradient(180deg,#ffffff,#f8fbff);border:1px solid #dce7f5;border-radius:14px;padding:10px;box-shadow:0 8px 18px rgba(15,23,42,.06);margin-bottom:8px;}
    .sg-event-card__head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding:2px 4px;}
    .sg-event-card__head h4{margin:0;font-size:.96rem;font-weight:800;color:#0f172a;}
    .sg-pill--count{background:#dbeafe;color:#1e3a8a;}
    .sg-charts{margin-top:12px;display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:10px;}
    .sg-charts--collapsed{max-height:0;overflow:hidden;opacity:.1;pointer-events:none;transition:max-height .35s ease,opacity .25s ease;}
    .sg-charts--expanded{max-height:2200px;opacity:1;pointer-events:auto;}
    .sg-card{background:#fff;border-radius:14px;padding:12px;border:1px solid #dce7f5;min-height:300px;}
    .sg-card--wide{grid-column:span 2;min-height:340px;}
    .sg-table-card{margin-top:12px;background:#fff;border-radius:14px;border:1px solid #dce7f5;padding:12px;}
    .sg-table-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;}
    .sg-table-head h3{font-size:1rem;font-weight:800;margin:0;color:#0f172a;}
    .sg-pill{font-size:.75rem;padding:6px 10px;border-radius:999px;background:#e2e8f0;color:#0f172a;}
    .sg-prestador-link{font-weight:700;color:#0f4c81;}
    .sg-prestador-link:hover{text-decoration:underline;}
    .sg-row-alert td{background:#fff7ed;}
    .sg-row-ok td{background:#f0fdf4;}
    .sg-detail-mask{position:fixed;inset:0;background:rgba(2,6,23,.35);z-index:1040;display:none;}
    .sg-detail-mask.is-open{display:block;}
    .sg-detail-drawer{position:fixed;top:0;right:-680px;width:660px;max-width:96vw;height:100vh;background:#fff;z-index:1045;box-shadow:-14px 0 34px rgba(2,6,23,.22);transition:right .25s ease;display:flex;flex-direction:column;}
    .sg-detail-drawer.is-open{right:0;}
    .sg-detail-drawer__head{padding:12px;border-bottom:1px solid #dce7f5;display:flex;justify-content:space-between;align-items:center;}
    .sg-detail-drawer__head h5{margin:0;font-weight:800;color:#0f172a;}
    .sg-detail-drawer__meta{padding:10px 12px;border-bottom:1px solid #edf2f7;background:#f8fbff;}
    .sg-detail-drawer__body{padding:10px 12px;overflow:auto;}
    @media (max-width:1200px){.sg-filters{grid-template-columns:repeat(3,minmax(160px,1fr));}.sg-field--wide{grid-column:span 3;}.sg-actions{grid-column:span 3;}.sg-kpis{grid-template-columns:repeat(3,minmax(130px,1fr));}}
    @media (max-width:768px){.sg-hero{flex-direction:column;align-items:flex-start;}.sg-filters{grid-template-columns:1fr;}.sg-field--wide,.sg-actions{grid-column:span 1;}.sg-kpis{grid-template-columns:repeat(2,minmax(120px,1fr));}.sg-charts{grid-template-columns:1fr;}.sg-card--wide{grid-column:span 1;}}
</style>
@stop

@section('js')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const detail113Base = @json(route('detallePrestador_113', ['id' => '__ID__']));
    const detail412Base = @json(route('gedetalle_prestador', ['id' => '__ID__']));

    $(function () {
        $('#dashboard-table-113, #dashboard-table-412').DataTable({
            pageLength: 10,
            order: [[4, 'desc']],
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_',
                info: 'Total: _TOTAL_',
                emptyTable: 'Sin registros para los filtros aplicados',
                zeroRecords: 'Sin resultados',
                paginate: { next: 'Siguiente', previous: 'Anterior' }
            }
        });

        function renderDetalleRows(evento, data) {
            if (!Array.isArray(data) || !data.length) {
                return '<tr><td colspan="3" class="text-center">No se encontraron pacientes sin seguimiento para este prestador.</td></tr>';
            }

            return data.map(function (item) {
                if (evento === '113') {
                    const nombre113 = [item.pri_nom_, item.seg_nom_, item.pri_ape_, item.seg_ape_].filter(Boolean).join(' ');
                    return '<tr>' +
                        '<td>' + (item.semana ?? '-') + '</td>' +
                        '<td>' + ((item.tip_ide_ ?? '-') + ' - ' + (item.num_ide_ ?? '-')) + '</td>' +
                        '<td>' + nombre113 + '</td>' +
                        '</tr>';
                }

                const nombre412 = [item.primer_nombre, item.segundo_nombre, item.primer_apellido, item.segundo_apellido].filter(Boolean).join(' ');
                return '<tr>' +
                    '<td>' + (item.fecha_captacion ?? '-') + '</td>' +
                    '<td>' + ((item.tipo_identificacion ?? '-') + ' - ' + (item.numero_identificacion ?? '-')) + '</td>' +
                    '<td>' + nombre412 + '</td>' +
                    '</tr>';
            }).join('');
        }

        $(document).on('click', '.sg-prestador-link', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const evento = String($(this).data('evento'));
            const anio = $(this).data('anio');
            const nombre = $(this).text().trim();
            const urlBase = evento === '113' ? detail113Base : detail412Base;
            const url = urlBase.replace('__ID__', id) + '?anio=' + encodeURIComponent(anio);

            $('#detalle-meta').text('Prestador: ' + nombre + ' | Evento: ' + evento + ' | Año: ' + anio);
            $('#detalle-body').html('<tr><td colspan="3" class="text-center">Cargando detalle...</td></tr>');
            $('#prestadorDetalleDrawer').addClass('is-open').attr('aria-hidden', 'false');
            $('#detailMask').addClass('is-open');

            $.ajax({
                url: url,
                method: 'GET',
                success: function (data) {
                    $('#detalle-body').html(renderDetalleRows(evento, data));
                },
                error: function () {
                    $('#detalle-body').html('<tr><td colspan="3" class="text-center">No fue posible cargar el detalle.</td></tr>');
                }
            });
        });

        function closeDetailDrawer() {
            $('#prestadorDetalleDrawer').removeClass('is-open').attr('aria-hidden', 'true');
            $('#detailMask').removeClass('is-open');
        }
        $('#closeDetailBtn, #detailMask').on('click', closeDetailDrawer);

        const $charts = $('#chartsSection');
        const $btn = $('#toggleChartsBtn');
        $btn.on('click', function () {
            const expanded = $charts.hasClass('sg-charts--expanded');
            if (expanded) {
                $charts.removeClass('sg-charts--expanded').addClass('sg-charts--collapsed');
                $btn.html('<i class="fas fa-chart-area mr-1"></i> Mostrar graficas');
            } else {
                $charts.removeClass('sg-charts--collapsed').addClass('sg-charts--expanded');
                $btn.html('<i class="fas fa-compress-alt mr-1"></i> Contraer graficas');
            }
        });
    });

    const chartData = @json($charts);
    const palette = ['#0ea5e9', '#ef4444', '#f59e0b', '#22c55e', '#6366f1', '#14b8a6', '#f97316', '#a855f7'];

    function buildChart(id, config) {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el, config);
    }

    buildChart('chartCobertura', {
        type: 'doughnut',
        data: {
            labels: chartData.seguimiento_vs_sin.labels,
            datasets: [{ data: chartData.seguimiento_vs_sin.data, backgroundColor: ['#22c55e', '#ef4444'] }]
        },
        options: { responsive: true, plugins: { title: { display: true, text: 'Cobertura Global' } } }
    });

    buildChart('chartEvento', {
        type: 'bar',
        data: {
            labels: chartData.evento.labels,
            datasets: [
                { label: 'Con seguimiento', data: chartData.evento.con, backgroundColor: '#22c55e' },
                { label: 'Sin seguimiento', data: chartData.evento.sin, backgroundColor: '#ef4444' }
            ]
        },
        options: { responsive: true, plugins: { title: { display: true, text: 'Comparativo por evento' } } }
    });

    buildChart('chartTopSin', {
        type: 'bar',
        data: {
            labels: chartData.top_sin.labels,
            datasets: [{ label: 'Casos sin seguimiento', data: chartData.top_sin.data, backgroundColor: '#f97316' }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { title: { display: true, text: 'Top prestadores con casos sin seguimiento' }, legend: { display: false } }
        }
    });

    buildChart('chartEstado', {
        type: 'pie',
        data: {
            labels: chartData.estado_seguimientos.labels,
            datasets: [{ data: chartData.estado_seguimientos.data, backgroundColor: ['#0ea5e9', '#475569'] }]
        },
        options: { responsive: true, plugins: { title: { display: true, text: 'Estado de seguimientos (113)' } } }
    });

    buildChart('chartClasificacion', {
        type: 'bar',
        data: {
            labels: chartData.clasificaciones.labels,
            datasets: [{ label: 'Total', data: chartData.clasificaciones.data, backgroundColor: palette }]
        },
        options: {
            responsive: true,
            plugins: { title: { display: true, text: 'Clasificacion de seguimientos (113)' }, legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@stop
