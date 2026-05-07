@extends('adminlte::page')

@section('title', 'Visualizar Asignacion Sivigila')

@section('content_header')
@include('sivigila.mensajes')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center mb-2">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" style="width:42px;height:42px;object-fit:contain;border-radius:10px;background:#fff;padding:3px;" class="mr-2">
        <div>
            <h1 class="m-0" style="font-size:1.3rem;font-weight:800;color:#1f3f47;">Visualizar asignacion Sivigila 113</h1>
            <small class="text-muted">Detalle completo de la asignacion y su auditoria</small>
        </div>
    </div>
    <div class="mb-2">
        @if(!empty($sivigila) && !empty($sivigila->id))
            <a href="{{ route('sivigila.edit', ['sivigila' => $sivigila->id, 'redirect_route' => ($redirectRoute ?? 'sivigila.index')]) }}" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit mr-1"></i> Editar</a>
        @endif
        <a href="{{ route($redirectRoute ?? 'sivigila.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
    </div>
</div>
@stop

@section('content')
<div class="card" style="border-radius:16px;border:1px solid #dce8ec;box-shadow:0 10px 24px rgba(15,23,42,.06);">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 form-group"><label>ID</label><input class="form-control" value="{{ $sivigila->id }}" readonly></div>
            <div class="col-md-3 form-group"><label>Documento</label><input class="form-control" value="{{ $sivigila->num_ide_ }}" readonly></div>
            <div class="col-md-4 form-group"><label>Paciente</label><input class="form-control" value="{{ trim(($sivigila->pri_nom_ ?? '').' '.($sivigila->seg_nom_ ?? '').' '.($sivigila->pri_ape_ ?? '').' '.($sivigila->seg_ape_ ?? '')) }}" readonly></div>
            <div class="col-md-2 form-group"><label>Semana</label><input class="form-control" value="{{ $sivigila->semana }}" readonly></div>
        </div>

        <div class="row">
            <div class="col-md-3 form-group"><label>Fecha notificacion</label><input class="form-control" value="{{ optional($sivigila->fec_not)->format('Y-m-d') }}" readonly></div>
            <div class="col-md-3 form-group"><label>Prestador asignado</label><input class="form-control" value="{{ optional($sivigila->user)->name }}" readonly></div>
            <div class="col-md-3 form-group"><label>Codigo habilitacion</label><input class="form-control" value="{{ optional($sivigila->user)->codigohabilitacion }}" readonly></div>
            <div class="col-md-3 form-group"><label>Estado</label><input class="form-control" value="{{ (int)$sivigila->estado === 1 ? 'Activo' : 'Inactivo' }}" readonly></div>
        </div>

        <div class="row">
            <div class="col-md-4 form-group"><label>Caso confirmado etiologia primaria</label><input class="form-control" value="{{ $sivigila->Caso_confirmada_desnutricion_etiologia_primaria }}" readonly></div>
            <div class="col-md-3 form-group"><label>Manejo hospitalario</label><input class="form-control" value="{{ $sivigila->nombreips_manejo_hospita }}" readonly></div>
            <div class="col-md-5 form-group"><label>IPS manejo hospitalario</label><input class="form-control" value="{{ $sivigila->Ips_manejo_hospitalario }}" readonly></div>
        </div>

        <div class="row">
            <div class="col-md-3 form-group"><label>Telefono</label><input class="form-control" value="{{ $sivigila->telefono_ }}" readonly></div>
            <div class="col-md-3 form-group"><label>Procesado</label><input class="form-control" value="{{ $hasSeguimiento ? 'SI (tiene seguimiento)' : 'NO' }}" readonly></div>
            <div class="col-md-3 form-group"><label>Creado</label><input class="form-control" value="{{ optional($sivigila->created_at)->format('Y-m-d H:i') }}" readonly></div>
            <div class="col-md-3 form-group"><label>Actualizado</label><input class="form-control" value="{{ optional($sivigila->updated_at)->format('Y-m-d H:i') }}" readonly></div>
        </div>
    </div>
</div>

<div class="card mt-3" style="border-radius:16px;border:1px solid #dce8ec;box-shadow:0 10px 24px rgba(15,23,42,.06);">
    <div class="card-header"><strong>Auditoria de asignacion (quien, que, cuando)</strong></div>
    <div class="card-body p-0">
        <div style="overflow:auto;">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Accion</th>
                        <th>Asignado anterior</th>
                        <th>Asignado nuevo</th>
                        <th>Ejecutado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $a)
                        <tr>
                            <td>{{ optional($a->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ strtoupper($a->action_type ?? '') }}</td>
                            <td>{{ $a->old_assigned_name ?? 'N/A' }}</td>
                            <td>{{ $a->new_assigned_name ?? 'N/A' }}</td>
                            <td>{{ $a->performed_by_user_id }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Sin eventos de auditoria para este registro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
