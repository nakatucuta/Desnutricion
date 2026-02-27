@extends('adminlte::page')

@section('title', 'Inicio')

@section('content_header')
    <div class="welcome-panel">
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

    <div class="row">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card home-card">
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

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card home-card">
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

        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card home-card">
                <div class="card-header border-0">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-history text-success mr-2"></i>Actividad Reciente Personal
                    </h3>
                </div>
                <div class="card-body activity-scroll">
                    @forelse($recentActivity as $activity)
                        <div class="activity-row">
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
        .welcome-panel{
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
        .home-card{
            border-radius:16px;
            border:1px solid #dbe9f8;
            box-shadow:0 14px 24px rgba(20,67,113,.1);
            overflow:hidden;
        }
        .home-card .card-header{
            background:linear-gradient(180deg, rgba(248,252,255,.96), rgba(255,255,255,.95));
        }
        .metric{
            font-size:2.2rem;
            line-height:1;
            font-weight:900;
            color:#2065a1;
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
        .activity-row{
            display:flex;
            gap:.7rem;
            padding:.58rem;
            border:1px solid #e0ecf8;
            border-radius:11px;
            background:linear-gradient(120deg, rgba(255,255,255,.96), rgba(246,251,255,.95));
            margin-bottom:.58rem;
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
        @media (max-width: 768px){
            .welcome-panel{
                flex-direction:column;
                align-items:flex-start;
            }
        }
    </style>
@stop
