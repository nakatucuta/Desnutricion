@extends('adminlte::page')

@section('title', 'Centro de Revision')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">
<style>
    :root {
        --rv-primary: #0f6f7e;
        --rv-secondary: #11939a;
        --rv-accent: #17a85f;
        --rv-border: #d8eaee;
        --rv-text: #163c44;
        --rv-muted: #5a7b82;
    }

    .rv-page { padding: 1.1rem 0 1.5rem; }

    .rv-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        border-radius: 22px;
        color: #fff;
        padding: 1.35rem 1.45rem;
        margin-bottom: 1rem;
        background:
            radial-gradient(circle at 85% 20%, rgba(255,255,255,.22), transparent 36%),
            radial-gradient(circle at 8% 82%, rgba(255,255,255,.12), transparent 30%),
            linear-gradient(130deg, #0f6f7e, #11939a 52%, #17a85f);
        box-shadow: 0 18px 36px rgba(11, 70, 80, .25);
    }

    .rv-brand { display: flex; align-items: center; gap: .95rem; }

    .rv-logo-wrap {
        width: 76px;
        height: 76px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.25);
        flex-shrink: 0;
    }

    .rv-logo { width: 50px; height: auto; object-fit: contain; }

    .rv-eyebrow {
        display: inline-block;
        margin-bottom: .35rem;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .92;
    }

    .rv-title { margin: 0; font-size: 1.68rem; font-weight: 800; line-height: 1.2; }
    .rv-subtitle { margin: .38rem 0 0; max-width: 760px; font-size: .92rem; color: rgba(255,255,255,.92); }

    .rv-alert-btn {
        border: none;
        border-radius: 14px;
        min-height: 46px;
        padding: .6rem 1rem;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, #db3e51, #c42644);
        box-shadow: 0 10px 22px rgba(120, 18, 37, .32);
        position: relative;
    }

    .rv-alert-btn .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        min-width: 24px;
        border-radius: 999px;
        border: 1px solid #fff;
    }

    .rv-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .8rem;
        margin-bottom: .95rem;
    }

    .rv-kpi {
        border: 1px solid var(--rv-border);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff, #f6fcfd);
        padding: .82rem .95rem;
        box-shadow: 0 10px 22px rgba(18, 66, 79, .08);
    }

    .rv-kpi__label { display: block; color: #597c84; font-size: .8rem; font-weight: 700; margin-bottom: .22rem; }
    .rv-kpi__value { display: block; font-size: 1.45rem; font-weight: 800; color: #15424a; line-height: 1.1; }

    .rv-card {
        border: 1px solid var(--rv-border);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 14px 30px rgba(18, 66, 79, .09);
        overflow: hidden;
    }

    .rv-filters {
        display: grid;
        grid-template-columns: 180px 170px 150px 1fr;
        gap: .65rem;
        padding: .85rem 1rem;
        background: #f4fafb;
        border-bottom: 1px solid #e1edf0;
    }

    .rv-select,
    .rv-input {
        width: 100%;
        min-height: 40px;
        border: 1px solid #c8dde2;
        border-radius: 11px;
        padding: .52rem .72rem;
        color: #1b434b;
        background: #fff;
    }

    .rv-select:focus,
    .rv-input:focus {
        outline: none;
        border-color: #139aa0;
        box-shadow: 0 0 0 .15rem rgba(19, 154, 160, .18);
    }

    .rv-table-wrap { padding: .75rem 1rem 1rem; }

    #revisionTable thead th {
        border-top: none;
        border-bottom: 1px solid #e1edf0;
        background: #eff8fa;
        color: #41666e;
        font-size: .77rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
        white-space: nowrap;
    }

    #revisionTable tbody td {
        color: #234b54;
        font-size: .84rem;
        border-color: #e8f0f2;
        vertical-align: middle;
    }

    #revisionTable.table-hover tbody tr:hover { background: #f7fcfd; }

    .dataTables_filter input,
    .dataTables_length select {
        border-radius: 999px !important;
        border: 1px solid #cfe2e6 !important;
        color: #25545d !important;
    }

    .rv-modal .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 71, 80, .24);
    }

    .rv-modal .modal-header {
        color: #fff;
        background: linear-gradient(130deg, #0f6f7e, #129099 60%, #1ba85f);
        border: none;
    }

    .rv-modal__summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .7rem;
        margin-bottom: .8rem;
    }

    .rv-modal__box {
        border: 1px solid #d9eaee;
        border-radius: 12px;
        background: #fff;
        padding: .7rem .78rem;
    }

    .rv-modal__box small { color: #64838b; display: block; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .rv-modal__box strong { color: #15424a; font-size: 1.15rem; }

    .rv-pending-item {
        border: 1px solid #e2edf0;
        border-radius: 12px;
        padding: .65rem .72rem;
        margin-bottom: .55rem;
        background: #fbfeff;
        display: flex;
        justify-content: space-between;
        gap: .7rem;
        align-items: center;
    }

    .rv-pending-item__name { color: #18434c; font-weight: 800; }
    .rv-pending-item__meta { color: #5f7c84; font-size: .82rem; }

    .rv-toast {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 1060;
        min-width: 320px;
        max-width: 420px;
        border-radius: 14px;
        padding: .75rem .85rem;
        color: #fff;
        background: linear-gradient(135deg, #d64252, #c62b45);
        box-shadow: 0 14px 28px rgba(122, 21, 40, .35);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .rv-toast__text strong { display:block; font-size:.92rem; }
    .rv-toast__text small { display:block; color: rgba(255,255,255,.9); }

    .rv-toast__btn {
        border: none;
        border-radius: 10px;
        min-height: 34px;
        padding: .35rem .68rem;
        font-weight: 700;
        color: #b91d39;
        background: #fff;
    }

    @media (max-width: 1100px) {
        .rv-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rv-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
        .rv-hero { flex-direction: column; }
        .rv-kpis { grid-template-columns: 1fr; }
        .rv-filters { grid-template-columns: 1fr; }
        .rv-modal__summary { grid-template-columns: 1fr; }
        .rv-toast { left: 12px; right: 12px; min-width: 0; }
    }
</style>
@stop

@section('content')
<div class="container-fluid rv-page">
    <section class="rv-hero">
        <div class="rv-brand">
            <div class="rv-logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="rv-logo">
            </div>
            <div>
                <span class="rv-eyebrow">Control inteligente de calidad</span>
                <h1 class="rv-title">Centro de Revision 113 + 412</h1>
                <p class="rv-subtitle">Revisa casos pendientes con DataTables en tiempo real y alertas activas de lo que falta por auditar.</p>
            </div>
        </div>

        <button type="button" class="rv-alert-btn" data-toggle="modal" data-target="#modalPendientesRevision" title="Pendientes por revisar">
            <i class="fas fa-bell mr-1"></i> Pendientes
            <span class="badge badge-light">{{ $totalPending }}</span>
        </button>
    </section>

    <section class="rv-kpis">
        <article class="rv-kpi"><span class="rv-kpi__label">Pendientes 113</span><span class="rv-kpi__value">{{ $pending113 }}</span></article>
        <article class="rv-kpi"><span class="rv-kpi__label">Pendientes 412</span><span class="rv-kpi__value">{{ $pending412 }}</span></article>
        <article class="rv-kpi"><span class="rv-kpi__label">Total pendientes</span><span class="rv-kpi__value">{{ $totalPending }}</span></article>
        <article class="rv-kpi"><span class="rv-kpi__label">Ano de trabajo</span><span class="rv-kpi__value">{{ $year }}</span></article>
    </section>

    <section class="rv-card">
        <div class="rv-filters">
            <select id="filtroModulo" class="rv-select">
                <option value="all" {{ $modulo === 'all' ? 'selected' : '' }}>Todos los modulos</option>
                <option value="113" {{ $modulo === '113' ? 'selected' : '' }}>Solo 113</option>
                <option value="412" {{ $modulo === '412' ? 'selected' : '' }} {{ !$hasRevision412Table ? 'disabled' : '' }}>Solo 412</option>
            </select>

            <select id="filtroStatus" class="rv-select">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Todos</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pendientes</option>
                <option value="done" {{ $status === 'done' ? 'selected' : '' }}>Revisados</option>
            </select>

            <input type="number" id="filtroAnio" class="rv-input" value="{{ $year }}" min="2020" max="2100">

            <input type="text" id="filtroBusqueda" class="rv-input" placeholder="Buscar paciente, documento, clasificacion...">
        </div>

        <div class="rv-table-wrap">
            <table id="revisionTable" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>Modulo</th>
                        <th>ID Seg.</th>
                        <th>Documento</th>
                        <th>Paciente</th>
                        <th>Responsable</th>
                        <th>Fecha consulta</th>
                        <th>Clasificacion</th>
                        <th>Estado</th>
                        <th>Revision</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<div class="modal fade rv-modal" id="modalPendientesRevision" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bell mr-1"></i> Centro de pendientes por notificar y revisar</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="rv-modal__summary">
                    <div class="rv-modal__box"><small>Pendientes 113</small><strong>{{ $pending113 }}</strong></div>
                    <div class="rv-modal__box"><small>Pendientes 412</small><strong>{{ $pending412 }}</strong></div>
                    <div class="rv-modal__box"><small>Total</small><strong>{{ $totalPending }}</strong></div>
                </div>

                @if(!$hasRevision412Table)
                    <div class="alert alert-warning">
                        Para habilitar revision formal de seguimientos 412 ejecuta: <code>php artisan migrate</code>.
                    </div>
                @endif

                @forelse($recentPending as $item)
                    <div class="rv-pending-item">
                        <div>
                            <div class="rv-pending-item__name">{{ trim($item->nombre_paciente) }}</div>
                            <div class="rv-pending-item__meta">{{ $item->modulo }} | Doc: {{ $item->documento }} | Seg: {{ $item->seguimiento_id }}</div>
                        </div>
                        <a href="{{ route('detalle_revisiones', [$item->paciente_id]) }}?modulo={{ $item->modulo }}&seguimiento={{ $item->seguimiento_id }}" class="btn btn-sm btn-outline-primary">Revisar</a>
                    </div>
                @empty
                    <div class="alert alert-success mb-0">No hay pendientes para el ano filtrado.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if($totalPending > 0)
<div class="rv-toast" id="rvToastPending">
    <div class="rv-toast__text">
        <strong>Tienes {{ $totalPending }} caso(s) pendientes</strong>
        <small>Hay seguimientos por notificar y revisar en modulos 113/412.</small>
    </div>
    <button type="button" class="rv-toast__btn" data-toggle="modal" data-target="#modalPendientesRevision">Ver</button>
</div>
@endif
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
$(function () {
    const table = $('#revisionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('revision.data') }}',
            data: function (d) {
                d.anio = $('#filtroAnio').val();
                d.modulo = $('#filtroModulo').val();
                d.status = $('#filtroStatus').val();
            }
        },
        columns: [
            { data: 'modulo_badge', name: 'modulo', orderable: false, searchable: false },
            { data: 'registro_id', name: 'registro_id' },
            { data: 'documento', name: 'documento' },
            { data: 'nombre_paciente', name: 'nombre_paciente' },
            { data: 'responsable', name: 'responsable' },
            { data: 'fecha_consulta', name: 'fecha_consulta' },
            { data: 'clasificacion', name: 'clasificacion' },
            { data: 'estado_badge', name: 'estado', orderable: false, searchable: false },
            { data: 'revision_badge', name: 'revisado_flag', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        language: {
            processing: 'Procesando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros en total)',
            loadingRecords: 'Cargando registros...',
            zeroRecords: 'No se encontraron resultados',
            emptyTable: 'No hay datos disponibles en esta tabla',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Ultimo'
            }
        }
    });

    $('#filtroModulo, #filtroStatus, #filtroAnio').on('change', function () {
        table.ajax.reload();
    });

    $('#filtroBusqueda').on('keyup change', function () {
        table.search(this.value).draw();
    });

    const toast = document.getElementById('rvToastPending');
    if (toast) {
        setTimeout(function () {
            if (typeof $ !== 'undefined') {
                $('#modalPendientesRevision').modal('show');
            }
        }, 700);

        setTimeout(function () {
            toast.style.display = 'none';
        }, 14000);
    }
});
</script>
@stop
