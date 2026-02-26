<style>
.pai-vac-modal{
    --m-line:var(--pai-line, rgba(15,23,42,.08));
    --m-text:var(--pai-text, #0f172a);
    --m-muted:var(--pai-muted, #64748b);
    --m-brand-1:var(--pai-brand-1, #1d4ed8);
}
.pai-vac-modal .modal-dialog{max-width:1050px;}
.pai-vac-modal .modal-dialog{
    transform:translateY(16px) scale(.985);
    opacity:0;
    transition:transform .28s ease, opacity .28s ease;
}
.pai-vac-modal.show .modal-dialog{
    transform:translateY(0) scale(1);
    opacity:1;
}
.pai-vac-card{
    border:1px solid var(--m-line);
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 30px 80px rgba(2,6,23,.20);
}
.pai-vac-head{
    border-bottom:1px solid var(--m-line);
    background:
        radial-gradient(circle at 15% 10%, rgba(14,165,233,.12), transparent 38%),
        linear-gradient(180deg,#f8fbff,#eff6ff);
}
.pai-vac-title{
    font-size:1.08rem;
    font-weight:900;
    color:var(--m-text);
    line-height:1.25;
}
.pai-vac-title em{
    font-size:.83rem;
    font-style:normal;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:var(--m-muted);
}
.pai-vac-patient{
    display:inline-block;
    margin-top:4px;
    color:#0b3f91;
}
.pai-vac-meta{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:8px;
    margin-top:12px;
}
@media (max-width:991px){.pai-vac-meta{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media (max-width:575px){.pai-vac-meta{grid-template-columns:1fr;}}
.pai-vac-chip{
    display:flex;
    align-items:center;
    gap:8px;
    background:#fff;
    border:1px solid var(--m-line);
    border-radius:12px;
    padding:8px 10px;
    min-height:44px;
    box-shadow:0 8px 18px rgba(2,6,23,.06);
}
.pai-vac-chip i{color:var(--m-brand-1);}
.pai-vac-chip span{font-weight:700;color:#334155;font-size:.9rem;line-height:1.2;}

.pai-vac-body{padding:14px 14px 10px;}
.pai-vac-table-wrap{
    max-height:420px;
    overflow:auto;
    border:1px solid var(--m-line);
    border-radius:14px;
}
.pai-vac-table{margin:0; background:#fff;}
.pai-vac-table thead th{
    position:sticky;
    top:0;
    z-index:2;
    border:0;
    background:linear-gradient(180deg,#eaf2ff,#e2ecfb);
    color:var(--m-text);
    font-weight:900;
    font-size:.78rem;
    text-transform:uppercase;
    letter-spacing:.35px;
    padding:.72rem .55rem;
    white-space:nowrap;
}
.pai-vac-table tbody td{
    vertical-align:middle;
    border-top:1px solid #edf2f7;
    color:#334155;
    font-size:.88rem;
    padding:.62rem .55rem;
}
.pai-vac-table tbody tr:nth-child(even){background:#fafcff;}
.pai-vac-table tbody tr:hover{background:#eef6ff;}
.pai-vac-foot{
    border-top:1px solid var(--m-line);
    background:#f8fafc;
}
.pai-vac-close{
    border-radius:10px;
    font-weight:800;
    min-width:110px;
}
@media (max-width:575px){
    .pai-vac-head{padding:12px 12px 10px;}
    .pai-vac-title{font-size:.96rem;}
    .pai-vac-title em{font-size:.74rem;}
    .pai-vac-meta{gap:6px; margin-top:10px;}
    .pai-vac-chip{
        min-height:38px;
        border-radius:10px;
        padding:6px 8px;
    }
    .pai-vac-chip span{font-size:.82rem;}
    .pai-vac-body{padding:10px 10px 8px;}
    .pai-vac-table-wrap{max-height:360px; border-radius:10px;}
    .pai-vac-table thead th{
        font-size:.69rem;
        padding:.56rem .45rem;
        letter-spacing:.2px;
    }
    .pai-vac-table tbody td{
        font-size:.8rem;
        padding:.5rem .42rem;
    }
    .pai-vac-foot{padding:8px 10px;}
    .pai-vac-close{
        width:100%;
        min-width:0;
        font-size:.88rem;
    }
}
@media (prefers-reduced-motion: reduce){
    .pai-vac-modal .modal-dialog{transition:none;}
}
</style>

<div class="modal fade pai-vac-modal" id="vacunaModal" tabindex="-1" aria-labelledby="vacunaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content pai-vac-card">
            <div class="modal-header flex-column align-items-stretch pai-vac-head">
                <div class="d-flex justify-content-between align-items-start w-100">
                    <h5 class="modal-title mb-0 pai-vac-title" id="vacunaModalLabel">
                        <em>Vacunas asociadas para:</em><br>
                        <span class="pai-vac-patient" id="nombrePaciente"></span>
                    </h5>
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
                            </tr>
                        </thead>
                        <tbody id="vacunaList">
                            {{-- Aqui se agregan las vacunas dinamicamente --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer pai-vac-foot">
                <button type="button" class="btn btn-secondary pai-vac-close" data-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
