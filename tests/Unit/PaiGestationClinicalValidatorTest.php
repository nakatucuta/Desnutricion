<?php

namespace Tests\Unit;

use App\Services\PaiGestationClinicalValidator;
use PHPUnit\Framework\TestCase;

class PaiGestationClinicalValidatorTest extends TestCase
{
    private PaiGestationClinicalValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new PaiGestationClinicalValidator();
    }

    public function test_it_recognizes_a_clinically_coherent_gestation(): void
    {
        $result = $this->validator->validate($this->coherentGestation());

        $this->assertTrue($result['is_gestante']);
        $this->assertSame([], $result['errors']);
    }

    public function test_decimal_gestation_weeks_are_accepted(): void
    {
        foreach ([28.5, '28.5', '28,5'] as $weeks) {
            $data = $this->coherentGestation();
            $data['semanas_gestacion'] = $weeks;

            $result = $this->validator->validate($data);

            $this->assertTrue($result['is_gestante'], implode(' | ', $result['errors']));
            $this->assertSame([], $result['errors'], (string) $weeks);
        }
    }

    public function test_weeks_or_due_date_do_not_determine_gestation_without_exact_condition(): void
    {
        $data = $this->coherentGestation();
        $data['condicion_usuaria'] = null;

        $result = $this->validator->validate($data);

        $this->assertFalse($result['is_gestante']);
        $this->assertStringContainsString('requieren Condicion Usuaria = GESTANTE', implode(' ', $result['errors']));
    }

    public function test_it_accepts_only_the_configured_condition_values_or_empty(): void
    {
        foreach ([
            '',
            'GESTANTE',
            'MUJER EN EDAD FERTIL',
            'MUJER EN EDAD F' . "\xC3\x89" . 'RTIL',
            'MUJER EN  EDAD   FERTIL',
            'MUJER MAYOR DE 50 A' . "\xC3\x91" . 'OS',
            'MUJER MAYOR DE 50 ANOS',
        ] as $condition) {
            $data = $condition === 'GESTANTE'
                ? $this->coherentGestation()
                : [
                    'fecha_atencion' => '2026-07-15',
                    'fecha_nacimiento' => '1986-01-01',
                    'edad_anos' => 40,
                    'sexo' => 'MUJER',
                    'condicion_usuaria' => $condition,
                ];

            $this->assertSame([], $this->validator->validate($data)['errors'], $condition ?: 'vacio');
        }

        $invalid = $this->validator->validate([
            'condicion_usuaria' => 'MADRE LACTANTE',
        ]);

        $this->assertStringContainsString('Condicion Usuaria debe ser', implode(' ', $invalid['errors']));
    }

    public function test_any_non_empty_condition_requires_female_sex(): void
    {
        foreach (['GESTANTE', 'MUJER EN EDAD FERTIL', 'MUJER MAYOR DE 50 ANOS'] as $condition) {
            $data = $condition === 'GESTANTE'
                ? array_merge($this->coherentGestation(), ['sexo' => 'HOMBRE'])
                : [
                    'fecha_atencion' => '2026-07-15',
                    'fecha_nacimiento' => '1986-01-01',
                    'edad_anos' => 40,
                    'sexo' => 'HOMBRE',
                    'condicion_usuaria' => $condition,
                ];

            $errors = $this->validator->validate($data)['errors'];
            $this->assertStringContainsString('solo aplica a afiliados de sexo femenino', implode(' ', $errors));
        }

        $withoutCondition = $this->validator->validate([
            'sexo' => 'HOMBRE',
            'condicion_usuaria' => '',
        ]);

        $this->assertSame([], $withoutCondition['errors']);
    }

    public function test_sex_is_a_closed_catalog_after_uppercase_normalization(): void
    {
        foreach (['HOMBRE', 'hombre', 'MUJER', 'mujer', 'INDETERMINADO', 'indeterminado'] as $sex) {
            $result = $this->validator->validate([
                'sexo' => $sex,
                'condicion_usuaria' => '',
            ]);

            $this->assertSame([], $result['errors'], $sex);
        }

        foreach (['', 'F', 'M', 'FEMENINO', 'MASCULINO'] as $sex) {
            $result = $this->validator->validate([
                'sexo' => $sex,
                'condicion_usuaria' => '',
            ]);

            $this->assertStringContainsString(
                'Sexo debe ser HOMBRE, MUJER o INDETERMINADO',
                implode(' ', $result['errors']),
                $sex ?: 'vacio'
            );
        }
    }

    public function test_it_exposes_canonical_values_for_persistence(): void
    {
        $this->assertSame('MUJER', $this->validator->normalizeSex(' mujer '));
        $this->assertSame('INDETERMINADO', $this->validator->normalizeSex('indeterminado'));
        $this->assertSame('MUJER EN EDAD FERTIL', $this->validator->normalizeCondition('mujer en edad f' . "\xC3\xA9" . 'rtil'));
        $this->assertSame('MUJER MAYOR DE 50 ANOS', $this->validator->normalizeCondition('mujer mayor de 50 anos'));
        $this->assertNull($this->validator->normalizeCondition(''));
    }

    public function test_gestante_requires_at_least_one_clinical_gestation_field(): void
    {
        $base = $this->coherentGestation();
        unset($base['fecha_ultima_menstruacion'], $base['semanas_gestacion'], $base['fecha_prob_parto']);

        $withoutClinicalData = $this->validator->validate($base);
        $this->assertFalse($withoutClinicalData['is_gestante']);
        $this->assertStringContainsString('al menos uno', implode(' ', $withoutClinicalData['errors']));

        foreach ([
            ['fecha_ultima_menstruacion' => '2026-01-01'],
            ['semanas_gestacion' => 28],
            ['fecha_prob_parto' => '2026-10-08'],
        ] as $clinicalField) {
            $result = $this->validator->validate(array_merge($base, $clinicalField));
            $this->assertTrue($result['is_gestante'], implode(' | ', $result['errors']));
        }
    }

    public function test_it_rejects_incoherent_sex_age_and_overdue_date(): void
    {
        $data = $this->coherentGestation();
        $data['sexo'] = 'HOMBRE';
        $data['fecha_nacimiento'] = '2018-01-01';
        $data['edad_anos'] = 8;
        $data['fecha_prob_parto'] = '2026-06-01';

        $result = $this->validator->validate($data);
        $errors = implode(' | ', $result['errors']);

        $this->assertFalse($result['is_gestante']);
        $this->assertStringContainsString('sexo femenino', $errors);
        $this->assertStringContainsString('entre 10 y 59 anos', $errors);
        $this->assertStringContainsString('Fecha Probable de Parto no puede anteceder mas de 14 dias', $errors);
    }

    public function test_incoherent_reported_weeks_do_not_block_gestation(): void
    {
        $data = $this->coherentGestation();
        $data['fecha_ultima_menstruacion'] = '2026-01-01';
        $data['semanas_gestacion'] = 10;

        $result = $this->validator->validate($data);

        $this->assertTrue($result['is_gestante'], implode(' | ', $result['errors']));
        $this->assertSame([], $result['errors']);
    }

    private function coherentGestation(): array
    {
        return [
            'fecha_atencion' => '2026-07-15',
            'fecha_nacimiento' => '1996-01-01',
            'edad_anos' => 30,
            'sexo' => 'MUJER',
            'condicion_usuaria' => 'GESTANTE',
            'fecha_ultima_menstruacion' => '2026-01-01',
            'semanas_gestacion' => 28,
            'fecha_prob_parto' => '2026-10-08',
        ];
    }
}
