@component('mail::message')
# Alerta de seguimiento vencido

**Paciente:** {{ $asignacion ? trim($asignacion->pri_nom_.' '.$asignacion->seg_nom_.' '.$asignacion->pri_ape_.' '.$asignacion->seg_ape_) : 'N/D' }}  
**Identificación:** {{ $asignacion ? ($asignacion->tip_ide_.' '.$asignacion->num_ide_) : 'N/D' }}  
**Evento:** {{ $asignacion->nom_eve ?? 'N/D' }}  
**Fecha notificación:** {{ $asignacion->fec_not ?? 'N/D' }}

---

**Hito vencido:** {{ $hito }}  
**Fecha límite:** {{ $fechaLimite->toDateString() }}  
**Días de atraso:** {{ $diasAtraso }}

@component('mail::button', ['url' => $editUrl])
Actualizar seguimiento
@endcomponent

Gracias,  
{{ config('app.name') }}
@endcomponent
