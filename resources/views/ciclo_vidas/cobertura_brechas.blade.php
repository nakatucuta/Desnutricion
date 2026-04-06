@extends('adminlte::page')

@section('title', 'Cobertura y brechas')

@section('content_header')
    <div class="cv-coverage-hero">
        <div>
            <span class="cv-coverage-chip">Cobertura y brechas</span>
            <h1 class="mb-2">Atenciones realizadas y atenciones faltantes por curso de vida</h1>
            <p class="mb-0">Consolida la carga real del periodo, la poblacion objetivo y la brecha normativa con filtros territoriales, operativos e institucionales.</p>
        </div>
        <div class="cv-coverage-brand">
            <img src="{{ $companyLogo }}" alt="Escudo institucional">
            <div><small>Analitica institucional</small><strong>INOVA · Cursos de vida</strong></div>
        </div>
    </div>
@stop

@section('content')
    <div id="cvCoverageLoading" class="cv-coverage-loading" hidden>
        <div class="cv-coverage-loading__panel">
            <img src="{{ $companyLogo }}" alt="Escudo institucional">
            <h3>Procesando cobertura y brechas</h3>
            <p>Estamos consolidando atenciones realizadas, oportunidades y faltantes por curso de vida.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 cv-coverage-card">
        <div class="card-body">
            @include('ciclo_vidas.partials.date_range_toolbar', [
                'pickerId' => 'coverageRange',
                'applyButtonId' => 'btnRefreshCoverage',
                'applyLabel' => 'Actualizar analisis',
                'applyIcon' => 'fas fa-wave-square',
                'noteClass' => 'cv-coverage-note',
                'note' => '<span><i class="fas fa-circle-info"></i> Las atenciones faltantes se calculan como atenciones esperadas menos atenciones validas en modulos con regla parametrizada. Los modulos por criterio clinico se muestran como no estandarizables.</span>',
            ])

            <div class="cv-coverage-filter-grid mt-4">
                <div><label class="mb-1 font-weight-bold">Curso de vida</label><select id="coverageCourse" class="form-control"><option value="">Todos los cursos</option>@foreach ($filters['courses'] as $option)<option value="{{ $option['value'] }}">{{ $option['label'] }}</option>@endforeach</select></div>
                <div><label class="mb-1 font-weight-bold">Atencion</label><select id="coverageModule" class="form-control"><option value="">Todas las atenciones</option>@foreach ($filters['modules'] as $option)<option value="{{ $option['value'] }}">{{ $option['label'] }}{{ !empty($option['measurable']) ? ' · exacta' : '' }}</option>@endforeach</select></div>
                <div><label class="mb-1 font-weight-bold">Departamento</label><select id="coverageDepartment" class="form-control"><option value="">Todos</option></select></div>
                <div><label class="mb-1 font-weight-bold">Municipio</label><select id="coverageMunicipality" class="form-control"><option value="">Todos</option></select></div>
                <div><label class="mb-1 font-weight-bold">IPS primaria</label><select id="coverageIps" class="form-control"><option value="">Todas</option></select></div>
                <div><label class="mb-1 font-weight-bold">Genero</label><select id="coverageGender" class="form-control"><option value="">Todos</option></select></div>
                <div><label class="mb-1 font-weight-bold">Zona</label><select id="coverageZone" class="form-control"><option value="">Todas</option></select></div>
                <div><label class="mb-1 font-weight-bold">Estado afiliado</label><select id="coverageState" class="form-control"><option value="">Todos</option></select></div>
            </div>

            <div class="cv-coverage-switches mt-3">
                <label class="cv-coverage-switch"><input type="checkbox" id="coverageIncludeNonMeasurable" checked><span>Incluir modulos no estandarizables en faltantes</span></label>
                <label class="cv-coverage-switch"><input type="checkbox" id="coverageHideEmpty"><span>Ocultar filas sin movimiento</span></label>
                <button type="button" id="btnResetCoverage" class="btn btn-link btn-sm p-0">Restablecer filtros</button>
            </div>

            <div class="cv-coverage-filter-summary mt-4">
                <div>
                    <small>Filtros activos</small>
                    <div id="coverageFilterPills" class="cv-coverage-filter-pills"><span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">Sin filtros adicionales</span></div>
                </div>
                <div class="cv-coverage-filter-summary__actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-toggle-details="#realizedDetails">Expandir detalle realizado</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-toggle-details="#missingDetails">Expandir detalle faltante</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="cv-coverage-stat"><small>Atenciones realizadas</small><strong id="summaryRealized">0</strong><span id="summaryRealizedPatients">0 pacientes impactados</span><em id="summaryRealizedFoot">0 IPS · 0 municipios</em></div></div>
        <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="cv-coverage-stat"><small>Atenciones esperadas</small><strong id="summaryExpected">0</strong><span id="summaryTargetPatients">0 pacientes con oportunidad</span><em id="summaryExpectedFoot">Brecha normativa total del corte</em></div></div>
        <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="cv-coverage-stat"><small>Atenciones faltantes</small><strong id="summaryMissing">0</strong><span id="summaryPatientsWithMissing">0 pacientes con brecha</span><em id="summaryMissingFoot">0 atenciones aplicadas dentro de norma</em></div></div>
        <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="cv-coverage-stat"><small>Cobertura normativa</small><strong id="summaryCoverage">0%</strong><span id="summaryMeasurable">0 modulos exactos</span><em id="summaryCoverageFoot">Lectura basada en reglas parametrizadas</em></div></div>
    </div>

    <div class="alert alert-light border shadow-sm mb-4 cv-coverage-meta" id="coverageMeta"></div>

    <details class="card shadow-sm border-0 mb-4 cv-coverage-card cv-coverage-section" open>
        <summary class="cv-coverage-section__summary">
            <div><span class="cv-coverage-section__eyebrow">Tabla 1</span><h3 class="card-title mb-1">Atenciones realizadas</h3><small class="text-muted">Sumatoria completa de la atencion materializada en el rango seleccionado.</small></div>
            <span class="cv-coverage-section__toggle">Mostrar / ocultar</span>
        </summary>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle cv-coverage-table">
                    <thead><tr><th>Curso de vida</th><th>Atenciones</th><th>Pacientes</th><th>Prom./paciente</th><th>IPS</th><th>Municipios</th><th>Modulos activos</th></tr></thead>
                    <tbody id="realizedCourseRows"></tbody>
                </table>
            </div>
            <div id="realizedDetails" class="cv-coverage-details mt-3"></div>
        </div>
    </details>

    <details class="card shadow-sm border-0 cv-coverage-card cv-coverage-section" open>
        <summary class="cv-coverage-section__summary">
            <div><span class="cv-coverage-section__eyebrow">Tabla 2</span><h3 class="card-title mb-1">Atenciones faltantes</h3><small class="text-muted">Atenciones esperadas menos atenciones validadas dentro de la ventana de corte.</small></div>
            <span class="cv-coverage-section__toggle">Mostrar / ocultar</span>
        </summary>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle cv-coverage-table">
                    <thead><tr><th>Curso de vida</th><th>Poblacion objetivo</th><th>Esperadas</th><th>Aplicadas</th><th>Faltantes</th><th>Pacientes con brecha</th><th>Cobertura</th></tr></thead>
                    <tbody id="missingCourseRows"></tbody>
                </table>
            </div>
            <div id="missingDetails" class="cv-coverage-details mt-3"></div>
        </div>
    </details>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <style>
        .cv-coverage-hero,.cv-coverage-stat,.cv-coverage-card,.cv-coverage-detail-card{position:relative;overflow:hidden}.cv-coverage-hero{display:flex;justify-content:space-between;gap:1.5rem;align-items:center;padding:1.9rem 2rem;border-radius:30px;background:radial-gradient(circle at top right,rgba(34,211,238,.34),transparent 24%),linear-gradient(135deg,#071423 0%,#0e3152 45%,#0a6c76 100%);color:#f8fbff;box-shadow:0 24px 70px rgba(2,6,23,.22)}.cv-coverage-chip{display:inline-flex;padding:.45rem .85rem;border-radius:999px;background:rgba(255,255,255,.12);font-size:.74rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:1rem}.cv-coverage-brand{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;border-radius:24px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.14)}.cv-coverage-brand img,.cv-coverage-loading__panel img{width:78px;height:78px;object-fit:contain}.cv-coverage-card{border-radius:26px;background:linear-gradient(180deg,#fff 0%,#fbfdff 100%)}.cv-coverage-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem}.cv-coverage-switches{display:flex;gap:1.2rem;flex-wrap:wrap;align-items:center}.cv-coverage-switch{display:flex;gap:.55rem;align-items:center;margin:0;font-weight:600;color:#425466}.cv-coverage-filter-summary{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap;padding:1rem 1.15rem;border-radius:20px;background:#f7fbff;border:1px solid #dbe7f4}.cv-coverage-filter-summary small{display:block;color:#6b7b90;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;font-weight:800;margin-bottom:.55rem}.cv-coverage-filter-summary__actions{display:flex;gap:.6rem;flex-wrap:wrap}.cv-coverage-filter-pills{display:flex;gap:.55rem;flex-wrap:wrap}.cv-coverage-filter-pill{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .75rem;border-radius:999px;background:#e8f2ff;color:#0f172a;font-weight:700;font-size:.82rem}.cv-coverage-filter-pill strong{color:#335274}.cv-coverage-filter-pill--empty{background:#eef2f7;color:#64748b}.cv-coverage-stat{height:100%;padding:1.35rem 1.4rem;border-radius:24px;background:linear-gradient(180deg,#fff 0%,#f5f9ff 100%);box-shadow:0 18px 44px rgba(15,23,42,.08)}.cv-coverage-stat small{display:block;color:#64748b;text-transform:uppercase;letter-spacing:.08em;font-weight:800}.cv-coverage-stat strong{display:block;font-size:2rem;line-height:1.1;margin:.45rem 0;color:#0f172a}.cv-coverage-stat span,.cv-coverage-stat em{display:block;color:#5b6b7f;font-style:normal}.cv-coverage-stat em{margin-top:.28rem;font-size:.83rem}.cv-coverage-meta{border-radius:20px}.cv-coverage-meta__notes{margin-top:.45rem;color:#516074}.cv-coverage-section summary{list-style:none;cursor:pointer}.cv-coverage-section summary::-webkit-details-marker{display:none}.cv-coverage-section__summary{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.35rem 1.45rem 0}.cv-coverage-section__eyebrow{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:800;margin-bottom:.3rem}.cv-coverage-section__toggle{display:inline-flex;padding:.45rem .8rem;border-radius:999px;background:#edf5ff;color:#27415f;font-weight:800;font-size:.78rem}.cv-coverage-table thead th{border-top:0;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:#5b6b7f;white-space:nowrap}.cv-coverage-course-tag{display:inline-flex;align-items:center;gap:.6rem;font-weight:800}.cv-coverage-course-tag i{width:2rem;height:2rem;border-radius:12px;display:grid;place-items:center;background:#e0f2fe;color:#0f172a}.cv-coverage-details{display:grid;gap:1rem}.cv-coverage-detail-card{padding:1rem 1rem 1.15rem;border-radius:22px;background:#f8fbff;border:1px solid #dce8f7}.cv-coverage-detail-card summary{display:flex;justify-content:space-between;gap:1rem;align-items:center;cursor:pointer;list-style:none}.cv-coverage-detail-card summary::-webkit-details-marker{display:none}.cv-coverage-detail-card__stats{display:flex;gap:1rem;flex-wrap:wrap;color:#516074;font-weight:700}.cv-coverage-detail-card__stats span{padding:.35rem .65rem;border-radius:999px;background:#e8f1fb}.cv-coverage-badge{display:inline-flex;align-items:center;padding:.3rem .65rem;border-radius:999px;font-size:.74rem;font-weight:800}.cv-coverage-badge--ok{background:#dcfce7;color:#166534}.cv-coverage-badge--muted{background:#e2e8f0;color:#475569}.cv-coverage-loading{position:fixed;inset:0;background:rgba(7,18,32,.38);z-index:1055;display:grid;place-items:center}.cv-coverage-loading__panel{width:min(460px,calc(100vw - 2rem));padding:2rem;border-radius:28px;background:#fff;text-align:center;box-shadow:0 30px 80px rgba(15,23,42,.24)}@media (max-width:991.98px){.cv-coverage-hero{flex-direction:column;align-items:flex-start}.cv-coverage-brand{width:100%}.cv-coverage-section__summary{flex-direction:column;align-items:flex-start}}
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @include('ciclo_vidas.partials.date_range_shared_script')
    <script>
        (() => {
            const dataUrl = @json($dataUrl), advancedFiltersUrl = @json($advancedFiltersUrl), moduleCatalog = @json($filters['moduleCatalog'] ?? []), nf = new Intl.NumberFormat('es-CO');
            const q = id => document.getElementById(id);
            const refs = { loading:q('cvCoverageLoading'), course:q('coverageCourse'), module:q('coverageModule'), department:q('coverageDepartment'), municipality:q('coverageMunicipality'), ips:q('coverageIps'), gender:q('coverageGender'), zone:q('coverageZone'), state:q('coverageState'), includeNonMeasurable:q('coverageIncludeNonMeasurable'), hideEmpty:q('coverageHideEmpty'), refresh:q('btnRefreshCoverage'), reset:q('btnResetCoverage'), filterPills:q('coverageFilterPills'), summaryRealized:q('summaryRealized'), summaryRealizedPatients:q('summaryRealizedPatients'), summaryRealizedFoot:q('summaryRealizedFoot'), summaryExpected:q('summaryExpected'), summaryTargetPatients:q('summaryTargetPatients'), summaryExpectedFoot:q('summaryExpectedFoot'), summaryMissing:q('summaryMissing'), summaryPatientsWithMissing:q('summaryPatientsWithMissing'), summaryMissingFoot:q('summaryMissingFoot'), summaryCoverage:q('summaryCoverage'), summaryMeasurable:q('summaryMeasurable'), summaryCoverageFoot:q('summaryCoverageFoot'), meta:q('coverageMeta'), realizedRows:q('realizedCourseRows'), missingRows:q('missingCourseRows'), realizedDetails:q('realizedDetails'), missingDetails:q('missingDetails') };
            const state = { advanced:null };

            function setSummaryErrorState() {
                refs.summaryRealized.textContent = '--';
                refs.summaryRealizedPatients.textContent = 'No disponible';
                refs.summaryRealizedFoot.textContent = 'Verifica la carga del panel';
                refs.summaryExpected.textContent = '--';
                refs.summaryTargetPatients.textContent = 'No disponible';
                refs.summaryExpectedFoot.textContent = 'Verifica la carga del panel';
                refs.summaryMissing.textContent = '--';
                refs.summaryPatientsWithMissing.textContent = 'No disponible';
                refs.summaryMissingFoot.textContent = 'Verifica la carga del panel';
                refs.summaryCoverage.textContent = '--';
                refs.summaryMeasurable.textContent = 'No disponible';
                refs.summaryCoverageFoot.textContent = 'Verifica la carga del panel';
            }

            function showClientError(message) {
                refs.filterPills.innerHTML = '<span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">No se pudo inicializar el analisis</span>';
                refs.meta.innerHTML = `<strong>Error de interfaz:</strong> ${message}`;
                setSummaryErrorState();
                refs.realizedRows.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">La pantalla no pudo inicializar el panel de analisis.</td></tr>';
                refs.missingRows.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">La pantalla no pudo inicializar el calculo de brechas.</td></tr>';
                refs.realizedDetails.innerHTML = '';
                refs.missingDetails.innerHTML = '';
                refs.loading.hidden = true;
            }

            if (typeof window.jQuery === 'undefined') {
                showClientError('No se encontro jQuery en la pagina.');
                return;
            }

            if (typeof window.moment === 'undefined') {
                showClientError('No se encontro la libreria de fechas requerida (moment.js).');
                return;
            }

            if (typeof window.jQuery.fn.daterangepicker !== 'function') {
                showClientError('No se encontro el selector de rango de fechas.');
                return;
            }

            const rangePicker = window.CicloVidaDateRange.init({ pickerSelector:'#coverageRange', start:@json($desde), end:@json($hasta) });
            const n = v => nf.format(Number(v || 0)), p = v => v === null || v === undefined ? '--' : `${Number(v).toFixed(1)}%`;
            const options = items => (items || []).map(i => `<option value="${i.value}">${i.label}</option>`).join('');

            function populateModules(course = '', selected = '') {
                const list = moduleCatalog[course || 'all'] || moduleCatalog.all || [];
                refs.module.innerHTML = '<option value="">Todas las atenciones</option>' + list.map(i => `<option value="${i.value}" ${i.value === selected ? 'selected' : ''}>${i.label}${i.measurable ? ' · exacta' : ''}</option>`).join('');
            }
            function populateMunicipalities(department = '', selected = '') {
                const map = state.advanced?.municipality_map || {}, list = department && map[department] ? map[department] : (state.advanced?.municipalities || []);
                refs.municipality.innerHTML = '<option value="">Todos</option>' + options(list);
                refs.municipality.value = list.some(i => i.value === selected) ? selected : '';
            }
            async function loadAdvancedFilters() {
                const data = await (await fetch(advancedFiltersUrl, { headers:{ 'X-Requested-With':'XMLHttpRequest' } })).json();
                state.advanced = data;
                refs.department.innerHTML = '<option value="">Todos</option>' + options(data.departments);
                refs.ips.innerHTML = '<option value="">Todas</option>' + options(data.ips);
                refs.gender.innerHTML = '<option value="">Todos</option>' + options(data.genders);
                refs.zone.innerHTML = '<option value="">Todas</option>' + options(data.zones);
                refs.state.innerHTML = '<option value="">Todos</option>' + options(data.states);
                populateMunicipalities();
            }
            const filters = () => ({ desde:rangePicker.getStart().format('YYYY-MM-DD'), hasta:rangePicker.getEndInclusive().format('YYYY-MM-DD'), course_key:refs.course.value, module_key:refs.module.value, departamento:refs.department.value, municipio:refs.municipality.value, ips:refs.ips.value, genero:refs.gender.value, zona:refs.zone.value, estado_actual:refs.state.value, include_non_measurable:refs.includeNonMeasurable.checked ? '1' : '0', hide_empty:refs.hideEmpty.checked ? '1' : '0' });
            const realizedModules = c => c.modules.filter(m => !refs.hideEmpty.checked || Number(m.total_attentions || 0) > 0);
            const missingModules = c => c.modules.filter(m => { if (!refs.includeNonMeasurable.checked && !m.measurable) return false; if (!refs.hideEmpty.checked) return true; return m.measurable ? Number(m.expected_attentions || 0) > 0 || Number(m.missing_attentions || 0) > 0 || Number(m.recorded_attentions || 0) > 0 : Number(m.recorded_attentions || 0) > 0; });

            function renderPills(currentFilters, data) {
                const pills = [{ label:'Rango', value:`${currentFilters.desde} a ${currentFilters.hasta}` }, ...(data.meta?.filters || [])];
                refs.filterPills.innerHTML = pills.length ? pills.map(i => `<span class="cv-coverage-filter-pill"><strong>${i.label}:</strong> ${i.value}</span>`).join('') : '<span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">Sin filtros adicionales</span>';
            }
            function renderSummary(data) {
                refs.summaryRealized.textContent = n(data.summary.total_realized);
                refs.summaryRealizedPatients.textContent = `${n(data.summary.total_realized_patients)} pacientes impactados`;
                refs.summaryRealizedFoot.textContent = `${n(data.summary.total_realized_ips)} IPS · ${n(data.summary.total_realized_municipalities)} municipios`;
                refs.summaryExpected.textContent = n(data.summary.total_expected);
                refs.summaryTargetPatients.textContent = `${n(data.summary.target_patients)} pacientes con oportunidad`;
                refs.summaryExpectedFoot.textContent = 'Suma de atenciones que debian cumplirse en el rango';
                refs.summaryMissing.textContent = n(data.summary.total_missing);
                refs.summaryPatientsWithMissing.textContent = `${n(data.summary.patients_with_missing)} pacientes con brecha`;
                refs.summaryMissingFoot.textContent = `${n(data.summary.total_valid)} atenciones aplicadas dentro de norma`;
                refs.summaryCoverage.textContent = p(data.summary.coverage);
                refs.summaryMeasurable.textContent = `${n(data.summary.measurable_modules)} modulos exactos · ${n(data.summary.non_measurable_modules)} no estandarizables`;
                refs.summaryCoverageFoot.textContent = 'Lectura basada en reglas parametrizadas y fecha de nacimiento';
                refs.meta.innerHTML = `<strong>Corte:</strong> ${data.meta.from} a ${data.meta.to}<br><strong>Generado:</strong> ${data.meta.generated_at}<div class="cv-coverage-meta__notes">${(data.meta.notes || []).map(note => `<div>• ${note}</div>`).join('')}</div>`;
            }
            function renderRealized(data) {
                const courses = data.realized.courses.filter(c => !refs.hideEmpty.checked || Number(c.total_attentions || 0) > 0);
                refs.realizedRows.innerHTML = courses.map(c => `<tr><td><span class="cv-coverage-course-tag"><i class="${c.icon}"></i>${c.label}</span></td><td>${n(c.total_attentions)}</td><td>${n(c.unique_patients)}</td><td>${c.avg_per_patient === null ? '--' : Number(c.avg_per_patient).toFixed(2)}</td><td>${n(c.unique_ips)}</td><td>${n(c.unique_municipalities)}</td><td>${n(c.modules_with_activity)} / ${n(c.total_modules)}</td></tr>`).join('') || '<tr><td colspan="7" class="text-center text-muted py-4">No hay atenciones realizadas para los filtros seleccionados.</td></tr>';
                refs.realizedDetails.innerHTML = courses.map((c, i) => { const modules = realizedModules(c); return `<details class="cv-coverage-detail-card" ${i === 0 ? 'open' : ''}><summary><div><strong>${c.label}</strong><div class="text-muted small">${c.age_label || 'Curso de vida'}</div></div><div class="cv-coverage-detail-card__stats"><span>${n(c.total_attentions)} atenciones</span><span>${n(c.unique_patients)} pacientes</span><span>${n(c.modules_with_activity)} modulos con movimiento</span></div></summary><div class="table-responsive mt-3"><table class="table table-sm table-striped"><thead><tr><th>Atencion</th><th>Registradas</th><th>Pacientes</th><th>Prom./paciente</th><th>IPS</th><th>Municipios</th></tr></thead><tbody>${modules.map(m => `<tr><td><strong>${m.short_label}</strong><div class="small text-muted">${m.description || ''}</div></td><td>${n(m.total_attentions)}</td><td>${n(m.unique_patients)}</td><td>${m.avg_per_patient === null ? '--' : Number(m.avg_per_patient).toFixed(2)}</td><td>${n(m.unique_ips)}</td><td>${n(m.unique_municipalities)}</td></tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted py-3">No hay detalle visible para este curso.</td></tr>'}</tbody></table></div></details>`; }).join('');
            }
            function renderMissing(data) {
                const courses = data.missing.courses.filter(c => { if (!refs.includeNonMeasurable.checked && Number(c.measurable_modules || 0) === 0) return false; if (!refs.hideEmpty.checked) return true; return Number(c.expected_attentions || 0) > 0 || Number(c.missing_attentions || 0) > 0; });
                refs.missingRows.innerHTML = courses.map(c => `<tr><td><span class="cv-coverage-course-tag"><i class="${c.icon}"></i>${c.label}</span></td><td>${n(c.target_patients)}</td><td>${n(c.expected_attentions)}</td><td>${n(c.valid_attentions)}</td><td>${n(c.missing_attentions)}</td><td>${n(c.patients_with_missing)}</td><td>${p(c.coverage)}</td></tr>`).join('') || '<tr><td colspan="7" class="text-center text-muted py-4">No hay brechas calculables para los filtros seleccionados.</td></tr>';
                refs.missingDetails.innerHTML = courses.map((c, i) => { const modules = missingModules(c); return `<details class="cv-coverage-detail-card" ${i === 0 ? 'open' : ''}><summary><div><strong>${c.label}</strong><div class="text-muted small">${c.measurable_modules} modulos exactos · ${c.modules_with_gap} modulos con brecha</div></div><div class="cv-coverage-detail-card__stats"><span>${n(c.target_patients)} pacientes objetivo</span><span>${n(c.missing_attentions)} faltantes</span><span>${p(c.coverage)}</span></div></summary><div class="table-responsive mt-3"><table class="table table-sm table-striped"><thead><tr><th>Atencion</th><th>Estado</th><th>Regla</th><th>Poblacion objetivo</th><th>Pacientes con brecha</th><th>Registradas</th><th>Esperadas</th><th>Aplicadas</th><th>Faltantes</th><th>Exceso</th><th>Cobertura</th></tr></thead><tbody>${modules.map(m => `<tr><td><strong>${m.short_label}</strong><div class="small text-muted">${m.description || ''}</div></td><td><span class="cv-coverage-badge ${m.measurable ? 'cv-coverage-badge--ok' : 'cv-coverage-badge--muted'}">${m.status}</span></td><td class="small">${m.method}</td><td>${m.target_patients === null ? '--' : n(m.target_patients)}</td><td>${m.patients_with_missing === null ? '--' : n(m.patients_with_missing)}</td><td>${n(m.recorded_attentions)}</td><td>${m.expected_attentions === null ? '--' : n(m.expected_attentions)}</td><td>${m.valid_attentions === null ? '--' : n(m.valid_attentions)}</td><td>${m.missing_attentions === null ? '--' : n(m.missing_attentions)}</td><td>${m.excess_attentions === null ? '--' : n(m.excess_attentions)}</td><td>${p(m.coverage)}</td></tr>`).join('') || '<tr><td colspan="11" class="text-center text-muted py-3">No hay detalle visible para este curso.</td></tr>'}</tbody></table></div></details>`; }).join('');
            }
            async function fetchCoverage() {
                refs.loading.hidden = false;
                try {
                    const currentFilters = filters();
                    const response = await fetch(`${dataUrl}?${new URLSearchParams(currentFilters).toString()}`, { headers:{ 'X-Requested-With':'XMLHttpRequest' } });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || data.error || 'No fue posible calcular cobertura y brechas con la configuracion actual.');
                    }
                    renderPills(currentFilters, data); renderSummary(data); renderRealized(data); renderMissing(data);
                } catch (error) {
                    refs.filterPills.innerHTML = '<span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">No se pudo cargar el analisis</span>';
                    refs.meta.innerHTML = `<strong>Error en el analisis:</strong> ${error.message || error}`;
                    setSummaryErrorState();
                    refs.realizedRows.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No fue posible consultar las atenciones realizadas.</td></tr>';
                    refs.missingRows.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No fue posible calcular las atenciones faltantes.</td></tr>';
                    refs.realizedDetails.innerHTML = '';
                    refs.missingDetails.innerHTML = '';
                } finally { refs.loading.hidden = true; }
            }
            function resetFilters() {
                refs.course.value = ''; populateModules(''); refs.department.value = ''; populateMunicipalities('', ''); refs.ips.value = ''; refs.gender.value = ''; refs.zone.value = ''; refs.state.value = ''; refs.includeNonMeasurable.checked = true; refs.hideEmpty.checked = false; rangePicker.setRange(moment(@json($desde)), moment(@json($hasta))); fetchCoverage();
            }
            document.querySelectorAll('[data-toggle-details]').forEach(btn => btn.addEventListener('click', () => { const details = [...document.querySelectorAll(`${btn.dataset.toggleDetails} details`)], open = details.some(d => !d.open); details.forEach(d => d.open = open); }));
            refs.course.addEventListener('change', () => populateModules(refs.course.value, ''));
            refs.department.addEventListener('change', () => populateMunicipalities(refs.department.value, ''));
            refs.refresh.addEventListener('click', fetchCoverage);
            refs.reset.addEventListener('click', resetFilters);
            refs.includeNonMeasurable.addEventListener('change', fetchCoverage);
            refs.hideEmpty.addEventListener('change', fetchCoverage);
            populateModules('');
            refs.meta.innerHTML = '<strong>Inicializando:</strong> preparando filtros avanzados y lectura del rango de fechas.';
            loadAdvancedFilters().catch(error => {
                refs.meta.innerHTML = `<strong>Advertencia:</strong> no fue posible cargar algunos filtros avanzados. ${error.message || ''}`;
            }).then(fetchCoverage);
        })();
    </script>
@stop
