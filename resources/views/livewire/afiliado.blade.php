
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>CARGUE REGISTRO DIARIO (PAI)</h1>
@stop

@section('content')
<div class="container mt-5">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
           CARGUE
        </h2>
    </x-slot>
    @include('livewire.mensajes')
    @if ($message = Session::get('success'))
    <div class="alert alert-success text-center">
        <strong>{{ $message }}</strong>
    </div>
    @endif

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
                    <button type="submit" class="btn btn-primary">Importar Excel</button>
                </div>
            </form>
        </div>
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
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css" rel="stylesheet">



@include('livewire.css')
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

    @include('livewire.javascript')

    <script>
        $(document).ready(function () {
            // Inicializar DataTables
            var table = $('#sivigila').DataTable({
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
                }
            });

            // Asignar eventos de clic para elementos con clase 'numero-identificacion'
            function attachEventHandlers() {
                $('.numero-identificacion').on('click', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    $.ajax({
                        url: '{{ route("getVacunas", "") }}/' + id,
                        method: 'GET',
                        success: function(data) {
                            $('#vacunaList').empty();
                            data.forEach(function(vacuna) {
                                $('#vacunaList').append('<li class="list-group-item">' + vacuna.nombre_vacuna + '</li>');
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

            // Mostrar el modal de carga cuando se envíe el formulario
            $('#file-upload-form').on('submit', function() {
                $('#loadingModal').modal('show');
            });

            // Ocultar el modal de carga cuando se complete la carga
            @if (Session::has('success'))
                $('#loadingModal').modal('hide');
                Swal.fire({
                    title: 'Carga Completa!',
                    text: 'El documento se ha cargado exitosamente.',
                    icon: 'success',
                    timer: 20000,
                    timerProgressBar: true,
                    didClose: () => {
                        confetti({
                            particleCount: 100,
                            spread: 70,
                            origin: { y: 0.6 }
                        });
                    }
                });
            @endif
        });

        // Script para el área de arrastrar y soltar
        document.addEventListener("DOMContentLoaded", function() {
            var dragDropArea = document.getElementById('drag-drop-area');
            var inputFile = dragDropArea.querySelector('input[type="file"]');
            var fileNameDisplay = document.createElement('p');
            dragDropArea.appendChild(fileNameDisplay);

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



        //JAVA SCRIPT PARA  ENVIAR EL CORREO DE LA MODAL  OJOOOOOO

        $(document).ready(function() {
    // Evento para abrir el modal de correo
    $('.send-email').on('click', function() {
        var patientId = $(this).data('id');
        var patientName = $(this).data('name');
        
        // Llenar los campos del modal con los datos del paciente
        $('#patientName').val(patientName);
        $('#patientId').val(patientId);
    });

    // Después de enviar el formulario
    $('#emailForm').on('submit', function() {
        var patientId = $('#patientId').val();
        
        // Deshabilitar el botón de envío para este paciente
        $('.send-email[data-id="' + patientId + '"]').prop('disabled', true).removeClass('blinking-button').addClass('btn-secondary').html('<i class="fas fa-envelope"></i> Correo Enviado');
    });
});


    </script>
@stop






    
    

    

   


