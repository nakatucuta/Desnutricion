{{-- resources/views/seguimiento/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Nuevo Seguimiento')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
  /* === Select2 personalizado === */
  .select2-container { width: 100% !important; }
  .select2-results__option { font-size: 14px; color: #333; }
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #fff !important;
    color: #ec0b0b !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff !important;
  }
  /* === Tags de Select2 para Medicamento === */
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: linear-gradient(45deg,#17bf63,#1d9bf0);
    color: #fff; border: none; border-radius: .75rem;
    padding: .25rem .75rem; margin-right: .5rem;
    font-size: .875rem; font-weight: 500;
    transition: transform .2s ease, opacity .2s ease;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
    transform: scale(1.05);
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: rgba(255,255,255,0.8) !important;
    margin-left: .5rem; font-size: 1rem; transition: color .2s ease;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #ffdddd !important;
  }

  /* === Tarjetas modernas === */
  .card-custom {
    background: #fff; border-radius: 1rem;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,0.15);
    border: none; margin-bottom: 1.5rem;
  }
  .card-custom .card-header {
    background: linear-gradient(135deg,#1d9bf0,#17bf63);
    color: #fff; font-weight: 600;
    border-top-left-radius: 1rem; border-top-right-radius: 1rem;
  }

  /* === Inputs con ícono === */
  .input-group-text {
    background: #f0f2f5; border: none;
    border-top-left-radius: .75rem; border-bottom-left-radius: .75rem;
  }
  .form-control {
    border: 1px solid #ddd; border-left: none;
    border-top-right-radius: .75rem; border-bottom-right-radius: .75rem;
  }

  /* === Botones degradados === */
  .btn-gradient {
    background: linear-gradient(45deg,#1d9bf0,#17bf63);
    color: #fff; border: none; border-radius: .75rem;
    padding: .75rem 1.5rem; transition: opacity .3s;
  }
  .btn-gradient:hover { opacity: .9; }

  /* === Overlay fullscreen === */
  #overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1050;
    align-items: center;
    justify-content: center;
  }
  /* Para mostrarlo, JS añadirá la clase `.show` */
  #overlay.show { display: flex !important; }

  #overlay .overlay-content {
    text-align: center; color: #fff;
  }
  #overlay .overlay-text {
    margin-top: 1rem;
    font-size: 1.5rem;
    font-weight: 500;
  }

  /* === Responsive tweaks === */
  @media(max-width:767px){
    .card-custom{ margin:1rem; }
    .btn-gradient{ width:100%; margin-bottom:1rem; }
  }
</style>
@stop

@section('content')
<div class="container-fluid py-4">
  <form id="update-form"
        action="{{ url('Seguimiento') }}"
        method="POST"
        enctype="multipart/form-data">
    @csrf

    {{-- inyecta todo tu form --}}
    @include('seguimiento.form', ['modo' => 'Crear'])



  {{-- Overlay que cubre toda la pantalla al enviar --}}
  <div id="overlay">
    <div class="overlay-content">
      <div class="spinner-border text-light"
           role="status"
           style="width:4rem; height:4rem;"></div>
      <div class="overlay-text">Enviando correo…</div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
  $(function(){
    // Inicializar todos los Select2
    $('#sivigilas_id').select2({
      placeholder: 'Seleccione paciente...',
      allowClear: true,
      width: 'resolve'
    });
    $('#medicamento').select2({
      placeholder: 'Seleccione medicamento(s)...',
      multiple: true,
      closeOnSelect: false,
      allowClear: true,
      width: 'resolve'
    });
    $('#estado').select2({
      placeholder: 'Seleccione estado...',
      width: 'resolve'
    });
    $('#tratamiento_f75').select2({
      placeholder: 'Seleccione tratamiento...',
      width: 'resolve'
    });

    // Mostrar/ocultar Próx. Seguimiento según Estado
    $('#estado').on('change', function(){
      if (this.value === '1') {
        $('#input_oculto').stop(true).slideDown();
      } else {
        $('#input_oculto').stop(true).slideUp();
        $('#fecha_proximo_control').val('');
      }
    }).trigger('change');

    // Mostrar/ocultar Fecha recibe f75 según Tratamiento f75
    $('#tratamiento_f75').on('change', function(){
      if (this.value === 'SI') {
        $('#row_fecha_recibio_tratf75').stop(true).slideDown();
      } else {
        $('#row_fecha_recibio_tratf75').stop(true).slideUp();
        $('#fecha_recibio_tratf75').val('');
      }
    }).trigger('change');
  });

  function submitForm(){
    // 1) Mostrar overlay
    $('#overlay').addClass('show');
    // 2) Deshabilitar botón
    $('#update-btn').prop('disabled', true);
    // 3) Pequeño retraso y submit
    setTimeout(function(){
      $('#update-form').submit();
    }, 100);
  }
</script>
@stop
