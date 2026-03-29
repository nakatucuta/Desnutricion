<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@include('ciclo_vidas.partials.date_range_shared_script')
<script>
    $(function () {
        moment.locale('es');

        const previewUrl = @json($previewUrl);
        const exportBaseUrl = @json($exportBaseUrl);
        const advancedFiltersUrl = @json($advancedFiltersUrl);
        const fieldGroups = @json($fieldGroups);
        const templates = @json($templates);
        const rangePicker = window.CicloVidaDateRange.init({
            pickerSelector: '#reportRange',
            start: @json($desde),
            end: @json($hasta),
            endExclusive: false
        });

        let selectedFields = [];
        let activeTemplate = null;
        let advancedFiltersLoaded = false;

        const defaultFields = ['course_label', 'module_label', 'event_date', 'tipo_identificacion', 'identificacion', 'nombre_completo', 'edad', 'ips_primaria', 'descripcion_servicio'];
        const fieldMap = {};
        (fieldGroups || []).forEach(group => {
            (group.fields || []).forEach(field => {
                fieldMap[field.key] = field;
            });
        });

        const $loading = $('#cvReportLoading');
        const $loadingText = $('#cvReportLoadingText');

        function showLoading(message) {
            if (message) {
                $loadingText.text(message);
            }
            $('body').addClass('cv-report-loading-lock');
            $loading.addClass('is-visible');
        }

        function hideLoading() {
            $('body').removeClass('cv-report-loading-lock');
            $loading.removeClass('is-visible');
        }

        function fieldLabel(key) {
            return fieldMap[key]?.label || key;
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function syncDynamicSelectOptions(selector, items, placeholder, selected) {
            const options = [`<option value="">${escapeHtml(placeholder)}</option>`];

            (items || []).forEach(function (item) {
                options.push(`<option value="${escapeHtml(item.value)}">${escapeHtml(item.label)}</option>`);
            });

            $(selector).html(options.join(''));
            $(selector).val(selected || '');
        }

        function setAdvancedFiltersState(message, tone) {
            const $state = $('#advancedFiltersState');
            $state.text(message);
            $state.removeClass('text-muted text-info text-success text-danger');
            $state.addClass(tone || 'text-muted');
        }

        function populateAdvancedFilters(options, selectedValues) {
            const selected = selectedValues || {};
            syncDynamicSelectOptions('#reportGender', options.genders || [], 'Todos', selected.genero || '');
            syncDynamicSelectOptions('#reportDepartment', options.departments || [], 'Todos', selected.departamento || '');
            syncDynamicSelectOptions('#reportMunicipality', options.municipalities || [], 'Todos', selected.municipio || '');
            syncDynamicSelectOptions('#reportIps', options.ips || [], 'Todas', selected.ips || '');
            $('[data-advanced-filter="true"]').prop('disabled', false);
        }

        function currentAdvancedOptions(selector) {
            return $(selector).find('option').map(function () {
                return $(this).val()
                    ? { value: $(this).val(), label: $(this).text() }
                    : null;
            }).get().filter(Boolean);
        }

        function loadAdvancedFilters(forceReload, selectedValues) {
            if (advancedFiltersLoaded && !forceReload) {
                if (selectedValues) {
                    populateAdvancedFilters({
                        genders: currentAdvancedOptions('#reportGender'),
                        departments: currentAdvancedOptions('#reportDepartment'),
                        municipalities: currentAdvancedOptions('#reportMunicipality'),
                        ips: currentAdvancedOptions('#reportIps')
                    }, selectedValues);
                }

                return $.Deferred().resolve().promise();
            }

            showLoading('Cargando filtros avanzados del reporte...');
            $('#btnLoadAdvancedFilters').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Cargando');
            setAdvancedFiltersState('Cargando genero, territorio e IPS...', 'text-info');

            return $.getJSON(advancedFiltersUrl)
                .done(function (payload) {
                    populateAdvancedFilters(payload || {}, selectedValues);
                    advancedFiltersLoaded = true;
                    $('#btnLoadAdvancedFilters')
                        .removeClass('btn-outline-primary')
                        .addClass('btn-outline-success')
                        .html('<i class="fas fa-check mr-2"></i>Filtros avanzados listos');
                    setAdvancedFiltersState('Filtros avanzados listos para segmentar el reporte.', 'text-success');
                })
                .fail(function () {
                    setAdvancedFiltersState('No fue posible cargar los filtros avanzados.', 'text-danger');
                    alert('No fue posible cargar los filtros avanzados del reporte.');
                })
                .always(function () {
                    $('#btnLoadAdvancedFilters').prop('disabled', false);
                    hideLoading();
                });
        }

        function normalizeSelectedFields() {
            selectedFields = selectedFields.filter((field, index) => fieldMap[field] && selectedFields.indexOf(field) === index);
            if (!selectedFields.length) {
                selectedFields = [...defaultFields];
            }
        }

        function syncFieldChecks() {
            $('.report-field-checkbox').each(function () {
                $(this).prop('checked', selectedFields.includes($(this).val()));
            });
        }

        function renderSelectedFields() {
            normalizeSelectedFields();
            syncFieldChecks();

            const html = selectedFields.map(field => `
                <div class="cv-selected-chip" data-selected-field="${field}">
                    <i class="fas fa-grip-vertical text-muted"></i>
                    <span>${fieldLabel(field)}</span>
                    <button type="button" data-remove-field="${field}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');

            $('#selectedFieldsBoard').html(html || '<span class="text-muted">No hay variables seleccionadas.</span>');
            $('#selectedTemplateLabel').text(activeTemplate?.label || 'Diseño libre');
        }

        function collectParams() {
            return {
                template: activeTemplate?.key || '',
                desde: rangePicker.getStart().format('YYYY-MM-DD'),
                hasta: rangePicker.getEndInclusive().format('YYYY-MM-DD'),
                course_key: $('#reportCourse').val() || '',
                module_key: $('#reportModule').val() || '',
                record_type: $('#reportType').val() || 'all',
                genero: $('#reportGender').val() || '',
                departamento: $('#reportDepartment').val() || '',
                municipio: $('#reportMunicipality').val() || '',
                ips: $('#reportIps').val() || '',
                edad_min: $('#reportAgeMin').val() || '',
                edad_max: $('#reportAgeMax').val() || '',
                fields: selectedFields
            };
        }

        function applyTemplate(templateKey) {
            const template = (templates || []).find(item => item.key === templateKey) || null;
            activeTemplate = template;

            $('[data-template-card]').removeClass('is-active');
            if (template) {
                $('[data-template-card="' + template.key + '"]').addClass('is-active');
                selectedFields = [...(template.fields || defaultFields)];
                $('#reportCourse').val(template.filters?.course_key || '');
                $('#reportType').val(template.filters?.record_type || 'all');
                if (template.filters?.module_key) {
                    $('#reportModule').val(template.filters.module_key);
                }
            } else {
                selectedFields = [...defaultFields];
            }

            renderSelectedFields();
        }

        function saveLocalDesign() {
            localStorage.setItem('cv_report_designer_state', JSON.stringify(collectParams()));
            alert('Diseño guardado en este navegador.');
        }

        function loadLocalDesign() {
            const raw = localStorage.getItem('cv_report_designer_state');
            if (!raw) {
                alert('No hay un diseño guardado en este navegador.');
                return;
            }

            try {
                const data = JSON.parse(raw);
                activeTemplate = (templates || []).find(item => item.key === data.template) || null;
                selectedFields = Array.isArray(data.fields) ? data.fields : [...defaultFields];
                $('#reportCourse').val(data.course_key || '');
                $('#reportModule').val(data.module_key || '');
                $('#reportType').val(data.record_type || 'all');
                $('#reportAgeMin').val(data.edad_min || '');
                $('#reportAgeMax').val(data.edad_max || '');
                if (data.desde && data.hasta) {
                    rangePicker.setRange(data.desde, data.hasta);
                }
                $('[data-template-card]').removeClass('is-active');
                if (activeTemplate) {
                    $('[data-template-card="' + activeTemplate.key + '"]').addClass('is-active');
                }
                const finalizeDesignLoad = function () {
                    $('#reportGender').val(data.genero || '');
                    $('#reportDepartment').val(data.departamento || '');
                    $('#reportMunicipality').val(data.municipio || '');
                    $('#reportIps').val(data.ips || '');
                    renderSelectedFields();
                    alert('Diseno cargado correctamente.');
                };

                if (data.genero || data.departamento || data.municipio || data.ips) {
                    loadAdvancedFilters(false, data).always(finalizeDesignLoad);
                } else {
                    finalizeDesignLoad();
                }
            } catch (error) {
                alert('No fue posible cargar el diseño guardado.');
            }
        }

        function buildExportUrl(format) {
            const params = new URLSearchParams();
            const payload = collectParams();
            Object.entries(payload).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(item => params.append('fields[]', item));
                } else if (value !== '') {
                    params.append(key, value);
                }
            });

            return exportBaseUrl.replace('__FORMAT__', format) + '?' + params.toString();
        }

        function renderPreview(payload) {
            $('#previewTemplate').text(payload.meta?.template || 'Diseño libre');
            $('#previewGeneratedBy').text(payload.meta?.generated_by || '-');
            $('#previewGeneratedAt').text(payload.meta?.generated_at || '-');
            $('#previewColumnsCount').text((payload.columns || []).length);
            $('#previewSummary').text('Vista previa: ' + (payload.rows?.length || 0) + ' filas de ' + (payload.meta?.total_records || 0) + ' registros');

            const filterParts = [];
            const filters = payload.meta?.filters || {};
            Object.entries(filters).forEach(([key, value]) => {
                if (value) {
                    filterParts.push({
                        label: key.replace(/_/g, ' '),
                        value: value
                    });
                }
            });
            $('#previewFilters').html(
                filterParts.length
                    ? filterParts.map(filter => `<span class="cv-preview-filter-chip"><small>${filter.label}</small><strong>${filter.value}</strong></span>`).join('')
                    : '<span class="text-muted">Sin filtros adicionales aplicados.</span>'
            );

            const columns = payload.columns || [];
            const rows = payload.rows || [];

            $('#reportPreviewTable thead').html(
                '<tr>' + columns.map(column => `<th>${column.label}</th>`).join('') + '</tr>'
            );

            if (!rows.length) {
                $('#reportPreviewTable tbody').html('<tr><td colspan="' + Math.max(columns.length, 1) + '" class="text-muted">No se encontraron registros con el diseño actual.</td></tr>');
                return;
            }

            const body = rows.map(row => {
                return '<tr>' + columns.map(column => `<td>${row[column.key] ?? ''}</td>`).join('') + '</tr>';
            }).join('');

            $('#reportPreviewTable tbody').html(body);
        }

        function loadPreview() {
            const params = collectParams();
            showLoading('Generando vista previa del reporte...');
            $('#btnPreviewReport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Generando');

            $.ajax({
                url: previewUrl,
                method: 'GET',
                data: params
            })
                .done(function (payload) {
                    renderPreview(payload);
                })
                .fail(function () {
                    alert('No fue posible generar la vista previa del reporte.');
                })
                .always(function () {
                    $('#btnPreviewReport').prop('disabled', false).html('<i class="fas fa-eye mr-2"></i>Generar vista previa');
                    hideLoading();
                });
        }

        $('.report-field-checkbox').on('change', function () {
            const value = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedFields.includes(value)) {
                    selectedFields.push(value);
                }
            } else {
                selectedFields = selectedFields.filter(field => field !== value);
            }
            renderSelectedFields();
        });

        $('#selectedFieldsBoard').on('click', '[data-remove-field]', function () {
            const field = $(this).data('remove-field');
            selectedFields = selectedFields.filter(item => item !== field);
            renderSelectedFields();
        });

        $('[data-template-card]').on('click', function () {
            applyTemplate($(this).data('template-card'));
        });

        $('#btnSelectAllFields').on('click', function () {
            selectedFields = Object.keys(fieldMap);
            renderSelectedFields();
        });

        $('#btnClearAllFields').on('click', function () {
            selectedFields = [];
            renderSelectedFields();
        });

        $('#btnLoadAdvancedFilters').on('click', function () {
            loadAdvancedFilters(false);
        });

        $('#btnPreviewReport').on('click', loadPreview);
        $('#btnSaveLocalDesign').on('click', saveLocalDesign);
        $('#btnLoadLocalDesign').on('click', loadLocalDesign);
        $('#btnResetDesign').on('click', function () {
            activeTemplate = null;
            $('[data-template-card]').removeClass('is-active');
            $('#reportCourse, #reportModule, #reportGender, #reportDepartment, #reportMunicipality, #reportIps').val('');
            $('#reportType').val('all');
            $('#reportAgeMin, #reportAgeMax').val('');
            selectedFields = [...defaultFields];
            renderSelectedFields();
        });

        $('.export-report').on('click', function () {
            window.open(buildExportUrl($(this).data('format')), '_blank');
        });

        new Sortable(document.getElementById('selectedFieldsBoard'), {
            animation: 150,
            onEnd: function () {
                selectedFields = $('#selectedFieldsBoard [data-selected-field]').map(function () {
                    return $(this).data('selected-field');
                }).get();
            }
        });

        applyTemplate('vejez_junio');
        setAdvancedFiltersState('Aun no se han cargado filtros avanzados.', 'text-muted');
    });
</script>
