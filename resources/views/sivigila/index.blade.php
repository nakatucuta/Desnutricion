
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
@include('seguimiento.mensajes')



{{-- <div>
    <input type="text" class="form-control" id="search" placeholder="Buscar...">
</div> --}}
<br>


<a href="{{route('export2')}}" class="btn  btn-success " style="float: right;
margin-right: 0;
width: 14%;
position: relative;
right: 0;"><i class="fas fa-book"></i>   REPORTE</a>
    <h1>Datos Sivigila</h1>
    <strong>Total {{ $sivigilas->total() }} </strong>
 
    {{-- <section class="content-header">
      
        <h1 class="pull-right">
        
            
            <a class="btn btn-primary pull-right"
            style="margin-top: -10px;
            margin-bottom: 5px" href="{{url('sivigila/create')}}">Realizar seguimiento</a>
            
        </h1>
        </section> --}}

        <form action="{{ route('BUSCADOR')}}" method="GET" role="search">
          <div class="input-group">
            <input type="text" name="q" id="q" class="form-control" placeholder="Search..."> <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary">
                      <span class="glyphicon glyphicon-search"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                      </svg></span>
                  </button>
                 
              
          </div>
         
        </form>
@stop

            @section('content')
            
             <table class="table table-hover table-striped {{-- table-responsive--}}"> 
                <thead class="table table-hover table-dark">
                  <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th scope="col">Fecha Notificacion</th>
                    <th scope="col">Tipo ID</th>
                    <th scope="col">Identificacion</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody id="table">
                  <tr>
                    @foreach($sivigilas as $student2)
                    {{-- <th scope="row">1</th> --}}
                    <td>{{ $student2->fec_noti }}</td>
                    <td>{{ $student2->tip_ide_ }}</td>
                    <td>{{ $student2->num_ide_ }}</td>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.
                    $student2->seg_ape_ }}</td>
                    {{-- <td>{{ $student2->cod_eve }}</td> --}}
                    <td> 
                        
                      @if (DB::connection('sqlsrv_1')->table('maestroSiv113')
                      ->where('num_ide_', $student2->num_ide_)
                      // ->where('fec_not', Carbon\Carbon::parse($student2->fec_noti)->format('d/m/Y'))->exists() 
                      &&
                      DB::connection('sqlsrv')->table('sivigilas')
                      ->where('num_ide_', $student2->num_ide_)
                      ->where('fec_not', $student2->fec_noti)
                      ->exists())
                      <div>
                        <a href="" onclick="return false;" title="DETALLE" class="btn  btn-secondary">
                          <span class="icon-zoom-in" ></span>Procesado <i class="fas fa-stop"></i></a>
                      </div>
                 
                      @else

                      
                      <a href="{{route('detalle_sivigila', [$student2->num_ide_, $student2->fec_noti])}}" title="DETALLE" class="btn  btn-warning">
                        <span class="icon-zoom-in" ></span>Seguimiento</a>

                      @endif
                </td>
                  </tr>
                    
             
              
                  @endforeach 
                 
                </tbody>
                 
              </table>
               {{ $sivigilas->links() }} 
              
            
          
             
            @stop
            

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')

<script type="text/javascript"> 
  $(document).ready(function(){
      $("#q").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#table tr").filter(function() {
              $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
      });
  });
</script>
@stop



