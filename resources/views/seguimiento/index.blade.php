
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
<a href="{{route('Seguimiento.create')}}" title="DETALLE" 
  class="btn btn-primary btn-sm" style="border-radius: 50px;
    padding: 10px 20px;
    font-weight: bold;
    letter-spacing: 1px;
    background-color: #007bff;">
    <i class="icon-zoom-in mr-2"></i> NUEVO SEGUIMIENTO
</a>

{{-- secion del reporte general --}}

{{-- <a href="{{route('export3')}}" 
class="btn btn-success btn-sm"
 style=" margin-right: 0; position: relative; right: 0;
  border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px;
   background-color: #28a745;">
  <i class="fas fa-book mr-2"></i>
</a> --}}

  {{-- seccion del primer reporte --}}
  <a href="{{route('export')}}" class="btn btn-success btn-sm 
  rounded-pill py-2 px-3" 
  style="float: right; margin-right: 0; position: relative; right: 0; 
  border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; 
  background-color: #28a745;"><i class="fas fa-file-export mr-2"></i> EXPORTAR</a>
  {{-- <br> <strong>Total {{ $incomeedit->total() }} </strong><br> --}}
  
@stop

@section('content')

{{-- TODO RESPECTO A LA TABLA --}}
@include('seguimiento.tabla')   

 @stop
            

@section('css')

@include('seguimiento.css') 

@stop

@section('js')
@include('seguimiento.javascript') 
@stop



