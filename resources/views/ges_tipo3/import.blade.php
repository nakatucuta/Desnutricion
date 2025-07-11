{{-- resources/views/ges_tipo3/import.blade.php --}}
@extends('adminlte::page')

@section('title', 'Importar Tipo 3')

@section('content_header')
<div class="header-container d-flex align-items-center justify-content-center mb-4">
    <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}"
         alt="Escudo"
         class="header-logo mr-3">
    <h1 class="executive-title mb-0">IMPORTAR TIPO 3</h1>
</div>
@stop

@section('content')
<div class="container position-relative">
    {{-- Overlay de bienvenida --}}
    <div id="welcomeOverlay">
        <div class="welcome-content animate__animated animate__fadeInDown text-center">
            <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo" class="welcome-logo mb-3">
            <h2 class="mb-2">Bienvenido al proceso de cargue TIPO 3</h2>
            <div class="spinner-border text-info" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>
    </div>

    {{-- Mensajes --}}
    @include('ges_tipo1.mensajes')

    {{-- Botones centrados --}}
    <div class="row mb-4 justify-content-center">
        <div class="col-md-6 text-center">
            <a href="{{ route('ges_tipo1.index') }}" class="btn btn-gradient-info btn-lg mx-2">
                <i class="fas fa-table mr-1"></i> Ver registros
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-gradient-warning btn-lg mx-2">
                <i class="fas fa-arrow-left mr-1"></i> Regresar
            </a>
        </div>
    </div>

    {{-- Card centrado --}}
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-info animate__animated animate__fadeInUp">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-file-excel mr-2"></i> Subir archivo Excel - Tipo 3
                    </h4>
                </div>
                <div class="card-body">
                    <form id="importForm" action="{{ route('ges_tipo3.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="custom-file mb-3">
                            <input
                                type="file"
                                name="excel_file"
                                id="excel_file"
                                class="custom-file-input @error('excel_file') is-invalid @enderror"
                                accept=".xlsx,.xls"
                                required
                            >
                            <label class="custom-file-label" for="excel_file">Elige un archivo .xlsx o .xls</label>
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button id="btnSubmit" type="submit" class="btn btn-gradient-info btn-block">
                            <i class="fas fa-upload mr-1"></i> Cargar datos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Overlay de carga --}}
<div id="loadingOverlay">
    <div class="spinner-border text-info" role="status">
        <span class="sr-only">Cargando...</span>
    </div>
    <div class="loading-text">Procesando, por favor espere...</div>
</div>
@stop

@section('css')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* Header */
    .header-container {
        padding: 15px 0;
        border-bottom: 2px solid #dee2e6;
    }
    .header-logo {
        width: 60px;
    }
    .executive-title {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 1.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #17a2b8;
    }

    /* Botones degradados */
    .btn-gradient-info {
        background: linear-gradient(45deg, #17a2b8, #117a8b);
        border: none;
        color: #fff;
        transition: background 0.3s ease;
    }
    .btn-gradient-info:hover {
        background: linear-gradient(45deg, #117a8b, #0e6272);
    }
    .btn-gradient-warning {
        background: linear-gradient(45deg, #ffc107, #e0a800);
        border: none;
        color: #212529;
        transition: background 0.3s ease;
    }
    .btn-gradient-warning:hover {
        background: linear-gradient(45deg, #e0a800, #c69500);
    }

    /* Welcome Overlay */
    #welcomeOverlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: #ffffff;
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .welcome-logo {
        width: 100px;
    }
    .welcome-content h2 {
        color: #17a2b8;
        margin-bottom: 1rem;
    }

    /* Loading Overlay */
    #loadingOverlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(255,255,255,0.85);
        z-index: 2000;
        text-align: center;
        padding-top: 30vh;
    }
    #loadingOverlay .spinner-border {
        width: 4rem;
        height: 4rem;
    }
    #loadingOverlay .loading-text {
        margin-top: 1rem;
        font-size: 1.25rem;
        color: #17a2b8;
    }

    /* Card */
    .card {
        border-radius: .5rem;
    }
    .custom-file-label::after {
        content: "Buscar";
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function(){
        // Animación de bienvenida
        $('#welcomeOverlay').delay(2000).fadeOut(600);

        // Mostrar nombre de archivo al seleccionar
        $('#excel_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(fileName);
        });

        // Al enviar el formulario, mostrar overlay de carga
        $('#importForm').on('submit', function(){
            $('#btnSubmit').prop('disabled', true);
            $('#loadingOverlay').fadeIn(200);
        });
    });
</script>
@stop
