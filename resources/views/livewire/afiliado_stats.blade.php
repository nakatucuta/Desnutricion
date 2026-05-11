@extends('adminlte::page')

@section('title', 'PAI - Estadisticas')

@section('content_header')
<div class="pai-head">
    <div>
        <h1 class="pai-title mb-1">Estadisticas PAI 2026</h1>
        <div class="text-muted">Replica dinamica del formato de seguimiento de coberturas</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('afiliado.stats.indicadores.index') }}" class="btn btn-outline-primary mr-2">
            <i class="fas fa-database mr-1"></i> Administrar Indicadores
        </a>
        <a href="{{ route('afiliado') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Cargue
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    <div class="card pai-card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Año</label>
                    <input type="number" min="2000" max="2100" class="form-control form-control-sm" id="paiYear" value="{{ now()->year }}">
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Escala</label>
                    <select class="form-control form-control-sm" id="paiEscala"></select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Periodo</label>
                    <select class="form-control form-control-sm" id="paiPeriodo"></select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Municipio</label>
                    <select class="form-control form-control-sm" id="paiMunicipio"></select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">IPS Vacunadora</label>
                    <select class="form-control form-control-sm" id="paiIps"></select>
                </div>
                <div class="col-lg-2 col-md-4 mb-2">
                    <label class="small text-muted mb-1">Regimen</label>
                    <select class="form-control form-control-sm" id="paiRegimen"></select>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap">
                <small class="text-muted mb-2" id="paiMeta">Cargando...</small>
                <div class="mb-2">
                    <button class="btn btn-sm btn-outline-secondary" id="paiLimpiar">Limpiar</button>
                    <button class="btn btn-sm btn-primary" id="paiAplicar">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-4 mb-2">
            <div class="pai-mini">
                <div class="pai-mini__label">Meta</div>
                <div class="pai-mini__value" id="kpiMeta">0</div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="pai-mini">
                <div class="pai-mini__label">Dosis aplicadas</div>
                <div class="pai-mini__value" id="kpiDosis">0</div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="pai-mini">
                <div class="pai-mini__label">Susceptibles</div>
                <div class="pai-mini__value" id="kpiSusceptibles">0</div>
            </div>
        </div>
    </div>

    <div class="card pai-card mt-2">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="pai-table-head">
                        <tr>
                            <th>INDICADOR</th>
                            <th>BIOLOGICOS APLICADOS</th>
                            <th class="text-right">META</th>
                            <th class="text-right">DOSIS APLICADAS</th>
                            <th class="text-right">SUSCEPTIBLES</th>
                            <th class="text-right">COBERTURA ALCANZADA %</th>
                            <th>ESTADO</th>
                        </tr>
                    </thead>
                    <tbody id="paiBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card pai-card mt-2">
        <div class="card-body py-2">
            <div class="small text-muted">Escala de evaluacion</div>
            <div class="d-flex flex-wrap gap-2 mt-1" id="paiThresholds"></div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.pai-head{display:flex;justify-content:space-between;align-items:center;gap:12px}
