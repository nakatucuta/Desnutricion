@extends('adminlte::page')

@section('title', 'Inteligencia 113/412')

@section('content')
<div class="container-fluid" style="padding-top:1rem; padding-bottom:1.5rem;">
    <div style="border-radius:20px; color:#fff; padding:1.1rem 1.2rem; margin-bottom:1rem; background:linear-gradient(130deg,#0d5f75,#14859a 58%,#21a364); display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
        <div style="display:flex; gap:.8rem; align-items:center;">
            <div style="width:64px;height:64px;border-radius:14px;background:rgba(255,255,255,.16);display:flex;align-items:center;justify-content:center;">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo" style="width:42px;height:auto;">
            </div>
            <div>
                <div style="font-size:.74rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Inteligencia operacional</div>
                <h1 style="font-size:1.45rem;margin:0;font-weight:800;">Centro Inteligente 113 + 412</h1>
                <div style="opacity:.9;">Semaforo de riesgo, alertas 48h, ranking, auditoria y calidad de dato.</div>
            </div>
        </div>
        <form method="GET" class="d-flex" style="gap:.5rem; align-items:center; flex-wrap:wrap;">
            <input type="number" name="anio" value="{{ $year }}" min="2020" max="2100" class="form-control form-control-sm" style="width:110px;">
            <input type="text" name="documento" value="{{ $documento }}" class="form-control form-control-sm" placeholder="Documento timeline" style="width:220px;">
            <button class="btn btn-light btn-sm"><i class="fas fa-sync-alt mr-1"></i>Actualizar</button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card" style="border-radius:14px;">
                <div class="card-header"><strong>Semaforo de riesgo</strong></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Rojo</span><span class="badge badge-danger">{{ $riesgos['totales']['rojo'] ?? 0 }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Amarillo</span><span class="badge badge-warning">{{ $riesgos['totales']['amarillo'] ?? 0 }}</span></div>
                    <div class="d-flex justify-content-between"><span>Verde</span><span class="badge badge-success">{{ $riesgos['totales']['verde'] ?? 0 }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-8 mb-3">
            <div class="card" style="border-radius:14px;">
                <div class="card-header"><strong>Alertas preventivas 48h</strong></div>
                <div class="card-body" style="max-height:180px; overflow:auto;">
                    @forelse($alertas48h as $a)
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eef3f5; padding:.35rem 0;">
                            <span><strong>{{ trim($a->paciente) }}</strong> ({{ $a->modulo }})</span>
                            <span>{{ $a->fecha_proximo_control }}</span>
                        </div>
                    @empty
                        <span class="text-muted">No hay alertas en las proximas 48 horas.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card" style="border-radius:14px;">
                <div class="card-header"><strong>Grafico semaforo</strong></div>
                <div class="card-body">
                    <canvas id="chartSemaforo" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <div class="card" style="border-radius:14px;">
                <div class="card-header"><strong>Tendencia mensual de cumplimiento</strong></div>
                <div class="card-body">
                    <canvas id="chartCumplimiento" height="95"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="border-radius:14px;">
        <div class="card-header"><strong>Ranking grafico de prestadores con casos sin seguimiento</strong></div>
        <div class="card-body">
            <canvas id="chartRanking" height="100"></canvas>
        </div>
    </div>

    <div class="card mb-3" style="border-radius:14px;">
        <div class="card-header"><strong>Panel sin seguimiento por prestador</strong></div>
        <div class="card-body p-0">
            <div style="overflow:auto;">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Modulo</th><th>Prestador</th><th>Asignados</th><th>Con seguimiento</th><th>Sin seguimiento</th><th>% cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ranking as $r)
                            <tr>
                                <td>{{ $r->modulo }}</td>
                                <td>{{ $r->prestador }}</td>
                                <td>{{ $r->asignados }}</td>
                                <td>{{ $r->con_seguimiento }}</td>
                                <td><span class="badge badge-danger">{{ $r->sin_seguimiento }}</span></td>
                                <td>{{ number_format($r->cumplimiento, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="border-radius:14px;">
        <div class="card-header"><strong>Indicadores ejecutivos mensuales</strong></div>
        <div class="card-body p-0">
            <div style="overflow:auto;">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Mes</th><th>Total</th><th>Cerrados</th><th>% Cumplimiento</th><th>% Cierre oportuno</th><th>Tiempo prom. gestion (dias)</th></tr>
                    </thead>
                    <tbody>
                        @foreach($indicadores as $i)
                            <tr>
                                <td>{{ $i->mes }}</td>
                                <td>{{ $i->total }}</td>
                                <td>{{ $i->cerrados }}</td>
                                <td>{{ number_format($i->cumplimiento, 2) }}%</td>
                                <td>{{ number_format($i->cierre_oportuno, 2) }}%</td>
                                <td>{{ number_format($i->tiempo_promedio, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 mb-3">
            <div class="card" style="border-radius:14px;">
                <div class="card-header"><strong>Top casos por riesgo</strong></div>
                <div class="card-body p-0">
                    <div style="overflow:auto; max-height:420px;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Riesgo</th><th>Paciente</th><th>Doc</th><th>Modulo</th><th>Clasificacion</th><th>Dias control</th></tr></thead>
                            <tbody>
                                @foreach(($riesgos['rows'] ?? collect())->take(120) as $row)
                                    <tr>
                                        <td>
                                            @if($row->riesgo === 'rojo') <span class="badge badge-danger">Rojo</span>
                                            @elseif($row->riesgo === 'amarillo') <span class="badge badge-warning">Amarillo</span>
                                            @else <span class="badge badge-success">Verde</span> @endif
                                        </td>
                                        <td>{{ $row->paciente }}</td>
                                        <td>{{ $row->documento }}</td>
                                        <td>{{ $row->modulo }}</td>
                                        <td>{{ $row->clasificacion }}</td>
                                        <td>{{ $row->dias_control }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 mb-3">
            <div class="card" style="border-radius:14px;">
                <div class="card-header"><strong>Timeline clinico + auditoria por documento</strong></div>
                <div class="card-body" style="max-height:420px; overflow:auto;">
                    @if($documento === '')
                        <span class="text-muted">Ingresa un documento arriba para ver timeline unificado de 113/412.</span>
                    @else
                        @forelse($timeline as $t)
                            <div style="border-left:3px solid #dbe7ea; padding:.35rem .55rem; margin-bottom:.45rem;">
                                <div style="font-size:.79rem; color:#5f7b83;">{{ $t->fecha_evento }} | {{ $t->modulo }} | {{ $t->evento }}</div>
                                <div><strong>{{ trim($t->paciente) }}</strong> - Doc {{ $t->documento }}</div>
                                <div style="font-size:.83rem;">Actor: {{ $t->actor }} | Clasif: {{ $t->clasificacion }} | Prox: {{ $t->fecha_proximo_control }}</div>
                                @if(!empty($t->motivo_reapuertura))
                                    <div style="font-size:.82rem; color:#996400;">Reapertura: {{ $t->motivo_reapuertura }}</div>
                                @endif
                            </div>
                        @empty
                            <span class="text-muted">No se encontraron eventos para el documento consultado.</span>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        const semaforoData = {
            rojo: @json((int)($riesgos['totales']['rojo'] ?? 0)),
            amarillo: @json((int)($riesgos['totales']['amarillo'] ?? 0)),
            verde: @json((int)($riesgos['totales']['verde'] ?? 0)),
        };

        const indicadores = @json($indicadores->values());
        const rankingTop = @json($ranking->take(10)->values());

        const mesLabels = indicadores.map(i => 'Mes ' + i.mes);
        const cumplimientoData = indicadores.map(i => Number(i.cumplimiento || 0));
        const oportunidadData = indicadores.map(i => Number(i.cierre_oportuno || 0));
        const tiempoData = indicadores.map(i => Number(i.tiempo_promedio || 0));

        const rankingLabels = rankingTop.map(r => ((r.prestador || 'N/D') + ' (' + r.modulo + ')'));
        const rankingData = rankingTop.map(r => Number(r.sin_seguimiento || 0));

        new Chart(document.getElementById('chartSemaforo'), {
            type: 'doughnut',
            data: {
                labels: ['Rojo', 'Amarillo', 'Verde'],
                datasets: [{
                    data: [semaforoData.rojo, semaforoData.amarillo, semaforoData.verde],
                    backgroundColor: ['#dc3545', '#f0ad4e', '#28a745'],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });

        new Chart(document.getElementById('chartCumplimiento'), {
            type: 'line',
            data: {
                labels: mesLabels,
                datasets: [
                    {
                        label: '% Cumplimiento',
                        data: cumplimientoData,
                        borderColor: '#0f7c92',
                        backgroundColor: 'rgba(15,124,146,.15)',
                        tension: .25,
                        fill: true
                    },
                    {
                        label: '% Cierre oportuno',
                        data: oportunidadData,
                        borderColor: '#23a05f',
                        backgroundColor: 'rgba(35,160,95,.12)',
                        tension: .25,
                        fill: true
                    },
                    {
                        label: 'Tiempo prom. (dias)',
                        data: tiempoData,
                        borderColor: '#6f42c1',
                        backgroundColor: 'rgba(111,66,193,.1)',
                        tension: .25,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, max: 100, title: { display: true, text: 'Porcentaje' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Dias' } }
                }
            }
        });

        new Chart(document.getElementById('chartRanking'), {
            type: 'bar',
            data: {
                labels: rankingLabels,
                datasets: [{
                    label: 'Casos sin seguimiento',
                    data: rankingData,
                    backgroundColor: '#d9534f'
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    })();
</script>
@stop
