@extends('adminlte::page')

@section('title', 'Nuevo Seguimiento - Caso #'.$asignacion->id)
@section('content_header')
  <h1 class="text-info">Nuevo Seguimiento - Caso #{{ $asignacion->id }}</h1>
@stop

@section('content')
<style>
  .case-summary .label { font-size:.75rem; color:#6c757d; margin-bottom:.15rem; }
  .case-summary .value { font-weight:600; }
  .case-summary .item { border:1px solid #e9ecef; border-radius:.5rem; padding:.5rem .75rem; background:#fff; height:100%; }
  .case-summary .row > [class*="col-"] { margin-bottom:.5rem; }
</style>

{{-- ===================== RESUMEN SOLO LECTURA ===================== --}}
<div class="card border-info mb-3">
  <div class="card-header bg-info text-white">
    <i class="fas fa-id-card-alt"></i> Resumen del caso asignado
  </div>
  <div class="card-body case-summary">
    <div class="mb-3">
      <span class="badge badge-primary mr-2">
        <i class="fas fa-clinic-medical"></i>
        Prestador primario:
      </span>
      <strong>{{ optional($asignacion->user)->name ?? 'N/D' }}</strong>
      @if(optional($asignacion->asignacion)->cod_pre)
        <span class="text-muted"> — {{ optional($asignacion->user)->codigohabilitacion }}</span>
      @endif
      {{-- @if(optional($asignacion->user)->email)
        <span class="text-muted d-block"><i class="far fa-envelope"></i> {{ $asignacion->user->email }}</span>
      @endif --}}
    </div>

    <div class="row">
      {{-- 1 --}}
      <div class="col-md-2"><div class="item">
        <div class="label">Consecutivo</div>
        <div class="value">{{ data_get($asignacion,'consecutivo','—') }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Cod. evento</div>
        <div class="value">{{ $asignacion->cod_eve ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Semana</div>
        <div class="value">{{ $asignacion->semana ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Año</div>
        <div class="value">{{ $asignacion->year ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Cod. prestador</div>
        <div class="value">{{ $asignacion->cod_pre ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Cod. sub</div>
        <div class="value">{{ $asignacion->cod_sub ?? '—' }}</div>
      </div></div>

      {{-- 2 Nombres / Identificación --}}
      <div class="col-md-3"><div class="item">
        <div class="label">Primer nombre</div>
        <div class="value">{{ $asignacion->pri_nom_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Segundo nombre</div>
        <div class="value">{{ $asignacion->seg_nom_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Primer apellido</div>
        <div class="value">{{ $asignacion->pri_ape_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Segundo apellido</div>
        <div class="value">{{ $asignacion->seg_ape_ ?? '—' }}</div>
      </div></div>

      <div class="col-md-2"><div class="item">
        <div class="label">Tipo ID</div>
        <div class="value">{{ $asignacion->tip_ide_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Número ID</div>
        <div class="value">{{ $asignacion->num_ide_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Teléfono</div>
        <div class="value">{{ $asignacion->telefono_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Sexo</div>
        <div class="value">{{ $asignacion->sexo_ ?? '—' }}</div>
      </div></div>

      {{-- 3 Afiliación / Grupo / Edad --}}
      <div class="col-md-3"><div class="item">
        <div class="label">Régimen de salud (tip_ss_)</div>
        <div class="value">{{ $asignacion->tip_ss_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Código asegurador</div>
        <div class="value">{{ $asignacion->cod_ase_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Pertenencia étnica (per_etn_)</div>
        <div class="value">{{ $asignacion->per_etn_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-3"><div class="item">
        <div class="label">Grupo étnico</div>
        <div class="value">{{ $asignacion->nom_grupo_ ?? '—' }}</div>
      </div></div>

      <div class="col-md-2"><div class="item">
        <div class="label">Edad</div>
        <div class="value">{{ $asignacion->edad_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Unidad edad</div>
        <div class="value">{{ $asignacion->uni_med_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Sem. gestación</div>
        <div class="value">{{ $asignacion->sem_ges ?? $asignacion->sem_ges_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Indígena (gp_indigen)</div>
        <div class="value">{{ $asignacion->gp_indigen ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Nacionalidad (cod)</div>
        <div class="value">{{ $asignacion->nacionali_ ?? '—' }}</div>
      </div></div>
      <div class="col-md-2"><div class="item">
        <div class="label">Nacionalidad (nombre)</div>
        <div class="value">{{ $asignacion->nombre_nacionalidad ?? '—' }}</div>
      </div></div>

      {{-- 4 Evento / UPGD --}}
      <div class="col-md-6"><div class="item">
        <div class="label">Evento</div>
        <div class="value">{{ $asignacion->nom_eve ?? '—' }}</div>
      </div></div>
      <div class="col-md-6"><div class="item">
        <div class="label">UPGD</div>
        <div class="value">{{ $asignacion->nom_upgd ?? '—' }}</div>
      </div></div>

      {{-- 5 Procedencia / Residencia / Notificación --}}
      <div class="col-md-4"><div class="item">
        <div class="label">País procedencia</div>
        <div class="value">{{ $asignacion->npais_proce ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Depto procedencia</div>
        <div class="value">{{ $asignacion->ndep_proce ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Mun. procedencia</div>
        <div class="value">{{ $asignacion->nmun_proce ?? '—' }}</div>
      </div></div>

      <div class="col-md-4"><div class="item">
        <div class="label">País residencia</div>
        <div class="value">{{ $asignacion->npais_resi ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Depto residencia</div>
        <div class="value">{{ $asignacion->ndep_resi ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Mun. residencia</div>
        <div class="value">{{ $asignacion->nmun_resi ?? '—' }}</div>
      </div></div>

      <div class="col-md-4"><div class="item">
        <div class="label">Depto notificación</div>
        <div class="value">{{ $asignacion->ndep_notif ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Mun. notificación</div>
        <div class="value">{{ $asignacion->nmun_notif ?? '—' }}</div>
      </div></div>
      <div class="col-md-4"><div class="item">
        <div class="label">Fecha/Hora notificación</div>
        <div class="value">{{ $asignacion->FechaHora ?? '—' }}</div>
      </div></div>
    </div>
  </div>
</div>

{{-- ===================== FORMULARIO DE SEGUIMIENTO ===================== --}}
<div class="card shadow-sm">
  <div class="card-body">
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('asignaciones.seguimientmaestrosiv549.store', $asignacion) }}" method="POST">
      @csrf

      <h5 class="mb-2 text-primary"><i class="fas fa-hospital-user"></i> Hospitalización</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha hospitalización</label>
          <input type="date" name="fecha_hospitalizacion" class="form-control" value="{{ old('fecha_hospitalizacion') }}">
        </div>
        <div class="form-group col-md-3">
          <label>Fecha egreso</label>
          <input type="date" name="fecha_egreso" class="form-control" value="{{ old('fecha_egreso') }}">
        </div>
        <div class="form-group col-md-6">
          <label>Gestión durante hospitalización</label>
          <textarea name="gestion_hospitalizacion" class="form-control" rows="2">{{ old('gestion_hospitalizacion') }}</textarea>
        </div>
      </div>

      <h5 class="mt-3 mb-2 text-primary"><i class="fas fa-bolt"></i> Seguimiento inmediato (48–72h)</h5>
      <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion_seguimiento_inmediato" class="form-control" rows="2">{{ old('descripcion_seguimiento_inmediato') }}</textarea>
      </div>

      <hr>
      <h5 class="mb-2 text-primary"><i class="fas fa-phone"></i> Seguimiento 1 (post egreso)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_1" class="form-control" value="{{ old('fecha_seguimiento_1') }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          <select name="tipo_seguimiento_1" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('tipo_seguimiento_1')=='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ old('tipo_seguimiento_1')=='2'?'selected':'' }}>Domiciliario</option>
            <option value="3" {{ old('tipo_seguimiento_1')=='3'?'selected':'' }}>Otro</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          <select name="paciente_sigue_embarazo_1" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('paciente_sigue_embarazo_1')==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ old('paciente_sigue_embarazo_1')==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control (especialista)</label>
          <input type="date" name="fecha_control_1" class="form-control" value="{{ old('fecha_control_1') }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Método anticonceptivo elegido y provisto</label>
          <input type="text" name="metodo_anticonceptivo" class="form-control" value="{{ old('metodo_anticonceptivo') }}">
        </div>
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_1" class="form-control" value="{{ old('fecha_consulta_rn_1') }}">
        </div>
        <div class="form-group col-md-4">
          <label>Entrega de medicamentos/labs en casa (detalle)</label>
          <input type="text" name="entrega_medicamentos_labs_1" class="form-control" value="{{ old('entrega_medicamentos_labs_1') }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión realizada post egreso</label>
        <textarea name="gestion_posegreso_1" class="form-control" rows="2">{{ old('gestion_posegreso_1') }}</textarea>
      </div>

      <hr>
      <h5 class="mb-2 text-primary"><i class="far fa-calendar-check"></i> Seguimiento 2 (7 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_2" class="form-control" value="{{ old('fecha_seguimiento_2') }}">
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          <select name="paciente_sigue_embarazo_2" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('paciente_sigue_embarazo_2')==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ old('paciente_sigue_embarazo_2')==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control (especialista)</label>
          <input type="date" name="fecha_control_2" class="form-control" value="{{ old('fecha_control_2') }}">
        </div>
        <div class="form-group col-md-3">
          <label>Fecha consulta RN (si aplica)</label>
          <input type="date" name="fecha_consulta_rn_2" class="form-control" value="{{ old('fecha_consulta_rn_2') }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_2" class="form-control" value="{{ old('entrega_medicamentos_labs_2') }}">
        </div>
        <div class="form-group col-md-6">
          <label>Gestión primera semana</label>
          <input type="text" name="gestion_primera_semana" class="form-control" value="{{ old('gestion_primera_semana') }}">
        </div>
      </div>

      <hr>
      <h5 class="mb-2 text-primary">Seguimiento 3 (14 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_3" class="form-control" value="{{ old('fecha_seguimiento_3') }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          <select name="tipo_seguimiento_3" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('tipo_seguimiento_3')=='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ old('tipo_seguimiento_3')=='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          <select name="paciente_sigue_embarazo_3" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('paciente_sigue_embarazo_3')==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ old('paciente_sigue_embarazo_3')==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_3" class="form-control" value="{{ old('fecha_control_3') }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_3" class="form-control" value="{{ old('fecha_consulta_rn_3') }}">
        </div>
        <div class="form-group col-md-8">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_3" class="form-control" value="{{ old('entrega_medicamentos_labs_3') }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión segunda semana</label>
        <input type="text" name="gestion_segunda_semana" class="form-control" value="{{ old('gestion_segunda_semana') }}">
      </div>

      <hr>
      <h5 class="mb-2 text-primary">Seguimiento 4 (21 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_4" class="form-control" value="{{ old('fecha_seguimiento_4') }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          <select name="tipo_seguimiento_4" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('tipo_seguimiento_4')=='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ old('tipo_seguimiento_4')=='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          <select name="paciente_sigue_embarazo_4" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('paciente_sigue_embarazo_4')==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ old('paciente_sigue_embarazo_4')==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_4" class="form-control" value="{{ old('fecha_control_4') }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_4" class="form-control" value="{{ old('fecha_consulta_rn_4') }}">
        </div>
        <div class="form-group col-md-8">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_4" class="form-control" value="{{ old('entrega_medicamentos_labs_4') }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión tercera semana</label>
        <input type="text" name="gestion_tercera_semana" class="form-control" value="{{ old('gestion_tercera_semana') }}">
      </div>

      <hr>
      <h5 class="mb-2 text-primary">Seguimiento 5 (28 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_5" class="form-control" value="{{ old('fecha_seguimiento_5') }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          <select name="tipo_seguimiento_5" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('tipo_seguimiento_5')=='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ old('tipo_seguimiento_5')=='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          <select name="paciente_sigue_embarazo_5" class="form-control">
            <option value="">--</option>
            <option value="1" {{ old('paciente_sigue_embarazo_5')==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ old('paciente_sigue_embarazo_5')==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_5" class="form-control" value="{{ old('fecha_control_5') }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN (si aplica)</label>
          <input type="date" name="fecha_consulta_rn_5" class="form-control" value="{{ old('fecha_consulta_rn_5') }}">
        </div>
        <div class="form-group col-md-8">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_5" class="form-control" value="{{ old('entrega_medicamentos_labs_5') }}">
        </div>
      </div>

      <hr>
      <h5 class="mb-2 text-primary"><i class="fas fa-baby"></i> Controles adicionales</h5>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Consulta apoyo lactancia</label>
          <input type="date" name="fecha_consulta_lactancia" class="form-control" value="{{ old('fecha_consulta_lactancia') }}">
        </div>
        <div class="form-group col-md-4">
          <label>1er control método anticonceptivo</label>
          <input type="date" name="fecha_control_metodo" class="form-control" value="{{ old('fecha_control_metodo') }}">
        </div>
        <div class="form-group col-md-4">
          <label>Gestión después del mes</label>
          <input type="text" name="gestion_despues_mes" class="form-control" value="{{ old('gestion_despues_mes') }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label>Consulta 6 meses</label>
          <input type="date" name="fecha_consulta_6_meses" class="form-control" value="{{ old('fecha_consulta_6_meses') }}">
        </div>
        <div class="form-group col-md-6">
          <label>Consulta 1 año</label>
          <input type="date" name="fecha_consulta_1_ano" class="form-control" value="{{ old('fecha_consulta_1_ano') }}">
        </div>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-success" title="Guardar seguimiento">
          <i class="fas fa-save"></i>
        </button>
        <a href="{{ route('seguimientos.index', $asignacion) }}" class="btn btn-secondary ml-2" title="Cancelar">
          <i class="fas fa-times"></i>
        </a>
      </div>
    </form>
  </div>
</div>
@stop
