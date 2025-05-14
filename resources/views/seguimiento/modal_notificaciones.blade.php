{{-- BOTÓN PARA ABRIR LA MODAL --}}
<button type="button" class="btn {{ $conteo > 0 ? 'btn-danger btn-sm btn-pulse' : 'btn-primary btn-sm btn-pulse' }} rounded-circle p-0"
  data-toggle="modal" data-target="#exampleModal"
  style="float: right; width: 40px; height: 40px; position: relative; right: 0;">
  <i class="fas fa-bell fa-2x text-white p-2"
     style="background-color: {{ $conteo > 0 ? '#dc3545' : '#007bff' }}; border-radius: 75%;"></i>
  <span class="badge badge-light position-absolute"
        style="top: -10px; right: -10px; font-size: 0.8rem;">{{ $conteo }}</span>
</button>

{{-- MODAL --}}
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

        @if(isset($otro) && $otro->count() > 0)
          @foreach($otro as $seguimiento)

            {{-- Para tipo 2 (solo sus propios) --}}
            @if($seguimiento->usr == Auth::id() && Auth::user()->usertype == 2 && $seguimiento->fecha_proximo_control)
              @include('seguimiento.notificacion_individual', ['seguimiento' => $seguimiento])
            @endif

            {{-- Para tipo 1 y 3 (todos) --}}
            @if(in_array(Auth::user()->usertype, [1, 3]) && $seguimiento->fecha_proximo_control)
              @include('seguimiento.notificacion_individual', ['seguimiento' => $seguimiento])
            @endif

          @endforeach
        @else
          <div class="alert alert-info">No hay notificaciones pendientes por el momento.</div>
        @endif

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>
