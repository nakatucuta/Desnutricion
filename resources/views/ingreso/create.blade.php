
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop
@section('content')
<br>
<form action="{{url('/Ingreso')}}" method="post" enctype="multipart/form-data">
    
    @csrf


    
    @include('ingreso.form', ['modo'=>'Crear']);


    </form>

    
       
    
    @stop
       
    


    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
     
        {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/select2.min.css') }}"> 
        recuerda que  esto esta en la vista vendor/adminlte/dist/js/select2.min.js 
        eso es ahora porque remplasaste la svista por la de la plantilla anteriormente 
        estaba en public vendor/adminlte/dist/js/select2.min.js--}}
        
        
    @stop
    
    @section('js')

        
   
    @stop
    