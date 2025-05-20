
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

  {{-- MENSAJES --}}
@include('seguimiento.mensajes')

@include('seguimiento.modal_notificaciones', ['conteo' => $conteo, 'otro' => $otro])

<div style="text-align: left; margin: 20px 0;">
  <h1 style="
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #2C3E50;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.15);
    padding: 8px 16px;
    background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
    border-radius: 6px;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    border-left: 5px solid #2980b9;
    display: inline-block;
  ">
    Seguimientos Evento 113
  </h1>
</div>


  <br>
  <a href="{{ route('Seguimiento.create') }}" title="Nuevo seguimiento"
  class="btn btn-nuevo-seguimiento">
  <i class="fas fa-plus-circle"></i> Nuevo Seguimiento
</a>


{{-- Selector elegante para exportar --}}
<div class="dropdown export-dropdown" style="float: right;">
  <button class="btn btn-exportar dropdown-toggle shadow-sm"
          type="button" id="dropdownExport"
          data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-file-export mr-2"></i> Exportar Reporte
  </button>
  <div class="dropdown-menu dropdown-menu-right"
       aria-labelledby="dropdownExport">
    <a class="dropdown-item" href="{{ route('export3') }}">
      📄 Reporte General
    </a>
    <a class="dropdown-item" href="{{ route('export') }}">
      📊 Reporte por Casos
    </a>
  </div>
</div>


  
@stop

@section('content')

{{-- TODO RESPECTO A LA TABLA --}}
@include('seguimiento.tabla')   

 @stop
            

@section('css')


@include('seguimiento.css') 


<style>
 
</style>

@stop

@section('js')
<script>
  document.getElementById('exportSelector').addEventListener('change', function () {
    const url = this.value;
    if (url) window.location.href = url;
  });
</script>

@include('seguimiento.javascript') 
@stop



