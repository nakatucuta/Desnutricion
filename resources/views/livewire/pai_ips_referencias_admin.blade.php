@extends('adminlte::page')

@section('title', 'PAI - IPS Objetivo')

@section('content_header')
<div class="pai-head">
    <div>
        <h1 class="pai-title mb-1">IPS Objetivo / Referenciadas</h1>
        <div class="text-muted">Controla que IPS primarias si cuentan para cada IPS vacunadora.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
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
                    <div class="pai-hero__eyebrow">Numerador real</div>
                    <h2 class="pai-hero__title mb-2">Define con claridad qué población le cuenta a cada IPS vacunadora</h2>
                    <p class="mb-0 text-muted">
                        Cada relación conecta una IPS vacunadora con una IPS primaria objetivo. Si una vacunadora solo se cuenta a sí misma,
                        crea una relación donde ambas IPS sean la misma.
                    </p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="pai-summary-grid">
                        <div class="pai-summary">
                            <div class="pai-summary__label">Relaciones</div>
                            <div class="pai-summary__value" id="sumRows">{{ number_format((int) data_get($summary, 'rows', 0)) }}</div>
                        </div>
                        <div class="pai-summary">
                            <div class="pai-summary__label">Vacunadoras</div>
                            <div class="pai-summary__value" id="sumVaccinators">{{ number_format((int) data_get($summary, 'vaccinators', 0)) }}</div>
                        </div>
                        <div class="pai-summary">
                            <div class="pai-summary__label">IPS objetivo</div>
                            <div class="pai-summary__value" id="sumTargets">{{ number_format((int) data_get($summary, 'target_ips', 0)) }}</div>
                        </div>
                        <div class="pai-summary">
                            <div class="pai-summary__label">Activas</div>
                            <div class="pai-summary__value" id="sumActive">{{ number_format((int) data_get($summary, 'active', 0)) }}</div>
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
                    <strong id="formTitle">Nueva relacion</strong>
                    <span class="text-muted small">La misma pantalla sirve para crear o editar.</span>
                </div>
                <div class="card-body">
                    <form id="paiRefForm">
                        <input type="hidden" id="id" value="">
                        <input type="hidden" id="ips_vacunadora_user_id" value="">
                        <input type="hidden" id="ips_vacunadora_nombre" value="">
                        <input type="hidden" id="ips_primaria_nombre" value="">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Vigencia</label>
                                <input type="number" class="form-control form-control-sm" id="vigencia" min="2000" max="2100" value="{{ $defaultYear }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small text-muted mb-1">Estado</label>
                                <select class="form-control form-control-sm" id="activo">
                                    <option value="1" selected>Activa</option>
                                    <option value="0">Inactiva</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="small text-muted mb-0">IPS vacunadora</label>
                                <button type="button" class="btn btn-link btn-sm p-0" id="btnMirrorIps">Usar la misma IPS como objetivo</button>
                            </div>
                            <select class="form-control form-control-sm" id="vaccinator_option" required>
                                <option value="">Selecciona la IPS que aplica</option>
                                @foreach(($ips_options ?? []) as $opt)
                                    <option value="{{ $opt['key'] }}"
                                            data-user-id="{{ $opt['user_id'] }}"
                                            data-code="{{ $opt['code'] }}"
                                            data-name="{{ $opt['name'] }}"
                                            data-municipio="{{ $opt['municipio'] }}">
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1">La vacunadora se toma del listado de IPS del sistema.</div>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted mb-1">Municipio de operacion</label>
                            <input list="municipiosList" class="form-control form-control-sm" id="municipio" placeholder="Se llena solo; puedes dejarlo vacio si aplica para cualquier municipio">
                            <datalist id="municipiosList">
                                @foreach(($municipios ?? []) as $item)
                                    <option value="{{ $item }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted mb-1">IPS primaria objetivo</label>
                            <select class="form-control form-control-sm" id="primary_option" required>
                                <option value="">Selecciona la IPS primaria que si cuenta</option>
                                @foreach(($ips_options ?? []) as $opt)
                                    <option value="{{ $opt['code'] }}"
                                            data-code="{{ $opt['code'] }}"
                                            data-name="{{ $opt['name'] }}"
                                            data-municipio="{{ $opt['municipio'] }}">
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1">Puedes crear varias relaciones para la misma vacunadora, una por cada IPS primaria valida.</div>
                        </div>

                        <div class="pai-preview mt-3">
                            <div class="pai-preview__row">
                                <span>Vacunadora</span>
                                <strong id="previewVaccinator">Sin seleccionar</strong>
                            </div>
                            <div class="pai-preview__row">
                                <span>IPS objetivo</span>
                                <strong id="previewPrimary">Sin seleccionar</strong>
                            </div>
                            <div class="pai-preview__row">
                                <span>Municipio</span>
                                <strong id="previewMunicipio">Sin definir</strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="small text-muted" id="paiRefMsg">Configura una relacion para guardar.</div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClear">Limpiar</button>
                                <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3 small text-muted pai-helpbox">
                        <strong>Ejemplo:</strong> si <code>IPS X</code> vacuna pacientes de <code>IPS A</code> y <code>IPS B</code>,
                        debes crear dos relaciones: <code>X -> A</code> y <code>X -> B</code>.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-3">
            <div class="card pai-card h-100">
                <div class="card-header pai-card__header">
                    <div>
                        <strong>Relaciones configuradas</strong>
                        <div class="text-muted small">Filtra por vigencia, municipio o codigos para revisar rapido.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="number" id="filterYear" class="form-control form-control-sm pai-filter" min="2000" max="2100" value="{{ $defaultYear }}" placeholder="Vigencia">
                        <input list="municipiosList" id="filterMunicipio" class="form-control form-control-sm pai-filter" placeholder="Municipio">
                        <input list="vaccinatorCodesList" id="filterVaccinatorCode" class="form-control form-control-sm pai-filter" placeholder="Codigo vacunadora">
                        <input list="primaryCodesList" id="filterPrimaryCode" class="form-control form-control-sm pai-filter" placeholder="Codigo IPS objetivo">
                        <button class="btn btn-sm btn-outline-primary" id="btnApplyFilters">Aplicar</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <datalist id="vaccinatorCodesList">
                        @foreach(($vaccinator_codes ?? []) as $item)
                            <option value="{{ $item }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="primaryCodesList">
                        @foreach(($primary_codes ?? []) as $item)
                            <option value="{{ $item }}"></option>
                        @endforeach
                    </datalist>
                    <div class="table-responsive pai-table-wrap">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="pai-table-head">
                                <tr>
                                    <th>Vigencia</th>
                                    <th>Municipio</th>
                                    <th>IPS vacunadora</th>
                                    <th>Codigo</th>
                                    <th>IPS objetivo</th>
                                    <th>Codigo</th>
                                    <th>Estado</th>
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
.pai-helpbox{padding:12px 14px;border-radius:14px;background:#f8fbff;border:1px solid rgba(37,99,235,.12)}
.pai-preview{display:grid;gap:8px}
.pai-preview__row{padding:10px 12px;border-radius:14px;background:#f8fbff;border:1px solid rgba(148,163,184,.18)}
.pai-preview__row span{display:block;font-size:.74rem;text-transform:uppercase;color:#64748b;font-weight:800}
.pai-preview__row strong{display:block;color:#0f172a}
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
        data: @json(route('afiliado.stats.references.data')),
        store: @json(route('afiliado.stats.references.store')),
        updateBase: @json(url('/afiliado/estadisticas/referencias-ips')),
        deleteBase: @json(url('/afiliado/estadisticas/referencias-ips'))
    };

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const initialRows = @json($defaultRows ?? []);
    const currentOptions = @json($ips_options ?? []);
    const body = document.getElementById('rowsBody');
    const msg = document.getElementById('paiRefMsg');
    let currentRows = initialRows || [];

    function v(id){ return (document.getElementById(id)?.value || '').trim(); }
    function set(id, value){ const el = document.getElementById(id); if (el) el.value = value ?? ''; }
    function esc(value){
        return String(value ?? '').replace(/[&<>"']/g, function(ch){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[ch] || ch;
        });
    }

    function selectedOptionData(id){
        const el = document.getElementById(id);
        if (!el) return null;
        const opt = el.options[el.selectedIndex];
        if (!opt) return null;
        return {
            value: opt.value || '',
            userId: opt.dataset.userId || '',
            code: opt.dataset.code || '',
            name: opt.dataset.name || '',
            municipio: opt.dataset.municipio || ''
        };
    }

    function updatePreview(){
        const vaccinator = selectedOptionData('vaccinator_option');
        const primary = selectedOptionData('primary_option');
        document.getElementById('previewVaccinator').textContent = vaccinator?.name ? (vaccinator.name + ' | ' + vaccinator.code) : 'Sin seleccionar';
        document.getElementById('previewPrimary').textContent = primary?.name ? (primary.name + ' | ' + primary.code) : 'Sin seleccionar';
        document.getElementById('previewMunicipio').textContent = v('municipio') || 'Sin definir';
    }

    function syncVaccinatorSelection(){
        const data = selectedOptionData('vaccinator_option');
        set('ips_vacunadora_user_id', data?.userId || '');
        set('ips_vacunadora_nombre', data?.name || '');
        if (!v('municipio')) {
            set('municipio', data?.municipio || '');
        }
        updatePreview();
    }

    function syncPrimarySelection(){
        const data = selectedOptionData('primary_option');
        set('ips_primaria_nombre', data?.name || '');
        updatePreview();
    }

    function rowHtml(row){
        const state = row.activo ? '<span class="badge badge-success">Activa</span>' : '<span class="badge badge-secondary">Inactiva</span>';
        return '<tr>' +
            '<td>' + esc(row.vigencia) + '</td>' +
            '<td>' + esc(row.municipio || 'Todos') + '</td>' +
            '<td>' + esc(row.ips_vacunadora_nombre || '') + '</td>' +
            '<td><code>' + esc(row.ips_vacunadora_codigo || '') + '</code></td>' +
            '<td>' + esc(row.ips_primaria_nombre || '') + '</td>' +
            '<td><code>' + esc(row.ips_primaria_codigo || '') + '</code></td>' +
            '<td>' + state + '</td>' +
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
        document.getElementById('sumVaccinators').textContent = Number(new Set(currentRows.map(r => String(r.ips_vacunadora_codigo || ''))).size).toLocaleString('es-CO');
        document.getElementById('sumTargets').textContent = Number(new Set(currentRows.map(r => String(r.ips_primaria_codigo || ''))).size).toLocaleString('es-CO');
        document.getElementById('sumActive').textContent = Number(currentRows.filter(r => !!r.activo).length).toLocaleString('es-CO');
    }

    function findVaccinatorOption(row){
        return currentOptions.find(function(opt){
            return String(opt.code || '') === String(row.ips_vacunadora_codigo || '') &&
                String(opt.municipio || '') === String(row.municipio || '');
        }) || currentOptions.find(function(opt){
            return String(opt.code || '') === String(row.ips_vacunadora_codigo || '');
        }) || null;
    }

    function findPrimaryOption(row){
        return currentOptions.find(function(opt){
            return String(opt.code || '') === String(row.ips_primaria_codigo || '');
        }) || null;
    }

    function fillForm(row){
        set('id', row.id);
        set('vigencia', row.vigencia);
        set('municipio', row.municipio);
        set('activo', row.activo ? '1' : '0');
        set('ips_vacunadora_user_id', row.ips_vacunadora_user_id || '');
        set('ips_vacunadora_nombre', row.ips_vacunadora_nombre || '');
        set('ips_primaria_nombre', row.ips_primaria_nombre || '');

        const vaccinator = findVaccinatorOption(row);
        const primary = findPrimaryOption(row);
        set('vaccinator_option', vaccinator?.key || '');
        set('primary_option', primary?.code || row.ips_primaria_codigo || '');
        document.getElementById('formTitle').textContent = 'Editando relacion #' + row.id;
        msg.textContent = 'Editando relacion #' + row.id;
        updatePreview();
    }

    function clearForm(){
        ['id','ips_vacunadora_user_id','ips_vacunadora_nombre','ips_primaria_nombre','municipio'].forEach(function(id){
            set(id, '');
        });
        set('vigencia', v('filterYear') || {{ (int) $defaultYear }});
        set('vaccinator_option', '');
        set('primary_option', '');
        set('activo', '1');
        document.getElementById('formTitle').textContent = 'Nueva relacion';
        msg.textContent = 'Configura una relacion para guardar.';
        updatePreview();
    }

    function payload(){
        const vaccinator = selectedOptionData('vaccinator_option');
        const primary = selectedOptionData('primary_option');

        return {
            vigencia: Number(v('vigencia') || {{ (int) $defaultYear }}),
            municipio: v('municipio'),
            ips_vacunadora_user_id: vaccinator?.userId ? Number(vaccinator.userId) : null,
            ips_vacunadora_codigo: vaccinator?.code || '',
            ips_vacunadora_nombre: vaccinator?.name || '',
            ips_primaria_codigo: primary?.code || '',
            ips_primaria_nombre: primary?.name || '',
            activo: v('activo') === '1'
        };
    }

    function applyFilters(){
        const qs = new URLSearchParams();
        qs.set('year', v('filterYear') || {{ (int) $defaultYear }});
        if (v('filterMunicipio')) qs.set('municipio', v('filterMunicipio'));
        if (v('filterVaccinatorCode')) qs.set('ips_vacunadora_codigo', v('filterVaccinatorCode'));
        if (v('filterPrimaryCode')) qs.set('ips_primaria_codigo', v('filterPrimaryCode'));

        fetch(routes.data + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) throw new Error('No se pudo cargar la tabla.');
                draw(data.rows || []);
                msg.textContent = 'Mostrando ' + (data.summary?.rows || 0) + ' relaciones.';
            })
            .catch(err => {
                msg.textContent = err.message || 'Error cargando datos.';
            });
    }

    document.getElementById('vaccinator_option').addEventListener('change', syncVaccinatorSelection);
    document.getElementById('primary_option').addEventListener('change', syncPrimarySelection);
    document.getElementById('municipio').addEventListener('input', updatePreview);

    document.getElementById('btnMirrorIps').addEventListener('click', function(){
        const vaccinator = selectedOptionData('vaccinator_option');
        if (!vaccinator?.code) return;
        set('primary_option', vaccinator.code);
        set('ips_primaria_nombre', vaccinator.name || '');
        updatePreview();
    });

    document.getElementById('paiRefForm').addEventListener('submit', function(e){
        e.preventDefault();
        const id = v('id');
        const method = id ? 'PUT' : 'POST';
        const url = id ? (routes.updateBase + '/' + encodeURIComponent(id)) : routes.store;

        msg.textContent = 'Guardando...';
        fetch(url, {
            method,
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
            if (!confirm('Desea eliminar esta relacion?')) return;
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

    document.getElementById('btnClear').addEventListener('click', clearForm);
    document.getElementById('btnApplyFilters').addEventListener('click', applyFilters);
    document.getElementById('btnReload').addEventListener('click', applyFilters);

    clearForm();
    draw(initialRows);
})();
</script>
@stop
