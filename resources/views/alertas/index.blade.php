@extends('adminlte::page')

@section('title', 'Centro de Alertas')

@php
    $estado = request('estado', '');
    $severidad = request('severidad', '');
    $modulo = request('modulo', '');
    $search = request('q', '');

    $total = (int) ($stats['total'] ?? 0);
    $pendientes = (int) ($stats['pendientes'] ?? 0);
    $resueltas = (int) ($stats['resueltas'] ?? 0);
    $nuevas = (int) ($stats['nuevas'] ?? 0);
@endphp

@section('content_header')
<div class="alertas-hero">

    <div class="alertas-hero__brand">
        <div class="alertas-hero__logo-wrap">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="alertas-hero__logo">
        </div>
        <div>
            <span class="alertas-eyebrow">Centro clínico</span>
            <h1 class="alertas-hero__title">Gestión inteligente de alertas</h1>
            <p class="alertas-hero__subtitle">Visualiza prioridades, abre la información clínica en un clic y gestiona trazabilidad de alertas sin salir del flujo operativo.</p>
        </div>
    </div>
    <div class="alertas-hero__status">
        <span class="alertas-chip">Total {{ $total }}</span>
        <span class="alertas-chip alertas-chip--warn">Pendientes {{ $pendientes }}</span>
        <span class="alertas-chip alertas-chip--ok">Resueltas {{ $resueltas }}</span>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(session('ok'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle mr-1"></i>{{ session('ok') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="alertas-kpi">
                <span>Total filtrado</span>
                <strong>{{ $total }}</strong>
                <small>Alertas encontradas</small>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="alertas-kpi alertas-kpi--warning">
                <span>No vistas</span>
                <strong>{{ $nuevas }}</strong>
                <small>Requieren revisión inicial</small>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="alertas-kpi alertas-kpi--danger">
                <span>Pendientes</span>
                <strong>{{ $pendientes }}</strong>
                <small>Sin resolver aún</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="alertas-kpi alertas-kpi--success">
                <span>Resueltas</span>
                <strong>{{ $resueltas }}</strong>
                <small>Cierre documentado</small>
            </div>
        </div>
    </div>

    <div class="card alertas-card alertas-card--filters mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-2 mb-3">
                    <label class="alertas-label">Estado</label>
                    <select name="estado" class="form-control alertas-input">
                        <option value="">Todos</option>
                        <option value="pendientes" {{ $estado==='pendientes' ? 'selected' : '' }}>Pendientes</option>
                        <option value="resueltas" {{ $estado==='resueltas' ? 'selected' : '' }}>Resueltas</option>
                        <option value="vistas" {{ $estado==='vistas' ? 'selected' : '' }}>Vistas</option>
                        <option value="no_vistas" {{ $estado==='no_vistas' ? 'selected' : '' }}>No vistas</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="alertas-label">Severidad</label>
                    <select name="severidad" class="form-control alertas-input">
                        <option value="">Todas</option>
                        <option value="alta" {{ $severidad==='alta' ? 'selected' : '' }}>Alta</option>
                        <option value="media" {{ $severidad==='media' ? 'selected' : '' }}>Media</option>
                        <option value="baja" {{ $severidad==='baja' ? 'selected' : '' }}>Baja</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="alertas-label">Módulo</label>
                    <input type="text" name="modulo" class="form-control alertas-input" value="{{ $modulo }}" placeholder="Ej: ges_tipo1">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="alertas-label">Buscar</label>
                    <input type="text" name="q" class="form-control alertas-input" value="{{ $search }}" placeholder="Documento, examen, resultado o nombre">
                </div>
                <div class="col-md-2 mb-3">
                    <button class="btn alertas-btn alertas-btn--primary btn-block">
                        <i class="fas fa-filter mr-1"></i> Aplicar
                    </button>
                </div>
                <div class="col-12 d-flex flex-wrap">
                    <a href="{{ route('alertas.index') }}" class="btn alertas-btn alertas-btn--ghost">
                        <i class="fas fa-sync-alt mr-1"></i> Limpiar filtros
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($alertas as $a)
            @php
                $isResolved = !is_null($a->resolved_at);
                $isSeen = !is_null($a->seen_at);
                $sev = strtolower((string) ($a->severidad ?? 'baja'));
                $sevBadge = $sev === 'alta' ? 'danger' : ($sev === 'media' ? 'warning' : 'info');
                $pdfExists = !empty($a->pdf_path);
                $gestanteUrl = $a->ges_tipo1_id ? route('ges_tipo1.show', $a->ges_tipo1_id) : null;
                $seguimientoUrl = ($a->ges_tipo1_id && $a->seguimiento_id)
                    ? route('ges_tipo1.seguimientos.edit', [$a->ges_tipo1_id, $a->seguimiento_id])
                    : null;
                $g = $a->gestante;
                $pacienteNombre = $g
                    ? trim(($g->primer_nombre ?? '').' '.($g->segundo_nombre ?? '').' '.($g->primer_apellido ?? '').' '.($g->segundo_apellido ?? ''))
                    : 'N/D';
                $tipoDoc = $g->tipo_de_identificacion_de_la_usuaria ?? '';
                $numDoc = $g->no_id_del_usuario ?? '';
                $doc = trim($tipoDoc.' '.$numDoc) ?: 'N/D';
                $moduloVista = $a->modulo === 'ges_tipo1' ? 'Tipo 2' : ($a->modulo ?: 'N/D');
            @endphp

            <div class="col-lg-6">
                <article class="alertas-item sev-{{ $sev }} {{ $isResolved ? 'is-resolved' : '' }}">
                    <header class="alertas-item__head">
                        <div>
                            <div class="d-flex flex-wrap align-items-center mb-1">
                                <h4 class="alertas-item__title mb-0 mr-2">{{ $a->examen ?: 'Examen sin nombre' }}</h4>
                                <span class="badge alertas-pill badge-{{ $sevBadge }} mr-1">{{ strtoupper((string) $a->resultado) }}</span>
                                <span class="badge alertas-pill badge-{{ $isSeen ? 'success' : 'primary' }} mr-1">{{ $isSeen ? 'Vista' : 'Nueva' }}</span>
                                @if($isResolved)
                                    <span class="badge alertas-pill badge-secondary">Resuelta</span>
                                @endif
                            </div>
                            <div class="alertas-item__patient">
                                <i class="fas fa-user mr-1"></i><strong>{{ $pacienteNombre }}</strong>
                                <span class="mx-2">•</span>
                                <i class="fas fa-id-card mr-1"></i>{{ $doc }}
                            </div>
                        </div>
                        <div class="text-right alertas-item__stamp">
                            <small class="d-block text-muted"><i class="far fa-clock mr-1"></i>{{ optional($a->created_at)->format('Y-m-d H:i') }}</small>
                            <small class="d-block">Severidad: <strong class="text-{{ $sevBadge }}">{{ strtoupper($sev) }}</strong></small>
                        </div>
                    </header>

                    <section class="alertas-item__body">
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <div class="alertas-meta">
                                    <span class="alertas-meta__label">Módulo</span>
                                    <strong class="alertas-meta__value">{{ $moduloVista }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="alertas-meta">
                                    <span class="alertas-meta__label">Campo</span>
                                    <strong class="alertas-meta__value">{{ $a->campo ?: 'N/D' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="alertas-meta">
                                    <span class="alertas-meta__label">Gestante ID</span>
                                    <strong class="alertas-meta__value">{{ $a->ges_tipo1_id ?: 'N/D' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="alertas-meta">
                                    <span class="alertas-meta__label">Seguimiento ID</span>
                                    <strong class="alertas-meta__value">{{ $a->seguimiento_id ?: 'N/D' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="alertas-actions mt-2">
                            @if($gestanteUrl)
                                <a href="{{ $gestanteUrl }}" class="btn btn-sm alertas-btn alertas-btn--info">
                                    <i class="fas fa-user-injured mr-1"></i> Ir a gestante
                                </a>
                            @endif
                            @if($seguimientoUrl)
                                <a href="{{ $seguimientoUrl }}" class="btn btn-sm alertas-btn alertas-btn--warn">
                                    <i class="fas fa-notes-medical mr-1"></i> Ir a seguimiento
                                </a>
                            @endif
                            @if($pdfExists)
                                <a href="{{ route('alertas.pdf', $a->id) }}" target="_blank" class="btn btn-sm alertas-btn alertas-btn--ghost">
                                    <i class="fas fa-file-pdf mr-1"></i> Abrir evidencia PDF
                                </a>
                            @endif
                        </div>
                    </section>

                    <footer class="alertas-item__foot">
                        <div class="d-flex flex-wrap">
                            <form action="{{ route('alertas.seen', $a->id) }}" method="POST" class="mr-2 mb-2">
                                @csrf
                                <button class="btn btn-sm alertas-btn-mini {{ $isSeen ? 'btn-success' : 'btn-outline-dark' }}" {{ $isSeen ? 'disabled' : '' }}>
                                    <i class="fas fa-eye mr-1"></i>{{ $isSeen ? 'Vista' : 'Marcar vista' }}
                                </button>
                            </form>

                            <form action="{{ route('alertas.resolve', $a->id) }}" method="POST" class="mr-2 mb-2">
                                @csrf
                                <button class="btn btn-sm alertas-btn-mini {{ $isResolved ? 'btn-secondary' : 'btn-success' }}" {{ $isResolved ? 'disabled' : '' }}>
                                    <i class="fas fa-check mr-1"></i>{{ $isResolved ? 'Resuelta' : 'Resolver' }}
                                </button>
                            </form>
                        </div>

                        <div class="alertas-time text-right">
                            @if($isSeen)
                                <div><i class="fas fa-eye mr-1"></i>Vista: {{ optional($a->seen_at)->format('Y-m-d H:i') }}</div>
                            @else
                                <div><i class="fas fa-eye-slash mr-1"></i>Pendiente de lectura</div>
                            @endif
                            @if($isResolved)
                                <div><i class="fas fa-check-circle mr-1"></i>Resuelta: {{ optional($a->resolved_at)->format('Y-m-d H:i') }}</div>
                            @endif
                        </div>
                    </footer>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info shadow-sm">
                    <i class="fas fa-info-circle mr-1"></i>No hay alertas para los filtros seleccionados.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $alertas->withQueryString()->links() }}
    </div>
</div>
@stop

@section('css')
<style>
    .alertas-hero{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1.2rem;
        margin-bottom:1rem;
        padding:1.25rem 1.35rem;
        border-radius:24px;
        color:#fff;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 28%),
            linear-gradient(135deg, #8f1334 0%, #c43755 45%, #f08b4f 100%);
        box-shadow:0 16px 32px rgba(127, 29, 29, .2);
    }
    .alertas-hero__brand{
        display:flex;
        align-items:center;
        gap:1rem;
    }
    .alertas-hero__logo-wrap{
        width:74px;
        height:74px;
        border-radius:22px;
        background:rgba(255,255,255,.18);
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .alertas-hero__logo{
        width:48px;
        height:auto;
    }
    .alertas-eyebrow{
        display:inline-block;
        margin-bottom:.4rem;
        font-size:.76rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.08em;
        opacity:.85;
    }
    .alertas-hero__title{
        margin:0;
        font-size:1.9rem;
        font-weight:800;
        line-height:1.1;
    }
    .alertas-hero__subtitle{
        margin:.3rem 0 0;
        color:rgba(255,255,255,.88);
    }
    .alertas-hero__status{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:.5rem;
    }
    .alertas-chip{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:.5rem .8rem;
        border-radius:999px;
        border:1px solid rgba(255,255,255,.24);
        background:rgba(255,255,255,.13);
        font-size:.78rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    .alertas-chip--warn{ background:#fff3cd; color:#7a4e00; border-color:#ffe8a1; }
    .alertas-chip--ok{ background:#dff9ee; color:#116b4a; border-color:#c4f1dd; }
    .alertas-kpi{
        height:100%;
        border:1px solid #e7e3de;
        border-radius:16px;
        padding:.95rem 1rem;
        background:linear-gradient(180deg, #fff, #f9f6f4);
        box-shadow:0 10px 22px rgba(93, 63, 38, .06);
    }
    .alertas-kpi--warning{ background:linear-gradient(180deg, #fff9ea, #fff2cf); border-color:#f6e0a5; }
    .alertas-kpi--danger{ background:linear-gradient(180deg, #fff5f5, #ffe8e8); border-color:#f2c7c7; }
    .alertas-kpi--success{ background:linear-gradient(180deg, #f1fff7, #e6faef); border-color:#caecd8; }
    .alertas-kpi span{
        display:block;
        font-size:.78rem;
        color:#7b6e63;
        text-transform:uppercase;
        letter-spacing:.05em;
        font-weight:800;
    }
    .alertas-kpi strong{
        display:block;
        font-size:2rem;
        line-height:1;
        color:#2f2721;
        margin:.25rem 0;
    }
    .alertas-kpi small{ color:#8d7f73; }
    .alertas-card{
        border:none;
        border-radius:20px;
        box-shadow:0 14px 30px rgba(54, 33, 18, .07);
    }
    .alertas-card--filters{ background:linear-gradient(180deg, #fff, #fbf9f8); }
    .alertas-label{
        display:block;
        margin-bottom:.4rem;
        color:#6a4f40;
        font-weight:700;
    }
    .alertas-input{
        min-height:44px;
        border-radius:12px;
        border:1px solid #e2d8cf;
        background:#fffdfa;
    }
    .alertas-input:focus{
        border-color:#c96a4f;
        box-shadow:0 0 0 .2rem rgba(201,106,79,.14);
    }
    .alertas-btn{
        border-radius:12px;
        font-weight:700;
        min-height:42px;
        border:none;
    }
    .alertas-btn--primary{
        color:#fff;
        background:linear-gradient(135deg, #a01f46, #db5c5b);
    }
    .alertas-btn--ghost{
        color:#7b4f40;
        background:#f8efea;
        border:1px solid #e7d4c9;
    }
    .alertas-btn--info{
        background:#e8f4fb;
        color:#155e75;
        border:1px solid #cbe5f4;
    }
    .alertas-btn--warn{
        background:#fff2dc;
        color:#8a5200;
        border:1px solid #f6dbad;
    }
    .alertas-item{
        position:relative;
        border:1px solid #d8dde6;
        border-radius:18px;
        overflow:hidden;
        margin-bottom:1.2rem;
        background:
            linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,251,255,.96)),
            radial-gradient(circle at top right, rgba(56,189,248,.08), transparent 36%);
        box-shadow:0 14px 34px rgba(15, 23, 42, .09);
    }
    .alertas-item::before{
        content:'';
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        width:5px;
        background:#94a3b8;
    }
    .alertas-item.is-resolved{
        border-color:#cfd8df;
        background:linear-gradient(180deg, #ffffff, #f6fafc);
    }
    .alertas-item.sev-alta::before{
        background:linear-gradient(180deg, #ef4444, #b91c1c);
        box-shadow:0 0 0 1px rgba(239,68,68,.2), 0 0 18px rgba(239,68,68,.35);
    }
    .alertas-item.sev-media::before{
        background:linear-gradient(180deg, #f59e0b, #b45309);
        box-shadow:0 0 0 1px rgba(245,158,11,.2), 0 0 16px rgba(245,158,11,.32);
    }
    .alertas-item.sev-baja::before{
        background:linear-gradient(180deg, #0ea5e9, #0369a1);
        box-shadow:0 0 0 1px rgba(14,165,233,.2), 0 0 16px rgba(14,165,233,.3);
    }
    .alertas-item__head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:.85rem;
        padding:1rem 1rem .9rem 1.15rem;
        border-bottom:1px solid #e3eaf2;
        background:
            linear-gradient(180deg, rgba(248,250,252,.98), rgba(241,245,249,.96)),
            repeating-linear-gradient(90deg, rgba(15,23,42,.02) 0, rgba(15,23,42,.02) 2px, transparent 2px, transparent 11px);
    }
    .alertas-item__title{
        font-size:1.09rem;
        font-weight:800;
        color:#13233a;
        letter-spacing:.01em;
    }
    .alertas-pill{
        border-radius:999px;
        font-size:.72rem;
        padding:.38rem .6rem;
        letter-spacing:.04em;
        text-transform:uppercase;
        font-weight:800;
        border:1px solid rgba(255,255,255,.45);
    }
    .alertas-item__patient{
        color:#4b5563;
        font-size:.92rem;
    }
    .alertas-item__stamp{
        padding:.45rem .65rem;
        border-radius:10px;
        background:linear-gradient(180deg, #ffffff, #eef2f7);
        border:1px solid #d9e2ec;
        min-width:160px;
    }
    .alertas-item__body{
        padding:1rem 1rem .45rem 1.15rem;
    }
    .alertas-meta{
        height:100%;
        border:1px solid #dce6f0;
        border-radius:11px;
        padding:.55rem .65rem;
        background:linear-gradient(180deg, #fff, #f7fbff);
    }
    .alertas-meta__label{
        display:block;
        font-size:.73rem;
        color:#64748b;
        text-transform:uppercase;
        letter-spacing:.04em;
        font-weight:700;
    }
    .alertas-meta__value{
        display:block;
        color:#1e293b;
        font-weight:700;
        word-break:break-word;
    }
    .alertas-actions{
        display:flex;
        gap:.5rem;
        flex-wrap:wrap;
    }
    .alertas-actions .alertas-btn{
        box-shadow:0 6px 14px rgba(15,23,42,.08);
    }
    .alertas-item__foot{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:.7rem;
        padding:.85rem 1rem .95rem 1.15rem;
        border-top:1px solid #e4ebf2;
        background:linear-gradient(180deg, #fbfdff, #f3f7fb);
    }
    .alertas-btn-mini{
        border-radius:10px;
        font-weight:700;
        padding:.37rem .7rem;
        min-height:35px;
    }
    .alertas-time{
        color:#5b6675;
        font-size:.78rem;
    }
    @media (max-width: 991px){
        .alertas-hero,
        .alertas-item__head,
        .alertas-item__foot{
            flex-direction:column;
            align-items:flex-start;
        }
    }
</style>
@stop
