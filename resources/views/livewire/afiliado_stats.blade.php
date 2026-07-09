@extends('adminlte::page')

@section('title', 'PAI - Estadisticas')

@section('content_header')
<div class="pai-head">
    <div>
        <h1 class="pai-title mb-1">Estadisticas PAI 2026</h1>
        <div class="text-muted">Replica dinamica del formato de seguimiento de coberturas</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('afiliado.stats.settings.index') }}" class="btn btn-outline-dark mr-2">
            <i class="fas fa-sliders-h mr-1"></i> Parametrizaciones PAI
        </a>
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
    <div class="pai-dashboard-shell">
    <div class="pai-dashboard-overlay" id="paiDashboardOverlay" aria-hidden="true">
        <div class="pai-dashboard-overlay__card">
            <div class="spinner-border text-primary mb-3" role="status" aria-label="Cargando"></div>
            <div class="pai-dashboard-overlay__title">Recalculando cobertura</div>
            <div class="pai-dashboard-overlay__text">Estamos ajustando los filtros y volviendo a consolidar el detalle. Un momento por favor.</div>
        </div>
    </div>
    <div class="card pai-card">
        <div class="card-body">
            <div class="pai-filters__hero">
                <div>
                    <div class="pai-kicker">Modo interactivo</div>
                    <h2 class="pai-section-title mb-1">Filtros inteligentes de cobertura</h2>
                    <div class="pai-section-subtitle">Selecciona paso a paso y el tablero se recalcula en vivo con el cruce exacto de municipio, sede y régimen.</div>
                </div>
                <div class="pai-filters__signal">
                    <span class="pai-signal-dot"></span>
                    <span id="paiSelectionCount">0 filtros activos</span>
                </div>
            </div>
            <div class="pai-progress mt-3 mb-3">
                <div class="pai-progress__track">
                    <div class="pai-progress__fill" id="paiProgressFill"></div>
                </div>
                <div class="pai-progress__labels">
                    <span>Año</span>
                    <span>Escala</span>
                    <span>Periodo</span>
                    <span>Municipio</span>
                    <span>IPS</span>
                    <span>Régimen</span>
                </div>
            </div>
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
                <div class="mb-2 d-flex flex-wrap align-items-center justify-content-end gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="paiLimpiar">Limpiar</button>
                    <button class="btn btn-sm btn-primary" id="paiAplicar">Aplicar</button>
                </div>
            </div>
            <div class="pai-tags mt-3" id="paiSelectionTags"></div>
            <div class="pai-progress-note mt-2">La vista se actualiza automáticamente mientras eliges filtros, pero siempre puedes forzar el recálculo con <strong>Aplicar</strong>.</div>
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
            <div class="pai-table-toolbar">
                <div>
                    <div class="pai-table-toolbar__title">Indicadores</div>
                    <div class="pai-table-toolbar__hint">Filtra la tabla sin recalcular la consulta.</div>
                </div>
                <label class="pai-toggle mb-0" for="paiTracerToggle">
                    <input type="checkbox" id="paiTracerToggle">
                    <span class="pai-toggle__track" aria-hidden="true"><span class="pai-toggle__thumb"></span></span>
                    <span class="pai-toggle__label">Solo trazadores</span>
                </label>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="pai-table-head">
                        <tr>
                            <th>INDICADOR</th>
                            <th>BIOLOGICOS APLICADOS</th>
                            <th class="text-center">DOSIS META</th>
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
</div>

<div class="modal fade" id="paiDoseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="paiDoseModalTitle">Detalle de dosis aplicadas</h5>
                    <div class="text-muted small" id="paiDoseModalSub">Pacientes y vacunaciones relacionadas</div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 mb-3" id="paiDoseModalChips"></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="pai-table-head">
                            <tr>
                                <th>Paciente</th>
                                <th>Tipo ID</th>
                                <th>Numero ID</th>
                                <th>Vacuna</th>
                                <th class="text-right">Dosis</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="paiDoseModalBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.pai-head{display:flex;justify-content:space-between;align-items:center;gap:12px}
