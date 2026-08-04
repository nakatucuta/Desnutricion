<?php

namespace Tests\Unit;

use App\Services\PaiClinicalDateNormalizer;
use App\Services\PaiGestationClinicalValidator;
use App\Services\PaiImportClinicalValidator;
use PHPUnit\Framework\TestCase;

class PaiImportClinicalValidatorTest extends TestCase
{
    private PaiImportClinicalValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new PaiImportClinicalValidator(
            new PaiClinicalDateNormalizer,
            new PaiGestationClinicalValidator
        );
    }

    public function test_edad_gestacional_alone_does_not_mark_the_row_as_gestante(): void
    {
        $row = array_fill(0, 256, null);
        $row[0] = '2026-07-15';
        $row[7] = '2026-06-01';
        $row[8] = 0;
        $row[13] = 'MUJER';
        $row[16] = 38;

        $this->assertSame([], $this->validator->validateExcelRow($row, 3));
    }

    public function test_invalid_serial_is_rejected_before_import(): void
    {
        $row = array_fill(0, 256, null);
        $row[0] = 280;

        $errors = $this->validator->validateExcelRow($row, 9);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Fila 9: Fecha de Atencion: la fecha 06/10/1900 no es valida para este campo.', $errors[0]);
        $this->assertStringNotContainsString('280', $errors[0]);
    }

    public function test_invalid_condition_is_rejected_and_gestante_with_weeks_is_accepted(): void
    {
        $row = array_fill(0, 256, null);
        $row[0] = '2026-07-15';
        $row[7] = '1996-01-01';
        $row[8] = 30;
        $row[13] = 'MUJER';
        $row[43] = 'OTRA CONDICION';

        $errors = $this->validator->validateExcelRow($row, 4);
        $this->assertStringContainsString('Condicion Usuaria debe ser', implode(' ', $errors));

        $row[43] = 'GESTANTE';
        $row[45] = '28,5';

        $this->assertSame([], $this->validator->validateExcelRow($row, 4));
    }

    public function test_lowercase_sex_and_condition_are_accepted_after_normalization(): void
    {
        $row = array_fill(0, 256, null);
        $row[0] = '2026-07-15';
        $row[7] = '1986-01-01';
        $row[8] = 40;
        $row[13] = 'mujer';
        $row[43] = 'mujer en edad f'."\xC3\xA9".'rtil';

        $this->assertSame([], $this->validator->validateExcelRow($row, 5));
    }

    public function test_fertile_age_woman_with_1900_due_date_is_not_accepted_as_gestante(): void
    {
        $row = array_fill(0, 256, null);
        $row[0] = '2026-07-15';
        $row[7] = '1986-01-01';
        $row[8] = 40;
        $row[13] = 'MUJER';
        $row[43] = 'MUJER EN EDAD FERTIL';
        $row[46] = '1900-10-06';

        $errors = implode(' ', $this->validator->validateExcelRow($row, 7));

        $this->assertStringContainsString(
            'Fecha Probable de Parto: fecha no valida',
            $errors
        );
        $this->assertStringContainsString('si no aplica, deja la celda vacia', $errors);
        $this->assertStringNotContainsString('requieren Condicion Usuaria = GESTANTE', $errors);
    }

    public function test_clinical_error_messages_do_not_contain_mojibake(): void
    {
        $row = array_fill(0, 256, null);
        $row[0] = '2026-07-15';
        $row[7] = '1996-01-01';
        $row[8] = 30;
        $row[13] = 'mujer';
        $row[43] = 'gestante';

        $message = implode(' ', $this->validator->validateExcelRow($row, 6));

        $this->assertStringNotContainsString("\xC3\x83", $message);
        $this->assertStringNotContainsString("\xC3\xA2", $message);
        $this->assertStringContainsString('inconsistencia clinica de gestacion', $message);
        $this->assertStringContainsString('una gestante requiere al menos uno', $message);
    }
}
