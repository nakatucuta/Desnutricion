<?php

namespace App\Jobs;

use App\Imports\AfiliadoImportStreaming;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportAfiliadosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $jobId;
    public string $fullPath;
    public int $userId;

    public $timeout = 3600;
    public $tries = 1;

    public function __construct(int $jobId, string $fullPath, int $userId)
    {
        $this->jobId = $jobId;
        $this->fullPath = $fullPath;
        $this->userId = $userId;
    }

    private function update(int $percent, string $step, string $message, string $status = 'running', array $errors = []): void
    {
        $row = ImportJob::find($this->jobId);
        if (!$row) return;

        $row->status = $status;
        $row->percent = max(0, min(100, $percent));
        $row->step = $step;
        $row->message = $message;

        if (!empty($errors)) {
            $row->errors = $errors;
            $row->errors_count = count($errors);
        }

        $row->save();
    }

    public function handle(): void
    {
        $this->update(5, 'inicio', 'Iniciando importación…', 'running');

        try {
            $this->update(10, 'lectura', 'Leyendo Excel…', 'running');

            $import = new AfiliadoImportStreaming($this->userId, null);

            Excel::import($import, $this->fullPath);

            $errores = $import->getErrores();
            if (!empty($errores)) {
                $this->update(100, 'final', 'Se encontraron errores de validación.', 'failed', $errores);
                return;
            }

            $stats = $import->getStats();
            $msg = "Importación finalizada";

            $this->update(100, 'final', $msg, 'done');

        } catch (\Throwable $e) {
            Log::error("ImportAfiliadosJob FAIL jobId={$this->jobId}: ".$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->update(100, 'final', 'Error interno durante la importación. Revisa el log.', 'failed', [
                $e->getMessage()
            ]);
        } finally {
            try { if (is_file($this->fullPath)) @unlink($this->fullPath); } catch (\Throwable $e) {}
        }
    }
}
