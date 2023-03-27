
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
  <i class="fas fa-bell"> {{$conteo}}  </i>
</button>
{{-- aqui termina el boton --}}


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

  {{-- aqui finliza la modal --}}
<div>
<h1>Listado De Seguimientos</h1>
</div>
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
                  <table class="table table-hover table-striped table-bordered  {{-- table-responsive--}}" style="border: 1px solid #000000;" id="seguimiento"> 
                    <thead class="table table-hover table-info table-bordered " style="background-color: #d9f2e6 ;border: 1px solid #000000;">
                       
                      <tr>
                    <th>ID</th>
                    <th >Identificacion</th>
                    <th >Nombre</th>
                    <th >Estado</th>
                    <th >Ips</th>
                    <th >Fecha proximo controls</th>
                    <th >Acciones</th>
                  </tr>
                </thead>
                <tbody id="table">
                  <tr>

                    @php
                    $user_id = Auth::id(); // Obtener el ID del usuario activo
                    $count = DB::table('seguimientos')->where('user_id', $user_id)->count();
                    $count1 = DB::table('seguimientos')->count(); // Contar los registros de ingresos del usuario activo
                    @endphp
                    
                    @if($count < 1 && auth()->user()->usertype == 2) 
                    
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                        @elseif($count >= 1 && (auth()->user()->usertype == 2))
                          

                          {{-- @if($count == 0)
                          
                          @endif --}}
                          {{-- @elseif($count >= 1 && auth()->user()->usertype == 1) --}}
                         
                    {{-- @elseif($count1 >= 1 && auth()->user()->usertype == 1) 
                     --}}
                     
                    @foreach($incomeedit as $student2)
                    
                    <th >{{ $student2->id }}</th>
                    <th >{{ $student2->num_ide_ }}</th>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                    
                    <td> @if ($student2->estado == 1)
                     Abierto
                   @else
                     Cerrado
                   @endif</td>
                    <td>{{$student2->Ips_at_inicial}}</td>
                    @if(!empty($student2->fecha_proximo_control))
                    <td>{{ $student2->fecha_proximo_control }}</td>
                @elseif(!empty($student2->created_at))
                    <td>{{ $student2->created_at }}</td>
                @else
                    <td>finalizado</td>
                @endif
                      <td>  <a class="btn  btn-success" href="{{url('/Seguimiento/'.$student2->id. '/edit')}}" class="ref" >
                        <i class="fas fa-edit"></i>

                        
                    </a>
                  
                    
                    
                    {{-- <a href="{{route('Seguimiento.destroy', $student2->id)}}"
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
                  --}}
                    </td>
                
                            
                    </td>
                  </tr>
                
                 
             
                  
                  @endforeach 
                  @endif
                  
                  @if($count1 < 1 && (auth()->user()->usertype == 1 ||  auth()->user()->usertype == 3)) 
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                 @elseif($count1 >= 1 && (auth()->user()->usertype == 1 ||  auth()->user()->usertype == 3))
                 @foreach($incomeedit as $student2)
                        
                 <th >{{ $student2->id }}</th>
                 <th >{{ $student2->num_ide_ }}</th>
                 <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                 
                 <td> @if ($student2->estado == 1)
                  Abierto
                @else
                  Cerrado
                @endif</td>
                 <td>{{$student2->Ips_at_inicial}}</td>
                 @if(!empty($student2->fecha_proximo_control))
                 <td>{{ $student2->fecha_proximo_control }}</td>
             @elseif(!empty($student2->created_at))
                 <td>{{ $student2->created_at }}</td>
             @else
                 <td>finalizado</td>
             @endif
                 
                   <td>  <a class="btn  btn-success" href="{{url('/Seguimiento/'.$student2->id. '/edit')}}" class="ref" >
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
                  @endif


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
        },
        "autoWidth": true
    });
});
</script>
@stop



