@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<style>
    :root{
        --brand-1:#0ea5e9;      /* cian */
        --brand-2:#6366f1;      /* índigo */
        --card-bg: rgba(255,255,255,.88);
        --card-br: 16px;
        --shadow: 0 20px 40px rgba(0,0,0,.25);
        --input-bg: #f8fafc;
        --ring: 0 0 0 3px rgba(14,165,233,.25);
    }

    /* Fondo con gradiente animado + imagen */
    body{
        min-height:100vh;
        background:
            radial-gradient(1200px 800px at 10% 10%, rgba(99,102,241,.25), transparent 60%),
            radial-gradient(1000px 700px at 90% 90%, rgba(14,165,233,.25), transparent 60%),
             url('{{ asset('img/familia-anas-wayuu.webp') }}') /* center/cover no-repeat fixed*/;  
        position:relative;
    }
    body::before{
        content:"";
        position:fixed; inset:0;
        background:linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.55));
        z-index:-1;
    }

    /* Tarjeta glass */
    .login-box .card{
        background:var(--card-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: var(--card-br);
        box-shadow: var(--shadow);
        border:1px solid rgba(255,255,255,.6);
        overflow:hidden;
    }

    .auth-logo-wrap{
        display:flex; align-items:center; justify-content:center;
        gap:.5rem; margin-bottom:.25rem;
        color:#111827; font-weight:800; letter-spacing:.2px;
    }
    .auth-logo-wrap img{ height:80px; width:auto; }

    .auth-subtitle{ text-align:center; color:#374151; margin-bottom:.75rem; }

    /* Segmented control (Correo / Código) */
    .segmented{
        display:flex; gap:.5rem; background:#eef2ff; padding:.35rem; border-radius:999px; margin-bottom:1rem;
    }
    .segmented input{ display:none; }
    .segmented label{
        flex:1; text-align:center; padding:.45rem .75rem; border-radius:999px; cursor:pointer;
        font-weight:600; color:#4338ca; user-select:none; transition:all .2s ease;
        display:flex; align-items:center; justify-content:center; gap:.5rem;
        white-space:nowrap;
    }
    .segmented input:checked + label{
        background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
        color:#fff; box-shadow: 0 6px 20px rgba(99,102,241,.35);
        transform: translateY(-1px);
    }

    /* Inputs */
    .input-group .form-control{
        background:var(--input-bg);
        border-radius:12px 0 0 12px !important;
        border:1px solid #e5e7eb;
        transition: box-shadow .15s ease, border-color .15s ease;
    }
    .input-group:focus-within .form-control{
        border-color: var(--brand-1);
        box-shadow: var(--ring);
    }
    .input-group-text{
        background:#fff; border:1px solid #e5e7eb; border-left:none;
        border-radius:0 12px 12px 0 !important;
    }

    /* Botón primario */
    .btn-brand{
        background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
        border:none; color:#fff; font-weight:700;
        border-radius:12px; padding:.7rem 1rem;
        box-shadow: 0 10px 24px rgba(14,165,233,.35);
        transition: transform .12s ease, box-shadow .2s ease;
    }
    .btn-brand:hover{ transform: translateY(-1px); box-shadow: 0 14px 28px rgba(99,102,241,.35); }

    .field-hint{ font-size:.85rem; color:#6b7280; margin-top:.25rem; }

    /* Errores visibles incluso si se alterna el método */
    .is-invalid ~ .invalid-feedback{ display:block; }

    /* Footer links */
    .auth-footer a{ color:#111827; font-weight:600; text-decoration:underline; }


    /* ===== Card-header VERDE con degradado premium + brillo ===== */
.card-header.bg-gradient-green{
  position: relative;
  color: #ffffff;
  /* degradado verde multiton */
  background: linear-gradient(
    135deg,
    #15803d 0%,   /* verde profundo */
    #16a34a 35%,  /* verde base */
    #22c55e 70%,  /* verde medio */
    #10b981 100%  /* acento esmeralda */
  ) !important;
  border-bottom: 0;
  /* brillo interior sutil + sombra externa */
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.28),
    0 10px 24px rgba(16,185,129,.35);
  /* por si el header no hereda el radio del card */
  border-top-left-radius: var(--card-br, 16px);
  border-top-right-radius: var(--card-br, 16px);
}

/* resalta textos/acciones dentro del header */
.card-header.bg-gradient-green .card-title,
.card-header.bg-gradient-green .card-tools,
.card-header.bg-gradient-green a,
.card-header.bg-gradient-green .btn-tool{
  color: #f0fdf4 !important;
}

/* brillo superior suave */
.card-header.bg-gradient-green::before{
  content:"";
  position:absolute;
  left:0; right:0; top:0; height:55%;
  border-top-left-radius: inherit;
  border-top-right-radius: inherit;
  background: linear-gradient(180deg, rgba(255,255,255,.22), rgba(255,255,255,0));
  pointer-events:none;
}

/* “sheen” (destello) animado */
.card-header.bg-gradient-green::after{
  content:"";
  position:absolute; top:0; left:-30%;
  width:30%; height:100%;
  background: linear-gradient(120deg, rgba(255,255,255,.35), rgba(255,255,255,0) 60%);
  filter: blur(8px);
  transform: skewX(-18deg);
  animation: headerSheen 4s ease-in-out infinite;
  pointer-events:none;
}

@keyframes headerSheen{
  0%   { left:-30%; }
  100% { left:130%; }
}

/* Accesibilidad: si el usuario prefiere menos movimiento, apaga la animación */
@media (prefers-reduced-motion: reduce){
  .card-header.bg-gradient-green::after{ animation: none; }
}

</style>
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header')
    <div class="auth-logo-wrap">
        {{-- Si tienes logo, descomenta: --}}
        {{-- <img src="{{ asset('img/logo-anas-wayuu.png') }}" alt="Anas Wayuu"> --}}
        <span>Acceso a la plataforma</span>
    </div>
    <div class="auth-subtitle">Ingresa con <strong>correo</strong> o <strong>código de habilitación</strong></div>
@stop

@section('auth_body')
<form action="{{ $login_url }}" method="POST" novalidate>
    @csrf

    {{-- Selector de método --}}
    <div class="segmented" role="tablist" aria-label="Método de inicio de sesión">
        <input type="radio" id="tabEmail" name="loginMethod" value="email" checked>
        <label for="tabEmail"><i class="fas fa-at"></i> Correo</label>

        <input type="radio" id="tabCode" name="loginMethod" value="code">
        <label for="tabCode"><i class="fas fa-id-card"></i> Código</label>
    </div>

    {{-- Campo EMAIL (visible por defecto) --}}
    <div class="input-group mb-2" id="groupEmail">
        <input type="email" name="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email') }}"
            placeholder="Correo electrónico"
            autocomplete="username"
            aria-label="Correo electrónico">
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-at"></span></div>
        </div>
        @error('email')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    {{-- Campo CÓDIGO (oculto inicialmente) --}}
    <div class="input-group mb-2" id="groupCode" style="display:none;">
        <input type="text" name="codigohabilitacion"
            class="form-control @error('codigohabilitacion') is-invalid @enderror"
            value="{{ old('codigohabilitacion') }}"
            placeholder="Código de habilitación"
            autocomplete="username"
            aria-label="Código de habilitación">
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-id-card"></span></div>
        </div>
        @error('codigohabilitacion')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="field-hint mb-3">Usa <em>uno</em> de los dos métodos.</div>

    {{-- CONTRASEÑA + toggle --}}
    <div class="input-group mb-3">
        <input type="password" name="password" id="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Contraseña" required autocomplete="current-password" aria-label="Contraseña">
        <div class="input-group-append">
            <div class="input-group-text">
                <a href="#" id="togglePassword" class="text-muted" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                    <span class="fas fa-eye"></span>
                </a>
            </div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    {{-- Recordarme + botón --}}
    <div class="row align-items-center">
        <div class="col-7">
            <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">{{ __('adminlte::adminlte.remember_me') }}</label>
            </div>
        </div>
        <div class="col-5">
            <button type="submit" class="btn btn-brand btn-block">
                <span class="fas fa-sign-in-alt"></span> {{ __('adminlte::adminlte.sign_in') }}
            </button>
        </div>
    </div>
</form>

{{-- JS: alternar método, foco y toggle de contraseña. Respeta errores del backend --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabEmail = document.getElementById('tabEmail');
    var tabCode  = document.getElementById('tabCode');
    var gEmail   = document.getElementById('groupEmail');
    var gCode    = document.getElementById('groupCode');
    var emailInp = gEmail.querySelector('input[name="email"]');
    var codeInp  = gCode.querySelector('input[name="codigohabilitacion"]');

    function setMethod(method){
        if(method === 'email'){
            gEmail.style.display = '';
            gCode.style.display  = 'none';
            emailInp.disabled = false;
            codeInp.disabled  = true;
            setTimeout(()=>emailInp.focus(), 60);
        }else{
            gEmail.style.display = 'none';
            gCode.style.display  = '';
            emailInp.disabled = true;
            codeInp.disabled  = false;
            setTimeout(()=>codeInp.focus(), 60);
        }
    }

    // Inicial según old() / errores del backend
    var startMethod = 'email';
    @if($errors->has('codigohabilitacion') || old('codigohabilitacion'))
        startMethod = 'code';
    @endif
    if(startMethod === 'code'){ tabCode.checked = true; }
    setMethod(startMethod);

    tabEmail.addEventListener('change', ()=> setMethod('email'));
    tabCode.addEventListener('change',  ()=> setMethod('code'));

    // Toggle contraseña
    var toggle = document.getElementById('togglePassword');
    var pass   = document.getElementById('password');
    if(toggle && pass){
        toggle.addEventListener('click', function(e){
            e.preventDefault();
            if(pass.type === 'password'){
                pass.type = 'text';
                this.querySelector('span').classList.remove('fa-eye');
                this.querySelector('span').classList.add('fa-eye-slash');
            }else{
                pass.type = 'password';
                this.querySelector('span').classList.remove('fa-eye-slash');
                this.querySelector('span').classList.add('fa-eye');
            }
        });
    }
});
</script>
@stop

@section('auth_footer')
<div class="auth-footer text-center">
    @if($password_reset_url)
        <p class="my-0">
            <a href="{{ $password_reset_url }}">{{ __('adminlte::adminlte.i_forgot_my_password') }}</a>
        </p>
    @endif
    {{-- Registro opcional
    @if($register_url)
        <p class="my-0"><a href="{{ $register_url }}">{{ __('adminlte::adminlte.register_a_new_membership') }}</a></p>
    @endif
    --}}
</div>
@stop
