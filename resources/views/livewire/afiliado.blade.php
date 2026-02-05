@extends('adminlte::page')

@section('title', 'PAI')

@section('content_header')
<div class="pai-topbar">
    <div class="pai-topbar__left">
        <div class="pai-brand">
            <div class="pai-brand__logo">PAI</div>
            <div class="pai-brand__text">
                <div class="pai-brand__title">Cargue Registro Diario</div>
                <div class="pai-brand__subtitle">Importación masiva de afiliados y vacunas (Excel .xlsx / .xls)</div>
            </div>
        </div>
    </div>

    <div class="pai-topbar__right">
        <a href="{{ route('download.excel') }}" class="btn btn-pai btn-pai-secondary">
            <i class="fas fa-file-download mr-2"></i> Descargar formato
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">

    {{-- ✅ Mensajes dentro del DOM (si usas include) --}}
    <div id="mensajes-container" class="mb-3">
        @include('livewire.mensajes')
    </div>

    <div class="row">
        {{-- ===== Panel Importación ===== --}}
        <div class="col-lg-5 col-xl-4">
            <div class="pai-card">
                <div class="pai-card__head">
                    <div>
                        <div class="pai-card__title">Importar archivo</div>
                        <div class="pai-card__hint">Arrastra y suelta tu Excel o haz clic para seleccionarlo.</div>
                    </div>
                    <div class="pai-card__icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                </div>

                <form action="{{ route('import-excel_2') }}" method="POST" enctype="multipart/form-data" id="file-upload-form">
                    @csrf

                    <div id="drag-drop-area" class="pai-dropzone">
                        <input type="file" name="file" id="file_input" class="pai-file" accept=".xlsx,.xls">

                        <div class="pai-dropzone__inner">
                            <div class="pai-dropzone__logo">
                                <i class="far fa-file-excel"></i>
                            </div>

                            <div class="pai-dropzone__text">
                                <div class="pai-dropzone__title">Suelta tu archivo aquí</div>
                                <div class="pai-dropzone__sub">o haz clic para buscar en tu equipo</div>
                            </div>

                            <div class="pai-filemeta" id="filemeta" style="display:none;">
                                <div class="pai-filemeta__name" id="filename">archivo.xlsx</div>
                                <div class="pai-filemeta__sub" id="filesub">Listo para importar</div>
                            </div>

                            <div class="pai-dropzone__footer">
                                <span class="pai-pill"><i class="fas fa-shield-alt mr-1"></i> Carga segura</span>
                                <span class="pai-pill"><i class="fas fa-file-excel mr-1"></i> .xlsx / .xls</span>
                                <span class="pai-pill"><i class="fas fa-clock mr-1"></i> Proceso asistido</span>
                            </div>
                        </div>
                    </div>

                    <div id="date-warning" class="alert alert-warning mt-3 text-center" style="display:none;">
                        El formulario no está disponible fuera del rango de fechas permitido.
                    </div>

                    <button type="submit" class="btn btn-pai btn-pai-primary btn-block mt-3" id="submit-button">
                        <i class="fas fa-play mr-2"></i> Iniciar importación
                    </button>

                    <div class="pai-minihelp mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        El sistema validará el archivo antes de guardar información.
                    </div>
                </form>
            </div>

            {{-- Modal loading clásico (lo mantengo) --}}
            <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel"
                aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-center">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title w-100" id="loadingModalLabel">Procesando importación</h5>
                        </div>
                        <div class="modal-body pt-2">
                            <div class="pai-spinner">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <p class="text-muted mb-0">Por favor espera… esto puede tardar según el tamaño del archivo.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===== Tabla + Modales ===== --}}
        <div class="col-lg-7 col-xl-8">
            @include('livewire.tabla')
            @include('livewire.modal_tabla')
        </div>
    </div>

    {{-- Modal correo (tu modal original) --}}
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Enviar Correo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="patientId">
                        <input type="hidden" id="patientName">

                        <div class="form-group">
                            <label for="emailSubject">Asunto</label>
                            <input type="text" class="form-control" id="emailSubject" placeholder="Asunto">
                        </div>
                        <div class="form-group">
                            <label for="emailMessage">Mensaje</label>
                            <textarea class="form-control" id="emailMessage" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="emailPatientName">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="sendEmailButton">Enviar Correo</button>
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css" rel="stylesheet">

@include('livewire.css')

