<?php

namespace App\Services;

class PaiVaccineExcelBlockCatalog
{
    private const NAMES_BY_VACUNAS_ID = [
        1 => 'COVID 19',
        2 => 'BCG',
        3 => 'HEPATITIS B',
        4 => 'POLIO INACTIVADO',
        5 => 'POLIO ORAL',
        6 => 'PENTAVALENTE',
        7 => 'HEXAVALENTE',
        8 => 'DIFTERIA, TOS FERINA Y TETANOS - DPT',
        9 => 'DTPA PEDIATRICO',
        10 => 'TD PEDIATRICO',
        11 => 'ROTAVIRUS (VACUNA ORAL)',
        12 => 'NEUMOCOCO',
        13 => 'TRIPLE VIRAL - SRP',
        14 => 'SARAMPION - RUBEOLA - SR MULTIDOSIS',
        15 => 'FIEBRE AMARILLA',
        16 => 'HEPATITIS A PEDIATRICA',
        17 => 'VARICELA',
        18 => 'TOXOIDE TETANICO Y DIFTERICO DE ADULTO',
        19 => 'DTPA ADULTO',
        20 => 'INFLUENZA',
        21 => 'VPH',
        22 => 'ANTIRRABICA HUMANA (VACUNA)',
        23 => 'ANTIRRABICO HUMANO (SUERO)',
        24 => 'HEPATITIS B (INMUNOGLOBULINA)',
        25 => 'INMUNOGLOBULINA ANTI TETANICA',
        26 => 'ANTI TOXINA TETANICA',
        27 => 'MENINGOCOCO A, C, W-135 E Y',
        28 => 'HEXAVALENTE (DPaT, HiB, HB, VPI)',
        29 => 'TETRAVALENTE (DPaT, VPI)',
        30 => 'DPT ACELULAR PEDIATRICO',
        31 => 'TOXOIDE TETANICO Y DIFTERICO PEDIATRICO',
        32 => 'ROTAVIRUS',
        33 => 'NEUMOCOCO CONJUGADA',
        34 => 'NEUMO POLISACARIDO',
        35 => 'TRIPLE VIRAL',
        36 => 'VARICELA + TRIPLE VIRAL',
        37 => 'FIEBRE AMARILLA',
        38 => 'HEPATITIS A',
        39 => 'HEPATITIS A, HEPATITIS B',
        40 => 'VARICELA',
        41 => 'TOXOIDE TETANICO/DIFTERICO ADULTOS',
        42 => 'DPT ACELULAR ADULTO',
        43 => 'INFLUENZA',
        44 => 'VPH',
        45 => 'ANTIRRABICA PROFILACTICA',
        46 => 'INMUNOGLOBULINA ANTI TETANICA',
        47 => 'INMUNOGLOBULINA ANTI HEPATITIS B',
        48 => 'ANTI TOXINA TETANICA',
        49 => 'MENINGOCOCO CONJUGADO',
        50 => 'FIEBRE TIFOIDEA',
        51 => 'HERPES ZOSTER',
        52 => 'VACUNA ESPECIAL 52',
        53 => 'VACUNA ESPECIAL 53',
        54 => 'VACUNA ESPECIAL 54',
        55 => 'HEPATITIS B',
        56 => 'PENTAVALENTE (DPaT, HiB, VPI)',
    ];

    public static function nameForVacunasId(int $vacunasId): ?string
    {
        return self::NAMES_BY_VACUNAS_ID[$vacunasId] ?? null;
    }
}
