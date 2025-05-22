{{-- resources/views/seguimiento/edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Editar Seguimiento')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
  /* === Select2 personalizado === */
  .select2-container { width: 100% !important; }
  .select2-results__option { font-size: 14px; color: #333; }
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #fff !important; color: #ec0b0b !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff !important;
  }

  /* === Tags de Select2 mejoradas para Medicamento === */
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: linear-gradient(45deg,#17bf63,#1d9bf0);
    color: #fff; border: none;
    border-radius: .75rem; padding: .25rem .75rem;
    margin-right: .5rem; font-size: .875rem; font-weight:500;
    transition: transform .2s, opacity .2s;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
    transform: scale(1.05);
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: rgba(255,255,255,0.8) !important;
    margin-left: .5rem; font-size:1rem; transition: color .2s;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #ffdddd !important;
  }

  /* === Tarjetas modernas === */
  .card-custom {
    background: #fff; border: none;
    border-radius: 1rem;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    margin-bottom: 1.5rem;
  }
  .card-custom .card-header {
    background: linear-gradient(135deg,#1d9bf0,#17bf63);
    color: #fff; font-weight:600;
    border-top-left-radius:1rem; border-top-right-radius:1rem;
  }

  /* === Inputs con ícono === */
  .input-group-text {
    background: #f0f2f5; border: none;
    border-top-left-radius: .75rem;
    border-bottom-left-radius: .75rem;
  }
  .form-control {
    border: 1px solid #ddd; border-left: none;
    border-top-right-radius: .75rem;
    border-bottom-right-radius: .75rem;
  }

  /* === Botones degradados === */
  .btn-gradient {
    background: linear-gradient(45deg,#1d9bf0,#17bf63);
    color: #fff; border: none; border-radius: .75rem;
    padding: .75rem 1.5rem; transition: opacity .3s;
  }
  .btn-gradient:hover { opacity: .9; }

  @media (max-width: 767px) {
    .card-custom { margin: 1rem; }
    .btn-gradient { width: 100%; margin-bottom: 1rem; }
  }



  /* para que  se podere de lapantalla cuando envie el form */
/* Overlay fullscreen */
#submit-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.8);
  z-index: 1050; /* por encima de todo */
  align-items: center;
  justify-content: center;
  flex-direction: column;
}
#submit-overlay .spinner-border {
  width: 4rem;
  height: 4rem;
}
#submit-overlay p {
  margin-top: 1rem;
  font-size: 1.25rem;
  color: #333;
}
  
</style>
@stop

@section('content')
<div class="container-fluid py-4">
  <form id="update-form"
        action="{{ url('Seguimiento/'.$empleado->id) }}"
        method="POST"
        enctype="multipart/form-data">

        
    @csrf
    @method('PATCH')
    @include('seguimiento.mensajes')

  {{-- Card: Datos Paciente y Fecha --}}
<div class="card card-custom mb-4">
    <div class="card-header d-flex align-items-center">
      <i class="fas fa-user me-2"></i>
      <h5 class="mb-0">Datos Paciente</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
  
        {{-- Paciente --}}
        <div class="col-12 col-md-6">
          <label for="sivigilas_id">Paciente</label>
          <div class="input-group">
            <div class="input-group-prepend">
              
            </div>
            <select
              id="sivigilas_id"
              name="sivigilas_id"
              class="form-control select2"
              required>
              <option value="">Seleccione...</option>
              @foreach($incomeedit as $dev)
                <option
                  value="{{ $dev->idin }}"
                  {{ old('sivigilas_id', $empleado->sivigilas_id) == $dev->idin ? 'selected' : '' }}>
                  {{ $dev->idin }} – {{ $dev->num_ide_ }}
                  {{ $dev->pri_nom_ }} {{ $dev->seg_nom_ }}
                  {{ $dev->pri_ape_ }} {{ $dev->seg_ape_ }}
                </option>
              @endforeach
            </select>
          </div>
          @error('sivigilas_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
  
        {{-- Fecha Consulta --}}
        <div class="col-12 col-md-6">
          <label for="fecha_consulta">Fecha Consulta</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
            </div>
            <input
              type="date"
              id="fecha_consulta"
              name="fecha_consulta"
              class="form-control"
              value="{{ old('fecha_consulta', $empleado->fecha_consulta) }}"
              required>
          </div>
          @error('fecha_consulta')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
  
      </div>
    </div>
  </div>
  
  {{-- Card: Antropometría --}}
<div class="card card-custom mb-4">
    <div class="card-header">
      <i class="fas fa-ruler-combined"></i> Antropometría
    </div>
    <div class="card-body">
      <div class="row g-3">
        {{-- Talla --}}
        <div class="col-12 col-md-4">
          <label for="talla_cm">Talla (cm)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-ruler"></i></span>
            <input
              type="number"
              step="0.01"
              id="talla_cm"
              name="talla_cm"
              class="form-control"
              value="{{ old('talla_cm', $empleado->talla_cm) }}"
              required>
          </div>
          @error('talla_cm')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
  
        {{-- Peso --}}
        <div class="col-12 col-md-4">
          <label for="peso_kilos">Peso (kg)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-weight-hanging"></i></span>
            <input
              type="number"
              step="0.0001"
              id="peso_kilos"
              name="peso_kilos"
              class="form-control"
              value="{{ old('peso_kilos', $empleado->peso_kilos) }}"
              required>
          </div>
          @error('peso_kilos')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
  
        {{-- Puntaje Z --}}
        <div class="col-12 col-md-4">
          <label for="puntajez">Puntaje Z</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
            <input
              type="number"
              step="0.0001"
              id="puntajez"
              name="puntajez"
              class="form-control"
              value="{{ old('puntajez', $empleado->puntajez) }}"
              required>
          </div>
          @error('puntajez')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>
  
      <div class="row g-3 mt-2">
        {{-- Perímetro Braquial --}}
        <div class="col-12 col-md-6">
          <label for="perimetro_braqueal">Perímetro Braquial</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-expand-arrows-alt"></i></span>
            <input
              type="text"
              id="perimetro_braqueal"
              name="perimetro_braqueal"
              class="form-control"
              value="{{ old('perimetro_braqueal', $empleado->perimetro_braqueal) }}"
              required>
          </div>
          @error('perimetro_braqueal')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
  
        {{-- Requerimiento Energía FTLC --}}
        <div class="col-12 col-md-6">
          <label for="requerimiento_energia_ftlc">Energía FTLC</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-bolt"></i></span>
            <input
              type="text"
              id="requerimiento_energia_ftlc"
              name="requerimiento_energia_ftlc"
              class="form-control"
              value="{{ old('requerimiento_energia_ftlc', $empleado->requerimiento_energia_ftlc) }}"
              required>
          </div>
          @error('requerimiento_energia_ftlc')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>
    </div>
  </div>
  
    {{-- Card: Manejo y Observaciones --}}
    <div class="card card-custom mb-4">
      <div class="card-header"><i class="fas fa-notes-medical"></i> Manejo y Observaciones</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label for="clasificacion">Clasificación</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-clipboard-list"></i></span>
              <select
                id="clasificacion"
                name="clasificacion"
                class="form-control select2"
                required>
                <option value="">Seleccione...</option>
                @foreach([
                  'DESNUTRICION AGUDA MODERADA',
                  'DESNUTRICION AGUDA SEVERA',
                  'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR',
                  'DESNUTRICION AGUDA SEVERA TIPO MARASMO',
                  'DESNUTRICION AGUDA SEVERA MIXTA',
                  'RIESGO DE DESNUTRICION',
                  'RIESGO DE DESNUTRICION AGUDA',
                  'BUSQUEDA FALLIDA',
                  'PESO ADECUADO PARA LA TALLA'
                ] as $opt)
                  <option
                    value="{{ $opt }}"
                    {{ old('clasificacion', $empleado->clasificacion)==$opt ? 'selected':'' }}>
                    {{ ucwords(strtolower($opt)) }}
                  </option>
                @endforeach
              </select>
            </div>
            @error('clasificacion')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="col-12 col-md-6">
            <label for="observaciones">Observaciones</label>
            <textarea
              id="observaciones"
              name="observaciones"
              class="form-control"
              rows="4"
              maxlength="600"
            >{{ old('observaciones', $empleado->observaciones) }}</textarea>
            @error('observaciones')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>
        <div class="row g-3 mt-2">
          <div class="col-12">
            <label for="medicamento">Medicamento</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-pills"></i></span>
              <select
                id="medicamento"
                name="medicamento[]"
                class="form-control select2-multiple"
                multiple>
                @foreach([
                  '23072-2'=>'Albendazol 200mg',
                  '54114-1'=>'Albendazol 400mg',
                  '35662-18'=>'Ácido Fólico',
                  '31063-1'=>'Vitamina A',
                  '27440-3'=>'Hierro',
                  'NO APLICA'=>'No Aplica'
                ] as $val=>$label)
                  <option
                    value="{{ $val }}"
                    {{ in_array($val, old('medicamento', explode(',', $empleado->medicamento))) ? 'selected':'' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
            </div>
            @error('medicamento')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>
      </div>
    </div>

    {{-- Card: Demanda Inducida --}}
    <div class="card card-custom mb-4">
      <div class="card-header"><strong>Demanda Inducida</strong></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label for="Esquemq_complrto_pai_edad">Esquema PAI</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-syringe"></i></span>
              <select
                id="Esquemq_complrto_pai_edad"
                name="Esquemq_complrto_pai_edad"
                class="form-control select2"
                required>
                <option value="">Seleccione...</option>
                @foreach(['INCOMPLETO','COMPLETO'] as $opt)
                  <option
                    value="{{ $opt }}"
                    {{ old('Esquemq_complrto_pai_edad', $empleado->Esquemq_complrto_pai_edad)==$opt ? 'selected':'' }}>
                    {{ ucwords(strtolower($opt)) }}
                  </option>
                @endforeach
              </select>
            </div>
            @error('Esquemq_complrto_pai_edad')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="col-12 col-md-6">
            <label for="Atecion_primocion_y_mantenimiento_res3280_2018">Promoción/Mantenimiento</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-route"></i></span>
              <select
                id="Atecion_primocion_y_mantenimiento_res3280_2018"
                name="Atecion_primocion_y_mantenimiento_res3280_2018"
                class="form-control select2"
                required>
                <option value="">Seleccione...</option>
                @foreach(['SI','NO'] as $opt)
                  <option
                    value="{{ $opt }}"
                    {{ old('Atecion_primocion_y_mantenimiento_res3280_2018', $empleado->Atecion_primocion_y_mantenimiento_res3280_2018)==$opt ? 'selected':'' }}>
                    {{ $opt }}
                  </option>
                @endforeach
              </select>
            </div>
            @error('Atecion_primocion_y_mantenimiento_res3280_2018')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
        </div>
      </div>
    </div>

{{-- Card: Cierre de Caso --}}
<div class="card card-custom mb-4">
    <div class="card-header d-flex align-items-center">
      <i class="fas fa-check-circle me-2"></i>
      <h5 class="mb-0">Cierre de Caso</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        {{-- Estado actual del menor --}}
        <div class="col-12 col-md-4">
          <label for="est_act_menor">Estado actual del menor</label>
          <select
            id="est_act_menor"
            name="est_act_menor"
            class="form-control select2"
            @if(Auth::user()->usertype != 2) required @endif>
            <option value="">Seleccione...</option>
            @foreach([
              'PESO ADECUADO PARA LA TALLA',
              'RIESGO DE DESNUTRICIÓN AGUDA',
              'DESNUTRICION AGUDA MODERADA',
              'DESNUTRICIÓN AGUDA SEVERA',
              'DESNUTRICIÓN AGUDA SEVERA TIPO MARASMO',
              'DESNUTRICIÓN AGUDA SEVERA TIPO KWASHIORKOR',
              'DESNUTRICIÓN AGUDA SEVERA MIXTA',
              'EN PROCESO DE RECUPERACION',
              'BUSQUEDA FALLIDA',
              'PROCESO DE RECUPERACION',
              'RECUPERADO',
              'FALLECIDO'
            ] as $opt)
              <option
                value="{{ $opt }}"
                {{ old('est_act_menor', $empleado->est_act_menor) === $opt ? 'selected' : '' }}>
                {{ ucwords(strtolower($opt)) }}
              </option>
            @endforeach
          </select>
        </div>
  
        {{-- Tratamiento f75 --}}
        <div class="col-12 col-md-4">
          <label for="tratamiento_f75">Tratamiento f75</label>
          <select
            id="tratamiento_f75"
            name="tratamiento_f75"
            class="form-control select2"
            @if(Auth::user()->usertype != 2) required @endif>
            <option value="">Seleccione...</option>
            @foreach(['SI','NO'] as $opt)
              <option
                value="{{ $opt }}"
                {{ old('tratamiento_f75', $empleado->tratamiento_f75) === $opt ? 'selected' : '' }}>
                {{ $opt }}
              </option>
            @endforeach
          </select>
        </div>
  
        {{-- Fecha recibe tratamiento f75 (oculto/visible) --}}
        <div class="col-12 col-md-4" id="row_fecha_recibio_tratf75" style="display:none;">
          <label for="fecha_recibio_tratf75">Fecha recibe f75</label>
          <input
            type="date"
            id="fecha_recibio_tratf75"
            name="fecha_recibio_tratf75"
            class="form-control"
            value="{{ old('fecha_recibio_tratf75', $empleado->fecha_recibio_tratf75) }}"
            @if(Auth::user()->usertype != 2) required @endif>
        </div>
  
        @if(Auth::user()->usertype != 2)
          {{-- Modificar Estado Caso --}}
          <div class="col-12 col-md-6">
            <label for="estado">Modificar Estado Caso</label>
            <select
              id="estado"
              name="estado"
              class="form-control select2"
              required>
              <option value="">Seleccione...</option>
              <option value="1" {{ old('estado', $empleado->estado)=='1' ? 'selected':'' }}>Abierto</option>
              <option value="0" {{ old('estado', $empleado->estado)=='0' ? 'selected':'' }}>Cerrado</option>
            </select>
          </div>
  
          {{-- Próx. Seguimiento --}}
          <div class="col-12 col-md-6" id="row_fecha_proximo_control">
            <label for="fecha_proximo_control">Próx. Seguimiento</label>
            <input
              type="date"
              id="fecha_proximo_control"
              name="fecha_proximo_control"
              class="form-control"
              value="{{ old('fecha_proximo_control', $empleado->fecha_proximo_control) }}"
              required>
          </div>
        @else
          {{-- Para tipo 2: inyectamos valores escondidos para no romper validación --}}
          <input type="hidden" name="estado" value="{{ $empleado->estado }}">
          <input type="hidden" name="fecha_proximo_control" value="{{ $empleado->fecha_proximo_control }}">
        @endif
  
        {{-- Fecha Entrega FTLC --}}
        <div class="col-12 col-md-4">
          <label for="fecha_entrega_ftlc">Entrega FTLC</label>
          <input
            type="date"
            id="fecha_entrega_ftlc"
            name="fecha_entrega_ftlc"
            class="form-control"
            value="{{ old('fecha_entrega_ftlc', $empleado->fecha_entrega_ftlc) }}"
            @if(Auth::user()->usertype != 2) required @endif>
        </div>
  
        {{-- PDF Adjunto --}}
        <div class="col-12 col-md-8">
          <label for="pdf">PDF Adjunto</label>
          <input
            type="file"
            id="pdf"
            name="pdf"
            class="form-control-file"
            @if(Auth::user()->usertype != 2) required @endif>
        </div>
      </div>
  
      {{-- Motivo de reapertura --}}
      @if(Auth::user()->usertype != 2)
        <div class="row g-3 mt-3">
          <div class="col-12">
            <label for="motivo_reapuertura">Motivo de reapertura</label>
            <textarea
              id="motivo_reapuertura"
              name="motivo_reapuertura"
              class="form-control"
              rows="5"
              maxlength="1000">{{ old('motivo_reapuertura', $empleado->motivo_reapuertura) }}</textarea>
          </div>
        </div>
      @endif
    </div>
  </div>
  

    {{-- Botones --}}
    <div class="text-center mb-5">
      <button
        type="submit"
        class="btn-gradient btn-lg mx-2"
        onclick="submitForm()">
        <i class="fas fa-paper-plane"></i> Actualizar
        <span id="loading-icon" class="spinner-border spinner-border-sm ml-2" style="display:none;"></span>
      </button>
      <a href="{{ url('Seguimiento') }}" class="btn btn-secondary btn-lg mx-2">
        <i class="fas fa-arrow-left"></i> Regresar
      </a>
    </div>
</form>

<div id="submit-overlay">
  <div class="spinner-border text-primary" role="status"></div>
  <p>Enviando correo…</p>
</div>
  
</div>

@stop

@section('js')
<script>
  $(function(){
    // Inicializa Select2
    $('.select2').select2({ placeholder: 'Seleccione...', allowClear: true, width: 'resolve' });
    $('.select2-multiple').select2({
      placeholder: 'Seleccione medicamento(s)...',
      closeOnSelect: false,
      allowClear: true,
      width: 'resolve'
    });

    // Mostrar/ocultar Próx. Seguimiento según Estado
    $('#estado').on('change', function(){
      if (this.value === '1') {
        $('#input_oculto').show();
      } else {
        $('#input_oculto').hide().find('input').val('');
      }
    }).trigger('change');

    // Mostrar/ocultar Fecha recibe f75 según Tratamiento f75
    $('#tratamiento_f75').on('change', function(){
      if (this.value === 'SI') {
        $('#input_oculto1').show();
      } else {
        $('#input_oculto1').hide().find('input').val('');
      }
    }).trigger('change');
  });

  function submitForm(){
  // mostramos el overlay en modo flex
  $('#submit-overlay').css('display','flex');
  // deshabilitamos el botón para evitar dobles clics
  $('button[type="submit"]').prop('disabled', true);
  // pequeño delay para que pinte
  setTimeout(function(){
    $('#update-form').submit();
  }, 100);
}

</script>
@stop

