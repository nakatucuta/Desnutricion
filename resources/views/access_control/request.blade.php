@extends('adminlte::page')

@section('title', 'Solicitar acceso')

@section('content_header')
    <h1><i class="fas fa-key mr-2"></i>Solicitar acceso a módulos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if (session('error1'))
                <div class="alert alert-danger">{{ session('error1') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if ($permissions->isEmpty())
                <div class="alert alert-info mb-3">
                    No tienes módulos pendientes por solicitar o ya cuentas con los accesos necesarios.
                </div>
            @else
                <form method="POST" action="{{ route('access-requests.store') }}">
                    @csrf
                    <input type="hidden" name="from" value="{{ $from }}">

                    <div class="form-group">
                        <label for="permission_code">Módulo</label>
                        <select name="permission_code" id="permission_code" class="form-control" required>
                            <option value="">Selecciona un módulo</option>
                            @foreach ($permissions as $permission)
                                <option value="{{ $permission->code }}" {{ old('permission_code') === $permission->code ? 'selected' : '' }}>
                                    {{ $permission->name }}
                                    @if(isset($pendingByPermission[$permission->id]))
                                        (solicitud pendiente #{{ $pendingByPermission[$permission->id] }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="requested_reason">Motivo (opcional)</label>
                        <textarea id="requested_reason" name="requested_reason" rows="4" class="form-control"
                            placeholder="Describe por qué necesitas este módulo.">{{ old('requested_reason') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i>Enviar solicitud
                    </button>
                </form>
            @endif
        </div>
    </div>
@stop
