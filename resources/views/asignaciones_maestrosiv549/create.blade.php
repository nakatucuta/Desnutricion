@extends('adminlte::page')

@section('title', 'Asignar Caso MaestroSiv549')

@php
    $highlightFields = [
        'tip_ide_' => 'Tipo ID',
        'num_ide_' => 'Numero identificacion',
        'fec_not' => 'Fecha notificacion',
        'nom_eve' => 'Evento',
        'nmun_resi' => 'Municipio residencia',
        'nom_upgd' => 'UPGD',
        'telefono_' => 'Telefono',
    ];

    $hiddenFields = ['created_at', 'updated_at'];
    $elegiblesCount = (int) (($usuariosElegibles ?? collect())->count());
    $asignadosCount = (int) (($asignacionesExistentes ?? collect())->count());
    $sinElegibles = $elegiblesCount === 0;

    $prettyLabel = function ($field) {
        return ucwords(str_replace('_', ' ', trim($field, '_')));
    };
@endphp

@section('content_header')
<div class="asig-hero">
    <div class="asig-hero__brand">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="asig-hero__logo">
        <div>
            <h1 class="asig-hero__title">Asignacion de Caso Maestro SIV 549</h1>
            <p class="asig-hero__subtitle">Selecciona el prestador, revisa la informacion del caso y guarda la asignacion sin perder el flujo actual.</p>
        </div>
    </div>
    <a href="{{ route('maestrosiv549.index') }}" class="btn btn-outline-light btn-sm asig-hero__back">
        <i class="fas fa-arrow-left mr-1"></i> Volver al listado
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div id="asigLoading" class="asig-loading" aria-live="polite" aria-busy="true">
        <div class="asig-loading__backdrop"></div>
        <div class="asig-loading__panel">
            <div class="asig-loading__orb">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="asig-loading__brand">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional">
            </div>
            <h3>Guardando asignacion</h3>
            <p>Estamos registrando el caso y vinculando el prestador seleccionado. Por favor espera.</p>
            <div class="asig-loading__status">
                <span class="asig-loading__dot"></span>
                <span id="asigLoadingText">Procesando asignacion...</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Hay datos por revisar</div>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('asig_duplicate'))
        <div id="duplicateToast549" class="asig-toast asig-toast--danger" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="asig-toast__icon"><i class="fas fa-ban"></i></div>
            <div class="asig-toast__body">
                <strong>Caso ya asignado</strong>
                <span>Ya existen asignaciones para el periodo seleccionado en algunos usuarios.</span>
            </div>
            <button type="button" class="asig-toast__close" id="closeDuplicateToast549" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <form action="{{ route('asignaciones_maestrosiv549.store') }}" method="POST" id="assignmentForm549">
        @csrf

        <div class="row">
            <div class="col-xl-4 mb-4">
                <div class="card card-outline card-primary asig-card h-100">
                    <div class="card-header border-0 pb-0">
                        <span class="asig-eyebrow">Prestador</span>
                        <h3 class="card-title asig-card__title">
                            <i class="fas fa-user-check mr-1"></i> Asignacion principal
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="asig-info-banner mb-3">
                            <div>
                                <strong>IPS primaria</strong>
                                <div>{{ $nombre_ips_primaria ?: 'Sin referencia encontrada' }}</div>
                            </div>
                            <span class="badge badge-light">{{ $codigo_habilitacion ?: 'Sin codigo' }}</span>
                        </div>

                        <div class="form-group mb-3" id="select-usuario-group">
                            <label for="user_ids" class="asig-label">
                                Prestador primario
                                <span class="badge badge-warning ml-2" id="badge-select">Selecciona usuarios _ges</span>
                            </label>

                            <select
                                name="user_ids[]"
                                id="user_ids"
                                class="form-control select2-user"
                                multiple
                            >
                                @foreach(($usuariosElegibles ?? collect()) as $user)
                                    @php
                                        $isPreselected = in_array($user->id, $usuarios_prestador_primario ?? [], true);
                                    @endphp
                                    <option value="{{ $user->id }}" {{ $isPreselected ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }}) - {{ $user->codigohabilitacion }}
                                    </option>
                                @endforeach
                            </select>

                            @if(($asignacionesExistentes ?? collect())->isNotEmpty())
                                <div class="asig-assigned mt-3">
                                    <div class="asig-assigned__title">
                                        <i class="fas fa-users mr-1"></i>
                                        Ya asignado en {{ $periodYear ?? '----' }}-SE{{ $periodSemana ?? '--' }} a:
                                    </div>
                                    <ul class="asig-assigned__list mb-0">
                                        @foreach($asignacionesExistentes as $asig)
                                            <li>
                                                <strong>{{ optional($asig->user)->name ?? 'Usuario eliminado' }}</strong>
                                                <span>{{ optional($asig->user)->email ?? 'sin correo' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(($usuariosElegibles ?? collect())->isEmpty())
                                <div class="alert alert-danger mb-0 mt-3">
                                    <div class="font-weight-bold mb-1">No hay usuarios elegibles para agregar en este caso</div>
                                    @if(($asignacionesExistentes ?? collect())->isNotEmpty())
                                        <small>
                                            Ya hay usuario(s) asignado(s) en {{ $periodYear ?? '----' }}-SE{{ $periodSemana ?? '--' }} y
                                            no existen mas usuarios <strong>_ges</strong> con el codigo <strong>{{ $codigo_habilitacion ?? 'N/D' }}</strong>
                                            para adicionar.
                                        </small>
                                    @else
                                        <small>Se requiere usuario con sufijo <strong>_ges</strong> y mismo codigo de habilitacion.</small>
                                    @endif
                                </div>
                            @else
                                <div class="asig-helper mt-3 mb-0">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Solo se permiten usuarios <strong>_ges</strong> con codigo de habilitacion <strong>{{ $codigo_habilitacion }}</strong>.
                                </div>
                            @endif

                            @error('user_ids')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!empty($sin_usuario_gestante_por_codigo) && $sin_usuario_gestante_por_codigo && $asignadosCount === 0)
                            <div class="alert alert-warning mb-3">
                                <div class="font-weight-bold mb-1">No hay usuario del modulo gestante para este codigo</div>
                                <div>Codigo de habilitacion: <strong>{{ $codigo_habilitacion ?? 'N/D' }}</strong></div>
                                <small>Debes crear usuarios _ges con ese codigo para poder asignar.</small>
                            </div>
                        @endif

                        <div class="asig-sticky-actions">
                            <button type="submit" class="btn btn-asig-primary btn-block mb-2" id="btn-asignar-guardar"
                                    {{ $sinElegibles ? 'disabled title=No hay usuarios elegibles para asignar' : '' }}>
                                <i class="fas fa-user-check mr-1"></i>
                                {{ $sinElegibles ? 'Sin usuarios para asignar' : 'Asignar y guardar' }}
                            </button>
                            <a href="{{ route('maestrosiv549.index') }}" class="btn btn-outline-secondary btn-block">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card card-outline card-info asig-card mb-4">
                    <div class="card-header border-0">
                        <span class="asig-eyebrow">Resumen</span>
                        <h3 class="card-title asig-card__title">
                            <i class="fas fa-id-card-alt mr-1"></i> Vista rapida del caso
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($highlightFields as $field => $label)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="asig-summary-tile">
                                        <span class="asig-summary-tile__label">{{ $label }}</span>
                                        <strong class="asig-summary-tile__value">{{ $datosCaso[$field] ?? 'Sin dato' }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary asig-card">
                    <div class="card-header border-0">
                        <span class="asig-eyebrow">Edicion</span>
                        <h3 class="card-title asig-card__title">
                            <i class="fas fa-edit mr-1"></i> Datos del caso
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="asig-helper mb-4">
                            <i class="fas fa-shield-alt mr-1"></i> Todos los campos conservan su funcionalidad actual. Puedes editar la informacion antes de guardar la asignacion.
                        </div>

                        <div class="row">
                            @foreach($datosCaso as $campo => $valor)
                                @continue(in_array($campo, $hiddenFields, true))
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <div class="form-group mb-0">
                                        <label for="{{ $campo }}" class="asig-label">{{ $prettyLabel($campo) }}</label>
                                        <input
                                            type="text"
                                            class="form-control asig-input"
                                            name="{{ $campo }}"
                                            id="{{ $campo }}"
                                            value="{{ old($campo, $valor ?? '') }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <button type="button" class="floating-arrow" id="goToGuardarBtn" title="Ir al boton Asignar y guardar">
        <i class="fas fa-arrow-down"></i>
    </button>
</div>

@if(session('asig_duplicate'))
<div class="modal fade" id="duplicateCaseModal549" tabindex="-1" role="dialog" aria-labelledby="duplicateCaseModalLabel549" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content asig-dup-modal">
            <div class="modal-header border-0 pb-1">
                <h5 class="modal-title" id="duplicateCaseModalLabel549">
                    <i class="fas fa-exclamation-circle mr-1"></i> Asignacion duplicada
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-2">Este caso ya tiene asignaciones para el mismo periodo epidemiologico.</p>
                <p class="mb-0 text-muted">Se omitieron los usuarios duplicados y solo se permiten usuarios _ges del mismo codigo de habilitacion.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-asig-primary px-4" data-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
@endif
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .asig-hero{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:1.1rem 1.35rem;
        border-radius:22px;
        color:#fff;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.2), transparent 28%),
            linear-gradient(135deg, #0f6c78 0%, #1797a8 45%, #1d7f51 100%);
        box-shadow:0 16px 34px rgba(13, 71, 97, .18);
    }
    .asig-hero__brand{
        display:flex;
        align-items:center;
        gap:1rem;
    }
    .asig-hero__logo{
        width:64px;
        height:64px;
        object-fit:contain;
        border-radius:16px;
        background:#fff;
        padding:6px;
        box-shadow:0 10px 18px rgba(0,0,0,.12);
    }
    .asig-hero__title{
        margin:0;
        font-size:1.7rem;
        font-weight:800;
        letter-spacing:.01em;
    }
    .asig-hero__subtitle{
        margin:.3rem 0 0;
        max-width:760px;
        color:rgba(255,255,255,.86);
    }
    .asig-hero__back{
        white-space:nowrap;
        border-radius:999px;
        padding:.55rem 1rem;
    }
    .asig-card{
        border-radius:20px;
        overflow:hidden;
        box-shadow:0 14px 28px rgba(15, 76, 92, .08);
    }
    .asig-card .card-header{
        background:linear-gradient(180deg, rgba(244,250,251,.96), rgba(255,255,255,.98));
        padding:1.15rem 1.3rem .85rem;
    }
    .asig-card .card-body{
        padding:1.35rem;
    }
    .asig-eyebrow{
        display:inline-block;
        margin-bottom:.4rem;
        padding:.18rem .55rem;
        border-radius:999px;
        font-size:.72rem;
        font-weight:700;
        letter-spacing:.08em;
        text-transform:uppercase;
        color:#0c6d79;
        background:#dff5f7;
    }
    .asig-card__title{
        font-size:1.2rem;
        font-weight:700;
        color:#264653;
    }
    .asig-label{
        display:block;
        margin-bottom:.45rem;
        font-weight:700;
        color:#15616d;
    }
    .asig-input{
        border-radius:14px;
        min-height:45px;
        border:1px solid #d7e8eb;
        background:#fbfeff;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.8);
    }
    .asig-input:focus{
        border-color:#1b9aaa;
        box-shadow:0 0 0 .2rem rgba(27,154,170,.15);
    }
    .asig-info-banner{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:.8rem;
        padding:1rem 1rem;
        border-radius:16px;
        background:linear-gradient(135deg, #effaf7, #f2f9ff);
        border:1px solid #d9eeea;
        color:#1d4952;
    }
    .asig-helper{
        padding:.8rem 1rem;
        border-radius:14px;
        background:#f8fbfd;
        border:1px dashed #c8dfe4;
        color:#4f6d73;
    }
    .asig-summary-tile{
        height:100%;
        padding:1rem;
        border-radius:16px;
        background:linear-gradient(180deg, #ffffff, #f8fbfd);
        border:1px solid #e3eef1;
        box-shadow:0 8px 18px rgba(34, 87, 122, .05);
    }
    .asig-summary-tile__label{
        display:block;
        margin-bottom:.35rem;
        font-size:.8rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#6a8a91;
    }
    .asig-summary-tile__value{
        display:block;
        font-size:1rem;
        color:#1f3942;
        word-break:break-word;
    }
    .btn-asig-primary{
        border:none;
        color:#fff;
        font-weight:700;
        min-height:46px;
        border-radius:14px;
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        box-shadow:0 12px 24px rgba(18, 122, 111, .22);
    }
    .btn-asig-primary:hover{
        color:#fff;
        filter:brightness(.98);
    }
    .asig-sticky-actions{
        padding-top:.5rem;
    }
    .select2-container--default .select2-selection--multiple{
        min-height:48px;
        border:1px solid #d7e8eb;
        border-radius:14px;
        background:#fbfeff;
        padding:.35rem .45rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple{
        border-color:#1b9aaa;
        box-shadow:0 0 0 .2rem rgba(27,154,170,.15);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice{
        background:#e8f7f3;
        border:1px solid #b9e0d4;
        color:#175a4d;
        border-radius:999px;
        padding:2px 10px;
    }
    .asig-assigned{
        border:1px solid #d5e8ed;
        background:#f7fcff;
        border-radius:14px;
        padding:.8rem .9rem;
    }
    .asig-assigned__title{
        font-weight:700;
        color:#215c67;
        margin-bottom:.45rem;
        font-size:.92rem;
    }
    .asig-assigned__list{
        padding-left:1.1rem;
    }
    .asig-assigned__list li{
        margin-bottom:.22rem;
        color:#355862;
        font-size:.88rem;
    }
    .asig-assigned__list li span{
        color:#64818a;
        margin-left:.35rem;
    }
    #badge-select{
        font-size:.8rem;
        border-radius:999px;
        padding:.3rem .6rem;
    }
    .floating-arrow{
        position:fixed;
        right:28px;
        bottom:28px;
        z-index:1031;
        width:58px;
        height:58px;
        border:none;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:1.7rem;
        cursor:pointer;
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        box-shadow:0 12px 24px rgba(18, 122, 111, .25);
        animation:asigFloat 1.7s infinite;
    }
    .floating-arrow:hover{
        transform:translateY(-2px) scale(1.03);
        color:#fff;
    }
    @keyframes asigFloat{
        0%,100%{ transform:translateY(0); }
        50%{ transform:translateY(-8px); }
    }
    .asig-loading{
        position:fixed;
        inset:0;
        z-index:3100;
        display:none;
        align-items:center;
        justify-content:center;
        padding:1.25rem;
    }
    .asig-loading.is-visible{ display:flex; }
    .asig-loading__backdrop{
        position:absolute;
        inset:0;
        background:
            radial-gradient(circle at top left, rgba(59,130,246,.24), transparent 30%),
            radial-gradient(circle at bottom right, rgba(34,197,94,.2), transparent 32%),
            rgba(15, 23, 42, .72);
        backdrop-filter:blur(10px);
    }
    .asig-loading__panel{
        position:relative;
        width:min(520px, 100%);
        border-radius:26px;
        border:1px solid rgba(255,255,255,.15);
        background:linear-gradient(145deg, rgba(15, 23, 42, .96), rgba(30, 41, 59, .92));
        box-shadow:0 30px 80px rgba(15, 23, 42, .42);
        padding:2rem 1.8rem 1.7rem;
        text-align:center;
        color:#fff;
        overflow:hidden;
    }
    .asig-loading__orb{
        position:relative;
        width:102px;
        height:102px;
        margin:0 auto 1rem;
    }
    .asig-loading__orb span{
        position:absolute;
        inset:0;
        border-radius:50%;
        border:2px solid transparent;
        border-top-color:rgba(96,165,250,.95);
        border-right-color:rgba(34,197,94,.78);
        animation:asigSpin 1.35s linear infinite;
    }
    .asig-loading__orb span:nth-child(2){
        inset:12px;
        border-top-color:rgba(34,211,238,.9);
        border-right-color:rgba(251,191,36,.72);
        animation-duration:1.05s;
        animation-direction:reverse;
    }
    .asig-loading__orb span:nth-child(3){
        inset:24px;
        border-top-color:rgba(74,222,128,.95);
        border-right-color:rgba(96,165,250,.72);
        animation-duration:.9s;
    }
    .asig-loading__brand img{
        width:48px;
        height:48px;
        object-fit:contain;
        border-radius:14px;
        background:rgba(255,255,255,.92);
        padding:.3rem;
        margin-bottom:.8rem;
    }
    .asig-loading__panel h3{
        color:#fff;
        font-size:1.45rem;
        font-weight:800;
    }
    .asig-loading__panel p{
        color:rgba(226,232,240,.86);
        max-width:360px;
        margin:0 auto 1rem;
    }
    .asig-loading__status{
        display:inline-flex;
        align-items:center;
        gap:.65rem;
        padding:.7rem 1rem;
        border-radius:999px;
        background:rgba(15,23,42,.45);
        border:1px solid rgba(148,163,184,.18);
        color:#e2e8f0;
        font-weight:600;
    }
    .asig-loading__dot{
        width:10px;
        height:10px;
        border-radius:50%;
        background:#22d3ee;
        box-shadow:0 0 0 0 rgba(34,211,238,.7);
        animation:asigPulse 1.5s ease-out infinite;
    }
    body.asig-loading-lock{ overflow:hidden; }
    @keyframes asigSpin{ to { transform:rotate(360deg); } }
    @keyframes asigPulse{
        0%{ box-shadow:0 0 0 0 rgba(34,211,238,.7); }
        70%{ box-shadow:0 0 0 14px rgba(34,211,238,0); }
        100%{ box-shadow:0 0 0 0 rgba(34,211,238,0); }
    }
    @media (max-width: 991px){
        .asig-hero{
            flex-direction:column;
            align-items:flex-start;
        }
    }
    @media (max-width: 767px){
        .asig-hero__brand{
            align-items:flex-start;
        }
        .asig-hero__title{
            font-size:1.35rem;
        }
        .asig-card .card-body,
        .asig-card .card-header{
            padding:1rem;
        }
        .floating-arrow{
            width:52px;
            height:52px;
            right:18px;
            bottom:18px;
        }
    }
    .asig-toast{
        position:fixed;
        right:22px;
        top:92px;
        z-index:3090;
        width:min(420px, calc(100vw - 30px));
        border-radius:16px;
        padding:.85rem .9rem;
        display:flex;
        align-items:flex-start;
        gap:.75rem;
        border:1px solid rgba(255,255,255,.22);
        box-shadow:0 14px 34px rgba(15, 23, 42, .26);
        color:#fff;
        animation:asigToastIn .35s ease-out;
    }
    .asig-toast--danger{
        background:linear-gradient(135deg, #8f1f2d, #b91c1c);
    }
    .asig-toast__icon{
        width:34px;
        height:34px;
        border-radius:10px;
        background:rgba(255,255,255,.15);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1rem;
        flex-shrink:0;
    }
    .asig-toast__body{
        display:flex;
        flex-direction:column;
        gap:.1rem;
        line-height:1.25;
    }
    .asig-toast__body strong{ font-size:.98rem; }
    .asig-toast__body span{ font-size:.87rem; opacity:.95; }
    .asig-toast__close{
        margin-left:auto;
        border:none;
        background:transparent;
        color:#fff;
        opacity:.9;
        cursor:pointer;
    }
    .asig-toast__close:hover{ opacity:1; }
    .asig-dup-modal{
        border:none;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 20px 50px rgba(15,23,42,.26);
    }
    .asig-dup-modal .modal-header{
        background:linear-gradient(135deg, #fff3f3, #ffe8e8);
    }
    .asig-dup-modal .modal-title{
        color:#8b1e2b;
        font-weight:800;
    }
    @keyframes asigToastIn{
        from{ transform:translateY(-12px); opacity:0; }
        to{ transform:translateY(0); opacity:1; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    let assignmentSubmitting = false;

    $('.select2-user').select2({
        placeholder: '-- Selecciona usuarios _ges --',
        width: '100%',
        allowClear: true
    });

    $('#user_ids').on('change', function () {
        if ($(this).val() && $(this).val().length > 0) {
            $('#badge-select').fadeOut(200);
            $('#btn-asignar-guardar').addClass('btn-pop');
            setTimeout(function () {
                $('#btn-asignar-guardar').removeClass('btn-pop');
            }, 900);
        } else {
            $('#badge-select').fadeIn(200);
        }
    });

    $('#goToGuardarBtn').on('click', function () {
        $('html, body').animate({
            scrollTop: $('#btn-asignar-guardar').offset().top - 90
        }, 500);
        $('#btn-asignar-guardar').addClass('btn-pop');
        setTimeout(function () {
            $('#btn-asignar-guardar').removeClass('btn-pop');
        }, 900);
    });

    function showAssignmentLoading(message) {
        if (message) {
            $('#asigLoadingText').text(message);
        }

        $('body').addClass('asig-loading-lock');
        $('#asigLoading').addClass('is-visible');
    }

    $('#assignmentForm549').on('submit', function (e) {
        if (assignmentSubmitting) {
            e.preventDefault();
            return false;
        }

        assignmentSubmitting = true;
        $('#btn-asignar-guardar')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
        showAssignmentLoading('Registrando asignacion y redirigiendo...');
    });

    @if(session('asig_duplicate'))
        $('#duplicateCaseModal549').modal('show');
        setTimeout(function () {
            const $t = $('#duplicateToast549');
            if ($t.length) {
                $t.fadeOut(400, function () { $(this).remove(); });
            }
        }, 6500);

        $('#closeDuplicateToast549').on('click', function () {
            $('#duplicateToast549').fadeOut(200, function () { $(this).remove(); });
        });
    @endif
});
</script>
<style>
    .btn-pop{
        animation: asigButtonPop .55s cubic-bezier(.2,1.5,.5,1.1);
    }
    @keyframes asigButtonPop{
        0%{ transform:scale(1); }
        35%{ transform:scale(1.05); }
        100%{ transform:scale(1); }
    }
</style>
@stop
