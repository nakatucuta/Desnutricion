@component('mail::message')
{{-- Cabecera con logo embebido --}}
{!! '<p style="text-align:center; margin-bottom:1rem;">
        <img src="cid:escudo.png" alt="Escudo PAI" style="max-width:120px; height:auto;">
    </p>' !!}

# Cargue de Tipo 3 completada

Se Cargaron **{{ $records->count() }}** registros correctamente:

<table style="border-collapse: collapse; width: 100%; margin-top: 1rem;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">ID</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Nombre Completo</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">No. ID</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Carnet</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Fecha Tec.</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Tipo Reg.</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Consec.</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Riesgo Gest.</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">Riesgo Pree.</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">PAS</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">PAD</th>
            <th style="border:1px solid #ccc; padding:8px; text-align:left;">IMC</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $r)
            @php
                $g = $r->gesTipo1;
                $fullName = $g
                    ? trim("{$g->primer_nombre} {$g->segundo_nombre} {$g->primer_apellido} {$g->segundo_apellido}")
                    : '';
                $idNumber = $g ? $g->no_id_del_usuario : '';
                $carnet   = $g ? $g->numero_carnet : '';
            @endphp
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->id }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $fullName }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $idNumber }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $carnet }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->fecha_tecnologia_en_salud }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->tipo_de_registro }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->consecutivo_de_registro }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->clasificacion_riesgo_gestacional }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->clasificacion_riesgo_preeclampsia }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->tension_arterial_sistolica_PAS_mmHg }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->tension_arterial_diastolica_PAD_mmHg }}</td>
                <td style="border:1px solid #ccc; padding:8px;">{{ $r->indice_de_masa_corporal }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>
Gracias por utilizar la plataforma.<br><br>
Saludos,<br>
**RUTAS INTEGRALES**
@endcomponent
