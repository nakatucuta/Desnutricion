@extends('adminlte::page')

@section('title', 'Cobertura y brechas')

@section('content_header')
    <div class="cv-coverage-hero">
        <div>
            <span class="cv-coverage-chip">Cobertura y brechas</span>
            <h1 class="mb-2">Atenciones realizadas y atenciones faltantes por curso de vida</h1>
            <p class="mb-0">
                Cruza fecha de nacimiento, curso de vida, territorio e IPS para leer la carga realizada
                y la brecha normativa desde una sola pantalla.
            </p>
        </div>
        <div class="cv-coverage-brand">
            <img src="{{ $companyLogo }}" alt="Escudo institucional">
            <div>
                <small>Lectura integral</small>
                <strong>INOVA · Cursos de vida</strong>
            </div>
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
                'note' => '<span><i class="fas fa-circle-info"></i> Los faltantes se calculan de forma exacta en modulos con regla parametrizada. Los modulos por criterio clinico quedan identificados como no estandarizables.</span>',
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
                            <option value="{{ $option['value'] }}">
                                {{ $option['label'] }}{{ !empty($option['measurable']) ? ' · exacta' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Departamento</label>
                    <select id="coverageDepartment" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Municipio</label>
                    <select id="coverageMunicipality" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">IPS primaria</label>
                    <select id="coverageIps" class="form-control">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Genero</label>
                    <select id="coverageGender" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Zona</label>
                    <select id="coverageZone" class="form-control">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Estado afiliado</label>
                    <select id="coverageState" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <div class="cv-coverage-switches mt-3">
                <label class="cv-coverage-switch">
                    <input type="checkbox" id="coverageIncludeNonMeasurable" checked>
                    <span>Incluir modulos no estandarizables en la tabla de faltantes</span>
                </label>
                <label class="cv-coverage-switch">
                    <input type="checkbox" id="coverageHideEmpty">
                    <span>Ocultar filas sin movimiento</span>
                </label>
                <button type="button" id="btnResetCoverage" class="btn btn-link btn-sm p-0">
                    Restablecer filtros
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="coverageSummaryCards">
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-coverage-stat">
                <small>Atenciones realizadas</small>
                <strong id="summaryRealized">0</strong>
                <span id="summaryRealizedPatients">0 pacientes impactados</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-coverage-stat">
                <small>Atenciones esperadas</small>
                <strong id="summaryExpected">0</strong>
                <span id="summaryTargetPatients">0 pacientes con oportunidad</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-coverage-stat">
                <small>Atenciones faltantes</small>
                <strong id="summaryMissing">0</strong>
                <span id="summaryPatientsWithMissing">0 pacientes con brecha</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="cv-coverage-stat">
                <small>Cobertura normativa</small>
                <strong id="summaryCoverage">0%</strong>
                <span id="summaryMeasurable">0 modulos exactos</span>
            </div>
        </div>
    </div>

    <div class="alert alert-light border shadow-sm mb-4 cv-coverage-meta" id="coverageMeta"></div>

    <div class="card shadow-sm border-0 mb-4 cv-coverage-card">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Tabla 1 · Atenciones realizadas</h3>
                <small class="text-muted">Sumatoria de atenciones registradas en el rango seleccionado.</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-toggle-details="#realizedDetails">
                Expandir / colapsar detalle
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle cv-coverage-table">
                    <thead>
                        <tr>
                            <th>Curso de vida</th>
                            <th>Atenciones</th>
                            <th>Pacientes</th>
                            <th>IPS</th>
                            <th>Municipios</th>
                        </tr>
                    </thead>
                    <tbody id="realizedCourseRows"></tbody>
                </table>
            </div>
            <div id="realizedDetails" class="cv-coverage-details mt-3"></div>
        </div>
    </div>

    <div class="card shadow-sm border-0 cv-coverage-card">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Tabla 2 · Atenciones faltantes</h3>
                <small class="text-muted">Brecha normativa calculada desde fecha de nacimiento y reglas parametrizadas por modulo.</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-toggle-details="#missingDetails">
                Expandir / colapsar detalle
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle cv-coverage-table">
                    <thead>
                        <tr>
                            <th>Curso de vida</th>
                            <th>Esperadas</th>
                            <th>Aplicadas</th>
                            <th>Faltantes</th>
                            <th>Cobertura</th>
                        </tr>
                    </thead>
                    <tbody id="missingCourseRows"></tbody>
                </table>
            </div>
            <div id="missingDetails" class="cv-coverage-details mt-3"></div>
        </div>
    </div>
@stop

@section('css')
    @include('ciclo_vidas.partials.date_range_shared_styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        .cv-coverage-hero,.cv-coverage-stat,.cv-coverage-card,.cv-coverage-detail-card{position:relative;overflow:hidden}
        .cv-coverage-hero{display:flex;justify-content:space-between;gap:1.5rem;align-items:center;padding:1.9rem 2rem;border-radius:30px;background:linear-gradient(135deg,#071423 0%,#0e3152 50%,#0a6c76 100%);color:#f8fbff;box-shadow:0 24px 70px rgba(2,6,23,.22)}
        .cv-coverage-chip{display:inline-flex;padding:.45rem .85rem;border-radius:999px;background:rgba(255,255,255,.12);font-size:.74rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:1rem}
        .cv-coverage-brand{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;border-radius:24px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.14)}
        .cv-coverage-brand img,.cv-coverage-loading__panel img{width:78px;height:78px;object-fit:contain}
        .cv-coverage-card{border-radius:26px}
        .cv-coverage-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem}
        .cv-coverage-switches{display:flex;gap:1.2rem;flex-wrap:wrap;align-items:center}
        .cv-coverage-switch{display:flex;gap:.55rem;align-items:center;margin:0;font-weight:600;color:#425466}
        .cv-coverage-stat{height:100%;padding:1.35rem 1.4rem;border-radius:24px;background:linear-gradient(180deg,#ffffff 0%,#f5f9ff 100%);box-shadow:0 18px 44px rgba(15,23,42,.08)}
        .cv-coverage-stat small{display:block;color:#64748b;text-transform:uppercase;letter-spacing:.08em;font-weight:800}
        .cv-coverage-stat strong{display:block;font-size:2rem;line-height:1.1;margin:.45rem 0;color:#0f172a}
        .cv-coverage-stat span{color:#5b6b7f}
        .cv-coverage-meta{border-radius:20px}
        .cv-coverage-table thead th{border-top:0;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:#5b6b7f}
        .cv-coverage-course-tag{display:inline-flex;align-items:center;gap:.5rem;font-weight:800}
        .cv-coverage-course-tag i{width:2rem;height:2rem;border-radius:12px;display:grid;place-items:center;background:#e0f2fe;color:#0f172a}
        .cv-coverage-details{display:grid;gap:1rem}
        .cv-coverage-detail-card{padding:1rem 1rem 1.15rem;border-radius:22px;background:#f8fbff;border:1px solid #dce8f7}
        .cv-coverage-detail-card summary{display:flex;justify-content:space-between;gap:1rem;align-items:center;cursor:pointer;list-style:none}
        .cv-coverage-detail-card summary::-webkit-details-marker{display:none}
        .cv-coverage-detail-card__stats{display:flex;gap:1rem;flex-wrap:wrap;color:#516074;font-weight:700}
        .cv-coverage-detail-card__stats span{padding:.35rem .65rem;border-radius:999px;background:#e8f1fb}
        .cv-coverage-badge{display:inline-flex;align-items:center;padding:.3rem .65rem;border-radius:999px;font-size:.74rem;font-weight:800}
        .cv-coverage-badge--ok{background:#dcfce7;color:#166534}
        .cv-coverage-badge--warn{background:#fee2e2;color:#991b1b}
        .cv-coverage-badge--muted{background:#e2e8f0;color:#475569}
        .cv-coverage-loading{position:fixed;inset:0;background:rgba(7,18,32,.38);z-index:1055;display:grid;place-items:center}
        .cv-coverage-loading__panel{width:min(460px,calc(100vw - 2rem));padding:2rem;border-radius:28px;background:#fff;text-align:center;box-shadow:0 30px 80px rgba(15,23,42,.24)}
        @media (max-width: 991.98px){.cv-coverage-hero{flex-direction:column;align-items:flex-start}.cv-coverage-brand{width:100%;justify-content:flex-start}}
    </style>
@stop

@section('js')
    @include('ciclo_vidas.partials.date_range_shared_script')
    <script>
        (function () {
            const dataUrl = @json($dataUrl);
            const advancedFiltersUrl = @json($advancedFiltersUrl);
            const numberFormatter = new Intl.NumberFormat('es-CO');

            const refs = {
                loading: document.getElementById('cvCoverageLoading'),
                course: document.getElementById('coverageCourse'),
                module: document.getElementById('coverageModule'),
                department: document.getElementById('coverageDepartment'),
                municipality: document.getElementById('coverageMunicipality'),
                ips: document.getElementById('coverageIps'),
                gender: document.getElementById('coverageGender'),
                zone: document.getElementById('coverageZone'),
                state: document.getElementById('coverageState'),
                includeNonMeasurable: document.getElementById('coverageIncludeNonMeasurable'),
                hideEmpty: document.getElementById('coverageHideEmpty'),
                refresh: document.getElementById('btnRefreshCoverage'),
                reset: document.getElementById('btnResetCoverage'),
                summaryRealized: document.getElementById('summaryRealized'),
                summaryRealizedPatients: document.getElementById('summaryRealizedPatients'),
                summaryExpected: document.getElementById('summaryExpected'),
                summaryTargetPatients: document.getElementById('summaryTargetPatients'),
                summaryMissing: document.getElementById('summaryMissing'),
                summaryPatientsWithMissing: document.getElementById('summaryPatientsWithMissing'),
                summaryCoverage: document.getElementById('summaryCoverage'),
                summaryMeasurable: document.getElementById('summaryMeasurable'),
                meta: document.getElementById('coverageMeta'),
                realizedRows: document.getElementById('realizedCourseRows'),
                missingRows: document.getElementById('missingCourseRows'),
                realizedDetails: document.getElementById('realizedDetails'),
                missingDetails: document.getElementById('missingDetails'),
            };

            const rangePicker = window.CicloVidaDateRange.init({
                pickerSelector: '#coverageRange',
                start: @json($desde),
                end: @json($hasta),
            });

            function showLoading(show) {
                refs.loading.hidden = !show;
            }

            function formatNumber(value) {
                return numberFormatter.format(Number(value || 0));
            }

            function formatPercent(value) {
                return value === null || value === undefined ? '--' : `${Number(value).toFixed(1)}%`;
            }

            function optionHtml(items) {
                return items.map(item => `<option value="${item.value}">${item.label}</option>`).join('');
            }

            async function loadAdvancedFilters() {
                const response = await fetch(advancedFiltersUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();
                refs.department.innerHTML = '<option value="">Todos</option>' + optionHtml(data.departments || []);
                refs.municipality.innerHTML = '<option value="">Todos</option>' + optionHtml(data.municipalities || []);
                refs.ips.innerHTML = '<option value="">Todas</option>' + optionHtml(data.ips || []);
                refs.gender.innerHTML = '<option value="">Todos</option>' + optionHtml(data.genders || []);
                refs.zone.innerHTML = '<option value="">Todas</option>' + optionHtml(data.zones || []);
                refs.state.innerHTML = '<option value="">Todos</option>' + optionHtml(data.states || []);
            }

            function collectFilters() {
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
                    hide_empty: refs.hideEmpty.checked ? '1' : '0',
                };
            }

            function filterRealizedModules(course) {
                return course.modules.filter(module => !refs.hideEmpty.checked || Number(module.total_attentions || 0) > 0);
            }

            function filterMissingModules(course) {
                return course.modules.filter(module => {
                    if (!refs.includeNonMeasurable.checked && !module.measurable) {
                        return false;
                    }

                    if (!refs.hideEmpty.checked) {
                        return true;
                    }

                    if (module.measurable) {
                        return Number(module.expected_attentions || 0) > 0
                            || Number(module.missing_attentions || 0) > 0
                            || Number(module.recorded_attentions || 0) > 0;
                    }

                    return Number(module.recorded_attentions || 0) > 0;
                });
            }

            function renderRealized(data) {
                const courses = data.realized.courses.filter(course => !refs.hideEmpty.checked || Number(course.total_attentions || 0) > 0);

                refs.realizedRows.innerHTML = courses.map(course => `
                    <tr>
                        <td><span class="cv-coverage-course-tag"><i class="${course.icon}"></i>${course.label}</span></td>
                        <td>${formatNumber(course.total_attentions)}</td>
                        <td>${formatNumber(course.unique_patients)}</td>
                        <td>${formatNumber(course.unique_ips)}</td>
                        <td>${formatNumber(course.unique_municipalities)}</td>
                    </tr>
                `).join('') || '<tr><td colspan="5" class="text-center text-muted py-4">No hay atenciones realizadas para los filtros seleccionados.</td></tr>';

                refs.realizedDetails.innerHTML = courses.map((course, index) => {
                    const modules = filterRealizedModules(course);
                    return `
                        <details class="cv-coverage-detail-card" ${index === 0 ? 'open' : ''}>
                            <summary>
                                <div>
                                    <strong>${course.label}</strong>
                                    <div class="text-muted small">${course.age_label || 'Curso de vida'}</div>
                                </div>
                                <div class="cv-coverage-detail-card__stats">
                                    <span>${formatNumber(course.total_attentions)} atenciones</span>
                                    <span>${formatNumber(course.unique_patients)} pacientes</span>
                                    <span>${formatNumber(modules.length)} atenciones visibles</span>
                                </div>
                            </summary>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Atencion</th>
                                            <th>Registradas</th>
                                            <th>Pacientes</th>
                                            <th>IPS</th>
                                            <th>Municipios</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${modules.map(module => `
                                            <tr>
                                                <td>
                                                    <strong>${module.short_label}</strong>
                                                    <div class="small text-muted">${module.description || ''}</div>
                                                </td>
                                                <td>${formatNumber(module.total_attentions)}</td>
                                                <td>${formatNumber(module.unique_patients)}</td>
                                                <td>${formatNumber(module.unique_ips)}</td>
                                                <td>${formatNumber(module.unique_municipalities)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="5" class="text-center text-muted py-3">No hay detalle visible para este curso.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    `;
                }).join('');
            }

            function renderMissing(data) {
                const courses = data.missing.courses.filter(course => {
                    if (!refs.includeNonMeasurable.checked && Number(course.measurable_modules || 0) === 0) {
                        return false;
                    }
                    if (!refs.hideEmpty.checked) {
                        return true;
                    }
                    return Number(course.expected_attentions || 0) > 0 || Number(course.missing_attentions || 0) > 0;
                });

                refs.missingRows.innerHTML = courses.map(course => `
                    <tr>
                        <td><span class="cv-coverage-course-tag"><i class="${course.icon}"></i>${course.label}</span></td>
                        <td>${formatNumber(course.expected_attentions)}</td>
                        <td>${formatNumber(course.valid_attentions)}</td>
                        <td>${formatNumber(course.missing_attentions)}</td>
                        <td>${formatPercent(course.coverage)}</td>
                    </tr>
                `).join('') || '<tr><td colspan="5" class="text-center text-muted py-4">No hay brechas calculables para los filtros seleccionados.</td></tr>';

                refs.missingDetails.innerHTML = courses.map((course, index) => {
                    const modules = filterMissingModules(course);
                    return `
                        <details class="cv-coverage-detail-card" ${index === 0 ? 'open' : ''}>
                            <summary>
                                <div>
                                    <strong>${course.label}</strong>
                                    <div class="text-muted small">${course.measurable_modules} modulos exactos en la seleccion</div>
                                </div>
                                <div class="cv-coverage-detail-card__stats">
                                    <span>${formatNumber(course.expected_attentions)} esperadas</span>
                                    <span>${formatNumber(course.missing_attentions)} faltantes</span>
                                    <span>${formatPercent(course.coverage)}</span>
                                </div>
                            </summary>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Atencion</th>
                                            <th>Estado</th>
                                            <th>Regla</th>
                                            <th>Registradas</th>
                                            <th>Esperadas</th>
                                            <th>Aplicadas</th>
                                            <th>Faltantes</th>
                                            <th>Cobertura</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${modules.map(module => `
                                            <tr>
                                                <td>
                                                    <strong>${module.short_label}</strong>
                                                    <div class="small text-muted">${module.description || ''}</div>
                                                </td>
                                                <td>
                                                    <span class="cv-coverage-badge ${module.measurable ? 'cv-coverage-badge--ok' : 'cv-coverage-badge--muted'}">
                                                        ${module.status}
                                                    </span>
                                                </td>
                                                <td class="small">${module.method}</td>
                                                <td>${formatNumber(module.recorded_attentions)}</td>
                                                <td>${module.expected_attentions === null ? '--' : formatNumber(module.expected_attentions)}</td>
                                                <td>${module.valid_attentions === null ? '--' : formatNumber(module.valid_attentions)}</td>
                                                <td>${module.missing_attentions === null ? '--' : formatNumber(module.missing_attentions)}</td>
                                                <td>${formatPercent(module.coverage)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="8" class="text-center text-muted py-3">No hay detalle visible para este curso.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    `;
                }).join('');
            }

            function renderSummary(data) {
                refs.summaryRealized.textContent = formatNumber(data.summary.total_realized);
                refs.summaryRealizedPatients.textContent = `${formatNumber(data.summary.total_realized_patients)} pacientes impactados`;
                refs.summaryExpected.textContent = formatNumber(data.summary.total_expected);
                refs.summaryTargetPatients.textContent = `${formatNumber(data.summary.target_patients)} pacientes con oportunidad`;
                refs.summaryMissing.textContent = formatNumber(data.summary.total_missing);
                refs.summaryPatientsWithMissing.textContent = `${formatNumber(data.summary.patients_with_missing)} pacientes con brecha`;
                refs.summaryCoverage.textContent = formatPercent(data.summary.coverage);
                refs.summaryMeasurable.textContent = `${formatNumber(data.summary.measurable_modules)} modulos exactos · ${formatNumber(data.summary.non_measurable_modules)} no estandarizables`;
                refs.meta.innerHTML = `
                    <strong>Corte:</strong> ${data.meta.from} a ${data.meta.to}
                    <br><strong>Metodologia:</strong> ${data.meta.notes.join(' ')}
                `;
            }

            async function fetchCoverage() {
                showLoading(true);
                try {
                    const params = new URLSearchParams(collectFilters());
                    const response = await fetch(`${dataUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await response.json();
                    renderSummary(data);
                    renderRealized(data);
                    renderMissing(data);
                } finally {
                    showLoading(false);
                }
            }

            function resetFilters() {
                refs.course.value = '';
                refs.module.value = '';
                refs.department.value = '';
                refs.municipality.value = '';
                refs.ips.value = '';
                refs.gender.value = '';
                refs.zone.value = '';
                refs.state.value = '';
                refs.includeNonMeasurable.checked = true;
                refs.hideEmpty.checked = false;
                rangePicker.setRange(moment(@json($desde)), moment(@json($hasta)));
                fetchCoverage();
            }

            document.querySelectorAll('[data-toggle-details]').forEach(button => {
                button.addEventListener('click', () => {
                    const details = Array.from(document.querySelectorAll(`${button.dataset.toggleDetails} details`));
                    const shouldOpen = details.some(detail => !detail.open);
                    details.forEach(detail => {
                        detail.open = shouldOpen;
                    });
                });
            });

            refs.refresh.addEventListener('click', fetchCoverage);
            refs.reset.addEventListener('click', resetFilters);
            refs.includeNonMeasurable.addEventListener('change', fetchCoverage);
            refs.hideEmpty.addEventListener('change', fetchCoverage);

            loadAdvancedFilters()
                .catch(() => null)
                .then(fetchCoverage);
        })();
    </script>
@stop
