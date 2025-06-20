@component('mail::message')
# Importación de Gestantes finalizada

Hola **{{ $user->name }}**,  
se han importado **{{ $imported }}** registros satisfactoriamente.

@if(count($details))
**Detalles de los importados**  
| No ID Usuario | Nombre Completo                 |
|:-------------:|:--------------------------------|
@foreach($details as $d)
| {{ $d['no_id_del_usuario'] }} | {{ $d['full_name'] }} |
@endforeach
@endif

Gracias,<br>
{{ config('app.name') }}
@endcomponent
