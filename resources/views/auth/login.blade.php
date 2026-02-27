<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ingreso | Anaswayuu</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg-1:#030814;
            --bg-2:#071629;
            --bg-3:#0b2138;
            --line:rgba(156,196,235,.22);
            --panel:rgba(6,18,32,.62);
            --panel-2:rgba(8,24,41,.72);
            --text:#e8f4ff;
            --muted:#aec7de;
            --acc:#ffd36f;
            --acc-2:#3f8be2;
            --danger:#ff7d8d;
            --neon:#69d4ff;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            color:var(--text);
            font-family:"Plus Jakarta Sans",system-ui,sans-serif;
            background:
                radial-gradient(circle at 8% 8%, rgba(105,212,255,.22), transparent 34%),
                radial-gradient(circle at 88% 86%, rgba(63,139,226,.2), transparent 35%),
                linear-gradient(130deg, var(--bg-1), var(--bg-2) 45%, var(--bg-3));
            overflow-x:hidden;
        }
        .bg-image{
            position:fixed;
            inset:0;
            background-image:url('{{ asset('img/familia-anas-wayuu.webp') }}');
            background-size:clamp(960px, 96vw, 1680px) auto;
            background-repeat:no-repeat;
            background-position:center 8%;
            opacity:.76;
            filter:saturate(.94) contrast(1.05) brightness(.76);
        }
        .bg-image::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 50% 14%, rgba(53,118,197,.16), transparent 44%),
                linear-gradient(180deg, rgba(2,8,15,.22) 0%, rgba(2,8,15,.56) 58%, rgba(2,8,15,.88) 100%);
            pointer-events:none;
        }
        .bg-grid{
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(126,183,231,.09) 1px, transparent 1px),
                linear-gradient(90deg, rgba(126,183,231,.09) 1px, transparent 1px);
            background-size:40px 40px;
            mask-image:radial-gradient(circle at 50% 36%, black, transparent 74%);
            animation:gridDrift 14s linear infinite;
            pointer-events:none;
        }
        .bg-orb{
            position:fixed;
            width:min(42vw, 560px);
            aspect-ratio:1/1;
            border-radius:50%;
            filter:blur(4px);
            opacity:.34;
            pointer-events:none;
        }
        .bg-orb.a{
            top:-120px;
            left:-110px;
            background:radial-gradient(circle, rgba(105,212,255,.52), rgba(105,212,255,0) 66%);
            animation:orbFloatA 12s ease-in-out infinite;
        }
        .bg-orb.b{
            right:-180px;
            bottom:-180px;
            background:radial-gradient(circle, rgba(63,139,226,.5), rgba(63,139,226,0) 70%);
            animation:orbFloatB 15s ease-in-out infinite;
        }
        .bg-scan{
            position:fixed;
            inset:0;
            background:linear-gradient(180deg, transparent 0%, rgba(105,212,255,.06) 50%, transparent 100%);
            transform:translateY(-100%);
            animation:scanMove 10s linear infinite;
            pointer-events:none;
        }
        .wrap{
            position:relative;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:28px 16px;
            isolation:isolate;
        }
        .corner-shield{
            position:fixed;
            top:18px;
            right:20px;
            z-index:20;
            width:114px;
            height:114px;
            border-radius:30px;
            background:radial-gradient(circle at 30% 22%, rgba(255,255,255,.98), rgba(242,248,255,.82) 46%, rgba(215,231,247,.58) 100%);
            border:1px solid rgba(255,255,255,.82);
            box-shadow:0 22px 48px rgba(0,0,0,.45), 0 0 42px rgba(63,139,226,.28), inset 0 1px 0 rgba(255,255,255,.82);
            display:flex;
            align-items:center;
            justify-content:center;
            backdrop-filter:blur(8px);
            animation:shieldFloat 3.8s ease-in-out infinite;
        }
        .corner-shield img{
            width:88px;
            height:88px;
            object-fit:contain;
            filter:drop-shadow(0 3px 10px rgba(0,0,0,.16)) drop-shadow(0 0 10px rgba(255,255,255,.33));
        }
        .corner-shield::before{
            content:"";
            position:absolute;
            inset:-6px;
            border-radius:36px;
            border:1px solid rgba(255,211,111,.48);
            box-shadow:0 0 22px rgba(255,211,111,.28);
            pointer-events:none;
        }
        .panel{
            position:relative;
            width:min(1020px, 100%);
            display:grid;
            grid-template-columns:1.08fr .92fr;
            border:1px solid var(--line);
            border-radius:24px;
            overflow:hidden;
            background:linear-gradient(130deg, var(--panel), var(--panel-2));
            backdrop-filter:blur(16px) saturate(1.18);
            box-shadow:0 30px 84px rgba(0,0,0,.48), 0 0 0 1px rgba(105,212,255,.09);
            animation:rise .55s cubic-bezier(.22,.61,.36,1);
        }
        .panel::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                linear-gradient(112deg, rgba(105,212,255,.11), transparent 38%),
                linear-gradient(250deg, rgba(255,211,111,.08), transparent 52%);
            pointer-events:none;
        }
        .hero{
            position:relative;
            padding:34px 34px 30px;
            border-right:1px solid rgba(183,218,247,.16);
            background:
                radial-gradient(circle at 12% 0%, rgba(93,161,247,.28), transparent 40%),
                linear-gradient(168deg, rgba(255,255,255,.07), rgba(255,255,255,.01));
        }
        .hero::after{
            content:"";
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            height:120px;
            background:
                linear-gradient(180deg, transparent, rgba(105,212,255,.07)),
                repeating-linear-gradient(90deg, rgba(111,183,238,.24) 0 1px, transparent 1px 10px);
            opacity:.5;
            pointer-events:none;
        }
        .logo{
            width:60px;
            height:60px;
            border-radius:16px;
            object-fit:contain;
            background:#fff;
            padding:7px;
            box-shadow:0 12px 28px rgba(0,0,0,.36), 0 0 16px rgba(105,212,255,.22);
            border:1px solid rgba(255,255,255,.2);
        }
        .hero h1{
            font-family:"Space Grotesk",sans-serif;
            font-size:2.1rem;
            line-height:1.04;
            margin:16px 0 9px;
            letter-spacing:.2px;
            background:linear-gradient(120deg, #f7fbff, #d7ebff 46%, #96d5ff);
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
        }
        .hero p{margin:0;color:var(--muted);font-size:.98rem;max-width:42ch}
        .chips{display:flex;flex-wrap:wrap;gap:9px;margin-top:19px}
        .chip{
            border:1px solid rgba(163,206,242,.26);
            background:rgba(255,255,255,.05);
            color:#d8eafe;
            padding:8px 11px;
            font-size:.8rem;
            border-radius:999px;
            font-weight:700;
            transition:transform .2s ease, border-color .2s ease, background .2s ease;
        }
        .chip:hover{
            transform:translateY(-1px);
            border-color:rgba(105,212,255,.5);
            background:rgba(105,212,255,.13);
        }
        .form-side{
            position:relative;
            padding:34px 30px 28px;
        }
        .form-side::before{
            content:"";
            position:absolute;
            inset:16px;
            border-radius:18px;
            border:1px solid rgba(168,211,245,.12);
            pointer-events:none;
        }
        .form-side h2{
            margin:0 0 6px;
            font-family:"Space Grotesk",sans-serif;
            font-size:1.38rem;
            letter-spacing:.2px;
        }
        .sub{margin:0 0 18px;color:var(--muted);font-size:.9rem}
        .session-alert{
            margin:0 0 14px;
            padding:10px 12px;
            border-radius:10px;
            border:1px solid rgba(255,211,111,.52);
            background:rgba(255,211,111,.14);
            color:#ffe7a6;
            font-size:.84rem;
            font-weight:600;
        }
        .login-modes{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:8px;
            margin-bottom:14px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(162,206,244,.24);
            border-radius:12px;
            padding:6px;
        }
        .mode-btn{
            border:0;
            border-radius:9px;
            background:transparent;
            color:#c9def2;
            font-weight:700;
            font-size:.85rem;
            height:38px;
            cursor:pointer;
            transition:.2s background,.2s color,.2s box-shadow;
        }
        .mode-btn.is-active{
            background:linear-gradient(120deg, rgba(105,212,255,.28), rgba(63,139,226,.26));
            color:#fff;
            box-shadow:0 6px 16px rgba(22,62,108,.35);
        }
        .auth-panel{
            opacity:0;
            transform:translateY(8px);
            max-height:0;
            overflow:hidden;
            transition:opacity .26s ease, transform .26s ease, max-height .26s ease;
        }
        .auth-panel.is-active{
            opacity:1;
            transform:translateY(0);
            max-height:520px;
        }
        .field{margin-bottom:14px}
        .field label{display:block;margin-bottom:7px;font-size:.83rem;font-weight:700;color:#d8e7f7}
        .control{
            width:100%;
            border:1px solid rgba(173,216,246,.2);
            border-radius:12px;
            height:46px;
            background:rgba(255,255,255,.07);
            color:#f0f7ff;
            padding:0 13px;
            outline:none;
            transition:.2s border-color,.2s box-shadow,.2s background;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
        }
        .control:focus{
            border-color:rgba(105,212,255,.86);
            box-shadow:0 0 0 4px rgba(105,212,255,.22), inset 0 1px 0 rgba(255,255,255,.1);
            background:rgba(255,255,255,.1);
        }
        .control::placeholder{color:#c2d5e8}
        .invalid{border-color:var(--danger)!important;box-shadow:0 0 0 4px rgba(255,125,141,.18)!important}
        .error{margin-top:6px;color:#ffc2ca;font-size:.79rem}
        .row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            margin:2px 0 16px;
        }
        .remember{display:flex;align-items:center;gap:8px;font-size:.86rem;color:#dbe8f6}
        .remember input{accent-color:#5dc8ff}
        .link{color:#9ad9ff;text-decoration:none;font-weight:700;font-size:.84rem}
        .link:hover{text-decoration:underline}
        .btn{
            position:relative;
            width:100%;
            height:46px;
            border:0;
            border-radius:12px;
            background:linear-gradient(120deg,var(--acc),var(--acc-2));
            color:#02223a;
            font-weight:800;
            letter-spacing:.2px;
            cursor:pointer;
            box-shadow:0 14px 30px rgba(32,87,146,.42), inset 0 1px 0 rgba(255,255,255,.38);
            transition:transform .15s ease, filter .15s ease;
            overflow:hidden;
        }
        .btn::before{
            content:"";
            position:absolute;
            top:0;
            left:-40%;
            width:32%;
            height:100%;
            background:linear-gradient(90deg, transparent, rgba(255,255,255,.5), transparent);
            transform:skewX(-18deg);
            animation:btnSweep 3.8s ease-in-out infinite;
        }
        .btn:hover{transform:translateY(-1px);filter:brightness(1.04)}
        .footer{margin-top:14px;font-size:.78rem;color:#b4c8dd;text-align:center}
        .security-note{
            margin:0 0 12px;
            padding:10px 12px;
            border:1px solid rgba(255,211,111,.32);
            background:rgba(255,211,111,.08);
            border-radius:10px;
            color:#f7dd9d;
            font-size:.84rem;
            line-height:1.4;
        }
        @keyframes shieldFloat{
            0%,100%{transform:translateY(0)}
            50%{transform:translateY(-3px)}
        }
        @keyframes rise{
            from{opacity:0;transform:translateY(10px)}
            to{opacity:1;transform:translateY(0)}
        }
        @keyframes gridDrift{
            0%{transform:translateY(0)}
            100%{transform:translateY(40px)}
        }
        @keyframes orbFloatA{
            0%,100%{transform:translate(0,0)}
            50%{transform:translate(26px,22px)}
        }
        @keyframes orbFloatB{
            0%,100%{transform:translate(0,0)}
            50%{transform:translate(-30px,-24px)}
        }
        @keyframes scanMove{
            0%{transform:translateY(-100%)}
            100%{transform:translateY(100%)}
        }
        @keyframes btnSweep{
            0%,54%,100%{left:-45%}
            72%{left:120%}
        }
        @media (max-width:920px){
            .panel{grid-template-columns:1fr}
            .hero{border-right:0;border-bottom:1px solid rgba(255,255,255,.12)}
            .form-side::before{display:none}
            .corner-shield{
                width:88px;
                height:88px;
                border-radius:20px;
                top:12px;
                right:12px;
            }
            .corner-shield img{width:66px;height:66px}
        }
        @media (prefers-reduced-motion:reduce){
            .bg-grid,.bg-orb,.bg-scan,.corner-shield,.btn::before,.panel{animation:none!important}
            .auth-panel{transition:none!important}
        }
    </style>
</head>
<body>
    <div class="bg-image" aria-hidden="true"></div>
    <div class="bg-grid" aria-hidden="true"></div>
    <div class="bg-orb a" aria-hidden="true"></div>
    <div class="bg-orb b" aria-hidden="true"></div>
    <div class="bg-scan" aria-hidden="true"></div>
    <div class="corner-shield" aria-hidden="true">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo Rutas Integrales">
    </div>

    <main class="wrap">
        <section class="panel">
            <aside class="hero">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo">
                <h1>Plataforma<br>Rutas Integrales</h1>
                <p>Gestion centralizada de cargue, seguimiento y auditoria de registros de vacunacion para tu equipo operativo.</p>
                <div class="chips">
                    <span class="chip">Seguridad de acceso</span>
                    <span class="chip">Trazabilidad por usuario</span>
                    <span class="chip">Reportes dinamicos</span>
                </div>
            </aside>

            <section class="form-side">
                <h2>Iniciar sesion</h2>
                <p class="sub">Ingresa tus credenciales para acceder al modulo.</p>
                <p class="security-note">
                    Seguridad activa: si hay 5 intentos fallidos, el acceso se bloquea por 10 minutos.
                    El metodo de ingreso sigue siendo el mismo (correo o usuario/codigo).
                </p>
                @if (session('status'))
                    <div class="session-alert">{{ session('status') }}</div>
                @endif
                @if (session('session_expired'))
                    <div class="session-alert">{{ session('session_expired') }}</div>
                @endif

                <div class="login-modes">
                    <button type="button" class="mode-btn {{ old('email') ? '' : 'is-active' }}" data-mode="codigo">Ingresar con codigo</button>
                    <button type="button" class="mode-btn {{ old('email') ? 'is-active' : '' }}" data-mode="correo">Ingresar con correo</button>
                </div>

                <div class="auth-panel {{ old('email') ? '' : 'is-active' }}" id="panel-codigo">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="field">
                            <label for="codigohabilitacion">Usuario o codigo de habilitacion</label>
                            <input id="codigohabilitacion" type="text" class="control @error('codigohabilitacion') invalid @enderror" name="codigohabilitacion" value="{{ old('codigohabilitacion') }}" required autocomplete="username" autofocus placeholder="Ingresa tu usuario o codigo">
                            @error('codigohabilitacion')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password_codigo">Contrasena</label>
                            <input id="password_codigo" type="password" class="control @error('password') invalid @enderror" name="password" required autocomplete="current-password" placeholder="Ingresa tu contrasena">
                            @error('password')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <label class="remember">
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                Recordarme
                            </label>
                            @if (Route::has('password.request'))
                                <a class="link" href="{{ route('password.request') }}">Olvide mi contrasena</a>
                            @endif
                        </div>

                        <button type="submit" class="btn">Acceder con usuario/codigo</button>
                    </form>
                </div>

                <div class="auth-panel {{ old('email') ? 'is-active' : '' }}" id="panel-correo">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="field">
                            <label for="email">Correo electronico</label>
                            <input id="email" type="email" class="control @error('email') invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus placeholder="usuario@dominio.com">
                            @error('email')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password_correo">Contrasena</label>
                            <input id="password_correo" type="password" class="control @error('password') invalid @enderror" name="password" required autocomplete="current-password" placeholder="Ingresa tu contrasena">
                            @error('password')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <label class="remember">
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                Recordarme
                            </label>
                            @if (Route::has('password.request'))
                                <a class="link" href="{{ route('password.request') }}">Olvide mi contrasena</a>
                            @endif
                        </div>

                        <button type="submit" class="btn">Acceder con correo</button>
                    </form>
                </div>

                <div class="footer">EPS IANAS WAYUU · Rutas Integrales</div>
            </section>
        </section>
    </main>
    <script>
        (function () {
            const buttons = document.querySelectorAll('.mode-btn');
            const panelCodigo = document.getElementById('panel-codigo');
            const panelCorreo = document.getElementById('panel-correo');
            if (!buttons.length || !panelCodigo || !panelCorreo) return;

            function setMode(mode) {
                const isCorreo = mode === 'correo';
                panelCodigo.classList.toggle('is-active', !isCorreo);
                panelCorreo.classList.toggle('is-active', isCorreo);
                buttons.forEach(btn => btn.classList.toggle('is-active', btn.dataset.mode === mode));
            }

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    setMode(this.dataset.mode);
                });
            });
        })();
    </script>
</body>
</html>

