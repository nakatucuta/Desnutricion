@extends('adminlte::page')

@section('title', 'Nuevo Seguimiento - Caso #'.$asignacion->id)
@section('content_header')
  <h1 class="text-info">Nuevo Seguimiento - Caso #{{ $asignacion->id }}</h1>
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

    {{-- Cabecera del caso (solo lectura) --}}
    <div class="mb-3 p-3 border rounded bg-light">
      <div class="row">
        <div class="col-md-3"><strong>Identificación:</strong> {{ $asignacion->tip_ide_ }} {{ $asignacion->num_ide_ }}</div>
        <div class="col-md-5"><strong>Paciente:</strong> {{ trim("{$asignacion->pri_nom_} {$asignacion->seg_nom_} {$asignacion->pri_ape_} {$asignacion->seg_ape_}") }}</div>
        <div class="col-md-2"><strong>Fec. Notif:</strong> {{ $asignacion->fec_not }}</div>
        <div class="col-md-2"><strong>Evento:</strong> {{ $asignacion->nom_eve }}</div>
      </div>
    </div>

    <form action="{{ route('asignaciones.seguimientmaestrosiv549.store', $asignacion) }}" method="POST">
      @csrf

      {{-- ======================= HOSPITALIZACIÓN ======================= --}}
      <h5 class="mb-2 text-primary"><i class="fas fa-hospital-user"></i> Hospitalización</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha hospitalización</label>
          <input type="date" name="fecha_hospitalizacion" class="form-control"
                 value="{{ old('fecha_hospitalizacion', optional($seguimiento)->fecha_hospitalizacion) }}">
        </div>

        <div class="form-group col-md-3">
          <label>Fecha egreso</label>
          <input type="date" name="fecha_egreso" class="form-control"
                 value="{{ old('fecha_egreso', optional($seguimiento)->fecha_egreso) }}">
        </div>

        <div class="form-group col-md-6">
          <label>Institución que da el egreso a la paciente</label>
          <input type="text" name="institucion_egreso_paciente" class="form-control"
                 value="{{ old('institucion_egreso_paciente', optional($seguimiento)->institucion_egreso_paciente) }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-12">
          <label>Gestión durante hospitalización</label>
          <textarea name="gestion_hospitalizacion" class="form-control" rows="2">{{ old('gestion_hospitalizacion', optional($seguimiento)->gestion_hospitalizacion) }}</textarea>
        </div>
      </div>

      <h6 class="mt-2 text-secondary">Criterios / complicaciones</h6>
      @php
        $crit = [
          'eclampsia' => 'Eclampsia',
          'preeclampsia_severa' => 'Preeclampsia severa',
          'sepsis_infeccion_sistemica_severa' => 'Sepsis o infección sistémica severa',
          'hemorragia_obstetrica_severa' => 'Hemorragia obstétrica severa',
          'ruptura_uterina' => 'Ruptura uterina',
          'falla_cardiovascular' => 'Falla cardiovascular',
          'falla_renal' => 'Falla renal',
          'falla_hepatica' => 'Falla hepática',
          'falla_cerebral' => 'Falla cerebral',
          'falla_respiratoria' => 'Falla respiratoria',
          'falla_coagulacion' => 'Falla coagulación',
          'cirugia_adicional' => 'Cirugía adicional',
        ];
      @endphp

      <div class="row">
        @foreach($crit as $name => $label)
          @php
            $checked = old($name, (bool) optional($seguimiento)->{$name});
          @endphp
          <div class="form-group col-md-3">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="{{ $name }}" name="{{ $name }}" {{ $checked ? 'checked' : '' }}>
              <label class="custom-control-label" for="{{ $name }}">{{ $label }}</label>
            </div>
          </div>
        @endforeach
      </div>

      <div class="row">
        <div class="form-group col-md-3">
          <label>Ttl_criter</label>
          <input type="number" min="0" max="50" name="ttl_criter" class="form-control"
                 value="{{ old('ttl_criter', optional($seguimiento)->ttl_criter) }}">
        </div>

        <div class="form-group col-md-3">
          <label>Diagnóstico CIE 10</label>
          <input type="text" name="diagnostico_cie10" class="form-control"
                 value="{{ old('diagnostico_cie10', optional($seguimiento)->diagnostico_cie10) }}">
        </div>

        <div class="form-group col-md-6">
          <label>Causa agrupada</label>
          <input type="text" name="causa_agrupada" class="form-control"
                 value="{{ old('causa_agrupada', optional($seguimiento)->causa_agrupada) }}">
        </div>
      </div>

      {{-- ======================= SEGUIMIENTO INMEDIATO ======================= --}}
      <h5 class="mt-3 mb-2 text-primary"><i class="fas fa-bolt"></i> Seguimiento inmediato (48–72h)</h5>

      <div class="row">
        <div class="form-group col-md-8">
          <label>Descripción</label>
          <textarea name="descripcion_seguimiento_inmediato" class="form-control" rows="2">{{ old('descripcion_seguimiento_inmediato', optional($seguimiento)->descripcion_seguimiento_inmediato) }}</textarea>
        </div>

        <div class="form-group col-md-2">
          <label>Control del recién nacido (sin pérdida perinatal)</label>
          <input type="date" name="fecha_control_rn_inmediato" class="form-control"
                 value="{{ old('fecha_control_rn_inmediato', optional($seguimiento)->fecha_control_rn_inmediato) }}">
        </div>

        <div class="form-group col-md-2">
          <label>¿Seguimiento efectivo?</label><br>
          @php $seInm = old('seguimiento_efectivo_inmediato', (bool) optional($seguimiento)->seguimiento_efectivo_inmediato); @endphp
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="seguimiento_efectivo_inmediato" name="seguimiento_efectivo_inmediato" {{ $seInm ? 'checked' : '' }}>
            <label class="custom-control-label" for="seguimiento_efectivo_inmediato">Sí</label>
          </div>
        </div>
      </div>

      <hr>

      {{-- ======================= SEGUIMIENTO 1 ======================= --}}
      <h5 class="mb-2 text-primary"><i class="fas fa-phone"></i> Seguimiento 1 (post egreso)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_1" class="form-control"
                 value="{{ old('fecha_seguimiento_1', optional($seguimiento)->fecha_seguimiento_1) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          <select name="tipo_seguimiento_1" class="form-control">
            @php $ts1 = (string) old('tipo_seguimiento_1', optional($seguimiento)->tipo_seguimiento_1); @endphp
            <option value="">--</option>
            <option value="1" {{ $ts1==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts1==='2'?'selected':'' }}>Domiciliario</option>
            <option value="3" {{ $ts1==='3'?'selected':'' }}>Otro</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb1 = (string) old('paciente_sigue_embarazo_1', optional($seguimiento)->paciente_sigue_embarazo_1); @endphp
          <select name="paciente_sigue_embarazo_1" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb1==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb1==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control (especialista)</label>
          <input type="date" name="fecha_control_1" class="form-control"
                 value="{{ old('fecha_control_1', optional($seguimiento)->fecha_control_1) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Método anticonceptivo elegido y provisto</label>
          <input type="text" name="metodo_anticonceptivo" class="form-control"
                 value="{{ old('metodo_anticonceptivo', optional($seguimiento)->metodo_anticonceptivo) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_1" class="form-control"
                 value="{{ old('fecha_consulta_rn_1', optional($seguimiento)->fecha_consulta_rn_1) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Entrega de medicamentos/labs en casa (detalle)</label>
          <input type="text" name="entrega_medicamentos_labs_1" class="form-control"
                 value="{{ old('entrega_medicamentos_labs_1', optional($seguimiento)->entrega_medicamentos_labs_1) }}">
        </div>
      </div>
      <div class="form-group">
        <label>Gestión realizada post egreso</label>
        <textarea name="gestion_posegreso_1" class="form-control" rows="2">{{ old('gestion_posegreso_1', optional($seguimiento)->gestion_posegreso_1) }}</textarea>
      </div>

      <hr>

      {{-- ======================= SEGUIMIENTO 2 ======================= --}}
      <h5 class="mb-2 text-primary"><i class="far fa-calendar-check"></i> Seguimiento 2 (7 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_2" class="form-control"
                 value="{{ old('fecha_seguimiento_2', optional($seguimiento)->fecha_seguimiento_2) }}">
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb2 = (string) old('paciente_sigue_embarazo_2', optional($seguimiento)->paciente_sigue_embarazo_2); @endphp
          <select name="paciente_sigue_embarazo_2" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb2==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb2==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control (especialista)</label>
          <input type="date" name="fecha_control_2" class="form-control"
                 value="{{ old('fecha_control_2', optional($seguimiento)->fecha_control_2) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Fecha consulta RN (si aplica)</label>
          <input type="date" name="fecha_consulta_rn_2" class="form-control"
                 value="{{ old('fecha_consulta_rn_2', optional($seguimiento)->fecha_consulta_rn_2) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_2" class="form-control"
                 value="{{ old('entrega_medicamentos_labs_2', optional($seguimiento)->entrega_medicamentos_labs_2) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Gestión primera semana</label>
          <input type="text" name="gestion_primera_semana" class="form-control"
                 value="{{ old('gestion_primera_semana', optional($seguimiento)->gestion_primera_semana) }}">
        </div>
        <div class="form-group col-md-4">
          <label>¿Seguimiento efectivo?</label><br>
          @php $se2 = old('seguimiento_efectivo_2', (bool) optional($seguimiento)->seguimiento_efectivo_2); @endphp
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="seguimiento_efectivo_2" name="seguimiento_efectivo_2" {{ $se2 ? 'checked' : '' }}>
            <label class="custom-control-label" for="seguimiento_efectivo_2">Sí</label>
          </div>
        </div>
      </div>

      <hr>

      {{-- ======================= SEGUIMIENTO 3 ======================= --}}
      <h5 class="mb-2 text-primary">Seguimiento 3 (14 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_3" class="form-control"
                 value="{{ old('fecha_seguimiento_3', optional($seguimiento)->fecha_seguimiento_3) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts3 = (string) old('tipo_seguimiento_3', optional($seguimiento)->tipo_seguimiento_3); @endphp
          <select name="tipo_seguimiento_3" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts3==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts3==='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb3 = (string) old('paciente_sigue_embarazo_3', optional($seguimiento)->paciente_sigue_embarazo_3); @endphp
          <select name="paciente_sigue_embarazo_3" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb3==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb3==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_3" class="form-control"
                 value="{{ old('fecha_control_3', optional($seguimiento)->fecha_control_3) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_3" class="form-control"
                 value="{{ old('fecha_consulta_rn_3', optional($seguimiento)->fecha_consulta_rn_3) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_3" class="form-control"
                 value="{{ old('entrega_medicamentos_labs_3', optional($seguimiento)->entrega_medicamentos_labs_3) }}">
        </div>
        <div class="form-group col-md-4">
          <label>¿Seguimiento efectivo?</label><br>
          @php $se3 = old('seguimiento_efectivo_3', (bool) optional($seguimiento)->seguimiento_efectivo_3); @endphp
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="seguimiento_efectivo_3" name="seguimiento_efectivo_3" {{ $se3 ? 'checked' : '' }}>
            <label class="custom-control-label" for="seguimiento_efectivo_3">Sí</label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Gestión segunda semana</label>
        <input type="text" name="gestion_segunda_semana" class="form-control"
               value="{{ old('gestion_segunda_semana', optional($seguimiento)->gestion_segunda_semana) }}">
      </div>

      <hr>

      {{-- ======================= SEGUIMIENTO 4 ======================= --}}
      <h5 class="mb-2 text-primary">Seguimiento 4 (21 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_4" class="form-control"
                 value="{{ old('fecha_seguimiento_4', optional($seguimiento)->fecha_seguimiento_4) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts4 = (string) old('tipo_seguimiento_4', optional($seguimiento)->tipo_seguimiento_4); @endphp
          <select name="tipo_seguimiento_4" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts4==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts4==='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb4 = (string) old('paciente_sigue_embarazo_4', optional($seguimiento)->paciente_sigue_embarazo_4); @endphp
          <select name="paciente_sigue_embarazo_4" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb4==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb4==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_4" class="form-control"
                 value="{{ old('fecha_control_4', optional($seguimiento)->fecha_control_4) }}">
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Fecha consulta RN</label>
          <input type="date" name="fecha_consulta_rn_4" class="form-control"
                 value="{{ old('fecha_consulta_rn_4', optional($seguimiento)->fecha_consulta_rn_4) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_4" class="form-control"
                 value="{{ old('entrega_medicamentos_labs_4', optional($seguimiento)->entrega_medicamentos_labs_4) }}">
        </div>
        <div class="form-group col-md-4">
          <label>¿Seguimiento efectivo?</label><br>
          @php $se4 = old('seguimiento_efectivo_4', (bool) optional($seguimiento)->seguimiento_efectivo_4); @endphp
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="seguimiento_efectivo_4" name="seguimiento_efectivo_4" {{ $se4 ? 'checked' : '' }}>
            <label class="custom-control-label" for="seguimiento_efectivo_4">Sí</label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Gestión tercera semana</label>
        <input type="text" name="gestion_tercera_semana" class="form-control"
               value="{{ old('gestion_tercera_semana', optional($seguimiento)->gestion_tercera_semana) }}">
      </div>

      <hr>

      {{-- ======================= SEGUIMIENTO 5 ======================= --}}
      <h5 class="mb-2 text-primary">Seguimiento 5 (28 días)</h5>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha</label>
          <input type="date" name="fecha_seguimiento_5" class="form-control"
                 value="{{ old('fecha_seguimiento_5', optional($seguimiento)->fecha_seguimiento_5) }}">
        </div>
        <div class="form-group col-md-3">
          <label>Tipo</label>
          @php $ts5 = (string) old('tipo_seguimiento_5', optional($seguimiento)->tipo_seguimiento_5); @endphp
          <select name="tipo_seguimiento_5" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $ts5==='1'?'selected':'' }}>Telefónico</option>
            <option value="2" {{ $ts5==='2'?'selected':'' }}>Domiciliario</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>¿Sigue en embarazo?</label>
          @php $emb5 = (string) old('paciente_sigue_embarazo_5', optional($seguimiento)->paciente_sigue_embarazo_5); @endphp
          <select name="paciente_sigue_embarazo_5" class="form-control">
            <option value="">--</option>
            <option value="1" {{ $emb5==='1'?'selected':'' }}>Sí</option>
            <option value="0" {{ $emb5==='0'?'selected':'' }}>No</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Fecha control</label>
          <input type="date" name="fecha_control_5" class="form-control"
                 value="{{ old('fecha_control_5', optional($seguimiento)->fecha_control_5) }}">
        </div>
      </div>

      {{-- ✅ MODIFICACIÓN AQUÍ: agregué otro checkbox para seguimiento_efectivo_6 --}}
      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha consulta RNsssssssss (si aplica)</label>
          <input type="date" name="fecha_consulta_rn_5" class="form-control"
                 value="{{ old('fecha_consulta_rn_5', optional($seguimiento)->fecha_consulta_rn_5) }}">
        </div>

        <div class="form-group col-md-3">
          <label>Entrega meds/labs en casa</label>
          <input type="text" name="entrega_medicamentos_labs_5" class="form-control"
                 value="{{ old('entrega_medicamentos_labs_5', optional($seguimiento)->entrega_medicamentos_labs_5) }}">
        </div>

        <div class="form-group col-md-3">
          <label>¿Seguimiento efectivo? (28 días)</label><br>
          @php $se5 = old('seguimiento_efectivo_5', (bool) optional($seguimiento)->seguimiento_efectivo_5); @endphp
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="seguimiento_efectivo_5" name="seguimiento_efectivo_5" {{ $se5 ? 'checked' : '' }}>
            <label class="custom-control-label" for="seguimiento_efectivo_5">Sí</label>
          </div>
        </div>

       
      </div>

      <hr>

      {{-- ======================= CONTROLES ADICIONALES ======================= --}}
      <h5 class="mb-2 text-primary"><i class="fas fa-baby"></i> Controles adicionales</h5>
      <div class="row">
        <div class="form-group col-md-4">
          <label>Consulta apoyo lactancia</label>
          <input type="date" name="fecha_consulta_lactancia" class="form-control"
                 value="{{ old('fecha_consulta_lactancia', optional($seguimiento)->fecha_consulta_lactancia) }}">
        </div>
        <div class="form-group col-md-4">
          <label>1er control método anticonceptivo</label>
          <input type="date" name="fecha_control_metodo" class="form-control"
                 value="{{ old('fecha_control_metodo', optional($seguimiento)->fecha_control_metodo) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Gestión después del mes</label>
          <input type="text" name="gestion_despues_mes" class="form-control"
                 value="{{ old('gestion_despues_mes', optional($seguimiento)->gestion_despues_mes) }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label>Consulta 6 meses</label>
          <input type="date" name="fecha_consulta_6_meses" class="form-control"
                 value="{{ old('fecha_consulta_6_meses', optional($seguimiento)->fecha_consulta_6_meses) }}">
        </div>
        <div class="form-group col-md-6">
          <label>Consulta 1 año</label>
          <input type="date" name="fecha_consulta_1_ano" class="form-control"
                 value="{{ old('fecha_consulta_1_ano', optional($seguimiento)->fecha_consulta_1_ano) }}">
        </div>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save"></i>
        </button>
        <a href="{{ route('hub.seguimientos') }}" class="btn btn-secondary ml-2">
          <i class="fas fa-times"></i>
        </a>
      </div>
    </form>
  </div>
</div>
@stop
