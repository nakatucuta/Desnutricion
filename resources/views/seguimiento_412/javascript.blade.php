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
                d.anio = $('#filtroAnio').val();
            },
            beforeSend: function () {
                $('#overlay-spinner').fadeIn(150);
            },
            complete: function () {
                $('#overlay-spinner').fadeOut(150);
            }
        },
        columns: [
            { data: 'fecha_seguimiento', name: 'fecha_seguimiento' },
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
                last: "Ultimo"
            },
            aria: {
                sortAscending: ": activar para ordenar ascendente",
                sortDescending: ": activar para ordenar descendente"
            }
        }
    });

    $('#filtroAnio').on('change', function () {
        tabla.ajax.reload();
    });

    function activarFiltro(id) {
        $('#filter-abiertos, #filter-cerrados, #filter-proximos').removeClass('selected-callout');
        $(id).addClass('selected-callout');
    }

    $('#filter-abiertos').on('click', function () {
        estadoFilter = 1;
        proximoFilter = '';
        activarFiltro('#filter-abiertos');
        tabla.ajax.reload();
    });

    $('#filter-cerrados').on('click', function () {
        estadoFilter = 0;
        proximoFilter = '';
        activarFiltro('#filter-cerrados');
        tabla.ajax.reload();
    });

    $('#filter-proximos').on('click', function () {
        estadoFilter = '';
        proximoFilter = 1;
        activarFiltro('#filter-proximos');
        tabla.ajax.reload();
    });
});
</script>
