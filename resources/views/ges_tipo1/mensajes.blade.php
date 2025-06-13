{{-- resources/views/partials/mensajes.blade.php --}}
{{-- Incluye primero la librería de SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: {!! json_encode(session('success')) !!},
            confirmButtonText: 'OK'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            html: {!! json_encode(session('error')) !!},
            confirmButtonText: 'OK'
        });
    @endif
});
</script>
