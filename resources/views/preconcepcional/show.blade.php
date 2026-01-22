{{-- resources/views/preconcepcional/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalle Preconcepcional')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="text-primary mb-0">
            <i class="fas fa-user-check mr-2"></i>Detalle Preconcepcional
        </h1>
        <small class="text-muted">
            Registro #{{ $preconcepcional->id }} @if($preconcepcional->no) | No: {{ $preconcepcional->no }} @endif
        </small>
    </div>

    <a href="{{ route('preconcepcional.index') }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
@php
    $fullName = trim(($preconcepcional->nombre_1 ?? '').' '.($preconcepcional->nombre_2 ?? '').' '.($preconcepcional->apellido_1 ?? '').' '.($preconcepcional->apellido_2 ?? ''));
    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : '—';
    $v = fn($x) => (isset($x) && $x !== '' && $x !== null) ? $x : '—';

    // badges “bonitos” para SI/NO
    $ynBadge = function($val){
        $val = is_string($val) ? trim(mb_strtoupper($val)) : $val;
        if(in_array($val, ['SI','SÍ','YES',1,true,'1'])) return '<span class="badge badge-success">SI</span>';
        if(in_array($val, ['NO',0,false,'0'])) return '<span class="badge badge-secondary">NO</span>';
        return '<span class="text-muted">—</span>';
    };
@endphp

<div class="row">
    {{-- LEFT COLUMN --}}
    <div class="col-lg-4">

        {{-- PERFIL --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-id-card mr-1"></i> Identificación
                </span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="mr-3">
                        <span class="badge badge-primary p-2" style="font-size:14px;">
                            {{ $v($preconcepcional->tipo_documento) }}
                        </span>
                    </div>
                    <div>
                        <div class="h5 mb-0">{{ $v($preconcepcional->numero_identificacion) }}</div>
                        <small class="text-muted">Documento / Identificación</small>
                    </div>
                </div>

                <p class="mb-1"><b>Nombre completo:</b> {{ $fullName ?: '—' }}</p>
                <p class="mb-1"><b>Sexo:</b> {{ $v($preconcepcional->sexo) }}</p>
                <p class="mb-1"><b>Fecha nacimiento:</b> {{ $fmtDate($preconcepcional->fecha_nacimiento) }}</p>
                <p class="mb-1"><b>Edad:</b> {{ $v($preconcepcional->edad) }}</p>

                <hr>

                <p class="mb-1"><b>Régimen afiliación:</b> {{ $v($preconcepcional->regimen_afiliacion) }}</p>
                <p class="mb-1"><b>Pertenencia étnica:</b> {{ $v($preconcepcional->pertenencia_etnica) }}</p>
                <p class="mb-1"><b>Grupo poblacional:</b> {{ $v($preconcepcional->grupo_poblacional) }}</p>
                <p class="mb-0"><b>Nivel educativo:</b> {{ $v($preconcepcional->nivel_educativo) }}</p>
            </div>
        </div>

        {{-- CONTACTO / RESIDENCIA --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-map-marker-alt mr-1"></i> Residencia y contacto
                </span>
            </div>
            <div class="card-body">
                <p class="mb-1"><b>Departamento:</b> {{ $v($preconcepcional->departamento_residencia) }}</p>
                <p class="mb-1"><b>Municipio:</b> {{ $v($preconcepcional->municipio_residencia) }}</p>
                <p class="mb-1"><b>Zona:</b> {{ $v($preconcepcional->zona) }}</p>
                <p class="mb-1"><b>Etnia:</b> {{ $v($preconcepcional->etnia) }}</p>
                <p class="mb-1"><b>Asentamiento:</b> {{ $v($preconcepcional->asentamiento) }}</p>
                <p class="mb-1"><b>Dirección:</b> {{ $v($preconcepcional->direccion) }}</p>
                <p class="mb-0"><b>Teléfono:</b> {{ $v($preconcepcional->telefono) }}</p>
            </div>
        </div>

        {{-- ADMIN --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-clock mr-1"></i> Auditoría
                </span>
            </div>
            <div class="card-body">
                <p class="mb-1"><b>Creado:</b> {{ $preconcepcional->created_at ? $preconcepcional->created_at->format('Y-m-d H:i') : '—' }}</p>
                <p class="mb-0"><b>Actualizado:</b> {{ $preconcepcional->updated_at ? $preconcepcional->updated_at->format('Y-m-d H:i') : '—' }}</p>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-8">

        {{-- ESTADO SOCIAL / IPS --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-hospital mr-1"></i> Información general / IPS
                </span>
                <span class="badge badge-light border">
                    <i class="fas fa-clinic-medical text-primary mr-1"></i> {{ $v($preconcepcional->nombre_ips_primaria) }}
                </span>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><p class="mb-1"><b>Discapacidad:</b> {!! $ynBadge($preconcepcional->discapacidad) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Mujer cabeza hogar:</b> {!! $ynBadge($preconcepcional->mujer_cabeza_hogar) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Ocupación:</b> {{ $v($preconcepcional->ocupacion) }}</p></div>

                    <div class="col-md-4"><p class="mb-1"><b>Estado civil:</b> {{ $v($preconcepcional->estado_civil) }}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Control tradicional:</b> {!! $ynBadge($preconcepcional->control_tradicional) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Inasistente:</b> {!! $ynBadge($preconcepcional->inasistente) !!}</p></div>

                    <div class="col-md-4"><p class="mb-0"><b>Gestante renuente:</b> {!! $ynBadge($preconcepcional->gestante_renuente) !!}</p></div>
                </div>
            </div>
        </div>

        {{-- ANTECEDENTES PERSONALES --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-notes-medical mr-1"></i> Antecedentes personales
                </span>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><p class="mb-1"><b>Hipertensión:</b> {!! $ynBadge($preconcepcional->hipertension_personal) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Diabetes:</b> {!! $ynBadge($preconcepcional->diabetes_mellitus) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Enfermedad renal:</b> {!! $ynBadge($preconcepcional->enfermedad_renal) !!}</p></div>

                    <div class="col-md-4"><p class="mb-1"><b>Cardiopatías:</b> {!! $ynBadge($preconcepcional->cardiopatias) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Epilepsia:</b> {!! $ynBadge($preconcepcional->epilepsia) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Autoinmunes:</b> {!! $ynBadge($preconcepcional->enfermedades_autoinmunes) !!}</p></div>

                    <div class="col-md-4"><p class="mb-1"><b>Trastornos mentales:</b> {!! $ynBadge($preconcepcional->trastornos_mentales) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Cáncer:</b> {!! $ynBadge($preconcepcional->cancer) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Infecciosas crónicas:</b> {!! $ynBadge($preconcepcional->enfermedades_infecciosas_cronicas) !!}</p></div>

                    <div class="col-md-6"><p class="mb-1"><b>Uso permanente medicamentos:</b> {!! $ynBadge($preconcepcional->uso_permanente_medicamentos) !!}</p></div>
                    <div class="col-md-6"><p class="mb-1"><b>Alergias:</b> {{ $v($preconcepcional->alergias) }}</p></div>
                </div>
            </div>
        </div>

        {{-- GINECO-OBSTÉTRICOS + FAMILIARES --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <span class="font-weight-bold text-secondary">
                            <i class="fas fa-venus mr-1"></i> Gineco-obstétricos
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><b>Edad menarquia:</b> {{ $v($preconcepcional->edad_menarquia) }}</p>
                        <p class="mb-1"><b>FUM:</b> {{ $fmtDate($preconcepcional->fecha_ultimo_periodo_mestrual) }}</p>

                        <hr class="my-2">

                        <p class="mb-1"><b>Gestaciones previas:</b> {{ $v($preconcepcional->numero_gestaciones_previas) }}</p>
                        <p class="mb-1"><b>Partos vaginales:</b> {{ $v($preconcepcional->partos_vaginales) }}</p>
                        <p class="mb-1"><b>Cesáreas:</b> {{ $v($preconcepcional->cesareas) }}</p>
                        <p class="mb-1"><b>Abortos:</b> {{ $v($preconcepcional->abortos) }}</p>
                        <p class="mb-0"><b>Complicaciones previas:</b> {{ $v($preconcepcional->complicaciones_obstetricas_previas) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <span class="font-weight-bold text-secondary">
                            <i class="fas fa-users mr-1"></i> Antecedentes familiares
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><b>Hipertensión:</b> {!! $ynBadge($preconcepcional->hipertension_familiar) !!}</p>
                        <p class="mb-1"><b>Diabetes:</b> {!! $ynBadge($preconcepcional->diabetes_familiar) !!}</p>
                        <p class="mb-1"><b>Malformaciones congénitas:</b> {{ $v($preconcepcional->malformaciones_congenitas) }}</p>
                        <p class="mb-1"><b>Enfermedades genéticas:</b> {{ $v($preconcepcional->enfermedades_geneticas) }}</p>
                        <p class="mb-1"><b>Mentales en familia:</b> {{ $v($preconcepcional->enfermedades_mentales_familia) }}</p>
                        <p class="mb-0"><b>Muerte materna:</b> {{ $v($preconcepcional->muerte_materna_familia) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SALUD SEXUAL --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-heart mr-1"></i> Salud sexual y reproductiva
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><p class="mb-1"><b>Inicio vida sexual:</b> {{ $v($preconcepcional->inicio_vida_sexual) }}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b># Parejas sexuales:</b> {{ $v($preconcepcional->numero_parejas_sexuales) }}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Deseo reproductivo:</b> {{ $v($preconcepcional->deseo_reproductivo) }}</p></div>

                    <div class="col-md-6"><p class="mb-1"><b>Métodos anticonceptivos:</b> {{ $v($preconcepcional->uso_actual_metodos_anticonceptivos) }}</p></div>
                    <div class="col-md-6"><p class="mb-0"><b>Antecedentes ITS:</b> {{ $v($preconcepcional->antecedentes_its) }}</p></div>
                </div>
            </div>
        </div>

        {{-- ESTILO DE VIDA --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-running mr-1"></i> Estilo de vida / factores de riesgo
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><p class="mb-1"><b>Tabaco:</b> {!! $ynBadge($preconcepcional->consumo_tabaco) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Alcohol:</b> {!! $ynBadge($preconcepcional->consumo_alcohol) !!}</p></div>
                    <div class="col-md-4"><p class="mb-1"><b>Sustancias:</b> {!! $ynBadge($preconcepcional->consumo_sustancias_psicoactivas) !!}</p></div>

                    <div class="col-md-6"><p class="mb-1"><b>Actividad física:</b> {{ $v($preconcepcional->actividad_fisica) }}</p></div>
                    <div class="col-md-6"><p class="mb-1"><b>Alimentación saludable:</b> {{ $v($preconcepcional->alimentacion_saludable) }}</p></div>

                    <div class="col-12"><p class="mb-0"><b>Violencias:</b> {{ $v($preconcepcional->violencias) }}</p></div>
                </div>
            </div>
        </div>

        {{-- NUTRICIÓN --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-secondary">
                    <i class="fas fa-apple-alt mr-1"></i> Evaluación nutricional
                </span>
                <span class="badge badge-info p-2" style="font-size:13px;">
                    IMC: {{ $v($preconcepcional->imc) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><p class="mb-1"><b>Peso:</b> {{ $v($preconcepcional->peso) }}</p></div>
                    <div class="col-md-3"><p class="mb-1"><b>Talla:</b> {{ $v($preconcepcional->talla) }}</p></div>
                    <div class="col-md-3"><p class="mb-1"><b>IMC:</b> {{ $v($preconcepcional->imc) }}</p></div>
                    <div class="col-md-3"><p class="mb-1"><b>Riesgo nutri:</b> {{ $v($preconcepcional->riesgo_nutricional) }}</p></div>

                    <div class="col-12"><p class="mb-0"><b>Suplementación ácido fólico:</b> {!! $ynBadge($preconcepcional->suplementacion_acido_folico) !!}</p></div>
                </div>
            </div>
        </div>

        {{-- INMUNIZACIÓN + TAMIZAJES --}}
        <div class="row">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <span class="font-weight-bold text-secondary">
                            <i class="fas fa-syringe mr-1"></i> Inmunización
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><b>Tétanos:</b> {{ $v($preconcepcional->tetanos) }}</p>
                        <p class="mb-1"><b>Influenza:</b> {{ $v($preconcepcional->influenza) }}</p>
                        <p class="mb-0"><b>COVID-19:</b> {{ $v($preconcepcional->covid_19) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <span class="font-weight-bold text-secondary">
                            <i class="fas fa-vials mr-1"></i> Tamizajes
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <b>Sífilis:</b> {{ $fmtDate($preconcepcional->fecha_tamizaje_sifilis) }}
                            <span class="text-muted">/</span> {{ $v($preconcepcional->resultado_sifilis) }}
                        </p>
                        <p class="mb-1">
                            <b>VIH:</b> {{ $fmtDate($preconcepcional->fecha_tamizaje_vih) }}
                            <span class="text-muted">/</span> {{ $v($preconcepcional->resultado_vih) }}
                        </p>
                        <p class="mb-0">
                            <b>Hepatitis B:</b> {{ $fmtDate($preconcepcional->fecha_tamizaje_hepatitis_b) }}
                            <span class="text-muted">/</span> {{ $v($preconcepcional->resultado_hepatitis_b) }}
                        </p>

                        <hr class="my-2">

                        <p class="mb-1"><b>Citología:</b> {{ $v($preconcepcional->citologia) }}</p>
                        <p class="mb-0"><b>Tamizaje salud mental:</b> {{ $v($preconcepcional->tamizaje_salud_mental) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIESGO + PLAN --}}
        <div class="card shadow-sm border-left border-primary">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-primary">
                    <i class="fas fa-shield-alt mr-1"></i> Riesgo y plan de intervención
                </span>
                <span class="badge badge-primary p-2" style="font-size:13px;">
                    Riesgo: {{ $v($preconcepcional->riesgo_preconcepcional) }}
                </span>
            </div>
            <div class="card-body">
                <p class="mb-2"><b>Consejería preconcepcional:</b><br><span class="text-muted">{{ $v($preconcepcional->consejeria_preconcepcional) }}</span></p>
                <p class="mb-2"><b>Educación en planificación familiar:</b><br><span class="text-muted">{{ $v($preconcepcional->educacion_planificacion_familiar) }}</span></p>
                <p class="mb-2"><b>Recomendaciones nutricionales:</b><br><span class="text-muted">{{ $v($preconcepcional->recomendaciones_nutricionales) }}</span></p>
                <p class="mb-0"><b>Órdenes médicas:</b><br><span class="text-muted">{{ $v($preconcepcional->ordenes_medicas) }}</span></p>
            </div>
        </div>

    </div>
</div>
@stop
