@extends('adminlte::page')

@section('title', 'Editar Seguimiento - Caso #'.$asignacion->id)
@section('content_header')
  <h1 class="text-info">Editar Seguimiento - Caso #{{ $asignacion->id }}</h1>
@stop

@section('content')
<div class="card shadow-sm">
  <div class="card-body">

    {{-- Mensajes --}}
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- === Resumen del caso asignado (solo lectura) === --}}
    @php
      use Illuminate\Support\Arr;
      use Illuminate\Support\Carbon;

      $fmt = function($v) { return ($v !== null && $v !== '') ? $v : 'N/D'; };
      $nombreCompleto = trim(implode(' ', array_filter([
        $asignacion->pri_nom_ ?? null,
        $asignacion->seg_nom_ ?? null,
        $asignacion->pri_ape_ ?? null,
        $asignacion->seg_ape_ ?? null,
      ])));
    @endphp

    <div class="card mb-3 border-info">
      <div class="card-header bg-info d-flex align-items-center justify-content-between">
        <span><i class="fas fa-id-card-alt mr-1"></i> Resumen del caso asignado</span>
        <button class="btn btn-sm btn-light" type="button" data-toggle="collapse" data-target="#resumenCaso" aria-expanded="true">
          <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div id="resumenCaso" class="collapse show">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Consecutivo (asignación)</small>
              <strong>{{ $fmt($asignacion->id) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Código evento</small>
              <strong>{{ $fmt(data_get($asignacion,'cod_eve')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Semana</small>
              <strong>{{ $fmt(data_get($asignacion,'semana')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Año</small>
              <strong>{{ $fmt(data_get($asignacion,'year')) }}</strong>
            </div>

            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Código prestador</small>
              <strong>{{ $fmt(data_get($asignacion,'cod_pre')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Código subred</small>
              <strong>{{ $fmt(data_get($asignacion,'cod_sub')) }}</strong>
            </div>
            <div class="col-md-6 mb-2">
              <small class="text-muted d-block">Paciente</small>
              <strong>{{ $fmt($nombreCompleto) }}</strong>
            </div>

            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Tipo/Número de ID</small>
              <strong>{{ $fmt($asignacion->tip_ide_ ?? null) }} {{ $fmt($asignacion->num_ide_ ?? null) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Régimen de salud</small>
              <strong>{{ $fmt(data_get($asignacion,'regimen_salud')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Prestador primario</small>
              <strong>{{ $fmt(data_get($asignacion,'prestador_primario')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Teléfono</small>
              <strong>{{ $fmt(data_get($asignacion,'telefono_')) }}</strong>
            </div>

            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Edad</small>
              <strong>{{ $fmt(data_get($asignacion,'edad_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Unidad de medida</small>
              <strong>{{ $fmt(data_get($asignacion,'uni_med_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Sexo</small>
              <strong>{{ $fmt(data_get($asignacion,'sexo_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Nacionalidad</small>
              <strong>{{ $fmt(data_get($asignacion,'nombre_nacionalidad', data_get($asignacion,'nacionali_'))) }}</strong>
            </div>

            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Tipo SS</small>
              <strong>{{ $fmt(data_get($asignacion,'tip_ss_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Código Asegurador</small>
              <strong>{{ $fmt(data_get($asignacion,'cod_ase_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Pertenencia étnica</small>
              <strong>{{ $fmt(data_get($asignacion,'per_etn_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Grupo étnico</small>
              <strong>{{ $fmt(data_get($asignacion,'nom_grupo_')) }}</strong>
            </div>

            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Sem. gestación</small>
              <strong>{{ $fmt(data_get($asignacion,'sem_ges_')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Pueblo indígena</small>
              <strong>{{ $fmt(data_get($asignacion,'gp_indigen')) }}</strong>
            </div>
            <div class="col-md-6 mb-2">
              <small class="text-muted d-block">Evento</small>
              <strong>{{ $fmt(data_get($asignacion,'nom_eve')) }}</strong>
            </div>

            <div class="col-md-4 mb-2">
              <small class="text-muted d-block">UPGD</small>
              <strong>{{ $fmt(data_get($asignacion,'nom_upgd')) }}</strong>
            </div>
            <div class="col-md-4 mb-2">
              <small class="text-muted d-block">Lugar procedencia</small>
              <strong>
                {{ $fmt(data_get($asignacion,'npais_proce')) }}
                {{ $fmt(data_get($asignacion,'ndep_proce')) }}
                {{ $fmt(data_get($asignacion,'nmun_proce')) }}
              </strong>
            </div>
            <div class="col-md-4 mb-2">
              <small class="text-muted d-block">Lugar residencia</small>
              <strong>
                {{ $fmt(data_get($asignacion,'npais_resi')) }}
                {{ $fmt(data_get($asignacion,'ndep_resi')) }}
                {{ $fmt(data_get($asignacion,'nmun_resi')) }}
              </strong>
            </div>

            <div class="col-md-6 mb-2">
              <small class="text-muted d-block">Lugar notificación</small>
              <strong>
                {{ $fmt(data_get($asignacion,'ndep_notif')) }}
                {{ $fmt(data_get($asignacion,'nmun_notif')) }}
              </strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Fecha de notificación</small>
              <strong>{{ $fmt(data_get($asignacion,'fec_not')) }}</strong>
            </div>
            <div class="col-md-3 mb-2">
              <small class="text-muted d-block">Usuario asignado</small>
              <strong>{{ $fmt(optional($asignacion->user)->name ?? null) }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
    {{-- === /Resumen === --}}

    {{-- Helpers locales para el formulario (sin funciones globales) --}}
    @php
      $v = function($name, $val = null) { return old($name, $val); };
      $vdate = function($name, $val = null) {
        $raw = old($name, $val);
        if ($raw === null || $raw === '') return '';
        try { return Carbon::parse($raw)->format('Y-m-d'); }
        catch (\Throwable $e) { return is_string($raw) ? $raw : ''; }
      };
    @endphp

    <form action="{{ route('asignaciones.seguimientmaestrosiv549.update', [$asignacion, $seguimiento]) }}" method="POST">
      @csrf
      @method('PUT')

      {{-- HOSPITALIZACIÓN --}}
      <h5 class="mb-2 text-primary"><i class="fas fa-hospital-user"></i> Hospitalización</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha hospitalización</label>
          <input type="date" name="fecha_hospitalizacion" class="form-control"
                 value="{{ $vdate('fecha_hospitalizacion', $seguimiento->fecha_hospitalizacion) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Fecha egreso</label>
          <input type="date" name="fecha_egreso" class="form-control"
                 value="{{ $vdate('fecha_egreso', $seguimiento->fecha_egreso) }}">
        </div>
        <div class="form-group col-md-6">
          <label>Gestión durante hospitalización</label>
          <textarea name="gestion_hospitalizacion" class="form-control" rows="2">{{ $v('gestion_hospitalizacion', $seguimiento->gestion_hospitalizacion) }}</textarea>
        </div>
      </div>

      {{-- INMEDIATO (48–72h) --}}
      <h5 class="mt-3 mb-2 text-primary"><i class="fas fa-bolt"></i> Seguimiento inmediato (48–72h)</h5>
      <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion_seguimiento_inmediato" class="form-control" rows="2">{{ $v('descripcion_seguimiento_inmediato', $seguimiento->descripcion_seguimiento_inmediato) }}</textarea>
      </div>

      <hr>
      {{-- SEGUIMIENTO 1 --}}
      <h5 class="mb-2 text-primary"><i class="fas fa-phone"></i> Seguimiento 1 (post egreso)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_1" class="form-control"
                 value="{{ $vdate('fecha_seguimiento_1', $seguimiento->fecha_seguimiento_1) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts1 = (string) $v('tipo_seguimiento_1', $seguimiento->tipo_seguimiento_1); @endphp
          <select name="tipo_seguimiento_1" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts1==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts1==='2'?'selected':'' }}>Domiciliario</option>
            <option value="3" {{ $ts1==='3'?'selected':'' }}>Otro</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb1 = (string) $v('paciente_sigue_embarazo_1', $seguimiento->paciente_sigue_embarazo_1); @endphp
          <select name="paciente_sigue_embarazo_1" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb1==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb1==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control (especialista)</label>
          <input type="date" name="fecha_control_1" class="form-control"
                 value="{{ $vdate('fecha_control_1', $seguimiento->fecha_control_1) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Método anticonceptivo elegido y provisto</label>
          <input type="text" name="metodo_anticonceptivo" class="form-control"
                 value="{{ $v('metodo_anticonceptivo', $seguimiento->metodo_anticonceptivo) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_1" class="form-control"
                 value="{{ $vdate('fecha_consulta_rn_1', $seguimiento->fecha_consulta_rn_1) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Entrega de medicamentos/labs en casa (detalle)</label>
          <input type="text" name="entrega_medicamentos_labs_1" class="form-control"
                 value="{{ $v('entrega_medicamentos_labs_1', $seguimiento->entrega_medicamentos_labs_1) }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión realizada post egreso</label>
        <textarea name="gestion_posegreso_1" class="form-control" rows="2">{{ $v('gestion_posegreso_1', $seguimiento->gestion_posegreso_1) }}</textarea>
      </div>

      <hr>
      {{-- SEGUIMIENTO 2 (7 días) --}}
      <h5 class="mb-2 text-primary"><i class="far fa-calendar-check"></i> Seguimiento 2 (7 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_2" class="form-control"
                 value="{{ $vdate('fecha_seguimiento_2', $seguimiento->fecha_seguimiento_2) }}">
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb2 = (string) $v('paciente_sigue_embarazo_2', $seguimiento->paciente_sigue_embarazo_2); @endphp
          <select name="paciente_sigue_embarazo_2" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb2==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb2==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control (especialista)</label>
          <input type="date" name="fecha_control_2" class="form-control"
                 value="{{ $vdate('fecha_control_2', $seguimiento->fecha_control_2) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Fecha consulta RN (si aplica)</label>
          <input type="date" name="fecha_consulta_rn_2" class="form-control"
                 value="{{ $vdate('fecha_consulta_rn_2', $seguimiento->fecha_consulta_rn_2) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_2" class="form-control"
                 value="{{ $v('entrega_medicamentos_labs_2', $seguimiento->entrega_medicamentos_labs_2) }}">
        </div>
        <div class="form-group col-md-6">
          <label>Gestión primera semana</label>
          <input type="text" name="gestion_primera_semana" class="form-control"
                 value="{{ $v('gestion_primera_semana', $seguimiento->gestion_primera_semana) }}">
        </div>
      </div>

      <hr>
      {{-- SEGUIMIENTO 3 (14 días) --}}
      <h5 class="mb-2 text-primary">Seguimiento 3 (14 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_3" class="form-control"
                 value="{{ $vdate('fecha_seguimiento_3', $seguimiento->fecha_seguimiento_3) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts3 = (string) $v('tipo_seguimiento_3', $seguimiento->tipo_seguimiento_3); @endphp
          <select name="tipo_seguimiento_3" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts3==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts3==='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb3 = (string) $v('paciente_sigue_embarazo_3', $seguimiento->paciente_sigue_embarazo_3); @endphp
          <select name="paciente_sigue_embarazo_3" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb3==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb3==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_3" class="form-control"
                 value="{{ $vdate('fecha_control_3', $seguimiento->fecha_control_3) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_3" class="form-control"
                 value="{{ $vdate('fecha_consulta_rn_3', $seguimiento->fecha_consulta_rn_3) }}">
        </div>
        <div class="form-group col-md-8">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_3" class="form-control"
                 value="{{ $v('entrega_medicamentos_labs_3', $seguimiento->entrega_medicamentos_labs_3) }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión segunda semana</label>
        <input type="text" name="gestion_segunda_semana" class="form-control"
               value="{{ $v('gestion_segunda_semana', $seguimiento->gestion_segunda_semana) }}">
      </div>

      <hr>
      {{-- SEGUIMIENTO 4 (21 días) --}}
      <h5 class="mb-2 text-primary">Seguimiento 4 (21 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_4" class="form-control"
                 value="{{ $vdate('fecha_seguimiento_4', $seguimiento->fecha_seguimiento_4) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts4 = (string) $v('tipo_seguimiento_4', $seguimiento->tipo_seguimiento_4); @endphp
          <select name="tipo_seguimiento_4" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts4==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts4==='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb4 = (string) $v('paciente_sigue_embarazo_4', $seguimiento->paciente_sigue_embarazo_4); @endphp
          <select name="paciente_sigue_embarazo_4" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb4==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb4==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_4" class="form-control"
                 value="{{ $vdate('fecha_control_4', $seguimiento->fecha_control_4) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_4" class="form-control"
                 value="{{ $vdate('fecha_consulta_rn_4', $seguimiento->fecha_consulta_rn_4) }}">
        </div>
        <div class="form-group col-md-8">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_4" class="form-control"
                 value="{{ $v('entrega_medicamentos_labs_4', $seguimiento->entrega_medicamentos_labs_4) }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión tercera semana</label>
        <input type="text" name="gestion_tercera_semana" class="form-control"
               value="{{ $v('gestion_tercera_semana', $seguimiento->gestion_tercera_semana) }}">
      </div>

      <hr>
      {{-- SEGUIMIENTO 5 (28 días) --}}
      <h5 class="mb-2 text-primary">Seguimiento 5 (28 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_5" class="form-control"
                 value="{{ $vdate('fecha_seguimiento_5', $seguimiento->fecha_seguimiento_5) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts5 = (string) $v('tipo_seguimiento_5', $seguimiento->tipo_seguimiento_5); @endphp
          <select name="tipo_seguimiento_5" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts5==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts5==='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb5 = (string) $v('paciente_sigue_embarazo_5', $seguimiento->paciente_sigue_embarazo_5); @endphp
          <select name="paciente_sigue_embarazo_5" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb5==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb5==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_5" class="form-control"
                 value="{{ $vdate('fecha_control_5', $seguimiento->fecha_control_5) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label>Fecha consulta RN (si aplica)</label>
          <input type="date" name="fecha_consulta_rn_5" class="form-control"
                 value="{{ $vdate('fecha_consulta_rn_5', $seguimiento->fecha_consulta_rn_5) }}">
        </div>
        <div class="form-group col-md-6">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_5" class="form-control"
                 value="{{ $v('entrega_medicamentos_labs_5', $seguimiento->entrega_medicamentos_labs_5) }}">
        </div>
      </div>

      <hr>
      {{-- CONTROLES ADICIONALES --}}
      <h5 class="mb-2 text-primary"><i class="fas fa-baby"></i> Controles adicionales</h5>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Consulta apoyo lactancia</label>
          <input type="date" name="fecha_consulta_lactancia" class="form-control"
                 value="{{ $vdate('fecha_consulta_lactancia', $seguimiento->fecha_consulta_lactancia) }}">
        </div>
        <div class="form-group col-md-4">
          <label>1er control método anticonceptivo</label>
          <input type="date" name="fecha_control_metodo" class="form-control"
                 value="{{ $vdate('fecha_control_metodo', $seguimiento->fecha_control_metodo) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Gestión después del mes</label>
          <input type="text" name="gestion_despues_mes" class="form-control"
                 value="{{ $v('gestion_despues_mes', $seguimiento->gestion_despues_mes) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label>Consulta 6 meses</label>
          <input type="date" name="fecha_consulta_6_meses" class="form-control"
                 value="{{ $vdate('fecha_consulta_6_meses', $seguimiento->fecha_consulta_6_meses) }}">
        </div>
        <div class="form-group col-md-6">
          <label>Consulta 1 año</label>
          <input type="date" name="fecha_consulta_1_ano" class="form-control"
                 value="{{ $vdate('fecha_consulta_1_ano', $seguimiento->fecha_consulta_1_ano) }}">
        </div>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-warning">
          <i class="fas fa-save"></i> Actualizar
        </button>
        <a href="{{ route('seguimientos.index') }}" class="btn btn-secondary ml-2">
          <i class="fas fa-times"></i> Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@stop
