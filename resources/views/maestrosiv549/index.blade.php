@extends('adminlte::page')

@section('title', 'Listado Maestrosiv549')

@section('content_header')
<div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 rounded shadow-sm">
    <div class="d-flex align-items-center">
        <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo PAI" class="header-logo mr-3">
        <h1 class="m-0 text-info">Listado de Maestrosiv549</h1>
    </div>
    <div>
      <form method="GET" action="{{ route('reportes.maestrosiv549.export') }}" class="form-inline mb-3">
  <input type="text" name="tip_ide_" class="form-control mr-2" placeholder="Tipo ID" value="{{ request('tip_ide_') }}">
  <input type="text" name="num_ide_" class="form-control mr-2" placeholder="Número ID" value="{{ request('num_ide_') }}">
  <input type="date" name="fec_desde" class="form-control mr-2" value="{{ request('fec_desde') }}">
  <input type="date" name="fec_hasta" class="form-control mr-2" value="{{ request('fec_hasta') }}">
  <button class="btn btn-gradient btn-lg mr-2">
    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
  </button>
</form>

    </div>
</div>
@stop

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="maestrosiv549-table" class="table table-hover table-striped table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Tipo ID</th>
                        <th>Número ID</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Fecha Notif.</th>
                        <th>Semana</th>
                        <th>Año</th>
                        <th>Evento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
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

    
/* Fondo para fila asignada */
tr.row-asignado, tr.row-asignado td {
    background: #fffbe0 !important;
}

/* Checklist badge, discreto */
.badge-checklist {
    background: #5fd77a;
    color: #fff;
    font-size: 1.03rem;
    padding: 0.38em 0.8em;
    border-radius: 1em;
    font-weight: 500;
    display: inline-block;
    margin-right: 7px;
    vertical-align: middle;
    box-shadow: 0 2px 7px #81ffa315;
}
.badge-checklist i {
    margin-right: 0.36em;
    font-size: 1.08em;
    vertical-align: middle;
}

/* Íconos de acción alineados */
.acciones-flex {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.45em;
    min-width: 70px;
}

/* Checklist icono verde */
.badge-checklist {
    background: #5fd77a;
    color: #fff;
    border-radius: 50%;
    width: 2.15em;
    height: 2.15em;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 8px #63ff962c;
    font-size: 1.12rem;
    border: 2px solid #e9ffe4;
}
.badge-checklist i {
    font-size: 1.2em;
    color: #fff;
    margin: 0;
}

/* Botón asignar solo icono, animado */
.btn-asignar {
    background: linear-gradient(90deg,#17a2b8 35%,#1d9bf0 100%);
    color: #fff !important;
    border: none;
    border-radius: 50%;
    width: 2.15em;
    height: 2.15em;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.17rem;
    margin-left: 0.15em;
    box-shadow: 0 3px 12px #1d9bf04c;
    transition: background .22s, box-shadow .21s, transform .14s;
}
.btn-asignar:hover, .btn-asignar:focus {
    background: linear-gradient(90deg,#1d9bf0 35%,#17a2b8 100%);
    color: #fff;
    transform: scale(1.09) rotate(-9deg);
    box-shadow: 0 8px 24px #1d9bf032;
    text-decoration: none;
}
.btn-asignar i {
    font-size: 1.22em;
}

tr.row-asignado, tr.row-asignado td {
    background: #fffbe0 !important;
}


    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(function() {
        $('#maestrosiv549-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('maestrosiv549.data') }}",
            columns: [
                { data: 'tip_ide_', name: 'tip_ide_' },
                { data: 'num_ide_', name: 'num_ide_' },
                { data: 'nombre_completo', name: 'nombre_completo', orderable: false, searchable: true },
                { data: 'edad_', name: 'edad_' },
                { data: 'sexo_', name: 'sexo_' },
                { data: 'fec_not', name: 'fec_not' },
                { data: 'semana', name: 'semana' },
                { data: 'year', name: 'year' },
                { data: 'nom_eve', name: 'nom_eve' },
                {
                    data: 'acciones',
                    name: 'acciones',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            pageLength: 25,
            order: [[5, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            }
        });
    });
    </script>
@stop
