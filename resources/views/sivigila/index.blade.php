@extends('adminlte::page')

@section('title', 'Anas Wayuu')

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
      .drag-drop-area {
          position: relative;
          height: 200px;
          border: 2px dashed #ccc;
          border-radius: 5px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
      }
      .drag-drop-area:hover { background-color: #f9f9f9; }
      .drag-drop-area.drag-over { background-color: #e8f4ff !important; }
      .drag-drop-area .file-input {
          position: absolute; width:100%; height:100%; top:0; left:0; opacity:0;
      }
      #file-instructions { z-index:1; color:#666; }
    </style>
@stop

@section('content_header')
    {{-- botones superiores --}}
    <div class="mb-3 text-right">
        <a href="{{ route('export7') }}" class="btn btn-success btn-sm"
           style="margin-right:.5rem;border-radius:50px;padding:10px 20px;">
            <i class="fas fa-file-export mr-2"></i> EXPORTAR
        </a>
    </div>
@stop

@section('content')
  <div class="row mb-2">
    <div class="col-sm-2">
      <select id="filter-year" class="form-control form-control-sm">
        <option value="">Todos los años</option>
        @foreach($years as $y)
          <option value="{{ $y }}">{{ $y }}</option>
        @endforeach
      </select>
    </div>
  </div>

  {{-- Formulario de importación --}}
  <div class="row mb-4 justify-content-center">
    <div class="col-md-6">
      <form action="{{ route('import-excel') }}" method="POST" enctype="multipart/form-data" id="file-upload-form">
        @csrf
        <div id="drag-drop-area" class="drag-drop-area mb-2">
          <p id="file-instructions">Arrastra y suelta un archivo aquí o haz clic para seleccionar uno</p>
          <input type="file" name="file" accept=".xls,.xlsx" class="file-input">
        </div>
        <div class="text-center">
          <button type="submit" class="btn btn-primary">Importar Excel</button>
        </div>
      </form>
    </div>
  </div>

  <table id="sivigila-table" class="table table-striped table-bordered" style="min-width:1200px;">
    <thead>
      <tr>
        <th>Fecha cargue</th>
        <th>Id</th>
        <th>Nombre Coperante</th>
        <th>Fecha Captación</th>
        <th>Municipio</th>
        <th>Nombre Cuidador</th>
        <th>Primer Nombre</th>
        <th>Tipo de Identificación</th>
        <th>Número de Identificación</th>
        <th>Sexo</th>
        <th>Edad en Meses</th>
        <th>IPS Primaria</th>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>

  <script>
    $(function(){
      var table = $('#sivigila-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route("import-excel-data") }}',
          data: function (d) {
            d.year = $('#filter-year').val();
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
        order: [[0,'desc']],
        columns: [
          { data:'fecha_cargue',          name:'fecha_cargue' },
          { data:'id',                    name:'id' },
          { data:'nombre_coperante',      name:'nombre_coperante' },
          { data:'fecha_captacion',       name:'fecha_captacion' },
          { data:'municipio',             name:'municipio' },
          { data:'nombre_cuidador',       name:'nombre_cuidador' },
          { data:'nombres_completos',     name:'nombres_completos' },
          { data:'tipo_identificacion',   name:'tipo_identificacion' },
          { data:'numero_identificacion', name:'numero_identificacion' },
          { data:'sexo',                  name:'sexo' },
          { data:'edad_meses',            name:'edad_meses' },
          { data:'ips_primaria',          name:'ips_primaria' },
          { data:'acciones',              orderable:false, searchable:false }
        ]
      });

      $('#filter-year').on('change', function(){
        table.ajax.reload();
      });

      // Drag & drop
      const area  = $('#drag-drop-area'),
            input = area.find('.file-input'),
            info  = $('#file-instructions');

      area.on('click',    () => input.trigger('click'));
      input.on('change',  e => showFile(e.target.files));
      area.on('dragover dragenter', e => { e.preventDefault(); area.addClass('drag-over'); });
      area.on('dragleave drop',     e => { e.preventDefault(); area.removeClass('drag-over'); });
      area.on('drop',               e => {
        input[0].files = e.originalEvent.dataTransfer.files;
        showFile(input[0].files);
      });

      function showFile(files) {
        if (!files.length) return;
        const f  = files[0],
              ok = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
              ];
        if (ok.includes(f.type)) {
          info.text(`Archivo seleccionado: ${f.name}`).css({ background:'', color:'' });
        } else {
          info.text('Solo archivos Excel (.xls, .xlsx)')
              .css({ background:'red', color:'white', padding:'5px', borderRadius:'3px' });
          input.val('');
        }
      }

      // SweetAlert mensajes
      @if(Session::has('mensaje'))
        Swal.fire({ icon:'info',    title:'Mensaje',       text:"{{ Session::get('mensaje') }}",    confirmButtonText:'Cerrar' });
      @endif
      @if(Session::has('success'))
        Swal.fire({ icon:'success', title:'Éxito',         text:"{{ Session::get('success') }}",    confirmButtonText:'Cerrar' });
      @endif
      @if(Session::has('error1'))
        Swal.fire({ icon:'error',   title:'Error de Cargue', html:`{!! nl2br(e(Session::get('error1'))) !!}`, confirmButtonText:'Cerrar', width:'940px' });
      @endif
      @if($errors->any())
        Swal.fire({ icon:'error', title:'Errores encontrados', html:`<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>`, confirmButtonText:'Cerrar' });
      @endif
    });
  </script>
@stop
