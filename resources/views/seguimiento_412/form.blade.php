@php
    $currentYear = (int) now()->year;
    $selectedYear = old('filtro_anio_paciente_412', (string) $currentYear);

    $years = collect($incomeedit ?? [])->map(function ($row) {
        if (empty($row->created_at)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($row->created_at)->year;
        } catch (\Throwable $e) {
            return null;
        }
    })->filter()->unique()->sortDesc()->values();

    if (!$years->contains($currentYear)) {
        $years = $years->prepend($currentYear);
    }

    $medicamentosOld = collect(old('medicamento', []));
@endphp

<div class="s412-section">
    <div class="s412-section__head">
        <span><i class="fas fa-user-injured mr-1"></i> Paciente y Consulta</span>
        <span class="s412-step">Paso 1/5</span>
    </div>
    <div class="s412-section__body">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="s412-field">
                    <div class="s412-year-filter">
                        <label for="filtro_anio_paciente_412"><i class="fas fa-filter mr-1"></i> Filtro por anio</label>
                        <select id="filtro_anio_paciente_412" name="filtro_anio_paciente_412">
                            <option value="{{ $currentYear }}" {{ (string)$selectedYear === (string)$currentYear ? 'selected' : '' }}>{{ $currentYear }} (actual)</option>
                            @foreach($years as $year)
                                @if((int)$year !== $currentYear)
                                    <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                                @endif
                            @endforeach
                            <option value="all" {{ (string)$selectedYear === 'all' ? 'selected' : '' }}>Todos los anios</option>
                        </select>
                    </div>

                    <label for="cargue412_id">Seleccionar paciente <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-search s412-icon"></i>
                        <select id="cargue412_id" name="cargue412_id" class="s412-select with-icon" required>
                            <option value="">Seleccione paciente...</option>
                            @foreach($incomeedit as $p)
                                @php
                                    $fecha = null;
                                    $anio = '';
                                    if (!empty($p->created_at)) {
                                        try {
                                            $fecha = \Carbon\Carbon::parse($p->created_at);
                                            $anio = $fecha->year;
                                        } catch (\Throwable $e) {
                                            $fecha = null;
                                            $anio = '';
                                        }
                                    }

                                    $fullName = trim(implode(' ', array_filter([
                                        $p->primer_nombre ?? null,
                                        $p->segundo_nombre ?? null,
                                        $p->primer_apellido ?? null,
                                        $p->segundo_apellido ?? null,
                                    ])));

                                    $fechaText = $fecha ? $fecha->format('Y-m-d') : 'SIN FECHA';
                                @endphp
                                <option
                                    value="{{ $p->idin }}"
                                    data-year="{{ $anio }}"
                                    data-fecha="{{ $fechaText }}"
                                    data-nombre="{{ $fullName }}"
                                    data-documento="{{ $p->numero_identificacion }}"
                                    data-codigo="{{ $p->idin }}"
                                    {{ old('cargue412_id') == $p->idin ? 'selected' : '' }}>
                                    {{ $fullName }} - {{ $p->numero_identificacion }} ({{ $fechaText }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="s412-year-meta" id="patientYearHelp412">Cargando pacientes filtrados por anio...</p>
                    @error('cargue412_id')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="s412-field">
                    <label for="fecha_consulta">Fecha consulta <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-calendar-alt s412-icon"></i>
                        <input type="date" id="fecha_consulta" name="fecha_consulta" class="s412-input with-icon" value="{{ old('fecha_consulta', now()->format('Y-m-d')) }}" required>
                    </div>
                    @error('fecha_consulta')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="s412-section">
    <div class="s412-section__head">
        <span><i class="fas fa-ruler-combined mr-1"></i> Antropometria</span>
        <span class="s412-step">Paso 2/5</span>
    </div>
    <div class="s412-section__body">
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="s412-field">
                    <label for="talla_cm">Talla (cm) <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-ruler s412-icon"></i>
                        <input type="number" step="0.01" id="talla_cm" name="talla_cm" class="s412-input with-icon" value="{{ old('talla_cm') }}" required>
                    </div>
                    @error('talla_cm')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="s412-field">
                    <label for="peso_kilos">Peso (kg) <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-weight-hanging s412-icon"></i>
                        <input type="number" step="0.0001" id="peso_kilos" name="peso_kilos" class="s412-input with-icon" value="{{ old('peso_kilos') }}" required>
                    </div>
                    @error('peso_kilos')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="s412-field">
                    <label for="puntajez">Puntaje Z <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-chart-line s412-icon"></i>
                        <input type="number" step="0.0001" id="puntajez" name="puntajez" class="s412-input with-icon" value="{{ old('puntajez') }}" required>
                    </div>
                    @error('puntajez')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="s412-field">
                    <label for="perimetro_braqueal">Perimetro braquial <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-arrows-alt-h s412-icon"></i>
                        <input type="text" id="perimetro_braqueal" name="perimetro_braqueal" class="s412-input with-icon" value="{{ old('perimetro_braqueal') }}" required>
                    </div>
                    @error('perimetro_braqueal')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="s412-field">
                    <label for="requerimiento_energia_ftlc">Requerimiento energia FTLC <span class="s412-req">*</span></label>
                    <div class="s412-input-group">
                        <i class="fas fa-bolt s412-icon"></i>
                        <input type="text" id="requerimiento_energia_ftlc" name="requerimiento_energia_ftlc" class="s412-input with-icon" value="{{ old('requerimiento_energia_ftlc') }}" required>
                    </div>
                    @error('requerimiento_energia_ftlc')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="s412-section">
    <div class="s412-section__head">
        <span><i class="fas fa-notes-medical mr-1"></i> Manejo clinico</span>
        <span class="s412-step">Paso 3/5</span>
    </div>
    <div class="s412-section__body">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="s412-field">
                    <label for="clasificacion">Clasificacion <span class="s412-req">*</span></label>
                    <select id="clasificacion" name="clasificacion" class="s412-select" required>
                        <option value="">Seleccione...</option>
                        <option value="DESNUTRICION AGUDA MODERADA" {{ old('clasificacion') == 'DESNUTRICION AGUDA MODERADA' ? 'selected' : '' }}>Desnutricion aguda moderada</option>
                        <option value="DESNUTRICION AGUDA SEVERA" {{ old('clasificacion') == 'DESNUTRICION AGUDA SEVERA' ? 'selected' : '' }}>Desnutricion aguda severa</option>
                        <option value="DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR" {{ old('clasificacion') == 'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR' ? 'selected' : '' }}>Kwashiorkor</option>
                        <option value="DESNUTRICION AGUDA SEVERA TIPO MARASMO" {{ old('clasificacion') == 'DESNUTRICION AGUDA SEVERA TIPO MARASMO' ? 'selected' : '' }}>Marasmo</option>
                        <option value="DESNUTRICION AGUDA SEVERA MIXTA" {{ old('clasificacion') == 'DESNUTRICION AGUDA SEVERA MIXTA' ? 'selected' : '' }}>Mixta</option>
                        <option value="RIESGO DE DESNUTRICION" {{ old('clasificacion') == 'RIESGO DE DESNUTRICION' ? 'selected' : '' }}>Riesgo de desnutricion</option>
                        <option value="BUSQUEDA FALLIDA" {{ old('clasificacion') == 'BUSQUEDA FALLIDA' ? 'selected' : '' }}>Busqueda fallida</option>
                        <option value="PESO ADECUADO PARA LA TALLA" {{ old('clasificacion') == 'PESO ADECUADO PARA LA TALLA' ? 'selected' : '' }}>Peso adecuado para la talla</option>
                    </select>
                    @error('clasificacion')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="s412-field">
                    <label for="observaciones">Observaciones <span class="s412-req">*</span></label>
                    <textarea id="observaciones" name="observaciones" class="s412-textarea" maxlength="600" required>{{ old('observaciones') }}</textarea>
                    @error('observaciones')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12">
                <div class="s412-field">
                    <label for="medicamento">Medicamentos <span class="s412-req">*</span></label>
                    <select id="medicamento" name="medicamento[]" class="s412-select" multiple required>
                        <option value="23072-2" {{ $medicamentosOld->contains('23072-2') ? 'selected' : '' }}>Albendazol 200mg</option>
                        <option value="54114-1" {{ $medicamentosOld->contains('54114-1') ? 'selected' : '' }}>Albendazol 400mg</option>
                        <option value="35662-18" {{ $medicamentosOld->contains('35662-18') ? 'selected' : '' }}>Acido folico</option>
                        <option value="31063-1" {{ $medicamentosOld->contains('31063-1') ? 'selected' : '' }}>Vitamina A</option>
                        <option value="27440-3" {{ $medicamentosOld->contains('27440-3') ? 'selected' : '' }}>Hierro</option>
                        <option value="NO APLICA" {{ $medicamentosOld->contains('NO APLICA') ? 'selected' : '' }}>No aplica</option>
                    </select>
                    @error('medicamento')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="s412-section">
    <div class="s412-section__head">
        <span><i class="fas fa-syringe mr-1"></i> Demanda inducida</span>
        <span class="s412-step">Paso 4/5</span>
    </div>
    <div class="s412-section__body">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="s412-field">
                    <label for="Esquemq_complrto_pai_edad">Esquema PAI <span class="s412-req">*</span></label>
                    <select id="Esquemq_complrto_pai_edad" name="Esquemq_complrto_pai_edad" class="s412-select" required>
                        <option value="">Seleccione...</option>
                        <option value="INCOMPLETO" {{ old('Esquemq_complrto_pai_edad') == 'INCOMPLETO' ? 'selected' : '' }}>Incompleto</option>
                        <option value="COMPLETO" {{ old('Esquemq_complrto_pai_edad') == 'COMPLETO' ? 'selected' : '' }}>Completo</option>
                    </select>
                    @error('Esquemq_complrto_pai_edad')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="s412-field">
                    <label for="Atecion_primocion_y_mantenimiento_res3280_2018">Atencion promocion y mantenimiento <span class="s412-req">*</span></label>
                    <select id="Atecion_primocion_y_mantenimiento_res3280_2018" name="Atecion_primocion_y_mantenimiento_res3280_2018" class="s412-select" required>
                        <option value="">Seleccione...</option>
                        <option value="SI" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018') == 'SI' ? 'selected' : '' }}>Si</option>
                        <option value="NO" {{ old('Atecion_primocion_y_mantenimiento_res3280_2018') == 'NO' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('Atecion_primocion_y_mantenimiento_res3280_2018')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="s412-section">
    <div class="s412-section__head">
        <span><i class="fas fa-check-circle mr-1"></i> Cierre de caso</span>
        <span class="s412-step">Paso 5/5</span>
    </div>
    <div class="s412-section__body">
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="s412-field">
                    <label for="est_act_menor">Estado actual del menor</label>
                    <select id="est_act_menor" name="est_act_menor" class="s412-select" required>
                        <option value="">Seleccione...</option>
                        <option value="DESNUTRICION AGUDA MODERADA" {{ old('est_act_menor') == 'DESNUTRICION AGUDA MODERADA' ? 'selected' : '' }}>Desnutricion aguda moderada</option>
                        <option value="PESO ADECUADO PARA LA TALLA" {{ old('est_act_menor') == 'PESO ADECUADO PARA LA TALLA' ? 'selected' : '' }}>Peso adecuado para la talla</option>
                        <option value="RIESGO DE DESNUTRICION AGUDA" {{ old('est_act_menor') == 'RIESGO DE DESNUTRICION AGUDA' ? 'selected' : '' }}>Riesgo de desnutricion aguda</option>
                        <option value="DESNUTRICION AGUDA SEVERA TIPO MARASMO" {{ old('est_act_menor') == 'DESNUTRICION AGUDA SEVERA TIPO MARASMO' ? 'selected' : '' }}>Marasmo</option>
                        <option value="DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR" {{ old('est_act_menor') == 'DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR' ? 'selected' : '' }}>Kwashiorkor</option>
                        <option value="DESNUTRICION AGUDA SEVERA MIXTA" {{ old('est_act_menor') == 'DESNUTRICION AGUDA SEVERA MIXTA' ? 'selected' : '' }}>Mixta</option>
                        <option value="EN PROCESO DE RECUPERACION" {{ old('est_act_menor') == 'EN PROCESO DE RECUPERACION' ? 'selected' : '' }}>En proceso de recuperacion</option>
                        <option value="BUSQUEDA FALLIDA" {{ old('est_act_menor') == 'BUSQUEDA FALLIDA' ? 'selected' : '' }}>Busqueda fallida</option>
                        <option value="PROCESO DE RECUPERACION" {{ old('est_act_menor') == 'PROCESO DE RECUPERACION' ? 'selected' : '' }}>Proceso de recuperacion</option>
                        <option value="RECUPERADO" {{ old('est_act_menor') == 'RECUPERADO' ? 'selected' : '' }}>Recuperado</option>
                        <option value="FALLECIDO" {{ old('est_act_menor') == 'FALLECIDO' ? 'selected' : '' }}>Fallecido</option>
                    </select>
                    @error('est_act_menor')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="s412-field">
                    <label for="tratamiento_f75">Tratamiento F75</label>
                    <select id="tratamiento_f75" name="tratamiento_f75" class="s412-select" required>
                        <option value="">Seleccione...</option>
                        <option value="SI" {{ old('tratamiento_f75') == 'SI' ? 'selected' : '' }}>Si</option>
                        <option value="NO" {{ old('tratamiento_f75') == 'NO' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('tratamiento_f75')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4" id="row_fecha_recibio_tratf75" style="display:none;">
                <div class="s412-field">
                    <label for="fecha_recibio_tratf75">Fecha recibio tratamiento F75</label>
                    <input type="date" id="fecha_recibio_tratf75" name="fecha_recibio_tratf75" class="s412-input" value="{{ old('fecha_recibio_tratf75') }}">
                    @error('fecha_recibio_tratf75')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="s412-field">
                    <label for="estado">Estado de seguimiento <span class="s412-req">*</span></label>
                    <select id="estado" name="estado" class="s412-select" required>
                        <option value="">Seleccione...</option>
                        <option value="1" {{ old('estado') == '1' ? 'selected' : '' }}>Abierto</option>
                        <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                    @error('estado')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4" id="input_oculto">
                <div class="s412-field">
                    <label for="fecha_proximo_control">Fecha proximo seguimiento</label>
                    <input type="date" id="fecha_proximo_control" name="fecha_proximo_control" class="s412-input" value="{{ old('fecha_proximo_control') }}">
                    @error('fecha_proximo_control')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-4" id="inputsuperoculto" style="display:none;">
                <div class="s412-field">
                    <label for="motivo_reapuertura">Motivo de cierre/reapertura</label>
                    <textarea id="motivo_reapuertura" name="motivo_reapuertura" class="s412-textarea" style="min-height: 80px;">{{ old('motivo_reapuertura') }}</textarea>
                    @error('motivo_reapuertura')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12">
                <div class="s412-field">
                    <label for="pdf">Adjuntar PDF <span class="s412-req">*</span></label>
                    <input type="file" id="pdf" name="pdf" class="s412-input" accept="application/pdf" required>
                    @error('pdf')<div class="s412-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="s412-actions">
            <button id="update-btn" type="button" class="s412-btn-main" onclick="submitForm()">
                <i class="fas fa-paper-plane"></i> Guardar seguimiento
            </button>
            <a href="{{ url('new412_seguimiento') }}" class="s412-btn-back">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>
    </div>
</div>
