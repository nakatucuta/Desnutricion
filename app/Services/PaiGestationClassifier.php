<?php

namespace App\Services;

class PaiGestationClassifier
{
    public function isGestante($condition, $weeks): bool
    {
        $normalized = $this->normalize((string) ($condition ?? ''));

        if ($this->isExplicitNegative($normalized)) {
            return false;
        }

        if ($normalized !== '' && preg_match('/\bGESTANTE\b/u', $normalized) === 1) {
            return true;
        }

        $gestationWeeks = $this->toIntOrNull($weeks);

        return $gestationWeeks !== null && $gestationWeeks > 0;
    }

    private function isExplicitNegative(string $condition): bool
    {
        if (in_array($condition, ['NO', 'NINGUNA', 'NO APLICA', 'N/A', 'NA'], true)) {
            return true;
        }

        return preg_match('/\bNO(?:\s+ES)?\s+GESTANTE\b/u', $condition) === 1;
    }

    private function toIntOrNull($value): ?int
    {
        $clean = trim((string) ($value ?? ''));

        return $clean !== '' && preg_match('/^-?\d+$/', $clean) ? (int) $clean : null;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtoupper(trim($value), 'UTF-8');

        return preg_replace('/\s+/u', ' ', $value) ?? '';
    }
}
