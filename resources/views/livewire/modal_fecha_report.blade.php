<!-- Modal para seleccionar fechas -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Exportar a Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para seleccionar las fechas -->
                <form id="exportForm">
                    <div class="form-group">
                        <label for="start_date">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Fecha Final</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <button id="exportButton" class="btn btn-success" type="button">
                        <span id="button-text">EXPORTAR</span>
                        <span id="loading-icon" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        <span id="sending-text" style="display: none;"> Generando Reporte...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
