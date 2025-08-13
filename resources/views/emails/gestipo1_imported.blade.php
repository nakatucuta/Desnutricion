{{-- resources/views/emails/ges_tipo1_imported.blade.php --}}

@component('mail::message')
{{-- Cabecera --}}
**Usuario que realizó el cargue:** {{ auth()->user()->name }}


# Importación de Gestantes (Tipo 1) completada

Se importaron **{{ $records->count() }}** registros correctamente:

<table style="border-collapse: collapse; width: 100%; margin-top: 1rem;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Tipo Reg.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Consec.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">País</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Municipio</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Zona</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Tipo ID</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">No. ID</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Carnet</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Nombres</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Apellidos</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">F. Nac.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Étnico</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Ocupación</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Nivel Educ.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">FPP</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">HTA</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Preecl.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Diabetes</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Autoim.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">S. Metab.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">ERC</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Trombof.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Anemia</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Sepsis</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Tabaco</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Intergén.</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Múltiple</th>
            <th style="border:1px solid #ccc;padding:8px;text-align:left;">Método Conc.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $r)
        <tr>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->tipo_de_registro }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->consecutivo }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->pais_de_la_nacionalidad }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->municipio_de_residencia_habitual }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->zona_territorial_de_residencia }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->tipo_de_identificacion_de_la_usuaria }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->no_id_del_usuario }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->numero_carnet }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ "{$r->primer_nombre} {$r->segundo_nombre}" }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ "{$r->primer_apellido} {$r->segundo_apellido}" }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->fecha_de_nacimiento }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->codigo_pertenencia_etnica }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->codigo_de_ocupacion }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->codigo_nivel_educativo_de_la_gestante }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->fecha_probable_de_parto }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_hipertension_cronica }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_preeclampsia }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_diabetes }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_les_enfermedad_autoinmune }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_sindrome_metabolico }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_erc }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_trombofilia_o_trombosis_venosa_profunda }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedentes_anemia_celulas_falciformes }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->antecedente_sepsis_durante_gestaciones_previas }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->consumo_tabaco_durante_la_gestacion }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->periodo_intergenesico }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->embarazo_multiple }}</td>
            <td style="border:1px solid #ccc;padding:8px;">{{ $r->metodo_de_concepcion }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>
Gracias por utilizar la plataforma.<br><br>
Saludos,<br>
**RUTAS INTEGRALES**
@endcomponent
