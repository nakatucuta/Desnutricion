@extends('adminlte::page')

@section('title', 'Gestion de Vacunas')

@section('content_header')
<div class="vm-topbar">
    <div class="vm-brand">
        <div class="vm-brand__logo">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa">
        </div>
        <div>
            <div class="vm-brand__title">Gestion Dinamica de Vacunas</div>
            <div class="vm-brand__subtitle">Elimina una o varias vacunas por afiliado de forma segura</div>
        </div>
    </div>
    <div>
        <a href="{{ route('afiliado') }}" class="btn vm-btn vm-btn-light">
            <i class="fas fa-arrow-left mr-1"></i> Volver al cargue
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="vm-card">
                <div class="vm-card__head">
                    <i class="fas fa-info-circle mr-2"></i> Como eliminar vacunas
                </div>
                <div class="vm-guide">
                    <div class="vm-guide__item">
                        <span class="vm-guide__step">1</span>
                        <div>Busca por <b>nombre</b>, <b>numero de identificacion</b> o <b>carnet</b>.</div>
                    </div>
                    <div class="vm-guide__item">
                        <span class="vm-guide__step">2</span>
                        <div>Para eliminar una vacuna, usa el boton <b>Eliminar</b> de la fila.</div>
                    </div>
                    <div class="vm-guide__item">
                        <span class="vm-guide__step">3</span>
                        <div>Para eliminar varias, marca las casillas y usa <b>Eliminar seleccionadas</b>.</div>
                    </div>
                    <div class="vm-guide__item">
                        <span class="vm-guide__step">4</span>
                        <div>Siempre se pedira confirmacion antes de borrar.</div>
                    </div>
                </div>
                <div class="vm-note">
                    <i class="fas fa-shield-alt mr-1"></i> Modo seguro admin activo
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-3">
            <div class="vm-card">
                <div class="vm-toolbar">
                    <div class="vm-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="vmSearch" placeholder="Buscar por nombre, identificacion, carnet o vacuna">
                    </div>
                    <div class="vm-actions">
                        <span class="vm-counter" id="vmCounter">0 seleccionadas</span>
                        <button type="button" id="vmDeleteSelected" class="btn vm-btn vm-btn-danger" disabled>
                            <i class="fas fa-trash-alt mr-1"></i> Eliminar seleccionadas
                        </button>
                    </div>
                </div>
                <div class="vm-subbar">
                    <span><i class="fas fa-filter mr-1"></i> Busqueda por documento, nombre o carnet</span>
                    <span><i class="fas fa-hand-pointer mr-1"></i> Puedes eliminar 1 o varias vacunas</span>
                </div>

                <div class="table-responsive">
                    <table class="table vm-table" id="vmTable">
                        <thead>
                            <tr>
                                <th style="width:48px;">
                                    <label class="vm-check-wrap m-0">
                                        <input type="checkbox" id="vmCheckAll">
                                        <span></span>
                                    </label>
                                </th>
                                <th>Afiliado</th>
                                <th>Vacuna</th>
                                <th>Fecha</th>
                                <th>Detalle</th>
                                <th>Cargado por</th>
                                <th style="width:140px;" class="text-right">Accion</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="vm-card">
                <div class="vm-card__head d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-clipboard-check mr-2"></i> Auditoria de eliminaciones</span>
                    <span class="vm-counter">{{ count($audits ?? []) }} registros recientes</span>
                </div>
                <div class="table-responsive">
                    <table class="table vm-audit-table mb-0">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Usuario</th>
                                <th>Vacuna eliminada</th>
                                <th>Afiliado</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse(($audits ?? []) as $a)
                            <tr>
                                <td>{{ $a->deleted_at ? \Carbon\Carbon::parse($a->deleted_at)->format('Y-m-d H:i:s') : '---' }}</td>
                                <td>{{ $a->user_name ?? 'Sin usuario' }}</td>
                                <td><span class="vm-pill vm-pill-vacuna">{{ $a->vacuna_nombre ?? ('Vacuna #'.$a->vacuna_id) }}</span></td>
                                <td>{{ $a->afiliado_nombre ?? '---' }}</td>
                                <td>{{ $a->afiliado_documento ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Aun no hay registros de auditoria.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<style>
.content-wrapper{
    background:
      radial-gradient(circle at 8% 6%, rgba(14,165,233,.10), transparent 30%),
      radial-gradient(circle at 95% 0%, rgba(37,99,235,.08), transparent 28%),
      linear-gradient(180deg,#f7fbff 0%, #f2f7fc 100%);
}
.vm-topbar{
    display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;
    padding:8px 4px 4px;
}
.vm-brand{display:flex;align-items:center;gap:12px}
.vm-brand__logo{
    width:58px;height:58px;border-radius:16px;background:#fff;border:1px solid rgba(0,0,0,.07);
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 14px 28px rgba(15,23,42,.15), 0 0 0 5px rgba(255,255,255,.55);
}
.vm-brand__logo img{width:44px;height:44px;object-fit:contain}
.vm-brand__title{
    font-size:1.25rem;font-weight:900;color:#0f172a;letter-spacing:.1px;
}
.vm-brand__subtitle{font-size:.92rem;color:#64748b}

.vm-card{
    background:#fff;border:1px solid rgba(15,23,42,.09);border-radius:18px;
    box-shadow:0 14px 34px rgba(2,6,23,.08);overflow:hidden;
}
.vm-card__head{
    padding:14px 16px;font-weight:900;border-bottom:1px solid rgba(15,23,42,.08);
    background:linear-gradient(180deg,#eef5ff,#fff);color:#0f172a;
}
.vm-guide{padding:14px 16px}
.vm-guide__item{display:flex;gap:10px;align-items:flex-start;margin-bottom:12px;color:#334155}
.vm-guide__item:last-child{margin-bottom:0}
.vm-guide__step{
    width:26px;height:26px;min-width:26px;border-radius:999px;
    background:#dbeafe;color:#1d4ed8;display:inline-flex;align-items:center;justify-content:center;font-weight:900;
    box-shadow: inset 0 0 0 1px rgba(37,99,235,.18);
}
.vm-note{
    margin:0 14px 14px; padding:10px 12px; border-radius:12px;
    background:#ecfdf3; color:#166534; border:1px solid #bbf7d0; font-weight:700; font-size:.86rem;
}

.vm-toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 14px;border-bottom:1px solid rgba(15,23,42,.08);background:#fbfdff}
.vm-search{position:relative;min-width:320px;flex:1}
.vm-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#64748b}
.vm-search input{width:100%;padding:11px 12px 11px 36px;border:1px solid rgba(15,23,42,.14);border-radius:11px;outline:none;background:#fff}
.vm-search input:focus{border-color:#93c5fd;box-shadow:0 0 0 4px rgba(147,197,253,.22)}
.vm-actions{display:flex;align-items:center;gap:10px}
.vm-counter{padding:8px 10px;border-radius:999px;background:#f1f5f9;color:#334155;font-weight:800;font-size:.86rem;border:1px solid #e2e8f0}
.vm-subbar{
    padding:9px 14px; font-size:.84rem; color:#475569;
    display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;
    background:linear-gradient(180deg,#ffffff,#f8fbff);
    border-bottom:1px solid rgba(15,23,42,.08);
}

.vm-btn{border-radius:11px;font-weight:800;border:1px solid transparent;padding:9px 12px;transition:all .16s ease}
.vm-btn:hover{transform:translateY(-1px)}
.vm-btn-light{background:#f8fafc;border-color:#cbd5e1;color:#0f172a}
.vm-btn-light:hover{background:#f1f5f9}
.vm-btn-danger{background:#fff1f2;border-color:#fecdd3;color:#9f1239}
.vm-btn-danger:hover{background:#ffe4e6;box-shadow:0 10px 22px rgba(190,24,93,.15)}
.vm-delete-one{min-width:112px;background:linear-gradient(180deg,#fff1f2,#ffe9ee)!important}

.vm-table{margin:0!important;border-collapse:separate!important;border-spacing:0 10px!important}
.vm-table thead th{
    background:linear-gradient(180deg,#e7f1ff,#fafdff);color:#0f172a;font-weight:900;
    border-bottom:1px solid rgba(2,6,23,.12);position:sticky;top:0;z-index:2;
}
.vm-table tbody td{
    vertical-align:middle;color:#0f172a;background:#fff;
    border-top:1px solid #e5edf8;border-bottom:1px solid #e5edf8;
}
.vm-table tbody td:first-child{
    border-left:1px solid #e5edf8;border-top-left-radius:12px;border-bottom-left-radius:12px;
}
.vm-table tbody td:last-child{
    border-right:1px solid #e5edf8;border-top-right-radius:12px;border-bottom-right-radius:12px;
}
.vm-table tbody tr{transition:all .16s ease}
.vm-table tbody tr:hover{transform:translateY(-1px)}
.vm-table tbody tr:hover td{background:linear-gradient(180deg,#f8fbff,#f4f9ff);border-color:#dbe8fa}
.vm-afiliado__name{font-weight:800}
.vm-afiliado__doc,.vm-muted{font-size:.84rem;color:#64748b}
.vm-stack{display:flex;flex-direction:column;gap:6px}
.vm-pill{
    display:inline-flex;align-items:center;max-width:max-content;border-radius:999px;
    padding:5px 10px;font-size:.78rem;font-weight:800;border:1px solid transparent;
}
.vm-pill-vacuna{background:#eaf2ff;border-color:#cfe0ff;color:#1e40af}
.vm-pill-dose{background:#f1f5f9;border-color:#dbe5ef;color:#334155}
.vm-chip{
    display:inline-flex;align-items:center;max-width:max-content;border-radius:8px;
    padding:4px 8px;font-size:.76rem;font-weight:700;
}
.vm-chip-lote{background:#fff7e6;border:1px solid #fde4b3;color:#9a4f07}
.vm-chip-regimen{background:#ecfdf3;border:1px solid #bbf7d0;color:#166534}
.vm-user{display:flex;flex-direction:column;gap:3px}
.vm-user__name{font-weight:800;color:#0f172a}
.vm-user__time{font-size:.76rem;color:#64748b}

.vm-check-wrap{position:relative;display:inline-flex;align-items:center}
.vm-check-wrap input{position:absolute;opacity:0}
.vm-check-wrap span{width:18px;height:18px;border-radius:5px;border:2px solid #94a3b8;display:inline-block;position:relative;transition:all .14s ease}
.vm-check-wrap input:checked + span{border-color:#2563eb;background:#2563eb}
.vm-check-wrap input:checked + span:after{content:"";position:absolute;left:4px;top:0px;width:6px;height:11px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg)}

.dataTables_wrapper .dataTables_processing{border-radius:10px!important;padding:8px 12px!important}
.dataTables_wrapper .dataTables_paginate .paginate_button{border-radius:8px!important}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
    background:#dbeafe!important;border-color:#bfdbfe!important;color:#1d4ed8!important;
}
.dataTables_wrapper .dataTables_info{font-weight:700;color:#475569}
.vm-audit-table thead th{
    background:linear-gradient(180deg,#f8fbff,#f2f8ff);
    color:#0f172a;
    font-weight:900;
    border-bottom:1px solid #dbe8f9;
    font-size:.85rem;
}
.vm-audit-table td{
    vertical-align:middle;
    border-bottom:1px solid #edf2f9;
    color:#334155;
    font-size:.86rem;
}

@media(max-width:992px){.vm-search{min-width:100%}}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
(function(){
    const deleteUrl = @json(route('vaccine.manager.delete'));
    const table = $('#vmTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        ajax: {
            url: @json(route('vaccine.manager.data')),
            type: 'GET'
        },
        order: [[3, 'desc']],
        columns: [
            {data: 'select', orderable: false, searchable: false},
            {data: 'afiliado', orderable: false},
            {data: 'vacuna', orderable: false},
            {data: 'fecha_vacuna'},
            {data: 'detalle', orderable: false, searchable: false},
            {data: 'cargado_por', orderable: false, searchable: false},
            {data: 'acciones', orderable: false, searchable: false, className: 'text-right'}
        ],
        language: {
            processing: '<span><i class="fas fa-circle-notch fa-spin mr-1"></i> Cargando...</span>',
            emptyTable: 'No hay vacunas registradas.',
            zeroRecords: 'No se encontraron coincidencias.',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            paginate: {first:'Primero', last:'Ultimo', next:'Siguiente', previous:'Anterior'}
        }
    });

    const selected = new Set();

    function refreshSelectedUI(){
        const count = selected.size;
        $('#vmCounter').text(count + ' seleccionadas');
        $('#vmDeleteSelected').prop('disabled', count === 0);
    }

    function syncChecks(){
        $('.vm-row-check').each(function(){
            const id = String($(this).val());
            $(this).prop('checked', selected.has(id));
        });
        const visible = $('.vm-row-check').length;
        const checked = $('.vm-row-check:checked').length;
        $('#vmCheckAll').prop('checked', visible > 0 && visible === checked);
    }

    $('#vmSearch').on('keyup', function(){
        table.search($(this).val()).draw();
    });

    $('#vmTable').on('change', '.vm-row-check', function(){
        const id = String($(this).val());
        if ($(this).is(':checked')) selected.add(id); else selected.delete(id);
        refreshSelectedUI();
        syncChecks();
    });

    $('#vmCheckAll').on('change', function(){
        const on = $(this).is(':checked');
        $('.vm-row-check').each(function(){
            const id = String($(this).val());
            $(this).prop('checked', on);
            if (on) selected.add(id); else selected.delete(id);
        });
        refreshSelectedUI();
    });

    table.on('draw', function(){
        syncChecks();
        refreshSelectedUI();
    });

    function notify(type, title, text){
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon: type, title: title, text: text });
            return;
        }
        alert((title ? title + ': ' : '') + (text || ''));
    }

    function confirmDelete(ids, onConfirm){
        const text = 'Vas a eliminar ' + ids.length + ' vacuna(s). Esta accion no se puede deshacer.';

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: 'warning',
                title: 'Confirmar eliminacion',
                html: 'Vas a eliminar <b>' + ids.length + '</b> vacuna(s).<br>Esta accion no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#be123c'
            }).then(function(result){
                const confirmed = !!(result && (result.isConfirmed || result.value === true));
                if (confirmed) onConfirm();
            });
            return;
        }

        if (window.confirm(text)) {
            onConfirm();
        }
    }

    function requestDelete(ids){
        if (!ids.length) return;

        confirmDelete(ids, function(){
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    _method: 'DELETE',
                    ids: ids
                },
                success: function(resp){
                    ids.forEach(function(id){ selected.delete(String(id)); });
                    refreshSelectedUI();
                    table.ajax.reload(null, false);
                    notify('success', 'Eliminacion completada', resp && resp.message ? resp.message : 'Vacunas eliminadas correctamente.');
                },
                error: function(xhr){
                    let msg = 'No se pudo completar la eliminacion.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    notify('error', 'Error', msg);
                }
            });
        });
    }

    $('#vmDeleteSelected').on('click', function(){
        requestDelete(Array.from(selected));
    });

    $('#vmTable').on('click', '.vm-delete-one', function(){
        const id = String($(this).data('id') || '');
        if (!id) return;
        requestDelete([id]);
    });
})();
</script>
@stop
