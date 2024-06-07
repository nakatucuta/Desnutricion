@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

   
 

@stop

@section('content')


<div class="content">
    <div class="clearfix">
        <div class="box box-primary">
            <div class="box-body">
                <table class="table table-hover table-striped table-bordered" style="border: 1px solid #000000;" id="seguimiento"> 
                    <thead class="table table-hover table-info table-bordered" style="background-color: #d9f2e6; border: 1px solid #000000;">
                        <tr>
                            <th>ID</th>
                            <th>PRESTADOR</th>
                            <th>CANT CASOS ASIGNADOS</th>
                            <th>CANT CASOS CON SEGUIMIENTOS</th>
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
                {{ $results->links() }}
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


@stop

@section('js')

<!-- Llamada al archivo Chart.min.js -->
{{-- <script src="{{ asset('node_modules/chart.js/dist/chart.js') }}"></script> --}}


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

