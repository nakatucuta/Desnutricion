<?php

namespace App\Services;

class PaiDoseNormalizer
{
    public function normalizePayload(array $row, ?int $vacunasId = null): array
    {
        $vacunasId = $vacunasId ?? (int) ($row['vacunas_id'] ?? 0);
        $raw = array_key_exists('docis', $row) ? $row['docis'] : null;

        $row['docis_original'] = $this->normalizeOriginal($raw);
        $row['docis'] = $this->normalizeDocis($raw, $vacunasId, $row);

        return $row;
    }

    public function normalizeOriginal($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $txt = trim((string) $value);
        if ($txt === '') {
            return null;
        }

        $txt = trim($txt, " \t\n\r\0\x0B'\"");
        $txt = preg_replace('/\s+/u', ' ', $txt) ?? $txt;
        return $txt;
    }

    public function normalizeDocis($value, ?int $vacunasId = null, array $context = []): ?string
    {
        $vacunasId = $vacunasId !== null ? (int) $vacunasId : null;
        if ($value !== null) {
            $value = str_replace(["'", '"', '´', '’', '‘'], '', (string) $value);
        }
        $raw = $this->cleanText($value);

        if ($raw === null) {
            return $this->defaultForVaccine($vacunasId, $context);
        }

        $normalized = $this->normalizeText($raw);

        $canonical = $this->matchCanonicalDose($normalized);
        if ($canonical !== null) {
            return $canonical;
        }

        if ($this->isSpecialGarbage($normalized)) {
            return $this->defaultForVaccine($vacunasId, $context);
        }

        // If the text is clearly a dose-like fragment, keep the canonicalized text.
        if ($this->looksLikeDoseText($normalized)) {
            return $this->formatCanonicalText($normalized);
        }

        return $this->defaultForVaccine($vacunasId, $context);
    }

    public function isRecognized(?string $value, ?int $vacunasId = null): bool
    {
        return $this->normalizeDocis($value, $vacunasId) !== null;
    }

    public function defaultForVaccine(?int $vacunasId, array $context = []): ?string
    {
        if ($vacunasId === null) {
            return null;
        }

        $defaults = (array) config('pai_docis.defaults_by_vacunas_id', []);
        $default = $defaults[$vacunasId] ?? null;

        if ($default === null) {
            return null;
        }

        return $default;
    }

    private function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $txt = trim((string) $value);
        if ($txt === '') {
            return null;
        }

        $txt = trim($txt, " \t\n\r\0\x0B'\"");
        $upper = mb_strtoupper($txt, 'UTF-8');
        $upper = preg_replace('/\s+/u', ' ', $upper) ?? $upper;
        if ($this->isNullToken($upper)) {
            return null;
        }

