@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')
@include('seguimiento.mensajes')

<div class="seg113-hero">
    <div class="seg113-hero__brand">
        <div class="seg113-hero__logo-wrap">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="seg113-hero__logo">
        </div>
        <div>
            <span class="seg113-eyebrow">Panel operativo</span>
            <h1 class="seg113-hero__title">Seguimientos evento 113</h1>
            <p class="seg113-hero__subtitle">
                Consulta casos abiertos, proximos controles y cierres con una vista tecnologica y alineada al diseno actual.
            </p>
        </div>
    </div>
    <div class="seg113-hero__chips">
        <span class="seg113-chip">Monitoreo activo</span>
        <span class="seg113-chip seg113-chip--ok">Ruta 113</span>
        @include('seguimiento.modal_notificaciones', [
            'conteo' => $novedadesPendientesCount ?? 0,
            'notificacionesPendientes' => $notificacionesPendientes ?? collect(),
        ])
    </div>
</div>

<div class="seg113-toolbar">
    <a href="{{ route('Seguimiento.create') }}" title="Nuevo seguimiento" class="btn seg113-btn seg113-btn--primary">
        <i class="fas fa-plus-circle mr-1"></i> Nuevo seguimiento
    </a>

    <div class="dropdown export-dropdown">
        <button class="btn seg113-btn seg113-btn--success dropdown-toggle shadow-sm"
                type="button" id="dropdownExport"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-file-export mr-1"></i> Exportar reporte
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownExport">
            <a class="dropdown-item" href="{{ route('Seguimiento.report-designer') }}">
                <i class="fas fa-drafting-compass mr-2 text-primary"></i> Disenador de reporte
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('export3') }}">
                <i class="far fa-file-alt mr-2 text-info"></i> Reporte general
            </a>
            <a class="dropdown-item" href="{{ route('export') }}">
                <i class="far fa-chart-bar mr-2 text-success"></i> Reporte por casos
            </a>
        </div>
    </div>
</div>
@stop

@section('content')
@include('seguimiento.tabla')
@stop

@section('css')
@include('seguimiento.css')
@stop

@section('js')
@include('seguimiento.javascript')
@stop
