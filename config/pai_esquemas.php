<?php

return [
    'version' => 'CURSO-VIDA-2026-07-V1',

    /*
    | El curso determina el universo base. Las reglas transversales de mujer
    | en edad fertil y gestante pueden sumarse al curso actual.
    */
    'courses' => [
        'recien_nacido' => [
            'label' => 'Recién nacido',
            'min_age_days' => 0,
            'max_age_days' => 30,
        ],
        'primera_infancia' => [
            'label' => 'Primera infancia',
            'min_age_days' => 8,
            'max_age_months' => 71,
        ],
        'infancia' => [
            'label' => 'Infancia',
            'min_age_months' => 72,
            'max_age_months' => 143,
        ],
        'adolescencia' => [
            'label' => 'Adolescencia',
            'min_age_months' => 144,
            'max_age_months' => 215,
        ],
        'juventud' => [
            'label' => 'Juventud',
            'min_age_months' => 216,
            'max_age_months' => 347,
        ],
        'adultez' => [
            'label' => 'Adultez',
            'min_age_months' => 348,
            'max_age_months' => 719,
        ],
        'vejez' => [
            'label' => 'Vejez',
            'min_age_months' => 720,
        ],
    ],

    /*
    | Cada regla solo afecta el estado mientras su ventana de edad esta activa.
    | Las edades estan expresadas en meses completos salvo las reglas neonatales.
    */
    'rules' => [
        ['key' => 'bcg_rn', 'nombre' => 'BCG recién nacido', 'course' => 'recien_nacido', 'min_age_days' => 0, 'max_age_days' => 7, 'vacunas_ids' => [2], 'accepted_doses' => ['UNICA']],
        ['key' => 'hepatitis_b_rn', 'nombre' => 'Hepatitis B recién nacido', 'course' => 'recien_nacido', 'min_age_days' => 0, 'max_age_days' => 7, 'vacunas_ids' => [3, 28], 'accepted_doses' => ['RECIEN NACIDO', 'UNICA', 'PRIMERA DOSIS']],

        ['key' => 'pentavalente_1', 'nombre' => 'Pentavalente primera dosis', 'course' => 'primera_infancia', 'min_age_months' => 2, 'max_age_months' => 3, 'vacunas_ids' => [6, 7, 29, 30], 'accepted_doses' => ['PRIMERA DOSIS']],
        ['key' => 'polio_1', 'nombre' => 'Polio primera dosis', 'course' => 'primera_infancia', 'min_age_months' => 2, 'max_age_months' => 3, 'vacunas_ids' => [4, 7, 29, 30, 31], 'accepted_doses' => ['PRIMERA DOSIS']],
        ['key' => 'rotavirus_1', 'nombre' => 'Rotavirus primera dosis', 'course' => 'primera_infancia', 'min_age_months' => 2, 'max_age_months' => 3, 'vacunas_ids' => [11, 34], 'accepted_doses' => ['PRIMERA DOSIS']],
        ['key' => 'neumococo_1', 'nombre' => 'Neumococo primera dosis', 'course' => 'primera_infancia', 'min_age_months' => 2, 'max_age_months' => 3, 'vacunas_ids' => [12, 35], 'accepted_doses' => ['PRIMERA DOSIS']],

        ['key' => 'pentavalente_2', 'nombre' => 'Pentavalente segunda dosis', 'course' => 'primera_infancia', 'min_age_months' => 4, 'max_age_months' => 5, 'vacunas_ids' => [6, 7, 29, 30], 'accepted_doses' => ['SEGUNDA DOSIS']],
        ['key' => 'polio_2', 'nombre' => 'Polio segunda dosis', 'course' => 'primera_infancia', 'min_age_months' => 4, 'max_age_months' => 5, 'vacunas_ids' => [4, 7, 29, 30, 31], 'accepted_doses' => ['SEGUNDA DOSIS']],
        ['key' => 'rotavirus_2', 'nombre' => 'Rotavirus segunda dosis', 'course' => 'primera_infancia', 'min_age_months' => 4, 'max_age_months' => 5, 'vacunas_ids' => [11, 34], 'accepted_doses' => ['SEGUNDA DOSIS']],
        ['key' => 'neumococo_2', 'nombre' => 'Neumococo segunda dosis', 'course' => 'primera_infancia', 'min_age_months' => 4, 'max_age_months' => 5, 'vacunas_ids' => [12, 35], 'accepted_doses' => ['SEGUNDA DOSIS']],

        ['key' => 'pentavalente_3', 'nombre' => 'Pentavalente tercera dosis', 'course' => 'primera_infancia', 'min_age_months' => 6, 'max_age_months' => 11, 'vacunas_ids' => [6, 7, 29, 30], 'accepted_doses' => ['TERCERA DOSIS']],
        ['key' => 'polio_3', 'nombre' => 'Polio tercera dosis', 'course' => 'primera_infancia', 'min_age_months' => 6, 'max_age_months' => 11, 'vacunas_ids' => [4, 7, 29, 30, 31], 'accepted_doses' => ['TERCERA DOSIS']],
        ['key' => 'influenza_menor_1', 'nombre' => 'Influenza para la edad actual', 'course' => 'primera_infancia', 'min_age_months' => 6, 'max_age_months' => 71, 'vacunas_ids' => [20, 45], 'accepted_doses' => ['PRIMERA DOSIS', 'SEGUNDA DOSIS', 'UNICA 0.25', 'UNICA 0.5'], 'recurrence' => 'current_year'],

        ['key' => 'neumococo_refuerzo', 'nombre' => 'Neumococo refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 12, 'max_age_months' => 17, 'vacunas_ids' => [12, 35], 'accepted_doses' => ['PRIMER REFUERZO', 'UNICA']],
        ['key' => 'triple_viral_1', 'nombre' => 'Triple viral primera dosis', 'course' => 'primera_infancia', 'min_age_months' => 12, 'max_age_months' => 17, 'vacunas_ids' => [13, 37], 'accepted_doses' => ['PRIMERA DOSIS', 'UNICA']],
        ['key' => 'varicela_1', 'nombre' => 'Varicela primera dosis', 'course' => 'primera_infancia', 'min_age_months' => 12, 'max_age_months' => 17, 'vacunas_ids' => [17, 42], 'accepted_doses' => ['PRIMERA DOSIS']],

        ['key' => 'pentavalente_refuerzo', 'nombre' => 'Pentavalente primer refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 18, 'max_age_months' => 23, 'vacunas_ids' => [6, 29, 30], 'accepted_doses' => ['PRIMER REFUERZO']],
        ['key' => 'polio_refuerzo_18m', 'nombre' => 'Polio primer refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 18, 'max_age_months' => 23, 'vacunas_ids' => [4, 5, 7, 29, 30, 31], 'accepted_doses' => ['PRIMER REFUERZO']],
        ['key' => 'dpt_refuerzo_18m', 'nombre' => 'DPT primer refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 18, 'max_age_months' => 23, 'vacunas_ids' => [8, 9, 32], 'accepted_doses' => ['PRIMER REFUERZO']],
        ['key' => 'triple_viral_refuerzo', 'nombre' => 'Triple viral refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 18, 'max_age_months' => 23, 'vacunas_ids' => [13, 37], 'accepted_doses' => ['REFUERZO']],

        ['key' => 'polio_refuerzo_5a', 'nombre' => 'Polio segundo refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 60, 'max_age_months' => 71, 'vacunas_ids' => [4, 5, 7, 29, 30, 31], 'accepted_doses' => ['SEGUNDO REFUERZO']],
        ['key' => 'dpt_refuerzo_5a', 'nombre' => 'DPT segundo refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 60, 'max_age_months' => 71, 'vacunas_ids' => [8, 9, 32], 'accepted_doses' => ['SEGUNDO REFUERZO']],
        ['key' => 'varicela_refuerzo_5a', 'nombre' => 'Varicela refuerzo', 'course' => 'primera_infancia', 'min_age_months' => 60, 'max_age_months' => 71, 'vacunas_ids' => [17, 42], 'accepted_doses' => ['REFUERZO']],

        ['key' => 'vph_adolescencia', 'nombre' => 'VPH adolescencia', 'course' => 'adolescencia', 'min_age_months' => 144, 'max_age_months' => 215, 'vacunas_ids' => [21, 46], 'accepted_doses' => ['UNICA', 'PRIMERA DOSIS']],
        ['key' => 'toxoide_mujer_edad_fertil', 'nombre' => 'Toxoide tetánico mujer en edad fértil', 'population' => 'mujer_edad_fertil', 'min_age_months' => 120, 'max_age_months' => 599, 'vacunas_ids' => [18, 43], 'required_doses' => 1],

        ['key' => 'gestante_influenza', 'nombre' => 'Gestante: Influenza', 'population' => 'gestante', 'vacunas_ids' => [20, 45], 'recurrence' => 'current_year'],
        ['key' => 'gestante_tdap', 'nombre' => 'Gestante: Tdap', 'population' => 'gestante', 'vacunas_ids' => [19, 44], 'recurrence' => 'current_year'],
        ['key' => 'gestante_vsr', 'nombre' => 'Gestante: VSR (semanas 28-36)', 'population' => 'gestante', 'vacunas_ids' => [55], 'gestation_week_min' => 28, 'gestation_week_max' => 36, 'recurrence' => 'current_year'],

        ['key' => 'influenza_vejez', 'nombre' => 'Influenza persona mayor', 'course' => 'vejez', 'min_age_months' => 720, 'vacunas_ids' => [20, 45], 'recurrence' => 'current_year'],
    ],
];
