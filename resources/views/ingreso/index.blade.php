
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
@include('ingreso.mensajes')

{{-- boton para abrir la modal --}}
<button type="button" class="btn {{$conteo > 0 ? 'btn-danger' : 'btn-primary'}}" data-toggle="modal" data-target="#exampleModal" style="float: right;
  margin-right: 0;
  width: 7%;
  position: relative;
  right: 0;">
  <i class="fas fa-bell"> {{$conteo}}</i>
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
<h1>Listado De Ingresos</h1>
</div>
<br>
<a href="{{route('Ingreso.create')}}" title="DETALLE" class="btn  btn-primary">
  <span class="icon-zoom-in" ></span> NUEVO INGRESO</a>
  
  <a href="{{route('export1')}}" class="btn  btn-success " style="float: right;
  margin-right: 0;
  width: 14%;
  position: relative;
  right: 0;"><i class="fas fa-book"></i>   REPORTE</a>
 
  <br>
  <strong>Total {{ $master->total() }} </strong> <br> 
    
    {{-- <section class="content-header">
      
        <h1 class="pull-right">
        
            
            <a class="btn btn-primary pull-right"
            style="margin-top: -10px;
            margin-bottom: 5px" href="{{url('sivigila/create')}}">Realizar seguimiento</a>
            
        </h1>
        </section> --}}
{{-- 
        <div>
          <input type="text" class="form-control" id="search" placeholder="Buscar...">
        </div> --}}
        
