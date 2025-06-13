@extends('adminlte::page')

@section('title', 'Importar Gestantes - PAI')

@section('content_header')
<div class="header-container">
    <h1 class="executive-title">IMPORTAR GESTANTES</h1>
</div>
@stop
@section('content')
<div class="container">
    <!-- Mensajes -->
    @include('ges_tipo1.mensajes')

    <div class="row mb-3">
        <div class="col-md-12 text-right">
            {{-- Nuevo botón “Ver datos” --}}
            <a href="{{ route('ges_tipo1.index') }}" class="btn btn-info btn-lg mr-2">
                <i class="fas fa-table"></i> Ver datos
            </a>
            {{-- Botón de Regresar existente --}}
            <a href="{{ url()->previous() }}" class="btn btn-warning btn-lg">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm animate__animated animate__fadeInUp">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0">Subir archivo Excel</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('ges_tipo1.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="excel_file">Selecciona el archivo Excel</label>
                            <input
                                type="file"
                                name="excel_file"
                                id="excel_file"
                                class="form-control @error('excel_file') is-invalid @enderror"
                                accept=".xlsx,.xls"
                                required
                            >
                            @error('excel_file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-file-excel"></i> Cargar datos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop


@section('css')
    <!-- Animate.css -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .header-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
            border-bottom: 2px solid #ccc;
            margin-bottom: 20px;
        }
        .executive-title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #2C3E50;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
        }
        body {
            background: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)),
                        url("{{ asset('vendor/adminlte/dist/img/logo.png') }}") center center no-repeat;
            background-size: 25% auto;
            background-attachment: fixed;
        }
        .card {
            animation-duration: 0.8s;
            animation-delay: 0.2s;
        }
    </style>
@stop

@section('js')
<!-- Si necesitas scripts adicionales, los agregas aquí -->
@stop
