@php
    $fechaControl = Carbon\Carbon::parse($seguimiento->fecha_proximo_control);
    $hoy = Carbon\Carbon::now();
    $diasDiferencia = (int) $hoy->diffInDays($fechaControl, false);
    $horasDiferencia = (int) $hoy->diffInHours($fechaControl, false);

    $nombrePaciente = trim(($seguimiento->pri_nom_ ?? '').' '.($seguimiento->seg_nom_ ?? '').' '.($seguimiento->pri_ape_ ?? '').' '.($seguimiento->seg_ape_ ?? ''));
    $estadoTexto = ((int) ($seguimiento->estado ?? 0) === 1) ? 'Abierto' : 'Cerrado';
    $responsable = trim((string) ($seguimiento->responsable_nombre ?? ''));
    $anioSeguimiento = Carbon\Carbon::parse($seguimiento->seguimiento_created_at ?? $seguimiento->fecha_proximo_control)->year;

    if ($diasDiferencia < 0) {
        $nivelClase = 'is-danger';
        $nivelTitulo = 'Vencido';
        $nivelDetalle = 'Este seguimiento ya supero su fecha limite.';
        $nivelIcono = 'fa-exclamation-triangle';
    } elseif ($diasDiferencia === 0) {
        $nivelClase = 'is-success';
        $nivelTitulo = 'Para hoy';
        $nivelDetalle = 'Este seguimiento debe gestionarse hoy.';
        $nivelIcono = 'fa-calendar-day';
    } else {
        $nivelClase = 'is-warning';
        $nivelTitulo = 'Proximo';
        $nivelDetalle = 'Este seguimiento esta cerca de su vencimiento.';
        $nivelIcono = 'fa-hourglass-half';
    }
@endphp

<article class="seg-notify-card412 {{ $nivelClase }}" data-year="{{ $anioSeguimiento }}">
    <div class="seg-notify-card412__head">
        <div class="seg-notify-card412__title-wrap">
            <span class="seg-notify-card412__icon">
                <i class="fas {{ $nivelIcono }}"></i>
            </span>
            <div>
                <h6 class="seg-notify-card412__title mb-0">Seguimiento #{{ $seguimiento->seguimiento_id ?? $seguimiento->idin }}</h6>
                <small class="seg-notify-card412__subtitle">{{ $nivelDetalle }}</small>
            </div>
        </div>
        <span class="seg-notify-card412__badge">{{ $nivelTitulo }}</span>
    </div>

    <div class="seg-notify-card412__grid">
        <div>
            <span class="seg-notify-card412__label">Paciente</span>
            <strong class="seg-notify-card412__value">{{ $nombrePaciente !== '' ? $nombrePaciente : 'Sin nombre' }}</strong>
        </div>
        <div>
            <span class="seg-notify-card412__label">Identificacion</span>
            <strong class="seg-notify-card412__value">{{ $seguimiento->num_ide_ ?? 'N/D' }}</strong>
        </div>
        <div>
            <span class="seg-notify-card412__label">Fecha proximo control</span>
            <strong class="seg-notify-card412__value">{{ $fechaControl->format('Y-m-d') }}</strong>
        </div>
        <div>
            <span class="seg-notify-card412__label">Estado del caso</span>
            <strong class="seg-notify-card412__value">{{ $estadoTexto }}</strong>
        </div>
        <div>
            <span class="seg-notify-card412__label">Responsable</span>
            <strong class="seg-notify-card412__value">{{ $responsable !== '' ? $responsable : 'Sin asignar' }}</strong>
        </div>
    </div>

    <div class="seg-notify-card412__timeline">
        @if($diasDiferencia < 0)
            <span><strong>{{ abs($diasDiferencia) }} dias</strong> de atraso</span>
        @elseif($diasDiferencia === 0)
            <span>Vence hoy, restan aprox. <strong>{{ max($horasDiferencia, 0) }} horas</strong></span>
        @else
            <span>Faltan <strong>{{ $diasDiferencia }} dias</strong> para el control</span>
        @endif
    </div>

    <div class="seg-notify-card412__actions">
        <a href="{{ route('new412_seguimiento.create') }}" class="btn btn-sm seg-notify-card412__btn">
            <i class="fas fa-clipboard-check mr-1"></i> Gestionar seguimiento
        </a>
    </div>
</article>
