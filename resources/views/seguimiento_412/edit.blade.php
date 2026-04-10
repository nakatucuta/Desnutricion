@extends('adminlte::page')

@section('title', 'Editar Seguimiento 412')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    :root {
        --s412-bg: #f3fafb;
        --s412-card: #ffffff;
        --s412-border: #d6eaee;
        --s412-primary: #0f6f7e;
        --s412-primary-2: #118a96;
        --s412-accent: #16a34a;
        --s412-text: #153b42;
    }

    .s412-page { padding: 1.2rem 0 1.5rem; }

    .s412-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem;
        border-radius: 22px;
        color: #fff;
        margin-bottom: 1rem;
        background:
            radial-gradient(circle at 85% 15%, rgba(255, 255, 255, .22), transparent 34%),
            radial-gradient(circle at 5% 75%, rgba(255, 255, 255, .12), transparent 31%),
            linear-gradient(130deg, #0f6f7e, #10929a 52%, #13b465);
        box-shadow: 0 18px 36px rgba(9, 72, 80, .24);
    }

    .s412-hero__brand { display: flex; align-items: center; gap: .95rem; }

    .s412-hero__logo-wrap {
        width: 78px;
        height: 78px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .25);
        flex-shrink: 0;
    }

    .s412-hero__logo {
        width: 50px;
        height: auto;
        object-fit: contain;
    }

    .s412-eyebrow {
        display: inline-block;
        margin-bottom: .35rem;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .9;
    }

    .s412-title {
        margin: 0;
        font-size: 1.72rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .s412-subtitle {
        margin: .4rem 0 0;
        max-width: 760px;
        font-size: .93rem;
        color: rgba(255, 255, 255, .92);
    }

    .s412-chip {
        padding: .42rem .78rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .25);
        background: rgba(255, 255, 255, .14);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .s412-shell {
        border: 1px solid var(--s412-border);
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff, #f8fdfe);
        box-shadow: 0 18px 32px rgba(17, 74, 85, .08);
        padding: 1rem;
    }

    .s412-section {
        margin-bottom: 1rem;
        border: 1px solid var(--s412-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(13, 66, 73, .08);
    }

    .s412-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .78rem 1rem;
        color: #fff;
        font-weight: 800;
        background: linear-gradient(120deg, #0f6f7e, #138993 55%, #18a85e);
    }

    .s412-step {
        padding: .2rem .58rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .24);
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .s412-section__body { padding: 1rem; background: var(--s412-card); }
    .s412-field { margin-bottom: .9rem; }

    .s412-field label {
        margin-bottom: .38rem;
        color: var(--s412-text);
        font-size: .88rem;
        font-weight: 700;
        display: block;
    }

    .s412-input-group { position: relative; }

    .s412-icon {
        position: absolute;
        left: .7rem;
        top: 50%;
        transform: translateY(-50%);
        color: #5c8389;
        z-index: 3;
        pointer-events: none;
    }

    .s412-input,
    .s412-select,
    .s412-textarea {
        width: 100%;
        min-height: 43px;
        border: 1px solid #c8dfe4;
        border-radius: 12px;
        background: #fff;
        color: #183d43;
        padding: .56rem .74rem;
    }

    .s412-input.with-icon,
    .s412-select.with-icon { padding-left: 2.15rem; }

    .s412-textarea {
        min-height: 110px;
        resize: vertical;
    }

    .s412-input:focus,
    .s412-select:focus,
    .s412-textarea:focus {
        outline: none;
        border-color: #129ba0;
        box-shadow: 0 0 0 .16rem rgba(18, 155, 160, .18);
    }

    .s412-req { color: #d51f1f; }
    .s412-error { margin-top: .32rem; color: #cb2f2f; font-size: .78rem; font-weight: 700; }

    .s412-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: .68rem;
        margin-top: .55rem;
    }

    .s412-btn-main,
    .s412-btn-back {
        border: none;
        border-radius: 12px;
        min-height: 44px;
        padding: .66rem 1.2rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .42rem;
    }

    .s412-btn-main {
        color: #fff;
        background: linear-gradient(135deg, #0e7f8f, #16a34a);
        box-shadow: 0 10px 20px rgba(11, 103, 90, .22);
    }

    .s412-btn-back {
        color: #275a64;
        background: #e8f5f7;
        border: 1px solid #cae4e8;
    }

    .select2-container { width: 100% !important; }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 43px;
        border: 1px solid #c8dfe4;
        border-radius: 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 41px;
        padding-left: 2.05rem;
        color: #163f45;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 41px; }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #129ba0;
        box-shadow: 0 0 0 .16rem rgba(18, 155, 160, .18);
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(120deg, #0f7282, #17a95f);
        border: none;
        border-radius: 999px;
        color: #fff;
        font-weight: 700;
        font-size: .78rem;
        padding: .18rem .6rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #dffff0;
        margin-right: .35rem;
    }

    #s412-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(7, 34, 40, .6);
        align-items: center;
        justify-content: center;
    }

    #s412-overlay.show { display: flex; }

    .s412-overlay__content { text-align: center; color: #fff; }
    .s412-overlay__text { margin-top: .8rem; font-weight: 700; }

    @media (max-width: 991px) { .s412-hero { flex-direction: column; } }
    @media (max-width: 767px) {
        .s412-page { padding-top: .75rem; }
        .s412-shell, .s412-section__body { padding: .8rem; }
        .s412-actions { flex-direction: column; }
        .s412-btn-main, .s412-btn-back { width: 100%; justify-content: center; }
    }
</style>
@stop

@section('content')
@php
    $medicamentosActuales = old('medicamento', $empleado->medicamento ? explode(',', $empleado->medicamento) : []);
@endphp
<div class="container-fluid s412-page">
    @include('seguimiento_412.mensajes')

    <section class="s412-hero">
        <div class="s412-hero__brand">
            <div class="s412-hero__logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="s412-hero__logo">
            </div>
            <div>
                <span class="s412-eyebrow">Seguimiento 412</span>
                <h1 class="s412-title">Editar Seguimiento Clinico</h1>
                <p class="s412-subtitle">
                    Actualiza informacion clinica y de seguimiento con una interfaz moderna, clara y segura para la gestion del caso.
                </p>
            </div>
        </div>
        <span class="s412-chip"><i class="fas fa-pen-nib mr-1"></i> edicion avanzada</span>
    </section>

    <div class="s412-shell">
        <form id="update-form" action="{{ url('/new412_seguimiento/'.$empleado->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="s412-section">
                <div class="s412-section__head">
                    <span><i class="fas fa-user-injured mr-1"></i> Paciente y Consulta</span>
                    <span class="s412-step">Paso 1/5</span>
                </div>
                <div class="s412-section__body">
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <div class="s412-field">
                                <label for="cargue412_id">Paciente <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-search s412-icon"></i>
                                    <select id="cargue412_id" name="cargue412_id" class="s412-select with-icon" required>
                                        @foreach($incomeedit as $p)
                                            @php
                                                $fullName = trim(implode(' ', array_filter([
                                                    $p->primer_nombre ?? null,
                                                    $p->segundo_nombre ?? null,
                                                    $p->primer_apellido ?? null,
                                                    $p->segundo_apellido ?? null,
                                                ])));
                                            @endphp
                                            <option value="{{ $p->idin }}" {{ (string)old('cargue412_id', $empleado->cargue412_id) === (string)$p->idin ? 'selected' : '' }}>
                                                {{ $fullName }} - {{ $p->numero_identificacion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('cargue412_id')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="s412-field">
                                <label for="fecha_consulta">Fecha consulta <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-calendar-alt s412-icon"></i>
                                    <input type="date" id="fecha_consulta" name="fecha_consulta" class="s412-input with-icon" value="{{ old('fecha_consulta', optional($empleado->fecha_consulta ? \Carbon\Carbon::parse($empleado->fecha_consulta) : null)->format('Y-m-d')) }}" required>
                                </div>
                                @error('fecha_consulta')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="s412-section">
                <div class="s412-section__head">
                    <span><i class="fas fa-ruler-combined mr-1"></i> Antropometria</span>
                    <span class="s412-step">Paso 2/5</span>
                </div>
                <div class="s412-section__body">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="s412-field">
                                <label for="talla_cm">Talla (cm) <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-ruler s412-icon"></i>
                                    <input type="number" step="0.01" id="talla_cm" name="talla_cm" class="s412-input with-icon" value="{{ old('talla_cm', $empleado->talla_cm) }}" required>
                                </div>
                                @error('talla_cm')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="s412-field">
                                <label for="peso_kilos">Peso (kg) <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-weight-hanging s412-icon"></i>
                                    <input type="number" step="0.0001" id="peso_kilos" name="peso_kilos" class="s412-input with-icon" value="{{ old('peso_kilos', $empleado->peso_kilos) }}" required>
                                </div>
                                @error('peso_kilos')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="s412-field">
                                <label for="puntajez">Puntaje Z <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-chart-line s412-icon"></i>
                                    <input type="number" step="0.0001" id="puntajez" name="puntajez" class="s412-input with-icon" value="{{ old('puntajez', $empleado->puntajez) }}" required>
                                </div>
                                @error('puntajez')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="s412-field">
                                <label for="perimetro_braqueal">Perimetro braquial <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-arrows-alt-h s412-icon"></i>
                                    <input type="text" id="perimetro_braqueal" name="perimetro_braqueal" class="s412-input with-icon" value="{{ old('perimetro_braqueal', $empleado->perimetro_braqueal) }}" required>
                                </div>
                                @error('perimetro_braqueal')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="s412-field">
                                <label for="requerimiento_energia_ftlc">Requerimiento energia FTLC <span class="s412-req">*</span></label>
                                <div class="s412-input-group">
                                    <i class="fas fa-bolt s412-icon"></i>
                                    <input type="text" id="requerimiento_energia_ftlc" name="requerimiento_energia_ftlc" class="s412-input with-icon" value="{{ old('requerimiento_energia_ftlc', $empleado->requerimiento_energia_ftlc) }}" required>
                                </div>
                                @error('requerimiento_energia_ftlc')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="s412-section">
                <div class="s412-section__head">
                    <span><i class="fas fa-notes-medical mr-1"></i> Manejo clinico</span>
                    <span class="s412-step">Paso 3/5</span>
                </div>
                <div class="s412-section__body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="s412-field">
                                <label for="clasificacion">Clasificacion <span class="s412-req">*</span></label>
                                <select id="clasificacion" name="clasificacion" class="s412-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach([
                                        'DESNUTRICION AGUDA MODERADA' => 'Desnutricion aguda moderada',
                                        'DESNUTRICION AGUDA SEVERA' => 'Desnutricion aguda severa',
                                        'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR' => 'Kwashiorkor',
                                        'DESNUTRICION AGUDA SEVERA TIPO MARASMO' => 'Marasmo',
                                        'DESNUTRICION AGUDA SEVERA MIXTA' => 'Mixta',
                                        'RIESGO DE DESNUTRICION' => 'Riesgo de desnutricion',
                                        'BUSQUEDA FALLIDA' => 'Busqueda fallida',
                                        'PESO ADECUADO PARA LA TALLA' => 'Peso adecuado para la talla'
                                    ] as $value => $label)
                                        <option value="{{ $value }}" {{ old('clasificacion', $empleado->clasificacion) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('clasificacion')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="s412-field">
                                <label for="observaciones">Observaciones <span class="s412-req">*</span></label>
                                <textarea id="observaciones" name="observaciones" class="s412-textarea" maxlength="600" required>{{ old('observaciones', $empleado->observaciones) }}</textarea>
                                @error('observaciones')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="s412-field">
                                <label for="medicamento">Medicamentos <span class="s412-req">*</span></label>
                                <select id="medicamento" name="medicamento[]" class="s412-select" multiple required>
                                    @foreach([
                                        '23072-2' => 'Albendazol 200mg',
                                        '54114-1' => 'Albendazol 400mg',
                                        '35662-18' => 'Acido folico',
                                        '31063-1' => 'Vitamina A',
                                        '27440-3' => 'Hierro',
                                        'NO APLICA' => 'No aplica'
                                    ] as $value => $label)
                                        <option value="{{ $value }}" {{ in_array($value, $medicamentosActuales) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('medicamento')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="s412-section">
                <div class="s412-section__head">
                    <span><i class="fas fa-syringe mr-1"></i> Demanda inducida</span>
                    <span class="s412-step">Paso 4/5</span>
                </div>
                <div class="s412-section__body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="s412-field">
                                <label for="Esquemq_complrto_pai_edad">Esquema PAI <span class="s412-req">*</span></label>
                                <select id="Esquemq_complrto_pai_edad" name="Esquemq_complrto_pai_edad" class="s412-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="INCOMPLETO" {{ old('Esquemq_complrto_pai_edad', $empleado->Esquemq_complrto_pai_edad) == 'INCOMPLETO' ? 'selected' : '' }}>Incompleto</option>
                                    <option value="COMPLETO" {{ old('Esquemq_complrto_pai_edad', $empleado->Esquemq_complrto_pai_edad) == 'COMPLETO' ? 'selected' : '' }}>Completo</option>
                                </select>
                                @error('Esquemq_complrto_pai_edad')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="s412-field">
                                <label for="Atecion_primocion_y_mantenimiento_res3280_2018">Atencion promocion y mantenimiento <span class="s412-req">*</span></label>
                                <select id="Atecion_primocion_y_mantenimiento_res3280_2018" name="Atecion_primocion_y_mantenimiento_res3280_2018" class="s412-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="SI" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018', $empleado->Atecion_primocion_y_mantenimiento_res3280_2018) == 'SI' ? 'selected' : '' }}>Si</option>
                                    <option value="NO" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018', $empleado->Atecion_primocion_y_mantenimiento_res3280_2018) == 'NO' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('Atecion_primocion_y_mantenimiento_res3280_2018')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="s412-section">
                <div class="s412-section__head">
                    <span><i class="fas fa-check-circle mr-1"></i> Cierre y control</span>
                    <span class="s412-step">Paso 5/5</span>
                </div>
                <div class="s412-section__body">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="s412-field">
                                <label for="est_act_menor">Estado actual del menor</label>
                                <select id="est_act_menor" name="est_act_menor" class="s412-select">
                                    <option value="">Seleccione...</option>
                                    @foreach([
                                        'DESNUTRICION AGUDA MODERADA',
                                        'PESO ADECUADO PARA LA TALLA',
                                        'RIESGO DE DESNUTRICION AGUDA',
                                        'DESNUTRICION AGUDA SEVERA TIPO MARASMO',
                                        'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR',
                                        'DESNUTRICION AGUDA SEVERA MIXTA',
                                        'EN PROCESO DE RECUPERACION',
                                        'BUSQUEDA FALLIDA',
                                        'PROCESO DE RECUPERACION',
                                        'RECUPERADO',
                                        'FALLECIDO'
                                    ] as $estadoMenor)
                                        <option value="{{ $estadoMenor }}" {{ old('est_act_menor', $empleado->est_act_menor) == $estadoMenor ? 'selected' : '' }}>{{ $estadoMenor }}</option>
                                    @endforeach
                                </select>
                                @error('est_act_menor')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="s412-field">
                                <label for="tratamiento_f75">Tratamiento F75</label>
                                <select id="tratamiento_f75" name="tratamiento_f75" class="s412-select">
                                    <option value="">Seleccione...</option>
                                    <option value="SI" {{ old('tratamiento_f75', $empleado->tratamiento_f75) == 'SI' ? 'selected' : '' }}>Si</option>
                                    <option value="NO" {{ old('tratamiento_f75', $empleado->tratamiento_f75) == 'NO' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('tratamiento_f75')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4" id="row_fecha_recibio_tratf75" style="display:none;">
                            <div class="s412-field">
                                <label for="fecha_recibio_tratf75">Fecha recibio tratamiento F75</label>
                                <input type="date" id="fecha_recibio_tratf75" name="fecha_recibio_tratf75" class="s412-input" value="{{ old('fecha_recibio_tratf75', optional($empleado->fecha_recibio_tratf75 ? \Carbon\Carbon::parse($empleado->fecha_recibio_tratf75) : null)->format('Y-m-d')) }}">
                                @error('fecha_recibio_tratf75')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="s412-field">
                                <label for="estado">Estado de seguimiento <span class="s412-req">*</span></label>
                                <select id="estado" name="estado" class="s412-select" required>
                                    <option value="1" {{ (string)old('estado', $empleado->estado) === '1' ? 'selected' : '' }}>Abierto</option>
                                    <option value="0" {{ (string)old('estado', $empleado->estado) === '0' ? 'selected' : '' }}>Cerrado</option>
                                </select>
                                @error('estado')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4" id="input_oculto">
                            <div class="s412-field">
                                <label for="fecha_proximo_control">Fecha proximo seguimiento</label>
                                <input type="date" id="fecha_proximo_control" name="fecha_proximo_control" class="s412-input" value="{{ old('fecha_proximo_control', optional($empleado->fecha_proximo_control ? \Carbon\Carbon::parse($empleado->fecha_proximo_control) : null)->format('Y-m-d')) }}">
                                @error('fecha_proximo_control')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4" id="inputsuperoculto" style="display:none;">
                            <div class="s412-field">
                                <label for="motivo_reapuertura">Motivo de cierre/reapertura</label>
                                <textarea id="motivo_reapuertura" name="motivo_reapuertura" class="s412-textarea" style="min-height:80px;">{{ old('motivo_reapuertura', $empleado->motivo_reapuertura) }}</textarea>
                                @error('motivo_reapuertura')<div class="s412-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="s412-actions">
                        <button id="update-btn" type="button" class="s412-btn-main" onclick="submitEditForm()">
                            <i class="fas fa-save"></i> Actualizar seguimiento
                        </button>
                        <a href="{{ url('new412_seguimiento') }}" class="s412-btn-back">
                            <i class="fas fa-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="s412-overlay">
        <div class="s412-overlay__content">
            <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;"></div>
            <div class="s412-overlay__text">Actualizando seguimiento y validando informacion...</div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        $('#cargue412_id').select2({
            placeholder: 'Paciente asignado',
            allowClear: false,
            width: '100%'
        });

        $('#clasificacion, #Esquemq_complrto_pai_edad, #Atecion_primocion_y_mantenimiento_res3280_2018, #est_act_menor, #tratamiento_f75, #estado').select2({
            width: '100%'
        });

        $('#medicamento').select2({
            placeholder: 'Selecciona medicamento(s)',
            closeOnSelect: false,
            width: '100%'
        });

        $('#estado').on('change', function () {
            if (this.value === '0') {
                $('#input_oculto').stop(true, true).slideUp();
                $('#fecha_proximo_control').val('');
                $('#inputsuperoculto').stop(true, true).slideDown();
            } else {
                $('#input_oculto').stop(true, true).slideDown();
                $('#inputsuperoculto').stop(true, true).slideUp();
            }
        }).trigger('change');

        $('#tratamiento_f75').on('change', function () {
            if (this.value === 'SI') {
                $('#row_fecha_recibio_tratf75').stop(true, true).slideDown();
            } else {
                $('#row_fecha_recibio_tratf75').stop(true, true).slideUp();
                $('#fecha_recibio_tratf75').val('');
            }
        }).trigger('change');
    });

    function submitEditForm() {
        $('#s412-overlay').addClass('show');
        $('#update-btn').prop('disabled', true);
        setTimeout(function () {
            $('#update-form').submit();
        }, 80);
    }
</script>
@stop
