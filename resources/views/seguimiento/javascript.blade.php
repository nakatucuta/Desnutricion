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