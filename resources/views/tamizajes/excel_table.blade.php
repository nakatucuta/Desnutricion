@extends('adminlte::page')

@section('title', 'Resultados de Tamizajes - PAI')

@section('content_header')

<div class="header-container">
    <h1 class="executive-title">TAMIZAJES</h1>
  
</div>


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 text-center">
                <h1 class="display-4 font-weight-bold text-primary">
                    Resultados de Tamizajes
                </h1>
                <p class="lead">
                    Esta tabla muestra todos los registros almacenados con sus usuarios asociados.
                </p>
            </div>
        </div>
    </div>
@stop
@section('content')
<div class="container">
    <!-- Botón de Regresar -->
    <div class="row mb-3">
        <div class="col-md-12 text-right">
            <a href="{{ route('excel.import.index') }}" class="btn btn-warning btn-lg">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Card con transición (fadeInUp) -->
            <div class="card shadow-sm animate__animated animate__fadeInUp">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0">Listado de Tamizajes</h4>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo Ident.</th>
                                <th>Número Ident.</th>
                                <th>Fecha Tamizaje</th>
                                <th>Tipo de Tamizaje</th>
                                <th>Código Resultado</th>
                                <th>Valor Laboratorio</th>
                                <th>Descripción Resultado</th>
                                <th>
                                    <a href="{{ route('excel.import.table', ['sort' => 'user']) }}" style="color: inherit; text-decoration: none;">
                                        Usuario
                                    </a>
                                </th>
                                <th>Creado en</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tamizajes as $t)
                                <tr>
                                    <td>{{ $t->id }}</td>
                                    <td>{{ $t->tipo_identificacion }}</td>
                                    <td>
                                        <a href="{{ route('tamizajes.show-pdfs', ['numero' => $t->numero_identificacion]) }}" class="text-primary">
                                          {{ $t->numero_identificacion }}
                                        </a>
                                      </td>
                                    <td>{{ $t->fecha_tamizaje }}</td>
                                    <td>{{ optional($t->tipo)->nombre }}</td>
                                    <td>{{ optional($t->resultado)->code }}</td>
                                    <td>{{ $t->valor_laboratorio ?? 'N/A' }}</td>
                                    <td>{{ $t->descript_resultado ?? 'N/A' }}</td>
                                    <td>{{ optional($t->user)->name }}</td>
                                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No hay registros de tamizajes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Paginación -->
                    <div class="p-3">
                        @if($tamizajes instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $tamizajes->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <!-- Animate.css para animaciones -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        /* Ajuste para la animación de la card */
        .card {
            animation-duration: 1s;
            animation-delay: 0.3s;
        }
    </style>


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
    justify-content: center; /* Alinea al centro horizontalmente */
    align-items: center;     /* Alinea verticalmente */
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
<!-- Scripts adicionales, si los necesitas -->
@stop
