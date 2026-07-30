<?php

namespace App\Services;

use App\Models\ImportJob;
use App\Models\PaiImportFileClaim;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class PaiImportFileIdempotencyService
{
    public function __construct(
        private PaiImportRetryPolicy $retryPolicy
    ) {}

    public function claim(
        string $storedFilePath,
        string $originalName,
        int $userId,
        string $formatVersion,
        ?string $requestedToken = null,
        bool $retryFailed = false,
        bool $allowNewWork = true
    ): array {
        try {
            $fingerprint = $this->fingerprint($storedFilePath);
        } catch (\Throwable $e) {
            $this->cleanup($storedFilePath);
            throw $e;
        }
        $sha256 = $fingerprint['sha256'];
        $fileSize = $fingerprint['size'];
        $contentSha256 = $fingerprint['content_sha256'];
        $contentRows = $fingerprint['content_rows'];
        $contentColumns = $fingerprint['content_columns'];

        $db = DB::connection('sqlsrv');
        $semanticColumnsReady = $this->semanticColumnsReady();
        $keepFile = false;

        try {
            $result = $db->transaction(function () use (
                $db,
                $sha256,
                $contentSha256,
                $fileSize,
                $contentRows,
                $contentColumns,
                $semanticColumnsReady,
                $originalName,
                $userId,
                $formatVersion,
                $requestedToken,
                $retryFailed,
                $allowNewWork
            ) {
                $activeContentSha256 = $semanticColumnsReady ? $contentSha256 : null;
                $this->acquireFingerprintLock($db, $activeContentSha256 ?: $sha256, $formatVersion);

                $claim = PaiImportFileClaim::query()
                    ->where('format_version', $formatVersion)
                    ->when($this->releaseColumnsReady(), function ($query) {
                        $query->whereNull('released_at');
                    })
                    ->where(function ($query) use ($sha256, $activeContentSha256) {
                        $query->where('file_sha256', $sha256);
                        if ($activeContentSha256 !== null) {
                            $query->orWhere('content_sha256', $activeContentSha256);
                        }
                    })
                    ->lockForUpdate()
                    ->first();

                if (! $claim) {
                    if (! $allowNewWork) {
                        return $this->result('busy', null, null, $sha256, (int) $fileSize, false);
                    }

                    $claimPayload = [
                        'file_sha256' => $sha256,
                        'file_size' => (int) $fileSize,
                        'format_version' => $formatVersion,
                        'first_original_name' => $originalName,
                        'last_original_name' => $originalName,
                        'first_user_id' => $userId,
                        'last_user_id' => $userId,
                        'status' => 'queued',
                        'submission_count' => 1,
                        'retry_count' => 0,
                    ];
                    if ($semanticColumnsReady) {
                        $claimPayload['content_sha256'] = $contentSha256;
                        $claimPayload['content_rows'] = $contentRows;
                        $claimPayload['content_columns'] = $contentColumns;
                    }

                    $claim = $this->createClaimRecord($claimPayload, $formatVersion, $sha256, $activeContentSha256);

                    $job = $this->createJob(
                        $claim,
                        $userId,
                        $requestedToken,
                        $sha256,
                        $semanticColumnsReady ? $contentSha256 : null,
                        (int) $fileSize,
                        $semanticColumnsReady ? $contentRows : null,
                        $semanticColumnsReady ? $contentColumns : null,
                        $originalName,
                        $formatVersion
                    );

                    $claim->current_import_job_id = $job->id;
                    $claim->save();

                    return $this->result('created', $claim, $job, $sha256, (int) $fileSize, true);
                }

                $claim->last_original_name = $originalName;
                $claim->last_user_id = $userId;
                $claim->submission_count = (int) $claim->submission_count + 1;
                if ($semanticColumnsReady) {
                    $claim->content_sha256 = $claim->content_sha256 ?: $contentSha256;
                    $claim->content_rows = $claim->content_rows ?: $contentRows;
                    $claim->content_columns = $claim->content_columns ?: $contentColumns;
                }

                $job = $claim->current_import_job_id
                    ? ImportJob::find((int) $claim->current_import_job_id)
                    : null;

                if (! $job) {
                    $claim->save();

                    return $this->result('retry_blocked', $claim, null, $sha256, (int) $fileSize, false);
                }

                $action = $this->retryPolicy->action(
                    (string) $job->status,
                    $retryFailed,
                    (bool) $job->retryable,
                    (int) $job->retry_count,
                    max(0, (int) config('pai_import.max_failed_retries', 3))
                );

                if ($action === 'retry' && ! $allowNewWork) {
                    $action = 'busy';
                }

                if ($action === 'retry') {
                    $job->user_id = $userId;
                    $job->status = 'queued';
                    $job->percent = 0;
                    $job->step = 'cola';
                    $job->message = 'Reintento en cola...';
                    $job->errors = null;
                    $job->errors_count = 0;
                    $job->report_path = null;
                    $job->retry_count = (int) $job->retry_count + 1;
                    $job->retryable = false;
                    $job->save();

                    $claim->status = 'queued';
                    $claim->retry_count = (int) $claim->retry_count + 1;
                    $claim->save();

                    return $this->result('retried', $claim, $job, $sha256, (int) $fileSize, true);
                }

                $claim->status = (string) $job->status;
                $claim->batch_verifications_id = $job->batch_verifications_id;
                $claim->save();

                return $this->result($action, $claim, $job, $sha256, (int) $fileSize, false);
            }, 3);

            $keepFile = (bool) ($result['dispatch'] ?? false);

            return $result;
        } finally {
            if (! $keepFile) {
                $this->cleanup($storedFilePath);
            }
        }
    }

    public function syncFromJob(ImportJob $job): void
    {
        if (! $job->file_claim_id) {
            return;
        }

        $payload = [
            'status' => (string) $job->status,
            'batch_verifications_id' => $job->batch_verifications_id,
            'updated_at' => DB::raw('GETDATE()'),
        ];

        if ($this->releaseColumnsReady()) {
            $payload['released_at'] = null;
            $payload['released_by_user_id'] = null;
            $payload['release_reason'] = null;
        }

        PaiImportFileClaim::query()
            ->where('id', (int) $job->file_claim_id)
            ->where('current_import_job_id', (int) $job->id)
            ->update($payload);
    }

    public function releaseClaimsForBatches(array $batchIds, int $releasedByUserId, string $reason): int
    {
        $batchIds = collect($batchIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($batchIds === [] || ! $this->releaseColumnsReady()) {
            return 0;
        }

        $released = 0;

        DB::connection('sqlsrv')->transaction(function () use ($batchIds, $releasedByUserId, $reason, &$released) {
            $jobClaimBatchById = ImportJob::query()
                ->whereIn('batch_verifications_id', $batchIds)
                ->whereNotNull('file_claim_id')
                ->pluck('batch_verifications_id', 'file_claim_id')
                ->mapWithKeys(fn ($batchId, $claimId) => [(int) $claimId => (int) $batchId])
                ->all();

            $jobClaimIds = ImportJob::query()
                ->whereIn('batch_verifications_id', $batchIds)
                ->whereNotNull('file_claim_id')
                ->pluck('file_claim_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $claims = PaiImportFileClaim::query()
                ->where(function ($query) use ($batchIds, $jobClaimIds) {
                    $query->whereIn('batch_verifications_id', $batchIds);

                    if ($jobClaimIds !== []) {
                        $query->orWhereIn('id', $jobClaimIds);
                    }

                    $query->orWhereIn('current_import_job_id', function ($sub) use ($batchIds) {
                        $sub->select('id')
                            ->from('import_jobs')
                            ->whereIn('batch_verifications_id', $batchIds);
                    });
                })
                ->whereNull('released_at')
                ->lockForUpdate()
                ->get();

            foreach ($claims as $claim) {
                $previousBatchId = (int) ($claim->batch_verifications_id ?? 0);
                if ($previousBatchId <= 0) {
                    $previousBatchId = (int) ($jobClaimBatchById[(int) $claim->id] ?? 0);
                }

                $archivedFileSha256 = $this->archivedHashValue('released-file', (string) $claim->file_sha256, (int) $claim->id);
                $archivedContentSha256 = $claim->content_sha256
                    ? $this->archivedHashValue('released-content', (string) $claim->content_sha256, (int) $claim->id)
                    : null;

                $updated = PaiImportFileClaim::query()
                    ->where('id', (int) $claim->id)
                    ->whereNull('released_at')
                    ->update([
                        'file_sha256' => $archivedFileSha256,
                        'released_file_sha256' => $claim->file_sha256,
                        'content_sha256' => $archivedContentSha256,
                        'released_content_sha256' => $claim->content_sha256,
                        'status' => 'released',
                        'released_at' => DB::raw('GETDATE()'),
                        'released_by_user_id' => $releasedByUserId > 0 ? $releasedByUserId : null,
                        'release_reason' => mb_substr(trim($reason), 0, 255),
                        'released_from_batch_verifications_id' => $previousBatchId > 0 ? $previousBatchId : null,
                        'current_import_job_id' => null,
                        'updated_at' => DB::raw('GETDATE()'),
                    ]);

                $released += (int) $updated;
            }
        }, 3);

        return $released;
    }

    public function fingerprint(string $filePath): array
    {
        $sha256 = hash_file('sha256', $filePath);
        $fileSize = filesize($filePath);

        if ($sha256 === false || $fileSize === false) {
            throw new RuntimeException('No fue posible calcular la huella SHA-256 del archivo.');
        }

        return [
            'sha256' => $sha256,
            'size' => (int) $fileSize,
            ...$this->semanticFingerprint($filePath),
        ];
    }

    public function semanticFingerprint(string $filePath): array
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = max(1, (int) $sheet->getHighestDataRow());
            $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
            $highestColumn = max(1, min($highestColumn, 256));

            $context = hash_init('sha256');
            hash_update($context, "pai-excel-semantic-v1\n");
            hash_update($context, $highestRow . '|' . $highestColumn . "\n");

            for ($row = 1; $row <= $highestRow; $row++) {
                $values = [];
                for ($column = 1; $column <= $highestColumn; $column++) {
                    $cellAddress = Coordinate::stringFromColumnIndex($column) . $row;
                    $values[] = $this->normalizeSemanticCell(
                        $sheet->getCell($cellAddress)->getValue()
                    );
                }

                while ($values !== [] && end($values) === null) {
                    array_pop($values);
                }

                hash_update($context, json_encode($values, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION) . "\n");
            }

            $spreadsheet->disconnectWorksheets();

            return [
                'content_sha256' => hash_final($context),
                'content_rows' => $highestRow,
                'content_columns' => $highestColumn,
            ];
        } catch (\Throwable $e) {
            return [
                'content_sha256' => null,
                'content_rows' => null,
                'content_columns' => null,
            ];
        }
    }

    private function semanticColumnsReady(): bool
    {
        return Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_sha256')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_rows')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_columns')
            && Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_sha256')
            && Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_rows')
            && Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_columns');
    }

    private function releaseColumnsReady(): bool
    {
        return Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_at')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_file_sha256')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_content_sha256')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_by_user_id')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'release_reason')
            && Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_from_batch_verifications_id');
    }

    private function createClaimRecord(array $claimPayload, string $formatVersion, string $sha256, ?string $contentSha256): PaiImportFileClaim
    {
        try {
            return PaiImportFileClaim::create($claimPayload);
        } catch (QueryException $e) {
            if (! $this->isDuplicateClaimException($e) || ! $this->releaseColumnsReady()) {
                throw $e;
            }

            $recovered = $this->archiveReleasedCollidingClaims($formatVersion, $sha256, $contentSha256);
            if ($recovered === 0) {
                throw $e;
            }

            return PaiImportFileClaim::create($claimPayload);
        }
    }

    private function archiveReleasedCollidingClaims(string $formatVersion, string $sha256, ?string $contentSha256): int
    {
        $claims = PaiImportFileClaim::query()
            ->where('format_version', $formatVersion)
            ->whereNotNull('released_at')
            ->where(function ($query) use ($sha256, $contentSha256) {
                $query->where('file_sha256', $sha256);

                if ($contentSha256 !== null) {
                    $query->orWhere('content_sha256', $contentSha256);
                }
            })
            ->lockForUpdate()
            ->get();

        $updated = 0;

        foreach ($claims as $claim) {
            $originalFileSha256 = $claim->released_file_sha256 ?: $claim->file_sha256;
            $originalContentSha256 = $claim->released_content_sha256 ?: $claim->content_sha256;

            $row = PaiImportFileClaim::query()
                ->where('id', (int) $claim->id)
                ->update([
                    'file_sha256' => $this->archivedHashValue('released-file', (string) $originalFileSha256, (int) $claim->id),
                    'released_file_sha256' => $originalFileSha256,
                    'content_sha256' => $originalContentSha256
                        ? $this->archivedHashValue('released-content', (string) $originalContentSha256, (int) $claim->id)
                        : null,
                    'released_content_sha256' => $originalContentSha256,
                    'updated_at' => DB::raw('GETDATE()'),
                ]);

            $updated += (int) $row;
        }

        return $updated;
    }

    private function archivedHashValue(string $prefix, string $originalHash, int $claimId): string
    {
        return hash('sha256', $prefix . '|' . $claimId . '|' . $originalHash);
    }

    private function isDuplicateClaimException(QueryException $e): bool
    {
        $message = $e->getMessage();

        return stripos($message, 'pai_import_file_claims') !== false
            && stripos($message, 'duplicate') !== false;
    }

    private function createJob(
        PaiImportFileClaim $claim,
        int $userId,
        ?string $requestedToken,
        string $sha256,
        ?string $contentSha256,
        int $fileSize,
        ?int $contentRows,
        ?int $contentColumns,
        string $originalName,
        string $formatVersion
    ): ImportJob {
        $token = trim((string) $requestedToken);
        if ($token === '' || ImportJob::query()->where('token', $token)->exists()) {
            $token = (string) Str::uuid();
        }

        $payload = [
            'user_id' => $userId,
            'token' => $token,
            'status' => 'queued',
            'percent' => 0,
            'step' => 'cola',
            'message' => 'En cola...',
            'errors' => null,
            'errors_count' => 0,
            'report_path' => null,
            'batch_verifications_id' => null,
            'file_claim_id' => $claim->id,
            'file_sha256' => $sha256,
            'file_size' => $fileSize,
            'original_name' => $originalName,
            'format_version' => $formatVersion,
            'retry_count' => 0,
            'retryable' => false,
        ];

        if ($contentSha256 !== null) {
            $payload['content_sha256'] = $contentSha256;
            $payload['content_rows'] = $contentRows;
            $payload['content_columns'] = $contentColumns;
        }

        return ImportJob::create($payload);
    }

    private function acquireFingerprintLock(
        ConnectionInterface $db,
        string $sha256,
        string $formatVersion
    ): void {
        $resource = 'pai-import:' . hash('sha256', $formatVersion . ':' . $sha256);
        $row = $db->selectOne(
            "DECLARE @result int;
             EXEC @result = sp_getapplock
                 @Resource = ?,
                 @LockMode = 'Exclusive',
                 @LockOwner = 'Transaction',
                 @LockTimeout = 15000;
             SELECT @result AS result;",
            [$resource]
        );

        if ((int) ($row->result ?? -999) < 0) {
            throw new RuntimeException('No fue posible reclamar de forma atomica la huella del archivo.');
        }
    }

    private function result(
        string $outcome,
        ?PaiImportFileClaim $claim,
        ?ImportJob $job,
        string $sha256,
        int $fileSize,
        bool $dispatch
    ): array {
        return [
            'outcome' => $outcome,
            'dispatch' => $dispatch,
            'claim_id' => $claim?->id,
            'job_id' => $job?->id,
            'token' => $job?->token,
            'status' => $job?->status,
            'batch_verifications_id' => $job?->batch_verifications_id ?? $claim?->batch_verifications_id,
            'processed_at' => $this->processedAt($job),
            'retry_count' => $job?->retry_count ?? $claim?->retry_count ?? 0,
            'file_sha256' => $sha256,
            'content_sha256' => $job?->content_sha256 ?? $claim?->content_sha256,
            'file_size' => $fileSize,
            'content_rows' => $job?->content_rows ?? $claim?->content_rows,
            'content_columns' => $job?->content_columns ?? $claim?->content_columns,
        ];
    }

    private function normalizeSemanticCell($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    private function processedAt(?ImportJob $job): ?string
    {
        if (! $job || (string) $job->status !== 'done') {
            return null;
        }

        $date = $job->updated_at ?? $job->created_at ?? null;

        return $date ? (string) $date : null;
    }

    private function cleanup(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
