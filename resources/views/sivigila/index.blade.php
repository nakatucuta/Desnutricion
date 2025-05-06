@extends('adminlte::page')

@section('title','Anas wayuu')

@section('css')
  <link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">

  <style>
    /* Reposiciona y estiliza el spinner */
    div.dataTables_processing {
      position: absolute !important;
      top: 50% !important;
      left: 50% !important;
      width: auto !important;
      height: auto !important;
      margin-left: -30px !important;
      margin-top: -30px !important;
      background: transparent !important;
      border: none !important;
      pointer-events: none !important; /* deja pasar los clics */
      z-index: 10 !important;           /* un poco bajo para no tapar botones */
    }
  </style>
@stop

@section('content_header')
    {{-- botones superiores --}}
    <div class="mb-3 text-right">
        <a href="{{ route('export2') }}" class="btn btn-success btn-sm"
           style="margin-right:.5rem;border-radius:50px;padding:10px 20px;">
            <i class="fas fa-file-export mr-2"></i> EXPORTAR
        </a>
        <a href="{{ route('export4') }}" class="btn btn-success btn-sm"
           style="margin-right:.5rem;border-radius:50px;padding:10px 20px;">
            <i class="fas fa-file-export mr-2"></i> S.publica
        </a>
        <a href="{{ route('export5') }}" class="btn btn-success btn-sm"
           style="margin-right:.5rem;border-radius:50px;padding:10px 20px;">
            <i class="fas fa-file-export mr-2"></i> sin seguimiento
        </a>
        <a href="{{ route('create11') }}" class="btn btn-success btn-sm"
           style="border-radius:50px;padding:10px 20px;">
            <i class="fas fa-plus mr-2"></i> AGREGAR
        </a>
    </div>
@stop

@section('content')
  <div class="row mb-2">
    <div class="col-sm-2">
      <select id="yearFilter" class="form-control">
        <option value="">Todos los años</option>
        @foreach($years as $y)
          <option value="{{ $y }}">{{ $y }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <table id="sivigila-table" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Fecha Notificación</th>
        <th>Semana</th>
        <th>Tipo ID</th>
        <th>Identificación</th>
        <th>Nombre</th>
        <th>Upgd Notificadora</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
@stop

@section('js')
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
  <script>
    $(function(){
      var table = $('#sivigila-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route("sivigila.data") }}',
          data: function (d) {
            // obtenemos el valor real del select
            var y = $('#yearFilter').val();
            if (y) d.year = y;
          }
        },
        language: {
          processing: '<i class="fas fa-spinner fa-spin fa-3x"></i>',
          search:      "BUSCAR",
          lengthMenu:  "Mostrar _MENU_ registros",
          info:        "Mostrando página _PAGE_ de _PAGES_",
          paginate: {
            first:    "Primero",
            last:     "Último",
            next:     "Siguiente",
            previous: "Anterior"
          }
        },
        columns: [
          { data: 'fec_noti' },
          { data: 'semana'   },
          { data: 'tip_ide_' },
          { data: 'num_ide_' },
          {
            data: null,
            orderable: false,
            searchable: false,
            render: row => [ row.pri_nom_, row.seg_nom_, row.pri_ape_, row.seg_ape_ ]
                             .filter(Boolean).join(' ')
          },
          { data: 'nom_upgd' },
          {
            data: 'acciones',
            orderable: false,
            searchable: false
          }
        ]
      });
    
      $('#yearFilter').on('change', function(){
        table.ajax.reload();
      });
    });
    </script>
    
@stop
