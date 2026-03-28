{{-- resources/views/ciclo_vidas/alerta.blade.php --}}
@extends('adminlte::page')

@section('title', 'Ciclos de vida - Alertas')

@section('content_header')
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-danger" style="font-size:0.9rem"><i class="fas fa-bell"></i></span>
        <h1 class="m-0">Alertas PI — Pacientes con actividades pendientes</h1>
    </div>
    <p class="text-muted mt-1 mb-0">
        Ventana por defecto: últimos 30 días. Usa los filtros para ajustar el rango y la edad.
    </p>
@stop

@section('css')
    {{-- DataTables core + Buttons + Responsive (solo CSS) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <style>
        .card { border-radius: 14px; }
        .dt-buttons .btn { margin-right: .25rem; }
        #tabla-alertas thead th { white-space: nowrap; }
        #tabla-alertas td { vertical-align: middle; }

        /* Badges por tipo de actividad */
        .badge-mg      { background:#0ea5e9; }
        .badge-enf     { background:#10b981; }
        .badge-odon    { background:#6366f1; }
        .badge-fluor   { background:#f59e0b; }
        .badge-placa   { background:#a855f7; }
        .badge-sellant { background:#ef4444; }

        /* Overlay con barra de progreso */
        .overlay {
            position:absolute; inset:0; background:rgba(255,255,255,.75);
            display:none; align-items:center; justify-content:center; z-index:2; border-radius:14px;
        }
        .loader-box { width:min(520px,92%); text-align:center; }
        .loader-title { font-weight:700; margin-bottom:.5rem; }
        .progress-outer {
            height:14px; background:#e5e7eb; border-radius:999px; overflow:hidden;
            box-shadow: inset 0 0 0 1px #d1d5db;
        }
        .progress-inner { height:100%; width:0%; background:linear-gradient(90deg,#3b82f6,#22c55e); transition: width .25s ease; }
        .progress-meta { display:flex; justify-content:space-between; font-size:.85rem; margin-top:.5rem; color:#374151; }
        .progress-percent { font-variant-numeric: tabular-nums; font-weight:700; }

        /* KPIs */
        .kpi { border-radius: 12px; padding: .75rem 1rem; background: #f8fafc; border:1px solid #e5e7eb; }
        .kpi .num { font-weight: 700; font-size: 1.1rem; }
    </style>
@stop

@section('content')
<div class="card position-relative">
    {{-- Overlay con barra de progreso --}}
    <div class="overlay" id="overlay" aria-live="polite" aria-busy="true">
        <div class="loader-box">
            <div class="loader-title">Cargando alertas…</div>
            <div class="progress-outer" aria-label="Porcentaje de carga" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-inner" id="progress-bar"></div>
            </div>
            <div class="progress-meta">
                <span id="progress-stage">Preparando…</span>
                <span class="progress-percent" id="progress-pct">0%</span>
            </div>
        </div>
    </div>

    <div class="card-body">
        {{-- Filtros --}}
        <form id="filtros" class="row g-3 align-items-end mb-3">
            @csrf
            <div class="col-12">
                @include('ciclo_vidas.partials.date_range_toolbar', [
                    'pickerId' => 'daterange',
                    'note' => '<i class="fas fa-info-circle"></i> Corte materializado para alertas y notificacion a prestadores.',
                ])
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtrar edad</label>
                <select name="filtraEdad" id="filtraEdad" class="form-control">
                    <option value="1" selected>Fijo PI (0-5)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Edad mín (años)</label>
                <input type="number" name="edadMin" id="edadMin" class="form-control" value="0" min="0" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Edad máx (años)</label>
                <input type="number" name="edadMax" id="edadMax" class="form-control" value="5" min="0" readonly>
            </div>
            <div class="col-md-3 d-grid">
                <button type="button" id="btn-aplicar" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Aplicar
                </button>
            </div>
        </form>

        {{-- Acciones y KPIs --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <button id="btn-email" class="btn btn-danger">
                    <i class="fas fa-paper-plane"></i> Enviar correo masivo
                </button>
            </div>
            <div class="d-flex gap-2">
                <div class="kpi"><div class="text-muted">Registros (página)</div><div class="num" id="kpi-reg">0</div></div>
                <div class="kpi"><div class="text-muted">Rango</div><div class="num" id="kpi-range">—</div></div>
                <div class="kpi"><div class="text-muted">Tiempo</div><div class="num" id="kpi-time">—</div></div>
            </div>
        </div>

        <div id="alerta-errores" class="alert alert-danger d-none"></div>

        <table id="tabla-alertas" class="table table-striped table-hover nowrap w-100">
            <thead class="bg-light">
            <tr>
                <th>Tipo ID</th>
                <th>Identificación</th>
                <th>Apellidos</th>
                <th>Nombres</th>
                <th>F. Nac.</th>
                <th>Edad (a)</th>
                <th>Edad (m)</th>
                <th>IPS Primaria</th>
                <th>Cód. Hab.</th>
                <th>Actividad</th> {{-- badges desde descrip --}}
                <th>descrip</th>   {{-- oculta para búsqueda/export --}}
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@stop

@section('js')
    {{-- DataTables + Buttons + Responsive --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @include('ciclo_vidas.partials.date_range_shared_script')

    <script>
        /* ===== Helpers UI ===== */
        const $ = window.jQuery;
        let tabla = null;

        const $overlay  = document.getElementById('overlay');
        const $pbar     = document.getElementById('progress-bar');
        const $ppct     = document.getElementById('progress-pct');
        const $pstage   = document.getElementById('progress-stage');
        const $kpiReg   = document.getElementById('kpi-reg');
        const $kpiRange = document.getElementById('kpi-range');
        const $kpiTime  = document.getElementById('kpi-time');
        const $errBox   = document.getElementById('alerta-errores');

        let progressTimer = null;
        let loadStartTs = 0;
        let rangePicker = null;

        function showOverlay(flag){ $overlay.style.display = flag ? 'flex' : 'none'; }
        function setProgress(pct, stage){
            const val = Math.max(0, Math.min(100, Math.round(pct)));
            $pbar.style.width = val + '%';
            $ppct.textContent = val + '%';
            if (stage) $pstage.textContent = stage;
        }
        function rampProgress(from, to, durationMs, stage){
            clearInterval(progressTimer);
            const start = performance.now();
            const delta = to - from;
            setProgress(from, stage);
            progressTimer = setInterval(() => {
                const k = Math.min(1, (performance.now() - start) / durationMs);
                setProgress(from + delta * k, stage);
                if (k >= 1) clearInterval(progressTimer);
            }, 100);
        }
        function startLoading(){
            loadStartTs = performance.now();
            showOverlay(true);
            setProgress(0, 'Preparando…');
            rampProgress(5, 35, 600, 'Aplicando filtros…');
            setTimeout(() => rampProgress(35, 70, 1200, 'Consultando servidor…'), 600);
        }
        function dataArriving(){ rampProgress(70, 90, 400, 'Procesando datos…'); }
        function finishLoading(){
            clearInterval(progressTimer);
            setProgress(100, 'Listo');
            setTimeout(() => showOverlay(false), 200);
        }
        function showError(msg){
            $errBox.classList.remove('alert-warning');
            $errBox.classList.add('alert-danger');
            $errBox.classList.remove('d-none');
            $errBox.textContent = msg || 'Ocurrió un error.';
            console.error(msg);
        }
        function hideError(){ $errBox.classList.add('d-none'); $errBox.textContent = ''; }
        function showNotice(msg){
            if (!msg) return;
            $errBox.classList.remove('alert-danger');
            $errBox.classList.add('alert-warning');
            $errBox.classList.remove('d-none');
            $errBox.textContent = msg;
        }
        function getParams(){
            return {
                desde: rangePicker.getStart().format('YYYY-MM-DD'),
                hasta: rangePicker.getEndExclusive().format('YYYY-MM-DD'),
                filtraEdad: document.getElementById('filtraEdad').value,
                edadMin: document.getElementById('edadMin').value,
                edadMax: document.getElementById('edadMax').value,
                _token: document.querySelector('input[name="_token"]').value
            };
        }
        function formatDateISO(iso){
            if(!iso) return '';
            try { const d = new Date(iso); if (isNaN(d)) return iso; return d.toLocaleDateString(); }
            catch(e){ return iso; }
        }
        function fmtMs(ms){ return ms < 1000 ? `${Math.round(ms)} ms` : (ms/1000).toFixed(2) + ' s'; }

        /* ===== Badges desde `descrip` =====
           Normaliza tildes/ñ y detecta múltiples actividades si aparecen */
        const BADGE_MAP = {
            mg:     { cls:'badge-mg',     label:'Medicina Gral.' },
            enf:    { cls:'badge-enf',    label:'Enfermería' },
            odon:   { cls:'badge-odon',   label:'Odontología' },
            fluor:  { cls:'badge-fluor',  label:'Flúor barniz' },
            placa:  { cls:'badge-placa',  label:'Control de placa' },
            sellant:{ cls:'badge-sellant',label:'Sellantes' },
            otra:   { cls:'bg-secondary', label:'Otra' }
        };
        function normalize(s){
            return (s||'')
                .toString()
                .normalize('NFD').replace(/[\u0300-\u036f]/g,'') // quita tildes
                .replace(/ñ/gi,'n')
                .toUpperCase();
        }
        function detectCodesFromDescrip(descrip){
            const n = normalize(descrip);
            const codes = new Set();
            // Detectores (no excluyentes)
            if (n.includes('SELLANT')) codes.add('sellant');
            if (n.includes('PLACA'))   codes.add('placa');
            if (n.includes('FLUOR') || n.includes('BARNIZ')) codes.add('fluor');
            if (n.includes('ODONTO'))  codes.add('odon');
            if (n.includes('ENFERMER'))codes.add('enf');
            if (n.includes('MEDICINA GENERAL') || n.includes('MEDICINA')) codes.add('mg');

            // Si no se detectó nada, retorna ['otra']
            if (!codes.size) codes.add('otra');
            return Array.from(codes);
        }
        function badgesHtmlFromDescrip(descrip){
            const title = (descrip||'').replace(/"/g,'&quot;');
            const codes = detectCodesFromDescrip(descrip);
            return codes.map(c => {
                const m = BADGE_MAP[c] || BADGE_MAP.otra;
                return `<span class="badge ${m.cls} me-1" title="${title}">${m.label}</span>`;
            }).join(' ');
        }

        /* ===== DataTables ===== */
        $.fn.dataTable.ext.errMode = 'none';

        document.addEventListener('DOMContentLoaded', function(){
            moment.locale('es');
            rangePicker = window.CicloVidaDateRange.init({
                pickerSelector: '#daterange',
                start: @json(now()->subDays((int) data_get(config('ciclosvida.courses.primera_infancia'), 'refresh.days', 120))->toDateString()),
                end: @json(now()->addDay()->toDateString()),
                endExclusive: true
            });

            const RUTA_DATA  = "{{ route('pi.alertas.data') }}";
            const RUTA_EMAIL = "{{ route('pi.alertas.email') }}";

            tabla = $('#tabla-alertas').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,       // ← Yajra
                deferRender: true,
                ajax: {
                    url: RUTA_DATA,
                    type: 'GET',
                    data: function(d){
                        const p = getParams();
                        d.desde = p.desde; d.hasta = p.hasta;
                        d.filtraEdad = p.filtraEdad; d.edadMin = p.edadMin; d.edadMax = p.edadMax;
                        hideError(); startLoading();
                    },
                    dataSrc: function(json){
                        dataArriving();
                        if (json.cut) {
                            $kpiRange.textContent = `${json.cut.from} → ${json.cut.to}`;
                        }
                        if (json.notice) {
                            showNotice(json.notice);
                        }
                        return json.data || [];
                    },
                    error: function(xhr){
                        const elapsed = performance.now() - loadStartTs;
                        $kpiTime.textContent = fmtMs(elapsed);
                        let msg = 'Error de red/servidor.';
                        try { const r = xhr.responseJSON; if (r && r.error) msg = r.error; } catch(e){}
                        showError(msg);
                        setProgress(100, 'Error');
                        setTimeout(()=>showOverlay(false), 300);
                    },
                    timeout: 180000
                },
                columns: [
                    { data:'tipoIdentificacion', width:'80px',  className:'text-nowrap' },
                    { data:'identificacion',     width:'120px', className:'text-nowrap' },
                    { data:'apellidos',          width:'180px' },
                    { data:'nombres',            width:'180px' },
                    { data:'fechaNacimiento',    render:(v)=>formatDateISO(v), width:'110px', className:'text-nowrap' },
                    { data:'edadAnios',          width:'90px',  className:'text-end' },
                    { data:'edadMeses',          width:'90px',  className:'text-end' },
                    { data:'ips_Prim',           width:'220px' },
                    { data:'codigoHabilitacion', width:'120px', className:'text-nowrap' },

                    // 🔧 Actividad: SIEMPRE se renderiza a partir de `descrip`
                    { data:'descrip', orderable:false, width:'160px', className:'text-center',
                      render: function(value, type, row){ return badgesHtmlFromDescrip(value); } },

                    // Oculta pero útil para búsqueda/export
                    { data:'descrip', visible:false, searchable:true }
                ],
                order: [[2,'asc'],[3,'asc']],
                pageLength: 10,
                lengthMenu: [[10,25,50,100,500],[10,25,50,100,500]],
                dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-2"Bf>rt<"d-flex justify-content-between align-items-center mt-2"lip>',
                buttons: [
                    { extend:'excelHtml5', text:'<i class="far fa-file-excel"></i> Excel', className:'btn btn-success btn-sm',
                      exportOptions:{ columns:[0,1,2,3,4,5,6,7,8,10] } }, // incluye 'descrip'
                    { extend:'csvHtml5',   text:'<i class="fas fa-file-csv"></i> CSV',    className:'btn btn-outline-secondary btn-sm',
                      exportOptions:{ columns:[0,1,2,3,4,5,6,7,8,10] } },
                    { extend:'print',      text:'<i class="fas fa-print"></i> Imprimir',  className:'btn btn-outline-primary btn-sm' },
                    { extend:'colvis',     text:'<i class="fas fa-columns"></i> Columnas',className:'btn btn-outline-dark btn-sm' },
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
            });

            // Al terminar el draw, medimos tiempo y ocultamos overlay
            $('#tabla-alertas').on('draw.dt', function(){
                const info = tabla.page.info();
                $kpiReg.textContent = (info.end - info.start);
                const elapsed = performance.now() - loadStartTs;
                $kpiTime.textContent = fmtMs(elapsed);
                finishLoading();
            });

            // Recarga con filtros
            document.getElementById('btn-aplicar').addEventListener('click', function(){
                tabla.ajax.reload(null, true);
            });

            // Envío masivo de correo
            document.getElementById('btn-email').addEventListener('click', function(){
                const p = getParams();
                showOverlay(true); setProgress(15, 'Preparando envío…');
                fetch("{{ route('pi.alertas.email') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': p._token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        desde: p.desde, hasta: p.hasta,
                        filtraEdad: p.filtraEdad, edadMin: p.edadMin, edadMax: p.edadMax
                    })
                }).then(r => r.json())
                  .then(resp => {
                      setProgress(100, resp.ok ? 'Correos enviados' : 'Error');
                      setTimeout(()=>showOverlay(false), 300);
                      if (resp.ok) { alert(resp.msg || 'Correos enviados.'); }
                      else { showError(resp.msg || 'Error enviando correos.'); }
                  }).catch(() => {
                      setProgress(100, 'Error');
                      setTimeout(()=>showOverlay(false), 300);
                      showError('Error de red al enviar correos.');
                  });
            });
        });
    </script>
@stop
