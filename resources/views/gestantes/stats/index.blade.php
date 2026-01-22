@extends('adminlte::page')

@section('title', 'Estadísticas Gestantes')

@section('content_header')
    <h1><i class="fas fa-chart-bar mr-1"></i> Estadísticas Gestantes</h1>
@stop

@section('content')
<div class="row">

    {{-- CARD: Preconcepcional --}}
    <div class="col-md-3">
        <div class="small-box bg-info stats-card" data-modulo="preconcepcional" style="cursor:pointer;">
            <div class="inner">
                <h3>{{ $stats['preconcepcional']['count'] }}</h3>
                <p>Preconcepcional</p>
                <small>
                    Alto: <b>{{ $preconExtra['alto_riesgo'] }}</b> |
                    Medio: <b>{{ $preconExtra['medio_riesgo'] }}</b> |
                    Bajo: <b>{{ $preconExtra['bajo_riesgo'] }}</b>
                </small>
            </div>
            <div class="icon">
                <i class="fas fa-seedling"></i>
            </div>
            <a class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- CARD: Tipo 1 --}}
    <div class="col-md-3">
        <div class="small-box bg-success stats-card" data-modulo="tipo1" style="cursor:pointer;">
            <div class="inner">
                <h3>{{ $stats['tipo1']['count'] }}</h3>
                <p>Gestantes Tipo 1</p>
                <small>
                    Próx 4 sem: <b>{{ $tipo1Extra['proximas_4_semanas'] }}</b> |
                    Vencidas: <b>{{ $tipo1Extra['vencidas'] }}</b>
                </small>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <a class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- CARD: Tipo 3 --}}
    <div class="col-md-3">
        <div class="small-box bg-warning stats-card" data-modulo="tipo3" style="cursor:pointer;">
            <div class="inner">
                <h3>{{ $stats['tipo3']['count'] }}</h3>
                <p>Gestantes Tipo 3</p>
                <small>Controles / tecnologías registradas</small>
            </div>
            <div class="icon">
                <i class="fas fa-file-medical"></i>
            </div>
            <a class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- CARD: MaestroSIV549 --}}
    <div class="col-md-3">
        <div class="small-box bg-danger stats-card" data-modulo="siv549" style="cursor:pointer;">
            <div class="inner">
                <h3>{{ $stats['siv549']['count'] }}</h3>
                <p>MaestroSIV549</p>
                <small>Asignaciones / casos cargados</small>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

</div>