.pai-title{font-weight:900;color:#0f172a}
.pai-dashboard-shell{position:relative}
.pai-dashboard-shell.pai-is-loading .pai-card,
.pai-dashboard-shell.pai-is-loading .pai-mini{filter:blur(.5px);pointer-events:none;user-select:none}
.pai-dashboard-shell.pai-is-loading .pai-card,
.pai-dashboard-shell.pai-is-loading .pai-mini{opacity:.72}
.pai-dashboard-overlay{position:absolute;inset:0;z-index:30;display:none;align-items:center;justify-content:center;padding:24px;background:linear-gradient(180deg,rgba(255,255,255,.58),rgba(248,251,255,.82));backdrop-filter:blur(4px)}
.pai-dashboard-overlay.is-visible{display:flex}
.pai-dashboard-overlay__card{min-width:min(92%,520px);text-align:center;padding:26px 28px;border-radius:20px;border:1px solid rgba(59,130,246,.18);background:rgba(255,255,255,.92);box-shadow:0 24px 50px rgba(15,23,42,.18)}
.pai-dashboard-overlay__title{font-size:1.05rem;font-weight:900;color:#0f172a}
.pai-dashboard-overlay__text{margin-top:6px;color:#64748b;font-size:.92rem}
.pai-section-title{font-weight:900;color:#0f172a;letter-spacing:-.02em}
.pai-section-subtitle{color:#64748b;font-size:.92rem;max-width:820px}
.pai-kicker{display:inline-flex;align-items:center;gap:8px;font-size:.72rem;text-transform:uppercase;letter-spacing:.16em;color:#2563eb;font-weight:900;margin-bottom:6px}
.pai-kicker:before{content:'';width:28px;height:2px;border-radius:999px;background:linear-gradient(90deg,#60a5fa,#2563eb)}
.pai-card{border:1px solid rgba(15,23,42,.08);border-radius:14px;box-shadow:0 8px 22px rgba(2,6,23,.05)}
.pai-filters__hero{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.pai-filters__signal{display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid rgba(37,99,235,.16);border-radius:999px;background:rgba(255,255,255,.72);box-shadow:0 10px 26px rgba(37,99,235,.08);font-size:.82rem;color:#334155;font-weight:700}
.pai-signal-dot{width:10px;height:10px;border-radius:50%;background:linear-gradient(180deg,#22c55e,#16a34a);box-shadow:0 0 0 6px rgba(34,197,94,.12)}
.pai-progress{background:rgba(255,255,255,.55);border:1px solid rgba(148,163,184,.22);border-radius:16px;padding:12px 14px}
.pai-progress__track{height:8px;border-radius:999px;background:rgba(148,163,184,.18);overflow:hidden}
.pai-progress__fill{height:100%;width:16%;border-radius:inherit;background:linear-gradient(90deg,#3b82f6,#06b6d4,#22c55e);box-shadow:0 0 18px rgba(59,130,246,.35);transition:width .25s ease}
.pai-progress__labels{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:6px;margin-top:8px;font-size:.72rem;color:#64748b;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.pai-filter-grid{margin-top:.25rem}
.pai-field-label{font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.05em}
.pai-control{border:1px solid rgba(148,163,184,.55);border-radius:12px;height:38px;background:rgba(255,255,255,.92);box-shadow:inset 0 1px 0 rgba(255,255,255,.7);transition:all .18s ease}
.pai-control:focus{border-color:#3b82f6;box-shadow:0 0 0 .2rem rgba(59,130,246,.14),0 8px 22px rgba(15,23,42,.06)}
.pai-control[disabled]{opacity:.58;background:#f8fafc;cursor:not-allowed}
.pai-field-hint{display:block;margin-top:6px;color:#64748b;font-size:.76rem;min-height:1rem}
.pai-livebar{display:flex;justify-content:space-between;gap:14px;align-items:center;flex-wrap:wrap;padding-top:4px}
.pai-livebar__meta{flex:1 1 520px;min-height:38px;padding:10px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.2);background:rgba(248,250,252,.9);color:#475569;font-size:.84rem;line-height:1.35}
.pai-livebar__actions{display:flex;gap:8px}
.pai-action-btn{min-width:84px;border-radius:12px;font-weight:800}
.pai-action-btn--primary{box-shadow:0 10px 22px rgba(59,130,246,.22)}
.pai-tags{display:flex;flex-wrap:wrap;gap:8px}
.pai-tag{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border-radius:999px;border:1px solid rgba(148,163,184,.22);background:#fff;font-size:.8rem;color:#334155;box-shadow:0 4px 14px rgba(2,6,23,.04)}
.pai-tag strong{color:#0f172a}
.pai-tag--active{border-color:rgba(37,99,235,.35);background:linear-gradient(180deg,#eff6ff,#fff);color:#1d4ed8}
.pai-progress-note{font-size:.76rem;color:#64748b}
.pai-toggle{display:inline-flex;align-items:center;gap:8px;padding:5px 9px;border:1px solid rgba(148,163,184,.32);border-radius:999px;background:#fff;color:#334155;font-size:.78rem;font-weight:800;cursor:pointer;user-select:none}
.pai-toggle input{position:absolute;opacity:0;pointer-events:none}
.pai-toggle__track{position:relative;width:34px;height:18px;border-radius:999px;background:#cbd5e1;transition:background .18s ease;flex:0 0 auto}
.pai-toggle__thumb{position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(15,23,42,.22);transition:transform .18s ease}
.pai-toggle input:checked + .pai-toggle__track{background:#2563eb}
.pai-toggle input:checked + .pai-toggle__track .pai-toggle__thumb{transform:translateX(16px)}
.pai-toggle__label{line-height:1}
.pai-dose-link{border:none;background:transparent;color:#1d4ed8;font-weight:900;font-size:1rem;padding:2px 6px;border-radius:10px;transition:all .16s ease}
.pai-dose-link:hover{background:rgba(37,99,235,.08);transform:translateY(-1px);text-decoration:none}
.pai-mini{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:12px;padding:12px}
.pai-mini__label{font-size:.76rem;text-transform:uppercase;color:#64748b;font-weight:800}
.pai-mini__value{font-size:1.3rem;font-weight:900;color:#0f172a}
.pai-table-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 14px;border-bottom:1px solid rgba(15,23,42,.08);background:#fff}
.pai-table-toolbar__title{font-weight:900;color:#0f172a;line-height:1.1}
.pai-table-toolbar__hint{font-size:.76rem;color:#64748b;margin-top:2px}
.pai-table-head th{font-size:.74rem;text-transform:uppercase;letter-spacing:.02em;background:#f8fbff;border-top:none}
.pai-chip{display:inline-block;padding:5px 10px;border-radius:0;font-size:.78rem;font-weight:800;border:1px solid #000;color:#000}
.chip-optimo{background:#0070c0;color:#fff}
.chip-util{background:#00b050;color:#000}
.chip-bajo{background:#ffff00;color:#000}
.chip-no-util{background:#f4b183;color:#000}
.chip-critica{background:#ff00ff;color:#000}
.chip-muy-critica{background:#ff0000;color:#000}
.chip-sin{background:#fff;color:#000}
.gap-2{gap:.5rem}
</style>
@stop

@section('js')
<script>
(function(){
    const url = @json(route('afiliado.stats.dashboard'));
    const detailUrl = @json(route('afiliado.stats.dose.detail'));
    const num = new Intl.NumberFormat('es-CO');
    let currentCatalogs = null;
    let loadTimer = null;
    let lastResponse = null;

    function selectedVal(id){ return (document.getElementById(id)?.value || '').trim(); }

    function stateClass(estado){
        const e = (estado || '').toLowerCase();
        if (e.includes('no util') || e.includes('no útil') || e.includes('no Ãºtil')) return 'chip-no-util';
        if (e.includes('optimo') || e.includes('optima') || e.includes('óptima')) return 'chip-optimo';
        if (e.includes('util') || e.includes('útil')) return 'chip-util';
        if (e.includes('bajo riesgo')) return 'chip-bajo';
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

    function countActiveFilters(){
        return ['paiYear','paiEscala','paiPeriodo','paiMunicipio','paiIps','paiRegimen']
            .filter(function(id){ return selectedVal(id) !== ''; }).length;
    }

    function syncFilterLocks(){
        const municipio = selectedVal('paiMunicipio');
        const escala = selectedVal('paiEscala');

        document.getElementById('paiPeriodo').disabled = !escala;
        document.getElementById('paiIps').disabled = !municipio;
        document.getElementById('paiRegimen').disabled = !municipio;
    }

    function setLoadingState(isLoading, message = ''){
        const shell = document.querySelector('.pai-dashboard-shell');
        const overlay = document.getElementById('paiDashboardOverlay');
        const selectionCount = document.getElementById('paiSelectionCount');
        const meta = document.getElementById('paiMeta');

        if (shell) {
            shell.classList.toggle('pai-is-loading', !!isLoading);
        }
        if (overlay) {
            overlay.classList.toggle('is-visible', !!isLoading);
        }
        if (selectionCount && isLoading) {
            selectionCount.textContent = 'Recalculando...';
        }
        if (meta && isLoading && message) {
            meta.textContent = message;
        }

        ['paiYear','paiEscala','paiPeriodo','paiMunicipio','paiIps','paiRegimen','paiAplicar','paiLimpiar','paiTracerToggle']
            .forEach(function(id){
                const el = document.getElementById(id);
                if (el) {
                    if (isLoading) {
                        el.disabled = true;
                    } else {
                        el.disabled = false;
                    }
                }
            });

        if (!isLoading) {
            syncFilterLocks();
        }
    }

    function paintFilterState(resp){
        const el = document.getElementById('paiSelectionTags');
        if (!el) return;

        const filters = resp?.filters || {};
        const ipsName = (resp?.catalogs?.ips || []).find(function(item){
            return String(item.key || '') === String(filters.ips_key || '');
        })?.name || filters.ips_key || '';

        const chips = [
            ['Año', filters.year],
            ['Escala', filters.escala],
            ['Periodo', filters.periodo],
            ['Municipio', filters.municipio],
            ['IPS', ipsName],
            ['Régimen', filters.regimen]
        ].filter(function(item){
            return String(item[1] ?? '').trim() !== '';
        });

        el.innerHTML = chips.map(function(item, index){
            return '<span class="pai-tag ' + (index >= 3 ? 'pai-tag--active' : '') + '"><strong>' + item[0] + ':</strong> ' + item[1] + '</span>';
        }).join('');

        const active = countActiveFilters();
        const fill = Math.max(16, Math.min(100, active * 16.5));
        document.getElementById('paiSelectionCount').textContent = active + ' filtros activos';
        document.getElementById('paiProgressFill').style.width = fill + '%';
    }

    function scheduleLoad(){
        if (loadTimer) clearTimeout(loadTimer);
        setLoadingState(true, 'Preparando recálculo...');
        loadTimer = setTimeout(function(){
            load(true);
        }, 520);
    }

    function normalizeText(value){
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toUpperCase()
            .replace(/[^A-Z0-9]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function isTracerIndicator(row){
        const indicador = normalizeText(row?.indicador);
        const biologico = normalizeText(row?.biologico);
        const dosis = normalizeText(row?.dosis_meta);
        const all = [indicador, biologico, dosis].join(' ');

        if (all.includes('BCG')) return true;
        if (all.includes('PENTAVALENTE') && (all.includes('3RA') || all.includes('TERCERA') || /\b3\b/.test(all))) return true;
        if (all.includes('TRIPLE VIRAL') && (indicador.includes('1 ANO') || dosis.includes('1') || all.includes('PRIMERA'))) return true;
        if (all.includes('TRIPLE VIRAL') && (indicador.includes('18 MESES') || all.includes('REFUERZO'))) return true;
        if (all.includes('FIEBRE AMARILLA') && (indicador.includes('18 MESES') || all.includes('18 MESES'))) return true;
        if (all.includes('DPT') && (indicador.includes('5 ANOS') || all.includes('SEGUNDO REFUERZO') || all.includes('2DO REFUERZO'))) return true;
        if (all.includes('VPH')) return true;
        if (indicador.includes('GESTANTE') && (all.includes('DPT') || all.includes('TDAP') || all.includes('DTPA'))) return true;
        if (indicador.includes('GESTANTE') && (all.includes('VSR') || all.includes('RSV') || all.includes('SINCITIAL'))) return true;

        return false;
    }

    function visibleRows(rows){
        const tracerOnly = document.getElementById('paiTracerToggle')?.checked || false;
        const source = rows || [];
        return tracerOnly ? source.filter(isTracerIndicator) : source;
    }

    function renderKpis(rows){
        const totals = (rows || []).reduce(function(acc, row){
            acc.meta += Number(row.meta || 0);
            acc.dosis += Number(row.dosis_aplicadas || 0);
            acc.susceptibles += Number(row.susceptibles || 0);
            return acc;
        }, { meta: 0, dosis: 0, susceptibles: 0 });

        document.getElementById('kpiMeta').textContent = num.format(totals.meta);
        document.getElementById('kpiDosis').textContent = num.format(totals.dosis);
        document.getElementById('kpiSusceptibles').textContent = num.format(totals.susceptibles);
    }

    function renderRows(rows){
        const body = document.getElementById('paiBody');
        if (!body) return;
        body.innerHTML = '';
        const sourceRows = visibleRows(rows);
        renderKpis(sourceRows);

        if (!sourceRows.length) {
            body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay indicadores para mostrar.</td></tr>';
            return;
        }

        sourceRows.forEach(function(r){
            const tr = document.createElement('tr');
            const pct = Number(r.cobertura || 0) * 100;
            const dose = Number(r.dosis_aplicadas || 0);
            tr.innerHTML =
                '<td>' + (r.indicador || '') + '</td>' +
                '<td>' + (r.biologico || '') + '</td>' +
                '<td class="text-center"><span class="pai-tag pai-tag--active">' + (r.dosis_meta || '') + '</span></td>' +
                '<td class="text-right">' + num.format(Number(r.meta || 0)) + '</td>' +
                '<td class="text-right">' +
                    '<button type="button" class="pai-dose-link" ' +
                        'data-id-vacuna="' + (r.id_vacuna || '') + '" ' +
                        'data-dosis-meta="' + (r.dosis_meta || '') + '" ' +
                        'data-period-start="' + ((lastResponse?.evaluation_period?.start_date || '') || '') + '" ' +
                        'data-period-end="' + ((lastResponse?.evaluation_period?.end_date || '') || '') + '" ' +
                        'data-indicador="' + (r.indicador || '') + '" ' +
                        'data-biologico="' + (r.biologico || '') + '" ' +
                        'data-dose-count="' + dose + '">' + num.format(dose) + '</button>' +
                '</td>' +
                '<td class="text-right">' + num.format(Number(r.susceptibles || 0)) + '</td>' +
                '<td class="text-right">' + pct.toFixed(1) + '%</td>' +
                '<td><span class="pai-chip ' + stateClass(r.estado) + '">' + (r.estado || '') + '</span></td>';
            body.appendChild(tr);
        });
    }

    function renderDoseModalLoading(){
        const body = document.getElementById('paiDoseModalBody');
        if (!body) return;
        body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Cargando detalle...</td></tr>';
    }

    function renderDoseModal(resp){
        const body = document.getElementById('paiDoseModalBody');
        if (!body) return;
        const rows = resp?.rows || [];
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay registros para mostrar.</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function(r){
            return '<tr>' +
                '<td>' + (r.nombre || '') + '</td>' +
                '<td>' + (r.tipo_identificacion || '') + '</td>' +
                '<td>' + (r.numero_identificacion || '') + '</td>' +
                '<td>' + (r.vacuna || '') + '</td>' +
                '<td class="text-right">' + (r.docis || '') + '</td>' +
                '<td>' + (r.fecha_vacuna || '') + '</td>' +
            '</tr>';
        }).join('');
    }

    function openDoseModal(payload){
        const modalTitle = document.getElementById('paiDoseModalTitle');
        const modalSub = document.getElementById('paiDoseModalSub');
        const modalChips = document.getElementById('paiDoseModalChips');
        if (modalTitle) modalTitle.textContent = 'Detalle de dosis aplicadas';
        if (modalSub) modalSub.textContent = 'Listado de pacientes, identificación, vacuna, dosis y fecha';

        if (modalChips) {
            modalChips.innerHTML = [
                '<span class="pai-tag pai-tag--active"><strong>Municipio:</strong> ' + (payload.municipio || '') + '</span>',
                '<span class="pai-tag pai-tag--active"><strong>IPS:</strong> ' + (payload.ips_name || '') + '</span>',
                '<span class="pai-tag pai-tag--active"><strong>Vacuna:</strong> ' + (payload.biologico || '') + '</span>',
                '<span class="pai-tag pai-tag--active"><strong>Dosis:</strong> ' + (payload.dosis_meta || '') + '</span>',
                '<span class="pai-tag pai-tag--active"><strong>Periodo:</strong> ' + (payload.period_start || '') + ' a ' + (payload.period_end || '') + '</span>'
            ].join('');
        }

        renderDoseModalLoading();

        fetch(detailUrl + '?' + new URLSearchParams(payload).toString(), { headers: { 'Accept': 'application/json' } })
            .then(function(r){ return r.json(); })
            .then(function(resp){
                if (!resp || !resp.ok) throw new Error('No se pudo cargar detalle');
                renderDoseModal(resp);
            })
            .catch(function(){
                const body = document.getElementById('paiDoseModalBody');
                if (body) {
                    body.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">No fue posible cargar el detalle.</td></tr>';
                }
            });

        if (window.$ && typeof window.$.fn.modal === 'function') {
            window.$('#paiDoseModal').modal('show');
        }
    }

    function renderThresholds(th){
        const el = document.getElementById('paiThresholds');
        if (!el) return;
        const map = [
            ['chip-optimo', 'Cobertura Óptima', th?.optima || '>100%'],
            ['chip-util', 'Cobertura útil', th?.util || '95.0% - 100%'],
            ['chip-bajo', 'Cobertura bajo riesgo', th?.bajo_riesgo || '90.0% - 94.9%'],
            ['chip-no-util', 'Cobertura no útil', th?.no_util || '80.0% - 89.9%'],
            ['chip-critica', 'Cobertura Crítica', th?.critica || '50.0% - 79.9%'],
            ['chip-muy-critica', 'Cobertura muy crítica', th?.muy_critica || '<=50%'],
            ['chip-sin', 'SIN REPORTE', th?.sin_reporte || '0']
        ];
        el.innerHTML = map.map(x => '<span class="pai-chip ' + x[0] + '">' + x[1] + ': ' + x[2] + '</span>').join('');
    }

    function load(applyCurrent){
        if (loadTimer) {
            clearTimeout(loadTimer);
            loadTimer = null;
        }
        const qs = new URLSearchParams();
        qs.set('year', selectedVal('paiYear') || new Date().getFullYear());
        if (applyCurrent) {
            qs.set('municipio', selectedVal('paiMunicipio'));
            qs.set('ips_key', selectedVal('paiIps'));
            qs.set('regimen', selectedVal('paiRegimen'));
            qs.set('escala', selectedVal('paiEscala'));
            qs.set('periodo', selectedVal('paiPeriodo'));
        }

        setLoadingState(true, 'Cargando reporte...');

        fetch(url + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function(resp){
                if (!resp || !resp.ok) throw new Error('No se pudo cargar');

                lastResponse = resp;
                currentCatalogs = resp.catalogs || null;
                fillSelect('paiEscala', currentCatalogs?.escalas || [], null, null, resp.filters?.escala || '');
                const escalaVal = selectedVal('paiEscala');
                fillSelect('paiPeriodo', periodOptionsForEscala(escalaVal), null, null, resp.filters?.periodo || '');
                fillSelect('paiMunicipio', currentCatalogs?.municipios || [], null, null, resp.filters?.municipio || '');
                fillIpsForMunicipio(resp.filters?.municipio || '', resp.filters?.ips_key || '');
                fillSelect('paiRegimen', currentCatalogs?.regimenes || [], null, null, resp.filters?.regimen || '');
                syncFilterLocks();
                paintFilterState(resp);

                const months = (resp.period?.month_labels || []).join(', ');
                const metaStartCode = resp.meta_period?.start_code || '';
                const metaEndCode = resp.meta_period?.end_code || '';
                const evalStart = resp.evaluation_period?.start_date || '-';
                const evalEnd = resp.evaluation_period?.end_date || '-';
                document.getElementById('paiMeta').textContent =
                    'Periodo: ' + (resp.filters?.periodo || '') +
                    ' (' + months + ') | Rango: ' + (resp.period?.start_date || '-') + ' a ' + (resp.period?.end_date || '-') +
                    ' | Meta: ' + (resp.flags?.meta_source || 'N/D') +
                    (metaStartCode || metaEndCode ? ' | Vigencia meta: ' + metaStartCode + ' a ' + metaEndCode : '') +
                    ' | Evaluacion: ' + evalStart + ' a ' + evalEnd +
                    ' | Generado: ' + (resp.generated_at || '-');

                if (resp.flags && resp.flags.combo_has_data === false) {
                    document.getElementById('paiMeta').textContent +=
                        ' | Sin registros para esta combinacion de Municipio + Codigo + Regimen.';
                }

                renderRows(resp.rows || []);
                renderThresholds(resp.thresholds || {});
            })
            .catch(function(){
                document.getElementById('paiMeta').textContent = 'Error consultando las estadisticas PAI.';
            })
            .finally(function(){
                setLoadingState(false);
            });
    }

    function fillIpsForMunicipio(municipio, selectedKey = ''){
        const ips = (currentCatalogs?.ips || []).filter(function(item){
            if (!municipio) return true;
            return String(item.municipio || '') === String(municipio);
        });
        fillSelect('paiIps', ips, 'key', 'name', selectedKey || (ips[0]?.key || ''));
    }

    document.getElementById('paiBody').addEventListener('click', function(e){
        const btn = e.target.closest('.pai-dose-link');
        if (!btn) return;

        const payload = {
            year: selectedVal('paiYear') || new Date().getFullYear(),
            municipio: selectedVal('paiMunicipio'),
            ips_key: selectedVal('paiIps'),
            regimen: selectedVal('paiRegimen'),
            escala: selectedVal('paiEscala'),
            periodo: selectedVal('paiPeriodo'),
            id_vacuna: btn.dataset.idVacuna || '',
            dosis_meta: btn.dataset.dosisMeta || '',
            period_start: btn.dataset.periodStart || (lastResponse?.evaluation_period?.start_date || ''),
            period_end: btn.dataset.periodEnd || (lastResponse?.evaluation_period?.end_date || ''),
            biologico: btn.dataset.biologico || '',
            ips_name: (currentCatalogs?.ips || []).find(function(item){
                return String(item.key || '') === String(selectedVal('paiIps'));
            })?.name || '',
        };

        openDoseModal(payload);
    });

    document.getElementById('paiMunicipio').addEventListener('change', function(){
        fillIpsForMunicipio(selectedVal('paiMunicipio'));
        syncFilterLocks();
        scheduleLoad();
    });

    document.getElementById('paiEscala').addEventListener('change', function(){
        fillSelect('paiPeriodo', periodOptionsForEscala(selectedVal('paiEscala')));
        syncFilterLocks();
        scheduleLoad();
    });

    document.getElementById('paiYear').addEventListener('change', function(){
        syncFilterLocks();
        scheduleLoad();
    });

    document.getElementById('paiPeriodo').addEventListener('change', function(){
        syncFilterLocks();
        scheduleLoad();
    });

    document.getElementById('paiIps').addEventListener('change', function(){
        syncFilterLocks();
        scheduleLoad();
    });

    document.getElementById('paiRegimen').addEventListener('change', function(){
        syncFilterLocks();
        scheduleLoad();
    });

    document.getElementById('paiTracerToggle').addEventListener('change', function(){
        renderRows(lastResponse?.rows || []);
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

    syncFilterLocks();
    load(false);
})();
</script>
@stop
