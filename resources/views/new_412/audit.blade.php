@extends('adminlte::page')

@section('title', 'Auditoria Asignaciones 412')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="m-0">Auditoria de Asignaciones 412</h1>
        <small class="text-muted">Trazabilidad detallada de asignacion y reasignacion de casos</small>
    </div>
    <a href="{{ route('import-excel-form') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a 412
    </a>
</div>
@stop

@section('content')
<div class="card card-outline card-info">
    <div class="card-body">
        <form id="auditFiltersForm" class="row">
            <div class="col-md-2">
                <label>Desde</label>
                <input type="date" name="from" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label>Hasta</label>
                <input type="date" name="to" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label>Usuario que asigna</label>
                <select name="performed_by_user_id" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Asignado a</label>
                <select name="new_assigned_user_id" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Tipo movimiento</label>
                <select name="action_type" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    <option value="asignacion">Asignacion</option>
                    <option value="reasignacion">Reasignacion</option>
                    <option value="sin_cambio">Sin cambio</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Identificacion</label>
                <input type="text" name="numero_identificacion" class="form-control form-control-sm" placeholder="Documento">
            </div>
            <div class="col-md-3 mt-2">
                <label>Municipio</label>
                <input type="text" name="municipio" class="form-control form-control-sm" placeholder="Municipio">
            </div>
            <div class="col-md-9 mt-4 d-flex align-items-end justify-content-end">
                <button type="submit" class="btn btn-info btn-sm mr-2">
                    <i class="fas fa-search mr-1"></i> Aplicar filtros
                </button>
                <button type="button" id="btnResetAuditFilters" class="btn btn-outline-secondary btn-sm mr-2">
                    Limpiar
                </button>
                <a href="#" id="btnAuditExportXlsx" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                </a>
                <a href="#" id="btnAuditExportCsv" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-secondary">
    <div class="card-body">
        <div class="table-responsive">
            <table id="audit412-table" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Movimiento</th>
                        <th>ID 412</th>
                        <th>Identificacion</th>
                        <th>Paciente</th>
                        <th>Municipio</th>
                        <th>Asignado Antes</th>
                        <th>Asignado Nuevo</th>
                        <th>Usuario que asigna</th>
                        <th>IP Cliente</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script>
$(function () {
    function getFilters() {
        const data = {};
        $('#auditFiltersForm').serializeArray().forEach(function (i) {
            data[i.name] = i.value;
        });
        return data;
    }

    const dt = $('#audit412-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route("import-excel.audit.data") }}',
            data: function (d) {
                Object.assign(d, getFilters());
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'created_at', name: 'a.created_at' },
            { data: 'action_type', name: 'a.action_type' },
            { data: 'cargue412_id', name: 'a.cargue412_id' },
            { data: 'numero_identificacion', name: 'a.numero_identificacion' },
            { data: 'paciente_nombre', name: 'a.paciente_nombre' },
            { data: 'municipio', name: 'a.municipio' },
            { data: 'old_assigned_name', name: 'a.old_assigned_name', defaultContent: '' },
            { data: 'new_assigned_name', name: 'a.new_assigned_name', defaultContent: '' },
            { data: 'performed_by_name', name: 'u.name', defaultContent: '' },
            { data: 'ip_address', name: 'a.ip_address', defaultContent: '' },
        ]
    });

    $('#auditFiltersForm').on('submit', function (e) {
        e.preventDefault();
        dt.ajax.reload();
    });

    $('#btnResetAuditFilters').on('click', function () {
        $('#auditFiltersForm')[0].reset();
        dt.ajax.reload();
    });

    function setExportHref(format) {
        const query = new URLSearchParams(getFilters());
        query.set('format', format);
        return '{{ route("import-excel.audit.export") }}' + '?' + query.toString();
    }

    $('#btnAuditExportXlsx').on('click', function (e) {
        e.preventDefault();
        window.location.href = setExportHref('xlsx');
    });

    $('#btnAuditExportCsv').on('click', function (e) {
        e.preventDefault();
        window.location.href = setExportHref('csv');
    });
});
</script>
@stop

