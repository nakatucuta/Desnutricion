
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')
<br>
<form action="{{url('/new412_seguimiento')}}" method="post" enctype="multipart/form-data">
    @csrf


    
     @include('seguimiento_412.form', ['modo'=>'Crear']);

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


<style>

    .select2-results__option {
      font-size: 14px;
      color: #333;
    }
    
    // Ajustar el estilo del contenedor del campo de selección
    .select2-container {
      width: 100%;
    }
    
    // Ajustar el estilo de la etiqueta "SELECCIONAR" que muestra el placeholder
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: #007bff;
      color: #fff;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
      color: #e20d0d !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: #fff !important; /* Cambiar el color de fondo del elemento seleccionado */
      color: #ec0b0b !important; /* Cambiar el color del texto del elemento seleccionado */
    }
    
    /* Cambiar el color del texto del icono "x" (para eliminar elementos seleccionados) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
      color: #fff; /* Cambiar el color del texto del icono "x" */
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
    