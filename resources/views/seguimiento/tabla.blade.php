@extends('adminlte::page')

@section('title','Seguimientos')

@section('content')
<div class="content">

  {{-- Contadores --}}
  <div class="row mb-4">
    <div class="col-md-4">
      <x-adminlte-callout id="filter-abiertos" theme="info" title="Abiertos" style="cursor:pointer">
        {{ $conteo }}
      </x-adminlte-callout>
    </div>
    <div class="col-md-4">
      <x-adminlte-callout id="filter-proximos" theme="success" title="Próximos" style="cursor:pointer">
        {{ $otro->count() }}
      </x-adminlte-callout>
    </div>
    <div class="col-md-4">
      <x-adminlte-callout id="filter-cerrados" theme="danger" title="Cerrados" style="cursor:pointer">
        {{ $cerrados }}
      </x-adminlte-callout>
    </div>
  </div>

  {{-- Encabezado de controles --}}
  <div class="row mb-3 align-items-center">
    <div class="col-md-6 d-flex align-items-center gap-2">
      {{-- Botón exportar --}}
      <a href="{{ route('export3') }}" class="btn btn-success btn-sm mr-3">
        <i class="fas fa-file-export"></i> Exportar
      </a>

      {{-- Filtro por año --}}
      <label for="filtroAnio" class="mb-0 mr-2 text-dark font-weight-bold">Año:</label>
      <select id="filtroAnio" class="form-control form-control-sm shadow-sm border rounded-pill" style="width: 120px;">
        <option value="">Todos</option>
        @for($año = now()->year; $año >= 2022; $año--)
          <option value="{{ $año }}">{{ $año }}</option>
        @endfor
      </select>
    </div>
  </div>

  {{-- Tabla --}}
  <table id="seguimiento" class="table table-striped table-bordered w-100">
    <thead class="bg-info">
      <tr>
        <th>ID</th>
        <th>Fecha asignación</th>
        <th>Identificación</th>
        <th>Semana Epid</th>
        <th>Nombre</th>
        <th>Estado</th>
        <th>IPS</th>
        <th>Próximo Control</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

{{-- Spinner de carga --}}
<div id="overlay-spinner">
  <div class="spinner-container">
    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
      <span class="sr-only">Cargando...</span>
    </div>
    <strong class="text-dark mt-3 d-block">Cargando datos, por favor espere...</strong>
  </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">
<style>
  .dataTables_filter {
    float: right !important;
    margin-bottom: 1rem;
  }

  .dataTables_filter input {
    border-radius: 20px;
    border: 1px solid #ced4da;
    padding: 6px 12px;
  }

  .dataTables_length {
    float: left !important;
    margin-top: 10px;
  }

  #overlay-spinner {
    display: none;
    position: fixed;
    z-index: 9999;
    background: rgba(255, 255, 255, 0.85);
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }

  .spinner-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
  }

  .spinner-border {
    width: 4rem;
    height: 4rem;
  }

  .selected-callout {
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    border: 2px solid #007bff;
  }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap4.min.js') }}"></script>

<script>
  $(function(){
    var estadoFilter  = '';
    var proximoFilter = '';

    const table = $('#seguimiento').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{!! route("Seguimiento.data") !!}',
        data: d => {
          d.estado  = estadoFilter;
          d.proximo = proximoFilter;
          d.anio    = $('#filtroAnio').val();
        },
        beforeSend: () => $('#overlay-spinner').fadeIn(200),
        complete:   () => $('#overlay-spinner').fadeOut(200)
      },
      columns: [
        { data: 'id',                    name: 'id' },
        { data: 'creado',                name: 'creado' },
        { data: 'num_ide',               name: 'num_ide' },
        { data: 'semana',                name: 'semana' },
        { data: 'nombre',                name: 'nombre',             orderable:false, searchable:true },
        { data: 'estado',                name: 'estado',             orderable:false, searchable:false },
        { data: 'ips',                   name: 'ips' },
        { data: 'fecha_proximo_control', name: 'fecha_proximo_control' },
        { data: 'acciones',              name: 'acciones',           orderable:false, searchable:false }
      ],
      dom: 'lfrtip',
      lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"] ],
      language: {
        processing:     "Procesando...",
        search:         "Buscar:",
        lengthMenu:     "Mostrar _MENU_ registros",
        info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty:      "Mostrando 0 a 0 de 0 registros",
        infoFiltered:   "(filtrado de _MAX_ registros en total)",
        loadingRecords: "Cargando registros...",
        zeroRecords:    "No se encontraron resultados",
        emptyTable:     "No hay datos disponibles en esta tabla",
        paginate: {
          first:      "Primero",
          previous:   "Anterior",
          next:       "Siguiente",
          last:       "Último"
        },
        aria: {
          sortAscending:  ": activar para ordenar ascendente",
          sortDescending: ": activar para ordenar descendente"
        }
      }
    });

    $('#filtroAnio').on('change', () => table.ajax.reload());

    function activarFiltro(id) {
      $('#filter-abiertos, #filter-cerrados, #filter-proximos').removeClass('selected-callout');
      $(id).addClass('selected-callout');
    }

    $('#filter-abiertos').click(() => {
      estadoFilter = '1'; proximoFilter = '';
      activarFiltro('#filter-abiertos');
      table.ajax.reload();
    });

    $('#filter-cerrados').click(() => {
      estadoFilter = '0'; proximoFilter = '';
      activarFiltro('#filter-cerrados');
      table.ajax.reload();
    });

    $('#filter-proximos').click(() => {
      estadoFilter = ''; proximoFilter = '1';
      activarFiltro('#filter-proximos');
      table.ajax.reload();
    });
  });
</script>
@stop
