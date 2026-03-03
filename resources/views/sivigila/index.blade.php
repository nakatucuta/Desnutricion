@extends('adminlte::page')

@section('title', 'Sivigila 113')

@section('content_header')
@include('sivigila.mensajes')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center mb-2">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="aw-logo mr-2">
        <div>
            <h1 class="m-0 aw-title">Sivigila 113</h1>
            <small class="text-muted">Gestion operativa, seguimiento y reportes dinamicos</small>
        </div>
    </div>
    <div class="mb-2 d-flex flex-wrap">
        <a href="{{ route('sivigila.audit.index') }}" class="btn btn-outline-dark btn-sm mr-2 mb-1">
            <i class="fas fa-history mr-1"></i> Auditoria
        </a>
        <button class="btn btn-outline-primary btn-sm mr-2 mb-1" id="btnOpenReportDesigner" data-toggle="modal" data-target="#reportDesignerModalSivigila" onclick="window.openSivigilaReportDesigner && window.openSivigilaReportDesigner(); return false;">
            <i class="fas fa-sliders-h mr-1"></i> Diseñar reporte
        </button>
        <a href="{{ route('export2') }}" class="btn btn-outline-success btn-sm mr-2 mb-1"><i class="fas fa-file-export mr-1"></i> Exportar base</a>
        <a href="{{ route('export4') }}" class="btn btn-outline-success btn-sm mr-2 mb-1"><i class="fas fa-file-export mr-1"></i> S. publica</a>
        <a href="{{ route('export5') }}" class="btn btn-outline-success btn-sm mr-2 mb-1"><i class="fas fa-file-export mr-1"></i> Sin seguimiento</a>
        <a href="{{ route('create11') }}" class="btn btn-success btn-sm mb-1"><i class="fas fa-plus mr-1"></i> Agregar</a>
    </div>
</div>
@stop

@section('content')
<div id="loadingOverlaySivigila" class="aw-overlay d-none">
    <div class="aw-overlay-card">
        <i class="fas fa-circle-notch fa-spin mr-2"></i>
        <span id="loadingOverlayTextSivigila">Procesando...</span>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h4>{{ number_format($resultados ?? 0) }}</h4>
                <p>Registros fuente</p>
            </div>
            <div class="icon"><i class="fas fa-database"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h4>{{ number_format($count123 ?? 0) }}</h4>
                <p>Con seguimiento</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h4>{{ number_format($sivi2 ?? 0) }}</h4>
                <p>Total sivigilas</p>
            </div>
            <div class="icon"><i class="fas fa-file-medical"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h4>{{ number_format($conteo ?? 0) }}</h4>
                <p>Alertas activas</p>
            </div>
            <div class="icon"><i class="fas fa-bell"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-info aw-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros rapidos</h3>
    </div>
    <div class="card-body">
        <form id="filtersFormSivigila">
            <div class="row">
                <div class="col-md-2">
                    <label>Año</label>
                    <select name="year" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ (string)($defaultYear ?? '') === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Semana</label>
                    <input type="number" min="1" max="53" name="semana" class="form-control form-control-sm" placeholder="1-53">
                </div>
                <div class="col-md-2">
                    <label>Tipo ID</label>
                    <input type="text" name="tip_ide_" class="form-control form-control-sm" placeholder="CC, TI...">
                </div>
                <div class="col-md-2">
                    <label>Procesado</label>
                    <select name="procesado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="1">Si</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>UPGD</label>
                    <input type="text" name="nom_upgd" class="form-control form-control-sm" placeholder="Nombre UPGD">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-8">
                    <label>Busqueda rapida</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Documento, nombre, UPGD...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-info btn-sm mr-2 btn-block"><i class="fas fa-search mr-1"></i> Aplicar</button>
                    <button type="button" id="btnResetFiltersSivigila" class="btn btn-outline-secondary btn-sm btn-block">Limpiar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-secondary aw-card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="sivigila-table" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Fecha Notificacion</th>
                        <th>Semana</th>
                        <th>Tipo ID</th>
                        <th>Identificacion</th>
                        <th>Nombre</th>
                        <th>UPGD Notificadora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="reportDesignerModalSivigila" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-drafting-compass mr-1"></i> Diseñador de reporte Sivigila</h5>
                <button type="button" class="close" id="btnCloseReportDesignerSivigila" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6 class="mb-2">Variables disponibles</h6>
                        <div id="columnsChecklistSivigila" class="aw-checklist"></div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Orden del reporte</h6>
                            <small class="text-muted">Arrastra para reordenar</small>
                        </div>
                        <ul id="selectedColumnsListSivigila" class="list-group mb-2"></ul>
                        <div class="d-flex mb-2">
                            <select id="reportFormatSivigila" class="form-control form-control-sm mr-2" style="max-width:160px;">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="csv">CSV</option>
                            </select>
                            <button id="btnPreviewReportSivigila" type="button" class="btn btn-outline-primary btn-sm mr-2">
                                <i class="fas fa-eye mr-1"></i> Vista previa
                            </button>
                            <button id="btnExportDesignedSivigila" type="button" class="btn btn-success btn-sm">
                                <i class="fas fa-file-export mr-1"></i> Exportar
                            </button>
                        </div>
                        <div class="table-responsive aw-preview-wrap">
                            <table class="table table-sm table-bordered table-striped mb-0" id="previewTableSivigila">
                                <thead></thead>
                                <tbody><tr><td class="text-center text-muted">Aun sin vista previa.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
