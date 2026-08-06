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
            'Fecha Probable de Parto: la fecha 06/10/1900 no es valida para este campo. Si no aplica, deja la celda vacia.',
            $normalizer->validationError(280, 'Fecha Probable de Parto')
        );
        $this->assertStringNotContainsString(
            '280',
            $normalizer->validationError(280, 'Fecha Probable de Parto')
        );
    }

    public function test_it_accepts_modern_excel_serials_and_strict_text_dates(): void
    {
        $normalizer = new PaiClinicalDateNormalizer();

        $this->assertSame('2026-07-15', $normalizer->normalize('15/07/2026'));
        $this->assertNotNull($normalizer->normalize(46000));
        $this->assertNull($normalizer->normalize('31/02/2026'));
    }

    public function test_it_accepts_historical_excel_serial_for_birth_date(): void
    {
        $normalizer = new PaiClinicalDateNormalizer();

        $this->assertSame('1925-04-08', $normalizer->normalize(9230, 'Fecha de Nacimiento'));
        $this->assertNull($normalizer->validationError(9230, 'Fecha de Nacimiento'));
        $this->assertNull($normalizer->normalize(9230));
    }

    public function test_it_still_rejects_1900_as_birth_date_placeholder(): void
    {
        $normalizer = new PaiClinicalDateNormalizer();

        $this->assertNull($normalizer->normalize(280, 'Fecha de Nacimiento'));
        $this->assertNotNull($normalizer->validationError(280, 'Fecha de Nacimiento'));
        $this->assertNull($normalizer->normalize('06/10/1900', 'Fecha de Nacimiento'));
        $this->assertNotNull($normalizer->validationError('06/10/1900', 'Fecha de Nacimiento'));
    }

    public function test_it_rejects_old_clinical_dates_like_1900(): void
    {
        $normalizer = new PaiClinicalDateNormalizer();

        $this->assertSame('1900-10-06', $normalizer->normalize('1900-10-06'));
        $this->assertStringContainsString(
            'si no aplica, deja la celda vacia',
            $normalizer->validationError('1900-10-06', 'Fecha Probable de Parto')
        );
        $this->assertNull($normalizer->validationError('1986-01-01', 'Fecha de Nacimiento'));
    }
}
