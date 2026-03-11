@extends('adminlte::page')

@section('title', 'Maestro SIV 549')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center mb-2">
        <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="msiv-logo mr-2">
        <div>
            <h1 class="m-0 msiv-title">Maestro SIV 549</h1>
            <small class="text-muted">Asignacion de casos, filtros operativos y reportes dinamicos</small>
        </div>
    </div>
    <div class="mb-2 d-flex flex-wrap">
        <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-1" id="btnOpenReportDesigner549">
            <i class="fas fa-sliders-h mr-1"></i> Disenar reporte
        </button>
        <button type="button" class="btn btn-outline-success btn-sm mb-1" id="btnClassicExport549">
            <i class="fas fa-file-excel mr-1"></i> Exportar clasico
        </button>
    </div>
</div>
@stop

@section('content')
<div id="loadingOverlay549" class="msiv-overlay d-none">
    <div class="msiv-overlay-card">
        <i class="fas fa-circle-notch fa-spin mr-2"></i>
        <span id="loadingOverlayText549">Procesando...</span>
        <small id="loadingOverlayHint549" class="d-block text-muted mt-2">CSV suele ser mas rapido. Excel (.xlsx) tarda mas en generarse.</small>
        <div class="mt-3 text-center">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCloseLoadingOverlay549">Cerrar aviso</button>
        </div>
    </div>
</div>

<form id="classicExportForm549" method="GET" action="{{ route('reportes.maestrosiv549.export') }}" class="d-none"></form>
<form id="designedExportForm549" method="POST" action="{{ route('maestrosiv549.report.export') }}" class="d-none">
    @csrf
