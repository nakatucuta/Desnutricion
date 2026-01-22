<?php
// Cada sección tiene: title, rows => [ [ ['name','label','type'], ... ], ... ]
// type: text|date|number|select|textarea
// Para selects: 'options' => ['valor' => 'Etiqueta', ...]
return [

  // ====== Metadatos clínicos clave (al inicio del form) ======
  'METADATOS' => [
    'title' => 'Metadatos del Seguimiento',
    'rows' => [
      [
        ['name' => 'fecha_contacto_info', 'label' => 'Fecha de Contacto (info)', 'type' => 'date'],
        ['name' => 'tipo_contacto_info',  'label' => 'Tipo Contacto (1=Tel/2=Dom/3=Otro)', 'type' => 'number'],
        ['name' => 'estado_info',         'label' => 'Estado', 'type' => 'text'],
        ['name' => 'proximo_contacto_info','label' => 'Próximo contacto', 'type' => 'date'],
      ],
    ],
    'help' => 'Estos campos son informativos; los de verdad están arriba del formulario.',
  ],

  // ====== Identificación y básicos (de tu lista) ======
  'IDENTIFICACION' => [
    'title' => 'Identificación y Datos Básicos',
    'rows' => [
      [
        ['name' => 'tipo_documento', 'label' => 'Tipo de documento de identidad', 'type' => 'text'],
        ['name' => 'numero_identificacion', 'label' => 'No. de identificación', 'type' => 'text'],
        ['name' => 'apellido_1', 'label' => 'Apellido 1', 'type' => 'text'],
        ['name' => 'apellido_2', 'label' => 'Apellido 2', 'type' => 'text'],
      ],
      [
        ['name' => 'nombre_1', 'label' => 'Nombre 1', 'type' => 'text'],
        ['name' => 'nombre_2', 'label' => 'Nombre 2', 'type' => 'text'],
        ['name' => 'fecha_nacimiento', 'label' => 'Fecha de Nacimiento', 'type' => 'date'],
        ['name' => 'edad_anios', 'label' => 'Edad (años)', 'type' => 'number'],
      ],
      [
        ['name' => 'sexo', 'label' => 'Sexo', 'type' => 'select', 'options' => ['F'=>'Femenino','M'=>'Masculino','I'=>'Intersexual','ND'=>'No define']],
        ['name' => 'regimen_afiliacion', 'label' => 'Régimen Afiliación', 'type' => 'text'],
        ['name' => 'pertenencia_etnica', 'label' => 'Pertenencia Étnica', 'type' => 'text'],
        ['name' => 'grupo_poblacional', 'label' => 'Grupo Poblacional', 'type' => 'text'],
      ],
      [
        ['name' => 'departamento_residencia', 'label' => 'Departamento Residencia', 'type' => 'text'],
        ['name' => 'municipio_residencia', 'label' => 'Municipio de Residencia', 'type' => 'text'],
        ['name' => 'zona', 'label' => 'Zona', 'type' => 'text'],
        ['name' => 'etnia', 'label' => 'Etnia', 'type' => 'text'],
      ],
      [
        ['name' => 'asentamiento', 'label' => 'Asentamiento/Ranchería/Comunidad', 'type' => 'text'],
        ['name' => 'telefono_usuaria', 'label' => 'Teléfono usuaria', 'type' => 'text'],
        ['name' => 'direccion', 'label' => 'Dirección', 'type' => 'text'],
        ['name' => 'nivel_educativo', 'label' => 'Nivel Educativo', 'type' => 'text'],
      ],
      [
        ['name' => 'discapacidad', 'label' => 'Discapacidad', 'type' => 'text'],
        ['name' => 'mujer_cabeza_hogar', 'label' => 'Mujer cabeza de Hogar', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'ocupacion', 'label' => 'Ocupación', 'type' => 'text'],
        ['name' => 'estado_civil', 'label' => 'Estado Civil', 'type' => 'text'],
      ],
      [
        ['name' => 'control_tradicional', 'label' => 'Control Tradicional', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'gestante_renuente', 'label' => 'Gestante Renuente', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'inasistente', 'label' => 'Inasistente', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'ips_primaria', 'label' => 'Nombre de la IPS Primaria', 'type' => 'text'],
      ],
    ],
  ],

  // ====== Embarazo / control prenatal ======
  'PRENATAL' => [
    'title' => 'Control Prenatal',
    'rows' => [
      [
        ['name' => 'fecha_ingreso_cpn', 'label' => 'Fecha de Ingreso al CPN', 'type' => 'date'],
        ['name' => 'fum', 'label' => 'FUM', 'type' => 'date'],
        ['name' => 'fpp', 'label' => 'FPP', 'type' => 'date'],
        ['name' => 'dias_para_parto', 'label' => 'Días para el parto', 'type' => 'number'],
      ],
      [
        ['name' => 'alarma', 'label' => 'Alarma', 'type' => 'text'],
        ['name' => 'edad_gest_inicio_control', 'label' => 'Edad Gest. Inicio Control (semanas)', 'type' => 'number'],
        ['name' => 'trimestre_inicio_control', 'label' => 'Trimestre inicio control', 'type' => 'text'],
        ['name' => 'formula_obstetrica', 'label' => 'Fórmula obstétrica (G P C A M V)', 'type' => 'text'],
      ],
    ],
  ],

  // ====== Antecedentes personales y obstétricos ======
  'ANTECEDENTES' => [
    'title' => 'Antecedentes',
    'rows' => [
      [
        ['name' => 'hipertension_arterial', 'label' => 'Hipertensión arterial', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'diabetes', 'label' => 'Diabetes', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'vih', 'label' => 'VIH', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'sifilis', 'label' => 'Sífilis', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
      ],
      [
        ['name' => 'tuberculosis', 'label' => 'Tuberculosis', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'otras_condiciones_graves', 'label' => 'Otras condiciones médicas graves', 'type' => 'textarea'],
        ['name' => 'apoyo_familiar', 'label' => 'Apoyo familiar', 'type' => 'text'],
        ['name' => 'embarazo_deseado', 'label' => 'Embarazo deseado', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
      ],
      [
        ['name' => 'habitos_riesgo', 'label' => 'Hábitos de riesgo', 'type' => 'textarea'],
        ['name' => 'violencia', 'label' => '¿Víctima de violencia?', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'abuso_sexual', 'label' => '¿Abuso sexual?', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'periodo_intergenesico', 'label' => 'Periodo Intergenésico', 'type' => 'number'],
      ],
      [
        ['name' => 'peso_inicial', 'label' => 'Peso Inicial (kg)', 'type' => 'number'],
        ['name' => 'talla', 'label' => 'Talla (m)', 'type' => 'number'],
        ['name' => 'imc', 'label' => 'IMC', 'type' => 'number'],
        ['name' => 'clasificacion_imc', 'label' => 'Clasificación IMC', 'type' => 'text'],
      ],
      [
        ['name' => 'riesgos_psicosociales', 'label' => 'Riesgos psicosociales', 'type' => 'textarea'],
        ['name' => 'ive_causales', 'label' => '¿Se Identifican causales para IVE?', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'clasificacion_riesgo', 'label' => 'Clasificación del riesgo', 'type' => 'text'],
        ['name' => 'alto_riesgo_causas', 'label' => 'Causas de Alto Riesgo', 'type' => 'textarea'],
      ],
      [
        ['name' => 'otras_cuales', 'label' => 'Otras ¿Cuáles?', 'type' => 'textarea'],
        ['name' => 'remitida_especialista', 'label' => 'Remitida a especialista', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
      ],
    ],
  ],

  // ====== Tamizajes VIH / Sífilis (primer/segundo/tercer) ======
  'TAMIZAJES_VIH_SIFILIS' => [
    'title' => 'Tamizajes VIH y Sífilis',
    'rows' => [
      // Asesoría VIH
      [
        ['name' => 'asesoria_vih', 'label' => 'Asesoría Prueba VIH', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'asesoria_vih_trimestre', 'label' => 'Trimestre Asesoría VIH', 'type' => 'text'],
      ],
      // VIH Primer tamizaje
      [
        ['name' => 'vih_tamiz1_fecha', 'label' => 'Fecha Toma VIH 1er Tamizaje', 'type' => 'date'],
        ['name' => 'vih_tamiz1_resultado', 'label' => 'Resultado VIH 1er Tamizaje', 'type' => 'text'],
        ['name' => 'vih_tamiz1_trimestre', 'label' => 'Trim. VIH 1er Tamizaje', 'type' => 'text'],
      ],
      // VIH Segundo
      [
        ['name' => 'vih_tamiz2_fecha', 'label' => 'Fecha Toma VIH 2do Tamizaje', 'type' => 'date'],
        ['name' => 'vih_tamiz2_resultado', 'label' => 'Resultado VIH 2do Tamizaje', 'type' => 'text'],
        ['name' => 'vih_tamiz2_trimestre', 'label' => 'Trim. VIH 2do Tamizaje', 'type' => 'text'],
      ],
      // VIH Tercero
      [
        ['name' => 'vih_tamiz3_fecha', 'label' => 'Fecha Toma VIH 3er Tamizaje', 'type' => 'date'],
        ['name' => 'vih_tamiz3_resultado', 'label' => 'Resultado VIH 3er Tamizaje', 'type' => 'text'],
        ['name' => 'vih_tamiz3_trimestre', 'label' => 'Trim. VIH 3er Tamizaje', 'type' => 'text'],
      ],

      // Sífilis Rápidas (1,2,3)
      [
        ['name' => 'sifilis_rapida1_fecha', 'label' => 'Fecha 1ra Prueba Treponémica Rápida', 'type' => 'date'],
        ['name' => 'sifilis_rapida1_resultado', 'label' => 'Resultado 1ra Rápida', 'type' => 'text'],
        ['name' => 'sifilis_rapida1_trimestre', 'label' => 'Trim. 1ra Rápida', 'type' => 'text'],
      ],
      [
        ['name' => 'sifilis_rapida2_fecha', 'label' => 'Fecha 2da Prueba Treponémica Rápida', 'type' => 'date'],
        ['name' => 'sifilis_rapida2_resultado', 'label' => 'Resultado 2da Rápida', 'type' => 'text'],
        ['name' => 'sifilis_rapida2_trimestre', 'label' => 'Trim. 2da Rápida', 'type' => 'text'],
      ],
      [
        ['name' => 'sifilis_rapida3_fecha', 'label' => 'Fecha 3ra Prueba Treponémica Rápida', 'type' => 'date'],
        ['name' => 'sifilis_rapida3_resultado', 'label' => 'Resultado 3ra Rápida', 'type' => 'text'],
        ['name' => 'sifilis_rapida3_trimestre', 'label' => 'Trim. 3ra Rápida', 'type' => 'text'],
      ],

      // Confirmatorias / No treponémicas
      [
        ['name' => 'vih_confirmatoria_fecha', 'label' => 'Fecha prueba confirmatoria VIH', 'type' => 'date'],
        ['name' => 'vih_confirmatoria_trimestre', 'label' => 'Trimestre confirmatoria VIH', 'type' => 'text'],
      ],
      [
        ['name' => 'sifilis_no_trep_fecha', 'label' => 'Fecha prueba No Treponémica', 'type' => 'date'],
        ['name' => 'sifilis_no_trep_resultado', 'label' => 'Resultado No Treponémica', 'type' => 'text'],
        ['name' => 'sifilis_no_trep_trimestre', 'label' => 'Trimestre No Treponémica', 'type' => 'text'],
      ],
    ],
  ],

  // ====== Otros laboratorios ======
  'LABS' => [
    'title' => 'Laboratorios y Tamizajes Adicionales',
    'rows' => [
      [
        ['name' => 'urocultivo_fecha', 'label' => 'Fecha Urocultivo', 'type' => 'date'],
        ['name' => 'urocultivo_resultado', 'label' => 'Resultado Urocultivo', 'type' => 'text'],
        ['name' => 'glicemia_fecha', 'label' => 'Fecha Glicemia', 'type' => 'date'],
        ['name' => 'glicemia_resultado', 'label' => 'Resultado Glicemia', 'type' => 'text'],
      ],
      [
        ['name' => 'pto_glucosa_fecha', 'label' => 'Fecha PTOG (24-28 sem)', 'type' => 'date'],
        ['name' => 'pto_glucosa_resultado', 'label' => 'Resultado PTOG', 'type' => 'text'],
        ['name' => 'hemoglobina_fecha', 'label' => 'Fecha Hemoglobina', 'type' => 'date'],
        ['name' => 'hemoglobina_resultado', 'label' => 'Resultado Hemoglobina', 'type' => 'text'],
      ],
      [
        ['name' => 'hemoclasificacion_resultado', 'label' => 'Hemoclasificación (Factor RH)', 'type' => 'text'],
        ['name' => 'ag_hbs_fecha', 'label' => 'Fecha Ag. Hepatitis B', 'type' => 'date'],
        ['name' => 'ag_hbs_resultado', 'label' => 'Resultado Ag. Hepatitis B', 'type' => 'text'],
        ['name' => 'toxoplasma_fecha', 'label' => 'Fecha Tamizaje Toxoplasma', 'type' => 'date'],
      ],
      [
        ['name' => 'toxoplasma_resultado', 'label' => 'Resultado Toxoplasma', 'type' => 'text'],
        ['name' => 'rubeola_fecha', 'label' => 'Fecha prueba Rubeola', 'type' => 'date'],
        ['name' => 'rubeola_resultado', 'label' => 'Resultado Rubeola', 'type' => 'text'],
        ['name' => 'citologia_fecha', 'label' => 'Fecha Citología', 'type' => 'date'],
      ],
      [
        ['name' => 'citologia_resultado', 'label' => 'Resultado Citología', 'type' => 'text'],
        ['name' => 'frotis_vaginal_fecha', 'label' => 'Fecha Frotis Vaginal', 'type' => 'date'],
        ['name' => 'frotis_vaginal_resultado', 'label' => 'Resultado Frotis Vaginal', 'type' => 'text'],
        ['name' => 'estreptococo_fecha', 'label' => 'Fecha Tamizaje Estreptococo (35-37)', 'type' => 'date'],
      ],
      [
        ['name' => 'estreptococo_resultado', 'label' => 'Resultado Estreptococo', 'type' => 'text'],
        ['name' => 'malaria_fecha', 'label' => 'Fecha Gota Gruesa (Malaria)', 'type' => 'date'],
        ['name' => 'malaria_resultado', 'label' => 'Resultado Malaria', 'type' => 'text'],
        ['name' => 'chagas_fecha', 'label' => 'Fecha Tamizaje Chagas', 'type' => 'date'],
      ],
      [
        ['name' => 'chagas_resultado', 'label' => 'Resultado Chagas', 'type' => 'text'],
      ],
    ],
  ],

  // ====== Vacunación y odontología / ecografías / micronutrientes ======
  'VACUNAS_ECOS' => [
    'title' => 'Vacunación, Odontología, Ecografías y Micronutrientes',
    'rows' => [
      [
        ['name' => 'vac_influenza_fecha', 'label' => 'FECHA APLICACIÓN Influenza (≥14 sem)', 'type' => 'date'],
        ['name' => 'vac_toxoide_fecha', 'label' => 'FECHA APLICACIÓN Toxoide', 'type' => 'date'],
        ['name' => 'vac_dpt_acelular_fecha', 'label' => 'FECHA APLICACIÓN DPT Acelular (26 sem)', 'type' => 'date'],
        ['name' => 'consulta_odontologica_fecha', 'label' => 'Fecha consulta odontológica', 'type' => 'date'],
      ],
      [
        ['name' => 'eco_translucencia', 'label' => 'Eco translucencia nucal (10.6–13.6)', 'type' => 'date'],
        ['name' => 'eco_anomalias', 'label' => 'Eco anomalias (18–23)', 'type' => 'date'],
        ['name' => 'eco_otras', 'label' => 'Otras ecografías (fecha)', 'type' => 'text'],
        ['name' => 'suministro_acido_folico', 'label' => 'Suministro Ácido Fólico', 'type' => 'date'],
      ],
      [
        ['name' => 'suministro_calcio', 'label' => 'Suministro Calcio (≥14 sem)', 'type' => 'date'],
        ['name' => 'suministro_hierro', 'label' => 'Suministro Hierro', 'type' => 'date'],
        ['name' => 'suministro_asa', 'label' => 'Suministro ASA', 'type' => 'date'],
        ['name' => 'desparasitacion_fecha', 'label' => 'Fecha Desparasitación (Albendazol)', 'type' => 'date'],
      ],
      [
        ['name' => 'informacion_en_salud', 'label' => 'Información en Salud (observaciones)', 'type' => 'textarea'],
      ],
    ],
  ],

  // ====== Controles prenatales (múltiples) ======
  'CONTROLES_PRENATALES' => [
    'title' => 'Atención del Control Prenatal (CPN)',
    'rows' => [
      // Usa patrón repetible: fecha_N y quien_N
      [
        ['name' => 'cpn1_fecha', 'label' => 'Fecha 1er Control', 'type' => 'date'],
        ['name' => 'cpn1_quien', 'label' => 'Quién realiza (Md/Enf/GO/Otro)', 'type' => 'text'],
        ['name' => 'cpn2_fecha', 'label' => 'Fecha 2do Control', 'type' => 'date'],
        ['name' => 'cpn2_quien', 'label' => 'Quién realiza', 'type' => 'text'],
      ],
      [
        ['name' => 'cpn3_fecha', 'label' => 'Fecha 3er Control', 'type' => 'date'],
        ['name' => 'cpn3_quien', 'label' => 'Quién realiza', 'type' => 'text'],
        ['name' => 'cpn4_fecha', 'label' => 'Fecha 4to Control', 'type' => 'date'],
        ['name' => 'cpn4_quien', 'label' => 'Quién realiza', 'type' => 'text'],
      ],
      [
        ['name' => 'cpn5_fecha', 'label' => 'Fecha 5to Control', 'type' => 'date'],
        ['name' => 'cpn5_quien', 'label' => 'Quién realiza', 'type' => 'text'],
        ['name' => 'cpn6_fecha', 'label' => 'Fecha 6to Control', 'type' => 'date'],
        ['name' => 'cpn6_quien', 'label' => 'Quién realiza', 'type' => 'text'],
      ],
      [
        ['name' => 'cpn7_fecha', 'label' => 'Fecha 7mo Control', 'type' => 'date'],
        ['name' => 'cpn7_quien', 'label' => 'Quién realiza', 'type' => 'text'],
        ['name' => 'cpn8_fecha', 'label' => 'Fecha 8vo Control', 'type' => 'date'],
        ['name' => 'cpn8_quien', 'label' => 'Quién realiza', 'type' => 'text'],
      ],
      [
        ['name' => 'cpn9_fecha', 'label' => 'Fecha 9no Control', 'type' => 'date'],
        ['name' => 'cpn9_quien', 'label' => 'Quién realiza', 'type' => 'text'],
        ['name' => 'num_total_cpn', 'label' => 'Número total de CPN', 'type' => 'number'],
        ['name' => 'ultimo_cpn', 'label' => 'Último CPN (fecha)', 'type' => 'date'],
      ],
    ],
  ],

  // ====== Atención especializada (Fechas por especialidad) ======
  'ATENCION_ESPECIALIZADA' => [
    'title' => 'Atención Especializada',
    'rows' => [
      [
        ['name' => 'cons_ginecologia_1', 'label' => 'Fecha 1ra Ginecología', 'type' => 'date'],
        ['name' => 'cons_ginecologia_2', 'label' => 'Fecha 2da Ginecología', 'type' => 'date'],
        ['name' => 'cons_ginecologia_3', 'label' => 'Fecha 3ra Ginecología', 'type' => 'date'],
        ['name' => 'cons_nutricion', 'label' => 'Fecha Nutrición', 'type' => 'date'],
      ],
      [
        ['name' => 'cons_psicologia', 'label' => 'Fecha Psicología', 'type' => 'date'],
        ['name' => 'cons_otro_especialista', 'label' => 'Fecha Otro Especialista', 'type' => 'date'],
        ['name' => 'cons_otro_quien', 'label' => '¿Quién?', 'type' => 'text'],
        ['name' => 'especialistas_describe', 'label' => 'Describa especialistas que han atendido', 'type' => 'textarea'],
      ],
    ],
  ],

  // ====== Parto y RN (resumen) ======
  'PARTO_RN' => [
    'title' => 'Parto y Recién Nacido(s)',
    'rows' => [
      [
        ['name' => 'parto_tipo', 'label' => 'Tipo de Parto', 'type' => 'text'],
        ['name' => 'parto_sem_gest', 'label' => 'Semanas de Gestación al Parto', 'type' => 'number'],
        ['name' => 'parto_complicaciones', 'label' => 'Complicaciones durante el parto', 'type' => 'textarea'],
        ['name' => 'uci_materna', 'label' => 'UCI Materna', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
      ],
      [
        ['name' => 'its_intraparto_toma', 'label' => '¿Toma ITS intraparto?', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'its_intraparto_positivo', 'label' => 'Resultado POSITIVO', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'defuncion_fecha', 'label' => 'Fecha (si defunción)', 'type' => 'date'],
        ['name' => 'defuncion_causa', 'label' => 'Causa de la defunción', 'type' => 'text'],
      ],
      [
        ['name' => 'multiplicidad_embarazo', 'label' => 'Multiplicidad del embarazo', 'type' => 'text'],
      ],
      // RN1
      [
        ['name' => 'rn1_registro_civil', 'label' => 'RN1 Registro Civil', 'type' => 'text'],
        ['name' => 'rn1_nombre', 'label' => 'RN1 Nombre', 'type' => 'text'],
        ['name' => 'rn1_sexo', 'label' => 'RN1 Sexo', 'type' => 'text'],
        ['name' => 'rn1_peso', 'label' => 'RN1 Peso al nacer (g)', 'type' => 'number'],
      ],
      [
        ['name' => 'rn1_condicion', 'label' => 'RN1 Condición', 'type' => 'text'],
        ['name' => 'rn1_tsh', 'label' => 'RN1 Toma TSH', 'type' => 'text'],
        ['name' => 'rn1_hipotiroideo_dx', 'label' => 'RN1 Dx. Hipotiroidismo', 'type' => 'text'],
        ['name' => 'rn1_trat_hipotiroideo', 'label' => 'RN1 Tto Hipotiroidismo', 'type' => 'text'],
      ],
      [
        ['name' => 'rn1_uci', 'label' => 'RN1 UCI Neonatal', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'rn1_vac_bcg', 'label' => 'RN1 Vacunación BCG', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'rn1_vac_hepb', 'label' => 'RN1 Vacuna Hep B', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
      ],
      // RN2
      [
        ['name' => 'rn2_registro_civil', 'label' => 'RN2 Registro Civil', 'type' => 'text'],
        ['name' => 'rn2_nombre', 'label' => 'RN2 Nombre', 'type' => 'text'],
        ['name' => 'rn2_sexo', 'label' => 'RN2 Sexo', 'type' => 'text'],
        ['name' => 'rn2_peso', 'label' => 'RN2 Peso al nacer (g)', 'type' => 'number'],
      ],
      [
        ['name' => 'rn2_condicion', 'label' => 'RN2 Condición', 'type' => 'text'],
        ['name' => 'rn2_tsh', 'label' => 'RN2 Toma TSH', 'type' => 'text'],
        ['name' => 'rn2_hipotiroideo_dx', 'label' => 'RN2 Dx. Hipotiroidismo', 'type' => 'text'],
        ['name' => 'rn2_trat_hipotiroideo', 'label' => 'RN2 Tto Hipotiroidismo', 'type' => 'text'],
      ],
      [
        ['name' => 'rn2_uci', 'label' => 'RN2 UCI Neonatal', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'rn2_vac_bcg', 'label' => 'RN2 Vacunación BCG', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
        ['name' => 'rn2_vac_hepb', 'label' => 'RN2 Vacuna Hep B', 'type' => 'select','options'=>['SI'=>'Sí','NO'=>'No']],
      ],
    ],
  ],

  // Puedes seguir añadiendo secciones y campos aquí si te falta alguno de tu lista:
  // 'OTRA_SECCION' => [ ... ]
];
