@extends('adminlte::page')

@section('title', 'Editar Seguimiento - Caso #'.$asignacion->id)

@php
    $nombreCompleto = trim(implode(' ', array_filter([
        $asignacion->pri_nom_ ?? null,
        $asignacion->seg_nom_ ?? null,
        $asignacion->pri_ape_ ?? null,
        $asignacion->seg_ape_ ?? null,
    ])));

    $summaryItems = [
        ['label' => 'Tipo ID', 'value' => $asignacion->tip_ide_ ?? 'N/D'],
        ['label' => 'Numero identificacion', 'value' => $asignacion->num_ide_ ?? 'N/D'],
        ['label' => 'Fecha notificacion', 'value' => $asignacion->fec_not ?? 'N/D'],
        ['label' => 'Evento', 'value' => $asignacion->nom_eve ?? 'N/D'],
        ['label' => 'Municipio residencia', 'value' => $asignacion->nmun_resi ?? 'N/D'],
        ['label' => 'UPGD', 'value' => $asignacion->nom_upgd ?? 'N/D'],
        ['label' => 'Telefono', 'value' => $asignacion->telefono_ ?? 'N/D'],
        ['label' => 'Prestador primario', 'value' => optional($asignacion->user)->name ?? 'N/D'],
    ];

    $trackerSteps = [
        ['key' => 'hospitalizacion', 'label' => 'Hospitalizacion', 'day' => 'Base clinica'],
        ['key' => 'inmediato', 'label' => 'Seguimiento inmediato', 'day' => '48-72h'],
        ['key' => 'seguimiento-1', 'label' => 'Seguimiento 1', 'day' => 'Post egreso'],
        ['key' => 'seguimiento-2', 'label' => 'Seguimiento 2', 'day' => 'Dia 7'],
        ['key' => 'seguimiento-3', 'label' => 'Seguimiento 3', 'day' => 'Dia 14'],
        ['key' => 'seguimiento-4', 'label' => 'Seguimiento 4', 'day' => 'Dia 21'],
        ['key' => 'seguimiento-5', 'label' => 'Seguimiento 5', 'day' => 'Dia 28'],
        ['key' => 'controles', 'label' => 'Controles finales', 'day' => 'Post mes'],
    ];

    $crit = [
        'eclampsia' => 'Eclampsia',
        'preeclampsia_severa' => 'Preeclampsia severa',
        'sepsis_infeccion_sistemica_severa' => 'Sepsis o infeccion sistemica severa',
        'hemorragia_obstetrica_severa' => 'Hemorragia obstetrica severa',
        'ruptura_uterina' => 'Ruptura uterina',
        'falla_cardiovascular' => 'Falla cardiovascular',
        'falla_renal' => 'Falla renal',
        'falla_hepatica' => 'Falla hepatica',
        'falla_cerebral' => 'Falla cerebral',
        'falla_respiratoria' => 'Falla respiratoria',
        'falla_coagulacion' => 'Falla coagulacion',
        'cirugia_adicional' => 'Cirugia adicional',
    ];

    $v = function ($name, $val = null) {
        return old($name, $val);
    };

    $vdate = function ($name, $val = null) {
        $raw = old($name, $val);
        if ($raw === null || $raw === '') {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return is_string($raw) ? $raw : '';
        }
    };

    $seguimientoDefaults = [
        'fecha_hospitalizacion' => $vdate('fecha_hospitalizacion', $seguimiento->fecha_hospitalizacion),
        'fecha_egreso' => $vdate('fecha_egreso', $seguimiento->fecha_egreso),
        'institucion_egreso_paciente' => $v('institucion_egreso_paciente', $seguimiento->institucion_egreso_paciente),
        'gestion_hospitalizacion' => $v('gestion_hospitalizacion', $seguimiento->gestion_hospitalizacion),
        'ttl_criter' => $v('ttl_criter', $seguimiento->ttl_criter),
        'diagnostico_cie10' => $v('diagnostico_cie10', $seguimiento->diagnostico_cie10),
        'causa_agrupada' => $v('causa_agrupada', $seguimiento->causa_agrupada),
        'descripcion_seguimiento_inmediato' => $v('descripcion_seguimiento_inmediato', $seguimiento->descripcion_seguimiento_inmediato),
        'fecha_control_rn_inmediato' => $vdate('fecha_control_rn_inmediato', $seguimiento->fecha_control_rn_inmediato),
        'seguimiento_efectivo_inmediato' => (string) $v(
            'seguimiento_efectivo_inmediato',
            ($seguimiento->seguimiento_efectivo_inmediato === null || $seguimiento->seguimiento_efectivo_inmediato === '') ? '0' : (string) $seguimiento->seguimiento_efectivo_inmediato
        ),
        'metodo_anticonceptivo' => $v('metodo_anticonceptivo', $seguimiento->metodo_anticonceptivo),
        'gestion_posegreso_1' => $v('gestion_posegreso_1', $seguimiento->gestion_posegreso_1),
        'gestion_primera_semana' => $v('gestion_primera_semana', $seguimiento->gestion_primera_semana),
        'gestion_segunda_semana' => $v('gestion_segunda_semana', $seguimiento->gestion_segunda_semana),
        'gestion_tercera_semana' => $v('gestion_tercera_semana', $seguimiento->gestion_tercera_semana),
        'fecha_consulta_lactancia' => $vdate('fecha_consulta_lactancia', $seguimiento->fecha_consulta_lactancia),
        'fecha_control_metodo' => $vdate('fecha_control_metodo', $seguimiento->fecha_control_metodo),
        'gestion_despues_mes' => $v('gestion_despues_mes', $seguimiento->gestion_despues_mes),
        'fecha_consulta_6_meses' => $vdate('fecha_consulta_6_meses', $seguimiento->fecha_consulta_6_meses),
        'fecha_consulta_1_ano' => $vdate('fecha_consulta_1_ano', $seguimiento->fecha_consulta_1_ano),
    ];

    foreach ($crit as $critName => $critLabel) {
        $seguimientoDefaults[$critName] = (bool) $v($critName, $seguimiento->{$critName} ?? false);
    }

    foreach ([1, 2, 3, 4, 5] as $idx) {
        $seguimientoDefaults['fecha_seguimiento_'.$idx] = $vdate('fecha_seguimiento_'.$idx, $seguimiento->{'fecha_seguimiento_'.$idx} ?? null);
        $seguimientoDefaults['paciente_sigue_embarazo_'.$idx] = (string) $v('paciente_sigue_embarazo_'.$idx, $seguimiento->{'paciente_sigue_embarazo_'.$idx} ?? '');
        $seguimientoDefaults['fecha_control_'.$idx] = $vdate('fecha_control_'.$idx, $seguimiento->{'fecha_control_'.$idx} ?? null);
        $seguimientoDefaults['fecha_consulta_rn_'.$idx] = $vdate('fecha_consulta_rn_'.$idx, $seguimiento->{'fecha_consulta_rn_'.$idx} ?? null);
        $seguimientoDefaults['entrega_medicamentos_labs_'.$idx] = $v('entrega_medicamentos_labs_'.$idx, $seguimiento->{'entrega_medicamentos_labs_'.$idx} ?? null);
    }

    foreach ([1, 3, 4, 5] as $idx) {
        $seguimientoDefaults['tipo_seguimiento_'.$idx] = (string) $v('tipo_seguimiento_'.$idx, $seguimiento->{'tipo_seguimiento_'.$idx} ?? '');
    }

    foreach ([2, 3, 4, 5] as $idx) {
        $seguimientoDefaults['seguimiento_efectivo_'.$idx] = (string) $v(
            'seguimiento_efectivo_'.$idx,
            ($seguimiento->{'seguimiento_efectivo_'.$idx} === null || $seguimiento->{'seguimiento_efectivo_'.$idx} === '') ? '0' : (string) $seguimiento->{'seguimiento_efectivo_'.$idx}
        );
    }

    $hasOldInput = !empty(session()->getOldInput());
