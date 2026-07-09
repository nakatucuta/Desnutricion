<div class="pai-admin-nav mb-3">
    <a href="{{ route('afiliado.stats.settings.index') }}" class="pai-admin-nav__item {{ request()->routeIs('afiliado.stats.settings.index') ? 'is-active' : '' }}">
        <span class="pai-admin-nav__eyebrow">Centro</span>
        <strong>Parametrizaciones PAI</strong>
        <small>Vista central de ajustes no recurrentes</small>
    </a>
    <a href="{{ route('afiliado.stats.indicadores.index') }}" class="pai-admin-nav__item {{ request()->routeIs('afiliado.stats.indicadores.*') ? 'is-active' : '' }}">
        <span class="pai-admin-nav__eyebrow">Metas</span>
        <strong>Metas de vacunacion</strong>
        <small>Programacion anual por IPS, cobertura y regimen</small>
    </a>
    <a href="{{ route('afiliado.stats.references.index') }}" class="pai-admin-nav__item {{ request()->routeIs('afiliado.stats.references.*') ? 'is-active' : '' }}">
        <span class="pai-admin-nav__eyebrow">Poblacion objetivo</span>
        <strong>IPS objetivo / referenciadas</strong>
        <small>Que IPS primarias suman al numerador de cada vacunadora</small>
    </a>
</div>
