{{-- resources/views/seguimiento/mi_formulario.blade.php --}}




@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
  /* === Tarjetas modernas === */
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
  }

  /* === Inputs con ícono === */
  .input-group-text {
    background: #f0f2f5;
    border: none;
    border-top-left-radius: .75rem;
    border-bottom-left-radius: .75rem;
  }
  .form-control {
    border: 1px solid #ddd;
    border-left: none;
    border-top-right-radius: .75rem;
    border-bottom-right-radius: .75rem;
  }

  /* === Select2 personalizado === */
  .select2-container { width:100% !important; }
  .select2-results__option { font-size:14px; color:#333; }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height:1.9;
  }

  /* === Botones degradados === */
  .btn-gradient {
    background: linear-gradient(45deg,#1d9bf0,#17bf63);
    color: #fff;
    border: none;
    border-radius: .75rem;
    padding: .75rem 1.5rem;
    transition: opacity .3s;
  }
  .btn-gradient:hover { opacity:.9; }

  /* === Overlay de envío dentro del botón === */
  #loading-icon { margin-left:.5rem; }
  #sending-text { margin-left:.5rem; font-weight:500; }

  @media(max-width:767px){
    .card-custom{ margin:1rem; }
    .btn-gradient{ width:100%; margin-bottom:1rem; }
  }
</style>
@stop


@include('seguimiento.mensajes')

<div class="row">
  <div class="col-lg-12">
    <div class="card card-custom">
      <div class="card-header text-center">
        <h2 class="card-title text-white">
          <i class="far fa-hospital" style="font-size:45px;color:#3333ff;"></i>
          Seguimiento
          <i class="bi bi-plus"></i>
          <i class="fas fa-user-md" style="font-size:45px;color:#3333ff;"></i>
        </h2>
      </div>
      <div class="card-body">
        <form id="update-form" action="{{ url('Seguimiento') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row g-3">
            {{-- Paciente --}}
            <div class="col-md-6">
              <label for="cargue412_id">Paciente</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <select class="person form-control" name="cargue412_id" id="cargue412_id" required>
                  <option value="">SELECCIONE</option>
                  @foreach($incomeedit as $developer)
                    <option value="{{ $developer->idin }}">
                      ({{ $developer->created_at
                        ? \Carbon\Carbon::parse($developer->created_at)->format('Y-m-d')
                        : 'SIN FECHA' }})
                      {{ $developer->idin }} {{ $developer->numero_identificacion }}
                      {{ $developer->primer_nombre }} {{ $developer->segundo_nombre }}
                      {{ $developer->primer_apellido }} {{ $developer->segundo_apellido }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- Fecha Consulta --}}
            <div class="col-md-6">
              <label for="fecha_consulta">Fecha Consulta</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                <input class="form-control" type="date" name="fecha_consulta" id="fecha_consulta" required>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-3">
            {{-- Talla --}}
            <div class="col-md-6">
              <label for="talla_cm">Talla (cm)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                <input class="form-control" type="number" step="0.01" name="talla_cm" id="talla_cm" required>
              </div>
            </div>
            {{-- Peso --}}
            <div class="col-md-6">
              <label for="peso_kilos">Peso (kg)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-weight-hanging"></i></span>
                <input class="form-control" type="number" step="0.0001" name="peso_kilos" id="peso_kilos" required>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-3">
            {{-- Clasificación --}}
            <div class="col-md-6">
              <label for="clasificacion">Calificación</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-clipboard-list"></i></span>
                <select class="person2 form-control" name="clasificacion" id="clasificacion" required>
                  <option value="">SELECCIONAR</option>
                  <option value="DESNUTRICION AGUDA MODERADA">DESNUTRICION AGUDA MODERADA</option>
                  <option value="DESNUTRICION AGUDA SEVERA">DESNUTRICION AGUDA SEVERA</option>
                  <option value="DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR">KWASHIORKOR</option>
                  <option value="DESNUTRICION AGUDA SEVERA TIPO MARASMO">MARASMO</option>
                  <option value="DESNUTRICION AGUDA SEVERA MIXTA">MIXTA</option>
                  <option value="RIESGO DE DESNUTRICION">RIESGO</option>
                  <option value="BUSQUEDA FALLIDA">BUSQUEDA FALLIDA</option>
                  <option value="PESO ADECUADO PARA LA TALLA">PESO ADECUADO</option>
                </select>
              </div>
            </div>
            {{-- Puntaje Z --}}
            <div class="col-md-6">
              <label for="puntajez">Puntaje Z</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
                <input class="form-control" type="number" step="0.0001" name="puntajez" id="puntajez" required>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-3">
            {{-- Perímetro Braquial --}}
            <div class="col-md-6">
              <label for="perimetro_braqueal">Perímetro Braquial</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-expand-arrows-alt"></i></span>
                <input class="form-control" type="text" name="perimetro_braqueal" id="perimetro_braqueal" required>
              </div>
            </div>
            {{-- Energía FTLC --}}
            <div class="col-md-6">
              <label for="requerimiento_energia_ftlc">Energía FTLC</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-bolt"></i></span>
                <input class="form-control" type="text" name="requerimiento_energia_ftlc" id="requerimiento_energia_ftlc" required>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-3">
            {{-- Observaciones --}}
            <div class="col-md-6">
              <label for="observaciones">Observaciones</label>
              <textarea class="form-control" name="observaciones" id="observaciones" rows="5" maxlength="600"></textarea>
            </div>
            {{-- Medicamento --}}
            <div class="col-md-6">
              <label for="medicamento">Medicamento</label>
              <select class="js-example-basic-multiple form-control" name="medicamento[]" id="medicamento" multiple>
                <option value="23072-2">albendazol 200MG</option>
                <option value="54114-1">albendazol 400MG</option>
                <option value="35662-18">Acido folico</option>
                <option value="31063-1">Vitamina A</option>
                <option value="27440-3">Hierro</option>
                <option value="NO APLICA">NO APLICA</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mt-3">
            {{-- Estado actual del menor --}}
            <div class="col-md-3">
              <label for="est_act_menor">Estado actual del menor</label>
              <select class="person2 form-control" name="est_act_menor" id="est_act_menor" required>
                <option value="">SELECCIONAR</option>
                <option value="DESNUTRICION AGUDA MODERADA">DESNUTRICION AGUDA MODERADA</option>
                <option value="PESO ADECUADO PARA LA TALLA">PESO ADECUADO</option>
                <option value="RIESGO DE DESNUTRICIÓN AGUDA">RIESGO DE DESNUTRICIÓN AGUDA</option>
                <option value="DESNUTRICIÓN AGUDA SEVERA TIPO MARASMO">MARASMO</option>
                <option value="DESNUTRICIÓN AGUDA SEVERA TIPO KWASHIORKOR">KWASHIORKOR</option>
                <option value="DESNUTRICIÓN AGUDA SEVERA MIXTA">MIXTA</option>
                <option value="EN PROCESO DE RECUPERACION">EN PROCESO</option>
                <option value="BUSQUEDA FALLIDA">BUSQUEDA FALLIDA</option>
                <option value="PROCESO DE RECUPERACION">PROCESO DE RECUPERACION</option>
                <option value="RECUPERADO">RECUPERADO</option>
                <option value="FALLECIDO">FALLECIDO</option>
              </select>
            </div>
            {{-- Tratamiento f75 --}}
            <div class="col-md-4">
              <label for="tratamiento_f75">Tratamiento f75</label>
              <select class="person2 form-control" name="tratamiento_f75" id="tratamiento_f75" required>
                <option value="">SELECCIONAR</option>
                <option value="SI">SI</option>
                <option value="NO">NO</option>
              </select>
            </div>
            {{-- Fecha recibe f75 --}}
            <div class="col-md-4" id="row_fecha_recibio_tratf75" style="display:none;">
              <label for="fecha_recibio_tratf75">Fecha recibe f75</label>
              <input class="form-control" type="date" name="fecha_recibio_tratf75" id="fecha_recibio_tratf75">
            </div>
          </div>

          {{-- Card: Demanda Inducida --}}
          <div class="card card-custom mb-4 mt-5">

            <div class="card-header text-center">
              <strong>DEMANDA INDUCIDA</strong>
            </div>
            <div class="card-body">
              <div class="row g-3">
                {{-- Esquema PAI --}}
                <div class="col-12 col-md-6">
                  <label for="Esquemq_complrto_pai_edad">Esquema PAI</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-syringe"></i></span>
                    <select
                      class="person2 form-control"
                      name="Esquemq_complrto_pai_edad"
                      id="Esquemq_complrto_pai_edad"
                      required>
                      <option value="">SELECCIONAR</option>
                      <option value="INCOMPLETO">INCOMPLETO</option>
                      <option value="COMPLETO">COMPLETO</option>
                    </select>
                  </div>
                </div>

                {{-- Promoción/Mantenimiento --}}
                <div class="col-12 col-md-6">
                  <label for="Atecion_primocion_y_mantenimiento_res3280_2018">
                    Atención ruta promoción y mantenimiento
                  </label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-route"></i></span>
                    <select
                      class="person2 form-control"
                      name="Atecion_primocion_y_mantenimiento_res3280_2018"
                      id="Atecion_primocion_y_mantenimiento_res3280_2018"
                      required>
                      <option value="">SELECCIONAR</option>
                      <option value="SI">SI</option>
                      <option value="NO">NO</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Card: Cierre de Caso --}}
          <div class="card card-custom mb-4">
            <div class="card-header text-center">
              <i class="fas fa-check-circle"></i> Cierre de Caso
            </div>
            <div class="card-body">
              <div class="row g-3">
                {{-- Estado --}}
                <div class="col-12 col-md-4">
                  <label for="estado">¿Desea cerrar el caso?</label>
                  <select
                    class="person2 form-control"
                    name="estado"
                    id="estado"
                    required>
                    <option value="">SELECCIONAR</option>
                    <option value="1">ABIERTO</option>
                    <option value="0">CERRADO</option>
                  </select>
                </div>

                {{-- Próx. Seguimiento --}}
                <div class="col-12 col-md-4" id="input_oculto">
                  <label for="fecha_proximo_control">Fecha Próx. Seguimiento</label>
                  <input
                    class="form-control"
                    type="date"
                    name="fecha_proximo_control"
                    id="fecha_proximo_control">
                </div>

                {{-- Motivo de reapertura --}}
            </div>
            {{-- PDF Adjunto --}}
            <div class="col-12 col-md-4">
              <label for="pdf">PDF Adjunto</label>
              <input type="file"
                     name="pdf"
                     id="pdf"
                     class="form-control-file"
                     required>
            </div>
          </div>
              </div>
            </div>
          </div>

          {{-- Botón enviar --}}
          <div class="text-center mt-4">
            <button id="update-btn" type="button" class="btn-gradient btn-lg" onclick="submitForm()">
              <span id="button-text">ENVIAR</span>
              <span id="loading-icon" class="spinner-border spinner-border-sm" style="display:none;"></span>
              <span id="sending-text" style="display:none;">Enviando correo…</span>
            </button>
            <a href="{{ url('Seguimiento') }}" class="btn btn-secondary btn-lg mx-2">REGRESAR</a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>


