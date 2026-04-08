@extends('adminlte::page')

@section('title', 'Nuevo Seguimiento 412')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    :root {
        --s412-bg: #f3fafb;
        --s412-card: #ffffff;
        --s412-border: #d6eaee;
        --s412-primary: #0f6f7e;
        --s412-primary-2: #118a96;
        --s412-accent: #16a34a;
        --s412-text: #153b42;
        --s412-muted: #57757c;
    }

    .s412-page {
        padding: 1.2rem 0 1.5rem;
    }

    .s412-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.4rem;
        border-radius: 22px;
        color: #fff;
        margin-bottom: 1rem;
        background:
            radial-gradient(circle at 85% 15%, rgba(255, 255, 255, .22), transparent 34%),
            radial-gradient(circle at 5% 75%, rgba(255, 255, 255, .12), transparent 31%),
            linear-gradient(130deg, #0f6f7e, #10929a 52%, #13b465);
        box-shadow: 0 18px 36px rgba(9, 72, 80, .24);
    }

    .s412-hero__brand {
        display: flex;
        align-items: center;
        gap: .95rem;
    }

    .s412-hero__logo-wrap {
        width: 78px;
        height: 78px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .25);
        flex-shrink: 0;
    }

    .s412-hero__logo {
        width: 50px;
        height: auto;
        object-fit: contain;
    }

    .s412-eyebrow {
        display: inline-block;
        margin-bottom: .35rem;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .9;
    }

    .s412-title {
        margin: 0;
        font-size: 1.72rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .s412-subtitle {
        margin: .4rem 0 0;
        max-width: 780px;
        font-size: .93rem;
        color: rgba(255, 255, 255, .92);
    }

    .s412-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-end;
    }

    .s412-chip {
        padding: .42rem .78rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .25);
        background: rgba(255, 255, 255, .14);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .s412-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        flex-wrap: wrap;
        margin-bottom: .9rem;
    }

    .s412-toolbar__hint {
        font-size: .88rem;
        font-weight: 600;
        color: #3f676f;
    }

    .s412-btn {
        border: none;
        border-radius: 12px;
        min-height: 43px;
        padding: .62rem 1rem;
        font-weight: 700;
    }

    .s412-btn--smart {
        color: #fff;
        background: linear-gradient(135deg, #0f7b8c, #16a34a);
        box-shadow: 0 10px 20px rgba(10, 94, 94, .2);
    }

    .s412-shell {
        border: 1px solid var(--s412-border);
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff, #f8fdfe);
        box-shadow: 0 18px 32px rgba(17, 74, 85, .08);
        padding: 1rem;
    }

    .s412-section {
        margin-bottom: 1rem;
        border: 1px solid var(--s412-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(13, 66, 73, .08);
    }

    .s412-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .78rem 1rem;
        color: #fff;
        font-weight: 800;
        letter-spacing: .02em;
        background: linear-gradient(120deg, #0f6f7e, #138993 55%, #18a85e);
    }

    .s412-step {
        padding: .2rem .58rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .24);
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .s412-section__body {
        padding: 1rem;
        background: var(--s412-card);
    }

    .s412-field {
        margin-bottom: .9rem;
    }

    .s412-field label {
        margin-bottom: .38rem;
        color: var(--s412-text);
        font-size: .88rem;
        font-weight: 700;
        display: block;
    }

    .s412-input-group {
        position: relative;
    }

    .s412-icon {
        position: absolute;
        left: .7rem;
        top: 50%;
        transform: translateY(-50%);
        color: #5c8389;
        z-index: 3;
        pointer-events: none;
    }

    .s412-input,
    .s412-select,
    .s412-textarea {
        width: 100%;
        min-height: 43px;
        border: 1px solid #c8dfe4;
        border-radius: 12px;
        background: #fff;
        color: #183d43;
        padding: .56rem .74rem;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .s412-input.with-icon,
    .s412-select.with-icon {
        padding-left: 2.15rem;
    }

    .s412-textarea {
        min-height: 110px;
        resize: vertical;
    }

    .s412-input:focus,
    .s412-select:focus,
    .s412-textarea:focus {
        outline: none;
        border-color: #129ba0;
        box-shadow: 0 0 0 .16rem rgba(18, 155, 160, .18);
    }

    .s412-error {
        margin-top: .32rem;
        color: #cb2f2f;
        font-size: .78rem;
        font-weight: 700;
    }

    .s412-req {
        color: #d51f1f;
    }

    .s412-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: .68rem;
        margin-top: .55rem;
    }

    .s412-btn-main,
    .s412-btn-back {
        border: none;
        border-radius: 12px;
        min-height: 44px;
        padding: .66rem 1.2rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .42rem;
    }

    .s412-btn-main {
        color: #fff;
        background: linear-gradient(135deg, #0e7f8f, #16a34a);
        box-shadow: 0 10px 20px rgba(11, 103, 90, .22);
    }

    .s412-btn-back {
        color: #275a64;
        background: #e8f5f7;
        border: 1px solid #cae4e8;
    }

    .s412-year-filter {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .5rem .75rem;
        border-radius: 999px;
        background: #f3fafb;
        border: 1px solid var(--s412-border);
        margin-bottom: .5rem;
    }

    .s412-year-filter label {
        margin: 0;
        font-size: .78rem;
        font-weight: 800;
        color: #315f67;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .s412-year-filter select {
        border: 1px solid #c8dfe4;
        border-radius: 999px;
        min-height: 34px;
        padding: .28rem .72rem;
        font-weight: 700;
        color: #204e57;
    }

    .s412-year-meta {
        margin-top: .34rem;
        color: #5a7d84;
        font-size: .81rem;
        font-weight: 600;
    }

    .select2-container { width: 100% !important; }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 43px;
        border: 1px solid #c8dfe4;
        border-radius: 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 41px;
        padding-left: 2.05rem;
        color: #163f45;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 41px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #129ba0;
        box-shadow: 0 0 0 .16rem rgba(18, 155, 160, .18);
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(120deg, #0f7282, #17a95f);
        border: none;
        border-radius: 999px;
        color: #fff;
        font-weight: 700;
        font-size: .78rem;
        padding: .18rem .6rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #dffff0;
        margin-right: .35rem;
    }

    .s412-patient-option {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .6rem;
    }

    .s412-patient-option__name {
        color: #113f47;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: .12rem;
    }

    .s412-patient-option__meta {
        color: #537880;
        font-size: .8rem;
        line-height: 1.3;
    }

    .s412-patient-year {
        border-radius: 999px;
        border: 1px solid #bff2ce;
        background: #dcfce7;
        color: #166534;
        font-size: .72rem;
        font-weight: 800;
        padding: .14rem .55rem;
        white-space: nowrap;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, #0f6f7e, #0e5f70) !important;
        color: #fff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .s412-patient-option__name {
        color: #fff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .s412-patient-option__meta {
        color: rgba(255, 255, 255, .92) !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .s412-patient-year {
        background: #fff !important;
        color: #0f6f7e !important;
        border-color: #d8edf0 !important;
    }

    .s412-modal .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 71, 80, .24);
    }

    .s412-modal .modal-header {
        color: #fff;
        background: linear-gradient(130deg, #0f6f7e, #129099 60%, #1ba85f);
        border: none;
    }

    .s412-modal .modal-title {
        font-weight: 800;
    }

    .s412-table-wrap {
        overflow: auto;
        max-height: 70vh;
    }

    .s412-table {
        width: 100%;
        margin: 0;
        min-width: 1150px;
    }

    .s412-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #ecf8fa;
        color: #3d666f;
        border-bottom: 1px solid #d4e8ec;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-size: .73rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .s412-table tbody td {
        font-size: .81rem;
        color: #2d535a;
        white-space: nowrap;
        border-color: #e0ecef;
    }

    #s412-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(7, 34, 40, .6);
        align-items: center;
        justify-content: center;
    }

    #s412-overlay.show { display: flex; }

    .s412-overlay__content {
        text-align: center;
        color: #fff;
    }

    .s412-overlay__text {
        margin-top: .8rem;
        font-weight: 700;
    }

    @media (max-width: 991px) {
        .s412-hero { flex-direction: column; }
        .s412-chips { justify-content: flex-start; }
    }

    @media (max-width: 767px) {
        .s412-page { padding-top: .75rem; }
        .s412-shell, .s412-section__body { padding: .8rem; }
        .s412-actions { flex-direction: column; }
        .s412-btn-main, .s412-btn-back { width: 100%; justify-content: center; }
    }
</style>
@stop

@section('content')
<div class="container-fluid s412-page">
    @include('seguimiento_412.mensajes')

    <section class="s412-hero">
        <div class="s412-hero__brand">
            <div class="s412-hero__logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="s412-hero__logo">
            </div>
            <div>
                <span class="s412-eyebrow">Seguimiento 412</span>
                <h1 class="s412-title">Nuevo Seguimiento Clinico</h1>
                <p class="s412-subtitle">
                    Interfaz renovada para una captura rapida, inteligente y trazable del seguimiento nutricional.
                    Incluye selector avanzado de paciente, filtro historico por anio y validaciones visuales claras.
                </p>
            </div>
        </div>
        <div class="s412-chips">
            <span class="s412-chip"><i class="fas fa-microchip mr-1"></i> tecnologico</span>
            <span class="s412-chip"><i class="fas fa-shield-alt mr-1"></i> seguro</span>
            <span class="s412-chip"><i class="fas fa-rocket mr-1"></i> agil</span>
        </div>
    </section>

    <div class="s412-toolbar">
        <p class="s412-toolbar__hint mb-0">Puedes consultar el consolidado rapido de pacientes antes de registrar el seguimiento.</p>
        <button type="button" class="s412-btn s412-btn--smart" data-toggle="modal" data-target="#modalPacientes412">
            <i class="fas fa-table mr-1"></i> Ver datos de apoyo
        </button>
    </div>

    <div class="s412-shell">
        <form id="update-form" action="{{ url('/new412_seguimiento') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('seguimiento_412.form', ['modo' => 'Crear'])
        </form>
    </div>

    <div id="s412-overlay">
        <div class="s412-overlay__content">
            <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;"></div>
            <div class="s412-overlay__text">Guardando seguimiento y procesando informacion...</div>
        </div>
    </div>
</div>

<div class="modal fade s412-modal" id="modalPacientes412" tabindex="-1" role="dialog" aria-labelledby="modalPacientes412Label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPacientes412Label"><i class="fas fa-notes-medical mr-1"></i> Resumen de pacientes 412</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="s412-table-wrap">
                    <table class="table table-hover s412-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre coperante</th>
                                <th>Profesional</th>
                                <th>Numero profesional</th>
                                <th>Fecha captacion</th>
                                <th>Cuidador</th>
                                <th>ID cuidador</th>
                                <th>Telefono cuidador</th>
                                <th>EAPB cuidador</th>
                                <th>Autoridad ancestral</th>
                                <th>Contacto autoridad</th>
                                <th>Tipo ID</th>
                                <th>Identificacion</th>
                                <th>Nombre menor</th>
                                <th>Sexo</th>
                                <th>Fecha nacimiento</th>
                                <th>Edad meses</th>
                                <th>Regimen</th>
                                <th>EAPB menor</th>
                                <th>Peso</th>
                                <th>Talla</th>
                                <th>Perimetro braquial</th>
                                <th>Signos infeccion</th>
                                <th>Signos desnutricion</th>
                                <th>Puntaje Z</th>
                                <th>Clasificacion</th>
                                <th>Municipio</th>
                                <th>UDS</th>
                                <th>Rancheria</th>
                                <th>Ubicacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sivigilas2030 as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td>{{ $p->nombre_coperante }}</td>
                                    <td>{{ $p->nombre_profesional }}</td>
                                    <td>{{ $p->numero_profesional }}</td>
                                    <td>{{ $p->fecha_captacion }}</td>
                                    <td>{{ $p->nombre_cuidador }}</td>
                                    <td>{{ $p->identioficacion_cuidador }}</td>
                                    <td>{{ $p->telefono_cuidador }}</td>
                                    <td>{{ $p->nombre_eapb_cuidador }}</td>
                                    <td>{{ $p->nombre_autoridad_trad_ansestral }}</td>
                                    <td>{{ $p->datos_contacto_autoridad }}</td>
                                    <td>{{ $p->tipo_identificacion }}</td>
                                    <td>{{ $p->numero_identificacion }}</td>
                                    <td>{{ $p->primer_nombre }} {{ $p->segundo_nombre }} {{ $p->primer_apellido }} {{ $p->segundo_apellido }}</td>
                                    <td>{{ $p->sexo }}</td>
                                    <td>{{ $p->fecha_nacimieto_nino }}</td>
                                    <td>{{ $p->edad_meses }}</td>
                                    <td>{{ $p->regimen_afiliacion }}</td>
                                    <td>{{ $p->nombre_eapb_menor }}</td>
                                    <td>{{ $p->peso_kg }}</td>
                                    <td>{{ $p->logitud_talla_cm }}</td>
                                    <td>{{ $p->perimetro_braqueal }}</td>
                                    <td>{{ $p->signos_peligro_infeccion_respiratoria }}</td>
                                    <td>{{ $p->sexosignos_desnutricion }}</td>
                                    <td>{{ $p->puntaje_z }}</td>
                                    <td>{{ $p->calsificacion_antropometrica }}</td>
                                    <td>{{ $p->municipio }}</td>
                                    <td>{{ $p->uds }}</td>
                                    <td>{{ $p->nombre_rancheria }}</td>
                                    <td>{{ $p->ubicacion_casa }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        const $yearFilter = $('#filtro_anio_paciente_412');
        const $paciente = $('#cargue412_id');
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
                '<div class="s412-patient-option">' +
                    '<div>' +
                        '<div class="s412-patient-option__name">' + nombre + '</div>' +
                        '<div class="s412-patient-option__meta">ID: ' + codigo + ' | Doc: ' + documento + '<br>Registro: ' + fecha + '</div>' +
                    '</div>' +
                    '<span class="s412-patient-year">' + anio + '</span>' +
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

        $('#clasificacion, #Esquemq_complrto_pai_edad, #Atecion_primocion_y_mantenimiento_res3280_2018, #est_act_menor, #tratamiento_f75, #estado').select2({
            width: '100%'
        });

        $('#medicamento').select2({
            placeholder: 'Selecciona medicamento(s)',
            closeOnSelect: false,
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
            $('#patientYearHelp412').text(totalVisible + ' paciente(s) disponibles para ' + yearText + '.');
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
            if (this.value === '0') {
                $('#input_oculto').stop(true, true).slideUp();
                $('#fecha_proximo_control').val('');
                $('#inputsuperoculto').stop(true, true).slideDown();
            } else {
                $('#input_oculto').stop(true, true).slideDown();
                $('#inputsuperoculto').stop(true, true).slideUp().find('textarea').val('');
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
            $('#s412-overlay').addClass('show');
            $('#update-btn').prop('disabled', true);
            setTimeout(function () {
                $('#update-form').submit();
            }, 80);
        };

        if (!$yearFilter.val()) {
            $yearFilter.val(currentYear);
            applyYearFilter();
        }
    });
</script>
@stop
