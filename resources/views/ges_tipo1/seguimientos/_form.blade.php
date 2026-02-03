{{-- resources/views/ges_tipo1_seguimientos/_form.blade.php --}}

@php
    use Illuminate\Support\Str;

    // Modelo base para precargar en create: el último seguimiento (si existe)
    $model = isset($seg) && $seg ? $seg : ($ultimo ?? null);

    // Helper para obtener valor -> old() > modelo (con formateo para dates)
    $val = function(string $name) use($model) {
        $ov = old($name);
        if ($ov !== null && $ov !== '') return $ov;
        if (!$model) return '';
        $attr = $model->getAttribute($name);
        if ($attr instanceof \Carbon\Carbon) return $attr->format('Y-m-d');
        return $attr ?? '';
    };

    // ¿el valor actual es un PDF (ruta/URL)?
    $isPdfValue = function(string $value = null) {
        if (!is_string($value) || $value === '') return false;
        $v = Str::lower($value);
        return Str::endsWith($v, '.pdf');
    };

    /**
     * URL pública del archivo PDF:
     * - Si ya viene http(s), lo devuelve tal cual
     * - Si es una ruta relativa (p.ej. "seguimientos/xxx.pdf"), genera asset('storage/...'),
     *   lo que en XAMPP te deja: http://localhost/Desnutricion/public/storage/seguimientos/xxx.pdf
     */
    $filePublicUrl = function(string $value = null) {
        if (!is_string($value) || $value === '') return null;
        if (Str::startsWith($value, ['http://','https://'])) return $value;
        $path = ltrim($value, '/');
        return asset('storage/' . $path);
    };

    // Opciones comunes
    $optSN = ['SI' => 'Sí', 'NO' => 'No'];
    $optTipoContacto = ['1' => 'Telefónico', '2' => 'Domiciliario', '3' => 'Otro'];

    /**
     * ✅ Opciones de "resultado" según el examen
     * Guardamos el valor en *_resultado_desc
     */
    $getResultadoOpts = function(string $name, string $label = '') {
        $k = Str::lower($name.' '.$label);

        // VIH / Sífilis / Hepatitis B / Chagas / Pruebas tipo "reactivo"
        if (
            Str::contains($k, 'vih') ||
            Str::contains($k, 'sifilis') ||
            Str::contains($k, 'ag_hbs') || Str::contains($k, 'hepatitis') ||
            Str::contains($k, 'chagas')
        ) {
            return [
                'REACTIVO' => 'Reactivo',
                'NO REACTIVO' => 'No reactivo',
                'NO REALIZADO' => 'No realizado',
            ];
        }

        // Toxoplasma / Rubéola (muy común reportar IgM)
        if (Str::contains($k, 'toxoplasma') || Str::contains($k, 'rubeola')) {
            return [
                'IGM POSITIVO' => 'IgM positivo',
                'IGM NEGATIVO' => 'IgM negativo',
                'NO REALIZADO' => 'No realizado',
            ];
        }

        // Citología (categorías típicas)
        if (Str::contains($k, 'citologia')) {
            return [
                'NEGATIVA' => 'Negativa',
                'ASC-US' => 'ASC-US',
                'LSIL' => 'LSIL (Lesión intraepitelial escamosa de bajo grado)',
                'HSIL' => 'HSIL (Lesión intraepitelial escamosa de alto grado)',
                'OTRO' => 'Otro',
                'NO REALIZADA' => 'No realizada',
            ];
        }

        // Hemoclasificación (grupo y Rh)
        if (Str::contains($k, 'hemoclasificacion')) {
            return [
                'A+' => 'A+',
                'A-' => 'A-',
                'B+' => 'B+',
                'B-' => 'B-',
                'AB+' => 'AB+',
                'AB-' => 'AB-',
                'O+' => 'O+',
                'O-' => 'O-',
                'NO REALIZADO' => 'No realizado',
            ];
        }

        // Urocultivo (a veces reportan contaminado)
        if (Str::contains($k, 'urocultivo')) {
            return [
                'POSITIVO' => 'Positivo',
                'NEGATIVO' => 'Negativo',
                'CONTAMINADO' => 'Contaminado',
                'NO REALIZADO' => 'No realizado',
            ];
        }

        // Estreptococo / Malaria (común pos/neg)
        if (Str::contains($k, 'estreptococo') || Str::contains($k, 'malaria')) {
            return [
                'POSITIVO' => 'Positivo',
                'NEGATIVO' => 'Negativo',
                'NO REALIZADO' => 'No realizado',
            ];
        }

        // Fallback general para "resultado" cualitativo
        return [
            'POSITIVO' => 'Positivo',
            'NEGATIVO' => 'Negativo',
            'NO REALIZADO' => 'No realizado',
        ];
    };

    // ==== DEFINICIÓN DE SECCIONES Y CAMPOS ====
    $SECCIONES = [

        'Cabecera del seguimiento' => [
            ['fecha_contacto','Fecha de contacto','date'],
            ['tipo_contacto','Tipo de contacto','select',$optTipoContacto],
            ['estado','Estado (Abierto/Cerrado)','text'],
            ['proximo_contacto','Próximo contacto','date'],
        ],

        'Identificación y demografía' => [
            ['tipo_documento','Tipo documento','text'],
            ['numero_identificacion','Número identificación','text'],
            ['apellido_1','Primer apellido','text'],
            ['apellido_2','Segundo apellido','text'],
            ['nombre_1','Primer nombre','text'],
            ['nombre_2','Segundo nombre','text'],
            ['fecha_nacimiento','Fecha nacimiento','date'],
            ['edad_anios','Edad (años)','number'],
            ['sexo','Sexo','text'],
            ['regimen_afiliacion','Régimen afiliación','text'],
            ['pertenencia_etnica','Pertenencia étnica','text'],
            ['grupo_poblacional','Grupo poblacional','text'],
            ['departamento_residencia','Departamento residencia','text'],
            ['municipio_residencia','Municipio residencia','text'],
            ['zona','Zona','text'],
            ['etnia','Etnia','text'],
            ['asentamiento','Asentamiento','text'],
            ['telefono_usuaria','Teléfono','text'],
            ['direccion','Dirección','text'],
            ['nivel_educativo','Nivel educativo','text'],
            ['discapacidad','Discapacidad','text'],
            ['mujer_cabeza_hogar','Mujer cabeza hogar','select',$optSN],
            ['ocupacion','Ocupación','text'],
            ['estado_civil','Estado civil','text'],
            ['control_tradicional','Control tradicional','select',$optSN],
            ['gestante_renuente','Gestante renuente','select',$optSN],
            ['inasistente','Inasistente','select',$optSN],
            ['ips_primaria','IPS primaria','text'],
        ],

        'Gestación (fechas y cálculo)' => [
            ['fecha_ingreso_cpn','Fecha ingreso CPN','date'],
            ['fum','FUM','date'],
            ['fpp','FPP','date'],
            ['dias_para_parto','Días para parto','number'],
            ['alarma','Alarma','select',$optSN],
            ['edad_gest_inicio_control','Edad gestacional al inicio','text'],
            ['trimestre_inicio_control','Trimestre inicio control','text'],
            ['formula_obstetrica','Fórmula obstétrica','text'],
        ],

        'Morbilidades y factores' => [
            ['hipertension_arterial','Hipertensión arterial','select',$optSN],
            ['diabetes','Diabetes','select',$optSN],
            ['vih','VIH','select',$optSN],
            ['sifilis','Sífilis','select',$optSN],
            ['tuberculosis','Tuberculosis','select',$optSN],
            ['otras_condiciones_graves','Otras condiciones graves','text'],
            ['apoyo_familiar','Apoyo familiar','select',$optSN],
            ['embarazo_deseado','Embarazo deseado','select',$optSN],
            ['habitos_riesgo','Hábitos de riesgo','text'],
            ['violencia','Violencia','select',$optSN],
            ['abuso_sexual','Abuso sexual','select',$optSN],
            ['periodo_intergenesico','Período intergenésico','text'],
        ],

        'Antropometría y riesgo' => [
            ['peso_inicial','Peso inicial (kg)','number'],
            ['talla','Talla (cm)','number'],
            ['imc','IMC','number'],
            ['clasificacion_imc','Clasificación IMC','text'],
            ['riesgos_psicosociales','Riesgos psicosociales','text'],
            ['ive_causales','IVE causales','text'],
            ['clasificacion_riesgo','Clasificación de riesgo','text'],
            ['alto_riesgo_causas','Alto riesgo (causas)','text'],
            ['otras_cuales','Otras ¿cuáles?','text'],
        ],

        'Asesorías / Remisiones' => [
            ['remitida_especialista','Remitida a especialista','select',$optSN],
            ['asesoria_vih','Asesoría VIH','select',$optSN],
            ['asesoria_vih_trimestre','Asesoría VIH (trimestre)','text'],
        ],

        'Tamizaje VIH' => [
            ['vih_tamiz1_fecha','VIH Tamiz 1 - fecha','date'],
            ['vih_tamiz1_resultado','VIH Tamiz 1 - resultado','text'],
            ['vih_tamiz1_trimestre','VIH Tamiz 1 - trimestre','text'],
            ['vih_tamiz2_fecha','VIH Tamiz 2 - fecha','date'],
            ['vih_tamiz2_resultado','VIH Tamiz 2 - resultado','text'],
            ['vih_tamiz2_trimestre','VIH Tamiz 2 - trimestre','text'],
            ['vih_tamiz3_fecha','VIH Tamiz 3 - fecha','date'],
            ['vih_tamiz3_resultado','VIH Tamiz 3 - resultado','text'],
            ['vih_tamiz3_trimestre','VIH Tamiz 3 - trimestre','text'],
            ['vih_confirmatoria_fecha','VIH confirmatoria - fecha','date'],
            ['vih_confirmatoria_trimestre','VIH confirmatoria - trimestre','text'],
        ],

        'Sífilis rápida y No treponémica' => [
            ['sifilis_rapida1_fecha','Sífilis rápida 1 - fecha','date'],
            ['sifilis_rapida1_resultado','Sífilis rápida 1 - resultado','text'],
            ['sifilis_rapida1_trimestre','Sífilis rápida 1 - trimestre','text'],
            ['sifilis_rapida2_fecha','Sífilis rápida 2 - fecha','date'],
            ['sifilis_rapida2_resultado','Sífilis rápida 2 - resultado','text'],
            ['sifilis_rapida2_trimestre','Sífilis rápida 2 - trimestre','text'],
            ['sifilis_rapida3_fecha','Sífilis rápida 3 - fecha','date'],
            ['sifilis_rapida3_resultado','Sífilis rápida 3 - resultado','text'],
            ['sifilis_rapida3_trimestre','Sífilis rápida 3 - trimestre','text'],
            ['sifilis_no_trep_fecha','No treponémica - fecha','date'],
            ['sifilis_no_trep_resultado','No treponémica - resultado','text'],
            ['sifilis_no_trep_trimestre','No treponémica - trimestre','text'],
        ],

        'Otros paraclínicos' => [
            ['urocultivo_fecha','Urocultivo - fecha','date'],
            ['urocultivo_resultado','Urocultivo - resultado','text'],
            ['glicemia_fecha','Glicemia - fecha','date'],
            ['glicemia_resultado','Glicemia - resultado','text'],
            ['pto_glucosa_fecha','PTO glucosa - fecha','date'],
            ['pto_glucosa_resultado','PTO glucosa - resultado','text'],
            ['hemoglobina_fecha','Hemoglobina - fecha','date'],
            ['hemoglobina_resultado','Hemoglobina - resultado','text'],
            ['hemoclasificacion_resultado','Hemoclasificación - resultado','text'],
            ['ag_hbs_fecha','Ag HBs - fecha','date'],
            ['ag_hbs_resultado','Ag HBs - resultado','text'],
            ['toxoplasma_fecha','Toxoplasma - fecha','date'],
            ['toxoplasma_resultado','Toxoplasma - resultado','text'],
            ['rubeola_fecha','Rubéola - fecha','date'],
            ['rubeola_resultado','Rubéola - resultado','text'],
            ['citologia_fecha','Citología - fecha','date'],
            ['citologia_resultado','Citología - resultado','text'],
            ['frotis_vaginal_fecha','Frotis vaginal - fecha','date'],
            ['frotis_vaginal_resultado','Frotis vaginal - resultado','text'],
            ['estreptococo_fecha','Estreptococo - fecha','date'],
            ['estreptococo_resultado','Estreptococo - resultado','text'],
            ['malaria_fecha','Malaria - fecha','date'],
            ['malaria_resultado','Malaria - resultado','text'],
            ['chagas_fecha','Chagas - fecha','date'],
            ['chagas_resultado','Chagas - resultado','text'],
        ],

        'Vacunas y controles' => [
            ['vac_influenza_fecha','Vacuna Influenza - fecha','date'],
            ['vac_toxoide_fecha','Vacuna Toxoide - fecha','date'],
            ['vac_dpt_acelular_fecha','Vacuna DPT acelular - fecha','date'],
            ['consulta_odontologica_fecha','Consulta odontológica - fecha','date'],
        ],

        'Ecos y Suministros' => [
            ['eco_translucencia','Eco translucencia','text'],
            ['eco_anomalias','Eco anomalías','text'],
            ['eco_otras','Eco otras','text'],
            ['suministro_acido_folico','Ácido fólico','select',$optSN],
            ['suministro_calcio','Calcio','select',$optSN],
            ['suministro_hierro','Hierro','select',$optSN],
            ['suministro_asa','ASA','select',$optSN],
            ['desparasitacion_fecha','Desparasitación - fecha','date'],
            ['informacion_en_salud','Información en salud','select',$optSN],
        ],

        'Controles prenatales (CPN)' => [
            ['cpn1_fecha','CPN1 - fecha','date'],['cpn1_quien','CPN1 - quién','text'],
            ['cpn2_fecha','CPN2 - fecha','date'],['cpn2_quien','CPN2 - quién','text'],
            ['cpn3_fecha','CPN3 - fecha','date'],['cpn3_quien','CPN3 - quién','text'],
            ['cpn4_fecha','CPN4 - fecha','date'],['cpn4_quien','CPN4 - quién','text'],
            ['cpn5_fecha','CPN5 - fecha','date'],['cpn5_quien','CPN5 - quién','text'],
            ['cpn6_fecha','CPN6 - fecha','date'],['cpn6_quien','CPN6 - quién','text'],
            ['cpn7_fecha','CPN7 - fecha','date'],['cpn7_quien','CPN7 - quién','text'],
            ['cpn8_fecha','CPN8 - fecha','date'],['cpn8_quien','CPN8 - quién','text'],
            ['cpn9_fecha','CPN9 - fecha','date'],['cpn9_quien','CPN9 - quién','text'],
            ['num_total_cpn','Núm. total CPN','number'],
            ['ultimo_cpn','Último CPN','text'],
        ],

        'Parto y RN' => [
            ['parto_tipo','Tipo de parto','text'],
            ['parto_sem_gest','Semanas gestacionales parto','text'],
            ['parto_complicaciones','Complicaciones parto','text'],
            ['uci_materna','UCI materna','select',$optSN],
            ['its_intraparto_toma','ITS intraparto - toma','select',$optSN],
            ['its_intraparto_positivo','ITS intraparto - positivo','select',$optSN],
            ['defuncion_fecha','Defunción - fecha','date'],
            ['defuncion_causa','Defunción - causa','text'],
            ['multiplicidad_embarazo','Multiplicidad embarazo','text'],

            ['rn1_registro_civil','RN1 - registro civil','select',$optSN],
            ['rn1_nombre','RN1 - nombre','text'],
            ['rn1_sexo','RN1 - sexo','text'],
            ['rn1_peso','RN1 - peso (g)','number'],
            ['rn1_condicion','RN1 - condición','text'],
            ['rn1_tsh','RN1 - TSH','text'],
            ['rn1_hipotiroideo_dx','RN1 - hipotiroideo Dx','select',$optSN],
            ['rn1_trat_hipotiroideo','RN1 - trat. hipotiroideo','select',$optSN],
            ['rn1_uci','RN1 - UCI','select',$optSN],
            ['rn1_vac_bcg','RN1 - VAC BCG','select',$optSN],
            ['rn1_vac_hepb','RN1 - VAC HEPB','select',$optSN],

            ['rn2_registro_civil','RN2 - registro civil','select',$optSN],
            ['rn2_nombre','RN2 - nombre','text'],
            ['rn2_sexo','RN2 - sexo','text'],
            ['rn2_peso','RN2 - peso (g)','number'],
            ['rn2_condicion','RN2 - condición','text'],
            ['rn2_tsh','RN2 - TSH','text'],
            ['rn2_hipotiroideo_dx','RN2 - hipotiroideo Dx','select',$optSN],
            ['rn2_trat_hipotiroideo','RN2 - trat. hipotiroideo','select',$optSN],
            ['rn2_uci','RN2 - UCI','select',$optSN],
            ['rn2_vac_bcg','RN2 - VAC BCG','select',$optSN],
            ['rn2_vac_hepb','RN2 - VAC HEPB','select',$optSN],
        ],

       // ==========================================================
// ✅ POSTPARTO (UNA SOLA SECCIÓN)
// ==========================================================
'Postparto' => [

    // ===== Datos del menor =====
    ['pp_menor_tipo_documento','Menor - Tipo de documento','text'],
    ['pp_menor_numero_identificacion','Menor - No. Identificación','text'],
    ['pp_menor_apellido_1','Menor - Apellido 1','text'],
    ['pp_menor_apellido_2','Menor - Apellido 2','text'],
    ['pp_menor_nombre_1','Menor - Nombre 1','text'],
    ['pp_menor_nombre_2','Menor - Nombre 2','text'],
    ['pp_menor_fecha_nacimiento','Menor - Fecha nacimiento','date'],
    ['pp_menor_edad','Menor - Edad','number'],

    // ===== Evaluación clínica completa (menor) =====
    ['pp_menor_temperatura','Menor - Temperatura','number'],
    ['pp_menor_frecuencia_cardiaca','Menor - Frecuencia cardíaca','number'],
    ['pp_menor_frecuencia_respiratoria','Menor - Frecuencia respiratoria','number'],
    ['pp_menor_peso','Menor - Peso','number'],
    ['pp_menor_talla','Menor - Talla','number'],
    ['pp_menor_imc','Menor - IMC','number'],
    ['pp_menor_perimetro_cefalico','Menor - Perímetro cefálico','number'],
    ['pp_menor_examen_fisico','Menor - Examen físico','textarea'],

    // ===== Alimentación y lactancia =====
    ['pp_tipo_alimentacion','Tipo de alimentación','text'],
    ['pp_educacion_tecnica_agarre','Educación técnica de agarre','select',$optSN],

    // ===== Vacunación =====
    ['pp_vac_bcg','BCG','select',$optSN],
    ['pp_vac_hepatitis_b','Hepatitis B','select',$optSN],

    // ===== Signos de alarma (menor) =====
    ['pp_alarma_fiebre','Fiebre','select',$optSN],
    ['pp_alarma_dificultad_respiratoria','Dificultad respiratoria','select',$optSN],
    ['pp_alarma_vomitos','Vómitos','select',$optSN],
    ['pp_alarma_alteraciones_ombligo','Alteraciones en el ombligo','select',$optSN],
    ['pp_clasificacion_riesgo_menor','Clasificación del riesgo','text'],

    // ===== Registro y seguimiento =====
    ['pp_programacion_proximos_controles','Programación de próximos controles','textarea'],

    // ===== Valoración general (madre) =====
    ['pp_madre_presion_arterial','Madre - Presión arterial','text'],
    ['pp_madre_frecuencia_cardiaca','Madre - Frecuencia cardíaca','number'],
    ['pp_madre_frecuencia_respiratoria','Madre - Frecuencia respiratoria','number'],
    ['pp_madre_temperatura','Madre - Temperatura','number'],
    ['pp_madre_examen_fisico','Madre - Examen físico','textarea'],

    // ===== Salud mental y emocional =====
    ['pp_tamizaje_depresion_posparto','Tamizaje de depresión posparto','text'],
    ['pp_evaluacion_ansiedad_estres','Evaluación ansiedad/estrés/sobrecarga','textarea'],
    ['pp_redes_apoyo','Identificación de redes de apoyo','textarea'],

    // ===== Nutrición y recuperación física =====
    ['pp_entrega_hierro','Entrega de hierro','select',$optSN],
    ['pp_entrega_acido_folico','Entrega de ácido fólico','select',$optSN],
    ['pp_entrega_calcio','Entrega de calcio','select',$optSN],

    // ===== Medicación y adherencia =====
    ['pp_revision_medicamentos','Revisión de medicamentos prescritos','textarea'],
    ['pp_educacion_uso_medicamentos','Educación sobre uso adecuado y efectos secundarios','textarea'],

    // ===== Planificación familiar =====
    ['pp_consejeria_metodos_anticonceptivos','Consejería en métodos anticonceptivos','textarea'],
    ['pp_fecha_colocacion_metodo','Fecha de colocación del método','date'],
    ['pp_metodo','Método','text'],
],

    ];

    // Responsivo: 4 campos por fila -> col-md-3
    $colClass = 'col-md-3';
