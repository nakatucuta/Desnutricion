<?php

namespace Tests\Unit;

use App\Models\PaiIpsReferencia;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class PaiIpsReferenciaTest extends TestCase
{
    public function test_it_serializes_timestamps_in_an_unambiguous_sql_server_format(): void
    {
        $reference = new PaiIpsReferencia;
        $reference->updated_at = CarbonImmutable::parse('2026-07-17 10:47:38');

        $this->assertSame('Ymd H:i:s', $reference->getDateFormat());
        $this->assertSame('20260717 10:47:38', $reference->getAttributes()['updated_at']);
    }
}
