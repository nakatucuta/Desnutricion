
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
@include('seguimiento.mensajes')
{{-- boton para abrir la modal --}}
<button type="button" class="btn {{$conteo > 0 ? 'btn-danger' : 'btn-primary'}}" data-toggle="modal" data-target="#exampleModal" style="float: right;
  margin-right: 0;
  width: 7%;
  position: relative;
  right: 0;">
  <i class="fas fa-bell"> {{$conteo}}</i>
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
       
@foreach($otro as $seguimiento)

   
     
@if( $seguimiento->usr == Auth::user()->id && Auth::user()->usertype == 2 && $seguimiento->fecha_proximo_control)
@if(Carbon\Carbon::now()->format('Y-m-d') > Carbon\Carbon::parse($seguimiento->fecha_proximo_control))
<div class="alert alert-danger">
    EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}} </strong>  HA SOBREPASADO SU  FECHA LIMITE. 
    {{$seguimiento->fecha_proximo_control}} FALLO POR 
    {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} 
    DIAS <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR 
    </a>
   </div>
@else
@if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 1)
<div class="alert alert-warning">
  EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}}</strong> FECHA DE PROXIMO CONTROL:    
  {{$seguimiento->fecha_proximo_control}} FALTAN  
  {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} 
  DIAS PARA SU VENCIMIENTO <a href="{{route('Seguimiento.create')}}">  CLICK AQUI PARA GESTIONAR 
  </a> </div>
@else
@if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 2)
<div class="alert alert-warning">
  EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}} </strong> FECHA DE PROXIMO CONTROL:
  {{$seguimiento->fecha_proximo_control}} FALTAN 
  {{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} 
  DIAS PARA SU VENCIMIENTO <a href="{{route('Seguimiento.create')}}"> CLICK AQUI PARA GESTIONAR 
  </a> </div>
{{-- @else

  @if(Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control)) < 24)
  <div class="alert alert-warning">
      EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}}</strong> FECHA DE PROXIMO CONTROL:    
      {{$seguimiento->fecha_proximo_control}} FALTAN  
      {{Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control))}} 
      HORAS PARA SU VENCIMIENTO <a href="{{route('Seguimiento.create')}}">  CLICK AQUI PARA GESTIONAR 
  </div> --}}
  @else 
  @if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 0 && Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control)) < 24)
  @php
    $diasRestantes = Carbon\Carbon::now()->diffInDays(Carbon\Carbon::parse($seguimiento->fecha_proximo_control));
  @endphp
  <div class="alert alert-success">
    EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}} </strong> FECHA DE PROXIMO CONTROL:
    {{$seguimiento->fecha_proximo_control}} FALTAN 
    @if(Carbon\Carbon::parse($seguimiento->fecha_proximo_control)->isPast())
      <strong>0 días y 0 horas</strong>
    @else
      <strong>{{$diasRestantes}} días y {{Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control))}} horas</strong>
    @endif
    PARA, <strong> AGREGAR OTRO SEGUIMIENTO O CERRAR EL CASO</strong> <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR</a>
  </div>
@endif





@endif
@endif
@endif





@endif



@if( Auth::user()->usertype == 1 || Auth::user()->usertype == 3)
@if(Carbon\Carbon::now()->format('Y-m-d') > Carbon\Carbon::parse($seguimiento->fecha_proximo_control))
<div class="alert alert-danger">
EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}} </strong>  HA SOBREPASADO SU  FECHA LIMITE. 
{{$seguimiento->fecha_proximo_control}} FALLO POR 
{{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} 
DIAS <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR 
</a>
</div>
@else
@if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 1)
<div class="alert alert-warning">
EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}}</strong> FECHA DE PROXIMO CONTROL:    
{{$seguimiento->fecha_proximo_control}} FALTAN  
{{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} 
DIAS PARA SU VENCIMIENTO <a href="{{route('Seguimiento.create')}}">  CLICK AQUI PARA GESTIONAR 
</a> </div>
@else
@if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 2)
<div class="alert alert-warning">
EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}} </strong> FECHA DE PROXIMO CONTROL:
{{$seguimiento->fecha_proximo_control}} FALTAN 
{{Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control)}} 
DIAS PARA SU VENCIMIENTO <a href="{{route('Seguimiento.create')}}"> CLICK AQUI PARA GESTIONAR 
</a> </div>
@else
@if(Carbon\Carbon::now()->diffInDays($seguimiento->fecha_proximo_control) == 0 && Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control)) < 24)
@php
$diasRestantes = Carbon\Carbon::now()->diffInDays(Carbon\Carbon::parse($seguimiento->fecha_proximo_control));
@endphp
<div class="alert alert-success">
EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}} </strong> FECHA DE PROXIMO CONTROL:
{{$seguimiento->fecha_proximo_control}} FALTAN 
@if(Carbon\Carbon::parse($seguimiento->fecha_proximo_control)->isPast())
<strong>0 días y 0 horas</strong>
@else
<strong>{{$diasRestantes}} días y {{Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control))}} horas</strong>
@endif
PARA, <strong> AGREGAR OTRO SEGUIMIENTO O CERRAR EL CASO</strong> <a href="{{route('Seguimiento.create')}}">CLICK AQUI PARA GESTIONAR</a>
</div>
@endif
@endif
@endif






@endif
@endif

@endforeach
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        {{-- <button type="button" class="btn btn-primary">Guardar cambios</button> --}}
      </div>
    </div>
  </div>


</div>
  {{-- aqui finliza la modal --


{{-- <div>
    <input type="text" class="form-control" id="search" placeholder="Buscar...">
</div> --}}
<br>

<div>
<a href="{{route('export2')}}" class="btn  btn-success " style="float: right;
margin-right: 0;
width: 14%;
position: relative;
right: 0;"><i class="fas fa-book"></i>   REPORTE</a>
</div>
    <h1>Datos Sivigila</h1>
    <strong>Total {{ $sivigilas->total() }} </strong>
 
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
            
             <table class="table table-hover table-striped {{-- table-responsive--}}" id="sivigila"> 
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
    $('#sivigila').DataTable({

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



