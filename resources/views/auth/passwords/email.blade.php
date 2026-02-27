<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar acceso | Anaswayuu</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <style>
        :root{
            --bg-1:#040a14;
            --bg-2:#081523;
            --bg-3:#0c1f33;
            --line:rgba(255,255,255,.14);
            --card:rgba(9,21,34,.78);
            --text:#eaf4ff;
            --muted:#b4c8dd;
            --acc:#f5c85d;
            --acc-2:#2f6db7;
            --danger:#ff7d8d;
            --ok:#9ce6b5;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            color:var(--text);
            font-family:"Plus Jakarta Sans",system-ui,sans-serif;
            background:linear-gradient(135deg, var(--bg-1), var(--bg-2) 45%, var(--bg-3));
        }
        .bg-image{
            position:fixed;
            inset:0;
            background-image:url('{{ asset('img/familia-anas-wayuu.webp') }}');
            background-size:clamp(980px, 96vw, 1680px) auto;
            background-repeat:no-repeat;
            background-position:center 8%;
            opacity:.9;
            filter:saturate(.95) contrast(1.02) brightness(.8);
        }
        .bg-image::before{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(180deg, rgba(3,9,18,.45) 0%, rgba(3,9,18,.74) 100%);
        }
        .wrap{
            position:relative;
            min-height:100vh;
            display:grid;
            place-items:center;
            padding:24px;
        }
        .card{
            width:min(520px, 100%);
            border:1px solid var(--line);
            border-radius:22px;
            background:var(--card);
            backdrop-filter:blur(12px);
            box-shadow:0 20px 50px rgba(0,0,0,.35);
            padding:24px;
        }
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:8px}
        .brand img{width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.08);padding:6px}
        h1{margin:0;font-size:1.25rem}
        .sub{margin:8px 0 18px;color:var(--muted);font-size:.92rem;line-height:1.4}
        .alert{padding:10px 12px;border-radius:10px;font-size:.86rem;margin-bottom:12px}
        .alert.ok{border:1px solid rgba(156,230,181,.45);background:rgba(156,230,181,.13);color:var(--ok)}
        .alert.err{border:1px solid rgba(255,125,141,.45);background:rgba(255,125,141,.1);color:#ffd1d8}
        .field{margin-bottom:12px}
        label{display:block;font-size:.84rem;color:var(--muted);margin-bottom:6px}
        .control{
            width:100%;
            border:1px solid rgba(255,255,255,.22);
            background:rgba(5,13,22,.58);
            color:var(--text);
            border-radius:12px;
            padding:12px 13px;
            outline:none;
        }
        .control:focus{border-color:var(--acc);box-shadow:0 0 0 3px rgba(245,200,93,.2)}
        .btn{
            width:100%;
            border:0;
            border-radius:12px;
            padding:12px 14px;
            color:#fff;
            background:linear-gradient(135deg,var(--acc),var(--acc-2));
            font-weight:800;
            cursor:pointer;
        }
        .links{margin-top:12px;display:flex;justify-content:space-between;gap:10px;font-size:.84rem}
        .links a{color:#d7e9ff;text-decoration:none}
        .links a:hover{color:#fff;text-decoration:underline}
        .note{
            margin-top:14px;
            font-size:.79rem;
            color:#c9d8e9;
            border-top:1px solid rgba(255,255,255,.1);
            padding-top:10px;
        }
    </style>
</head>
<body>
    <div class="bg-image" aria-hidden="true"></div>
    <main class="wrap">
        <section class="card">
            <div class="brand">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo">
                <h1>Recuperacion de contrasena</h1>
            </div>
            <p class="sub">Escribe tu correo institucional y te enviaremos un enlace para restablecer tu acceso.</p>

            @if (session('status'))
                <div class="alert ok">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert err">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="field">
                    <label for="email">Correo electronico</label>
                    <input id="email" type="email" class="control" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="usuario@dominio.com">
                </div>
                <button type="submit" class="btn">Enviar enlace de recuperacion</button>
            </form>

            <div class="links">
                <a href="{{ route('login') }}">Volver a iniciar sesion</a>
            </div>

            <div class="note">
                Seguridad: por proteccion anti-abuso, solicitudes repetidas se limitan temporalmente.
            </div>
        </section>
    </main>
</body>
</html>
