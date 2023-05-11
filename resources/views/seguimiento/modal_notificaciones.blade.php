
{{-- boton para abrir la modal --}}
<button type="button" class="btn {{$conteo > 0 ? 'btn-danger btn-sm' : 'btn-primary btn-sm'}} rounded-circle p-0" data-toggle="modal" data-target="#exampleModal" style="float: right; width: 40px; height: 40px; position: relative; right: 0;">
    <i class="fas fa-bell fa-2x text-white p-2" style="background-color: {{$conteo > 0 ? '#dc3545' : '#007bff'}}; border-radius: 75%; animation: {{$conteo > 0 ? 'pulse 1s ease-in-out infinite' : 'none'}};"></i>
    <span class="badge badge-light position-absolute" style="top: -10px; right: -10px; font-size: 0.8rem; {{$conteo > 0 ? 'animation: pulse 1s ease-in-out infinite' : 'none'}};">{{$conteo}}</span>
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




    {{-- CODIGO QUE YA NO UTILIZAS QUE ESTABA EN LA INDEX ESTORBANDO --}}

    {{-- BUSCADOR VIEJO QUE HICISTE --}}

        {{-- <form action="{{ route('BUSCADOR1')}}" method="GET" role="search">
  <div class="input-group">
    <input type="text" name="q" id="q" class="form-control" placeholder="Search..."> <span class="input-group-btn">
          <button type="submit" class="btn btn-primary">
              <span class="glyphicon glyphicon-search" ><i class="fas fa-search"></i></span>
          </button>
         
      
  </div>
 
</form> --}}