</form>
<iframe name="downloadFrame549" id="downloadFrame549" class="d-none"></iframe>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h4>{{ number_format($stats['total'] ?? 0) }}</h4>
                <p>Registros disponibles</p>
            </div>
            <div class="icon"><i class="fas fa-database"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success msiv-kpi-click" data-summary-mode="asignados">
            <div class="inner">
                <h4>{{ number_format($stats['asignados'] ?? 0) }}</h4>
                <p>Casos asignados</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning msiv-kpi-click" data-summary-mode="sin_seguimientos">
            <div class="inner">
                <h4>{{ number_format($stats['sin_seguimientos'] ?? 0) }}</h4>
                <p>Sin seguimientos</p>
            </div>
            <div class="icon"><i class="fas fa-user-clock"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h4>{{ number_format($stats['semanas'] ?? 0) }}</h4>
                <p>Semanas reportadas</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-week"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-warning msiv-card d-none" id="summaryCard549">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0" id="summaryTitle549"><i class="fas fa-list mr-1"></i> Listado</h3>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCloseSummary549">Cerrar</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="summaryTable549" class="table table-sm table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Tipo ID</th>
                        <th>Numero identificacion</th>
                        <th>Nombre completo</th>
                        <th>Fecha asignacion</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card card-outline card-info msiv-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros rapidos</h3>
    </div>
    <div class="card-body">
        <form id="filtersForm549">
            <div class="row">
                <div class="col-md-2">
                    <label>Anio</label>
                    <select name="year" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($filterOptions['years'] ?? []) as $year)
                            <option value="{{ $year }}" {{ (string) ($defaultYear ?? now()->year) === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Semana</label>
                    <select name="semana" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach(($filterOptions['weeks'] ?? []) as $week)
                            <option value="{{ $week }}">{{ $week }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tipo ID</label>
                    <select name="tip_ide_" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($filterOptions['tiposId'] ?? []) as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Sexo</label>
                    <select name="sexo_" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($filterOptions['sexos'] ?? []) as $sexo)
                            <option value="{{ $sexo }}">{{ $sexo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date" name="fec_desde" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date" name="fec_hasta" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3">
                    <label>Documento</label>
                    <input type="text" name="num_ide_" class="form-control form-control-sm" placeholder="Numero de identificacion">
                </div>
                <div class="col-md-3">
                    <label>Busqueda rapida</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Documento, nombre, telefono, evento...">
                </div>
                <div class="col-md-3">
                    <label>Evento</label>
                    <select name="nom_eve" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(($filterOptions['eventos'] ?? []) as $evento)
                            <option value="{{ $evento }}">{{ $evento }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-info btn-sm mr-2 btn-block">
                        <i class="fas fa-search mr-1"></i> Aplicar
                    </button>
                    <button type="button" id="btnResetFilters549" class="btn btn-outline-secondary btn-sm btn-block">
                        Limpiar
                    </button>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-link btn-sm px-0" data-toggle="collapse" data-target="#advancedFilters549" aria-expanded="false" aria-controls="advancedFilters549">
                    <i class="fas fa-chevron-down mr-1"></i> Mostrar filtros avanzados
                </button>
            </div>

            <div class="collapse mt-2" id="advancedFilters549">
                <div class="msiv-advanced-panel">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Area</label>
                            <select name="area_" class="form-control form-control-sm">
                                <option value="">Todas</option>
                                @foreach(($filterOptions['areas'] ?? []) as $area)
                                    <option value="{{ $area }}">{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Asignado</label>
                            <select name="asignado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Edad desde</label>
                            <input type="number" min="0" name="edad_desde" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label>Edad hasta</label>
                            <input type="number" min="0" name="edad_hasta" class="form-control form-control-sm" placeholder="99">
                        </div>
                        <div class="col-md-2">
                            <label>Sem. gest. desde</label>
                            <input type="number" min="0" name="sem_ges_desde" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label>Sem. gest. hasta</label>
                            <input type="number" min="0" name="sem_ges_hasta" class="form-control form-control-sm" placeholder="42">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label>Municipio residencia</label>
                            <select name="nmun_resi" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(($filterOptions['municipiosResi'] ?? []) as $municipio)
                                    <option value="{{ $municipio }}">{{ $municipio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Municipio notificacion</label>
                            <select name="nmun_notif" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(($filterOptions['municipiosNotif'] ?? []) as $municipio)
                                    <option value="{{ $municipio }}">{{ $municipio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>UPGD notificadora</label>
                            <select name="nom_upgd" class="form-control form-control-sm">
                                <option value="">Todas</option>
                                @foreach(($filterOptions['upgd'] ?? []) as $upgd)
                                    <option value="{{ $upgd }}">{{ $upgd }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Telefono</label>
                            <input type="text" name="telefono_" class="form-control form-control-sm" placeholder="Telefono">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label>Ocupacion</label>
                            <input type="text" name="ocupacion_" class="form-control form-control-sm" placeholder="Ocupacion">
                        </div>
                        <div class="col-md-3">
                            <label>Direccion</label>
                            <input type="text" name="dir_res_" class="form-control form-control-sm" placeholder="Direccion residencia">
                        </div>
                        <div class="col-md-2">
                            <label>Tipo caso</label>
                            <select name="tip_cas_" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(($filterOptions['tiposCaso'] ?? []) as $tipoCaso)
                                    <option value="{{ $tipoCaso }}">{{ $tipoCaso }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Aseguramiento</label>
                            <select name="tip_ss_" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(($filterOptions['aseguramientos'] ?? []) as $aseguramiento)
                                    <option value="{{ $aseguramiento }}">{{ $aseguramiento }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Grupo etnico</label>
                            <select name="nom_grupo_" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(($filterOptions['etnias'] ?? []) as $etnia)
                                    <option value="{{ $etnia }}">{{ $etnia }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label>Primer nombre</label>
                            <input type="text" name="pri_nom_" class="form-control form-control-sm" placeholder="Primer nombre">
                        </div>
                        <div class="col-md-3">
                            <label>Primer apellido</label>
                            <input type="text" name="pri_ape_" class="form-control form-control-sm" placeholder="Primer apellido">
                        </div>
                        <div class="col-md-3">
                            <label>Codigo evento</label>
                            <input type="text" name="cod_eve" class="form-control form-control-sm" placeholder="549">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-secondary msiv-card">
    <div class="card-body">
        <div id="tableLoading549" class="msiv-table-loading d-none">
            <span class="spinner-border spinner-border-sm text-info mr-2" role="status" aria-hidden="true"></span>
            <span id="tableLoadingText549">Cargando registros...</span>
        </div>
        <div class="table-responsive">
            <table id="maestrosiv549-table" class="table table-hover table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Tipo ID</th>
                        <th>Numero ID</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Fecha notif.</th>
                        <th>Semana</th>
                        <th>Anio</th>
                        <th>Municipio res.</th>
                        <th>UPGD</th>
                        <th>Evento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="reportDesignerModal549" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-drafting-compass mr-1"></i> Disenador de reporte SIV 549</h5>
                <button type="button" class="close" id="btnCloseReportDesigner549" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6 class="mb-2">Variables disponibles</h6>
                        <div id="columnsChecklist549" class="msiv-checklist"></div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Orden del reporte</h6>
                            <small class="text-muted">Arrastra para reorganizar columnas</small>
                        </div>
                        <ul id="selectedColumnsList549" class="list-group mb-2"></ul>
                        <div class="d-flex mb-2">
                            <select id="reportFormat549" class="form-control form-control-sm mr-2" style="max-width:160px;">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="csv">CSV</option>
                            </select>
                            <button id="btnPreviewReport549" type="button" class="btn btn-outline-primary btn-sm mr-2">
                                <i class="fas fa-eye mr-1"></i> Vista previa
                            </button>
                            <button id="btnExportDesigned549" type="button" class="btn btn-success btn-sm">
                                <i class="fas fa-file-export mr-1"></i> Exportar
                            </button>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Sugerencia: `CSV` descarga mas rapido. `Excel (.xlsx)` tarda mas porque el archivo requiere mas procesamiento.</small>
                        </div>
                        <div class="table-responsive msiv-preview-wrap">
                            <table class="table table-sm table-bordered table-striped mb-0" id="previewTable549">
                                <thead></thead>
                                <tbody>
                                    <tr><td class="text-center text-muted">Aun sin vista previa.</td></tr>
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
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
.msiv-logo{width:44px;height:44px;object-fit:contain;border-radius:10px;background:#fff;padding:3px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.msiv-title{font-weight:700;color:#1f2937;letter-spacing:.3px}
.msiv-card{border-radius:14px;overflow:hidden;box-shadow:0 10px 24px rgba(15,23,42,.06)}
.msiv-advanced-panel{border:1px solid #dbeafe;border-radius:14px;padding:16px;background:linear-gradient(180deg,#f8fbff,#f3f8ff)}
.msiv-checklist{max-height:420px;overflow:auto;border:1px solid #e5e7eb;border-radius:10px;padding:10px;background:#fafafa}
.msiv-check-item{display:flex;align-items:center;padding:6px 4px;border-bottom:1px dashed #e5e7eb}
.msiv-check-item:last-child{border-bottom:none}
.msiv-check-item label{margin:0 0 0 8px;font-weight:500}
.msiv-preview-wrap{max-height:430px;overflow:auto;border:1px solid #e5e7eb;border-radius:10px}
#selectedColumnsList549 .list-group-item{cursor:move}
.msiv-overlay{position:fixed;inset:0;background:rgba(15,23,42,.32);z-index:2100;display:flex;align-items:center;justify-content:center}
.msiv-overlay-card{background:#fff;padding:14px 18px;border-radius:12px;box-shadow:0 12px 24px rgba(0,0,0,.2)}
.msiv-table-loading{display:flex;align-items:center;justify-content:flex-end;margin-bottom:10px;color:#6c757d;font-size:.92rem}
.table-hover tbody tr:hover{background-color:rgba(23,162,184,0.08)}
tr.row-asignado, tr.row-asignado td{background:#fff8db !important}
.acciones-flex{display:flex;align-items:center;justify-content:center;gap:.45rem;min-width:70px}
.badge-checklist{background:#22c55e;color:#fff;border-radius:50%;width:2.1rem;height:2.1rem;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 8px rgba(34,197,94,.25);border:2px solid #ecfdf5}
.btn-asignar{background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff !important;border:none;border-radius:50%;width:2.1rem;height:2.1rem;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 12px rgba(37,99,235,.25);transition:all .2s ease}
.btn-asignar:hover,.btn-asignar:focus{transform:scale(1.06);color:#fff;box-shadow:0 8px 24px rgba(37,99,235,.22);text-decoration:none}
.dataTables_filter input{border-radius:20px;border:1px solid #ced4da;padding:6px 12px}
.msiv-kpi-click{cursor:pointer;transition:transform .18s ease, box-shadow .18s ease}
.msiv-kpi-click:hover{transform:translateY(-3px);box-shadow:0 12px 24px rgba(0,0,0,.14)}
</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(function () {
    const columnsCatalog = @json($reportColumns ?? []);
    let selectedColumns = @json($defaultReportColumns ?? []);
    let summaryMode = 'asignados';
    const defaultYear549 = @json((string) ($defaultYear ?? now()->year));

    const overlay = $('#loadingOverlay549');
    const overlayText = $('#loadingOverlayText549');
    const overlayHint = $('#loadingOverlayHint549');
    const tableLoading = $('#tableLoading549');
    const tableLoadingText = $('#tableLoadingText549');

    function setReportLoading(on, text) {
        if (text) {
            overlayText.text(text);
        }
        overlay.toggleClass('d-none', !on);
    }

    function setTableLoading(on, text) {
        if (text) {
            tableLoadingText.text(text);
        }
        tableLoading.toggleClass('d-none', !on);
    }

    function beginDownloadLoading(kind) {
        if (kind === 'csv') {
            overlayHint.text('CSV suele salir mas rapido. Espera unos segundos mientras se genera el archivo.');
            setReportLoading(true, 'Preparando descarga CSV...');
        } else {
            overlayHint.text('Excel (.xlsx) tarda mas que CSV porque requiere mas procesamiento y armado del archivo.');
            setReportLoading(true, 'Preparando descarga Excel, por favor espera...');
        }

        window.__downloadStarted549 = true;
        window.__downloadOverlaySafety549 = window.setTimeout(function () {
            setReportLoading(false);
            window.__downloadStarted549 = false;
        }, 90000);
    }

    function getFilters() {
        const data = {};
        $('#filtersForm549').serializeArray().forEach(item => {
            data[item.name] = item.value;
        });
        return data;
    }

    function syncFormFields(formSelector, extraFields = {}) {
        const form = $(formSelector);
        form.find('input[type="hidden"].js-dynamic-field').remove();

        const filters = getFilters();
        Object.keys(filters).forEach(key => {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: filters[key] || '',
                class: 'js-dynamic-field'
            }));
        });

        Object.keys(extraFields).forEach(key => {
            const value = extraFields[key];
            if (Array.isArray(value)) {
                value.forEach(item => {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: key,
                        value: item,
                        class: 'js-dynamic-field'
                    }));
                });
                return;
            }

            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: value,
                class: 'js-dynamic-field'
            }));
        });
    }

    const table = $('#maestrosiv549-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        stateSave: false,
        searching: true,
        searchDelay: 300,
        pageLength: 25,
        ajax: {
            url: '{{ route("maestrosiv549.data") }}',
            data: function (d) {
                Object.assign(d, getFilters());
            }
        },
        order: [[5, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_',
            info: 'Mostrando _START_ a _END_ de _TOTAL_',
            infoEmpty: 'Mostrando 0 a 0 de 0',
            infoFiltered: '(filtrado de _MAX_)',
            paginate: { first: 'Primero', last: 'Ultimo', next: 'Siguiente', previous: 'Anterior' }
        },
        columns: [
            { data: 'tip_ide_', name: 'tip_ide_' },
            { data: 'num_ide_', name: 'num_ide_' },
            { data: 'nombre_completo', name: 'nombre_completo', orderable: false },
            { data: 'edad_', name: 'edad_' },
            { data: 'sexo_', name: 'sexo_' },
            { data: 'fec_not', name: 'fec_not' },
            { data: 'semana', name: 'semana' },
            { data: 'year', name: 'year' },
            { data: 'nmun_resi', name: 'nmun_resi' },
            { data: 'nom_upgd', name: 'nom_upgd' },
            { data: 'nom_eve', name: 'nom_eve' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    const summaryTable = $('#summaryTable549').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        pageLength: 10,
        ajax: {
            url: '{{ route("maestrosiv549.summary.data") }}',
            data: function (d) {
                d.mode = summaryMode;
            }
        },
        order: [[3, 'desc']],
        language: {
            processing: 'Cargando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_',
            info: 'Mostrando _START_ a _END_ de _TOTAL_',
            infoEmpty: 'Sin registros',
            paginate: { first: 'Primero', last: 'Ultimo', next: 'Siguiente', previous: 'Anterior' }
        },
        columns: [
            { data: 'tip_ide_', name: 'tip_ide_' },
            { data: 'num_ide_', name: 'num_ide_' },
            { data: 'nombre_completo', name: 'nombre_completo', orderable: false },
            { data: 'fecha_asignacion', name: 'fecha_asignacion', orderable: false },
            { data: 'estado_resumen', name: 'estado_resumen', orderable: false, searchable: false }
        ]
    });

    $('#filtersForm549').on('submit', function (e) {
        e.preventDefault();
        setTableLoading(true, 'Aplicando filtros...');
        table.ajax.reload();
    });

    $('#btnResetFilters549').on('click', function () {
        $('#filtersForm549')[0].reset();
        $('#filtersForm549 select[name="year"]').val(defaultYear549);
        setTableLoading(true, 'Recargando datos...');
        table.ajax.reload();
    });

    $('.msiv-kpi-click').on('click', function () {
        summaryMode = $(this).data('summary-mode');
        const title = summaryMode === 'sin_seguimientos'
            ? '<i class="fas fa-user-clock mr-1"></i> Casos asignados sin seguimiento'
            : '<i class="fas fa-user-check mr-1"></i> Casos asignados';
        $('#summaryTitle549').html(title);
        $('#summaryCard549').removeClass('d-none');
        summaryTable.ajax.reload();
        $('html, body').animate({ scrollTop: $('#summaryCard549').offset().top - 20 }, 300);
    });

    $('#btnCloseSummary549').on('click', function () {
        $('#summaryCard549').addClass('d-none');
    });

    $('#maestrosiv549-table').on('preXhr.dt', function () {
        setTableLoading(true, 'Cargando registros...');
    });

    $('#maestrosiv549-table').on('xhr.dt error.dt', function () {
        setTableLoading(false);
    });

    function renderColumnsChecklist() {
        const wrap = $('#columnsChecklist549');
        wrap.empty();
        Object.keys(columnsCatalog).forEach(key => {
            const checked = selectedColumns.includes(key) ? 'checked' : '';
            wrap.append(`
                <div class="msiv-check-item">
                    <input type="checkbox" class="col-toggle-549" id="col_549_${key}" data-key="${key}" ${checked}>
                    <label for="col_549_${key}">${columnsCatalog[key].label}</label>
                </div>
            `);
        });
    }

    function renderSelectedList() {
        const ul = $('#selectedColumnsList549');
        ul.empty();
        selectedColumns.forEach(key => {
            if (!columnsCatalog[key]) return;
            ul.append(`<li class="list-group-item d-flex justify-content-between align-items-center" data-key="${key}"><span><i class="fas fa-grip-vertical text-muted mr-2"></i>${columnsCatalog[key].label}</span><button class="btn btn-xs btn-outline-danger btn-remove-col-549" type="button" data-key="${key}"><i class="fas fa-times"></i></button></li>`);
        });
    }

    $('#columnsChecklist549').on('change', '.col-toggle-549', function () {
        const key = $(this).data('key');
        if (this.checked) {
            if (!selectedColumns.includes(key)) {
                selectedColumns.push(key);
            }
        } else {
            selectedColumns = selectedColumns.filter(item => item !== key);
        }
        renderSelectedList();
    });

    $('#selectedColumnsList549').on('click', '.btn-remove-col-549', function () {
        const key = $(this).data('key');
        selectedColumns = selectedColumns.filter(item => item !== key);
        $('#col_549_' + key).prop('checked', false);
        renderSelectedList();
    });

    if (typeof Sortable !== 'undefined') {
        new Sortable(document.getElementById('selectedColumnsList549'), {
            animation: 120,
            onEnd: function () {
                const order = [];
                $('#selectedColumnsList549 li').each(function () {
                    order.push($(this).data('key'));
                });
                selectedColumns = order;
            }
        });
    }

    function buildBody(includeFormat) {
        const body = new URLSearchParams();
        const filters = getFilters();
        Object.keys(filters).forEach(key => body.append(key, filters[key] || ''));
        selectedColumns.forEach(column => body.append('columns[]', column));
        if (includeFormat) {
            body.append('format', $('#reportFormat549').val());
        }
        return body.toString();
    }

    function renderPreview(headings, cols, rows) {
        const thead = $('#previewTable549 thead');
        const tbody = $('#previewTable549 tbody');

        if (!cols.length) {
            thead.html('');
            tbody.html('<tr><td class="text-center text-muted">Sin columnas seleccionadas.</td></tr>');
            return;
        }

        thead.html('<tr>' + cols.map(col => `<th>${headings[col] || col}</th>`).join('') + '</tr>');

        if (!rows.length) {
            tbody.html('<tr><td colspan="' + cols.length + '" class="text-center text-muted">Sin datos para filtros actuales.</td></tr>');
            return;
        }

        tbody.html(rows.map(row => '<tr>' + cols.map(col => `<td>${row[col] ?? ''}</td>`).join('') + '</tr>').join(''));
    }

    $('#btnPreviewReport549').on('click', async function () {
        if (!selectedColumns.length) {
            Swal.fire({ icon: 'warning', title: 'Selecciona columnas', text: 'Debes elegir al menos una variable.' });
            return;
        }

        setReportLoading(true, 'Construyendo vista previa...');
        try {
            const res = await fetch('{{ route("maestrosiv549.report.preview") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: buildBody(false)
            });
            const json = await res.json();
            if (!json.ok) {
                throw new Error('No se pudo generar la vista previa');
            }
            renderPreview(json.headings || {}, json.columns || [], json.rows || []);
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Error en vista previa' });
        } finally {
            setReportLoading(false);
        }
    });

    $('#btnExportDesigned549').on('click', function () {
        if (!selectedColumns.length) {
            Swal.fire({ icon: 'warning', title: 'Selecciona columnas', text: 'Debes elegir al menos una variable.' });
            return;
        }

        beginDownloadLoading($('#reportFormat549').val());
        syncFormFields('#designedExportForm549', {
            'columns[]': selectedColumns,
            format: $('#reportFormat549').val()
        });
        document.getElementById('designedExportForm549').target = 'downloadFrame549';
        document.getElementById('designedExportForm549').submit();
    });

    $('#btnClassicExport549').on('click', function () {
        beginDownloadLoading('xlsx');
        syncFormFields('#classicExportForm549');
        document.getElementById('classicExportForm549').target = 'downloadFrame549';
        document.getElementById('classicExportForm549').submit();
    });

    document.getElementById('downloadFrame549').addEventListener('load', function () {
        if (window.__downloadStarted549) {
            window.clearTimeout(window.__downloadOverlaySafety549);
            setReportLoading(false);
            window.__downloadStarted549 = false;
        }
    });

    window.addEventListener('focus', function () {
        if (window.__downloadStarted549) {
            window.clearTimeout(window.__downloadOverlaySafety549);
            setReportLoading(false);
            window.__downloadStarted549 = false;
        }
    });

    $('#btnCloseLoadingOverlay549').on('click', function () {
        window.clearTimeout(window.__downloadOverlaySafety549);
        setReportLoading(false);
        window.__downloadStarted549 = false;
    });

    function toggleDesignerFallback(show) {
        const el = document.getElementById('reportDesignerModal549');
        if (!el) return;

        if (show) {
            el.style.display = 'block';
            el.classList.add('show');
            el.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
            if (!document.getElementById('modal-backdrop-549')) {
                const backdrop = document.createElement('div');
                backdrop.id = 'modal-backdrop-549';
                backdrop.className = 'modal-backdrop fade show';
                backdrop.addEventListener('click', function () { toggleDesignerFallback(false); });
                document.body.appendChild(backdrop);
            }
            return;
        }

        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        const backdrop = document.getElementById('modal-backdrop-549');
        if (backdrop) {
            backdrop.remove();
        }
    }

    function openDesigner() {
        renderColumnsChecklist();
        renderSelectedList();
        toggleDesignerFallback(true);
    }

    function closeDesigner() {
        toggleDesignerFallback(false);
    }

    window.openReportDesigner549 = openDesigner;
    window.closeReportDesigner549 = closeDesigner;

    document.getElementById('btnOpenReportDesigner549').addEventListener('click', function () {
        openDesigner();
    });

    document.getElementById('btnCloseReportDesigner549').addEventListener('click', function () {
        closeDesigner();
    });
});
</script>
@stop