<style>
/* ====== Topbar ====== */
.pai-topbar{display:flex; justify-content:space-between; align-items:center; padding:18px 10px; margin-bottom:10px; border-bottom:1px solid rgba(0,0,0,.08);}
.pai-brand{display:flex; align-items:center; gap:14px;}
.pai-brand__logo{background:linear-gradient(135deg,#0ea5e9,#2563eb); color:#fff; font-weight:900; border-radius:14px; padding:10px 12px; box-shadow:0 10px 25px rgba(37,99,235,.25); letter-spacing:.5px;}
.pai-brand__title{font-weight:900; color:#0f172a; font-size:1.25rem; line-height:1.1;}
.pai-brand__subtitle{color:#64748b; font-size:.92rem; margin-top:2px;}

/* ====== Buttons ====== */
.btn-pai{border-radius:12px; padding:10px 14px; font-weight:800; transition:transform .15s ease, box-shadow .15s ease; box-shadow:0 12px 25px rgba(0,0,0,.08);}
.btn-pai:hover{transform:translateY(-1px); box-shadow:0 18px 35px rgba(0,0,0,.10);}
.btn-pai-primary{background:linear-gradient(135deg,#2563eb,#0ea5e9); border:none; color:#fff;}
.btn-pai-secondary{background:#fff; border:1px solid rgba(0,0,0,.08); color:#0f172a;}

/* ====== Card ====== */
.pai-card{background:#fff; border-radius:18px; padding:18px; box-shadow:0 16px 40px rgba(2,6,23,.08); border:1px solid rgba(15,23,42,.06);}
.pai-card__head{display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;}
.pai-card__title{font-weight:900; color:#0f172a; font-size:1.1rem;}
.pai-card__hint{color:#64748b; font-size:.92rem;}
.pai-card__icon{width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:rgba(37,99,235,.10); color:#2563eb; font-size:20px;}

/* ====== Dropzone ====== */
.pai-dropzone{position:relative; border-radius:18px; border:2px dashed rgba(37,99,235,.35); background:linear-gradient(180deg, rgba(37,99,235,.06), rgba(14,165,233,.04)); padding:20px; cursor:pointer; overflow:hidden;}
.pai-dropzone.drag-over{border-color:#2563eb; box-shadow:0 0 0 4px rgba(37,99,235,.15); transform:translateY(-1px);}
.pai-file{position:absolute; inset:0; opacity:0; cursor:pointer;}
.pai-dropzone__inner{display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center;}
.pai-dropzone__logo{width:56px; height:56px; border-radius:18px; display:flex; align-items:center; justify-content:center; background:rgba(16,185,129,.12); color:#10b981; font-size:26px;}
.pai-dropzone__title{font-weight:900; color:#0f172a;}
.pai-dropzone__sub{color:#64748b; font-size:.95rem;}
.pai-dropzone__footer{display:flex; gap:8px; margin-top:6px; flex-wrap:wrap; justify-content:center;}
.pai-pill{font-size:.82rem; padding:6px 10px; border-radius:999px; border:1px solid rgba(0,0,0,.07); color:#334155; background:#fff;}
.pai-filemeta{width:100%; border-radius:14px; padding:10px 12px; border:1px solid rgba(0,0,0,.08); background:#fff; text-align:left; max-width:520px;}
.pai-filemeta__name{font-weight:900; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
.pai-filemeta__sub{color:#64748b; font-size:.9rem;}
.pai-minihelp{color:#64748b; font-size:.9rem; display:flex; align-items:center;}

/* ====== Loading modal tweaks ====== */
.pai-spinner{display:flex; justify-content:center; padding:14px 0;}
</style>
@stop

@section('js')

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>

@include('livewire.javascript')
<script>
(function(){

  // =========================================================
  // 0) FIX DEFINITIVO: cerrar modales Bootstrap sin congelar
  // =========================================================
  function forceCloseBootstrapModal(modalSelector){
      try{
          const $m = $(modalSelector);
          $m.modal('hide');

          setTimeout(() => {
              $('body').removeClass('modal-open');
              $('body').css('padding-right','');
              $('.modal-backdrop').remove();

              $m.removeClass('show');
              $m.attr('aria-hidden','true');
              $m.css('display','none');
          }, 200);
      }catch(e){}
  }

  // Limpieza extra si el usuario cierra el modal manualmente
  $(document).on('hidden.bs.modal', '#exportModal', function () {
      $('body').removeClass('modal-open');
      $('body').css('padding-right','');
      $('.modal-backdrop').remove();
  });

  // =========================================================
  // 1) DROPZONE UI + VALIDACIÓN EXCEL
  // =========================================================
  const dz = document.getElementById('drag-drop-area');
  const input = document.getElementById('file_input'); // ✅ debe existir en tu vista
  const filemeta = document.getElementById('filemeta');
  const filename = document.getElementById('filename');
  const filesub = document.getElementById('filesub');

  function showFile(file){
      const ok = file && (
          file.name.toLowerCase().endsWith('.xlsx') ||
          file.name.toLowerCase().endsWith('.xls')
      );

      if(!ok){
          Swal.fire({
              icon: 'warning',
              title: 'Archivo inválido',
              text: 'Sube un Excel (.xlsx o .xls)',
              toast: true,
              position: 'top-end',
              timer: 4500,
              showConfirmButton: false
          });
          if(input) input.value = '';
          if(filemeta) filemeta.style.display='none';
          return false;
      }

      if(filemeta) filemeta.style.display='block';
      if(filename) filename.textContent = file.name;
      if(filesub) filesub.textContent = 'Listo para importar';
      return true;
  }

  if(dz && input){
      dz.addEventListener('dragover', (e)=>{ e.preventDefault(); dz.classList.add('drag-over'); });
      dz.addEventListener('dragleave', ()=> dz.classList.remove('drag-over'));
      dz.addEventListener('drop', (e)=>{
          e.preventDefault(); dz.classList.remove('drag-over');
          if(e.dataTransfer.files && e.dataTransfer.files[0]){
              input.files = e.dataTransfer.files;
              showFile(input.files[0]);
          }
      });
      dz.addEventListener('click', ()=> input.click());
      input.addEventListener('change', ()=> {
          if(input.files && input.files[0]) showFile(input.files[0]);
      });
  }

  // =========================================================
  // 2) SUBMIT IMPORT: ABRE MODAL "CARGANDO"
  // =========================================================
  $('#file-upload-form').on('submit', function(){
      if(!input || !input.files || !input.files[0]){
          Swal.fire({
              icon: 'info',
              title: 'Falta archivo',
              text: 'Selecciona un Excel antes de importar.',
              toast: true,
              position: 'top-end',
              timer: 4500,
              showConfirmButton: false
          });
          return false;
      }
      if(!showFile(input.files[0])) return false;

      $('#loadingModal').modal('show');
  });

  // =========================================================
  // 3) RANGO DE FECHAS (1 al 5 del mes siguiente)
  // =========================================================
  const submitButton = document.getElementById("submit-button");
  const dateWarning = document.getElementById("date-warning");

  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonth = currentDate.getMonth();

  const startDate = new Date(currentYear, currentMonth, 1);
  let endDate = new Date(currentYear, currentMonth + 1, 5);
  if (currentMonth === 11) endDate = new Date(currentYear + 1, 0, 5);

  if (!(currentDate >= startDate && currentDate <= endDate)) {
      if(submitButton) submitButton.disabled = true;
      if(dateWarning) dateWarning.style.display = "block";
      if(dz){
          dz.style.pointerEvents = 'none';
          dz.style.opacity = '0.55';
      }
  }

  // =========================================================
  // 4) SWEETALERT MENSAJES (SESSION)
  // =========================================================
  document.addEventListener('DOMContentLoaded', function(){

      @if (Session::has('success'))
          Swal.fire({
              icon: 'success',
              title: 'Proceso completado',
              text: @json(Session::get('success')),
              toast: true,
              position: 'top-end',
              timer: 5000,
              showConfirmButton: false
          });
      @endif

      @if (Session::has('error1'))
          @if (is_array(Session::get('error1')))
              let errorsHtml = '<ul style="margin:0; padding-left:18px;">';
              @foreach (Session::get('error1') as $error)
                  errorsHtml += '<li>{{ $error }}</li>';
              @endforeach
              errorsHtml += '</ul>';

              Swal.fire({
                  icon: 'error',
                  title: 'Se encontraron problemas',
                  html: errorsHtml,
                  confirmButtonText: 'Entendido'
              });
          @else
              Swal.fire({
                  icon: 'error',
                  title: 'Ocurrió un error',
                  text: @json(Session::get('error1')),
                  confirmButtonText: 'Entendido'
              });
          @endif
      @endif

      @if (count($errors) > 0)
          let validationErrors = '<ul style="margin:0; padding-left:18px;">';
          @foreach ($errors->all() as $error)
              validationErrors += '<li>{{ $error }}</li>';
          @endforeach
          validationErrors += '</ul>';

          Swal.fire({
              icon: 'warning',
              title: 'Errores de validación',
              html: validationErrors,
              confirmButtonText: 'Revisar'
          });
      @endif
  });

  // =========================================================
  // 5) BUSCADOR AJAX + FILTRO TABLA
  // =========================================================
  let searchTimer = null;

  $('#search').on('keyup', function(){
      const query = $(this).val().trim();
      clearTimeout(searchTimer);

      if(query.length < 2){
          $('#search-results').hide().empty();
          return;
      }

      searchTimer = setTimeout(function(){

          $('#search-results').show().empty()
              .append('<div style="text-align:center; padding:10px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

          $.ajax({
              url: "{{ route('buscar.afiliados') }}",
              method: "GET",
              dataType: "json",
              data: { search: query },
              success: function(data){
                  $('#search-results').empty();

                  if(data.length > 0){
                      $.each(data, function(index, afiliado){

                          const nombre = [
                              afiliado.primer_nombre,
                              afiliado.segundo_nombre,
                              afiliado.primer_apellido,
                              afiliado.segundo_apellido
                          ].filter(Boolean).join(' ');

                          $('#search-results').append(
                              '<a href="#" class="list-group-item list-group-item-action search-result-item" ' +
                              'data-id="'+afiliado.numero_identificacion+'">' +
                              (afiliado.tipo_identificacion ? afiliado.tipo_identificacion + ' ' : '') +
                              afiliado.numero_identificacion + ' – ' + nombre +
                              '</a>'
                          );
                      });
                  }else{
                      $('#search-results').append('<a href="#" class="list-group-item list-group-item-action disabled">No se encontraron resultados</a>');
                  }
              },
              error: function(xhr){
                  $('#search-results').empty().append('<a href="#" class="list-group-item list-group-item-action disabled">Error en la búsqueda</a>');
                  console.log("Error AJAX buscador:", xhr.responseText);
              }
          });

      }, 250);
  });

  $(document).on('click', '.search-result-item', function(e){
      e.preventDefault();

      const numeroIdentificacion = $(this).data('id');
      $('#search').val(numeroIdentificacion);
      $('#search-results').hide().empty();

      $.ajax({
          url: "{{ route('afiliado') }}",
          method: "GET",
          dataType: "json",
          data: { search: numeroIdentificacion },
          success: function(response){

              const rows = response.sivigilas_usernormal || response.sivigilas || [];
              const $tbody = $('#sivigila tbody');
              $tbody.empty();

              $.each(rows, function(index, r){

                  const fullName = [r.primer_nombre, r.segundo_nombre, r.primer_apellido, r.segundo_apellido]
                      .filter(Boolean).join(' ');

                  const carnet = (r.numero_carnet ?? 0);

                  const acciones = '<a href="#" class="btn btn-sm btn-warning blinking-button send-email" data-toggle="modal" data-target="#emailModal" data-id="'+r.id+'" data-name="'+fullName+'"><i class="fas fa-envelope"></i> Solicitud</a>';

                  $tbody.append(
                      '<tr>' +
                          '<td><small>'+ (r.id ?? '') +'</small></td>' +
                          '<td><a href="#" class="numero-identificacion" data-id="'+(r.id ?? '')+'" data-carnet="'+carnet+'" style="text-decoration:underline;">'+ (r.numero_identificacion ?? '') +'</a></td>' +
                          '<td><small>'+fullName+'</small></td>' +
                          '<td><small>'+(r.numero_carnet ?? (r.batch_verifications_id ?? '') )+'</small></td>' +
                          '<td>'+acciones+'</td>' +
                      '</tr>'
                  );
              });

          },
          error: function(xhr){
              console.log("Error filtrando tabla:", xhr.responseText);
          }
      });
  });

  $(document).on('click', function(e){
      if(!$(e.target).closest('.search-container').length){
          $('#search-results').hide().empty();
      }
  });

  // =========================================================
  // 6) CLICK DOCUMENTO -> MODAL VACUNAS (TU RUTA REAL)
  // Route::get('/vacunas/{id}/{numeroCarnet?}', ...)->name('getVacunas');
  // =========================================================
  $(document).on('click', '.numero-identificacion', function(e){
      e.preventDefault();

      const id = $(this).data('id');
      let carnet = $(this).data('carnet');

      if(carnet === undefined || carnet === null || carnet === ''){
          carnet = 0;
      }

      let url = "{{ route('getVacunas', ['id' => ':id', 'numeroCarnet' => ':carnet']) }}";
      url = url.replace(':id', encodeURIComponent(id));
      url = url.replace(':carnet', encodeURIComponent(carnet));

      $.ajax({
          url: url,
          method: 'GET',
          success: function(data){

              $('#vacunaList').empty();

              if(!data || data.length === 0){
                  $('#vacunaList').append('<tr><td colspan="7" class="text-center">No se encontraron vacunas</td></tr>');
                  $('#vacunaModal').modal('show');
                  return;
              }

              const nombreCompleto = [data[0].prim_nom, data[0].seg_nom, data[0].pri_ape, data[0].seg_ape]
                  .filter(Boolean).join(' ');

              $('#nombrePaciente').text(nombreCompleto);
              $('#tipoIdentificacion').text(data[0].tipo_id ?? '');
              $('#identificacion').text(data[0].numero_id ?? '');
              $('#sexo').text(data[0].genero ?? '');
              $('#fechaNacimiento').text(data[0].fecha_nacimiento ?? '');
              $('#ips').text(data[0].ips ?? '');
              $('#edad').text(data[0].age ?? '');

              data.forEach(function(v){
                  $('#vacunaList').append(
                      '<tr>' +
                          '<td>' + (v.nombre_vacuna ?? '') + '</td>' +
                          '<td>' + (v.docis_vacuna ?? '') + '</td>' +
                          '<td>' + (v.fecha_vacunacion ?? '') + '</td>' +
                          '<td>' + (v.edad_anos ?? '') + '</td>' +
                          '<td>' + (v.total_meses ?? '') + '</td>' +
                          '<td>' + (v.nombre_usuario ?? '') + '</td>' +
                          '<td>' + (v.responsable ? v.responsable : '---') + '</td>' +
                      '</tr>'
                  );
              });

              $('#vacunaModal').modal('show');
          },
          error: function(xhr){
              console.log("Error getVacunas:", xhr.responseText);
              Swal.fire({
                  title: 'Error',
                  text: 'No se pudo cargar el detalle de vacunas.',
                  icon: 'error'
              });
          }
      });
  });

  // =========================================================
  // 7) MODAL CORREO
  // =========================================================
  $(document).on('click', '.send-email', function(){
      const patientId = $(this).data('id');
      const patientName = $(this).data('name');

      $('#patientName').val(patientName);
      $('#patientId').val(patientId);

      $('#emailModal').modal('show');
  });

  // =========================================================
  // 8) EXPORTAR REPORTE (DESCARGA SIN CONGELAR)
  // =========================================================
  $('#exportButton').on('click', function (e) {
      e.preventDefault();

      const start = $('#start_date').val();
      const end   = $('#end_date').val();

      if (!start || !end) {
          Swal.fire({
              icon: 'warning',
              title: 'Fechas requeridas',
              text: 'Selecciona fecha de inicio y fecha final.',
          });
          return;
      }

      $('#button-text').hide();
      $('#loading-icon').show();
      $('#sending-text').show();
      $('#exportButton').prop('disabled', true);

      const url = "{{ route('exportVacunas') }}";

      function resetExportUI(){
          $('#button-text').show();
          $('#loading-icon').hide();
          $('#sending-text').hide();
          $('#exportButton').prop('disabled', false);
      }

      $.ajax({
          url: url,
          type: 'GET',
          data: { start_date: start, end_date: end },
          xhrFields: { responseType: 'blob' },

          success: function (blob, status, xhr) {

              const disposition = xhr.getResponseHeader('Content-Disposition') || '';
              let filename = 'reporte.xlsx';
              const match = disposition.match(/filename="?([^"]+)"?/);
              if (match && match[1]) filename = match[1];

              const downloadUrl = window.URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = downloadUrl;
              a.download = filename;
              document.body.appendChild(a);
              a.click();
              a.remove();
              window.URL.revokeObjectURL(downloadUrl);

              // ✅ cerrar modal y limpiar backdrop
              forceCloseBootstrapModal('#exportModal');

              resetExportUI();
          },

          error: function (xhr) {
              console.log("Export error:", xhr);

              // ✅ cerrar modal y limpiar backdrop
              forceCloseBootstrapModal('#exportModal');

              resetExportUI();

              // fallback normal
              const fallback = url + '?start_date=' + encodeURIComponent(start) + '&end_date=' + encodeURIComponent(end);
              window.location.href = fallback;
          }
      });
  });

})();
</script>


@stop
