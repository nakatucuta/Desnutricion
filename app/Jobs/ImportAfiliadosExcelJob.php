<?php

namespace App\Jobs;

use App\Imports\AfiliadoImportStreaming;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAfiliadosExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        public int $importJobId,
        public string $fullPath,
        public int $userId,
        public string $token
    ) {}

    public function handle(): void
    {
        $jobRow = ImportJob::find($this->importJobId);

        if (!$jobRow) {
            Log::error("ImportAfiliadosExcelJob: NO existe import_jobs id={$this->importJobId}");
            return;
        }

        // ✅ LOCK: evita doble ejecución en paralelo del mismo importJobId
        $lockKey = "import_lock:{$this->importJobId}";
        $lock = Cache::lock($lockKey, 3600);

        if (!$lock->get()) {
            Log::warning("ImportAfiliadosExcelJob: LOCK tomado, se evita duplicado. importJobId={$this->importJobId}");
            return;
        }

        $import = null;

        try {
            // ✅ si ya está finalizado, no hagas nada
            if (in_array($jobRow->status, ['done', 'failed'], true)) {
                return;
            }

            // ✅ Limpia errores SOLO al inicio
            $jobRow->errors = null;
            $jobRow->errors_count = 0;
            $jobRow->save();

            $this->updateJobSafe($jobRow, 'running', 3, 'inicio', 'Iniciando importación…');

            if (!is_file($this->fullPath)) {
                throw new \RuntimeException("No existe el archivo: {$this->fullPath}");
            }

            $this->updateJobSafe($jobRow, 'running', 5, 'lectura', 'Leyendo Excel (estimando filas)…');

            $totalRows = $this->estimateTotalRows($this->fullPath);
            $totalDataRows = max(1, $totalRows - 2); // startRow = 3

            $import = new AfiliadoImportStreaming(
                userId: $this->userId,
                uploadToken: $this->token,
                totalRows: $totalDataRows,
                progressFn: function (int $pct, string $step, string $msg) {
                    $fresh = ImportJob::find($this->importJobId);
                    if (!$fresh) return;

                    if (in_array($fresh->status, ['done', 'failed'], true)) return;

                    // ✅ evita spamear el mismo mensaje/porcentaje
                    if ((int)$fresh->percent === (int)$pct
                        && (string)$fresh->message === (string)$msg
                        && (string)$fresh->step === (string)$step) {
                        return;
                    }

                    $this->updateJobSafe($fresh, 'running', $pct, $step, $msg);
                }
            );

            // ✅ guardo batch_id en import_jobs
            $jobRow->batch_verifications_id = $import->getBatchVerificationsID();
            $jobRow->save();

            $this->updateJobSafe($jobRow, 'running', 8, 'procesando', 'Procesando filas…');

            /**
             * ✅ IMPORTANTE:
             * Para evitar el error "ROLLBACK trans2" en SQL Server,
             * DEBES tener en config/excel.php:  'transactions.handler' => null
             */
            Excel::import($import, $this->fullPath);

            // ✅ flush final (si tu import lo tiene)
            if ($import && method_exists($import, 'finalize')) {
                try { $import->finalize(); } catch (\Throwable $x) {}
            }

            // ✅ errores acumulados
            $errores = [];
            try { $errores = $import->getErrores(); } catch (\Throwable $x) { $errores = []; }

            if (!empty($errores)) {
                $this->rollbackBatchSafe((int)($jobRow->batch_verifications_id ?? 0));

                $this->updateJobSafe(
                    $jobRow,
                    'failed',
                    100,
                    'validacion',
                    'Importación con errores. No se guardó nada.',
                    $errores
                );
                return;
            }

            $batchId = (int)($jobRow->batch_verifications_id ?? 0);

            // ✅ Conteo REAL insertado (NO depende del import)
            [$insertedAfil, $insertedVac] = $this->countInsertedByBatch($batchId);

            // ✅ Stats extra (si están disponibles) solo como complemento
            $stats = [];
            try { $stats = $import->getStats(); } catch (\Throwable $e) { $stats = []; }

            $oldAfil   = (int)($stats['oldAfil'] ?? 0);
            $oldVacuna = (int)($stats['oldVacuna'] ?? 0);

            $msg = "Importación finalizada. "
                 . "Afiliados insertados: {$insertedAfil}. "
                 . "Vacunas insertadas: {$insertedVac}.";

            if ($oldAfil > 0 || $oldVacuna > 0) {
                $msg .= " Afiliados existentes: {$oldAfil}. Vacunas repetidas/omitidas: {$oldVacuna}.";
            }

            $this->updateJobSafe($jobRow, 'done', 100, 'final', $msg);

        } catch (\Throwable $e) {

            $jobRow = ImportJob::find($this->importJobId);
            if (!$jobRow) return;

            $errorsList = [];
            if ($import && method_exists($import, 'getErrores')) {
                try { $errorsList = $import->getErrores(); } catch (\Throwable $x) { $errorsList = []; }
            }

            if ($import && method_exists($import, 'finalize')) {
                try { $import->finalize(); } catch (\Throwable $x) {}
            }

            $mainMsg = $e->getMessage();
            array_unshift($errorsList, $mainMsg);

            $errorsList = array_values(array_unique(array_filter(array_map(function ($v) {
                $s = is_string($v) ? trim($v) : (string)$v;
                return $s === '' ? null : $s;
            }, $errorsList))));

            Log::error("ImportAfiliadosExcelJob ERROR: " . $mainMsg, [
                'importJobId' => $this->importJobId,
                'userId' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            if (!empty($jobRow->batch_verifications_id)) {
                $this->rollbackBatchSafe((int)$jobRow->batch_verifications_id);
            }

            $this->updateJobSafe($jobRow, 'failed', 100, 'error', 'Importación detenida por error.', $errorsList);

            // ✅ re-lanza para que la cola lo marque como failed correctamente
            throw $e;

        } finally {
            try { @unlink($this->fullPath); } catch (\Throwable $e) {}
            try { $lock?->release(); } catch (\Throwable $e) {}
        }
    }

    private function rollbackBatchSafe(int $batchId): void
    {
        if ($batchId <= 0) return;

        try {
            DB::table('vacunas')->where('batch_verifications_id', $batchId)->delete();
            DB::table('afiliados')->where('batch_verifications_id', $batchId)->delete();
        } catch (\Throwable $e) {
            Log::error("rollbackBatchSafe ERROR: " . $e->getMessage(), ['batchId' => $batchId]);
        }
    }

    private function countInsertedByBatch(int $batchId): array
    {
        if ($batchId <= 0) return [0, 0];

        try {
            $af = (int) DB::table('afiliados')->where('batch_verifications_id', $batchId)->count();
            $va = (int) DB::table('vacunas')->where('batch_verifications_id', $batchId)->count();
            return [$af, $va];
        } catch (\Throwable $e) {
            Log::error("countInsertedByBatch ERROR: " . $e->getMessage(), ['batchId' => $batchId]);
            return [0, 0];
        }
    }

    private function estimateTotalRows(string $path): int
    {
        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            return (int) $sheet->getHighestRow();
        } catch (\Throwable $e) {
            return 1000;
        }
    }

    private function updateJobSafe(
        ImportJob $jobRow,
        string $status,
        int $percent,
        string $step,
        string $message,
        ?array $errors = null
    ): void {
        $fresh = ImportJob::find($jobRow->id);
        if (!$fresh) return;

        if (in_array($fresh->status, ['done', 'failed'], true)) {
            return;
        }

        $fresh->status = $status;
        $fresh->percent = max(0, min(100, (int)$percent));
        $fresh->step = $step;
        $fresh->message = $message;

        if ($errors !== null) {
            $errors = array_values(array_filter(array_map(function ($x) {
                $s = is_string($x) ? trim($x) : (string)$x;
                if ($s === '') return null;
                return mb_substr($s, 0, 500);
            }, $errors)));

            $errors = array_slice($errors, 0, 250);

            if (!empty($errors)) {
                $fresh->errors = json_encode($errors, JSON_UNESCAPED_UNICODE);
                $fresh->errors_count = count($errors);
            } else {
                $fresh->errors = null;
                $fresh->errors_count = 0;
            }
        }

        $fresh->save();
    }
}
