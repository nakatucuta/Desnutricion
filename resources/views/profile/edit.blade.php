@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
    <h1>Perfil de Usuario</h1>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Datos del Perfil</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Correo</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="codigohabilitacion">Codigo de Habilitacion / Usuario</label>
                            <input id="codigohabilitacion" name="codigohabilitacion" type="text" class="form-control @error('codigohabilitacion') is-invalid @enderror" value="{{ old('codigohabilitacion', $user->codigohabilitacion) }}">
                            @error('codigohabilitacion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Cambiar Contrasena</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="current_password">Contrasena Actual</label>
                            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Nueva Contrasena</label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                            <small class="text-muted">Minimo 10 caracteres, con mayuscula, minuscula, numero y simbolo.</small>
                            @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nueva Contrasena</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary" type="submit">Actualizar Contrasena</button>
                    </form>
                </div>
            </div>

            @if($canViewAudit)
                <div class="mt-2">
                    <a href="{{ route('profile.audit') }}" class="btn btn-outline-primary">Ver Auditoria de Cambios de Perfil</a>
                </div>
            @endif
        </div>
    </div>
@stop
