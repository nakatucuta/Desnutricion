@extends('adminlte::page')

@section('title', 'Acceso denegado')

@section('content_header')
    <h1><i class="fas fa-lock mr-2 text-danger"></i>Acceso denegado</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p class="mb-2">No tienes permiso para ingresar a este modulo.</p>

            @if (!empty($permission?->name))
                <p class="mb-3">
                    <strong>Modulo:</strong> {{ $permission->name }}
                    @if (!empty($permission->description))
                        <br><small class="text-muted">{{ $permission->description }}</small>
                    @endif
                </p>
            @endif

            @if (session('error1'))
                <div class="alert alert-warning">{{ session('error1') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($service->isGesExclusiveUser(auth()->user()) && $permissionCode !== \App\Services\AccessControlService::GESTANTES_ACCESS)
                <div class="alert alert-danger mb-3">
                    Tu usuario es de tipo <strong>_ges</strong> y solo puede usar modulos de gestantes.
                </div>
            @else
                <form method="POST" action="{{ route('access-requests.store') }}" class="mb-3">
                    @csrf
                    <input type="hidden" name="permission_code" value="{{ $permissionCode }}">
                    <input type="hidden" name="from" value="{{ $from }}">

                    <div class="form-group">
                        <label for="requested_reason">Motivo de solicitud (opcional)</label>
                        <textarea id="requested_reason" name="requested_reason" rows="3" class="form-control"
                            placeholder="Describe por que necesitas acceso a este modulo.">{{ old('requested_reason') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i>Solicitar acceso
                    </button>
                </form>
            @endif

            <a href="{{ $from }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
            <a href="{{ route('access-requests.create', ['from' => $from]) }}" class="btn btn-outline-primary ml-2">
                <i class="fas fa-key mr-1"></i>Solicitar otro módulo
            </a>
        </div>
    </div>
@stop