@stop

            @section('content')
            
            {{-- @if(auth()->user()->usertype == 2  ) --}}

            <table class="table table-hover table-striped" id="ingreso">
                <thead class="table table-hover table-dark">
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">FECHA INGRESO</th>
                    <th scope="col">IDENTIFICACION</th>
                    <th scope="col">NOMBRE</th>
                    
                     <th scope="col">IPS ATENCION</th>
                     @if(auth()->user()->usertype == 3  )
                     <th scope="col">usuario</th>
                     <th scope="col">ACCIONES</th>
                     @else
                    <th scope="col">ACCIONES</th>
                    @endif
                  </tr>
                </thead>
                <tbody id="table">
                  <tr>
                    {{-- @if(auth()->user()->usertype = 2  ) --}}
                    @php
                    $user_id = Auth::id(); // Obtener el ID del usuario activo
                    $count = DB::table('ingresos')->where('user_id', $user_id)->count(); // Contar los registros de ingresos del usuario activo
                    $count1 = DB::table('ingresos')->count(); // Contar los registros de ingresos del usuario activo
                   
                @endphp
                    
                    @if($count < 1 && auth()->user()->usertype == 2) 
                    <td  class="text-center">No hay registros disponibles</td>
                    <td  class="text-center">No hay registros disponibles</td>
                    <td  class="text-center">No hay registros disponibles</td>
                    <td  class="text-center">No hay registros disponibles</td>
                    <td  class="text-center">No hay registros disponibles</td>
                    <td  class="text-center">No hay registros disponibles</td>
                    @elseif($count >= 1 && (auth()->user()->usertype == 2))
                       

                          {{-- @elseif($count >= 1 && auth()->user()->usertype == 1) --}}
                          
                    @foreach($master as $student2)
                    <th scope="row">{{ $student2->id }}</th>
                    <td>{{$student2->Fecha_ingreso_ingres}}</td>
                    <td>{{$student2->num_ide_}}</td>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_ }}</td>
                    <td>{{$student2->Nom_ips_at_prim}}</td>
                    
                  
                    
                    
                        
                      <td>  
                       
                        
                        @if(auth()->user()->usertype == 2  )

                        @else
                        <a class="btn  btn-warning" href="{{url('/Ingreso/'.$student2->id. '/edit')}}" class="ref" >
                        <i class="fas fa-edit"></i>
                    </a>
                  
                    

                  <a href="{{route('Ingreso.destroy', $student2->id)}}"
                    onclick="event.preventDefault();
                    if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                    document.getElementById('delete-form-{{$student2->id}}').submit();
                    }" class="btn  btn-danger">
                   <i class="fas fa-trash"></i>
                 </a>
                 <form id="delete-form-{{$student2->id}}" action="{{route('Ingreso.destroy', $student2->id)}}"
                  method="POST" style="display: none;">
                @method('DELETE')
                @csrf
            </form>
                

                {{-- <h5><strong aling = "center">NO SE PUEDE ELMINAR FACTURA YA QUE AUN TIENE PRODUCTOS CARGADOS DEBE DEVOLVER TODOS LOS PRODUCTOS CARGADOS PARA ELIMINAR UNA FACTURA</strong></h5>
            --}}
          @endif
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
                  @if($count1 < 1 &&  auth()->user()->usertype == 3)
                  <td  class="text-center">No hay registros disponibles</td>
                  
                  @endif
                 @elseif($count1 >= 1 && (auth()->user()->usertype == 1 ||  auth()->user()->usertype == 3))
                 @foreach($master as $student2)
                    <th scope="row">{{ $student2->id }}</th>
                    <td>{{$student2->Fecha_ingreso_ingres}}</td>
                    <td>{{$student2->num_ide_}}</td>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_ }}</td>
                    <td>{{$student2->Nom_ips_at_prim}}</td>
                    
                  
                    
                    
                        
                       
                       
                        
                        @if(auth()->user()->usertype == 3  )
                        <td>{{$student2->usu}}</td>
                        <td> 
                          <a class="btn  btn-warning" href="{{url('/Ingreso/'.$student2->id. '/edit')}}" class="ref" >
                          <i class="fas fa-edit"></i>
                      </a>
                    
                      
  
                    <a href="{{route('Ingreso.destroy', $student2->id)}}"
                      onclick="event.preventDefault();
                      if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                      document.getElementById('delete-form-{{$student2->id}}').submit();
                      }" class="btn  btn-danger">
                     <i class="fas fa-trash"></i>
                   </a>
                   <form id="delete-form-{{$student2->id}}" action="{{route('Ingreso.destroy', $student2->id)}}"
                    method="POST" style="display: none;">
                  @method('DELETE')
                  @csrf
              </form>
                  
  
                  {{-- <h5><strong aling = "center">NO SE PUEDE ELMINAR FACTURA YA QUE AUN TIENE PRODUCTOS CARGADOS DEBE DEVOLVER TODOS LOS PRODUCTOS CARGADOS PARA ELIMINAR UNA FACTURA</strong></h5>
              --}}
            
                    </td>
                        @else
                        <td> 
                        <a class="btn  btn-warning" href="{{url('/Ingreso/'.$student2->id. '/edit')}}" class="ref" >
                        <i class="fas fa-edit"></i>
                    </a>
                  
                    

                  <a href="{{route('Ingreso.destroy', $student2->id)}}"
                    onclick="event.preventDefault();
                    if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                    document.getElementById('delete-form-{{$student2->id}}').submit();
                    }" class="btn  btn-danger">
                   <i class="fas fa-trash"></i>
                 </a>
                 <form id="delete-form-{{$student2->id}}" action="{{route('Ingreso.destroy', $student2->id)}}"
                  method="POST" style="display: none;">
                @method('DELETE')
                @csrf
            </form>
                

                {{-- <h5><strong aling = "center">NO SE PUEDE ELMINAR FACTURA YA QUE AUN TIENE PRODUCTOS CARGADOS DEBE DEVOLVER TODOS LOS PRODUCTOS CARGADOS PARA ELIMINAR UNA FACTURA</strong></h5>
            --}}
          @endif
                  </td>
                   
                            
                
                  </tr>
                
                 
             
                  
                  @endforeach 
                  @endif
                </tbody>
                
              </table>

              


              
            
          
             
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
    font-weight: bold !important;
  }
  
  .dataTables_filter label  {
    font-weight: bold !important;
  }
  
   .dataTables_length label  {
    
    font-weight: bold !important;
  } 
  
  .dataTables_length select  {
    display: flex ;
    border: solid 3px !important;
    border-radius: 20px !important;
    align-items: center !important;
    margin-bottom: 10px !important;
    color: rgb(64, 125, 232) !important;
  }
 </style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>


<script>
  $(document).ready(function () {
    $('#ingreso').DataTable({

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


              "columnDefs": [
      {
        "targets": [0],
        "visible": false
      }
    ],

    });
});
</script>


{{-- <script type="text/javascript"> 
  $(document).ready(function(){
      $("#search").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#table tr").filter(function() {
              $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
      });
  });
</script> --}}
@stop



