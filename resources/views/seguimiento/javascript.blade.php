<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>




{{-- <script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script> --}}

  
  
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
  {{-- <script>
//     $(document).ready(function () {
//     $('#seguimiento').DataTable({
//         "language":{
//             "search": "BUSCAR",
//             "lengthMenu": "Mostrar _MENU_ registros",
//             "info": "Mostrando pagina _PAGE_ de _PAGES_",
//             "paginate": {
//                 "first": "Primero",
//                 "last": "Último",
//                 "next": "Siguiente",
//                 "previous": "Anterior"
//             }
//         },
//         "autoWidth": true
//     });
// });

</script> --}}
<script>
  // Editar => /Seguimiento/:id/edit
  let editUrlTemplate = "{{ url('/Seguimiento/:id/edit') }}";

  // Detalles => /detalleseguimiento/:id
  let detallesUrlTemplate = "{{ route('detalleseguimiento', ':id') }}";

  // PDF => /seguimiento/view-pdf/:id
  let pdfUrlTemplate = "{{ route('seguimiento.view-pdf', ':id') }}";

  // Ocasional => /seguimiento_ocasional/create?id=:id
  let ocasionalUrlTemplate = "{{ route('seguimiento_ocasional.create', ['id' => ':id']) }}";

  // Eliminar => route('Seguimiento.destroy', ':id')
  let destroyUrlTemplate = "{{ route('Seguimiento.destroy', ':id') }}";

  // CSRF token para DELETE
  let csrfToken = "{{ csrf_token() }}";

  // Variable booleana: ¿usertype == 1?
  let isUsertype1 = @json(auth()->user()->usertype == 1);
</script>