@endphp

<div class="card shadow-sm">
  <div class="card-body">

    {{-- Resumen del caso --}}
    <div class="mb-3 p-3 border rounded bg-light">
      <div class="row">
        <div class="col-md-6"><strong>Paciente:</strong> {{ trim("{$ges->primer_nombre} {$ges->segundo_nombre} {$ges->primer_apellido} {$ges->segundo_apellido}") }}</div>
        <div class="col-md-3"><strong>Tipo ID:</strong> {{ $ges->tipo_de_identificacion_de_la_usuaria }}</div>
        <div class="col-md-3"><strong>Número ID:</strong> {{ $ges->no_id_del_usuario }}</div>
      </div>
      <div class="row mt-1">
        <div class="col-md-3"><strong>F. Nac:</strong> {{ $ges->fecha_de_nacimiento }}</div>
        <div class="col-md-3"><strong>FPP:</strong> {{ $ges->fecha_probable_de_parto }}</div>
        <div class="col-md-6"><strong>IPS:</strong> {{ $ges->codigo_de_habilitacion_ips_primaria_de_la_gestante }}</div>
      </div>
    </div>

    {{-- Errores --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if(strtoupper($method)==='PUT')
        @method('PUT')
      @endif

      <div class="row">
        <div class="form-group col-md-3">
          <label>Fecha de seguimiento</label>
          <input type="date" name="fecha_seguimiento" class="form-control" value="{{ $val('fecha_seguimiento') }}">
        </div>
        <div class="form-group col-md-9">
          <label>Observaciones</label>
          <textarea name="observaciones" class="form-control" rows="2">{{ $val('observaciones') }}</textarea>
        </div>
      </div>

      <div id="accordion">
        @php $secIndex = 0; @endphp
        @foreach($SECCIONES as $titulo => $campos)
          @php $secIndex++; $open = $secIndex===1 ? 'show' : ''; @endphp

          <div class="card mb-2">
            <div class="card-header" id="hd-{{ $secIndex }}">
              <h5 class="mb-0 d-flex align-items-center justify-content-between">
                <button class="btn btn-link {{ $open ? '' : 'collapsed' }}" type="button" data-toggle="collapse" data-target="#sec-{{ $secIndex }}">
                  {{ $titulo }}
                </button>
              </h5>
            </div>

            <div id="sec-{{ $secIndex }}" class="collapse {{ $open }}" data-parent="#accordion">
              <div class="card-body">
                @php $chunked = array_chunk($campos, 4); @endphp

                @foreach($chunked as $fila)
                  <div class="form-row">
                    @foreach($fila as $cfg)
                      @php
                        $name  = $cfg[0] ?? '';
                        $label = $cfg[1] ?? $name;
                        $type  = $cfg[2] ?? 'text';
                        $opts  = $cfg[3] ?? null;

                        $current = $val($name);

                        $isResultado = Str::contains(Str::lower($label.' '.$name), 'resultado');

                        $descName    = $name . '_desc';
                        $descCurrent = $val($descName);

                        $isPdf = $isPdfValue($current);
                        $fileInputName = $name . '_file';

                        $initialMode = $isResultado ? ($isPdf ? 'pdf' : 'text') : 'text';
                        $fileUrlCurrent = $isPdf ? $filePublicUrl($current) : null;

                        $resultadoOpts = $isResultado ? $getResultadoOpts($name, $label) : [];
                      @endphp

                      <div class="form-group {{ $colClass }}">
                        <label class="d-flex align-items-center justify-content-between">
                          <span>{{ $label }}</span>

                          @if($isResultado)
                            <span class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                              <label class="btn btn-outline-secondary mode-btn {{ $initialMode==='text' ? 'active' : '' }}" data-target="{{ $name }}" data-mode="text">
                                <input type="radio" autocomplete="off" {{ $initialMode==='text' ? 'checked' : '' }}> Texto
                              </label>
                              <label class="btn btn-outline-secondary mode-btn {{ $initialMode==='pdf' ? 'active' : '' }}" data-target="{{ $name }}" data-mode="pdf">
                                <input type="radio" autocomplete="off" {{ $initialMode==='pdf' ? 'checked' : '' }}> PDF
                              </label>
                            </span>
                          @endif
                        </label>

                        @if($isResultado)
                          <input type="hidden" name="{{ $name }}" value="{{ $current }}">
                        @endif

                        {{-- MODO TEXTO --}}
                        <div class="resultado-text-wrap" id="wrap-text-{{ $name }}" @if($isResultado && $initialMode==='pdf') style="display:none" @endif>
                          @if($isResultado)
                            <select name="{{ $descName }}" class="form-control">
                              <option value="">-- Seleccione resultado --</option>
                              @foreach($resultadoOpts as $ov => $ot)
                                <option value="{{ $ov }}" {{ ((string)$descCurrent===(string)$ov) ? 'selected' : '' }}>
                                  {{ $ot }}
                                </option>
                              @endforeach
                            </select>
                          @else
                            @if($type === 'textarea')
                              <textarea name="{{ $name }}" class="form-control" rows="2">{{ $current }}</textarea>
                            @elseif($type === 'select')
                              <select name="{{ $name }}" class="form-control">
                                <option value="">--</option>
                                @foreach($opts as $ov => $ot)
                                  <option value="{{ $ov }}" {{ ((string)$current===(string)$ov)?'selected':'' }}>{{ $ot }}</option>
                                @endforeach
                              </select>
                            @else
                              <input type="{{ $type }}" name="{{ $name }}" class="form-control" value="{{ $current }}">
                            @endif
                          @endif
                        </div>

                        {{-- MODO PDF --}}
                        @if($isResultado)
                          <div class="resultado-pdf-wrap" id="wrap-pdf-{{ $name }}" @if($initialMode!=='pdf') style="display:none" @endif>
                            <div class="custom-file mb-2">
                              <input type="file" accept="application/pdf" class="custom-file-input" id="{{ $fileInputName }}" name="{{ $fileInputName }}">
                              <label class="custom-file-label" for="{{ $fileInputName }}">Seleccionar PDF...</label>
                            </div>

                            @if($fileUrlCurrent)
                              <div class="border rounded p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                  <strong class="small mb-0">PDF cargado</strong>
                                  <a class="btn btn-xs btn-outline-primary" href="{{ $fileUrlCurrent }}" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Abrir en nueva pestaña
                                  </a>
                                </div>
                                <iframe src="{{ $fileUrlCurrent }}#toolbar=1" style="width:100%;height:300px;border:0;"></iframe>
                              </div>
                            @endif

                            <small class="form-text text-muted">
                              Se guardará la ruta del PDF. Para registrar el resultado (reactivo/negativo/etc), cambia a “Texto”.
                            </small>
                          </div>
                        @endif
                      </div>
                    @endforeach
                  </div>
                @endforeach

              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('ges_tipo1.show', $ges->id) }}" class="btn btn-secondary mr-2">
          <i class="fas fa-times"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </form>
  </div>
</div>

@push('css')
<style>
  .custom-file-input ~ .custom-file-label::after { content: "Buscar"; }
</style>
@endpush

@push('js')
<script>
  document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.mode-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        const target = e.currentTarget.getAttribute('data-target');
        const mode   = e.currentTarget.getAttribute('data-mode');

        const textWrap = document.getElementById('wrap-text-' + target);
        const pdfWrap  = document.getElementById('wrap-pdf-' + target);

        if (textWrap && pdfWrap) {
          if (mode === 'text') {
            textWrap.style.display = '';
            pdfWrap.style.display  = 'none';
          } else {
            textWrap.style.display = 'none';
            pdfWrap.style.display  = '';
          }
        }
      });
    });

    document.querySelectorAll('.custom-file-input').forEach(function(input) {
      input.addEventListener('change', function(e) {
        const fileName = e.target.files.length ? e.target.files[0].name : 'Seleccionar PDF...';
        const label = e.target.nextElementSibling;
        if (label && label.classList.contains('custom-file-label')) {
          label.textContent = fileName;
        }
      });
    });

  });
</script>
@endpush
