@extends('adminlte::page')

@section('title', 'Resultados de Tamizajes - PAI')

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@stop

@section('content_header')
    <div class="header-container">
        <h1 class="executive-title">TAMIZAJES</h1>
    </div>
    <div class="container text-center mb-4">
        <h1 class="display-4 font-weight-bold text-primary">Resultados de Tamizajes</h1>
        <p class="lead">Esta tabla muestra todos los registros almacenados con sus usuarios asociados.</p>
    </div>
@stop

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col text-right">
            <a href="{{ route('excel.import.index') }}" class="btn btn-warning btn-lg">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>

    
    <div class="card shadow-sm animate__fadeInUp">
        <div class="card-header bg-info text-white">
            <h4 class="card-title mb-0">Listado de Tamizajes</h4>
        </div>
        <div class="card-body">
            <table id="tamizajes-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Tipo Ident.</th>
                        <th>Número Ident.</th>
                        <!-- Nueva cabecera -->
                        <th>Nombre Completo</th>
                        <th>Fecha Tamizaje</th>
                        <th>Tipo de Tamizaje</th>
                        {{-- <th>Código Resultado</th> --}}
                        <th>Resultado</th>
                        <th>Valor Laboratorio</th>
                        <th>Descripción Resultado</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function() {
    $('#tamizajes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route("excel.import.table") !!}',
        order: [[0, 'desc']],
        columns: [
            { data: 'tipo_identificacion' },
            {
                data: 'numero_identificacion',
                render: function(v) {
                    const url = '{!! route("tamizajes.show-pdfs", ":numero") !!}'
                                .replace(':numero', v);
                    return `<a href="${url}" class="text-primary">${v}</a>`;
                }
            },
            // Nueva columna
            { data: 'nombre_completo', defaultContent: '—' },
            { data: 'fecha_tamizaje' },
            { data: 'tipo_tamizaje' },
            // { data: 'codigo_resultado' },
            { data: 'descripcion_codigo', defaultContent: 'N/A' },
            { data: 'valor_laboratorio',    defaultContent: 'N/A' },
            { data: 'descript_resultado',    defaultContent: 'N/A' },
            { data: 'usuario' },
            { data: 'acciones', orderable: false, searchable: false }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
    });
});
</script>
@stop