.aw-logo{width:44px;height:44px;object-fit:contain;border-radius:10px;background:#fff;padding:3px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.aw-title{font-weight:700;color:#1f2937;letter-spacing:.3px}
.aw-card{border-radius:14px;overflow:hidden;box-shadow:0 10px 24px rgba(15,23,42,.06)}
.aw-checklist{max-height:420px;overflow:auto;border:1px solid #e5e7eb;border-radius:10px;padding:10px;background:#fafafa}
.aw-check-item{display:flex;align-items:center;padding:6px 4px;border-bottom:1px dashed #e5e7eb}
.aw-check-item:last-child{border-bottom:none}
.aw-check-item label{margin:0 0 0 8px;font-weight:500}
.aw-preview-wrap{max-height:430px;overflow:auto;border:1px solid #e5e7eb;border-radius:10px}
#selectedColumnsListSivigila .list-group-item{cursor:move}
.aw-overlay{position:fixed;inset:0;background:rgba(15,23,42,.32);z-index:2100;display:flex;align-items:center;justify-content:center}
.aw-overlay-card{background:#fff;padding:14px 18px;border-radius:12px;box-shadow:0 12px 24px rgba(0,0,0,.2)}
</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(function() {
    const columnsCatalog = @json($reportColumns ?? []);
    let selectedColumns = @json($defaultReportColumns ?? []);

    const overlay = $('#loadingOverlaySivigila');
    const overlayText = $('#loadingOverlayTextSivigila');
    function setLoading(on, text) {
        if (text) overlayText.text(text);
        overlay.toggleClass('d-none', !on);
    }

    const table = $('#sivigila-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        stateSave: false,
        searching: true,
        searchDelay: 300,
        pageLength: 15,
        ajax: {
            url: '{{ route("sivigila.data") }}',
            data: function(d) { Object.assign(d, getFilters()); }
        },
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_',
            info: 'Mostrando _START_ a _END_ de _TOTAL_',
            paginate: { first:'Primero', last:'Ultimo', next:'Siguiente', previous:'Anterior' }
        },
        columns: [
            { data: 'fec_noti', name: 'v.fec_noti' },
            { data: 'semana', name: 'v.semana' },
            { data: 'tip_ide_', name: 'v.tip_ide_' },
            { data: 'num_ide_', name: 'v.num_ide_' },
            {
                data: null, orderable: false, searchable: false,
                render: row => [row.pri_nom_, row.seg_nom_, row.pri_ape_, row.seg_ape_].filter(Boolean).join(' ')
            },
            { data: 'nom_upgd', name: 'v.nom_upgd' },
            { data: 'acciones', orderable: false, searchable: false }
        ]
    });

    function getFilters() {
        const data = {};
        $('#filtersFormSivigila').serializeArray().forEach(i => data[i.name] = i.value);
        return data;
    }

    $('#filtersFormSivigila').on('submit', function(e){
        e.preventDefault();
        setLoading(true, 'Aplicando filtros...');
        table.ajax.reload();
    });
    $('#btnResetFiltersSivigila').on('click', function(){
        $('#filtersFormSivigila')[0].reset();
        setLoading(true, 'Recargando datos...');
        table.ajax.reload();
    });
    $('#sivigila-table').on('preXhr.dt', function(){ setLoading(true, 'Cargando registros...'); });
    $('#sivigila-table').on('xhr.dt error.dt', function(){ setLoading(false); });

    function renderColumnsChecklist() {
        const wrap = $('#columnsChecklistSivigila');
        wrap.empty();
        Object.keys(columnsCatalog).forEach(key => {
            const checked = selectedColumns.includes(key) ? 'checked' : '';
            wrap.append(`
                <div class="aw-check-item">
                    <input type="checkbox" class="col-toggle-siv" id="col_siv_${key}" data-key="${key}" ${checked}>
                    <label for="col_siv_${key}">${columnsCatalog[key].label}</label>
                </div>
            `);
        });
    }
    function renderSelectedList() {
        const ul = $('#selectedColumnsListSivigila');
        ul.empty();
        selectedColumns.forEach(key => {
            if (!columnsCatalog[key]) return;
            ul.append(`<li class="list-group-item d-flex justify-content-between align-items-center" data-key="${key}"><span><i class="fas fa-grip-vertical text-muted mr-2"></i>${columnsCatalog[key].label}</span><button class="btn btn-xs btn-outline-danger btn-remove-col-siv" type="button" data-key="${key}"><i class="fas fa-times"></i></button></li>`);
        });
    }

    $('#columnsChecklistSivigila').on('change', '.col-toggle-siv', function(){
        const key = $(this).data('key');
        if (this.checked) {
            if (!selectedColumns.includes(key)) selectedColumns.push(key);
        } else {
            selectedColumns = selectedColumns.filter(x => x !== key);
        }
        renderSelectedList();
    });
    $('#selectedColumnsListSivigila').on('click', '.btn-remove-col-siv', function(){
        const key = $(this).data('key');
        selectedColumns = selectedColumns.filter(x => x !== key);
        $('#col_siv_' + key).prop('checked', false);
        renderSelectedList();
    });

    if (typeof Sortable !== 'undefined') {
        new Sortable(document.getElementById('selectedColumnsListSivigila'), {
            animation: 120,
            onEnd: function() {
                const order = [];
                $('#selectedColumnsListSivigila li').each(function(){ order.push($(this).data('key')); });
                selectedColumns = order;
            }
        });
    }

    function buildBody(includeFormat) {
        const body = new URLSearchParams();
        const filters = getFilters();
        Object.keys(filters).forEach(k => body.append(k, filters[k] || ''));
        selectedColumns.forEach(c => body.append('columns[]', c));
        if (includeFormat) body.append('format', $('#reportFormatSivigila').val());
        return body.toString();
    }

    function renderPreview(headings, cols, rows) {
        const thead = $('#previewTableSivigila thead');
        const tbody = $('#previewTableSivigila tbody');
        if (!cols.length) {
            thead.html('');
            tbody.html('<tr><td class="text-center text-muted">Sin columnas seleccionadas.</td></tr>');
            return;
        }
        thead.html('<tr>' + cols.map(c => `<th>${headings[c] || c}</th>`).join('') + '</tr>');
        if (!rows.length) {
            tbody.html('<tr><td colspan="' + cols.length + '" class="text-center text-muted">Sin datos para filtros actuales.</td></tr>');
            return;
        }
        tbody.html(rows.map(r => '<tr>' + cols.map(c => `<td>${r[c] ?? ''}</td>`).join('') + '</tr>').join(''));
    }

    $('#btnPreviewReportSivigila').on('click', async function(){
        if (!selectedColumns.length) {
            Swal.fire({ icon:'warning', title:'Selecciona columnas', text:'Debes elegir al menos una variable.' });
            return;
        }
        setLoading(true, 'Construyendo vista previa...');
        try {
            const res = await fetch('{{ route("sivigila.report.preview") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: buildBody(false)
            });
            const json = await res.json();
            if (!json.ok) throw new Error('No se pudo generar la vista previa');
            renderPreview(json.headings || {}, json.columns || [], json.rows || []);
        } catch (e) {
            Swal.fire({ icon:'error', title:'Error', text: e.message || 'Error en vista previa' });
        } finally {
            setLoading(false);
        }
    });

    $('#btnExportDesignedSivigila').on('click', function(){
        if (!selectedColumns.length) {
            Swal.fire({ icon:'warning', title:'Selecciona columnas', text:'Debes elegir al menos una variable.' });
            return;
        }
        const form = $('<form>', { method:'POST', action:'{{ route("sivigila.report.export") }}' });
        form.append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }));
        const filters = getFilters();
        Object.keys(filters).forEach(k => form.append($('<input>', { type:'hidden', name:k, value:filters[k] || '' })));
        selectedColumns.forEach(c => form.append($('<input>', { type:'hidden', name:'columns[]', value:c })));
        form.append($('<input>', { type:'hidden', name:'format', value:$('#reportFormatSivigila').val() }));
        $('body').append(form); form.trigger('submit'); form.remove();
    });

    function toggleDesignerFallback(show) {
        const el = document.getElementById('reportDesignerModalSivigila');
        if (!el) return;
        if (show) {
            el.style.display = 'block';
            el.classList.add('show');
            el.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
            if (!document.getElementById('sivigila-modal-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.id = 'sivigila-modal-backdrop';
                backdrop.className = 'modal-backdrop fade show';
                backdrop.addEventListener('click', function() { toggleDesignerFallback(false); });
                document.body.appendChild(backdrop);
            }
            return;
        }
        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        const backdrop = document.getElementById('sivigila-modal-backdrop');
        if (backdrop) backdrop.remove();
    }

    function openDesigner() {
        renderColumnsChecklist();
        renderSelectedList();
        const modalEl = document.getElementById('reportDesignerModalSivigila');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }
        if ($.fn.modal) {
            $(modalEl).modal('show');
            return;
        }
        toggleDesignerFallback(true);
    }
    function closeDesigner() {
        const modalEl = document.getElementById('reportDesignerModalSivigila');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            return;
        }
        if ($.fn.modal) {
            $(modalEl).modal('hide');
            return;
        }
        toggleDesignerFallback(false);
    }
    window.openSivigilaReportDesigner = openDesigner;
    $(document).on('click', '#btnOpenReportDesigner', function(e){ e.preventDefault(); openDesigner(); });
    $('#btnCloseReportDesignerSivigila').on('click', closeDesigner);
});
</script>
@stop


