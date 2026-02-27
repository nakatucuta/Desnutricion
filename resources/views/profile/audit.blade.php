@extends('adminlte::page')

@section('title', 'Auditoria Perfil')

@section('content_header')
    <h1>Auditoria de Cambios de Perfil</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario Afectado</th>
                        <th>Cambiado Por</th>
                        <th>Campos</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($audits as $audit)
                    <tr>
                        <td>{{ optional($audit->changed_at)->format('Y-m-d H:i:s') }}</td>
                        <td>{{ optional($audit->user)->name }}<br><small>{{ optional($audit->user)->email }}</small></td>
                        <td>{{ optional($audit->changedBy)->name ?? 'Sistema' }}</td>
                        <td>{{ implode(', ', (array)$audit->changed_fields) }}</td>
                        <td>{{ $audit->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No hay registros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $audits->links() }}
        </div>
    </div>
@stop