@section('js')
<script>
  $(function(){
    // Select2 initialization
    $('.person, .person2, #cargue412_id').select2({
      placeholder: 'Seleccione...',
      allowClear: true,
      width: 'resolve'
    });
    $('.js-example-basic-multiple').select2({
      placeholder: 'Seleccione medicamento(s)...',
      multiple: true,
      closeOnSelect: false,
      allowClear: true,
      width: 'resolve'
    });

    // Estado → Próx. Seguimiento + Motivo
    $('#estado').on('change', function(){
      if (this.value === '0') {
        $('#input_oculto').slideUp();
        $('#fecha_proximo_control').val('');
        $('#inputsuperoculto').slideDown();
      } else {
        $('#input_oculto').slideDown();
        $('#inputsuperoculto').slideUp().find('textarea').val('');
      }
    }).trigger('change');

    // Tratamiento f75 → Fecha recibe f75
    $('#tratamiento_f75').on('change', function(){
      if (this.value === 'SI') {
        $('#row_fecha_recibio_tratf75').slideDown();
      } else {
        $('#row_fecha_recibio_tratf75').slideUp().find('input').val('');
      }
    }).trigger('change');
  });

  function submitForm(){
    // show spinner/text
    $('#button-text').hide();
    $('#loading-icon, #sending-text').show();
    $('#update-btn').prop('disabled', true);
    // small delay to render
    setTimeout(function(){
      $('#update-form').submit();
    }, 100);
  }
</script>
@stop
