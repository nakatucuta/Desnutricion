@if(($seguimiento->estado == 1) && (in_array(Auth::user()->usertype, [1, 3]) || $seguimiento->usr == Auth::id()))
  @php
    $fechaControl = Carbon\Carbon::parse($seguimiento->fecha_proximo_control);
    $hoy = Carbon\Carbon::now();

    $diferenciaDias = $hoy->diffInDays($fechaControl, false);
    $diferenciaHoras = $hoy->diffInHours($fechaControl, false);

    $id = $seguimiento->idin;
    $identificacion = $seguimiento->num_ide_;
  @endphp

  @if($diferenciaDias < 0)
    {{-- 🔴 ALERTA ROJA --}}
    <div class="alert alert-danger shadow-sm p-3 mb-3 rounded d-flex align-items-start">
      <i class="fas fa-exclamation-circle fa-2x mr-3 text-white bg-danger p-2 rounded-circle"></i>
      <div>
        <h5 class="mb-1 font-weight-bold">Seguimiento vencido</h5>
        <p class="mb-1">
          El seguimiento <strong>#{{ $id }}</strong> (Identificación: <strong>{{ $identificacion }}</strong>) ha
          <strong>superado su fecha límite</strong> ({{ $fechaControl->format('d/m/Y') }}).
          Han pasado <strong>{{ abs(intval($diferenciaDias)) }} días</strong>.
        </p>
        <a href="{{ route('Seguimiento.create') }}" class="btn btn-outline-light btn-sm mt-1">
          <i class="fas fa-tools"></i> Gestionar ahora
        </a>
      </div>
    </div>

  @elseif($diferenciaDias === 1 || $diferenciaDias === 2)
    {{-- 🟡 ALERTA AMARILLA --}}
    <div class="alert alert-warning shadow-sm p-3 mb-3 rounded d-flex align-items-start">
      <i class="fas fa-hourglass-half fa-2x mr-3 text-dark bg-warning p-2 rounded-circle"></i>
      <div>
        <h5 class="mb-1 font-weight-bold">Próximo a vencer</h5>
        <p class="mb-1">
          El seguimiento <strong>#{{ $id }}</strong> (Identificación: <strong>{{ $identificacion }}</strong>) tiene próximo control el
          <strong>{{ $fechaControl->format('d/m/Y') }}</strong>. Faltan <strong>{{ $diferenciaDias }} días</strong>.
        </p>
        <a href="{{ route('Seguimiento.create') }}" class="btn btn-outline-dark btn-sm mt-1">
          <i class="fas fa-calendar-check"></i> Agendar seguimiento
        </a>
      </div>
    </div>

  @elseif($diferenciaDias === 0)
    {{-- 🟢 ALERTA VERDE --}}
    <div class="alert alert-success shadow-sm p-3 mb-3 rounded d-flex align-items-start">
      <i class="fas fa-check-circle fa-2x mr-3 text-white bg-success p-2 rounded-circle"></i>
      <div>
        <h5 class="mb-1 font-weight-bold">Seguimiento para hoy</h5>
        <p class="mb-1">
          El seguimiento <strong>#{{ $id }}</strong> (Identificación: <strong>{{ $identificacion }}</strong>) tiene fecha de próximo control
          <strong>hoy</strong> ({{ $fechaControl->format('d/m/Y') }}). Puedes gestionarlo ahora.
        </p>
        <a href="{{ route('Seguimiento.create') }}" class="btn btn-outline-light btn-sm mt-1">
          <i class="fas fa-plus-circle"></i> Registrar seguimiento
        </a>
      </div>
    </div>
  @endif
@endif
