
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@include('seguimiento.mensajes')


<h1>Listado De Seguimientos</h1>
<br>
{{-- <form action="{{ route('BUSCADOR1')}}" method="GET" role="search">
  <div class="input-group">
    <input type="text" name="q" id="q" class="form-control" placeholder="Search..."> <span class="input-group-btn">
          <button type="submit" class="btn btn-primary">
              <span class="glyphicon glyphicon-search" ><i class="fas fa-search"></i></span>
          </button>
         
      
  </div>
 
</form> --}}







<a href="{{route('Seguimiento.create')}}" title="DETALLE" class="btn  btn-primary">
  <span class="icon-zoom-in" ></span> NUEVO SEGUIMIENTO</a>
  {{-- <strong>Total {{ $incomeedit->total() }} </strong> --}}
{{-- secion del reporte general --}}

<a href="{{route('export3')}}" class="btn  btn-success " style="
float: right:;
margin-right: 0;
width: 6%;
position: relative;
right: 0;"><i class="fas fa-book"></i>  </a>
  {{-- seccion del primer reporte --}}
  <a href="{{route('export')}}" class="btn  btn-success " style="float: right;
  margin-right: 0;
  width: 14%;
  position: relative;
  right: 0;"><i class="fas fa-book"></i>   REPORTE</a>
  <br> <strong>Total {{ $incomeedit->total() }} </strong><br>
    {{-- <section class="content-header">
      
        <h1 class="pull-right">
        
            
            <a class="btn btn-primary pull-right"
            style="margin-top: -10px;
            margin-bottom: 5px" href="{{url('sivigila/create')}}">Realizar seguimiento</a>
            
        </h1>
        </section> --}}



        {{-- <div>
          <input type="text" class="form-control" id="search" placeholder="Buscar...">
        </div>
         --}}
@stop

            @section('content')
            

            <div class="content">
              <div class="clearfix">
              <div class="box box-primary">
                <div class="box-body">
            <table class="table table-hover table-striped" id="seguimiento">
                <thead class="table table-hover table-dark">
                  <tr>
                    <th scope="col">Identificacion</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Fecha Reporte</th>
                    <th scope="col">Ips</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody id="table">
                  <tr>
                    @foreach($incomeedit as $student2)
                    <th scope="row">{{ $student2->num_ide_ }}</th>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                    <td>{{$student2->Fecha_ingreso_ingres}}</td>
                    <td>{{$student2->Ips_at_inicial}}</td>
                        
                      <td>  <a class="btn  btn-warning" href="{{url('/Seguimiento/'.$student2->id. '/edit')}}" class="ref" >
                        <i class="fas fa-edit"></i>
                    </a>
                  
                    
                  
                    <a href="{{route('Seguimiento.destroy', $student2->id)}}"
                      onclick="event.preventDefault();
                      if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                      document.getElementById('delete-form-{{$student2->id}}').submit();
                      }" class="btn  btn-danger">
                     <i class="fas fa-trash"></i>
                   </a>
                   <form id="delete-form-{{$student2->id}}" action="{{route('Seguimiento.destroy', $student2->id)}}"
                    method="POST" style="display: none;">
                  @method('DELETE')
                  @csrf
              </form>
                  
                  </td>
                
                            
                </td>
                  </tr>
                
                 
             
                  
                  @endforeach 
            
                </tbody>
                
              </table>
               {{ $incomeedit->links() }} 
            
              </div>
              </div>
            </div>
          </div>
            @stop
            

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
 .dataTables_filter input {
  width: 500px !important;
  height: 100%;
  background-color: #555 ;
  border: solid 3px !important;
  border-radius: 20px !important;
  color: rgb(64, 125, 232);
  padding: 10px !important;
}
</style>
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
    font-weight: bold !important;
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
  
  <script>
    $(document).ready(function () {
      $('#seguimiento').DataTable({
  
        "language":{
  
              "search": "BUSCAR",
              "lengthMenu": "Mostrar _MENU_ registros",
              "info": "Mostrando pagina _PAGE_ de _PAGES_",
              "paginate": {
              "first": "Primero",
              "last": "Último",
              "next": "Siguiente",
              "previous": "Anterior"
                             }
  
  
                }
  
      });
  });
  </script>
@stop



