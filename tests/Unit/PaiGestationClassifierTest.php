<?php

namespace Tests\Unit;

use App\Services\PaiGestationClassifier;
use App\Services\PaiGestationClinicalValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PaiGestationClassifierTest extends TestCase
{
    #[DataProvider('conditions')]
    public function test_it_requires_condition_and_clinically_coherent_fields($condition, $weeks, array $clinicalData, bool $expected): void
    {
        $classifier = new PaiGestationClassifier(new PaiGestationClinicalValidator());

        $this->assertSame($expected, $classifier->isGestante($condition, $weeks, $clinicalData));
    }

    public static function conditions(): array
    {
        $coherent = [
            'fecha_atencion' => '2026-07-15',
            'fecha_nacimiento' => '1996-01-01',
            'edad_anos' => 30,
            'sexo' => 'MUJER',
            'fecha_ultima_menstruacion' => '2026-01-01',
            'fecha_prob_parto' => '2026-10-08',
        ];

        return [
            'coherent gestante' => ['GESTANTE', 28, $coherent, true],
            'condition without clinical support is insufficient' => [
                'GESTANTE',
                null,
                array_diff_key($coherent, array_flip(['fecha_ultima_menstruacion', 'fecha_prob_parto'])),
                false,
            ],
            'fum alone is sufficient when coherent' => [
                'GESTANTE',
                null,
                array_diff_key($coherent, array_flip(['fecha_prob_parto'])),
                true,
            ],
            'condition must be exact' => ['SI GESTANTE', 28, $coherent, false],
            'weeks without condition' => [null, 28, $coherent, false],
            'male data is incoherent' => ['GESTANTE', 28, array_merge($coherent, ['sexo' => 'HOMBRE']), false],
            'weeks above clinical range' => ['GESTANTE', 43, $coherent, false],
        ];
    }
}
