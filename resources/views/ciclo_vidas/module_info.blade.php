@extends('adminlte::page')

@section('title', ($course['label'] ?? 'Curso de vida') . ' - ' . ($module['label'] ?? 'Modulo'))

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route($course['menu_route'] ?? 'ciclosvida.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div>
            <h1 class="mb-0">{{ $module['label'] ?? 'Modulo' }}</h1>
            <small class="text-muted">{{ $course['label'] ?? 'Curso de vida' }}</small>
        </div>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-start">
                <div class="mr-3">
                    <span class="badge badge-primary p-3">
                        <i class="{{ $module['icon'] ?? 'fas fa-layer-group' }}"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h4 class="mb-2">{{ $module['label'] ?? 'Modulo' }}</h4>
                    <p class="text-muted mb-2">{{ $module['description'] ?? 'Seccion configurada para este curso de vida.' }}</p>
                    <p class="mb-1"><strong>Curso de vida:</strong> {{ $course['label'] ?? 'No definido' }}</p>
                    <p class="mb-1"><strong>Rango de edad:</strong> {{ $course['age_label'] ?? 'No definido' }}</p>
                    <p class="mb-0"><strong>Estado:</strong> Estructura lista para operar bajo el modelo por cursos de vida y materializacion por modulo.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-0">
        <i class="fas fa-info-circle"></i>
        Esta seccion ya quedo alineada con la Ruta de Promocion y Mantenimiento de la Salud por curso de vida. Cuando se conecte su fuente materializada o su logica de alertas, se mostrara aqui sin cambiar el diseno del modulo.
    </div>
@stop
