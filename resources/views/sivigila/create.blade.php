@extends('adminlte::page')

@section('title', 'Asignacion Seguimiento 113')

@section('content')
<div class="container-fluid siv-create-wrap">
    <form id="update-form" action="{{ url('/sivigila') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('sivigila.form', ['modo' => 'Crear'])
    </form>
</div>
@stop

@section('css')
<style>
    .siv-create-wrap { padding-top: 1.1rem; padding-bottom: 1.4rem; }
</style>
@stop

@section('js')
<script>
    (function () {
        const selectManejo = document.getElementById('nombreips_manejo_hospita');
        const ipsGroup = document.getElementById('ips_manejo_group');
        const ipsSelect = document.getElementById('Ips_manejo_hospitalario');

        function toggleIpsManejo() {
            if (!selectManejo || !ipsGroup) return;
            const show = (selectManejo.value || '').toUpperCase() === 'SI';
            ipsGroup.style.display = show ? '' : 'none';
            if (ipsSelect) {
                ipsSelect.required = show;
                if (!show) ipsSelect.value = '';
            }
        }

        if (selectManejo) {
            selectManejo.addEventListener('change', toggleIpsManejo);
            toggleIpsManejo();
        }
    })();

    function submitForm() {
        const buttonText = document.getElementById('button-text');
        const loadingIcon = document.getElementById('loading-icon');
        const sendingText = document.getElementById('sending-text');
        const updateBtn = document.getElementById('update-btn');

        if (buttonText) buttonText.style.display = 'none';
        if (loadingIcon) loadingIcon.style.display = 'inline-block';
        if (sendingText) sendingText.style.display = 'inline-block';
        if (updateBtn) updateBtn.disabled = true;

        setTimeout(function () {
            document.getElementById('update-form').submit();
        }, 250);
    }
</script>
@stop
