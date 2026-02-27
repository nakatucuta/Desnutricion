<?php

namespace App\Jobs;

use App\Imports\GesTipo1Import;
use App\Mail\GesTipo1ImportResumenMail;
use App\Models\GesTipo1;
use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ImportGesTipo1ExcelJob implements ShouldQueue
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
            Log::error("ImportGesTipo1ExcelJob: no existe import_jobs id={$this->importJobId}");
            return;
        }

        if (in_array((string) $jobRow->status, ['done', 'failed'], true)) {
            return;
        }

        $batchId = 0;

        try {
            $this->updateJob($jobRow, 'running', 5, 'inicio', 'Iniciando importacion gestantes tipo 1...');

            if (!is_file($this->fullPath)) {
                throw new \RuntimeException("No existe el archivo temporal: {$this->fullPath}");
            }

            $batchId = (int) DB::table('batch_verifications')->insertGetId([
                'fecha_cargue' => DB::raw('CONVERT(date, GETDATE())'),
                'created_at' => DB::raw('GETDATE()'),
                'updated_at' => DB::raw('GETDATE()'),
            ]);

            $jobRow->batch_verifications_id = $batchId;
            $jobRow->save();

            $this->updateJob($jobRow, 'running', 15, 'lectura', 'Leyendo y validando estructura del archivo...');

            $t0 = microtime(true);
            $import = new GesTipo1Import($this->userId, $batchId);

            $this->updateJob($jobRow, 'running', 25, 'procesando', 'Procesando filas del archivo...');

            Excel::import($import, $this->fullPath);
            $import->finalize();

            $seconds = round(microtime(true) - $t0, 2);
            $counters = $import->getCounters();
            $allErrors = (array) $import->getErrores();

            if (!empty($allErrors)) {
                $visibleErrors = $this->prepareErrorsForUI($allErrors, 350);
                $this->rollbackBatchSafe($batchId);

                $msg = sprintf(
                    'Importacion rechazada por validacion. Errores: %d | Filas invalidas: %d | Filas omitidas: %d',
                    count($allErrors),
                    (int) ($counters['rows_invalid'] ?? 0),
                    (int) ($counters['rows_skipped'] ?? 0)
                );

                $this->updateJob($jobRow, 'failed', 100, 'validacion', $msg, $visibleErrors);

                $this->sendImportEmailSafe(
                    ok: false,
                    batchId: $batchId,
                    counters: array_merge($counters, ['duration_seconds' => $seconds]),
                    errors: $allErrors,
                    resumen: $msg
                );

                return;
            }

            $msg = sprintf(
                'Importacion finalizada. Lote #%d | Creados: %d | Duplicados detectados: %d | Omitidos: %d',
                $batchId,
                (int) ($counters['rows_created'] ?? 0),
                (int) ($counters['rows_duplicated'] ?? 0),
                (int) ($counters['rows_skipped'] ?? 0)
            );

            $this->updateJob($jobRow, 'done', 100, 'final', $msg);

            $this->sendImportEmailSafe(
                ok: true,
                batchId: $batchId,
                counters: array_merge($counters, ['duration_seconds' => $seconds]),
                errors: [],
                resumen: $msg
            );
        } catch (\Throwable $e) {
            Log::error('ImportGesTipo1ExcelJob ERROR: ' . $e->getMessage(), [
                'importJobId' => $this->importJobId,
                'userId' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($batchId > 0) {
                $this->rollbackBatchSafe($batchId);
            }

            $errorList = [(string) $e->getMessage()];
            $visibleErrors = $this->prepareErrorsForUI($errorList, 350);

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
                batchId: $batchId,
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

    private function rollbackBatchSafe(int $batchId): void
    {
        if ($batchId <= 0) {
            return;
        }

        try {
            GesTipo1::where('batch_verifications_id', $batchId)->delete();
            DB::table('batch_verifications')->where('id', $batchId)->delete();
        } catch (\Throwable $cleanupError) {
            Log::error('ImportGesTipo1ExcelJob cleanup ERROR: ' . $cleanupError->getMessage(), [
                'batchId' => $batchId,
            ]);
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
                Log::warning('GesTipo1 mail: no hay destinatarios validos', [
                    'userId' => $this->userId,
                    'batchId' => $batchId,
                ]);
                return;
            }

            $mail = new GesTipo1ImportResumenMail(
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
            Log::error('GesTipo1 mail ERROR: ' . $mailError->getMessage(), [
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
