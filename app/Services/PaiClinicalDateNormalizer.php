<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PaiClinicalDateNormalizer
{
    private const MIN_EXCEL_SERIAL = 10000;

    private const MAX_EXCEL_SERIAL = 80000;

    public function normalize($value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($this->isEmpty($value)) {
            return null;
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric(trim($value)))) {
            $serial = (float) $value;
            if ($serial < self::MIN_EXCEL_SERIAL || $serial > self::MAX_EXCEL_SERIAL) {
                return null;
            }

            try {
                return Date::excelToDateTimeObject($serial)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);
        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'Y/m/d'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $text);
            $errors = DateTimeImmutable::getLastErrors();

            if (
                $date !== false
                && (! is_array($errors) || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            ) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    public function validationError($value, string $field): ?string
    {
        if ($this->isEmpty($value) || $value instanceof DateTimeInterface) {
            return null;
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric(trim($value)))) {
            $serial = (float) $value;
            if ($serial < self::MIN_EXCEL_SERIAL || $serial > self::MAX_EXCEL_SERIAL) {
                return "{$field}: serial Excel {$value} fuera del rango permitido";
            }
        }

        if ($this->normalize($value) === null) {
            return "{$field}: fecha invalida; use DD/MM/AAAA o AAAA-MM-DD";
        }

        return null;
    }

    private function isEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        $text = mb_strtoupper(trim((string) $value), 'UTF-8');

        return in_array($text, ['', '-', '0', 'NO TIENE', 'N/A', 'NA', 'SIN DATO', 'NULL', 'NONE', '?', 'NO APLICA'], true);
    }
}
