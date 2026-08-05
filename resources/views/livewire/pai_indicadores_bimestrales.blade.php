@extends('adminlte::page')

@section('title', 'PAI - Informe bimestral')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="pai-title mb-1">Informe de indicadores bimestral PAI</h1>
        <div class="text-muted">Dosis aplicadas unicamente a usuarios de IPS primarias referenciadas.</div>
    </div>
    <a href="{{ route('afiliado.stats.view') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver a coberturas</a>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 mb-2">
                    <label for="bimonthlyYear">Vigencia</label>
                    <input id="bimonthlyYear" type="number" min="2000" max="2100" class="form-control" value="{{ now()->year }}">
                </div>
                <div class="col-lg-3 col-md-4 mb-2">
                    <label for="bimonthlyPeriod">Bimestre</label>
                    <select id="bimonthlyPeriod" class="form-control">
                        <option value="1">Enero - Febrero</option>
                        <option value="2">Marzo - Abril</option>
                        <option value="3">Mayo - Junio</option>
                        <option value="4">Julio - Agosto</option>
                        <option value="5">Septiembre - Octubre</option>
                        <option value="6">Noviembre - Diciembre</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label for="bimonthlyMunicipio">Municipio</label>
                    <select id="bimonthlyMunicipio" class="form-control"><option value="">Todos</option></select>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <label for="bimonthlyIps">IPS vacunadora</label>
                    <select id="bimonthlyIps" class="form-control"><option value="">Todas</option></select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label for="bimonthlyRegimen">Regimen</label>
                    <select id="bimonthlyRegimen" class="form-control">
                        <option value="">Todos</option>
                        <option value="SUBSIDIADO">Subsidiado</option>
                        <option value="CONTRIBUTIVO">Contributivo</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
                <small id="bimonthlyMeta" class="text-muted">Cargando informe...</small>
                <div>
                    <button type="button" id="bimonthlyApply" class="btn btn-primary mr-2"><i class="fas fa-sync-alt mr-1"></i> Consultar</button>
                    <a id="bimonthlyExport" href="#" class="btn btn-success"><i class="fas fa-file-excel mr-1"></i> Exportar Excel</a>
                </div>
            </div>
        </div>
    </div>

    <div id="bimonthlyLoading" class="alert alert-light border d-none align-items-center" role="status">
        <span class="spinner-border spinner-border-sm text-primary mr-2" aria-hidden="true"></span>
        <span>Cargando datos del informe...</span>
    </div>
    <div id="bimonthlyResults"></div>
</div>
@stop

