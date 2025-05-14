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
    <input type="text" id="search" class="form-control w-25" placeholder="Buscar identificación" autocomplete="off">
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
    dom: 'rtip',
    language: {
      processing:   "Cargando...",
      paginate:     { first:"Primero", last:"Último", next:"Siguiente", previous:"Anterior" },
      lengthMenu:   "Mostrar _MENU_ registros",
      info:         "Mostrando _START_ a _END_ de _TOTAL_"
    }
  });

  // búsqueda libre por identificación
  $('#search').on('keyup', function(){
    table.column(2).search(this.value).draw();
  });

  // filtros por callout
  $('#filter-abiertos').click(()=>{
    estadoFilter = '1'; proximoFilter = ''; table.ajax.reload();
  });
  $('#filter-cerrados').click(()=>{
    estadoFilter = '0'; proximoFilter = ''; table.ajax.reload();
  });
  $('#filter-proximos').click(()=>{
    estadoFilter = ''; proximoFilter = '1'; table.ajax.reload();
  });
});
</script>
@stop
