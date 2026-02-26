<div class="modal fade pai-export-modal" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content pai-export-card">
            <div class="modal-header pai-export-head">
                <div class="pai-export-brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="pai-export-logo">
                    <div>
                    <h5 class="modal-title pai-export-title" id="exportModalLabel">Reportes PAI por Rango</h5>
                    <div class="pai-export-sub">Elige un rango de fechas y decide si exportas Excel o visualizas informe dinamico.</div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pai-export-body">
                <form id="exportForm">
                    <div class="pai-export-grid">
                        <div class="form-group pai-export-field mb-0">
                            <label for="start_date">Fecha de inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group pai-export-field mb-0">
                            <label for="end_date">Fecha final</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="pai-export-help">
                        <i class="fas fa-info-circle mr-1"></i>
                        El informe dinamico usa fecha de carga del registro (created_at) para trazabilidad por usuario.
                    </div>

                    <div class="pai-export-actions">
                        <button id="exportButton" class="btn btn-success pai-export-btn" type="button">
                            <span id="button-text"><i class="fas fa-file-export mr-2"></i>EXPORTAR EXCEL</span>
                            <span id="loading-icon" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                            <span id="sending-text" style="display: none;"> Generando archivo...</span>
                        </button>

                        <button id="viewLoadReportButton" class="btn btn-outline-primary pai-export-btn pai-export-btn-alt" type="button">
                            <span id="view-report-text"><i class="fas fa-chart-line mr-2"></i>VER INFORME DINAMICO</span>
                            <span id="view-report-loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                            <span id="view-report-sending" style="display: none;"> Consultando datos...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade pai-load-report-modal" id="loadReportModal" tabindex="-1" role="dialog" aria-labelledby="loadReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content pai-load-report-card">
            <div class="modal-header pai-load-report-head">
                <div class="pai-load-report-brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="pai-load-report-logo">
                    <div>
                        <h5 class="modal-title pai-load-report-title" id="loadReportModalLabel">Informe Dinamico de Cargue PAI</h5>
                        <div class="pai-load-report-sub" id="loadReportRangeText">Rango: --</div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pai-load-report-body">
                <div class="pai-load-filterbar">
                    <div class="form-group mb-0">
                        <label for="loadFilterUser" class="mb-1">Usuario</label>
                        <select id="loadFilterUser" class="form-control form-control-sm">
                            <option value="">Todos los usuarios</option>
                        </select>
                    </div>
                    <div class="form-group mb-0 d-flex align-items-end">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="loadFilterOnlyWithout">
                            <label class="custom-control-label" for="loadFilterOnlyWithout">Solo usuarios sin cargue</label>
                        </div>
                    </div>
                    <div class="d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="loadFilterReset">
                            <i class="fas fa-sync-alt mr-1"></i> Limpiar filtros
                        </button>
                    </div>
                </div>

                <div class="pai-load-kpis">
                    <div class="pai-load-kpi">
                        <div class="pai-load-kpi__label">Usuarios</div>
                        <div class="pai-load-kpi__value" id="loadUsersTotal">0</div>
                    </div>
                    <div class="pai-load-kpi pai-load-kpi--ok">
                        <div class="pai-load-kpi__label">Con Cargue</div>
                        <div class="pai-load-kpi__value" id="loadUsersWith">0</div>
                    </div>
                    <div class="pai-load-kpi pai-load-kpi--warn">
                        <div class="pai-load-kpi__label">Sin Cargue</div>
                        <div class="pai-load-kpi__value" id="loadUsersWithout">0</div>
                    </div>
                    <div class="pai-load-kpi pai-load-kpi--info">
                        <div class="pai-load-kpi__label">Vacunas Cargadas</div>
                        <div class="pai-load-kpi__value" id="loadVacunasTotal">0</div>
                    </div>
                    <div class="pai-load-kpi pai-load-kpi--soft">
                        <div class="pai-load-kpi__label">Afiliados Impactados</div>
                        <div class="pai-load-kpi__value" id="loadAfiliadosTotal">0</div>
                    </div>
                </div>

                <div class="pai-load-table-wrap">
                    <table class="table table-sm table-hover pai-load-table mb-0">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th class="text-center">Vacunas</th>
                                <th class="text-center">Afiliados</th>
                                <th class="text-center">Lotes</th>
                                <th>Ultimo Cargue</th>
                                <th class="text-center">Solicitudes</th>
                                <th>Afiliados Cargados (Muestra)</th>
                            </tr>
                        </thead>
                        <tbody id="loadReportBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">Sin datos para mostrar.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer pai-load-report-foot d-flex justify-content-between">
                <span class="text-muted small">Informe dinamico para control de productividad y trazabilidad.</span>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <a href="#" class="btn btn-outline-danger" id="downloadLoadReportPdfButton" target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
