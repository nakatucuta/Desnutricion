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
        'PRIMERA Y SEGUNDA DOSIS',
        'TERCERA Y CUARTA DOSIS',
        'NUMERO DE FRASCOS',
    ],

    /*
    |-----------------------------------------------------------------------
    | Vaccine-specific valid doses for strict imports
    |-----------------------------------------------------------------------
    | Keys are normalized vaccine names used by the PAI import validator.
    | The import does not assume defaults: if a vaccine block has data, the
    | dose must be present and normalize to one of these values.
    */
    'valid_doses_by_vaccine' => [
        'COVID19' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
            'REFUERZO',
        ],
        'BCG' => [
            'UNICA',
        ],
        'HEPATITIS B' => [
            'RECIEN NACIDO',
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'ADICIONAL',
        ],
        'POLIO INACTIVO' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        'PENTAVALENTE' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        'HEXAVALENTE' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
        ],
        'DPT' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        'DPTA PED' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        'ROTAVIRUS' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
        ],
        'NEUMOCOCO' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'PRIMER REFUERZO',
            'UNICA',
        ],
        'SARAMPION' => [
            'CERO',
            'UNICA',
        ],
        'FIEBRE AMARILLA' => [
            'UNICA',
        ],
        'HEPATITIS A' => [
            'UNICA',
        ],
        'VARICELA' => [
            'PRIMERA DOSIS',
            'REFUERZO',
        ],
        'TOXOIDE TETANICO' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'CUARTA DOSIS',
            'QUINTA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
            'TERCER REFUERZO',
            'CUARTO REFUERZO',
        ],
        'DPTA ADULTO' => [
            'UNICA',
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'CUARTA DOSIS',
            'QUINTA DOSIS',
        ],
        'INFLUENZA' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'UNICA 0.25',
            'UNICA 0.5',
        ],
        'VPH' => [
            'UNICA',
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
        ],
        'ANTIRRABICA HUMANA' => [
            'PRIMERA DOSIS',
            'PRIMERA Y SEGUNDA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'TERCERA Y CUARTA DOSIS',
            'CUARTA DOSIS',
        ],
        'ANTIRRABICA SUERO' => [
            'NUMERO DE FRASCOS',
        ],
        'HEPATITIS B INMUNOGLOBULINA' => [
            'NUMERO DE FRASCOS',
        ],
        'INMUNOGLOBULINA' => [
            'NUMERO DE FRASCOS',
        ],
        'ANTITOCOIDE TETANICA' => [
            'NUMERO DE FRASCOS',
        ],
        'MENINGOCOCO' => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'UNICA',
        ],
        'VSR' => [
            'UNICA',
        ],
    ],

    /*
    |-----------------------------------------------------------------------
    | Vaccine-specific valid doses by referencia_vacunas.id
    |-----------------------------------------------------------------------
    | This is the catalog used by new strict imports. It avoids depending on
    | the vaccine name stored in referencia_vacunas.
    */
    'valid_doses_by_vacunas_id' => [
        1 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
            'REFUERZO',
        ],
        2 => [
            'UNICA',
        ],
        3 => [
            'RECIEN NACIDO',
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'ADICIONAL',
        ],
        4 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        6 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
            'UNICA',
        ],
        7 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
        ],
        8 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        9 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        10 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
        ],
        11 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
        ],
        12 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'PRIMER REFUERZO',
            'UNICA',
        ],
        13 => [
            'PRIMERA DOSIS',
            'REFUERZO',
        ],
        14 => [
            'CERO',
            'UNICA',
        ],
        15 => [
            'UNICA',
        ],
        16 => [
            'UNICA',
        ],
        17 => [
            'PRIMERA DOSIS',
            'REFUERZO',
        ],
        18 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'CUARTA DOSIS',
            'QUINTA DOSIS',
            'PRIMER REFUERZO',
            'SEGUNDO REFUERZO',
            'TERCER REFUERZO',
            'CUARTO REFUERZO',
        ],
        19 => [
            'UNICA',
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'CUARTA DOSIS',
            'QUINTA DOSIS',
        ],
        20 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'UNICA 0.25',
            'UNICA 0.5',
        ],
        21 => [
            'UNICA',
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
        ],
        22 => [
            'PRIMERA DOSIS',
            'PRIMERA Y SEGUNDA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'TERCERA Y CUARTA DOSIS',
            'CUARTA DOSIS',
        ],
        23 => [
            'NUMERO DE FRASCOS',
        ],
        24 => [
            'NUMERO DE FRASCOS',
        ],
        25 => [
            'NUMERO DE FRASCOS',
        ],
        26 => [
            'NUMERO DE FRASCOS',
        ],
        27 => [
            'PRIMERA DOSIS',
            'SEGUNDA DOSIS',
            'TERCERA DOSIS',
            'UNICA',
        ],
        55 => [
            'UNICA',
        ],
        56 => [
            'UNICA',
        ],
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
