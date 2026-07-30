<?php

namespace Tests\Unit;

use App\Services\PaiClinicalDateNormalizer;
use PHPUnit\Framework\TestCase;

class PaiClinicalDateNormalizerTest extends TestCase
{
    public function test_it_rejects_small_excel_serials_instead_of_turning_them_into_dates_from_1900(): void
    {
        $normalizer = new PaiClinicalDateNormalizer();

        $this->assertNull($normalizer->normalize(280));
        $this->assertStringContainsString(
            'serial Excel 280 fuera del rango permitido',
            $normalizer->validationError(280, 'fecha_atencion')
        );
    }

    public function test_it_accepts_modern_excel_serials_and_strict_text_dates(): void
    {
        $normalizer = new PaiClinicalDateNormalizer();

        $this->assertSame('2026-07-15', $normalizer->normalize('15/07/2026'));
        $this->assertNotNull($normalizer->normalize(46000));
        $this->assertNull($normalizer->normalize('31/02/2026'));
    }
}
