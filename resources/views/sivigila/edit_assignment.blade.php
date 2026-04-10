@extends('adminlte::page')

@section('title', 'Editar Asignacion Sivigila')

@section('content_header')
@include('sivigila.mensajes')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center mb-2">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" style="width:42px;height:42px;object-fit:contain;border-radius:10px;background:#fff;padding:3px;" class="mr-2">
        <div>
            <h1 class="m-0" style="font-size:1.3rem;font-weight:800;color:#1f3f47;">Editar asignacion Sivigila 113</h1>
            <small class="text-muted">Actualiza responsable y datos clave de la asignacion</small>
        </div>
    </div>
    <a href="{{ route('sivigila.index') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
</div>
@stop

@section('content')
<div class="card" style="border-radius:16px;border:1px solid #dce8ec;box-shadow:0 10px 24px rgba(15,23,42,.06);">
    <div class="card-body">
        <form method="POST" action="{{ route('sivigila.update', $sivigila->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Documento</label>
                    <input type="text" class="form-control" value="{{ $sivigila->num_ide_ }}" readonly>
                </div>
                <div class="col-md-4 form-group">
                    <label>Paciente</label>
                    <input type="text" class="form-control" value="{{ trim(($sivigila->pri_nom_ ?? '').' '.($sivigila->seg_nom_ ?? '').' '.($sivigila->pri_ape_ ?? '').' '.($sivigila->seg_ape_ ?? '')) }}" readonly>
                </div>
                <div class="col-md-2 form-group">
                    <label>Fecha notificacion</label>
                    <input type="text" class="form-control" value="{{ optional($sivigila->fec_not)->format('Y-m-d') }}" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>Estado</label>
                    <select name="estado" class="form-control">
                        <option value="1" {{ (int) old('estado', $sivigila->estado) === 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ (int) old('estado', $sivigila->estado) === 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5 form-group">
                    <label>Prestador asignado</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Seleccione...</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" {{ (string) old('user_id', $sivigila->user_id) === (string) $u->id ? 'selected' : '' }}>
                                {{ trim(($u->codigohabilitacion ?? '').' - '.$u->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Telefono</label>
                    <input type="text" name="telefono_" class="form-control" value="{{ old('telefono_', $sivigila->telefono_) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label>Caso confirmado etiologia primaria</label>
                    <select name="Caso_confirmada_desnutricion_etiologia_primaria" class="form-control" required>
                        <option value="SI APLICA" {{ old('Caso_confirmada_desnutricion_etiologia_primaria', $sivigila->Caso_confirmada_desnutricion_etiologia_primaria) === 'SI APLICA' ? 'selected' : '' }}>SI APLICA</option>
                        <option value="NO APLICA" {{ old('Caso_confirmada_desnutricion_etiologia_primaria', $sivigila->Caso_confirmada_desnutricion_etiologia_primaria) === 'NO APLICA' ? 'selected' : '' }}>NO APLICA</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Manejo hospitalario</label>
                    <select name="nombreips_manejo_hospita" id="nombreips_manejo_hospita" class="form-control" required>
                        <option value="SI" {{ old('nombreips_manejo_hospita', $sivigila->nombreips_manejo_hospita) === 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ old('nombreips_manejo_hospita', $sivigila->nombreips_manejo_hospita) === 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>
                <div class="col-md-8 form-group" id="ips_manejo_group">
                    <label>IPS manejo hospitalario</label>
                    <input type="text" name="Ips_manejo_hospitalario" id="Ips_manejo_hospitalario" class="form-control" value="{{ old('Ips_manejo_hospitalario', $sivigila->Ips_manejo_hospitalario) }}">
                </div>
            </div>

            <div class="d-flex" style="gap:.6rem;">
                <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
                <a href="{{ route('sivigila.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    (function () {
        const manejo = document.getElementById('nombreips_manejo_hospita');
        const grp = document.getElementById('ips_manejo_group');
        const ips = document.getElementById('Ips_manejo_hospitalario');
        const toggle = function () {
            const show = (manejo.value || '').toUpperCase() === 'SI';
            grp.style.display = show ? '' : 'none';
            ips.required = show;
            if (!show) ips.value = '';
        };
        manejo.addEventListener('change', toggle);
        toggle();
    })();
</script>
@stop

