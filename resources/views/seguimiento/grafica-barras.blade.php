@extends('adminlte::page')

@section('title', 'Anas Wayuu')

@section('content_header')
@stop

@section('content')



        <div class="content">
            <div class="tables-container row">
                <!-- Tabla del Evento 113 -->
                <div class="col-md-6">
                    <div class="box box-primary" style="background-color: #f0fff4; border: 1px solid #d4edda; border-radius: 8px;">
                        <div class="box-body">
                            <div class="table-title" style="background-color: #d4edda; padding: 10px; border-radius: 8px 8px 0 0; color: #155724;">
                                EVENTO:113
                            </div>
                            <table class="table table-hover table-striped table-bordered" id="seguimiento" style="background-color: #ffffff;">
                                <thead class="table table-info table-bordered" style="background-color: #c3e6cb;">
                                    <tr>
                                        <th>ID</th>
                                        <th>PRESTADOR</th>
                                        <th>CASOS ASIGNADOS</th>
                                        <th>CASOS SIN SEGUIMIENTOS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($results as $student)
                                        <tr>
                                            <td>{{ $student->id }}</td>
                                            <td><a href="#" data-id="{{ $student->id }}">{{ $student->name }}</a></td>
                                            <td>{{ $student->cant_casos_asignados }}</td>
                                            <td>{{ $student->total_Sin_Seguimientos }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabla del Evento 412 -->
                <div class="col-md-6">
                    <div class="box box-primary" style="background-color: #f0fff4; border: 1px solid #d4edda; border-radius: 8px;">
                        <div class="box-body">
                            <div class="table-title" style="background-color: #d4edda; padding: 10px; border-radius: 8px 8px 0 0; color: #155724;">
                                EVENTO:412
                            </div>
                            <table class="table table-hover table-striped table-bordered" id="seguimiento2" style="background-color: #ffffff;">
                                <thead class="table table-info table-bordered" style="background-color: #c3e6cb;">
                                    <tr>
                                        <th>ID</th>
                                        <th>PRESTADOR</th>
                                        <th>CASOS ASIGNADOS</th>
                                        <th>CASOS SIN SEGUIMIENTOS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($results_412 as $student2)
                                        <tr>
                                            <td>{{ $student2->id }}</td>
                                            <td><a href="#" data-id="{{ $student2->id }}">{{ $student2->name }}</a></td>
                                            <td>{{ $student2->cant_casos_asignados }}</td>
                                            <td>{{ $student2->total_Sin_Seguimientos }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modales -->
            <div class="modal fade" id="prestador113Modal" tabindex="-1" aria-labelledby="prestador113ModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="prestador113ModalLabel">Detalles del Prestador - Evento 113</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Semana Epidemiologica</th>
                                        <th>Identificación</th>
                                        <th>Nombre Completo</th>
                                    </tr>
                                </thead>
                                <tbody id="prestador113List"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="prestadorModal" tabindex="-1" aria-labelledby="prestadorModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="prestadorModalLabel">Detalles del Prestador - Evento 412</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha Captacion</th>
                                        <th>Identificación</th>
                                        <th>Nombre Completo</th>
                                    </tr>
                                </thead>
                                <tbody id="prestadorList"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <div class="row">
          
   

@stop

@section('css')
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // URLs base con placeholder
            const detail113Base = '{{ route("detallePrestador_113", [":id"]) }}';
            const detail412Base = '{{ route("gedetalle_prestador", [":id"]) }}';

            // Inicializar DataTables
            var table1 = $('#seguimiento').DataTable({
                pageLength: 5,
                lengthMenu: [[5,10,25,50,-1],[5,10,25,50,'Todos']],
                language: {
                    search: 'BUSCAR:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'CANTIDAD: _TOTAL_', infoEmpty: 'No hay registros disponibles',
                    zeroRecords: 'No se encontraron registros coincidentes',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' }
                }, autoWidth: true
            });
            var table2 = $('#seguimiento2').DataTable({
                pageLength: 5,
                lengthMenu: [[5,10,25,50,-1],[5,10,25,50,'Todos']],
                language: {
                    search: 'BUSCAR:', lengthMenu: 'Mostrar _MENU_ registros', info: 'CANTIDAD: _TOTAL_',
                    zeroRecords: 'No se encontraron registros coincidentes', paginate: { next:'Siguiente', previous:'Anterior' }
                }, autoWidth: true
            });

            // Handler para clic en prestador
            function attachClickHandler() {
                $('a[data-id]').off('click').on('click', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var modal, modalBody, routeUrl;
                    if ($(this).parents('#seguimiento').length > 0) {
                        modal = $('#prestador113Modal');
                        modalBody = modal.find('#prestador113List');
                        routeUrl = detail113Base.replace(':id', id);
                    } else {
                        modal = $('#prestadorModal');
                        modalBody = modal.find('#prestadorList');
                        routeUrl = detail412Base.replace(':id', id);
                    }
                    $.ajax({ url: routeUrl, method: 'GET', success: function(data) {
                        modalBody.empty();
                        if (data.length) {
                            data.forEach(function(detalle) {
                                if (modal.is('#prestador113Modal')) {
                                    modalBody.append(
                                        '<tr><td>'+detalle.semana+'</td><td>'+detalle.tip_ide_+' - '+detalle.num_ide_+
                                        '</td><td>'+detalle.pri_nom_+' '+(detalle.seg_nom_?detalle.seg_nom_+' ':'')+detalle.pri_ape_+' '+detalle.seg_ape_+'</td></tr>'
                                    );
                                } else {
                                    modalBody.append(
                                        '<tr><td>'+detalle.fecha_captacion+'</td><td>'+detalle.tipo_identificacion+' - '+detalle.numero_identificacion+
                                        '</td><td>'+detalle.primer_nombre+' '+(detalle.segundo_nombre?detalle.segundo_nombre+' ':'')+detalle.primer_apellido+' '+detalle.segundo_apellido+'</td></tr>'
                                    );
                                }
                            });
                        } else {
                            modalBody.html('<tr><td colspan="3">No se encontraron detalles para este prestador.</td></tr>');
                        }
                        modal.modal('show');
                    }, error: function() {
                        modalBody.html('<tr><td colspan="3">Error al cargar detalles.</td></tr>');
                        modal.modal('show');
                    }});
                });
            }
            attachClickHandler();
            table1.on('draw', attachClickHandler);
            table2.on('draw', attachClickHandler);

            // Gráfica de barras

        });
    </script>
@stop
