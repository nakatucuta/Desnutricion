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
            0 => 'Fecha de Atencion',
            7 => 'Fecha de Nacimiento',
            44 => 'Fecha Ultima Menstruacion',
            46 => 'Fecha Probable de Parto',
            48 => 'Fecha de Antecedente',
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
            'fecha_atencion' => $this->dateNormalizer->normalize($row[0] ?? null, 'Fecha de Atencion'),
            'fecha_nacimiento' => $this->dateNormalizer->normalize($row[7] ?? null, 'Fecha de Nacimiento'),
            'edad_anos' => $row[8] ?? null,
            'sexo' => $row[13] ?? null,
            'condicion_usuaria' => $row[43] ?? null,
            'fecha_ultima_menstruacion' => $this->dateNormalizer->normalize($row[44] ?? null, 'Fecha Ultima Menstruacion'),
            'semanas_gestacion' => $row[45] ?? null,
            'fecha_prob_parto' => $this->dateNormalizer->normalize($row[46] ?? null, 'Fecha Probable de Parto'),
        ]);

        foreach ($result['errors'] as $error) {
            $errors[] = "Fila {$rowNumber}: inconsistencia clinica de gestacion: {$error}.";
        }

        return $errors;
    }
}
