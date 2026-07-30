<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;

class PaiVaccineClinicalIdentity
{
    public function __construct(
        private PaiDoseNormalizer $doseNormalizer
    ) {}

    /**
     * Identidad clinica de una aplicacion:
     * afiliado + vacuna + dosis normalizada + fecha de aplicacion.
     */
    public function key(
        int $afiliadoId,
        int $vacunasId,
        $dose,
        $applicationDate
    ): string {
        $components = [
            'afiliado_id' => $afiliadoId,
            'vacunas_id' => $vacunasId,
            'docis' => $this->doseNormalizer->normalizeDocisStrict($dose),
            'fecha_vacuna' => $this->normalizeApplicationDate($applicationDate),
        ];

        return hash(
            'sha256',
            json_encode($components, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    public function normalizeApplicationDate($value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        // SQL Server puede retornar date como Y-m-d o datetime.
        if (preg_match('/^(\d{4}-\d{2}-\d{2})(?:[ T].*)?$/', $text, $matches)) {
            return $this->validDate($matches[1], 'Y-m-d');
        }

        foreach (['d/m/Y', 'd/m/y', 'Y/m/d'] as $format) {
            $normalized = $this->validDate($text, $format);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private function validDate(string $value, string $format): ?string
    {
        $date = DateTimeImmutable::createFromFormat('!'.$format, $value);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            return null;
        }

        return $date->format('Y-m-d');
    }
}
