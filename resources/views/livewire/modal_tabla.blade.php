<!-- Modal HTML -->
<div class="modal fade" id="vacunaModal" tabindex="-1" aria-labelledby="vacunaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Cambiamos a modal-lg para mayor ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vacunaModalLabel">Vacunas Asociadas para: <strong><span id="nombrePaciente"></span></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Tabla de vacunas -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;"> <!-- Contenedor para agregar desplazamiento vertical -->
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Biologico</th>
                                <th>Dosis</th>
                                <th>Fecha de aplicacion</th>
                                <th>Edad años</th>
                                <th>Edad Meses</th>
                                <th>IPS Vacunadora</th>
                                <th>Vacunador</th>
                            </tr>
                        </thead>
                        <tbody id="vacunaList">
                            {{-- Aquí se agregan las vacunas dinámicamente --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
