
@extends('adminlte::page')

@section('title', 'PAI')

@section('content_header')
   
<div class="header-container">
    <h1 class="executive-title">CARGUE REGISTRO DIARIO (PAI)</h1>
    <a href="{{ route('download.excel') }}" class="btn btn-download">
        Descargar Formato
    </a>
</div>
@stop

@section('content')
<div class="container mt-5">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
           CARGUE
        </h2>
    </x-slot>
    {{-- @include('livewire.mensajes')
    @if ($message = Session::get('success'))
    <div class="alert alert-success text-center">
        <strong>{{ $message }}</strong>
    </div>
    @endif --}}
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('import-excel_2') }}" method="POST" enctype="multipart/form-data" class="form-horizontal" id="file-upload-form">
                @csrf
                <div class="form-group">
                    <!-- Área de arrastrar y soltar -->
                    <div id="drag-drop-area" class="drag-drop-area border border-primary p-3 text-center">
                        <p>Arrastra y suelta un archivo aquí o haz clic para seleccionar un archivo</p>
                        <input type="file" name="file" class="form-control-file" style="display: none;">
                    </div>
                </div>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary" id="submit-button">Importar Excel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Contenedor para mostrar el mensaje de advertencia -->
    <div id="date-warning" class="alert alert-warning text-center" style="display: none;">
        El formulario no está disponible fuera del rango de fechas permitido.
    </div>
    
    
    {{-- <div class="container mt-5">
        <h4>Exportar Vacunas</h4>
        <a href="{{ url('export-vacunas') }}" class="btn btn-success">Exportar a Excel</a>
    </div> --}}
    <!-- Contenedor para la tabla -->
    @include('livewire.tabla')
    <!-- modal  que muestra las vacunas -->
    @include('livewire.modal_tabla')

   <!-- Modal para enviar correo mensajes de carga -->
<!-- Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="emailModalLabel">Enviar Correo</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form>
            <div class="form-group">
              <label for="emailSubject">Asunto</label>
              <input type="text" class="form-control" id="emailSubject" placeholder="Asunto">
            </div>
            <div class="form-group">
              <label for="emailMessage">Mensaje</label>
              <textarea class="form-control" id="emailMessage" rows="3"></textarea>
            </div>
            <input type="hidden" id="emailPatientName">
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="sendEmailButton">Enviar Correo</button>
        </div>
      </div>
    </div>
  </div>
  

</div>





 <!-- Modal para mensajes de carga -->
 <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title" id="loadingModalLabel">Cargando Documento</h5>
            </div>
            <div class="modal-body">
                <div class="spinner-border text-primary spinner-large" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p>Por favor espere mientras se carga el documento...</p>
            </div>
        </div>
    </div>
    </div>
    
    </div>
@stop
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css" rel="stylesheet">



@include('livewire.css')
<style>
    /* #search-results {
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
}
.list-group-item {
    cursor: pointer;
} */


/*AQUI COMIENZA EL CSS PARA EL BUSCADOR */
/* Contenedor principal */
.loading-spinner {
    text-align: center;
    font-size: 1rem;
    color: #007bff;
    margin-top: 10px;
}



.search-container {
    position: relative;
    max-width: 100%; /* Ocupa todo el ancho disponible */
    width: 500px; /* Ancho deseado */
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    margin-left: auto; /* Para asegurar que esté alineado a la derecha */

}

/* Input de búsqueda con icono */
.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 12px 40px 12px 15px;
    font-size: 16px;
    border: 2px solid #ddd;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
}

.search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0px 4px 12px rgba(0, 123, 255, 0.2);
}

/* Icono de búsqueda dentro del input */
.search-icon {
    position: absolute;
    right: 15px;
    font-size: 18px;
    color: #888;
    transition: color 0.3s ease;
}

.search-input:focus + .search-icon {
    color: #007bff;
}

/* Resultados de búsqueda */
#search-results {
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    position: absolute;
    bottom: 100%; /* Hacer que el contenedor suba sobre el input */
    left: 0;
    background-color: #fff;
    box-shadow: 0px -8px 16px rgba(0, 0, 0, 0.1); /* Sombra hacia arriba */
    border-radius: 6px;
    z-index: 1000;
    animation: slideUp 0.3s ease;
    padding-top: 8px;
}

.list-group-item {
    padding: 12px 20px;
    font-size: 16px;
    color: #333;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.list-group-item:hover {
    background-color: #007bff;
    color: #fff;
}

/* Animación de deslizar para mostrar resultados */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive: pantalla pequeña */
@media (max-width: 576px) {
    .search-container {
        width: 90%;
        padding: 15px;
    }

    .search-input {
        padding: 10px 35px 10px 15px;
        font-size: 14px;
    }

    .list-group-item {
        font-size: 14px;
    }
}

