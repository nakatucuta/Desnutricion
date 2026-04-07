<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">
<style>
    .seg412-hero{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1.25rem;
        margin-bottom:1.2rem;
        padding:1.3rem 1.4rem;
        border-radius:24px;
        background:
            radial-gradient(circle at top right, rgba(66, 214, 151, .2), transparent 35%),
            radial-gradient(circle at left center, rgba(27, 154, 170, .18), transparent 30%),
            linear-gradient(135deg, #0f5560, #127b88 52%, #17a36b);
        box-shadow:0 18px 34px rgba(16, 78, 89, .2);
        color:#fff;
    }
    .seg412-hero__brand{
        display:flex;
        align-items:center;
        gap:1rem;
    }
    .seg412-hero__logo-wrap{
        width:74px;
        height:74px;
        border-radius:20px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:rgba(255,255,255,.15);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.28);
        flex-shrink:0;
    }
    .seg412-hero__logo{
        width:48px;
        height:auto;
    }
    .seg412-eyebrow{
        display:inline-block;
        margin-bottom:.4rem;
        font-size:.78rem;
        font-weight:800;
        letter-spacing:.08em;
        text-transform:uppercase;
        opacity:.8;
    }
    .seg412-hero__title{
        margin:0;
        font-size:1.85rem;
        font-weight:800;
    }
    .seg412-hero__subtitle{
        margin:.35rem 0 0;
        font-size:.96rem;
        line-height:1.5;
        color:rgba(255,255,255,.88);
        max-width:760px;
    }
    .seg412-hero__chips{
        display:flex;
        gap:.55rem;
        flex-wrap:wrap;
        justify-content:flex-end;
    }
    .seg412-chip{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:.45rem .8rem;
        border-radius:999px;
        border:1px solid rgba(255,255,255,.18);
        background:rgba(255,255,255,.14);
        font-size:.76rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        white-space:nowrap;
    }
    .seg412-chip--ok{
        color:#0d7a63;
        background:#dff9ee;
        border-color:#dff9ee;
    }
    .seg412-toolbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:1.1rem;
        flex-wrap:wrap;
    }
    .seg412-btn{
        border:none;
        border-radius:14px;
        min-height:46px;
        padding:.72rem 1.2rem;
        font-size:.9rem;
        font-weight:700;
        letter-spacing:.02em;
    }
    .seg412-btn--primary{
        background:linear-gradient(135deg, #0f7c8a, #1e9d5f);
        color:#fff;
        box-shadow:0 12px 22px rgba(18, 122, 111, .2);
    }
    .seg412-btn--primary:hover{
        color:#fff;
        filter:brightness(.98);
    }
    .seg412-btn--success{
        background:linear-gradient(135deg, #1c8f63, #1ca56f);
        color:#fff;
        box-shadow:0 10px 20px rgba(28, 143, 99, .24);
    }
    .seg412-btn--success:hover{
        color:#fff;
        filter:brightness(.98);
    }
    .export-dropdown .dropdown-menu{
        border:none;
        border-radius:12px;
        box-shadow:0 14px 28px rgba(18, 58, 70, .14);
    }
    .seg412-kpi-grid{
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:1rem;
    }
    .seg412-kpi-card{
        display:flex;
        align-items:center;
        gap:.95rem;
        border-radius:20px;
        padding:1.05rem 1.1rem;
        border:1px solid #deeaee;
        background:linear-gradient(180deg, #ffffff, #f4fbfd);
        box-shadow:0 10px 22px rgba(16, 65, 79, .06);
        cursor:pointer;
        transition:all .2s ease-in-out;
    }
    .seg412-kpi-card:hover{
        transform:translateY(-2px);
        box-shadow:0 16px 28px rgba(16, 65, 79, .12);
    }
    .seg412-kpi-card__icon{
        width:52px;
        height:52px;
        border-radius:14px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:1.3rem;
        flex-shrink:0;
    }
    .seg412-kpi-card__label{
        display:block;
        font-size:.9rem;
        color:#617f89;
        font-weight:700;
    }
    .seg412-kpi-card__value{
        display:block;
        font-size:1.8rem;
        font-weight:800;
        line-height:1.1;
        color:#1a3f49;
    }
    .stat-abiertos .seg412-kpi-card__icon{
        background:linear-gradient(135deg, #178cb1, #0f6f92);
    }
    .stat-proximos .seg412-kpi-card__icon{
        background:linear-gradient(135deg, #1aa36e, #1c8f63);
    }
    .stat-cerrados .seg412-kpi-card__icon{
        background:linear-gradient(135deg, #d94b5e, #bc3548);
    }
    .selected-callout{
        border-color:#22a3ba !important;
        box-shadow:0 0 0 3px rgba(34,163,186,.17), 0 14px 26px rgba(17,87,107,.12) !important;
        background:linear-gradient(180deg, #f4fdff, #ebf9fd) !important;
    }
    .seg412-table-card{
        border-radius:22px;
        background:#fff;
        border:1px solid #dceaf0;
        box-shadow:0 16px 30px rgba(17, 74, 87, .08);
        padding:1.15rem;
    }
    .seg412-table-card__head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:.9rem;
        flex-wrap:wrap;
    }
    .seg412-table-card__title{
        margin:.2rem 0 0;
        font-size:1.3rem;
        font-weight:800;
        color:#173f49;
    }
    .filtro-anio-wrapper{
        background:#f5fbfd;
        border:1px solid #dceaf0;
        padding:.5rem .75rem;
        border-radius:999px;
        box-shadow:0 4px 12px rgba(21, 96, 116, .08);
    }
    .filtro-label{
        font-weight:700;
        color:#335964;
        margin-right:.45rem;
    }
    .filtro-select{
        border:none;
        background:#fff;
        border-radius:999px;
        padding:.35rem .8rem;
        min-width:105px;
        box-shadow:inset 0 1px 2px rgba(0,0,0,.06);
    }
    .filtro-select:focus{
        outline:none;
        box-shadow:0 0 0 .2rem rgba(27,154,170,.14);
    }
    .seg412-table-wrap{
        border-radius:18px;
        overflow:hidden;
        border:1px solid #e0edf1;
    }
    #seguimiento412{
        margin-bottom:0 !important;
    }
    #seguimiento412 thead th{
        border-top:none;
        border-bottom:1px solid #deeaee;
        background:#eff8fa;
        color:#45626b;
        font-size:.79rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.04em;
    }
    #seguimiento412.table-hover tbody tr:hover{
        background:#f3fbfd;
    }
    .dataTables_filter input{
        border-radius:999px !important;
        border:1px solid #d0e2e8 !important;
        padding:5px 12px !important;
    }
    .btn-acciones{
        background:linear-gradient(135deg, #0f7c8a, #0f6f92);
        border:none;
        color:#fff;
        border-radius:999px;
        font-weight:700;
        padding:.42rem .85rem;
        box-shadow:0 7px 14px rgba(15, 111, 146, .24);
    }
    .btn-acciones:hover{
        color:#fff;
        filter:brightness(.98);
    }
    #overlay-spinner{
        display:none;
        position:fixed;
        z-index:9999;
        background:rgba(255, 255, 255, .84);
        top:0;
        left:0;
        width:100%;
        height:100%;
        backdrop-filter:blur(2px);
    }
    .spinner-container{
        position:absolute;
        top:50%;
        left:50%;
        transform:translate(-50%, -50%);
        text-align:center;
    }
    .spinner-border{
        width:3.3rem;
        height:3.3rem;
    }
    @media (max-width: 991px){
        .seg412-hero{
            flex-direction:column;
        }
        .seg412-kpi-grid{
            grid-template-columns:1fr;
        }
    }
    @media (max-width: 767px){
        .seg412-hero__brand{
            align-items:flex-start;
        }
        .seg412-hero__title{
            font-size:1.45rem;
        }
    }
</style>
