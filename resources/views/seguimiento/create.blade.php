
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')
<br>
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
        
        <style>
            
    .js-example-basic-multiple {
      color: black;
    }
  </style>
    @stop
    
    @section('js')
       
    <script type="text/javascript">
           $(document).ready(function() {
                $('#est_act_menor').on('change', function() {
                    if ($(this).val() == 'RECUPERADO') {
                        $('.col-sm-4').hide();
                        $('.col-md-6').hide();
                        $('#inputsuperoculto').hide();
                    
                    } else {
                    
                    $('.col-sm-4').show();
                    $('.col-md-6').show();
                    $('#inputsuperoculto').show();
                    }
                });
                });

             $(document).ready(function(){
             $('#estado').on('change', function() {
                 if ( this.value == '1')
                 $("#input_oculto").show();
                 else
                 $("#input_oculto").hide();
             });
            
                });



                 $(document).ready(function(){
             $('#tratamiento_f75').on('change', function() {
                 if ( this.value == 'SI')
                 $("#input_oculto1").show();
                 else
                 $("#input_oculto1").hide();
             });
            
         });

         $(document).ready(function() {
    $('.js-example-basic-multiple').select2();
});
    </script>
    @stop
    