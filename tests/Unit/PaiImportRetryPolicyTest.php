<?php

namespace Tests\Unit;

use App\Services\PaiImportRetryPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PaiImportRetryPolicyTest extends TestCase
{
    #[DataProvider('states')]
    public function test_it_decides_reuse_and_controlled_retry(
        string $status,
        bool $requested,
        bool $retryable,
        int $retryCount,
        int $maxRetries,
        string $expected
    ): void {
        $policy = new PaiImportRetryPolicy();

        $this->assertSame(
            $expected,
            $policy->action($status, $requested, $retryable, $retryCount, $maxRetries)
        );
    }

    public static function states(): array
    {
        return [
            'queued is reused' => ['queued', false, false, 0, 3, 'reuse_active'],
            'running is reused' => ['running', true, true, 0, 3, 'reuse_active'],
            'done returns previous batch' => ['done', false, false, 0, 3, 'reuse_done'],
            'failed offers retry' => ['failed', false, true, 0, 3, 'retry_available'],
            'failed retries when rollback succeeded' => ['failed', true, true, 0, 3, 'retry'],
            'failed does not offer unsafe retry' => ['failed', false, false, 0, 3, 'retry_blocked'],
            'failed blocks retry without rollback' => ['failed', true, false, 0, 3, 'retry_blocked'],
            'failed blocks retry at limit' => ['failed', true, true, 3, 3, 'retry_blocked'],
        ];
    }
}
