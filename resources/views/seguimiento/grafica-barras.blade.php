@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')
@stop

@section('content')

    @if (auth()->user()->usertype == 1 || auth()->user()->usertype == 3)

        <div class="content">
            <div class="tables-container row">
                <!-- Tabla del Evento 113 -->
                <div class="col-md-6">
                    <div class="box box-primary"
                        style="background-color: #f0fff4; border: 1px solid #d4edda; border-radius: 8px;">
                        <div class="box-body">
                            <div class="table-title"
                                style="background-color: #d4edda; padding: 10px; border-radius: 8px 8px 0 0; color: #155724;">
                                EVENTO:113
                            </div>
                            <table class="table table-hover table-striped table-bordered" id="seguimiento"
                                style="background-color: #ffffff;">
                                <thead class="table table-info table-bordered" style="background-color: #c3e6cb;">
                                    <tr>
                                        <th>ID</th>
                                        <th>PRESTADOR</th>
                                        <th>CASOS ASIGNADOS</th>
                                        <th>CASOS SIN SEGUIMIENTOS</th>
                                    </tr>
                                </thead>
                                <tbody id="table">
                                    @foreach ($results as $student2)
                                        <tr>
                                            <td>{{ $student2->id }}</td>
                                            <td>
                                                <a href="#" data-id="{{ $student2->id }}">{{ $student2->name }}</a>
                                            </td>
                                            <td>{{ $student2->cant_casos_asignados }}</td>
                                            <td>{{ $student2->total_Sin_Seguimientos }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Tabla del Evento 412 -->
                <div class="col-md-6">
                    <div class="box box-primary"
                        style="background-color: #f0fff4; border: 1px solid #d4edda; border-radius: 8px;">
                        <div class="box-body">
                            <div class="table-title"
                                style="background-color: #d4edda; padding: 10px; border-radius: 8px 8px 0 0; color: #155724;">
                                EVENTO:412
                            </div>
                            <table class="table table-hover table-striped table-bordered" id="seguimiento2"
                                style="background-color: #ffffff;">
                                <thead class="table table-info table-bordered" style="background-color: #c3e6cb;">
                                    <tr>
                                        <th>ID</th>
                                        <th>PRESTADOR</th>
                                        <th>CASOS ASIGNADOS</th>
                                        <th>CASOS SIN SEGUIMIENTOS</th>
                                    </tr>
                                </thead>
                                <tbody id="table">
                                    @foreach ($results_412 as $student2)
                                        <tr>
                                            <td>{{ $student2->id }}</td>
                                            <td>
                                                <a href="#" data-toggle="modal" data-target="#prestadorModal"
                                                    data-id="{{ $student2->id }}">{{ $student2->name }}</a>
                                            </td>
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

            <!-- Modal HTML para Evento 113 -->
            <!-- Modal HTML -->
            <div class="modal fade" id="prestador113Modal" tabindex="-1" aria-labelledby="prestador113ModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="prestador113ModalLabel">Detalles del Prestador: <strong><span
                                        id="nombrePrestador"></span></strong></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Tabla de detalles del prestador -->
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Semana Epidemiologica</th>
                                        <th>Identificación</th>
                                        <th>Nombre Completo</th>
                                    </tr>
                                </thead>
                                <tbody id="prestador113List">
                                    {{-- Aquí se agregarán los detalles del prestador dinámicamente --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal HTML para Evento 412 -->
            <!-- Modal HTML -->
            <div class="modal fade" id="prestadorModal" tabindex="-1" aria-labelledby="prestadorModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="prestadorModalLabel">Detalles del Prestador: <strong><span
                                        id="nombrePrestador"></span></strong></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Tabla de detalles del prestador -->
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha Captacion</th>
                                        <th>Identificación</th>
                                        <th>Nombre Completo</th>
                                    </tr>
                                </thead>
                                <tbody id="prestadorList">
                                    {{-- Aquí se agregarán los detalles del prestador dinámicamente --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>


            <br>

            <div class="row">
                <div class="col-sm-5">
                    <canvas id="grafica-torta" width="300" height="200"></canvas>
                    <h3 class="estilo-h3">Seguimientos por clasificación <i class="fas fa-chart-line"></i></h3>
                </div>
                <div class="col-sm-7">
                    <canvas id="grafica-barras" width="300" height="200"></canvas>
                    <h3 class="estilo-h3">Seguimientos por estado <i class="fas fa-chart-line"></i></h3>
                </div>
            </div>
        </div>
    @else
        <div class="content">
            <div class="tables-container row">
                <div class="box box-primary">
                    <p class="center-text">ESTADISTICAS NO DISPONIBLES</p>
                </div>
            </div>
        </div>

    @endif


    <style>
        .estilo-h3 {
            text-align: center;
            font-family: 'Copperplate', sans-serif;
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-top: 30px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .content {
            margin: 20px;
        }

        .evento {
            text-align: center;
            font-weight: bold;
        }

        .tables-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .box {
            flex: 1;
            min-width: 45%;
            margin: 10px;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .center-text {
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
       

        .box-body {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #cccccc;
        }

        thead {
            background-color: #d9f2e6;
        }

        th {
            background-color: #70a1ff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .pagination {
            display: flex;
            justify-content: center;
            padding: 10px;
        }

        .table-title {
            text-align: center;
            font-size: 1.25em;
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
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
            // Inicializar la tabla para Evento 113
            var table1 = $('#seguimiento').DataTable({
                "pageLength": 5,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ],
                "language": {
                    "search": "BUSCAR:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "CANTIDAD: _TOTAL_",
                    "infoEmpty": "No hay registros disponibles",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar la columna en orden ascendente",
                        "sortDescending": ": Activar para ordenar la columna en orden descendente"
                    }
                },
                "autoWidth": true
            });

            // Inicializar la tabla para Evento 412 (si existe otra tabla con id #seguimiento2)
            var table2 = $('#seguimiento2').DataTable({
                "pageLength": 5,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ],
                "language": {
                    "search": "BUSCAR:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "CANTIDAD: _TOTAL_",
                    "infoEmpty": "No hay registros disponibles",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar la columna en orden ascendente",
                        "sortDescending": ": Activar para ordenar la columna en orden descendente"
                    }
                },
                "autoWidth": true
            });

            // Función para manejar el evento de clic en los enlaces sin usar data-toggle="modal"
            function attachClickHandler() {
                $('a[data-id]').off('click').on('click', function(e) {
                    e.preventDefault(); // Prevenir el comportamiento por defecto
                    var id = $(this).data('id'); // Obtiene el ID del prestador
                    var modal, modalBody, routeUrl;

                    // Determina si el clic se realizó en la tabla de Evento 113
                    if ($(this).parents('#seguimiento').length > 0) {
                        modal = $('#prestador113Modal'); // Modal para Evento 113
                        modalBody = modal.find('.modal-body #prestador113List'); // Cuerpo de la modal
                        routeUrl = '{{ route('detallePrestador_113', '') }}/' +
                            id; // Ruta para obtener los detalles del prestador
                    } else {
                        // Caso contrario, asume que es la tabla de Evento 412
                        modal = $('#prestadorModal'); // Modal para Evento 412
                        modalBody = modal.find('.modal-body #prestadorList'); // Cuerpo de la modal
                        routeUrl = '{{ route('gedetalle_prestador', '') }}/' +
                            id; // Ruta para obtener los detalles del prestador
                    }

                    // Realiza la solicitud AJAX para obtener los detalles del prestador
                    $.ajax({
                        url: routeUrl,
                        method: 'GET',
                        success: function(data) {
                            modalBody.empty(); // Limpia el contenido anterior de la modal
                            if (data.length > 0) {
                                data.forEach(function(detalle) {
                                    if (modal.is('#prestador113Modal')) {
                                        // Renderiza los detalles del prestador para Evento 113
                                        modalBody.append(
                                            '<tr>' +
                                            '<td>' + detalle.semana + '</td>' +
                                            '<td>' + detalle.tip_ide_ + ' - ' +
                                            detalle.num_ide_ + '</td>' +
                                            '<td>' + detalle.pri_nom_ + ' ' + (
                                                detalle.seg_nom_ ? detalle
                                                .seg_nom_ + ' ' : '') + detalle
                                            .pri_ape_ + ' ' + detalle.seg_ape_ +
                                            '</td>' +
                                            // Nuevo campo para mostrar la semana
                                            '</tr>'
                                        );


                                    } else {
                                        // Renderiza los detalles del prestador para Evento 412
                                        modalBody.append(
                                            '<tr>' +
                                            '<td>' + detalle.fecha_captacion +
                                            '</td>' +
                                            '<td>' + detalle.tipo_identificacion +
                                            ' - ' + detalle.numero_identificacion +
                                            '</td>' +
                                            '<td>' + detalle.primer_nombre + ' ' + (
                                                detalle.segundo_nombre ? detalle
                                                .segundo_nombre + ' ' : '') +
                                            detalle.primer_apellido + ' ' + detalle
                                            .segundo_apellido + '</td>' +
                                            '</tr>'
                                        );

                                    }
                                });
                            } else {
                                // Muestra un mensaje si no se encontraron detalles para el prestador
                                modalBody.append(
                                    '<li class="list-group-item">No se encontraron detalles para este prestador.</li>'
                                );
                            }
                            modal.modal('show'); // Muestra la modal correspondiente
                        },
                        error: function(error) {
                            console.log('Error en la solicitud AJAX:',
                                error); // Muestra el error en la consola
                            modalBody.html(
                                '<li class="list-group-item">Error al cargar los detalles.</li>'
                            ); // Muestra un mensaje de error en la modal
                            modal.modal('show'); // Muestra la modal
                        }
                    });
                });
            }

            // Llamada inicial para activar el controlador de eventos
            attachClickHandler();

            // Reasigna los manejadores de clic en cada recarga de la tabla
            table1.on('draw', function() {
                attachClickHandler();
            });
            table2.on('draw', function() {
                attachClickHandler();
            });
        });



        var ctx1 = document.getElementById('grafica-barras').getContext('2d');
        var myChart1 = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: {!! json_encode($estados_labels) !!},
                datasets: [{
                    label: 'Seguimientos por estado',
                    data: {!! json_encode($estados_data) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: 'Seguimientos por estado'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        var ctx2 = document.getElementById('grafica-torta').getContext('2d');
        var myChart2 = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: {!! json_encode($clasificaciones_labels) !!},
                datasets: [{
                    data: {!! json_encode($clasificaciones_data) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Seguimientos por clasificación'
                }
            }
        });
    </script>
@stop
