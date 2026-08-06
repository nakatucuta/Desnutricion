@extends('adminlte::page')

@section('title', 'PAI - Estadisticas')

@section('content_header')
<div class="pai-head">
    <div>
        @if(request()->routeIs('afiliado.stats.charts.view'))
            <h1 class="pai-title mb-1">Graficas estadisticas PAI</h1>
            <div class="text-muted">Resumen de vacunacion reportada y poblacion asignada</div>
        @else
            <h1 class="pai-title mb-1">Coberturas PAI {{ now()->year }}</h1>
            <div class="text-muted">Seguimiento de metas, dosis aplicadas y susceptibles</div>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if(request()->routeIs('afiliado.stats.charts.view'))
            <a href="{{ route('afiliado.stats.view') }}" class="btn btn-outline-info mr-2">
                <i class="fas fa-table mr-1"></i> Ver coberturas
            </a>
        @else
            <a href="{{ route('afiliado.stats.charts.view') }}" class="btn btn-outline-info mr-2">
                <i class="fas fa-chart-bar mr-1"></i> Ver graficas
            </a>
        @endif
        <a href="{{ route('afiliado.stats.settings.index') }}" class="btn btn-outline-dark mr-2">
            <i class="fas fa-sliders-h mr-1"></i> Parametrizaciones PAI
        </a>
        <a href="{{ route('afiliado.stats.indicadores.index') }}" class="btn btn-outline-primary mr-2">
            <i class="fas fa-database mr-1"></i> Administrar Indicadores
        </a>
        <a href="{{ route('afiliado.stats.bimonthly.index') }}" class="btn btn-outline-success mr-2">
            <i class="fas fa-calendar-alt mr-1"></i> Informe bimestral
        </a>
        <a href="{{ route('afiliado') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Cargue
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    @if(request()->routeIs('afiliado.stats.charts.view'))
    <section class="pai-visual" id="paiVisualDashboard">
        <div class="pai-visual__header">
            <div>
                <div class="pai-kicker">Resumen grafico</div>
                <h2 class="pai-section-title mb-1">Vacunacion reportada y poblacion asignada</h2>
                <div class="pai-section-subtitle">Consolidado de dosis por territorio, vacunadora, biologico y poblacion asignada.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="paiChartsRefresh">
                <i class="fas fa-sync-alt mr-1"></i> Actualizar
            </button>
        </div>

        <div class="pai-visual__filters">
            <div class="row">
                <div class="col-xl-2 col-md-4 mb-2">
                    <label for="chartYear">Vigencia</label>
                    <input type="number" min="2000" max="2100" class="form-control form-control-sm" id="chartYear" value="{{ now()->year }}">
                </div>
                <div class="col-xl-2 col-md-4 mb-2">
                    <label for="chartStartDate">Fecha inicial</label>
                    <input type="date" class="form-control form-control-sm" id="chartStartDate" value="{{ now()->startOfYear()->format('Y-m-d') }}">
                </div>
                <div class="col-xl-2 col-md-4 mb-2">
                    <label for="chartEndDate">Fecha final</label>
                    <input type="date" class="form-control form-control-sm" id="chartEndDate" value="{{ now()->endOfYear()->format('Y-m-d') }}">
                </div>
                <div class="col-xl-2 col-md-4 mb-2">
                    <label for="chartMunicipio">Municipio vacunadora</label>
                    <select class="form-control form-control-sm" id="chartMunicipio"><option value="">Todos</option></select>
                </div>
                <div class="col-xl-2 col-md-4 mb-2">
                    <label for="chartVaccinator">IPS vacunadora</label>
                    <select class="form-control form-control-sm" id="chartVaccinator"><option value="">Todas</option></select>
                </div>
                <div class="col-xl-2 col-md-4 mb-2">
                    <label for="chartRegimen">Regimen</label>
                    <select class="form-control form-control-sm" id="chartRegimen"><option value="">Todos</option></select>
                </div>
                <div class="col-xl-3 col-md-5 mb-2">
                    <label for="chartBiologico">Biologico</label>
                    <select class="form-control form-control-sm" id="chartBiologico"><option value="">Todos</option></select>
                </div>
                <div class="col-xl-3 col-md-7 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-light mr-2" id="paiChartsReset">Limpiar</button>
                    <button type="button" class="btn btn-sm btn-primary" id="paiChartsApply">Aplicar filtros</button>
                </div>
            </div>
            <div class="pai-visual__meta" id="paiChartsMeta">Preparando estadisticas...</div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-3 col-6 mb-3"><div class="pai-visual-kpi"><span>Dosis aplicadas</span><strong id="chartKpiDoses">0</strong></div></div>
            <div class="col-lg-3 col-6 mb-3"><div class="pai-visual-kpi"><span>Personas vacunadas</span><strong id="chartKpiPeople">0</strong></div></div>
            <div class="col-lg-3 col-6 mb-3"><div class="pai-visual-kpi pai-visual-kpi--assigned"><span>Dosis de IPS asignadas</span><strong id="chartKpiAssigned">0</strong></div></div>
            <div class="col-lg-3 col-6 mb-3"><div class="pai-visual-kpi pai-visual-kpi--percent"><span>Asignadas / total vacunado</span><strong id="chartKpiAssignedPct">0%</strong></div></div>
        </div>

        <div class="row">
            <div class="col-xl-6 mb-3"><div class="pai-visual-card"><h3>Total vacunas aplicadas por municipio</h3><div class="pai-chart-box"><canvas id="chartPaiMunicipalities"></canvas></div></div></div>
            <div class="col-xl-6 mb-3"><div class="pai-visual-card"><h3>Top IPS con mayor cantidad reportada</h3><div class="pai-chart-box"><canvas id="chartPaiProviders"></canvas></div></div></div>
            <div class="col-xl-6 mb-3"><div class="pai-visual-card"><h3>Total biologicos aplicados por mes</h3><div class="pai-chart-box"><canvas id="chartPaiMonthly"></canvas></div></div></div>
            <div class="col-xl-6 mb-3"><div class="pai-visual-card"><h3>Total vacunas reportadas por biologico</h3><div class="pai-chart-box pai-chart-box--scroll"><canvas id="chartPaiBiologics"></canvas></div></div></div>
            <div class="col-xl-6 mb-3"><div class="pai-visual-card"><h3>IPS asignadas contra todo lo vacunado</h3><p class="pai-visual-card__hint">El porcentaje usa como denominador todas las dosis aplicadas por la vacunadora seleccionada.</p><div class="pai-chart-box pai-chart-box--donut"><canvas id="chartPaiAssignment"></canvas></div></div></div>
            <div class="col-xl-6 mb-3"><div class="pai-visual-card"><h3>Total vacunas aplicadas por sexo</h3><div class="pai-chart-box pai-chart-box--donut"><canvas id="chartPaiSex"></canvas></div></div></div>
            <div class="col-12 mb-3"><div class="pai-visual-card"><h3>Porcentaje de poblacion asignada por vacunadora</h3><p class="pai-visual-card__hint">Dosis cuya IPS primaria esta activa en la asignacion de cada vacunadora, sobre el total reportado por ella.</p><div class="pai-chart-box pai-chart-box--wide"><canvas id="chartPaiAssignmentProviders"></canvas></div></div></div>
        </div>
    </section>
    @else
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
    @endif
