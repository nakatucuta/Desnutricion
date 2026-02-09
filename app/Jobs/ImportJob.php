<?php

namespace App\Jobs;

use App\Imports\AfiliadoImportStreaming;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $token;
    public int $userId;
    public string $filePath;

    public $timeout = 3600; // 1 hora
    public $tries = 1;

    public function __construct(string $token, int $userId, string $filePath)
    {
        $this->token = $token;
        $this->userId = $userId;
        $this->filePath = $filePath;
    }

    private function progress(int $percent, string $message, string $step, string $status = 'running', array $errors = [])
    {
        Cache::put("import_progress:{$this->token}", [
            'status'  => $status,   // queued | running | done | failed
            'percent' => $percent,
            'message' => $message,
            'step'    => $step,
            'errors'  => $errors,
        ], now()->addMinutes(60));
    }

    public function handle(): void
    {
        $this->progress(5, 'Iniciando importación…', 'inicio', 'running');

        try {
            $import = new AfiliadoImportStreaming($this->userId, $this->token);

            $this->progress(10, 'Leyendo Excel…', 'lectura', 'running');

            Excel::import($import, $this->filePath);

            $errores = $import->getErrores();

            if (!empty($errores)) {
                $this->progress(100, 'Se encontraron errores de validación.', 'final', 'failed', $errores);
                return;
            }

            $stats = $import->getStats();

            $msg = "Importación finalizada. Afiliados nuevos: {$stats['newAfil']}. Vacunas nuevas: {$stats['newVacuna']}.";

            $this->progress(100, $msg, 'final', 'done', []);

        } catch (\Throwable $e) {
            Log::error("ImportJob FAIL token={$this->token}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->progress(100, 'Error interno durante la importación. Revisa el log.', 'final', 'failed', [
                $e->getMessage()
            ]);
        } finally {
            // Limpia el archivo temporal si existe
            try {
                if (is_file($this->filePath)) @unlink($this->filePath);
            } catch (\Throwable $e) {
                // silencioso
            }
        }
    }
}
