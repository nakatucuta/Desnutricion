{{-- resources/views/seguimiento/form.blade.php --}}
@php
    $currentYear = (int) now()->year;
    $selectedYear = old('filtro_anio_paciente', (string) $currentYear);

    $years = collect($incomeedit ?? [])->map(function ($dev) {
        if (empty($dev->fec_not)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($dev->fec_not)->year;
        } catch (\Throwable $e) {
            return null;
        }
    })->filter()->unique()->sortDesc()->values();

    if (!$years->contains($currentYear)) {
        $years = $years->prepend($currentYear);
    }

    $medicamentosOld = collect(old('medicamento', []));
@endphp

<div class="seg-card">
    <div class="seg-card__head">
        <span><i class="fas fa-user-injured mr-1"></i> Paciente y Consulta</span>
        <span class="small">Paso 1 de 5</span>
    </div>
    <div class="seg-card__body">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="seg-field">
                    <div class="seg-year-filter">
                        <p class="seg-year-filter__label"><i class="fas fa-filter mr-1"></i> Filtro por anio</p>
                        <select id="filtro_anio_paciente" class="seg-select" name="filtro_anio_paciente">
                            <option value="{{ $currentYear }}" {{ (string)$selectedYear === (string)$currentYear ? 'selected' : '' }}>
                                {{ $currentYear }} (actual)
                            </option>
                            @foreach($years as $year)
                                @if((int)$year !== $currentYear)
                                    <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                                @endif
                            @endforeach
                            <option value="all" {{ (string)$selectedYear === 'all' ? 'selected' : '' }}>Todos los anios</option>
                        </select>
                    </div>
                    <label for="sivigilas_id">Seleccionar paciente <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-search seg-input-icon"></i>
                        <select id="sivigilas_id" name="sivigilas_id" class="seg-select with-icon" required>
                            <option value="">Seleccione paciente...</option>
                            @foreach($incomeedit as $dev)
                                @php
                                    $fechaNot = null;
                                    $anioNot = '';
                                    if (!empty($dev->fec_not)) {
                                        try {
                                            $fechaNot = \Carbon\Carbon::parse($dev->fec_not);
                                            $anioNot = $fechaNot->year;
                                        } catch (\Throwable $e) {
                                            $fechaNot = null;
                                            $anioNot = '';
                                        }
                                    }

                                    $fullName = trim(implode(' ', array_filter([
                                        $dev->pri_nom_ ?? null,
                                        $dev->seg_nom_ ?? null,
                                        $dev->pri_ape_ ?? null,
                                        $dev->seg_ape_ ?? null,
                                    ])));

                                    $fechaText = $fechaNot ? $fechaNot->format('Y-m-d') : 'SIN FECHA';
                                @endphp
                                <option
                                    value="{{ $dev->idin }}"
                                    data-year="{{ $anioNot }}"
                                    data-fecha="{{ $fechaText }}"
                                    data-nombre="{{ $fullName }}"
                                    data-documento="{{ $dev->num_ide_ }}"
                                    data-codigo="{{ $dev->idin }}"
                                    {{ old('sivigilas_id') == $dev->idin ? 'selected' : '' }}>
                                    {{ $fullName }} - {{ $dev->num_ide_ }} ({{ $fechaText }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="seg-year-meta" id="patientYearHelp">Cargando pacientes filtrados por anio...</p>
                    @error('sivigilas_id')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="seg-field">
                    <label for="fecha_consulta">Fecha consulta <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-calendar-alt seg-input-icon"></i>
                        <input
                            type="date"
                            id="fecha_consulta"
                            name="fecha_consulta"
                            class="seg-input with-icon"
                            value="{{ old('fecha_consulta', now()->format('Y-m-d')) }}"
                            required>
                    </div>
                    @error('fecha_consulta')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="seg-card">
    <div class="seg-card__head">
        <span><i class="fas fa-ruler-combined mr-1"></i> Antropometria</span>
        <span class="small">Paso 2 de 5</span>
    </div>
    <div class="seg-card__body">
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="talla_cm">Talla (cm) <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-ruler seg-input-icon"></i>
                        <input type="number" step="0.01" id="talla_cm" name="talla_cm" class="seg-input with-icon" value="{{ old('talla_cm') }}" required>
                    </div>
                    @error('talla_cm')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="peso_kilos">Peso (kg) <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-weight-hanging seg-input-icon"></i>
                        <input type="number" step="0.0001" id="peso_kilos" name="peso_kilos" class="seg-input with-icon" value="{{ old('peso_kilos') }}" required>
                    </div>
                    @error('peso_kilos')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="puntajez">Puntaje Z <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-chart-line seg-input-icon"></i>
                        <input type="number" step="0.0001" id="puntajez" name="puntajez" class="seg-input with-icon" value="{{ old('puntajez') }}" required>
                    </div>
                    @error('puntajez')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="seg-field">
                    <label for="perimetro_braqueal">Perimetro braquial <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-arrows-alt-h seg-input-icon"></i>
                        <input type="text" id="perimetro_braqueal" name="perimetro_braqueal" class="seg-input with-icon" value="{{ old('perimetro_braqueal') }}" required>
                    </div>
                    @error('perimetro_braqueal')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="seg-field">
                    <label for="requerimiento_energia_ftlc">Requerimiento energia FTLC <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-bolt seg-input-icon"></i>
                        <input type="text" id="requerimiento_energia_ftlc" name="requerimiento_energia_ftlc" class="seg-input with-icon" value="{{ old('requerimiento_energia_ftlc') }}" required>
                    </div>
                    @error('requerimiento_energia_ftlc')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="seg-card">
    <div class="seg-card__head">
        <span><i class="fas fa-notes-medical mr-1"></i> Manejo clinico</span>
        <span class="small">Paso 3 de 5</span>
    </div>
    <div class="seg-card__body">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="seg-field">
                    <label for="clasificacion">Clasificacion <span class="required-dot">*</span></label>
                    <div class="seg-input-group">
                        <i class="fas fa-clipboard-list seg-input-icon"></i>
                        <select id="clasificacion" name="clasificacion" class="seg-select with-icon" required>
                            <option value="">Seleccione...</option>
                            @foreach([
                                'DESNUTRICION AGUDA MODERADA',
                                'DESNUTRICION AGUDA SEVERA',
                                'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR',
                                'DESNUTRICION AGUDA SEVERA TIPO MARASMO',
                                'DESNUTRICION AGUDA SEVERA MIXTA',
                                'RIESGO DE DESNUTRICION',
                                'RIESGO DE DESNUTRICION AGUDA',
                                'BUSQUEDA FALLIDA',
                                'PESO ADECUADO PARA LA TALLA'
                            ] as $opt)
                                <option value="{{ $opt }}" {{ old('clasificacion') == $opt ? 'selected' : '' }}>{{ ucwords(strtolower($opt)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('clasificacion')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="seg-field">
                    <label for="observaciones">Observaciones <span class="required-dot">*</span></label>
                    <textarea id="observaciones" name="observaciones" class="seg-textarea" maxlength="600" required>{{ old('observaciones') }}</textarea>
                    @error('observaciones')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12">
                <div class="seg-field">
                    <label for="medicamento">Medicamentos <span class="required-dot">*</span></label>
                    <select id="medicamento" name="medicamento[]" class="seg-select" multiple required>
                        <option value="23072-2" {{ $medicamentosOld->contains('23072-2') ? 'selected' : '' }}>Albendazol 200mg</option>
                        <option value="54114-1" {{ $medicamentosOld->contains('54114-1') ? 'selected' : '' }}>Albendazol 400mg</option>
                        <option value="35662-18" {{ $medicamentosOld->contains('35662-18') ? 'selected' : '' }}>Acido folico</option>
                        <option value="31063-1" {{ $medicamentosOld->contains('31063-1') ? 'selected' : '' }}>Vitamina A</option>
                        <option value="27440-3" {{ $medicamentosOld->contains('27440-3') ? 'selected' : '' }}>Hierro</option>
                        <option value="NO APLICA" {{ $medicamentosOld->contains('NO APLICA') ? 'selected' : '' }}>No aplica</option>
                    </select>
                    @error('medicamento')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="seg-card">
    <div class="seg-card__head">
        <span><i class="fas fa-syringe mr-1"></i> Demanda inducida</span>
        <span class="small">Paso 4 de 5</span>
    </div>
    <div class="seg-card__body">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="seg-field">
                    <label for="Esquemq_complrto_pai_edad">Esquema PAI por edad <span class="required-dot">*</span></label>
                    <select id="Esquemq_complrto_pai_edad" name="Esquemq_complrto_pai_edad" class="seg-select" required>
                        <option value="">Seleccione...</option>
                        <option value="INCOMPLETO" {{ old('Esquemq_complrto_pai_edad') == 'INCOMPLETO' ? 'selected' : '' }}>Incompleto</option>
                        <option value="COMPLETO" {{ old('Esquemq_complrto_pai_edad') == 'COMPLETO' ? 'selected' : '' }}>Completo</option>
                    </select>
                    @error('Esquemq_complrto_pai_edad')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="seg-field">
                    <label for="Atecion_primocion_y_mantenimiento_res3280_2018">Atencion promocion y mantenimiento <span class="required-dot">*</span></label>
                    <select id="Atecion_primocion_y_mantenimiento_res3280_2018" name="Atecion_primocion_y_mantenimiento_res3280_2018" class="seg-select" required>
                        <option value="">Seleccione...</option>
                        <option value="SI" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018') == 'SI' ? 'selected' : '' }}>Si</option>
                        <option value="NO" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018') == 'NO' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('Atecion_primocion_y_mantenimiento_res3280_2018')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="seg-card">
    <div class="seg-card__head">
        <span><i class="fas fa-check-circle mr-1"></i> Cierre de caso</span>
        <span class="small">Paso 5 de 5</span>
    </div>
    <div class="seg-card__body">
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="est_act_menor">Estado actual del menor <span class="required-dot">*</span></label>
                    <select id="est_act_menor" name="est_act_menor" class="seg-select" required>
                        <option value="">Seleccione...</option>
                        @foreach([
                            'PESO ADECUADO PARA LA TALLA',
                            'RIESGO DE DESNUTRICION AGUDA',
                            'DESNUTRICION AGUDA MODERADA',
                            'DESNUTRICION AGUDA SEVERA',
                            'DESNUTRICION AGUDA SEVERA TIPO MARASMO',
                            'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR',
                            'DESNUTRICION AGUDA SEVERA MIXTA',
                            'EN PROCESO DE RECUPERACION',
                            'BUSQUEDA FALLIDA',
                            'PROCESO DE RECUPERACION',
                            'RECUPERADO',
                            'FALLECIDO'
                        ] as $opt)
                            <option value="{{ $opt }}" {{ old('est_act_menor') == $opt ? 'selected' : '' }}>{{ ucwords(strtolower($opt)) }}</option>
                        @endforeach
                    </select>
                    @error('est_act_menor')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="tratamiento_f75">Tratamiento F75 <span class="required-dot">*</span></label>
                    <select id="tratamiento_f75" name="tratamiento_f75" class="seg-select" required>
                        <option value="">Seleccione...</option>
                        <option value="SI" {{ old('tratamiento_f75') == 'SI' ? 'selected' : '' }}>Si</option>
                        <option value="NO" {{ old('tratamiento_f75') == 'NO' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('tratamiento_f75')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4" id="row_fecha_recibio_tratf75" style="display:none;">
                <div class="seg-field">
                    <label for="fecha_recibio_tratf75">Fecha recibio tratamiento F75</label>
                    <input type="date" id="fecha_recibio_tratf75" name="fecha_recibio_tratf75" class="seg-input" value="{{ old('fecha_recibio_tratf75') }}">
                    @error('fecha_recibio_tratf75')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="estado">Estado de seguimiento <span class="required-dot">*</span></label>
                    <select id="estado" name="estado" class="seg-select" required>
                        <option value="">Seleccione...</option>
                        <option value="1" {{ old('estado') == '1' ? 'selected' : '' }}>Abierto</option>
                        <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                    @error('estado')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4" id="input_oculto">
                <div class="seg-field">
                    <label for="fecha_proximo_control">Fecha proximo seguimiento</label>
                    <input type="date" id="fecha_proximo_control" name="fecha_proximo_control" class="seg-input" value="{{ old('fecha_proximo_control') }}">
                    @error('fecha_proximo_control')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="seg-field">
                    <label for="fecha_entrega_ftlc">Fecha entrega FTLC</label>
                    <input type="date" id="fecha_entrega_ftlc" name="fecha_entrega_ftlc" class="seg-input" value="{{ old('fecha_entrega_ftlc') }}">
                    @error('fecha_entrega_ftlc')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12">
                <div class="seg-field">
                    <label for="pdf">Adjuntar PDF <span class="required-dot">*</span></label>
                    <input type="file" id="pdf" name="pdf" class="seg-input" accept="application/pdf" required>
                    @error('pdf')<div class="seg-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="seg-actions">
            <button id="update-btn" type="button" class="seg-btn seg-btn--primary" onclick="submitForm()">
                <i class="fas fa-paper-plane mr-1"></i> Guardar seguimiento
            </button>
            <a href="{{ url('Seguimiento') }}" class="seg-btn seg-btn--ghost">
                <i class="fas fa-arrow-left mr-1"></i> Regresar
            </a>
        </div>
    </div>
</div>
