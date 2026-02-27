@extends('adminlte::page')

@section('title', 'Auditoria de Novedad')

@section('content_header')
    <h1>Auditoria de Lectura</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ $novedad->title }}</h3>
                    <a href="{{ route('novedades.audit.pdf', $novedad->id) }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
                    </a>
                </div>
                <div class="card-body">
                    <p class="mb-2">{{ $novedad->message }}</p>
                    <small class="text-muted">
                        Publicada: {{ optional($novedad->created_at)->format('Y-m-d H:i') }}
                    </small>
                    <hr>
                    <div class="d-flex flex-wrap" style="gap:1rem;">
                        <span class="badge badge-info">Total usuarios: {{ $totalUsers }}</span>
                        <span class="badge badge-success">Leida: {{ $totalReads }}</span>
                        <span class="badge badge-warning">Pendiente: {{ $totalPending }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Usuarios que leyeron ({{ $reads->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Fecha lectura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reads as $read)
                                    <tr>
                                        <td>{{ optional($read->user)->name ?? 'N/D' }}</td>
                                        <td>{{ optional($read->user)->email ?? 'N/D' }}</td>
                                        <td>{{ optional($read->read_at)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Nadie la ha leido aun.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Usuarios pendientes ({{ $notReadUsers->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Codigo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notReadUsers as $u)
                                    <tr>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td>{{ $u->codigohabilitacion }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Todos los usuarios la han leido.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
