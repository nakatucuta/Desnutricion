<?php

namespace App\Services;

use DateTimeImmutable;

class PaiGestationClinicalValidator
{
    private const CONDITION_CANONICAL_BY_KEY = [
        '' => '',
        'GESTANTE' => 'GESTANTE',
        'MUJER EN EDAD FERTIL' => 'MUJER EN EDAD FERTIL',
        'MUJER MAYOR DE 50 ANOS' => 'MUJER MAYOR DE 50 ANOS',
    ];

    private const ALLOWED_SEXES = [
        'HOMBRE',
        'MUJER',
        'INDETERMINADO',
    ];

    public function validate(array $data): array
    {
        $errors = [];
        $conditionKey = $this->normalizeText($data['condicion_usuaria'] ?? null);
        $condition = self::CONDITION_CANONICAL_BY_KEY[$conditionKey] ?? $conditionKey;
        $sex = $this->normalizeSex($data['sexo'] ?? null);
        $weeksRaw = $data['semanas_gestacion'] ?? null;
        $weeks = $this->toIntOrNull($weeksRaw);
        $age = $this->toIntOrNull($data['edad_anos'] ?? null);

        $attention = $this->date($data['fecha_atencion'] ?? null);
        $birth = $this->date($data['fecha_nacimiento'] ?? null);
        $fum = $this->date($data['fecha_ultima_menstruacion'] ?? null);
        $due = $this->date($data['fecha_prob_parto'] ?? null);

        $isGestanteCondition = $condition === 'GESTANTE';
        $isFemale = $sex === 'MUJER';

        if (! array_key_exists($conditionKey, self::CONDITION_CANONICAL_BY_KEY)) {
            $errors[] = 'condicion_usuaria debe ser GESTANTE, MUJER EN EDAD FERTIL, MUJER MAYOR DE 50 ANOS o vacio';
        }

        if (! in_array($sex, self::ALLOWED_SEXES, true)) {
            $errors[] = 'sexo debe ser HOMBRE, MUJER o INDETERMINADO';
        }

        if ($condition !== '' && ! $isFemale) {
            $errors[] = 'condicion_usuaria solo aplica a afiliados de sexo femenino';
        }

        if (! $this->isEmpty($weeksRaw) && $weeks === null) {
            $errors[] = 'semanas_gestacion debe ser un numero entero';
        }

        if ($attention && $birth) {
            if ($birth > $attention) {
                $errors[] = 'la fecha de nacimiento es posterior a la fecha de atencion';
            } else {
                $calculatedAge = $birth->diff($attention)->y;
                if ($age !== null && abs($calculatedAge - $age) > 1) {
                    $errors[] = "edad reportada ({$age}) incoherente con fecha de nacimiento ({$calculatedAge})";
                }
                $age = $calculatedAge;
            }
        }

        if (! $isGestanteCondition) {
            if ($weeks !== null || $due !== null) {
                $errors[] = 'semanas de gestacion o fecha probable de parto requieren condicion_usuaria = GESTANTE';
            }

            return ['is_gestante' => false, 'errors' => array_values(array_unique($errors))];
        }

        if ($attention === null) {
            $errors[] = 'una gestante requiere fecha de atencion valida';
        }

        if ($age === null || $age < 10 || $age > 59) {
            $errors[] = 'la edad de una gestante debe estar entre 10 y 59 anos';
        }

        if ($fum === null && $weeks === null && $due === null) {
            $errors[] = 'una gestante requiere al menos uno de estos datos: FUM, semanas_gestacion o fecha probable de parto';
        }

        if ($weeks !== null && ($weeks < 1 || $weeks > 42)) {
            $errors[] = 'semanas_gestacion debe estar entre 1 y 42';
        }

        if ($attention && $fum) {
            $daysSinceFum = (int) $fum->diff($attention)->format('%r%a');
            if ($daysSinceFum < 0) {
                $errors[] = 'la FUM no puede ser posterior a la fecha de atencion';
            } elseif ($daysSinceFum > 294) {
                $errors[] = 'la FUM supera las 42 semanas respecto a la fecha de atencion';
            }

            if ($weeks !== null && abs(($daysSinceFum / 7) - $weeks) > 2) {
                $errors[] = 'semanas_gestacion no coincide con la FUM (tolerancia de 2 semanas)';
            }
        }

        if ($attention && $due) {
            $daysToDue = (int) $attention->diff($due)->format('%r%a');
            if ($daysToDue < -14) {
                $errors[] = 'la fecha probable de parto no puede anteceder mas de 14 dias a la fecha de atencion';
            } elseif ($daysToDue > 294) {
                $errors[] = 'la fecha probable de parto esta fuera del rango maximo de 42 semanas';
            }

            if ($weeks !== null) {
                $expectedDays = (40 - $weeks) * 7;
                if (abs($daysToDue - $expectedDays) > 21) {
                    $errors[] = 'fecha probable de parto no coincide con semanas_gestacion (tolerancia de 3 semanas)';
                }
            }
        }

        if ($fum && $due) {
            $pregnancyDays = (int) $fum->diff($due)->format('%r%a');
            if ($pregnancyDays < 266 || $pregnancyDays > 294) {
                $errors[] = 'FUM y fecha probable de parto deben estar separadas entre 38 y 42 semanas';
            }
        }

        $errors = array_values(array_unique($errors));

        return ['is_gestante' => $errors === [], 'errors' => $errors];
    }

    private function date($value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $text);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && (! is_array($errors) || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
                ? $date
                : null;
    }

    private function normalizeText($value): string
    {
        $text = preg_replace('/\s+/u', ' ', mb_strtoupper(trim((string) ($value ?? '')), 'UTF-8')) ?? '';
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        $normalized = $ascii !== false ? $ascii : $text;
        $normalized = str_replace(["'", '`', '^', '~'], '', $normalized);

        return preg_replace('/\s+/u', ' ', trim($normalized)) ?? $normalized;
    }

    public function normalizeCondition($value): ?string
    {
        $key = $this->normalizeText($value);
        if ($key === '') {
            return null;
        }

        return self::CONDITION_CANONICAL_BY_KEY[$key] ?? $key;
    }

    public function normalizeSex($value): string
    {
        return preg_replace('/\s+/u', ' ', mb_strtoupper(trim((string) ($value ?? '')), 'UTF-8')) ?? '';
    }

    private function toIntOrNull($value): ?int
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' && preg_match('/^-?\d+$/', $text) ? (int) $text : null;
    }

    private function isEmpty($value): bool
    {
        return trim((string) ($value ?? '')) === '';
    }
}
