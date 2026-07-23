<?php

namespace App\Services;

use Carbon\CarbonInterface;

class PaiCurrentSchemeEvaluator
{
    public function __construct(private readonly PaiLifeCourseClassifier $classifier) {}

    public function evaluate(object $person, array $applications, CarbonInterface $asOf): array
    {
        $birthDate = $person->birth_date ?? null;
        if (! $birthDate instanceof CarbonInterface) {
            return $this->notEvaluable('Falta una fecha de nacimiento válida.');
        }

        $course = $this->classifier->classify($birthDate, $asOf);
        if ($course === null) {
            return $this->notEvaluable('La fecha de nacimiento es posterior a la fecha de evaluación.');
        }

        $sex = $this->normalize((string) ($person->sex ?? ''));
        $isFemale = in_array($sex, ['F', 'FEMENINO', 'MUJER'], true);
        $isKnownSex = $isFemale || in_array($sex, ['M', 'MASCULINO', 'HOMBRE'], true);
        $isGestante = (bool) ($person->is_gestante ?? false);
        $gestationWeeks = isset($person->gestation_weeks) ? (int) $person->gestation_weeks : null;
        $isFertileAge = $course['age_months'] >= 120 && $course['age_months'] <= 599;

        $populations = [$course['key']];
        if ($isFemale && $isFertileAge) {
            $populations[] = 'mujer_edad_fertil';
        }
        if ($isGestante) {
            $populations[] = 'gestante';
        }

        $activeRules = [];
        foreach ((array) config('pai_esquemas.rules', []) as $rule) {
            if ($this->ruleApplies($rule, $course, $isFemale, $isGestante, $gestationWeeks)) {
                $activeRules[] = $rule;
            }
        }

        if (! $isKnownSex && $isFertileAge) {
            return $this->notEvaluable('El sexo es necesario para evaluar mujer en edad fértil.', $course, $populations);
        }

        if ($activeRules === []) {
            return $this->result('NO_APLICA', $course, $populations, [], [], [], 'No hay vacunas exigibles para el curso y la edad actual.');
        }

        $completed = [];
        $missing = [];
        $exempt = [];
        $hasContraindication = (bool) ($person->has_contraindication ?? false);

        foreach ($activeRules as $rule) {
            if ($hasContraindication && ! empty($rule['can_be_exempted_by_contra'])) {
                $exempt[] = $this->rulePayload($rule, 0, 'Contraindicación registrada.');

                continue;
            }

            $required = max((int) ($rule['required_doses'] ?? 1), 1);
            $matched = $this->matchingApplications($rule, $applications, $asOf);
            $payload = $this->rulePayload($rule, count($matched));
            $payload['requeridas'] = $required;
            $payload['faltan'] = max($required - count($matched), 0);

            if (count($matched) >= $required) {
                $completed[] = $payload;
            } else {
                $missing[] = $payload;
            }
        }

        $status = $missing === [] ? 'COMPLETO' : 'INCOMPLETO';

        return $this->result($status, $course, $populations, $completed, $missing, $exempt);
    }

    private function ruleApplies(array $rule, array $course, bool $isFemale, bool $isGestante, ?int $gestationWeeks): bool
    {
        if (isset($rule['course']) && (string) $rule['course'] !== $course['key']) {
            return false;
        }

        $population = $rule['population'] ?? null;
        if ($population === 'mujer_edad_fertil' && ! $isFemale) {
            return false;
        }
        if ($population === 'gestante' && ! $isGestante) {
            return false;
        }

        if (! $this->ageRuleIsDue($rule, $course)) {
            return false;
        }

        if (isset($rule['gestation_week_min']) || isset($rule['gestation_week_max'])) {
            if (! $isGestante || $gestationWeeks === null) {
                return false;
            }
            if (isset($rule['gestation_week_min']) && $gestationWeeks < (int) $rule['gestation_week_min']) {
                return false;
            }
            if (isset($rule['gestation_week_max']) && $gestationWeeks > (int) $rule['gestation_week_max']) {
                return false;
            }
        }

        return true;
    }

