
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')


{{-- boton para abrir la modal --}}
<br>
<button type="button" class="btn btn-primary btn-sm" style="border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #007bff;" data-toggle="modal" data-target="#exampleModal">
  <i class="icon-zoom-in mr-2"></i> CLICK AQUI PARA VER TODOS LOS DATOS
</button>
<br>

  {{-- aqui termina el boton --}}
  
  
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">NOTIFICACIONES</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">




<table class="table table-hover table-striped table-bordered" style="border: 1px solid #000000;" id="sivigila"> 
  <thead class="table table-hover table-info table-bordered" style="background-color: #d9f2e6; border: 1px solid #000000;">
      <tr>
        <th style="font-size: smaller;" scope="col">Id</th>
        <th style="font-size: smaller;" scope="col">nombre coperante</th>
  
        <th style="font-size: smaller;" scope="col">fecha captacion</th>
          {{-- <th style="font-size: smaller;" scope="col">Número de Orden</th> --}}
          {{-- <th style="font-size: smaller;" scope="col">Nombre Coperante</th> --}}

          <th style="font-size: smaller;" scope="col">nombre_cuidador</th>
          <th style="font-size: smaller;" scope="col">identioficacion_cuidador</th>
          <th style="font-size: smaller;" scope="col">telefono_cuidador</th>
          <th style="font-size: smaller;" scope="col">nombre_eapb_cuidador</th>


          <th style="font-size: smaller;" scope="col">nombre_autoridad_trad_ansestral</th>
          <th style="font-size: smaller;" scope="col">datos_contacto_autoridad</th>

          <th style="font-size: smaller;" scope="col">Tipo ID</th>
          <th style="font-size: smaller;" scope="col">Identificacion</th>
          <th style="font-size: smaller;" scope="col">Nombre</th>

          <th style="font-size: smaller;" scope="col">sexo</th>
          <th style="font-size: smaller;" scope="col">fecha_nacimieto_nino</th>
          <th style="font-size: smaller;" scope="col">edad_meses</th>

          <th style="font-size: smaller;" scope="col">regimen_afiliacion</th>
          <th style="font-size: smaller;" scope="col">nombre_eapb_menor</th>
          <th style="font-size: smaller;" scope="col">peso_kg</th>
          <th style="font-size: smaller;" scope="col">logitud_talla_cm</th>
          <th style="font-size: smaller;" scope="col">perimetro_braqueal</th>
          <th style="font-size: smaller;" scope="col">signos_peligro_infeccion_respiratoria</th>
          <th style="font-size: smaller;" scope="col">sexosignos_desnutricion</th>
          <th style="font-size: smaller;" scope="col">puntaje_z</th>
          <th style="font-size: smaller;" scope="col">calsificacion_antropometrica</th>

          <th style="font-size: smaller;" scope="col">Municipio</th>
          
          <th style="font-size: smaller;" scope="col">Nombre rancheria</th>
          {{-- <th style="font-size: smaller;" scope="col">Nombre Ranchería</th> --}}
          {{-- <th style="font-size: smaller;" scope="col">Ubicación Casa</th> --}}
          <th style="font-size: smaller;" scope="col">Ubicacion casa</th>
        
  </thead>
  <tbody id="table">
      @foreach($sivigilas2030 as $student2)
      <tr>
        <td><small>{{ $student2->id }}</small></td>
        <td><small>{{ $student2->nombre_coperante }}</small></td>
        
           <td><small>{{ $student2->fecha_captacion }}</small></td> 
           
           <td><small>{{ $student2->nombre_cuidador }}</small></td>
           <td><small>{{ $student2->identioficacion_cuidador }}</small></td>
           <td><small>{{ $student2->telefono_cuidador }}</small></td>
           <td><small>{{ $student2->nombre_eapb_cuidador }}</small></td>


           <td><small>{{ $student2->nombre_autoridad_trad_ansestral }}</small></td>
           <td><small>{{ $student2->datos_contacto_autoridad }}</small></td>
        


          <td><small>{{ $student2->tipo_identificacion }}</small></td> 
          <td><small>{{ $student2->numero_identificacion }}</small></td> 
          <td><small>{{ $student2->primer_nombre.' '.$student2->segundo_nombre.' '.$student2->primer_apellido.' '.
            $student2->segundo_apellido }} </small> </td>

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
          <td><small>{{ $student2->nombre_rancheria }}</small></td>
          {{-- <td><small>{{ $student2->nombre_rancheria }}</small></td> --}}
          {{-- <td><small>{{ $student2->ubicacion_casa }}</small></td> --}}
          <td><small>{{ $student2->ubicacion_casa }}</small></td>
          
      </tr>
      @endforeach 
  </tbody>
</table>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
  {{-- <button type="button" class="btn btn-primary">Guardar cambios</button> --}}
</div>
</div>
</div>
</div>

</div>



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

<script>
  $(document).ready(function () {
    $('#sivigila').DataTable({
        "language": {
            "search": "BUSCAR",
            "lengthMenu": "Mostrar _MENU_ registros",
            "info": "Mostrando pagina _PAGE_ de _PAGES_",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "paging": true, // Activar paginación
        "lengthChange": false, // Desactivar el cambio de cantidad de registros mostrados
        "searching": true, // Habilitar la función de búsqueda
        "info": false, // Desactivar la información de la tabla (registros mostrados, registros totales, etc.)
        "pageLength": 1 // Mostrar solo un registro por página
    });
});




</script>  



<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>





      
    @stop
    