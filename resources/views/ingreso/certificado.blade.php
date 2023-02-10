
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop
@section('content')
@include('ingreso.mensajes')

<br><br>
<p style="position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);">
    
    <img src="img/logo.jpg" alt="" style=" float: left; width: 100px" >

    <div class="alert alert-info" role="alert">
        <h1 style="text-align: center;">Espacio para descargar certificado de ingreso de datos</h1>
      </div>


    <P style="text-align: center;">
      
    </P>
    <a style="position: absolute;
    top: 35%;
    left: 50%;
    transform: translate(-50%, -50%);" class="btn btn-primary" href="{{route('pdfcertificado')}}" class="btn  btn-success"> 
       <h5>CLICK AQUI</h5> </a>
    @stop
       
</p> 


    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
     
        {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/select2.min.css') }}"> 
        recuerda que  esto esta en la vista vendor/adminlte/dist/js/select2.min.js 
        eso es ahora porque remplasaste la svista por la de la plantilla anteriormente 
        estaba en public vendor/adminlte/dist/js/select2.min.js--}}
        
        
    @stop
    
    @section('js')

        
   
    @stop
    