{{-- GRAFICA: resumen por módulo --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Cantidad por módulo</h3>
    </div>
    <div class="card-body">
        <canvas id="chartModules" height="120"></canvas>
    </div>
</div>

{{-- GRAFICAS: tendencia por mes --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Tipo 1 - Registros por mes</h3>
            </div>
            <div class="card-body">
                <canvas id="chartTipo1Monthly" height="140"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Tipo 3 - Registros por mes</h3>
            </div>
            <div class="card-body">
                <canvas id="chartTipo3Monthly" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Preconcepcional - Registros por mes</h3>
            </div>
            <div class="card-body">
                <canvas id="chartPreconMonthly" height="140"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> SIV549 - Registros por mes</h3>
            </div>
            <div class="card-body">
                <canvas id="chart549Monthly" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- GRAFICA: Precon por riesgo --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Preconcepcional por Riesgo</h3>
            </div>
            <div class="card-body">
                <canvas id="chartPreconRiesgo" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETALLE --}}
<div class="modal fade" id="statsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="statsModalTitle">Detalle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        {{-- Resumen dinámico --}}
        <div id="statsSummary" class="mb-3"></div>

        {{-- Bloques dinámicos --}}
        <div id="statsBlocks"></div>

      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4"></script>

<script>
(function(){

    function escapeHtml(v) {
        if (v === null || v === undefined) return '';
        return String(v)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderSummary(summaryObj) {
        if (!summaryObj) return '';
        const items = Object.keys(summaryObj).map(k => {
            return `<div class="col-md-3 mb-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-info-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">${escapeHtml(k)}</span>
                                <span class="info-box-number">${escapeHtml(summaryObj[k])}</span>
                            </div>
                        </div>
                    </div>`;
        }).join('');
        return `<div class="row">${items}</div>`;
    }

    function renderTable(rows, title = 'Tabla', mini = false, columns = null) {
        if (!rows || rows.length === 0) {
            return `<div class="card card-outline card-secondary">
                        <div class="card-header"><h3 class="card-title">${escapeHtml(title)}</h3></div>
                        <div class="card-body"><em>Sin datos</em></div>
                    </div>`;
        }

        const cols = columns && columns.length ? columns : Object.keys(rows[0]);

        const thead = `<tr>${cols.map(c => `<th>${escapeHtml(c)}</th>`).join('')}</tr>`;
        const tbody = rows.map(r => {
            return `<tr>${cols.map(c => `<td>${escapeHtml(r[c])}</td>`).join('')}</tr>`;
        }).join('');

        return `<div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">${escapeHtml(title)}</h3></div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height:520px; overflow:auto;">
                            <table class="table table-bordered table-striped ${mini ? 'table-sm' : ''}">
                                <thead>${thead}</thead>
                                <tbody>${tbody}</tbody>
                            </table>
                        </div>
                    </div>
                </div>`;
    }

    // =========================
    // GRAFICAS
    // =========================
    const chartModules = @json($chartModules);
    new Chart(document.getElementById('chartModules').getContext('2d'), {
        type: 'bar',
        data: {
            labels: chartModules.labels,
            datasets: [{ label: 'Cantidad', data: chartModules.values }]
        },
        options: { responsive: true, legend: { display: false } }
    });

    const t1 = @json($chartTipo1Monthly);
    new Chart(document.getElementById('chartTipo1Monthly').getContext('2d'), {
        type: 'line',
        data: { labels: t1.labels, datasets: [{ label: 'Registros', data: t1.values }] },
        options: { responsive: true }
    });

    const t3 = @json($chartTipo3Monthly);
    new Chart(document.getElementById('chartTipo3Monthly').getContext('2d'), {
        type: 'line',
        data: { labels: t3.labels, datasets: [{ label: 'Registros', data: t3.values }] },
        options: { responsive: true }
    });

    const pre = @json($chartPreconMonthly);
    new Chart(document.getElementById('chartPreconMonthly').getContext('2d'), {
        type: 'line',
        data: { labels: pre.labels, datasets: [{ label: 'Registros', data: pre.values }] },
        options: { responsive: true }
    });

    const s549 = @json($chart549Monthly);
    new Chart(document.getElementById('chart549Monthly').getContext('2d'), {
        type: 'line',
        data: { labels: s549.labels, datasets: [{ label: 'Registros', data: s549.values }] },
        options: { responsive: true }
    });

    const preR = @json($chartPreconRiesgo);
    new Chart(document.getElementById('chartPreconRiesgo').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: preR.labels,
            datasets: [{ data: preR.values }]
        },
        options: { responsive: true }
    });

    // =========================
    // CLICK en cards => DETALLE
    // =========================
    document.querySelectorAll('.stats-card').forEach(el => {
        el.addEventListener('click', async () => {
            const modulo = el.getAttribute('data-modulo');
            const url = "{{ url('/estadisticas/gestantes/detalle') }}/" + modulo;

            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            if (!data.ok) {
                alert(data.message || 'No se pudo cargar el detalle');
                return;
            }

            document.getElementById('statsModalTitle').innerText = data.title || ('Detalle: ' + modulo);

            document.getElementById('statsSummary').innerHTML = renderSummary(data.summary || {});

            const blocksEl = document.getElementById('statsBlocks');
            blocksEl.innerHTML = '';

            (data.blocks || []).forEach(b => {
                if (b.type === 'mini_table') {
                    blocksEl.innerHTML += renderTable(b.rows || [], b.name || 'Tabla', true, b.columns || null);
                } else {
                    blocksEl.innerHTML += renderTable(b.rows || [], b.name || 'Tabla', false, null);
                }
            });

            $('#statsModal').modal('show');
        });
    });

})();
</script>
@stop
