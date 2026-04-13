<div class="modal fade pai-vac-modal" id="vacunaModal" tabindex="-1" aria-labelledby="vacunaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content pai-vac-card">
            <div class="modal-header flex-column align-items-stretch pai-vac-head">
                <div class="d-flex justify-content-between align-items-start w-100">
                    <div class="pai-vac-brand">
                        <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="pai-vac-logo">
                        <h5 class="modal-title mb-0 pai-vac-title" id="vacunaModalLabel">
                            <em>Vacunas asociadas para:</em><br>
                            <span class="pai-vac-patient" id="nombrePaciente"></span>
                        </h5>
                    </div>
                    <button type="button" class="close ml-2" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="pai-vac-meta">
                    <div class="pai-vac-chip">
                        <i class="fas fa-id-card"></i>
                        <span><span id="tipoIdentificacion"></span> <span id="identificacion"></span></span>
                    </div>
                    <div class="pai-vac-chip">
                        <i class="fas fa-venus-mars"></i>
                        <span id="sexo"></span>
                    </div>
                    <div class="pai-vac-chip">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="fechaNacimiento"></span>
                    </div>
                    <div class="pai-vac-chip">
                        <i class="fas fa-birthday-cake"></i>
                        <span id="edad"></span>
                    </div>
                    <div class="pai-vac-chip">
                        <i class="fas fa-hospital"></i>
                        <span id="ips"></span>
                    </div>
                </div>
            </div>

            <div class="modal-body pai-vac-body">
                <div class="pai-vac-table-wrap table-responsive">
                    <table class="table table-sm pai-vac-table">
                        <thead>
                            <tr>
                                <th>Biologico</th>
                                <th>Dosis</th>
                                <th>Fecha de aplicacion</th>
                                <th>Edad anos</th>
                                <th>Edad meses</th>
                                <th>IPS vacunadora</th>
                                <th>Vacunador</th>
                                <th>Regimen</th>
                            </tr>
                        </thead>
                        <tbody id="vacunaList">
                            {{-- Aqui se agregan las vacunas dinamicamente --}}
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <h6 class="mb-1 mb-md-0">
                        <i class="fas fa-clipboard-check mr-1 text-primary"></i>
                        Estado esquema normativo (Fase 2)
                    </h6>
                    <div>
                        <span class="badge badge-danger mr-1">Faltantes: <span id="mvpMissingCount">0</span></span>
                        <span class="badge badge-success mr-1">Cumplidas: <span id="mvpDoneCount">0</span></span>
                        <span class="badge badge-secondary">No aplica: <span id="mvpNoAplicaCount">0</span></span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Regla</th>
                                <th class="text-center" style="width:120px;">Aplicadas</th>
                                <th class="text-center" style="width:120px;">Requeridas</th>
                                <th class="text-center" style="width:120px;">Faltan</th>
                                <th style="width:180px;">Edad/Criterio</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody id="mvpMissingList">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Cargando estado normativo...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer pai-vac-foot d-flex justify-content-between">
                <a href="#" id="btnVacunasPdf" class="btn btn-outline-primary pai-vac-close" target="_blank" rel="noopener" aria-disabled="true">
                    <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                </a>
                <button type="button" class="btn btn-secondary pai-vac-close" data-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
