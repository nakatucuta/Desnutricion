
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
{{-- @include('seguimiento.mensajes') --}}
{{-- boton para abrir la modal --}}
{{-- <button type="button" class="btn {{$conteo > 0 ? 'btn-danger' : 'btn-primary'}}" data-toggle="modal" data-target="#exampleModal" style="float: right;
  margin-right: 0;
  width: 7%;
  position: relative;
  right: 0;">
  <i class="fas fa-bell"> {{$conteo}}</i>
</button> --}}

{{-- aqui termina el boton --}}
{{-- <br>

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
  </a> </div> --}}
{{-- @else

  @if(Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control)) < 24)
  <div class="alert alert-warning">
      EL SEGUIMIENTO CON ID {{$seguimiento->idin}} Y NUMERO DE IDENTIFICACION: <strong>{{$seguimiento->num_ide_}}</strong> FECHA DE PROXIMO CONTROL:    
      {{$seguimiento->fecha_proximo_control}} FALTAN  
      {{Carbon\Carbon::now()->diffInHours(Carbon\Carbon::parse($seguimiento->fecha_proximo_control))}} 
      HORAS PARA SU VENCIMIENTO <a href="{{route('Seguimiento.create')}}">  CLICK AQUI PARA GESTIONAR 
  </div> --}}
  {{-- @else 
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
     <button type="button" class="btn btn-primary">Guardar cambios</button> 
      </div>
    </div>
  </div>


</div> --}}
  {{-- aqui finliza la modal --


{{-- <div>
    <input type="text" class="form-control" id="search" placeholder="Buscar...">
</div> --}}
{{-- <br>

<div>
<a href="{{route('export2')}}" class="btn  btn-success " style="float: right;
margin-right: 0;
width: 14%;
position: relative;
right: 0;"><i class="fas fa-book"></i>   REPORTE</a>
</div> --}}
    <h1>Datos Revision</h1>
   
 

@stop

            @section('content')
            
             <table class="table table-hover table-striped {{-- table-responsive--}}" id="sivigila"> 
                <thead class="table table-hover table-dark">
                  <tr>
                    <th>ID</th>
                    <th >Identificacion</th>
                    <th >Nombre</th>
                   
                    <th >Ips</th>
                    
                    {{-- <th >Fecha proximo control</th> --}}
                    <th >Revisar</th>
                  </tr>
                </thead>
                <tbody id="table">
                  <tr>
                    @foreach($incomeedit as $student2)
                <th> {{ $student2->id }}</th>   
                <th >{{ $student2->num_ide_ }}</th>
                 <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                 <td>{{$student2->hospi}}</td>
                 
                 

                 
               
                 {{-- @if(!empty($student2->fecha_proximo_control))
                 <td>{{ $student2->fecha_proximo_control }}</td>
             @elseif(!empty($student2->created_at))
                 <td>{{ $student2->created_at }}</td>
             @else
                 <td>finalizado</td>
             @endif
           
                 
                    
                       --}}
                       <td>
                        @if (DB::table('seguimientos')
    ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
    ->where('sivigilas.id', '=', $student2->id)
    ->whereExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('revisions')
              ->whereRaw('revisions.seguimientos_id = seguimientos.id');
    })
    ->exists())
    <div>
        <a href="" onclick="return false;" title="DETALLE" class="btn  btn-primary">
            <span class="icon-zoom-in" ></span> <i class="fas fa-check"></i>
        </a>
    </div>
@else
    <a href="{{route('detalle_revisiones', [$student2->id])}}" title="DETALLE" class="btn btn-danger">
        <span class="icon-zoom-in"></span><i class="fas fa-times"></i>
    </a>
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



