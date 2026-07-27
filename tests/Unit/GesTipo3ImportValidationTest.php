<?php

namespace Tests\Unit;

use App\Imports\GesTipo3Import;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as SpreadsheetRow;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class GesTipo3ImportValidationTest extends TestCase
{
    public function test_only_unconditionally_required_columns_report_errors_without_cups_context(): void
    {
        $import = $this->newImportWithoutDatabase();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $values = array_fill(0, 23, 'NULL');
        $values[0] = 3;
        $sheet->fromArray([$values], null, 'A2');

        $import->onRow(new Row(new SpreadsheetRow($sheet, 2)));

        $errors = $import->getErrores();

        $this->assertCount(18, $errors);
        $this->assertSame(1, $import->getCounters()['rows_invalid']);

        foreach ([
            'Consecutivo de registro',
            'Tipo identificacion',
            'No ID usuario',
            'Fecha tecnologia en salud',
            'Codigo CUPS',
            'Finalidad tecnologia en salud',
            'Clasificacion riesgo gestacional',
            'Clasificacion riesgo preeclampsia',
            'Suministro ASA',
            'Suministro acido folico',
            'Suministro sulfato ferroso',
            'Suministro calcio',
            'Fecha suministro anticonceptivo post evento',
            'Suministro metodo anticonceptivo post evento',
            'Fecha salida aborto/parto/cesarea',
            'Fecha terminacion gestacion',
            'Tipo terminacion gestacion',
            'Resultado hemoglobina',
        ] as $field) {
            $this->assertTrue(
                collect($errors)->contains(fn (string $error) => str_contains($error, $field.':')),
                "No se encontro el error obligatorio para {$field}"
            );
        }
    }

    public function test_allowed_integer_codes_are_preserved_and_other_values_are_rejected(): void
    {
        foreach ([0, 1, 21] as $code) {
            $import = $this->newImportWithoutDatabase();
            $result = $this->invoke(
                $import,
                'parseAllowedIntegerCode',
                [$code, 2, 'Suministro calcio', [0, 1, 21]]
            );

            $this->assertSame($code, $result);
            $this->assertSame([], $import->getErrores());
        }

        $import = $this->newImportWithoutDatabase();
        $result = $this->invoke(
            $import,
            'parseAllowedIntegerCode',
            [2, 2, 'Suministro calcio', [0, 1, 21]]
        );

        $this->assertNull($result);
        $this->assertStringContainsString('permitidos: 0, 1, 21', $import->getErrores()[0]);
    }

    public function test_dates_are_validated_and_normalized_to_iso_format(): void
    {
        $excelSerial = ExcelDate::dateTimeToExcel(new \DateTimeImmutable('2026-07-27 14:30:00'));

        foreach ([
            '2026-07-27' => '2026-07-27',
            '1845-01-01' => '1845-01-01',
            '27/07/2026' => '2026-07-27',
            '07-27-2026' => '2026-07-27',
            '2026-7-1' => '2026-07-01',
            '1/7/2026' => '2026-07-01',
            '2026/07/27 14:30:00' => '2026-07-27',
            $excelSerial => '2026-07-27',
            (string) $excelSerial => '2026-07-27',
        ] as $input => $expected) {
            $import = $this->newImportWithoutDatabase();
            $result = $this->invoke(
                $import,
                'parseDate',
                [$input, 2, 'Fecha prueba', true]
            );

            $this->assertSame($expected, $result);
        }

        $import = $this->newImportWithoutDatabase();
        $result = $this->invoke(
            $import,
            'parseDate',
            ['2026-02-30', 2, 'Fecha prueba', true]
        );

        $this->assertNull($result);
        $this->assertStringContainsString('fecha invalida', $import->getErrores()[0]);
    }

    public function test_decimal_formats_follow_excel_rules(): void
    {
        $import = $this->newImportWithoutDatabase();
        $this->assertSame(
            25.4,
            $this->invoke($import, 'parseFormattedDecimal', ['25.4', 2, 'IMC', true, 1, 5, 80])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertNull(
            $this->invoke($import, 'parseFormattedDecimal', ['25,4', 2, 'IMC', true, 1, 5, 80])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertSame(
            25.4,
            $this->invoke($import, 'parseFormattedDecimal', ['25.44', 2, 'IMC', true, 1, 5, 80])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertSame(
            25.5,
            $this->invoke($import, 'parseFormattedDecimal', ['25.45', 2, 'IMC', true, 1, 5, 80])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertSame(
            1.26,
            $this->invoke($import, 'parseFormattedDecimal', ['1.256', 2, 'IP uterinas', true, 2, 0, 20])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertSame(
            1.2,
            $this->invoke($import, 'parseFormattedDecimal', ['1.2', 2, 'IP uterinas', true, 2, 0, 20])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertSame(
            10.26,
            $this->invoke($import, 'parseFormattedDecimal', ['10.256', 2, 'IP uterinas', true, 2, 0, 20])
        );

        $import = $this->newImportWithoutDatabase();
        $this->assertNull(
            $this->invoke($import, 'parseFormattedDecimal', ['20.006', 2, 'IP uterinas', true, 2, 0, 20])
        );
        $this->assertStringContainsString('debe ser <= 20', $import->getErrores()[0]);
    }

    public function test_consulta_with_finalidad_23_requires_pas_pad_and_imc(): void
    {
        $import = $this->importForCatalogCodes('890201', 23);
        $import->onRow($this->makeRow([
            5 => '890201',
            6 => 23,
        ]));

        $errors = $import->getErrores();

        foreach ([
            'Tension arterial sistolica PAS',
            'Tension arterial diastolica PAD',
            'Indice de masa corporal',
        ] as $field) {
            $this->assertTrue(
                collect($errors)->contains(fn (string $error) => str_contains($error, $field.': campo obligatorio')),
                "No se encontro la obligatoriedad condicional para {$field}"
            );
        }

        $this->assertFalse(
            collect($errors)->contains(fn (string $error) => str_contains($error, 'Indice de pulsatilidad arterias uterinas:'))
        );
    }

    public function test_consulta_without_finalidad_23_allows_pas_pad_and_imc_empty(): void
    {
        $import = $this->importForCatalogCodes('890201', 22);
        $import->onRow($this->makeRow([
            5 => '890201',
            6 => 22,
        ]));

        $errors = $import->getErrores();

        foreach ([
            'Tension arterial sistolica PAS',
            'Tension arterial diastolica PAD',
            'Indice de masa corporal',
        ] as $field) {
            $this->assertFalse(
                collect($errors)->contains(fn (string $error) => str_contains($error, $field.':')),
                "{$field} no debe ser obligatorio para una finalidad diferente de 23"
            );
        }
    }

    public function test_non_consulta_with_finalidad_23_allows_pas_pad_and_imc_empty(): void
    {
        $import = $this->importForCatalogCodes('999999', 23);
        $import->onRow($this->makeRow([
            5 => '999999',
            6 => 23,
        ]));

        $errors = $import->getErrores();

        foreach ([
            'Tension arterial sistolica PAS',
            'Tension arterial diastolica PAD',
            'Indice de masa corporal',
        ] as $field) {
            $this->assertFalse(
                collect($errors)->contains(fn (string $error) => str_contains($error, $field.':')),
                "{$field} no debe ser obligatorio para un CUPS que no sea de consulta"
            );
        }
    }

    public function test_ecografia_requires_uterine_artery_pulsatility(): void
    {
        $import = $this->importForCatalogCodes('881401', 22);
        $import->onRow($this->makeRow([
            5 => '881401',
            6 => 22,
        ]));

        $this->assertTrue(
            collect($import->getErrores())->contains(
                fn (string $error) => str_contains(
                    $error,
                    'Indice de pulsatilidad arterias uterinas: campo obligatorio'
                )
            )
        );
    }

    public function test_exact_cups_lists_are_used_for_conditional_rules(): void
    {
        $import = $this->newImportWithoutDatabase();

        foreach ([
            '890201', '890205', '890206', '890250', '890266', '890301',
            '890302', '890305', '890306', '890350', '890366', '890701',
        ] as $cups) {
            $this->assertTrue($this->invoke($import, 'isCupsConsulta', [$cups]));
        }

        foreach ([
            '881401', '881402', '881403', '881410', '881431',
            '881432', '881434', '881435', '881436', '881437',
        ] as $cups) {
            $this->assertTrue($this->invoke($import, 'isCupsEcografia', [$cups]));
        }

        $this->assertFalse($this->invoke($import, 'isCupsConsulta', ['890202']));
        $this->assertFalse($this->invoke($import, 'isCupsEcografia', ['881404']));
    }

    private function newImportWithoutDatabase(): GesTipo3Import
    {
        return (new ReflectionClass(GesTipo3Import::class))->newInstanceWithoutConstructor();
    }

    private function importForCatalogCodes(string $cups, int $finalidad): GesTipo3Import
    {
        $import = $this->newImportWithoutDatabase();
        $reflection = new ReflectionClass($import);

        $reflection->getProperty('cupsSet')->setValue($import, [$cups => 0]);
        $reflection->getProperty('finalidadSet')->setValue($import, [(string) $finalidad => 0]);

        return $import;
    }

    private function makeRow(array $overrides): Row
    {
        $values = [
            3,
            1,
            'NULL',
            'NULL',
            '2026-07-20',
            '890201',
            23,
            4,
            4,
            0,
            0,
            0,
            0,
            '1845-01-01',
            0,
            '1845-01-01',
            '1845-01-01',
            0,
            'NULL',
            'NULL',
            'NULL',
            '12.0',
            'NULL',
        ];

        foreach ($overrides as $index => $value) {
            $values[$index] = $value;
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$values], null, 'A2');

        return new Row(new SpreadsheetRow($sheet, 2));
    }

    private function invoke(GesTipo3Import $import, string $method, array $arguments)
    {
        return (new ReflectionMethod($import, $method))->invokeArgs($import, $arguments);
    }
}
