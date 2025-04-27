@extends('adminlte::page')

@section('title', 'Importación de Datos - PAI')

{{-- Sección CSS para el fondo difuminado --}}
@section('css')
    <!-- Animate.css para animaciones -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        /* Fondo difuminado: combinamos una capa semitransparente con la imagen de fondo */
        body {
            /* Imagen centrada, sin repetición, abarcando todo */
            background: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.7)), 
                        url("{{ asset('vendor/adminlte/dist/img/logo.png') }}") center center no-repeat;
            background-size: 30% auto; /* Ajusta el tamaño de la imagen */
            background-attachment: fixed;
        }




        
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

@section('content_header')

<div class="header-container">
    <h1 class="executive-title">CARGUE TAMIZAJE</h1>
    <a href="{{ route('download.excel') }}" class="btn btn-download">
        Descargar Formato
    </a>
</div>


    <div class="container">
        <div class="row justify-content-center align-items-center text-center">
            <div class="col-md-12">
                <!-- Imagen del escudo (centro superior) -->
                <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" 
                     alt="Escudo" 
                     style="width: 100px; margin-bottom: 10px;">
                
                <h1 class="display-4 font-weight-bold text-primary">
                    Cargue  aqui  su archivo  excel 
                </h1>
                <p class="lead">
                    Suba su archivo Excel para importar la información de forma rápida y segura.
                </p>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container" style="max-width: 900px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Card del formulario principal -->
            <div class="card shadow-sm animate__animated animate__fadeInUp">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Cargar Archivo Excel</h3>
                </div>
                <div class="card-body">
                    <!-- Alertas de error o éxito (se llenarán por AJAX) -->
                    <div id="alertContainer"></div>

                    <form id="uploadForm" action="{{ route('excel.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="excel_file">Selecciona archivo Excel (.xlsx, .xls):</label>
                            <div class="custom-file">
                                <input type="file" name="excel_file" class="custom-file-input" id="excel_file" required>
                                <label class="custom-file-label" for="excel_file">Elegir archivo</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Importar</button>
                    </form>

                    <!-- Botón para ver resultados -->
                    <a href="{{ route('excel.import.table') }}" class="btn btn-info btn-block mt-3">
                        Ver Resultados
                    </a>

                    <!-- Botón que abre el modal para generar el reporte por fecha -->
                    <button class="btn btn-secondary btn-block mt-3" data-toggle="modal" data-target="#reporteModal">
                        Generar reporte por fecha
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para escoger el rango de fechas -->
<div class="modal fade" id="reporteModal" tabindex="-1" role="dialog" aria-labelledby="reporteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{ route('excel.import.generate-excel') }}" method="POST">
        @csrf
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="reporteModalLabel">Generar reporte por fecha</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Seleccione el rango de fechas:</p>
                <div class="form-group">
                    <label for="start_date">Fecha inicio:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Fecha fin:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-secondary">Generar Excel</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- Overlay de carga (barra de progreso y contador) -->
<div id="loadingOverlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%;
     height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1050;">
    <div class="d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
        <div class="text-center text-white mb-3">
            <h3>Cargando archivo, por favor espere...</h3>
            <div id="timer" style="font-size: 1.5rem;">0 segundos</div>
        </div>
        <div class="progress" style="width: 50%;">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                 role="progressbar" style="width: 0%;"></div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function(){
        // Actualiza la etiqueta del input al seleccionar archivo
        $('.custom-file-input').on('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Elegir archivo';
            $(this).next('.custom-file-label').html(fileName);
        });

        // Manejar el envío del formulario via AJAX
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            $('#alertContainer').html('');
            $('#loadingOverlay').show();

            var formData = new FormData(this);
            var startTime = new Date().getTime();

            var timerInterval = setInterval(function(){
                var seconds = Math.floor((new Date().getTime() - startTime) / 1000);
                $('#timer').text(seconds + ' segundos');
            }, 1000);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = Math.round((e.loaded / e.total) * 100);
                            $('#progressBar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    clearInterval(timerInterval);
                    var totalSeconds = Math.max(1, Math.floor((new Date().getTime() - startTime) / 1000));
                    if (response.status === 'success') {
                        $('#loadingOverlay').html(
                            '<div class="d-flex flex-column justify-content-center align-items-center" style="height: 100%;">' +
                                '<div class="text-center text-white mb-3">' +
                                    '<h3>' + response.message + '<br/>Demoró ' + totalSeconds + ' segundos.</h3>' +
                                '</div>' +
                            '</div>'
                        );
                        setTimeout(function(){
                            window.location.reload();
                        }, 3000);
                    } else {
                        $('#loadingOverlay').hide();
                        $('#alertContainer').html(
                            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
                                    '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    clearInterval(timerInterval);
                    $('#loadingOverlay').hide();

                    var errorMsg = 'Error en la carga.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#alertContainer').html(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            errorMsg +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">' +
                                '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                        '</div>'
                    );
                }
            });
        });
    });
</script>
@stop
