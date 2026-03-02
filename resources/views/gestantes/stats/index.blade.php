@extends('adminlte::page')

@section('title', 'Tablero Gestantes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center mb-2">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" style="width:48px;height:48px;object-fit:contain;border-radius:10px;background:#fff;padding:4px;margin-right:10px;">
        <div>
            <h1 class="m-0"><i class="fas fa-chart-network mr-1"></i> Tablero Inteligente de Gestantes</h1>
            <small class="text-muted">Analitica operativa para decisiones y seguimiento</small>
        </div>
    </div>
    <div class="mb-2">
        <button id="btnPrint" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print"></i> Imprimir vista</button>
        <button id="btnPdf" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf"></i> Exportar PDF completo</button>
    </div>
</div>
@stop

@section('content')
<div id="loadingOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.35);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:18px 22px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.2);display:flex;align-items:center;">
        <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>
        <span id="loadingText">Aplicando filtros, por favor espera...</span>
    </div>
</div>

<div class="card card-outline card-primary" id="filtersCard">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros avanzados</h3>
    </div>
    <div class="card-body">
        <form id="filtersForm">
            <div class="row">
                <div class="col-md-2">
                    <label>Modulo</label>
                    <select class="form-control form-control-sm" name="module">
                        @foreach(($filterOptions['modules'] ?? []) as $opt)
                            @php
                                $optLabel = $opt['label'];
                                if (($opt['value'] ?? '') === 'tipo1') $optLabel = 'Tipo 2';
                                if (($opt['value'] ?? '') === 'tipo3') $optLabel = 'Atencion, Monitoreo y Seguimiento';
                            @endphp
                            <option value="{{ $opt['value'] }}" {{ ($filters['module'] ?? 'todos') === $opt['value'] ? 'selected' : '' }}>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Fecha desde</label>
                    <input type="date" class="form-control form-control-sm" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label>Fecha hasta</label>
                    <input type="date" class="form-control form-control-sm" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label>Meses tendencia</label>
                    <input type="number" min="3" max="24" class="form-control form-control-sm" name="months" value="{{ $filters['months'] ?? 12 }}">
                </div>
                <div class="col-md-2">
                    <label>Identificacion</label>
                    <input type="text" class="form-control form-control-sm" name="identificacion" value="{{ $filters['identificacion'] ?? '' }}" placeholder="CC / documento">
                </div>
                <div class="col-md-2">
                    <label>Municipio (texto)</label>
                    <input type="text" class="form-control form-control-sm" name="municipio" value="{{ $filters['municipio'] ?? '' }}" placeholder="codigo o nombre">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <label>Riesgo preconcepcional</label>
                    <select class="form-control form-control-sm" name="riesgo_precon">
                        <option value="">Todos</option>
                        @foreach(($filterOptions['riesgo_precon'] ?? []) as $v)
                            <option value="{{ $v }}" {{ ($filters['riesgo_precon'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Riesgo gestacional (Tipo 3)</label>
                    <select class="form-control form-control-sm" name="riesgo_gestacional">
                        <option value="">Todos</option>
                        @foreach(($filterOptions['riesgo_gestacional'] ?? []) as $v)
                            <option value="{{ $v }}" {{ ($filters['riesgo_gestacional'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>FPP (Tipo 1)</label>
                    <select class="form-control form-control-sm" name="fpp_estado">
                        @foreach(($filterOptions['fpp_estado'] ?? []) as $opt)
                            <option value="{{ $opt['value'] }}" {{ ($filters['fpp_estado'] ?? 'todos') === $opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Semana SIV549</label>
                    <select class="form-control form-control-sm" name="semana">
                        <option value="">Todas</option>
                        @foreach(($filterOptions['semanas'] ?? []) as $v)
                            <option value="{{ $v }}" {{ ($filters['semana'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success btn-sm mr-2" type="submit"><i class="fas fa-sync"></i> Aplicar</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="btnClearFilters">Limpiar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row" id="kpisRow"></div>

<div class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-lightbulb mr-1"></i> Alertas para toma de decisiones</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0" id="insightsTable">
                <thead>
                    <tr>
                        <th>Indicador</th>
                        <th>Valor</th>
                        <th>Prioridad</th>
                        <th>Accion sugerida</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Distribucion por modulo</h3></div>
            <div class="card-body"><canvas id="chartModules" height="180"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Tendencia comparada</h3></div>
            <div class="card-body"><canvas id="chartMonthly" height="180"></canvas></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3"><div class="card card-outline card-info"><div class="card-header"><h3 class="card-title">Riesgo preconcepcional</h3></div><div class="card-body"><canvas id="chartPrecon"></canvas></div></div></div>
    <div class="col-md-3"><div class="card card-outline card-success"><div class="card-header"><h3 class="card-title">Estado FPP Tipo 1</h3></div><div class="card-body"><canvas id="chartTipo1"></canvas></div></div></div>
    <div class="col-md-3"><div class="card card-outline card-warning"><div class="card-header"><h3 class="card-title">Riesgo Tipo 3</h3></div><div class="card-body"><canvas id="chartTipo3"></canvas></div></div></div>
    <div class="col-md-3"><div class="card card-outline card-danger"><div class="card-header"><h3 class="card-title">SIV549 por semana</h3></div><div class="card-body"><canvas id="chartSiv"></canvas></div></div></div>
</div>

<div class="row" id="moduleCards"></div>

<div class="modal fade" id="statsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statsModalTitle">Detalle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="statsSummary" class="mb-3"></div>
        <div id="statsBlocks"></div>
      </div>
    </div>
  </div>
</div>

<form id="pdfForm" method="POST" action="{{ route('gestantes.stats.pdf') }}" target="_blank" style="display:none;">
    @csrf
    <input type="hidden" name="payload" id="pdfPayload">
    <input type="hidden" name="chart_images" id="pdfCharts">
</form>
@stop

@section('css')
<style>
.kpi-card{border-radius:14px;overflow:hidden;box-shadow:0 10px 20px rgba(16,24,40,.08)}
.kpi-card .small-box-footer{background:rgba(255,255,255,.2)!important}
.module-click{cursor:pointer;transition:transform .15s ease,box-shadow .2s ease}
.module-click:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(16,24,40,.12)}
@media print{
    .main-header,.main-sidebar,.content-header, #filtersCard, #btnPdf, #btnPrint, .modal, .control-sidebar{display:none!important}
    .content-wrapper,.content{margin:0!important;padding:0!important}
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const initialPayload = @json($initialPayload);
    const chartColors = ['#0ea5e9','#22c55e','#f59e0b','#ef4444','#8b5cf6','#14b8a6'];
    const state = { payload: initialPayload, charts: {} };
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = document.getElementById('loadingText');

    function viewLabel(txt){
        const t = String(txt || '');
        if (t === 'Tipo 1' || t === 'Gestantes Tipo 1') return 'Tipo 2';
        if (t === 'Tipo 3' || t === 'Gestantes Tipo 3') return 'Atencion, Monitoreo y Seguimiento';
        return t;
    }

    function esc(v){ return (v===null||v===undefined)?'':String(v).replace(/[&<>'"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[m])); }
    function num(v){ return new Intl.NumberFormat('es-CO').format(Number(v||0)); }
    function badge(p){
        const x=(p||'').toLowerCase();
        if(x.includes('alta')) return '<span class="badge badge-danger">Alta</span>';
        if(x.includes('media')) return '<span class="badge badge-warning">Media</span>';
        return '<span class="badge badge-success">Baja</span>';
    }

    function kpiBox(title, value, icon, color){
        return `<div class="col-md-3 col-sm-6"><div class="small-box ${color} kpi-card"><div class="inner"><h3>${num(value)}</h3><p>${esc(title)}</p></div><div class="icon"><i class="${icon}"></i></div><span class="small-box-footer">Actualizado con filtros</span></div></div>`;
    }

    function renderKpis(data){
        const k = data.kpis || {};
        document.getElementById('kpisRow').innerHTML = [
            kpiBox('Total General', k.total_general, 'fas fa-database', 'bg-primary'),
            kpiBox('FPP Vencidas', k.fpp_vencidas, 'fas fa-exclamation-triangle', 'bg-danger'),
            kpiBox('Precon Alto Riesgo', k.precon_alto_riesgo, 'fas fa-heartbeat', 'bg-warning'),
            kpiBox('SIV Notificados Hoy', k.siv_notificados_hoy, 'fas fa-bell', 'bg-info'),
        ].join('');
    }

    function renderInsights(data){
        const rows = (data.insights || []).map(r => `<tr><td>${esc(r.indicador)}</td><td>${esc(r.valor)}</td><td>${badge(r.prioridad)}</td><td>${esc(r.accion||r.accion_sugerida)}</td></tr>`).join('');
        document.querySelector('#insightsTable tbody').innerHTML = rows || '<tr><td colspan="4" class="text-center">Sin hallazgos para los filtros actuales.</td></tr>';
    }

    function destroyCharts(){ Object.values(state.charts).forEach(ch=>ch&&ch.destroy&&ch.destroy()); state.charts={}; }

    function buildChart(id, type, labels, valuesOrDatasets, opts={}){
        const ctx = document.getElementById(id).getContext('2d');
        const data = Array.isArray(valuesOrDatasets)
            ? { labels, datasets: [{ label:'Total', data: valuesOrDatasets, backgroundColor: chartColors, borderColor: chartColors }] }
            : { labels, datasets: valuesOrDatasets };
        state.charts[id] = new Chart(ctx, { type, data, options: Object.assign({ responsive:true, maintainAspectRatio:false }, opts) });
    }

    function renderCharts(data){
        destroyCharts();
        const c = data.charts || {};

        const moduleLabels = (c.modules?.labels || []).map(viewLabel);
        buildChart('chartModules','bar', moduleLabels, c.modules?.values || [], { plugins:{legend:{display:false}} });

        const monthlySets = (c.monthly_comparison?.datasets || []).map((d, i) => ({
            label: viewLabel(d.label),
            data: d.data || [],
            borderColor: chartColors[i % chartColors.length],
            backgroundColor: chartColors[i % chartColors.length],
            fill: false,
            tension: .25
        }));
        buildChart('chartMonthly','line', c.monthly_comparison?.labels || [], monthlySets);

        buildChart('chartPrecon','doughnut', c.precon_riesgo?.labels || [], c.precon_riesgo?.values || []);
        buildChart('chartTipo1','pie', c.tipo1_fpp?.labels || [], c.tipo1_fpp?.values || []);
        buildChart('chartTipo3','bar', c.tipo3_riesgo?.labels || [], c.tipo3_riesgo?.values || [], { plugins:{legend:{display:false}} });
        buildChart('chartSiv','line', c.siv_semana?.labels || [], [{ label:'Casos', data:c.siv_semana?.values || [], borderColor:'#ef4444', backgroundColor:'#ef4444', fill:false, tension:.2 }]);
    }

    function moduleCard(key, title, total, subtitle, color, icon){
        return `<div class="col-md-3"><div class="small-box ${color} module-click" data-modulo="${key}"><div class="inner"><h3>${num(total)}</h3><p>${esc(title)}</p><small>${esc(subtitle||'Click para detalle')}</small></div><div class="icon"><i class="${icon}"></i></div><span class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></span></div></div>`;
    }

    function renderModuleCards(data){
        const m = data.modules || {};
        const html = [
            moduleCard('preconcepcional','Preconcepcional', m.preconcepcional?.summary?.total || 0, 'Riesgo y adherencia', 'bg-info', 'fas fa-seedling'),
            moduleCard('tipo1','Tipo 2', m.tipo1?.summary?.total || 0, 'Control por FPP', 'bg-success', 'fas fa-user-check'),
            moduleCard('tipo3','Atencion, Monitoreo y Seguimiento', m.tipo3?.summary?.total || 0, 'CUPS y riesgos', 'bg-warning', 'fas fa-file-medical'),
            moduleCard('siv549','Maestro SIV549', m.siv549?.summary?.total || 0, 'Notificaciones y contacto', 'bg-danger', 'fas fa-clipboard-list')
        ];
        document.getElementById('moduleCards').innerHTML = html.join('');
        document.querySelectorAll('.module-click').forEach(el => el.addEventListener('click', ()=>loadDetail(el.dataset.modulo)));
    }

    function renderSummary(summaryObj){
        const keys = Object.keys(summaryObj || {});
        if (!keys.length) return '<em>Sin resumen.</em>';
        return `<div class="row">${keys.map(k=>`<div class="col-md-3 mb-2"><div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-info"></i></span><div class="info-box-content"><span class="info-box-text">${esc(k)}</span><span class="info-box-number">${esc(summaryObj[k])}</span></div></div></div>`).join('')}</div>`;
    }

    function renderTable(rows, title, mini, columns){
        if (!rows || !rows.length) return `<div class="card card-outline card-secondary"><div class="card-header"><h3 class="card-title">${esc(title)}</h3></div><div class="card-body"><em>Sin datos</em></div></div>`;
        const cols = (columns && columns.length) ? columns : Object.keys(rows[0]);
        const head = `<tr>${cols.map(c=>`<th>${esc(c)}</th>`).join('')}</tr>`;
        const body = rows.map(r=>`<tr>${cols.map(c=>`<td>${esc(r[c])}</td>`).join('')}</tr>`).join('');
        return `<div class="card card-outline card-secondary"><div class="card-header"><h3 class="card-title">${esc(title)}</h3></div><div class="card-body"><div class="table-responsive" style="max-height:450px;overflow:auto;"><table class="table table-bordered table-striped ${mini?'table-sm':''}"><thead>${head}</thead><tbody>${body}</tbody></table></div></div></div>`;
    }

    function setLoading(on, text){
        if (!loadingOverlay) return;
        if (loadingText && text) loadingText.textContent = text;
        loadingOverlay.style.display = on ? 'flex' : 'none';
    }

    async function loadData(){
        const form = document.getElementById('filtersForm');
        const params = new URLSearchParams(new FormData(form));
        setLoading(true, 'Aplicando filtros, por favor espera...');
        try {
            const res = await fetch(`{{ route('gestantes.stats.data') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (!json.ok) throw new Error('No se pudo actualizar tablero.');
            state.payload = json.data;
            renderAll();
        } finally {
            setLoading(false);
        }
    }

    async function loadDetail(modulo){
        const params = new URLSearchParams(new FormData(document.getElementById('filtersForm')));
        const url = `{{ url('/estadisticas/gestantes/detalle') }}/${modulo}?${params.toString()}`;
        setLoading(true, 'Cargando detalle del modulo...');
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!data.ok) { alert(data.message || 'No se pudo cargar detalle'); return; }

            document.getElementById('statsModalTitle').innerText = viewLabel(data.title || modulo);
            document.getElementById('statsSummary').innerHTML = renderSummary(data.summary || {});
            const blocksEl = document.getElementById('statsBlocks');
            blocksEl.innerHTML = '';
            (data.blocks || []).forEach(b => { blocksEl.innerHTML += renderTable(b.rows || [], b.name || 'Tabla', b.type === 'mini_table', b.columns || null); });
            $('#statsModal').modal('show');
        } finally {
            setLoading(false);
        }
    }

    function renderAll(){
        renderKpis(state.payload);
        renderInsights(state.payload);
        renderCharts(state.payload);
        renderModuleCards(state.payload);
    }

    document.getElementById('filtersForm').addEventListener('submit', async function(e){
        e.preventDefault();
        try { await loadData(); }
        catch (err) { alert(err.message || 'Error al filtrar'); }
    });

    document.getElementById('btnClearFilters').addEventListener('click', function(){
        document.getElementById('filtersForm').reset();
    });

    document.getElementById('btnPrint').addEventListener('click', function(){ window.print(); });

    document.getElementById('btnPdf').addEventListener('click', function(){
        const chartImages = {};
        Object.keys(state.charts).forEach(id => {
            const el = document.getElementById(id);
            if (el && el.toDataURL) chartImages[id] = el.toDataURL('image/png');
        });
        document.getElementById('pdfPayload').value = JSON.stringify(state.payload || {});
        document.getElementById('pdfCharts').value = JSON.stringify(chartImages || {});
        document.getElementById('pdfForm').submit();
    });

    renderAll();
})();
</script>
@stop
