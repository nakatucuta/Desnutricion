@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>NOTIFICACIONES</h1>
@stop
@section('content')


@foreach($seguimientos as $seguimiento)
    @if($seguimiento->fecha_proximo_control)
       
            
            @if(Carbon\Carbon::now()->format('Y-m-d') > Carbon\Carbon::parse($seguimiento->fecha_proximo_control))
            <div class="alert alert-danger">
                EL SEGUIMIENTO CON ID {{$seguimiento->ingresos_id}} HA SOBREPASADO SU  FECHA LIMITE {{$seguimiento->fecha_proximo_control}} FALLO POR {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} DIAS <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR</a>
            </div>
            @else
            @if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 1)
            <div class="alert alert-warning">
                EL SEGUIMIENTO CON ID {{$seguimiento->ingresos_id}} FECHA LIMITE{{$seguimiento->fecha_proximo_control}} faltan {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} dias para su proximo control <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR</a>
            </div>
            @else
            @if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 2)
            <div class="alert alert-warning">
                EL SEGUIMIENTO CON ID {{$seguimiento->ingresos_id}} FECHA LIMITE{{$seguimiento->fecha_proximo_control}} faltan {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} dias para su proximo control <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR</a>
            </div>
            @else
            @if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 0)
            <div class="alert alert-warning">
                EL SEGUIMIENTO CON ID {{$seguimiento->ingresos_id}} FECHA LIMITE{{$seguimiento->fecha_proximo_control}} faltan {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} dias para su proximo control <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR</a>
            </div>
            @endif
            @endif
            @endif
            
           
            
        @endif
        
    @endif
@endforeach
@stop
       


    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
        <link rel="stylesheet" href="/css/select2.min.css">
        {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/select2.min.css') }}"> --}}
        
        
    @stop
    
    @section('js')
       
       {{-- <script src="{{ asset('vendor/adminlte/dist/js/select2.min.js') }}"></script> --}}
   
    @stop