@extends('adminlte::page')

@section('title', 'Listado de Gestantes')

@section('content_header')
    <h1>Gestantes</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12 text-right">
        {{-- Nuevo botón “Ver datos” --}}
    
        {{-- Botón de Regresar existente --}}
        <a href="{{ route('ges_tipo1.import.form') }}" class="btn btn-warning btn-lg">
            <i class="fas fa-arrow-left"></i> Regresar
        </a>
        
    </div>
</div>
<div class="card">
    <div class="card-body">
        <table id="gestantes-table" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>No ID Usuario</th>
                    <th>Número Carnet</th>
                    <th>Fecha Nacimiento</th>
                    <th>Fecha Probable de Parto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('css')
    <!-- DataTables Bootstrap4 CSS via CDN -->
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css"
    >
@stop

@section('js')
    <!-- jQuery (AdminLTE ya lo carga), luego DataTables JS via CDN -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#gestantes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ges_tipo1.index') }}",
            columns: [
                { data: 'full_name',               name: 'full_name' },
                { data: 'no_id_del_usuario',       name: 'no_id_del_usuario' },
                { data: 'numero_carnet',           name: 'numero_carnet' },
                { data: 'fecha_de_nacimiento',     name: 'fecha_de_nacimiento' },
                { data: 'fecha_probable_de_parto', name: 'fecha_probable_de_parto' },
                { data: 'acciones',                name: 'acciones', orderable: false, searchable: false },
            ],
            order: [[ 0, 'asc' ]],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    });
    </script>
@stop
