<script>
    window.CicloVidaDateRange = window.CicloVidaDateRange || (function () {
        function normalizeMoment(value) {
            return moment.isMoment(value) ? value.clone() : moment(value, 'YYYY-MM-DD');
        }

        function resolveRange(key) {
            const today = moment();

            switch (key) {
                case '7d':
                    return [today.clone().subtract(6, 'days'), today.clone()];
                case '30d':
                    return [today.clone().subtract(29, 'days'), today.clone()];
                case '90d':
                    return [today.clone().subtract(89, 'days'), today.clone()];
                case '120d':
                    return [today.clone().subtract(119, 'days'), today.clone()];
                case 'month':
                    return [today.clone().startOf('month'), today.clone().endOf('month')];
                case 'prev_month':
                    return [
                        today.clone().subtract(1, 'month').startOf('month'),
                        today.clone().subtract(1, 'month').endOf('month')
                    ];
                case 'year':
                    return [today.clone().startOf('year'), today.clone()];
                case 'today':
                    return [today.clone(), today.clone()];
                default:
                    return null;
            }
        }

        function sameDates(a, b) {
            return a[0].isSame(b[0], 'day') && a[1].isSame(b[1], 'day');
        }

        function syncToolbar($picker, $chipScope, start, end) {
            $picker.find('span').text(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
            $chipScope.find('.cv-range-chip').each(function () {
                const range = resolveRange($(this).data('range'));
                $(this).toggleClass('is-active', !!range && sameDates(range, [start, end]));
            });
        }

        function bindExisting(options) {
            const pickerSelector = options.pickerSelector || '#daterange';
            const $picker = $(pickerSelector);
            const $chipScope = options.chipScopeSelector ? $(options.chipScopeSelector) : $picker.closest('.cv-date-toolbar');
            const picker = $picker.data('daterangepicker');

            if (!picker || !$chipScope.length) {
                return null;
            }

            syncToolbar($picker, $chipScope, picker.startDate.clone(), picker.endDate.clone());

            $chipScope.find('.cv-range-chip').off('click.cvExisting').on('click.cvExisting', function () {
                const range = resolveRange($(this).data('range'));
                if (!range) {
                    return;
                }

                picker.setStartDate(range[0]);
                picker.setEndDate(range[1]);
                syncToolbar($picker, $chipScope, range[0], range[1]);
            });

            return {
                getStart: function () { return picker.startDate.clone(); },
                getEndInclusive: function () { return picker.endDate.clone(); },
                getEndExclusive: function () { return picker.endDate.clone().add(1, 'day'); }
            };
        }

        function init(options) {
            const pickerSelector = options.pickerSelector || '#daterange';
            const chipScope = options.chipScopeSelector || null;
            const $picker = $(pickerSelector);
            const $chipScope = chipScope ? $(chipScope) : $picker.closest('.cv-date-toolbar');
            const startDefault = normalizeMoment(options.start);
            const endSource = normalizeMoment(options.end);
            const endDefault = options.endExclusive ? endSource.clone().subtract(1, 'day') : endSource.clone();

            function updateLabel(start, end) {
                syncToolbar($picker, $chipScope, start, end);
            }

            $picker.daterangepicker({
                startDate: startDefault,
                endDate: endDefault,
                autoUpdateInput: false,
                linkedCalendars: false,
                showDropdowns: true,
                alwaysShowCalendars: true,
                opens: options.opens || 'left',
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    customRangeLabel: 'Personalizado'
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Ultimos 7 dias': [moment().subtract(6, 'days'), moment()],
                    'Ultimos 30 dias': [moment().subtract(29, 'days'), moment()],
                    'Ultimos 90 dias': [moment().subtract(89, 'days'), moment()],
                    'Ultimos 120 dias': [moment().subtract(119, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Ano actual': [moment().startOf('year'), moment()]
                }
            }, updateLabel);

            updateLabel(startDefault, endDefault);

            $chipScope.find('.cv-range-chip').on('click', function () {
                const range = resolveRange($(this).data('range'));
                if (!range) {
                    return;
                }

                const picker = $picker.data('daterangepicker');
                picker.setStartDate(range[0]);
                picker.setEndDate(range[1]);
                updateLabel(range[0], range[1]);

                if (typeof options.onQuickRange === 'function') {
                    options.onQuickRange(range[0].clone(), range[1].clone());
                }
            });

            return {
                getStart: function () {
                    return $picker.data('daterangepicker').startDate.clone();
                },
                getEndInclusive: function () {
                    return $picker.data('daterangepicker').endDate.clone();
                },
                getEndExclusive: function () {
                    return $picker.data('daterangepicker').endDate.clone().add(1, 'day');
                },
                setRange: function (start, end) {
                    const picker = $picker.data('daterangepicker');
                    picker.setStartDate(start);
                    picker.setEndDate(end);
                    updateLabel(start, end);
                }
            };
        }

        return { init: init, bindExisting: bindExisting };
    })();
</script>
