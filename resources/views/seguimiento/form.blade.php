{{-- resources/views/seguimiento/form.blade.php --}}
@include('seguimiento.mensajes')

{{-- Card: Título Seguimiento --}}
<div class="card card-custom mb-4">
  <div class="card-header text-center">
    <h2 class="card-title text-white">
      <i class="fas fa-hospital-user"></i>
      Seguimiento
      <i class="fas fa-user-md"></i>
    </h2>
  </div>
</div>

<form id="update-form" action="{{ url('Seguimiento') }}" method="POST" enctype="multipart/form-data">
  @csrf

  {{-- Card: Datos Paciente --}}
  <div class="card card-custom mb-4">
    <div class="card-header"><i class="fas fa-user"></i> Datos Paciente</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label for="sivigilas_id">Paciente</label>
          <div class="input-group">
            {{-- <span class="input-group-text"><i class="fas fa-user"></i></span> --}}
            <select id="sivigilas_id" name="sivigilas_id" class="form-control select2" required>
              <option value="">Seleccione...</option>
              @foreach($incomeedit as $dev)
                <option value="{{ $dev->idin }}" {{ old('sivigilas_id')==$dev->idin?'selected':'' }}>
                  ({{ $dev->fec_not ? \Carbon\Carbon::parse($dev->fec_not)->format('Y-m-d') : 'SIN FECHA' }})
                  {{ $dev->idin }} {{ $dev->num_ide_ }}
                  {{ $dev->pri_nom_ }} {{ $dev->seg_nom_ }}
                  {{ $dev->pri_ape_ }} {{ $dev->seg_ape_ }}
                </option>
              @endforeach
            </select>
          </div>
          @error('sivigilas_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="col-12 col-md-6">
          <label for="fecha_consulta">Fecha Consulta</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
            <input
              type="date"
              id="fecha_consulta"
              name="fecha_consulta"
              class="form-control"
              value="{{ old('fecha_consulta') }}"
              required>
          </div>
          @error('fecha_consulta')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- Card: Antropometría --}}
  <div class="card card-custom mb-4">
    <div class="card-header"><i class="fas fa-ruler-combined"></i> Antropometría</div>
    <div class="card-body">
      <div class="row g-3">
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
              value="{{ old('talla_cm') }}"
              required>
          </div>
          @error('talla_cm')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
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
              value="{{ old('peso_kilos') }}"
              required>
          </div>
          @error('peso_kilos')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
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
              value="{{ old('puntajez') }}"
              required>
          </div>
          @error('puntajez')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>
      <div class="row g-3 mt-2">
        <div class="col-12 col-md-6">
          <label for="perimetro_braqueal">Perímetro Braquial</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-expand-arrows-alt"></i></span>
            <input
              type="text"
              id="perimetro_braqueal"
              name="perimetro_braqueal"
              class="form-control"
              value="{{ old('perimetro_braqueal') }}"
              required>
          </div>
          @error('perimetro_braqueal')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
        <div class="col-12 col-md-6">
          <label for="requerimiento_energia_ftlc">Energía FTLC</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-bolt"></i></span>
            <input
              type="text"
              id="requerimiento_energia_ftlc"
              name="requerimiento_energia_ftlc"
              class="form-control"
              value="{{ old('requerimiento_energia_ftlc') }}"
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
                <option value="{{ $opt }}" {{ old('clasificacion')==$opt?'selected':'' }}>
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
            maxlength="600">{{ old('observaciones') }}</textarea>
          @error('observaciones')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>

      {{-- Medicamento --}}
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
              <option value="23072-2" {{ collect(old('medicamento'))->contains('23072-2')?'selected':'' }}>Albendazol 200mg</option>
              <option value="54114-1" {{ collect(old('medicamento'))->contains('54114-1')?'selected':'' }}>Albendazol 400mg</option>
              <option value="35662-18" {{ collect(old('medicamento'))->contains('35662-18')?'selected':'' }}>Ácido Fólico</option>
              <option value="31063-1" {{ collect(old('medicamento'))->contains('31063-1')?'selected':'' }}>Vitamina A</option>
              <option value="27440-3" {{ collect(old('medicamento'))->contains('27440-3')?'selected':'' }}>Hierro</option>
              <option value="NO APLICA" {{ collect(old('medicamento'))->contains('NO APLICA')?'selected':'' }}>No Aplica</option>
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
              <option value="INCOMPLETO" {{ old('Esquemq_complrto_pai_edad')=='INCOMPLETO'?'selected':'' }}>Incompleto</option>
              <option value="COMPLETO"   {{ old('Esquemq_complrto_pai_edad')=='COMPLETO'?'selected':'' }}>Completo</option>
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
              <option value="SI" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018')=='SI'?'selected':'' }}>Sí</option>
              <option value="NO" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018')=='NO'?'selected':'' }}>No</option>
            </select>
          </div>
          @error('Atecion_primocion_y_mantenimiento_res3280_2018')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- Card: Cierre de Caso --}}
