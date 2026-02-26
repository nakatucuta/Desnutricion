<style>
.pai-export-modal{
    --m-line:var(--pai-line, rgba(15,23,42,.08));
    --m-text:var(--pai-text, #0f172a);
    --m-muted:var(--pai-muted, #64748b);
    --m-brand-1:var(--pai-brand-1, #1d4ed8);
    --m-brand-2:var(--pai-brand-2, #0284c7);
}
.pai-export-modal .modal-dialog{max-width:560px;}
.pai-export-modal .modal-dialog{
    transform:translateY(14px) scale(.98);
    opacity:0;
    transition:transform .26s ease, opacity .26s ease;
}
.pai-export-modal.show .modal-dialog{
    transform:translateY(0) scale(1);
    opacity:1;
}
.pai-export-card{
    border:1px solid var(--m-line);
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 24px 60px rgba(2,6,23,.16);
}
.pai-export-head{
    background:linear-gradient(180deg,#f8fbff,#eef5ff);
    border-bottom:1px solid var(--m-line);
}
.pai-export-title{font-weight:900;color:var(--m-text);}
.pai-export-sub{font-size:.88rem;color:var(--m-muted);margin-top:2px;}
.pai-export-body{background:#fff;padding:16px;}
.pai-export-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
@media (max-width:575px){.pai-export-grid{grid-template-columns:1fr;}}
.pai-export-field label{font-weight:800;font-size:.86rem;color:#334155;}
.pai-export-field .form-control{
    border-radius:10px;
    border-color:rgba(15,23,42,.15);
    box-shadow:none;
}
.pai-export-field .form-control:focus{
    border-color:var(--m-brand-1);
    box-shadow:0 0 0 3px rgba(59,130,246,.18);
}
.pai-export-help{
    margin-top:10px;
    background:#f8fafc;
    border:1px solid var(--m-line);
    border-radius:10px;
    padding:8px 10px;
    font-size:.85rem;
    color:#475569;
}
.pai-export-btn{
    width:100%;
    margin-top:12px;
    border:none;
    border-radius:12px;
    font-weight:900;
    background:linear-gradient(135deg,var(--m-brand-1),var(--m-brand-2));
    box-shadow:0 12px 24px rgba(29,78,216,.24);
}
.pai-export-btn:hover{filter:brightness(1.03);}
.pai-export-btn:disabled{opacity:.75;cursor:not-allowed;}
@media (max-width:575px){
    .pai-export-head{padding:12px 12px;}
    .pai-export-title{font-size:1rem;}
    .pai-export-sub{font-size:.8rem;}
    .pai-export-body{padding:12px;}
    .pai-export-help{font-size:.8rem; padding:7px 9px;}
    .pai-export-btn{margin-top:10px; border-radius:10px; font-size:.9rem;}
    .pai-export-field label{font-size:.8rem;}
}
@media (prefers-reduced-motion: reduce){
    .pai-export-modal .modal-dialog{transition:none;}
}
</style>

<div class="modal fade pai-export-modal" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content pai-export-card">
            <div class="modal-header pai-export-head">
                <div>
                    <h5 class="modal-title pai-export-title" id="exportModalLabel">Exportar Reporte PAI</h5>
                    <div class="pai-export-sub">Selecciona el rango de fechas para generar el archivo CSV.</div>
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
