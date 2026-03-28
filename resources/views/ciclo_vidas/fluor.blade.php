{{-- resources/views/ciclo_vidas/fluor.blade.php --}}
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
        <div class="card-body">
            @include('ciclo_vidas.partials.date_range_toolbar', [
                'pickerId' => 'daterange',
                'applyButtonId' => 'btnAplicar',
                'applyLabel' => 'Aplicar rango',
                'note' => '<i class="fas fa-info-circle"></i> Datos en vivo (server-side)',
            ])
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row" id="kpis">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="kpiTotal">0</h3>
                    <p>Atenciones</p>
                </div>
                <div class="icon"><i class="fas fa-tooth"></i></div>
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
                    <h3 id="kpiCups">0</h3>
                    <p>CUPS distintos</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabla (Yajra DataTables server-side) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Detalle de atenciones · Flúor (1er semestre)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo Doc</th>
                            <th>Identificación</th>
                            <th>Primer Nombre</th>
                            <th>Segundo Nombre</th>
                            <th>Primer Apellido</th>
                            <th>Segundo Apellido</th>
                            <th>CUPS</th>
                            <th>Descripción</th>
                            <th>Dx Principal</th>
                            <th>Finalidad</th>
                            <th>IPS / Grupo</th>
                            <th>Edad</th>
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
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <style>.small-box{border-radius:16px;}</style>
@stop

@section('js')
    {{-- Moment + DateRangePicker --}}
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @include('ciclo_vidas.partials.date_range_shared_script')

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function () {
            moment.locale('es');

            const rangePicker = window.CicloVidaDateRange.init({
                pickerSelector: '#daterange',
                start: @json($desde),
                end: @json($hasta),
                endExclusive: true
            });

            const tabla = $('#tabla').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: "{{ route('pi.bucal.fluor.sem1.data') }}",
                    type: 'GET',
                    data: function (d) {
                        d.desde = rangePicker.getStart().format('YYYY-MM-DD');
                        d.hasta = rangePicker.getEndExclusive().format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'fechaAtencion', name: 'fechaAtencion' },
                    { data: 'tipoIdentificacion', name: 'tipoIdentificacion' },
                    { data: 'identificacion', name: 'identificacion' },
                    { data: 'primerNombre', name: 'primerNombre' },
                    { data: 'segundoNombre', name: 'segundoNombre' },
                    { data: 'primerApellido', name: 'primerApellido' },
                    { data: 'segundoApellido', name: 'segundoApellido' },
                    { data: 'codigoCups', name: 'codigoCups' },
                    { data: 'descrip', name: 'descrip' },
                    { data: 'diagnosticoPrincipal', name: 'diagnosticoPrincipal' },
                    { data: 'finalidad', name: 'finalidad' },
                    { data: 'ips_Prim', name: 'ips_Prim' },
                    { data: 'edad', name: 'edad' },
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
            });

            // KPIs desde el JSON del server
            $('#tabla').on('xhr.dt', function (e, settings, json) {
                const k = (json && json.kpis) ? json.kpis : {};
                $('#kpiTotal').text((k.total || 0).toLocaleString('es-CO'));
                $('#kpiPacientes').text((k.pacientes || 0).toLocaleString('es-CO'));
                $('#kpiIps').text((k.ips || 0).toLocaleString('es-CO'));
                $('#kpiCups').text((k.cups || 0).toLocaleString('es-CO'));
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