    private function ageRuleIsDue(array $rule, array $course): bool
    {
        $accumulatesWithinCourse = isset($rule['course'])
            && (bool) ($rule['accumulate_within_course'] ?? true);

        if (isset($rule['min_age_days']) && $course['age_days'] < (int) $rule['min_age_days']) {
            return false;
        }
        if (! $accumulatesWithinCourse && isset($rule['max_age_days']) && $course['age_days'] > (int) $rule['max_age_days']) {
            return false;
        }
        if (isset($rule['min_age_months']) && $course['age_months'] < (int) $rule['min_age_months']) {
            return false;
        }
        if (! $accumulatesWithinCourse && isset($rule['max_age_months']) && $course['age_months'] > (int) $rule['max_age_months']) {
            return false;
        }

        return true;
    }

    private function matchingApplications(array $rule, array $applications, CarbonInterface $asOf): array
    {
        $ids = array_map('intval', (array) ($rule['vacunas_ids'] ?? []));
        $acceptedDoses = array_map(fn ($dose) => $this->normalize((string) $dose), (array) ($rule['accepted_doses'] ?? []));

        return array_values(array_filter($applications, function (array $application) use ($ids, $acceptedDoses, $rule, $asOf): bool {
            if (! in_array((int) ($application['vacunas_id'] ?? 0), $ids, true)) {
                return false;
            }

            if ($acceptedDoses !== [] && ! in_array($this->normalize((string) ($application['dose'] ?? '')), $acceptedDoses, true)) {
                return false;
            }

            $date = $application['date'] ?? null;
            if ($date instanceof CarbonInterface && $date->copy()->startOfDay()->greaterThan($asOf->copy()->startOfDay())) {
                return false;
            }

            if (($rule['recurrence'] ?? null) === 'current_year') {
                if (! $date instanceof CarbonInterface || (int) $date->year !== (int) $asOf->year) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function rulePayload(array $rule, int $applied, ?string $reason = null): array
    {
        $payload = [
            'key' => (string) $rule['key'],
            'nombre' => (string) $rule['nombre'],
            'aplicadas' => $applied,
            'requeridas' => max((int) ($rule['required_doses'] ?? 1), 1),
            'vacunas_ids' => array_values((array) ($rule['vacunas_ids'] ?? [])),
            'criterio_edad' => $this->ageWindowLabel($rule),
            'fuente' => 'Matriz PAI por curso de vida '.config('pai_esquemas.version'),
        ];

        if ($reason !== null) {
            $payload['motivo'] = $reason;
        }

        return $payload;
    }

    private function result(string $status, ?array $course, array $populations, array $completed, array $missing, array $exempt, ?string $reason = null): array
    {
        return [
            'estado' => $status,
            'curso' => $course,
            'poblaciones' => array_values(array_unique($populations)),
            'cumplidas' => $completed,
            'faltantes' => $missing,
            'exentas' => $exempt,
            'no_aplica' => $status === 'NO_APLICA' ? [['motivo' => $reason]] : [],
            'motivo_estado' => $reason,
            'stats' => [
                'faltantes_count' => array_sum(array_map(
                    static fn (array $rule): int => (int) ($rule['faltan'] ?? 0),
                    $missing,
                )),
                'reglas_faltantes_count' => count($missing),
                'cumplidas_count' => count($completed),
                'exentas_count' => count($exempt),
                'no_aplica_count' => $status === 'NO_APLICA' ? 1 : 0,
            ],
        ];
    }

    private function notEvaluable(string $reason, ?array $course = null, array $populations = []): array
    {
        return $this->result('NO_EVALUABLE', $course, $populations, [], [], [], $reason);
    }

    private function ageWindowLabel(array $rule): string
    {
        if (isset($rule['min_age_days']) || isset($rule['max_age_days'])) {
            return ((int) ($rule['min_age_days'] ?? 0)).'-'.((int) ($rule['max_age_days'] ?? 0)).' días';
        }

        if (isset($rule['min_age_months']) && isset($rule['max_age_months'])) {
            return ((int) $rule['min_age_months']).'-'.((int) $rule['max_age_months']).' meses';
        }
        if (isset($rule['min_age_months'])) {
            return 'Desde '.((int) $rule['min_age_months']).' meses';
        }

        return 'Condición actual';
    }

    private function normalize(string $value): string
    {
        $value = mb_strtoupper(trim($value), 'UTF-8');

        return preg_replace('/\s+/u', ' ', $value) ?? '';
    }
}
