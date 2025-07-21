@extends('adminlte::page')

@section('title', 'Editar Seguimiento')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
  .card-custom {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,0.15);
    border: none;
    margin-bottom: 1.5rem;
  }
  .card-custom .card-header {
    background: linear-gradient(135deg,#1d9bf0,#17bf63);
    color: #fff;
    font-weight: 600;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    text-align: center;
  }
  .btn-gradient {
    background: linear-gradient(45deg,#1d9bf0,#17bf63);
    color: #fff;
    border: none;
    border-radius: .75rem;
    padding: .75rem 1.5rem;
    transition: opacity .3s;
  }
  .btn-gradient:hover {
    opacity: .9;
  }
  .input-group-text {
    background: #f0f2f5;
    border: none;
    border-radius: .75rem 0 0 .75rem;
  }
  .form-control {
    border-radius: 0 .75rem .75rem 0;
  }
</style>
@stop

@section('content')
@include('seguimiento_412.mensajes')

<form action="{{url('/new412_seguimiento/'.$empleado->id)}}" method="post" enctype="multipart/form-data">
  @csrf
  {{method_field('PATCH')}}

  {{-- ============================== ENCABEZADO ============================== --}}
  <div class="card card-custom">
    <div class="card-header">
      <h2><i class="far fa-hospital"></i> Seguimiento <i class="fas fa-user-md"></i></h2>
    </div>
    <div class="card-body">

      {{-- ========================= DATOS DEL PACIENTE ========================= --}}
      <div class="card card-custom">
        <div class="card-header">Datos del Paciente</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Paciente</label>
              <select class="person form-control" name="cargue412_id" id="cargue412_id" required>
                <option value="">Seleccione...</option>
                @foreach($incomeedit as $developer)
                  <option value="{{$developer->idin}}" {{ $developer->idin == $empleado->cargue412_id ? 'selected' : '' }}>
                    {{ $developer->idin.' '.$developer->numero_identificacion.' '.$developer->primer_nombre.' '.$developer->segundo_nombre.' '.$developer->primer_apellido.' '.$developer->segundo_apellido }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label>Fecha Consulta</label>
              <input type="date" name="fecha_consulta" class="form-control" value="{{$empleado->fecha_consulta}}" required>
            </div>
          </div>
        </div>
      </div>

      {{-- ======================== DATOS ANTROPOMÉTRICOS ======================== --}}
      <div class="card card-custom">
        <div class="card-header">Datos Antropométricos</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Talla en centímetros</label>
              <input type="number" step="0.01" name="talla_cm" class="form-control" value="{{$empleado->talla_cm}}">
            </div>
            <div class="col-md-6">
              <label>Peso en kilos</label>
              <input type="number" step="0.0001" name="peso_kilos" class="form-control" value="{{$empleado->peso_kilos}}">
            </div>
            <div class="col-md-6">
              <label>Perímetro braquial</label>
              <input type="text" name="perimetro_braqueal" class="form-control" value="{{$empleado->perimetro_braqueal}}">
            </div>
            <div class="col-md-6">
              <label>Requerimiento energía FTLC</label>
              <input type="text" name="requerimiento_energia_ftlc" class="form-control" value="{{$empleado->requerimiento_energia_ftlc}}">
            </div>
          </div>
        </div>
      </div>

      {{-- =========================== EVALUACIÓN =========================== --}}
      <div class="card card-custom">
        <div class="card-header">Evaluación</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Clasificación</label>
              <select name="clasificacion" class="person2 form-control" required>
                <option value="{{$empleado->clasificacion}}">{{$empleado->clasificacion}}</option>
                <option value="DESNUTRICION AGUDA MODERADA">DESNUTRICION AGUDA MODERADA</option>
                <option value="DESNUTRICION AGUDA SEVERA">DESNUTRICION AGUDA SEVERA</option>
                <option value="KWASHIORKOR">KWASHIORKOR</option>
                <option value="MARASMO">MARASMO</option>
                <option value="MIXTA">MIXTA</option>
                <option value="RIESGO">RIESGO</option>
                <option value="BUSQUEDA FALLIDA">BUSQUEDA FALLIDA</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Puntaje Z</label>
              <input type="number" step="0.0001" name="puntajez" class="form-control" value="{{$empleado->puntajez}}">
            </div>
          </div>
        </div>
      </div>

      {{-- ======================= OBSERVACIONES Y MEDICAMENTOS ======================= --}}
      <div class="card card-custom">
        <div class="card-header">Observaciones y Medicamentos</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Medicamento</label>
<select class="js-example-basic-multiple form-control" name="medicamento[]" multiple>
  @php
      // Convierte el string en un array
      $medicamentosSeleccionados = explode(',', $empleado->medicamento);
  @endphp

  @foreach(["23072-2" => "albendazol 200MG", 
            "54114-1" => "albendazol 400MG", 
            "35662-18" => "Acido folico", 
            "31063-1" => "Vitamina A", 
            "27440-3" => "Hierro"] as $key => $label)
    <option value="{{ $key }}" 
        {{ in_array($key, $medicamentosSeleccionados) ? 'selected' : '' }}>
        {{ $label }}
    </option>
  @endforeach
</select>

            </div>
          </div>
        </div>
      </div>

      {{-- =========================== ESTADO Y DEMANDA =========================== --}}
      <div class="card card-custom">
        <div class="card-header">Estado del Menor y Demanda</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Estado actual del menor</label>
              <select name="est_act_menor" class="person2 form-control">
                <option value="{{$empleado->est_act_menor}}">{{$empleado->est_act_menor}}</option>
                <option value="RECUPERADO">RECUPERADO</option>
                <option value="FALLECIDO">FALLECIDO</option>
                <option value="EN PROCESO DE RECUPERACION">EN PROCESO DE RECUPERACION</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Esquema PAI</label>
              <select name="Esquemq_complrto_pai_edad" class="person2 form-control">
                <option value="{{$empleado->Esquemq_complrto_pai_edad}}">{{$empleado->Esquemq_complrto_pai_edad}}</option>
                <option value="INCOMPLETO">INCOMPLETO</option>
                <option value="COMPLETO">COMPLETO</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      @if(Auth::user()->usertype != 2)
      {{-- ===================== SEGUIMIENTO Y MOTIVO DE REAPERTURA ===================== --}}
      <div class="card card-custom">
        <div class="card-header">Seguimiento</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>¿Desea modificar el estado del caso?</label>
              <select name="estado" class="person2 form-control">
                <option value="{{$empleado->estado}}">{{ $empleado->estado == 1 ? 'ABIERTO' : 'CERRADO' }}</option>
                <option value="1">ABIERTO</option>
                <option value="0">CERRADO</option>
              </select>
            </div>
            <div class="col-md-6" id="input_oculto">
              <label>Fecha Próximo Seguimiento</label>
              <input type="date" name="fecha_proximo_control" class="form-control" value="{{$empleado->fecha_proximo_control}}">
            </div>
          </div>
          <div class="row g-3 mt-3">
            <div class="col-md-12">
              <label>Motivo de reapertura</label>
              <textarea name="motivo_reapuertura" class="form-control" rows="4">{{$empleado->motivo_reapuertura}}</textarea>
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- ============================ BOTONES ============================ --}}
      <div class="text-center mt-4">
        <button type="submit" class="btn-gradient btn-lg">ENVIAR</button>
        <a href="{{url('new412_seguimiento')}}" class="btn btn-secondary btn-lg mx-2">REGRESAR</a>
      </div>

    </div>
  </div>
</form>
@stop

@section('js')
<script>
  $(function() {
    $('.js-example-basic-multiple').select2();
    $('.person, .person2').select2();

    $('#estado').on('change', function() {
      if (this.value == '0') {
        $('#input_oculto').hide();
      } else {
        $('#input_oculto').show();
      }
    }).trigger('change');
  });
</script>
@stop