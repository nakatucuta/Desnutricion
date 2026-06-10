@extends('adminlte::page')

@section('title', 'Seguimientos')

@php
    $hasFilters = filled(request('tip_ide_')) || filled(request('num_ide_')) || filled(request('fec_desde')) || filled(request('fec_hasta'));
@endphp

@section('content_header')
<div class="seg-hub-hero">
    <div class="seg-hub-hero__brand">
        <div class="seg-hub-hero__logo-wrap">
            <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo PAI" class="seg-hub-hero__logo">
        </div>
        <div>
            <span class="seg-hub-eyebrow">Centro de control</span>
            <h1 class="seg-hub-hero__title">Seguimientos evento 549</h1>
            <p class="seg-hub-hero__subtitle">Visualiza pendientes, seguimientos realizados y alertas vencidas en una sola vista, con un diseño mas claro y operativo.</p>
        </div>
    </div>
    <div class="seg-hub-hero__status">
        <span class="seg-hub-chip">Vista integral</span>
        <span class="seg-hub-chip seg-hub-chip--strong">Operacion activa</span>
    </div>
</div>

<div class="modal fade" id="modalPrestadoresCaso" tabindex="-1" role="dialog" aria-labelledby="modalPrestadoresCasoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrestadoresCasoLabel">Prestadores asignados</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                            </tr>
                        </thead>
                        <tbody id="modalPrestadoresCasoBody">
                            <tr>
                                <td colspan="2" class="text-center text-muted">Sin datos</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="segHubToastWrap" class="seg-hub-toast-wrap" aria-live="polite" aria-atomic="true"></div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <div class="seg-hub-glance mb-4">
                <div class="seg-hub-stat">
                    <span class="seg-hub-stat__label">Pendientes visibles</span>
                    <strong class="seg-hub-stat__value" id="kpiAsignados">0</strong>
                    <small class="seg-hub-stat__hint">Casos listos para iniciar o continuar</small>
                </div>
                <div class="seg-hub-stat">
                    <span class="seg-hub-stat__label">Seguimientos visibles</span>
                    <strong class="seg-hub-stat__value" id="kpiRealizados">0</strong>
                    <small class="seg-hub-stat__hint">Registros ya diligenciados</small>
                </div>
                <div class="seg-hub-stat seg-hub-stat--danger">
                    <span class="seg-hub-stat__label">Alertas activas</span>
                    <strong class="seg-hub-stat__value" id="kpiAlertas">0</strong>
                    <small class="seg-hub-stat__hint">Hitos vencidos que requieren accion</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card seg-hub-card mb-4">
        <div class="card-header border-0 pb-0">
            <span class="seg-hub-eyebrow">Tablero operativo</span>
            <h3 class="seg-hub-section-title mb-3">Indicadores del evento 549</h3>
        </div>
        <div class="card-body pt-2">
            <div class="seg-kpi-filters mb-3">
                <div>
                    <label class="seg-hub-label mb-1">Desde</label>
                    <input type="date" id="indFecDesde" class="form-control seg-hub-input">
                </div>
                <div>
                    <label class="seg-hub-label mb-1">Hasta</label>
                    <input type="date" id="indFecHasta" class="form-control seg-hub-input">
                </div>
                <div class="d-flex align-items-end">
                    <button type="button" id="btnAplicarIndicadores" class="btn seg-hub-btn seg-hub-btn--primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Aplicar periodo
                    </button>
                    <button type="button" id="btnLimpiarIndicadores" class="btn seg-hub-btn seg-hub-btn--ghost">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                </div>
                <div class="d-flex align-items-end">
                    <small class="text-muted" id="indPeriodoAplicado">Periodo: sin filtro</small>
                </div>
            </div>

            <div class="seg-kpi-grid mb-3">
                <div class="seg-kpi-tile">
                    <span>Oportunidad notificacion</span>
                    <strong id="indOportunidad">0%</strong>
                </div>
                <div class="seg-kpi-tile">
                    <span>Casos con 3+ criterios</span>
                    <strong id="indCriterios">0%</strong>
                </div>
                <div class="seg-kpi-tile">
                    <span>Letalidad estimada</span>
                    <strong id="indLetalidad">0%</strong>
                </div>
                <div class="seg-kpi-tile">
                    <span>Casos alto riesgo</span>
                    <strong id="indAltoRiesgo">0</strong>
                </div>
                <div class="seg-kpi-tile seg-kpi-tile--danger">
                    <span>Super inmediata vencida</span>
                    <strong id="indSuperVencida">0</strong>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="seg-mini-card">
                        <h5>Causas agrupadas</h5>
                        <ul id="listCausas" class="seg-mini-list"></ul>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="seg-mini-card">
                        <h5>Top IPS</h5>
                        <ul id="listIps" class="seg-mini-list"></ul>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="seg-mini-card">
                        <h5>Top EAPB</h5>
                        <ul id="listEapb" class="seg-mini-list"></ul>
                    </div>
                </div>
                <div class="col-md-6 col-xl-6 mb-3 mb-xl-0">
                    <div class="seg-mini-card">
                        <h5>Top municipio residencia</h5>
                        <ul id="listMunicipio" class="seg-mini-list"></ul>
                    </div>
                </div>
                <div class="col-md-6 col-xl-6">
                    <div class="seg-mini-card">
                        <h5>Semana epidemiologica</h5>
                        <ul id="listSemana" class="seg-mini-list"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card seg-hub-card seg-hub-card--filters mb-4">
        <div class="card-body">
            <div class="seg-hub-filter-head">
                <div>
                    <span class="seg-hub-eyebrow">Filtros y salida</span>
                    <h3 class="seg-hub-section-title">Consulta y exportacion</h3>
                    <p class="seg-hub-section-subtitle mb-0">Aplica filtros por identificacion o rango de fechas y exporta el consolidado actual a Excel.</p>
                </div>
                @if($hasFilters)
                    <span class="seg-hub-chip seg-hub-chip--soft">Filtros activos</span>
                @endif
            </div>

            <form method="GET" action="{{ route('seguimientos.export') }}" class="row align-items-end" id="segHubFilterForm">
                <div class="col-md-3 mb-3">
                    <label class="seg-hub-label">Tipo ID</label>
                    <input type="text" name="tip_ide_" id="filterTipIde" class="form-control seg-hub-input" placeholder="Ej: CC" value="{{ request('tip_ide_') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="seg-hub-label">Numero ID</label>
                    <input type="text" name="num_ide_" id="filterNumIde" class="form-control seg-hub-input" placeholder="Documento" value="{{ request('num_ide_') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="seg-hub-label">Desde</label>
                    <input type="date" name="fec_desde" id="filterFecDesde" class="form-control seg-hub-input" value="{{ request('fec_desde') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="seg-hub-label">Hasta</label>
                    <input type="date" name="fec_hasta" id="filterFecHasta" class="form-control seg-hub-input" value="{{ request('fec_hasta') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <button class="btn seg-hub-btn seg-hub-btn--primary btn-block mb-2" type="button" id="btnFiltrarPanel">
                        <i class="fas fa-search mr-1"></i> Filtrar panel
                    </button>
                    <button class="btn seg-hub-btn seg-hub-btn--ghost btn-block" type="submit">
                        <i class="fas fa-file-excel mr-1"></i> Exportar
                    </button>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="button" id="btnLimpiarPanel" class="btn seg-hub-btn seg-hub-btn--ghost">
                        <i class="fas fa-sync-alt mr-1"></i> Limpiar filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card seg-hub-card">
        <div class="card-header border-0 pb-0">
            <span class="seg-hub-eyebrow">Panel operativo</span>
            <h3 class="seg-hub-section-title mb-3">Ruta de seguimiento</h3>

            <ul class="nav seg-hub-tabs" id="seguimientosTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-asignados" data-toggle="tab" href="#pane-asignados" role="tab">
                        <span class="seg-hub-tabs__title">Asignaciones pendientes</span>
                        <span class="seg-hub-tabs__meta" id="tabCountAsignados">0 visibles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-realizados" data-toggle="tab" href="#pane-realizados" role="tab">
                        <span class="seg-hub-tabs__title">Seguimientos realizados</span>
                        <span class="seg-hub-tabs__meta" id="tabCountRealizados">0 visibles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-alertas" data-toggle="tab" href="#pane-alertas" role="tab">
                        <span class="seg-hub-tabs__title">Alertas vencidas</span>
                        <span class="seg-hub-tabs__meta seg-hub-tabs__meta--danger" id="tabCountAlertas">0 activas</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body pt-4">
            <div class="tab-content" id="seguimientosTabContent">
                <div class="tab-pane fade show active" id="pane-asignados" role="tabpanel">
                    <div class="seg-hub-pane-head">
                        <div>
                            <h4 class="seg-hub-pane-title">Asignaciones pendientes</h4>
                            <p class="seg-hub-pane-subtitle">Casos disponibles para iniciar seguimiento o continuar el ultimo registro abierto.</p>
                        </div>
                    </div>
                    <div class="table-responsive seg-hub-table-wrap">
                        <table id="tabla-asignados" class="table table-hover w-100 seg-hub-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente</th>
                                    <th>Tipo ID</th>
                                    <th>Numero ID</th>
                                    <th>Evento</th>
                                    <th>Fec. notif.</th>
                                    <th>Prestador</th>
                                    <th style="width:140px;">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-realizados" role="tabpanel">
                    <div class="seg-hub-pane-head">
                        <div>
                            <h4 class="seg-hub-pane-title">Seguimientos realizados</h4>
                            <p class="seg-hub-pane-subtitle">Historico de seguimientos diligenciados con acceso directo para continuar o corregir informacion.</p>
                        </div>
                    </div>
                    <div class="table-responsive seg-hub-table-wrap">
                        <table id="tabla-realizados" class="table table-hover w-100 seg-hub-table">
                            <thead>
                                <tr>
                                    <th>ID Seg.</th>
                                    <th>Caso</th>
                                    <th>Paciente</th>
                                    <th>Tipo ID</th>
                                    <th>Numero ID</th>
                                    <th>Prestador</th>
                                    <th>Ultimo hito</th>
                                    <th>Creado</th>
                                    <th style="width:210px;">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-alertas" role="tabpanel">
                    <div class="seg-hub-alert-banner">
                        <div class="seg-hub-alert-banner__icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <strong class="d-block mb-1">Alertas por seguimiento vencido</strong>
                            <span>Se priorizan hitos incumplidos de 48-72h, 7, 14, 21 y 28 dias, ademas de controles a 6 meses y 1 ano.</span>
                        </div>
                    </div>
                    <div class="table-responsive seg-hub-table-wrap">
                        <table id="tabla-alertas" class="table table-hover w-100 seg-hub-table">
                            <thead>
                                <tr>
                                    <th>ID Seg.</th>
                                    <th>Caso</th>
                                    <th>Paciente</th>
                                    <th>Tipo ID</th>
                                    <th>Numero ID</th>
                                    <th>Prestador</th>
                                    <th>Vencido</th>
                                    <th>Riesgo clinico</th>
                                    <th>Semaforo</th>
                                    <th>Temporizador</th>
                                    <th>Fecha limite</th>
                                    <th>Dias atraso</th>
                                    <th style="width:180px;">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .seg-hub-hero{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1.5rem;
        margin-bottom:1.5rem;
        padding:1.4rem 1.6rem;
        border-radius:26px;
        background:
            radial-gradient(circle at top right, rgba(66, 214, 151, .18), transparent 32%),
            radial-gradient(circle at left center, rgba(27, 154, 170, .16), transparent 28%),
            linear-gradient(135deg, #0f5560, #127b88 52%, #17a36b);
        color:#fff;
        box-shadow:0 18px 34px rgba(16, 78, 89, .22);
    }
    .seg-hub-hero__brand{
        display:flex;
        align-items:center;
        gap:1rem;
    }
    .seg-hub-hero__logo-wrap{
        display:flex;
        align-items:center;
        justify-content:center;
        width:74px;
        height:74px;
        border-radius:22px;
        background:rgba(255,255,255,.14);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.25);
        backdrop-filter:blur(8px);
        flex-shrink:0;
    }
    .seg-hub-hero__logo{
        width:48px;
        height:auto;
    }
    .seg-hub-eyebrow{
        display:inline-block;
        margin-bottom:.45rem;
        font-size:.78rem;
        font-weight:800;
        letter-spacing:.08em;
        text-transform:uppercase;
        opacity:.78;
    }
    .seg-hub-hero__title{
        margin:0;
        font-size:2rem;
        font-weight:800;
        line-height:1.08;
    }
    .seg-hub-hero__subtitle{
        margin:.35rem 0 0;
        max-width:760px;
        font-size:.98rem;
        line-height:1.55;
        color:rgba(255,255,255,.88);
    }
    .seg-hub-hero__status{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:.6rem;
    }
    .seg-hub-chip{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:.5rem .85rem;
        border-radius:999px;
        background:rgba(255,255,255,.14);
        color:#fff;
        font-size:.78rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        border:1px solid rgba(255,255,255,.16);
        white-space:nowrap;
    }
    .seg-hub-chip--strong{
        background:#dff9ee;
        border-color:#dff9ee;
        color:#0d7a63;
    }
    .seg-hub-chip--soft{
        background:#e8f7fb;
        border-color:#cfe8ef;
        color:#15616d;
    }
    .seg-hub-glance{
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:1rem;
    }
    .seg-hub-stat{
        padding:1.15rem 1.2rem;
        border-radius:22px;
        background:linear-gradient(180deg, #ffffff, #f4fbfd);
        border:1px solid #deedf1;
        box-shadow:0 10px 24px rgba(17, 74, 87, .06);
    }
    .seg-hub-stat--danger{
        background:linear-gradient(180deg, #fff8f8, #fff1f1);
        border-color:#f3d7d7;
    }
    .seg-hub-stat__label{
        display:block;
        font-size:.82rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.05em;
        color:#65818a;
    }
    .seg-hub-stat__value{
        display:block;
        margin:.25rem 0;
        font-size:2rem;
        font-weight:800;
        color:#163a44;
    }
    .seg-hub-stat--danger .seg-hub-stat__value{
        color:#b84747;
    }
    .seg-hub-stat__hint{
        color:#78919a;
    }
    .seg-hub-card{
        border:none;
        border-radius:24px;
        box-shadow:0 16px 32px rgba(18, 60, 72, .08);
        overflow:hidden;
    }
    .seg-hub-card--filters{
        background:linear-gradient(180deg, #ffffff, #f8fcfd);
    }
    .seg-hub-filter-head,
    .seg-hub-pane-head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:1rem;
    }
    .seg-hub-section-title{
        margin:0;
        font-size:1.35rem;
        font-weight:800;
        color:#173f49;
    }
    .seg-hub-section-subtitle,
    .seg-hub-pane-subtitle{
        color:#6a858e;
        line-height:1.55;
    }
    .seg-hub-label{
        display:block;
        margin-bottom:.45rem;
        font-weight:700;
        color:#15616d;
    }
    .seg-hub-input{
        min-height:46px;
        border-radius:14px;
        border:1px solid #d6e7eb;
        background:#fbfeff;
    }
    .seg-hub-input:focus{
        border-color:#1b9aaa;
        box-shadow:0 0 0 .2rem rgba(27,154,170,.12);
    }
    .seg-hub-btn{
        min-height:46px;
        padding:.75rem 1rem;
        border-radius:14px;
        font-weight:700;
        border:none;
    }
    .seg-hub-btn--primary{
        color:#fff;
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        box-shadow:0 12px 22px rgba(18, 122, 111, .18);
    }
    .seg-hub-btn--primary:hover{
        color:#fff;
        filter:brightness(.98);
    }
    .seg-hub-btn--ghost{
        color:#15616d;
        background:#eef8fb;
        border:1px solid #d4e7ed;
    }
    .seg-hub-tabs{
        display:flex;
        flex-wrap:wrap;
        gap:.85rem;
        border:none;
    }
    .seg-hub-tabs .nav-item{
        margin-bottom:0;
    }
    .seg-hub-tabs .nav-link{
        min-width:240px;
        padding:1rem 1.1rem;
        border:none;
        border-radius:18px;
        background:linear-gradient(180deg, #f7fbfd, #eef6f8);
        color:#35515b;
        box-shadow:inset 0 0 0 1px #deebef;
    }
    .seg-hub-tabs .nav-link.active{
        background:linear-gradient(135deg, #0f7c8a, #17a36b);
        color:#fff;
        box-shadow:0 14px 26px rgba(21, 137, 132, .22);
    }
    .seg-hub-tabs__title{
        display:block;
        font-weight:800;
        line-height:1.3;
    }
    .seg-hub-tabs__meta{
        display:block;
        margin-top:.2rem;
        font-size:.8rem;
        color:#6c8791;
    }
    .seg-hub-tabs .nav-link.active .seg-hub-tabs__meta{
        color:rgba(255,255,255,.85);
    }
    .seg-hub-tabs__meta--danger{
        color:#c94b4b;
    }
    .seg-hub-pane-title{
        margin:0 0 .2rem;
        font-size:1.15rem;
        font-weight:800;
        color:#173f49;
    }
    .seg-hub-table-wrap{
        padding:.4rem;
        border-radius:22px;
        background:linear-gradient(180deg, #f9fcfd, #f4f9fb);
        border:1px solid #e2eef1;
    }
    .seg-hub-table{
        margin-bottom:0 !important;
        border-collapse:separate;
        border-spacing:0;
        overflow:hidden;
    }
    .seg-hub-table thead th{
        border-top:none;
        border-bottom:1px solid #ddebee !important;
        background:#eef7f9;
        color:#45626b;
        font-size:.8rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    .seg-hub-table tbody tr{
        background:#fff;
    }
    .seg-hub-table.table-hover tbody tr:hover{
        background:#f2fbfd;
    }
    .seg-hub-table td:last-child{
        white-space:nowrap;
    }
    .seg-hub-table .btn.btn-sm{
        min-width:34px;
        height:32px;
        border-radius:10px;
        border:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 8px 16px rgba(19, 78, 90, .14);
        transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
    }
    .seg-hub-table .btn.btn-sm:hover{
        transform:translateY(-1px);
        box-shadow:0 10px 20px rgba(19, 78, 90, .22);
        filter:brightness(1.02);
    }
    .seg-hub-table .btn.btn-info{
        background:linear-gradient(135deg, #1f9fb5, #1f78b5);
    }
    .seg-hub-table .btn.btn-warning{
        color:#24323b;
        background:linear-gradient(135deg, #f4cd3b, #f0ab2e);
    }
    .seg-hub-table .btn.btn-danger{
        background:linear-gradient(135deg, #f35d66, #de2f4c);
    }
    .seg-hub-table .js-delete-async{
        position:relative;
    }
    .seg-hub-table tr.seg-row-deleting{
        background:linear-gradient(90deg, rgba(23,163,107,.08), rgba(15,124,138,.05));
    }
    .seg-hub-table tr.seg-row-deleting td{
        position:relative;
    }
    .seg-hub-table tr.seg-row-deleting td:last-child::after{
        content:'Eliminando...';
        display:inline-flex;
        align-items:center;
        gap:.45rem;
        margin-left:.5rem;
        padding:.35rem .65rem;
        border-radius:999px;
        font-size:.74rem;
        font-weight:800;
        letter-spacing:.03em;
        color:#fff;
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        box-shadow:0 8px 16px rgba(15, 124, 138, .26);
    }
    .seg-hub-alert-banner{
        display:flex;
        align-items:flex-start;
        gap:1rem;
        margin-bottom:1rem;
        padding:1rem 1.1rem;
        border-radius:18px;
        background:linear-gradient(135deg, #fff8ef, #fff3e8);
        border:1px solid #f4ddbd;
        color:#805c24;
    }
    .seg-hub-alert-banner__icon{
        display:flex;
        align-items:center;
        justify-content:center;
        width:44px;
        height:44px;
        border-radius:14px;
        background:#fff;
        color:#c6841f;
        box-shadow:0 8px 18px rgba(198, 132, 31, .12);
        flex-shrink:0;
    }
    .dataTables_wrapper .dataTables_paginate .pagination{
        margin:0;
    }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select{
        border-radius:12px;
        border:1px solid #d5e5ea;
        background:#fff;
    }
    .dataTables_wrapper .dataTables_info{
        color:#69828b;
    }
    .gap-2{
        gap:.5rem;
    }
    .seg-kpi-grid{
        display:grid;
        gap:.75rem;
        grid-template-columns:repeat(5,minmax(0,1fr));
    }
    .seg-kpi-filters{
        display:grid;
        gap:.75rem;
        grid-template-columns:180px 180px auto 1fr;
        align-items:end;
    }
    .seg-kpi-tile{
        border:1px solid #deebef;
        border-radius:16px;
        padding:.8rem .9rem;
        background:linear-gradient(180deg,#fff,#f6fbfd);
    }
    .seg-kpi-tile--danger{
        background:linear-gradient(180deg,#fff9f9,#fff1f1);
        border-color:#f3d7d7;
    }
    .seg-kpi-tile span{
        display:block;
        font-size:.75rem;
        color:#6c8791;
        text-transform:uppercase;
        letter-spacing:.04em;
        font-weight:800;
    }
    .seg-kpi-tile strong{
        font-size:1.25rem;
        color:#173f49;
    }
    .seg-mini-card{
        border:1px solid #e1edf1;
        border-radius:16px;
        padding:.9rem;
        background:#fff;
        height:100%;
    }
    .seg-mini-card h5{
        font-size:.95rem;
        font-weight:800;
        color:#234a54;
        margin-bottom:.6rem;
    }
    .seg-mini-list{
        list-style:none;
        padding-left:0;
        margin:0;
        max-height:210px;
        overflow:auto;
    }
    .seg-mini-list li{
        display:flex;
        justify-content:space-between;
        gap:.8rem;
        border-bottom:1px dashed #e3edf1;
        padding:.35rem 0;
        font-size:.87rem;
        color:#49656e;
    }
    .seg-mini-list li:last-child{
        border-bottom:none;
    }
    .seg-mini-list .empty{
        color:#8aa0a8;
    }
    .seg-prestadores-link{
        font-weight:700;
        color:#0f7c8a;
        text-decoration:none;
    }
    .seg-prestadores-link:hover{
        color:#0c5f6a;
        text-decoration:underline;
    }
    .seg-hub-toast-wrap{
        position:fixed;
        top:1rem;
        right:1rem;
        z-index:2100;
        display:flex;
        flex-direction:column;
        gap:.65rem;
    }
    .seg-hub-toast{
        min-width:280px;
        max-width:420px;
        border-radius:14px;
        box-shadow:0 14px 30px rgba(17, 63, 73, .22);
        border:1px solid transparent;
        padding:.75rem .9rem;
        color:#113f49;
        background:#eef8fb;
    }
    .seg-hub-toast--success{
        background:#e9f9ef;
        border-color:#bfe9cb;
        color:#0f6b4d;
    }
    .seg-hub-toast--error{
        background:#fff0f0;
        border-color:#f0c8c8;
        color:#9f2d2d;
    }
    .seg-hub-toast__title{
        font-size:.85rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
        margin-bottom:.15rem;
    }
    .seg-hub-toast__text{
        font-size:.92rem;
        font-weight:600;
        line-height:1.35;
    }
    .js-delete-async .btn[disabled]{
        opacity:.88;
        cursor:progress;
        transform:none;
        box-shadow:0 6px 12px rgba(19, 78, 90, .18);
    }
    @media (max-width: 991px){
        .seg-hub-hero,
        .seg-hub-filter-head,
        .seg-hub-pane-head{
            flex-direction:column;
        }
        .seg-hub-glance{
            grid-template-columns:1fr;
        }
        .seg-hub-tabs .nav-link{
            min-width:unset;
            width:100%;
        }
        .seg-kpi-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
        .seg-kpi-filters{
            grid-template-columns:1fr 1fr;
        }
    }
    @media (max-width: 767px){
        .seg-hub-hero__brand{
            align-items:flex-start;
        }
        .seg-hub-hero__title{
            font-size:1.6rem;
        }
        .seg-hub-hero,
        .seg-hub-card .card-body,
        .seg-hub-card .card-header{
            padding-left:1rem;
            padding-right:1rem;
        }
        .seg-kpi-grid{
            grid-template-columns:1fr;
        }
        .seg-kpi-filters{
            grid-template-columns:1fr;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function () {
    function showHubToast(type, title, message) {
        const wrap = $('#segHubToastWrap');
        if (!wrap.length) {
            return;
        }
        const safeType = type === 'error' ? 'error' : 'success';
        const $toast = $('<div class="seg-hub-toast seg-hub-toast--' + safeType + '" role="status"></div>');
        $toast.append('<div class="seg-hub-toast__title">' + (title || '') + '</div>');
        $toast.append('<div class="seg-hub-toast__text">' + (message || '') + '</div>');
        wrap.append($toast);
        setTimeout(function () {
            $toast.fadeOut(260, function () {
                $(this).remove();
            });
        }, 3400);
    }

    function updateCounter(table, kpiSelector, tabSelector, suffix) {
        if (!table) {
            return;
        }

        const info = table.page.info();
        const total = info ? info.recordsDisplay : 0;

        $(kpiSelector).text(total);
        $(tabSelector).text(total + ' ' + suffix);
    }

    function renderList(listSelector, items) {
        if (!items || !items.length) {
            $(listSelector).html('<li class="empty">Sin datos disponibles</li>');
            return;
        }

        const html = items.map(function (item) {
            return '<li><span>' + item.label + '</span><strong>' + item.count + '</strong></li>';
        }).join('');
        $(listSelector).html(html);
    }

    function panelFilters() {
        return {
            tip_ide_: $('#filterTipIde').val(),
            num_ide_: $('#filterNumIde').val(),
            fec_desde: $('#filterFecDesde').val(),
            fec_hasta: $('#filterFecHasta').val()
        };
    }

    function reloadPanelTables() {
        dtAsignados.ajax.reload(null, false);
        dtRealizados.ajax.reload(null, false);
        dtAlertas.ajax.reload(null, false);
    }

    function loadIndicadores() {
        const desde = $('#indFecDesde').val();
        const hasta = $('#indFecHasta').val();
        const params = {};
        if (desde) {
            params.fec_desde = desde;
        }
        if (hasta) {
            params.fec_hasta = hasta;
        }

        $.getJSON('{{ route("seguimientos.indicadores.data") }}', params)
            .done(function (payload) {
                const totals = payload.totales || {};
                const periodo = payload.filtro_periodo || {};
                $('#indOportunidad').text((totals.oportunidad_notificacion_pct ?? 0) + '%');
                $('#indCriterios').text((totals.casos_3_criterios_pct ?? 0) + '%');
                $('#indLetalidad').text((totals.letalidad_pct ?? 0) + '%');
                $('#indAltoRiesgo').text(totals.alto_riesgo ?? 0);
                $('#indSuperVencida').text(totals.super_inmediata_vencida ?? 0);

                if (periodo.desde || periodo.hasta) {
                    $('#indPeriodoAplicado').text('Periodo: ' + (periodo.desde || '...') + ' a ' + (periodo.hasta || '...'));
                } else {
                    $('#indPeriodoAplicado').text('Periodo: sin filtro');
                }

                renderList('#listCausas', payload.causas_agrupadas || []);
                renderList('#listIps', payload.por_ips || []);
                renderList('#listEapb', payload.por_eapb || []);
                renderList('#listMunicipio', payload.por_municipio || []);
                renderList('#listSemana', payload.por_semana || []);
            })
            .fail(function () {
                $('#indPeriodoAplicado').text('Periodo: sin filtro');
                renderList('#listCausas', []);
                renderList('#listIps', []);
                renderList('#listEapb', []);
                renderList('#listMunicipio', []);
                renderList('#listSemana', []);
            });
    }

    const commonLanguage = {
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Sin registros',
        zeroRecords: 'No se encontraron resultados',
        processing: 'Cargando...',
        paginate: {
            first: 'Primero',
            last: 'Ultimo',
            next: 'Siguiente',
            previous: 'Anterior'
        }
    };

    const dtAsignados = $('#tabla-asignados').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("seguimientos.asignados.data") }}',
            data: function (d) {
                Object.assign(d, panelFilters());
            }
        },
        language: commonLanguage,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'paciente', name: 'paciente' },
            { data: 'tip_ide_', name: 'tip_ide_' },
            { data: 'num_ide_', name: 'num_ide_' },
            { data: 'nom_eve', name: 'nom_eve' },
            { data: 'fec_not', name: 'fec_not' },
            { data: 'prestador', name: 'prestador' },
            { data: 'acciones', name: 'acciones', orderable:false, searchable:false }
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            updateCounter(dtAsignados, '#kpiAsignados', '#tabCountAsignados', 'visibles');
        }
    });

    const dtRealizados = $('#tabla-realizados').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("seguimientos.realizados.data") }}',
            data: function (d) {
                Object.assign(d, panelFilters());
            }
        },
        language: commonLanguage,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'asignacion_id', name: 'asignacion_id', title: 'Caso' },
            { data: 'paciente', name: 'paciente' },
            { data: 'tip_ide_', name: 'tip_ide_' },
            { data: 'num_ide_', name: 'num_ide_' },
            { data: 'prestador', name: 'prestador' },
            { data: 'ultimo_hito', name: 'ultimo_hito' },
            { data: 'created_at', name: 'created_at' },
            { data: 'acciones', name: 'acciones', orderable:false, searchable:false }
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            updateCounter(dtRealizados, '#kpiRealizados', '#tabCountRealizados', 'visibles');
        }
    });

    const dtAlertas = $('#tabla-alertas').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("seguimientos.alertas.data") }}',
            data: function (d) {
                Object.assign(d, panelFilters());
            }
        },
        language: commonLanguage,
        columns: [
            { data: 'id', name: 'id', title: 'ID Seg.' },
            { data: 'asignacion_id', name: 'asignacion_id', title: 'Caso' },
            { data: 'paciente', name: 'paciente', title: 'Paciente' },
            { data: 'tip_ide_', name: 'tip_ide_', title: 'Tipo ID' },
            { data: 'num_ide_', name: 'num_ide_', title: 'Numero ID' },
            { data: 'prestador', name: 'prestador', title: 'Prestador' },
            { data: 'hito', name: 'hito', title: 'Vencido' },
            { data: 'riesgo', name: 'riesgo', title: 'Riesgo clinico', orderable:false, searchable:false },
            { data: 'semaforo', name: 'semaforo', title: 'Semaforo', orderable:false, searchable:false },
            { data: 'temporizador', name: 'temporizador', title: 'Temporizador', orderable:false, searchable:false },
            { data: 'fecha_limite', name: 'fecha_limite', title: 'Fecha limite' },
            { data: 'dias_atraso', name: 'dias_atraso', title: 'Dias atraso' },
            { data: 'acciones', name: 'acciones', orderable:false, searchable:false, title: 'Acciones' }
        ],
        order: [[10, 'desc']],
        drawCallback: function () {
            updateCounter(dtAlertas, '#kpiAlertas', '#tabCountAlertas', 'activas');
        }
    });

    loadIndicadores();

    $('#btnFiltrarPanel').on('click', function () {
        reloadPanelTables();
    });

    $('#segHubFilterForm input').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            reloadPanelTables();
        }
    });

    $('#btnLimpiarPanel').on('click', function () {
        $('#filterTipIde').val('');
        $('#filterNumIde').val('');
        $('#filterFecDesde').val('');
        $('#filterFecHasta').val('');
        reloadPanelTables();
    });

    $('#btnAplicarIndicadores').on('click', function () {
        loadIndicadores();
    });

    $('#btnLimpiarIndicadores').on('click', function () {
        $('#indFecDesde').val('');
        $('#indFecHasta').val('');
        loadIndicadores();
    });

    $(document).on('submit', '.js-delete-async', function (e) {
        e.preventDefault();

        const $form = $(this);
        if ($form.data('busy')) {
            return;
        }

        const confirmMsg = $form.data('confirm') || 'Confirmar eliminacion?';
        if (!window.confirm(confirmMsg)) {
            return;
        }

        const $btn = $form.find('button[type="submit"]').first();
        const $row = $form.closest('tr');
        const originalHtml = $btn.html();
        const originalTitle = $btn.attr('title') || '';
        $form.data('busy', true);
        $row.addClass('seg-row-deleting');
        $btn.prop('disabled', true).attr('title', 'Eliminando...').html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).done(function (response) {
            showHubToast('success', 'Proceso completado', (response && response.message) ? response.message : 'Registro eliminado correctamente.');
            dtAsignados.ajax.reload(null, false);
            dtRealizados.ajax.reload(null, false);
            dtAlertas.ajax.reload(null, false);
            loadIndicadores();
        }).fail(function (xhr) {
            let message = 'No fue posible eliminar en este momento.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showHubToast('error', 'Error al eliminar', message);
            $row.removeClass('seg-row-deleting');
            $form.data('busy', false);
            $btn.prop('disabled', false).attr('title', originalTitle).html(originalHtml);
        });
    });

    $(document).on('click', '.seg-prestadores-link', function () {
        let prestadores = [];
        const raw = $(this).attr('data-prestadores') || '[]';
        try {
            prestadores = JSON.parse(raw);
        } catch (e) {
            prestadores = [];
        }

        const total = Array.isArray(prestadores) ? prestadores.length : 0;
        $('#modalPrestadoresCasoLabel').text('Prestadores asignados (' + total + ')');

        if (!total) {
            $('#modalPrestadoresCasoBody').html('<tr><td colspan="2" class="text-center text-muted">No hay prestadores para mostrar</td></tr>');
            $('#modalPrestadoresCaso').modal('show');
            return;
        }

        const rows = prestadores.map(function (p) {
            const name = (p && p.name) ? p.name : 'N/D';
            const email = (p && p.email) ? p.email : 'Sin correo';
            return '<tr><td>' + name + '</td><td>' + email + '</td></tr>';
        }).join('');

        $('#modalPrestadoresCasoBody').html(rows);
        $('#modalPrestadoresCaso').modal('show');
    });
});
</script>
@stop
