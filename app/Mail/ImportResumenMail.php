<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportResumenMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $usuario,
        public int $batchId,
        public array $stats = [],
        public array $errores = [],
        public array $noAfiliados = [],
    ) {}

    public function build()
    {
        return $this->subject("✅ Importación PAI finalizada (Batch #{$this->batchId})")
            ->view('mail.import_resumen'); // creamos esta vista abajo
    }
}