<script>
  // Mostrar el modal de carga cuando se envíe algún formulario (opcional)
  $('#file-upload-form').on('submit', function() {
      $('#loadingModal').modal('show');
  });
  
  $(document).ready(function() {
    
      // ----- BÚSQUEDA EN TIEMPO REAL -----
      $('#search').on('keyup', function() {
          var query = $(this).val().trim();

          if (query.length > 0) {
              // Mostrar el spinner
              $('#loading-spinner').show();

              // Realizar la búsqueda con AJAX
              $.ajax({
                  url: "{{ route('buscar.seguimiento') }}",  // Ajusta a tu ruta
                  method: "GET",
                  dataType: "json",
                  data: { search: query },
                  success: function(data) {
                      $('#search-results').empty();
                      $('#loading-spinner').hide();

                      if (data.length > 0) {
                          $.each(data, function(index, afiliado) {
                              // Agregar enlaces de resultados
                              $('#search-results').append(
                                  '<a href="#" class="list-group-item list-group-item-action search-result-item" ' +
                                    'data-id="' + afiliado.numero_identificacion + '">' +
                                      afiliado.numero_identificacion + ' - ' +
                                      afiliado.primer_nombre + ' ' +
                                      afiliado.segundo_nombre + ' ' +
                                      afiliado.primer_apellido + ' ' +
                                      afiliado.segundo_apellido +
                                  '</a>'
                              );
                          });
                      } else {
                          $('#search-results').append(
                              '<a href="#" class="list-group-item list-group-item-action">No se encontraron resultados</a>'
                          );
                      }
                  },
                  error: function(xhr, status, error) {
                      $('#loading-spinner').hide();
                      console.error("Error en la solicitud AJAX (buscar.seguimiento):", error);
                  }
              });
          } else {
              // Si el campo está vacío, limpiar resultados y ocultar spinner
              $('#search-results').empty();
              $('#loading-spinner').hide();
          }
      });


      // ----- SELECCIÓN DE UN RESULTADO DE LA LISTA -----
      $(document).on('click', '.search-result-item', function(e) {
          e.preventDefault();
          var numeroIdentificacion = $(this).data('id');
          // Colocar el valor elegido en el input de búsqueda
          $('#search').val(numeroIdentificacion);

          // Mostrar el spinner
          $('#loading-spinner').show();

          // Petición AJAX para filtrar la tabla de Seguimientos:
          $.ajax({
              url: "{{ route('Seguimiento.index') }}", 
              method: "GET",
              dataType: "json",
              data: { search: numeroIdentificacion },
              success: function(response) {
                  // Limpiar la tabla <tbody>
                  $('#seguimiento tbody').html('');
                  // Ocultar el spinner
                  $('#loading-spinner').hide();

                  if (response.incomeedit && response.incomeedit.length > 0) {
                      $.each(response.incomeedit, function(index, student2) {
                          
                          // (1) Determinar estado
                          var estadoTexto = (student2.estado == 1) ? 'Abierto' : 'Cerrado';

                          // (2) Determinar la fecha a mostrar en "Fecha próximo control"
                          var fechaControl = 'finalizado';
                          if (student2.fecha_proximo_control) {
                              fechaControl = student2.fecha_proximo_control;
                          } else if (student2.created_at) {
                              fechaControl = student2.created_at;
                          }

                          // (3) Construir nombre completo
                          var nombreCompleto = (student2.pri_nom_ ?? '') + ' ' +
                                               (student2.seg_nom_ ?? '') + ' ' +
                                               (student2.pri_ape_ ?? '') + ' ' +
                                               (student2.seg_ape_ ?? '');

                          // (4) Construir acciones: Editar, Eliminar (si usertype=1), Detalles, PDF, Ocasional
                          let editUrl      = editUrlTemplate.replace(':id', student2.id);
                          let detalleUrl   = detallesUrlTemplate.replace(':id', student2.id);
                          let pdfUrl       = pdfUrlTemplate.replace(':id', student2.id);
                          let ocasionalUrl = ocasionalUrlTemplate.replace(':id', student2.id);
                          let destroyUrl   = destroyUrlTemplate.replace(':id', student2.id);

                          let acciones = '';

                          // Editar
                          acciones += '<a class="btn btn-success btn-sm" href="' + editUrl + '">' +
                                        '<i class="fas fa-edit"></i>' +
                                      '</a> ';

                          // ELIMINAR (solo si usertype == 1)
                          if (isUsertype1) {
                              acciones += `
                                <a href="${destroyUrl}" 
                                   onclick="event.preventDefault(); 
                                   if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                                      document.getElementById('delete-form-${student2.id}').submit();
                                   }" 
                                   class="btn btn-danger btn-sm">
                                   <i class="fas fa-trash"></i>
                                </a>
                                <form id="delete-form-${student2.id}" action="${destroyUrl}" method="POST" style="display:none;">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                </form>
                              `;
                          }

                          // Detalles si hay motivo
                          if (student2.motivo_reapuertura) {
                              acciones += '<a class="btn btn-primary btn-sm" href="' + detalleUrl + '">' +
                                              '<i class="far fa-eye"></i>' +
                                          '</a> ';
                          }

                          // PDF
                          acciones += '<a href="' + pdfUrl + '" target="_blank" class="btn btn-info btn-sm">' +
                                          '<i class="far fa-file-pdf"></i>' +
                                      '</a> ';

                          // Ocasional
                          if (student2.estado == 1) {
                              acciones += '<a class="btn btn-primary btn-sm" href="' + ocasionalUrl + '">' +
                                              '<i class="fas fa-plus"></i>' +
                                          '</a> ';
                          }

                          // (5) Construir la fila
                          let fila = '<tr>' +
                                '<th>' + (student2.id ?? '') + '</th>' +
                                '<th>' + (student2.creado ?? '') + '</th>' +
                                '<th>' + (student2.num_ide_ ?? '') + '</th>' +
                                '<th>' + (student2.semana ?? '') + '</th>' +
                                '<td>' + nombreCompleto + '</td>' +
                                '<td>' + estadoTexto + '</td>' +
                                '<td>' + (student2.Ips_at_inicial ?? student2.name ?? '') + '</td>' +
                                '<td>' + fechaControl + '</td>' +
                                '<td>' + acciones + '</td>' +
                              '</tr>';

                          // Agregar la fila
                          $('#seguimiento tbody').append(fila);
                      });
                  } else {
                      // Si no hay registros
                      $('#seguimiento tbody').html(
                          '<tr><td colspan="9" class="text-center">No hay registros disponibles</td></tr>'
                      );
                  }

                  // Limpiar la lista de resultados
                  $('#search-results').empty();
              },
              error: function(xhr, status, error) {
                  $('#loading-spinner').hide();
                  console.error("Error al filtrar la tabla (Seguimiento.index):", error);
              }
          });
      });

  }); // Fin document.ready
</script>

  


