@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')
@include('seguimiento_412.mensajes')

<div class="seg412-hero">
    <div class="seg412-hero__brand">
        <div class="seg412-hero__logo-wrap">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="seg412-hero__logo">
        </div>
        <div>
            <span class="seg412-eyebrow">Panel operativo</span>
            <h1 class="seg412-hero__title">Seguimientos evento 412</h1>
            <p class="seg412-hero__subtitle">
                Gestiona casos abiertos, proximos controles y cierres con una vista mas clara, moderna y alineada al diseno actual.
            </p>
        </div>
    </div>
    <div class="seg412-hero__chips">
        <span class="seg412-chip">Monitoreo activo</span>
        <span class="seg412-chip seg412-chip--ok">Ruta 412</span>
        @include('seguimiento_412.modal_notificaciones', [
            'conteo' => $novedadesPendientesCount ?? 0,
            'notificacionesPendientes' => $notificacionesPendientes ?? collect(),
        ])
    </div>
</div>

<div class="seg412-toolbar">
    <a href="{{ route('new412_seguimiento.create') }}" title="Nuevo seguimiento" class="btn seg412-btn seg412-btn--primary">
        <i class="fas fa-plus-circle mr-1"></i> Nuevo seguimiento
    </a>

    <div class="dropdown export-dropdown">
        <button class="btn seg412-btn seg412-btn--success dropdown-toggle shadow-sm"
                type="button" id="dropdownExport"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-file-export mr-1"></i> Exportar reporte
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownExport">
            <a class="dropdown-item" href="{{ route('new412_seguimiento.report-designer') }}">
                <i class="fas fa-drafting-compass mr-2 text-primary"></i> Disenador de reporte
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('export6') }}">
                <i class="far fa-file-alt mr-2 text-info"></i> Reporte general
            </a>
        </div>
    </div>
</div>
@stop

@section('content')
@include('seguimiento_412.tabla')
@stop

@section('css')
@include('seguimiento_412.css')
@stop

@section('js')
@include('seguimiento_412.javascript')
@stop
