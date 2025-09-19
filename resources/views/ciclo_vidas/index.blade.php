{{-- resources/views/ges_tipo1/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Ciclos de vida')

@section('content_header')
    <h1 class="mb-0">Ciclos de vida</h1>
    <p class="text-muted">Selecciona un ciclo para ver más detalles.</p>
@stop

@section('content')
    <div class="row">
        @foreach ($etapas as $slug => $etapa)
            @php
                // Mapeo de cada ciclo a su ruta específica (listas para tus vistas futuras)
                $routeMap = [
                    'primera-infancia' => 'ciclosvida.pi.menu',
                    'infancia'         => 'ciclosvida.infancia.menu',
                    'adolescencia'     => 'ciclosvida.adolescencia.menu',
                    'juventud'         => 'ciclosvida.juventud.menu',
                    'adultez'          => 'ciclosvida.adultez.menu',
                    'vejez'            => 'ciclosvida.vejez.menu',
                ];

                $href = isset($routeMap[$slug])
                    ? route($routeMap[$slug])
                    : route('ciclosvida.show', $slug); // fallback si agregas otro slug en el futuro
            @endphp

            <div class="col-12 col-sm-6 col-lg-4 d-flex align-items-stretch">
                <a href="{{ $href }}" class="w-100 text-reset" style="text-decoration:none;">
                    <div class="card card-hover shadow-sm mb-4">
                        <div class="card-body d-flex">
                            <div class="icon-circle {{ $etapa['color'] }} mr-3">
                                <i class="{{ $etapa['icono'] }}"></i>
                            </div>
                            <div>
                                <h3 class="card-title mb-1">{{ $etapa['titulo'] }}</h3>
                                <p class="card-text text-muted mb-0">{{ $etapa['descripcion'] }}</p>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Ver detalles</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@stop

@section('css')
    <!-- DataTables Bootstrap4 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">

    <style>
        .card-hover {
            border-radius: 16px;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,.08);
        }
        .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            flex: 0 0 56px;
        }
        .card-title {
            font-weight: 700;
            font-size: 1.15rem;
        }
    </style>
@stop

@section('js')
    <!-- DataTables JS (no se usa aquí, pero lo dejamos si tu layout lo requiere) -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
@stop
