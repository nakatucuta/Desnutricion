{{-- resources/views/ciclo_vidas/pi_menu.blade.php --}}
@extends('adminlte::page')

@section('title', 'Primera Infancia - Opciones')

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('ciclosvida.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div>
            <h1 class="mb-0">Primera Infancia · Opciones</h1>
            <small class="text-muted">Selecciona una categoría para continuar.</small>
        </div>
    </div>
@stop

@section('content')
    @php
        $principales = [
            [
                'titulo' => 'Atención en salud médica',
                'desc'   => 'Consulta general, crecimiento y desarrollo.',
                'icon'   => 'fas fa-user-md',
                'route'  => 'ciclosvida.show',
                'route_params' => ['slug' => 'primera-infancia'], // ← va al detalle
                'theme'  => 'grad-indigo',
            ],
            [
                'titulo' => 'Atención por enfermería',
                'desc'   => 'Procedimientos y seguimientos.',
                'icon'   => 'fas fa-notes-medical',
                'route'  => 'ciclosvida.enfermeria',
                'theme'  => 'grad-pink',
            ],
        ];

        $bucal = [
            ['label' => 'Flúor · 1er semestre (≥5 años) · 2/año',   'route' => 'pi.bucal.fluor.sem1',  'theme' => 'grad-sky',    'icon' => 'fas fa-tooth'],
            // ['label' => 'Flúor · 2do semestre (≥5 años) · 2/año',   'route' => 'pi.bucal.fluor.sem2',  'theme' => 'grad-cyan',   'icon' => 'fas fa-tooth'],
            ['label' => 'Control de placa · 1er semestre (≥5)',     'route' => 'pi.bucal.placa.sem1',  'theme' => 'grad-emerald','icon' => 'fas fa-tooth'],
            // ['label' => 'Control de placa · 2do semestre (≥5)',     'route' => 'pi.bucal.placa.sem2',  'theme' => 'grad-teal',   'icon' => 'fas fa-tooth'],
            ['label' => 'Sellantes (≥3 años · según criterio)',     'route' => 'pi.bucal.sellantes',   'theme' => 'grad-amber',  'icon' => 'fas fa-tooth'],
        ];

        $nutri = [
            ['label' => 'Tamizaje de hemoglobina',                  'route' => 'pi.nutri.hemoglobina', 'theme' => 'grad-lime',   'icon' => 'fas fa-vial'],
            ['label' => 'Lactancia materna R202 (1–8 meses)',       'route' => 'pi.nutri.lactancia',   'theme' => 'grad-rose',   'icon' => 'fas fa-baby'],
            ['label' => 'Vitamina A (24–60 meses) R202',            'route' => 'pi.nutri.vitamina_a',  'theme' => 'grad-violet', 'icon' => 'fas fa-capsules'],
            ['label' => 'Hierro (24–59 meses) R202',                'route' => 'pi.nutri.hierro',      'theme' => 'grad-orange', 'icon' => 'fas fa-pills'],
            ['label' => 'Alertas',                                  'route' => 'pi.alertas',      'theme' => 'grad-orange', 'icon' => 'fas fa-pills'],
            ['label' => 'Datos generales',                                  'route' => 'pi.datos',      'theme' => 'grad-orange', 'icon' => 'fas fa-notes-medical'],
        ];
    @endphp

    {{-- Fondo blanco limpio, tarjetas coloridas --}}
    <div class="row">
        {{-- Bloque principales --}}
        @foreach ($principales as $card)
            <div class="col-12 col-md-6 mb-4">
                <a href="{{ route($card['route'], $card['route_params'] ?? []) }}" class="pi-card-link">
                    <div class="card pi-card {{ $card['theme'] }}">
                        <div class="card-body d-flex align-items-center">
                            <div class="pi-icon">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 pi-title">{{ $card['titulo'] }}</h5>
                                <p class="mb-0 text-muted pi-desc">{{ $card['desc'] }}</p>
                            </div>
                            <i class="fas fa-arrow-right pi-arrow"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach

        {{-- ===== Encabezado: Salud bucal (CLICKABLE) ===== --}}
        <div class="col-12 mt-2 mb-2">
            <a href="{{ route('pi.bucal.index') }}" class="pi-section-link d-block">
                <div class="pi-section d-flex align-items-center">
                    <div class="pi-section-icon"><i class="fas fa-tooth"></i></div>
                    <h4 class="mb-0">Atención en salud bucal</h4>
                    <i class="fas fa-chevron-right ml-auto text-muted"></i>
                </div>
            </a>
        </div>

        {{-- Tarjetas: Salud bucal --}}
        @foreach ($bucal as $item)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <a href="{{ route($item['route'], $item['route_params'] ?? []) }}" class="pi-card-link">
                    <div class="card pi-card {{ $item['theme'] }}">
                        <div class="card-body d-flex align-items-start">
                            <div class="pi-icon-sm mr-2"><i class="{{ $item['icon'] }}"></i></div>
                            <h6 class="mb-0 pi-subtitle">{{ $item['label'] }}</h6>
                            <i class="fas fa-chevron-right pi-arrow ml-auto"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach

        {{-- ===== Encabezado: Tamizaje y micronutrientes (CLICKABLE) ===== --}}
        <div class="col-12 mt-1 mb-2">
            <a href="{{ route('pi.nutri.index') }}" class="pi-section-link d-block">
                <div class="pi-section d-flex align-items-center">
                    <div class="pi-section-icon"><i class="fas fa-vial"></i></div>
                    <h4 class="mb-0">Tamizaje y micronutrientes</h4>
                    <i class="fas fa-chevron-right ml-auto text-muted"></i>
                </div>
            </a>
        </div>

        {{-- Tarjetas: Tamizaje y Micronutrientes --}}
        @foreach ($nutri as $item)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <a href="{{ route($item['route'], $item['route_params'] ?? []) }}" class="pi-card-link">
                    <div class="card pi-card {{ $item['theme'] }}">
                        <div class="card-body d-flex align-items-start">
                            <div class="pi-icon-sm mr-2"><i class="{{ $item['icon'] }}"></i></div>
                            <h6 class="mb-0 pi-subtitle">{{ $item['label'] }}</h6>
                            <i class="fas fa-chevron-right pi-arrow ml-auto"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@stop

@section('css')
<style>
/* Fondo blanco en toda el área de contenido */
body, .content-wrapper, .content, .container-fluid { background-color: #ffffff !important; }

/* Enlaces ocupan toda la tarjeta */
.pi-card-link { display:block; text-decoration:none !important; }

/* Tarjeta base: limpia, con borde suave y halo colorido */
.pi-card {
    border: 1px solid #eef1f5;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 6px 18px rgba(0,0,0,.05);
    transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    position: relative;
    overflow: hidden;
}
.pi-card::before{
    content:"";
    position:absolute; inset: -2px -2px auto auto;
    width: 140px; height: 140px; border-radius: 50%;
    opacity:.12; filter: blur(6px);
}
.pi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 26px rgba(0,0,0,.08);
    border-color:#e6eaf0;
}

/* Títulos y textos */
.pi-title { font-weight: 700; color:#0f172a; }
.pi-subtitle { font-weight: 600; color:#111827; }
.pi-desc { color:#6b7280 !important; }

/* Iconos */
.pi-icon{
    width:58px;height:58px;border-radius:14px;
    display:inline-flex;align-items:center;justify-content:center;
    margin-right:14px; font-size:22px; color:#fff;
}
.pi-icon-sm{
    width:36px;height:36px;border-radius:10px;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:16px;color:#fff; flex-shrink:0;
}

/* Flecha */
.pi-arrow { color:#9aa3af; margin-left:10px; }

/* Sub-secciones */
.pi-section { border-bottom:1px solid #f2f4f7; padding:.25rem 0 .75rem; transition: background .12s ease; border-radius: 10px; }
.pi-section:hover { background:#f9fafb; }
.pi-section-icon{
    width:34px;height:34px;border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    background:#f6f7fb;color:#4b5563;margin-right:.5rem;
}
.pi-section-link { text-decoration: none !important; }

/* === Paletas con gradiente (tarjeta + iconos + halo) === */
/* Indigo */
.grad-indigo .pi-icon, .grad-indigo .pi-icon-sm { background: linear-gradient(135deg,#4338CA,#6366F1); }
.grad-indigo::before { background: radial-gradient(closest-side,#6366F1,#4338CA); }

/* Pink */
.grad-pink .pi-icon, .grad-pink .pi-icon-sm { background: linear-gradient(135deg,#DB2777,#F472B6); }
.grad-pink::before { background: radial-gradient(closest-side,#F472B6,#DB2777); }

/* Sky */
.grad-sky .pi-icon, .grad-sky .pi-icon-sm { background: linear-gradient(135deg,#0284C7,#38BDF8); }
.grad-sky::before { background: radial-gradient(closest-side,#38BDF8,#0284C7); }

/* Cyan */
.grad-cyan .pi-icon, .grad-cyan .pi-icon-sm { background: linear-gradient(135deg,#06B6D4,#67E8F9); }
.grad-cyan::before { background: radial-gradient(closest-side,#67E8F9,#06B6D4); }

/* Emerald */
.grad-emerald .pi-icon, .grad-emerald .pi-icon-sm { background: linear-gradient(135deg,#059669,#34D399); }
.grad-emerald::before { background: radial-gradient(closest-side,#34D399,#059669); }

/* Teal */
.grad-teal .pi-icon, .grad-teal .pi-icon-sm { background: linear-gradient(135deg,#0D9488,#5EEAD4); }
.grad-teal::before { background: radial-gradient(closest-side,#5EEAD4,#0D9488); }

/* Amber */
.grad-amber .pi-icon, .grad-amber .pi-icon-sm { background: linear-gradient(135deg,#D97706,#FBBF24); }
.grad-amber::before { background: radial-gradient(closest-side,#FBBF24,#D97706); }

/* Lime */
.grad-lime .pi-icon, .grad-lime .pi-icon-sm { background: linear-gradient(135deg,#65A30D,#A3E635); }
.grad-lime::before { background: radial-gradient(closest-side,#A3E635,#65A30D); }

/* Rose */
.grad-rose .pi-icon, .grad-rose .pi-icon-sm { background: linear-gradient(135deg,#E11D48,#FB7185); }
.grad-rose::before { background: radial-gradient(closest-side,#FB7185,#E11D48); }

/* Violet */
.grad-violet .pi-icon, .grad-violet .pi-icon-sm { background: linear-gradient(135deg,#7C3AED,#A78BFA); }
.grad-violet::before { background: radial-gradient(closest-side,#A78BFA,#7C3AED); }

/* Orange */
.grad-orange .pi-icon, .grad-orange .pi-icon-sm { background: linear-gradient(135deg,#EA580C,#FDBA74); }
.grad-orange::before { background: radial-gradient(closest-side,#FDBA74,#EA580C); }
</style>
@stop
