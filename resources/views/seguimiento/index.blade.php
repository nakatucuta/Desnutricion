
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

  {{-- MENSAJES --}}
@include('seguimiento.mensajes')

  {{-- MODAL NOTIFICACIONES --}}
@include('seguimiento.modal_notificaciones')
<iframe title="Autorizaciones" width="1140" height="541.25" src="https://app.powerbi.com/reportEmbed?reportId=129b3b0a-5f22-44b7-99a7-62fd6ed304dc&autoAuth=true&ctid=a2654f78-8087-41a0-a705-a3dfcce05abd" frameborder="0" allowFullScreen="true"></iframe>
<div>
    <h1 style="font-family: 'Helvetica Neue', sans-serif; 
    font-weight: 700;
    font-size: 2rem;">Seguimientos</h1>
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

<a href="{{route('export3')}}" 
class="btn btn-success btn-sm"
 style=" margin-right: 0; position: relative; right: 0;
  border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px;
   background-color: #28a745;">
  <i class="fas fa-book mr-2"></i>
</a>

  {{-- seccion del primer reporte --}}
  <a href="{{route('export')}}" class="btn btn-success btn-sm 
  rounded-pill py-2 px-3" 
  style="float: right; margin-right: 0; position: relative; right: 0; 
  border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; 
  background-color: #28a745;"><i class="fas fa-file-export mr-2"></i> EXPORTAR</a>
  <br> <strong>Total {{ $incomeedit->total() }} </strong><br>
  
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



