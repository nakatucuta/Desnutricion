<?php

namespace App\Services;

class PaiGestationClassifier
{
    public function __construct(
        private PaiGestationClinicalValidator $clinicalValidator
    ) {}

    public function isGestante($condition, $weeks, array $clinicalData = []): bool
    {
        $result = $this->clinicalValidator->validate(array_merge($clinicalData, [
            'condicion_usuaria' => $condition,
            'semanas_gestacion' => $weeks,
        ]));

        return $result['is_gestante'];
    }
}
