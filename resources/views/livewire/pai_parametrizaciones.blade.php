@extends('adminlte::page')

@section('title', 'PAI - Parametrizaciones')

@section('content_header')
<div class="pai-head">
    <div>
        <h1 class="pai-title mb-1">Parametrizaciones PAI</h1>
        <div class="text-muted">Centro unico para ajustes no recurrentes del tablero PAI.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('afiliado.stats.view') }}" class="btn btn-outline-secondary">
            <i class="fas fa-chart-line mr-1"></i> Volver a Estadisticas
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4 pai-settings-shell">
    @include('livewire.partials.pai_admin_nav')

    <div class="pai-settings-hero card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="pai-settings-hero__eyebrow">Operacion segura</div>
                    <h2 class="pai-settings-hero__title mb-2">Todo lo que cambia el calculo PAI, en un solo lugar</h2>
                    <p class="text-muted mb-0">
                        Desde aqui encuentras rapido los dos ajustes que mas impacto tienen en cobertura:
                        las metas anuales y las IPS primarias que si cuentan para cada IPS vacunadora.
                    </p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="pai-settings-kpis">
                        <div class="pai-settings-kpi">
                            <span>Vigencia actual</span>
                            <strong>{{ (int) $defaultYear }}</strong>
                        </div>
                        <div class="pai-settings-kpi">
                            <span>Metas configuradas</span>
                            <strong>{{ number_format((int) data_get($metaSummary, 'rows', 0)) }}</strong>
                        </div>
                        <div class="pai-settings-kpi">
                            <span>Relaciones IPS</span>
                            <strong>{{ number_format((int) data_get($referenceSummary, 'rows', 0)) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-3">
            <div class="card pai-settings-card h-100">
                <div class="card-body">
                    <div class="pai-settings-card__eyebrow">Paso 1</div>
                    <h3 class="pai-settings-card__title">Metas de vacunacion</h3>
                    <p class="text-muted">
                        Ajusta la programacion anual por IPS, periodo, regimen y biologico. Esta pantalla sigue siendo
                        la fuente del denominador para cobertura.
                    </p>
                    <div class="pai-settings-stats">
                        <span><strong>{{ number_format((int) data_get($metaSummary, 'rows', 0)) }}</strong> registros</span>
                        <span><strong>{{ number_format((int) data_get($metaSummary, 'prestadores', 0)) }}</strong> IPS</span>
                        <span><strong>{{ number_format((int) data_get($metaSummary, 'municipios', 0)) }}</strong> municipios</span>
                    </div>
                    <div class="pai-settings-preview mt-3">
                        @forelse(($metaRowsPreview ?? collect()) as $row)
                            <div class="pai-settings-preview__row">
                                <strong>{{ $row['prestador'] ?? 'IPS' }}</strong>
                                <span>{{ $row['municipio'] ?? '' }} · {{ $row['biologico'] ?? '' }}</span>
                            </div>
                        @empty
                            <div class="text-muted small">Aun no hay metas para esta vigencia.</div>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('afiliado.stats.indicadores.index', ['year' => $defaultYear]) }}" class="btn btn-primary">
                            <i class="fas fa-bullseye mr-1"></i> Abrir metas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-3">
            <div class="card pai-settings-card h-100 pai-settings-card--accent">
                <div class="card-body">
                    <div class="pai-settings-card__eyebrow">Paso 2</div>
                    <h3 class="pai-settings-card__title">IPS objetivo / referenciadas</h3>
                    <p class="text-muted">
                        Define para cada IPS vacunadora que IPS primarias de usuarios si suman al numerador.
                        Aqui se controla la cobertura real que entra al calculo.
                    </p>
                    <div class="pai-settings-stats">
                        <span><strong>{{ number_format((int) data_get($referenceSummary, 'rows', 0)) }}</strong> relaciones</span>
                        <span><strong>{{ number_format((int) data_get($referenceSummary, 'vaccinators', 0)) }}</strong> vacunadoras</span>
                        <span><strong>{{ number_format((int) data_get($referenceSummary, 'target_ips', 0)) }}</strong> IPS objetivo</span>
                    </div>
                    <div class="pai-settings-preview mt-3">
                        @forelse(($referenceRowsPreview ?? collect()) as $row)
                            <div class="pai-settings-preview__row">
                                <strong>{{ $row['ips_vacunadora_nombre'] ?? 'IPS vacunadora' }}</strong>
                                <span>{{ $row['ips_primaria_nombre'] ?? 'IPS primaria' }} · {{ $row['municipio'] ?: 'Todos los municipios' }}</span>
                            </div>
                        @empty
                            <div class="text-muted small">Aun no hay relaciones configuradas para esta vigencia.</div>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('afiliado.stats.references.index', ['year' => $defaultYear]) }}" class="btn btn-dark">
                            <i class="fas fa-project-diagram mr-1"></i> Abrir IPS objetivo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.pai-settings-shell{background:linear-gradient(180deg,#f5f8ff 0%,#fff 100%)}
.pai-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.pai-title{font-weight:900;color:#132238}
.pai-admin-nav{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.pai-admin-nav__item{display:flex;flex-direction:column;gap:4px;padding:14px 16px;border-radius:18px;border:1px solid rgba(15,23,42,.08);background:#fff;color:#0f172a;box-shadow:0 10px 24px rgba(15,23,42,.05);text-decoration:none}
.pai-admin-nav__item small{color:#64748b}
.pai-admin-nav__item.is-active{border-color:rgba(29,78,216,.26);background:linear-gradient(180deg,#eff6ff,#fff)}
.pai-admin-nav__eyebrow{font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#2563eb}
.pai-settings-hero{border:0;border-radius:22px;box-shadow:0 18px 40px rgba(15,23,42,.06);background:radial-gradient(circle at top left,#eef6ff 0%,#ffffff 55%)}
.pai-settings-hero__eyebrow{display:inline-block;padding:4px 10px;border-radius:999px;background:#e7f0ff;color:#1d4ed8;font-size:.76rem;font-weight:800;text-transform:uppercase}
.pai-settings-hero__title{font-size:1.6rem;font-weight:900;color:#0f172a}
.pai-settings-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.pai-settings-kpi{padding:14px;border-radius:16px;border:1px solid rgba(15,23,42,.08);background:#fff}
.pai-settings-kpi span{display:block;font-size:.74rem;text-transform:uppercase;color:#64748b;font-weight:800}
.pai-settings-kpi strong{font-size:1.3rem;color:#0f172a}
.pai-settings-card{border:1px solid rgba(15,23,42,.08);border-radius:20px;box-shadow:0 14px 34px rgba(15,23,42,.06)}
.pai-settings-card--accent{background:linear-gradient(180deg,#f9fbff,#fff)}
.pai-settings-card__eyebrow{font-size:.74rem;text-transform:uppercase;font-weight:900;color:#1d4ed8}
.pai-settings-card__title{font-size:1.35rem;font-weight:900;color:#0f172a}
.pai-settings-stats{display:flex;flex-wrap:wrap;gap:10px;font-size:.88rem;color:#334155}
.pai-settings-preview{display:grid;gap:8px}
.pai-settings-preview__row{padding:10px 12px;border-radius:14px;background:#f8fbff;border:1px solid rgba(148,163,184,.18)}
.pai-settings-preview__row strong{display:block;color:#0f172a}
.pai-settings-preview__row span{font-size:.84rem;color:#64748b}
.gap-2{gap:.5rem}
@media (max-width: 991.98px){
    .pai-admin-nav{grid-template-columns:1fr}
    .pai-settings-kpis{grid-template-columns:1fr}
}
</style>
@stop
