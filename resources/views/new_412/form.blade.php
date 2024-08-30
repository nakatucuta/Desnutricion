@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')




            @section('content')
            <br>
            <a href="{{route('export7')}}" class="btn btn-success btn-sm"
            style="float: right; margin-right: 1rem; position: relative; right: 0; border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #28a745;">
                <i class="fas fa-file-export mr-2"></i> EXPORTAR
            </a>
            @include('new_412.mensajes')
            @if ($message = Session::get('success'))
            <div class="alert alert-success text-center">
                <strong>{{ $message }}</strong>
            </div>
            @endif
            
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <form action="{{ route('import-excel') }}" method="POST" enctype="multipart/form-data" class="form-horizontal" id="file-upload-form">
                            @csrf
                            <div class="form-group">
                                <!-- Área de arrastrar y soltar -->
                                <div id="drag-drop-area" class="drag-drop-area">
                                    <p>Arrastra y suelta un archivo aquí o haz clic para seleccionar un archivo</p>
                                    <input type="file" name="file" class="form-control" style="display: none;">
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Importar Excel</button>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
            
            


         



            <table class="table table-hover table-striped table-bordered" style="border: 1px solid #000000;" id="sivigila"> 
                <thead class="table table-hover table-info table-bordered" style="background-color: #d9f2e6; border: 1px solid #000000;">
                    <tr>
                        <th style="font-size: smaller;" scope="col">Fecha cargue</th>
                        <th style="font-size: smaller;" scope="col">Id</th>
                        {{-- <th style="font-size: smaller;" scope="col">Número de Orden</th> --}}
                        <th style="font-size: smaller;" scope="col">Nombre Coperante</th>
                        <th style="font-size: smaller;" scope="col">Fecha de Captación</th>
                        <th style="font-size: smaller;" scope="col">Municipio</th>
                        {{-- <th style="font-size: smaller;" scope="col">Nombre Ranchería</th> --}}
                        {{-- <th style="font-size: smaller;" scope="col">Ubicación Casa</th> --}}
                        <th style="font-size: smaller;" scope="col">Nombre Cuidador</th>
                        {{-- <th style="font-size: smaller;" scope="col">Identificación Cuidador</th> --}}
                        {{-- <th style="font-size: smaller;" scope="col">Teléfono Cuidador</th> --}}
                        {{-- <th style="font-size: smaller;" scope="col">Nombre EAPB Cuidador</th> --}}
                        {{-- <th style="font-size: smaller;" scope="col">Nombre Autoridad Trad. Ancestral</th>
                        <th style="font-size: smaller;" scope="col">Datos de Contacto Autoridad</th> --}}
                        <th style="font-size: smaller;" scope="col">Primer Nombre</th>
                        {{-- <th style="font-size: smaller;" scope="col">Segundo Nombre</th>
                        <th style="font-size: smaller;" scope="col">Primer Apellido</th>
                        <th style="font-size: smaller;" scope="col">Segundo Apellido</th> --}}
                        <th style="font-size: smaller;" scope="col">Tipo de Identificación</th>
                        <th style="font-size: smaller;" scope="col">Número de Identificación</th>
                        <th style="font-size: smaller;" scope="col">Sexo</th>
                        {{-- <th style="font-size: smaller;" scope="col">Fecha de Nacimiento del Niño</th> --}}
                        <th style="font-size: smaller;" scope="col">Edad en Meses</th>
                        <th style="font-size: smaller;" scope="col">Ips Primaria</th>
                        {{-- <th style="font-size: smaller;" scope="col">Validacion</th> --}}
                        <th style="font-size: smaller;" scope="col">Acciones</th>
                        {{-- <th style="font-size: smaller;" scope="col">Regimen de Afiliación</th>
                        <th style="font-size: smaller;" scope="col">Nombre EAPB Menor</th>
                        <th style="font-size: smaller;" scope="col">Peso (kg)</th>
                        <th style="font-size: smaller;" scope="col">Longitud/Talla (cm)</th>
                        <th style="font-size: smaller;" scope="col">Perímetro Braquial</th>
                        <th style="font-size: smaller;" scope="col">Signos de Peligro de Infección Respiratoria</th>
                        <th style="font-size: smaller;" scope="col">Sexo y Signos de Desnutrición</th>
                        <th style="font-size: smaller;" scope="col">Puntaje Z</th>
                        <th style="font-size: smaller;" scope="col">Clasificación Antropométrica</th>
                        <th style="font-size: smaller;" scope="col">Acciones</th> --}}
                    </tr>
                </thead>
                <tbody id="table">
                    @foreach($sivigilas as $student2)

                    


                    <tr>
                        <td><small>{{ $student2->created_at }}</small></td>
                        <td><small>{{ $student2->id }}</small></td>
                        {{-- <td><small>{{ $student2->numero_orden }}</small></td> --}}
                        <td><small>{{ $student2->nombre_coperante }}</small></td>
                        <td><small>{{ $student2->fecha_captacion }}</small></td>
                        <td><small>{{ $student2->municipio }}</small></td>
                        {{-- <td><small>{{ $student2->nombre_rancheria }}</small></td> --}}
                        {{-- <td><small>{{ $student2->ubicacion_casa }}</small></td> --}}
                        <td><small>{{ $student2->nombre_cuidador }}</small></td>
                        {{-- <td><small>{{ $student2->identioficacion_cuidador }}</small></td> --}}
                        {{-- <td><small>{{ $student2->telefono_cuidador }}</small></td> --}}
                        {{-- <td><small>{{ $student2->nombre_eapb_cuidador }}</small></td>
                        <td><small>{{ $student2->nombre_autoridad_trad_ansestral }}</small></td>
                        <td><small>{{ $student2->datos_contacto_autoridad }}</small></td> --}}
                        <td><small>{{ $student2->primer_nombre.' '.$student2->segundo_nombre.' '.$student2->primer_apellido.' '.
                            $student2->segundo_apellido }} </small> </td>
                        {{-- <td><small>{{ $student2->primer_nombre }}</small></td>
                        <td><small>{{ $student2->segundo_nombre }}</small></td>
                        <td><small>{{ $student2->primer_apellido }}</small></td>
                        <td><small>{{ $student2->segundo_apellido }}</small></td> --}}
                        <td><small>{{ $student2->tipo_identificacion }}</small></td>
                        <td><small>{{ $student2->numero_identificacion }}</small></td>
                        <td><small>{{ $student2->sexo }}</small></td>
                        {{-- <td><small>{{ $student2->fecha_nacimieto_nino }}</small></td> --}}
                        <td><small>{{ $student2->edad_meses }}</small></td>
                        <td style="color: {{ $student2->textColor }}"><small>{{ $student2->displayText }}</small></td>
                        {{-- <td>
                            <small>
                                @if ($seguimientoen113->contains($student2->numero_identificacion))
                                    
                                <span class="badge badge-warning d-block text-wrap" style="white-space: normal; font-size: 0.875rem;">
                                        NO ASIGNAR YA QUE TIENE UN SEGUIMIENTO ACTIVO EN EVENTO 113
                                    </span>
                                @endif
                            </small>
                        </td> --}}
                        <td><small> 
                            @if ($seguimientoen113->contains($student2->numero_identificacion))
                            <div class="alert alert-warning text-center" role="alert" style="background-color: #ffcc00; color: #333; padding: 10px; border-radius: 5px; position: relative; font-size: 0.8rem;">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem; margin-bottom: 5px; animation: colorChange 3s infinite;"></i>
                                    <span>
                                        <strong>¡Atención!</strong> NO ASIGNAR YA QUE TIENE UN SEGUIMIENTO ACTIVO EN EVENTO 113
                                    </span>
                                </div>
                            </div>
                            
                            @else
                            @if ($student2->user_id === null)
                            <!-- El campo user_id es NULL -->

                            <!-- El campo user_id no es NULL -->
                            <a href="{{ route('new412.destroy', $student2->id) }}"
                                onclick="event.preventDefault();
                                         if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                                             document.getElementById('delete-form-{{$student2->id}}').submit();
                                         }" class="btn  btn-danger btn-sm">
                                 <i class="fas fa-trash"></i>
                             </a>
                             <form id="delete-form-{{$student2->id}}" action="{{ route('new412.destroy', $student2->id) }}"
                                   method="POST" style="display: none;">
                                 @method('DELETE')
                                 @csrf
                             </form>
                         
                             <a class="btn btn-success btn-sm" href="{{ url('/new412/' . $student2->id . '/' . $student2->numero_identificacion . '/edit') }}" class="ref">
                                 <i class="fas fa-edit"></i>
                             </a>
                            
                        @else
                        <a href="" onclick="return false;" title="DETALLE" class="btn  btn-secondary btn-sm">
                            <span class="icon-zoom-in" ></span>Procesado <i class="fas fa-stop"></i></a>


                            <a class="btn btn-success btn-sm" href="{{ url('/new412/' . $student2->id . '/' . $student2->numero_identificacion . '/edit') }}" class="ref">
                                <i class="fas fa-tools"></i>

                                <a href="{{ route('new412.destroy', $student2->id) }}"
                                    onclick="event.preventDefault();
                                             if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                                                 document.getElementById('delete-form-{{$student2->id}}').submit();
                                             }" class="btn  btn-danger btn-sm">
                                     <i class="fas fa-trash"></i>
                                 </a>
                                 <form id="delete-form-{{$student2->id}}" action="{{ route('new412.destroy', $student2->id) }}"
                                       method="POST" style="display: none;">
                                     @method('DELETE')
                                     @csrf
                                 </form>
                            </a>
                        @endif


                        @endif
                            
                        </small></td>
                        {{-- <td><small>{{ $student2->regimen_afiliacion }}</small></td>
                        <td><small>{{ $student2->nombre_eapb_menor }}</small></td>
                        <td><small>{{ $student2->peso_kg }}</small></td>
                        <td><small>{{ $student2->logitud_talla_cm }}</small></td>
                        <td><small>{{ $student2->perimetro_braqueal }}</small></td>
                        <td><small>{{ $student2->signos_peligro_infeccion_respiratoria }}</small></td>
                        <td><small>{{ $student2->sexosignos_desnutricion }}</small></td>
                        <td><small>{{ $student2->puntaje_z }}</small></td>
                        <td><small>{{ $student2->calsificacion_antropometrica }}</small></td> --}}
                         <!-- Columna de acciones -->
                    </tr>
                    @endforeach 
                </tbody>
            </table>


            @section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
