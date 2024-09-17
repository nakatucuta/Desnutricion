<!-- Modal HTML -->
<div class="modal fade" id="vacunaModal" tabindex="-1" aria-labelledby="vacunaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vacunaModalLabel">Vacunas Asociadas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Tabla de vacunas -->
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Vacuna</th>
                            <th>Docis</th>
                            <th>Fecha de Vacunación</th>
                        </tr>
                    </thead>
                    <tbody id="vacunaList">
                        {{-- Aquí se agregan las vacunas dinámicamente --}}
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
