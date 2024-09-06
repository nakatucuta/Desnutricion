@if(Session::has('mensaje'))
<script>
    Swal.fire({
        icon: 'info',
        title: 'Mensaje',
        text: '{{ Session::get('mensaje') }}',
        confirmButtonText: 'Cerrar'
    });
</script>
@endif

@if(Session::has('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: '{{ Session::get('success') }}',
        confirmButtonText: 'Cerrar'
    });
</script>
@endif

@if(Session::has('error1'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error De Cargue',
        html: `<strong>{!! nl2br(e(Session::get('error1'))) !!}</strong><br><br>`, // Añadimos saltos de línea adicionales
        confirmButtonText: 'Cerrar',
        width: '940px' // Ajusta el ancho del modal, puedes cambiar el valor a lo que desees
    });
</script>
@endif

@if(count($errors) > 0)
<script>
    Swal.fire({
        icon: 'error',
        title: 'Errores encontrados',
        html: '<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>', // Muestra cada error en una lista
        confirmButtonText: 'Cerrar'
    });
</script>
@endif



{{-- <style>
    .custom-alert {
        margin-bottom: 15px; /* Espaciado entre alertas */
        opacity: 1;
        transition: opacity 0.5s ease;
    }
</style>

@if(Session::has('mensaje'))
<div class="alert alert-primary custom-alert" role="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ Session::get('mensaje') }}
</div>
@endif

@if(Session::has('error1'))
<div class="alert alert-danger custom-alert" role="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {!! nl2br(e(Session::get('error1'))) !!}
</div>
@endif

@if(count($errors) > 0)
@foreach($errors->all() as $error)
<div class="alert alert-danger custom-alert" role="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ $error }}
</div>
@endforeach
@endif --}}