@endphp

@section('content_header')
<div class="seg-hero">
    <div class="seg-hero__brand">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="seg-hero__logo">
        <div>
            <h1 class="seg-hero__title">Editar seguimiento Maestro SIV 549</h1>
            <p class="seg-hero__subtitle">Actualiza el seguimiento del caso asignado con una vista mas clara, manteniendo intacto el flujo actual de guardado.</p>
        </div>
    </div>
    <a href="{{ route('seguimientos.index') }}" class="btn btn-outline-light btn-sm seg-hero__back">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Hay datos por revisar</div>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-outline card-info seg-card mb-4">
        <div class="card-header border-0">
            <span class="seg-eyebrow">Caso asignado</span>
            <h3 class="card-title seg-card__title">
                <i class="fas fa-id-card-alt mr-1"></i> Resumen rapido del caso
            </h3>
        </div>
        <div class="card-body">
            <div class="seg-patient-banner mb-4">
                <div>
                    <span class="seg-patient-banner__label">Paciente</span>
                    <h4 class="seg-patient-banner__title mb-1">{{ $nombreCompleto ?: 'Sin nombre registrado' }}</h4>
                    <div class="text-muted">{{ ($asignacion->tip_ide_ ?? 'N/D') . ' ' . ($asignacion->num_ide_ ?? '') }}</div>
                </div>
                <div class="text-md-right">
                    <span class="badge badge-light mb-2">Caso #{{ $asignacion->id }}</span>
                    <div><strong>IPS:</strong> {{ optional($asignacion->user)->name ?? 'N/D' }}</div>
                    <small class="text-muted">{{ optional($asignacion->user)->codigohabilitacion ?? 'Sin codigo' }}</small>
                </div>
            </div>

            <div class="row">
                @foreach($summaryItems as $item)
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="seg-summary-tile">
                            <span class="seg-summary-tile__label">{{ $item['label'] }}</span>
                            <strong class="seg-summary-tile__value">{{ $item['value'] }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary seg-card">
        <div class="card-header border-0">
            <span class="seg-eyebrow">Formulario</span>
            <h3 class="card-title seg-card__title">
                <i class="fas fa-notes-medical mr-1"></i> Edicion del seguimiento
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('asignaciones.seguimientmaestrosiv549.update', [$asignacion, $seguimiento]) }}" method="POST" id="seguimiento549Form" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="seg-orbit mb-4">
                    <div class="seg-orbit__head">
                        <div>
                            <span class="seg-eyebrow">Ruta sugerida</span>
                            <h4 class="seg-orbit__title mb-1">Secuencia de seguimientos</h4>
                            <p class="seg-orbit__subtitle mb-0">Cada bloque se desbloquea visualmente cuando diligencias el paso anterior, para facilitar un orden claro de trabajo.</p>
                        </div>
                    </div>
                    <div class="seg-form-note mt-3" role="status" aria-live="polite">
                        <i class="fas fa-info-circle mr-2"></i>
                        Cada bloque queda completo solo cuando todos sus campos obligatorios estan diligenciados. Si falta uno, el siguiente seguimiento permanece bloqueado.
                    </div>
                    <div class="seg-tracker">
                        @foreach($trackerSteps as $step)
                            <div class="seg-step" data-step-key="{{ $step['key'] }}">
                                <span class="seg-step__day">{{ $step['day'] }}</span>
                                <strong class="seg-step__label">{{ $step['label'] }}</strong>
                                <small class="seg-step__state">Pendiente</small>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="seg-section" data-stage-key="hospitalizacion" data-stage-title="Hospitalizacion" data-complete-fields="fecha_hospitalizacion,fecha_egreso,institucion_egreso_paciente,gestion_hospitalizacion,ttl_criter,diagnostico_cie10,causa_agrupada">
                    <div class="seg-section__head">
                        <span class="seg-section__icon"><i class="fas fa-hospital-user"></i></span>
                        <div>
                            <h5 class="mb-0">Hospitalizacion</h5>
                            <small class="text-muted">Registro inicial y criterios clinicos asociados.</small>
                        </div>
                        <div class="seg-section__meta">
                            <span class="seg-day-pill">Base clinica</span>
                            <span class="seg-status-pill">Disponible</span>
                        </div>
                    </div>
                    <div class="seg-section__content">

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label class="seg-label">Fecha hospitalizacion</label>
                            <input type="date" name="fecha_hospitalizacion" class="form-control seg-input" value="{{ old('fecha_hospitalizacion') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="seg-label">Fecha egreso</label>
                            <input type="date" name="fecha_egreso" class="form-control seg-input" value="{{ old('fecha_egreso') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="seg-label">Institucion que da el egreso a la paciente</label>
                            <input type="text" name="institucion_egreso_paciente" class="form-control seg-input" value="{{ old('institucion_egreso_paciente') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="seg-label">Gestion durante hospitalizacion</label>
                        <textarea name="gestion_hospitalizacion" class="form-control seg-input" rows="2">{{ old('gestion_hospitalizacion') }}</textarea>
                    </div>

                    <div class="seg-subtitle">Criterios y complicaciones</div>
                    <div class="row">
                        @foreach($crit as $name => $label)
                            <input type="hidden" name="{{ $name }}" value="0">
                            <div class="form-group col-md-3">
                                <div class="seg-toggle-card {{ old($name) ? 'is-on' : '' }}">
                                    <div class="seg-toggle-card__top">
                                        <label class="seg-toggle-card__label" for="{{ $name }}">{{ $label }}</label>
                                        <span class="seg-toggle-card__state">{{ old($name) ? 'Aplica' : 'No aplica' }}</span>
                                    </div>
                                    <div class="seg-toggle-control">
                                        <span class="seg-toggle-control__text">No</span>
                                        <input type="checkbox" class="seg-toggle__input" id="{{ $name }}" name="{{ $name }}" value="1" {{ old($name) ? 'checked' : '' }}>
                                        <label class="seg-toggle__track" for="{{ $name }}">
                                            <span class="seg-toggle__thumb"></span>
                                        </label>
                                        <span class="seg-toggle-control__text seg-toggle-control__text--on">Si</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label class="seg-label">Ttl_criter</label>
                            <input type="number" min="0" max="50" name="ttl_criter" class="form-control seg-input" value="{{ old('ttl_criter') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="seg-label">Diagnostico CIE 10</label>
                            <input type="text" name="diagnostico_cie10" class="form-control seg-input" value="{{ old('diagnostico_cie10') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="seg-label">Causa agrupada</label>
                            <input type="text" name="causa_agrupada" class="form-control seg-input" value="{{ old('causa_agrupada') }}">
                        </div>
                    </div>
                    </div>
                </div>

                <div class="seg-section" data-stage-key="inmediato" data-stage-title="Seguimiento inmediato" data-complete-fields="descripcion_seguimiento_inmediato,fecha_control_rn_inmediato,seguimiento_efectivo_inmediato">
                    <div class="seg-section__head">
                        <span class="seg-section__icon"><i class="fas fa-bolt"></i></span>
                        <div>
                            <h5 class="mb-0">Seguimiento inmediato</h5>
                            <small class="text-muted">Ventana de 48 a 72 horas.</small>
                        </div>
                        <div class="seg-section__meta">
                            <span class="seg-day-pill">48-72h</span>
                            <span class="seg-status-pill">Bloqueado</span>
                        </div>
                    </div>
                    <div class="seg-section__content">

                    <div class="row">
                        <div class="form-group col-md-8">
                            <label class="seg-label">Descripcion</label>
                            <textarea name="descripcion_seguimiento_inmediato" class="form-control seg-input" rows="2">{{ old('descripcion_seguimiento_inmediato') }}</textarea>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="seg-label">Control del recien nacido</label>
                            <input type="date" name="fecha_control_rn_inmediato" class="form-control seg-input" value="{{ old('fecha_control_rn_inmediato') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label class="seg-label">Seguimiento efectivo</label>
                            @php $sei = (string) old('seguimiento_efectivo_inmediato', ''); @endphp
                            <select name="seguimiento_efectivo_inmediato" class="form-control seg-input">
                                <option value="" {{ $sei===''?'selected':'' }}>--</option>
                                <option value="1" {{ $sei==='1'?'selected':'' }}>Si</option>
                                <option value="0" {{ $sei==='0'?'selected':'' }}>No</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="seg-label">Soporte PDF historia clinica (max 5 MB)</label>
                            <input type="file" name="soporte_inmediato_pdf" class="form-control seg-input" accept="application/pdf">
                            @if(!empty($seguimiento->soporte_inmediato_pdf))
                                <a href="{{ asset('storage/'.$seguimiento->soporte_inmediato_pdf) }}" target="_blank" class="btn btn-sm btn-outline-info mt-2">
                                    <i class="fas fa-file-pdf mr-1"></i> Ver PDF actual
                                </a>
                            @endif
                            <small class="text-muted d-block mt-1">Si adjuntas uno nuevo, reemplaza el soporte actual.</small>
                        </div>
                    </div>
                    </div>
                </div>

                @php
                    $seguimientos = [
                        1 => ['titulo' => 'Seguimiento 1', 'subtitulo' => 'Post egreso', 'tipo' => true, 'efectivo' => false],
                        2 => ['titulo' => 'Seguimiento 2', 'subtitulo' => '7 dias', 'tipo' => false, 'efectivo' => true],
                        3 => ['titulo' => 'Seguimiento 3', 'subtitulo' => '14 dias', 'tipo' => true, 'efectivo' => true],
                        4 => ['titulo' => 'Seguimiento 4', 'subtitulo' => '21 dias', 'tipo' => true, 'efectivo' => true],
                        5 => ['titulo' => 'Seguimiento 5', 'subtitulo' => '28 dias', 'tipo' => true, 'efectivo' => true],
                    ];
                @endphp

                @foreach($seguimientos as $idx => $cfg)
                    @php
                        $requiredFields = ['fecha_seguimiento_'.$idx];
                        if ($cfg['tipo']) {
                            $requiredFields[] = 'tipo_seguimiento_'.$idx;
                        }
                        $requiredFields[] = 'paciente_sigue_embarazo_'.$idx;
                        $requiredFields[] = 'fecha_control_'.$idx;
                        if ($idx === 1) {
                            $requiredFields[] = 'metodo_anticonceptivo';
                        }
                        $requiredFields[] = 'fecha_consulta_rn_'.$idx;
                        $requiredFields[] = 'entrega_medicamentos_labs_'.$idx;
                        if ($cfg['efectivo']) {
                            $requiredFields[] = 'seguimiento_efectivo_'.$idx;
                        }
                        if ($idx === 1) {
                            $requiredFields[] = 'gestion_posegreso_1';
                        } elseif ($idx === 2) {
                            $requiredFields[] = 'gestion_primera_semana';
                        } elseif ($idx === 3) {
                            $requiredFields[] = 'gestion_segunda_semana';
                        } elseif ($idx === 4) {
                            $requiredFields[] = 'gestion_tercera_semana';
                        }
                    @endphp
                    <div class="seg-section" data-stage-key="seguimiento-{{ $idx }}" data-stage-title="{{ $cfg['titulo'] }}" data-complete-fields="{{ implode(',', $requiredFields) }}">
                        <div class="seg-section__head">
                            <span class="seg-section__icon"><i class="fas fa-calendar-check"></i></span>
                            <div>
                                <h5 class="mb-0">{{ $cfg['titulo'] }}</h5>
                                <small class="text-muted">{{ $cfg['subtitulo'] }}</small>
                            </div>
                            <div class="seg-section__meta">
                                <span class="seg-day-pill">{{ $cfg['subtitulo'] }}</span>
                                <span class="seg-status-pill">Bloqueado</span>
                            </div>
                        </div>
                        <div class="seg-section__content">

                        <div class="row">
                            <div class="form-group col-md-3">
                                <label class="seg-label">Fecha</label>
                                <input type="date" name="fecha_seguimiento_{{ $idx }}" class="form-control seg-input" value="{{ old('fecha_seguimiento_'.$idx) }}">
                            </div>

                            @if($cfg['tipo'])
                                <div class="form-group col-md-3">
                                    <label class="seg-label">Tipo</label>
                                    <select name="tipo_seguimiento_{{ $idx }}" class="form-control seg-input">
                                        <option value="">--</option>
                                        <option value="1" {{ old('tipo_seguimiento_'.$idx)=='1'?'selected':'' }}>Telefonico</option>
                                        <option value="2" {{ old('tipo_seguimiento_'.$idx)=='2'?'selected':'' }}>Domiciliario</option>
                                        @if($idx === 1)
                                            <option value="3" {{ old('tipo_seguimiento_'.$idx)=='3'?'selected':'' }}>Otro</option>
                                        @endif
                                    </select>
                                </div>
                            @endif

                            <div class="form-group col-md-3">
                                <label class="seg-label">Sigue en embarazo</label>
                                <select name="paciente_sigue_embarazo_{{ $idx }}" class="form-control seg-input">
                                    <option value="">--</option>
                                    <option value="1" {{ old('paciente_sigue_embarazo_'.$idx)==='1'?'selected':'' }}>Si</option>
                                    <option value="0" {{ old('paciente_sigue_embarazo_'.$idx)==='0'?'selected':'' }}>No</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="seg-label">Fecha control</label>
                                <input type="date" name="fecha_control_{{ $idx }}" class="form-control seg-input" value="{{ old('fecha_control_'.$idx) }}">
                            </div>
                        </div>

                        <div class="row">
                            @if($idx === 1)
                                <div class="form-group col-md-4">
                                    <label class="seg-label">Metodo anticonceptivo elegido y provisto</label>
                                    <input type="text" name="metodo_anticonceptivo" class="form-control seg-input" value="{{ old('metodo_anticonceptivo') }}">
                                </div>
                            @endif

                            <div class="form-group {{ $idx === 1 ? 'col-md-4' : 'col-md-4' }}">
                                <label class="seg-label">Fecha consulta RN</label>
                                <input type="date" name="fecha_consulta_rn_{{ $idx }}" class="form-control seg-input" value="{{ old('fecha_consulta_rn_'.$idx) }}">
                            </div>

                            <div class="form-group {{ $idx === 1 ? 'col-md-4' : 'col-md-4' }}">
                                <label class="seg-label">Entrega de medicamentos o labs en casa</label>
                                <input type="text" name="entrega_medicamentos_labs_{{ $idx }}" class="form-control seg-input" value="{{ old('entrega_medicamentos_labs_'.$idx) }}">
                            </div>

                            @if($cfg['efectivo'])
                                <div class="form-group col-md-4">
                                    <label class="seg-label">Seguimiento efectivo</label>
                                    @php $se = (string) old('seguimiento_efectivo_'.$idx, ''); @endphp
                                    <select name="seguimiento_efectivo_{{ $idx }}" class="form-control seg-input">
                                        <option value="" {{ $se===''?'selected':'' }}>--</option>
                                        <option value="1" {{ $se==='1'?'selected':'' }}>Si</option>
                                        <option value="0" {{ $se==='0'?'selected':'' }}>No</option>
                                    </select>
                                </div>
                            @endif
                        </div>

                        @if($idx === 1)
                            <div class="form-group mb-0">
                                <label class="seg-label">Gestion realizada post egreso</label>
                                <textarea name="gestion_posegreso_1" class="form-control seg-input" rows="2">{{ old('gestion_posegreso_1') }}</textarea>
                            </div>
                        @elseif($idx === 2)
                            <div class="row">
                                <div class="form-group col-md-12 mb-0">
                                    <label class="seg-label">Gestion primera semana</label>
                                    <input type="text" name="gestion_primera_semana" class="form-control seg-input" value="{{ old('gestion_primera_semana') }}">
                                </div>
                            </div>
                        @elseif($idx === 3)
                            <div class="row">
                                <div class="form-group col-md-12 mb-0">
                                    <label class="seg-label">Gestion segunda semana</label>
                                    <input type="text" name="gestion_segunda_semana" class="form-control seg-input" value="{{ old('gestion_segunda_semana') }}">
                                </div>
                            </div>
                        @elseif($idx === 4)
                            <div class="row">
                                <div class="form-group col-md-12 mb-0">
                                    <label class="seg-label">Gestion tercera semana</label>
                                    <input type="text" name="gestion_tercera_semana" class="form-control seg-input" value="{{ old('gestion_tercera_semana') }}">
                                </div>
                            </div>
                        @endif
                        <div class="row mt-2">
                            <div class="form-group col-md-12 mb-0">
                                <label class="seg-label">Soporte PDF historia clinica {{ $cfg['titulo'] }} (max 5 MB)</label>
                                <input type="file" name="soporte_seguimiento_{{ $idx }}_pdf" class="form-control seg-input" accept="application/pdf">
                                @php $supportField = 'soporte_seguimiento_'.$idx.'_pdf'; @endphp
                                @if(!empty($seguimiento->{$supportField}))
                                    <a href="{{ asset('storage/'.$seguimiento->{$supportField}) }}" target="_blank" class="btn btn-sm btn-outline-info mt-2">
                                        <i class="fas fa-file-pdf mr-1"></i> Ver PDF actual
                                    </a>
                                @endif
                                <small class="text-muted d-block mt-1">Si adjuntas uno nuevo, reemplaza el soporte actual.</small>
                            </div>
                        </div>
                    </div>
                    </div>
                @endforeach

                <div class="seg-section" data-stage-key="controles" data-stage-title="Controles finales" data-complete-fields="fecha_consulta_lactancia,fecha_control_metodo,gestion_despues_mes,fecha_consulta_6_meses,fecha_consulta_1_ano">
                    <div class="seg-section__head">
                        <span class="seg-section__icon"><i class="fas fa-baby"></i></span>
                        <div>
                            <h5 class="mb-0">Controles adicionales</h5>
                            <small class="text-muted">Seguimiento posterior y controles complementarios.</small>
                        </div>
                        <div class="seg-section__meta">
                            <span class="seg-day-pill">Post mes</span>
                            <span class="seg-status-pill">Bloqueado</span>
                        </div>
                    </div>
                    <div class="seg-section__content">

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label class="seg-label">Consulta apoyo lactancia</label>
                            <input type="date" name="fecha_consulta_lactancia" class="form-control seg-input" value="{{ old('fecha_consulta_lactancia') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="seg-label">Primer control metodo anticonceptivo</label>
                            <input type="date" name="fecha_control_metodo" class="form-control seg-input" value="{{ old('fecha_control_metodo') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="seg-label">Gestion despues del mes</label>
                            <input type="text" name="gestion_despues_mes" class="form-control seg-input" value="{{ old('gestion_despues_mes') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="seg-label">Consulta 6 meses</label>
                            <input type="date" name="fecha_consulta_6_meses" class="form-control seg-input" value="{{ old('fecha_consulta_6_meses') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="seg-label">Consulta 1 ano</label>
                            <input type="date" name="fecha_consulta_1_ano" class="form-control seg-input" value="{{ old('fecha_consulta_1_ano') }}">
                        </div>
                    </div>
                    </div>
                </div>

                <div class="seg-actions">
                    <button type="submit" class="btn btn-seg-primary" id="btnGuardarSeguimiento549">
                        <i class="fas fa-save mr-1"></i> Actualizar seguimiento
                    </button>
                    <a href="{{ route('seguimientos.index') }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .seg-hero{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:1.1rem 1.35rem;
        border-radius:22px;
        color:#fff;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 28%),
            linear-gradient(135deg, #0f5f86 0%, #0e8da5 48%, #228861 100%);
        box-shadow:0 16px 34px rgba(13, 71, 97, .16);
    }
    .seg-hero__brand{
        display:flex;
        align-items:center;
        gap:1rem;
    }
    .seg-hero__logo{
        width:64px;
        height:64px;
        object-fit:contain;
        border-radius:16px;
        background:#fff;
        padding:6px;
        box-shadow:0 10px 18px rgba(0,0,0,.12);
    }
    .seg-hero__title{
        margin:0;
        font-size:1.7rem;
        font-weight:800;
    }
    .seg-hero__subtitle{
        margin:.3rem 0 0;
        color:rgba(255,255,255,.86);
    }
    .seg-hero__back{
        white-space:nowrap;
        border-radius:999px;
        padding:.55rem 1rem;
    }
    .seg-card{
        border-radius:20px;
        overflow:hidden;
        box-shadow:0 14px 28px rgba(15, 76, 92, .08);
    }
    .seg-card .card-header{
        background:linear-gradient(180deg, rgba(245,250,252,.96), rgba(255,255,255,.98));
        padding:1.15rem 1.3rem .85rem;
    }
    .seg-card .card-body{
        padding:1.35rem;
    }
    .seg-eyebrow{
        display:inline-block;
        margin-bottom:.4rem;
        padding:.18rem .55rem;
        border-radius:999px;
        font-size:.72rem;
        font-weight:700;
        letter-spacing:.08em;
        text-transform:uppercase;
        color:#0b6b7d;
        background:#dff3f8;
    }
    .seg-card__title{
        font-size:1.2rem;
        font-weight:700;
        color:#264653;
    }
    .seg-patient-banner{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        padding:1rem 1.1rem;
        border-radius:18px;
        border:1px solid #dfecee;
        background:linear-gradient(135deg, #f4fbfc, #f7fcf9);
    }
    .seg-patient-banner__label{
        display:inline-block;
        margin-bottom:.3rem;
        font-size:.75rem;
        font-weight:700;
        letter-spacing:.08em;
        text-transform:uppercase;
        color:#6d8b90;
    }
    .seg-patient-banner__title{
        color:#1f3942;
        font-weight:800;
    }
    .seg-summary-tile{
        height:100%;
        padding:1rem;
        border-radius:16px;
        background:linear-gradient(180deg, #ffffff, #f8fbfd);
        border:1px solid #e3eef1;
        box-shadow:0 8px 18px rgba(34, 87, 122, .05);
    }
    .seg-summary-tile__label{
        display:block;
        margin-bottom:.35rem;
        font-size:.8rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#6a8a91;
    }
    .seg-summary-tile__value{
        display:block;
        color:#1f3942;
        word-break:break-word;
    }
    .seg-orbit{
        padding:1.2rem;
        border-radius:20px;
        background:
            radial-gradient(circle at top left, rgba(19, 161, 191, .12), transparent 35%),
            linear-gradient(135deg, #f4fbfe, #f6fbf8);
        border:1px solid #d9edf3;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.7);
    }
    .seg-orbit__title{
        font-weight:800;
        color:#1f3942;
    }
    .seg-orbit__subtitle{
        max-width:760px;
        color:#5a747b;
    }
    .seg-tracker{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));
        gap:.9rem;
        margin-top:1rem;
    }
    .seg-form-note{
        display:flex;
        align-items:flex-start;
        gap:.35rem;
        padding:.75rem .9rem;
        border-radius:12px;
        border:1px solid #cfe8ef;
        background:linear-gradient(180deg, #f7fcff, #eef9ff);
        color:#2f5f6a;
        font-size:.88rem;
        line-height:1.4;
    }
    .seg-form-note i{
        color:#1b8fa1;
        margin-top:.1rem;
    }
    .seg-step{
        position:relative;
        padding:1rem;
        border-radius:18px;
        border:1px solid #d7e8eb;
        background:linear-gradient(180deg, #ffffff, #f7fbfd);
        box-shadow:0 10px 20px rgba(30, 74, 96, .05);
        transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease, opacity .22s ease;
    }
    .seg-step__day{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:28px;
        padding:.2rem .7rem;
        border-radius:999px;
        margin-bottom:.65rem;
        font-size:.76rem;
        font-weight:800;
        letter-spacing:.06em;
        text-transform:uppercase;
        color:#0a6c78;
        background:#dff3f8;
    }
    .seg-step__label{
        display:block;
        color:#24414a;
        font-size:1rem;
        line-height:1.2;
    }
    .seg-step__state{
        display:block;
        margin-top:.45rem;
        color:#6f8a91;
        font-weight:600;
    }
    .seg-step.is-active{
        border-color:#29a6bc;
        box-shadow:0 14px 28px rgba(21, 134, 167, .12);
        transform:translateY(-2px);
    }
    .seg-step.is-active .seg-step__state{
        color:#0c7f90;
    }
    .seg-step.is-complete{
        border-color:#8fd3b3;
        background:linear-gradient(180deg, #f8fffc, #eefaf4);
    }
    .seg-step.is-complete .seg-step__day{
        background:#daf5e8;
        color:#1b7f55;
    }
    .seg-step.is-complete .seg-step__state{
        color:#1b7f55;
    }
    .seg-step.is-locked{
        opacity:.58;
        filter:saturate(.75);
    }
    .seg-section{
        position:relative;
        margin-bottom:1.25rem;
        padding:1.2rem;
        border-radius:18px;
        border:1px solid #e5eff2;
        background:#fff;
        box-shadow:0 10px 18px rgba(30, 74, 96, .04);
        transition:transform .22s ease, box-shadow .22s ease, opacity .22s ease, border-color .22s ease;
    }
    .seg-section__head{
        display:flex;
        align-items:center;
        gap:.85rem;
        margin-bottom:1rem;
        padding-bottom:.85rem;
        border-bottom:1px solid #edf4f6;
    }
    .seg-section__icon{
        width:44px;
        height:44px;
        border-radius:14px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        box-shadow:0 10px 20px rgba(18, 122, 111, .18);
    }
    .seg-section__meta{
        margin-left:auto;
        display:flex;
        align-items:center;
        gap:.6rem;
        flex-wrap:wrap;
    }
    .seg-day-pill,
    .seg-status-pill{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:30px;
        border-radius:999px;
        padding:.28rem .75rem;
        font-size:.76rem;
        font-weight:800;
        letter-spacing:.05em;
        text-transform:uppercase;
    }
    .seg-day-pill{
        color:#0c6d79;
        background:#dff3f8;
    }
    .seg-status-pill{
        color:#6f8a91;
        background:#eef4f6;
    }
    .seg-status-pill.is-active{
        color:#0c7f90;
        background:#dff5fb;
    }
    .seg-status-pill.is-complete{
        color:#1b7f55;
        background:#daf5e8;
    }
    .seg-status-pill.is-locked{
        color:#7a8b90;
        background:#edf1f3;
    }
    .seg-section__content{
        position:relative;
        z-index:1;
    }
    .seg-section__missing{
        display:none;
        margin:-.25rem 0 1rem;
        padding:.75rem .9rem;
        border-radius:12px;
        border:1px solid #f1c7bd;
        background:linear-gradient(180deg, #fff8f6, #fff0ec);
        color:#8d3829;
        font-size:.88rem;
        font-weight:600;
    }
    .seg-section.is-active .seg-section__missing,
    .seg-section.is-locked .seg-section__missing{
        display:block;
    }
    .seg-input.is-missing{
        border-color:#dc6b55;
        background:#fff8f6;
        box-shadow:0 0 0 .12rem rgba(220, 107, 85, .12);
    }
    .seg-section__lock{
        position:absolute;
        inset:0;
        z-index:4;
        display:none;
        align-items:center;
        justify-content:center;
        border-radius:18px;
        background:linear-gradient(135deg, rgba(247, 251, 253, .82), rgba(244, 250, 248, .9));
        backdrop-filter:blur(2px);
        padding:1rem;
        text-align:center;
    }
    .seg-section__lock-card{
        max-width:320px;
        padding:1rem 1.1rem;
        border-radius:16px;
        border:1px solid rgba(157, 182, 189, .45);
        background:rgba(255,255,255,.92);
        box-shadow:0 14px 28px rgba(31, 57, 66, .08);
        color:#3d5860;
    }
    .seg-section__lock-card i{
        font-size:1.2rem;
        color:#5d7680;
        margin-bottom:.45rem;
    }
    .seg-section.is-locked{
        opacity:.72;
        border-style:dashed;
    }
    .seg-section.is-locked .seg-section__lock{
        display:flex;
    }
    .seg-section.is-active{
        border-color:#29a6bc;
        box-shadow:0 14px 30px rgba(21, 134, 167, .08);
    }
    .seg-section.is-complete{
        border-color:#8fd3b3;
        background:linear-gradient(180deg, #ffffff, #fbfffd);
    }
    .seg-label{
        display:block;
        margin-bottom:.45rem;
        font-weight:700;
        color:#15616d;
    }
    .seg-input{
        border-radius:14px;
        min-height:45px;
        border:1px solid #d7e8eb;
        background:#fbfeff;
    }
    .seg-input:focus{
        border-color:#1b9aaa;
        box-shadow:0 0 0 .2rem rgba(27,154,170,.15);
    }
    .seg-subtitle{
        margin-bottom:.8rem;
        font-size:.9rem;
        font-weight:700;
        color:#56767d;
        text-transform:uppercase;
        letter-spacing:.05em;
    }
    .seg-toggle-card{
        height:100%;
        padding:1rem 1rem .95rem;
        border-radius:18px;
        border:1px solid #d9e9ee;
        background:linear-gradient(180deg, #ffffff, #f7fbfd);
        box-shadow:0 10px 22px rgba(18, 53, 64, .04);
        transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .seg-toggle-card:hover{
        transform:translateY(-1px);
        box-shadow:0 16px 28px rgba(18, 53, 64, .08);
    }
    .seg-toggle-card.is-on{
        border-color:#8dd0c0;
        background:linear-gradient(180deg, #f6fffc, #eefaf6);
        box-shadow:0 14px 28px rgba(31, 134, 114, .12);
    }
    .seg-toggle-card__top{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:.8rem;
        margin-bottom:.9rem;
    }
    .seg-toggle-card__label{
        margin:0;
        font-size:1.06rem;
        font-weight:700;
        line-height:1.25;
        color:#253b4a;
        cursor:pointer;
    }
    .seg-toggle-card__state{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:84px;
        padding:.32rem .7rem;
        border-radius:999px;
        font-size:.76rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#70838d;
        background:#edf3f6;
        border:1px solid #dbe8ed;
        white-space:nowrap;
    }
    .seg-toggle-card.is-on .seg-toggle-card__state{
        color:#0f7b68;
        background:#dff7ee;
        border-color:#bfe8d8;
    }
    .seg-toggle-control{
        display:flex;
        align-items:center;
        gap:.7rem;
    }
    .seg-toggle-control__text{
        font-size:.82rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#89a0aa;
    }
    .seg-toggle-control__text--on{
        color:#0f7b68;
    }
    .seg-toggle__input{
        position:absolute;
        opacity:0;
        pointer-events:none;
    }
    .seg-toggle__track{
        position:relative;
        width:62px;
        height:34px;
        margin:0;
        border-radius:999px;
        cursor:pointer;
        background:linear-gradient(135deg, #d7e2e8, #bccad2);
        box-shadow:inset 0 2px 4px rgba(53, 78, 91, .16);
        transition:background .2s ease, box-shadow .2s ease;
    }
    .seg-toggle__thumb{
        position:absolute;
        top:4px;
        left:4px;
        width:26px;
        height:26px;
        border-radius:50%;
        background:#fff;
        box-shadow:0 6px 12px rgba(36, 56, 66, .22);
        transition:transform .2s ease;
    }
    .seg-toggle__input:checked + .seg-toggle__track{
        background:linear-gradient(135deg, #13a38a, #1794b2);
        box-shadow:inset 0 2px 4px rgba(8, 88, 98, .18), 0 8px 18px rgba(23, 148, 178, .18);
    }
    .seg-toggle__input:checked + .seg-toggle__track .seg-toggle__thumb{
        transform:translateX(28px);
    }
    .seg-toggle__input:focus + .seg-toggle__track{
        box-shadow:0 0 0 .2rem rgba(27,154,170,.16);
    }
    .seg-actions{
        display:flex;
        justify-content:flex-end;
        gap:.75rem;
        margin-top:1.5rem;
    }
    .btn-seg-primary{
        border:none;
        color:#fff;
        font-weight:700;
        min-height:46px;
        border-radius:14px;
        padding:.7rem 1.3rem;
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        box-shadow:0 12px 24px rgba(18, 122, 111, .22);
    }
    .btn-seg-primary:hover{
        color:#fff;
        filter:brightness(.98);
    }
    @media (max-width: 991px){
        .seg-hero{
            flex-direction:column;
            align-items:flex-start;
        }
    }
    @media (max-width: 767px){
        .seg-hero__brand{
            align-items:flex-start;
        }
        .seg-hero__title{
            font-size:1.35rem;
        }
        .seg-section__meta{
            margin-left:0;
            width:100%;
        }
        .seg-card .card-body,
        .seg-card .card-header,
        .seg-section{
            padding:1rem;
        }
        .seg-patient-banner,
        .seg-actions{
            flex-direction:column;
        }
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hasOldInput = @json($hasOldInput);
    const seguimientoDefaults = @json($seguimientoDefaults);

    Object.entries(seguimientoDefaults).forEach(([name, value]) => {
        const fields = Array.from(document.querySelectorAll(`[name="${name}"]`));
        if (!fields.length || hasOldInput) {
            return;
        }

        fields.forEach((field) => {
            if (field.type === 'hidden') {
                return;
            }

            if (field.type === 'checkbox') {
                field.checked = Boolean(value);
                return;
            }

            field.value = value ?? '';
        });
    });

    const sections = Array.from(document.querySelectorAll('.seg-section[data-stage-key]'));
    const trackerSteps = new Map(
        Array.from(document.querySelectorAll('.seg-step')).map((step) => [step.dataset.stepKey, step])
    );

    sections.forEach((section) => {
        if (!section.querySelector('.seg-section__lock')) {
            const overlay = document.createElement('div');
            overlay.className = 'seg-section__lock';
            overlay.innerHTML = `
                <div class="seg-section__lock-card">
                    <i class="fas fa-lock"></i>
                    <div class="font-weight-bold mb-1">Bloque pendiente</div>
                    <div class="seg-section__lock-message">Completa el paso anterior para habilitar esta etapa.</div>
                </div>
            `;
            section.appendChild(overlay);
        }

        if (!section.querySelector('.seg-section__missing')) {
            const missingAlert = document.createElement('div');
            missingAlert.className = 'seg-section__missing';
            missingAlert.setAttribute('role', 'alert');
            missingAlert.setAttribute('aria-live', 'polite');
            section.querySelector('.seg-section__content')?.prepend(missingAlert);
        }
    });

    function hasMeaningfulValue(field) {
        if (!field) return false;
        if (field.type === 'checkbox') return field.checked;
        return String(field.value || '').trim() !== '';
    }

    function requiredFieldNames(section) {
        return (section.dataset.completeFields || '')
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean);
    }

    function fieldsByName(section, name) {
        return Array.from(section.querySelectorAll(`[name="${name}"]`))
            .filter((field) => field.type !== 'hidden');
    }

    function fieldLabel(section, name) {
        const field = fieldsByName(section, name)[0];
        const label = field?.closest('.form-group')?.querySelector('label');
        return (label?.textContent || name).replace(/\s+/g, ' ').trim();
    }

    function missingFieldNames(section) {
        return requiredFieldNames(section).filter((name) => {
            const fields = fieldsByName(section, name);
            return !fields.length || !fields.some(hasMeaningfulValue);
        });
    }

    function isSectionStarted(section) {
        return requiredFieldNames(section).some((name) => {
            const fields = fieldsByName(section, name);
            return fields.some(hasMeaningfulValue);
        });
    }

    function isSectionComplete(section) {
        const fieldNames = (section.dataset.completeFields || '')
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean);

        if (!fieldNames.length) return false;

        return fieldNames.every((name) => {
            const fields = fieldsByName(section, name);
            return fields.some(hasMeaningfulValue);
        });
    }

    function updateMissingStyles(section, forceVisible = false) {
        const missingNames = missingFieldNames(section);
        const missingAlert = section.querySelector('.seg-section__missing');

        requiredFieldNames(section).forEach((name) => {
            fieldsByName(section, name).forEach((field) => {
                field.classList.toggle('is-missing', missingNames.includes(name) && (forceVisible || isSectionStarted(section)));
            });
        });

        if (!missingAlert) {
            return missingNames;
        }

        if (!missingNames.length) {
            missingAlert.textContent = '';
            missingAlert.style.display = 'none';
            return missingNames;
        }

        const labels = missingNames.map((name) => fieldLabel(section, name));
        missingAlert.textContent = `Falta completar en ${section.dataset.stageTitle || 'este bloque'}: ${labels.join(', ')}.`;
        missingAlert.style.display = (forceVisible || isSectionStarted(section)) ? 'block' : 'none';

        return missingNames;
    }

    function setStatusPill(section, state) {
        const pill = section.querySelector('.seg-status-pill');
        if (!pill) return;

        pill.classList.remove('is-active', 'is-complete', 'is-locked');

        if (state === 'complete') {
            pill.textContent = 'Completo';
            pill.classList.add('is-complete');
            return;
        }

        if (state === 'active') {
            pill.textContent = 'Disponible';
            pill.classList.add('is-active');
            return;
        }

        pill.textContent = 'Bloqueado';
        pill.classList.add('is-locked');
    }

    function setTrackerState(step, state) {
        if (!step) return;
        step.classList.remove('is-active', 'is-complete', 'is-locked');

        const stateLabel = step.querySelector('.seg-step__state');
        if (!stateLabel) return;

        if (state === 'complete') {
            step.classList.add('is-complete');
            stateLabel.textContent = 'Completado';
            return;
        }

        if (state === 'active') {
            step.classList.add('is-active');
            stateLabel.textContent = 'Disponible';
            return;
        }

        step.classList.add('is-locked');
        stateLabel.textContent = 'Bloqueado';
    }

    function updateSectionStates() {
        sections.forEach((section, index) => {
            const currentComplete = isSectionComplete(section);
            const previousSection = index === 0 ? null : sections[index - 1];
            const unlocked = !previousSection || isSectionComplete(previousSection);
            const lockMessage = section.querySelector('.seg-section__lock-message');
            const previousMissing = previousSection ? missingFieldNames(previousSection) : [];

            section.classList.toggle('is-locked', !unlocked);
            section.classList.toggle('is-complete', currentComplete);
            section.classList.toggle('is-active', unlocked && !currentComplete);
            updateMissingStyles(section, unlocked && isSectionStarted(section));

            if (lockMessage && previousSection) {
                const previousTitle = previousSection.dataset.stageTitle || 'el paso anterior';
                const currentTitle = section.dataset.stageTitle || 'este bloque';
                const missingLabels = previousMissing.map((name) => fieldLabel(previousSection, name));
                lockMessage.textContent = missingLabels.length
                    ? `Completa ${previousTitle} para desbloquear ${currentTitle}. Falta: ${missingLabels.join(', ')}.`
                    : `Completa ${previousTitle} para desbloquear ${currentTitle}.`;
            }

            if (currentComplete) {
                setStatusPill(section, 'complete');
                setTrackerState(trackerSteps.get(section.dataset.stageKey), 'complete');
                return;
            }

            if (unlocked) {
                setStatusPill(section, 'active');
                setTrackerState(trackerSteps.get(section.dataset.stageKey), 'active');
                return;
            }

            setStatusPill(section, 'locked');
            setTrackerState(trackerSteps.get(section.dataset.stageKey), 'locked');
        });
    }

    document.querySelectorAll('.seg-section input, .seg-section select, .seg-section textarea').forEach((field) => {
        field.addEventListener('input', updateSectionStates);
        field.addEventListener('change', updateSectionStates);
    });

    document.getElementById('seguimiento549Form')?.addEventListener('submit', function (event) {
        let firstProblem = null;

        sections.forEach((section, index) => {
            const previousSection = index === 0 ? null : sections[index - 1];
            const unlocked = !previousSection || isSectionComplete(previousSection);
            const mustValidate = index === 0 || isSectionStarted(section);
            const missing = updateMissingStyles(section, mustValidate || !unlocked);

            if ((mustValidate && missing.length) || (!unlocked && isSectionStarted(section))) {
                firstProblem = firstProblem || section;
            }
        });

        if (firstProblem) {
            event.preventDefault();
            updateSectionStates();
            firstProblem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const firstMissing = missingFieldNames(firstProblem)[0];
            const field = firstMissing ? fieldsByName(firstProblem, firstMissing)[0] : null;
            setTimeout(() => field?.focus({ preventScroll: true }), 320);
        }
    });

    function syncToggleCard(input) {
        const card = input.closest('.seg-toggle-card');
        if (!card) return;

        const state = card.querySelector('.seg-toggle-card__state');
        const isOn = input.checked;

        card.classList.toggle('is-on', isOn);

        if (state) {
            state.textContent = isOn ? 'Aplica' : 'No aplica';
        }
    }

    document.querySelectorAll('.seg-toggle__input').forEach((input) => {
        syncToggleCard(input);
        input.addEventListener('change', function () {
            syncToggleCard(input);
        });
    });

    updateSectionStates();
});
</script>
@stop
