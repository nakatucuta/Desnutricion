<?php

namespace Tests\Unit;

use App\Services\PaiDoseNormalizer;
use App\Services\PaiVaccineClinicalIdentity;
use DateTimeImmutable;
use Tests\TestCase;

class PaiVaccineClinicalIdentityTest extends TestCase
{
    private PaiVaccineClinicalIdentity $identity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->identity = new PaiVaccineClinicalIdentity(new PaiDoseNormalizer);
    }

    public function test_equivalent_dose_texts_on_the_same_date_have_the_same_identity(): void
    {
        $first = $this->identity->key(10, 19, '1RA DOSIS', '2026-07-20');
        $duplicate = $this->identity->key(10, 19, 'primera dosis', new DateTimeImmutable('2026-07-20 18:30:00'));

        $this->assertSame($first, $duplicate);
    }

    public function test_same_vaccine_and_dose_on_different_dates_are_distinct_applications(): void
    {
        $firstPregnancy = $this->identity->key(10, 19, 'UNICA', '2025-03-10');
        $laterPregnancy = $this->identity->key(10, 19, 'DOSIS UNICA', '2026-07-20');

        $this->assertNotSame($firstPregnancy, $laterPregnancy);
    }

    public function test_identity_changes_when_any_clinical_component_changes(): void
    {
        $base = $this->identity->key(10, 19, 'PRIMERA DOSIS', '2026-07-20');

        $this->assertNotSame($base, $this->identity->key(11, 19, 'PRIMERA DOSIS', '2026-07-20'));
        $this->assertNotSame($base, $this->identity->key(10, 20, 'PRIMERA DOSIS', '2026-07-20'));
        $this->assertNotSame($base, $this->identity->key(10, 19, 'SEGUNDA DOSIS', '2026-07-20'));
        $this->assertNotSame($base, $this->identity->key(10, 19, 'PRIMERA DOSIS', '2026-07-21'));
    }

    public function test_null_dose_is_stable_for_vaccines_measured_by_vials(): void
    {
        $first = $this->identity->key(10, 23, null, '20/07/2026');
        $duplicate = $this->identity->key(10, 23, '', '2026-07-20');

        $this->assertSame($first, $duplicate);
    }
}
