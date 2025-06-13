@extends('adminlte::page')

@section('title', "Detalle Tamizaje #{$tamizaje->id}")

@section('content_header')
    <h1>Detalle de Tamizaje</h1>
@stop

@section('content')
<div class="container">
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver
    </a>

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h4 class="card-title">Tamizaje #{{ $tamizaje->id }}</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $tamizaje->id }}</td>
                    </tr>
                    <tr>
                        <th>Tipo Identificación</th>
                        <td>{{ $tamizaje->tipo_identificacion }}</td>
                    </tr>
                    <tr>
                        <th>Número Identificación</th>
                        <td>{{ $tamizaje->numero_identificacion }}</td>
                    </tr>
                    <tr>
                        <th>Número Carnet</th>
                        <td>{{ $tamizaje->numero_carnet }}</td>
                    </tr>
                    <tr>
                        <th>Nombre Completo</th>
                        <td>{{ $tamizaje->nombre_completo }}</td>
                    </tr>
                    <tr>
                        <th>Primer Nombre</th>
                        <td>{{ $tamizaje->primerNombre }}</td>
                    </tr>
                    <tr>
                        <th>Segundo Nombre</th>
                        <td>{{ $tamizaje->segundoNombre }}</td>
                    </tr>
                    <tr>
                        <th>Primer Apellido</th>
                        <td>{{ $tamizaje->primerApellido }}</td>
                    </tr>
                    <tr>
                        <th>Segundo Apellido</th>
                        <td>{{ $tamizaje->segundoApellido }}</td>
                    </tr>
                    <tr>
                        <th>Telefono</th>
                        <td>{{ $tamizaje->telefono }}</td>
                    </tr>

                    <tr>
                        <th>Direccion</th>
                        <td>{{ $tamizaje->direccion }}</td>
                    </tr>

                    <tr>
                        <th>Fecha de Tamizaje</th>
                        <td>{{ \Carbon\Carbon::parse($tamizaje->fecha_tamizaje)->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Tipo de Tamizaje</th>
                        <td>{{ $tamizaje->tipo_tamizaje }}</td>
                    </tr>
                    <tr>
                        <th>Código Resultado</th>
                        <td>{{ $tamizaje->codigo_resultado }}</td>
                    </tr>
                    <tr>
                        <th>Descripción Código</th>
                        <td>{{ $tamizaje->descripcion_codigo ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Valor Laboratorio</th>
                        <td>{{ $tamizaje->valor_laboratorio ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Descripción Resultado</th>
                        <td>{{ $tamizaje->descript_resultado ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Usuario Responsable</th>
                        <td>{{ $tamizaje->usuario }}</td>
                    </tr>
                    <tr>
                        <th>Creado en</th>
                        <td>{{ \Carbon\Carbon::parse($tamizaje->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Última Actualización</th>
                        <td>{{ \Carbon\Carbon::parse($tamizaje->updated_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
