@extends('adminlte::page')

@section('title', 'Detalle del Lote')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="text-primary mb-0">
            <i class="fas fa-eye mr-2"></i>Detalle Lote #{{ $batch->id }}
        </h1>
        <small class="text-muted">
            Creados: {{ $batch->created_rows }} | Tiempo: {{ $batch->duration_ms }} ms | Usuario: {{ $batch->user->name ?? '—' }}
        </small>
    </div>

    <a href="{{ route('preconcepcional.batches') }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a lotes
    </a>
</div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-secondary">
                <i class="fas fa-file-excel mr-1"></i> Archivo: {{ $batch->original_filename ?? '—' }}
            </span>
            <span class="text-monospace small">
                Hash: {{ $batch->file_hash ?? '—' }}
            </span>
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Tipo Doc</th>
                    <th>Identificación</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Municipio</th>
                    <th>Riesgo</th>
                    <th style="width:90px;">Acción</th>
                </tr>
            </thead>
            <tbody>
            @forelse($registros as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->tipo_documento }}</td>
                    <td>{{ $r->numero_identificacion }}</td>
                    <td>{{ trim(($r->nombre_1 ?? '').' '.($r->nombre_2 ?? '')) }}</td>
                    <td>{{ trim(($r->apellido_1 ?? '').' '.($r->apellido_2 ?? '')) }}</td>
                    <td>{{ $r->municipio_residencia }}</td>
                    <td>{{ $r->riesgo_preconcepcional }}</td>
                    <td class="text-center">
                        <a class="btn btn-sm btn-primary" href="{{ route('preconcepcional.show', $r) }}">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Este lote no creó registros</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $registros->links() }}
        </div>
    </div>
</div>
@stop
