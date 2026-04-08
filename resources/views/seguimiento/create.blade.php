{{-- resources/views/seguimiento/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Nuevo Seguimiento')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    :root {
        --seg-primary: #0f766e;
        --seg-secondary: #0ea5a4;
        --seg-accent: #22c55e;
        --seg-surface: #f4fbfa;
        --seg-border: #d9ece9;
        --seg-text: #12363a;
        --seg-muted: #4f6f73;
    }

    .seg-create {
        padding: 1.25rem 0 1.5rem;
    }

    .seg-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem 1.5rem;
        border-radius: 22px;
        color: #fff;
        margin-bottom: 1.15rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .2), transparent 35%),
            radial-gradient(circle at left center, rgba(255, 255, 255, .14), transparent 30%),
            linear-gradient(130deg, #0f766e, #0e9a95 50%, #14b868);
        box-shadow: 0 16px 34px rgba(13, 86, 82, .25);
    }

    .seg-hero__brand {
        display: flex;
        align-items: center;
        gap: .95rem;
    }

    .seg-hero__logo-wrap {
        width: 74px;
        height: 74px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .18);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25);
        flex-shrink: 0;
    }

    .seg-hero__logo {
        width: 48px;
        height: auto;
        object-fit: contain;
    }

    .seg-hero__eyebrow {
        display: block;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: .35rem;
        opacity: .88;
    }

    .seg-hero__title {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .seg-hero__subtitle {
        margin: .4rem 0 0;
        color: rgba(255, 255, 255, .9);
        max-width: 790px;
        font-size: .94rem;
    }

    .seg-hero__chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-end;
    }

    .seg-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        padding: .42rem .75rem;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .25);
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .seg-form-shell {
        border: 1px solid var(--seg-border);
        background: linear-gradient(180deg, #ffffff, #f8fcfb);
        border-radius: 22px;
        padding: 1rem;
        box-shadow: 0 16px 32px rgba(19, 68, 68, .08);
    }

    .seg-card {
        border: 1px solid var(--seg-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 22px rgba(16, 70, 70, .07);
        margin-bottom: 1rem;
    }

    .seg-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .78rem 1rem;
        color: #fff;
        font-weight: 800;
        letter-spacing: .02em;
        background: linear-gradient(120deg, #0f766e, #129389 55%, #1cb35e);
    }

    .seg-card__body {
        padding: 1rem;
        background: #fff;
    }

    .seg-field {
        margin-bottom: .95rem;
    }

    .seg-field label {
        display: block;
        margin-bottom: .42rem;
        color: var(--seg-text);
        font-size: .88rem;
        font-weight: 700;
    }

    .seg-field .required-dot {
        color: #dc2626;
    }

    .seg-input-group {
        position: relative;
    }

    .seg-input-icon {
        position: absolute;
        left: .72rem;
        top: 50%;
        transform: translateY(-50%);
        color: #578086;
        z-index: 3;
        pointer-events: none;
    }

    .seg-input,
    .seg-textarea,
    .seg-select {
        width: 100%;
        border: 1px solid #c8dfe2;
        border-radius: 12px;
        min-height: 44px;
        padding: .58rem .78rem;
        color: #18363b;
        background: #fff;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .seg-input.with-icon,
    .seg-select.with-icon {
        padding-left: 2.2rem;
    }

    .seg-textarea {
        min-height: 110px;
        resize: vertical;
    }

    .seg-input:focus,
    .seg-textarea:focus,
    .seg-select:focus {
        border-color: #0fa59a;
        outline: none;
        box-shadow: 0 0 0 .16rem rgba(15, 165, 154, .18);
    }

    .seg-year-filter {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .75rem;
        border-radius: 999px;
        background: var(--seg-surface);
        border: 1px solid var(--seg-border);
        margin-bottom: .55rem;
    }

    .seg-year-filter__label {
        margin: 0;
        font-size: .8rem;
        font-weight: 800;
        color: #2f5b61;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .seg-year-filter select {
        border: 1px solid #c8dfe2;
        border-radius: 999px;
        background: #fff;
        min-height: 35px;
        padding: .3rem .75rem;
        color: #1d444b;
        font-weight: 700;
    }

    .seg-year-meta {
        margin-top: .35rem;
        color: var(--seg-muted);
        font-size: .82rem;
        font-weight: 600;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #c8dfe2;
        min-height: 44px;
        border-radius: 12px;
        background: #fff;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #0fa59a;
        box-shadow: 0 0 0 .16rem rgba(15, 165, 154, .18);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 2.1rem;
        color: #18363b;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .select2-container--default .select2-selection--multiple {
        padding: .22rem .42rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(120deg, #0f7d83, #16a34a);
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: .2rem .62rem;
        margin-top: .3rem;
        font-size: .79rem;
        font-weight: 700;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #d9ffea;
        margin-right: .35rem;
    }

    .seg-patient-option {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .6rem;
    }

    .seg-patient-option__name {
        font-weight: 800;
        color: #144147;
        line-height: 1.3;
        margin-bottom: .15rem;
    }

    .seg-patient-option__meta {
        color: #496b71;
        font-size: .8rem;
        line-height: 1.3;
    }

    .seg-patient-year {
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        padding: .14rem .55rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .select2-results__option {
        padding: .55rem .65rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, #0f766e, #0b5f69) !important;
        color: #ffffff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .seg-patient-option__name {
        color: #ffffff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .seg-patient-option__meta {
        color: rgba(255, 255, 255, .92) !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .seg-patient-year {
        background: #ffffff !important;
        color: #0f766e !important;
        border-color: #d9f5ef !important;
    }

    .seg-actions {
        display: flex;
        gap: .7rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: .65rem;
    }

    .seg-btn {
        border: none;
        border-radius: 12px;
        min-height: 44px;
        padding: .65rem 1.2rem;
        font-weight: 700;
    }

    .seg-btn--primary {
        color: #fff;
        background: linear-gradient(135deg, #0f7f87, #16a34a);
        box-shadow: 0 12px 22px rgba(16, 122, 103, .2);
    }

    .seg-btn--ghost {
        color: #235b66;
        background: #e7f5f6;
        border: 1px solid #c7e6e8;
    }

    .seg-error {
        margin-top: .35rem;
        font-size: .78rem;
        color: #d12d2d;
        font-weight: 700;
    }

    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(6, 32, 37, .62);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    #overlay.show {
        display: flex;
    }

    .overlay-content {
        text-align: center;
        color: #fff;
    }

    .overlay-text {
        margin-top: .8rem;
        font-size: 1.06rem;
        font-weight: 700;
    }

    @media (max-width: 991px) {
        .seg-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .seg-hero__chips {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767px) {
        .seg-create {
            padding-top: .8rem;
        }

        .seg-form-shell,
        .seg-card__body {
            padding: .8rem;
        }

        .seg-actions {
            flex-direction: column;
        }

        .seg-btn {
            width: 100%;
        }
    }
</style>
@stop

@section('content')
<div class="container-fluid seg-create">
    @include('seguimiento.mensajes')

    <section class="seg-hero">
        <div class="seg-hero__brand">
            <div class="seg-hero__logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="seg-hero__logo">
            </div>
            <div>
                <span class="seg-hero__eyebrow">Modulo de seguimiento nutricional</span>
                <h1 class="seg-hero__title">Nuevo Seguimiento</h1>
                <p class="seg-hero__subtitle">
                    Registra el control del paciente en una vista renovada, con seleccion inteligente y filtro por anio para trabajar historicos sin perder velocidad.
                </p>
            </div>
        </div>
        <div class="seg-hero__chips">
            <span class="seg-chip"><i class="fas fa-shield-alt"></i> seguro</span>
            <span class="seg-chip"><i class="fas fa-magic"></i> inteligente</span>
            <span class="seg-chip"><i class="fas fa-chart-line"></i> trazable</span>
        </div>
    </section>

    <div class="seg-form-shell">
        <form id="update-form" action="{{ url('Seguimiento') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('seguimiento.form', ['modo' => 'Crear'])
        </form>
    </div>

    <div id="overlay">
        <div class="overlay-content">
            <div class="spinner-border text-light" role="status" style="width: 3.2rem; height: 3.2rem;"></div>
            <div class="overlay-text">Guardando seguimiento y procesando datos...</div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        const $yearFilter = $('#filtro_anio_paciente');
        const $paciente = $('#sivigilas_id');
        const currentYear = '{{ now()->year }}';

        function parsePacienteOption(option) {
            if (!option.id) {
                return option.text;
            }

            const $option = $(option.element);
            const nombre = $option.data('nombre') || option.text;
            const documento = $option.data('documento') || '';
            const codigo = $option.data('codigo') || option.id;
            const fecha = $option.data('fecha') || 'Sin fecha';
            const anio = $option.data('year') || 'N/A';

            return $(
                '<div class="seg-patient-option">' +
                    '<div>' +
                        '<div class="seg-patient-option__name">' + nombre + '</div>' +
                        '<div class="seg-patient-option__meta">ID: ' + codigo + ' | Doc: ' + documento + '<br>Notificacion: ' + fecha + '</div>' +
                    '</div>' +
                    '<span class="seg-patient-year">' + anio + '</span>' +
                '</div>'
            );
        }

        function parsePacienteSelection(option) {
            if (!option.id) {
                return option.text;
            }

            const $option = $(option.element);
            const nombre = $option.data('nombre') || option.text;
            const documento = $option.data('documento') || '';
            return documento ? (nombre + ' - ' + documento) : nombre;
        }

        function getOptionYear(data) {
            if (!data || !data.element) {
                return '';
            }
            return String($(data.element).data('year') || '');
        }

        function patientMatcher(params, data) {
            if (!data.id) {
                return data;
            }

            const selectedYear = String($yearFilter.val() || currentYear);
            const optionYear = getOptionYear(data);
            const yearMatches = (selectedYear === 'all') || (optionYear === selectedYear);

            if (!yearMatches) {
                return null;
            }

            const term = $.trim((params.term || '').toLowerCase());
            if (term === '') {
                return data;
            }

            const $option = $(data.element);
            const searchable = [
                data.text || '',
                $option.data('nombre') || '',
                $option.data('documento') || '',
                $option.data('codigo') || ''
            ].join(' ').toLowerCase();

            return searchable.indexOf(term) > -1 ? data : null;
        }

        $paciente.select2({
            placeholder: 'Buscar por nombre, documento o ID...',
            allowClear: true,
            width: '100%',
            matcher: patientMatcher,
            templateResult: parsePacienteOption,
            templateSelection: parsePacienteSelection,
            escapeMarkup: function (markup) { return markup; }
        });

        $('#medicamento').select2({
            placeholder: 'Selecciona medicamento(s)',
            multiple: true,
            closeOnSelect: false,
            width: '100%'
        });

        $('#clasificacion, #Esquemq_complrto_pai_edad, #Atecion_primocion_y_mantenimiento_res3280_2018, #est_act_menor, #tratamiento_f75, #estado').select2({
            width: '100%'
        });

        function updatePatientCounter() {
            const selectedYear = String($yearFilter.val() || currentYear);
            const totalVisible = $paciente.find('option').filter(function () {
                const val = $(this).val();
                if (val === '') {
                    return false;
                }
                const optionYear = String($(this).data('year') || '');
                return selectedYear === 'all' || optionYear === selectedYear;
            }).length;

            const yearText = selectedYear === 'all' ? 'todos los anios' : selectedYear;
            $('#patientYearHelp').text(totalVisible + ' paciente(s) disponibles para ' + yearText + '.');
        }

        function applyYearFilter() {
            const year = String($yearFilter.val() || currentYear);
            const selected = $paciente.val();

            if (selected) {
                const selectedOption = $paciente.find('option[value="' + selected + '"]');
                const selectedYear = String(selectedOption.data('year') || '');
                const isStillValid = year === 'all' || selectedYear === year;
                if (selectedOption.length && !isStillValid) {
                    $paciente.val(null).trigger('change');
                }
            }

            $paciente.select2('close');
            $paciente.trigger('change.select2');
            updatePatientCounter();
        }

        $yearFilter.on('change', applyYearFilter);
        applyYearFilter();

        $('#estado').on('change', function () {
            if (this.value === '1') {
                $('#input_oculto').stop(true, true).slideDown();
            } else {
                $('#input_oculto').stop(true, true).slideUp();
                $('#fecha_proximo_control').val('');
            }
        }).trigger('change');

        $('#tratamiento_f75').on('change', function () {
            if (this.value === 'SI') {
                $('#row_fecha_recibio_tratf75').stop(true, true).slideDown();
            } else {
                $('#row_fecha_recibio_tratf75').stop(true, true).slideUp();
                $('#fecha_recibio_tratf75').val('');
            }
        }).trigger('change');

        window.submitForm = function () {
            $('#overlay').addClass('show');
            $('#update-btn').prop('disabled', true);
            setTimeout(function () {
                $('#update-form').submit();
            }, 80);
        };

        // Garantiza valor por defecto del filtro: anio actual.
        if (!$yearFilter.val()) {
            $yearFilter.val(currentYear);
            applyYearFilter();
        }
    });
</script>
@stop
