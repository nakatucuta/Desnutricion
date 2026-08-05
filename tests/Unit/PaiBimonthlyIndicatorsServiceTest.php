<?php

namespace Tests\Unit;

use App\Services\PaiBimonthlyIndicatorsService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PaiBimonthlyIndicatorsServiceTest extends TestCase
{
    public function test_annual_target_is_distributed_deterministically_by_month(): void
    {
        $service = new PaiBimonthlyIndicatorsService();
        $method = new ReflectionMethod($service, 'splitAnnualTarget');

        $this->assertSame([9, 9], $method->invoke($service, 100, [1, 2]));
        $this->assertSame([8, 8], $method->invoke($service, 100, [5, 6]));
    }

    public function test_zero_programmed_returns_null_percentage(): void
    {
        $service = new PaiBimonthlyIndicatorsService();
        $method = new ReflectionMethod($service, 'percentage');

        $this->assertNull($method->invoke($service, 2, 0));
        $this->assertSame(125.0, $method->invoke($service, 5, 4));
    }

    public function test_dpt_acellular_is_not_classified_as_regular_dpt(): void
    {
        $service = new PaiBimonthlyIndicatorsService();
        $method = new ReflectionMethod($service, 'vaccineKey');
        $method->setAccessible(true);

        $this->assertSame('dpt_acelular', $method->invoke($service, 0, 'DPT ACELULAR', []));
        $this->assertSame('dpt', $method->invoke($service, 0, 'DPT', []));
    }
}
