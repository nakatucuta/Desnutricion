@extends('adminlte::page')

@section('title','Editar Seguimiento')
@section('content_header')
  <h1 class="text-warning">Editar Seguimiento</h1>
@stop

@section('content')
  @include('ges_tipo1.seguimientos._form', [
    'mode'    => 'edit',
    'action'  => route('ges_tipo1.seguimientos.update', [$ges->id, $seg->id]),
    'method'  => 'PUT',
    'ges'     => $ges,
    'seg'     => $seg,
    'ultimo'  => null,
  ])
@stop
