(function () {
    const filtersForm = document.querySelector('.an-filters');
    const tableBody = document.getElementById('indicadoresTableBody');
    const tableLoading = document.getElementById('tableLoadingState');
    const tableVisibleCount = document.getElementById('tableVisibleCount');
    const tableTotalCount = document.getElementById('tableTotalCount');
    const summaryRows = document.getElementById('summaryRows');
    const summaryRange = document.getElementById('summaryRange');
    const summaryEvent = document.getElementById('summaryEvent');
    const summaryClasificacion = document.getElementById('summaryClasificacion');
    const kpiPrestadores = document.getElementById('kpiPrestadores');
    const kpiUsuarios = document.getElementById('kpiUsuarios');
    const kpiAsignados = document.getElementById('kpiAsignados');
    const kpiSeguimientos = document.getElementById('kpiSeguimientos');
    const kpiCobertura = document.getElementById('kpiCobertura');
    const kpiBrecha = document.getElementById('kpiBrecha');
    const kpiPrimerSeguimiento = document.getElementById('kpiPrimerSeguimiento');
    const kpiClasificacionFinal = document.getElementById('kpiClasificacionFinal');
    const kpiCalidadIncompletos = document.getElementById('kpiCalidadIncompletos');
    const kpiCalidadDato = document.getElementById('kpiCalidadDato');
    const kpiCalidadInconsistentes = document.getElementById('kpiCalidadInconsistentes');
    const filtersSubmitBtn = document.getElementById('filtersSubmitBtn');

    const traceDrawer = document.getElementById('traceDrawer');
    const traceMask = document.getElementById('traceMask');
    const traceTitle = document.getElementById('traceTitle');
    const traceMeta = document.getElementById('traceMeta');
    const traceChipRange = document.getElementById('traceChipRange');
    const traceChipEvent = document.getElementById('traceChipEvent');
    const traceChipClasificacion = document.getElementById('traceChipClasificacion');
    const traceChipCode = document.getElementById('traceChipCode');
    const traceSummaryHint = document.getElementById('traceSummaryHint');
    const traceEventFilter = document.getElementById('traceEventFilter');
    const traceRefresh = document.getElementById('traceRefresh');
    const traceLoading = document.getElementById('traceLoading');
    const traceError = document.getElementById('traceError');
    const traceErrorText = document.getElementById('traceErrorText');
    const traceEmpty = document.getElementById('traceEmpty');
    const traceContent = document.getElementById('traceContent');
    const traceClose = document.getElementById('traceClose');

    const primerSeguimientoModal = document.getElementById('primerSeguimientoModal');
    const primerSeguimientoContent = document.getElementById('primerSeguimientoContent');
    const primerSeguimientoLoading = document.getElementById('primerSeguimientoLoading');
    const primerSeguimientoError = document.getElementById('primerSeguimientoError');
    const primerSeguimientoErrorText = document.getElementById('primerSeguimientoErrorText');
    const primerSeguimientoClose = document.getElementById('primerSeguimientoClose');
    const primerSeguimientoModalTitle = document.getElementById('primerSeguimientoModalTitle');
    const primerSeguimientoModalMeta = document.getElementById('primerSeguimientoModalMeta');

    const seguimientosModal = document.getElementById('seguimientosModal');
    const seguimientosContent = document.getElementById('seguimientosContent');
    const seguimientosLoading = document.getElementById('seguimientosLoading');
    const seguimientosError = document.getElementById('seguimientosError');
    const seguimientosErrorText = document.getElementById('seguimientosErrorText');
    const seguimientosClose = document.getElementById('seguimientosClose');
    const seguimientosModalTitle = document.getElementById('seguimientosModalTitle');
    const seguimientosModalMeta = document.getElementById('seguimientosModalMeta');

    const asignadosModal = document.getElementById('asignadosModal');
    const asignadosContent = document.getElementById('asignadosContent');
    const asignadosLoading = document.getElementById('asignadosLoading');
    const asignadosError = document.getElementById('asignadosError');
    const asignadosErrorText = document.getElementById('asignadosErrorText');
    const asignadosClose = document.getElementById('asignadosClose');
    const asignadosModalTitle = document.getElementById('asignadosModalTitle');
    const asignadosModalMeta = document.getElementById('asignadosModalMeta');

    const dateFormatter = new Intl.DateTimeFormat('es-CO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
    const numberFormatter = new Intl.NumberFormat('es-CO', {
        maximumFractionDigits: 2,
    });

    const state = {
        basePath: '',
        dashboardTimer: null,
        currentFilters: null,
        traceContext: null,
        tracePayload: null,
    };

    function initBasePath() {
        if (!filtersForm) {
            return '';
        }

        const action = filtersForm.getAttribute('action') || window.location.href;
        const url = new URL(action, window.location.href);
        return url.pathname.replace(/\/alteraciones-nutricionales\/indicadores\/?$/, '');
    }

    function endpoint(path) {
        return `${window.location.origin}${state.basePath}${path}`;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function isBlank(value) {
        return value === null || value === undefined || String(value).trim() === '';
    }

    function formatInteger(value) {
        if (isBlank(value) || Number.isNaN(Number(value))) {
            return '0';
        }

        return numberFormatter.format(Math.round(Number(value)));
    }

    function formatDecimal(value) {
        if (isBlank(value) || Number.isNaN(Number(value))) {
            return '0';
        }

        return numberFormatter.format(Number(value));
    }

    function formatPercent(value) {
        if (isBlank(value) || Number.isNaN(Number(value))) {
            return '0,00%';
        }

        return `${numberFormatter.format(Number(value))}%`;
    }

    function formatDate(value) {
        if (isBlank(value)) {
            return 'Sin dato';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return dateFormatter.format(date);
    }

    function formatDays(value) {
        if (isBlank(value)) {
            return 'Sin dato';
        }

        const days = Math.round(Number(value));
        if (Number.isNaN(days)) {
            return 'Sin dato';
        }

        return `${numberFormatter.format(days)} días`;
    }

    function formatText(value, fallback = 'Sin dato') {
        const text = String(value ?? '').trim();
        return text !== '' ? text : fallback;
    }

    function setVisible(node, visible) {
        if (!node) {
            return;
        }

        node.classList.toggle('d-none', !visible);
    }

    function openModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('an-modal-open');
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('an-modal-open');
    }

    function openDrawer() {
        if (traceDrawer) {
            traceDrawer.setAttribute('aria-hidden', 'false');
            traceDrawer.classList.add('is-open');
        }
        if (traceMask) {
            traceMask.classList.add('is-open');
        }
    }

    function closeDrawer() {
        if (traceDrawer) {
            traceDrawer.setAttribute('aria-hidden', 'true');
            traceDrawer.classList.remove('is-open');
        }
        if (traceMask) {
            traceMask.classList.remove('is-open');
        }
    }

    function currentFilters() {
        return {
            desde: filtersForm?.querySelector('[name="desde"]')?.value || '',
            hasta: filtersForm?.querySelector('[name="hasta"]')?.value || '',
            evento: filtersForm?.querySelector('[name="evento"]')?.value || 'all',
            clasificacion: filtersForm?.querySelector('[name="clasificacion"]')?.value || 'all',
        };
    }

    function currentParams(filters) {
        const params = new URLSearchParams();
        if (filters.desde) {
            params.set('desde', filters.desde);
        }
        if (filters.hasta) {
            params.set('hasta', filters.hasta);
        }
        if (filters.evento && filters.evento !== 'all') {
            params.set('evento', filters.evento);
        }
        if (filters.clasificacion && filters.clasificacion !== 'all') {
            params.set('clasificacion', filters.clasificacion);
        }
        return params;
    }

    async function fetchJson(path, params) {
        const response = await fetch(`${endpoint(path)}?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            let message = 'No fue posible cargar la informacion.';
            try {
                const errorData = await response.json();
                message = errorData.message || message;
            } catch (error) {
                // noop
            }
            throw new Error(message);
        }

        return response.json();
    }

    function setTableLoading(loading) {
        if (!tableLoading) {
            return;
        }
        tableLoading.classList.toggle('is-visible', loading);
        tableLoading.setAttribute('aria-hidden', loading ? 'false' : 'true');
        if (filtersSubmitBtn) {
            filtersSubmitBtn.disabled = loading;
        }
    }

    function setError(node, textNode, message) {
        if (!node || !textNode) {
            return;
        }

        textNode.textContent = message;
        node.classList.remove('d-none');
    }

    function clearError(node) {
        if (!node) {
            return;
        }

        node.classList.add('d-none');
    }

    function renderRow(row) {
        const codigo = formatText(row.codigo, '');
        const desde = state.currentFilters?.desde || '';
        const hasta = state.currentFilters?.hasta || '';
        const evento = state.currentFilters?.evento || 'all';
        const clasificacion = state.currentFilters?.clasificacion || 'all';
        const usuariosLabel = formatText(row.usuarios_label || '', 'Sin usuarios asociados');
        const coverage = formatPercent(row.total_cobertura ?? 0);
        const primerSeguimiento = isBlank(row.primer_seguimiento_promedio)
            ? 'Sin dato'
            : formatDays(row.primer_seguimiento_promedio);
        const clasificaciones = Array.isArray(row.clasificaciones) ? row.clasificaciones : [];
        const chips = clasificaciones.length
            ? clasificaciones.slice(0, 3).map((item) => {
                const label = formatText(item.label, 'SIN CLASIFICACION');
                const count = formatInteger(item.count);
                return `<span class="an-chip an-chip--mini">${escapeHtml(label)} (${count})</span>`;
            }).join(' ')
            : '<span class="an-muted">Sin clasificaciones visibles</span>';

        return `
            <tr class="${escapeHtml(String(row.codigo || '')) === escapeHtml(String(row.codigo || '')) ? '' : ''}">
                <td>
                    <div class="an-code">${escapeHtml(formatText(row.usuario_destacado, 'Sin usuario destacado'))}</div>
                    <div class="an-muted">Codigo ${escapeHtml(codigo)}</div>
                    <div class="an-muted" style="margin-top:6px;">${escapeHtml(usuariosLabel)}</div>
                </td>
                <td>
                    <button
                        type="button"
                        class="an-primer-seguimiento-btn an-asignados-btn"
                        data-asignados="1"
                        data-codigo="${escapeHtml(codigo)}"
                        data-desde="${escapeHtml(desde)}"
                        data-hasta="${escapeHtml(hasta)}"
                        data-evento="${escapeHtml(evento)}"
                        data-clasificacion="${escapeHtml(clasificacion)}"
                        onclick="window.openAsignadosModal && window.openAsignadosModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                    >${formatInteger(row.total_asignados ?? 0)}</button>
                </td>
                <td>
                    <button
                        type="button"
                        class="an-seguimientos-btn"
                        data-seguimientos="1"
                        data-codigo="${escapeHtml(codigo)}"
                        data-desde="${escapeHtml(desde)}"
                        data-hasta="${escapeHtml(hasta)}"
                        data-evento="${escapeHtml(evento)}"
                        data-clasificacion="${escapeHtml(clasificacion)}"
                        onclick="window.openSeguimientosModal && window.openSeguimientosModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                    >${formatInteger(row.total_seguimientos ?? 0)}</button>
                </td>
                <td>
                    <div class="an-number">${formatInteger(row.sin_seguimiento ?? row.total_gap ?? 0)}</div>
                    <div class="an-muted">casos sin traza</div>
                </td>
                <td>
                    <span class="an-coverage">${coverage}</span>
                    <div class="an-muted">brecha ${formatInteger(row.total_gap ?? 0)}</div>
                </td>
                <td>
                    <button
                        type="button"
                        class="an-primer-seguimiento-btn"
                        data-primer-seguimiento="1"
                        data-codigo="${escapeHtml(codigo)}"
                        data-desde="${escapeHtml(desde)}"
                        data-hasta="${escapeHtml(hasta)}"
                        data-evento="${escapeHtml(evento)}"
                        data-clasificacion="${escapeHtml(clasificacion)}"
                        onclick="window.openPrimerSeguimientoModal && window.openPrimerSeguimientoModal(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                    >${escapeHtml(primerSeguimiento)}</button>
                </td>
                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        data-trace-button="1"
                        data-codigo="${escapeHtml(codigo)}"
                        data-desde="${escapeHtml(desde)}"
                        data-hasta="${escapeHtml(hasta)}"
                        data-evento="${escapeHtml(evento)}"
                        data-clasificacion="${escapeHtml(clasificacion)}"
                        onclick="window.__ALTERACIONES_INDICADORES_TRACE__ && window.__ALTERACIONES_INDICADORES_TRACE__.openTrace(this.dataset.codigo, this.dataset.desde, this.dataset.hasta, this.dataset.evento, this.dataset.clasificacion)"
                    >Ver traza</button>
                </td>
            </tr>
        `;
    }

    function renderTable(rows) {
        if (!tableBody) {
            return;
        }

        if (!rows || !rows.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No hay datos para el rango seleccionado.</td>
                </tr>
            `;
        } else {
            tableBody.innerHTML = rows.map(renderRow).join('');
        }

        if (tableVisibleCount) {
            tableVisibleCount.textContent = formatInteger(rows?.length || 0);
        }
        if (tableTotalCount) {
            tableTotalCount.textContent = formatInteger(rows?.length || 0);
        }
    }

    function updateSummary(payload) {
        const totals = payload?.totales || {};
        const range = payload?.range || {};

        if (summaryRows) {
            summaryRows.textContent = formatInteger(payload?.rows?.length || 0);
        }
        if (summaryRange) {
            summaryRange.textContent = `${formatText(range.desde, state.currentFilters?.desde || '')} a ${formatText(range.hasta, state.currentFilters?.hasta || '')}`;
        }
        if (summaryEvent) {
            summaryEvent.textContent = state.currentFilters?.evento === 'all' ? 'Todos' : `Evento ${state.currentFilters?.evento}`;
        }
        if (summaryClasificacion) {
            summaryClasificacion.textContent = state.currentFilters?.clasificacion === 'all'
                ? 'Todas'
                : state.currentFilters?.clasificacion;
        }

        if (kpiPrestadores) kpiPrestadores.textContent = formatInteger(totals.prestadores ?? 0);
        if (kpiUsuarios) kpiUsuarios.textContent = formatInteger(totals.usuarios ?? 0);
        if (kpiAsignados) kpiAsignados.textContent = formatInteger(totals.asignados ?? 0);
        if (kpiSeguimientos) kpiSeguimientos.textContent = formatInteger(totals.seguimientos ?? 0);
        if (kpiCobertura) kpiCobertura.textContent = formatPercent(totals.cobertura ?? 0);
        if (kpiBrecha) kpiBrecha.textContent = formatInteger(totals.brecha ?? 0);
        if (kpiPrimerSeguimiento) {
            kpiPrimerSeguimiento.textContent = isBlank(totals.primer_seguimiento_promedio)
                ? 'Sin dato'
                : formatDays(totals.primer_seguimiento_promedio);
        }
        if (kpiClasificacionFinal) kpiClasificacionFinal.textContent = formatInteger(totals.casos_clasificacion_final ?? 0);
        if (kpiCalidadIncompletos) kpiCalidadIncompletos.textContent = formatInteger(totals.calidad_incompletos ?? 0);
        if (kpiCalidadDato) kpiCalidadDato.textContent = `${formatDecimal(totals.calidad_dato ?? 0)}%`;
        if (kpiCalidadInconsistentes) kpiCalidadInconsistentes.textContent = formatInteger(totals.calidad_inconsistentes ?? 0);
    }

    function collectTraceRecords(payload, eventFilter, key) {
        const trace = payload?.trace || {};
        const keys = eventFilter && eventFilter !== 'all' ? [String(eventFilter)] : Object.keys(trace);
        const records = [];

        keys.forEach((evento) => {
            const detail = trace[evento] || {};
            (detail[key] || []).forEach((record) => {
                records.push({
                    ...record,
                    evento,
                    evento_label: detail.label || `Evento ${evento}`,
                });
            });
        });

        return records;
    }

    function summarizeFollowUps(records) {
        const byCase = new Map();
        const patients = [];

        records.forEach((record) => {
            const caseId = String(record.case_id ?? '');
            const patient = formatText(record.paciente, 'Sin nombre');
            if (!byCase.has(caseId)) {
                byCase.set(caseId, []);
            }
            byCase.get(caseId).push(record);
            if (patient !== 'Sin nombre') {
                patients.push(patient);
            }
        });

        const uniquePatients = Array.from(new Set(patients));
        const firstDate = records.length ? records.slice().sort((a, b) => new Date(a.fecha_consulta || a.created_at || 0) - new Date(b.fecha_consulta || b.created_at || 0))[0] : null;
        const lastDate = records.length ? records.slice().sort((a, b) => new Date(b.fecha_consulta || b.created_at || 0) - new Date(a.fecha_consulta || a.created_at || 0))[0] : null;

        return {
            personas: byCase.size,
            total: records.length,
            pacientes: uniquePatients,
            pacientesLabel: uniquePatients.length ? uniquePatients.slice(0, 4).join(' · ') + (uniquePatients.length > 4 ? ` · +${uniquePatients.length - 4}` : '') : 'Sin pacientes',
            primeraFecha: firstDate ? formatDate(firstDate.fecha_consulta || firstDate.created_at) : 'Sin dato',
            ultimaFecha: lastDate ? formatDate(lastDate.fecha_consulta || lastDate.created_at) : 'Sin dato',
        };
    }

    function summarizeAssignments(records) {
        const patients = Array.from(new Set(records.map((item) => formatText(item.paciente, 'Sin nombre')).filter((name) => name !== 'Sin nombre')));
        const firstDate = records.length ? records.slice().sort((a, b) => new Date(a.fecha_asignacion || 0) - new Date(b.fecha_asignacion || 0))[0] : null;
        const lastDate = records.length ? records.slice().sort((a, b) => new Date(b.fecha_asignacion || 0) - new Date(a.fecha_asignacion || 0))[0] : null;

        return {
            personas: new Set(records.map((item) => String(item.case_id ?? ''))).size,
            total: records.length,
            pacientes: patients,
            pacientesLabel: patients.length ? patients.slice(0, 4).join(' · ') + (patients.length > 4 ? ` · +${patients.length - 4}` : '') : 'Sin pacientes',
            primeraFecha: firstDate ? formatDate(firstDate.fecha_asignacion) : 'Sin dato',
            ultimaFecha: lastDate ? formatDate(lastDate.fecha_asignacion) : 'Sin dato',
        };
    }

    function followUpRecordCard(record, index) {
        const fecha = formatDate(record.fecha_consulta || record.created_at);
        const paciente = formatText(record.paciente);
        const edad = formatText(record.edad);
        const usuario = formatText(record.usuario, 'Sin usuario');
        const estado = formatText(record.estado, '-');
        const clasificacion = formatText(record.clasificacion, 'SIN CLASIFICACION');
        const eventTone = eventToneClass(record.evento_label || record.evento);
        const peso = isBlank(record.peso_kilos) ? 'Sin dato' : `${formatDecimal(record.peso_kilos)} kg`;
        const talla = isBlank(record.talla_cm) ? 'Sin dato' : `${formatDecimal(record.talla_cm)} cm`;
        const puntajez = isBlank(record.puntajez) ? 'Sin dato' : formatDecimal(record.puntajez);
        const perimetro = isBlank(record.perimetro_braqueal) ? 'Sin dato' : formatDecimal(record.perimetro_braqueal);
        const proximo = formatDate(record.fecha_proximo_control);
        const observaciones = formatText(record.observaciones, 'Sin observaciones');

        return `
            <tr class="an-follow-table__row ${index === 0 ? 'is-first' : ''}">
                <td><strong>${escapeHtml(fecha)}</strong></td>
                <td><strong>${escapeHtml(paciente)}</strong></td>
                <td>${escapeHtml(edad)}</td>
                <td>${escapeHtml(usuario)}</td>
                <td><span class="an-status-chip">${escapeHtml(estado)}</span></td>
                <td><span class="an-class-chip ${escapeHtml(eventTone)}">${escapeHtml(clasificacion)}</span></td>
                <td>${escapeHtml(peso)}</td>
                <td>${escapeHtml(talla)}</td>
                <td>${escapeHtml(puntajez)}</td>
                <td>${escapeHtml(perimetro)}</td>
                <td>${escapeHtml(proximo)}</td>
            </tr>
            <tr class="an-follow-table__detail ${index === 0 ? 'is-first' : ''}">
                <td colspan="11">
                    <div class="an-follow-observation">
                        <span>Observaciones</span>
                        <strong>${escapeHtml(observaciones)}</strong>
                    </div>
                </td>
            </tr>
        `;
    }

    function assignmentRecordCard(record, index) {
        const eventTone = eventToneClass(record.evento_label || record.evento);
        const fields = [
            ['Paciente', formatText(record.paciente)],
            ['Edad', formatText(record.edad)],
            ['Tipo', formatText(record.tipo_identificacion, 'Sin tipo')],
            ['Documento', formatText(record.numero_identificacion, 'Sin numero')],
            ['Asignación', formatDate(record.fecha_asignacion)],
        ];

        return `
            <article class="an-follow-card ${index === 0 ? 'is-first' : ''}">
                <div class="an-follow-card__head">
                    <div class="an-follow-card__heading">
                        <div class="an-follow-card__eyebrow ${escapeHtml(eventTone)}">${escapeHtml(formatText(record.evento_label || record.evento, 'Evento'))}</div>
                        <h5 class="an-follow-card__title">${escapeHtml(formatText(record.paciente))}</h5>
                        <p class="an-follow-card__meta">
                            ${escapeHtml(formatText(record.tipo_identificacion, 'Sin tipo'))} ${escapeHtml(formatText(record.numero_identificacion, 'Sin numero'))}
                        </p>
                    </div>
                    <span class="an-pill an-pill--soft ${escapeHtml(eventTone)}">${escapeHtml(formatDate(record.fecha_asignacion))}</span>
                </div>
                <div class="an-follow-card__grid">
                    ${fields.map(([label, value]) => `
                        <article class="an-follow-chip">
                            <span>${escapeHtml(label)}</span>
                            <strong>${escapeHtml(value)}</strong>
                        </article>
                    `).join('')}
                </div>
            </article>
        `;
    }

    function renderSummaryChips(target, chips) {
        if (!target) {
            return;
        }

        target.innerHTML = chips.filter(Boolean).join('');
    }

    function eventSummaryHtml(summary) {
        return `
            <div class="an-modal-content">
                <article class="an-modal-card">
                    <span>Personas con seguimiento</span>
                    <strong>${escapeHtml(formatInteger(summary.personas))}</strong>
                </article>
                <article class="an-modal-card">
                    <span>Total registros</span>
                    <strong>${escapeHtml(formatInteger(summary.total))}</strong>
                </article>
                <article class="an-modal-card">
                    <span>Primera fecha</span>
                    <strong>${escapeHtml(summary.primeraFecha)}</strong>
                </article>
                <article class="an-modal-card">
                    <span>Ultima fecha</span>
                    <strong>${escapeHtml(summary.ultimaFecha)}</strong>
                </article>
                <article class="an-modal-card an-modal-card--wide">
                    <span>Pacientes incluidos</span>
                    <strong>${escapeHtml(summary.pacientesLabel)}</strong>
                </article>
            </div>
        `;
    }

    function renderFollowUpContent(records, summary, eventLabel, metaLabel) {
        if (!seguimientosContent) {
            return;
        }

        if (seguimientosModalMeta) {
            seguimientosModalMeta.textContent = `Personas con seguimiento: ${formatInteger(summary.personas)} | Registros: ${formatInteger(summary.total)} | ${metaLabel}`;
        }

        if (!records.length) {
            seguimientosContent.innerHTML = `
                <div class="an-empty-state">No hay seguimientos para el filtro actual.</div>
            `;
            setVisible(seguimientosContent, true);
            return;
        }

        seguimientosContent.innerHTML = `
            <div class="an-follow-layout an-modal-full">
                ${eventSummaryHtml(summary)}
                <div class="an-follow-context">
                    <div class="an-follow-context__item">
                        <span>Contexto</span>
                        <strong>${escapeHtml(metaLabel)}</strong>
                    </div>
                    <div class="an-follow-context__item">
                        <span>Evento</span>
                        <strong>${escapeHtml(eventLabel)}</strong>
                    </div>
                </div>
                <div class="an-follow-list">
                    <div class="an-follow-list__head">
                        <h4>Seguimientos detallados</h4>
                        <span class="an-pill an-pill--soft">${escapeHtml(formatInteger(records.length))} registros</span>
                    </div>
                    <div class="an-follow-scrollbar" data-follow-scrollbar="1" aria-label="Desplazamiento horizontal de la tabla">
                        <div class="an-follow-scrollbar__inner" data-follow-scrollbar-inner="1"></div>
                    </div>
                    <div class="an-follow-table-wrap">
                        <table class="an-follow-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Edad</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Clasificación</th>
                                    <th>Peso</th>
                                    <th>Talla</th>
                                    <th>Puntaje Z</th>
                                    <th>Perímetro</th>
                                    <th>Próx. control</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${records.map((record, index) => followUpRecordCard(record, index)).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        setVisible(seguimientosContent, true);
        syncFollowTableScrollbar();
    }

    function syncFollowTableScrollbar() {
        if (!seguimientosContent) {
            return;
        }

        const scrollbar = seguimientosContent.querySelector('[data-follow-scrollbar="1"]');
        const scrollbarInner = seguimientosContent.querySelector('[data-follow-scrollbar-inner="1"]');
        const tableWrap = seguimientosContent.querySelector('.an-follow-table-wrap');
        const table = seguimientosContent.querySelector('.an-follow-table');

        if (!scrollbar || !scrollbarInner || !tableWrap || !table) {
            return;
        }

        const updateInnerWidth = () => {
            const width = Math.max(table.scrollWidth, tableWrap.clientWidth + 1);
            scrollbarInner.style.width = `${width}px`;
        };

        const syncFromBar = () => {
            tableWrap.scrollLeft = scrollbar.scrollLeft;
        };

        const syncFromTable = () => {
            scrollbar.scrollLeft = tableWrap.scrollLeft;
        };

        updateInnerWidth();
        scrollbar.scrollLeft = tableWrap.scrollLeft;

        if (!scrollbar.dataset.syncBound) {
            scrollbar.addEventListener('scroll', syncFromBar, { passive: true });
            scrollbar.dataset.syncBound = '1';
        }

        if (!tableWrap.dataset.syncBound) {
            tableWrap.addEventListener('scroll', syncFromTable, { passive: true });
            tableWrap.dataset.syncBound = '1';
        }

        window.requestAnimationFrame(updateInnerWidth);
        window.addEventListener('resize', updateInnerWidth, { passive: true, once: true });
    }

    function renderAssignmentContent(records, summary, eventLabel, metaLabel) {
        if (!asignadosContent) {
            return;
        }

        if (asignadosModalMeta) {
            asignadosModalMeta.textContent = `Personas asignadas: ${formatInteger(summary.personas)} | Registros: ${formatInteger(summary.total)} | ${metaLabel}`;
        }

        if (!records.length) {
            asignadosContent.innerHTML = `
                <div class="an-empty-state">No hay asignaciones para el filtro actual.</div>
            `;
            setVisible(asignadosContent, true);
            return;
        }

        asignadosContent.innerHTML = `
            <div class="an-follow-layout an-modal-full">
                <div class="an-follow-summary">
                    <article class="an-follow-summary__card">
                        <span>Asignados</span>
                        <strong>${escapeHtml(formatInteger(summary.total))}</strong>
                        <small>casos asignados en el rango</small>
                    </article>
                    <article class="an-follow-summary__card">
                        <span>Personas</span>
                        <strong>${escapeHtml(formatInteger(summary.personas))}</strong>
                        <small>asignaciones únicas visibles</small>
                    </article>
                    <article class="an-follow-summary__card">
                        <span>Primera fecha</span>
                        <strong>${escapeHtml(summary.primeraFecha)}</strong>
                        <small>primer caso asignado</small>
                    </article>
                    <article class="an-follow-summary__card">
                        <span>Última fecha</span>
                        <strong>${escapeHtml(summary.ultimaFecha)}</strong>
                        <small>último caso asignado</small>
                    </article>
                    <article class="an-follow-summary__card an-follow-summary__card--wide">
                        <span>Pacientes incluidos</span>
                        <strong>${escapeHtml(summary.pacientesLabel)}</strong>
                        <small>resumen de personas asignadas</small>
                    </article>
                </div>
                <div class="an-follow-context">
                    <div class="an-follow-context__item">
                        <span>Contexto</span>
                        <strong>${escapeHtml(metaLabel)}</strong>
                    </div>
                    <div class="an-follow-context__item">
                        <span>Evento</span>
                        <strong>${escapeHtml(eventLabel)}</strong>
                    </div>
                </div>
                <div class="an-follow-list">
                    <div class="an-follow-list__head">
                        <h4>Asignaciones detalladas</h4>
                        <span class="an-pill an-pill--soft">${escapeHtml(formatInteger(records.length))} registros</span>
                    </div>
                    ${records.map((record, index) => assignmentRecordCard(record, index)).join('')}
                </div>
            </div>
        `;
        setVisible(asignadosContent, true);
    }

    function renderFirstFollowupContent(records, summary, eventLabel, metaLabel) {
        if (!primerSeguimientoContent) {
            return;
        }

        if (primerSeguimientoModalMeta) {
            primerSeguimientoModalMeta.textContent = `Casos con seguimiento: ${formatInteger(summary.personas)} | Registros: ${formatInteger(summary.total)} | ${metaLabel}`;
        }

        if (!records.length) {
            primerSeguimientoContent.innerHTML = `
                <div class="an-empty-state">No hay seguimientos para calcular el primer seguimiento.</div>
            `;
            setVisible(primerSeguimientoContent, true);
            return;
        }

        const sorted = records.slice().sort((a, b) => new Date(a.fecha_consulta || a.created_at || 0) - new Date(b.fecha_consulta || b.created_at || 0));
        const first = sorted[0];
        const assignmentDate = first.fecha_asignacion || null;
        const firstFollowUpDate = first.fecha_consulta || first.created_at || null;
        const days = assignmentDate && firstFollowUpDate ? Math.round((new Date(firstFollowUpDate) - new Date(assignmentDate)) / 86400000) : null;

        primerSeguimientoContent.innerHTML = `
            <div class="an-follow-layout an-modal-full">
                <div class="an-follow-summary">
                    <article class="an-follow-summary__card">
                        <span>Primer seguimiento</span>
                        <strong>${escapeHtml(days === null ? 'Sin dato' : `${days} días`)}</strong>
                        <small>tiempo entre asignación y primer control</small>
                    </article>
                    <article class="an-follow-summary__card">
                        <span>Paciente</span>
                        <strong>${escapeHtml(formatText(first.paciente))}</strong>
                        <small>caso consultado</small>
                    </article>
                    <article class="an-follow-summary__card">
                        <span>Asignado</span>
                        <strong>${escapeHtml(formatDate(assignmentDate))}</strong>
                        <small>fecha de asignación</small>
                    </article>
                    <article class="an-follow-summary__card">
                        <span>Primer seguimiento</span>
                        <strong>${escapeHtml(formatDate(firstFollowUpDate))}</strong>
                        <small>primer registro del periodo</small>
                    </article>
                    <article class="an-follow-summary__card an-follow-summary__card--wide">
                        <span>Contexto</span>
                        <strong>${escapeHtml(metaLabel)}</strong>
                        <small>rango y clasificación seleccionados</small>
                    </article>
                </div>
                <div class="an-follow-context">
                    <div class="an-follow-context__item">
                        <span>Evento</span>
                        <strong>${escapeHtml(eventLabel)}</strong>
                    </div>
                    <div class="an-follow-context__item">
                        <span>Registro base</span>
                        <strong>${escapeHtml(first ? formatText(first.evento_label, 'Evento') : 'Sin dato')}</strong>
                    </div>
                </div>
                <div class="an-follow-list">
                    <div class="an-follow-list__head">
                        <h4>Primer seguimiento detallado</h4>
                        <span class="an-pill an-pill--soft">1 registro</span>
                    </div>
                    ${first ? followUpRecordCard(first, 0) : ''}
                </div>
            </div>
        `;
        setVisible(primerSeguimientoContent, true);
    }

    function hideAllModalStates() {
        [primerSeguimientoLoading, primerSeguimientoError, primerSeguimientoContent,
            seguimientosLoading, seguimientosError, seguimientosContent,
            asignadosLoading, asignadosError, asignadosContent].forEach((node) => {
            if (node) {
                node.classList.add('d-none');
            }
        });
    }

    function showModalLoading(kind) {
        hideAllModalStates();
        if (kind === 'primer') setVisible(primerSeguimientoLoading, true);
        if (kind === 'seguimientos') setVisible(seguimientosLoading, true);
        if (kind === 'asignados') setVisible(asignadosLoading, true);
        if (kind === 'trace' && traceLoading) {
            traceLoading.classList.remove('d-none');
        }
    }

    function showModalError(kind, message) {
        hideAllModalStates();
        if (kind === 'primer') {
            setError(primerSeguimientoError, primerSeguimientoErrorText, message);
        } else if (kind === 'seguimientos') {
            setError(seguimientosError, seguimientosErrorText, message);
        } else if (kind === 'asignados') {
            setError(asignadosError, asignadosErrorText, message);
        }
    }

    function activeEventsFromTrace(payload, eventFilter) {
        const trace = payload?.trace || {};
        if (eventFilter && eventFilter !== 'all') {
            return [String(eventFilter)];
        }
        return Object.keys(trace);
    }

    function traceSummaryLabel(payload, eventFilter) {
        const range = payload?.range || {};
        const eventLabel = eventFilter && eventFilter !== 'all' ? `Evento ${eventFilter}` : 'Todos los eventos';
        return `Rango ${formatText(range.desde)} a ${formatText(range.hasta)} · ${eventLabel}`;
    }

    function renderTraceSection(evento, detail) {
        const asignados = Array.isArray(detail?.asignaciones) ? detail.asignaciones : [];
        const sinSeguimiento = Array.isArray(detail?.sin_seguimiento_detalle) ? detail.sin_seguimiento_detalle : [];
        const seguimientos = Array.isArray(detail?.seguimientos_detalle) ? detail.seguimientos_detalle : [];
        const cards = [
            ['Asignados', detail?.asignados ?? 0],
            ['Seguimientos', detail?.seguimientos ?? 0],
            ['Sin seguimiento', detail?.sin_seguimiento ?? 0],
            ['1er seguimiento', isBlank(detail?.primer_seguimiento_promedio) ? 'Sin dato' : formatDays(detail.primer_seguimiento_promedio)],
            ['Calidad dato', isBlank(detail?.calidad_dato) ? 'Sin dato' : `${formatDecimal(detail.calidad_dato)}%`],
        ];

        return `
            <section class="an-trace-section">
                <div class="an-trace-section__head">
                    <h4>${escapeHtml(detail?.label || `Evento ${evento}`)}</h4>
                    <span class="an-pill an-pill--soft">${escapeHtml(formatInteger(detail?.seguimientos ?? 0))} seguimientos</span>
                </div>
                <div class="an-modal-content">
                    ${cards.map(([label, value]) => `
                        <article class="an-modal-card">
                            <span>${escapeHtml(label)}</span>
                            <strong>${escapeHtml(String(value))}</strong>
                        </article>
                    `).join('')}
                    <article class="an-modal-card an-modal-card--wide">
                        <span>Asignaciones</span>
                        <strong>${escapeHtml(formatInteger(asignados.length))} registros</strong>
                    </article>
                    <article class="an-modal-card an-modal-card--wide">
                        <span>Sin seguimiento</span>
                        <strong>${escapeHtml(formatInteger(sinSeguimiento.length))} registros</strong>
                    </article>
                </div>
                <div class="an-record-list" style="margin-top:16px;">
                    <div class="an-trace-subtitle">Seguimientos detallados</div>
                    ${seguimientos.length ? seguimientos.map((item, index) => followUpRecordCard(item, index)).join('') : '<div class="an-empty-state">Sin detalles de seguimiento.</div>'}
                </div>
            </section>
        `;
    }

    async function loadTrace(kind, codigo, desde, hasta, evento, clasificacion) {
        const params = new URLSearchParams();
        params.set('codigo', codigo);
        params.set('desde', desde);
        params.set('hasta', hasta);
        params.set('evento', evento || 'all');
        params.set('clasificacion', clasificacion || 'all');
        return fetchJson('/alteraciones-nutricionales/indicadores/trace', params);
    }

    async function openTrace(codigo, desde, hasta, evento, clasificacion) {
        state.traceContext = {
            codigo: String(codigo || ''),
            desde: desde || '',
            hasta: hasta || '',
            evento: evento || 'all',
            clasificacion: clasificacion || 'all',
        };

        if (traceEventFilter) {
            traceEventFilter.value = state.traceContext.evento;
        }

        openDrawer();
        showModalLoading('trace');
        clearError(traceError);
        if (traceEmpty) {
            traceEmpty.classList.add('d-none');
        }
        if (traceContent) {
            traceContent.classList.add('d-none');
            traceContent.innerHTML = '';
        }
        if (traceTitle) {
            traceTitle.textContent = `Codigo ${state.traceContext.codigo}`;
        }
        if (traceMeta) {
            traceMeta.textContent = traceSummaryLabel(state.traceContext, state.traceContext.evento);
        }
        if (traceChipRange) traceChipRange.textContent = `${state.traceContext.desde} a ${state.traceContext.hasta}`;
        if (traceChipEvent) traceChipEvent.textContent = state.traceContext.evento === 'all' ? 'Todos' : `Evento ${state.traceContext.evento}`;
        if (traceChipClasificacion) traceChipClasificacion.textContent = state.traceContext.clasificacion === 'all' ? 'Todas' : state.traceContext.clasificacion;
        if (traceChipCode) traceChipCode.textContent = state.traceContext.codigo;
        if (traceSummaryHint) {
            traceSummaryHint.textContent = 'Cargando trazabilidad completa sin recargar la pagina.';
        }

        try {
            const payload = await loadTrace('trace', state.traceContext.codigo, state.traceContext.desde, state.traceContext.hasta, state.traceContext.evento, state.traceContext.clasificacion);
            state.tracePayload = payload;
            renderTraceDrawer(payload, state.traceContext.evento);
        } catch (error) {
            showModalError('trace', error.message || 'No fue posible cargar la traza.');
        } finally {
            if (traceLoading) {
                traceLoading.classList.add('d-none');
            }
        }
    }

    function renderTraceDrawer(payload, eventFilter) {
        if (!traceContent) {
            return;
        }

        const events = activeEventsFromTrace(payload, eventFilter);
        const sections = events
            .map((evento) => renderTraceSection(evento, payload?.trace?.[evento]))
            .filter(Boolean);

        if (!sections.length) {
            if (traceEmpty) {
                traceEmpty.classList.remove('d-none');
                traceEmpty.textContent = 'No hay informacion para mostrar con el filtro actual.';
            }
            traceContent.classList.add('d-none');
            traceContent.innerHTML = '';
            return;
        }

        traceContent.innerHTML = sections.join('');
        traceContent.classList.remove('d-none');
        if (traceEmpty) {
            traceEmpty.classList.add('d-none');
        }
        if (traceSummaryHint) {
            traceSummaryHint.textContent = `Mostrando ${events.length} evento(s) con el rango seleccionado.`;
        }
    }

    async function refreshTrace() {
        if (!state.traceContext) {
            return;
        }

        const eventFilter = traceEventFilter?.value || state.traceContext.evento || 'all';
        showModalLoading('trace');
        clearError(traceError);
        if (traceLoading) {
            traceLoading.classList.remove('d-none');
        }

        try {
            const payload = await loadTrace('trace', state.traceContext.codigo, state.traceContext.desde, state.traceContext.hasta, eventFilter, state.traceContext.clasificacion);
            state.tracePayload = payload;
            state.traceContext.evento = eventFilter;
            if (traceMeta) {
                traceMeta.textContent = traceSummaryLabel(state.traceContext, eventFilter);
            }
            if (traceChipEvent) traceChipEvent.textContent = eventFilter === 'all' ? 'Todos' : `Evento ${eventFilter}`;
            renderTraceDrawer(payload, eventFilter);
        } catch (error) {
            showModalError('trace', error.message || 'No fue posible recargar la traza.');
        } finally {
            if (traceLoading) {
                traceLoading.classList.add('d-none');
            }
        }
    }

    async function openSeguimientosModal(codigo, desde, hasta, evento, clasificacion) {
        const paramsEvent = evento || 'all';
        showModalLoading('seguimientos');
        openModal(seguimientosModal);
        clearError(seguimientosError);
        if (seguimientosModalTitle) {
            seguimientosModalTitle.textContent = 'Seguimientos por paciente';
        }
        if (seguimientosModalMeta) {
            seguimientosModalMeta.textContent = `Codigo ${codigo} | Rango ${desde} a ${hasta} | ${paramsEvent === 'all' ? 'Todos los eventos' : `Evento ${paramsEvent}`} | ${clasificacion === 'all' ? 'Todas las clasificaciones' : clasificacion}`;
        }

        try {
            const payload = await loadTrace('seguimientos', codigo, desde, hasta, paramsEvent, clasificacion);
            const records = collectTraceRecords(payload, paramsEvent, 'seguimientos_detalle').slice().sort((a, b) => new Date(b.fecha_consulta || b.created_at || 0) - new Date(a.fecha_consulta || a.created_at || 0));
            const summary = summarizeFollowUps(records);
            const eventLabel = paramsEvent === 'all' ? 'Todos los eventos' : `Evento ${paramsEvent}`;
            const metaLabel = `Rango ${desde} a ${hasta} · ${clasificacion === 'all' ? 'Todas las clasificaciones' : clasificacion}`;

            renderFollowUpContent(records, summary, eventLabel, metaLabel);
        } catch (error) {
            showModalError('seguimientos', error.message || 'No fue posible cargar los seguimientos.');
        } finally {
            if (seguimientosLoading) {
                seguimientosLoading.classList.add('d-none');
            }
        }
    }

    async function openAsignadosModal(codigo, desde, hasta, evento, clasificacion) {
        const paramsEvent = evento || 'all';
        showModalLoading('asignados');
        openModal(asignadosModal);
        clearError(asignadosError);
        if (asignadosModalTitle) {
            asignadosModalTitle.textContent = 'Asignaciones por paciente';
        }
        if (asignadosModalMeta) {
            asignadosModalMeta.textContent = `Codigo ${codigo} · Rango ${desde} a ${hasta} · ${paramsEvent === 'all' ? 'Todos los eventos' : `Evento ${paramsEvent}`} · ${clasificacion === 'all' ? 'Todas las clasificaciones' : clasificacion}`;
        }

        try {
            const payload = await loadTrace('asignados', codigo, desde, hasta, paramsEvent, clasificacion);
            const records = collectTraceRecords(payload, paramsEvent, 'asignaciones').slice().sort((a, b) => new Date(b.fecha_asignacion || 0) - new Date(a.fecha_asignacion || 0));
            const summary = summarizeAssignments(records);
            const eventLabel = paramsEvent === 'all' ? 'Todos los eventos' : `Evento ${paramsEvent}`;
            const metaLabel = `Rango ${desde} a ${hasta} · ${clasificacion === 'all' ? 'Todas las clasificaciones' : clasificacion}`;

            renderAssignmentContent(records, summary, eventLabel, metaLabel);
        } catch (error) {
            showModalError('asignados', error.message || 'No fue posible cargar las asignaciones.');
        } finally {
            if (asignadosLoading) {
                asignadosLoading.classList.add('d-none');
            }
        }
    }

    async function openPrimerSeguimientoModal(codigo, desde, hasta, evento, clasificacion) {
        const paramsEvent = evento || 'all';
        showModalLoading('primer');
        openModal(primerSeguimientoModal);
        clearError(primerSeguimientoError);
        if (primerSeguimientoModalTitle) {
            primerSeguimientoModalTitle.textContent = 'Primer seguimiento por paciente';
        }
        if (primerSeguimientoModalMeta) {
            primerSeguimientoModalMeta.textContent = `Codigo ${codigo} · Rango ${desde} a ${hasta} · ${paramsEvent === 'all' ? 'Todos los eventos' : `Evento ${paramsEvent}`} · ${clasificacion === 'all' ? 'Todas las clasificaciones' : clasificacion}`;
        }

        try {
            const payload = await loadTrace('primer', codigo, desde, hasta, paramsEvent, clasificacion);
            const records = collectTraceRecords(payload, paramsEvent, 'seguimientos_detalle').slice();
            const summary = summarizeFollowUps(records);
            const eventLabel = paramsEvent === 'all' ? 'Todos los eventos' : `Evento ${paramsEvent}`;
            const metaLabel = `Rango ${desde} a ${hasta} · ${clasificacion === 'all' ? 'Todas las clasificaciones' : clasificacion}`;

            renderFirstFollowupContent(records, summary, eventLabel, metaLabel);
        } catch (error) {
            showModalError('primer', error.message || 'No fue posible cargar el primer seguimiento.');
        } finally {
            if (primerSeguimientoLoading) {
                primerSeguimientoLoading.classList.add('d-none');
            }
        }
    }

    async function refreshDashboard() {
        if (!filtersForm) {
            return;
        }

        state.currentFilters = currentFilters();
        setTableLoading(true);
        clearError(traceError);

        try {
            const payload = await fetchJson('/alteraciones-nutricionales/indicadores/data', currentParams(state.currentFilters));
            updateSummary(payload);
            renderTable(payload.rows || []);
        } catch (error) {
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">${escapeHtml(error.message || 'No fue posible actualizar la tabla.')}</td>
                    </tr>
                `;
            }
        } finally {
            setTableLoading(false);
        }
    }

    function scheduleDashboardRefresh() {
        if (state.dashboardTimer) {
            window.clearTimeout(state.dashboardTimer);
        }

        state.dashboardTimer = window.setTimeout(() => {
            refreshDashboard();
        }, 350);
    }

    function bindFilterEvents() {
        if (!filtersForm) {
            return;
        }

        const inputs = filtersForm.querySelectorAll('input, select');
        inputs.forEach((input) => {
            input.addEventListener('change', scheduleDashboardRefresh);
            input.addEventListener('input', scheduleDashboardRefresh);
        });

        filtersForm.addEventListener('submit', (event) => {
            event.preventDefault();
            refreshDashboard();
        });
    }

    function bindModalClose(modal, closeButton) {
        if (closeButton) {
            closeButton.addEventListener('click', () => closeModal(modal));
        }
        if (modal) {
            modal.addEventListener('click', (event) => {
                const target = event.target;
                if (target?.matches?.('[data-modal-close="1"]')) {
                    closeModal(modal);
                }
            });
        }
    }

    function bindDelegatedButtons() {
        document.addEventListener('click', (event) => {
            const traceButton = event.target.closest('[data-trace-button="1"]');
            if (traceButton) {
                event.preventDefault();
                openTrace(
                    traceButton.dataset.codigo,
                    traceButton.dataset.desde,
                    traceButton.dataset.hasta,
                    traceButton.dataset.evento,
                    traceButton.dataset.clasificacion,
                );
                return;
            }

            const asignadosButton = event.target.closest('[data-asignados="1"]');
            if (asignadosButton) {
                event.preventDefault();
                openAsignadosModal(
                    asignadosButton.dataset.codigo,
                    asignadosButton.dataset.desde,
                    asignadosButton.dataset.hasta,
                    asignadosButton.dataset.evento,
                    asignadosButton.dataset.clasificacion,
                );
                return;
            }

            const seguimientosButton = event.target.closest('[data-seguimientos="1"]');
            if (seguimientosButton) {
                event.preventDefault();
                openSeguimientosModal(
                    seguimientosButton.dataset.codigo,
                    seguimientosButton.dataset.desde,
                    seguimientosButton.dataset.hasta,
                    seguimientosButton.dataset.evento,
                    seguimientosButton.dataset.clasificacion,
                );
                return;
            }

            const primerButton = event.target.closest('[data-primer-seguimiento="1"]');
            if (primerButton) {
                event.preventDefault();
                openPrimerSeguimientoModal(
                    primerButton.dataset.codigo,
                    primerButton.dataset.desde,
                    primerButton.dataset.hasta,
                    primerButton.dataset.evento,
                    primerButton.dataset.clasificacion,
                );
            }
        });
    }

    function bindTraceControls() {
        if (traceMask) {
            traceMask.addEventListener('click', closeDrawer);
        }
        if (traceClose) {
            traceClose.addEventListener('click', closeDrawer);
        }
        if (traceRefresh) {
            traceRefresh.addEventListener('click', (event) => {
                event.preventDefault();
                refreshTrace();
            });
        }
        if (traceEventFilter) {
            traceEventFilter.addEventListener('change', () => {
                if (state.traceContext) {
                    state.traceContext.evento = traceEventFilter.value;
                    if (traceMeta) {
                        traceMeta.textContent = traceSummaryLabel(state.traceContext, traceEventFilter.value);
                    }
                }
            });
        }
    }

    function bindTraceShortcutState() {
        window.__ALTERACIONES_INDICADORES_TRACE__ = {
            openTrace,
            closeTrace: closeDrawer,
            refreshTrace,
        };
        window.openAsignadosModal = openAsignadosModal;
        window.closeAsignadosModal = () => closeModal(asignadosModal);
        window.openSeguimientosModal = openSeguimientosModal;
        window.closeSeguimientosModal = () => closeModal(seguimientosModal);
        window.openPrimerSeguimientoModal = openPrimerSeguimientoModal;
        window.closePrimerSeguimientoModal = () => closeModal(primerSeguimientoModal);
    }

    function boot() {
        state.basePath = initBasePath();
        state.currentFilters = currentFilters();

        bindFilterEvents();
        bindTraceControls();
        bindTraceShortcutState();
        bindModalClose(primerSeguimientoModal, primerSeguimientoClose);
        bindModalClose(seguimientosModal, seguimientosClose);
        bindModalClose(asignadosModal, asignadosClose);

        if (tableBody && !tableBody.children.length) {
            refreshDashboard();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    function summarizeFollowUps(records) {
        const byCase = new Map();
        const sortValue = (record) => {
            const value = record?.fecha_consulta || record?.created_at || null;
            const date = new Date(value || 0);
            return Number.isNaN(date.getTime()) ? 0 : date.getTime();
        };

        records.forEach((record) => {
            const caseId = String(record.case_id ?? '');
            const patient = formatText(record.paciente, 'Sin nombre');
            const key = caseId !== '' ? caseId : `${patient}|${formatText(record.codigo, '')}`;

            if (!byCase.has(key)) {
                byCase.set(key, {
                    caseId: caseId !== '' ? caseId : null,
                    codigo: formatText(record.codigo, 'Sin codigo'),
                    paciente: patient,
                    edad: formatText(record.edad, 'Sin dato'),
                    registros: 0,
                    primeraFechaValue: null,
                    primeraFecha: 'Sin dato',
                    ultimaFechaValue: null,
                    ultimaFecha: 'Sin dato',
                    usuarios: new Set(),
                    clasificaciones: new Set(),
                    eventos: new Set(),
                });
            }

            const current = byCase.get(key);
            current.registros += 1;
            const dateValue = sortValue(record);
            const readableDate = formatDate(record.fecha_consulta || record.created_at);

            if (current.primeraFechaValue === null || dateValue < current.primeraFechaValue) {
                current.primeraFechaValue = dateValue;
                current.primeraFecha = readableDate;
            }

            if (current.ultimaFechaValue === null || dateValue > current.ultimaFechaValue) {
                current.ultimaFechaValue = dateValue;
                current.ultimaFecha = readableDate;
            }

            if (!isBlank(record.usuario)) {
                current.usuarios.add(formatText(record.usuario, 'Sin usuario'));
            }
            if (!isBlank(record.clasificacion)) {
                current.clasificaciones.add(formatText(record.clasificacion, 'SIN CLASIFICACION'));
            }
            if (!isBlank(record.evento_label)) {
                current.eventos.add(formatText(record.evento_label, 'Evento'));
            }
        });

        const pacientes = Array.from(byCase.values())
            .sort((a, b) => (b.ultimaFechaValue ?? 0) - (a.ultimaFechaValue ?? 0))
            .map((item) => ({
                ...item,
                usuarios: Array.from(item.usuarios),
                clasificaciones: Array.from(item.clasificaciones),
                eventos: Array.from(item.eventos),
            }));
        const uniquePatients = pacientes.map((item) => item.paciente).filter((patient) => patient !== 'Sin nombre');
        const firstDate = records.length ? records.slice().sort((a, b) => sortValue(a) - sortValue(b))[0] : null;
        const lastDate = records.length ? records.slice().sort((a, b) => sortValue(b) - sortValue(a))[0] : null;

        return {
            personas: byCase.size,
            total: records.length,
            pacientes,
            pacientesLabel: uniquePatients.length ? uniquePatients.slice(0, 4).join(' · ') + (uniquePatients.length > 4 ? ` · +${uniquePatients.length - 4}` : '') : 'Sin pacientes',
            primeraFecha: firstDate ? formatDate(firstDate.fecha_consulta || firstDate.created_at) : 'Sin dato',
            ultimaFecha: lastDate ? formatDate(lastDate.fecha_consulta || lastDate.created_at) : 'Sin dato',
        };
    }

    function patientInitials(name) {
        const parts = formatText(name, 'Sin nombre')
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2);

        if (!parts.length) {
            return 'SN';
        }

        return parts.map((part) => part.charAt(0)).join('').toUpperCase();
    }

    function eventCode(value) {
        const text = formatText(value, '');
        const match = text.match(/\b(113|412|114)\b/);
        return match ? match[1] : text;
    }

    function eventLabel(value) {
        const code = eventCode(value);
        return code ? `Evento ${code}` : 'Evento';
    }

    function eventToneClass(value) {
        const code = eventCode(value);
        if (code === '113') return 'is-event-113';
        if (code === '412') return 'is-event-412';
        if (code === '114') return 'is-event-114';
        return 'is-event-generic';
    }

    function followUpPatientCard(patient, index) {
        const initials = patientInitials(patient.paciente);
        const code = formatText(patient.codigo, 'Sin codigo');
        const caseLabel = patient.caseId !== null && patient.caseId !== undefined ? `Caso ${patient.caseId}` : 'Caso sin identificar';
        const eventosLabel = patient.eventos.length ? patient.eventos.slice(0, 2).join(' · ') + (patient.eventos.length > 2 ? ` · +${patient.eventos.length - 2}` : '') : 'Sin evento';
        const usersLabel = patient.usuarios.length ? patient.usuarios.slice(0, 2).join(' · ') + (patient.usuarios.length > 2 ? ` · +${patient.usuarios.length - 2}` : '') : 'Sin usuario';
        const clasifLabel = patient.clasificaciones.length ? patient.clasificaciones.slice(0, 2).join(' · ') + (patient.clasificaciones.length > 2 ? ` · +${patient.clasificaciones.length - 2}` : '') : 'Sin clasificacion';

        return `
            <article class="an-patient-card ${index === 0 ? 'is-first' : ''}">
                <div class="an-patient-card__avatar" aria-hidden="true">${escapeHtml(initials)}</div>
                <div class="an-patient-card__body">
                    <div class="an-patient-card__top">
                        <div class="an-patient-card__heading">
                            <div class="an-patient-card__eyebrow">Paciente incluido</div>
                            <h5>${escapeHtml(patient.paciente)}</h5>
                            <p>${escapeHtml(code)} · ${escapeHtml(caseLabel)}</p>
                        </div>
                        <span class="an-pill an-pill--soft">${escapeHtml(formatInteger(patient.registros))} registros</span>
                    </div>
                    <div class="an-patient-card__grid">
                        <div>
                            <span>Edad</span>
                            <strong>${escapeHtml(patient.edad)}</strong>
                        </div>
                        <div>
                            <span>Primera fecha</span>
                            <strong>${escapeHtml(patient.primeraFecha)}</strong>
                        </div>
                        <div>
                            <span>Ultima fecha</span>
                            <strong>${escapeHtml(patient.ultimaFecha)}</strong>
                        </div>
                        <div>
                            <span>Usuario</span>
                            <strong>${escapeHtml(usersLabel)}</strong>
                        </div>
                        <div class="an-patient-card__grid--wide">
                            <span>Evento(s)</span>
                            <strong>${escapeHtml(eventosLabel)}</strong>
                        </div>
                        <div class="an-patient-card__grid--wide">
                            <span>Clasificaciones vistas</span>
                            <strong>${escapeHtml(clasifLabel)}</strong>
                        </div>
                    </div>
                </div>
            </article>
        `;
    }

    function followUpRecordCard(record, index) {
        const fecha = formatDate(record.fecha_consulta || record.created_at);
        const paciente = formatText(record.paciente);
        const edad = formatText(record.edad);
        const usuario = formatText(record.usuario, 'Sin usuario');
        const estado = formatText(record.estado, '-');
        const clasificacion = formatText(record.clasificacion, 'SIN CLASIFICACION');
        const evento = formatText(record.evento_label || record.evento, 'Evento');
        const eventTone = eventToneClass(evento);
        const codigo = formatText(record.codigo, 'Sin codigo');
        const caso = formatText(record.case_id, 'Sin caso');
        const peso = isBlank(record.peso_kilos) ? 'Sin dato' : `${formatDecimal(record.peso_kilos)} kg`;
        const talla = isBlank(record.talla_cm) ? 'Sin dato' : `${formatDecimal(record.talla_cm)} cm`;
        const puntajez = isBlank(record.puntajez) ? 'Sin dato' : formatDecimal(record.puntajez);
        const perimetro = isBlank(record.perimetro_braqueal) ? 'Sin dato' : formatDecimal(record.perimetro_braqueal);
        const proximo = formatDate(record.fecha_proximo_control);
        const observaciones = formatText(record.observaciones, 'Sin observaciones');
        const medicamento = formatText(record.medicamento, 'Sin dato');

        return `
            <article class="an-follow-card ${index === 0 ? 'is-first' : ''}">
                <div class="an-follow-card__head">
                    <div class="an-follow-card__identity">
                        <div class="an-follow-card__eyebrow">${escapeHtml(evento)} · Registro ${escapeHtml(formatInteger(index + 1))}</div>
                        <h5>${escapeHtml(paciente)}</h5>
                        <p>Codigo ${escapeHtml(codigo)} · Caso ${escapeHtml(caso)} · Usuario ${escapeHtml(usuario)}</p>
                    </div>
                    <div class="an-follow-card__meta">
                        <span class="an-pill an-pill--soft">${escapeHtml(fecha)}</span>
                        <span class="an-status-chip">${escapeHtml(estado)}</span>
                        <span class="an-class-chip ${escapeHtml(eventTone)}">${escapeHtml(evento)}</span>
                    </div>
                </div>
                <div class="an-follow-card__chips">
                    <span>Clasificacion: ${escapeHtml(clasificacion)}</span>
                    <span class="an-event-chip ${escapeHtml(eventTone)}">${escapeHtml(evento)}</span>
                    <span>Edad: ${escapeHtml(edad)}</span>
                    <span>Peso: ${escapeHtml(peso)}</span>
                    <span>Talla: ${escapeHtml(talla)}</span>
                </div>
                <div class="an-follow-card__grid">
                    <div>
                        <label>Puntaje Z</label>
                        <strong>${escapeHtml(puntajez)}</strong>
                    </div>
                    <div>
                        <label>Perimetro</label>
                        <strong>${escapeHtml(perimetro)}</strong>
                    </div>
                    <div>
                        <label>Prox. control</label>
                        <strong>${escapeHtml(proximo)}</strong>
                    </div>
                    <div>
                        <label>Medicamento</label>
                        <strong>${escapeHtml(medicamento)}</strong>
                    </div>
                </div>
                <details class="an-follow-card__notes">
                    <summary>Observaciones</summary>
                    <div class="an-follow-card__notes-body">${escapeHtml(observaciones)}</div>
                </details>
            </article>
        `;
    }

    function renderFollowUpContent(records, summary, eventLabel, metaLabel) {
        if (!seguimientosContent) {
            return;
        }
        const eventTone = eventToneClass(eventLabel);

        if (seguimientosModalMeta) {
            seguimientosModalMeta.textContent = `Personas con seguimiento: ${formatInteger(summary.personas)} | Registros: ${formatInteger(summary.total)} | ${metaLabel}`;
        }

        if (!records.length) {
            seguimientosContent.innerHTML = `
                <div class="an-empty-state">No hay seguimientos para el filtro actual.</div>
            `;
            setVisible(seguimientosContent, true);
            return;
        }

        seguimientosContent.innerHTML = `
            <div class="an-follow-layout an-modal-full">
                <section class="an-follow-hero">
                    <div class="an-follow-hero__copy">
                        <div class="an-follow-hero__eyebrow">Lectura por paciente</div>
                        <h4>Seguimientos ordenados para leer rapido y sin perder contexto</h4>
                        <p>Cada tarjeta destaca quien es el paciente, a que caso pertenece, quien registro la informacion y que observaciones la acompañan.</p>
                    </div>
                    <div class="an-follow-hero__stats">
                        <article>
                            <span>Personas</span>
                            <strong>${escapeHtml(formatInteger(summary.personas))}</strong>
                        </article>
                        <article>
                            <span>Registros</span>
                            <strong>${escapeHtml(formatInteger(summary.total))}</strong>
                        </article>
                        <article>
                            <span>Primera fecha</span>
                            <strong>${escapeHtml(summary.primeraFecha)}</strong>
                        </article>
                        <article>
                            <span>Ultima fecha</span>
                            <strong>${escapeHtml(summary.ultimaFecha)}</strong>
                        </article>
                    </div>
                </section>
                <section class="an-follow-section">
                    <div class="an-follow-section__head">
                        <div>
                            <div class="an-follow-section__eyebrow">Pacientes incluidos</div>
                            <h4>${escapeHtml(formatInteger(summary.pacientes.length))} pacientes visibles</h4>
                        </div>
                        <span class="an-pill an-pill--soft ${escapeHtml(eventTone)}">${escapeHtml(summary.pacientesLabel)}</span>
                    </div>
                    <div class="an-patient-grid">
                        ${summary.pacientes.map((patient, index) => followUpPatientCard(patient, index)).join('')}
                    </div>
                </section>
                <section class="an-follow-section">
                    <div class="an-follow-section__head">
                        <div>
                            <div class="an-follow-section__eyebrow">Seguimientos detallados</div>
                            <h4>${escapeHtml(eventLabel)}</h4>
                        </div>
                        <span class="an-pill an-pill--soft ${escapeHtml(eventTone)}">${escapeHtml(formatInteger(records.length))} registros</span>
                    </div>
                    <div class="an-follow-records">
                        ${records.map((record, index) => followUpRecordCard(record, index)).join('')}
                    </div>
                </section>
            </div>
        `;
        setVisible(seguimientosContent, true);
    }
})();
