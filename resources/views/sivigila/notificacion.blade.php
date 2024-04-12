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