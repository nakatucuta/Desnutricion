@extends('adminlte::page')

@section('title', 'Inicio')

@section('content_header')
    <div class="home-tech-bg" aria-hidden="true"></div>
    <div class="welcome-panel home-reveal" style="--delay: .15s;">
        <div>
            <h1 class="welcome-title">Centro de Inicio</h1>
            <p class="welcome-subtitle">
                Hola {{ $user->name }}, este es tu panel personal de novedades, seguridad y actividad.
            </p>
        </div>
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            <form method="POST" action="{{ route('ui.iframe.toggle') }}">
                @csrf
                <input type="hidden" name="enabled" value="{{ $iframeModeEnabled ? '0' : '1' }}">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-window-restore mr-1"></i>
                    {{ $iframeModeEnabled ? 'Desactivar modo pestanas' : 'Activar modo pestanas' }}
                </button>
            </form>
            <a href="{{ route('novedades.index') }}" class="btn btn-primary">
                <i class="fas fa-bell mr-1"></i> Ir a Novedades
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="home-stage-effects" aria-hidden="true">
        <div class="impact-ripple" data-impact="0"></div>
        <div class="impact-ripple" data-impact="1"></div>
        <div class="impact-ripple" data-impact="2"></div>
        <canvas id="home-particle-canvas"></canvas>
    </div>

    <div class="row home-dashboard-grid">
        <div class="col-lg-4 col-md-6 mb-3 home-reveal home-drop-card" style="--delay: 1.00s;">
            <div class="card home-card home-card--cyan">
                <div class="card-header border-0">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-bell text-info mr-2"></i>Novedades
                    </h3>
                </div>
                <div class="card-body">
                    <div class="metric">{{ $unreadCount }}</div>
                    <div class="metric-label">Pendientes por leer</div>
                    <hr>
                    @forelse($latestUnreadNovedades as $item)
                        <div class="mini-item d-flex justify-content-between align-items-center">
                            <span class="text-truncate pr-2">{{ $item->title }}</span>
                            <small class="text-muted">{{ optional($item->created_at)->format('m-d') }}</small>
                        </div>
                    @empty
                        <div class="text-muted">No tienes novedades pendientes.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3 home-reveal home-drop-card" style="--delay: 1.85s;">
            <div class="card home-card home-card--gold">
                <div class="card-header border-0">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-lock text-warning mr-2"></i>Seguridad de Cuenta
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge {{ $security['badgeClass'] }}">{{ $security['label'] }}</span>
                    </div>
                    <div class="mini-item">
                        <strong>Ultimo cambio de contrasena:</strong> {{ $security['lastChangeText'] }}
                    </div>
                    <div class="mini-item mt-2">
                        <strong>Recomendacion:</strong> {{ $security['recommendation'] }}
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                            Gestionar Perfil y Contrasena
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 mb-3 home-reveal home-drop-card" style="--delay: 2.70s;">
            <div class="card home-card home-card--green">
                <div class="card-header border-0">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-history text-success mr-2"></i>Actividad Reciente Personal
                    </h3>
                </div>
                <div class="card-body activity-scroll">
                    @forelse($recentActivity as $activity)
                        <div class="activity-row" style="--item-delay: {{ min($loop->index, 8) * 70 }}ms;">
                            <div class="activity-icon">
                                <i class="{{ $activity['icon'] }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">{{ $activity['title'] }}</div>
                                <div class="activity-desc">{{ $activity['description'] }}</div>
                                <small class="text-muted">{{ optional($activity['at'])->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Aun no hay actividad reciente para mostrar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop

@section('adminlte_css')
    @parent
    <style>
        .content-wrapper{
            position:relative;
            overflow:hidden;
            background:
                radial-gradient(circle at 8% 8%, rgba(105,212,255,.15), transparent 28%),
                radial-gradient(circle at 88% 0%, rgba(16,185,129,.12), transparent 30%),
                linear-gradient(135deg, #f6fbff 0%, #eef6ff 52%, #f7fbff 100%);
        }
        .home-tech-bg{
            position:fixed;
            inset:0;
            z-index:0;
            pointer-events:none;
            background-image:
                linear-gradient(rgba(37,99,159,.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,159,.055) 1px, transparent 1px);
            background-size:42px 42px;
            mask-image:radial-gradient(circle at 50% 12%, black, transparent 72%);
            animation:homeGridDrift 16s linear infinite;
        }
        .content-header,
        .content{
            position:relative;
            z-index:1;
        }
        .home-reveal{
            opacity:0;
            transform:translateY(24px) scale(.975);
            filter:blur(8px);
            animation:homeReveal .78s cubic-bezier(.16,.84,.28,1) forwards;
            animation-delay:var(--delay, 0s);
        }
        .home-drop-card{
            position:relative;
            transform-origin:center top;
            transform:translateY(-92px) scale(.92) rotateX(12deg);
            filter:blur(10px);
            animation:cardDropBounce 1.35s cubic-bezier(.18,.9,.22,1) forwards;
            animation-delay:var(--delay, 0s);
        }
        .home-drop-card::after{
            content:"";
            position:absolute;
            left:12%;
            right:12%;
            bottom:-8px;
            height:18px;
            border-radius:50%;
            background:radial-gradient(ellipse, rgba(63,139,226,.22), transparent 70%);
            opacity:0;
            filter:blur(7px);
            animation:dropShadowPulse 1.35s ease forwards;
            animation-delay:var(--delay, 0s);
            pointer-events:none;
        }
        .welcome-panel{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:1rem;
            padding:1rem 1.1rem;
            border:1px solid #d7e8fa;
            border-radius:16px;
            background:
                radial-gradient(circle at 10% 15%, rgba(105,212,255,.18), transparent 42%),
                linear-gradient(145deg, rgba(255,255,255,.96), rgba(246,251,255,.95));
            box-shadow:0 10px 20px rgba(20,67,113,.1);
            overflow:hidden;
        }
        .welcome-panel::before{
            content:"";
            position:absolute;
            inset:-1px;
            background:linear-gradient(110deg, transparent 22%, rgba(105,212,255,.28), transparent 44%);
            transform:translateX(-110%);
            animation:homeSheen 3.8s ease-in-out .7s infinite;
            pointer-events:none;
        }
        .welcome-title{
            margin:0;
            font-weight:800;
            color:#1b446e;
            letter-spacing:.2px;
        }
        .welcome-subtitle{
            margin:.35rem 0 0;
            color:#4b6f92;
            font-weight:600;
        }
        .home-dashboard-grid{
            perspective:1200px;
        }
        .home-stage-effects{
            position:absolute;
            inset:0;
            z-index:0;
            pointer-events:none;
            overflow:visible;
        }
        #home-particle-canvas{
            position:absolute;
            inset:0;
            width:100%;
            height:100%;
            pointer-events:none;
        }
        .impact-ripple{
            position:absolute;
            width:170px;
            height:34px;
            border-radius:50%;
            background:
                radial-gradient(ellipse, rgba(105,212,255,.34) 0%, rgba(63,139,226,.16) 38%, transparent 72%);
            opacity:0;
            filter:blur(2px);
            transform:translate(-50%, -50%) scale(.45);
            mix-blend-mode:multiply;
        }
        .impact-ripple.is-active{
            animation:impactRipple .72s cubic-bezier(.12,.72,.24,1) both;
        }
        .home-card{
            position:relative;
            border-radius:16px;
            border:1px solid #dbe9f8;
            box-shadow:0 14px 24px rgba(20,67,113,.1);
            overflow:hidden;
            transform-style:preserve-3d;
            transition:transform .28s ease, box-shadow .28s ease, border-color .28s ease;
            background:rgba(255,255,255,.9);
        }
        .home-card::before{
            content:"";
            position:absolute;
            inset:0;
            border-radius:inherit;
            background:
                linear-gradient(130deg, rgba(255,255,255,.34), transparent 32%),
                radial-gradient(circle at 16% 10%, var(--card-glow, rgba(105,212,255,.18)), transparent 36%);
            pointer-events:none;
        }
        .home-card::after{
            content:"";
            position:absolute;
            top:0;
            bottom:0;
            left:-42%;
            width:32%;
            background:linear-gradient(100deg, transparent, rgba(255,255,255,.72), transparent);
            transform:skewX(-18deg);
            filter:blur(1px);
            animation:cardSweep 4.8s ease-in-out infinite;
            animation-delay:var(--delay, .2s);
            pointer-events:none;
        }
        .home-drop-card .home-card{
            animation:landingGlow 1.35s ease forwards;
            animation-delay:var(--delay, 0s);
        }
        .home-drop-card.is-landing .home-card{
            animation:landingGlow 1.1s ease both, cardMicroShake .42s cubic-bezier(.36,.07,.19,.97) both;
        }
        .home-card:hover{
            transform:translateY(-5px) rotateX(1.5deg);
            border-color:rgba(63,139,226,.34);
            box-shadow:0 22px 42px rgba(20,67,113,.17), 0 0 34px var(--card-shadow, rgba(105,212,255,.12));
        }
        .home-card--cyan{
            --card-glow:rgba(14,165,233,.20);
            --card-shadow:rgba(14,165,233,.18);
        }
        .home-card--gold{
            --card-glow:rgba(255,193,7,.20);
            --card-shadow:rgba(255,193,7,.16);
        }
        .home-card--green{
            --card-glow:rgba(16,185,129,.18);
            --card-shadow:rgba(16,185,129,.16);
        }
        .home-card .card-header{
            position:relative;
            z-index:1;
            background:linear-gradient(180deg, rgba(248,252,255,.96), rgba(255,255,255,.95));
        }
        .home-card .card-body{
            position:relative;
            z-index:1;
        }
        .home-card .card-title{
            font-weight:800;
            color:#203b5d;
        }
        .metric{
            font-size:2.2rem;
            line-height:1;
            font-weight:900;
            color:#2065a1;
            animation:metricPop .86s cubic-bezier(.2,.9,.25,1.2) both;
            animation-delay:.52s;
        }
        .metric-label{
            margin-top:.3rem;
            color:#5b7c9f;
            font-weight:600;
        }
        .mini-item{
            color:#24496e;
            font-weight:600;
        }
        .activity-scroll{
            max-height:360px;
            overflow:auto;
            padding-right:.3rem;
        }
        .activity-scroll::-webkit-scrollbar{
            width:8px;
        }
        .activity-scroll::-webkit-scrollbar-thumb{
            background:linear-gradient(180deg, rgba(105,212,255,.55), rgba(63,139,226,.45));
            border-radius:999px;
        }
        .activity-row{
            position:relative;
            display:flex;
            gap:.7rem;
            padding:.58rem;
            border:1px solid #e0ecf8;
            border-radius:11px;
            background:linear-gradient(120deg, rgba(255,255,255,.96), rgba(246,251,255,.95));
            margin-bottom:.58rem;
            overflow:hidden;
            opacity:0;
            transform:translateX(18px);
            animation:activityReveal .55s ease forwards;
            animation-delay:calc(3.85s + var(--item-delay, 0ms));
            transition:transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .activity-row::before{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(105deg, transparent 12%, rgba(105,212,255,.16), transparent 38%);
            transform:translateX(-105%);
            animation:activityScan 3.8s ease-in-out infinite;
            animation-delay:calc(4.15s + var(--item-delay, 0ms));
            pointer-events:none;
        }
        .activity-row:hover{
            transform:translateX(-2px);
            border-color:rgba(63,139,226,.32);
            box-shadow:0 10px 22px rgba(20,67,113,.1);
        }
        .activity-icon{
            width:34px;
            height:34px;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#2a78be;
            background:linear-gradient(140deg, rgba(105,212,255,.2), rgba(63,139,226,.17));
            box-shadow:inset 0 0 0 1px rgba(63,139,226,.2);
            flex-shrink:0;
            animation:iconPulse 2.8s ease-in-out infinite;
            animation-delay:calc(4.2s + var(--item-delay, 0ms));
        }
        .activity-title{
            font-weight:800;
            color:#1f4b74;
            line-height:1.15;
        }
        .activity-desc{
            font-size:.9rem;
            color:#54779b;
            margin:.18rem 0;
        }
        @keyframes homeReveal{
            0%{
                opacity:0;
                transform:translateY(24px) scale(.975) rotateX(4deg);
                filter:blur(8px);
            }
            62%{
                opacity:1;
                filter:blur(0);
            }
            100%{
                opacity:1;
                transform:translateY(0) scale(1) rotateX(0);
                filter:blur(0);
            }
        }
        @keyframes cardDropBounce{
            0%{
                opacity:0;
                transform:translateY(-170px) scale(.86) rotateX(22deg) rotateZ(-1.6deg);
                filter:blur(14px) saturate(1.08);
            }
            18%{
                opacity:.55;
                filter:blur(8px) saturate(1.12);
            }
            45%{
                opacity:1;
                transform:translateY(24px) scale(1.035) rotateX(-5deg) rotateZ(.6deg);
                filter:blur(0);
            }
            55%{
                transform:translateY(-18px) scale(.982) rotateX(3deg) rotateZ(-.35deg);
            }
            68%{
                transform:translateY(10px) scale(1.014) rotateX(-1.4deg);
            }
            79%{
                transform:translateY(-6px) scale(.996) rotateX(.7deg);
            }
            90%{
                transform:translateY(3px) scale(1.004);
            }
            100%{
                opacity:1;
                transform:translateY(0) scale(1) rotateX(0);
                filter:blur(0);
            }
        }
        @keyframes dropShadowPulse{
            0%,24%{opacity:0;transform:scaleX(.72)}
            42%{opacity:1;transform:scaleX(1.08)}
            62%{opacity:.38;transform:scaleX(.92)}
            100%{opacity:0;transform:scaleX(1)}
        }
        @keyframes landingGlow{
            0%,24%{box-shadow:0 14px 24px rgba(20,67,113,.1)}
            42%{box-shadow:0 32px 64px rgba(20,67,113,.22), 0 0 62px var(--card-shadow, rgba(105,212,255,.18))}
            100%{box-shadow:0 14px 24px rgba(20,67,113,.1)}
        }
        @keyframes cardMicroShake{
            0%,100%{transform:translateX(0)}
            18%{transform:translateX(-1.5px) rotateZ(-.15deg)}
            36%{transform:translateX(1.2px) rotateZ(.12deg)}
            54%{transform:translateX(-.8px) rotateZ(-.08deg)}
            72%{transform:translateX(.5px)}
        }
        @keyframes impactRipple{
            0%{opacity:0;transform:translate(-50%, -50%) scale(.35)}
            18%{opacity:.95}
            100%{opacity:0;transform:translate(-50%, -50%) scale(2.15)}
        }
        @keyframes homeGridDrift{
            from{transform:translateY(0)}
            to{transform:translateY(42px)}
        }
        @keyframes homeSheen{
            0%,52%,100%{transform:translateX(-110%);opacity:0}
            68%{opacity:1}
            88%{transform:translateX(110%);opacity:0}
        }
        @keyframes cardSweep{
            0%,58%,100%{left:-42%;opacity:0}
            72%{opacity:.75}
            90%{left:122%;opacity:0}
        }
        @keyframes metricPop{
            from{opacity:0;transform:translateY(8px) scale(.78)}
            to{opacity:1;transform:translateY(0) scale(1)}
        }
        @keyframes activityReveal{
            to{opacity:1;transform:translateX(0)}
        }
        @keyframes activityScan{
            0%,56%,100%{transform:translateX(-105%);opacity:0}
            68%{opacity:1}
            86%{transform:translateX(105%);opacity:0}
        }
        @keyframes iconPulse{
            0%,100%{box-shadow:inset 0 0 0 1px rgba(63,139,226,.2), 0 0 0 rgba(105,212,255,0)}
            50%{box-shadow:inset 0 0 0 1px rgba(63,139,226,.28), 0 0 18px rgba(105,212,255,.26)}
        }
        @media (max-width: 768px){
            .welcome-panel{
                flex-direction:column;
                align-items:flex-start;
            }
        }
        @media (prefers-reduced-motion: reduce){
            .home-tech-bg,
            .home-reveal,
            .home-drop-card,
            .home-drop-card::after,
            .home-drop-card .home-card,
            .welcome-panel::before,
            .home-card::after,
            .metric,
            .activity-row,
            .activity-row::before,
            .activity-icon{
                animation:none!important;
            }
            .home-reveal,
            .home-drop-card,
            .activity-row{
                opacity:1;
                transform:none;
                filter:none;
            }
            .home-card,
            .activity-row{
                transition:none!important;
            }
        }
    </style>
@stop

@section('adminlte_js')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const grid = document.querySelector('.home-dashboard-grid');
            const canvas = document.getElementById('home-particle-canvas');
            const cards = Array.from(document.querySelectorAll('.home-drop-card'));
            const ripples = Array.from(document.querySelectorAll('.impact-ripple'));

            if (!grid || !canvas || !cards.length) {
                return;
            }

            const ctx = canvas.getContext('2d');
            let particles = [];
            let animationFrame = null;

            function resizeCanvas() {
                const rect = grid.getBoundingClientRect();
                const ratio = Math.min(window.devicePixelRatio || 1, 2);
                canvas.style.width = rect.width + 'px';
                canvas.style.height = rect.height + 'px';
                canvas.width = Math.max(1, Math.floor(rect.width * ratio));
                canvas.height = Math.max(1, Math.floor(rect.height * ratio));
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            }

            function renderParticles() {
                const rect = grid.getBoundingClientRect();
                ctx.clearRect(0, 0, rect.width, rect.height);

                particles = particles.filter(function (particle) {
                    particle.life -= 1;
                    particle.x += particle.vx;
                    particle.y += particle.vy;
                    particle.vy += 0.055;
                    particle.vx *= 0.985;
                    particle.vy *= 0.985;

                    const alpha = Math.max(0, particle.life / particle.maxLife);
                    ctx.globalAlpha = alpha;
                    ctx.fillStyle = particle.color;
                    ctx.shadowColor = particle.color;
                    ctx.shadowBlur = 11;
                    ctx.beginPath();
                    ctx.arc(particle.x, particle.y, particle.size * (0.65 + alpha), 0, Math.PI * 2);
                    ctx.fill();

                    return particle.life > 0;
                });

                ctx.globalAlpha = 1;
                ctx.shadowBlur = 0;

                if (particles.length) {
                    animationFrame = requestAnimationFrame(renderParticles);
                } else {
                    animationFrame = null;
                    ctx.clearRect(0, 0, rect.width, rect.height);
                }
            }

            function burstAt(card, index) {
                const gridRect = grid.getBoundingClientRect();
                const cardRect = card.getBoundingClientRect();
                const x = cardRect.left - gridRect.left + cardRect.width / 2;
                const y = cardRect.bottom - gridRect.top - 8;
                const colors = ['#69d4ff', '#3f8be2', '#ffd36f', '#10b981'];
                const count = 34;

                const ripple = ripples[index % ripples.length];
                if (ripple) {
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.remove('is-active');
                    void ripple.offsetWidth;
                    ripple.classList.add('is-active');
                }

                card.classList.add('is-landing');
                window.setTimeout(function () {
                    card.classList.remove('is-landing');
                }, 520);

                for (let i = 0; i < count; i += 1) {
                    const angle = Math.PI + (Math.random() * Math.PI);
                    const speed = 1.2 + Math.random() * 3.2;
                    particles.push({
                        x: x + (Math.random() * 56 - 28),
                        y: y + (Math.random() * 8 - 4),
                        vx: Math.cos(angle) * speed,
                        vy: Math.sin(angle) * speed - (Math.random() * 1.2),
                        size: 1.4 + Math.random() * 2.8,
                        life: 34 + Math.floor(Math.random() * 24),
                        maxLife: 58,
                        color: colors[Math.floor(Math.random() * colors.length)]
                    });
                }

                if (!animationFrame) {
                    animationFrame = requestAnimationFrame(renderParticles);
                }
            }

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas, { passive: true });

            cards.forEach(function (card, index) {
                const delay = parseFloat(getComputedStyle(card).getPropertyValue('--delay')) || 0;
                window.setTimeout(function () {
                    burstAt(card, index);
                }, (delay * 1000) + 610);
            });
        });
    </script>
@stop