        return $txt;
    }

    private function normalizeText(string $value): string
    {
        $txt = trim($value);
        $txt = trim($txt, " \t\n\r\0\x0B'\"");
        $txt = str_replace(["'", '"'], '', $txt);
        $txt = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt) ?: $txt;
        $txt = str_replace(['_', '-', '/', '\\'], ' ', $txt);
        $txt = preg_replace('/\s+/u', ' ', $txt) ?? $txt;
        return mb_strtoupper(trim($txt), 'UTF-8');
    }

    private function formatCanonicalText(string $normalized): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);
        return str_replace(["'", '"'], '', $normalized);
    }

    private function matchCanonicalDose(string $normalized): ?string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);

        if ($normalized === '') {
            return null;
        }

        if ($this->hasConflictingDoseSignals($normalized)) {
            return null;
        }

        if (preg_match('/^(?:DOSIS\s+)?UNICA(?:\s*[_ ]?\s*(0[.,]\d+))?$/u', $normalized, $m)) {
            $suffix = isset($m[1]) ? ' ' . str_replace(',', '.', $m[1]) : '';
            return $this->ascii([85, 78, 73, 67, 65]) . $suffix;
        }

        if (preg_match('/^(?:UNICA|DOSIS\s+UNICA)\s*[_ ]\s*(0[.,]\d+)$/u', $normalized, $m)) {
            return $this->ascii([85, 78, 73, 67, 65]) . ' ' . str_replace(',', '.', $m[1]);
        }

        if (preg_match('/^RECIEN NACIDO$|^DOSIS DE RECIEN NACIDO$/u', $normalized)) {
            return $this->ascii([82, 69, 67, 73, 69, 78, 32, 78, 65, 67, 73, 68, 79]);
        }

        if ($normalized === 'PRIMERA' || $normalized === 'PRIMERA DOSIS' || $normalized === '1RA DOSIS' || $normalized === '1RA') {
            return 'PRIMERA DOSIS';
        }
        if ($normalized === 'SEGUNDA' || $normalized === 'SEGUNDA DOSIS' || $normalized === '2DA DOSIS' || $normalized === '2DA') {
            return 'SEGUNDA DOSIS';
        }
        if ($normalized === 'TERCERA' || $normalized === 'TERCERA DOSIS' || $normalized === '3RA DOSIS' || $normalized === '3RA' || $normalized === 'TERCERA D' || $normalized === 'TERCERADOSIS') {
            return 'TERCERA DOSIS';
        }
        if ($normalized === 'CUARTA' || $normalized === 'CUARTA DOSIS' || $normalized === '4TA DOSIS' || $normalized === '4TO DOSIS') {
            return 'CUARTA DOSIS';
        }
        if ($normalized === 'QUINTA' || $normalized === 'QUINTA DOSIS' || $normalized === '5TA DOSIS') {
            return 'QUINTA DOSIS';
        }

        if (preg_match('/\bPRIMER(?:A)?\b/u', $normalized) && str_contains($normalized, 'REFUERZO')) {
            return 'PRIMER REFUERZO';
        }
        if ((str_contains($normalized, '2DO') || str_contains($normalized, 'SEGUNDO')) && str_contains($normalized, 'REFUERZO')) {
            return 'SEGUNDO REFUERZO';
        }
        if ((str_contains($normalized, '3RO') || str_contains($normalized, 'TERCER')) && str_contains($normalized, 'REFUERZO')) {
            return 'TERCER REFUERZO';
        }
        if ((str_contains($normalized, '4TO') || str_contains($normalized, 'CUARTO') || $normalized === '4 = REFUERZO') && str_contains($normalized, 'REFUERZO')) {
            return 'CUARTO REFUERZO';
        }
        if ($normalized === 'PRIMER R') {
            return 'PRIMER REFUERZO';
        }
        if ($normalized === 'SEGUNDO R') {
            return 'SEGUNDO REFUERZO';
        }
        if ($normalized === '3RO REFUERZO') {
            return 'TERCER REFUERZO';
        }
        if ($normalized === '4TO REFUERZO') {
            return 'CUARTO REFUERZO';
        }
        if ($normalized === 'REFUERZA' || $normalized === 'DOSIS REFUERZO' || $normalized === 'REFUERZO') {
            return 'REFUERZO';
        }

        if (preg_match('/\bPRIMER(?:A)?\b/u', $normalized) && str_contains($normalized, 'DOSIS')) {
            return 'PRIMERA DOSIS';
        }
        if (preg_match('/\bSEGUND(?:A|O)?\b/u', $normalized) && str_contains($normalized, 'DOSIS')) {
            return 'SEGUNDA DOSIS';
        }
        if (preg_match('/\bTERCER(?:A|O)?\b/u', $normalized) && str_contains($normalized, 'DOSIS')) {
            return 'TERCERA DOSIS';
        }
        if (preg_match('/\bCUART(?:A|O)?\b/u', $normalized) && str_contains($normalized, 'DOSIS')) {
            return 'CUARTA DOSIS';
        }
        if (preg_match('/\bQUINT(?:A|O)?\b/u', $normalized) && str_contains($normalized, 'DOSIS')) {
            return 'QUINTA DOSIS';
        }

        return null;
    }

    private function hasConflictingDoseSignals(string $normalized): bool
    {
        $signals = 0;
        foreach (['PRIMER', 'SEGUND', 'TERCER', 'CUART', 'QUINT', 'REFUERZO'] as $needle) {
            if (str_contains($normalized, $needle)) {
                $signals++;
            }
        }

        if ($signals > 1 && str_contains($normalized, ' Y ')) {
            return true;
        }

        return false;
    }

    private function looksLikeDoseText(string $normalized): bool
    {
        if ($normalized === '') {
            return false;
        }

        if (preg_match('/\b(DOSIS|REFUERZO|UNICA|RECIEN NACIDO)\b/u', $normalized)) {
            return true;
        }

        if (preg_match('/^\d+(?:[.,]\d+)?$/', $normalized)) {
            return false;
        }

        return false;
    }

    private function isNullToken(string $upper): bool
    {
        $tokens = (array) config('pai_docis.null_tokens', []);
        return in_array($upper, $tokens, true);
    }

    private function ascii(array $codes): string
    {
        return implode('', array_map(static fn (int $code) => chr($code), $codes));
    }

    private function isSpecialGarbage(string $normalized): bool
    {
        if ($normalized === '') {
            return true;
        }

        if (preg_match('/^(?:\d+(?:[.,]\d+)?|[A-Z]{1,4}\d{2,}.*|\d{2,}[A-Z].*|[A-Z]+(?:-[A-Z0-9]+){2,})$/', $normalized)) {
            return true;
        }

        $noise = [
            'AUTORIZADAS POR EL MINISTERIO',
            'SIN DOSIS',
            'ANTES DE 12 HORAS',
            'JERINGA',
            'JEFE',
        ];

        foreach ($noise as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
