<div class="content">
    <div class="seg113-kpi-grid mb-4">
        <div class="seg113-kpi-card stat-abiertos" id="filter-abiertos">
            <div class="seg113-kpi-card__icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="seg113-kpi-card__content">
                <span class="seg113-kpi-card__label">Abiertos</span>
                <strong class="seg113-kpi-card__value">{{ $conteo }}</strong>
            </div>
        </div>

        <div class="seg113-kpi-card stat-proximos" id="filter-proximos">
            <div class="seg113-kpi-card__icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="seg113-kpi-card__content">
                <span class="seg113-kpi-card__label">Proximos</span>
                <strong class="seg113-kpi-card__value">{{ $otro->count() }}</strong>
            </div>
        </div>

        <div class="seg113-kpi-card stat-cerrados" id="filter-cerrados">
            <div class="seg113-kpi-card__icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="seg113-kpi-card__content">
                <span class="seg113-kpi-card__label">Cerrados</span>
                <strong class="seg113-kpi-card__value">{{ $cerrados }}</strong>
            </div>
        </div>
    </div>

    <div class="seg113-table-card">
        <div class="seg113-table-card__head">
            <div>
                <span class="seg113-eyebrow">Listado dinamico</span>
                <h3 class="seg113-table-card__title">Seguimientos registrados</h3>
            </div>
            <div class="filtro-anio-wrapper d-flex align-items-center">
                <label for="filtroAnio" class="filtro-label mb-0">Ano:</label>
                <select id="filtroAnio" class="filtro-select">
                    <option value="">Todos</option>
                    @for($anio = now()->year; $anio >= 2022; $anio--)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="table-responsive seg113-table-wrap">
            <table id="seguimiento" class="table table-hover table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha asignacion</th>
                        <th>Identificacion</th>
                        <th>Semana epid</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>IPS</th>
                        <th>Proximo control</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="overlay-spinner">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <strong class="text-dark mt-3 d-block">Cargando datos, por favor espere...</strong>
    </div>
</div>
