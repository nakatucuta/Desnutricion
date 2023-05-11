<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>

  
  
  {{-- <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script> --}}
  {{-- <script type="text/javascript"> 
    $(document).ready(function(){
        $("#q").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#table tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
  </script> --}}
  {{-- <script>
    setInterval(function() {
      var icono = document.getElementById('icono-notificaciones');
      var badge = document.getElementById('badge-notificaciones');
      var colores = ['#dc3545', '#007bff', '#28a745', '#ffc107', '#17a2b8', '#6c757d'];
      var colorAleatorio = colores[Math.floor(Math.random() * colores.length)];
      icono.style.backgroundColor = colorAleatorio;
      badge.style.backgroundColor = colorAleatorio;
    }, 500);
  </script> --}}
  <script>
    $(document).ready(function () {
    $('#seguimiento').DataTable({
        "language":{
            "search": "BUSCAR",
            "lengthMenu": "Mostrar _MENU_ registros",
            "info": "Mostrando pagina _PAGE_ de _PAGES_",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "autoWidth": true
    });
});
</script>