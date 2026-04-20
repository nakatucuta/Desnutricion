@extends('adminlte::page')

@section('title', 'Asignacion 412')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    :root{
        --aw-bg:#f4fafb;
        --aw-card:#ffffff;
        --aw-border:#d6e8ec;
        --aw-primary:#0d7181;
        --aw-primary2:#15a2a5;
        --aw-accent:#17a34a;
        --aw-text:#174148;
        --aw-muted:#55777e;
    }
    .aw-wrap{padding:1rem 0 1.4rem;}
    .aw-hero{
        display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;
        border-radius:20px;color:#fff;padding:1.2rem 1.3rem;margin-bottom:1rem;
        background:
          radial-gradient(circle at 90% 15%, rgba(255,255,255,.2), transparent 35%),
          linear-gradient(130deg, #0c6676, #0f8e98 52%, #15b067);
        box-shadow:0 18px 32px rgba(8,74,86,.2);
    }
    .aw-brand{display:flex;gap:.9rem;align-items:center;}
    .aw-logo-box{
        width:72px;height:72px;border-radius:18px;display:flex;align-items:center;justify-content:center;
        background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);
    }
    .aw-logo{width:46px;object-fit:contain;}
    .aw-title{margin:0;font-size:1.6rem;font-weight:800;line-height:1.1;}
    .aw-sub{margin:.35rem 0 0;font-size:.9rem;color:rgba(255,255,255,.92);}
    .aw-chip{
        border:1px solid rgba(255,255,255,.3);background:rgba(255,255,255,.14);border-radius:999px;
        padding:.45rem .78rem;font-size:.72rem;font-weight:800;text-transform:uppercase;white-space:nowrap;
    }
    .aw-shell{
        background:linear-gradient(180deg,#fff,#f7fcfd);border:1px solid var(--aw-border);
        border-radius:20px;box-shadow:0 16px 28px rgba(10,69,80,.08);padding:1rem;
    }
    .aw-section{
        border:1px solid var(--aw-border);border-radius:16px;overflow:hidden;margin-bottom:.9rem;
        box-shadow:0 8px 16px rgba(12,68,79,.08);
    }
    .aw-head{
        display:flex;justify-content:space-between;align-items:center;color:#fff;padding:.72rem .95rem;
        background:linear-gradient(120deg,#0d7181,#1499a0 56%,#1aad5d);font-weight:800;
    }
    .aw-step{
        background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);border-radius:999px;
        padding:.18rem .52rem;font-size:.72rem;
    }
    .aw-body{background:#fff;padding:.95rem;}
    .aw-field{margin-bottom:.8rem;}
    .aw-field label{display:block;margin-bottom:.35rem;font-size:.87rem;font-weight:700;color:var(--aw-text);}
    .aw-req{color:#d91f1f;}
    .aw-control,.aw-select,.aw-textarea{
        width:100%;min-height:42px;border:1px solid #c5dde3;border-radius:11px;padding:.56rem .72rem;color:#164048;
    }
    .aw-textarea{min-height:92px;resize:vertical;}
    .aw-control:focus,.aw-select:focus,.aw-textarea:focus{outline:none;border-color:#11a0a5;box-shadow:0 0 0 .16rem rgba(17,160,165,.18);}
    .aw-help{font-size:.79rem;color:var(--aw-muted);margin-top:.32rem;}
    .aw-alert{
        border-radius:12px;border:1px solid #f4d1d1;background:#fff5f5;color:#8b1f1f;padding:.75rem .8rem;font-weight:600;
    }
    .aw-good{
        border-radius:12px;border:1px solid #b9efcb;background:#effcf3;color:#116a34;padding:.75rem .8rem;font-weight:600;
    }
    .aw-actions{display:flex;gap:.65rem;justify-content:center;flex-wrap:wrap;margin-top:.4rem;}
    .aw-btn-main,.aw-btn-back{
        border:none;border-radius:12px;min-height:44px;padding:.65rem 1.15rem;font-weight:700;text-decoration:none;
        display:inline-flex;align-items:center;gap:.4rem;
    }
    .aw-btn-main{color:#fff;background:linear-gradient(135deg,#0d7f8e,#17a34a);box-shadow:0 10px 20px rgba(11,100,92,.2);}
    .aw-btn-back{color:#285c66;background:#e9f5f7;border:1px solid #c8e2e7;}
    .select2-container{width:100%!important;}
    .select2-container--default .select2-selection--single{
        min-height:42px;border:1px solid #c5dde3;border-radius:11px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:40px;color:#164048;}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:40px;}
    #aw-overlay{
        display:none;position:fixed;inset:0;background:rgba(5,35,44,.58);z-index:9999;align-items:center;justify-content:center;color:#fff;
    }
    #aw-overlay.show{display:flex;}
    .aw-overlay-box{text-align:center;}
    .aw-overlay-text{margin-top:.7rem;font-weight:700;}
    @media (max-width: 991px){.aw-hero{flex-direction:column;}}
</style>
@stop

@section('content')
@php
    $usersForManual = collect($allUsers ?? [])->values();
    $usersSuggested = collect($income12 ?? [])->values();
    $selectedUserId = old('user_id', $edit_cargue->user_id);

    $fieldsPaciente = [
        ['numero_orden','Numero orden'],
        ['nombre_coperante','Nombre coperante'],
        ['fecha_captacion','Fecha de captacion','date'],
        ['municipio','Municipio'],
        ['nombre_rancheria','Nombre rancheria'],
        ['ubicacion_casa','Ubicacion casa'],
        ['nombre_cuidador','Nombre cuidador'],
        ['identioficacion_cuidador','Identificacion cuidador'],
        ['telefono_cuidador','Telefono cuidador'],
        ['nombre_autoridad_trad_ansestral','Autoridad tradicional'],
    ];

    $fieldsIdentificacion = [
        ['primer_nombre','Primer nombre'],
        ['segundo_nombre','Segundo nombre'],
        ['primer_apellido','Primer apellido'],
        ['segundo_apellido','Segundo apellido'],
        ['tipo_identificacion','Tipo identificacion'],
        ['numero_identificacion','Numero identificacion'],
        ['sexo','Sexo'],
        ['fecha_nacimieto_nino','Fecha nacimiento','date'],
        ['edad_meses','Edad meses'],
    ];

    $fieldsClinicos = [
        ['regimen_afiliacion','Regimen afiliacion'],
        ['nombre_eapb_menor','Nombre EAPB menor'],
        ['peso_kg','Peso kg'],
        ['logitud_talla_cm','Longitud / talla cm'],
        ['perimetro_braqueal','Perimetro braquial'],
        ['signos_peligro_infeccion_respiratoria','Signos peligro infeccion respiratoria'],
        ['sexosignos_desnutricion','Signos de desnutricion'],
        ['puntaje_z','Puntaje Z'],
        ['calsificacion_antropometrica','Clasificacion antropometrica'],
    ];
@endphp

<div class="container-fluid aw-wrap">
    <section class="aw-hero">
        <div class="aw-brand">
            <div class="aw-logo-box">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="aw-logo">
            </div>
            <div>
                <h1 class="aw-title">Editar Asignacion 412</h1>
                <p class="aw-sub">Asignacion inteligente por IPS y respaldo manual con todos los usuarios habilitados.</p>
            </div>
        </div>
        <span class="aw-chip"><i class="fas fa-layer-group mr-1"></i> módulo 412</span>
    </section>

    <div class="aw-shell">
        <form id="update-form" action="{{ url('/new412/'.$edit_cargue->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="aw-section">
                <div class="aw-head">
                    <span><i class="fas fa-user-check mr-1"></i> Asignacion de Prestador</span>
                    <span class="aw-step">Paso 1/4</span>
                </div>
                <div class="aw-body">
                    @if(!$ipsMatched)
                        <div class="aw-alert mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No se encontró IPS primaria coincidente. Puedes asignar manualmente desde todos los usuarios.
                        </div>
                    @else
                        <div class="aw-good mb-3">
                            <i class="fas fa-check-circle mr-1"></i>
                            IPS primaria identificada. Se muestran sugeridos primero y también todos los usuarios para selección manual.
                        </div>
                    @endif

                    <div class="aw-field">
                        <label for="user_id">IPS seguimiento ambulatorio <span class="aw-req">*</span></label>
                        <select name="user_id" id="user_id" class="aw-select" required>
                            <option value="">Seleccione usuario...</option>

                            @if($usersSuggested->isNotEmpty())
                                <optgroup label="Sugeridos por IPS primaria">
                                    @foreach($usersSuggested as $u)
                                        <option value="{{ $u->id }}" {{ (string)$selectedUserId === (string)$u->id ? 'selected' : '' }}>
                                            {{ trim(($u->codigohabilitacion ?? 'SIN-COD')." - ".$u->name) }}{{ !empty($u->email) ? " ({$u->email})" : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            <optgroup label="Asignacion manual (todos los usuarios)">
                                @foreach($usersForManual->whereNotIn('id', $usersSuggested->pluck('id')) as $u)
                                    <option value="{{ $u->id }}" {{ (string)$selectedUserId === (string)$u->id ? 'selected' : '' }}>
                                        {{ trim(($u->codigohabilitacion ?? 'SIN-COD')." - ".$u->name) }}{{ !empty($u->email) ? " ({$u->email})" : '' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('user_id')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        <p class="aw-help">El buscador permite encontrar por nombre, correo o código de habilitación.</p>
                    </div>
                </div>
            </div>

            <div class="aw-section">
                <div class="aw-head">
                    <span><i class="fas fa-address-card mr-1"></i> Datos de Captacion</span>
                    <span class="aw-step">Paso 2/4</span>
                </div>
                <div class="aw-body">
                    <div class="row">
                        @foreach($fieldsPaciente as $f)
                            @php($name = $f[0])
                            @php($label = $f[1])
                            @php($type = $f[2] ?? 'text')
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="aw-field">
                                    <label for="{{ $name }}">{{ $label }}</label>
                                    <input
                                        type="{{ $type }}"
                                        id="{{ $name }}"
                                        name="{{ $name }}"
                                        class="aw-control"
                                        value="{{ old($name, $edit_cargue->$name) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="aw-section">
                <div class="aw-head">
                    <span><i class="fas fa-id-card mr-1"></i> Identificacion del Menor</span>
                    <span class="aw-step">Paso 3/4</span>
                </div>
                <div class="aw-body">
                    <div class="row">
                        @foreach($fieldsIdentificacion as $f)
                            @php($name = $f[0])
                            @php($label = $f[1])
                            @php($type = $f[2] ?? 'text')
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="aw-field">
                                    <label for="{{ $name }}">{{ $label }} @if(in_array($name,['primer_nombre','primer_apellido','numero_identificacion']))<span class="aw-req">*</span>@endif</label>
                                    <input
                                        type="{{ $type }}"
                                        id="{{ $name }}"
                                        name="{{ $name }}"
                                        class="aw-control"
                                        value="{{ old($name, $edit_cargue->$name) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="aw-section">
                <div class="aw-head">
                    <span><i class="fas fa-heartbeat mr-1"></i> Estado Clinico</span>
                    <span class="aw-step">Paso 4/4</span>
                </div>
                <div class="aw-body">
                    <div class="row">
                        @foreach($fieldsClinicos as $f)
                            @php($name = $f[0])
                            @php($label = $f[1])
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="aw-field">
                                    <label for="{{ $name }}">{{ $label }}</label>
                                    <input
                                        type="text"
                                        id="{{ $name }}"
                                        name="{{ $name }}"
                                        class="aw-control"
                                        value="{{ old($name, $edit_cargue->$name) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="aw-actions">
                        <button id="update-btn" type="button" class="aw-btn-main" onclick="submitForm412()">
                            <i class="fas fa-save"></i>
                            <span id="aw-btn-text">Actualizar asignacion</span>
                            <span id="aw-btn-loading" class="spinner-border spinner-border-sm" style="display:none;" role="status"></span>
                        </button>
                        <a href="{{ url('import-excel') }}" class="aw-btn-back">
                            <i class="fas fa-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="aw-overlay">
    <div class="aw-overlay-box">
        <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;"></div>
        <div class="aw-overlay-text">Guardando cambios y notificando al prestador...</div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        $('#user_id').select2({
            width: '100%',
            placeholder: 'Buscar por nombre, correo o codigo...',
            allowClear: true
        });
    });

    function submitForm412() {
        const $btn = $('#update-btn');
        if (!$btn.length) return;

        $('#aw-btn-text').hide();
        $('#aw-btn-loading').show();
        $btn.prop('disabled', true);
        $('#aw-overlay').addClass('show');

        setTimeout(function () {
            $('#update-form').submit();
        }, 80);
    }
</script>
@stop
