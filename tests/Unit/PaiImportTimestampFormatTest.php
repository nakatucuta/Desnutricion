<?php

namespace Tests\Unit;

use App\Models\ImportJob;
use App\Models\PaiImportFileClaim;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class PaiImportTimestampFormatTest extends TestCase
{
    public function test_file_claim_serializes_timestamps_for_sql_server(): void
    {
        $claim = new PaiImportFileClaim;
        $claim->updated_at = CarbonImmutable::parse('2026-07-29 15:04:05');

        $this->assertSame('Ymd H:i:s', $claim->getDateFormat());
        $this->assertSame('20260729 15:04:05', $claim->getAttributes()['updated_at']);
    }

    public function test_import_job_serializes_timestamps_for_sql_server(): void
    {
        $job = new ImportJob;
        $job->updated_at = CarbonImmutable::parse('2026-07-29 15:04:05');

        $this->assertSame('Ymd H:i:s', $job->getDateFormat());
        $this->assertSame('20260729 15:04:05', $job->getAttributes()['updated_at']);
    }
}
