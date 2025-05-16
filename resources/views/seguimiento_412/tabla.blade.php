@extends('adminlte::page')

@section('title','Seguimiento 412')

@section('content')
<div class="content">

  {{-- Contadores con filtros --}}
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

  {{-- Tabla con DataTables --}}
  <div class="box box-primary">
    <div class="box-body">
      <table class="table table-hover table-striped table-bordered w-100" id="seguimiento412">
        <thead class="bg-info">
          <tr>
            <th>Fecha de Cargue</th>
            <th>ID</th>
            <th>Identificación</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>IPS</th>
            <th>Próximo Control</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

{{-- Spinner de carga pantalla completa --}}
<div id="overlay-spinner">
  <div class="spinner-container">
    <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Cargando...</span>
    </div>
    <strong class="text-dark mt-3 d-block">Cargando datos, por favor espere...</strong>
  </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">
<style>
  .dataTables_filter input {
    border-radius: 20px;
    border: 1px solid #ced4da;
    padding: 6px 12px;
  }

  .selected-callout {
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    border: 2px solid #007bff;
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
</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap4.min.js') }}"></script>

<script>
$(function () {
  let estadoFilter = '';
  let proximoFilter = '';

  const tabla = $('#seguimiento412').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route("new412_seguimiento.data") }}',
      data: function (d) {
        d.estado = estadoFilter;
        d.proximo = proximoFilter;
      },
      beforeSend: function () {
        $('#overlay-spinner').fadeIn(200); // Mostrar el spinner
      },
      complete: function () {
        $('#overlay-spinner').fadeOut(200); // Ocultar el spinner
      }
    },
    columns: [
      { data: 'seguimiento_created_at', name: 'seguimiento_created_at' },
      { data: 'seguimiento_id', name: 'seguimiento_id' },
      { data: 'numero_identificacion', name: 'numero_identificacion' },
      { data: 'nombre_completo', name: 'nombre_completo' },
      { data: 'estado', name: 'estado' },
      { data: 'nombre_coperante', name: 'nombre_coperante' },
      { data: 'fecha_proximo_control', name: 'fecha_proximo_control' },
      { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
    ],
    dom: 'lfrtip',
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
    language: {
      processing: "Procesando...",
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_ registros",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      infoEmpty: "Mostrando 0 a 0 de 0 registros",
      infoFiltered: "(filtrado de _MAX_ registros en total)",
      loadingRecords: "Cargando registros...",
      zeroRecords: "No se encontraron resultados",
      emptyTable: "No hay datos disponibles en esta tabla",
      paginate: {
        first: "Primero",
        previous: "Anterior",
        next: "Siguiente",
        last: "Último"
      },
      aria: {
        sortAscending: ": activar para ordenar ascendente",
        sortDescending: ": activar para ordenar descendente"
      }
    }
  });

  // Filtros interactivos
  function activarFiltro(id) {
    $('#filter-abiertos, #filter-cerrados, #filter-proximos').removeClass('selected-callout');
    $(id).addClass('selected-callout');
  }

  $('#filter-abiertos').click(function () {
    estadoFilter = 1;
    proximoFilter = '';
    activarFiltro('#filter-abiertos');
    tabla.ajax.reload();
  });

  $('#filter-cerrados').click(function () {
    estadoFilter = 0;
    proximoFilter = '';
    activarFiltro('#filter-cerrados');
    tabla.ajax.reload();
  });

  $('#filter-proximos').click(function () {
    estadoFilter = '';
    proximoFilter = 1;
    activarFiltro('#filter-proximos');
    tabla.ajax.reload();
  });
});
</script>
@stop
