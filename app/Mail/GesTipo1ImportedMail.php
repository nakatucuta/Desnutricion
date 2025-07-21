<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GesTipo1ImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var Collection */
    public $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function build()
    {
        return $this
            ->subject('Importación Gestantes (Tipo 1) completada')
            ->markdown('emails.gestipo1_imported');
    }
}
