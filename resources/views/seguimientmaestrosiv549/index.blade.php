@extends('adminlte::page')

@section('title', 'Seguimientos Caso #'.$asignacion->id)

@section('content_header')
    <h1 class="text-info">Seguimientos de la asignación #{{ $asignacion->id }}</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">
                Paciente: <strong>{{ $asignacion->pri_nom_ }} {{ $asignacion->seg_nom_ }} {{ $asignacion->pri_ape_ }} {{ $asignacion->seg_ape_ }}</strong>
                | ID: <strong>{{ $asignacion->tip_ide_ }} {{ $asignacion->num_ide_ }}</strong>
            </span>
            <a href="{{ route('asignaciones.seguimientmaestrosiv549.create', $asignacion) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="white-space:nowrap;">F. Hospitalización</th>
                        <th style="white-space:nowrap;">F. Egreso</th>
                        <th style="white-space:nowrap;">F. Seg. 1</th>
                        <th style="white-space:nowrap;">F. Seg. 2</th>
                        <th style="white-space:nowrap;">F. Seg. 3</th>
                        <th style="white-space:nowrap;">F. Seg. 4</th>
                        <th style="white-space:nowrap;">F. Seg. 5</th>
                        <th style="width:110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($seguimientos as $s)
                    <tr>
                        <td>{{ $s->fecha_hospitalizacion }}</td>
                        <td>{{ $s->fecha_egreso }}</td>
                        <td>{{ $s->fecha_seguimiento_1 }}</td>
                        <td>{{ $s->fecha_seguimiento_2 }}</td>
                        <td>{{ $s->fecha_seguimiento_3 }}</td>
                        <td>{{ $s->fecha_seguimiento_4 }}</td>
                        <td>{{ $s->fecha_seguimiento_5 }}</td>
                        <td class="text-center">
                            <a class="btn btn-xs btn-info" href="{{ route('asignaciones.seguimientmaestrosiv549.show', [$asignacion, $s]) }}" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a class="btn btn-xs btn-warning" href="{{ route('asignaciones.seguimientmaestrosiv549.edit', [$asignacion, $s]) }}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('asignaciones.seguimientmaestrosiv549.destroy', [$asignacion, $s]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este seguimiento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No hay seguimientos registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