@push('css')
<style>
    .pai-bimonthly-card{border:0;border-radius:14px;overflow:hidden}
    .pai-bimonthly-card .card-header{background:#eff6ff}
    .pai-bimonthly-table th{white-space:nowrap;font-size:.78rem}
    .pai-bimonthly-table td{vertical-align:middle}
    .pai-bimonthly-table .pai-month-1{background:#f8fbff}
    .pai-bimonthly-table .pai-month-2{background:#f8fdf9}
    .pai-bimonthly-table .pai-month-divider{border-left:2px solid #cbd5e1!important}
    .pai-bimonthly-table thead .pai-month-1{background:#eff6ff;color:#334155}
    .pai-bimonthly-table thead .pai-month-2{background:#f0fdf4;color:#334155}
    .pai-bimonthly-table thead th{border-bottom:1px solid #94a3b8}
    .pai-bimonthly-pct{font-weight:700}
    .pai-bimonthly-pct--none{color:#64748b}
    .pai-bimonthly-pct--low{color:#b91c1c}
    .pai-bimonthly-pct--ok{color:#166534}
    .pai-bimonthly-pct--high{color:#1d4ed8}
</style>
@endpush

@push('js')
<script>
(function () {
    const dataUrl = @json(route('afiliado.stats.bimonthly.data'));
    const exportUrl = @json(route('afiliado.stats.bimonthly.export'));
    const refs = {
        year: document.getElementById('bimonthlyYear'),
        period: document.getElementById('bimonthlyPeriod'),
        municipio: document.getElementById('bimonthlyMunicipio'),
        ips: document.getElementById('bimonthlyIps'),
        regimen: document.getElementById('bimonthlyRegimen'),
        meta: document.getElementById('bimonthlyMeta'),
        results: document.getElementById('bimonthlyResults'),
        apply: document.getElementById('bimonthlyApply'),
        export: document.getElementById('bimonthlyExport'),
        loading: document.getElementById('bimonthlyLoading')
    };
    let loading = false;
    let catalogIps = [];

    function esc(value) { return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c])); }
    function n(value) { return new Intl.NumberFormat('es-CO').format(Number(value || 0)); }
    function pct(value) {
        if (value === null || value === undefined) return '<span class="pai-bimonthly-pct pai-bimonthly-pct--none">N/A</span>';
        const number = Number(value);
        const cls = number > 100 ? 'high' : (number >= 90 ? 'ok' : 'low');
        return '<span class="pai-bimonthly-pct pai-bimonthly-pct--' + cls + '">' + number.toFixed(2) + '%</span>';
    }
    function params() {
        const p = new URLSearchParams({year: refs.year.value, bimester: refs.period.value});
        if (refs.municipio.value) p.set('municipio', refs.municipio.value);
        if (refs.ips.value) p.set('ips_code', refs.ips.value);
        if (refs.regimen.value) p.set('regimen', refs.regimen.value);
        return p;
    }
    function fillSelect(select, rows, labelKey, valueKey, emptyLabel) {
        const old = select.value;
        select.innerHTML = '<option value="">' + emptyLabel + '</option>' + rows.map(row => '<option value="' + esc(row[valueKey]) + '">' + esc(row[labelKey]) + '</option>').join('');
        if ([...select.options].some(o => o.value === old)) select.value = old;
    }
    function refreshIpsForMunicipio() {
        const selected = refs.municipio.value.toUpperCase();
        const rows = selected ? catalogIps.filter(row => String(row.municipio || '').toUpperCase() === selected) : catalogIps;
        fillSelect(refs.ips, rows, 'name', 'code', 'Todas');
        if (refs.ips.value && !rows.some(row => String(row.code) === refs.ips.value)) refs.ips.value = '';
    }
    function render(data) {
        const groups = data.groups || [];
        catalogIps = data.catalogs?.ips || data.ips || [];
        const municipalities = (data.catalogs?.municipalities || []).map(value => ({value: value, label: value}));
        fillSelect(refs.municipio, municipalities, 'label', 'value', 'Todos');
        refreshIpsForMunicipio();
        const labels = data.period?.labels || ['Mes 1', 'Mes 2'];
        refs.meta.textContent = 'Periodo: ' + labels.join(' - ') + ' | ' + groups.length + ' IPS | Numerador restringido a IPS primarias referenciadas | Generado: ' + (data.generated_at || '');
        refs.export.href = exportUrl + '?' + params().toString();
        if (!groups.length) {
            refs.results.innerHTML = '<div class="alert alert-info">No hay metas o dosis para los filtros seleccionados.</div>';
            return;
        }
        refs.results.innerHTML = groups.map(group => {
            const ips = group.ips || {};
            const rows = group.rows || [];
            const body = rows.map(row => '<tr><td><strong>' + esc(row.vaccine) + '</strong></td><td class="pai-month-1">' + n(row.month_1.programmed) + '</td><td class="pai-month-1">' + n(row.month_1.applied) + '</td><td class="pai-month-1">' + pct(row.month_1.percentage) + '</td><td class="pai-month-2 pai-month-divider">' + n(row.month_2.programmed) + '</td><td class="pai-month-2">' + n(row.month_2.applied) + '</td><td class="pai-month-2">' + pct(row.month_2.percentage) + '</td></tr>').join('');
            const totals = group.totals || {};
            const totalRow = '<tr class="font-weight-bold table-info"><td>TOTAL IPS</td><td class="pai-month-1">' + n(totals.month_1?.programmed) + '</td><td class="pai-month-1">' + n(totals.month_1?.applied) + '</td><td class="pai-month-1">' + pct(totals.month_1?.percentage) + '</td><td class="pai-month-2 pai-month-divider">' + n(totals.month_2?.programmed) + '</td><td class="pai-month-2">' + n(totals.month_2?.applied) + '</td><td class="pai-month-2">' + pct(totals.month_2?.percentage) + '</td></tr>';
            return '<div class="card pai-bimonthly-card shadow-sm mb-4"><div class="card-header d-flex justify-content-between align-items-center flex-wrap"><div><strong>' + esc(ips.name) + '</strong> <span class="text-muted ml-2">Codigo: ' + esc(ips.code) + ' - ' + esc(ips.municipio) + '</span></div><small class="text-muted">' + n(ips.referenced_primary_count) + ' IPS primarias referenciadas</small></div><div class="table-responsive"><table class="table table-sm table-striped pai-bimonthly-table mb-0"><thead><tr><th rowspan="2">Vacuna</th><th colspan="3" class="text-center pai-month-1">' + esc(labels[0]) + '</th><th colspan="3" class="text-center pai-month-2 pai-month-divider">' + esc(labels[1]) + '</th></tr><tr><th class="pai-month-1">Actividades programadas</th><th class="pai-month-1">Actividades realizadas</th><th class="pai-month-1">Porcentaje de cobertura</th><th class="pai-month-2 pai-month-divider">Actividades programadas</th><th class="pai-month-2">Actividades realizadas</th><th class="pai-month-2">Porcentaje de cobertura</th></tr></thead><tbody>' + body + totalRow + '</tbody></table></div></div>';
        }).join('');
    }
    async function load() {
        if (loading) return;
        loading = true;
        refs.apply.disabled = true;
        refs.loading.classList.remove('d-none');
        refs.loading.classList.add('d-flex');
        refs.meta.textContent = 'Consultando...';
        try {
            const response = await fetch(dataUrl + '?' + params().toString(), {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'No fue posible consultar el informe.');
            render(data);
        } catch (error) {
            refs.results.innerHTML = '<div class="alert alert-danger">' + esc(error.message) + '</div>';
            refs.meta.textContent = 'Error al consultar';
        } finally {
            loading = false;
            refs.apply.disabled = false;
            refs.loading.classList.remove('d-flex');
            refs.loading.classList.add('d-none');
        }
    }
    refs.apply.addEventListener('click', load);
    refs.period.addEventListener('change', load);
    refs.municipio.addEventListener('change', function () { refreshIpsForMunicipio(); load(); });
    load();
})();
</script>
@endpush
