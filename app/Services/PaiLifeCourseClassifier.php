<?php

namespace App\Services;

use Carbon\CarbonInterface;

class PaiLifeCourseClassifier
{
    public function classify(CarbonInterface $birthDate, CarbonInterface $asOf): ?array
    {
        $birth = $birthDate->copy()->startOfDay();
        $cutoff = $asOf->copy()->startOfDay();

        if ($birth->greaterThan($cutoff)) {
            return null;
        }

        $ageDays = (int) $birth->diffInDays($cutoff);
        $ageMonths = (int) floor($birth->diffInMonths($cutoff));
        $ageYears = (int) floor($birth->diffInYears($cutoff));

        foreach ((array) config('pai_esquemas.courses', []) as $key => $course) {
            if (! $this->within($ageDays, $course, 'age_days')) {
                continue;
            }
            if (! $this->within($ageMonths, $course, 'age_months')) {
                continue;
            }

            return [
                'key' => (string) $key,
                'label' => (string) ($course['label'] ?? $key),
                'age_days' => $ageDays,
                'age_months' => $ageMonths,
                'age_years' => $ageYears,
            ];
        }

        return null;
    }

    private function within(int $value, array $definition, string $suffix): bool
    {
        $minKey = 'min_'.$suffix;
        $maxKey = 'max_'.$suffix;

        if (array_key_exists($minKey, $definition) && $value < (int) $definition[$minKey]) {
            return false;
        }

        return ! array_key_exists($maxKey, $definition) || $value <= (int) $definition[$maxKey];
    }
}
