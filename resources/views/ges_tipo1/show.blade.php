{{-- resources/views/ges_tipo1/show.blade.php --}}

@extends('adminlte::page')

@section('title', 'Detalle Gestante')

@section('content_header')
    <h1>Detalle Gestante</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('ges_tipo1.index') }}" class="btn btn-sm btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>

        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>Tipo de Registro</th>
                    <td>{{ $gestante->tipo_de_registro }}</td>
                </tr>
                <tr>
                    <th>Consecutivo</th>
                    <td>{{ $gestante->consecutivo }}</td>
                </tr>
                <tr>
                    <th>País de la Nacionalidad</th>
                    <td>{{ $gestante->pais_de_la_nacionalidad }}</td>
                </tr>
                <tr>
                    <th>Municipio de Residencia Habitual</th>
                    <td>{{ $gestante->municipio_de_residencia_habitual }}</td>
                </tr>
                <tr>
                    <th>Zona Territorial de Residencia</th>
                    <td>{{ $gestante->zona_territorial_de_residencia }}</td>
                </tr>
                <tr>
                    <th>Código de Habilitación IPS Primaria</th>
                    <td>{{ $gestante->codigo_de_habilitacion_ips_primaria_de_la_gestante }}</td>
                </tr>
                <tr>
                    <th>Tipo de Identificación de la Usuaria</th>
                    <td>{{ $gestante->tipo_de_identificacion_de_la_usuaria }}</td>
                </tr>
                <tr>
                    <th>No. ID del Usuario</th>
                    <td>{{ $gestante->no_id_del_usuario }}</td>
                </tr>
                <tr>
                    <th>Número Carnet</th>
                    <td>{{ $gestante->numero_carnet }}</td>
                </tr>
                <tr>
                    <th>Primer Apellido</th>
                    <td>{{ $gestante->primer_apellido }}</td>
                </tr>
                <tr>
                    <th>Segundo Apellido</th>
                    <td>{{ $gestante->segundo_apellido }}</td>
                </tr>
                <tr>
                    <th>Primer Nombre</th>
                    <td>{{ $gestante->primer_nombre }}</td>
                </tr>
                <tr>
                    <th>Segundo Nombre</th>
                    <td>{{ $gestante->segundo_nombre }}</td>
                </tr>
                <tr>
                    <th>Fecha de Nacimiento</th>
                    <td>{{ $gestante->fecha_de_nacimiento }}</td>
                </tr>
                <tr>
                    <th>Código Pertenencia Étnica</th>
                    <td>{{ $gestante->codigo_pertenencia_etnica }}</td>
                </tr>
                <tr>
                    <th>Código de Ocupación</th>
                    <td>{{ $gestante->codigo_de_ocupacion }}</td>
                </tr>
                <tr>
                    <th>Código Nivel Educativo</th>
                    <td>{{ $gestante->codigo_nivel_educativo_de_la_gestante }}</td>
                </tr>
                <tr>
                    <th>Fecha Probable de Parto</th>
                    <td>{{ $gestante->fecha_probable_de_parto }}</td>
                </tr>
                <tr>
                    <th>Dirección de Residencia</th>
                    <td>{{ $gestante->direccion_de_residencia_de_la_gestante }}</td>
                </tr>
                <tr>
                    <th>Antecedente Hipertensión Crónica</th>
                    <td>{{ $gestante->antecedente_hipertension_cronica }}</td>
                </tr>
                <tr>
                    <th>Antecedente Preeclampsia</th>
                    <td>{{ $gestante->antecedente_preeclampsia }}</td>
                </tr>
                <tr>
                    <th>Antecedente Diabetes</th>
                    <td>{{ $gestante->antecedente_diabetes }}</td>
                </tr>
                <tr>
                    <th>Antecedente Enfermedad Autoinmune</th>
                    <td>{{ $gestante->antecedente_les_enfermedad_autoinmune }}</td>
                </tr>
                <tr>
                    <th>Antecedente Síndrome Metabólico</th>
                    <td>{{ $gestante->antecedente_sindrome_metabolico }}</td>
                </tr>
                <tr>
                    <th>Antecedente ERC</th>
                    <td>{{ $gestante->antecedente_erc }}</td>
                </tr>
                <tr>
                    <th>Antecedente Trombofilia / Trombosis Venosa</th>
                    <td>{{ $gestante->antecedente_trombofilia_o_trombosis_venosa_profunda }}</td>
                </tr>
                <tr>
                    <th>Anemia Células Falciformes</th>
                    <td>{{ $gestante->antecedentes_anemia_celulas_falciformes }}</td>
                </tr>
                <tr>
                    <th>Antecedente Sepsis en Gestaciones Previas</th>
                    <td>{{ $gestante->antecedente_sepsis_durante_gestaciones_previas }}</td>
                </tr>
                <tr>
                    <th>Consumo de Tabaco Durante la Gestación</th>
                    <td>{{ $gestante->consumo_tabaco_durante_la_gestacion }}</td>
                </tr>
                <tr>
                    <th>Período Intergenésico</th>
                    <td>{{ $gestante->periodo_intergenesico }}</td>
                </tr>
                <tr>
                    <th>Embarazo Múltiple</th>
                    <td>{{ $gestante->embarazo_multiple }}</td>
                </tr>
                <tr>
                    <th>Método de Concepción</th>
                    <td>{{ $gestante->metodo_de_concepcion }}</td>
                </tr>
                <tr>
                    <th>Creado En</th>
                    <td>{{ $gestante->created_at }}</td>
                </tr>
                <tr>
                    <th>Actualizado En</th>
                    <td>{{ $gestante->updated_at }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@stop
