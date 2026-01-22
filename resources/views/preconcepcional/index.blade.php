{{-- resources/views/preconcepcional/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Preconcepcional')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="text-primary mb-0">
            <i class="fas fa-file-medical-alt mr-2"></i>Preconcepcional
        </h1>
        <small class="text-muted">Listado general (carga rápida con DataTables server-side)</small>
    </div>

    <div class="d-flex" style="gap:.5rem;">
        <a href="{{ route('preconcepcional.import') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-upload mr-1"></i> Importar Excel
        </a>
<a href="{{ route('preconcepcional.batches') }}" class="btn btn-outline-primary shadow-sm mr-2">
    <i class="fas fa-layer-group mr-1"></i> Lotes
</a>

        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary shadow-sm dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-file-export mr-1"></i> Exportar
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" id="btnExportCsv">
                    <i class="fas fa-file-csv mr-1 text-success"></i> CSV (lo filtrado)
                </a>
                <a class="dropdown-item" href="#" id="btnExportXlsx">
                    <i class="fas fa-file-excel mr-1 text-success"></i> Excel (lo filtrado)
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success shadow-sm">
        <i class="fas fa-check mr-1"></i>{{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning shadow-sm">
        <i class="fas fa-exclamation-triangle mr-1"></i>{{ session('warning') }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between">
            <span class="font-weight-bold text-secondary">
                <i class="fas fa-table mr-1"></i> Registros
            </span>

            <span class="badge badge-light border">
                <i class="fas fa-bolt text-warning mr-1"></i> Server-side
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla-precon" class="table table-striped table-hover table-bordered w-100">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Tipo Doc</th>
                        <th>Identificación</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Municipio</th>
                        <th>Riesgo</th>
                        <th style="width:90px;">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <small class="text-muted d-block mt-2">
            * Exporta lo que esté filtrado/buscado/ordenado en la tabla.
        </small>
    </div>
</div>
@stop

@section('js')
<script>
$(function () {

    const tabla = $('#tabla-precon').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,

        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
        order: [[0, 'desc']],

        ajax: "{{ route('preconcepcional.data') }}",

        columns: [
            { data: 'id', name: 'id' },
            { data: 'tipo_documento', name: 'tipo_documento' },
            { data: 'numero_identificacion', name: 'numero_identificacion' },
            { data: 'nombres', name: 'nombre_1', orderable: false, searchable: true },
            { data: 'apellidos', name: 'apellido_1', orderable: false, searchable: true },
            { data: 'municipio_residencia', name: 'municipio_residencia' },
            { data: 'riesgo_preconcepcional', name: 'riesgo_preconcepcional' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],

        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });

    function buildExportUrl(format){
        // Reutiliza EXACTO el request actual de DataTables: search, order, etc.
        const params = tabla.ajax.params();
        params.format = format; // csv | xlsx
        return "{{ route('preconcepcional.export') }}?" + $.param(params);
    }

    $('#btnExportCsv').on('click', function(e){
        e.preventDefault();
        window.location.href = buildExportUrl('csv');
    });

    $('#btnExportXlsx').on('click', function(e){
        e.preventDefault();
        window.location.href = buildExportUrl('xlsx');
    });

});
</script>
@stop
