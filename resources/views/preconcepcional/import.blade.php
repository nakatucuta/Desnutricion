@extends('adminlte::page')

@section('title', 'Importar Preconcepcional')

@section('content_header')
<h1 class="text-primary mb-0"><i class="fas fa-upload mr-2"></i>Importar Excel Preconcepcional</h1>
@stop

@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Errores:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

@if(session('failures'))
<div class="alert alert-warning">
    <strong>Filas con errores (mostrando máximo 50):</strong>
    <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Fila</th>
                    <th>Campo</th>
                    <th>Error</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach(session('failures') as $failure)
                    @foreach($failure->errors() as $error)
                        <tr>
                            <td>{{ $failure->row() }}</td>
                            <td>{{ implode(', ', $failure->attribute()) }}</td>
                            <td>{{ $error }}</td>
                            <td>{{ json_encode($failure->values(), JSON_UNESCAPED_UNICODE) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
       <form action="{{ route('preconcepcional.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">

    <button class="btn btn-success mt-2">
        <i class="fas fa-upload mr-1"></i> Importar
    </button>
</form>
    </div>
</div>

@stop
