<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;

class PaiVaccineRepeatPolicy
{
    public function __construct(
        private PaiDoseNormalizer $doseNormalizer,
        private PaiVaccineClinicalIdentity $clinicalIdentity
    ) {}

    /**
     * Returns null when the regular deduplication rule must be used.
     * Otherwise returns the decision made by the configured repeat policy.
     *
     * @param array<int, array{vacunas_id:mixed, docis:mixed, fecha_vacuna:mixed}> $applications
     * @return array{allowed:bool, policy:string, reason:string}|null
     */
    public function evaluate(
        int $vacunasId,
        $dose,
        $applicationDate,
        $birthDate,
        array $applications
    ): ?array {
        $normalizedDose = $this->doseNormalizer->normalizeDocisStrict($dose);
        $candidateDate = $this->parseDate($applicationDate);

        foreach ((array) config('pai_vaccine_repeat.policies', []) as $policyName => $policy) {
            if (! $this->policyApplies($policy, $vacunasId, $normalizedDose, $candidateDate, $birthDate)) {
                continue;
            }

            if ($candidateDate === null) {
                return [
                    'allowed' => false,
                    'policy' => (string) $policyName,
                    'reason' => 'No fue posible validar el intervalo porque la fecha de aplicacion esta vacia o no es valida. No se inserto.',
                ];
            }

            foreach ($applications as $existing) {
                if (! $this->existingApplicationApplies($policy, $existing)) {
                    continue;
                }

                $existingDate = $this->parseDate($existing['fecha_vacuna'] ?? null);
                if ($existingDate === null) {
                    continue;
                }

                if ($existingDate->format('Y-m-d') === $candidateDate->format('Y-m-d')) {
                    return [
                        'allowed' => false,
                        'policy' => (string) $policyName,
                        'reason' => 'El afiliado ya tiene registrada una aplicacion de este biologico en la misma fecha. No se inserto.',
                    ];
                }

                if ($this->intervalIsTooShort($existingDate, $candidateDate, (array) ($policy['minimum_interval'] ?? []))) {
                    return [
                        'allowed' => false,
                        'policy' => (string) $policyName,
                        'reason' => 'El intervalo con otra aplicacion de este biologico del '
                            .$existingDate->format('d/m/Y')
                            .' es demasiado corto. No se inserto.',
                    ];
                }
            }

            return [
                'allowed' => true,
                'policy' => (string) $policyName,
                'reason' => '',
            ];
        }

        return null;
    }

    private function policyApplies(
        array $policy,
        int $vacunasId,
        ?string $dose,
        ?DateTimeImmutable $applicationDate,
        $birthDate
    ): bool {
        $vaccineIds = array_map('intval', (array) ($policy['vaccine_ids'] ?? []));
        if (! in_array($vacunasId, $vaccineIds, true)) {
            return false;
        }

        if (isset($policy['candidate_doses']) && ! in_array($dose, (array) $policy['candidate_doses'], true)) {
            return false;
        }

        if (isset($policy['candidate_dose_contains']) && ! str_contains((string) $dose, (string) $policy['candidate_dose_contains'])) {
            return false;
        }

        if (isset($policy['minimum_age_years'])) {
            $birth = $this->parseDate($birthDate);
            if ($birth === null || $applicationDate === null || $applicationDate < $birth) {
                return false;
            }

            if ($birth->diff($applicationDate)->y < (int) $policy['minimum_age_years']) {
                return false;
            }
        }

        return true;
    }

    private function existingApplicationApplies(array $policy, array $existing): bool
    {
        $vaccineIds = array_map('intval', (array) ($policy['vaccine_ids'] ?? []));
        if (! in_array((int) ($existing['vacunas_id'] ?? 0), $vaccineIds, true)) {
            return false;
        }

        if (! empty($policy['compare_all_doses'])) {
            return true;
        }

        $existingDose = $this->doseNormalizer->normalizeDocisStrict($existing['docis'] ?? null);
        if (isset($policy['existing_dose_contains'])) {
            return str_contains((string) $existingDose, (string) $policy['existing_dose_contains']);
        }

        return true;
    }

    private function intervalIsTooShort(
        DateTimeImmutable $first,
        DateTimeImmutable $second,
        array $minimumInterval
    ): bool {
        $earlier = $first < $second ? $first : $second;
        $later = $first < $second ? $second : $first;

        if (isset($minimumInterval['years'])) {
            return $later < $earlier->modify('+'.(int) $minimumInterval['years'].' years');
        }
        if (isset($minimumInterval['months'])) {
            return $later < $earlier->modify('+'.(int) $minimumInterval['months'].' months');
        }
        if (isset($minimumInterval['days'])) {
            return $later < $earlier->modify('+'.(int) $minimumInterval['days'].' days');
        }

        return false;
    }

    private function parseDate($value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return new DateTimeImmutable($value->format('Y-m-d'));
        }

        $normalized = $this->clinicalIdentity->normalizeApplicationDate($value);
        return $normalized !== null ? new DateTimeImmutable($normalized) : null;
    }
}
