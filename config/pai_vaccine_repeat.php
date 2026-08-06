<?php

return [
    'policies' => [
        'influenza_adult' => [
            'vaccine_ids' => [20, 43],
            'minimum_age_years' => 18,
            'candidate_doses' => ['UNICA', 'UNICA 0.25', 'UNICA 0.5'],
            'compare_all_doses' => true,
            'minimum_interval' => ['months' => 6],
        ],
        'yellow_fever' => [
            'vaccine_ids' => [15, 37],
            'candidate_doses' => ['UNICA'],
            'compare_all_doses' => true,
            'minimum_interval' => ['years' => 8],
        ],
        'toxoid_booster' => [
            'vaccine_ids' => [18, 31, 41],
            'candidate_dose_contains' => 'REFUERZO',
            'existing_dose_contains' => 'REFUERZO',
            'minimum_interval' => ['days' => 30],
        ],
    ],
];
