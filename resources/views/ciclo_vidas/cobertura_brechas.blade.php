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
            <div>
                <small>Analitica institucional</small>
                <strong>INOVA · Cursos de vida</strong>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <style>
        .cv-coverage-hero,.cv-coverage-stat,.cv-coverage-card,.cv-coverage-detail-card{position:relative;overflow:hidden}
        .cv-coverage-hero{display:flex;justify-content:space-between;gap:1.5rem;align-items:center;padding:1.9rem 2rem;border-radius:30px;background:radial-gradient(circle at top right,rgba(34,211,238,.34),transparent 24%),linear-gradient(135deg,#071423 0%,#0e3152 45%,#0a6c76 100%);color:#f8fbff;box-shadow:0 24px 70px rgba(2,6,23,.22)}
        .cv-coverage-hero h1{color:#fff!important}
        .cv-coverage-chip{display:inline-flex;padding:.45rem .85rem;border-radius:999px;background:rgba(255,255,255,.12);font-size:.74rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:1rem}
        .cv-coverage-brand{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;border-radius:24px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.14)}
        .cv-coverage-brand img,.cv-coverage-loading__panel img{width:78px;height:78px;object-fit:contain}
        .cv-coverage-card{border-radius:26px;background:linear-gradient(180deg,#fff 0%,#fbfdff 100%)}
        .cv-coverage-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem}
        .cv-coverage-switches{display:flex;gap:1.2rem;flex-wrap:wrap;align-items:center}
        .cv-coverage-switch{display:flex;gap:.55rem;align-items:center;margin:0;font-weight:600;color:#425466}
        .cv-coverage-filter-summary{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap;padding:1rem 1.15rem;border-radius:20px;background:#f7fbff;border:1px solid #dbe7f4}
        .cv-coverage-filter-summary small{display:block;color:#6b7b90;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;font-weight:800;margin-bottom:.55rem}
        .cv-coverage-filter-summary__actions{display:flex;gap:.6rem;flex-wrap:wrap}
        .cv-coverage-filter-pills{display:flex;gap:.55rem;flex-wrap:wrap}
        .cv-coverage-filter-pill{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .75rem;border-radius:999px;background:#e8f2ff;color:#0f172a;font-weight:700;font-size:.82rem}
        .cv-coverage-filter-pill strong{color:#335274}
        .cv-coverage-filter-pill--empty{background:#eef2f7;color:#64748b}
        .cv-coverage-stat{height:100%;padding:1.35rem 1.4rem;border-radius:24px;background:linear-gradient(180deg,#fff 0%,#f5f9ff 100%);box-shadow:0 18px 44px rgba(15,23,42,.08)}
        .cv-coverage-stat small{display:block;color:#64748b;text-transform:uppercase;letter-spacing:.08em;font-weight:800}
        .cv-coverage-stat strong{display:block;font-size:2rem;line-height:1.1;margin:.45rem 0;color:#0f172a}
        .cv-coverage-stat span,.cv-coverage-stat em{display:block;color:#5b6b7f;font-style:normal}
        .cv-coverage-stat em{margin-top:.28rem;font-size:.83rem}
        .cv-coverage-meta{border-radius:20px}
        .cv-coverage-meta__notes{margin-top:.45rem;color:#516074}
        .cv-coverage-section summary{list-style:none;cursor:pointer}
        .cv-coverage-section summary::-webkit-details-marker{display:none}
        .cv-coverage-section__summary{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.35rem 1.45rem 0}
        .cv-coverage-section__eyebrow{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:800;margin-bottom:.3rem}
        .cv-coverage-section__toggle{display:inline-flex;padding:.45rem .8rem;border-radius:999px;background:#edf5ff;color:#27415f;font-weight:800;font-size:.78rem}
        .cv-coverage-table thead th{border-top:0;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:#5b6b7f;white-space:nowrap}
        .cv-coverage-course-tag{display:inline-flex;align-items:center;gap:.6rem;font-weight:800}
        .cv-coverage-course-tag i{width:2rem;height:2rem;border-radius:12px;display:grid;place-items:center;background:#e0f2fe;color:#0f172a}
        .cv-coverage-details{display:grid;gap:1rem}
        .cv-coverage-detail-card{padding:1rem 1rem 1.15rem;border-radius:22px;background:#f8fbff;border:1px solid #dce8f7}
        .cv-coverage-detail-card summary{display:flex;justify-content:space-between;gap:1rem;align-items:center;cursor:pointer;list-style:none}
        .cv-coverage-detail-card summary::-webkit-details-marker{display:none}
        .cv-coverage-detail-card__stats{display:flex;gap:1rem;flex-wrap:wrap;color:#516074;font-weight:700}
        .cv-coverage-detail-card__stats span{padding:.35rem .65rem;border-radius:999px;background:#e8f1fb}
        .cv-coverage-badge{display:inline-flex;align-items:center;padding:.3rem .65rem;border-radius:999px;font-size:.74rem;font-weight:800}
        .cv-coverage-badge--ok{background:#dcfce7;color:#166534}
        .cv-coverage-badge--muted{background:#e2e8f0;color:#475569}
        .cv-coverage-drilldown{padding:1.2rem 1.2rem 1.35rem;border-radius:24px;background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%);border:1px solid #d9e5f3;box-shadow:inset 0 1px 0 rgba(255,255,255,.7)}
        .cv-coverage-drilldown__header{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap;margin-bottom:1rem}
        .cv-coverage-drilldown__actions{display:flex;gap:.65rem;flex-wrap:wrap}
        .cv-coverage-drilldown__summary{display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:1rem}
        .cv-coverage-drilldown__summary span{display:inline-flex;align-items:center;padding:.4rem .7rem;border-radius:999px;background:#e8f2ff;color:#27415f;font-weight:800;font-size:.8rem}
        .cv-coverage-table--detail td{vertical-align:top}
        .cv-coverage-clickable-row{cursor:pointer;transition:background .2s ease,transform .2s ease}
        .cv-coverage-clickable-row:hover{background:#f3f8ff}
        .cv-coverage-clickable-row td:first-child::after{content:'Ver listado';display:inline-flex;margin-left:.75rem;padding:.25rem .55rem;border-radius:999px;background:#edf5ff;color:#2d5d93;font-size:.72rem;font-weight:800}
        .cv-coverage-clickable-row--active{background:#e8f2ff!important;box-shadow:inset 3px 0 0 #1d4ed8}
        .cv-coverage-loading{position:fixed;inset:0;background:rgba(7,18,32,.38);z-index:1055;display:grid;place-items:center}
        .cv-coverage-loading__panel{width:min(460px,calc(100vw - 2rem));padding:2rem;border-radius:28px;background:#fff;text-align:center;box-shadow:0 30px 80px rgba(15,23,42,.24)}
        @media (max-width:991.98px){.cv-coverage-hero{flex-direction:column;align-items:flex-start}.cv-coverage-brand{width:100%}.cv-coverage-section__summary,.cv-coverage-drilldown__header{flex-direction:column;align-items:flex-start}}
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @include('ciclo_vidas.partials.date_range_shared_script')
    <script>
        (() => {
            const dataUrl = @json($dataUrl);
            const advancedFiltersUrl = @json($advancedFiltersUrl);
            const missingDetailUrl = @json($missingDetailUrl);
            const missingExportBaseUrl = @json($missingExportBaseUrl);
            const moduleCatalog = @json($filters['moduleCatalog'] ?? []);
            const nf = new Intl.NumberFormat('es-CO');
            const q = id => document.getElementById(id);

            const refs = {
                loading: q('cvCoverageLoading'),
                course: q('coverageCourse'),
                module: q('coverageModule'),
                department: q('coverageDepartment'),
                municipality: q('coverageMunicipality'),
                ips: q('coverageIps'),
                gender: q('coverageGender'),
                zone: q('coverageZone'),
                state: q('coverageState'),
                includeNonMeasurable: q('coverageIncludeNonMeasurable'),
                hideEmpty: q('coverageHideEmpty'),
                refresh: q('btnRefreshCoverage'),
                reset: q('btnResetCoverage'),
                filterPills: q('coverageFilterPills'),
                summaryRealized: q('summaryRealized'),
                summaryRealizedPatients: q('summaryRealizedPatients'),
                summaryRealizedFoot: q('summaryRealizedFoot'),
                summaryExpected: q('summaryExpected'),
                summaryTargetPatients: q('summaryTargetPatients'),
                summaryExpectedFoot: q('summaryExpectedFoot'),
                summaryMissing: q('summaryMissing'),
                summaryPatientsWithMissing: q('summaryPatientsWithMissing'),
                summaryMissingFoot: q('summaryMissingFoot'),
                summaryCoverage: q('summaryCoverage'),
                summaryMeasurable: q('summaryMeasurable'),
                summaryCoverageFoot: q('summaryCoverageFoot'),
                meta: q('coverageMeta'),
                realizedRows: q('realizedCourseRows'),
                missingRows: q('missingCourseRows'),
                realizedDetails: q('realizedDetails'),
                missingDetails: q('missingDetails'),
                missingPatientPanel: q('missingPatientPanel')
            };

            const state = { advanced: null, selectedMissingCourse: '' };

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

            const rangePicker = window.CicloVidaDateRange.init({
                pickerSelector: '#coverageRange',
                start: @json($desde),
                end: @json($hasta)
            });

            const n = value => nf.format(Number(value || 0));
            const p = value => value === null || value === undefined ? '--' : `${Number(value).toFixed(1)}%`;
            const options = items => (items || []).map(item => `<option value="${item.value}">${item.label}</option>`).join('');

            function filters() {
                return {
                    desde: rangePicker.getStart().format('YYYY-MM-DD'),
                    hasta: rangePicker.getEndInclusive().format('YYYY-MM-DD'),
                    course_key: refs.course.value,
                    module_key: refs.module.value,
                    departamento: refs.department.value,
                    municipio: refs.municipality.value,
                    ips: refs.ips.value,
                    genero: refs.gender.value,
                    zona: refs.zone.value,
                    estado_actual: refs.state.value,
                    include_non_measurable: refs.includeNonMeasurable.checked ? '1' : '0',
                    hide_empty: refs.hideEmpty.checked ? '1' : '0'
                };
            }

            function populateModules(course = '', selected = '') {
                const list = moduleCatalog[course || 'all'] || moduleCatalog.all || [];
                refs.module.innerHTML = '<option value="">Todas las atenciones</option>' + list.map(item => `<option value="${item.value}" ${item.value === selected ? 'selected' : ''}>${item.label}${item.measurable ? ' · exacta' : ''}</option>`).join('');
            }

            function populateMunicipalities(department = '', selected = '') {
                const map = state.advanced?.municipality_map || {};
                const list = department && map[department] ? map[department] : (state.advanced?.municipalities || []);
                refs.municipality.innerHTML = '<option value="">Todos</option>' + options(list);
                refs.municipality.value = list.some(item => item.value === selected) ? selected : '';
            }

            async function loadAdvancedFilters() {
                const data = await (await fetch(advancedFiltersUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                state.advanced = data;
                refs.department.innerHTML = '<option value="">Todos</option>' + options(data.departments);
                refs.ips.innerHTML = '<option value="">Todas</option>' + options(data.ips);
                refs.gender.innerHTML = '<option value="">Todos</option>' + options(data.genders);
                refs.zone.innerHTML = '<option value="">Todas</option>' + options(data.zones);
                refs.state.innerHTML = '<option value="">Todos</option>' + options(data.states);
                populateMunicipalities();
            }

            const realizedModules = course => course.modules.filter(module => !refs.hideEmpty.checked || Number(module.total_attentions || 0) > 0);
            const missingModules = course => course.modules.filter(module => {
                if (!refs.includeNonMeasurable.checked && !module.measurable) return false;
                if (!refs.hideEmpty.checked) return true;
                return module.measurable
                    ? Number(module.expected_attentions || 0) > 0 || Number(module.missing_attentions || 0) > 0 || Number(module.recorded_attentions || 0) > 0
                    : Number(module.recorded_attentions || 0) > 0;
            });

            function detailPanelMarkup() {
                return `
                    <div class="cv-coverage-drilldown__header">
                        <div>
                            <span class="cv-coverage-section__eyebrow">Detalle por curso</span>
                            <h4 id="missingPatientTitle" class="mb-1">Personas con atenciones faltantes</h4>
                            <p id="missingPatientSubtitle" class="text-muted mb-0">Haz clic sobre un curso de vida en la tabla para ver el listado de personas, la atencion faltante y la IPS responsable.</p>
                        </div>
                        <div class="cv-coverage-drilldown__actions">
                            <a id="missingPatientExportCsv" href="#" class="btn btn-primary btn-sm" target="_blank" rel="noopener"><i class="fas fa-file-csv"></i> Descargar CSV</a>
                            <a id="missingPatientExportXlsx" href="#" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                        </div>
                    </div>
                    <div id="missingPatientSummary" class="cv-coverage-drilldown__summary"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle cv-coverage-table cv-coverage-table--detail">
                            <thead>
                                <tr>
                                    <th>Tipo ID</th>
                                    <th>Identificacion</th>
                                    <th>Nombre completo</th>
                                    <th>Atencion faltante</th>
                                    <th>IPS responsable</th>
                                    <th>Municipio</th>
                                    <th>Esperadas</th>
                                    <th>Aplicadas</th>
                                    <th>Faltantes</th>
                                </tr>
                            </thead>
                            <tbody id="missingPatientRows"></tbody>
                        </table>
                    </div>
                `;
            }

            function missingPanelRefs() {
                return {
                    title: document.getElementById('missingPatientTitle'),
                    subtitle: document.getElementById('missingPatientSubtitle'),
                    summary: document.getElementById('missingPatientSummary'),
                    rows: document.getElementById('missingPatientRows'),
                    exportCsv: document.getElementById('missingPatientExportCsv'),
                    exportXlsx: document.getElementById('missingPatientExportXlsx')
                };
            }

            function ensureMissingPanel() {
                if (!document.getElementById('missingPatientTitle')) {
                    refs.missingPatientPanel.innerHTML = detailPanelMarkup();
                }
                return missingPanelRefs();
            }

            function renderPills(currentFilters, data) {
                const pills = [{ label: 'Rango', value: `${currentFilters.desde} a ${currentFilters.hasta}` }, ...(data.meta?.filters || [])];
                refs.filterPills.innerHTML = pills.length
                    ? pills.map(item => `<span class="cv-coverage-filter-pill"><strong>${item.label}:</strong> ${item.value}</span>`).join('')
                    : '<span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">Sin filtros adicionales</span>';
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
                const courses = data.realized.courses.filter(course => !refs.hideEmpty.checked || Number(course.total_attentions || 0) > 0);
                refs.realizedRows.innerHTML = courses.map(course => `<tr><td><span class="cv-coverage-course-tag"><i class="${course.icon}"></i>${course.label}</span></td><td>${n(course.total_attentions)}</td><td>${n(course.unique_patients)}</td><td>${course.avg_per_patient === null ? '--' : Number(course.avg_per_patient).toFixed(2)}</td><td>${n(course.unique_ips)}</td><td>${n(course.unique_municipalities)}</td><td>${n(course.modules_with_activity)} / ${n(course.total_modules)}</td></tr>`).join('') || '<tr><td colspan="7" class="text-center text-muted py-4">No hay atenciones realizadas para los filtros seleccionados.</td></tr>';
                refs.realizedDetails.innerHTML = courses.map((course, index) => {
                    const modules = realizedModules(course);
                    return `<details class="cv-coverage-detail-card" ${index === 0 ? 'open' : ''}><summary><div><strong>${course.label}</strong><div class="text-muted small">${course.age_label || 'Curso de vida'}</div></div><div class="cv-coverage-detail-card__stats"><span>${n(course.total_attentions)} atenciones</span><span>${n(course.unique_patients)} pacientes</span><span>${n(course.modules_with_activity)} modulos con movimiento</span></div></summary><div class="table-responsive mt-3"><table class="table table-sm table-striped"><thead><tr><th>Atencion</th><th>Registradas</th><th>Pacientes</th><th>Prom./paciente</th><th>IPS</th><th>Municipios</th></tr></thead><tbody>${modules.map(module => `<tr><td><strong>${module.short_label}</strong><div class="small text-muted">${module.description || ''}</div></td><td>${n(module.total_attentions)}</td><td>${n(module.unique_patients)}</td><td>${module.avg_per_patient === null ? '--' : Number(module.avg_per_patient).toFixed(2)}</td><td>${n(module.unique_ips)}</td><td>${n(module.unique_municipalities)}</td></tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted py-3">No hay detalle visible para este curso.</td></tr>'}</tbody></table></div></details>`;
                }).join('');
            }

            function missingExportUrl(format, courseKey) {
                const params = new URLSearchParams(filters());
                params.set('course_key', courseKey);
                return `${missingExportBaseUrl.replace('__FORMAT__', format)}?${params.toString()}`;
            }

            function clearMissingPatientPanel(message = 'Haz clic sobre un curso de vida en la tabla para ver el listado de personas, la atencion faltante y la IPS responsable.') {
                refs.missingPatientPanel.hidden = true;
                state.selectedMissingCourse = '';
                refs.missingRows.querySelectorAll('[data-missing-course]').forEach(row => row.classList.remove('cv-coverage-clickable-row--active'));
                refs.missingPatientPanel.innerHTML = detailPanelMarkup();
                const panel = missingPanelRefs();
                panel.subtitle.textContent = message;
                panel.summary.innerHTML = '';
                panel.rows.innerHTML = '';
                panel.exportCsv.href = '#';
                panel.exportXlsx.href = '#';
            }

            function renderMissingPatientDetail(data) {
                refs.missingPatientPanel.hidden = false;
                const panel = ensureMissingPanel();
                panel.title.textContent = `Personas con atenciones faltantes · ${data.meta.course_label}`;
                panel.subtitle.textContent = `Listado detallado de personas con brecha normativa entre ${data.meta.from} y ${data.meta.to}.`;
                panel.summary.innerHTML = [
                    `<span>${n(data.summary.patients)} personas con brecha</span>`,
                    `<span>${n(data.summary.missing_attentions)} atenciones faltantes</span>`,
                    `<span>${n(data.summary.ips)} IPS responsables</span>`,
                    `<span>${n(data.summary.rows)} filas de detalle</span>`
                ].join('');
                panel.rows.innerHTML = (data.rows || []).map(row => `
                    <tr>
                        <td>${row.tipo_identificacion || '--'}</td>
                        <td>${row.identificacion || '--'}</td>
                        <td><strong>${row.nombre_completo || 'Sin nombre'}</strong><div class="small text-muted">${row.genero || 'Sin genero'} · ${row.fecha_nacimiento || 'Sin fecha'} · ${row.estado_actual || 'Sin estado'}</div></td>
                        <td><strong>${row.module_label}</strong><div class="small text-muted">${row.rule_label}</div></td>
                        <td>${row.ips_responsable || 'Sin IPS'}</td>
                        <td>${row.municipio || 'Sin municipio'}<div class="small text-muted">${row.departamento || 'Sin departamento'}</div></td>
                        <td>${n(row.expected_attentions)}</td>
                        <td>${n(row.valid_attentions)}</td>
                        <td><strong>${n(row.missing_attentions)}</strong></td>
                    </tr>
                `).join('') || '<tr><td colspan="9" class="text-center text-muted py-4">No se encontraron personas con atenciones faltantes para este curso y filtros.</td></tr>';
                panel.exportCsv.href = missingExportUrl('csv', data.meta.course_key);
                panel.exportXlsx.href = missingExportUrl('xlsx', data.meta.course_key);
                refs.missingRows.querySelectorAll('[data-missing-course]').forEach(row => row.classList.toggle('cv-coverage-clickable-row--active', row.dataset.missingCourse === data.meta.course_key));
                state.selectedMissingCourse = data.meta.course_key;
                setTimeout(() => refs.missingPatientPanel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
            }

            async function fetchMissingPatientDetail(courseKey) {
                refs.loading.hidden = false;
                refs.missingPatientPanel.hidden = false;
                const panel = ensureMissingPanel();
                panel.title.textContent = 'Cargando personas con atenciones faltantes';
                panel.subtitle.textContent = 'Estamos consolidando el listado detallado para el curso seleccionado.';
                panel.summary.innerHTML = '<span>Procesando detalle...</span>';
                panel.rows.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Consultando personas con atenciones faltantes...</td></tr>';

                try {
                    const currentFilters = filters();
                    currentFilters.course_key = courseKey;
                    const response = await fetch(`${missingDetailUrl}?${new URLSearchParams(currentFilters).toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || data.error || 'No fue posible cargar el detalle de faltantes.');
                    }
                    renderMissingPatientDetail(data);
                } catch (error) {
                    panel.title.textContent = 'No fue posible cargar el detalle';
                    panel.subtitle.textContent = error.message || error;
                    panel.summary.innerHTML = '';
                    panel.rows.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">No fue posible consultar el listado de personas con atenciones faltantes.</td></tr>';
                    panel.exportCsv.href = '#';
                    panel.exportXlsx.href = '#';
                } finally {
                    refs.loading.hidden = true;
                }
            }

            function renderMissing(data) {
                const courses = data.missing.courses.filter(course => {
                    if (!refs.includeNonMeasurable.checked && Number(course.measurable_modules || 0) === 0) return false;
                    if (!refs.hideEmpty.checked) return true;
                    return Number(course.expected_attentions || 0) > 0 || Number(course.missing_attentions || 0) > 0;
                });

                refs.missingRows.innerHTML = courses.map(course => `<tr class="cv-coverage-clickable-row" data-missing-course="${course.course_key}"><td><span class="cv-coverage-course-tag"><i class="${course.icon}"></i>${course.label}</span></td><td>${n(course.target_patients)}</td><td>${n(course.expected_attentions)}</td><td>${n(course.valid_attentions)}</td><td>${n(course.missing_attentions)}</td><td>${n(course.patients_with_missing)}</td><td>${p(course.coverage)}</td></tr>`).join('') || '<tr><td colspan="7" class="text-center text-muted py-4">No hay brechas calculables para los filtros seleccionados.</td></tr>';

                refs.missingDetails.innerHTML = courses.map((course, index) => {
                    const modules = missingModules(course);
                    return `<details class="cv-coverage-detail-card" ${index === 0 ? 'open' : ''}><summary><div><strong>${course.label}</strong><div class="text-muted small">${course.measurable_modules} modulos exactos · ${course.modules_with_gap} modulos con brecha</div></div><div class="cv-coverage-detail-card__stats"><span>${n(course.target_patients)} pacientes objetivo</span><span>${n(course.missing_attentions)} faltantes</span><span>${p(course.coverage)}</span></div></summary><div class="table-responsive mt-3"><table class="table table-sm table-striped"><thead><tr><th>Atencion</th><th>Estado</th><th>Regla</th><th>Poblacion objetivo</th><th>Pacientes con brecha</th><th>Registradas</th><th>Esperadas</th><th>Aplicadas</th><th>Faltantes</th><th>Exceso</th><th>Cobertura</th></tr></thead><tbody>${modules.map(module => `<tr><td><strong>${module.short_label}</strong><div class="small text-muted">${module.description || ''}</div></td><td><span class="cv-coverage-badge ${module.measurable ? 'cv-coverage-badge--ok' : 'cv-coverage-badge--muted'}">${module.status}</span></td><td class="small">${module.method}</td><td>${module.target_patients === null ? '--' : n(module.target_patients)}</td><td>${module.patients_with_missing === null ? '--' : n(module.patients_with_missing)}</td><td>${n(module.recorded_attentions)}</td><td>${module.expected_attentions === null ? '--' : n(module.expected_attentions)}</td><td>${module.valid_attentions === null ? '--' : n(module.valid_attentions)}</td><td>${module.missing_attentions === null ? '--' : n(module.missing_attentions)}</td><td>${module.excess_attentions === null ? '--' : n(module.excess_attentions)}</td><td>${p(module.coverage)}</td></tr>`).join('') || '<tr><td colspan="11" class="text-center text-muted py-3">No hay detalle visible para este curso.</td></tr>'}</tbody></table></div></details>`;
                }).join('');

                refs.missingRows.querySelectorAll('[data-missing-course]').forEach(row => {
                    row.addEventListener('click', () => fetchMissingPatientDetail(row.dataset.missingCourse));
                    row.classList.toggle('cv-coverage-clickable-row--active', row.dataset.missingCourse === state.selectedMissingCourse);
                });

                if (state.selectedMissingCourse && !courses.some(course => course.course_key === state.selectedMissingCourse)) {
                    clearMissingPatientPanel();
                }
            }

            async function fetchCoverage() {
                refs.loading.hidden = false;
                try {
                    const currentFilters = filters();
                    const response = await fetch(`${dataUrl}?${new URLSearchParams(currentFilters).toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.ok === false) {
                        throw new Error(data.message || data.error || 'No fue posible calcular cobertura y brechas con la configuracion actual.');
                    }

                    renderPills(currentFilters, data);
                    renderSummary(data);
                    renderRealized(data);
                    renderMissing(data);

                    if (state.selectedMissingCourse) {
                        const visible = (data.missing.courses || []).some(course => course.course_key === state.selectedMissingCourse);
                        if (visible) {
                            await fetchMissingPatientDetail(state.selectedMissingCourse);
                        }
                    }
                } catch (error) {
                    refs.filterPills.innerHTML = '<span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">No se pudo cargar el analisis</span>';
                    refs.meta.innerHTML = `<strong>Error en el analisis:</strong> ${error.message || error}`;
                    setSummaryErrorState();
                    refs.realizedRows.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No fue posible consultar las atenciones realizadas.</td></tr>';
                    refs.missingRows.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">No fue posible calcular las atenciones faltantes.</td></tr>';
                    refs.realizedDetails.innerHTML = '';
                    refs.missingDetails.innerHTML = '';
                    clearMissingPatientPanel('El listado de personas con atenciones faltantes no estuvo disponible por un error en el analisis.');
                } finally {
                    refs.loading.hidden = true;
                }
            }

            function resetFilters() {
                refs.course.value = '';
                populateModules('');
                refs.department.value = '';
                populateMunicipalities('', '');
                refs.ips.value = '';
                refs.gender.value = '';
                refs.zone.value = '';
                refs.state.value = '';
                refs.includeNonMeasurable.checked = true;
                refs.hideEmpty.checked = false;
                clearMissingPatientPanel();
                rangePicker.setRange(moment(@json($desde)), moment(@json($hasta)));
                fetchCoverage();
            }

            document.querySelectorAll('[data-toggle-details]').forEach(button => button.addEventListener('click', () => {
                const details = [...document.querySelectorAll(`${button.dataset.toggleDetails} details`)];
                const open = details.some(detail => !detail.open);
                details.forEach(detail => detail.open = open);
            }));

            refs.course.addEventListener('change', () => populateModules(refs.course.value, ''));
            refs.department.addEventListener('change', () => populateMunicipalities(refs.department.value, ''));
            refs.refresh.addEventListener('click', fetchCoverage);
            refs.reset.addEventListener('click', resetFilters);
            refs.includeNonMeasurable.addEventListener('change', fetchCoverage);
            refs.hideEmpty.addEventListener('change', fetchCoverage);

            populateModules('');
            clearMissingPatientPanel();
            refs.meta.innerHTML = '<strong>Inicializando:</strong> preparando filtros avanzados y lectura del rango de fechas.';
            loadAdvancedFilters().catch(error => {
                refs.meta.innerHTML = `<strong>Advertencia:</strong> no fue posible cargar algunos filtros avanzados. ${error.message || ''}`;
            }).then(fetchCoverage);
        })();
    </script>
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
                <div>
                    <label class="mb-1 font-weight-bold">Curso de vida</label>
                    <select id="coverageCourse" class="form-control">
                        <option value="">Todos los cursos</option>
                        @foreach ($filters['courses'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Atencion</label>
                    <select id="coverageModule" class="form-control">
                        <option value="">Todas las atenciones</option>
                        @foreach ($filters['modules'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}{{ !empty($option['measurable']) ? ' · exacta' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Departamento</label>
                    <select id="coverageDepartment" class="form-control"><option value="">Todos</option></select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Municipio</label>
                    <select id="coverageMunicipality" class="form-control"><option value="">Todos</option></select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">IPS primaria</label>
                    <select id="coverageIps" class="form-control"><option value="">Todas</option></select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Genero</label>
                    <select id="coverageGender" class="form-control"><option value="">Todos</option></select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Zona</label>
                    <select id="coverageZone" class="form-control"><option value="">Todas</option></select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Estado afiliado</label>
                    <select id="coverageState" class="form-control"><option value="">Todos</option></select>
                </div>
            </div>

            <div class="cv-coverage-switches mt-3">
                <label class="cv-coverage-switch"><input type="checkbox" id="coverageIncludeNonMeasurable" checked><span>Incluir modulos no estandarizables en faltantes</span></label>
                <label class="cv-coverage-switch"><input type="checkbox" id="coverageHideEmpty"><span>Ocultar filas sin movimiento</span></label>
                <button type="button" id="btnResetCoverage" class="btn btn-link btn-sm p-0">Restablecer filtros</button>
            </div>

            <div class="cv-coverage-filter-summary mt-4">
                <div>
                    <small>Filtros activos</small>
                    <div id="coverageFilterPills" class="cv-coverage-filter-pills">
                        <span class="cv-coverage-filter-pill cv-coverage-filter-pill--empty">Sin filtros adicionales</span>
                    </div>
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
            <div id="missingPatientPanel" class="cv-coverage-drilldown mt-4" hidden></div>
        </div>
    </details>
@stop
