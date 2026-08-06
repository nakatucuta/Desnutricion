<?php

namespace Tests\Unit;

use App\Imports\GesTipo1Import;
use Carbon\Carbon;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class GesTipo1ImportValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_fecha_probable_parto_accepts_up_to_seven_days_before_load_date(): void
    {
        Carbon::setTestNow('2026-08-06 10:00:00');
        $import = $this->newImportWithoutDatabase();

        $this->validateFechaProbableParto($import, '2026-07-30');

        $this->assertSame([], $import->getErrores());
    }

    public function test_fecha_probable_parto_rejects_eight_days_before_load_date(): void
    {
        Carbon::setTestNow('2026-08-06 10:00:00');
        $import = $this->newImportWithoutDatabase();

        $this->validateFechaProbableParto($import, '2026-07-29');

        $this->assertCount(1, $import->getErrores());
        $this->assertStringContainsString(
            'fecha anterior al rango permitido',
            $import->getErrores()[0]
        );
    }

    public function test_fecha_probable_parto_explains_when_date_is_after_allowed_range(): void
    {
        Carbon::setTestNow('2026-08-06 10:00:00');
        $import = $this->newImportWithoutDatabase();

        $this->validateFechaProbableParto($import, '2027-05-28');

        $this->assertCount(1, $import->getErrores());
        $this->assertStringContainsString(
            'fecha posterior al rango permitido',
            $import->getErrores()[0]
        );
    }

    private function newImportWithoutDatabase(): GesTipo1Import
    {
        return (new ReflectionClass(GesTipo1Import::class))->newInstanceWithoutConstructor();
    }

    private function validateFechaProbableParto(GesTipo1Import $import, string $fecha): void
    {
        (new ReflectionMethod($import, 'validateFechaProbableParto'))->invoke(
            $import,
            $fecha,
            '1990-01-01',
            2
        );
    }
}
