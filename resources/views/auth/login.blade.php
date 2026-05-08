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
        body::before{
            content:"";
            position:fixed;
            inset:0;
            z-index:90;
            background:
                radial-gradient(circle at 50% 44%, rgba(105,212,255,.18), transparent 30%),
                linear-gradient(130deg, rgba(3,8,20,.96), rgba(7,22,41,.94));
            animation:welcomeCurtain .58s cubic-bezier(.2,.8,.2,1) forwards;
            pointer-events:none;
        }
        .welcome-loader{
            position:fixed;
            inset:0;
            z-index:91;
            display:grid;
            place-items:center;
            color:#e8f4ff;
            pointer-events:none;
            animation:welcomeExit .58s ease forwards;
        }
        .welcome-loader__box{
            display:grid;
            place-items:center;
            gap:12px;
            padding:20px 26px;
            border-radius:22px;
            border:1px solid rgba(173,216,246,.28);
            background:rgba(8,24,41,.62);
            box-shadow:0 24px 70px rgba(0,0,0,.44), 0 0 42px rgba(105,212,255,.2);
            backdrop-filter:blur(14px);
            transform:translateY(8px) scale(.96);
            animation:welcomeBox .78s cubic-bezier(.16,.84,.28,1) forwards;
        }
        .welcome-loader__pulse{
            width:54px;
            height:54px;
            border-radius:18px;
            background:linear-gradient(135deg, var(--acc), var(--neon));
            box-shadow:0 0 34px rgba(105,212,255,.42);
            display:grid;
            place-items:center;
            color:#06233a;
            font-weight:900;
            animation:welcomePulse .72s ease-in-out infinite alternate;
        }
        .welcome-loader__text{
            margin:0;
            font-size:.92rem;
            font-weight:800;
            letter-spacing:.2px;
        }
        #login-particles{
            position:fixed;
            inset:0;
            z-index:1;
            width:100%;
            height:100%;
            pointer-events:none;
            opacity:.72;
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
            animation:bgAwake .62s ease-out both;
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
            opacity:0;
            animation:layerIn .62s cubic-bezier(.16,.84,.28,1) .78s forwards, shieldFloat 3.8s ease-in-out 1.4s infinite;
        }
        .corner-shield img{
            position:relative;
            z-index:2;
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
        .corner-shield::after{
            content:"";
            position:absolute;
            inset:-13px;
            border-radius:42px;
            background:conic-gradient(from 90deg, transparent, rgba(105,212,255,.42), transparent, rgba(255,211,111,.34), transparent);
            filter:blur(8px);
            opacity:.74;
            animation:shieldHalo 5s linear infinite;
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
            opacity:0;
            transform:translateY(18px) scale(.985);
            animation:panelEnter .74s cubic-bezier(.16,.84,.28,1) .88s forwards;
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
        .panel::after{
            content:"";
            position:absolute;
            inset:1px;
            border-radius:23px;
            background:linear-gradient(120deg, transparent 20%, rgba(105,212,255,.15), transparent 47%);
            transform:translateX(-75%);
            animation:panelTrace 5.6s ease-in-out infinite;
            pointer-events:none;
            mix-blend-mode:screen;
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
            opacity:0;
            animation:layerIn .55s cubic-bezier(.16,.84,.28,1) 1.05s forwards;
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
            opacity:0;
            animation:layerIn .58s cubic-bezier(.16,.84,.28,1) 1.22s forwards;
        }
        .hero p{
            margin:0;
            color:var(--muted);
            font-size:.98rem;
            max-width:42ch;
            opacity:0;
            animation:layerIn .58s cubic-bezier(.16,.84,.28,1) 1.36s forwards;
        }
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
            opacity:0;
            animation:chipIn .48s cubic-bezier(.16,.84,.28,1) forwards;
        }
        .chip:nth-child(1){animation-delay:1.52s}
        .chip:nth-child(2){animation-delay:1.62s}
        .chip:nth-child(3){animation-delay:1.72s}
        .chip:nth-child(4){animation-delay:1.82s}
        }
        .chip:hover{
            transform:translateY(-1px);
            border-color:rgba(105,212,255,.5);
            background:rgba(105,212,255,.13);
        }
        .signal-card{
            position:relative;
            z-index:1;
            display:grid;
            grid-template-columns:44px 1fr;
            gap:12px;
            align-items:center;
            margin-top:28px;
            padding:14px;
            border-radius:18px;
            border:1px solid rgba(173,216,246,.2);
            background:linear-gradient(140deg, rgba(255,255,255,.11), rgba(255,255,255,.035));
            box-shadow:inset 0 1px 0 rgba(255,255,255,.12), 0 16px 36px rgba(0,0,0,.16);
            opacity:0;
            animation:layerIn .58s cubic-bezier(.16,.84,.28,1) 1.86s forwards;
        }
        .signal-icon{
            width:44px;
            height:44px;
            border-radius:14px;
            display:grid;
            place-items:center;
            color:#06314d;
            background:linear-gradient(135deg, var(--acc), var(--neon));
            box-shadow:0 0 22px rgba(105,212,255,.28);
            font-weight:800;
        }
        .signal-card strong{display:block;font-size:.92rem;color:#f7fbff}
        .signal-card span{display:block;margin-top:3px;font-size:.79rem;color:#bdd4ea}
        .form-side{
            position:relative;
            padding:34px 30px 28px;
            opacity:0;
            animation:formEnter .7s cubic-bezier(.16,.84,.28,1) 1.42s forwards;
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
        .system-status{
            display:flex;
            align-items:center;
            gap:8px;
            margin:0 0 14px;
            padding:9px 11px;
            border-radius:999px;
            border:1px solid rgba(105,212,255,.22);
            background:rgba(105,212,255,.075);
            color:#cce7ff;
            font-size:.78rem;
            font-weight:800;
            letter-spacing:.18px;
            width:max-content;
            max-width:100%;
        }
        .system-status__dot{
            width:9px;
            height:9px;
            border-radius:50%;
            background:#10b981;
            box-shadow:0 0 0 5px rgba(16,185,129,.12), 0 0 16px rgba(16,185,129,.6);
            animation:statusPulse 1.7s ease-in-out infinite;
            flex:0 0 auto;
        }
        .session-alert{
            margin:0 0 14px;
            padding:10px 12px;
            border-radius:10px;
            border:1px solid rgba(255,211,111,.52);
            background:rgba(255,211,111,.14);
            color:#ffe7a6;
            font-size:.84rem;
            font-weight:600;
            box-shadow:0 0 22px rgba(255,211,111,.1), inset 0 1px 0 rgba(255,255,255,.08);
        }
        .login-modes{
            position:relative;
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:8px;
            margin-bottom:14px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(162,206,244,.24);
            border-radius:12px;
            padding:6px;
            overflow:hidden;
        }
        .login-modes::before{
            content:"";
            position:absolute;
            top:6px;
            bottom:6px;
            left:6px;
            width:calc(50% - 10px);
            border-radius:9px;
            background:linear-gradient(120deg, rgba(105,212,255,.22), rgba(63,139,226,.2));
            box-shadow:0 8px 20px rgba(22,62,108,.28);
            transition:transform .28s cubic-bezier(.16,.84,.28,1);
            pointer-events:none;
        }
        .login-modes.is-code::before{
            transform:translateX(calc(100% + 8px));
        }
        .mode-btn{
            position:relative;
            z-index:1;
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
            background:transparent;
            color:#fff;
            text-shadow:0 0 14px rgba(105,212,255,.38);
        }
        .auth-panel{
            opacity:0;
            transform:translateY(10px) rotateX(6deg) scale(.985);
            transform-origin:top center;
            max-height:0;
            overflow:hidden;
            transition:opacity .34s ease, transform .34s cubic-bezier(.16,.84,.28,1), max-height .34s ease;
        }
        .auth-panel.is-active{
            opacity:1;
            transform:translateY(0) rotateX(0) scale(1);
            max-height:520px;
        }
        .field{
            position:relative;
            margin-bottom:14px;
            overflow:hidden;
            border-radius:13px;
        }
        .field::after{
            content:"";
            position:absolute;
            left:0;
            right:0;
            top:28px;
            height:46px;
            border-radius:12px;
            background:linear-gradient(100deg, transparent, rgba(105,212,255,.22), transparent);
            transform:translateX(-110%);
            pointer-events:none;
            opacity:0;
        }
        .field:focus-within::after{
            animation:fieldScan .92s cubic-bezier(.16,.84,.28,1);
        }
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
        .error{
            position:relative;
            margin-top:8px;
            padding:8px 10px 8px 30px;
            color:#ffd7dc;
            font-size:.79rem;
            font-weight:800;
            border:1px solid rgba(255,125,141,.34);
            border-radius:10px;
            background:linear-gradient(120deg, rgba(255,125,141,.14), rgba(255,125,141,.06));
            box-shadow:0 0 20px rgba(255,125,141,.11), inset 0 1px 0 rgba(255,255,255,.08);
        }
        .error::before{
            content:"!";
            position:absolute;
            left:10px;
            top:50%;
            width:14px;
            height:14px;
            border-radius:50%;
            transform:translateY(-50%);
            display:grid;
            place-items:center;
            color:#2b0710;
            background:#ff9aa7;
            font-size:.68rem;
            font-weight:900;
        }
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
        .btn.is-loading{
            pointer-events:none;
            filter:saturate(1.12) brightness(1.08);
        }
        .btn.is-loading span{
            opacity:.72;
        }
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
        .launch-overlay{
            position:fixed;
            inset:0;
            z-index:100;
            display:grid;
            place-items:center;
            padding:22px;
            background:
                radial-gradient(circle at 50% 45%, rgba(105,212,255,.22), transparent 30%),
                radial-gradient(circle at 50% 55%, rgba(255,211,111,.14), transparent 34%),
                rgba(2,8,15,.9);
            opacity:0;
            visibility:hidden;
            pointer-events:none;
            transition:opacity .32s ease, visibility .32s ease;
        }
        .launch-overlay::before{
            content:"";
            position:absolute;
            inset:0;
            background-image:
                linear-gradient(rgba(105,212,255,.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(105,212,255,.08) 1px, transparent 1px);
            background-size:42px 42px;
            mask-image:radial-gradient(circle at 50% 50%, black, transparent 72%);
            animation:launchGrid 1.25s linear infinite;
        }
        .launch-card{
            position:relative;
            width:min(430px, 100%);
            min-height:360px;
            border-radius:30px;
            border:1px solid rgba(173,216,246,.28);
            background:linear-gradient(145deg, rgba(8,24,41,.78), rgba(4,14,26,.92));
            box-shadow:0 34px 94px rgba(0,0,0,.55), 0 0 64px rgba(105,212,255,.18);
            display:grid;
            place-items:center;
            text-align:center;
            overflow:hidden;
            transform:translateY(16px) scale(.98);
            transition:transform .42s cubic-bezier(.2,.78,.24,1);
            transform-style:preserve-3d;
            will-change:transform, opacity, filter;
        }
        .launch-card::before{
            content:"";
            position:absolute;
            inset:-45%;
            background:conic-gradient(from 180deg, transparent, rgba(105,212,255,.32), transparent, rgba(255,211,111,.22), transparent);
            animation:launchAurora 2.2s linear infinite;
        }
        .launch-core{
            position:relative;
            z-index:1;
            display:grid;
            place-items:center;
            gap:18px;
            padding:32px;
        }
        .launch-ring{
            position:relative;
            width:152px;
            height:152px;
            border-radius:50%;
            display:grid;
            place-items:center;
        }
        .launch-ring::before,
        .launch-ring::after{
            content:"";
            position:absolute;
            inset:0;
            border-radius:50%;
            border:1px solid rgba(105,212,255,.42);
            box-shadow:0 0 24px rgba(105,212,255,.25);
            animation:ringPulse 1.15s ease-out infinite;
        }
        .launch-ring::after{
            inset:18px;
            border-color:rgba(255,211,111,.48);
            animation-delay:.18s;
        }
        .launch-logo{
            width:82px;
            height:82px;
            border-radius:24px;
            background:rgba(255,255,255,.96);
            padding:10px;
            object-fit:contain;
            box-shadow:0 0 30px rgba(255,255,255,.2), 0 0 36px rgba(105,212,255,.3);
            animation:logoLift 1.15s ease-in-out infinite;
        }
        .launch-title{
            margin:0;
            font-family:"Space Grotesk",sans-serif;
            font-size:1.24rem;
            letter-spacing:.2px;
            color:#f6fbff;
        }
        .launch-copy{
            margin:0;
            color:#bdd4ea;
            font-size:.9rem;
            min-height:1.25em;
        }
        .launch-progress{
            width:240px;
            max-width:100%;
            height:7px;
            border-radius:99px;
            overflow:hidden;
            background:rgba(255,255,255,.09);
            border:1px solid rgba(255,255,255,.12);
        }
        .launch-progress span{
            display:block;
            height:100%;
            width:38%;
            border-radius:inherit;
            background:linear-gradient(90deg, var(--acc), var(--neon), var(--acc-2));
            box-shadow:0 0 18px rgba(105,212,255,.48);
            animation:progressRun 1s ease-in-out infinite;
        }
        body.auth-launching{
            overflow:hidden;
        }
        body.auth-launching .launch-overlay{
            opacity:1;
            visibility:visible;
            pointer-events:auto;
        }
        body.auth-launching .launch-card{
            transform:translateY(0) scale(1);
        }
        body.auth-launching .panel{
            transform:translateY(-8px) scale(.985);
            opacity:.44;
            filter:blur(1px) saturate(1.12);
            transition:transform .32s ease, opacity .32s ease, filter .32s ease;
        }
        @keyframes shieldFloat{
            0%,100%{transform:translateY(0)}
            50%{transform:translateY(-3px)}
        }
        @keyframes shieldHalo{
            to{transform:rotate(360deg)}
        }
        @keyframes welcomeCurtain{
            0%,68%{opacity:1;visibility:visible}
            100%{opacity:0;visibility:hidden}
        }
        @keyframes welcomeExit{
            0%,62%{opacity:1;visibility:visible}
            100%{opacity:0;visibility:hidden}
        }
        @keyframes welcomeBox{
            to{transform:translateY(0) scale(1)}
        }
        @keyframes welcomePulse{
            from{transform:scale(.96);filter:saturate(1)}
            to{transform:scale(1.05);filter:saturate(1.2)}
        }
        @keyframes bgAwake{
            from{opacity:0;filter:saturate(.82) contrast(1.02) brightness(.58)}
            to{opacity:.76;filter:saturate(.94) contrast(1.05) brightness(.76)}
        }
        @keyframes panelEnter{
            to{opacity:1;transform:translateY(0) scale(1)}
        }
        @keyframes layerIn{
            from{opacity:0;transform:translateY(14px) scale(.985);filter:blur(5px)}
            to{opacity:1;transform:translateY(0) scale(1);filter:blur(0)}
        }
        @keyframes formEnter{
            from{opacity:0;transform:translateX(20px);filter:blur(7px)}
            to{opacity:1;transform:translateX(0);filter:blur(0)}
        }
        @keyframes chipIn{
            from{opacity:0;transform:translateY(10px) scale(.92)}
            to{opacity:1;transform:translateY(0) scale(1)}
        }
        @keyframes statusPulse{
            0%,100%{transform:scale(1);box-shadow:0 0 0 5px rgba(16,185,129,.12), 0 0 16px rgba(16,185,129,.6)}
            50%{transform:scale(1.18);box-shadow:0 0 0 8px rgba(16,185,129,.08), 0 0 22px rgba(16,185,129,.82)}
        }
        @keyframes fieldScan{
            0%{opacity:0;transform:translateX(-110%)}
            18%{opacity:1}
            100%{opacity:0;transform:translateX(110%)}
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
        @keyframes panelTrace{
            0%,58%,100%{transform:translateX(-75%);opacity:0}
            70%{opacity:.82}
            88%{transform:translateX(75%);opacity:0}
        }
        @keyframes launchGrid{
            from{transform:translateY(0)}
            to{transform:translateY(42px)}
        }
        @keyframes launchAurora{
            to{transform:rotate(360deg)}
        }
        @keyframes ringPulse{
            0%{transform:scale(.82);opacity:.92}
            100%{transform:scale(1.16);opacity:0}
        }
        @keyframes logoLift{
            0%,100%{transform:translateY(0) scale(1)}
            50%{transform:translateY(-4px) scale(1.025)}
        }
        @keyframes progressRun{
            0%{transform:translateX(-115%)}
            100%{transform:translateX(275%)}
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
            body::before,.welcome-loader,.welcome-loader__box,.welcome-loader__pulse,.bg-image,.bg-grid,.bg-orb,.bg-scan,.corner-shield,.corner-shield::after,.logo,.hero h1,.hero p,.chip,.signal-card,.form-side,.system-status__dot,.field::after,.btn::before,.panel,.panel::after,.launch-overlay::before,.launch-card::before,.launch-ring::before,.launch-ring::after,.launch-logo,.launch-progress span{animation:none!important}
            body::before,.welcome-loader{display:none!important}
            .corner-shield,.panel,.logo,.hero h1,.hero p,.chip,.signal-card,.form-side{opacity:1!important;transform:none!important;filter:none!important}
            .auth-panel{transition:none!important}
        }
    </style>
</head>
<body>
    <div class="welcome-loader" aria-hidden="true">
        <div class="welcome-loader__box">
            <div class="welcome-loader__pulse">RI</div>
            <p class="welcome-loader__text">Conectando con Rutas Integrales...</p>
        </div>
    </div>
    <canvas id="login-particles" aria-hidden="true"></canvas>
    <div class="bg-image" aria-hidden="true"></div>
    <div class="bg-grid" aria-hidden="true"></div>
    <div class="bg-orb a" aria-hidden="true"></div>
    <div class="bg-orb b" aria-hidden="true"></div>
    <div class="bg-scan" aria-hidden="true"></div>
    <div class="corner-shield" aria-hidden="true">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo Rutas Integrales">
    </div>
    <div class="launch-overlay" aria-live="polite" aria-hidden="true">
        <div class="launch-card">
            <div class="launch-core">
                <div class="launch-ring">
                    <img class="launch-logo" src="{{ asset('img/logo.png') }}" alt="Anas Wayuu">
                </div>
                <div>
                    <h2 class="launch-title">Validando acceso seguro</h2>
                    <p class="launch-copy">Preparando tu espacio de trabajo...</p>
                </div>
                <div class="launch-progress" aria-hidden="true"><span></span></div>
            </div>
        </div>
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
                <div class="signal-card">
                    <div class="signal-icon">RI</div>
                    <div>
                        <strong>Ingreso protegido</strong>
                        <span>Verificacion ligera, experiencia fluida y acceso directo al panel.</span>
                    </div>
                </div>
            </aside>

            <section class="form-side">
                <h2>Iniciar sesion</h2>
                <p class="sub">Ingresa tus credenciales para acceder al modulo.</p>
                <div class="system-status">
                    <span class="system-status__dot" aria-hidden="true"></span>
                    <span>Sistema seguro activo · Acceso institucional · <span id="login-clock">--:--</span></span>
                </div>
                <p class="security-note">
                    Seguridad activa: si hay 5 intentos fallidos, el acceso se bloquea por 10 minutos.
                    Si tu IPS comparte codigo de habilitacion entre varios usuarios, ingresa con correo para entrar a la cuenta correcta.
                </p>
                @if (session('status'))
                    <div class="session-alert">{{ session('status') }}</div>
                @endif
                @if (session('session_expired'))
                    <div class="session-alert">{{ session('session_expired') }}</div>
                @endif

                <div class="login-modes {{ old('codigohabilitacion') ? 'is-code' : '' }}">
                    <button type="button" class="mode-btn {{ old('codigohabilitacion') ? '' : 'is-active' }}" data-mode="correo">Ingresar con correo</button>
                    <button type="button" class="mode-btn {{ old('codigohabilitacion') ? 'is-active' : '' }}" data-mode="codigo">Ingresar con codigo</button>
                </div>

                <div class="auth-panel {{ old('codigohabilitacion') ? 'is-active' : '' }}" id="panel-codigo">
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

                        <button type="submit" class="btn"><span>Acceder con usuario/codigo</span></button>
                    </form>
                </div>

                <div class="auth-panel {{ old('codigohabilitacion') ? '' : 'is-active' }}" id="panel-correo">
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

                        <button type="submit" class="btn"><span>Acceder con correo</span></button>
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
            const modeShell = document.querySelector('.login-modes');
            if (!buttons.length || !panelCodigo || !panelCorreo) return;

            function setMode(mode) {
                const isCorreo = mode === 'correo';
                panelCodigo.classList.toggle('is-active', !isCorreo);
                panelCorreo.classList.toggle('is-active', isCorreo);
                if (modeShell) {
                    modeShell.classList.toggle('is-code', !isCorreo);
                }
                buttons.forEach(btn => btn.classList.toggle('is-active', btn.dataset.mode === mode));
            }

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    setMode(this.dataset.mode);
                });
            });

            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.submitting === 'true') {
                        return;
                    }

                    if (!form.checkValidity()) {
                        return;
                    }

                    event.preventDefault();
                    form.dataset.submitting = 'true';

                    const submitButton = form.querySelector('button[type="submit"]');
                    const submitText = submitButton ? submitButton.querySelector('span') : null;
                    if (submitButton) {
                        submitButton.classList.add('is-loading');
                        submitButton.setAttribute('aria-busy', 'true');
                        submitButton.disabled = true;
                    }

                    const overlay = document.querySelector('.launch-overlay');
                    if (overlay) {
                        overlay.setAttribute('aria-hidden', 'false');
                    }
                    const launchTitle = document.querySelector('.launch-title');
                    const launchCopy = document.querySelector('.launch-copy');
                    const states = [
                        ['Verificando credenciales...', 'Contrastando identidad institucional.'],
                        ['Validando acceso seguro...', 'Activando capa de proteccion.'],
                        ['Preparando panel...', 'Cargando tu espacio de trabajo.']
                    ];
                    document.body.classList.add('auth-launching');

                    states.forEach(function (state, index) {
                        window.setTimeout(function () {
                            if (submitText) {
                                submitText.textContent = state[0];
                            }
                            if (launchTitle) {
                                launchTitle.textContent = state[0];
                            }
                            if (launchCopy) {
                                launchCopy.textContent = state[1];
                            }
                        }, index * 180);
                    });

                    window.setTimeout(function () {
                        if (submitText) {
                            submitText.textContent = 'Preparando panel...';
                        }
                        form.submit();
                    }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 40 : 760);
                });
            });

            const clock = document.getElementById('login-clock');
            function updateClock() {
                if (!clock) return;
                const now = new Date();
                clock.textContent = now.toLocaleTimeString('es-CO', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            updateClock();
            window.setInterval(updateClock, 30000);

            const particleCanvas = document.getElementById('login-particles');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (particleCanvas && !prefersReducedMotion) {
                const ctx = particleCanvas.getContext('2d');
                const particles = [];
                const total = 34;

                function resizeParticles() {
                    const ratio = Math.min(window.devicePixelRatio || 1, 2);
                    particleCanvas.width = Math.floor(window.innerWidth * ratio);
                    particleCanvas.height = Math.floor(window.innerHeight * ratio);
                    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                }

                function seedParticles() {
                    particles.length = 0;
                    for (let i = 0; i < total; i += 1) {
                        particles.push({
                            x: Math.random() * window.innerWidth,
                            y: Math.random() * window.innerHeight,
                            vx: (Math.random() - .5) * .22,
                            vy: (Math.random() - .5) * .18,
                            r: 1.2 + Math.random() * 2.4,
                            a: .18 + Math.random() * .34
                        });
                    }
                }

                function drawParticles() {
                    ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
                    for (let i = 0; i < particles.length; i += 1) {
                        const p = particles[i];
                        p.x += p.vx;
                        p.y += p.vy;

                        if (p.x < -20) p.x = window.innerWidth + 20;
                        if (p.x > window.innerWidth + 20) p.x = -20;
                        if (p.y < -20) p.y = window.innerHeight + 20;
                        if (p.y > window.innerHeight + 20) p.y = -20;

                        ctx.globalAlpha = p.a;
                        ctx.fillStyle = '#69d4ff';
                        ctx.shadowColor = '#69d4ff';
                        ctx.shadowBlur = 10;
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                        ctx.fill();

                        for (let j = i + 1; j < particles.length; j += 1) {
                            const q = particles[j];
                            const dx = p.x - q.x;
                            const dy = p.y - q.y;
                            const distance = Math.sqrt(dx * dx + dy * dy);
                            if (distance < 118) {
                                ctx.globalAlpha = (1 - distance / 118) * .16;
                                ctx.strokeStyle = '#69d4ff';
                                ctx.lineWidth = 1;
                                ctx.beginPath();
                                ctx.moveTo(p.x, p.y);
                                ctx.lineTo(q.x, q.y);
                                ctx.stroke();
                            }
                        }
                    }
                    ctx.globalAlpha = 1;
                    ctx.shadowBlur = 0;
                    window.requestAnimationFrame(drawParticles);
                }

                resizeParticles();
                seedParticles();
                drawParticles();
                window.addEventListener('resize', function () {
                    resizeParticles();
                    seedParticles();
                }, { passive: true });
            }
        })();
    </script>
</body>
</html>

