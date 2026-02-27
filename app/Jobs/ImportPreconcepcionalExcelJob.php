<?php

namespace App\Jobs;

use App\Imports\PreconcepcionalImport;
use App\Mail\PreconcepcionalImportResumenMail;
use App\Models\ImportJob;
use App\Models\Preconcepcional;
use App\Models\PreconcepcionalImportBatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ImportPreconcepcionalExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        public int $importJobId,
        public string $fullPath,
        public int $userId,
        public string $token,
        public string $originalFilename
    ) {
    }

    public function handle(): void
    {
        $jobRow = ImportJob::find($this->importJobId);

        if (!$jobRow) {
            Log::error("ImportPreconcepcionalExcelJob: no existe import_jobs id={$this->importJobId}");
            return;
        }

        if (in_array((string) $jobRow->status, ['done', 'failed'], true)) {
            return;
        }

        $batch = null;

        try {
            $this->updateJob($jobRow, 'running', 5, 'inicio', 'Iniciando importacion preconcepcional...');

            if (!is_file($this->fullPath)) {
                throw new \RuntimeException("No existe el archivo temporal: {$this->fullPath}");
            }

            $hash = hash_file('sha256', $this->fullPath);
            $size = filesize($this->fullPath) ?: 0;
            $now = now()->format('Ymd H:i:s');

            $batch = PreconcepcionalImportBatch::withoutTimestamps(function () use ($hash, $size, $now) {
                return PreconcepcionalImportBatch::create([
                    'original_name' => $this->originalFilename,
                    'user_id' => $this->userId,
                    'file_hash' => $hash,
                    'file_size' => $size,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

            $jobRow->batch_verifications_id = (int) $batch->id;
            $jobRow->save();

            $this->updateJob($jobRow, 'running', 15, 'lectura', 'Leyendo y validando estructura del archivo...');

            $t0 = microtime(true);
            $import = new PreconcepcionalImport((int) $batch->id);

            $this->updateJob($jobRow, 'running', 25, 'procesando', 'Procesando filas del archivo...');

            Excel::import($import, $this->fullPath);

            $seconds = round(microtime(true) - $t0, 2);
            $counters = $import->getCounters();
            $allErrors = method_exists($import, 'getErrores') ? (array) $import->getErrores() : [];
            $rowsInvalid = (int) ($counters['rows_invalid'] ?? 0);

            if (!empty($allErrors)) {
                $visibleErrors = $this->prepareErrorsForUI($allErrors, 350);
                $rowsSkippedTotal = (int) ($counters['rows_skipped'] ?? 0) + $rowsInvalid;

                $this->cleanupBatch($batch);

                $msg = sprintf(
                    'Importacion rechazada por validacion. Errores: %d | Filas invalidas: %d | Filas omitidas: %d',
                    count($allErrors),
                    $rowsInvalid,
                    $rowsSkippedTotal
                );

                $this->updateJob($jobRow, 'failed', 100, 'validacion', $msg, $visibleErrors);
            $this->sendImportEmailSafe(
                ok: false,
                batchId: (int) ($batch->id ?? 0),
                counters: array_merge($counters, [
                    'rows_skipped_total' => $rowsSkippedTotal,
                    'duration_seconds' => $seconds,
                ]),
                errors: $allErrors,
                resumen: $msg
            );
            return;
        }

            $rowsSkippedTotal = (int) ($counters['rows_skipped'] ?? 0) + $rowsInvalid;

            PreconcepcionalImportBatch::withoutTimestamps(function () use ($batch, $counters, $seconds, $now) {
                $batch->update([
                    'rows_total' => (int) ($counters['rows_total'] ?? 0),
                    'rows_created' => (int) ($counters['rows_created'] ?? 0),
                    'rows_updated' => 0,
                    'rows_skipped' => (int) (($counters['rows_skipped'] ?? 0) + ($counters['rows_invalid'] ?? 0)),
                    'rows_duplicate' => (int) ($counters['rows_duplicate'] ?? 0),
                    'duration_seconds' => $seconds,
                    'updated_at' => $now,
                ]);
            });

            $msg = sprintf(
                'Importacion finalizada. Lote #%d | Creados: %d | Duplicados detectados: %d | Omitidos: %d',
                (int) $batch->id,
                (int) ($counters['rows_created'] ?? 0),
                (int) ($counters['rows_duplicate'] ?? 0),
                $rowsSkippedTotal
            );

            $this->updateJob($jobRow, 'done', 100, 'final', $msg);
            $this->sendImportEmailSafe(
                ok: true,
                batchId: (int) ($batch->id ?? 0),
                counters: array_merge($counters, [
                    'rows_skipped_total' => $rowsSkippedTotal,
                    'duration_seconds' => $seconds,
                ]),
                errors: [],
                resumen: $msg
            );
        } catch (\Throwable $e) {
            Log::error('ImportPreconcepcionalExcelJob ERROR: ' . $e->getMessage(), [
                'importJobId' => $this->importJobId,
                'userId' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            $errorList = [(string) $e->getMessage()];
            $visibleErrors = $this->prepareErrorsForUI($errorList, 350);

            if ($batch) {
                $this->cleanupBatch($batch);
            }

            $this->updateJob(
                $jobRow,
                'failed',
                100,
                'error',
                'La importacion fallo: ' . mb_substr((string) $e->getMessage(), 0, 400),
                $visibleErrors
            );
            $this->sendImportEmailSafe(
                ok: false,
                batchId: (int) ($batch->id ?? 0),
                counters: [],
                errors: $errorList,
                resumen: 'La importacion fallo: ' . mb_substr((string) $e->getMessage(), 0, 400)
            );

            throw $e;
        } finally {
            try {
                @unlink($this->fullPath);
            } catch (\Throwable $e) {
            }
        }
    }

    private function sendImportEmailSafe(
        bool $ok,
        int $batchId,
        array $counters,
        array $errors,
        string $resumen
    ): void {
        try {
            $user = User::find($this->userId);
            $userEmail = $user && !empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)
                ? strtolower(trim((string) $user->email))
                : null;

            $fixedRecipients = [
                'jsuarez@epsianaswayuu.com',
                'rutamp@epsianaswayuu.com',
            ];

            $fixedRecipients = array_values(array_unique(array_map(function ($email) {
                return strtolower(trim((string) $email));
            }, $fixedRecipients)));

            if ($userEmail !== null) {
                $to = $userEmail;
                $cc = array_values(array_filter($fixedRecipients, fn($email) => $email !== $to));
            } else {
                $to = $fixedRecipients[0] ?? null;
                $cc = array_slice($fixedRecipients, 1);
            }

            if (!$to) {
                Log::warning('Preconcepcional mail: no hay destinatarios validos', [
                    'userId' => $this->userId,
                    'batchId' => $batchId,
                ]);
                return;
            }

            $mail = new PreconcepcionalImportResumenMail(
                usuario: (string) ($user->name ?? 'Usuario'),
                estado: $ok ? 'EXITO' : 'FALLIDO',
                batchId: $batchId,
                originalFilename: $this->originalFilename,
                counters: $counters,
                resumen: $resumen,
                errors: array_slice($errors, 0, 120)
            );

            $send = Mail::to($to);
            if (!empty($cc)) {
                $send->cc($cc);
            }

            $send->send($mail);
        } catch (\Throwable $mailError) {
            Log::error('Preconcepcional mail ERROR: ' . $mailError->getMessage(), [
                'userId' => $this->userId,
                'batchId' => $batchId,
            ]);
        }
    }

    private function prepareErrorsForUI(array $errors, int $max = 350): array
    {
        $errors = array_values(array_filter(array_map(function ($e) {
            $s = trim((string) $e);
            return $s === '' ? null : $s;
        }, $errors)));

        $total = count($errors);
        if ($total > $max) {
            $errors = array_slice($errors, 0, $max);
            $errors[] = "Se detectaron {$total} errores. Solo se muestran los primeros {$max}.";
        }

        return $errors;
    }

    private function cleanupBatch(?PreconcepcionalImportBatch $batch): void
    {
        if (!$batch) {
            return;
        }

        try {
            Preconcepcional::where('created_batch_id', (int) $batch->id)->delete();
            $batch->delete();
        } catch (\Throwable $cleanupError) {
            Log::error('ImportPreconcepcionalExcelJob cleanup ERROR: ' . $cleanupError->getMessage(), [
                'batchId' => (int) $batch->id,
            ]);
        }
    }

    private function updateJob(
        ImportJob $jobRow,
        string $status,
        int $percent,
        string $step,
        string $message,
        ?array $errors = null
    ): void {
        $fresh = ImportJob::find($jobRow->id);
        if (!$fresh) {
            return;
        }

        if (in_array((string) $fresh->status, ['done', 'failed'], true)) {
            return;
        }

        $fresh->status = $status;
        $fresh->percent = max(0, min(100, $percent));
        $fresh->step = $step;
        $fresh->message = $message;

        if ($errors !== null) {
            $errors = array_values(array_filter(array_map(function ($e) {
                $s = trim((string) $e);
                return $s === '' ? null : mb_substr($s, 0, 500);
            }, $errors)));

            $totalErrors = count($errors);
            if ($totalErrors > 500) {
                $errors = array_slice($errors, 0, 500);
                $errors[] = "Se detectaron {$totalErrors} errores. Solo se muestran los primeros 500.";
            }

            $fresh->errors = !empty($errors) ? json_encode($errors, JSON_UNESCAPED_UNICODE) : null;
            $fresh->errors_count = $totalErrors;
        }

        $fresh->save();
    }
}
