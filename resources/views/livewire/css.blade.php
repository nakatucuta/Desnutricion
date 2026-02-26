 <style>
        .spinner-large {
            width: 20rem;
            height: 20rem;
            border-width: 5.0rem;
        }

        .spinner-large {
            width: 10rem;
            height: 10rem;
            border-width: 1rem;
            color: #28a745; /* Verde */
        }

        .modal-content {
            background-color: #ffffff; /* blanco */
            border: 2px solid #28a745; /* Verde */
        }

        .modal-header {
            background-color: #28a745; /* Verde */
            color: #ffffff;
        }

        .modal-indicator {            
            color: #202020;
            font-size: 1rem;            
        }

        .text-gray-custom {
            color: #202020; /* Gris personalizado */
        }


        .drag-drop-area {
            background-color: #ffffff; /* Verde claro */
            border: 2px dashed #28a745; /* Verde */
        }

        .drag-drop-area:hover {
            background-color: #e6f9e6; /* Verde claro más oscuro */
        }

        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }


        /*PARA EL ICONO DE ENVIAR MENSAJE PAROPADEE */

       /* Estilo base del botón más pequeño */
.blinking-button {
    display: inline-block;
    padding: 5px 10px;  /* Reducido el padding */
    font-size: 12px;    /* Tamaño de texto más pequeño */
    font-weight: bold;
    color: white;
    background-color: #28a745; /* Verde base */
    border: none;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.5s ease;
    position: relative;
}

/* Agregar el icono dentro del botón, con un tamaño más pequeño */
.blinking-button i {
    margin-right: 5px;
    font-size: 12px; /* Reducir el tamaño del icono */
}

/* Parpadeo con variaciones de color verde */
@keyframes blinking {
    0% { background-color: #28a745; }  /* Verde base */
    50% { background-color: #34d058; } /* Verde más claro */
    100% { background-color: #28a745; } /* Regresar al verde base */
}

/* Aplicar la animación de parpadeo */
.blinking-button {
    animation: blinking 1.5s infinite;
}

/* Efecto de hover */
.blinking-button:hover {
    background-color: #218838; /* Verde más oscuro al pasar el mouse */
    text-decoration: none;
}

/* Sombra al pasar el mouse */
.blinking-button:hover {
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
}

/* ===== Export Modal (centralizado) ===== */
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
.pai-export-brand{
    display:flex;
    align-items:center;
    gap:10px;
}
.pai-export-logo{
    width:42px;
    height:42px;
    object-fit:contain;
    border-radius:10px;
    background:#fff;
    border:1px solid var(--m-line);
    padding:4px;
    box-shadow:0 8px 20px rgba(2,6,23,.12);
}
.pai-export-title{font-weight:900;color:var(--m-text);}
.pai-export-sub{font-size:.88rem;color:var(--m-muted);margin-top:2px;}
.pai-export-body{background:#fff;padding:16px;}
.pai-export-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
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

/* ===== Vacunas Modal (centralizado) ===== */
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
.pai-vac-brand{
    display:flex;
    align-items:flex-start;
    gap:10px;
}
.pai-vac-logo{
    width:46px;
    height:46px;
    object-fit:contain;
    border-radius:11px;
    background:#fff;
    border:1px solid var(--m-line);
    padding:4px;
    box-shadow:0 10px 22px rgba(2,6,23,.12);
    flex:0 0 auto;
}
.pai-vac-title em{
    font-size:.83rem;
    font-style:normal;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:var(--m-muted);
}
.pai-vac-patient{display:inline-block; margin-top:4px; color:#0b3f91;}
.pai-vac-meta{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:8px;
    margin-top:12px;
}
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
    min-width:140px;
}
#btnVacunasPdf[aria-disabled=\"true\"]{
    pointer-events:none;
    opacity:.55;
}

@media (max-width:991px){.pai-vac-meta{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media (max-width:575px){
    .pai-export-grid{grid-template-columns:1fr;}
    .pai-export-head{padding:12px 12px;}
    .pai-export-logo{width:36px;height:36px;}
    .pai-export-title{font-size:1rem;}
    .pai-export-sub{font-size:.8rem;}
    .pai-export-body{padding:12px;}
    .pai-export-help{font-size:.8rem; padding:7px 9px;}
    .pai-export-btn{margin-top:10px; border-radius:10px; font-size:.9rem;}
    .pai-export-field label{font-size:.8rem;}

    .pai-vac-head{padding:12px 12px 10px;}
    .pai-vac-logo{width:38px;height:38px;border-radius:9px;}
    .pai-vac-title{font-size:.96rem;}
    .pai-vac-title em{font-size:.74rem;}
    .pai-vac-meta{grid-template-columns:1fr; gap:6px; margin-top:10px;}
    .pai-vac-chip{min-height:38px; border-radius:10px; padding:6px 8px;}
    .pai-vac-chip span{font-size:.82rem;}
    .pai-vac-body{padding:10px 10px 8px;}
    .pai-vac-table-wrap{max-height:360px; border-radius:10px;}
    .pai-vac-table thead th{font-size:.69rem; padding:.56rem .45rem; letter-spacing:.2px;}
    .pai-vac-table tbody td{font-size:.8rem; padding:.5rem .42rem;}
    .pai-vac-foot{padding:8px 10px;}
    .pai-vac-close{width:100%; min-width:0; font-size:.88rem;}
}
@media (prefers-reduced-motion: reduce){
    .pai-export-modal .modal-dialog,
    .pai-vac-modal .modal-dialog{transition:none;}
}

    </style>
