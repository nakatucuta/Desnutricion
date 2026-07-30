<?php

namespace App\Services;

class PaiImportRetryPolicy
{
    public function action(
        string $status,
        bool $retryRequested,
        bool $retryable,
        int $retryCount,
        int $maxRetries
    ): string {
        if (in_array($status, ['queued', 'running'], true)) {
            return 'reuse_active';
        }

        if ($status === 'done') {
            return 'reuse_done';
        }

        if ($status !== 'failed') {
            return 'reuse_active';
        }

        if (! $retryable || $retryCount >= $maxRetries) {
            return 'retry_blocked';
        }

        if (! $retryRequested) {
            return 'retry_available';
        }

        return 'retry';
    }
}
