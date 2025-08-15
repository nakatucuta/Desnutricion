@extends('adminlte::page')

@section('title', 'Seguimiento #'.$seguimiento->id.' — Caso #'.$asignacion->id)

@section('content_header')
    <h1 class="text-info">Detalle del seguimiento — Asignación #{{ $asignacion->id }}</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <div class="mb-3">
            <a href="{{ route('asignaciones.seguimientmaestrosiv549.edit', [$asignacion, $seguimiento]) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i>
            </a>
            <a href="{{ route('asignaciones.seguimientmaestrosiv549.index', $asignacion) }}" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <tbody>
                    <tr><th>Fecha hospitalización</th><td>{{ $seguimiento->fecha_hospitalizacion }}</td></tr>
                    <tr><th>Gestión en hospitalización</th><td>{{ $seguimiento->gestion_hospitalizacion }}</td></tr>
                    <tr><th>Fecha egreso</th><td>{{ $seguimiento->fecha_egreso }}</td></tr>
                    <tr><th>Seguimiento inmediato (48–72h)</th><td>{{ $seguimiento->descripcion_posegreso_48_72 }}</td></tr>

                    <tr class="table-active"><th colspan="2">Seguimiento 1</th></tr>
                    <tr><th>Fecha</th><td>{{ $seguimiento->fecha_seguimiento_1 }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $seguimiento->tipo_seguimiento_1 }}</td></tr>
                    <tr><th>Sigue en embarazo</th><td>{{ $seguimiento->sigue_embarazo_1 ? 'Sí' : 'No' }}</td></tr>
                    <tr><th>F. control especialista</th><td>{{ $seguimiento->fecha_control_especialista_1 }}</td></tr>
                    <tr><th>F. control RN</th><td>{{ $seguimiento->fecha_control_rn_1 }}</td></tr>
                    <tr><th>Entrega meds/labs</th><td>{{ $seguimiento->entrega_meds_labs_1 }}</td></tr>
                    <tr><th>Gestión posegreso</th><td>{{ $seguimiento->gestion_posegreso_1 }}</td></tr>

                    <tr class="table-active"><th colspan="2">Seguimiento 2 (7 días)</th></tr>
                    <tr><th>Fecha</th><td>{{ $seguimiento->fecha_seguimiento_2 }}</td></tr>
                    <tr><th>Sigue en embarazo</th><td>{{ $seguimiento->sigue_embarazo_2 ? 'Sí' : 'No' }}</td></tr>
                    <tr><th>F. control especialista</th><td>{{ $seguimiento->fecha_control_especialista_2 }}</td></tr>
                    <tr><th>F. control RN</th><td>{{ $seguimiento->fecha_control_rn_2 }}</td></tr>
                    <tr><th>Entrega meds/labs</th><td>{{ $seguimiento->entrega_meds_labs_2 }}</td></tr>
                    <tr><th>Gestión 1ra semana</th><td>{{ $seguimiento->gestion_semana_1 }}</td></tr>

                    <tr class="table-active"><th colspan="2">Seguimiento 3 (14 días)</th></tr>
                    <tr><th>Fecha</th><td>{{ $seguimiento->fecha_seguimiento_3 }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $seguimiento->tipo_seguimiento_3 }}</td></tr>
                    <tr><th>Sigue en embarazo</th><td>{{ $seguimiento->sigue_embarazo_3 ? 'Sí' : 'No' }}</td></tr>
                    <tr><th>F. control especialista</th><td>{{ $seguimiento->fecha_control_especialista_3 }}</td></tr>
                    <tr><th>F. control RN</th><td>{{ $seguimiento->fecha_control_rn_3 }}</td></tr>
                    <tr><th>Entrega meds/labs</th><td>{{ $seguimiento->entrega_meds_labs_3 }}</td></tr>
                    <tr><th>Gestión 2da semana</th><td>{{ $seguimiento->gestion_semana_2 }}</td></tr>

                    <tr class="table-active"><th colspan="2">Seguimiento 4 (21 días)</th></tr>
                    <tr><th>Fecha</th><td>{{ $seguimiento->fecha_seguimiento_4 }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $seguimiento->tipo_seguimiento_4 }}</td></tr>
                    <tr><th>Sigue en embarazo</th><td>{{ $seguimiento->sigue_embarazo_4 ? 'Sí' : 'No' }}</td></tr>
                    <tr><th>F. control especialista</th><td>{{ $seguimiento->fecha_control_especialista_4 }}</td></tr>
                    <tr><th>F. control RN</th><td>{{ $seguimiento->fecha_control_rn_4 }}</td></tr>
                    <tr><th>Entrega meds/labs</th><td>{{ $seguimiento->entrega_meds_labs_4 }}</td></tr>
                    <tr><th>Gestión 3ra semana</th><td>{{ $seguimiento->gestion_semana_3 }}</td></tr>

                    <tr class="table-active"><th colspan="2">Seguimiento 5 (28 días)</th></tr>
                    <tr><th>Fecha</th><td>{{ $seguimiento->fecha_seguimiento_5 }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $seguimiento->tipo_seguimiento_5 }}</td></tr>
                    <tr><th>Sigue en embarazo</th><td>{{ $seguimiento->sigue_embarazo_5 ? 'Sí' : 'No' }}</td></tr>
                    <tr><th>F. control especialista</th><td>{{ $seguimiento->fecha_control_especialista_5 }}</td></tr>
                    <tr><th>F. control RN</th><td>{{ $seguimiento->fecha_control_rn_5 }}</td></tr>
                    <tr><th>Entrega meds/labs</th><td>{{ $seguimiento->entrega_meds_labs_5 }}</td></tr>

                    <tr class="table-active"><th colspan="2">Controles complementarios</th></tr>
                    <tr><th>Fecha apoyo lactancia</th><td>{{ $seguimiento->fecha_lactancia }}</td></tr>
                    <tr><th>Método anticonceptivo provisto</th><td>{{ $seguimiento->metodo_anticonceptivo }}</td></tr>
                    <tr><th>F. primer control del método</th><td>{{ $seguimiento->fecha_primer_control_metodo }}</td></tr>
                    <tr><th>Gestión después del mes</th><td>{{ $seguimiento->gestion_despues_mes }}</td></tr>
                    <tr><th>F. consulta 6 meses</th><td>{{ $seguimiento->fecha_control_6m }}</td></tr>
                    <tr><th>F. consulta 1 año</th><td>{{ $seguimiento->fecha_control_1y }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
