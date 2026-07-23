<?php

namespace Tests\Unit;

use App\Services\PaiCurrentSchemeEvaluator;
use App\Services\PaiLifeCourseClassifier;
use Carbon\Carbon;
use Tests\TestCase;

class PaiCurrentSchemeEvaluatorTest extends TestCase
{
    private PaiCurrentSchemeEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new PaiCurrentSchemeEvaluator(new PaiLifeCourseClassifier);
    }

    public function test_course_changes_from_newborn_on_day_eight(): void
    {
        $classifier = new PaiLifeCourseClassifier;
        $birth = Carbon::parse('2026-07-01');

        $this->assertSame('recien_nacido', $classifier->classify($birth, Carbon::parse('2026-07-08'))['key']);
        $this->assertSame('primera_infancia', $classifier->classify($birth, Carbon::parse('2026-07-09'))['key']);
    }

    public function test_newborn_is_only_evaluated_for_newborn_vaccines(): void
    {
        $result = $this->evaluatePerson('2026-07-10', 'M', false, null, [], '2026-07-15');

        $this->assertSame('INCOMPLETO', $result['estado']);
        $this->assertSame('recien_nacido', $result['curso']['key']);
        $this->assertEqualsCanonicalizing(['bcg_rn', 'hepatitis_b_rn'], array_column($result['faltantes'], 'key'));
    }

    public function test_five_year_old_accumulates_due_doses_from_current_course_but_not_newborn(): void
    {
        $applications = [
            $this->application(14, 'UNICA', '2026-02-01'),
        ];

        $result = $this->evaluatePerson('2021-01-15', 'M', false, null, $applications, '2026-07-15');

        $missingKeys = array_column($result['faltantes'], 'key');

        $this->assertSame('INCOMPLETO', $result['estado']);
        $this->assertSame('primera_infancia', $result['curso']['key']);
        $this->assertCount(21, $result['faltantes']);
        $this->assertNotContains('bcg_rn', $missingKeys);
        $this->assertNotContains('hepatitis_b_rn', $missingKeys);
        $this->assertContains('pentavalente_1', $missingKeys);
        $this->assertContains('polio_refuerzo_5a', $missingKeys);
        $this->assertContains('dpt_refuerzo_5a', $missingKeys);
        $this->assertContains('varicela_refuerzo_5a', $missingKeys);
    }

    public function test_ten_year_old_boy_does_not_inherit_earlier_vaccines(): void
    {
        $result = $this->evaluatePerson('2016-03-01', 'M', false, null, [], '2026-07-15');

        $this->assertSame('NO_APLICA', $result['estado']);
        $this->assertSame('infancia', $result['curso']['key']);
        $this->assertSame([], $result['faltantes']);
    }

    public function test_adolescent_woman_combines_vph_and_fertile_age_rules(): void
    {
        $result = $this->evaluatePerson(
            '2010-01-01',
            'F',
            false,
            null,
            [$this->application(21, 'UNICA', '2025-06-01')],
            '2026-07-15'
        );

        $this->assertSame('INCOMPLETO', $result['estado']);
        $this->assertContains('adolescencia', $result['poblaciones']);
        $this->assertContains('mujer_edad_fertil', $result['poblaciones']);
        $this->assertSame(['vph_adolescencia'], array_column($result['cumplidas'], 'key'));
        $this->assertSame(['toxoide_mujer_edad_fertil'], array_column($result['faltantes'], 'key'));
    }

    public function test_previous_year_influenza_does_not_complete_current_year(): void
    {
        $result = $this->evaluatePerson(
            '1960-01-01',
            'M',
            false,
            null,
            [$this->application(20, 'UNICA 0.5', '2025-11-01')],
            '2026-07-15'
        );

        $this->assertSame('INCOMPLETO', $result['estado']);
        $this->assertSame(['influenza_vejez'], array_column($result['faltantes'], 'key'));
    }

    public function test_vsr_only_applies_inside_gestation_week_window(): void
    {
        $outside = $this->evaluatePerson('1996-01-01', 'F', true, 20, [], '2026-07-15');
        $inside = $this->evaluatePerson('1996-01-01', 'F', true, 30, [], '2026-07-15');

        $this->assertNotContains('gestante_vsr', array_column($outside['faltantes'], 'key'));
        $this->assertContains('gestante_vsr', array_column($inside['faltantes'], 'key'));
    }

    public function test_missing_birth_date_is_not_evaluable(): void
    {
        $result = $this->evaluator->evaluate((object) [
            'birth_date' => null,
            'sex' => 'M',
            'is_gestante' => false,
        ], [], Carbon::parse('2026-07-15'));

        $this->assertSame('NO_EVALUABLE', $result['estado']);
    }

    private function evaluatePerson(string $birthDate, string $sex, bool $gestante, ?int $weeks, array $applications, string $asOf): array
    {
        return $this->evaluator->evaluate((object) [
            'birth_date' => Carbon::parse($birthDate),
            'sex' => $sex,
            'is_gestante' => $gestante,
            'gestation_weeks' => $weeks,
            'has_contraindication' => false,
        ], $applications, Carbon::parse($asOf));
    }

    private function application(int $vaccineId, string $dose, string $date): array
    {
        return [
            'vacunas_id' => $vaccineId,
            'dose' => $dose,
            'date' => Carbon::parse($date),
        ];
    }
}
