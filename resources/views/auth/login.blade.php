<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ingreso | {{ config('app.name', 'Desnutricion') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg-1:#040a14;
            --bg-2:#081523;
            --bg-3:#0c1f33;
            --line:rgba(255,255,255,.14);
            --card:rgba(9,21,34,.76);
            --text:#eaf4ff;
            --muted:#b4c8dd;
            --acc:#f5c85d;
            --acc-2:#2f6db7;
            --danger:#ff7d8d;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            color:var(--text);
            font-family:"Plus Jakarta Sans",system-ui,sans-serif;
            background:
                linear-gradient(120deg, rgba(10,24,44,.42), transparent 34%),
                linear-gradient(230deg, rgba(8,19,34,.52), transparent 40%),
                radial-gradient(circle at 20% 15%, rgba(16,40,66,.48), transparent 44%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2) 45%, var(--bg-3));
            overflow-x:hidden;
        }
        .bg-image{
            position:fixed;
            inset:0;
            background-image:url('{{ asset('img/familia-anas-wayuu.webp') }}');
            background-size:clamp(980px, 96vw, 1680px) auto;
            background-repeat:no-repeat;
            background-position:center 8%;
            opacity:.94;
            filter:saturate(.95) contrast(1.02) brightness(.8);
            transform:none;
        }
        .bg-image::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 50% 12%, rgba(22,48,82,.10), transparent 46%),
                linear-gradient(180deg, rgba(3,9,18,.28) 0%, rgba(3,9,18,.45) 58%, rgba(3,9,18,.76) 100%);
            pointer-events:none;
        }
        .bg-image::after{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 50% 58%, transparent 42%, rgba(3,9,18,.38) 100%);
            opacity:.9;
            pointer-events:none;
        }
        .bg-grid{
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size:36px 36px;
            mask-image:radial-gradient(circle at 50% 40%, black, transparent 78%);
        }
        .wrap{
            position:relative;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:28px 16px;
        }
        .corner-shield{
            position:fixed;
            top:18px;
            right:20px;
            z-index:20;
            width:114px;
            height:114px;
            border-radius:30px;
            background:
                radial-gradient(circle at 30% 22%, rgba(255,255,255,.96), rgba(245,249,255,.78) 48%, rgba(216,230,247,.55) 100%);
            border:1px solid rgba(255,255,255,.85);
            box-shadow:
                0 24px 52px rgba(0,0,0,.42),
                0 0 36px rgba(47,109,183,.26),
                inset 0 1px 0 rgba(255,255,255,.8);
            display:flex;
            align-items:center;
            justify-content:center;
            backdrop-filter:blur(10px);
            animation:shieldFloat 3.8s ease-in-out infinite;
        }
        .corner-shield img{
            width:88px;
            height:88px;
            object-fit:contain;
            display:block;
            opacity:.98;
            filter:
                drop-shadow(0 2px 10px rgba(0,0,0,.14))
                drop-shadow(0 0 10px rgba(255,255,255,.34));
        }
        .corner-shield::before{
            content:"";
            position:absolute;
            inset:-7px;
            border-radius:36px;
            border:1px solid rgba(245,200,93,.46);
            box-shadow:0 0 20px rgba(245,200,93,.26);
            opacity:.9;
            pointer-events:none;
        }
        .corner-shield::after{
            content:"";
            position:absolute;
            inset:6px;
            border-radius:20px;
            background:radial-gradient(circle at 50% 35%, rgba(255,255,255,.36), transparent 70%);
            pointer-events:none;
        }
        @keyframes shieldFloat{
            0%,100%{transform:translateY(0)}
            50%{transform:translateY(-3px)}
        }
        .panel{
            width:min(980px, 100%);
            display:grid;
            grid-template-columns:1.05fr .95fr;
            border:1px solid var(--line);
            border-radius:24px;
            overflow:hidden;
            background:rgba(6,16,28,.56);
            backdrop-filter: blur(16px) saturate(1.2);
            box-shadow:0 28px 80px rgba(0,0,0,.45);
            animation:rise .55s cubic-bezier(.22,.61,.36,1);
        }
        .hero{
            position:relative;
            padding:34px 34px 30px;
            border-right:1px solid rgba(255,255,255,.09);
            background:
                radial-gradient(circle at 12% 0%, rgba(79,140,255,.34), transparent 42%),
                linear-gradient(170deg, rgba(255,255,255,.06), rgba(255,255,255,.01));
        }
        .logo{
            width:58px;height:58px;border-radius:14px;object-fit:contain;background:#fff;padding:7px;
            box-shadow:0 10px 24px rgba(0,0,0,.35);
            border:1px solid rgba(255,255,255,.14);
        }
        .hero h1{
            font-family:"Space Grotesk",sans-serif;
            font-size:2rem;
            line-height:1.06;
            margin:16px 0 8px;
            letter-spacing:.2px;
        }
        .hero p{margin:0;color:var(--muted);font-size:.98rem;max-width:42ch}
        .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
        .chip{
            border:1px solid rgba(255,255,255,.14);
            background:rgba(255,255,255,.06);
            color:#d6e6f8;
            padding:8px 10px;
            font-size:.8rem;
            border-radius:999px;
            font-weight:600;
        }
        .form-side{padding:34px 30px 28px}
        .form-side h2{
            margin:0 0 6px;
            font-family:"Space Grotesk",sans-serif;
            font-size:1.35rem;
        }
        .sub{margin:0 0 18px;color:var(--muted);font-size:.9rem}
        .login-modes{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:8px;
            margin-bottom:14px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.14);
            border-radius:12px;
            padding:6px;
        }
        .mode-btn{
            border:0;
            border-radius:9px;
            background:transparent;
            color:#cde0f4;
            font-weight:700;
            font-size:.85rem;
            height:38px;
            cursor:pointer;
            transition:.2s background,.2s color;
        }
        .mode-btn.is-active{
            background:rgba(255,255,255,.16);
            color:#ffffff;
        }
        .auth-panel{display:none}
        .auth-panel.is-active{display:block}
        .field{margin-bottom:14px}
        .field label{display:block;margin-bottom:7px;font-size:.83rem;font-weight:700;color:#d8e7f7}
        .control{
            width:100%;
            border:1px solid rgba(255,255,255,.18);
            border-radius:12px;
            height:46px;
            background:rgba(255,255,255,.08);
            color:#eff6ff;
            padding:0 13px;
            outline:none;
            transition:.2s border-color,.2s box-shadow,.2s background;
        }
        .control:focus{
            border-color:rgba(79,140,255,.8);
            box-shadow:0 0 0 4px rgba(79,140,255,.2);
            background:rgba(255,255,255,.12);
        }
        .control::placeholder{color:#c7d6e6}
        .invalid{border-color:var(--danger)!important;box-shadow:0 0 0 4px rgba(255,125,141,.18)!important}
        .error{margin-top:6px;color:#ffc2ca;font-size:.79rem}
        .row{
            display:flex;align-items:center;justify-content:space-between;gap:10px;margin:2px 0 16px;
        }
        .remember{display:flex;align-items:center;gap:8px;font-size:.86rem;color:#dbe8f6}
        .remember input{accent-color:#4f8cff}
        .link{color:#a9d9ff;text-decoration:none;font-weight:700;font-size:.84rem}
        .link:hover{text-decoration:underline}
        .btn{
            width:100%;
            height:46px;
            border:0;
            border-radius:12px;
            background:linear-gradient(120deg,var(--acc),var(--acc-2));
            color:#032036;
            font-weight:800;
            letter-spacing:.2px;
            cursor:pointer;
            box-shadow:0 12px 28px rgba(47,109,183,.38);
            transition:transform .15s ease, filter .15s ease;
        }
        .btn:hover{transform:translateY(-1px);filter:brightness(1.04)}
        .footer{margin-top:14px;font-size:.78rem;color:#b4c8dd;text-align:center}
        @keyframes rise{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        @media (max-width:920px){
            .panel{grid-template-columns:1fr}
            .hero{border-right:0;border-bottom:1px solid rgba(255,255,255,.1)}
            .corner-shield{
                width:88px;
                height:88px;
                border-radius:20px;
                top:12px;
                right:12px;
            }
            .corner-shield img{
                width:66px;
                height:66px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-image" aria-hidden="true"></div>
    <div class="bg-grid" aria-hidden="true"></div>
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

