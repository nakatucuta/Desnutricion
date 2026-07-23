<?php

namespace Tests\Unit;

use App\Services\PaiStatisticsService;
use PHPUnit\Framework\TestCase;

class PaiStatisticsServiceTest extends TestCase
{
    public function test_assignment_percentage_uses_all_vaccinated_doses_as_denominator(): void
    {
        $this->assertSame(58.0, PaiStatisticsService::assignmentPercentage(58, 100));
        $this->assertSame(42.86, PaiStatisticsService::assignmentPercentage(3, 7));
    }

    public function test_assignment_percentage_handles_empty_or_inconsistent_totals(): void
    {
        $this->assertSame(0.0, PaiStatisticsService::assignmentPercentage(0, 0));
        $this->assertSame(0.0, PaiStatisticsService::assignmentPercentage(-2, 10));
        $this->assertSame(100.0, PaiStatisticsService::assignmentPercentage(12, 10));
    }
}
