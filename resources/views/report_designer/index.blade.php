@extends('adminlte::page')

@section('title', 'Disenador de Reportes')

@section('content')
<div class="container-fluid" style="padding-top:1.1rem; padding-bottom:1.3rem;">
    <div style="border-radius:22px; color:#fff; padding:1.25rem 1.35rem; margin-bottom:1rem; background:radial-gradient(circle at 85% 20%, rgba(255,255,255,.22), transparent 36%),linear-gradient(130deg,#0f6f7e,#11939a 52%,#17a85f); box-shadow:0 16px 32px rgba(11,70,80,.24); display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
        <div style="display:flex; gap:.9rem; align-items:center;">
            <div style="width:72px;height:72px;border-radius:18px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.24);">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" style="width:46px;height:auto;object-fit:contain;">
            </div>
            <div>
                <div style="font-size:.76rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; opacity:.92;">Disenador avanzado</div>
                <h1 style="margin:0; font-size:1.6rem; font-weight:800; line-height:1.2;">Reporte personalizado {{ $moduleLabel }}</h1>
                <p style="margin:.35rem 0 0; max-width:780px; font-size:.92rem; color:rgba(255,255,255,.92);">
                    Selecciona variables, aplica filtros y genera una vista previa antes de descargar. Las variables basicas de paciente siempre se incluyen para trazabilidad.
                </p>
            </div>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-light btn-sm" style="border-radius:10px; font-weight:700;"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
    </div>

    <div class="card" style="border:1px solid #d8eaee; border-radius:18px; box-shadow:0 12px 26px rgba(18,66,79,.1); overflow:hidden;">
        <div class="card-header" style="background:#f4fafb; border-bottom:1px solid #e1edf0;">
            <strong style="color:#2d5b64;"><i class="fas fa-sliders-h mr-1"></i> Configuracion del reporte</strong>
        </div>
        <div class="card-body">
            <form id="reportDesignerForm" method="POST" action="{{ $exportUrl }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label style="font-weight:700; color:#335c64;">Ano</label>
                        <input type="number" name="anio" id="filterYear" class="form-control" min="2020" max="2100" value="{{ $year }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label style="font-weight:700; color:#335c64;">Estado</label>
                        <select name="estado" id="filterStatus" class="form-control">
                            <option value="all">Todos</option>
                            <option value="abierto">Abiertos</option>
                            <option value="cerrado">Cerrados</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label style="font-weight:700; color:#335c64;">Busqueda</label>
                        <input type="text" name="q" id="filterQuery" class="form-control" placeholder="Nombre, documento, clasificacion...">
                    </div>
                </div>

                <div class="mb-2" style="font-weight:700; color:#335c64;">Variables a incluir</div>
                <div class="row" style="max-height:270px; overflow:auto; border:1px solid #e2edf0; border-radius:12px; padding:.75rem; background:#fbfeff;">
                    @foreach($columns as $key => $label)
                        @php $locked = in_array($key, $lockedColumns, true); @endphp
                        <div class="col-md-4 col-sm-6 mb-2">
                            <label style="display:flex; align-items:center; gap:.45rem; margin-bottom:0; color:#244c55; font-weight:600;">
                                <input type="checkbox" name="columns[]" value="{{ $key }}" {{ $locked ? 'checked disabled' : 'checked' }}>
                                <span>{{ $label }}</span>
                                @if($locked)
                                    <small class="text-muted">(fija)</small>
                                @endif
                            </label>
                            @if($locked)
                                <input type="hidden" name="columns[]" value="{{ $key }}">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="d-flex flex-wrap" style="gap:.55rem; margin-top:1rem;">
                    <button type="button" id="btnPreview" class="btn btn-info"><i class="fas fa-eye mr-1"></i> Vista previa</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-download mr-1"></i> Descargar CSV</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3" style="border:1px solid #d8eaee; border-radius:18px; box-shadow:0 12px 26px rgba(18,66,79,.08); overflow:hidden;">
        <div class="card-header" style="background:#f4fafb; border-bottom:1px solid #e1edf0; display:flex; justify-content:space-between; align-items:center;">
            <strong style="color:#2d5b64;"><i class="fas fa-table mr-1"></i> Vista previa del reporte</strong>
            <span id="previewMeta" class="text-muted" style="font-size:.85rem;">Sin datos aun</span>
        </div>
        <div class="card-body p-0">
            <div id="previewEmpty" class="text-center text-muted py-4">Configura filtros y haz clic en "Vista previa".</div>
            <div id="previewTableWrap" style="display:none; overflow:auto; max-height:460px;">
                <table class="table table-sm table-hover mb-0" id="previewTable"></table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
(function () {
    const form = document.getElementById('reportDesignerForm');
    const btnPreview = document.getElementById('btnPreview');
    const previewWrap = document.getElementById('previewTableWrap');
    const previewTable = document.getElementById('previewTable');
    const previewEmpty = document.getElementById('previewEmpty');
    const previewMeta = document.getElementById('previewMeta');
    const previewUrl = @json($previewUrl);

    function renderPreview(payload) {
        const headers = payload.headers || [];
        const rows = payload.rows || [];

        if (!headers.length) {
            previewWrap.style.display = 'none';
            previewEmpty.style.display = '';
            previewEmpty.textContent = 'No se pudo generar la vista previa.';
            previewMeta.textContent = 'Sin datos';
            return;
        }

        let thead = '<thead style="position:sticky;top:0;background:#eff8fa;"><tr>';
        headers.forEach(h => thead += `<th style="white-space:nowrap; font-size:.77rem; text-transform:uppercase; letter-spacing:.03em; color:#446a72;">${h}</th>`);
        thead += '</tr></thead>';

        let tbody = '<tbody>';
        if (!rows.length) {
            tbody += `<tr><td colspan="${headers.length}" class="text-center text-muted py-3">No hay datos para los filtros seleccionados.</td></tr>`;
        } else {
            rows.forEach(r => {
                tbody += '<tr>';
                r.forEach(cell => {
                    const value = (cell ?? '').toString();
                    tbody += `<td style="font-size:.84rem; color:#264c55; white-space:nowrap;">${value.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</td>`;
                });
                tbody += '</tr>';
            });
        }
        tbody += '</tbody>';

        previewTable.innerHTML = thead + tbody;
        previewWrap.style.display = '';
        previewEmpty.style.display = 'none';
        const total = payload.meta?.total ?? rows.length;
        previewMeta.textContent = `Registros en vista previa: ${total}`;
    }

    btnPreview.addEventListener('click', async function () {
        btnPreview.disabled = true;
        btnPreview.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando...';

        try {
            const fd = new FormData(form);
            const response = await fetch(previewUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
                credentials: 'same-origin'
            });

            const payload = await response.json();
            renderPreview(payload);
        } catch (e) {
            previewWrap.style.display = 'none';
            previewEmpty.style.display = '';
            previewEmpty.textContent = 'Error al cargar vista previa. Intenta nuevamente.';
            previewMeta.textContent = 'Error de conexion';
        } finally {
            btnPreview.disabled = false;
            btnPreview.innerHTML = '<i class="fas fa-eye mr-1"></i> Vista previa';
        }
    });
})();
</script>
@stop
