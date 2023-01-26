
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
formulario de creacion ingreso uuu
@stop
@section('content')

<form action="{{url('/Seguimiento')}}" method="post" enctype="multipart/form-data">
    @csrf


    
     @include('seguimiento.form', ['modo'=>'Crear']);

    </form>

    
       
    
    @stop
       
    


    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
     
        {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/select2.min.css') }}"> --}}
         {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/select2.min.css') }}"> 
        recuerda que  esto esta en la vista vendor/adminlte/dist/js/select2.min.js 
        eso es ahora porque remplasaste la svista por la de la plantilla anteriormente 
        estaba en public vendor/adminlte/dist/js/select2.min.js--}}
        
    @stop
    
    @section('js')
       
    <script type="text/javascript">
        $(document).ready(function(){
            $('#estado').on('change', function() {
                if ( this.value == '1')
                $("#input_oculto").show();
                else
                $("#input_oculto").hide();
            });
        });
    </script>
    @stop
    