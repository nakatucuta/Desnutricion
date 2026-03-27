@extends('adminlte::page')

@section('title', ($course['label'] ?? 'Curso de vida') . ' - Opciones')

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('ciclosvida.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div>
            <h1 class="mb-0">{{ $course['label'] ?? 'Curso de vida' }} · Opciones</h1>
            <small class="text-muted">{{ $course['description'] ?? 'Selecciona una categoria para continuar.' }}</small>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        @forelse ($groups as $group)
            <div class="col-12 mt-1 mb-2">
                @if (!empty($group['header_route']))
                    <a href="{{ route($group['header_route']) }}" class="pi-section-link d-block">
                @endif
                        <div class="pi-section d-flex align-items-center">
                            <div class="pi-section-icon"><i class="{{ $group['icon'] ?? 'fas fa-layer-group' }}"></i></div>
                            <h4 class="mb-0">{{ $group['title'] ?? 'Grupo' }}</h4>
                            @if (!empty($group['header_route']))
                                <i class="fas fa-chevron-right ml-auto text-muted"></i>
                            @endif
                        </div>
                @if (!empty($group['header_route']))
                    </a>
                @endif
            </div>

            @foreach (($group['items'] ?? []) as $item)
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <a href="{{ route($item['route'], $item['route_params'] ?? []) }}" class="pi-card-link">
                        <div class="card pi-card {{ $item['theme'] ?? 'grad-indigo' }}">
                            <div class="card-body d-flex align-items-start">
                                <div class="pi-icon-sm mr-2"><i class="{{ $item['icon'] ?? 'fas fa-arrow-right' }}"></i></div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 pi-subtitle">{{ $item['label'] ?? $item['short_label'] ?? $item['description'] ?? 'Modulo' }}</h6>
                                    @if (!empty($item['description']))
                                        <p class="mb-0 pi-desc">{{ $item['description'] }}</p>
                                    @endif
                                </div>
                                <i class="fas fa-chevron-right pi-arrow ml-auto"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    Este curso de vida ya quedo preparado para operar por cache, pero aun no tiene modulos configurados.
                </div>
            </div>
        @endforelse
    </div>
@stop

@section('css')
<style>
body, .content-wrapper, .content, .container-fluid { background-color: #ffffff !important; }
.pi-card-link { display:block; text-decoration:none !important; }
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
.pi-subtitle { font-weight: 600; color:#111827; }
.pi-desc { color:#6b7280 !important; font-size:.92rem; }
.pi-icon-sm{
    width:36px;height:36px;border-radius:10px;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:16px;color:#fff; flex-shrink:0;
}
.pi-arrow { color:#9aa3af; margin-left:10px; }
.pi-section { border-bottom:1px solid #f2f4f7; padding:.25rem 0 .75rem; transition: background .12s ease; border-radius: 10px; }
.pi-section:hover { background:#f9fafb; }
.pi-section-icon{
    width:34px;height:34px;border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    background:#f6f7fb;color:#4b5563;margin-right:.5rem;
}
.pi-section-link { text-decoration: none !important; }
.grad-indigo .pi-icon-sm { background: linear-gradient(135deg,#4338CA,#6366F1); }
.grad-indigo::before { background: radial-gradient(closest-side,#6366F1,#4338CA); }
.grad-pink .pi-icon-sm { background: linear-gradient(135deg,#DB2777,#F472B6); }
.grad-pink::before { background: radial-gradient(closest-side,#F472B6,#DB2777); }
.grad-sky .pi-icon-sm { background: linear-gradient(135deg,#0284C7,#38BDF8); }
.grad-sky::before { background: radial-gradient(closest-side,#38BDF8,#0284C7); }
.grad-cyan .pi-icon-sm { background: linear-gradient(135deg,#06B6D4,#67E8F9); }
.grad-cyan::before { background: radial-gradient(closest-side,#67E8F9,#06B6D4); }
.grad-emerald .pi-icon-sm { background: linear-gradient(135deg,#059669,#34D399); }
.grad-emerald::before { background: radial-gradient(closest-side,#34D399,#059669); }
.grad-amber .pi-icon-sm { background: linear-gradient(135deg,#D97706,#FBBF24); }
.grad-amber::before { background: radial-gradient(closest-side,#FBBF24,#D97706); }
.grad-lime .pi-icon-sm { background: linear-gradient(135deg,#65A30D,#A3E635); }
.grad-lime::before { background: radial-gradient(closest-side,#A3E635,#65A30D); }
.grad-rose .pi-icon-sm { background: linear-gradient(135deg,#E11D48,#FB7185); }
.grad-rose::before { background: radial-gradient(closest-side,#FB7185,#E11D48); }
.grad-violet .pi-icon-sm { background: linear-gradient(135deg,#7C3AED,#A78BFA); }
.grad-violet::before { background: radial-gradient(closest-side,#A78BFA,#7C3AED); }
.grad-orange .pi-icon-sm { background: linear-gradient(135deg,#EA580C,#FDBA74); }
.grad-orange::before { background: radial-gradient(closest-side,#FDBA74,#EA580C); }
</style>
@stop
