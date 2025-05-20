@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

{{-- MENSAJES --}}
@include('seguimiento_412.mensajes')

{{-- MODAL NOTIFICACIONES --}}
@include('seguimiento_412.modal_notificaciones')

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
      Seguimientos Evento 412
    </h1>
  </div>
<br>
<br>

<a href="{{ route('new412_seguimiento.create') }}" title="Nuevo seguimiento"
class="btn btn-nuevo-seguimiento">
<i class="fas fa-plus-circle"></i> Nuevo Seguimiento
</a>

{{-- <a href="{{ route('export6') }}" class="btn btn-success btn-sm rounded-pill py-2 px-3" 
   style="float: right; margin-right: 0; position: relative; right: 0; 
   border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; 
   background-color: #28a745;">
   <i class="fas fa-file-export mr-2"></i> EXPORTAR
</a> --}}


<div class="dropdown export-dropdown" style="float: right;">
    <button class="btn btn-exportar dropdown-toggle shadow-sm"
            type="button" id="dropdownExport"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fas fa-file-export mr-2"></i> Exportar Reporte
    </button>
    <div class="dropdown-menu dropdown-menu-right"
         aria-labelledby="dropdownExport">
      <a class="dropdown-item" href="{{ route('export6') }}">
        📄 Reporte General
      </a>
      {{-- <a class="dropdown-item" href="{{ route('export') }}">
        📊 Reporte por Casos
      </a> --}}
    </div>
  </div>
  

{{-- Ya no usamos incomeedit->total() --}}
{{-- <br> <strong>Total {{ $incomeedit->total() }} </strong><br> --}}
<br>

@stop

@section('content')
    {{-- TODO RESPECTO A LA TABLA --}}
    @include('seguimiento_412.tabla')  
@stop

@section('css')
    @include('seguimiento_412.css') 
@stop

@section('js')
    @include('seguimiento_412.javascript') 
@stop
