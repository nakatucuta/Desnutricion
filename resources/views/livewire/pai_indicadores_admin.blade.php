@extends('adminlte::page')

@section('title', 'PAI - Metas Vacunacion')

@section('content_header')
<div class="pai-head">
    <div>
        <h1 class="pai-title mb-1">Metas de Vacunacion</h1>
        <div class="text-muted">CRUD directo sobre <code>metas_vacunacion</code> para ajustar la programacion anual por vigencia.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('afiliado.stats.settings.index') }}" class="btn btn-outline-dark">
            <i class="fas fa-sliders-h mr-1"></i> Parametrizaciones PAI
        </a>
        <a href="{{ route('afiliado.stats.view') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Estadisticas
        </a>
        <button type="button" class="btn btn-outline-primary" id="btnReload">
            <i class="fas fa-sync-alt mr-1"></i> Recargar
        </button>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4 pai-shell">
    @include('livewire.partials.pai_admin_nav')

    <div class="pai-hero card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="pai-hero__eyebrow">Edicion rapida</div>
                    <h2 class="pai-hero__title mb-2">Administra la meta anual de cada IPS, municipio y regimen</h2>
                    <p class="mb-0 text-muted">
                        Cada fila representa una meta anual. Aqui puedes crear, corregir o eliminar registros sin pasar por hojas tecnicas ni tablas intermedias.
                    </p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="pai-summary-grid">
                        <div class="pai-summary">
                            <div class="pai-summary__label">Registros</div>
                            <div class="pai-summary__value" id="sumRows">{{ number_format((int) data_get($summary, 'rows', 0)) }}</div>
                        </div>
                        <div class="pai-summary">
                            <div class="pai-summary__label">IPS</div>
                            <div class="pai-summary__value" id="sumIps">{{ number_format((int) data_get($summary, 'prestadores', 0)) }}</div>
                        </div>
                        <div class="pai-summary">
                            <div class="pai-summary__label">Municipios</div>
                            <div class="pai-summary__value" id="sumMuns">{{ number_format((int) data_get($summary, 'municipios', 0)) }}</div>
                        </div>
                        <div class="pai-summary">
                            <div class="pai-summary__label">Poblacion</div>
                            <div class="pai-summary__value" id="sumPop">{{ number_format((int) data_get($summary, 'poblacion', 0)) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-3">
            <div class="card pai-card h-100">
                <div class="card-header pai-card__header">
                    <strong id="formTitle">Nueva meta</strong>
                    <span class="text-muted small">Completa los campos y guarda.</span>
                </div>
                <div class="card-body">
                    <form id="paiMetaForm">
                        <input type="hidden" id="id" value="">

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="small text-muted mb-1">Vigencia</label>
                                <input type="number" class="form-control form-control-sm" id="vigencia" min="2000" max="2100" value="{{ $defaultYear }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small text-muted mb-1">Periodo inicio</label>
                                <input type="text" class="form-control form-control-sm" id="periodo_inicio" placeholder="YYYYMM" maxlength="6" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small text-muted mb-1">Periodo fin</label>
                                <input type="text" class="form-control form-control-sm" id="periodo_fin" placeholder="YYYYMM" maxlength="6" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Municipio</label>
                                <input list="municipiosList" class="form-control form-control-sm" id="municipio" placeholder="Escribe o elige..." required>
                                <datalist id="municipiosList">
                                    @foreach(($municipios ?? []) as $item)
                                        <option value="{{ $item }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Prestador</label>
                                <input list="prestadoresList" class="form-control form-control-sm" id="prestador" placeholder="Escribe o elige..." required>
                                <datalist id="prestadoresList">
                                    @foreach(($prestadores ?? []) as $item)
                                        <option value="{{ $item }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Codigo de habilitacion</label>
                                <input list="codigosList" class="form-control form-control-sm" id="codigo_habilitacion" placeholder="000000000000" required>
                                <datalist id="codigosList">
                                    @foreach(($codigos ?? []) as $item)
                                        <option value="{{ $item }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Regimen</label>
                                <input list="regimenesList" class="form-control form-control-sm" id="regimen" placeholder="SUBSIDIADO / CONTRIBUTIVO" required>
                                <datalist id="regimenesList">
                                    @foreach(($regimenes ?? []) as $item)
                                        <option value="{{ $item }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <label class="small text-muted mb-1">Cobertura</label>
                                <input list="coberturasList" class="form-control form-control-sm" id="cobertura" placeholder="Nombre de la cobertura" required>
                                <datalist id="coberturasList">
                                    @foreach(($coberturas ?? []) as $item)
                                        <option value="{{ $item }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group col-md-5">
                                <label class="small text-muted mb-1">Poblacion</label>
                                <input type="number" class="form-control form-control-sm" id="poblacion" min="0" value="0" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="small text-muted mb-1">Vacuna</label>
                                <select class="form-control form-control-sm" id="id_vacuna" required>
                                    <option value="">Seleccione...</option>
                                    @foreach(($biologicos ?? []) as $bio)
                                        <option value="{{ $bio['id'] }}">{{ $bio['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label class="small text-muted mb-1">Biologico</label>
                                <input list="biologicosList" class="form-control form-control-sm" id="biologico" placeholder="Nombre del biologico" required>
                                <datalist id="biologicosList">
                                    @foreach(($biologicos ?? []) as $bio)
                                        <option value="{{ $bio['nombre'] }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Biologico seleccionado</label>
                                <div class="form-control form-control-sm pai-readonly" id="bioPreview">Sin seleccionar</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Estado</label>
                                <select class="form-control form-control-sm" id="meta_state">
                                    <option value="active" selected>Activo</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="small text-muted" id="paiMetaMsg">Listo para editar.</div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClear">Limpiar</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSave">Guardar</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3 small text-muted pai-helpbox">
                        <strong>Tip:</strong> el mismo codigo de habilitacion puede tener varios usuarios `_ges` en el sistema, pero aqui se administra una sola meta consolidada por IPS.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-3">
            <div class="card pai-card h-100">
                <div class="card-header pai-card__header">
                    <div>
                        <strong>Registros de metas</strong>
                        <div class="text-muted small">Filtra por vigencia y ajusta rapidamente la tabla.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="number" id="filterYear" class="form-control form-control-sm pai-filter" min="2000" max="2100" value="{{ $defaultYear }}" placeholder="Vigencia">
                        <input list="municipiosList" id="filterMunicipio" class="form-control form-control-sm pai-filter" placeholder="Municipio">
                        <input list="prestadoresList" id="filterPrestador" class="form-control form-control-sm pai-filter" placeholder="Prestador">
                        <input list="codigosList" id="filterCodigo" class="form-control form-control-sm pai-filter" placeholder="Codigo">
                        <button class="btn btn-sm btn-outline-primary" id="btnApplyFilters">Aplicar</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive pai-table-wrap">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="pai-table-head">
                                <tr>
                                    <th>Vigencia</th>
                                    <th>Periodo</th>
                                    <th>Municipio</th>
                                    <th>Prestador</th>
                                    <th>Codigo</th>
                                    <th>Cobertura</th>
                                    <th>Biologico</th>
                                    <th>Dosis</th>
                                    <th>Regimen</th>
                                    <th class="text-right">Poblacion</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="rowsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.pai-shell{background:linear-gradient(180deg,#f6f9ff 0%,#fff 100%)}
.pai-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.pai-title{font-weight:900;color:#132238}
.pai-admin-nav{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.pai-admin-nav__item{display:flex;flex-direction:column;gap:4px;padding:14px 16px;border-radius:18px;border:1px solid rgba(15,23,42,.08);background:#fff;color:#0f172a;box-shadow:0 10px 24px rgba(15,23,42,.05);text-decoration:none}
.pai-admin-nav__item small{color:#64748b}
.pai-admin-nav__item.is-active{border-color:rgba(29,78,216,.26);background:linear-gradient(180deg,#eff6ff,#fff)}
.pai-admin-nav__eyebrow{font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#2563eb}
.pai-card{border:1px solid rgba(15,23,42,.08);border-radius:18px;box-shadow:0 14px 34px rgba(15,23,42,.06);overflow:hidden}
.pai-card__header{display:flex;justify-content:space-between;align-items:center;gap:12px;background:linear-gradient(90deg,#f8fbff 0%,#fff 100%)}
.pai-hero{border:0;border-radius:22px;box-shadow:0 18px 40px rgba(15,23,42,.06);background:radial-gradient(circle at top left,#eef6ff 0%,#ffffff 55%)}
.pai-hero__eyebrow{display:inline-block;padding:4px 10px;border-radius:999px;background:#e7f0ff;color:#1d4ed8;font-size:.76rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.pai-hero__title{font-size:1.55rem;font-weight:900;color:#0f172a}
.pai-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.pai-summary{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:12px}
.pai-summary__label{font-size:.72rem;text-transform:uppercase;color:#64748b;font-weight:800}
.pai-summary__value{font-size:1.2rem;font-weight:900;color:#0f172a}
.pai-filter{min-width:130px}
.pai-table-head th{font-size:.74rem;text-transform:uppercase;letter-spacing:.02em;background:#f8fbff;border-top:none;white-space:nowrap}
.pai-table-wrap{max-height:70vh}
.pai-readonly{background:#f8fafc;color:#334155}
.pai-helpbox{padding:12px 14px;border-radius:14px;background:#f8fbff;border:1px solid rgba(37,99,235,.12)}
.gap-2{gap:.5rem}
@media (max-width: 991.98px){
    .pai-admin-nav{grid-template-columns:1fr}
}
</style>
@stop

@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
(function(){
    const routes = {
        data: @json(route('afiliado.stats.indicadores.data')),
        store: @json(route('afiliado.stats.indicadores.store')),
        updateBase: @json(url('/afiliado/estadisticas/indicadores')),
        deleteBase: @json(url('/afiliado/estadisticas/indicadores'))
    };

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const initialRows = @json($defaultRows ?? []);
    const biologics = @json($biologicos ?? []);
    const body = document.getElementById('rowsBody');
    const msg = document.getElementById('paiMetaMsg');
    let currentRows = initialRows || [];

    function v(id){ return (document.getElementById(id)?.value || '').trim(); }
    function set(id, value){ const el = document.getElementById(id); if (el) el.value = value ?? ''; }
    function esc(value){
        return String(value ?? '').replace(/[&<>"']/g, function(ch){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[ch] || ch;
        });
    }

    function biologicNameById(id){
        const row = (biologics || []).find(b => String(b.id) === String(id));
        return row ? String(row.nombre || '') : '';
    }

    function syncBioPreview(){
        const text = biologicNameById(v('id_vacuna')) || v('biologico');
        document.getElementById('bioPreview').textContent = text || 'Sin seleccionar';
        if (v('id_vacuna') && !v('biologico')) {
            set('biologico', text);
        }
    }

    function rowHtml(row){
        return '<tr>' +
            '<td>' + esc(row.vigencia) + '</td>' +
            '<td><span class="badge badge-light">' + esc(row.periodo_inicio) + ' - ' + esc(row.periodo_fin) + '</span></td>' +
            '<td>' + esc(row.municipio) + '</td>' +
            '<td>' + esc(row.prestador) + '</td>' +
            '<td><code>' + esc(row.codigo_habilitacion) + '</code></td>' +
            '<td>' + esc(row.cobertura) + '</td>' +
            '<td>' + esc(row.biologico) + '</td>' +
            '<td>' + esc(row.dosis) + '</td>' +
            '<td>' + esc(row.regimen) + '</td>' +
            '<td class="text-right">' + Number(row.poblacion || 0).toLocaleString('es-CO') + '</td>' +
            '<td class="text-right">' +
                '<button class="btn btn-xs btn-outline-primary mr-1 btn-edit" data-id="' + esc(row.id) + '">Editar</button>' +
                '<button class="btn btn-xs btn-outline-danger btn-del" data-id="' + esc(row.id) + '">Eliminar</button>' +
            '</td>' +
        '</tr>';
    }

    function draw(rows){
        currentRows = rows || [];
        body.innerHTML = currentRows.map(rowHtml).join('');
        document.getElementById('sumRows').textContent = Number(currentRows.length || 0).toLocaleString('es-CO');
        document.getElementById('sumIps').textContent = Number(new Set(currentRows.map(r => String(r.codigo_habilitacion || ''))).size).toLocaleString('es-CO');
        document.getElementById('sumMuns').textContent = Number(new Set(currentRows.map(r => String(r.municipio || ''))).size).toLocaleString('es-CO');
        document.getElementById('sumPop').textContent = Number(currentRows.reduce((acc, row) => acc + Number(row.poblacion || 0), 0)).toLocaleString('es-CO');
    }

    function fillForm(row){
        set('id', row.id);
        set('vigencia', row.vigencia);
        set('periodo_inicio', row.periodo_inicio);
        set('periodo_fin', row.periodo_fin);
        set('municipio', row.municipio);
        set('prestador', row.prestador);
        set('codigo_habilitacion', row.codigo_habilitacion);
        set('cobertura', row.cobertura);
        set('id_vacuna', row.id_vacuna);
        set('biologico', row.biologico);
        set('dosis', row.dosis);
        set('regimen', row.regimen);
        set('poblacion', row.poblacion);
        set('meta_state', 'active');
        document.getElementById('formTitle').textContent = 'Editando meta #' + row.id;
        msg.textContent = 'Editando registro #' + row.id;
        syncBioPreview();
    }

    function clearForm(){
        ['id','periodo_inicio','periodo_fin','municipio','prestador','codigo_habilitacion','cobertura','id_vacuna','biologico','dosis','regimen','poblacion'].forEach(function(id){
            set(id, '');
        });
        set('vigencia', v('filterYear') || {{ (int) $defaultYear }});
        set('poblacion', '0');
        set('meta_state', 'active');
        document.getElementById('formTitle').textContent = 'Nueva meta';
        msg.textContent = 'Listo para crear una nueva meta.';
        syncBioPreview();
    }

    function payload(){
        return {
            vigencia: Number(v('vigencia') || {{ (int) $defaultYear }}),
            periodo_inicio: v('periodo_inicio'),
            periodo_fin: v('periodo_fin'),
            municipio: v('municipio'),
            prestador: v('prestador'),
            codigo_habilitacion: v('codigo_habilitacion'),
            cobertura: v('cobertura'),
            id_vacuna: Number(v('id_vacuna') || 0),
            biologico: v('biologico'),
            dosis: v('dosis'),
            regimen: v('regimen'),
            poblacion: Number(v('poblacion') || 0)
        };
    }

    function applyFilters(){
        const qs = new URLSearchParams();
        qs.set('year', v('filterYear') || {{ (int) $defaultYear }});
        if (v('filterMunicipio')) qs.set('municipio', v('filterMunicipio'));
        if (v('filterPrestador')) qs.set('prestador', v('filterPrestador'));
        if (v('filterCodigo')) qs.set('codigo_habilitacion', v('filterCodigo'));
        fetch(routes.data + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) throw new Error('No se pudo cargar la tabla.');
                draw(data.rows || []);
                msg.textContent = 'Mostrando ' + (data.summary?.rows || 0) + ' registros.';
            })
            .catch(err => {
                msg.textContent = err.message || 'Error cargando datos.';
            });
    }

    document.getElementById('id_vacuna').addEventListener('change', function(){
        set('biologico', biologicNameById(this.value));
        syncBioPreview();
    });

    document.getElementById('biologico').addEventListener('input', syncBioPreview);

    document.getElementById('paiMetaForm').addEventListener('submit', function(e){
        e.preventDefault();
        const id = v('id');
        const method = id ? 'PUT' : 'POST';
        const url = id ? (routes.updateBase + '/' + encodeURIComponent(id)) : routes.store;

        msg.textContent = 'Guardando...';
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload())
        })
        .then(async r => {
            const data = await r.json();
            if (!r.ok || !data.ok) throw new Error(data.message || 'No se pudo guardar.');
            msg.textContent = data.message || 'Guardado.';
            clearForm();
            applyFilters();
        })
        .catch(err => {
            msg.textContent = err.message || 'Error guardando.';
        });
    });

    body.addEventListener('click', function(e){
        const edit = e.target.closest('.btn-edit');
        const del = e.target.closest('.btn-del');

        if (edit) {
            const row = currentRows.find(function(x){ return String(x.id) === String(edit.dataset.id); });
            if (row) fillForm(row);
        }

        if (del) {
            const id = del.dataset.id;
            if (!confirm('Desea eliminar esta meta?')) return;
            fetch(routes.deleteBase + '/' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok || !data.ok) throw new Error(data.message || 'No se pudo eliminar.');
                msg.textContent = data.message || 'Eliminado.';
                if (v('id') === String(id)) clearForm();
                applyFilters();
            })
            .catch(err => {
                msg.textContent = err.message || 'Error eliminando.';
            });
        }
    });

    document.getElementById('btnClear').addEventListener('click', function(){
        clearForm();
    });

    document.getElementById('btnApplyFilters').addEventListener('click', function(){
        applyFilters();
    });

    document.getElementById('btnReload').addEventListener('click', function(){
        applyFilters();
    });

    set('periodo_inicio', '{{ data_get($defaultRows, '0.periodo_inicio', now()->format('Ym')) }}');
    set('periodo_fin', '{{ data_get($defaultRows, '0.periodo_fin', now()->format('Ym')) }}');
    syncBioPreview();
    draw(initialRows);
})();
</script>
@stop
