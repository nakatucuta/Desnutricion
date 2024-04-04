
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')
<br>
@stop
@section('content')

<form id="update-form" action="{{url('/sivigila')}}" method="post" enctype="multipart/form-data">
    @csrf

    @include('sivigila.form', ['modo'=>'Crear']);

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
    
    <script type="text/javascript">
                    $(document).ready(function() {
                $('#nombreips_manejo_hospita').on('change', function() {
                    if ($(this).val() == 'SI') {
                    $('.col-sm-4').show();
                    $('.col-sm-7').show();
                    } else {
                    $('.col-sm-4').hide();
                    $('.col-sm-7').hide();
                    }
                });
                });
        // $(document).ready(function(){
        //     $('#tratamiento_f75').on('change', function() {
        //         if ( this.value == 'SI')
        //         $("#input_oculto").show();
        //         else
        //         $("#input_oculto").hide();
        //     });
            
        // });

        function submitForm() {
        // Muestra el texto de "Enviando correo..." y oculta el texto del botón
        document.getElementById('button-text').style.display = 'none';
        document.getElementById('loading-icon').style.display = 'inline-block';
        document.getElementById('sending-text').style.display = 'inline-block';

        // Deshabilita el botón para evitar clics repetidos
        document.getElementById('update-btn').disabled = true;

        // Envía el formulario después de un breve retraso para permitir que se muestre el ícono de carga
        setTimeout(function() {
            document.getElementById('update-form').submit();
        }, 500); // Puedes ajustar el tiempo de retraso según tus necesidades
    }
    </script>
    @stop
    