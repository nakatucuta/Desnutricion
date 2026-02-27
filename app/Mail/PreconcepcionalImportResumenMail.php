<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreconcepcionalImportResumenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $usuario,
        public string $estado,
        public int $batchId,
        public string $originalFilename,
        public array $counters = [],
        public string $resumen = '',
        public array $errors = []
    ) {
    }

    public function build()
    {
        $subjectPrefix = $this->estado === 'EXITO' ? 'Importacion completada' : 'Importacion con errores';
        return $this->subject("{$subjectPrefix} Preconcepcional (Lote #{$this->batchId})")
            ->view('mail.preconcepcional_import_resumen');
    }
}
