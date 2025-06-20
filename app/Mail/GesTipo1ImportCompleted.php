<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GesTipo1ImportCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $imported;      // número total de registros importados
    public $details;       // array con datos cargados o resumen

    /**
     * @param \App\Models\User $user      El usuario que ejecutó el import
     * @param int              $imported  Cantidad de filas importadas
     * @param array            $details   Breakdown por grupos o IDs
     */
    public function __construct($user, int $imported, array $details)
    {
        $this->user     = $user;
        $this->imported = $imported;
        $this->details  = $details;
    }

    public function build()
    {
        return $this
            ->subject("Importación de Gestantes completada: {$this->imported} registros")
            ->markdown('emails.ges_tipo1_import_summary');
    }
}
