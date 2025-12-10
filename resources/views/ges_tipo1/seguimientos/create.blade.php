@extends('adminlte::page')

@section('title','Nuevo Seguimiento')
@section('content_header')
  <h1 class="text-info">Nuevo Seguimiento</h1>
@stop

@section('content')
  @include('ges_tipo1.seguimientos._form', [
    'mode'    => 'create',
    'action'  => route('ges_tipo1.seguimientos.store', $ges->id),
    'method'  => 'POST',
    'ges'     => $ges,
    'seg'     => null,
    'ultimo'  => $ultimo ?? null,
  ])
@stop
