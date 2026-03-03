@extends('adminlte::page')

@section('title', 'Cargue 412')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center mb-2">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="aw-logo mr-2">
        <div>
            <h1 class="m-0 aw-title">Cargue y Analitica 412</h1>
            <small class="text-muted">Importacion, filtros operativos y reportes personalizables</small>
        </div>
    </div>
    <div class="mb-2 d-flex">
        <a href="{{ route('import-excel.audit.index') }}" class="btn btn-outline-dark btn-sm mr-2">
            <i class="fas fa-clipboard-list mr-1"></i> Auditoria 412
        </a>
        <button class="btn btn-outline-primary btn-sm mr-2" id="btnOpenReportDesigner" onclick="window.__openReportDesigner && window.__openReportDesigner(); return false;">
            <i class="fas fa-sliders-h mr-1"></i> Diseñar reporte
        </button>
        <a href="{{ route('export7') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv mr-1"></i> Exportar clásico CSV
        </a>
    </div>
</div>
@stop

@section('content')
<div id="loadingOverlay412" class="aw-overlay d-none">
    <div class="aw-overlay-card">
        <i class="fas fa-circle-notch fa-spin mr-2"></i>
        <span id="loadingOverlayText412">Procesando solicitud...</span>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card card-outline card-primary aw-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-upload mr-1"></i> Cargar archivo</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('import-excel') }}" method="POST" enctype="multipart/form-data" id="file-upload-form">
                    @csrf
                    <div id="drag-drop-area" class="aw-dropzone mb-2">
                        <i class="fas fa-cloud-upload-alt aw-drop-icon"></i>
                        <p id="file-instructions" class="mb-0 text-muted">Arrastra tu Excel aquí o haz clic para seleccionarlo</p>
                        <input type="file" name="file" accept=".xls,.xlsx" class="file-input">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-upload mr-1"></i> Importar Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-outline card-info aw-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros rápidos</h3>
            </div>
            <div class="card-body">
                <form id="filtersForm412">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Año</label>
                            <select id="filter-year-412" name="year" class="form-control form-control-sm">
                                <option value="">Todos (puede tardar)</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ (string)($defaultYear ?? '') === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Desde</label>
                            <input type="date" name="captacion_desde" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label>Hasta</label>
                            <input type="date" name="captacion_hasta" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label>Sexo</label>
                            <select name="sexo" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="F">F</option>
                                <option value="M">M</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Procesado</label>
                            <select name="procesado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Municipio</label>
                            <input type="text" name="municipio" class="form-control form-control-sm" placeholder="Municipio">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-5">
                            <label>Búsqueda rápida</label>
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="ID, nombre, IPS, coperante...">
                        </div>
                        <div class="col-md-5">
                            <label>IPS primaria</label>
                            <input type="text" name="ips_primaria" class="form-control form-control-sm" placeholder="Nombre IPS">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-sm mr-2 btn-block">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                            <button type="button" id="btnResetFilters412" class="btn btn-outline-secondary btn-sm btn-block">
                                Limpiar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary aw-card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="new412-table" class="table table-hover table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Fecha cargue</th>
                        <th>ID</th>
                        <th>Coperante</th>
                        <th>Fecha captación</th>
                        <th>Municipio</th>
                        <th>Paciente</th>
                        <th>Tipo ID</th>
                        <th>Número ID</th>
                        <th>Sexo</th>
                        <th>Edad (meses)</th>
                        <th>IPS primaria</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="reportDesignerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-drafting-compass mr-1"></i> Diseñador de reporte 412</h5>
                <button type="button" class="close" id="btnCloseReportDesigner" data-dismiss="modal" aria-label="Cerrar" onclick="window.__closeReportDesigner && window.__closeReportDesigner(); return false;"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6 class="mb-2">Variables disponibles</h6>
                        <div id="columnsChecklist" class="aw-checklist"></div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Orden del reporte</h6>
                            <small class="text-muted">Arrastra para reordenar columnas</small>
                        </div>
                        <ul id="selectedColumnsList" class="list-group mb-2"></ul>
                        <div class="d-flex mb-2">
                            <select id="reportFormat" class="form-control form-control-sm mr-2" style="max-width:160px;">
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel (.xlsx)</option>
                            </select>
                            <button id="btnPreviewReport" type="button" class="btn btn-outline-primary btn-sm mr-2">
                                <i class="fas fa-eye mr-1"></i> Vista previa
                            </button>
                            <button id="btnExportDesigned" type="button" class="btn btn-success btn-sm">
                                <i class="fas fa-file-export mr-1"></i> Exportar
                            </button>
                        </div>
                        <div class="table-responsive aw-preview-wrap">
                            <table class="table table-sm table-bordered table-striped mb-0" id="previewTable412">
                                <thead></thead>
                                <tbody>
                                    <tr><td class="text-center text-muted">Aún sin vista previa.</td></tr>
                                </tbody>
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
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
.aw-logo{width:44px;height:44px;object-fit:contain;border-radius:10px;background:#fff;padding:3px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.aw-title{font-weight:700;color:#1f2937;letter-spacing:.3px}
.aw-card{border-radius:14px;overflow:hidden;box-shadow:0 10px 24px rgba(15,23,42,.06)}
.aw-dropzone{border:2px dashed #8ab4f8;border-radius:14px;padding:24px;text-align:center;position:relative;background:linear-gradient(180deg,#f8fbff,#f0f7ff)}
.aw-dropzone.drag-over{background:#e8f2ff}
.aw-drop-icon{font-size:34px;color:#0ea5e9;margin-bottom:8px}
.aw-dropzone .file-input{position:absolute;inset:0;opacity:0;cursor:pointer}
.aw-checklist{max-height:420px;overflow:auto;border:1px solid #e5e7eb;border-radius:10px;padding:10px;background:#fafafa}
.aw-check-item{display:flex;align-items:center;padding:6px 4px;border-bottom:1px dashed #e5e7eb}
.aw-check-item:last-child{border-bottom:none}
.aw-check-item label{margin:0 0 0 8px;font-weight:500}
.aw-preview-wrap{max-height:430px;overflow:auto;border:1px solid #e5e7eb;border-radius:10px}
#selectedColumnsList .list-group-item{cursor:move}
.aw-overlay{position:fixed;inset:0;background:rgba(15,23,42,.32);z-index:2100;display:flex;align-items:center;justify-content:center}
.aw-overlay-card{background:#fff;padding:14px 18px;border-radius:12px;box-shadow:0 12px 24px rgba(0,0,0,.2)}
.aw-btn-edit{background:linear-gradient(135deg,#0ea5e9,#2563eb);border-color:#1d4ed8;color:#fff}
.aw-btn-edit:hover{background:linear-gradient(135deg,#0284c7,#1d4ed8);color:#fff}
.aw-btn-delete{background:linear-gradient(135deg,#ef4444,#b91c1c);border-color:#991b1b;color:#fff}
.aw-btn-delete:hover{background:linear-gradient(135deg,#dc2626,#991b1b);color:#fff}
.aw-btn-processed{background:linear-gradient(135deg,#f59e0b,#b45309);border-color:#92400e;color:#fff}
.aw-btn-processed:hover{background:linear-gradient(135deg,#d97706,#92400e);color:#fff}
</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
window.__openReportDesigner = function () {
    var modal = document.getElementById('reportDesignerModal');
    if (!modal) return;
    modal.classList.add('show');
    modal.style.display = 'block';
    modal.setAttribute('aria-modal', 'true');
    modal.removeAttribute('aria-hidden');
    document.body.classList.add('modal-open');
    if (!document.getElementById('reportDesignerBackdrop')) {
        var backdrop = document.createElement('div');
        backdrop.id = 'reportDesignerBackdrop';
        backdrop.className = 'modal-backdrop fade show';
        backdrop.onclick = function () { window.__closeReportDesigner(); };
        document.body.appendChild(backdrop);
    }
};

window.__closeReportDesigner = function () {
    var modal = document.getElementById('reportDesignerModal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.removeAttribute('aria-modal');
        modal.setAttribute('aria-hidden', 'true');
    }
    var backdrop = document.getElementById('reportDesignerBackdrop');
    if (backdrop) backdrop.remove();
    document.body.classList.remove('modal-open');
};
</script>
<script>
$(function() {
    const columnsCatalog = @json($reportColumns ?? []);
    let selectedColumns = @json($defaultReportColumns ?? []);

    const overlay = $('#loadingOverlay412');
    const overlayText = $('#loadingOverlayText412');
    function setLoading(on, text) {
        if (text) overlayText.text(text);
        overlay.toggleClass('d-none', !on);
    }

        let table412 = null;
    try {
        table412 = $('#new412-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            stateSave: false,
            searching: false,
            searchDelay: 350,
            pageLength: 10,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_',
                info: 'Mostrando _START_ a _END_ de _TOTAL_',
                paginate: { first:'Primero', last:'Ultimo', next:'Siguiente', previous:'Anterior' }
            },
            ajax: {
                url: '{{ route("import-excel-data") }}',
                data: function(d) {
                    const f = getFilters();
                    Object.assign(d, f);
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data:'fecha_cargue', name:'fecha_cargue' },
                { data:'id', name:'id' },
                { data:'nombre_coperante', name:'nombre_coperante' },
                { data:'fecha_captacion', name:'fecha_captacion' },
                { data:'municipio', name:'municipio' },
                { data:'nombres_completos', name:'nombres_completos' },
                { data:'tipo_identificacion', name:'tipo_identificacion' },
                { data:'numero_identificacion', name:'numero_identificacion' },
                { data:'sexo', name:'sexo' },
                { data:'edad_meses', name:'edad_meses' },
                { data:'ips_primaria', name:'ips_primaria' },
                { data:'acciones', orderable:false, searchable:false }
            ]
        });
    } catch (e) {
        console.error('Error iniciando tabla 412', e);
    }

    function getFilters() {
        const data = {};
        $('#filtersForm412').serializeArray().forEach(x => data[x.name] = x.value);
        return data;
    }

    $('#filtersForm412').on('submit', function(e){
        e.preventDefault();
        if (!table412) return;
        setLoading(true, 'Aplicando filtros...');
        table412.ajax.reload();
    });

    $('#btnResetFilters412').on('click', function(){
        $('#filtersForm412')[0].reset();
        if (!table412) return;
        setLoading(true, 'Recargando datos...');
        table412.ajax.reload();
    });
    
    if (table412) {
        $('#new412-table').on('preXhr.dt', function () {
            setLoading(true, 'Cargando registros...');
        });
        $('#new412-table').on('xhr.dt error.dt', function () {
            setLoading(false);
        });
    }

    const area = $('#drag-drop-area');
    const input = area.find('.file-input');
    const info = $('#file-instructions');
    area.on('click', () => input.trigger('click'));
    input.on('change', e => showFile(e.target.files));
    area.on('dragover dragenter', e => { e.preventDefault(); area.addClass('drag-over'); });
    area.on('dragleave drop', e => { e.preventDefault(); area.removeClass('drag-over'); });
    area.on('drop', e => {
        input[0].files = e.originalEvent.dataTransfer.files;
        showFile(input[0].files);
    });

    function showFile(files) {
        if (!files.length) return;
        const f = files[0];
        const ok = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        if (ok.includes(f.type) || /\.xlsx?$/.test(f.name.toLowerCase())) {
            info.text('Archivo seleccionado: ' + f.name).removeClass('text-danger');
        } else {
            info.text('Solo archivos .xls o .xlsx').addClass('text-danger');
            input.val('');
        }
    }

    function renderColumnsChecklist() {
        const wrap = $('#columnsChecklist');
        wrap.empty();
        Object.keys(columnsCatalog).forEach(key => {
            const checked = selectedColumns.includes(key) ? 'checked' : '';
            const row = $(`
                <div class="aw-check-item">
                    <input type="checkbox" class="col-toggle" id="col_${key}" data-key="${key}" ${checked}>
                    <label for="col_${key}">${columnsCatalog[key].label}</label>
                </div>
            `);
            wrap.append(row);
        });
    }

    function renderSelectedList() {
        const ul = $('#selectedColumnsList');
        ul.empty();
        selectedColumns.forEach(key => {
            if (!columnsCatalog[key]) return;
            ul.append(`<li class="list-group-item d-flex justify-content-between align-items-center" data-key="${key}"><span><i class="fas fa-grip-vertical text-muted mr-2"></i>${columnsCatalog[key].label}</span><button class="btn btn-xs btn-outline-danger btn-remove-col" type="button" data-key="${key}"><i class="fas fa-times"></i></button></li>`);
        });
    }

    $('#columnsChecklist').on('change', '.col-toggle', function(){
        const key = $(this).data('key');
        if (this.checked) {
            if (!selectedColumns.includes(key)) selectedColumns.push(key);
        } else {
            selectedColumns = selectedColumns.filter(x => x !== key);
        }
        renderSelectedList();
    });

    $('#selectedColumnsList').on('click', '.btn-remove-col', function(){
        const key = $(this).data('key');
        selectedColumns = selectedColumns.filter(x => x !== key);
        $('#col_' + key).prop('checked', false);
        renderSelectedList();
    });

    if (typeof Sortable !== 'undefined' && document.getElementById('selectedColumnsList')) {
        new Sortable(document.getElementById('selectedColumnsList'), {
            animation: 120,
            onEnd: function() {
                const order = [];
                $('#selectedColumnsList li').each(function(){ order.push($(this).data('key')); });
                selectedColumns = order;
            }
        });
    }

    function buildPostBody(includeFormat) {
        const body = new URLSearchParams();
        const filters = getFilters();
        Object.keys(filters).forEach(k => body.append(k, filters[k] || ''));
        selectedColumns.forEach(c => body.append('columns[]', c));
        if (includeFormat) body.append('format', $('#reportFormat').val());
        return body;
    }

    async function previewReport() {
        if (!selectedColumns.length) {
            Swal.fire({ icon:'warning', title:'Selecciona columnas', text:'Debes elegir al menos una variable.' });
            return;
        }
        setLoading(true, 'Construyendo vista previa del reporte...');
        try {
            const res = await fetch('{{ route("import-excel.report.preview") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: buildPostBody(false).toString()
            });
            const json = await res.json();
            if (!json.ok) throw new Error('No se pudo generar la vista previa');
            renderPreviewTable(json.headings || {}, json.columns || [], json.rows || []);
        } catch (e) {
            Swal.fire({ icon:'error', title:'Error', text: e.message || 'Error de vista previa' });
        } finally {
            setLoading(false);
        }
    }

    function renderPreviewTable(headings, cols, rows) {
        const thead = $('#previewTable412 thead');
        const tbody = $('#previewTable412 tbody');
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
        const body = rows.map(r => '<tr>' + cols.map(c => `<td>${(r[c] ?? '')}</td>`).join('') + '</tr>').join('');
        tbody.html(body);
    }

    $('#btnPreviewReport').on('click', previewReport);

    $('#btnExportDesigned').on('click', function(){
        if (!selectedColumns.length) {
            Swal.fire({ icon:'warning', title:'Selecciona columnas', text:'Debes elegir al menos una variable.' });
            return;
        }
        const form = $('<form>', { method:'POST', action:'{{ route("import-excel.report.export") }}' });
        form.append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }));
        const filters = getFilters();
        Object.keys(filters).forEach(k => form.append($('<input>', { type:'hidden', name:k, value:filters[k] || '' })));
        selectedColumns.forEach(c => form.append($('<input>', { type:'hidden', name:'columns[]', value:c })));
        form.append($('<input>', { type:'hidden', name:'format', value:$('#reportFormat').val() }));
        $('body').append(form);
        form.trigger('submit');
        form.remove();
    });

        function openReportDesignerModal() {
        renderColumnsChecklist();
        renderSelectedList();
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('reportDesignerModal')).show();
            return;
        }
        if ($.fn.modal) {
            $('#reportDesignerModal').modal('show');
            return;
        }
        const modal = $('#reportDesignerModal');
        modal.addClass('show d-block').css('display', 'block').attr('aria-modal', 'true').removeAttr('aria-hidden');
        if (!$('#reportDesignerBackdrop').length) {
            $('body').append('<div class="modal-backdrop fade show" id="reportDesignerBackdrop"></div>');
        }
        $('body').addClass('modal-open');
    }

    function closeReportDesignerModal() {
        const modal = $('#reportDesignerModal');
        modal.removeClass('show d-block').css('display', 'none').removeAttr('aria-modal').attr('aria-hidden', 'true');
        $('#reportDesignerBackdrop').remove();
        $('body').removeClass('modal-open');
    }

    $('#btnOpenReportDesigner').on('click', openReportDesignerModal);
    $('#btnCloseReportDesigner').on('click', closeReportDesignerModal);
    $('#reportDesignerModal').on('click', function(e){
        if (e.target === this) closeReportDesignerModal();
    });
    @if(Session::has('mensaje'))
        Swal.fire({ icon:'info', title:'Mensaje', text:"{{ Session::get('mensaje') }}" });
    @endif
    @if(Session::has('success'))
        Swal.fire({ icon:'success', title:'Éxito', text:"{{ Session::get('success') }}" });
    @endif
    @if(Session::has('error1'))
        Swal.fire({ icon:'error', title:'Error de Cargue', html:`{!! nl2br(e(Session::get('error1'))) !!}`, width:'940px' });
    @endif
    @if($errors->any())
        Swal.fire({ icon:'error', title:'Errores encontrados', html:`<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>` });
    @endif
});
</script>
@stop