.pai-title{font-weight:900;color:#0f172a}
.pai-card{border:1px solid rgba(15,23,42,.08);border-radius:14px;box-shadow:0 8px 22px rgba(2,6,23,.05)}
.pai-mini{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:12px;padding:12px}
.pai-mini__label{font-size:.76rem;text-transform:uppercase;color:#64748b;font-weight:800}
.pai-mini__value{font-size:1.3rem;font-weight:900;color:#0f172a}
.pai-table-head th{font-size:.74rem;text-transform:uppercase;letter-spacing:.02em;background:#f8fbff;border-top:none}
.pai-chip{display:inline-block;padding:5px 10px;border-radius:0;font-size:.78rem;font-weight:800;border:1px solid #000;color:#000}
.chip-optimo{background:#0070c0;color:#fff}
.chip-util{background:#00b0b0;color:#000}
.chip-bajo{background:#ffff00;color:#000}
.chip-alto{background:#f4b183;color:#000}
.chip-critica{background:#ff0000;color:#000}
.chip-muy-critica{background:#c00000;color:#fff}
.chip-sin{background:#fff;color:#000}
.gap-2{gap:.5rem}
</style>
@stop

@section('js')
<script>
(function(){
    const url = @json(route('afiliado.stats.dashboard'));
    const num = new Intl.NumberFormat('es-CO');
    let currentCatalogs = null;

    function selectedVal(id){ return (document.getElementById(id)?.value || '').trim(); }

    function stateClass(estado){
        const e = (estado || '').toLowerCase();
        if (e.includes('optimo') || e.includes('optima') || e.includes('óptima')) return 'chip-optimo';
        if (e.includes('util') || e.includes('útil')) return 'chip-util';
        if (e.includes('bajo riesgo')) return 'chip-bajo';
        if (e.includes('alto riesgo')) return 'chip-alto';
        if (e.includes('muy critico') || e.includes('muy critica') || e.includes('muy crítica')) return 'chip-muy-critica';
        if (e.includes('critico') || e.includes('critica') || e.includes('crítica')) return 'chip-critica';
        if (e.includes('sin reporte')) return 'chip-sin';
        return 'chip-sin';
    }

    function fillSelect(id, options, valueKey = null, textKey = null, selected = ''){
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = '';
        (options || []).forEach(function(item){
            const value = valueKey ? String(item[valueKey] ?? '') : String(item ?? '');
            const text = textKey ? String(item[textKey] ?? value) : String(item ?? '');
            const op = document.createElement('option');
            op.value = value;
            op.textContent = text;
            if (selected !== '' && String(selected) === value) op.selected = true;
            el.appendChild(op);
        });
        if (selected === '' && el.options.length > 0) el.selectedIndex = 0;
    }

    function periodOptionsForEscala(escala){
        if (!currentCatalogs || !currentCatalogs.periodos) return [];
        const p = currentCatalogs.periodos[escala] || {};
        return Object.keys(p);
    }

    function renderRows(rows){
        const body = document.getElementById('paiBody');
        if (!body) return;
        body.innerHTML = '';
        (rows || []).forEach(function(r){
            const tr = document.createElement('tr');
            const pct = Number(r.cobertura || 0) * 100;
            tr.innerHTML =
                '<td>' + (r.indicador || '') + '</td>' +
                '<td>' + (r.biologico || '') + '</td>' +
                '<td class="text-right">' + num.format(Number(r.meta || 0)) + '</td>' +
                '<td class="text-right">' + num.format(Number(r.dosis_aplicadas || 0)) + '</td>' +
                '<td class="text-right">' + num.format(Number(r.susceptibles || 0)) + '</td>' +
                '<td class="text-right">' + pct.toFixed(1) + '%</td>' +
                '<td><span class="pai-chip ' + stateClass(r.estado) + '">' + (r.estado || '') + '</span></td>';
            body.appendChild(tr);
        });
    }

    function renderThresholds(th){
        const el = document.getElementById('paiThresholds');
        if (!el) return;
        const map = [
            ['chip-optimo', 'OPTIMO', th?.optima || '>=23.7%'],
            ['chip-util', 'UTIL', th?.util || '23.69% - 20.00%'],
            ['chip-bajo', 'BAJO RIESGO', th?.bajo_riesgo || '19.99% - 16.00%'],
            ['chip-alto', 'ALTO RIESGO', th?.alto_riesgo || '15.99% - 13.00%'],
            ['chip-critica', 'CRITICO', th?.critica || '12.99% - 10.69%'],
            ['chip-muy-critica', 'MUY CRITICO', th?.muy_critica || '10.59% - 0.10%'],
            ['chip-sin', 'SIN REPORTE', th?.sin_reporte || '0']
        ];
        el.innerHTML = map.map(x => '<span class="pai-chip ' + x[0] + '">' + x[1] + ': ' + x[2] + '</span>').join('');
    }

    function load(applyCurrent){
        const qs = new URLSearchParams();
        qs.set('year', selectedVal('paiYear') || new Date().getFullYear());
        if (applyCurrent) {
            qs.set('municipio', selectedVal('paiMunicipio'));
            qs.set('ips_id', selectedVal('paiIps'));
            qs.set('regimen', selectedVal('paiRegimen'));
            qs.set('escala', selectedVal('paiEscala'));
            qs.set('periodo', selectedVal('paiPeriodo'));
        }

        document.getElementById('paiMeta').textContent = 'Cargando reporte...';
        document.getElementById('paiAplicar').disabled = true;

        fetch(url + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function(resp){
                if (!resp || !resp.ok) throw new Error('No se pudo cargar');

                currentCatalogs = resp.catalogs || null;
                fillSelect('paiEscala', currentCatalogs?.escalas || [], null, null, resp.filters?.escala || '');
                const escalaVal = selectedVal('paiEscala');
                fillSelect('paiPeriodo', periodOptionsForEscala(escalaVal), null, null, resp.filters?.periodo || '');
                fillSelect('paiMunicipio', currentCatalogs?.municipios || [], null, null, resp.filters?.municipio || '');
                fillSelect('paiIps', currentCatalogs?.ips || [], 'id', 'name', String(resp.filters?.ips_id || ''));
                fillSelect('paiRegimen', currentCatalogs?.regimenes || [], null, null, resp.filters?.regimen || '');

                const months = (resp.period?.month_labels || []).join(', ');
                document.getElementById('paiMeta').textContent =
                    'Periodo: ' + (resp.filters?.periodo || '') +
                    ' (' + months + ') | Rango: ' + (resp.period?.start_date || '-') + ' a ' + (resp.period?.end_date || '-') +
                    ' | Meta: ' + (resp.flags?.meta_source || 'N/D') +
                    ' | Generado: ' + (resp.generated_at || '-');

                if (resp.flags && resp.flags.combo_has_data === false) {
                    document.getElementById('paiMeta').textContent +=
                        ' | Sin registros para esta combinacion de Municipio + IPS + Regimen.';
                }

                document.getElementById('kpiMeta').textContent = num.format(Number(resp.totals?.meta || 0));
                document.getElementById('kpiDosis').textContent = num.format(Number(resp.totals?.dosis_aplicadas || 0));
                document.getElementById('kpiSusceptibles').textContent = num.format(Number(resp.totals?.susceptibles || 0));

                renderRows(resp.rows || []);
                renderThresholds(resp.thresholds || {});
            })
            .catch(function(){
                document.getElementById('paiMeta').textContent = 'Error consultando las estadisticas PAI.';
            })
            .finally(function(){
                document.getElementById('paiAplicar').disabled = false;
            });
    }

    document.getElementById('paiEscala').addEventListener('change', function(){
        fillSelect('paiPeriodo', periodOptionsForEscala(selectedVal('paiEscala')));
    });

    document.getElementById('paiAplicar').addEventListener('click', function(e){
        e.preventDefault();
        load(true);
    });

    document.getElementById('paiLimpiar').addEventListener('click', function(e){
        e.preventDefault();
        document.getElementById('paiYear').value = new Date().getFullYear();
        load(false);
    });

    load(false);
})();
</script>
@stop
