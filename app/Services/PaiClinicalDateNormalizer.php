<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PaiClinicalDateNormalizer
{
    private const MIN_EXCEL_SERIAL = 10000;

    private const MIN_RECENT_FIELD_DATE = '2000-01-01';

    private const MIN_BIRTH_DATE = '1901-01-01';

    private const MAX_EXCEL_SERIAL = 80000;

    public function normalize($value, ?string $field = null): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $this->dateWithinFieldRange($value->format('Y-m-d'), $field);
        }

        if ($this->isEmpty($value)) {
            return null;
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric(trim($value)))) {
            $serial = (float) $value;
            $minimumSerial = $this->isBirthDateField($field) ? 1 : self::MIN_EXCEL_SERIAL;
            if ($serial < $minimumSerial || $serial > self::MAX_EXCEL_SERIAL) {
                return null;
            }

            try {
                $normalized = Date::excelToDateTimeObject($serial)->format('Y-m-d');
                return $this->dateWithinFieldRange($normalized, $field);
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
                return $this->dateWithinFieldRange($date->format('Y-m-d'), $field);
            }
        }

        return null;
    }

    public function validationError($value, string $field): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric(trim($value)))) {
            $serial = (float) $value;
            if ($this->normalize($value, $field) === null) {
                $displayDate = $this->displayDateFromExcelSerial($serial);
                if ($displayDate !== null) {
                    return "{$field}: la fecha {$displayDate} no es valida para este campo. Si no aplica, deja la celda vacia.";
                }

                return "{$field}: el valor ingresado no parece una fecha valida. Revisa que la celda tenga formato de fecha.";
            }
        }

        $normalized = $this->normalize($value, $field);
        if ($normalized === null) {
            return "{$field}: fecha no valida. Usa el formato DD/MM/AAAA o AAAA-MM-DD y verifica que no sea una fecha antigua como 1900.";
        }

        if ($this->requiresRecentDate($field) && $normalized < self::MIN_RECENT_FIELD_DATE) {
            return "{$field}: fecha no valida. Para este campo no uses fechas antiguas como 1900; si no aplica, deja la celda vacia.";
        }

        return null;
    }

    private function isBirthDateField(?string $field): bool
    {
        return in_array($field, ['Fecha de Nacimiento', 'fecha_nacimiento'], true);
    }

    private function dateWithinFieldRange(string $date, ?string $field): ?string
    {
        if ($this->isBirthDateField($field) && $date < self::MIN_BIRTH_DATE) {
            return null;
        }

        return $date;
    }

    private function isEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        $text = mb_strtoupper(trim((string) $value), 'UTF-8');

        return in_array($text, ['', '-', '0', 'NO TIENE', 'N/A', 'NA', 'SIN DATO', 'NULL', 'NONE', '?', 'NO APLICA'], true);
    }

    private function requiresRecentDate(string $field): bool
    {
        return ! $this->isBirthDateField($field);
    }

    private function displayDateFromExcelSerial(float $serial): ?string
    {
        try {
            return Date::excelToDateTimeObject($serial)->format('d/m/Y');
        } catch (\Throwable) {
            return null;
        }
    }
}
