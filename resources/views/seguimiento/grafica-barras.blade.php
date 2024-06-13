@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

   
 

@stop

@section('content')
<div class="content">
   
    <div class="tables-container">
        <div class="box box-primary">
            <div class="box-body">
                <div class="table-title">EVENTO:113</div>
                <table class="table table-hover table-striped table-bordered" id="seguimiento"> 
                    <thead class="table table-info table-bordered">
                        <tr>
                            <th>ID</th>
                            <th>PRESTADOR</th>
                            <th>CASOS ASIGNADOS</th>
                            <th>CASOS CON SEGUIMIENTOS</th>
                        </tr>
                    </thead>
                    <tbody id="table">
                        @foreach($results as $student2)
                        <tr>
                            <td>{{ $student2->id }}</td>
                            <td>{{ $student2->name }}</td>
                            <td>{{ $student2->cant_casos_asignados }}</td>
                            <td>{{ $student2->total_Seguimientos }}</td>
                            <!-- Los botones o enlaces de acciones pueden ir aquí -->
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination">
                    {{-- {{ $results->links() }} --}}
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-body">
                <div class="table-title">EVENTO:412</div>
                <table class="table table-hover table-striped table-bordered" id="seguimiento2"> 
                    <thead class="table table-info table-bordered">
                        <tr>
                            <th>ID</th>
                            <th>PRESTADOR</th>
                            <th>CASOS ASIGNADOS</th>
                            <th>CASOS CON SEGUIMIENTOS</th>
                        </tr>
                    </thead>
                    <tbody id="table">
                        @foreach($results_412 as $student2)
                        <tr>
                            <td>{{ $student2->id }}</td>
                            <td>{{ $student2->name }}</td>
                            <td>{{ $student2->cant_casos_asignados }}</td>
                            <td>{{ $student2->total_Seguimientos }}</td>
                            <!-- Los botones o enlaces de acciones pueden ir aquí -->
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination">
                    {{-- {{ $results_412->links() }} --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <i class="fas fa-chart-line fa-3x" style="color: #33cc33; border: 2px solid
 #33cc33; border-radius: 5px; padding: 5px;"></i> --}}


<br>
<div class="row">
    
    <div class="col-sm-5">
        
      <canvas id="grafica-torta" width="300" height="200"></canvas>
      <h3 class="estilo-h3"> Seguimientos por clasificación <i class="fas fa-chart-line "></i></h3>
    </div>
    <div class="col-sm-7">
        
      <canvas id="grafica-barras" width="300" height="200"></canvas>
      <h3 class="estilo-h3"> Seguimientos por estado<i class="fas fa-chart-line "></i></h3>
    </div>
  </div>
  


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
</style>


@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
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
        .box-body {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }
        th, td {
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

@section('js')

<!-- Llamada al archivo Chart.min.js -->
{{-- <script src="{{ asset('node_modules/chart.js/dist/chart.js') }}"></script> --}}
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
    $('#seguimiento').DataTable({
        "pageLength": 5, // Mostrar 5 registros por página
        "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"] ], // Opciones de selección de cantidad de registros

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
                "previous": false // Eliminamos el botón "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna en orden ascendente",
                "sortDescending": ": Activar para ordenar la columna en orden descendente"
            }
        },
        "autoWidth": true
    });
});


$(document).ready(function () {
    $('#seguimiento2').DataTable({
        "pageLength": 5, // Mostrar 5 registros por página
        "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"] ], // Opciones de selección de cantidad de registros

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
                "previous": false // Eliminamos el botón "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna en orden ascendente",
                "sortDescending": ": Activar para ordenar la columna en orden descendente"
            }
        },
        "autoWidth": true
    });
});

</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('grafica-barras').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($estados_labels) !!},
            datasets: [{
                label: 'Seguimientos por estado',
                data: {!! json_encode($estados_data) !!},
                backgroundColor: [                    'rgba(255, 99, 132, 0.2)',                    'rgba(54, 162, 235, 0.2)',                    'rgba(255, 206, 86, 0.2)',                    'rgba(75, 192, 192, 0.2)',                    'rgba(153, 102, 255, 0.2)',                    'rgba(255, 159, 64, 0.2)'                ],
                borderColor: [                    'rgba(255, 99, 132, 1)',                    'rgba(54, 162, 235, 1)',                    'rgba(255, 206, 86, 1)',                    'rgba(75, 192, 192, 1)',                    'rgba(153, 102, 255, 1)',                    'rgba(255, 159, 64, 1)'                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
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
</script>





<script>
    var ctx = document.getElementById('grafica-torta').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($clasificaciones_labels) !!},
            datasets: [{
                data: {!! json_encode($clasificaciones_data) !!},
                backgroundColor: [                   
                     'rgba(255, 99, 132, 0.2)',    
                                'rgba(54, 162, 235, 0.2)',                   
                                 'rgba(255, 206, 86, 0.2)',                    
                                 'rgba(75, 192, 192, 0.2)',                   
                                  'rgba(153, 102, 255, 0.2)',                   
                                   'rgba(255, 159, 64, 0.2)'               
                                 ],
                borderColor: [                    'rgba(255, 99, 132, 1)',                    'rgba(54, 162, 235, 1)',                    'rgba(255, 206, 86, 1)',                    'rgba(75, 192, 192, 1)',                    'rgba(153, 102, 255, 1)',                    'rgba(255, 159, 64, 1)'                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
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

