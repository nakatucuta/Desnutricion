<?php

return [
    /*
    |-----------------------------------------------------------------------
    | Canonical dose labels
    |-----------------------------------------------------------------------
    | These are the normalized labels that the system should store in `docis`.
    | The import and backfill use these labels as the stable vocabulary.
    */
    'canonical_labels' => [
        'CERO',
        'UNICA',
        'UNICA CON FRACCION',
        'PRIMERA DOSIS',
        'SEGUNDA DOSIS',
        'TERCERA DOSIS',
        'CUARTA DOSIS',
        'QUINTA DOSIS',
        'PRIMER REFUERZO',
        'SEGUNDO REFUERZO',
        'TERCER REFUERZO',
        'CUARTO REFUERZO',
        'RECIEN NACIDO',
        'REFUERZO',
        'ADICIONAL',
    ],

    /*
    |-----------------------------------------------------------------------
    | Vaccine-specific defaults
    |-----------------------------------------------------------------------
    | Used when the source value is empty or clearly invalid.
    | This is intentionally small; the normalizer prefers explicit matches.
    */
    'defaults_by_vacunas_id' => [
        55 => 'UNICA',
        56 => 'UNICA',
    ],

    /*
    |-----------------------------------------------------------------------
    | Values that should be treated as empty / invalid
    |-----------------------------------------------------------------------
    */
    'null_tokens' => [
        'NO TIENE',
        'N/A',
        'NA',
        'SIN DATO',
        'NULL',
        'NONE',
        '?',
        'NO APLICA',
        'NO APLIQUE',
    ],
];
