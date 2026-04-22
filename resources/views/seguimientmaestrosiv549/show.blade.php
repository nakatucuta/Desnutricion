@extends('adminlte::page')

@section('title', 'Detalle seguimiento - Caso #'.$asignacion->id)

@php
    $nombreCompleto = trim(implode(' ', array_filter([
        $asignacion->pri_nom_ ?? null,
        $asignacion->seg_nom_ ?? null,
        $asignacion->pri_ape_ ?? null,
        $asignacion->seg_ape_ ?? null,
    ])));

    $siNo = function ($value) {
        if ($value === null || $value === '') {
            return 'N/D';
        }

        return (int) $value === 1 ? 'Si' : 'No';
    };

    $fecha = function ($value) {
        if (empty($value)) {
            return 'N/D';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $valor = function ($value) {
        return filled($value) ? $value : 'N/D';
    };

    $criterios = [
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

    $soportes = [
        'soporte_inmediato_pdf' => 'Seguimiento inmediato (48-72h)',
        'soporte_seguimiento_1_pdf' => 'Seguimiento 1',
        'soporte_seguimiento_2_pdf' => 'Seguimiento 2',
        'soporte_seguimiento_3_pdf' => 'Seguimiento 3',
        'soporte_seguimiento_4_pdf' => 'Seguimiento 4',
        'soporte_seguimiento_5_pdf' => 'Seguimiento 5',
    ];
@endphp

@section('content_header')
<div class="seg-detail-hero">
    <div>
        <span class="seg-detail-hero__eyebrow">Detalle seguimiento</span>
        <h1 class="seg-detail-hero__title">Caso #{{ $asignacion->id }} - Seguimiento #{{ $seguimiento->id }}</h1>
        <p class="seg-detail-hero__subtitle">Consulta completa del seguimiento registrado para la paciente asignada.</p>
    </div>
    <div class="seg-detail-hero__actions">
        <a href="{{ route('seguimientos.index') }}" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <a href="{{ route('asignaciones.seguimientmaestrosiv549.edit', [$asignacion, $seguimiento]) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        @if((int) (auth()->user()->usertype ?? 0) === 1)
            <form action="{{ route('asignaciones.seguimientmaestrosiv549.destroy', [$asignacion, $seguimiento]) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar seguimiento?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash-alt mr-1"></i> Eliminar
                </button>
            </form>
        @endif
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card seg-detail-card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3 mb-md-0">
                    <div class="seg-detail-patient">
                        <span class="seg-detail-label">Paciente</span>
                        <h4 class="mb-1">{{ $nombreCompleto ?: 'Sin nombre registrado' }}</h4>
                        <div class="text-muted">{{ $valor($asignacion->tip_ide_) }} {{ $valor($asignacion->num_ide_) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="seg-detail-summary">
                        <div><strong>Evento:</strong> {{ $valor($asignacion->nom_eve) }}</div>
                        <div><strong>Prestador:</strong> {{ $valor(optional($asignacion->user)->name) }}</div>
                        <div><strong>Notificacion:</strong> {{ $valor($asignacion->fec_not) }}</div>
                        <div><strong>Creado:</strong> {{ $valor(optional($seguimiento->created_at)->format('Y-m-d H:i')) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card seg-detail-card mb-4">
        <div class="card-header seg-detail-card__head">Hospitalizacion y criterios clinicos</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2"><strong>Fecha hospitalizacion:</strong> {{ $fecha($seguimiento->fecha_hospitalizacion) }}</div>
                <div class="col-md-4 mb-2"><strong>Fecha egreso:</strong> {{ $fecha($seguimiento->fecha_egreso) }}</div>
                <div class="col-md-4 mb-2"><strong>Institucion egreso:</strong> {{ $valor($seguimiento->institucion_egreso_paciente) }}</div>
                <div class="col-12 mb-3"><strong>Gestion hospitalizacion:</strong> {{ $valor($seguimiento->gestion_hospitalizacion) }}</div>
                <div class="col-md-3 mb-2"><strong>Total criterios:</strong> {{ $valor($seguimiento->ttl_criter) }}</div>
                <div class="col-md-3 mb-2"><strong>Diagnostico CIE10:</strong> {{ $valor($seguimiento->diagnostico_cie10) }}</div>
                <div class="col-md-6 mb-2"><strong>Causa agrupada:</strong> {{ $valor($seguimiento->causa_agrupada) }}</div>
            </div>
            <div class="row mt-2">
                @foreach($criterios as $field => $label)
                    <div class="col-md-3 mb-2">
                        <span class="badge seg-detail-badge {{ (int) $seguimiento->{$field} === 1 ? 'seg-detail-badge--on' : 'seg-detail-badge--off' }}">
                            {{ $label }}: {{ $siNo($seguimiento->{$field}) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card seg-detail-card mb-4">
        <div class="card-header seg-detail-card__head">Seguimiento inmediato (48-72h)</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-2"><strong>Descripcion:</strong> {{ $valor($seguimiento->descripcion_seguimiento_inmediato) }}</div>
                <div class="col-md-2 mb-2"><strong>Control RN:</strong> {{ $fecha($seguimiento->fecha_control_rn_inmediato) }}</div>
                <div class="col-md-2 mb-2"><strong>Efectivo:</strong> {{ $siNo($seguimiento->seguimiento_efectivo_inmediato) }}</div>
            </div>
        </div>
    </div>

    <div class="card seg-detail-card mb-4">
        <div class="card-header seg-detail-card__head">Soportes PDF historia clinica</div>
        <div class="card-body">
            <div class="row">
                @foreach($soportes as $campo => $titulo)
                    <div class="col-md-6 mb-2">
                        <strong>{{ $titulo }}:</strong>
                        @if(!empty($seguimiento->{$campo}))
                            <a href="{{ asset('storage/'.$seguimiento->{$campo}) }}" target="_blank" class="btn btn-sm btn-outline-info ml-2">
                                <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                            </a>
                        @else
                            <span class="text-muted ml-1">Sin soporte</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach([1,2,3,4,5] as $idx)
        <div class="card seg-detail-card mb-4">
            <div class="card-header seg-detail-card__head">Seguimiento {{ $idx }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2"><strong>Fecha seguimiento:</strong> {{ $fecha($seguimiento->{'fecha_seguimiento_'.$idx}) }}</div>
                    @if(in_array($idx, [1,3,4,5], true))
                        <div class="col-md-3 mb-2"><strong>Tipo seguimiento:</strong> {{ $valor($seguimiento->{'tipo_seguimiento_'.$idx}) }}</div>
                    @endif
                    <div class="col-md-3 mb-2"><strong>Sigue embarazo:</strong> {{ $siNo($seguimiento->{'paciente_sigue_embarazo_'.$idx}) }}</div>
                    <div class="col-md-3 mb-2"><strong>Fecha control:</strong> {{ $fecha($seguimiento->{'fecha_control_'.$idx}) }}</div>
                    <div class="col-md-3 mb-2"><strong>Consulta RN:</strong> {{ $fecha($seguimiento->{'fecha_consulta_rn_'.$idx}) }}</div>
                    <div class="col-md-3 mb-2"><strong>Entrega meds/labs:</strong> {{ $valor($seguimiento->{'entrega_medicamentos_labs_'.$idx}) }}</div>
                    @if(in_array($idx, [2,3,4,5], true))
                        <div class="col-md-3 mb-2"><strong>Efectivo:</strong> {{ $siNo($seguimiento->{'seguimiento_efectivo_'.$idx}) }}</div>
                    @endif
                    @if($idx === 1)
                        <div class="col-md-6 mb-2"><strong>Metodo anticonceptivo:</strong> {{ $valor($seguimiento->metodo_anticonceptivo) }}</div>
                        <div class="col-12 mb-2"><strong>Gestion posegreso:</strong> {{ $valor($seguimiento->gestion_posegreso_1) }}</div>
                    @endif
                    @if($idx === 2)
                        <div class="col-12 mb-2"><strong>Gestion primera semana:</strong> {{ $valor($seguimiento->gestion_primera_semana) }}</div>
                    @endif
                    @if($idx === 3)
                        <div class="col-12 mb-2"><strong>Gestion segunda semana:</strong> {{ $valor($seguimiento->gestion_segunda_semana) }}</div>
                    @endif
                    @if($idx === 4)
                        <div class="col-12 mb-2"><strong>Gestion tercera semana:</strong> {{ $valor($seguimiento->gestion_tercera_semana) }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div class="card seg-detail-card mb-4">
        <div class="card-header seg-detail-card__head">Controles posteriores</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2"><strong>Consulta lactancia:</strong> {{ $fecha($seguimiento->fecha_consulta_lactancia) }}</div>
                <div class="col-md-3 mb-2"><strong>Control metodo:</strong> {{ $fecha($seguimiento->fecha_control_metodo) }}</div>
                <div class="col-md-3 mb-2"><strong>Consulta 6 meses:</strong> {{ $fecha($seguimiento->fecha_consulta_6_meses) }}</div>
                <div class="col-md-3 mb-2"><strong>Consulta 1 ano:</strong> {{ $fecha($seguimiento->fecha_consulta_1_ano) }}</div>
                <div class="col-12"><strong>Gestion despues del mes:</strong> {{ $valor($seguimiento->gestion_despues_mes) }}</div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .seg-detail-hero{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:1.2rem 1.4rem;
        border-radius:20px;
        color:#fff;
        background:linear-gradient(135deg, #0f5f86 0%, #0e8da5 48%, #228861 100%);
        box-shadow:0 16px 34px rgba(13, 71, 97, .16);
    }
    .seg-detail-hero__eyebrow{
        display:inline-block;
        font-size:.76rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.08em;
        opacity:.85;
    }
    .seg-detail-hero__title{
        margin:.25rem 0 .3rem;
        font-size:1.65rem;
        font-weight:800;
    }
    .seg-detail-hero__subtitle{
        margin:0;
        opacity:.9;
    }
    .seg-detail-hero__actions{
        display:flex;
        gap:.5rem;
        flex-wrap:wrap;
    }
    .seg-detail-card{
        border:none;
        border-radius:18px;
        box-shadow:0 12px 24px rgba(17, 74, 87, .08);
        overflow:hidden;
    }
    .seg-detail-card__head{
        font-weight:800;
        color:#1b4d59;
        background:linear-gradient(180deg, #f5fafc, #ffffff);
        border-bottom:1px solid #e5eff2;
    }
    .seg-detail-patient{
        padding:1rem;
        border-radius:14px;
        border:1px solid #deebef;
        background:#f8fcfd;
    }
    .seg-detail-summary{
        padding:1rem;
        border-radius:14px;
        border:1px solid #deebef;
        background:#ffffff;
    }
    .seg-detail-label{
        display:inline-block;
        margin-bottom:.25rem;
        color:#5d7c85;
        font-weight:700;
        text-transform:uppercase;
        font-size:.76rem;
        letter-spacing:.06em;
    }
    .seg-detail-badge{
        width:100%;
        display:inline-block;
        text-align:left;
        padding:.55rem .7rem;
        border-radius:10px;
        font-weight:700;
        font-size:.78rem;
        white-space:normal;
    }
    .seg-detail-badge--on{
        color:#0f7b68;
        background:#e5f8f0;
    }
    .seg-detail-badge--off{
        color:#6f7d82;
        background:#edf2f4;
    }
    @media (max-width: 991px){
        .seg-detail-hero{
            flex-direction:column;
            align-items:flex-start;
        }
    }
</style>
@stop
