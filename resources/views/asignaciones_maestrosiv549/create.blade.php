@extends('adminlte::page')

@section('title', 'Asignar Caso MaestroSiv549')

@section('content_header')
    <h1 class="text-info"></h1>
@stop

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-12 col-xl-10">
    <div class="card card-custom shadow">
      <div class="card-header d-flex flex-column align-items-center justify-content-center" style="background: linear-gradient(135deg,#1d9bf0,#17bf63); min-height: 84px;">
        <h2 class="card-title title-centered mb-0">
            <span class="icon-bounce">
                <i class="fas fa-baby"></i>
            </span>
            Asignar Caso MaestroSiv549
        </h2>
      </div>
      <div class="card-body">
        {{-- Mensajes de error --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Mensaje de éxito --}}
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('asignaciones_maestrosiv549.store') }}" method="POST">
            @csrf

            {{-- Select de usuario profesional y llamativo --}}
            <div class="form-group mb-4 text-center position-relative" id="select-usuario-group">
                <label for="user_id" class="font-weight-bold text-danger" style="font-size: 1.4rem;">
                    <i class="fas fa-user-md"></i> Asignar Prestador Primario
                    @if($nombre_ips_primaria)
                        <span class="badge badge-info ml-2">IPS Primaria: {{ $nombre_ips_primaria }}</span>
                    @endif
                    <span class="badge badge-warning ml-2" id="badge-select">¡Selecciona aquí!</span>
                </label>
             <select 
    name="user_ids[]" 
    id="user_ids" 
    class="form-control select2-user" 
    multiple
    required
    style="font-size:1.1rem;"
    @if(count($usuarios_prestador_primario) == 0) disabled @endif
>
    @foreach($usuarios as $user)
        <option 
            value="{{ $user->id }}"
            @if(in_array($user->id, $usuarios_prestador_primario)) selected @endif
            style="{{ in_array($user->id, $usuarios_prestador_primario) ? 'font-weight:bold; color:#17a2b8;' : '' }}"
        >
            {{ in_array($user->id, $usuarios_prestador_primario) ? '★ ' : '' }}
            {{ $user->name }} ({{ $user->email }}) - {{ $user->codigohabilitacion }}
        </option>
    @endforeach
