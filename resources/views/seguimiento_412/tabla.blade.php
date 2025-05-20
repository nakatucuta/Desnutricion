@extends('adminlte::page')

@section('title','Seguimiento 412')

@section('content')
<div class="content">

  {{-- Contadores con filtros --}}
  <div class="row mb-4">
    <div class="col-md-4">
          <div class="stat-box stat-abiertos" id="filter-abiertos">
            <div class="icon"><i class="fas fa-user-check"></i></div> {{-- Ícono de caso activo --}}
            <div class="content">
                <h5>Abiertos</h5>
                <h2>{{ $conteo }}</h2>
            </div>
    
    
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-box stat-proximos" id="filter-proximos">
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <div class="content">
                <h5>Próximos</h5>
                <h2>{{ $otro->count() }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-box stat-cerrados" id="filter-cerrados">
            <div class="icon"><i class="fas fa-lock"></i></div>
            <div class="content">
                <h5>Cerrados</h5>
                <h2>{{ $cerrados }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- Filtro por año y botón exportar --}}
{{-- <div class="d-flex justify-content-between mb-3 align-items-center flex-wrap gap-2">

  <a href="{{ route('export6') }}" class="btn btn-success btn-sm">
    <i class="fas fa-file-export"></i> Exportar
  </a> --}}

{{-- Filtro por año (alineado a la derecha) --}}
<div class="d-flex justify-content-end mb-3">
  <div class="filtro-anio-wrapper d-flex align-items-center">
    <label for="filtroAnio" class="filtro-label">Año:</label>
    <select id="filtroAnio" class="filtro-select">
      <option value="">Todos</option>
      @for($año = now()->year; $año >= 2022; $año--)
        <option value="{{ $año }}">{{ $año }}</option>
      @endfor
    </select>
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

  
 /* CSS DE LAS BOX QUE  SIRVEN DE FILTROS */
  .stat-box {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background-color: #fff;
  border-left: 6px solid transparent;
  text-decoration: none;
  position: relative;
  overflow: hidden;
  transition: all 0.2s ease-in-out;
  
}
.stat-box:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  text-decoration: none;
  transform: scale(1.02);
}

.stat-box .icon {
  font-size: 2rem;
  margin-right: 15px;
  color: #fff;
  background-color: #007bff;
  padding: 12px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
}

.stat-box .content h5 {
  margin: 0;
  font-weight: 600;
  color: #333;
}
.stat-box .content h2 {
  margin: 0;
  font-size: 1.8rem;
  color: #007bff;
}
.stat-box.selected {
  background-color: rgba(0, 123, 255, 0.05);
  border-left: 6px solid #007bff !important;
  box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
}

.stat-box .icon i {
  font-size: 24px;
}
.stat-abiertos .icon { background-color: #17a2b8; }
.stat-abiertos { border-left-color: #17a2b8; }

.stat-proximos .icon { background-color: #28a745; }
.stat-proximos { border-left-color: #28a745; }

.stat-cerrados .icon { background-color: #dc3545; }
.stat-cerrados { border-left-color: #dc3545; }

.selected-callout {
  outline: 3px solid rgba(0, 123, 255, 0.5);
  background: rgba(0, 123, 255, 0.05);
}



/* boton  de nuevo seguimiento */

.btn-nuevo-seguimiento {
  border-radius: 50px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.8px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: white !important;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
  transition: all 0.3s ease-in-out;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-transform: uppercase;
}

.btn-nuevo-seguimiento i {
  margin-right: 8px;
  font-size: 1rem;
}

.btn-nuevo-seguimiento:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 16px rgba(0, 123, 255, 0.6);
  text-decoration: none;
}


/* css del boton  de los reporte */
.btn-exportar {
  border-radius: 50px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.8px;
  background: linear-gradient(135deg, #28a745, #218838);
  color: white !important;
  border: none;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  transition: all 0.3s ease-in-out;
  text-transform: uppercase;
}

.btn-exportar:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 18px rgba(40, 167, 69, 0.6);
}

.export-dropdown .dropdown-menu {
  border-radius: 10px;
  border: none;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.2s ease-in-out;
}

.export-dropdown .dropdown-item {
  padding: 10px 18px;
  font-size: 0.95rem;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.export-dropdown .dropdown-item:hover {
  background-color: #f1f1f1;
  color: #000;
}



.filtro-anio-wrapper {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  padding: 6px 12px;
  border-radius: 30px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease-in-out;
}

.filtro-anio-wrapper:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.filtro-label {
  font-weight: 600;
  margin-right: 10px;
  font-size: 0.9rem;
  color: #343a40;
}

.filtro-select {
  border: none;
  background: #fff;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #495057;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: border-color 0.2s ease-in-out;
}

.filtro-select:focus {
  outline: none;
  border: 1px solid #007bff;
  box-shadow: 0 0 6px rgba(0, 123, 255, 0.25);
}





/* ESTILO PARA EL BOTOONDE ACCIONES  */
/* Botón Acciones moderno */
.btn-acciones {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border: none;
  color: #fff;
  padding: 8px 16px;
  border-radius: 30px;
  font-weight: 600;
  font-size: 0.9rem;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
  transition: all 0.3s ease-in-out;
  display: inline-flex;
  align-items: center;
}

.btn-acciones:hover {
  transform: scale(1.03);
  box-shadow: 0 6px 16px rgba(0, 123, 255, 0.6);
  color: #fff;
}

.dropdown-menu.animated--fade-in {
  animation: fadeIn 0.2s ease-in-out;
}
/* @keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
} */

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
          d.anio = $('#filtroAnio').val(); // ✅ Filtro año incluido
        },
        beforeSend: function () {
          $('#overlay-spinner').fadeIn(200);
        },
        complete: function () {
          $('#overlay-spinner').fadeOut(200);
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

    // ✅ Filtro por año correctamente ubicado
    $('#filtroAnio').on('change', function () {
      tabla.ajax.reload();
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
