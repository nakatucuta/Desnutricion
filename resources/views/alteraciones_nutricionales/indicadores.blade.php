@extends('adminlte::page')

@section('title', 'Indicadores nutricionales')

@section('content_header')
@stop

@section('content')
@php
    $initialTotals = data_get($initialPayload ?? [], 'totales', []);
    $initialRows = collect(data_get($initialPayload ?? [], 'rows', []));
    $defaultDesde = $defaultDesde ?? now()->subDays(30)->toDateString();
    $defaultHasta = $defaultHasta ?? now()->toDateString();
    $selectedEvento = $selectedEvento ?? 'all';
    $selectedClasificacion = $selectedClasificacion ?? 'all';
    $classificationOptions = collect($classificationOptions ?? data_get($initialPayload ?? [], 'clasificaciones_totales', []));
    $initialPrimerSeguimiento = data_get($initialTotals, 'primer_seguimiento_promedio');
    $initialClasificacionFinal = (int) data_get($initialTotals, 'casos_clasificacion_final', 0);
    $initialCalidadDato = (float) data_get($initialTotals, 'calidad_dato', 0);
    $initialCalidadIncompletos = (int) data_get($initialTotals, 'calidad_incompletos', 0);
    $initialCalidadInconsistentes = (int) data_get($initialTotals, 'calidad_inconsistentes', 0);
@endphp

<section class="an-hero">
    <div class="an-hero__brand">
        <div class="an-hero__logo-wrap">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="an-hero__logo">
        </div>
        <div class="an-hero__copy">
            <div class="an-eyebrow">Alteraciones nutricionales</div>
            <h1 class="an-title">Indicadores de seguimiento de prestadores</h1>
            <p class="an-subtitle">
                Panel interactivo para analizar casos asignados, seguimientos y trazabilidad por rango de fecha, evento y clasificación.
            </p>
        </div>
    </div>
    <div class="an-hero__actions">
        <span class="an-pill">Rango activo</span>
        <span class="an-pill an-pill--soft">Evento: {{ $selectedEvento === 'all' ? 'Todos' : 'Evento ' . $selectedEvento }}</span>
        <span class="an-pill an-pill--soft">Clasificación: {{ $selectedClasificacion === 'all' ? 'Todas' : $selectedClasificacion }}</span>
    </div>
</section>

<details class="an-summary-accordion" id="summaryAccordion">
    <summary class="an-summary-accordion__head">
        <div>
            <div class="an-eyebrow">Resumen inicial del rango</div>
            <h3>Vista resumida y plegable</h3>
            <p>Se carga cerrada para que la pantalla respire mejor y puedes abrirla cuando necesites comparar indicadores.</p>
        </div>
        <div class="an-summary-accordion__chips">
            <span class="an-pill">Filas: <strong id="summaryRows">{{ $initialRows->count() }}</strong></span>
            <span class="an-pill an-pill--soft">Rango: <strong id="summaryRange">{{ $defaultDesde }} a {{ $defaultHasta }}</strong></span>
            <span class="an-pill an-pill--soft">Evento: <strong id="summaryEvent">{{ $selectedEvento === 'all' ? 'Todos' : 'Evento ' . $selectedEvento }}</strong></span>
            <span class="an-pill an-pill--soft">Clasificación: <strong id="summaryClasificacion">{{ $selectedClasificacion === 'all' ? 'Todas' : $selectedClasificacion }}</strong></span>
        </div>
    </summary>
    <div class="an-panel an-panel--summary">
        <div class="an-kpis" style="margin-top:0;">
            <article class="an-kpi an-kpi--accent">
                <span>Prestadores</span>
                <strong id="kpiPrestadores">{{ number_format((int) data_get($initialTotals, 'prestadores', 0), 0, ',', '.') }}</strong>
                <small>códigos de habilitación</small>
            </article>
            <article class="an-kpi">
                <span>Usuarios vinculados</span>
                <strong id="kpiUsuarios">{{ number_format((int) data_get($initialTotals, 'usuarios', 0), 0, ',', '.') }}</strong>
                <small>usuarios que han operado el módulo</small>
            </article>
            <article class="an-kpi">
                <span>Casos asignados</span>
                <strong id="kpiAsignados">{{ number_format((int) data_get($initialTotals, 'asignados', 0), 0, ',', '.') }}</strong>
                <small>cohorte de casos del rango</small>
            </article>
            <article class="an-kpi">
                <span>Seguimientos</span>
                <strong id="kpiSeguimientos">{{ number_format((int) data_get($initialTotals, 'seguimientos', 0), 0, ',', '.') }}</strong>
                <small>seguimientos vinculados a la cohorte</small>
            </article>
            <article class="an-kpi">
                <span>Cobertura</span>
                <strong id="kpiCobertura">{{ number_format((float) data_get($initialTotals, 'cobertura', 0), 2, ',', '.') }}%</strong>
                <small>seguimientos sobre asignados</small>
            </article>
            <article class="an-kpi an-kpi--clickable" data-open-sin-seguimiento="1" onclick="window.openSinSeguimientoModal && window.openSinSeguimientoModal()">
                <span>Sin seguimiento</span>
                <button type="button" id="kpiBrecha" class="an-kpi__cta" aria-label="Abrir casos sin seguimiento" onclick="window.openSinSeguimientoModal && window.openSinSeguimientoModal()">{{ number_format((int) data_get($initialTotals, 'brecha', 0), 0, ',', '.') }}</button>
                <small>asignados sin seguimiento</small>
            </article>
        </div>
        <div class="an-insight-grid">
            <article class="an-insight-card an-insight-card--primary">
                <span>Tiempo promedio al primer seguimiento</span>
                <strong id="kpiPrimerSeguimiento">{{ is_null($initialPrimerSeguimiento) ? 'Sin dato' : number_format((float) $initialPrimerSeguimiento, 2, ',', '.') . ' días' }}</strong>
                <small>casos con primera trazabilidad</small>
            </article>
            <article class="an-insight-card">
                <span>Casos con clasificación final</span>
                <strong id="kpiClasificacionFinal">{{ number_format($initialClasificacionFinal, 0, ',', '.') }}</strong>
                <small>último seguimiento con clasificación</small>
            </article>
            <article class="an-insight-card">
                <span>Registros incompletos</span>
                <strong id="kpiCalidadIncompletos">{{ number_format($initialCalidadIncompletos, 0, ',', '.') }}</strong>
                <small>seguimientos con campos faltantes</small>
            </article>
            <article class="an-insight-card">
                <span>Calidad del dato</span>
                <strong id="kpiCalidadDato">{{ number_format($initialCalidadDato, 2, ',', '.') }}%</strong>
                <small>inconsistentes: <strong id="kpiCalidadInconsistentes">{{ number_format($initialCalidadInconsistentes, 0, ',', '.') }}</strong></small>
            </article>
        </div>
    </div>
</details>

