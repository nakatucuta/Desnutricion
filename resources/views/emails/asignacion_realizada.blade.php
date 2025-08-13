@component('mail::message')
# Asignación Realizada

Usted ha asignado correctamente el siguiente caso:

@component('mail::panel')
<strong>Asignado a:</strong> {{ $usuarioAsignado->name }} ({{ $usuarioAsignado->email }})  
<strong>Paciente:</strong> {{ $asignacion->pri_nom_ }} {{ $asignacion->seg_nom_ }} {{ $asignacion->pri_ape_ }} {{ $asignacion->seg_ape_ }}  
<strong>Tipo ID:</strong> {{ $asignacion->tip_ide_ }}  
<strong>Número ID:</strong> {{ $asignacion->num_ide_ }}  
<strong>Evento:</strong> {{ $asignacion->nom_eve }}  
<strong>Fecha Notificación:</strong> {{ $asignacion->fec_not }}
@endcomponent

Gracias por usar la plataforma.

@endcomponent
