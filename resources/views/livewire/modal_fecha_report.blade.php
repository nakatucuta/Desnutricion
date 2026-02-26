<div class="modal fade pai-export-modal" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content pai-export-card">
            <div class="modal-header pai-export-head">
                <div class="pai-export-brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="pai-export-logo">
                    <div>
                    <h5 class="modal-title pai-export-title" id="exportModalLabel">Exportar Reporte PAI</h5>
                    <div class="pai-export-sub">Selecciona el rango de fechas para generar el archivo CSV.</div>
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
                        El reporte puede tardar unos segundos dependiendo del volumen de datos.
                    </div>

                    <button id="exportButton" class="btn btn-success pai-export-btn" type="button">
                        <span id="button-text"><i class="fas fa-file-export mr-2"></i>EXPORTAR</span>
                        <span id="loading-icon" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        <span id="sending-text" style="display: none;"> Generando reporte...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
