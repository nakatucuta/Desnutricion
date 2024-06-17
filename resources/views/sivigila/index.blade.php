@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')
@include('seguimiento.mensajes')

{{-- <a href="{{ route('download.pdf') }}">Descargar PDF</a> --}}
{{-- boton para abrir la modal --}}
<button type="button" class="btn {{$conteo > 0 ? 'btn-danger btn-sm btn-pulse' : 'btn-primary btn-sm btn-pulse'}} rounded-circle p-0" data-toggle="modal" data-target="#exampleModal" style="float: right; width: 40px; height: 40px; position: relative; right: 0;">
  <i class="fas fa-bell fa-2x text-white p-2" style="background-color: {{$conteo > 0 ? '#dc3545' : '#007bff'}}; border-radius: 75%;"></i>
  <span class="badge badge-light position-absolute" style="top: -10px; right: -10px; font-size: 0.8rem;">{{$conteo}}</span>
</button>

{{-- aqui termina el boton --}}
<br>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">NOTIFICACIONES</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        @include('sivigila.notificacion')

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
        {{-- <button type="button" class="btn btn-primary">Guardar cambios</button> --}}
      </div>
    </div>
  </div>



</div>
  {{-- aqui finliza la modal --
hola prueba
{{-- <div>
    <input type="text" class="form-control" id="search" placeholder="Buscar...">
</div> --}}
<br>

<div>
  <a href="{{route('export2')}}" class="btn btn-success btn-sm"
  style="float: right; margin-right: 1rem; position: relative; right: 0; border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #28a745;">
      <i class="fas fa-file-export mr-2"></i> EXPORTAR
  </a>
  

  <a href="{{route('export4')}}" class="btn btn-success btn-sm"
  style="float: right; margin-right: 1rem; position: relative; right: 0; border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #28a745;">
      <i class="fas fa-file-export mr-2"></i> S.publica
  </a>


  <a href="{{route('export5')}}" class="btn btn-success btn-sm"
  style="float: right; margin-right: 1rem; position: relative; right: 0; border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #28a745;">
      <i class="fas fa-file-export mr-2"></i> sin seguimiento</a>





  <a href="{{route('create11')}}" class="btn btn-success btn-sm"
  style="float: right; margin-right: 1rem; position: relative; right: 0; border-radius: 50px; padding: 10px 20px; font-weight: bold; letter-spacing: 1px; background-color: #28a745;">
      <i class="fas fa-plus mr-2"></i> AGREGAR
  </a>
  
  
</div>
    <h1 style="font-family: 'Helvetica Neue', sans-serif; font-weight: 700; font-size: 2rem;">Datos Sivigila</h1>
     <strong>Procesados = {{ $sivi2}} </strong> 

    <strong>cantidad = {{ $resultados}} </strong>
 
    {{-- <section class="content-header">
      
        <h1 class="pull-right">
        
            
            <a class="btn btn-primary pull-right"
            style="margin-top: -10px;
            margin-bottom: 5px" href="{{url('sivigila/create')}}">Realizar seguimiento</a>
            
        </h1>
        </section> --}}

        {{-- <form action="{{ route('BUSCADOR')}}" method="GET" role="search">
          <div class="input-group">
            <input type="text" name="q" id="q" class="form-control" placeholder="Search..."> <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary">
                      <span class="glyphicon glyphicon-search"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                      </svg></span>
                  </button>
                 
              
          </div>
         
        </form> --}}
@stop

            @section('content')
            
             <table class="table table-hover table-striped table-bordered  {{-- table-responsive--}}" style="border: 1px solid #000000;" id="sivigila"> 
              <thead class="table table-hover table-info table-bordered " style="background-color: #d9f2e6 ;border: 1px solid #000000;">
                  <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th style="font-size: smaller;" scope="col">Fecha Notificación</th>
                    <th style="font-size: smaller;" scope="col">Semana</th>
                    <th style="font-size: smaller;" scope="col">Tipo ID</th>
                    <th style="font-size: smaller;" scope="col">Identificación</th>
                    <th style="font-size: smaller;" scope="col">Nombre</th>
                    <th style="font-size: smaller;" scope="col">Upgd Notificadora</th>
                    <th style="font-size: smaller;" scope="col">Ips Primaria</th> 
                    <th style="font-size: smaller;" scope="col">Acciones</th>
                    <th style="font-size: smaller;" scope="col">Ips Primaria</th> 
                    <th style="font-size: smaller;" scope="col">Acciones</th>
                    
                  </tr>
                </thead>
                <tbody id="table">
                 
                </tbody>
                 
              </table>
               {{-- {{ $sivigilas->links() }}  --}}
              
            
          
             
            @stop
            

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<style> 

.dataTables_filter input {
  width: 500px !important;
  height: 100%;
  background-color: #555 ;
  border: solid 3px !important;
  border-radius: 20px !important;
  color: rgb(64, 125, 232);
  padding: 10px !important;
  font-weight: bold !important;
}

.dataTables_filter label {
  font-weight: bold !important ;
}

 .dataTables_length label {
  
  font-weight: bold !important;
} 

.dataTables_length select {
  display: flex ;
  border: solid 3px !important;
  border-radius: 20px !important;
  align-items: center !important;
  margin-bottom: 10px !important;
  color: rgb(64, 125, 232) !important;
}


@keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(255, 99, 132, 0.7);
    }
    70% {
      box-shadow: 0 0 0 20px rgba(255, 99, 132, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(255, 99, 132, 0);
    }
  }

  .btn-pulse {
  animation: pulse 1s ease-in-out infinite;
}


  
</style>


{{-- <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script> --}}
{{-- <script type="text/javascript"> 
  $(document).ready(function(){
      $("#q").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#table tr").filter(function() {
              $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
      });
  });
</script> --}}


<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
    $('#sivigila').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('sivigila.data') }}",
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
        "autoWidth": true,
        "columns": [
          { "data": "fec_noti" },
            { "data": "tip_ide_" },
            { "data": "num_ide_" },
            { "data": "pri_nom_" }
            { "data": "seg_nom_" },
            { "data": "pri_ape_" },
            { "data": "seg_ape_" },
            { "data": "semana" }
            { "data": "nom_upgd" }
            
        ]
    });
});


</script>
@stop



