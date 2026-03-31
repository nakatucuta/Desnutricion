@extends('adminlte::page')

@section('title', 'Ciclos de vida')

@section('content_header')
    @php
        $courseCount = count($etapas);
    @endphp

    <div class="cv-home-hero">
        <div class="cv-home-hero__backdrop"></div>
        <div class="cv-home-hero__copy">
            <span class="cv-home-chip">Cursos de vida</span>
            <h1 class="mb-3">Gestiona aqui toda la informacion.</h1>
            <p class="mb-0">
                Accede a estadisticas, informacion general, reportes y a cada curso de vida .
            </p>
        </div>

        <div class="cv-home-hero__visual">
            <div class="cv-home-crest-panel">
                <div class="cv-home-crest-ring"></div>
                <div class="cv-home-crest">
                    <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional">
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <section class="cv-home-shell mb-5">
        <div class="cv-home-section-head">
            <div>
                <span class="cv-home-kicker">Accesos clave</span>
                <h2>Explora el modulo</h2>
                <p>
                Desde aqui puedes acceder a reportes informacion general y estaditicas    
                </p>
            </div>
            <div class="cv-home-section-pill">
                <span></span>
                Explorar
            </div>
        </div>

        <div class="row cv-home-command-grid mb-2">
            @foreach ($quickCards as $card)
                <div class="col-12 col-lg-4 mb-4">
                    <a href="{{ $card['route'] }}" class="cv-home-link">
                        <article class="cv-home-feature {{ $card['color'] }}" data-tilt-card>
                            <div class="cv-home-feature__beam"></div>
                            <div class="cv-home-feature__scan"></div>

                            <div class="cv-home-feature__topline">
                                <span class="cv-home-feature__tag">Modo {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="cv-home-feature__dot"></span>
                            </div>

                            <div class="cv-home-feature__body">
                                <div class="cv-home-feature-icon">
                                    <i class="{{ $card['icon'] }}"></i>
                                </div>
                                <div class="cv-home-feature__copy">
                                    <h3 class="mb-2">{{ $card['title'] }}</h3>
                                    <p class="mb-0">{{ $card['description'] }}</p>
                                </div>
                            </div>

                            <div class="cv-home-feature__footer">
                                <span>Entrar ahora</span>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </article>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <section class="cv-home-shell">
        <div class="cv-home-section-head cv-home-section-head--courses">
            <div>
                <span class="cv-home-kicker">Arquitectura por curso</span>
                <h2>Selecciona una etapa y entra a sus modulos operativos</h2>
                <p>
                Selecciona un curso de vida    
                </p>
            </div>
            <div class="cv-home-section-summary">
                <strong>{{ $courseCount }}</strong>
                <span>rutas de navegacion disponibles</span>
            </div>
        </div>

        <div class="row">
            @foreach ($etapas as $slug => $etapa)
                <div class="col-12 col-md-6 col-xl-4 mb-4 d-flex align-items-stretch">
                    <a href="{{ route($etapa['route_name']) }}" class="cv-home-link w-100">
                        <article class="cv-home-course" data-tilt-card>
                            <div class="cv-home-course__mesh"></div>

                            <div class="cv-home-course__header">
                                <div class="cv-home-course-icon {{ $etapa['color'] }}">
                                    <i class="{{ $etapa['icono'] }}"></i>
                                </div>
                                <div class="cv-home-course__index">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>

                            <h3>{{ $etapa['titulo'] }}</h3>
                            <p>{{ $etapa['descripcion'] }}</p>

                            <div class="cv-home-course-meta">
                                <span><i class="far fa-clock"></i>{{ $etapa['age_label'] }}</span>
                                <span><i class="fas fa-layer-group"></i>{{ $etapa['module_count'] }} opciones</span>
                                <span><i class="fas fa-th-large"></i>{{ $etapa['group_count'] }} bloques</span>
                            </div>

                            <div class="cv-home-course__footer">
                                <span>Ver modulos y accesos</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </article>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@stop

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cv-text: #081220;
            --cv-muted: #64748b;
            --cv-shadow: 0 28px 70px rgba(3, 12, 25, .16);
        }

        .content-wrapper,
        .content,
        .container-fluid {
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, .10), transparent 24%),
                radial-gradient(circle at 85% 0%, rgba(34, 197, 94, .08), transparent 22%),
                linear-gradient(180deg, #eef5ff 0%, #f7fbff 45%, #f1f6fd 100%) !important;
        }

        .content-header,
        .content {
            font-family: 'Manrope', sans-serif;
        }

        .cv-home-hero,
        .cv-home-shell,
        .cv-home-feature,
        .cv-home-course {
            position: relative;
            overflow: hidden;
        }

        .cv-home-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(260px, .8fr);
            gap: 1.4rem;
            padding: 2rem;
            border-radius: 34px;
            background:
                radial-gradient(circle at 18% 18%, rgba(56, 189, 248, .22), transparent 22%),
                radial-gradient(circle at 82% 14%, rgba(251, 191, 36, .14), transparent 18%),
                radial-gradient(circle at 72% 78%, rgba(20, 184, 166, .18), transparent 20%),
                linear-gradient(135deg, #06111f 0%, #10294c 52%, #0b5f67 100%);
            border: 1px solid rgba(148, 163, 184, .14);
            box-shadow: 0 28px 90px rgba(2, 6, 23, .28);
            color: #f8fbff;
        }

        .cv-home-hero__backdrop {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 24px 24px;
            opacity: .18;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.85), transparent 92%);
            pointer-events: none;
        }

        .cv-home-hero__copy,
        .cv-home-hero__visual {
            position: relative;
            z-index: 1;
        }

        .cv-home-chip,
        .cv-home-kicker,
        .cv-home-feature__tag {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .12em;
            font-size: .72rem;
            font-weight: 800;
        }

        .cv-home-chip {
            padding: .45rem .82rem;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .14);
            color: rgba(255,255,255,.88);
            margin-bottom: 1rem;
        }

        .cv-home-hero h1,
        .cv-home-section-head h2,
        .cv-home-feature h3,
        .cv-home-course h3 {
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -.03em;
        }

        .cv-home-hero h1 {
            font-size: clamp(2.2rem, 3vw, 3.3rem);
            line-height: 1.02;
            font-weight: 700;
            color: #fff !important;
            max-width: 760px;
        }

        .cv-home-hero p {
            max-width: 720px;
            font-size: 1.02rem;
            line-height: 1.75;
            color: rgba(236, 253, 245, .82);
        }

        .cv-home-hero__visual {
            display: grid;
            align-content: center;
        }

        .cv-home-crest-panel {
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.07);
            backdrop-filter: blur(14px);
            position: relative;
            min-height: 250px;
            display: grid;
            place-items: center;
        }

        .cv-home-crest-ring {
            position: absolute;
            width: 210px;
            height: 210px;
            border-radius: 50%;
            border: 1px dashed rgba(255,255,255,.34);
            box-shadow:
                0 0 0 20px rgba(255,255,255,.03),
                0 0 45px rgba(56, 189, 248, .20);
            animation: cv-home-spin 20s linear infinite;
        }

        .cv-home-crest {
            position: relative;
            width: 154px;
            height: 154px;
            padding: 1rem;
            border-radius: 34px;
            background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(219,234,254,.90));
            box-shadow: 0 18px 40px rgba(0, 0, 0, .22);
            display: grid;
            place-items: center;
        }

        .cv-home-crest img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 10px 18px rgba(8, 15, 33, .18));
        }

        .cv-home-shell {
            position: relative;
            padding: 1.65rem;
            border-radius: 32px;
            background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(245,249,255,.96));
            border: 1px solid rgba(226, 232, 240, .88);
            box-shadow: var(--cv-shadow);
        }

        .cv-home-shell::before {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 31px;
            background:
                radial-gradient(circle at top right, rgba(96, 165, 250, .12), transparent 28%),
                linear-gradient(180deg, rgba(255,255,255,.86), rgba(248,250,252,.82));
            z-index: 0;
            pointer-events: none;
        }

        .cv-home-shell > * {
            position: relative;
            z-index: 1;
        }

        .cv-home-section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1.25rem;
            margin-bottom: 1.45rem;
        }

        .cv-home-kicker {
            color: #0f4c81;
            margin-bottom: .65rem;
        }

        .cv-home-section-head h2 {
            margin-bottom: .55rem;
            font-size: clamp(1.55rem, 2vw, 2.3rem);
            color: #06111f;
        }

        .cv-home-section-head p {
            margin-bottom: 0;
            max-width: 780px;
            color: var(--cv-muted);
            line-height: 1.7;
        }

        .cv-home-section-pill,
        .cv-home-section-summary {
            flex-shrink: 0;
            border-radius: 18px;
            border: 1px solid rgba(14, 116, 144, .12);
            background: linear-gradient(135deg, #eff6ff, #ecfeff);
            box-shadow: 0 12px 24px rgba(15, 23, 42, .06);
        }

        .cv-home-section-pill {
            display: inline-flex;
            align-items: center;
            gap: .7rem;
            padding: .88rem 1rem;
            font-weight: 700;
            color: #0f4c81;
        }

        .cv-home-section-pill span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #14b8a6, #38bdf8);
            box-shadow: 0 0 0 8px rgba(20, 184, 166, .12);
        }

        .cv-home-section-summary {
            min-width: 180px;
            padding: 1rem 1.1rem;
            text-align: right;
        }

        .cv-home-section-summary strong {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            color: #082032;
        }

        .cv-home-section-summary span {
            color: #4b5563;
            font-size: .92rem;
        }

        .cv-home-link {
            text-decoration: none !important;
        }

        .cv-home-command-grid:hover .cv-home-feature:not(:hover) {
            transform: scale(.985) translateY(6px);
            opacity: .92;
        }

        .cv-home-feature,
        .cv-home-course {
            --rx: 0deg;
            --ry: 0deg;
            --mx: 50%;
            --my: 50%;
            border-radius: 26px;
            transform: perspective(1400px) rotateX(var(--rx)) rotateY(var(--ry));
            transform-style: preserve-3d;
            transition:
                transform .18s ease,
                box-shadow .24s ease,
                filter .24s ease,
                opacity .24s ease;
            will-change: transform;
        }

        .cv-home-feature {
            min-height: 285px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.2rem 1.25rem 1.15rem;
            color: #fff;
            box-shadow: 0 24px 50px rgba(8, 15, 33, .18);
        }

        .cv-home-feature:hover {
            box-shadow: 0 34px 65px rgba(8, 15, 33, .26);
            filter: saturate(1.05);
        }

        .cv-home-feature__beam,
        .cv-home-feature__scan {
            position: absolute;
            pointer-events: none;
        }

        .cv-home-feature__beam {
            inset: -40% auto auto -12%;
            width: 60%;
            height: 190%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.14), transparent);
            transform: rotate(18deg) translateX(-140%);
            transition: transform .65s ease;
        }

        .cv-home-feature:hover .cv-home-feature__beam {
            transform: rotate(18deg) translateX(300%);
        }

        .cv-home-feature__scan {
            inset: 0;
            background:
                radial-gradient(circle at var(--mx) var(--my), rgba(255,255,255,.24), transparent 28%),
                linear-gradient(180deg, rgba(255,255,255,.08), transparent 58%);
            opacity: .95;
        }

        .cv-home-feature__topline,
        .cv-home-feature__body,
        .cv-home-feature__footer,
        .cv-home-course > * {
            position: relative;
            z-index: 1;
        }

        .cv-home-feature__topline,
        .cv-home-feature__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cv-home-feature__tag {
            padding: .48rem .78rem;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.16);
            color: rgba(255,255,255,.9);
        }

        .cv-home-feature__dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #fef08a;
            box-shadow: 0 0 0 8px rgba(254, 240, 138, .18);
            animation: cv-home-pulse 2.2s ease-in-out infinite;
        }

        .cv-home-feature__body {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .cv-home-feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.16);
            font-size: 1.5rem;
            flex-shrink: 0;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.18);
        }

        .cv-home-feature__copy h3 {
            font-size: 1.6rem;
            color: #fff;
        }

        .cv-home-feature__copy p {
            color: rgba(255,255,255,.85);
            line-height: 1.7;
            font-size: .96rem;
        }

        .cv-home-feature__footer {
            padding-top: .95rem;
            border-top: 1px solid rgba(255,255,255,.14);
            color: rgba(255,255,255,.88);
            font-weight: 700;
        }

        .grad-indigo {
            background: linear-gradient(135deg, #16213e 0%, #1946d2 52%, #21b3ff 100%);
        }

        .grad-emerald {
            background: linear-gradient(135deg, #042f2e 0%, #0f766e 48%, #34d399 100%);
        }

        .grad-rose {
            background: linear-gradient(135deg, #4c0519 0%, #b91c1c 48%, #fb7185 100%);
        }

        .cv-home-course {
            height: 100%;
            padding: 1.2rem;
            border: 1px solid rgba(203, 213, 225, .74);
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            box-shadow: 0 18px 42px rgba(15, 23, 42, .08);
        }

        .cv-home-course:hover {
            box-shadow: 0 24px 55px rgba(15, 23, 42, .12);
        }

        .cv-home-course__mesh {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at var(--mx) var(--my), rgba(59, 130, 246, .14), transparent 26%),
                linear-gradient(180deg, rgba(255,255,255,.25), transparent 55%);
            pointer-events: none;
        }

        .cv-home-course__header,
        .cv-home-course__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cv-home-course__header {
            margin-bottom: 1rem;
        }

        .cv-home-course-icon {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.45rem;
            box-shadow: 0 16px 30px rgba(15, 23, 42, .15);
        }

        .cv-home-course__index {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: rgba(15, 23, 42, .22);
        }

        .cv-home-course h3 {
            font-size: 1.55rem;
            color: var(--cv-text);
            margin-bottom: .7rem;
        }

        .cv-home-course p {
            color: #556476;
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .cv-home-course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            margin-bottom: 1.2rem;
        }

        .cv-home-course-meta span {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .52rem .78rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            border: 1px solid #dbe6f3;
            color: #24435f;
            font-size: .84rem;
            font-weight: 700;
        }

        .cv-home-course__footer {
            padding-top: 1rem;
            border-top: 1px solid rgba(203, 213, 225, .68);
            color: #0f4c81;
            font-weight: 700;
        }

        .cv-home-course__footer i,
        .cv-home-feature__footer i {
            transition: transform .18s ease;
        }

        .cv-home-course:hover .cv-home-course__footer i,
        .cv-home-feature:hover .cv-home-feature__footer i {
            transform: translateX(4px);
        }

        @keyframes cv-home-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes cv-home-pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 8px rgba(254, 240, 138, .18); }
            50% { transform: scale(1.18); box-shadow: 0 0 0 12px rgba(254, 240, 138, .10); }
        }

        @media (max-width: 1199px) {
            .cv-home-hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991px) {
            .cv-home-shell,
            .cv-home-hero {
                padding: 1.35rem;
                border-radius: 26px;
            }

            .cv-home-section-head {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 767px) {
            .cv-home-hero h1 {
                font-size: 2rem;
            }

            .cv-home-feature,
            .cv-home-course {
                transform: none !important;
            }

            .cv-home-crest-panel {
                border-radius: 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .cv-home-feature,
            .cv-home-course,
            .cv-home-feature__beam,
            .cv-home-crest-ring,
            .cv-home-feature__dot {
                animation: none !important;
                transition: none !important;
                transform: none !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cards = document.querySelectorAll('[data-tilt-card]');

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            cards.forEach((card) => {
                const reset = () => {
                    card.style.setProperty('--rx', '0deg');
                    card.style.setProperty('--ry', '0deg');
                    card.style.setProperty('--mx', '50%');
                    card.style.setProperty('--my', '50%');
                };

                card.addEventListener('mousemove', (event) => {
                    const rect = card.getBoundingClientRect();
                    const x = event.clientX - rect.left;
                    const y = event.clientY - rect.top;
                    const rotateY = ((x / rect.width) - 0.5) * 12;
                    const rotateX = ((0.5 - (y / rect.height)) * 10);

                    card.style.setProperty('--rx', rotateX.toFixed(2) + 'deg');
                    card.style.setProperty('--ry', rotateY.toFixed(2) + 'deg');
                    card.style.setProperty('--mx', ((x / rect.width) * 100).toFixed(2) + '%');
                    card.style.setProperty('--my', ((y / rect.height) * 100).toFixed(2) + '%');
                });

                card.addEventListener('mouseleave', reset);
                card.addEventListener('blur', reset, true);
            });
        });
    </script>
@stop
