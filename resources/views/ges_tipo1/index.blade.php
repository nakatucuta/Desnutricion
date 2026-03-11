{{-- resources/views/ges_tipo1/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Listado de Gestantes')

@section('content_header')
<div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 rounded shadow-sm flex-wrap">
    <div class="d-flex align-items-center">
        <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo PAI" class="header-logo mr-3">
        <div>
            <h1 class="m-0 text-info">Listado de Gestantes</h1>
            <small class="text-muted">Seguimiento operativo y reportes diseñados con vista previa</small>
        </div>
    </div>
    <div class="mt-2 mt-md-0">
        <button class="btn btn-gradient btn-lg mr-2 btn-open-designer" data-module="tipo1">
            <i class="fas fa-sliders-h mr-1"></i> Diseñar reporte Tipo 2
        </button>
        <button class="btn btn-gradient btn-lg mr-2 btn-open-designer" data-module="tipo3">
            <i class="fas fa-sliders-h mr-1"></i> Diseñar reporte Tipo 3
        </button>
        <a href="{{ route('ges_tipo3.import') }}" class="btn btn-gradient btn-lg">
            <i class="fas fa-file-excel mr-1"></i> Cargar (tipo 3)
        </a>
    </div>
</div>
@stop

@section('content')
<div id="reportLoadingOverlay" class="designer-overlay d-none">
    <div class="designer-overlay-card">
        <i class="fas fa-circle-notch fa-spin mr-2"></i>
        <span id="reportLoadingText">Procesando...</span>
        <small id="reportLoadingHint" class="d-block text-muted mt-2">CSV y TSV suelen descargar mas rapido. XLSX tarda un poco mas en generarse.</small>
        <div class="mt-3 text-center">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCloseReportOverlay">Cerrar aviso</button>
        </div>
    </div>
</div>

<form id="designedExportFormGestantes" method="POST" class="d-none">
    @csrf
</form>
<iframe name="downloadFrameGestantes" id="downloadFrameGestantes" class="d-none"></iframe>

<div class="card card-outline card-info gest-table-card">
    <div class="card-header border-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gest-table-toolbar">
            <div>
                <h5 class="mb-1 gest-table-title">Base operativa de gestantes</h5>
                <small class="text-muted">Consulta, seguimiento y edición en una sola vista.</small>
            </div>
            <div class="gest-table-chip">
                <i class="fas fa-table mr-1"></i> Panel administrativo
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="gestantes-table" class="table table-hover table-bordered gestantes-datatable">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Tipo ID</th>
                        <th>No ID Usuaria</th>
                        <th>Número Carnet</th>
                        <th>Fecha Nac.</th>
                        <th>FPP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="reportDesignerModalGestantes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable designer-dialog" role="document">
        <div class="modal-content designer-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="designerModalTitle"><i class="fas fa-drafting-compass mr-1"></i> Diseñador de reporte</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="designer-hero mb-3">
                    <div>
                        <div class="designer-eyebrow" id="designerEyebrow">Tipo 2</div>
                        <h4 class="designer-hero-title mb-1" id="designerHeroTitle">Diseña un reporte claro y bonito</h4>
                        <p class="mb-0 text-muted" id="designerHeroText">Elige el rango de fechas, organiza columnas y revisa la vista previa antes de descargar.</p>
                    </div>
                    <div class="designer-chip-group">
                        <span class="designer-chip">Vista previa</span>
                        <span class="designer-chip">Orden personalizable</span>
                        <span class="designer-chip">CSV, TSV, XLSX y JSON</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="designer-panel">
                            <h6 class="designer-panel-title">Rango del reporte</h6>
                            <div class="form-group">
                                <label>Fecha inicio</label>
                                <input type="date" id="designerFrom" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Fecha fin</label>
                                <input type="date" id="designerTo" class="form-control" required>
                            </div>
                            <div class="form-group mb-0">
                                <label>Formato</label>
                                <select id="designerFormat" class="form-control">
                                    <option value="csv">CSV (recomendado, más rápido)</option>
                                    <option value="tsv">TSV</option>
                                    <option value="xlsx">Excel XLSX</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                        </div>

                        <div class="designer-panel mt-3">
                            <h6 class="designer-panel-title">Variables disponibles</h6>
                            <div id="designerColumnsChecklist" class="designer-checklist"></div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="designer-panel">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                <div>
                                    <h6 class="designer-panel-title mb-0">Vista previa</h6>
                                    <small class="text-muted">Se mostrarán hasta 25 registros del rango elegido.</small>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" id="btnPreviewGestantes">
                                        <i class="fas fa-eye mr-1"></i> Vista previa
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="btnExportGestantes">
                                        <i class="fas fa-file-export mr-1"></i> Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="designer-selection-wrap mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                    <div>
                                        <h6 class="designer-panel-title mb-0">Columnas activas</h6>
                                        <small class="text-muted">Arrastra para reorganizar. Todo queda visible sin empujar la vista previa.</small>
                                    </div>
                                    <span class="designer-count-badge" id="designerSelectedMeta">0 columnas</span>
                                </div>
                                <div id="designerSelectedColumns" class="designer-selected-columns"></div>
                            </div>
                            <div class="table-responsive designer-preview-wrap">
                                <table class="table table-sm table-bordered table-striped mb-0" id="designerPreviewTable">
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
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<style>
    .header-logo {
        width: 60px;
        height: auto;
    }
    .btn-gradient {
        background: linear-gradient(45deg, #17a2b8, #117a8b);
        border: none;
        color: #fff;
        transition: background .3s ease;
    }
    .btn-gradient:hover {
        background: linear-gradient(45deg, #117a8b, #0e6272);
    }
    .btn-gradient:focus {
        box-shadow: 0 0 0 0.2rem rgba(23,162,184,0.5);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(23,162,184,0.1);
    }
    .badge {
        font-size: .78rem;
        letter-spacing: .01em;
    }
    .gest-table-card {
        border-radius: .5rem;
    }
    .gest-table-toolbar {
        gap: 12px;
    }
    .gest-table-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #343a40;
    }
    .gest-table-chip {
        display: inline-flex;
        align-items: center;
        padding: .35rem .75rem;
        border-radius: 50rem;
        background: #f4f6f9;
        border: 1px solid #dee2e6;
        color: #6c757d;
        font-weight: 600;
        font-size: .8rem;
    }
    .gestantes-datatable {
        border-collapse: separate !important;
        border-spacing: 0;
    }
    .gestantes-datatable thead th {
        background: #e9ecef;
        color: #495057;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
        padding: .8rem .75rem;
        vertical-align: middle;
    }
    .gestantes-datatable thead th:first-child {
        border-top-left-radius: .35rem;
    }
    .gestantes-datatable thead th:last-child {
        border-top-right-radius: .35rem;
    }
    .gestantes-datatable tbody td {
        padding: .75rem;
        vertical-align: middle;
        border-color: #e2e8f0 !important;
        background: #fff;
    }
    .gestantes-datatable tbody tr:nth-child(even) td {
        background: #fcfcfd;
    }
    .gestantes-datatable tbody tr:hover td {
        background: rgba(0,123,255,.04) !important;
    }
    .gest-action-menu {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .35rem;
        min-width: 138px;
    }
    .gest-action-badge {
        border-radius: 50rem;
        padding: .35rem .6rem;
        font-weight: 600;
        font-size: .75rem;
    }
    .gest-dropdown-toggle {
        min-width: 120px;
        border-radius: .25rem;
        border: 1px solid #ced4da;
        background: #fff;
        color: #495057;
        font-weight: 600;
        box-shadow: none;
    }
    .gest-dropdown-toggle:hover,
    .gest-dropdown-toggle:focus {
        background: #f8f9fa;
        color: #495057;
        box-shadow: none;
    }
    .gest-dropdown-menu {
        border-radius: .35rem;
        border: 1px solid rgba(0,0,0,.125);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        padding: .35rem;
        min-width: 210px;
    }
    .gest-dropdown-menu .dropdown-item {
        border-radius: .25rem;
        padding: .5rem .75rem;
        font-weight: 500;
        color: #495057;
    }
    .gest-dropdown-menu .dropdown-item:hover {
        background: #f4f6f9;
    }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border-radius: .25rem;
        border: 1px solid #ced4da;
        min-height: calc(2.25rem + 2px);
    }
    .dataTables_wrapper .dataTables_filter input {
        padding: .375rem .75rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: .25rem !important;
        margin: 0 2px;
    }
    .dataTables_info,
    .dataTables_length label,
    .dataTables_filter label {
        color: #6c757d;
        font-weight: 500;
    }
    .designer-modal {
        border-radius: 18px;
        overflow: hidden;
    }
    .designer-dialog {
        max-width: 96vw;
    }
    .designer-hero {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        padding: 18px 20px;
        border-radius: 16px;
        background: linear-gradient(135deg, #ecfeff, #f0fdf4 55%, #fff7ed);
        border: 1px solid #dbeafe;
    }
    .designer-eyebrow {
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #0f766e;
    }
    .designer-hero-title {
        color: #0f172a;
        font-weight: 700;
    }
    .designer-chip-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-start;
    }
    .designer-chip {
        background: rgba(255,255,255,.9);
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .82rem;
        color: #334155;
    }
    .designer-panel {
        border: 1px solid #dbe5f1;
        border-radius: 16px;
        padding: 16px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15,23,42,.05);
    }
    .designer-panel-title {
        font-weight: 700;
        color: #0f172a;
    }
    .designer-selection-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        background: linear-gradient(180deg, #f8fafc, #ffffff);
    }
    .designer-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 110px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #0f766e;
        color: #fff;
        font-size: .82rem;
        font-weight: 700;
    }
    .designer-checklist {
        max-height: 250px;
        overflow: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fafcff;
        padding: 10px;
    }
    .designer-check-item {
        display: flex;
        align-items: center;
        padding: 6px 4px;
        border-bottom: 1px dashed #e5e7eb;
    }
    .designer-check-item:last-child {
        border-bottom: none;
    }
    .designer-check-item label {
        margin: 0 0 0 8px;
        font-weight: 500;
    }
    .designer-preview-wrap {
        max-height: 430px;
        overflow: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    .designer-selected-columns {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        max-height: 112px;
        overflow: auto;
        padding-right: 4px;
    }
    .designer-selected-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        color: #0f172a;
        cursor: move;
    }
    .designer-selected-item .btn {
        border-radius: 999px;
    }
    .designer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.34);
        z-index: 2200;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .designer-overlay-card {
        background: #fff;
        padding: 16px 20px;
        border-radius: 14px;
        box-shadow: 0 16px 32px rgba(0,0,0,.18);
        max-width: 380px;
        text-align: center;
    }
    @media (max-width: 991.98px) {
        .designer-dialog {
            max-width: calc(100vw - 1rem);
            margin: .5rem;
        }
        .designer-preview-wrap {
            max-height: 320px;
        }
        .gest-action-menu {
            align-items: stretch;
        }
        .gest-dropdown-toggle {
            width: 100%;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(function() {
    const reportModules = {
        tipo1: {
            title: 'Diseñador de reporte Tipo 2',
            eyebrow: 'Tipo 2',
            heroTitle: 'Construye tu reporte de gestantes Tipo 2',
            heroText: 'Usa el rango de fechas actual, organiza las variables y valida el resultado antes de descargar.',
            previewUrl: "{{ route('ges_tipo1.report.preview') }}",
            exportUrl: "{{ route('ges_tipo1.report.export') }}",
            columns: @json($tipo1ReportColumns ?? []),
            defaults: @json($tipo1DefaultReportColumns ?? []),
        },
        tipo3: {
            title: 'Diseñador de reporte Tipo 3',
            eyebrow: 'Tipo 3',
            heroTitle: 'Construye tu reporte de atención, monitoreo y seguimiento',
            heroText: 'Selecciona el rango, deja solo las columnas que necesitas y revisa una vista previa limpia.',
            previewUrl: "{{ route('ges_tipo3.report.preview') }}",
            exportUrl: "{{ route('ges_tipo3.report.export') }}",
            columns: @json($tipo3ReportColumns ?? []),
            defaults: @json($tipo3DefaultReportColumns ?? []),
        }
    };

    let activeModule = 'tipo1';
    let selectedColumns = [];
    const overlay = $('#reportLoadingOverlay');
    const overlayText = $('#reportLoadingText');
    const overlayHint = $('#reportLoadingHint');

    function setLoading(on, text, hint) {
        if (text) overlayText.text(text);
        if (hint) overlayHint.text(hint);
        overlay.toggleClass('d-none', !on);
    }

    function beginDownloadLoading(format) {
        const hint = format === 'xlsx'
            ? 'Excel XLSX requiere más procesamiento. La descarga puede tardar un poco más.'
            : 'El formato seleccionado es ligero y debería descargarse más rápido.';
        setLoading(true, 'Preparando descarga del reporte...', hint);
        window.__gestantesDownloadStarted = true;
        window.__gestantesDownloadSafety = window.setTimeout(function () {
            setLoading(false);
            window.__gestantesDownloadStarted = false;
        }, 90000);
    }

    function currentConfig() {
        return reportModules[activeModule];
    }

    function renderChecklist() {
        const config = currentConfig();
        const wrap = $('#designerColumnsChecklist');
        wrap.empty();
        Object.keys(config.columns).forEach(key => {
            const checked = selectedColumns.includes(key) ? 'checked' : '';
            wrap.append(`
                <div class="designer-check-item">
                    <input type="checkbox" class="designer-col-toggle" id="designer_col_${key}" data-key="${key}" ${checked}>
                    <label for="designer_col_${key}">${config.columns[key].label}</label>
                </div>
            `);
        });
    }

    function renderSelectedColumns() {
        const config = currentConfig();
        const list = $('#designerSelectedColumns');
        list.empty();
        $('#designerSelectedMeta').text(`${selectedColumns.length} columnas`);
        selectedColumns.forEach(key => {
            if (!config.columns[key]) return;
            list.append(`<div class="designer-selected-item" data-key="${key}"><span><i class="fas fa-grip-vertical text-muted mr-1"></i>${config.columns[key].label}</span><button class="btn btn-xs btn-outline-danger btn-remove-designer-col" type="button" data-key="${key}"><i class="fas fa-times"></i></button></div>`);
        });
    }

    function renderPreview(headings, cols, rows) {
        const thead = $('#designerPreviewTable thead');
        const tbody = $('#designerPreviewTable tbody');

        if (!cols.length) {
            thead.html('');
            tbody.html('<tr><td class="text-center text-muted">Sin columnas seleccionadas.</td></tr>');
            return;
        }

        thead.html('<tr>' + cols.map(col => `<th>${headings[col] || col}</th>`).join('') + '</tr>');

        if (!rows.length) {
            tbody.html('<tr><td colspan="' + cols.length + '" class="text-center text-muted">Sin datos para el rango seleccionado.</td></tr>');
            return;
        }

        tbody.html(rows.map(row => '<tr>' + cols.map(col => `<td>${row[col] ?? ''}</td>`).join('') + '</tr>').join(''));
    }

    function syncDesignerMeta() {
        const config = currentConfig();
        $('#designerModalTitle').html(`<i class="fas fa-drafting-compass mr-1"></i> ${config.title}`);
        $('#designerEyebrow').text(config.eyebrow);
        $('#designerHeroTitle').text(config.heroTitle);
        $('#designerHeroText').text(config.heroText);
    }

    function openDesigner(moduleKey) {
        activeModule = moduleKey;
        selectedColumns = [...currentConfig().defaults];
        syncDesignerMeta();
        renderChecklist();
        renderSelectedColumns();
        renderPreview({}, [], []);
        $('#designerFormat').val('csv');
        $('#reportDesignerModalGestantes').modal('show');
    }

    function buildRequestBody(includeFormat) {
        const body = new URLSearchParams();
        body.append('from', $('#designerFrom').val() || '');
        body.append('to', $('#designerTo').val() || '');
        selectedColumns.forEach(column => body.append('columns[]', column));
        if (includeFormat) {
            body.append('format', $('#designerFormat').val());
        }
        return body.toString();
    }

    $('.btn-open-designer').on('click', function () {
        openDesigner($(this).data('module'));
    });

    $('#designerColumnsChecklist').on('change', '.designer-col-toggle', function () {
        const key = $(this).data('key');
        if (this.checked) {
            if (!selectedColumns.includes(key)) selectedColumns.push(key);
        } else {
            selectedColumns = selectedColumns.filter(item => item !== key);
        }
        renderSelectedColumns();
    });

    $('#designerSelectedColumns').on('click', '.btn-remove-designer-col', function () {
        const key = $(this).data('key');
        selectedColumns = selectedColumns.filter(item => item !== key);
        $('#designer_col_' + key).prop('checked', false);
        renderSelectedColumns();
    });

    if (typeof Sortable !== 'undefined') {
        new Sortable(document.getElementById('designerSelectedColumns'), {
            animation: 120,
            onEnd: function () {
                const order = [];
                $('#designerSelectedColumns [data-key]').each(function () {
                    order.push($(this).data('key'));
                });
                selectedColumns = order;
            }
        });
    }

    $('#btnPreviewGestantes').on('click', async function () {
        if (!$('#designerFrom').val() || !$('#designerTo').val()) {
            Swal.fire({ icon: 'warning', title: 'Rango incompleto', text: 'Selecciona fecha inicio y fecha fin.' });
            return;
        }
        if (!selectedColumns.length) {
            Swal.fire({ icon: 'warning', title: 'Selecciona columnas', text: 'Debes elegir al menos una variable.' });
            return;
        }

        setLoading(true, 'Construyendo vista previa...', 'Estamos preparando una muestra clara del reporte.');
        try {
            const res = await fetch(currentConfig().previewUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: buildRequestBody(false)
            });
            const json = await res.json();
            if (!json.ok) throw new Error('No se pudo generar la vista previa');
            renderPreview(json.headings || {}, json.columns || [], json.rows || []);
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo construir la vista previa' });
        } finally {
            setLoading(false);
        }
    });

    $('#btnExportGestantes').on('click', function () {
        if (!$('#designerFrom').val() || !$('#designerTo').val()) {
            Swal.fire({ icon: 'warning', title: 'Rango incompleto', text: 'Selecciona fecha inicio y fecha fin.' });
            return;
        }
        if (!selectedColumns.length) {
            Swal.fire({ icon: 'warning', title: 'Selecciona columnas', text: 'Debes elegir al menos una variable.' });
            return;
        }

        beginDownloadLoading($('#designerFormat').val());
        const form = $('#designedExportFormGestantes');
        form.attr('action', currentConfig().exportUrl);
        form.find('.js-dynamic-field').remove();

        [
            { name: 'from', value: $('#designerFrom').val() },
            { name: 'to', value: $('#designerTo').val() },
            { name: 'format', value: $('#designerFormat').val() },
        ].forEach(field => {
            form.append($('<input>', { type: 'hidden', name: field.name, value: field.value, class: 'js-dynamic-field' }));
        });

        selectedColumns.forEach(column => {
            form.append($('<input>', { type: 'hidden', name: 'columns[]', value: column, class: 'js-dynamic-field' }));
        });

        document.getElementById('designedExportFormGestantes').target = 'downloadFrameGestantes';
        document.getElementById('designedExportFormGestantes').submit();
    });

    document.getElementById('downloadFrameGestantes').addEventListener('load', function () {
        if (window.__gestantesDownloadStarted) {
            window.clearTimeout(window.__gestantesDownloadSafety);
            setLoading(false);
            window.__gestantesDownloadStarted = false;
        }
    });

    window.addEventListener('focus', function () {
        if (window.__gestantesDownloadStarted) {
            window.clearTimeout(window.__gestantesDownloadSafety);
            setLoading(false);
            window.__gestantesDownloadStarted = false;
        }
    });

    $('#btnCloseReportOverlay').on('click', function () {
        window.clearTimeout(window.__gestantesDownloadSafety);
        setLoading(false);
        window.__gestantesDownloadStarted = false;
    });

    $('#gestantes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('ges_tipo1.index') }}",
        columns: [
            { data: 'full_name', name: 'full_name' },
            { data: 'tipo_de_identificacion_de_la_usuaria', name: 'tipo_de_identificacion_de_la_usuaria' },
            { data: 'no_id_del_usuario', name: 'no_id_del_usuario' },
            { data: 'numero_carnet', name: 'numero_carnet' },
            { data: 'fecha_de_nacimiento', name: 'fecha_de_nacimiento' },
            { data: 'fecha_probable_de_parto', name: 'fecha_probable_de_parto' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
        ],
        order: [[0, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });

    const today = new Date().toISOString().slice(0, 10);
    const firstDay = new Date();
    firstDay.setDate(1);
    $('#designerFrom').val(firstDay.toISOString().slice(0, 10));
    $('#designerTo').val(today);
});
</script>
@stop
