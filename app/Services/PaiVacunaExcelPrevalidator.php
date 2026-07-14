<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class PaiVacunaExcelPrevalidator
{
    public function __construct(
        private PaiDoseNormalizer $doseNormalizer
    ) {}

    public function validate(string $path, int $startRow = 3, int $maxErrors = 1): array
    {
        $errors = [];

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = (int) $sheet->getHighestDataRow();

        for ($rowNumber = $startRow; $rowNumber <= $highestRow; $rowNumber++) {
            $row = $sheet->rangeToArray("A{$rowNumber}:IV{$rowNumber}", null, true, false)[0] ?? [];
            $row = array_replace(array_fill(0, 256, null), $row);

            foreach ($this->vaccineBlocks() as $block) {
                [$vacunasId, $idxs, $doseIdx, $frascosIdx] = $block;

                if (!$this->hasAny($row, $idxs)) {
                    continue;
                }

                $error = $frascosIdx !== null
                    ? $this->validateFrascos($row[$frascosIdx] ?? null, $rowNumber, $vacunasId)
                    : $this->validateDose($row[$doseIdx] ?? null, $rowNumber, $vacunasId);

                if ($error !== null) {
                    $errors[] = $error;
                    if (count($errors) >= $maxErrors) {
                        $spreadsheet->disconnectWorksheets();
                        return $errors;
                    }
                }
            }
        }

        $spreadsheet->disconnectWorksheets();
        return $errors;
    }

    private function validateDose($value, int $rowNumber, int $vacunasId): ?string
    {
        $allowedDoses = $this->validDosesForVacuna($vacunasId);
        if ($allowedDoses === null) {
            return "Fila {$rowNumber}: no hay catalogo de dosis configurado para vacunas_id={$vacunasId}. No se guardo nada.";
        }

        $docisNorm = $this->doseNormalizer->normalizeDocisStrict($value);
        if ($docisNorm === null) {
            return "Fila {$rowNumber}: vacunas_id={$vacunasId} tiene informacion en el Excel pero la dosis esta vacia o no se pudo normalizar. Dosis permitidas: " . implode(', ', $allowedDoses) . ". No se guardo nada.";
        }

        if (!in_array($docisNorm, $allowedDoses, true)) {
            return "Fila {$rowNumber}: la dosis '{$docisNorm}' no pertenece al catalogo de vacunas_id={$vacunasId}. Dosis permitidas: " . implode(', ', $allowedDoses) . ". No se guardo nada.";
        }

        return null;
    }

    private function validateFrascos($value, int $rowNumber, int $vacunasId): ?string
    {
        if ($this->isNullLike($value)) {
            return "Fila {$rowNumber}: la vacuna vacunas_id={$vacunasId} requiere numero de frascos utilizado y debe ser un valor numerico positivo. No se guardo nada.";
        }

        $txt = trim((string) $value);
        $normalized = str_replace(',', '.', $txt);

        if (!is_numeric($normalized) || (float) $normalized <= 0) {
            return "Fila {$rowNumber}: la vacuna vacunas_id={$vacunasId} requiere numero de frascos utilizado y debe ser un valor numerico positivo. No se guardo nada.";
        }

        return null;
    }

    private function validDosesForVacuna(int $vacunasId): ?array
    {
        $catalog = (array) config('pai_docis.valid_doses_by_vacunas_id', []);
        return isset($catalog[$vacunasId]) ? array_values((array) $catalog[$vacunasId]) : null;
    }

    private function hasAny(array $row, array $idxs): bool
    {
        foreach ($idxs as $idx) {
            $value = $row[$idx] ?? null;
            if (!$this->isNullLike($value)) {
                return true;
            }
        }

        return false;
    }

    private function isNullLike($value): bool
    {
        if ($value === null) {
            return true;
        }

        $txt = trim((string) $value);
        if ($txt === '') {
            return true;
        }

        $upper = mb_strtoupper($txt, 'UTF-8');
        return in_array($upper, (array) config('pai_docis.null_tokens', []), true)
            || $upper === 'NONE';
    }

    private function vaccineBlocks(): array
    {
        return [
            [1, range(75, 80), 75, null],
            [2, range(81, 86), 81, null],
            [3, range(87, 91), 87, null],
            [4, range(92, 96), 92, null],
            [5, range(97, 99), 97, null],
            [6, range(100, 104), 100, null],
            [7, range(105, 108), 105, null],
            [8, range(109, 112), 109, null],
            [9, range(113, 116), 113, null],
            [10, range(117, 120), 117, null],
            [11, range(121, 122), 121, null],
            [12, range(123, 127), 124, null],
            [13, range(128, 132), 128, null],
            [14, range(133, 137), 133, null],
            [15, range(138, 142), 138, null],
            [16, range(143, 146), 143, null],
            [17, range(147, 151), 147, null],
            [18, range(152, 155), 152, null],
            [19, range(156, 159), 156, null],
            [20, range(160, 164), 160, null],
            [21, range(165, 168), 165, null],
            [22, range(169, 174), 169, null],
            [23, range(175, 176), null, 175],
            [24, range(177, 181), null, 177],
            [25, range(182, 185), null, 182],
            [26, range(186, 189), null, 186],
            [27, range(190, 194), 190, null],
            [55, range(195, 196), 195, null],
            [56, range(197, 198), 197, null],
            [28, range(199, 200), 199, null],
            [29, range(201, 202), 201, null],
            [30, range(203, 204), 203, null],
            [31, range(205, 206), 205, null],
            [32, range(207, 208), 207, null],
            [33, range(209, 210), 209, null],
            [34, range(211, 212), 211, null],
            [35, range(213, 214), 213, null],
            [36, range(215, 216), 215, null],
            [37, range(217, 218), 217, null],
            [38, range(219, 220), 219, null],
            [39, range(221, 222), 221, null],
            [40, range(223, 224), 223, null],
            [41, range(225, 226), 225, null],
            [42, range(227, 228), 227, null],
            [43, range(229, 230), 229, null],
            [44, range(231, 232), 231, null],
            [45, range(233, 234), 233, null],
            [46, range(235, 236), 235, null],
            [47, range(237, 239), 237, null],
            [48, range(240, 241), null, 240],
            [49, range(242, 244), null, 242],
            [50, range(245, 246), null, 245],
            [51, range(247, 248), null, 247],
            [52, range(249, 250), 249, null],
            [53, range(251, 252), 251, null],
            [54, range(253, 254), 253, null],
        ];
    }
}
