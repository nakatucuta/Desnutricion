<?php

namespace Tests\Unit;

use App\Http\Controllers\AfiliadoController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PaiCoveragePopulationRuleTest extends TestCase
{
    public function test_vph_female_rule_uses_woman_and_vaccination_date_for_age(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule($query, '9_to_17_f', '2026-12-31', 'v.fecha_vacuna');

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString('CONVERT(DATE, v.fecha_vacuna)', $sql);
        $this->assertStringContainsString('BETWEEN 108 AND 215', $sql);
        $this->assertStringContainsString("LIKE 'MUJER%'", $sql);
        $this->assertStringNotContainsString("LIKE 'HOMBRE%'", $sql);
    }

    public function test_vph_male_rule_uses_man_and_vaccination_date_for_age(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule($query, '9_to_17_m', '2026-12-31', 'v.fecha_vacuna');

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString('CONVERT(DATE, v.fecha_vacuna)', $sql);
        $this->assertStringContainsString('BETWEEN 108 AND 215', $sql);
        $this->assertStringContainsString("LIKE 'HOMBRE%'", $sql);
        $this->assertStringNotContainsString("LIKE 'MUJER%'", $sql);
    }

    public function test_newborn_rule_covers_zero_to_one_completed_month(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule($query, '0_to_1m', '2026-06-30', 'v.fecha_vacuna');

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString('BETWEEN 0 AND 1', $sql);
    }

    public function test_under_one_rule_covers_two_to_eleven_completed_months(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule($query, '2_to_11m', '2026-06-30', 'v.fecha_vacuna');

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString('BETWEEN 2 AND 11', $sql);
    }

    public function test_population_target_keeps_period_cutoff_as_age_reference(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule($query, '12_to_23m', '2026-06-30');

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString("CONVERT(DATE, '2026-06-30')", $sql);
        $this->assertStringNotContainsString('v.fecha_vacuna', $sql);
    }

    public function test_coverage_indicators_use_the_requested_age_windows(): void
    {
        $method = new ReflectionMethod(AfiliadoController::class, 'paiIndicatorsDefinition');
        $indicators = collect($method->invoke(new AfiliadoController))->keyBy('key');

        $this->assertSame('0_to_1m', $indicators['bcg']['population_rule']);
        $this->assertSame('2_to_11m', $indicators['penta_3']['population_rule']);
        $this->assertSame('12_to_23m', $indicators['triple_viral_1']['population_rule']);
        $this->assertSame('60_to_71m', $indicators['dpt_ref2']['population_rule']);
    }

    public function test_applied_gestante_doses_require_exact_condition_on_vaccine(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule(
            $query,
            'gestante',
            '2026-06-30',
            'v.fecha_vacuna',
            'v.condicion_usuaria'
        );

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString("ISNULL(v.condicion_usuaria, '')", $sql);
        $this->assertStringContainsString("= 'GESTANTE'", $sql);
        $this->assertStringNotContainsString('fecha_prob_parto', $sql);
        $this->assertStringNotContainsString('semanas_gestacion', $sql);
        $this->assertStringNotContainsString('LIKE', $sql);
    }

    public function test_gestante_population_requires_exact_condition_on_affiliate(): void
    {
        $query = new PaiPopulationRuleRecordingQuery;

        $this->applyRule($query, 'gestante', '2026-06-30');

        $sql = implode(' ', $query->rawClauses);

        $this->assertStringContainsString("ISNULL(a.condicion_usuaria, '')", $sql);
        $this->assertStringContainsString("= 'GESTANTE'", $sql);
        $this->assertStringNotContainsString('fecha_prob_parto', $sql);
        $this->assertStringNotContainsString('semanas_gestacion', $sql);
    }

    private function applyRule(
        PaiPopulationRuleRecordingQuery $query,
        string $rule,
        string $cutoffDate,
        ?string $ageReferenceColumn = null,
        string $conditionColumn = 'a.condicion_usuaria'
    ): void {
        $method = new ReflectionMethod(AfiliadoController::class, 'applyPaiPopulationRule');
        $method->invoke(
            new AfiliadoController,
            $query,
            $rule,
            $cutoffDate,
            $ageReferenceColumn,
            $conditionColumn
        );
    }
}

class PaiPopulationRuleRecordingQuery
{
    public array $rawClauses = [];

    public function whereNotNull(string $column): self
    {
        return $this;
    }

    public function whereRaw(string $sql): self
    {
        $this->rawClauses[] = $sql;

        return $this;
    }
}