/* AQUI TERMINA EL CSS DEL BUSCADOR */





    /* Contenedor del header para alinear el botón a la derecha */
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 2px solid #ccc;
        margin-bottom: 20px;
    }

    /* Estilo profesional para el título */
    .executive-title {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 36px;
        font-weight: 700;
        color: #2C3E50;
        text-transform: uppercase;
        letter-spacing: 2px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        padding: 10px 20px;
        background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        border-left: 6px solid #2980b9;
    }

    /* Estilo del botón animado */
    .btn-download {
        background-color: #ff4b5c;
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        font-size: 18px;
        text-align: center;
        display: inline-block;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        animation: pulse 1s infinite;
        transition: background-color 0.3s ease;
    }

    .btn-download:hover {
        background-color: #ff616f;
        color: white;
    }

    /* Efecto de palpitación */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
</style>

@stop
@section('js')
    <!-- jQuery -->
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> --}}

    <!-- Popper.js and Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- AdminLTE JS -->
    {{-- <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script> --}}

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>

    <!-- Confetti JS -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.min.js"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}


    @include('livewire.javascript')

    <script>

          // Mostrar el modal de carga cuando se envíe el formulario
          $('#file-upload-form').on('submit', function() {
                $('#loadingModal').modal('show');
            });
$(document).ready(function() {
    // Maneja el evento de escritura en el campo de búsqueda
    $(document).ready(function() {
    // Maneja el evento de escritura en el campo de búsqueda
    $('#search').on('keyup', function() {
        var query = $(this).val();

        if (query.length > 0) {
            // Mostrar el spinner
            $('#loading-spinner').show();

            // Realizar la búsqueda con AJAX
            $.ajax({
                url: "{{ route('buscar.afiliados') }}",
                method: "GET",
                dataType: "json",
                data: {search: query},
                success: function(data) {
                    $('#search-results').empty(); // Limpiar resultados previos
                    $('#loading-spinner').hide(); // Ocultar el spinner

                    if (data.length > 0) {
                        $.each(data, function(index, afiliado) {
                            $('#search-results').append('<a href="#" class="list-group-item list-group-item-action search-result-item" data-id="'+ afiliado.numero_identificacion +'">' +
                                afiliado.numero_identificacion + ' - ' +
                                afiliado.primer_nombre + ' ' + afiliado.segundo_nombre + ' ' +
                                afiliado.primer_apellido + ' ' + afiliado.segundo_apellido +
                                '</a>');
                        });
                    } else {
                        $('#search-results').append('<a href="#" class="list-group-item list-group-item-action">No se encontraron resultados</a>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading-spinner').hide(); // Ocultar el spinner si hay un error
                    console.log("Error en la solicitud AJAX:", error);
                }
            });
        } else {
            $('#search-results').empty(); // Limpiar resultados si el campo está vacío
            $('#loading-spinner').hide(); // Ocultar el spinner
        }
    });

    // Maneja la selección de un resultado en la lista desplegable
    $(document).on('click', '.search-result-item', function(e) {
        e.preventDefault();
        var numeroIdentificacion = $(this).data('id');
        $('#search').val(numeroIdentificacion); // Establece el valor en el campo de búsqueda

        // Mostrar el spinner
        $('#loading-spinner').show();

        // Filtra la tabla automáticamente por el número de identificación seleccionado
        $.ajax({
            url: "{{ route('afiliado') }}",
            method: "GET",
            dataType: "json",
            data: {search: numeroIdentificacion},
            success: function(response) {
                $('#sivigila tbody').html(''); // Limpiar la tabla
                $('#loading-spinner').hide(); // Ocultar el spinner

                $.each(response.sivigilas_usernormal, function(index, student2) {
                    var correoEnviado = student2.correo_enviado ? true : false;

                    var acciones = correoEnviado
                        ? '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-envelope"></i> Correo Enviado</button>'
                        : '<a href="#" class="btn btn-sm btn-warning blinking-button send-email" data-toggle="modal" data-target="#emailModal" data-id="' + student2.id + '" data-name="' + student2.primer_nombre + ' ' + student2.segundo_nombre + ' ' + student2.primer_apellido + ' ' + student2.segundo_apellido + '"><i class="fas fa-envelope"></i> Solicitud</a>';

                    $('#sivigila tbody').append(
                        '<tr>' +
                        '<td>' + student2.id + '</td>' +
                        '<td><a href="#" class="numero-identificacion" data-id="' + student2.id + '">' + student2.numero_identificacion + '</a></td>' +
                        '<td>' + student2.primer_nombre + ' ' + student2.segundo_nombre + ' ' + student2.primer_apellido + ' ' + student2.segundo_apellido + '</td>' +
                        '<td>' + student2.numero_carnet + '</td>' +
                        '<td>' + acciones + '</td>' +
                        '</tr>'
                    );
                });

                $('#search-results').empty(); // Limpiar los resultados de la lista desplegable

                handleEmailModal(); // Adjuntar manejador para el botón de "Solicitud"
                attachEventHandlers();
            },
            error: function(xhr, status, error) {
                $('#loading-spinner').hide(); // Ocultar el spinner si hay un error
                console.log("Error al filtrar la tabla:", error);
            }
        });
    });
});

    //EL BUSCADOR TERMINA A QUI 
  

        
            // // Inicializar DataTables
            // var table = $('#sivigila').DataTable({
            //     "language": {
            //         "search": "BUSCAR",
            //         "lengthMenu": "Mostrar _MENU_ registros",
            //         "info": "Mostrando pagina _PAGE_ de _PAGES_",
            //         "paginate": {
            //             "first": "Primero",
            //             "last": "Último",
            //             "next": "Siguiente",
            //             "previous": "Anterior"
            //         }
            //     }
            // });

            function attachEventHandlers() {
    $('.numero-identificacion').on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        var numeroCarnet = $(this).data('carnet');

        var url = '{{ route("getVacunas", ["id" => ":id", "numero_carnet" => ":numeroCarnet"]) }}';
        url = url.replace(':id', id);
        url = url.replace(':numeroCarnet', numeroCarnet);

        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                $('#vacunaList').empty();

                // Armar el nombre completo del paciente
                if (data.length > 0) {
                    var nombreCompleto = data[0].prim_nom + ' ' + (data[0].seg_nom ? data[0].seg_nom + ' ' : '') + data[0].pri_ape + ' ' + data[0].seg_ape;
                    $('#nombrePaciente').text(nombreCompleto); // Actualizar el título con el nombre del paciente
                }

                                    data.forEach(function(vacuna) {
                        $('#vacunaList').append(
                            '<tr>' +
                            '<td>' + vacuna.nombre_vacuna + '</td>' +
                            '<td>' + vacuna.docis_vacuna + '</td>' +
                            '<td>' + vacuna.fecha_vacunacion + '</td>' +
                            '<td>' + vacuna.edad_anos + '</td>' +
                            '<td>' + vacuna.total_meses + '</td>' +
                            '<td>' + vacuna.nombre_usuario + '</td>' +
                            '<td>' + vacuna.responsable + '</td>' +
                            '</tr>'
                        );
                    });     

                $('#vacunaModal').modal('show');
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
}


            // Llamar a attachEventHandlers después de inicializar DataTables
            attachEventHandlers();
            table.on('draw', function() {
                attachEventHandlers();
            });

          

            // Ocultar el modal de carga cuando se complete la carga
            // @if (Session::has('success'))
            //     $('#loadingModal').modal('hide');
            //     // Swal.fire({
            //     //     title: 'Carga Completa!',
            //     //     text: 'El documento se ha cargado exitosamente.',
            //     //     icon: 'success',
            //     //     timer: 20000,
            //     //     timerProgressBar: true,
            //     //     didClose: () => {
            //     //         confetti({
            //     //             particleCount: 100,
            //     //             spread: 70,
            //     //             origin: { y: 0.6 }
            //     //         });
            //     //     }
            //     // });
            // @endif

                // Ocultar el modal de carga cuando se complete la carga
                @if (Session::has('success'))
                    $('#loadingModal').modal('hide');
                    // Insertar los mensajes de éxito en el DOM, si existen
                    $(document).ready(function() {
                        $('#mensajes-container').html(`@include('livewire.mensajes')`);
                    });
                @endif


        });



    // Script para el área de arrastrar y soltar
           // Script para el área de arrastrar y soltar
           document.addEventListener("DOMContentLoaded", function() {
    var dragDropArea = document.getElementById('drag-drop-area');
    var inputFile = dragDropArea.querySelector('input[type="file"]');
    var fileNameDisplay = document.createElement('p');
    dragDropArea.appendChild(fileNameDisplay);

    var submitButton = document.getElementById("submit-button");
    var dateWarning = document.getElementById("date-warning");

    // Obtener la fecha actual
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth(); // Mes actual (0-11)

    // Definir la fecha de inicio (en este caso, desde ayer)
    var startDate = new Date(currentYear, currentMonth, 20);  // Día 11 del mes actual
   
    // Definir la fecha de fin (5 del mes siguiente)
    var endDate = new Date(currentYear, currentMonth + 1, 5);

    // Si estamos en diciembre, corregir para pasar a enero del próximo año
    if (currentMonth === 11) {
        endDate = new Date(currentYear + 1, 0, 5);
    }

    // Verificar si la fecha actual está dentro del rango (entre startDate y endDate)
    if (currentDate >= startDate && currentDate <= endDate) {
        // Habilitar el botón de enviar
        submitButton.disabled = false;
        dateWarning.style.display = "none";  // Ocultar el mensaje de advertencia

        // Hacer funcional el área de arrastrar y soltar
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
            inputFile.files = evt.dataTransfer.files;
            validateAndDisplayFile(inputFile.files);
        };
    } else {
        // Deshabilitar el botón de enviar y el área de arrastrar y soltar
        submitButton.disabled = true;
        dateWarning.style.display = "block";  // Mostrar el mensaje de advertencia

        // Deshabilitar la interacción con el área de arrastrar y soltar
        dragDropArea.style.pointerEvents = 'none';
        dragDropArea.style.opacity = '0.5';  // Hacer que se vea "deshabilitado"
        dragDropArea.innerHTML = "<p>El formulario no está disponible fuera del rango de fechas permitido.</p>";
    }

    // Función para validar y mostrar el archivo cargado
    function validateAndDisplayFile(files) {
        if (files.length > 0) {
            var file = files[0];
            if (file.type === "application/vnd.ms-excel" || file.type === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                fileNameDisplay.textContent = `Archivo cargado: ${file.name}`;
                fileNameDisplay.style.background = "";
                fileNameDisplay.style.color = "";
            } else {
                fileNameDisplay.textContent = "Por favor, sube un archivo Excel (.xls, .xlsx)";
                fileNameDisplay.style.background = "red";
                fileNameDisplay.style.color = "white";
                fileNameDisplay.style.padding = "10px";
                fileNameDisplay.style.borderRadius = "5px";
                fileNameDisplay.style.marginTop = "10px";
                inputFile.value = "";
            }
        }
    }
});