<div class="card card-custom mb-4">
    <div class="card-header"><i class="fas fa-check-circle"></i> Cierre de Caso</div>
    <div class="card-body">
      <div class="row g-3">
        {{-- Estado actual del menor --}}
        <div class="col-12 col-md-4">
          <label for="est_act_menor">Estado actual del menor</label>
          <select
            id="est_act_menor"
            name="est_act_menor"
            class="form-control select2"
            required>
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
              <option value="{{ $opt }}"
                {{ old('est_act_menor')==$opt ? 'selected':'' }}>
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
            required>
            <option value="">Seleccione...</option>
            @foreach(['SI','NO'] as $opt)
              <option value="{{ $opt }}"
                {{ old('tratamiento_f75')==$opt ? 'selected':'' }}>
                {{ $opt }}
              </option>
            @endforeach
          </select>
        </div>
  
        {{-- Fecha recibe f75 (oculto/visible) --}}
        <div class="col-12 col-md-4" id="row_fecha_recibio_tratf75" style="display:none;">
          <label for="fecha_recibio_tratf75">Fecha recibió tratamiento f75</label>
          <input
            type="date"
            id="fecha_recibio_tratf75"
            name="fecha_recibio_tratf75"
            class="form-control"
            value="{{ old('fecha_recibio_tratf75') }}">
        </div>
  
        {{-- Estado cierre --}}
        <div class="col-12 col-md-4">
          <label for="estado">Estado</label>
          <select
            id="estado"
            name="estado"
            class="form-control select2"
            required>
            <option value="">Seleccione...</option>
            <option value="1" {{ old('estado')=='1' ? 'selected':'' }}>Abierto</option>
            <option value="0" {{ old('estado')=='0' ? 'selected':'' }}>Cerrado</option>
          </select>
        </div>
  
        {{-- Próximo Seguimiento --}}
        <div class="col-12 col-md-4" id="input_oculto">
          <label for="fecha_proximo_control">Próx. Seguimiento</label>
          <input
            type="date"
            id="fecha_proximo_control"
            name="fecha_proximo_control"
            class="form-control"
            value="{{ old('fecha_proximo_control') }}">
        </div>
  
        {{-- Fecha Entrega FTLC --}}
        <div class="col-12 col-md-4">
          <label for="fecha_entrega_ftlc">Entrega FTLC</label>
          <input
            type="date"
            id="fecha_entrega_ftlc"
            name="fecha_entrega_ftlc"
            class="form-control"
            value="{{ old('fecha_entrega_ftlc') }}">
        </div>
  
        {{-- PDF Adjunto --}}
        <div class="col-12">
          <label for="pdf">PDF Adjunto</label>
          <input
            type="file"
            id="pdf"
            name="pdf"
            class="form-control-file"
            required>
        </div>
      </div>
    </div>
  </div>

  {{-- Botones --}}
  <div class="text-center mb-5">
    <button
      id="update-btn"
      type="button"
      class="btn-gradient btn-lg mx-2"
      onclick="submitForm()">
      <i class="fas fa-paper-plane"></i> Enviar
    </button>
    <a href="{{ url('Seguimiento') }}" class="btn btn-secondary btn-lg mx-2">
      <i class="fas fa-arrow-left"></i> Regresar
    </a>
  </div>
</form>