@stop
            @section('js')


<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<style> 

.dataTables_filter input {
  width: 500px !important;
  height: 100%;
  background-color: #555 ;
  border: solid 3px !important;
  border-radius: 20px !important;
  color: rgb(64, 125, 232);
  padding: 10px !important;
  font-weight: bold !important;
}

.dataTables_filter label {
  font-weight: bold !important ;
}

 .dataTables_length label {
  
  font-weight: bold !important;
} 

.dataTables_length select {
  display: flex ;
  border: solid 3px !important;
  border-radius: 20px !important;
  align-items: center !important;
  margin-bottom: 10px !important;
  color: rgb(64, 125, 232) !important;
}


.drag-drop-area {
    border: 2px dashed #ccc;
    border-radius: 5px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
}

.drag-drop-area:hover {
    background-color: #f9f9f9;
}

.drag-over {
    background-color: #e8f4ff;
}

 @keyframes colorChange {
        0% { color: #ff3333; }
        25% { color: #ffcc00; }
        50% { color: #33cc33; }
        75% { color: #3399ff; }
        100% { color: #ff3333; }
    }


            </style>

            <script>
                $(document).ready(function () {
                  $('#sivigila').DataTable({
              
                    "language":{
              
                          "search": "BUSCAR",
                          "lengthMenu": "Mostrar _MENU_ registros",
                          "info": "Mostrando pagina _PAGE_ de _PAGES_",
                          "paginate": {
                          "first": "Primero",
                          "last": "Último",
                          "next": "Siguiente",
                          "previous": "Anterior"
                                         }
              
              
                            }
              
                  });
              });



              
              </script>  

{{-- JAVA SCRIPT PARA EL DRAG AND DROG --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    var dragDropArea = document.getElementById('drag-drop-area');
    var inputFile = dragDropArea.querySelector('input[type="file"]');
    var fileNameDisplay = document.createElement('p'); // Crear un elemento <p> para mostrar el nombre del archivo
    dragDropArea.appendChild(fileNameDisplay); // Añadir el elemento <p> al área de arrastrar y soltar

    dragDropArea.onclick = function () {
        inputFile.click();
    };

    inputFile.onchange = function () {
        validateAndDisplayFile(inputFile.files);
    };

    dragDropArea.ondragover = dragDropArea.ondragenter = function(evt) {
        evt.preventDefault();
        dragDropArea.classList.add('drag-over');
    };

    dragDropArea.ondragleave = function() {
        dragDropArea.classList.remove('drag-over');
    };

    dragDropArea.ondrop = function(evt) {
        evt.preventDefault();
        dragDropArea.classList.remove('drag-over');
        
        // Obtiene el archivo desde el evento de arrastrar y soltar
        inputFile.files = evt.dataTransfer.files;
        validateAndDisplayFile(inputFile.files);
    };

    function validateAndDisplayFile(files) {
    if (files.length > 0) {
        var file = files[0];
        if (file.type === "application/vnd.ms-excel" || file.type === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            fileNameDisplay.textContent = `Archivo cargado: ${file.name}`;
            // Restablecer estilos en caso de éxito
            fileNameDisplay.style.background = ""; // Color de fondo por defecto
            fileNameDisplay.style.color = ""; // Color de texto por defecto
        } else {
            fileNameDisplay.textContent = "Por favor, sube un archivo Excel (.xls, .xlsx)";
            // Establecer el color de fondo a rojo y el texto a blanco
            fileNameDisplay.style.background = "red";
            fileNameDisplay.style.color = "white";
            fileNameDisplay.style.padding = "10px"; // Agregar algo de padding para mejor estética
            fileNameDisplay.style.borderRadius = "5px"; // Bordes redondeados para mejor estética
            fileNameDisplay.style.marginTop = "10px"; // Espacio sobre el mensaje de error
            // Restablecer el valor del input file si el archivo no es válido
            inputFile.value = "";
        }
    }
}
});



</script>
@stop

@stop



