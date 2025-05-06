@extends('adminlte::page')

@section('title', 'Anas Wayuu')

@section('content_header')

    <h1 class="executive-title">Importar Excel y Tabla 412</h1>
@stop

@section('content')
<div class="container-fluid mt-5">
    {{-- Botón de exportar --}}
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('export7') }}" class="btn btn-success btn-sm float-right">
                <i class="fas fa-file-export mr-2"></i> EXPORTAR
            </a>
        </div>
    </div>

    {{-- Formulario de importación --}}
    <div class="row mb-4 justify-content-center">
        <div class="col-md-6">
            <form action="{{ route('import-excel') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="file-upload-form">
                @csrf
                <div id="drag-drop-area" class="drag-drop-area mb-2">
                    <p id="file-instructions">
                        Arrastra y suelta un archivo aquí o haz clic para seleccionar uno
                    </p>
                    <input type="file"
                           name="file"
                           accept=".xls,.xlsx"
                           class="file-input">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Importar Excel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Filtro por año --}}
    <div class="row mb-2">
        <div class="col-auto">
            <label for="filter-year" class="mr-2">Filtrar año captación:</label>
            <select id="filter-year" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabla Sivigilas con DataTables server-side --}}
    <div class="row">
        <div class="col-12">
            <table id="sivigila" class="table table-striped table-bordered" style="min-width:1200px;">
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
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>
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
/* Centramos el processing overlay */
.dataTables_wrapper .dataTables_processing {
    top: 50%;
    left: 50%;
    width: auto;
    height: auto;
    padding: .5em 1em;
    margin-left: -1.5em;
    margin-top: -1.5em;
    border-radius: .3em;
    background: rgba(255,255,255,0.8);
}



.title-wrapper {
      text-align: center; /* centra todo su contenido */
      margin: 20px 0;
    }
    .executive-title {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 36px;
      font-weight: 700;
      color: #2C3E50;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
      padding: 10px 20px;
      background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      border-left: 6px solid #2980b9;
      display: inline-block; /* ocupa sólo el ancho del contenido */
    }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>

<script>
$(function() {
  // Inicializar DataTable con spinner en lugar de texto
  var table = $('#sivigila').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    pageLength: 10,
    language: {
      processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
      search:     "BUSCAR",
      lengthMenu: "Mostrar _MENU_ registros",
      info:       "Mostrando página _PAGE_ de _PAGES_",
      paginate: {
        first:    "Primero",
        last:     "Último",
        next:     "Siguiente",
        previous: "Anterior"
      }
    },
    ajax: {
      url: '{!! route('import-excel-data') !!}',
      data: function(d) {
        d.year = $('#filter-year').val();
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

  // Cuando cambie el año, recargamos
  $('#filter-year').on('change', function(){
    table.ajax.reload();
  });

  // Drag & drop y validación de archivo
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

  // SweetAlert de mensajes de sesión
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
    Swal.fire({ icon:'error', title:'Errores', html:`<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>`, confirmButtonText:'Cerrar' });
  @endif
});
</script>
@stop
