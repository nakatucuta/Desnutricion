{{-- resources/views/ciclo_vidas/vitaminaa.blade.php --}}
@extends('adminlte::page')

@section('title', 'Ciclos de vida - ' . $etapa['titulo'])

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('ciclosvida.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div>
            <h1 class="mb-0">{{ $etapa['titulo'] }}</h1>
            <small class="text-muted">{{ $etapa['descripcion'] }}</small>
        </div>
    </div>
@stop

@section('content')

    {{-- Filtros superiores --}}
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-end">
            <div class="mr-3 mb-2">
                <label class="mb-1 font-weight-bold">Rango de fecha</label>
                <div id="daterange" class="form-control d-inline-block" style="width: 280px; cursor: pointer;">
                    <i class="far fa-calendar-alt"></i>
                    <span class="ml-2"></span> <i class="fa fa-caret-down float-right mt-1"></i>
                </div>
            </div>

            <div class="mb-2">
                <button id="btnAplicar" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Aplicar
                </button>
            </div>

            <div class="ml-auto mb-2">
                <span class="badge badge-secondary p-2">
                    <i class="fas fa-info-circle"></i> Datos en vivo (server-side)
                </span>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row" id="kpis">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="kpiTotal">0</h3>
                    <p>Registros</p>
                </div>
                <div class="icon"><i class="fas fa-capsules"></i></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="kpiPacientes">0</h3>
                    <p>Pacientes únicos</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="kpiIps">0</h3>
                    <p>IPS / Grupo</p>
                </div>
                <div class="icon"><i class="fas fa-hospital"></i></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="kpiFechas">0</h3>
                    <p>Fechas distintas</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabla (Yajra DataTables server-side) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Detalle de atenciones · Vitamina A</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Cód. IPS</th>
                            <th>Tipo Doc</th>
                            <th>Identificación</th>
                            <th>Primer Nombre</th>
                            <th>Segundo Nombre</th>
                            <th>Primer Apellido</th>
                            <th>Segundo Apellido</th>
                            <th>Fecha Consulta</th>
                            <th>Descripción</th>
                            <th>IPS / Grupo</th>
                            <th>F. Nac.</th>
                            <th>Edad (a)</th>
                            <th>Rango Edad</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@section('css')
    {{-- DateRangePicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>.small-box{border-radius:16px;}</style>
@stop

@section('js')
    {{-- Moment + DateRangePicker --}}
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

   <script>
    $(function () {
        moment.locale('es');

        // === Valores por defecto: Año actual ===
        const startDefault  = moment().startOf('year');  // 1 enero del año actual
        const endExDefault  = moment().clone().add(1, 'day'); // exclusivo = mañana
        const endIncDefault = moment(); // visual = hoy

        function setLabel(start, end) {
            $('#daterange span').text(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        }

        $('#daterange').daterangepicker({
            startDate: startDefault,
            endDate: endIncDefault,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                customRangeLabel: 'Personalizado'
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1,'days'), moment().subtract(1,'days')],
                'Últimos 7 días': [moment().subtract(6,'days'), moment()],
                'Últimos 30 días': [moment().subtract(29,'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Año actual': [moment().startOf('year'), moment()]
            }
        }, setLabel);
        setLabel(startDefault, endIncDefault);

        const tabla = $('#tabla').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: "{{ route('pi.nutri.vitamina_a.data') }}",
                type: 'GET',
                data: function (d) {
                    const drp = $('#daterange').data('daterangepicker');
                    d.desde = drp.startDate.format('YYYY-MM-DD');
                    d.hasta = drp.endDate.clone().add(1,'day').format('YYYY-MM-DD'); // exclusivo
                }
            },
            columns: [
                { data: 'codigoIps',          name: 'codigoIps' },
                { data: 'tipoIdentificacion', name: 'tipoIdentificacion' },
                { data: 'identificacion',     name: 'identificacion' },
                { data: 'primerNombre',       name: 'primerNombre' },
                { data: 'segundoNombre',      name: 'segundoNombre' },
                { data: 'primerApellido',     name: 'primerApellido' },
                { data: 'segundoApellido',    name: 'segundoApellido' },
                { data: 'fechaConsulta',      name: 'fechaConsulta' },
                { data: 'descrip',            name: 'descrip' },
                { data: 'ips_Prim',           name: 'ips_Prim' },
                { data: 'fechaNacimiento',    name: 'fechaNacimiento' },
                { data: 'edad',               name: 'edad' },
                { data: 'rangoEdad',          name: 'rangoEdad' },
            ],
            order: [[7, 'desc']],
            pageLength: 10,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
        });

        // KPIs
        $('#tabla').on('xhr.dt', function (e, settings, json) {
            const k = (json && json.kpis) ? json.kpis : {};
            $('#kpiTotal').text((k.total || 0).toLocaleString('es-CO'));
            $('#kpiPacientes').text((k.pacientes || 0).toLocaleString('es-CO'));
            $('#kpiIps').text((k.ips || 0).toLocaleString('es-CO'));
            $('#kpiFechas').text((k.fechas || 0).toLocaleString('es-CO'));
        });

        // Botón Aplicar
        $('#btnAplicar').on('click', function () {
            $('#btnAplicar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando');
            tabla.ajax.reload(() => {
                $('#btnAplicar').prop('disabled', false).html('<i class="fas fa-sync"></i> Aplicar');
            }, false);
        });
    });
</script>
@stop
