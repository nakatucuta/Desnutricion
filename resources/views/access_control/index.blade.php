@extends('adminlte::page')

@section('title', 'Permisos de Usuarios')
@section('plugins.Datatables', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0"><i class="fas fa-user-shield mr-2"></i>Permisos de Usuarios</h1>
        <span class="badge badge-danger">Solo superadministrador</span>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Asignar o quitar permisos por usuario</strong></div>
        <div class="card-body table-responsive p-0">
            <table id="users-permissions-table" class="table table-sm table-bordered mb-0" style="width:100%;">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Codigo</th>
                        <th>Tipo</th>
                        <th>Permisos</th>
                        <th style="width: 130px;">Accion</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Solicitudes pendientes de acceso</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Modulo</th>
                        <th>Motivo</th>
                        <th style="width: 280px;">Gestion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingRequests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>{{ optional($req->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $req->user->name ?? 'N/A' }}<br><small>{{ $req->user->email ?? '' }}</small></td>
                            <td>{{ $req->modulePermission->name ?? $req->modulePermission->code ?? 'N/A' }}</td>
                            <td>{{ $req->requested_reason ?: 'Sin motivo' }}</td>
                            <td>
                                <form method="POST" action="{{ route('access-control.requests.resolve', $req) }}" class="form-inline mb-1">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <input type="text" name="admin_response" class="form-control form-control-sm mr-2"
                                        placeholder="Respuesta opcional">
                                    <button type="submit" class="btn btn-sm btn-success">Aprobar</button>
                                </form>
                                <form method="POST" action="{{ route('access-control.requests.resolve', $req) }}" class="form-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <input type="text" name="admin_response" class="form-control form-control-sm mr-2"
                                        placeholder="Motivo de rechazo">
                                    <button type="submit" class="btn btn-sm btn-danger">Rechazar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay solicitudes pendientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    (function () {
        $('#users-permissions-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            searchDelay: 250,
            ajax: '{{ route('access-control.data') }}',
            order: [[0, 'desc']],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'user_label', name: 'user_label', orderable: false, searchable: true },
                { data: 'email', name: 'email' },
                { data: 'codigohabilitacion', name: 'codigohabilitacion' },
                { data: 'usertype', name: 'usertype' },
                { data: 'permissions_html', name: 'permissions_html', orderable: false, searchable: false },
                { data: 'action_html', name: 'action_html', orderable: false, searchable: false }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
        });

        const wrapper = $('#users-permissions-table_wrapper');
        wrapper.find('.dataTables_filter input')
            .attr('placeholder', 'Buscar por nombre, correo o codigo...')
            .addClass('form-control form-control-sm');
        wrapper.find('.dataTables_length select').addClass('form-control form-control-sm');
    })();
</script>
@stop
