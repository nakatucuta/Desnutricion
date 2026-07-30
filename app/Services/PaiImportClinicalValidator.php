<?php

namespace App\Services;

class PaiImportClinicalValidator
{
    public function __construct(
        private PaiClinicalDateNormalizer $dateNormalizer,
        private PaiGestationClinicalValidator $gestationValidator
    ) {}

    public function validateExcelRow(array $row, int $rowNumber): array
    {
        $dateFields = [
            0 => 'fecha_atencion',
            7 => 'fecha_nacimiento',
            44 => 'fecha_ultima_menstruacion',
            46 => 'fecha_prob_parto',
            48 => 'fecha_antecedente',
        ];

        $errors = [];
        foreach ($dateFields as $index => $field) {
            $error = $this->dateNormalizer->validationError($row[$index] ?? null, $field);
            if ($error !== null) {
                $errors[] = "Fila {$rowNumber}: {$error}.";
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        $result = $this->gestationValidator->validate([
            'fecha_atencion' => $this->dateNormalizer->normalize($row[0] ?? null),
            'fecha_nacimiento' => $this->dateNormalizer->normalize($row[7] ?? null),
            'edad_anos' => $row[8] ?? null,
            'sexo' => $row[13] ?? null,
            'condicion_usuaria' => $row[43] ?? null,
            'fecha_ultima_menstruacion' => $this->dateNormalizer->normalize($row[44] ?? null),
            'semanas_gestacion' => $row[45] ?? null,
            'fecha_prob_parto' => $this->dateNormalizer->normalize($row[46] ?? null),
        ]);

        foreach ($result['errors'] as $error) {
            $errors[] = "Fila {$rowNumber}: inconsistencia clinica de gestacion: {$error}.";
        }

        return $errors;
    }
}
