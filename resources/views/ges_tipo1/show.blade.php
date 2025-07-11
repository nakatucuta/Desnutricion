{{-- resources/views/ges_tipo1/show.blade.php --}}

@extends('adminlte::page')

@section('title', 'Detalle Gestante')

@section('content_header')
    <h1 class="mb-0 text-primary">
        <i class="fas fa-user-circle mr-2"></i>Detalle de la Gestante
    </h1>
@stop

@section('content')
    {{-- Volver --}}
    <div class="mb-4">
        <a href="{{ route('ges_tipo1.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>
{{-- Card: Datos Personales --}}
<div class="card card-outline card-primary mb-4">
    <div class="card-header bg-white">
        <h3 class="card-title">
            <i class="fas fa-id-card-alt text-primary mr-2"></i>Datos Personales
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- Tabla izquierda --}}
            <div class="col-md-6 mb-4">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light w-50">Nombre Completo</th>
                            <td>{{ "{$gestante->primer_nombre} {$gestante->segundo_nombre} {$gestante->primer_apellido} {$gestante->segundo_apellido}" }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Tipo ID</th>
                            <td>{{ $gestante->tipo_de_identificacion_de_la_usuaria }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">No. ID Usuario</th>
                            <td>{{ $gestante->no_id_del_usuario }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Número Carnet</th>
                            <td>{{ $gestante->numero_carnet }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Fecha Nacimiento</th>
                            <td>{{ $gestante->fecha_de_nacimiento }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Probable Parto</th>
                            <td>{{ $gestante->fecha_probable_de_parto }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Tabla derecha --}}
            <div class="col-md-6 mb-4">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light w-50">Tipo de Registro</th>
                            <td>{{ $gestante->tipo_de_registro }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Consecutivo</th>
                            <td>{{ $gestante->consecutivo }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">País</th>
                            <td>{{ $gestante->pais_de_la_nacionalidad }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Municipio</th>
                            <td>{{ $gestante->municipio_de_residencia_habitual }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Zona Territorial</th>
                            <td>{{ $gestante->zona_territorial_de_residencia }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">IPS Primaria</th>
                            <td>{{ $gestante->codigo_de_habilitacion_ips_primaria_de_la_gestante }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            {{-- Segunda fila: más datos --}}
            <div class="col-md-6 mb-4">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light w-50">Étnica</th>
                            <td>{{ $gestante->codigo_pertenencia_etnica }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Ocupación</th>
                            <td>{{ $gestante->codigo_de_ocupacion }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Nivel Educ.</th>
                            <td>{{ $gestante->codigo_nivel_educativo_de_la_gestante }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Dirección</th>
                            <td>{{ $gestante->direccion_de_residencia_de_la_gestante }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 mb-4">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light w-50">Tabaco</th>
                            <td>{{ $gestante->consumo_tabaco_durante_la_gestacion }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Embarazo Múltiple</th>
                            <td>{{ $gestante->embarazo_multiple }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Intergenésico</th>
                            <td>{{ $gestante->periodo_intergenesico }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Método Concepción</th>
                            <td>{{ $gestante->metodo_de_concepcion }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-right text-sm text-muted">
            Creado: {{ $gestante->created_at->format('d/m/Y H:i') }} &middot;
            Actualizado: {{ $gestante->updated_at->format('d/m/Y H:i') }}
        </div>
    </div>
</div>



    {{-- Card: Antecedentes Médicos --}}
    <div class="card card-outline card-info mb-4">
        <div class="card-header bg-white">
            <h3 class="card-title">
                <i class="fas fa-notes-medical text-info mr-2"></i>Antecedentes Médicos
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach([
                    'Hipertensión'            => $gestante->antecedente_hipertension_cronica,
                    'Preeclampsia'            => $gestante->antecedente_preeclampsia,
                    'Diabetes'                => $gestante->antecedente_diabetes,
                    'Autoinmune'              => $gestante->antecedente_les_enfermedad_autoinmune,
                    'Síndrome Metabólico'     => $gestante->antecedente_sindrome_metabolico,
                    'ERC'                     => $gestante->antecedente_erc,
                    'Trombofilia/TVP'         => $gestante->antecedente_trombofilia_o_trombosis_venosa_profunda,
                    'Anemia Falciformes'      => $gestante->antecedentes_anemia_celulas_falciformes,
                    'Sepsis Previas'          => $gestante->antecedente_sepsis_durante_gestaciones_previas,
                ] as $label => $value)
                    <div class="col-md-4 mb-2">
                        <strong>{{ $label }}:</strong> {{ $value }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Card: Registros Tipo 3 --}}
    <div class="card card-outline card-success">
        <div class="card-header bg-white">
            <h3 class="card-title">
                <i class="fas fa-file-medical-alt text-success mr-2"></i>Registros Tipo 3 Asociados
            </h3>
        </div>
        <div class="card-body">
            @if($gestante->tipo3->isEmpty())
                <div class="alert alert-warning mb-0">
                    No hay registros de Tipo 3 para esta gestante.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha Tec.</th>
                                <th>CUPS</th>
                                <th>Finalidad</th>
                                <th>Riesgo Gest.</th>
                                <th>Riesgo Pree.</th>
                                <th>ASA</th>
                                <th>Ác. Fólico</th>
                                <th>Ferroso</th>
                                <th>Calcio</th>
                                <th>Fecha Post</th>
                                <th>Met. Post</th>
                                <th>Salida</th>
                                <th>Term.</th>
                                <th>Tipo Term.</th>
                                <th>PAS</th>
                                <th>PAD</th>
                                <th>IMC</th>
                                <th>Hb</th>
                                <th>Índice Puls.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gestante->tipo3 as $i => $t3)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $t3->fecha_tecnologia_en_salud }}</td>
                                    <td>{{ $t3->codigo_cups_de_la_tecnologia_en_salud }}</td>
                                    <td>{{ $t3->finalidad_de_la_tecnologia_en_salud }}</td>
                                    <td>{{ $t3->clasificacion_riesgo_gestacional }}</td>
                                    <td>{{ $t3->clasificacion_riesgo_preeclampsia }}</td>
                                    <td>{{ (int)$t3->suministro_acido_acetilsalicilico_ASA }}</td>
                                    <td>{{ (int)$t3->suministro_acido_folico_en_el_control_prenatal }}</td>
                                    <td>{{ (int)$t3->suministro_sulfato_ferroso_en_el_control_prenatal }}</td>
                                    <td>{{ (int)$t3->suministro_calcio_en_el_control_prenatal }}</td>
                                    <td>{{ $t3->fecha_suministro_de_anticonceptivo_post_evento_obstetrico }}</td>
                                    <td>{{ (int)$t3->suministro_metodo_anticonceptivo_post_evento_obstetrico }}</td>
                                    <td>{{ $t3->fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea }}</td>
                                    <td>{{ $t3->fecha_de_terminacion_de_la_gestacion }}</td>
                                    <td>{{ $t3->tipo_de_terminacion_de_la_gestacion }}</td>
                                    <td>{{ $t3->tension_arterial_sistolica_PAS_mmHg }}</td>
                                    <td>{{ $t3->tension_arterial_diastolica_PAD_mmHg }}</td>
                                    <td>{{ $t3->indice_de_masa_corporal }}</td>
                                    <td>{{ $t3->resultado_de_la_hemoglobina }}</td>
                                    <td>{{ $t3->indice_de_pulsatilidad_de_arterias_uterinas }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop
