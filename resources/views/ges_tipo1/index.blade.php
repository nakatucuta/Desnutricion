{{-- resources/views/ges_tipo1/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Listado de Gestantes')

@section('content_header')
<div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 rounded shadow-sm">
    <div class="d-flex align-items-center">
        <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo PAI" class="header-logo mr-3">
        <h1 class="m-0 text-info">Listado de Gestantes</h1>
    </div>
    <div>
        <button class="btn btn-gradient btn-lg mr-2" data-toggle="modal" data-target="#exportTipo1Modal">
            <i class="fas fa-file-excel mr-1"></i> Exportar Tipo 2
        </button>
        <button class="btn btn-gradient btn-lg" data-toggle="modal" data-target="#exportTipo3Modal">
            <i class="fas fa-file-excel mr-1"></i> Exportar Tipo 3
        </button>

             <a href="{{ route('ges_tipo3.import') }}" class="btn btn-gradient btn-lg mr-2">
            <i class="fas fa-file-excel mr-1"></i> Cargar (tipo 3)
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="gestantes-table" class="table table-hover table-striped table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Tipo ID</th>
                        <th>No ID Usuaria</th>
                        <th>Número Carnet</th>
                        <th>Fecha Nac.</th>
                        <th>FPP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Export Tipo 1 --}}
<div class="modal fade" id="exportTipo1Modal" tabindex="-1" role="dialog" aria-labelledby="exportTipo1Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form method="GET" action="{{ route('ges_tipo1.export') }}">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="exportTipo1Label">
            <i class="fas fa-calendar-alt mr-1"></i> Rango Fechas – Tipo 1
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
              <label>Fecha inicio</label>
              <input type="date" name="from" class="form-control" required>
          </div>
          <div class="form-group">
              <label>Fecha fin</label>
              <input type="date" name="to" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-info">Descargar Excel</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Modal Export Tipo 3 --}}
<div class="modal fade" id="exportTipo3Modal" tabindex="-1" role="dialog" aria-labelledby="exportTipo3Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form method="GET" action="{{ route('ges_tipo3.export') }}">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="exportTipo3Label">
            <i class="fas fa-calendar-alt mr-1"></i> Rango Fechas – Tipo 3
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
              <label>Fecha inicio</label>
              <input type="date" name="from" class="form-control" required>
          </div>
          <div class="form-group">
              <label>Fecha fin</label>
              <input type="date" name="to" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-info">Descargar Excel</button>
        </div>
      </div>
    </form>
  </div>
</div>
@stop

@section('css')
    <!-- DataTables Bootstrap4 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .header-logo {
            width: 60px; height: auto;
        }
        .btn-gradient {
            background: linear-gradient(45deg, #17a2b8, #117a8b);
            border: none; color: #fff;
            transition: background .3s ease;
        }
        .btn-gradient:hover {
            background: linear-gradient(45deg, #117a8b, #0e6272);
        }
        .btn-gradient:focus {
            box-shadow: 0 0 0 0.2rem rgba(23,162,184,0.5);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(23,162,184,0.1);
        }
    </style>
@stop

@section('js')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(function() {
        $('#gestantes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ges_tipo1.index') }}",
            columns: [
                { data: 'full_name',                         name: 'full_name' },
                { data: 'tipo_de_identificacion_de_la_usuaria', name: 'tipo_de_identificacion_de_la_usuaria' },
                { data: 'no_id_del_usuario',                 name: 'no_id_del_usuario' },
                { data: 'numero_carnet',                     name: 'numero_carnet' },
                { data: 'fecha_de_nacimiento',               name: 'fecha_de_nacimiento' },
                { data: 'fecha_probable_de_parto',           name: 'fecha_probable_de_parto' },
                { data: 'acciones',                          name: 'acciones', orderable: false, searchable: false },
            ],
            order: [[0, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    });
    </script>
@stop
