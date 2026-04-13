<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaiMvpCoverageService
{
    private const MODE_MVP = 'mvp';
    private const MODE_NORMATIVE = 'normative';

    private const MVP_RULES = [
        ['key' => 'bcg', 'nombre' => 'BCG', 'required_doses' => 1, 'vacunas_ids' => [2], 'min_age_months' => 0],
        ['key' => 'hepb_rn', 'nombre' => 'Hepatitis B (RN)', 'required_doses' => 1, 'vacunas_ids' => [3, 28], 'min_age_months' => 0],
        ['key' => 'penta_hexa_primaria', 'nombre' => 'Penta/Hexa primaria', 'required_doses' => 3, 'vacunas_ids' => [6, 7, 29, 30], 'min_age_months' => 2],
        ['key' => 'polio_primaria', 'nombre' => 'Polio primaria', 'required_doses' => 3, 'vacunas_ids' => [4, 7, 29, 30, 31], 'min_age_months' => 2],
        ['key' => 'rotavirus', 'nombre' => 'Rotavirus', 'required_doses' => 2, 'vacunas_ids' => [11, 34], 'min_age_months' => 2, 'max_age_months' => 7],
        ['key' => 'neumococo', 'nombre' => 'Neumococo', 'required_doses' => 3, 'vacunas_ids' => [12, 35], 'min_age_months' => 2],
        ['key' => 'srp_refuerzo_18m', 'nombre' => 'SRP (2 dosis)', 'required_doses' => 2, 'vacunas_ids' => [13, 37], 'min_age_months' => 18],
        ['key' => 'dpt_refuerzo_18m', 'nombre' => 'DPT/DPaT refuerzo', 'required_doses' => 1, 'vacunas_ids' => [8, 9, 32], 'min_age_months' => 18],
        ['key' => 'varicela', 'nombre' => 'Varicela', 'required_doses' => 1, 'vacunas_ids' => [17, 42], 'min_age_months' => 12],
        ['key' => 'hepa', 'nombre' => 'Hepatitis A', 'required_doses' => 1, 'vacunas_ids' => [16, 40], 'min_age_months' => 12],
        ['key' => 'fa', 'nombre' => 'Fiebre Amarilla', 'required_doses' => 1, 'vacunas_ids' => [15, 39], 'min_age_months' => 18],
        ['key' => 'vph_9_17', 'nombre' => 'VPH (dosis única 9-17 años)', 'required_doses' => 1, 'vacunas_ids' => [21, 46], 'min_age_months' => 108, 'max_age_months' => 215],
        ['key' => 'gestante_influenza', 'nombre' => 'Gestante: Influenza', 'required_doses' => 1, 'vacunas_ids' => [20, 45], 'requires_gestante' => true],
        ['key' => 'gestante_dtpa', 'nombre' => 'Gestante: dTpa', 'required_doses' => 1, 'vacunas_ids' => [19, 44], 'requires_gestante' => true],
        ['key' => 'gestante_vsr', 'nombre' => 'Gestante: VSR (sem 28-36)', 'required_doses' => 1, 'vacunas_ids' => [55], 'requires_gestante' => true, 'gestation_week_min' => 28, 'gestation_week_max' => 36],
    ];

    public function evaluateForAfiliado(int $afiliadoId, string $mode = self::MODE_NORMATIVE): array
    {
        $afiliado = DB::table('afiliados as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->where('a.id', $afiliadoId)
            ->select([
                'a.id',
                'a.tipo_identificacion',
                'a.numero_identificacion',
                'a.numero_carnet',
                'a.primer_nombre',
                'a.segundo_nombre',
                'a.primer_apellido',
                'a.segundo_apellido',
                'a.fecha_nacimiento',
                'a.sexo',
                'a.condicion_usuaria',
                'a.semanas_gestacion',
                'a.municipio_residencia',
                'a.contraindicacion_vacuna',
                'a.enfermedad_contraindicacion',
                'u.name as prestador',
            ])
            ->first();

        if (!$afiliado) {
            return ['ok' => false, 'message' => 'Afiliado no encontrado.'];
        }

        $appliedMap = $this->buildAppliedMapForAfiliadoIds(collect([(int) $afiliado->id]));
        $summary = $this->evaluateRow($afiliado, $appliedMap[(int) $afiliado->id] ?? [], $mode);

        return [
            'ok' => true,
            'mode' => $mode,
            'afiliado' => $summary['afiliado'],
            'stats' => $summary['stats'],
            'faltantes' => $summary['faltantes'],
            'cumplidas' => $summary['cumplidas'],
            'no_aplica' => $summary['no_aplica'],
            'as_of' => now()->format('Y-m-d H:i:s'),
            'version' => $mode === self::MODE_NORMATIVE ? 'NORMATIVO-FASE2-2026-04' : 'MVP-OPERATIVO-2026-04',
        ];
    }

    public function evaluateForCollection(Collection $afiliados, string $mode = self::MODE_NORMATIVE): array
    {
        $ids = $afiliados->pluck('id')
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $appliedByAfiliado = $this->buildAppliedMapForAfiliadoIds($ids);
        $out = [];

        foreach ($afiliados as $row) {
            $afId = (int) ($row->id ?? 0);
            if ($afId <= 0) {
                continue;
            }

            $out[$afId] = $this->evaluateRow($row, $appliedByAfiliado[$afId] ?? [], $mode);
        }

        return $out;
    }

    private function evaluateRow(object $row, array $appliedByVacId, string $mode): array
    {
        $birthDate = $this->safeParseDate($row->fecha_nacimiento ?? null);
        $ageMonths = $birthDate ? (int) floor($birthDate->diffInMonths(now())) : null;
        $ageYears = $birthDate ? (int) floor($birthDate->diffInYears(now())) : null;

        $isGestante = $this->isGestante($row);
        $gestationWeeks = $this->toIntOrNull($row->semanas_gestacion ?? null);
        $municipio = $this->normalizeText($row->municipio_residencia ?? '');
        $hasContraindicacion = $this->isYes($row->contraindicacion_vacuna ?? null);
        $condicionContra = trim((string) ($row->enfermedad_contraindicacion ?? ''));

        $rules = $mode === self::MODE_NORMATIVE
            ? $this->buildNormativeRules($ageMonths, $ageYears, $isGestante, $gestationWeeks, $municipio)
            : self::MVP_RULES;

        $faltantes = [];
        $cumplidas = [];
        $noAplica = [];

        foreach ($rules as $rule) {
            $requires = $this->resolveRequiredDoses($rule, $ageMonths, $ageYears);
            if ($requires <= 0) {
                $noAplica[] = ['key' => $rule['key'], 'nombre' => $rule['nombre']];
                continue;
            }

            if (!$this->ruleApplies($rule, $ageMonths, $isGestante, $gestationWeeks, $municipio)) {
                $noAplica[] = ['key' => $rule['key'], 'nombre' => $rule['nombre']];
                continue;
            }

            if ($hasContraindicacion && !empty($rule['can_be_exempted_by_contra'])) {
                $noAplica[] = [
                    'key' => $rule['key'],
                    'nombre' => $rule['nombre'],
                    'motivo' => 'Contraindicación registrada: ' . ($condicionContra ?: 'sin detalle'),
                ];
                continue;
            }

            $applied = 0;
            foreach (($rule['vacunas_ids'] ?? []) as $vacId) {
                $applied += (int) ($appliedByVacId[(int) $vacId] ?? 0);
            }

            if ($applied >= $requires) {
                $cumplidas[] = [
                    'key' => $rule['key'],
                    'nombre' => $rule['nombre'],
                    'requeridas' => $requires,
                    'aplicadas' => $applied,
                ];
                continue;
            }

            $faltantes[] = [
                'key' => $rule['key'],
                'nombre' => $rule['nombre'],
                'requeridas' => $requires,
                'aplicadas' => $applied,
                'faltan' => max($requires - $applied, 0),
                'vacunas_ids' => $rule['vacunas_ids'] ?? [],
                'edad_actual' => $this->formatAge($ageYears, $ageMonths),
                'edad_meses_actual' => $ageMonths,
                'motivo' => $this->buildMissingReason($rule, $ageYears, $ageMonths, $isGestante, $gestationWeeks, $municipio),
                'criterio_edad' => $this->buildAgeWindowLabel($rule),
                'fuente' => 'PAI Colombia 2026 (implementación normativa fase 2)',
            ];
        }

        $fullName = trim(implode(' ', array_filter([
            $row->primer_nombre ?? '',
            $row->segundo_nombre ?? '',
            $row->primer_apellido ?? '',
            $row->segundo_apellido ?? '',
        ])));

        return [
            'afiliado' => [
                'id' => (int) ($row->id ?? 0),
                'nombre' => $fullName,
                'tipo_identificacion' => $row->tipo_identificacion ?? null,
                'numero_identificacion' => $row->numero_identificacion ?? null,
                'numero_carnet' => $row->numero_carnet ?? null,
                'fecha_nacimiento' => $birthDate ? $birthDate->format('Y-m-d') : null,
                'edad_meses' => $ageMonths,
                'edad_anios' => $ageYears,
                'prestador' => $row->prestador ?? null,
                'es_gestante' => $isGestante,
                'semanas_gestacion' => $gestationWeeks,
                'municipio_residencia' => $row->municipio_residencia ?? null,
                'contraindicacion_vacuna' => $row->contraindicacion_vacuna ?? null,
                'enfermedad_contraindicacion' => $row->enfermedad_contraindicacion ?? null,
            ],
            'stats' => [
                'faltantes_count' => count($faltantes),
                'cumplidas_count' => count($cumplidas),
                'no_aplica_count' => count($noAplica),
            ],
            'faltantes' => $faltantes,
            'cumplidas' => $cumplidas,
            'no_aplica' => $noAplica,
        ];
    }

    private function buildNormativeRules(?int $ageMonths, ?int $ageYears, bool $isGestante, ?int $gestationWeeks, string $municipio): array
    {
        $rules = self::MVP_RULES;

        $rules[] = [
            'key' => 'polio_refuerzo',
            'nombre' => 'Polio refuerzo (escolar)',
            'required_doses' => 4,
            'vacunas_ids' => [4, 5, 7, 29, 30, 31],
            'min_age_months' => 60,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'srp_catchup_5_9',
            'nombre' => 'SRP catch-up 5-9 años',
            'required_doses' => 2,
            'vacunas_ids' => [13, 14, 37],
            'min_age_months' => 60,
            'max_age_months' => 119,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'td_adolescente',
            'nombre' => 'Td adolescente',
            'required_doses' => 1,
            'vacunas_ids' => [10, 18, 33, 43],
            'min_age_months' => 108,
            'max_age_months' => 215,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'td_adulto_base',
            'nombre' => 'Td adulto (base)',
            'required_doses' => 1,
            'vacunas_ids' => [18, 43],
            'min_age_months' => 216,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'influenza_adulto_mayor',
            'nombre' => 'Influenza adulto mayor',
            'required_doses' => 1,
            'vacunas_ids' => [20, 45],
            'min_age_months' => 720,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'influenza_6_23m',
            'nombre' => 'Influenza 6-23 meses',
            'required_doses' => 1,
            'vacunas_ids' => [20, 45],
            'min_age_months' => 6,
            'max_age_months' => 23,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'fiebre_amarilla_riesgo',
            'nombre' => 'Fiebre Amarilla por riesgo territorial',
            'required_doses' => 1,
            'vacunas_ids' => [15, 39],
            'min_age_months' => 18,
            'max_age_months' => 719,
            'requires_fa_risk_municipio' => true,
            'can_be_exempted_by_contra' => true,
        ];

        $rules[] = [
            'key' => 'dengue_priorizado',
            'nombre' => 'Dengue en municipio priorizado',
            'required_doses' => 1,
            'vacunas_ids' => [56],
            'min_age_months' => 120,
            'max_age_months' => 359,
            'requires_dengue_priorizado_municipio' => true,
            'can_be_exempted_by_contra' => true,
        ];

        return $rules;
    }

    private function resolveRequiredDoses(array $rule, ?int $ageMonths, ?int $ageYears): int
    {
        return (int) ($rule['required_doses'] ?? 1);
    }

    private function buildAppliedMapForAfiliadoIds(Collection $ids): array
    {
        $rows = DB::table('vacunas')
            ->whereIn('afiliado_id', $ids->all())
            ->whereNotNull('vacunas_id')
            ->select([
                'afiliado_id',
                'vacunas_id',
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('afiliado_id', 'vacunas_id')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $afId = (int) $r->afiliado_id;
            $vacId = (int) $r->vacunas_id;
            if (!isset($out[$afId])) {
                $out[$afId] = [];
            }
            $out[$afId][$vacId] = (int) $r->total;
        }

        return $out;
    }

    private function ruleApplies(array $rule, ?int $ageMonths, bool $isGestante, ?int $gestationWeeks, string $municipio): bool
    {
        if (isset($rule['requires_gestante']) && $rule['requires_gestante'] === true && !$isGestante) {
            return false;
        }

        if ($ageMonths !== null) {
            if (isset($rule['min_age_months']) && $ageMonths < (int) $rule['min_age_months']) {
                return false;
            }
            if (isset($rule['max_age_months']) && $ageMonths > (int) $rule['max_age_months']) {
                return false;
            }
        } elseif (isset($rule['min_age_months']) || isset($rule['max_age_months'])) {
            return false;
        }

        if (isset($rule['gestation_week_min']) || isset($rule['gestation_week_max'])) {
            if (!$isGestante || $gestationWeeks === null) {
                return false;
            }
            if (isset($rule['gestation_week_min']) && $gestationWeeks < (int) $rule['gestation_week_min']) {
                return false;
            }
            if (isset($rule['gestation_week_max']) && $gestationWeeks > (int) $rule['gestation_week_max']) {
                return false;
            }
        }

        if (!empty($rule['requires_dengue_priorizado_municipio']) && !$this->isDengueMunicipioPriorizado($municipio)) {
            return false;
        }

        if (!empty($rule['requires_fa_risk_municipio']) && !$this->isFiebreAmarillaMunicipioRiesgo($municipio)) {
            return false;
        }

        return true;
    }

    private function isDengueMunicipioPriorizado(string $municipio): bool
    {
        if ($municipio === '') {
            return false;
        }

        $set = collect(config('pai_normativo.dengue_municipios', []))
            ->map(fn ($x) => $this->normalizeText($x))
            ->filter()
            ->values();

        return $set->contains($municipio);
    }

    private function isFiebreAmarillaMunicipioRiesgo(string $municipio): bool
    {
        if ($municipio === '') {
            return false;
        }

        $set = collect(config('pai_normativo.fiebre_amarilla_municipios_riesgo', []))
            ->map(fn ($x) => $this->normalizeText($x))
            ->filter()
            ->values();

        return $set->contains($municipio);
    }

    private function isGestante(object $row): bool
    {
        $cond = mb_strtoupper(trim((string) ($row->condicion_usuaria ?? '')), 'UTF-8');
        if ($cond !== '' && str_contains($cond, 'GESTANTE')) {
            return true;
        }

        $weeks = $this->toIntOrNull($row->semanas_gestacion ?? null);
        return $weeks !== null && $weeks > 0;
    }

    private function isYes($value): bool
    {
        $t = $this->normalizeText((string) $value);
        return in_array($t, ['SI', 'S', 'YES', 'Y', '1', 'TRUE'], true);
    }

    private function safeParseDate($value): ?Carbon
    {
        try {
            if (!$value) {
                return null;
            }
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toIntOrNull($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);
        if ($clean === '' || !preg_match('/^-?\d+$/', $clean)) {
            return null;
        }

        return (int) $clean;
    }

    private function normalizeText(string $value): string
    {
        $txt = mb_strtoupper(trim($value), 'UTF-8');
        $txt = preg_replace('/\s+/u', ' ', $txt) ?? '';
        return $txt;
    }

    private function formatAge(?int $years, ?int $months): string
    {
        if ($years === null && $months === null) {
            return 'Sin fecha de nacimiento';
        }
        if ($years === null) {
            return (int) $months . ' meses';
        }
        return (int) $years . ' años (' . (int) ($months ?? 0) . ' meses)';
    }

    private function buildAgeWindowLabel(array $rule): string
    {
        $min = array_key_exists('min_age_months', $rule) ? (int) $rule['min_age_months'] : null;
        $max = array_key_exists('max_age_months', $rule) ? (int) $rule['max_age_months'] : null;

        if ($min === null && $max === null) {
            return 'Sin ventana etaria fija';
        }
        if ($min !== null && $max !== null) {
            return $min . '-' . $max . ' meses';
        }
        if ($min !== null) {
            return 'Desde ' . $min . ' meses';
        }
        return 'Hasta ' . $max . ' meses';
    }

    private function buildMissingReason(array $rule, ?int $ageYears, ?int $ageMonths, bool $isGestante, ?int $gestationWeeks, string $municipio): string
    {
        $parts = [];
        $parts[] = 'Edad actual: ' . $this->formatAge($ageYears, $ageMonths) . '.';

        $window = $this->buildAgeWindowLabel($rule);
        if ($window !== 'Sin ventana etaria fija') {
            $parts[] = 'Ventana de la regla: ' . $window . '.';
        }

        if (!empty($rule['requires_gestante'])) {
            $parts[] = $isGestante
                ? 'Condición gestante confirmada' . ($gestationWeeks ? ' (' . $gestationWeeks . ' semanas).' : '.')
                : 'Regla para gestantes.';
        }

        if (!empty($rule['requires_dengue_priorizado_municipio'])) {
            $parts[] = 'Municipio para validación dengue: ' . ($municipio !== '' ? $municipio : 'SIN MUNICIPIO');
        }

        if (!empty($rule['requires_fa_risk_municipio'])) {
            $parts[] = 'Municipio para riesgo de fiebre amarilla: ' . ($municipio !== '' ? $municipio : 'SIN MUNICIPIO');
        }

        $parts[] = 'No cumple dosis requeridas para esta regla.';
        return implode(' ', $parts);
    }
}
