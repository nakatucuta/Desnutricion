@component('mail::message')
# ¡Nuevo Caso Asignado!

Estimado/a **{{ $usuarioAsignado->name }}**,

Se le ha asignado un nuevo caso en el sistema **MaestroSiv549**.

@component('mail::panel')
<strong>Asignado por:</strong> {{ $usuarioAsignador->name }} ({{ $usuarioAsignador->email }})  
<strong>Paciente:</strong> {{ $asignacion->pri_nom_ }} {{ $asignacion->seg_nom_ }} {{ $asignacion->pri_ape_ }} {{ $asignacion->seg_ape_ }}  
<strong>Tipo ID:</strong> {{ $asignacion->tip_ide_ }}  
<strong>Número ID:</strong> {{ $asignacion->num_ide_ }}  
<strong>Evento:</strong> {{ $asignacion->nom_eve }}  
<strong>Fecha Notificación:</strong> {{ $asignacion->fec_not }}
@endcomponent

Por favor ingrese al sistema para consultar el caso.

Saludos,  
**Equipo Rutas Integrales**
@endcomponent
