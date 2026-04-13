<div class="pai-analytics pai-card mb-3">
    <div class="pai-analytics__head">
        <div class="pai-analytics__brand">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="pai-analytics__logo">
            <div>
                <div class="pai-analytics__title">Estadísticas Detalladas PAI</div>
                <div class="pai-analytics__subtitle">Panel ejecutivo dinámico por municipio, prestador y biológico</div>
            </div>
        </div>
        <div class="pai-analytics__actions">
            <button class="btn btn-sm btn-pai btn-pai-pastel-primary" id="paiStatsRefreshBtn">
                <i class="fas fa-sync-alt mr-1"></i> Actualizar
            </button>
        </div>
    </div>

    <div class="pai-analytics__filters">
        <div class="row">
            <div class="col-md-2 mb-2">
                <label class="small text-muted mb-1">Fecha inicio</label>
                <input type="date" class="form-control form-control-sm" id="statsStartDate">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small text-muted mb-1">Fecha fin</label>
                <input type="date" class="form-control form-control-sm" id="statsEndDate">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small text-muted mb-1">Prestador</label>
                <select class="form-control form-control-sm" id="statsPrestador">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small text-muted mb-1">Municipio</label>
                <select class="form-control form-control-sm" id="statsMunicipio">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small text-muted mb-1">Biológico</label>
                <select class="form-control form-control-sm" id="statsVacuna">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small text-muted mb-1">Régimen</label>
                <select class="form-control form-control-sm" id="statsRegimen">
                    <option value="">Todos</option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <small id="paiStatsMeta" class="text-muted">Sin datos aún.</small>
            <div>
                <button class="btn btn-sm btn-outline-secondary" id="paiStatsResetBtn">Limpiar filtros</button>
                <button class="btn btn-sm btn-primary" id="paiStatsApplyBtn">Aplicar</button>
            </div>
        </div>
    </div>

    <div class="row mt-3 px-3">
        <div class="col-md-2 col-6 mb-2"><div class="pai-stat-box"><div class="label">Vacunas</div><div class="value" id="kpiTotalVacunas">0</div></div></div>
        <div class="col-md-2 col-6 mb-2"><div class="pai-stat-box"><div class="label">Afiliados</div><div class="value" id="kpiTotalAfiliados">0</div></div></div>
        <div class="col-md-2 col-6 mb-2"><div class="pai-stat-box"><div class="label">Prestadores</div><div class="value" id="kpiTotalPrestadores">0</div></div></div>
        <div class="col-md-2 col-6 mb-2"><div class="pai-stat-box"><div class="label">Municipios</div><div class="value" id="kpiTotalMunicipios">0</div></div></div>
        <div class="col-md-2 col-6 mb-2"><div class="pai-stat-box pai-stat-box--ok"><div class="label">Esquema completo</div><div class="value" id="kpiCompleto">0</div></div></div>
        <div class="col-md-2 col-6 mb-2"><div class="pai-stat-box pai-stat-box--bad"><div class="label">Esquema incompleto</div><div class="value" id="kpiIncompleto">0</div></div></div>
    </div>

    <div class="row px-3 pb-3">
        <div class="col-lg-6 mb-3">
            <div class="pai-chart-card">
                <div class="pai-chart-card__title">Vacunas por municipio (Top 12)</div>
                <div class="pai-chart-wrap">
                    <canvas id="paiChartMunicipio"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="pai-chart-card">
                <div class="pai-chart-card__title">Vacunas por prestador (Top 10)</div>
                <div class="pai-chart-wrap">
                    <canvas id="paiChartPrestador"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="pai-chart-card">
                <div class="pai-chart-card__title">Top biológicos aplicados</div>
                <div class="pai-chart-wrap">
                    <canvas id="paiChartBiologico"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="pai-chart-card">
                <div class="pai-chart-card__title">Tendencia mensual de vacunación</div>
                <div class="pai-chart-wrap">
                    <canvas id="paiChartTrend"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