</select>

                @if(count($usuarios_prestador_primario) == 0)
                    <div class="alert alert-warning mt-2">
                        <b>No hay usuarios con el código de habilitación <b>{{ $codigo_habilitacion }}</b> y nombre terminado en <b>_ges</b>.</b>
                        Puedes asignar a otro usuario si es necesario.
                    </div>
                @else
                    <small class="form-text text-info">
                        ★ Indica prestador primario sugerido según código de habilitación ".
                    </small>
                @endif
            </div>

            <hr>
            <h5 class="mb-3"><i class="fas fa-user-edit"></i> Datos del caso (editables)</h5>
            <div class="row">
            @foreach($datosCaso as $campo => $valor)
                <div class="form-group col-sm-6 col-md-4 mb-3">
                    <label for="{{ $campo }}" class="font-weight-bold">{{ ucwords(str_replace('_', ' ', $campo)) }}</label>
                    <input 
                        type="text" 
                        class="form-control"
                        name="{{ $campo }}" 
                        id="{{ $campo }}" 
                        value="{{ old($campo, $valor ?? '') }}">
                </div>
            @endforeach
            </div>

            <div class="d-flex justify-content-end mt-4 position-relative">
                <button type="submit" class="btn btn-gradient mr-2" id="btn-asignar-guardar">
                    <i class="fas fa-user-check"></i> Asignar y Guardar
                </button>
                <a href="{{ route('maestrosiv549.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>

        {{-- FLECHA FLOTANTE PARA IR AL BOTÓN --}}
        <button type="button" 
                class="floating-arrow" 
                id="goToGuardarBtn"
                title="Ir al botón Asignar y Guardar">
            <i class="fas fa-arrow-down"></i>
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .card-custom {
    background: #fff;
    border-radius: 1.2rem;
    box-shadow: 0 .5rem 1.5rem rgba(23,162,184,.15);
    border: none;
    margin-bottom: 2rem;
  }
  .card-custom .card-header {
    background: linear-gradient(135deg,#1d9bf0,#17bf63);
    color: #fff;
    font-weight: 700;
    font-size: 1.4rem;
    border-top-left-radius: 1.2rem;
    border-top-right-radius: 1.2rem;
    padding: 1.1rem 0;
  }
  .btn-gradient {
    background: linear-gradient(45deg,#1d9bf0,#17bf63);
    color: #fff !important;
    border: none;
    border-radius: .75rem;
    padding: .7rem 2rem;
    font-weight: 600;
    font-size: 1.1rem;
    transition: opacity .2s;
  }
  .btn-gradient:hover { opacity:.95; }
  .form-group label { color: #17a2b8; font-size: 1.05rem;}
  .form-group input.form-control { border-radius: .7rem; border-color:#d1ecf1;}
  .badge-info { font-size:1rem; }
  @media(max-width:767px){
    .card-custom{ margin:1rem 0; }
    .btn-gradient, .btn-secondary{ width:100%; margin-bottom:1rem; }
  }

  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Roboto:wght@400;700&display=swap');

  .title-centered {
      font-family: 'Montserrat', 'Roboto', Arial, sans-serif;
      color: #fff;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: .02em;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.8rem;
      margin-bottom: 0;
      width: 100%;
      text-align: center;
      min-height: 50px;
  }

  .icon-bounce {
      display: inline-block;
      animation: bounce 1.5s infinite;
      font-size: 2.5rem;
      color: #fff;
      margin-right: .35rem;
      vertical-align: middle;
      filter: drop-shadow(0 3px 14px #17a2b8);
  }
  @keyframes bounce {
      0%, 100% { transform: translateY(0);}
      30% { transform: translateY(-18px);}
      50% { transform: translateY(-8px);}
      70% { transform: translateY(-12px);}
  }

  /* Select2 styles */
  .select2-container--default .select2-selection--single {
      border: 2.5px solid #17a2b8;
      border-radius: 1rem !important;
      font-size: 1.13rem;
      padding: .18rem 0.7rem;
      min-height: 46px;
      box-shadow: 0 1px 12px 0 #b0f6ff36;
      transition: border .26s;
      background: #f8fafc;
  }
  .select2-user:focus, .border-select-anim .select2-selection--single {
      border: 2.5px solid #1d9bf0 !important;
      box-shadow: 0 0 0 4px #17bf636e !important;
  }
  .select2-results__option--highlighted[aria-selected] {
      background: #e0f7fa !important;
      color: #17a2b8 !important;
  }

  #badge-select {
      font-size: .95rem;
      background: linear-gradient(90deg,#f7c873,#ffe193);
      color: #9c6800;
      border-radius: 10px;
      font-weight: 600;
      animation: pulse 1.3s infinite alternate;
  }
  @keyframes pulse {
      to { background: linear-gradient(90deg,#ffe193,#f7c873); color:#ff9800; }
  }

  .btn-pop {
      animation: pop-btn .55s cubic-bezier(.2,1.5,.5,1.1);
  }
  @keyframes pop-btn {
      0% { transform: scale(1); }
      20% { transform: scale(1.18); box-shadow:0 6px 26px #1d9bf021;}
      65% { transform: scale(0.97);}
      100% { transform: scale(1);}
  }

  /* FLECHA FLOTANTE */
  .floating-arrow {
      position: fixed;
      right: 34px;
      bottom: 44px;
      z-index: 1031;
      background: linear-gradient(135deg,#1d9bf0,#17bf63);
      color: #fff;
      border: none;
      outline: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      box-shadow: 0 4px 18px rgba(23,162,184,.15);
      font-size: 2.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background .22s, transform .21s;
      animation: arrow-bounce 1.6s infinite;
      cursor: pointer;
  }
  .floating-arrow:hover {
      background: linear-gradient(135deg,#117a8b,#1d9bf0);
      transform: scale(1.08) rotate(-3deg);
      color: #ffe193;
  }
  @keyframes arrow-bounce {
      0%, 100% { transform: translateY(0);}
      40% { transform: translateY(-17px);}
      65% { transform: translateY(-10px);}
      85% { transform: translateY(-5px);}
  }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializa Select2
    $('.select2-user').select2({
        placeholder: "-- Selecciona un usuario --",
        width: '100%',
        allowClear: true
    });

    // Efecto de borde animado al enfocar
    $('.select2-user').on('select2:open', function(e) {
        $('#select-usuario-group').addClass('border-select-anim');
    }).on('select2:close', function(e) {
        $('#select-usuario-group').removeClass('border-select-anim');
    });

    // Desaparece badge al seleccionar
    $('#user_id').on('change', function() {
        $('#badge-select').fadeOut(250);

        // Scroll hasta el botón de asignar y guardar
        if (this.value) {
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $('#btn-asignar-guardar').offset().top - 80
                }, 600);
                // Agrega animación al botón
                $('#btn-asignar-guardar').addClass('btn-pop');
                setTimeout(function() {
                    $('#btn-asignar-guardar').removeClass('btn-pop');
                }, 1100);
            }, 300);
        }
    });

    // FLECHA FLOTANTE scroll a botón
    $('#goToGuardarBtn').on('click', function() {
        $('html, body').animate({
            scrollTop: $('#btn-asignar-guardar').offset().top - 80
        }, 650);
        $('#btn-asignar-guardar').addClass('btn-pop');
        setTimeout(function() {
            $('#btn-asignar-guardar').removeClass('btn-pop');
        }, 1300);
    });
});
</script>
@stop
