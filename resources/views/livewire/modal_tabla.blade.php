<!-- Modal HTML -->
<div class="modal fade" id="vacunaModal" tabindex="-1" aria-labelledby="vacunaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Cambiamos a modal-lg para mayor ancho -->
        <div class="modal-content">
            <div class="modal-header flex-column">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h5 class="modal-title mb-0" id="vacunaModalLabel">
                        <span class="modal-indicator"><em>Vacunas asociadas para:</em></span>
                        <br><strong><span id="nombrePaciente"></span></strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="d-flex justify-content-around flex-wrap w-100 mt-2">
                    <div class="d-flex align-items-center flex-grow-1 justify-content-center">
                        <i class="fas fa-id-card text-gray-custom mr-2"></i>
                        <span><span id="tipoIdentificacion"></span> <span id="identificacion"></span></span>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 justify-content-center">
                        <i class="fas fa-venus-mars text-gray-custom mr-2"></i>
                        <span id="sexo"></span>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 justify-content-center">
                        <i class="fas fa-calendar-alt text-gray-custom mr-2"></i>
                        <span id="fechaNacimiento"></span>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 justify-content-center">
                        <i class="fas fa-birthday-cake text-gray-custom mr-2"></i>
                        <span id="edad"></span>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 justify-content-center">
                        <i class="fas fa-hospital text-gray-custom mr-2"></i>
                        <span id="ips"></span>
                    </div>
                </div>
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
