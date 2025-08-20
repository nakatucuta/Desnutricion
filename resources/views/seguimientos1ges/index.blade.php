@extends('adminlte::page')

@section('title', 'Seguimientos')
@section('content_header')
<div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 rounded shadow-sm">
    <div class="d-flex align-items-center">
        <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Escudo PAI" class="header-logo mr-3">
        <h1 class="m-0 text-info">Seguimientos evento 549</h1>
    </div>
    <div>
     <form method="GET" action="{{ route('seguimientos.export') }}" class="form-inline mb-3">
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
<div class="card card-outline card-info">
  <div class="card-header">
    <ul class="nav nav-pills" id="seguimientosTabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="tab-asignados" data-toggle="tab" href="#pane-asignados" role="tab">
          Asignaciones pendientes
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="tab-realizados" data-toggle="tab" href="#pane-realizados" role="tab">
          Seguimientos realizados
        </a>
      </li>
      {{-- NUEVO: pestaña de alertas --}}
      <li class="nav-item">
        <a class="nav-link" id="tab-alertas" data-toggle="tab" href="#pane-alertas" role="tab">
          Alertas (vencidos) <span class="badge badge-danger ml-1" id="badgeAlertas">0</span>
        </a>
      </li>
    </ul>
  </div>

  <div class="card-body">
    <div class="tab-content" id="seguimientosTabContent">

      {{-- TAB 1: Asignaciones pendientes --}}
      <div class="tab-pane fade show active" id="pane-asignados" role="tabpanel">
        <div class="table-responsive">
          <table id="tabla-asignados" class="table table-striped table-bordered w-100">
            <thead class="thead-light">
              <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Tipo ID</th>    {{-- separado --}}
                <th>Número ID</th>  {{-- separado --}}
                <th>Evento</th>
                <th>Fec. Notif.</th>
                <th>Prestador (usuario)</th>
                <th style="width:80px;">Acciones</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>

      {{-- TAB 2: Seguimientos realizados --}}
      <div class="tab-pane fade" id="pane-realizados" role="tabpanel">
        <div class="table-responsive">
          <table id="tabla-realizados" class="table table-striped table-bordered w-100">
            <thead class="thead-light">
              <tr>
                <th>ID Seg.</th>
                <th>Caso (Asignación)</th>
                <th>Paciente</th>
                <th>Tipo ID</th>     {{-- separado --}}
                <th>Número ID</th>   {{-- separado --}}
                <th>Prestador</th>
                <th>Último hito</th>
                <th>Creado</th>
                <th style="width:120px;">Acciones</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>

      {{-- TAB 3: Alertas (vencidos) --}}
      <div class="tab-pane fade" id="pane-alertas" role="tabpanel">
        <div class="alert alert-warning">
          <i class="fas fa-bell"></i>
          Se listan los seguimientos con hitos vencidos según: 48–72h, 7, 14, 21, 28 días, 6 meses y 1 año
          (basados en la fecha de egreso o la fecha de creación del seguimiento).
        </div>
        <div class="table-responsive">
          <table id="tabla-alertas" class="table table-striped table-bordered w-100">
            <thead class="thead-light">
              <tr>
                <th>ID Seg.</th>
                <th>Caso</th>
                <th>Paciente</th>
                <th>Tipo ID</th>
                <th>Número ID</th>
                <th>Prestador</th>
                <th>Hito vencido</th>
                <th>Fecha límite</th>
                <th>Días atraso</th>
                <th style="width:90px;">Acciones</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@stop

@section('css')
<style>
  .dataTables_wrapper .dataTables_paginate .pagination { margin: 0; }


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
{{-- DataTables (si no los cargas global) --}}
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function () {
  // === TABLA 1: Asignaciones pendientes ===
  $('#tabla-asignados').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: '{{ route("seguimientos.asignados.data") }}',
    columns: [
      { data: 'id', name: 'id' },
      { data: 'paciente', name: 'paciente' },
      { data: 'tip_ide_', name: 'tip_ide_' },  // separado
      { data: 'num_ide_', name: 'num_ide_' },  // separado
      { data: 'nom_eve', name: 'nom_eve' },
      { data: 'fec_not', name: 'fec_not' },
      { data: 'prestador', name: 'prestador' },
      { data: 'acciones', name: 'acciones', orderable:false, searchable:false }
    ],
    order: [[0, 'desc']]
  });

  // === TABLA 2: Seguimientos realizados ===
  $('#tabla-realizados').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: '{{ route("seguimientos.realizados.data") }}',
    columns: [
      { data: 'id', name: 'id' },
      { data: 'asignacion_id', name: 'asignacion_id', title: 'Caso' },
      { data: 'paciente', name: 'paciente' },
      { data: 'tip_ide_', name: 'tip_ide_' },  // separado
      { data: 'num_ide_', name: 'num_ide_' },  // separado
      { data: 'prestador', name: 'prestador' },
      { data: 'ultimo_hito', name: 'ultimo_hito' },
      { data: 'created_at', name: 'created_at' },
      { data: 'acciones', name: 'acciones', orderable:false, searchable:false },
    ],
    order: [[0, 'desc']]
  });

  // === TABLA 3: Alertas (vencidos) ===
  var dtAlertas = $('#tabla-alertas').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: '{{ route("seguimientos.alertas.data") }}',
    columns: [
      { data: 'id', name: 'id', title: 'ID Seg.' },
      { data: 'asignacion_id', name: 'asignacion_id', title: 'Caso' },
      { data: 'paciente', name: 'paciente', title: 'Paciente' },
      { data: 'tip_ide_', name: 'tip_ide_', title: 'Tipo ID' },
      { data: 'num_ide_', name: 'num_ide_', title: 'Número ID' },
      { data: 'prestador', name: 'prestador', title: 'Prestador' },
      { data: 'hito', name: 'hito', title: 'Hito vencido' },
      { data: 'fecha_limite', name: 'fecha_limite', title: 'Fecha límite' },
      { data: 'dias_atraso', name: 'dias_atraso', title: 'Días atraso' },
      { data: 'acciones', name: 'acciones', orderable:false, searchable:false, title: 'Acciones' },
    ],
    order: [[8, 'desc']], // ordena por días de atraso desc
    drawCallback: function () {
      // Actualizar badge con cantidad visible
      var info = dtAlertas.page.info();
      $('#badgeAlertas').text(info.recordsDisplay);
    }
  });
});
</script>
@stop