</div>

@unless(request()->routeIs('afiliado.stats.charts.view'))
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
@endunless
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
.pai-visual{position:relative;margin-bottom:24px;padding:22px;border:1px solid rgba(15,23,42,.08);border-radius:22px;background:linear-gradient(145deg,#f8fbff,#fff);box-shadow:0 16px 36px rgba(15,23,42,.07)}
.pai-visual.is-loading:after{content:'Consultando datos...';position:absolute;inset:0;z-index:20;display:flex;align-items:center;justify-content:center;border-radius:22px;background:rgba(248,250,252,.78);backdrop-filter:blur(2px);font-weight:900;color:#0f766e}
.pai-visual__header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.pai-visual__filters{margin-top:18px;padding:14px 14px 8px;border:1px solid rgba(148,163,184,.24);border-radius:15px;background:rgba(255,255,255,.86)}
.pai-visual__filters label{display:block;margin-bottom:4px;font-size:.72rem;font-weight:900;letter-spacing:.04em;text-transform:uppercase;color:#64748b}
.pai-visual__meta{padding:4px 0 3px;font-size:.78rem;color:#64748b}
.pai-visual-kpi{height:100%;padding:16px;border:1px solid rgba(14,116,144,.14);border-radius:16px;background:#fff;box-shadow:0 9px 22px rgba(15,23,42,.05)}
.pai-visual-kpi span{display:block;font-size:.75rem;font-weight:900;text-transform:uppercase;color:#64748b}
.pai-visual-kpi strong{display:block;margin-top:6px;font-size:1.65rem;line-height:1;color:#0f172a}
.pai-visual-kpi--assigned{background:linear-gradient(145deg,#ecfdf5,#fff)}
.pai-visual-kpi--percent{background:linear-gradient(145deg,#ecfeff,#fff)}
.pai-visual-kpi--percent strong{color:#0f766e}
.pai-visual-card{height:100%;min-height:355px;padding:18px;border:1px solid rgba(15,23,42,.08);border-radius:20px;background:#fff;box-shadow:0 12px 28px rgba(15,23,42,.06)}
.pai-visual-card h3{margin:0 0 12px;text-align:center;font-size:.92rem;font-weight:900;text-transform:uppercase;color:#0f172a}
.pai-visual-card__hint{margin:-5px auto 8px;max-width:660px;text-align:center;font-size:.74rem;color:#64748b}
.pai-chart-box{position:relative;height:285px;min-width:0}
.pai-chart-box--donut{height:270px}
.pai-chart-box--wide{height:360px}
.pai-chart-box--scroll{overflow-x:auto}
@media(max-width:767.98px){.pai-visual{padding:14px;border-radius:16px}.pai-visual-card{min-height:330px;padding:12px}.pai-chart-box{height:270px}.pai-visual-kpi strong{font-size:1.3rem}}
</style>
@stop

@section('js')
@unless(request()->routeIs('afiliado.stats.charts.view'))
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
            cobertura: btn.dataset.indicador || '',
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
@endunless
@if(request()->routeIs('afiliado.stats.charts.view'))
@vite('resources/js/pai-statistics.js')
<script>
(function(){
    const chartsUrl = @json(route('afiliado.stats.charts.data'));
    const numberFormat = new Intl.NumberFormat('es-CO');
    const chartInstances = {};
    let chartCatalogs = { municipalities: [], providers: [], regimes: [], biologics: [] };

    function value(id){ return String(document.getElementById(id)?.value || '').trim(); }

    function setOptions(id, rows, valueKey, labelBuilder, keepValue){
        const select = document.getElementById(id);
        if (!select) return;
        const selected = keepValue ? select.value : '';
        const uppercaseLabels = id === 'chartVaccinator' || id === 'chartBiologico';
        const normalizeLabel = function(label){
            const text = String(label || '');
            return uppercaseLabels ? text.toLocaleUpperCase('es-CO') : text;
        };
        const firstLabel = id === 'chartVaccinator' ? 'Todas' : (id === 'chartMunicipio' ? 'Todos' : 'Todos');
        select.innerHTML = '<option value="">' + normalizeLabel(firstLabel) + '</option>';
        (rows || []).forEach(function(row){
            const option = document.createElement('option');
            option.value = typeof row === 'object' ? String(row[valueKey] ?? '') : String(row);
            option.textContent = normalizeLabel(labelBuilder ? labelBuilder(row) : String(row));
            select.appendChild(option);
        });
        if (keepValue && Array.from(select.options).some(option => option.value === selected)) {
            select.value = selected;
        }
    }

    function providersForMunicipality(){
        const municipality = value('chartMunicipio');
        return (chartCatalogs.providers || []).filter(function(provider){
            return !municipality || String(provider.municipio || '').toUpperCase() === municipality.toUpperCase();
        });
    }

    function renderCatalogs(catalogs, keepValues){
        chartCatalogs = catalogs || chartCatalogs;
        setOptions('chartMunicipio', chartCatalogs.municipalities || [], null, null, keepValues);
        setOptions('chartVaccinator', providersForMunicipality(), 'code', function(row){
            return (row.name || 'IPS') + ' | ' + (row.code || '');
        }, keepValues);
        setOptions('chartRegimen', chartCatalogs.regimes || [], null, null, keepValues);
        setOptions('chartBiologico', chartCatalogs.biologics || [], 'id', row => row.name || '', keepValues);
    }

    const valueLabelsPlugin = {
        id: 'paiValueLabels',
        afterDatasetsDraw(chart, args, options){
            if (options === false || chart.config.type === 'doughnut' || chart.config.type === 'pie') return;
            const ctx = chart.ctx;
            ctx.save();
            ctx.fillStyle = '#0f172a';
            ctx.font = '700 10px sans-serif';
            ctx.textAlign = chart.options.indexAxis === 'y' ? 'left' : 'center';
            chart.data.datasets.forEach(function(dataset, datasetIndex){
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.data.forEach(function(element, index){
                    const raw = Number(dataset.data[index] || 0);
                    if (!raw) return;
                    const suffix = options?.suffix || '';
                    const text = (suffix === '%' ? raw.toFixed(1) : numberFormat.format(raw)) + suffix;
                    const pos = element.tooltipPosition();
                    const x = chart.options.indexAxis === 'y' ? pos.x + 6 : pos.x;
                    const y = chart.options.indexAxis === 'y' ? pos.y + 3 : pos.y - 8;
                    ctx.fillText(text, x, y);
                });
            });
            ctx.restore();
        }
    };

    function destroyChart(key){
        if (chartInstances[key]) {
            chartInstances[key].destroy();
            delete chartInstances[key];
        }
    }

    function buildChart(key, canvasId, type, items, options){
        destroyChart(key);
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof window.Chart === 'undefined') return;
        const rows = items || [];
        const config = options || {};
        const dataset = {
            label: config.datasetLabel || 'Dosis',
            data: rows.map(row => Number(config.valueKey ? row[config.valueKey] : row.total) || 0),
            backgroundColor: config.backgroundColor || '#079a9a',
            borderColor: config.borderColor || config.backgroundColor || '#079a9a',
            borderWidth: config.borderWidth ?? 1,
            tension: config.tension ?? 0,
            fill: config.fill || false,
            pointRadius: config.pointRadius ?? 3
        };

        chartInstances[key] = new window.Chart(canvas, {
            type: type,
            data: { labels: rows.map(row => row.label || ''), datasets: [dataset] },
            plugins: [valueLabelsPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: config.indexAxis || 'x',
                layout: { padding: { top: 20, right: config.indexAxis === 'y' ? 52 : 12 } },
                plugins: {
                    legend: { display: config.legend === true, position: 'right' },
                    paiValueLabels: config.showLabels === false ? false : { suffix: config.suffix || '' },
                    tooltip: config.tooltip || {}
                },
                scales: type === 'doughnut' ? {} : {
                    x: { beginAtZero: true, ticks: { autoSkip: false, maxRotation: config.maxRotation ?? 45, minRotation: config.minRotation ?? 0 } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    function buildDoughnut(key, canvasId, items, colors){
        destroyChart(key);
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof window.Chart === 'undefined') return;
        chartInstances[key] = new window.Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: (items || []).map(row => row.label),
                datasets: [{ data: (items || []).map(row => Number(row.total || 0)), backgroundColor: colors, borderColor: '#fff', borderWidth: 2 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: { display: true, position: 'right' },
                    tooltip: { callbacks: { label(context){
                        const values = context.dataset.data || [];
                        const total = values.reduce((sum, current) => sum + Number(current || 0), 0);
                        const current = Number(context.raw || 0);
                        const pct = total > 0 ? ((current / total) * 100).toFixed(1) : '0.0';
                        return ' ' + context.label + ': ' + numberFormat.format(current) + ' (' + pct + '%)';
                    } } }
                }
            }
        });
    }

    function renderCharts(response){
        const charts = response.charts || {};
        buildChart('municipalities', 'chartPaiMunicipalities', 'bar', charts.municipalities, { backgroundColor: '#069b9b', maxRotation: 0 });
        buildChart('providers', 'chartPaiProviders', 'bar', charts.providers, { backgroundColor: '#08a9df', indexAxis: 'y', maxRotation: 0 });
        buildChart('monthly', 'chartPaiMonthly', 'line', charts.monthly, { backgroundColor: 'rgba(6,182,212,.14)', borderColor: '#06b6d4', borderWidth: 3, tension: .25, fill: true, maxRotation: 0 });
        buildChart('biologics', 'chartPaiBiologics', 'bar', charts.biologics, { backgroundColor: '#0f9f96', maxRotation: 58 });
        buildDoughnut('assignment', 'chartPaiAssignment', charts.assignment, ['#0ea5a4','#f59e0b','#cbd5e1']);
        buildDoughnut('sex', 'chartPaiSex', charts.sex, ['#ff2bb5','#082b65','#94a3b8']);

        const assignmentRows = charts.assignment_by_provider || [];
        buildChart('assignmentProviders', 'chartPaiAssignmentProviders', 'bar', assignmentRows, {
            backgroundColor: '#14b8a6',
            indexAxis: 'y',
            valueKey: 'percentage',
            datasetLabel: 'Porcentaje asignado',
            suffix: '%',
            maxRotation: 0,
            tooltip: { callbacks: { afterLabel(context){
                const row = assignmentRows[context.dataIndex] || {};
                return 'Asignadas: ' + numberFormat.format(row.assigned || 0) + ' / Total: ' + numberFormat.format(row.total || 0);
            } } }
        });
    }

    function setLoading(active){
        document.getElementById('paiVisualDashboard')?.classList.toggle('is-loading', active);
        ['paiChartsRefresh','paiChartsReset','paiChartsApply'].forEach(function(id){
            const button = document.getElementById(id);
            if (button) button.disabled = active;
        });
    }

    function loadCharts(keepCatalogValues){
        const params = new URLSearchParams();
        params.set('year', value('chartYear') || String(new Date().getFullYear()));
        ['StartDate','EndDate'].forEach(function(suffix){
            const field = value('chart' + suffix);
            if (field) params.set(suffix === 'StartDate' ? 'start_date' : 'end_date', field);
        });
        if (value('chartMunicipio')) params.set('municipio', value('chartMunicipio'));
        if (value('chartVaccinator')) params.set('vaccinator_code', value('chartVaccinator'));
        if (value('chartRegimen')) params.set('regimen', value('chartRegimen'));
        if (value('chartBiologico')) params.set('biologico_id', value('chartBiologico'));

        setLoading(true);
        fetch(chartsUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(function(response){
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(response){
                if (!response || !response.ok) throw new Error('Respuesta invalida');
                renderCatalogs(response.catalogs || {}, keepCatalogValues);
                document.getElementById('chartKpiDoses').textContent = numberFormat.format(response.totals?.doses || 0);
                document.getElementById('chartKpiPeople').textContent = numberFormat.format(response.totals?.people || 0);
                document.getElementById('chartKpiAssigned').textContent = numberFormat.format(response.totals?.assigned_doses || 0);
                document.getElementById('chartKpiAssignedPct').textContent = Number(response.totals?.assigned_percentage || 0).toFixed(1) + '%';
                document.getElementById('paiChartsMeta').textContent =
                    'Rango: ' + (response.filters?.start_date || '-') + ' a ' + (response.filters?.end_date || '-') +
                    ' | Asignadas: ' + numberFormat.format(response.assignment?.assigned || 0) +
                    ' | No asignadas: ' + numberFormat.format(response.assignment?.not_assigned || 0) +
                    ' | Sin IPS primaria: ' + numberFormat.format(response.assignment?.missing_primary || 0) +
                    ' | Generado: ' + (response.generated_at || '-');
                renderCharts(response);
            })
            .catch(function(error){
                document.getElementById('paiChartsMeta').textContent = 'No fue posible cargar las graficas: ' + error.message;
            })
            .finally(function(){ setLoading(false); });
    }

    function resetCharts(){
        const year = new Date().getFullYear();
        document.getElementById('chartYear').value = year;
        document.getElementById('chartStartDate').value = year + '-01-01';
        document.getElementById('chartEndDate').value = year + '-12-31';
        ['chartMunicipio','chartVaccinator','chartRegimen','chartBiologico'].forEach(function(id){ document.getElementById(id).value = ''; });
        loadCharts(false);
    }

    function initCharts(){
        if (typeof window.Chart === 'undefined') {
            document.getElementById('paiChartsMeta').textContent = 'La libreria de graficas no esta disponible.';
            return;
        }
        document.getElementById('paiChartsApply').addEventListener('click', () => loadCharts(true));
        document.getElementById('paiChartsRefresh').addEventListener('click', () => loadCharts(true));
        document.getElementById('paiChartsReset').addEventListener('click', resetCharts);
        document.getElementById('chartMunicipio').addEventListener('change', function(){
            setOptions('chartVaccinator', providersForMunicipality(), 'code', row => (row.name || 'IPS') + ' | ' + (row.code || ''), false);
        });
        document.getElementById('chartYear').addEventListener('change', function(){
            const year = value('chartYear');
            if (/^\d{4}$/.test(year)) {
                document.getElementById('chartStartDate').value = year + '-01-01';
                document.getElementById('chartEndDate').value = year + '-12-31';
            }
        });
        loadCharts(false);
    }

    window.addEventListener('load', initCharts, { once: true });
})();
</script>
@endif
@stop
