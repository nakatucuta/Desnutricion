
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

  {{-- MENSAJES --}}
@include('seguimiento_412.mensajes')

  {{-- MODAL NOTIFICACIONES --}}
@include('seguimiento_412.modal_notificaciones')

<div>
    <h1 style="font-family: 'Helvetica Neue', sans-serif; 
    font-weight: 700;
    font-size: 2rem;">Seguimientos 412</h1>
</div>
  <br>
<a href="{{route('new412_seguimiento.create')}}" title="DETALLE" 
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
  <a href="{{route('export6')}}" class="btn btn-success btn-sm 
  rounded-pill py-2 px-3" 
  style="float: right; margin-right: 0; position: relative; right: 0; 
  border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; 
  background-color: #28a745;"><i class="fas fa-file-export mr-2"></i> EXPORTAR</a>
  <br> <strong>Total {{ $incomeedit->total() }} </strong><br>
  
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



