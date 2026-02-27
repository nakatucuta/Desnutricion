<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GesTipo1ImportResumenMail extends Mailable
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

        return $this->subject("{$subjectPrefix} Gestantes Tipo 1 (Lote #{$this->batchId})")
            ->view('mail.ges_tipo1_import_resumen');
    }
}