// Función para manejar la exportación a Excel con fechas
$('#exportButton').on('click', function() {
                    var startDate = $('#start_date').val();
                    var endDate = $('#end_date').val();

                    if (startDate && endDate) {
                        // Usa la ruta de Laravel para generar la URL completa
                        var url = '{{ route("exportVacunas") }}' + '?start_date=' + startDate + '&end_date=' + endDate;
                        window.location.href = url;
                    } else {
                        alert('Por favor selecciona ambas fechas.');
                    }
            });


        //JAVA SCRIPT PARA  ENVIAR EL CORREO DE LA MODAL  OJOOOOOO

        function handleEmailModal() {
        // Evento para abrir el modal de correo
        $('.send-email').off('click').on('click', function() {
            var patientId = $(this).data('id');
            var patientName = $(this).data('name');
            
            // Llenar los campos del modal con los datos del paciente
            $('#patientName').val(patientName);
            $('#patientId').val(patientId);
            
            // Abre el modal
            $('#emailModal').modal('show');
        });

        // Evento para enviar el correo al hacer clic en el botón de "Enviar Correo"
        $('#sendEmailButton').off('click').on('click', function() {
            var patientId = $('#patientId').val();
            var subject = $('#emailSubject').val();
            var message = $('#emailMessage').val();
            
            // Aquí iría la lógica para enviar el correo (puede ser una llamada AJAX)

            // Simulación de éxito en el envío de correo
            alert('Correo enviado a ' + $('#patientName').val());

            // Deshabilitar el botón de envío para este paciente
            $('.send-email[data-id="' + patientId + '"]').prop('disabled', true).removeClass('blinking-button').addClass('btn-secondary').html('<i class="fas fa-envelope"></i> Correo Enviado');
            
            // Cerrar el modal
            $('#emailModal').modal('hide');
        });
    }

    // Aplica la funcionalidad al cargar la tabla y cada vez que se redibuja
    handleEmailModal();  // Aplica para la primera carga

    // Vuelve a aplicar los eventos después de cada redibujado de la tabla
    table.on('draw', function() {
        handleEmailModal();  // Aplica para cada redibujado (paginación, filtrado, etc.)
    });


 

    </script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mensaje de éxito
        @if(Session::has('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ Session::get('success') }}',
                icon: 'success',
                confirmButtonText: 'Ok'
            });
        @endif

        // Mensaje de error personalizado
        @if(Session::has('error1'))
            @if(is_array(Session::get('error1')))
                let errors = '';
                @foreach(Session::get('error1') as $error)
                    errors += '<li>{{ $error }}</li>';
                @endforeach

                Swal.fire({
                    title: '¡Error!',
                    html: '<ul>' + errors + '</ul>',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            @else
                Swal.fire({
                    title: '¡Error!',
                    text: '{{ Session::get('error1') }}',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            @endif
        @endif

        // Errores de validación
        @if(count($errors) > 0)
            let validationErrors = '';
            @foreach($errors->all() as $error)
                validationErrors += '<li>{{ $error }}</li>';
            @endforeach

            Swal.fire({
                title: 'Errores de validación!',
                html: '<ul>' + validationErrors + '</ul>',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        @endif
    });
</script>
@stop






    
    

    

   


