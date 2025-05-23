
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')


{{-- boton para abrir la modal --}}
{{-- Botón que abre la modal --}}
<br>
<button type="button" class="btn btn-primary btn-sm" style="border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #007bff;" data-toggle="modal" data-target="#exampleModal">
  <i class="icon-zoom-in mr-2"></i> CLICK AQUI PARA VER TODOS LOS DATOS
</button>
<br>

{{-- Modal con tabla --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document"> {{-- <- Cambiado a modal-xl --}}
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="exampleModalLabel">NOTIFICACIONES</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0" style="overflow-x: auto; max-height: 80vh;">
        <table class="table table-hover table-striped table-bordered mb-0" style="border: 1px solid #000;" id="sivigila">
          <thead class="table-info text-center">
            <tr>
              <th style="font-size: smaller;">Id</th>
              <th style="font-size: smaller;">nombre coperante</th>
              <th style="font-size: smaller;">nombre profesional</th>
              <th style="font-size: smaller;">numero profesional</th>
              <th style="font-size: smaller;">fecha captacion</th>
              <th style="font-size: smaller;">nombre cuidador</th>
              <th style="font-size: smaller;">identificación cuidador</th>
              <th style="font-size: smaller;">teléfono cuidador</th>
              <th style="font-size: smaller;">EAPB cuidador</th>
              <th style="font-size: smaller;">Autoridad ancestral</th>
              <th style="font-size: smaller;">Contacto autoridad</th>
              <th style="font-size: smaller;">Tipo ID</th>
              <th style="font-size: smaller;">Identificación</th>
              <th style="font-size: smaller;">Nombre</th>
              <th style="font-size: smaller;">Sexo</th>
              <th style="font-size: smaller;">Fecha nacimiento</th>
              <th style="font-size: smaller;">Edad (meses)</th>
              <th style="font-size: smaller;">Régimen</th>
              <th style="font-size: smaller;">EAPB menor</th>
              <th style="font-size: smaller;">Peso</th>
              <th style="font-size: smaller;">Talla</th>
              <th style="font-size: smaller;">Perímetro braquial</th>
              <th style="font-size: smaller;">Signos infección</th>
              <th style="font-size: smaller;">Signos desnutrición</th>
              <th style="font-size: smaller;">Z</th>
              <th style="font-size: smaller;">Clasificación</th>
              <th style="font-size: smaller;">Municipio</th>
              <th style="font-size: smaller;">UDS</th>
              <th style="font-size: smaller;">Ranchería</th>
              <th style="font-size: smaller;">Ubicación</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sivigilas2030 as $student2)
            <tr>
              <td><small>{{ $student2->id }}</small></td>
              <td><small>{{ $student2->nombre_coperante }}</small></td>
              <td><small>{{ $student2->nombre_profesional }}</small></td>
              <td><small>{{ $student2->numero_profesional }}</small></td>
              <td><small>{{ $student2->fecha_captacion }}</small></td>
              <td><small>{{ $student2->nombre_cuidador }}</small></td>
              <td><small>{{ $student2->identioficacion_cuidador }}</small></td>
              <td><small>{{ $student2->telefono_cuidador }}</small></td>
              <td><small>{{ $student2->nombre_eapb_cuidador }}</small></td>
              <td><small>{{ $student2->nombre_autoridad_trad_ansestral }}</small></td>
              <td><small>{{ $student2->datos_contacto_autoridad }}</small></td>
              <td><small>{{ $student2->tipo_identificacion }}</small></td>
              <td><small>{{ $student2->numero_identificacion }}</small></td>
              <td><small>{{ $student2->primer_nombre }} {{ $student2->segundo_nombre }} {{ $student2->primer_apellido }} {{ $student2->segundo_apellido }}</small></td>
              <td><small>{{ $student2->sexo }}</small></td>
              <td><small>{{ $student2->fecha_nacimieto_nino }}</small></td>
              <td><small>{{ $student2->edad_meses }}</small></td>
              <td><small>{{ $student2->regimen_afiliacion }}</small></td>
              <td><small>{{ $student2->nombre_eapb_menor }}</small></td>
              <td><small>{{ $student2->peso_kg }}</small></td>
              <td><small>{{ $student2->logitud_talla_cm }}</small></td>
              <td><small>{{ $student2->perimetro_braqueal }}</small></td>
              <td><small>{{ $student2->signos_peligro_infeccion_respiratoria }}</small></td>
              <td><small>{{ $student2->sexosignos_desnutricion }}</small></td>
              <td><small>{{ $student2->puntaje_z }}</small></td>
              <td><small>{{ $student2->calsificacion_antropometrica }}</small></td>
              <td><small>{{ $student2->municipio }}</small></td>
              <td><small>{{ $student2->uds }}</small></td>
              <td><small>{{ $student2->nombre_rancheria }}</small></td>
              <td><small>{{ $student2->ubicacion_casa }}</small></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>




<br>
<form  id="update-form" action="{{url('/new412_seguimiento')}}" method="post" enctype="multipart/form-data">
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

.modal-dialog {
    max-width: 90%; /* ajusta el ancho máximo de la modal */
  }
  .modal-body {
    max-height: 70vh; /* ajusta la altura máxima de la modal */
    overflow-y: auto; /* permite desplazamiento vertical si el contenido excede la altura máxima */
  }

            
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

    .dataTables_filter input {

  height: 100%;
  background-color: #555 ;
  border: solid 3px !important;
  border-radius: 20px !important;
  color: rgb(64, 125, 232);
  padding: 10px !important;
  font-weight: bold !important;
}
    
    </style>
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
    @stop
    
    @section('js')
       


<script>



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



<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>





      
    @stop
    