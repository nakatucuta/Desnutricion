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

  {{-- Exportar + buscador --}}
  <div class="d-flex justify-content-between mb-3">
    <a href="{{route('export3')}}"  class="btn btn-success btn-sm">
      <i class="fas fa-file-export"></i> Exportar
    </a>
    {{-- <input type="text" id="search" class="form-control w-25" placeholder="Buscar identificación" autocomplete="off"> --}}
  </div>

  {{-- DataTable --}}
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
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">

<style>
  /* Alineación horizontal y vertical perfecta entre selector y buscador */
  div.dataTables_wrapper .dataTables_length,
  div.dataTables_wrapper .dataTables_filter {
    display: inline-flex;
    align-items: center;
    margin-bottom: 1rem;
    margin-top: 0 !important;
    vertical-align: middle;
  }

  div.dataTables_wrapper .dataTables_length {
    float: left;
  }

  div.dataTables_wrapper .dataTables_filter {
    float: right;
  }

  /* Estilo mejorado del input de búsqueda */
  div.dataTables_wrapper .dataTables_filter input {
    margin-left: 0.5rem;
    height: 38px;
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 30px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
  }

  div.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #5dade2;
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 5px rgba(93, 173, 226, 0.5);
  }

  /* Estilo del select para longitud */
  div.dataTables_wrapper .dataTables_length select {
    height: 38px;
    padding: 0.375rem 0.75rem;
    margin: 0 0.5rem;
    border-radius: 6px;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
  }

  div.dataTables_wrapper .dataTables_length select:focus {
    border-color: #5dade2;
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 5px rgba(93, 173, 226, 0.5);
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
  
    var table = $('#seguimiento').DataTable({
      processing: true,
      serverSide: true,
  
      ajax: {
        url: '{!! route("Seguimiento.data") !!}',
        data: d => {
          d.estado  = estadoFilter;
          d.proximo = proximoFilter;
        }
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
        infoPostFix:    "",
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
  
    $('#search').on('keyup', function(){
      table.column(2).search(this.value).draw();
    });
  
    $('#filter-abiertos').click(()=>{ estadoFilter = '1'; proximoFilter = ''; table.ajax.reload(); });
    $('#filter-cerrados').click(()=>{ estadoFilter = '0'; proximoFilter = ''; table.ajax.reload(); });
    $('#filter-proximos').click(()=>{ estadoFilter = ''; proximoFilter = '1'; table.ajax.reload(); });
  });
  </script>
  
@stop
