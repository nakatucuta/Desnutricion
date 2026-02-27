<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/select2.css') }}">
        {{-- Configured Stylesheets --}}
        @include('adminlte::plugins', ['type' => 'css'])

        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

        @if(config('adminlte.google_fonts.allowed', true))
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        @endif
    @else
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @endif

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg-1:#030814;
            --bg-2:#071629;
            --bg-3:#0b2138;
            --acc:#ffd36f;
            --acc-2:#3f8be2;
            --neon:#69d4ff;
            --tech-bg:#f5f9ff;
            --tech-panel:#ffffff;
            --tech-line:#d6e4f4;
            --tech-ink:#1c2d44;
            --tech-muted:#617d99;
            --tech-glow:rgba(105,212,255,.3);
        }

        body{
            font-family:"Manrope","Plus Jakarta Sans","Source Sans Pro",system-ui,sans-serif;
            background:
                radial-gradient(circle at 12% 8%, rgba(94,208,255,.12), transparent 36%),
                radial-gradient(circle at 92% 88%, rgba(47,134,232,.11), transparent 34%),
                var(--tech-bg);
            color:var(--tech-ink);
        }

        .main-header.navbar{
            position:relative;
            background:linear-gradient(180deg, #ffffff, #f8fbff) !important;
            border-bottom:1px solid var(--tech-line);
            box-shadow:0 8px 24px rgba(20,47,79,.08);
            backdrop-filter:saturate(1.2);
            border-bottom-left-radius:16px;
            border-bottom-right-radius:16px;
            overflow:visible;
        }
        .main-header.navbar::before{
            content:"";
            position:absolute;
            left:-1px;
            right:-1px;
            top:-1px;
            height:8px;
            background:linear-gradient(90deg, rgba(7,22,41,.96) 0%, rgba(63,139,226,.92) 38%, rgba(105,212,255,.9) 72%, rgba(255,211,111,.88) 100%);
            border-top-left-radius:2px;
            border-top-right-radius:2px;
        }
        .main-header .nav-link{
            color:#24405f !important;
            border-radius:10px;
            transition:transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
        }
        .main-header .nav-link:hover{
            color:#0e4d84 !important;
            background:linear-gradient(135deg, rgba(94,208,255,.18), rgba(47,134,232,.14));
            box-shadow:0 6px 14px rgba(29,95,164,.16);
            transform:translateY(-1px);
        }
        .main-header .navbar-nav .nav-item.show > .nav-link,
        .main-header .navbar-nav .nav-link.active{
            color:#0f4e84 !important;
            background:linear-gradient(135deg, rgba(94,208,255,.2), rgba(47,134,232,.14));
            box-shadow:0 6px 14px rgba(29,95,164,.12);
        }
        .main-header .navbar-nav .nav-link > .fas,
        .main-header .navbar-nav .nav-link > .far,
        .main-header .navbar-nav .nav-link > .fab{
            color:#2f79c2;
        }
        .main-header .user-menu > .nav-link > span{
            color:#0a4f8c;
            font-weight:800;
            letter-spacing:.2px;
            padding:.18rem .52rem;
            border-radius:999px;
            background:linear-gradient(120deg, rgba(255,211,111,.32), rgba(94,208,255,.2));
            box-shadow:inset 0 0 0 1px rgba(47,134,232,.18);
        }
        .main-header .user-menu.show > .nav-link > span,
        .main-header .user-menu > .nav-link:hover > span{
            color:#073a67;
            background:linear-gradient(120deg, rgba(255,211,111,.44), rgba(94,208,255,.28));
            box-shadow:inset 0 0 0 1px rgba(47,134,232,.28), 0 4px 10px rgba(47,134,232,.18);
        }
        .main-header .user-menu > .nav-link > .user-image{
            width:36px !important;
            height:36px !important;
            max-width:36px !important;
            min-width:36px !important;
            border-radius:50% !important;
            object-fit:cover !important;
            object-position:center !important;
            border:2px solid rgba(63,139,226,.45);
            box-shadow:0 0 0 2px rgba(255,255,255,.7), 0 4px 12px rgba(31,94,157,.22);
        }
        .main-header .user-menu .dropdown-menu .user-header p{
            color:#103a60;
            font-weight:800;
            letter-spacing:.2px;
        }
        .main-header .user-menu .dropdown-menu{
            margin-top:.42rem;
            border:1px solid #cfe3f6;
            border-top:3px solid #3f8be2;
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 16px 30px rgba(17,57,97,.2);
            background:linear-gradient(180deg, #ffffff, #f8fcff);
            min-width:250px;
        }
        .main-header .user-menu .dropdown-menu .user-header{
            background:linear-gradient(135deg, rgba(63,139,226,.15), rgba(105,212,255,.16), rgba(255,211,111,.18)) !important;
            border-bottom:1px solid #d8e8f8;
            padding:1rem .9rem;
        }
        .main-header .user-menu .dropdown-menu .user-header img{
            width:78px !important;
            height:78px !important;
            border-radius:50% !important;
            object-fit:cover !important;
            object-position:center !important;
            border:3px solid rgba(255,255,255,.92);
            box-shadow:0 0 0 2px rgba(63,139,226,.35), 0 8px 20px rgba(21,77,136,.22);
        }
        .main-header .user-menu .dropdown-menu .user-header small{
            color:#55789b !important;
            font-weight:600;
        }
        .main-header .user-menu .dropdown-menu .user-footer{
            background:rgba(255,255,255,.78);
            border-top:1px solid #d8e8f8;
            padding:.65rem .75rem;
        }
        .main-header .user-menu .dropdown-menu .user-footer .btn{
            border-radius:10px;
            border:1px solid #c6def4;
            background:#fff;
            color:#1f547f;
            font-weight:700;
            transition:transform .16s ease, box-shadow .16s ease, background .16s ease;
        }
        .main-header .user-menu .dropdown-menu .user-footer .btn:hover{
            transform:translateY(-1px);
            background:linear-gradient(120deg, rgba(63,139,226,.14), rgba(105,212,255,.16));
            box-shadow:0 8px 14px rgba(28,90,149,.16);
            color:#0f4f83;
        }
        .main-header .user-menu .dropdown-menu .dropdown-item{
            color:#2a547b;
            font-weight:600;
        }
        .main-header .user-menu .dropdown-menu .dropdown-item:hover{
            background:linear-gradient(120deg, rgba(63,139,226,.1), rgba(105,212,255,.12));
            color:#0f4f83;
        }
        .main-header .dropdown-menu{
            border-top:2px solid rgba(47,134,232,.5);
        }
        .main-header .navbar-badge{
            box-shadow:0 0 0 2px #fff, 0 0 10px rgba(231,76,60,.32);
        }

        .main-sidebar{
            background:
                radial-gradient(circle at 14% 8%, rgba(105,212,255,.2), transparent 36%),
                linear-gradient(165deg, rgba(3,8,20,.9) 0%, rgba(7,22,41,.88) 46%, rgba(11,33,56,.9) 100%) !important;
            border-right:1px solid rgba(128,185,236,.26);
            box-shadow:12px 0 30px rgba(5,14,27,.34), inset -1px 0 0 rgba(105,212,255,.12);
            border-top-right-radius:0;
            border-bottom-right-radius:24px;
            overflow:hidden;
            backdrop-filter:blur(10px) saturate(1.18);
        }
        .main-sidebar::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                repeating-linear-gradient(0deg, rgba(255,255,255,.06) 0 1px, transparent 1px 22px),
                repeating-linear-gradient(135deg, rgba(255,255,255,.028) 0 1px, transparent 1px 28px);
            pointer-events:none;
            opacity:.62;
        }
        .main-sidebar::after{
            content:"";
            position:absolute;
            left:0;
            right:-1px;
            top:-1px;
            height:8px;
            background:linear-gradient(90deg, rgba(7,22,41,.96) 0%, rgba(63,139,226,.92) 38%, rgba(105,212,255,.9) 72%, rgba(255,211,111,.88) 100%);
            pointer-events:none;
            z-index:1;
            border-top-right-radius:0;
        }
        .main-sidebar .sidebar::after{
            content:"";
            position:absolute;
            left:14px;
            right:14px;
            bottom:8px;
            height:4px;
            border-radius:999px;
            background:linear-gradient(90deg, rgba(63,139,226,0) 0%, rgba(63,139,226,.22) 28%, rgba(105,212,255,.3) 54%, rgba(255,211,111,.24) 76%, rgba(63,139,226,0) 100%);
            box-shadow:none;
            opacity:.75;
            pointer-events:none;
        }
        .main-sidebar .brand-link{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            min-height:68px;
            padding:.72rem .78rem;
            border-bottom:1px solid rgba(130,192,244,.22);
            background:linear-gradient(180deg, rgba(8,23,41,.78), rgba(6,17,31,.78));
            overflow:hidden;
            border-top-right-radius:0;
        }
        .main-sidebar .brand-link::after{
            content:"";
            position:absolute;
            left:12px;
            right:12px;
            bottom:8px;
            height:1px;
            background:linear-gradient(90deg, transparent, rgba(105,212,255,.46) 45%, rgba(255,211,111,.34) 62%, transparent);
            pointer-events:none;
        }
        .main-sidebar .brand-text{
            color:#e0f0ff !important;
            font-weight:800;
            letter-spacing:.22px;
            text-shadow:0 0 12px rgba(105,212,255,.22);
            margin:0 !important;
            line-height:1.1;
            white-space:nowrap;
            display:inline-flex;
            align-items:center;
            font-size:1rem;
        }
        .main-sidebar .brand-text b{
            color:#ffe5a3;
            text-shadow:0 0 10px rgba(255,211,111,.28);
        }
        .main-sidebar .brand-link .brand-image,
        .main-sidebar .brand-link .tech-brand-logo{
            float:none !important;
            margin:0 !important;
            width:52px !important;
            height:52px !important;
            max-height:52px !important;
            object-fit:contain;
            opacity:1 !important;
            filter:contrast(1.12) saturate(1.1) drop-shadow(0 0 10px rgba(94,208,255,.3));
            image-rendering:-webkit-optimize-contrast;
            image-rendering:high-quality;
            transform:translateZ(0);
            border-radius:14px;
            background:radial-gradient(circle at 30% 28%, rgba(255,255,255,.96), rgba(227,239,251,.82) 62%, rgba(201,220,238,.72) 100%);
            padding:5px;
            box-shadow:0 0 0 1px rgba(255,255,255,.26), 0 0 0 1px rgba(94,208,255,.22) inset, 0 8px 16px rgba(5,19,34,.36);
        }
        .main-sidebar .sidebar{
            scrollbar-width:thin;
            scrollbar-color:#4f7ea9 #0a1a2d;
        }
        .main-sidebar .nav-sidebar>.nav-item{
            margin:4px 8px;
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link{
            color:#d2e7fb !important;
            border:1px solid transparent;
            border-radius:16px;
            position:relative;
            overflow:hidden;
            transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
            font-family:"Manrope","Plus Jakarta Sans",sans-serif;
            font-weight:700;
            letter-spacing:.18px;
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link::after{
            content:"";
            position:absolute;
            left:3px;
            top:50%;
            width:24px;
            height:24px;
            transform:translateY(-50%) scale(.88);
            background-image:url('{{ asset('img/logo.png') }}');
            background-size:contain;
            background-repeat:no-repeat;
            background-position:center;
            opacity:0;
            filter:drop-shadow(0 0 6px rgba(105,212,255,.28));
            transition:opacity .18s ease, transform .18s ease;
            pointer-events:none;
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link i{
            color:#9ed7ff !important;
            transition:transform .18s ease, color .18s ease, text-shadow .18s ease;
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link .nav-icon{
            width:1.65rem;
            margin-right:.35rem;
            border-radius:10px;
            background:linear-gradient(145deg, rgba(94,208,255,.12), rgba(47,134,232,.08));
            box-shadow:inset 0 0 0 1px rgba(94,208,255,.15);
            padding:6px 0;
            text-align:center;
            font-size:.88rem;
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link::before{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(110deg, transparent 0%, rgba(94,208,255,.36) 40%, transparent 74%);
            transform:translateX(-112%);
            transition:transform .34s ease;
            pointer-events:none;
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link:hover{
            transform:translateX(3px);
            border-color:rgba(130,207,255,.42);
            background:linear-gradient(135deg, rgba(94,208,255,.2), rgba(47,134,232,.16));
            box-shadow:0 8px 18px rgba(9,37,67,.34), 0 0 0 1px rgba(94,208,255,.16), 0 0 10px rgba(94,208,255,.18);
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link:hover::after{
            opacity:.98;
            transform:translateY(-50%) scale(1);
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link:hover i{
            color:#ffffff !important;
            transform:translateX(1px) scale(1.04);
            text-shadow:0 0 8px rgba(105,212,255,.36);
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link:hover .nav-icon{
            background:linear-gradient(145deg, rgba(94,208,255,.25), rgba(47,134,232,.2));
            box-shadow:inset 0 0 0 1px rgba(94,208,255,.3), 0 0 10px rgba(94,208,255,.18);
        }
        .main-sidebar .nav-sidebar>.nav-item>.nav-link:hover::before{
            transform:translateX(112%);
        }
        .main-sidebar .nav-sidebar .nav-link.active{
            color:#f1f8ff !important;
            border-color:rgba(255,211,111,.42);
            background:linear-gradient(135deg, rgba(255,211,111,.17), rgba(94,208,255,.17));
            box-shadow:0 10px 20px rgba(6,33,60,.38), 0 0 12px rgba(255,211,111,.16), 0 0 12px rgba(94,208,255,.16);
        }
        .main-sidebar .nav-sidebar .nav-link.active i{
            color:#ffe9b0 !important;
            text-shadow:0 0 8px rgba(255,211,111,.26);
        }
        .main-sidebar .nav-sidebar .nav-link.active .nav-icon{
            background:linear-gradient(145deg, rgba(255,211,111,.26), rgba(94,208,255,.22));
            box-shadow:inset 0 0 0 1px rgba(255,211,111,.34), 0 0 12px rgba(255,211,111,.16);
        }
        .main-sidebar .nav-treeview>.nav-item>.nav-link{
            color:#b4d3ef !important;
            border-radius:12px;
            transition:background .15s ease, transform .15s ease, color .15s ease;
            font-family:"Manrope","Plus Jakarta Sans",sans-serif;
            font-weight:600;
        }
        .main-sidebar .nav-treeview>.nav-item>.nav-link:hover{
            background:rgba(94,208,255,.16);
            color:#eaf5ff !important;
            transform:translateX(3px);
        }
        .main-sidebar .nav-header{
            color:#82b8e3 !important;
            font-weight:700;
            letter-spacing:.46px;
            text-transform:uppercase;
            font-size:.68rem;
            margin-top:11px;
        }

        .content-wrapper{
            background:transparent !important;
        }
        .content-header h1{
            color:#1f3b58;
            font-family:"Space Grotesk","Plus Jakarta Sans",sans-serif;
            font-weight:700;
            letter-spacing:.2px;
        }
        .card{
            border:1px solid #dbe8f6;
            border-radius:14px;
            box-shadow:0 10px 22px rgba(31,78,130,.08);
        }
        .content-header .breadcrumb{
            background:rgba(255,255,255,.65);
            border:1px solid #dbe8f6;
            border-radius:10px;
            padding:.42rem .72rem;
            box-shadow:0 6px 14px rgba(30,77,127,.06);
        }
        .content-header .breadcrumb-item,
        .content-header .breadcrumb-item a{
            color:#587a9a;
            font-weight:600;
        }
        .content-header .breadcrumb-item.active{
            color:#1f4f7b;
        }
        .card .card-header{
            border-bottom:1px solid #e1ecf8;
            background:linear-gradient(180deg, rgba(247,251,255,.96), rgba(255,255,255,.96));
        }
        .card .card-title{
            color:#22405f;
            font-weight:700;
        }
        .small-box{
            border-radius:14px;
            box-shadow:0 10px 24px rgba(21,66,112,.12);
            overflow:hidden;
        }
        .btn{
            border-radius:10px;
            font-weight:700;
            letter-spacing:.15px;
            transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
        }
        .btn:hover{
            transform:translateY(-1px);
        }
        .btn-primary{
            border-color:#2e80dc;
            background:linear-gradient(120deg, #2f86e8, #56c9ff);
            box-shadow:0 8px 14px rgba(32,94,160,.16);
        }
        .btn-primary:hover{
            filter:brightness(1.03);
            box-shadow:0 10px 18px rgba(32,94,160,.2);
        }
        .btn-outline-primary{
            border-color:#82baf0;
            color:#23649d;
        }
        .btn-outline-primary:hover{
            background:linear-gradient(120deg, rgba(47,134,232,.12), rgba(94,208,255,.16));
            border-color:#58a7ef;
            color:#0f4f87;
        }
        .form-control,
        .custom-select,
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple{
            border-color:#cfe0f1 !important;
            border-radius:10px !important;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.8);
        }
        .form-control:focus,
        .custom-select:focus,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple{
            border-color:#7ebcf4 !important;
            box-shadow:0 0 0 .2rem rgba(94,208,255,.2) !important;
        }
        .input-group-text{
            border-color:#cfe0f1;
            background:#f5faff;
            color:#4e7396;
        }
        .table{
            color:#233e5b;
        }
        .table thead th{
            border-bottom:1px solid #d7e6f6;
            background:linear-gradient(180deg, #f3f9ff, #f9fcff);
            color:#2a4a69;
            font-weight:700;
        }
        .table td,
        .table th{
            border-top:1px solid #e7f0fa;
            vertical-align:middle;
        }
        .table-hover tbody tr{
            transition:background .14s ease;
        }
        .table-hover tbody tr:hover{
            background:linear-gradient(90deg, rgba(94,208,255,.12), rgba(47,134,232,.08));
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select{
            border-radius:9px;
            border-color:#cfe0f1;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button{
            border-radius:8px !important;
            border:1px solid #d2e4f7 !important;
            background:#fff !important;
            color:#2a4d70 !important;
            transition:.14s ease;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
            border-color:#7fbdf2 !important;
            background:linear-gradient(120deg, rgba(47,134,232,.12), rgba(94,208,255,.14)) !important;
            color:#134f82 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current{
            border-color:#5aa8ef !important;
            background:linear-gradient(120deg, rgba(47,134,232,.2), rgba(94,208,255,.22)) !important;
            color:#0f4f83 !important;
            font-weight:700;
        }
        .modal-content{
            border:1px solid #d4e4f4;
            border-radius:14px;
            box-shadow:0 18px 42px rgba(14,46,77,.24);
        }
        .modal-header,
        .modal-footer{
            border-color:#e2edf8;
        }
        .dropdown-menu{
            border:1px solid #d6e5f5;
            border-radius:10px;
            box-shadow:0 10px 24px rgba(23,72,119,.14);
        }
        .dropdown-item{
            transition:background .14s ease, color .14s ease;
        }
        .dropdown-item:hover{
            background:linear-gradient(120deg, rgba(47,134,232,.1), rgba(94,208,255,.12));
            color:#144f83;
        }
        .page-item .page-link{
            color:#2a5b88;
            border-color:#d2e3f5;
            border-radius:8px;
            margin:0 2px;
        }
        .page-item.active .page-link{
            border-color:#5aa8ef;
            background:linear-gradient(120deg, #2f86e8, #5ed0ff);
            box-shadow:0 8px 16px rgba(32,94,160,.22);
        }
        .badge{
            border-radius:999px;
            padding:.4em .66em;
            letter-spacing:.2px;
        }
        .main-footer{
            border-top:1px solid #dbe8f6;
            background:rgba(255,255,255,.82);
            color:#607d99;
        }

        @media (prefers-reduced-motion:reduce){
            .main-header .nav-link,
            .main-sidebar .nav-sidebar>.nav-item>.nav-link,
            .main-sidebar .nav-treeview>.nav-item>.nav-link,
            .btn,
            .table-hover tbody tr{
                transition:none !important;
            }
            .main-sidebar .nav-sidebar>.nav-item>.nav-link::before{
                display:none;
            }
        }
    </style>

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('vendor/adminlte/dist/js/select2.min.js') }}"></script>

        <script>$(document).ready(function() {
            $('.person').select2();
        });
    
        $(document).ready(function() {
    $('.person2').select2();
        });
        </script>
        {{-- Configured Scripts --}}
        @include('adminlte::plugins', ['type' => 'js'])

        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        <script>
            (function () {
                let sessionAlertShown = false;
                const expiredMessage = @json(session('session_expired'));

                function notifyAndReload(message) {
                    if (sessionAlertShown) return;
                    sessionAlertShown = true;

                    const text = message || 'Tu sesion expiro por inactividad. Recarga la pagina e inicia sesion nuevamente.';

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Sesion expirada',
                            text: text,
                            confirmButtonText: 'Recargar',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        alert(text);
                        window.location.reload();
                    }
                }

                if (expiredMessage) {
                    notifyAndReload(expiredMessage);
                }

                if (window.jQuery) {
                    window.jQuery(document).ajaxError(function (_event, jqxhr) {
                        if (jqxhr && jqxhr.status === 419) {
                            notifyAndReload();
                        }
                    });
                }
            })();
        </script>
    @else
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @endif

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
