<?php

namespace Tests\Unit;

use App\Services\PaiDoseNormalizer;
use App\Services\PaiVaccineClinicalIdentity;
use App\Services\PaiVaccineRepeatPolicy;
use Tests\TestCase;

class PaiVaccineRepeatPolicyTest extends TestCase
{
    private PaiVaccineRepeatPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $doseNormalizer = new PaiDoseNormalizer;
        $identity = new PaiVaccineClinicalIdentity($doseNormalizer);
        $this->policy = new PaiVaccineRepeatPolicy($doseNormalizer, $identity);
    }

    public function test_adult_influenza_blocks_less_than_six_months_and_allows_six_months(): void
    {
        $applications = [$this->application(43, 'UNICA 0.5', '2026-01-15')];

        $blocked = $this->policy->evaluate(20, 'UNICA', '2026-07-14', '1980-01-01', $applications);
        $allowed = $this->policy->evaluate(20, 'UNICA', '2026-07-15', '1980-01-01', $applications);

        $this->assertFalse($blocked['allowed']);
        $this->assertTrue($allowed['allowed']);
    }

    public function test_influenza_policy_does_not_replace_the_regular_rule_for_a_minor(): void
    {
        $decision = $this->policy->evaluate(
            20,
            'UNICA',
            '2026-07-15',
            '2015-01-01',
            [$this->application(20, 'UNICA', '2025-07-15')]
        );

        $this->assertNull($decision);
    }

    public function test_yellow_fever_uses_an_eight_year_minimum_across_equivalent_ids(): void
    {
        $applications = [$this->application(37, 'UNICA', '2020-03-10')];

        $blocked = $this->policy->evaluate(15, 'UNICA', '2028-03-09', null, $applications);
        $allowed = $this->policy->evaluate(15, 'UNICA', '2028-03-10', null, $applications);

        $this->assertFalse($blocked['allowed']);
        $this->assertTrue($allowed['allowed']);
    }

    public function test_historical_yellow_fever_checks_the_future_application_too(): void
    {
        $applications = [$this->application(15, 'UNICA', '2030-01-01')];

        $blocked = $this->policy->evaluate(15, 'UNICA', '2022-01-02', null, $applications);
        $allowed = $this->policy->evaluate(15, 'UNICA', '2022-01-01', null, $applications);

        $this->assertFalse($blocked['allowed']);
        $this->assertTrue($allowed['allowed']);
    }

    public function test_toxoid_booster_blocks_less_than_thirty_days(): void
    {
        $applications = [$this->application(18, 'PRIMER REFUERZO', '2026-04-01')];

        $blocked = $this->policy->evaluate(41, 'SEGUNDO REFUERZO', '2026-04-30', null, $applications);
        $allowed = $this->policy->evaluate(41, 'SEGUNDO REFUERZO', '2026-05-01', null, $applications);

        $this->assertFalse($blocked['allowed']);
        $this->assertTrue($allowed['allowed']);
    }

    public function test_toxoid_primary_dose_keeps_the_regular_deduplication_rule(): void
    {
        $decision = $this->policy->evaluate(
            18,
            'TERCERA DOSIS',
            '2026-05-01',
            null,
            [$this->application(18, 'TERCERA DOSIS', '2025-05-01')]
        );

        $this->assertNull($decision);
    }

    public function test_special_policy_always_blocks_the_same_application_date(): void
    {
        $decision = $this->policy->evaluate(
            20,
            'UNICA',
            '2026-07-15',
            '1980-01-01',
            [$this->application(43, 'UNICA 0.5', '2026-07-15')]
        );

        $this->assertFalse($decision['allowed']);
        $this->assertStringContainsString('misma fecha', $decision['reason']);
    }

    private function application(int $vaccineId, string $dose, string $date): array
    {
        return [
            'vacunas_id' => $vaccineId,
            'docis' => $dose,
            'fecha_vacuna' => $date,
        ];
    }
}