<section class="an-panel an-panel--filters" style="margin-top:18px;">
    <form method="GET" action="{{ route('alteraciones.nutricionales.indicadores') }}" class="an-filters">
        <div class="an-field">
            <label>Desde</label>
            <input type="date" class="form-control" name="desde" value="{{ $defaultDesde }}">
        </div>
        <div class="an-field">
            <label>Hasta</label>
            <input type="date" class="form-control" name="hasta" value="{{ $defaultHasta }}">
        </div>
        <div class="an-field">
            <label>Evento</label>
            <select class="form-control" name="evento">
                <option value="all" @selected($selectedEvento === 'all')>Todos</option>
                <option value="113" @selected($selectedEvento === '113')>Evento 113</option>
                <option value="412" @selected($selectedEvento === '412')>Evento 412</option>
                <option value="114" @selected($selectedEvento === '114')>Evento 114</option>
            </select>
        </div>
        <div class="an-field">
            <label>Clasificación</label>
            <select class="form-control" name="clasificacion">
                <option value="all" @selected($selectedClasificacion === 'all')>Todas</option>
                @foreach($classificationOptions as $clasificacion)
                    <option value="{{ data_get($clasificacion, 'label') }}" @selected($selectedClasificacion === data_get($clasificacion, 'label'))>
                        {{ data_get($clasificacion, 'label') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="an-actions">
            <button type="submit" class="btn an-btn an-btn--primary" id="filtersSubmitBtn">
                <i class="fas fa-filter mr-1"></i> Actualizar rango
            </button>
            <a href="{{ route('alteraciones.nutricionales.indicadores') }}" class="btn an-btn an-btn--ghost">
                <i class="fas fa-undo mr-1"></i> Limpiar
            </a>
        </div>
    </form>
</section>

<section class="an-panel an-panel--filters" style="margin-top:18px;">
    <div class="an-table-head an-table-head--table">
        <div>
            <h3>Detalle en tiempo real por prestador</h3>
        </div>
        <div class="an-table-head__actions">
            <span class="an-pill">Mostrando <strong id="tableVisibleCount">{{ $initialRows->count() }}</strong> de <strong id="tableTotalCount">{{ $initialRows->count() }}</strong></span>
        </div>
    </div>

    <div class="an-table-stage">
        <div class="an-table-loading" id="tableLoadingState" aria-hidden="true">
            <div class="spinner-border text-primary" role="status"></div>
            <div>
                <strong>Actualizando indicadores</strong>
                <p>Estamos recalculando la tabla con los filtros seleccionados.</p>
            </div>
        </div>
        <div class="table-responsive an-table-wrap">
        <table class="table table-hover an-table mb-0">
            <thead>
                <tr>
                    <th>Usuario destacado</th>
                    <th>Asignados</th>
                    <th>Seguimientos</th>
                    <th>Sin seguimiento</th>
                    <th>Cobertura</th>
                    <th>1er seguimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="indicadoresTableBody">
                @forelse($initialRows as $row)
                    <tr class="{{ data_get($row, 'codigo') === ($selectedCodigo ?? null) ? 'an-row--selected' : '' }}">
                        <td>
                            <div class="an-code">{{ data_get($row, 'usuario_destacado') }}</div>
                            <div class="an-muted">Código {{ data_get($row, 'codigo') }}</div>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="an-primer-seguimiento-btn an-asignados-btn"
                                data-asignados="1"
                                data-codigo="{{ data_get($row, 'codigo') }}"
                                data-desde="{{ $defaultDesde }}"
                                data-hasta="{{ $defaultHasta }}"
                                data-evento="{{ $selectedEvento }}"
                                data-clasificacion="{{ $selectedClasificacion }}"
                                onclick="window.openAsignadosModal && window.openAsignadosModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                            >
                                {{ number_format((int) data_get($row, 'total_asignados', 0), 0, ',', '.') }}
                            </button>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="an-seguimientos-btn"
                                data-seguimientos="1"
                                data-codigo="{{ data_get($row, 'codigo') }}"
                                data-desde="{{ $defaultDesde }}"
                                data-hasta="{{ $defaultHasta }}"
                                data-evento="{{ $selectedEvento }}"
                                data-clasificacion="{{ $selectedClasificacion }}"
                                onclick="window.openSeguimientosModal && window.openSeguimientosModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                            >
                                {{ number_format((int) data_get($row, 'total_seguimientos', 0), 0, ',', '.') }}
                            </button>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="an-seguimientos-btn an-sin-seguimiento-btn"
                                data-sin-seguimiento="1"
                                data-codigo="{{ data_get($row, 'codigo') }}"
                                data-desde="{{ $defaultDesde }}"
                                data-hasta="{{ $defaultHasta }}"
                                data-evento="{{ $selectedEvento }}"
                                data-clasificacion="{{ $selectedClasificacion }}"
                                onclick="window.openSinSeguimientoModal && window.openSinSeguimientoModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                            >
                                {{ number_format((int) data_get($row, 'sin_seguimiento', data_get($row, 'total_gap', 0)), 0, ',', '.') }}
                            </button>
                            <div class="an-muted">casos sin traza</div>
                        </td>
                        <td>
                            <span class="an-coverage">{{ number_format((float) data_get($row, 'total_cobertura', 0), 2, ',', '.') }}%</span>
                            <div class="an-muted">brecha {{ number_format((int) data_get($row, 'total_gap', 0), 0, ',', '.') }}</div>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="an-primer-seguimiento-btn"
                                data-primer-seguimiento="1"
                                data-codigo="{{ data_get($row, 'codigo') }}"
                                data-desde="{{ $defaultDesde }}"
                                data-hasta="{{ $defaultHasta }}"
                                data-evento="{{ $selectedEvento }}"
                                data-clasificacion="{{ $selectedClasificacion }}"
                                onclick="window.openPrimerSeguimientoModal && window.openPrimerSeguimientoModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                            >
                                {{ is_null(data_get($row, 'primer_seguimiento_promedio')) ? 'Sin dato' : number_format((int) round((float) data_get($row, 'primer_seguimiento_promedio', 0)), 0, ',', '.') . ' días' }}
                            </button>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-trace-button="1"
                                data-codigo="{{ data_get($row, 'codigo') }}"
                                data-desde="{{ $defaultDesde }}"
                                data-hasta="{{ $defaultHasta }}"
                                data-evento="{{ $selectedEvento }}"
                                data-clasificacion="{{ $selectedClasificacion }}"
                                onclick="window.__ALTERACIONES_INDICADORES_TRACE__ && window.__ALTERACIONES_INDICADORES_TRACE__.openTrace(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                            >
                                Ver traza
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No hay datos para el rango seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</section>

<div id="indicadoresTraceApp" class="an-shell an-shell--trace">
    <div id="traceMask" class="an-drawer-mask"></div>
    <aside id="traceDrawer" class="an-drawer" aria-hidden="false">
        <div class="an-drawer__head">
            <div>
                <div class="an-eyebrow">Traza interactiva</div>
                <h3 id="traceTitle">Selecciona un prestador</h3>
                <p id="traceMeta" class="an-drawer__meta">Sin detalle cargado</p>
            </div>
            <button type="button" class="btn an-btn an-btn--ghost" id="traceClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="an-drawer__body">
            <div class="an-drawer-chipbar" id="traceChips">
                <div class="an-chip an-chip--range">
                    <span>Rango</span>
                    <strong id="traceChipRange">Sin rango</strong>
                </div>
                <div class="an-chip an-chip--event">
                    <span>Evento</span>
                    <strong id="traceChipEvent">Todos</strong>
                </div>
                <div class="an-chip an-chip--class">
                    <span>Clasificación</span>
                    <strong id="traceChipClasificacion">Todas</strong>
                </div>
                <div class="an-chip an-chip--code">
                    <span>Código</span>
                    <strong id="traceChipCode">---</strong>
                </div>
            </div>
            <div id="traceSummaryHint" class="an-summary-hint">Mostrando todos los eventos del mismo rango del resumen.</div>

            <div class="an-drawer-toolbar">
                <div class="an-drawer-field">
                    <label>Evento de la traza</label>
                    <select class="form-control" id="traceEventFilter">
                        <option value="all">Todos</option>
                        <option value="113">Evento 113</option>
                        <option value="412">Evento 412</option>
                        <option value="114">Evento 114</option>
                    </select>
                </div>
                <div class="an-drawer-actions">
                    <button type="button" class="btn an-btn an-btn--primary" id="traceRefresh">
                        <i class="fas fa-sync-alt mr-1"></i> Recargar traza
                    </button>
                </div>
            </div>

            <div id="traceLoading" class="an-loader d-none">
                <div class="spinner-border text-info" role="status"></div>
                <span>Cargando traza sin recargar la página...</span>
            </div>

            <div id="traceError" class="alert alert-danger shadow-sm d-none">
                <i class="fas fa-triangle-exclamation mr-1"></i><span id="traceErrorText"></span>
            </div>

            <div id="traceEmpty" class="an-empty-state">
                Selecciona un botón <strong>Ver traza</strong> para abrir el detalle completo sin abandonar el rango de fecha.
            </div>

            <div id="traceContent" class="d-none"></div>
        </div>
    </aside>
</div>

<div id="primerSeguimientoModal" class="an-modal" aria-hidden="true">
    <div class="an-modal__backdrop" data-modal-close="1"></div>
    <div class="an-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="primerSeguimientoModalTitle">
        <div class="an-modal__header">
            <div>
                <div class="an-eyebrow">Primer seguimiento</div>
                <h3 id="primerSeguimientoModalTitle">Detalle del primer seguimiento</h3>
                <p id="primerSeguimientoModalMeta">Selecciona un registro para ver la trazabilidad completa.</p>
            </div>
            <button type="button" class="btn an-btn an-btn--ghost" id="primerSeguimientoClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="an-modal__body">
            <div id="primerSeguimientoLoading" class="an-loader d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <span>Cargando detalle del primer seguimiento...</span>
            </div>
            <div id="primerSeguimientoError" class="alert alert-danger shadow-sm d-none">
                <i class="fas fa-triangle-exclamation mr-1"></i><span id="primerSeguimientoErrorText"></span>
            </div>
            <div id="primerSeguimientoContent" class="an-modal-content d-none"></div>
        </div>
    </div>
</div>

<div id="seguimientosModal" class="an-modal" aria-hidden="true">
    <div class="an-modal__backdrop" data-modal-close="1"></div>
    <div class="an-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="seguimientosModalTitle">
        <div class="an-modal__header">
            <div>
                <div class="an-eyebrow">Seguimientos</div>
                <h3 id="seguimientosModalTitle">Detalle de seguimientos</h3>
                <p id="seguimientosModalMeta">Selecciona un registro para ver la trazabilidad completa.</p>
            </div>
            <button type="button" class="btn an-btn an-btn--ghost" id="seguimientosClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="an-modal__body">
            <div id="seguimientosLoading" class="an-loader d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <span>Cargando detalle de seguimientos...</span>
            </div>
            <div id="seguimientosError" class="alert alert-danger shadow-sm d-none">
                <i class="fas fa-triangle-exclamation mr-1"></i><span id="seguimientosErrorText"></span>
            </div>
            <div id="seguimientosContent" class="an-modal-content d-none"></div>
        </div>
    </div>
</div>

<div id="asignadosModal" class="an-modal" aria-hidden="true">
    <div class="an-modal__backdrop" data-modal-close="1"></div>
    <div class="an-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="asignadosModalTitle">
        <div class="an-modal__header">
            <div>
                <div class="an-eyebrow">Asignados</div>
                <h3 id="asignadosModalTitle">Detalle de asignaciones</h3>
                <p id="asignadosModalMeta">Selecciona un registro para ver la trazabilidad completa.</p>
            </div>
            <button type="button" class="btn an-btn an-btn--ghost" id="asignadosClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="an-modal__body">
            <div id="asignadosLoading" class="an-loader d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <span>Cargando detalle de asignaciones...</span>
            </div>
            <div id="asignadosError" class="alert alert-danger shadow-sm d-none">
                <i class="fas fa-triangle-exclamation mr-1"></i><span id="asignadosErrorText"></span>
            </div>
            <div id="asignadosContent" class="an-modal-content d-none"></div>
        </div>
    </div>
</div>

<div id="sinSeguimientoModal" class="an-modal" aria-hidden="true">
    <div class="an-modal__backdrop" data-modal-close="1"></div>
    <div class="an-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="sinSeguimientoModalTitle">
        <div class="an-modal__header">
            <div>
                <div class="an-eyebrow">Sin seguimiento</div>
                <h3 id="sinSeguimientoModalTitle">Casos sin traza</h3>
                <p id="sinSeguimientoModalMeta">Selecciona un rango para ver los casos sin seguimiento.</p>
            </div>
            <button type="button" class="btn an-btn an-btn--ghost" id="sinSeguimientoClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="an-modal__body">
            <div id="sinSeguimientoLoading" class="an-loader d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <span>Cargando casos sin seguimiento...</span>
            </div>
            <div id="sinSeguimientoError" class="alert alert-danger shadow-sm d-none">
                <i class="fas fa-triangle-exclamation mr-1"></i><span id="sinSeguimientoErrorText"></span>
            </div>
            <div id="sinSeguimientoContent" class="an-modal-content d-none"></div>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    [v-cloak]{display:none;}
    .an-shell{
        font-family:'Sora',sans-serif;
        padding:8px 2px 24px;
        color:#0f172a;
        background:
            radial-gradient(circle at top left, rgba(14,165,233,.14), transparent 28%),
            radial-gradient(circle at top right, rgba(34,197,94,.14), transparent 26%),
            linear-gradient(135deg, #f8fbff 0%, #eef5ff 48%, #f7fbf5 100%);
    }
    .an-hero{
        display:flex;
        justify-content:space-between;
        gap:16px;
        align-items:center;
        padding:22px 24px;
        border-radius:26px;
        background:linear-gradient(135deg,#0f172a 0%,#13274a 48%,#0f766e 100%);
        color:#e2e8f0;
        box-shadow:0 22px 60px rgba(15,23,42,.24);
    }
    .an-hero__brand{display:flex;align-items:center;gap:14px;min-width:0;flex:1 1 auto;}
    .an-hero__copy{min-width:0;}
    .an-hero__logo-wrap{
        width:72px;height:72px;border-radius:20px;padding:9px;
        background:rgba(255,255,255,.12);backdrop-filter:blur(10px);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.18);
    }
    .an-hero__logo{width:100%;height:100%;object-fit:contain;background:#fff;border-radius:14px;padding:6px;}
    .an-eyebrow{font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:#93c5fd;font-weight:800;}
    .an-title{font-size:1.62rem;font-weight:800;line-height:1.1;margin:4px 0 6px;}
    .an-subtitle{margin:0;color:rgba(226,232,240,.92);max-width:840px;}
    .an-hero__actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;align-items:flex-start;max-width:380px;}
    .an-panel{
        margin-top:14px;
        background:#fff;
        border:1px solid #dbe7f3;
        border-radius:20px;
        box-shadow:0 10px 24px rgba(15,23,42,.05);
    }
    .an-panel--filters{padding:18px;}
    .an-panel--summary{padding:18px;margin-top:0;border-radius:0 0 20px 20px;}
    .an-summary-accordion{
        margin-top:14px;
        margin-bottom:18px;
        border:1px solid #dbe7f3;
        border-radius:22px;
        overflow:hidden;
        background:linear-gradient(180deg,#fdfefe 0%,#f7fbff 100%);
        box-shadow:0 10px 24px rgba(15,23,42,.05);
    }
    .an-summary-accordion > summary{
        list-style:none;
        cursor:pointer;
        padding:18px 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        background:linear-gradient(135deg,#f8fbff 0%,#edf5ff 100%);
    }
    .an-summary-accordion > summary::-webkit-details-marker{display:none;}
    .an-summary-accordion__head h3{margin:4px 0 6px;font-size:1.06rem;font-weight:800;color:#0f172a;}
    .an-summary-accordion__head p{margin:0;color:#64748b;max-width:840px;}
    .an-summary-accordion__chips{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}
    .an-summary-accordion[open] > summary{border-bottom:1px solid #e5eef8;}
    .an-filters{
        display:grid;
        grid-template-columns:repeat(4,minmax(160px,1fr));
        gap:12px;
        align-items:end;
    }
    .an-field label{
        display:block;
        margin-bottom:5px;
        font-size:.74rem;
        font-weight:800;
        color:#334155;
    }
    .an-actions{
        grid-column:span 4;
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        padding-top:2px;
    }
    .an-btn{
        border-radius:12px;
        font-weight:800;
        font-size:.84rem;
        padding:10px 14px;
    }
    .an-btn--primary{
        color:#fff;
        border:none;
        background:linear-gradient(135deg,#0ea5e9,#2563eb);
        box-shadow:0 12px 24px rgba(37,99,235,.28);
    }
    .an-btn--ghost{
        background:#fff;
        border:1px solid #cbd5e1;
        color:#334155;
    }
    .an-kpis{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:14px;
        margin-top:14px;
    }
    .an-insight-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:12px;
        margin-top:14px;
    }
    .an-insight-card{
        background:linear-gradient(180deg,#ffffff 0%,#f7fbff 100%);
        border:1px solid #dce7f5;
        border-radius:18px;
        padding:14px 16px;
        box-shadow:0 10px 24px rgba(15,23,42,.05);
        position:relative;
        overflow:hidden;
        min-height:108px;
    }
    .an-insight-card::before{
        content:'';
        position:absolute;
        inset:0 auto auto 0;
        width:100%;
        height:4px;
        background:linear-gradient(90deg,#0ea5e9,#22c55e);
    }
    .an-insight-card--primary::before{
        background:linear-gradient(90deg,#2563eb,#0ea5e9);
    }
    .an-insight-card span{
        display:block;
        font-size:.72rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#475569;
    }
    .an-insight-card strong{
        display:block;
        margin-top:6px;
        font-size:1.18rem;
        line-height:1.15;
        color:#0f172a;
    }
    .an-insight-card small{
        display:block;
        margin-top:6px;
        color:#64748b;
    }
    .an-kpi{
        background:#fff;
        border:1px solid #dce7f5;
        border-radius:20px;
        padding:16px 16px 14px;
        box-shadow:0 10px 24px rgba(15,23,42,.05);
        position:relative;
        overflow:hidden;
        min-height:116px;
    }
    .an-kpi__cta{
        border:none;
        padding:0;
        background:transparent;
        color:#0f172a;
        font:inherit;
        font-size:1.5rem;
        font-weight:900;
        line-height:1.1;
        cursor:pointer;
        text-align:left;
        width:100%;
        position:relative;
        z-index:1;
    }
    .an-kpi__cta:hover{
        color:#1d4ed8;
        text-decoration:underline;
    }
    .an-kpi__cta:focus-visible{
        outline:3px solid rgba(37,99,235,.25);
        outline-offset:4px;
        border-radius:10px;
    }
    .an-kpi--clickable{
        cursor:pointer;
        transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .an-kpi--clickable:hover{
        transform:translateY(-1px);
        border-color:#93c5fd;
        box-shadow:0 14px 28px rgba(37,99,235,.10);
    }
    .an-kpi::after{
        content:'';
        position:absolute;
        inset:auto -20px -36px auto;
        width:96px;height:96px;border-radius:999px;
        background:radial-gradient(circle, rgba(14,165,233,.16), transparent 70%);
        pointer-events:none;
    }
    .an-kpi--clickable > *{
        position:relative;
        z-index:1;
    }
    .an-kpi span{display:block;font-size:.74rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.04em;}
    .an-kpi strong{display:block;font-size:1.35rem;line-height:1.1;margin-top:6px;color:#0f172a;}
    .an-kpi small{display:block;margin-top:5px;color:#64748b;}
    .an-event-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:10px;
        margin-top:14px;
    }
    .an-event-card{
        background:linear-gradient(180deg,#ffffff 0%,#f7fbff 100%);
        border:1px solid #dce7f5;
        border-radius:18px;
        padding:14px;
        box-shadow:0 10px 20px rgba(15,23,42,.05);
    }
    .an-event-card__head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;}
    .an-event-card__head h3{margin:2px 0 0;font-size:1.15rem;font-weight:800;color:#0f172a;}
    .an-event-card__label{font-size:.74rem;font-weight:800;color:#0f766e;text-transform:uppercase;letter-spacing:.05em;}
    .an-event-card__metrics{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
        margin-top:12px;
    }
    .an-event-card__metrics span{display:block;font-size:.72rem;color:#64748b;}
    .an-event-card__metrics strong{font-size:1.1rem;color:#0f172a;}
    .an-pill{
        display:inline-flex;
        align-items:center;
        gap:4px;
        padding:7px 11px;
        border-radius:999px;
        background:#dbeafe;
        color:#1d4ed8;
        font-size:.75rem;
        font-weight:800;
        white-space:nowrap;
    }
    .an-pill--soft{background:#ecfeff;color:#0f766e;}
    .an-charts{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
        margin-top:14px;
    }
    .an-chart-card{
        background:#fff;
        border:1px solid #dce7f5;
        border-radius:18px;
        padding:14px;
        min-height:320px;
        box-shadow:0 10px 20px rgba(15,23,42,.05);
    }
    .an-card-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        margin-bottom:10px;
    }
    .an-card-head h3{
        margin:0;
        font-size:1rem;
        font-weight:800;
        color:#0f172a;
    }
    .an-table-panel{padding:18px;}
    .an-table-head{
        display:flex;
        justify-content:space-between;
        gap:10px;
        align-items:flex-start;
        margin-bottom:12px;
    }
    .an-table-head--summary{
        padding-bottom:12px;
        border-bottom:1px solid #e7eff8;
        margin-bottom:16px;
    }
    .an-table-head--table{
        padding-bottom:10px;
        border-bottom:1px solid #e7eff8;
    }
    .an-table-head h3{margin:0;font-size:1.05rem;font-weight:800;color:#0f172a;}
    .an-table-head p{margin:4px 0 0;color:#64748b;}
    .an-table-head__actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}
    .an-table-stage{position:relative;}
    .an-table-wrap{border-radius:18px;overflow:auto;border:1px solid #dce7f5;box-shadow:inset 0 1px 0 rgba(255,255,255,.7);}
    .an-table-loading{
        position:absolute;
        inset:0;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:14px;
        background:rgba(255,255,255,.72);
        backdrop-filter:blur(4px);
        border-radius:18px;
        z-index:3;
        opacity:0;
        pointer-events:none;
        transition:opacity .2s ease;
    }
    .an-table-loading.is-visible{
        opacity:1;
        pointer-events:auto;
    }
    .an-table-loading strong{display:block;font-size:.95rem;color:#0f172a;}
    .an-table-loading p{margin:2px 0 0;color:#64748b;font-size:.82rem;}
    .an-table{margin-bottom:0;}
    .an-table tbody td:nth-child(6),
    .an-table tbody td:nth-child(7),
    .an-table tbody td:nth-child(8){
        min-width:140px;
    }
    .an-table thead th{
        position:sticky;top:0;z-index:1;
        background:linear-gradient(180deg,#eef6ff 0%,#eaf3ff 100%);
        color:#0f172a;
        border-bottom:1px solid #dce7f5 !important;
        font-size:.78rem;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    .an-table tbody tr{cursor:pointer;transition:background .18s ease, transform .18s ease;}
    .an-table tbody tr:hover{background:#f8fbff;transform:translateY(-1px);}
    .an-table tbody td{vertical-align:top;border-color:#edf2f7;padding-top:14px;padding-bottom:14px;}
    .an-code{font-weight:800;color:#0f172a;font-size:.94rem;}
    .an-muted{font-size:.76rem;color:#64748b;}
    .an-number{font-size:1rem;font-weight:800;color:#0f172a;}
    .an-coverage{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:84px;
        padding:7px 11px;
        border-radius:999px;
        font-weight:800;
        font-size:.78rem;
    }
    .an-coverage--good{background:#dcfce7;color:#166534;}
    .an-coverage--warn{background:#fef3c7;color:#92400e;}
    .an-coverage--bad{background:#fee2e2;color:#991b1b;}
    .an-tags{display:flex;gap:6px;flex-wrap:wrap;}
    .an-tag{
        display:inline-flex;
        align-items:center;
        padding:5px 9px;
        border-radius:999px;
        font-size:.72rem;
        font-weight:800;
        background:#dbeafe;
        color:#1e3a8a;
    }
    .an-tag--soft{background:#ecfeff;color:#0f766e;}
    .an-row--selected td{background:#eefbf7 !important;}
    .an-loader{
        display:flex;
        align-items:center;
        gap:10px;
        padding:16px;
        color:#334155;
    }
    .an-filters.is-loading{
        opacity:.9;
    }
    .an-filters.is-loading .an-btn--primary{
        pointer-events:none;
        opacity:.9;
    }
    .an-drawer-mask{
        position:fixed;
        inset:0;
        background:rgba(2,6,23,.42);
        z-index:1040;
        opacity:0;
        pointer-events:none;
        transition:opacity .25s ease;
    }
    .an-drawer-mask.is-open{opacity:1;pointer-events:auto;}
    .an-drawer{
        position:fixed;
        top:0;
        right:-760px;
        width:720px;
        max-width:96vw;
        height:100vh;
        z-index:1045;
        background:#fff;
        box-shadow:-18px 0 42px rgba(2,6,23,.22);
        transition:right .28s ease;
        display:flex;
        flex-direction:column;
    }
    .an-drawer.is-open{right:0;}
    .an-drawer__head{
        padding:16px;
        border-bottom:1px solid #dce7f5;
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:10px;
        background:linear-gradient(135deg,#f8fbff,#eef6ff);
    }
    .an-drawer__head h3{margin:4px 0 0;font-size:1.18rem;font-weight:800;color:#0f172a;}
    .an-drawer__meta{
        margin:6px 0 0;
        font-size:.84rem;
        color:#475569;
        line-height:1.35;
    }
    .an-drawer__body{padding:16px;overflow:auto;}
    .an-drawer-toolbar{
        display:flex;
        gap:12px;
        align-items:end;
        justify-content:space-between;
        flex-wrap:wrap;
        margin-bottom:14px;
        padding:12px;
        border:1px solid #dce7f5;
        border-radius:16px;
        background:linear-gradient(180deg,#f8fbff,#ffffff);
    }
    .an-drawer-chipbar{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:10px;
        margin-bottom:12px;
    }
    .an-summary-hint{
        margin:0 0 12px;
        padding:10px 12px;
        border-radius:14px;
        background:#eff6ff;
        border:1px solid #dbeafe;
        color:#1e3a8a;
        font-size:.82rem;
        font-weight:700;
    }
    .an-chip{
        border-radius:18px;
        padding:12px 14px;
        border:1px solid #dbe7f3;
        box-shadow:0 8px 20px rgba(15,23,42,.05);
        background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
    }
    .an-chip span{
        display:block;
        font-size:.72rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#64748b;
        margin-bottom:5px;
    }
    .an-chip strong{
        display:block;
        font-size:.98rem;
        line-height:1.25;
        font-weight:800;
        color:#0f172a;
        word-break:break-word;
    }
    .an-chip--range{background:linear-gradient(135deg,#eff6ff,#ffffff);}
    .an-chip--event{background:linear-gradient(135deg,#ecfeff,#ffffff);}
    .an-chip--class{background:linear-gradient(135deg,#faf5ff,#ffffff);}
    .an-chip--code{background:linear-gradient(135deg,#f0fdf4,#ffffff);}
    .an-drawer-field{
        min-width:240px;
        flex:1 1 280px;
    }
    .an-drawer-field label{
        display:block;
        margin-bottom:6px;
        font-size:.74rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#334155;
    }
    .an-drawer-actions{
        display:flex;
        justify-content:flex-end;
        flex:0 0 auto;
    }
    .an-drawer__summary{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:10px;
    }
    .an-mini-card{
        background:#fff;
        border:1px solid #dce7f5;
        border-radius:16px;
        padding:12px;
        box-shadow:0 6px 16px rgba(15,23,42,.05);
    }
    .an-mini-card span{display:block;font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:800;letter-spacing:.04em;}
    .an-mini-card strong{display:block;margin-top:6px;font-size:1.08rem;color:#0f172a;}
    .an-drawer-block{margin-top:16px;}
    .an-drawer-block h4{margin:0 0 10px;font-size:.98rem;font-weight:800;color:#0f172a;}
    .an-user-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
    .an-user-chip{
        background:linear-gradient(180deg,#ffffff,#f8fbff);
        border:1px solid #dce7f5;
        border-radius:14px;
        padding:10px 12px;
    }
    .an-user-chip--warn{
        background:linear-gradient(180deg,#fffdf5,#fff8e7);
        border-color:#fde68a;
    }
    .an-user-chip strong{display:block;font-size:.88rem;color:#0f172a;}
    .an-user-chip small{display:block;color:#64748b;margin-top:4px;}
    .an-event-breakdown{
        border:1px solid #dce7f5;
        border-radius:16px;
        padding:12px;
        margin-bottom:10px;
        background:#fff;
    }
    .an-event-breakdown__head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;}
    .an-event-breakdown__grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }
    .an-event-breakdown__grid span{display:block;font-size:.72rem;color:#64748b;}
    .an-event-breakdown__grid strong{display:block;margin-top:4px;font-size:1rem;color:#0f172a;}
    .an-empty-state{
        padding:26px 18px;
        border:1px dashed #cbd5e1;
        border-radius:18px;
        background:#f8fbff;
        color:#334155;
        text-align:center;
        line-height:1.6;
    }
    .an-followup-list{
        display:flex;
        flex-direction:column;
        gap:12px;
    }
    .an-followup-empty{
        padding:16px 18px;
        border:1px dashed #cbd5e1;
        border-radius:16px;
        background:#f8fbff;
        color:#475569;
        font-weight:700;
    }
    .an-followup-card{
        border:1px solid #dbe7f3;
        border-radius:18px;
        background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
        box-shadow:0 10px 22px rgba(15,23,42,.05);
        padding:14px 16px;
    }
    .an-followup-card__top{
        display:flex;
        justify-content:space-between;
        gap:12px;
        align-items:flex-start;
        margin-bottom:12px;
    }
    .an-followup-card__badge{
        display:inline-flex;
        align-items:center;
        padding:4px 9px;
        border-radius:999px;
        background:#dbeafe;
        color:#1d4ed8;
        font-size:.72rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
        margin-bottom:6px;
    }
    .an-followup-card h5{
        margin:0;
        font-size:1rem;
        font-weight:800;
        color:#0f172a;
    }
    .an-followup-card p{
        margin:4px 0 0;
        color:#64748b;
        font-size:.85rem;
    }
    .an-followup-card__chips{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:6px;
    }
    .an-followup-card__chips span{
        display:inline-flex;
        padding:5px 10px;
        border-radius:999px;
        background:#ecfeff;
        color:#0f766e;
        font-size:.74rem;
        font-weight:800;
    }
    .an-followup-card__grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:10px;
        margin-top:6px;
    }
    .an-followup-card__grid div{
        padding:10px 12px;
        border:1px solid #e2e8f0;
        border-radius:14px;
        background:#fff;
    }
    .an-followup-card__grid label{
        display:block;
        font-size:.7rem;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#64748b;
        font-weight:800;
    }
    .an-followup-card__grid strong{
        display:block;
        margin-top:4px;
        color:#0f172a;
        font-size:.92rem;
    }
    .an-followup-card__obs{
        margin-top:12px;
        border:1px solid #dbe7f3;
        border-radius:14px;
        background:#f8fbff;
        overflow:hidden;
        transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .an-followup-card__obs:hover{
        transform:translateY(-1px);
        border-color:#93c5fd;
        box-shadow:0 10px 20px rgba(37,99,235,.10);
    }
    .an-followup-card__obs[open]{
        border-color:#93c5fd;
        box-shadow:0 12px 24px rgba(37,99,235,.12);
    }
    .an-followup-card__obs summary{
        cursor:pointer;
        position:relative;
        list-style:none;
        padding:12px 38px 12px 12px;
        font-size:.8rem;
        font-weight:800;
        color:#1d4ed8;
        background:linear-gradient(135deg,#eff6ff,#f8fbff);
        transition:background .18s ease, color .18s ease;
    }
    .an-followup-card__obs summary::after{
        content:'›';
        position:absolute;
        right:12px;
        top:50%;
        transform:translateY(-50%) rotate(90deg);
        font-size:1.2rem;
        line-height:1;
        color:#2563eb;
        transition:transform .18s ease, color .18s ease;
    }
    .an-followup-card__obs:hover summary{
        background:linear-gradient(135deg,#dbeafe,#eff6ff);
        color:#1e40af;
    }
    .an-followup-card__obs:hover summary::after{
        color:#1d4ed8;
    }
    .an-followup-card__obs[open] summary::after{
        transform:translateY(-50%) rotate(270deg);
    }
    .an-followup-card__obs summary::-webkit-details-marker{display:none;}
    .an-followup-card__preview,
    .an-followup-card__fullobs{
        padding:12px;
        color:#334155;
        line-height:1.6;
        white-space:pre-wrap;
        word-break:break-word;
    }
    .an-followup-card__fullobs{
        border-top:1px solid #e2e8f0;
        background:#fff;
    }
    .an-followup-card__preview{
        font-weight:600;
    }
    .an-primer-seguimiento-btn{
        border:none;
        background:transparent;
        padding:0;
        font-weight:800;
        color:#111827;
        cursor:pointer;
        text-align:left;
    }
    .an-primer-seguimiento-btn:hover{
        color:#2563eb;
        text-decoration:underline;
    }
    .an-asignados-btn{
        border:none;
        background:transparent;
        padding:0;
        font-weight:800;
        color:#111827;
        cursor:pointer;
        text-align:left;
        font-size:1rem;
        display:inline-block;
    }
    .an-asignados-btn:hover{
        color:#2563eb;
        text-decoration:underline;
    }
    .an-asignados-btn:focus-visible{
        outline:2px solid rgba(37,99,235,.22);
        outline-offset:2px;
    }
    .an-seguimientos-btn{
        border:none;
        background:linear-gradient(135deg,#eff6ff,#ffffff);
        border:1px solid #bfdbfe;
        color:#1d4ed8;
        font-weight:900;
        font-size:1rem;
        padding:10px 12px;
        border-radius:14px;
        min-width:72px;
        box-shadow:0 8px 18px rgba(37,99,235,.08);
        transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        cursor:pointer;
    }
    .an-seguimientos-btn:hover{
        transform:translateY(-1px);
        background:linear-gradient(135deg,#dbeafe,#eff6ff);
        border-color:#60a5fa;
        box-shadow:0 12px 20px rgba(37,99,235,.16);
    }
    .an-seguimientos-btn:focus-visible{
        outline:3px solid rgba(59,130,246,.28);
        outline-offset:2px;
    }
    .an-modal{
        position:fixed;
        inset:0;
        z-index:1055;
        display:none;
    }
    .an-modal.is-open{display:block;}
    .an-modal__backdrop{
        position:absolute;
        inset:0;
        background:rgba(2,6,23,.48);
        backdrop-filter:blur(4px);
    }
    .an-modal__dialog{
        position:relative;
        width:min(1400px, calc(100vw - 28px));
        margin:5vh auto;
        background:#fff;
        border-radius:24px;
        box-shadow:0 24px 64px rgba(2,6,23,.24);
        overflow:hidden;
        display:flex;
        flex-direction:column;
        max-height:90vh;
    }
    .an-modal__header{
        padding:18px 20px;
        border-bottom:1px solid #e2e8f0;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
        background:linear-gradient(135deg,#f8fbff,#eef6ff);
    }
    .an-modal__header h3{
        margin:4px 0 6px;
        font-size:1.15rem;
        font-weight:800;
        color:#0f172a;
    }
    .an-modal__header p{
        margin:0;
        color:#64748b;
    }
    .an-modal__body{
        padding:18px 20px 22px;
        overflow:auto;
    }
    .an-modal-content{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
    }
    .an-modal-card{
        border:1px solid #dbe7f3;
        border-radius:18px;
        padding:14px 16px;
        background:linear-gradient(180deg,#ffffff,#f8fbff);
        box-shadow:0 10px 20px rgba(15,23,42,.05);
    }
    .an-modal-card span{
        display:block;
        font-size:.72rem;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#64748b;
        font-weight:800;
    }
    .an-modal-card strong{
        display:block;
        margin-top:6px;
        font-size:1rem;
        color:#0f172a;
    }
    .an-modal-card small{
        display:block;
        margin-top:6px;
        color:#64748b;
    }
    .an-modal-card--wide{grid-column:1 / -1;}
    .an-follow-layout{
        display:flex;
        flex-direction:column;
        gap:18px;
        width:100%;
    }
    .an-modal-full{
        grid-column:1 / -1;
    }
    .an-follow-summary{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
    }
    .an-follow-summary__card{
        border:1px solid #dbe7f3;
        border-radius:20px;
        padding:16px 18px;
        background:linear-gradient(180deg,#ffffff,#f8fbff);
        box-shadow:0 10px 20px rgba(15,23,42,.05);
        min-height:110px;
    }
    .an-follow-summary__card--wide{grid-column:1 / -1;}
    .an-follow-summary__card span{
        display:block;
        font-size:.73rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#64748b;
    }
    .an-follow-summary__card strong{
        display:block;
        margin-top:6px;
        font-size:1.05rem;
        font-weight:800;
        color:#0f172a;
        line-height:1.3;
    }
    .an-follow-summary__card small{
        display:block;
        margin-top:6px;
        color:#64748b;
        font-size:.82rem;
        line-height:1.35;
    }
    .an-follow-context{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
    }
    .an-follow-context__item{
        border:1px solid #dbe7f3;
        border-radius:18px;
        padding:14px 16px;
        background:linear-gradient(180deg,#ffffff,#fbfdff);
    }
    .an-follow-context__item span{
        display:block;
        font-size:.72rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#64748b;
    }
    .an-follow-context__item strong{
        display:block;
        margin-top:6px;
        font-size:1rem;
        color:#0f172a;
        line-height:1.4;
    }
    .an-follow-list{
        display:flex;
        flex-direction:column;
        gap:14px;
    }
    .an-follow-list__head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding:2px 2px 0;
    }
    .an-follow-list__head h4{
        margin:0;
        font-size:1rem;
        font-weight:800;
        color:#0f172a;
    }
    .an-follow-scrollbar{
        position:sticky;
        top:0;
        z-index:2;
        height:18px;
        overflow-x:auto;
        overflow-y:hidden;
        border:1px solid #dbe7f3;
        border-radius:999px;
        background:linear-gradient(180deg,#f8fbff,#eef4ff);
        box-shadow:0 6px 14px rgba(15,23,42,.05);
        margin-bottom:10px;
    }
    .an-follow-scrollbar__inner{
        height:1px;
    }
    .an-follow-table-wrap{
        border:1px solid #dbe7f3;
        border-radius:20px;
        overflow:auto;
        background:#fff;
        box-shadow:0 12px 24px rgba(15,23,42,.05);
    }
    .an-follow-table{
        width:100%;
        min-width:0;
        table-layout:fixed;
        border-collapse:separate;
        border-spacing:0;
    }
    .an-follow-table thead th{
        position:sticky;
        top:0;
        z-index:1;
        background:linear-gradient(180deg,#f8fbff,#eef4ff);
        color:#36517e;
        font-size:.76rem;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.04em;
        padding:14px 12px;
        border-bottom:1px solid #d8e4f2;
        white-space:nowrap;
    }
    .an-follow-table tbody td{
        padding:14px 12px;
        border-bottom:1px solid #edf2f7;
        vertical-align:top;
        color:#0f172a;
        font-size:.92rem;
        line-height:1.45;
        background:#fff;
        overflow-wrap:anywhere;
    }
    .an-follow-table thead th:nth-child(1),
    .an-follow-table tbody td:nth-child(1){width:10%;}
    .an-follow-table thead th:nth-child(2),
    .an-follow-table tbody td:nth-child(2){width:18%;}
    .an-follow-table thead th:nth-child(3),
    .an-follow-table tbody td:nth-child(3){width:5%;}
    .an-follow-table thead th:nth-child(4),
    .an-follow-table tbody td:nth-child(4){width:11%;}
    .an-follow-table thead th:nth-child(5),
    .an-follow-table tbody td:nth-child(5){width:7%;}
    .an-follow-table thead th:nth-child(6),
    .an-follow-table tbody td:nth-child(6){width:19%;}
    .an-follow-table thead th:nth-child(7),
    .an-follow-table tbody td:nth-child(7){width:7%;}
    .an-follow-table thead th:nth-child(8),
    .an-follow-table tbody td:nth-child(8){width:7%;}
    .an-follow-table thead th:nth-child(9),
    .an-follow-table tbody td:nth-child(9){width:7%;}
    .an-follow-table thead th:nth-child(10),
    .an-follow-table tbody td:nth-child(10){width:4%;}
    .an-follow-table thead th:nth-child(11),
    .an-follow-table tbody td:nth-child(11){width:5%;}
    .an-follow-table__row.is-first td{
        background:linear-gradient(180deg,#f9fcff,#f4f8ff);
    }
    .an-follow-table__detail td{
        background:#fbfdff;
        padding:12px 12px 16px;
    }
    .an-follow-observation{
        border:1px solid #dbe7f3;
        border-radius:16px;
        padding:12px 14px;
        background:linear-gradient(180deg,#ffffff,#f8fbff);
    }
    .an-follow-observation span{
        display:block;
        font-size:.7rem;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#6b7aa6;
        margin-bottom:6px;
    }
    .an-follow-observation strong{
        display:block;
        color:#1e293b;
        white-space:pre-wrap;
        line-height:1.55;
    }
    .an-status-chip,
    .an-class-chip{
        display:inline-flex;
        align-items:center;
        min-height:28px;
        padding:5px 10px;
        border-radius:999px;
        background:#eefcfb;
        border:1px solid #bceeed;
        color:#0f766e;
        font-size:.76rem;
        font-weight:800;
        line-height:1;
    }
    .an-event-chipbar{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        align-items:center;
    }
    .an-event-chip{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:5px 10px;
        border-radius:999px;
        border:1px solid #dbe7f3;
        background:#fff;
        color:#334155;
        font-size:.74rem;
        font-weight:900;
        line-height:1;
        box-shadow:0 8px 16px rgba(15,23,42,.05);
    }
    .an-class-chip.is-event-113,
    .an-event-chip.is-event-113,
    .an-pill.is-event-113{background:#eff6ff;border-color:#93c5fd;color:#1d4ed8;}
    .an-class-chip.is-event-412,
    .an-event-chip.is-event-412,
    .an-pill.is-event-412{background:#ecfeff;border-color:#67e8f9;color:#0f766e;}
    .an-class-chip.is-event-114,
    .an-event-chip.is-event-114,
    .an-pill.is-event-114{background:#fff7ed;border-color:#fdba74;color:#c2410c;}
    .an-class-chip.is-event-generic,
    .an-event-chip.is-event-generic,
    .an-pill.is-event-generic{background:#f8fafc;border-color:#cbd5e1;color:#334155;}
    .an-follow-card{
        border:1px solid #d8e4f2;
        border-radius:22px;
        background:linear-gradient(180deg,#ffffff,#f7fbff);
        box-shadow:0 14px 28px rgba(15,23,42,.06);
        padding:16px 18px 18px;
        display:flex;
        flex-direction:column;
        gap:14px;
    }
    .an-follow-card.is-first{
        border-color:#bfd7ff;
        box-shadow:0 18px 34px rgba(37,99,235,.10);
    }
    .an-follow-card__head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:14px;
    }
    .an-follow-card__heading{
        min-width:0;
    }
    .an-follow-card__eyebrow{
        font-size:.72rem;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#6b7aa6;
        margin-bottom:4px;
    }
    .an-follow-card__eyebrow.is-event-113{color:#1d4ed8;}
    .an-follow-card__eyebrow.is-event-412{color:#0f766e;}
    .an-follow-card__eyebrow.is-event-114{color:#c2410c;}
    .an-follow-card__eyebrow.is-event-generic{color:#475569;}
    .an-follow-card__title{
        margin:0;
        font-size:1.02rem;
        font-weight:900;
        color:#0f172a;
        line-height:1.35;
    }
    .an-follow-card__meta{
        margin:4px 0 0;
        color:#64748b;
        font-size:.92rem;
        line-height:1.35;
    }
    .an-follow-card__identity{
        min-width:0;
    }
    .an-follow-card__identity h5{
        margin:0;
        font-size:1.08rem;
        font-weight:900;
        color:#0f172a;
        line-height:1.35;
    }
    .an-follow-card__identity p{
        margin:6px 0 0;
        color:#64748b;
        font-size:.88rem;
        line-height:1.45;
    }
    .an-follow-card__meta{
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:8px;
        flex:0 0 auto;
    }
    .an-follow-card__chips{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }
    .an-follow-card__chips span{
        display:inline-flex;
        align-items:center;
        padding:5px 10px;
        border-radius:999px;
        border:1px solid #dbe7f3;
        background:#fff;
        color:#334155;
        font-size:.74rem;
        font-weight:800;
    }
    .an-follow-card__grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:12px;
    }
    .an-follow-chip{
        border:1px solid #dbe7f3;
        border-radius:16px;
        padding:12px 13px;
        background:#fff;
        min-height:72px;
    }
    .an-follow-chip span{
        display:block;
        font-size:.68rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#6b7aa6;
    }
    .an-follow-chip strong{
        display:block;
        margin-top:6px;
        color:#0f172a;
        font-size:.96rem;
        line-height:1.35;
    }
    .an-follow-card__notes{
        border:1px solid #dbe7f3;
        border-radius:18px;
        padding:14px 16px;
        background:linear-gradient(180deg,#f8fbff,#ffffff);
    }
    .an-follow-card__notes span{
        display:block;
        font-size:.72rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#64748b;
        margin-bottom:6px;
    }
    .an-follow-card__notes strong{
        display:block;
        color:#0f172a;
        font-size:.95rem;
        line-height:1.55;
        white-space:pre-wrap;
    }
    .an-follow-card__notes summary{
        cursor:pointer;
        list-style:none;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }
    .an-follow-card__notes summary::-webkit-details-marker{display:none;}
    .an-follow-card__notes summary::after{
        content:'Ver';
        font-size:.72rem;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#1d4ed8;
        padding:4px 8px;
        border-radius:999px;
        background:#eff6ff;
        border:1px solid #dbeafe;
    }
    .an-follow-card__notes[open] summary::after{
        content:'Ocultar';
    }
    .an-follow-card__notes-body{
        margin-top:10px;
        padding:12px 14px;
        border:1px solid #dbe7f3;
        border-radius:14px;
        background:#fff;
        color:#1e293b;
        line-height:1.6;
        white-space:pre-wrap;
    }
    .an-follow-hero{
        display:grid;
        grid-template-columns:minmax(0,1.3fr) minmax(360px,.9fr);
        gap:16px;
        padding:18px;
        border:1px solid #dbe7f3;
        border-radius:24px;
        background:linear-gradient(135deg,#f8fbff 0%,#eef6ff 52%,#f6fff8 100%);
        box-shadow:0 14px 28px rgba(15,23,42,.05);
    }
    .an-follow-hero__copy h4{
        margin:6px 0 8px;
        font-size:1.18rem;
        line-height:1.25;
        font-weight:900;
        color:#0f172a;
    }
    .an-follow-hero__copy p{
        margin:0;
        color:#475569;
        line-height:1.65;
        max-width:72ch;
    }
    .an-follow-hero__eyebrow,
    .an-follow-section__eyebrow{
        font-size:.72rem;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.12em;
        color:#0f766e;
    }
    .an-follow-hero__stats{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }
    .an-follow-hero__stats article{
        border:1px solid #dbe7f3;
        border-radius:18px;
        padding:14px 16px;
        background:#fff;
        box-shadow:0 10px 20px rgba(15,23,42,.05);
    }
    .an-follow-hero__stats span{
        display:block;
        font-size:.7rem;
        font-weight:900;
        letter-spacing:.05em;
        text-transform:uppercase;
        color:#64748b;
    }
    .an-follow-hero__stats strong{
        display:block;
        margin-top:6px;
        color:#0f172a;
        font-size:1rem;
        font-weight:800;
        line-height:1.4;
    }
    .an-follow-section{
        display:flex;
        flex-direction:column;
        gap:14px;
    }
    .an-follow-section__head{
        display:flex;
        justify-content:space-between;
        gap:12px;
        align-items:flex-end;
    }
    .an-follow-section__head h4{
        margin:4px 0 0;
        font-size:1.02rem;
        font-weight:900;
        color:#0f172a;
    }
    .an-patient-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
    }
    .an-patient-card{
        display:flex;
        gap:14px;
        border:1px solid #dbe7f3;
        border-radius:22px;
        padding:16px;
        background:linear-gradient(180deg,#ffffff,#f8fbff);
        box-shadow:0 12px 24px rgba(15,23,42,.05);
    }
    .an-patient-card.is-first{
        border-color:#a5f3fc;
        box-shadow:0 16px 30px rgba(6,182,212,.12);
    }
    .an-patient-card__avatar{
        width:56px;
        height:56px;
        flex:0 0 auto;
        border-radius:18px;
        display:grid;
        place-items:center;
        background:linear-gradient(135deg,#0ea5e9,#14b8a6);
        color:#fff;
        font-weight:900;
        letter-spacing:.08em;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.2);
    }
    .an-patient-card__body{
        min-width:0;
        flex:1 1 auto;
    }
    .an-patient-card__top{
        display:flex;
        justify-content:space-between;
        gap:10px;
        align-items:flex-start;
    }
    .an-patient-card__heading{
        min-width:0;
    }
    .an-patient-card__heading h5{
        margin:0;
        font-size:1.04rem;
        line-height:1.35;
        font-weight:900;
        color:#0f172a;
    }
    .an-patient-card__heading p{
        margin:5px 0 0;
        color:#64748b;
        font-size:.86rem;
        line-height:1.45;
    }
    .an-patient-card__grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
        margin-top:12px;
    }
    .an-patient-card__grid div{
        padding:10px 12px;
        border:1px solid #e2e8f0;
        border-radius:14px;
        background:#fff;
    }
    .an-patient-card__grid span{
        display:block;
        font-size:.68rem;
        font-weight:900;
        letter-spacing:.05em;
        text-transform:uppercase;
        color:#64748b;
    }
    .an-patient-card__grid strong{
        display:block;
        margin-top:4px;
        color:#0f172a;
        font-size:.92rem;
        line-height:1.45;
    }
    .an-patient-card__grid--wide{
        grid-column:1 / -1;
    }
    .an-follow-records{
        display:flex;
        flex-direction:column;
        gap:14px;
    }
    .an-shell--trace{
        position:relative;
        z-index:1042;
    }
    @media (max-width: 1200px){
        .an-filters{grid-template-columns:repeat(3,minmax(160px,1fr));}
        .an-actions{grid-column:span 3;}
        .an-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}
        .an-insight-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
        .an-event-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
        .an-charts{grid-template-columns:1fr;}
        .an-drawer__summary{grid-template-columns:repeat(2,minmax(0,1fr));}
        .an-drawer-chipbar{grid-template-columns:1fr;}
        .an-follow-summary{grid-template-columns:repeat(2,minmax(0,1fr));}
        .an-follow-context{grid-template-columns:1fr;}
        .an-follow-card__grid{grid-template-columns:repeat(2,minmax(0,1fr));}
        .an-follow-hero{grid-template-columns:1fr;}
        .an-patient-grid{grid-template-columns:1fr;}
    }
    @media (max-width: 768px){
        .an-hero{flex-direction:column;align-items:flex-start;}
        .an-filters{grid-template-columns:1fr;}
        .an-actions{grid-column:span 1;}
        .an-kpis{grid-template-columns:1fr;}
        .an-insight-grid{grid-template-columns:1fr;}
        .an-event-grid{grid-template-columns:1fr;}
        .an-user-list{grid-template-columns:1fr;}
        .an-drawer{width:100vw;max-width:100vw;}
        .an-followup-card__grid{grid-template-columns:1fr;}
        .an-drawer-chipbar{grid-template-columns:1fr;}
        .an-follow-summary{grid-template-columns:1fr;}
        .an-follow-card__grid{grid-template-columns:1fr;}
        .an-follow-card__head{flex-direction:column;}
        .an-follow-list__head{flex-direction:column;align-items:flex-start;}
        .an-modal__dialog{width:calc(100vw - 16px);max-width:100vw;}
        .an-follow-table{min-width:940px;}
        .an-follow-scrollbar{top:0;}
        .an-follow-hero__stats{grid-template-columns:1fr;}
        .an-follow-section__head{flex-direction:column;align-items:flex-start;}
        .an-patient-card{flex-direction:column;}
        .an-patient-card__top{flex-direction:column;}
        .an-patient-card__grid{grid-template-columns:1fr;}
    }
</style>
@stop

@section('js')
<script>
window.__ALTERACIONES_INDICADORES__ = {!! json_encode([
    'initialFrom' => $defaultDesde,
    'initialTo' => $defaultHasta,
    'initialPayload' => $initialPayload ?? null,
    'apiUrl' => route('alteraciones.nutricionales.indicadores.data'),
    'traceUrl' => route('alteraciones.nutricionales.indicadores.trace'),
    'eventOrder' => ['113', '412', '114'],
    'eventLabels' => [
        '113' => 'Evento 113',
        '412' => 'Evento 412',
        '114' => 'Evento 114',
    ],
    'palette' => ['#0ea5e9', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#1d4ed8'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
</script>
@vite(['resources/js/app.js'])
@stop
