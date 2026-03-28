@extends('adminlte::page')

@section('title', 'Ciclos de vida')

@section('content_header')
    <div class="cv-home-hero">
        <div>
            <span class="cv-home-chip">Cursos de vida</span>
            <h1 class="mb-2">Opciones principales del modulo</h1>
            <p class="mb-0">
                Navega por cada curso de vida, consulta la informacion general consolidada
                y entra al tablero estadistico con una lectura transversal de todas las atenciones.
            </p>
        </div>
        <div class="cv-home-side">
            <div class="cv-home-side-card">
                <small>Vista principal</small>
                <strong>Menus operativos por curso</strong>
            </div>
            <div class="cv-home-side-card">
                <small>Accesos complementarios</small>
                <strong>Estadisticas e informacion general</strong>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row mb-4">
        @foreach ($quickCards as $card)
            <div class="col-12 col-lg-4 mb-3">
                <a href="{{ $card['route'] }}" class="cv-home-link">
                    <div class="cv-home-feature {{ $card['color'] }}">
                        <div class="cv-home-feature-icon">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1">{{ $card['title'] }}</h3>
                            <p class="mb-0">{{ $card['description'] }}</p>
                        </div>
                        <i class="fas fa-arrow-right cv-home-arrow"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title mb-1">Cursos de vida y opciones</h3>
            <small class="text-muted">Selecciona el curso que quieres revisar para entrar a sus modulos, atenciones y alertas.</small>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($etapas as $slug => $etapa)
                    <div class="col-12 col-md-6 col-xl-4 d-flex align-items-stretch">
                        <a href="{{ route($etapa['route_name']) }}" class="w-100 text-reset cv-home-link">
                            <div class="card cv-home-course shadow-sm mb-4">
                                <div class="card-body d-flex">
                                    <div class="cv-home-course-icon {{ $etapa['color'] }} mr-3">
                                        <i class="{{ $etapa['icono'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="card-title mb-1">{{ $etapa['titulo'] }}</h3>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                        <div class="cv-home-course-meta">
                                            <span><i class="far fa-clock mr-1"></i>{{ $etapa['age_label'] }}</span>
                                            <span><i class="fas fa-layer-group mr-1"></i>{{ $etapa['module_count'] }} opciones</span>
                                        </div>
                                        <p class="card-text text-muted mb-0">{{ $etapa['descripcion'] }}</p>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">{{ $etapa['group_count'] }} bloques operativos</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .content-wrapper, .content, .container-fluid { background: #f4f7fb !important; }
        .cv-home-hero {
            display: grid;
            grid-template-columns: 1.65fr 1fr;
            gap: 1rem;
            padding: 1.5rem 1.75rem;
            border-radius: 26px;
            background:
                radial-gradient(circle at top left, rgba(96,165,250,.24), transparent 32%),
                radial-gradient(circle at bottom right, rgba(59,130,246,.18), transparent 28%),
                linear-gradient(135deg, #0f172a, #1d4ed8 55%, #0f766e);
            color: #fff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .22);
        }
        .cv-home-chip {
            display: inline-flex;
            align-items: center;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            font-size: .82rem;
            letter-spacing: .03em;
            margin-bottom: .9rem;
        }
        .cv-home-hero h1 {
            font-size: 2.15rem;
            font-weight: 800;
            color: #fff !important;
        }
        .cv-home-hero p { color: rgba(255,255,255,.82); }
        .cv-home-side {
            display: grid;
            gap: .85rem;
            align-content: center;
        }
        .cv-home-side-card {
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.12);
        }
        .cv-home-side-card small {
            display: block;
            color: rgba(255,255,255,.74);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .2rem;
        }
        .cv-home-link { text-decoration: none !important; }
        .cv-home-feature,
        .cv-home-course {
            border-radius: 20px;
            transition: transform .16s ease, box-shadow .16s ease;
        }
        .cv-home-feature:hover,
        .cv-home-course:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .1);
        }
        .cv-home-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.35rem;
            color: #fff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .14);
        }
        .cv-home-feature p { color: rgba(255,255,255,.88); }
        .cv-home-feature-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.15);
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .cv-home-arrow { color: rgba(255,255,255,.72); }
        .grad-indigo { background: linear-gradient(135deg, #4338ca, #2563eb); }
        .grad-emerald { background: linear-gradient(135deg, #0f766e, #16a34a); }
        .grad-rose { background: linear-gradient(135deg, #be123c, #f43f5e); }
        .cv-home-course {
            border: 1px solid #e6edf7;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        }
        .cv-home-course-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.35rem;
            flex: 0 0 58px;
        }
        .cv-home-course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem .9rem;
            margin-bottom: .55rem;
            font-size: .86rem;
            color: #475569;
        }
        @media (max-width: 991px) {
            .cv-home-hero {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop
