<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Municipios priorizados para dengue (fase 2)
    |--------------------------------------------------------------------------
    | Formato: nombres en mayúscula separados por coma en .env
    | Ejemplo: RIOHACHA,MAICAO,URIBIA
    */
    'dengue_municipios' => array_values(array_filter(array_map(
        fn ($x) => trim(mb_strtoupper((string) $x, 'UTF-8')),
        explode(',', (string) env('PAI_DENGUE_MUNICIPIOS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Municipios de riesgo para fiebre amarilla (fase 2)
    |--------------------------------------------------------------------------
    */
    'fiebre_amarilla_municipios_riesgo' => array_values(array_filter(array_map(
        fn ($x) => trim(mb_strtoupper((string) $x, 'UTF-8')),
        explode(',', (string) env('PAI_FA_MUNICIPIOS_RIESGO', ''))
    ))),
];

