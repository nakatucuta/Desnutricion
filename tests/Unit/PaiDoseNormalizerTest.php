<?php

namespace Tests\Unit;

use App\Services\PaiDoseNormalizer;
use Tests\TestCase;

class PaiDoseNormalizerTest extends TestCase
{
    public function test_combined_antirabica_doses_use_full_dose_labels(): void
    {
        $normalizer = new PaiDoseNormalizer();

        $this->assertSame(
            'PRIMERA DOSIS Y SEGUNDA DOSIS',
            $normalizer->normalizeDocisStrict('PRIMERA Y SEGUNDA DOSIS')
        );

        $this->assertSame(
            'PRIMERA DOSIS Y SEGUNDA DOSIS',
            $normalizer->normalizeDocisStrict('1RA Y 2DA DOSIS')
        );

        $this->assertSame(
            'TERCERA DOSIS Y CUARTA DOSIS',
            $normalizer->normalizeDocisStrict('TERCERA Y CUARTA DOSIS')
        );
    }

    public function test_recien_nacido_with_accent_is_normalized(): void
    {
        $normalizer = new PaiDoseNormalizer();

        $this->assertSame(
            'RECIEN NACIDO',
            $normalizer->normalizeDocisStrict('Reci' . "\xC3\xA9" . 'n nacido')
        );
    }

    public function test_unica_with_accent_is_normalized(): void
    {
        $normalizer = new PaiDoseNormalizer();

        $this->assertSame(
            'UNICA',
            $normalizer->normalizeDocisStrict("\xC3\x9Anica")
        );
    }

    public function test_primer_refuerzo_can_match_generic_refuerzo_when_allowed(): void
    {
        $normalizer = new PaiDoseNormalizer();

        $this->assertSame(
            'REFUERZO',
            $normalizer->normalizeDocisStrictForAllowed('primer refuerzo', ['PRIMERA DOSIS', 'REFUERZO'])
        );
    }

    public function test_primer_refuerzo_is_preserved_when_specific_dose_is_allowed(): void
    {
        $normalizer = new PaiDoseNormalizer();

        $this->assertSame(
            'PRIMER REFUERZO',
            $normalizer->normalizeDocisStrictForAllowed('primer refuerzo', ['PRIMER REFUERZO', 'SEGUNDO REFUERZO'])
        );
    }

    public function test_influenza_accepts_unica_as_valid_dose(): void
    {
        $normalizer = new PaiDoseNormalizer();
        $allowed = config('pai_docis.valid_doses_by_vacunas_id.20');

        $this->assertContains('UNICA', $allowed);
        $this->assertSame(
            'UNICA',
            $normalizer->normalizeDocisStrictForAllowed('unica', $allowed)
        );
    }

    public function test_covid_19_accepts_unica_as_valid_dose(): void
    {
        $normalizer = new PaiDoseNormalizer();
        $allowed = config('pai_docis.valid_doses_by_vacunas_id.1');

        $this->assertContains('UNICA', $allowed);
        $this->assertSame(
            'UNICA',
            $normalizer->normalizeDocisStrictForAllowed('unica', $allowed)
        );
    }
}
