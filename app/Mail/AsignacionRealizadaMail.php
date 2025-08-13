<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AsignacionRealizadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $asignacion, $usuarioAsignado, $usuarioAsignador;

    public function __construct($asignacion, $usuarioAsignado, $usuarioAsignador)
    {
        $this->asignacion = $asignacion;
        $this->usuarioAsignado = $usuarioAsignado;
        $this->usuarioAsignador = $usuarioAsignador;
    }

    public function build()
    {
        return $this->subject('Usted asignó un caso en MaestroSiv549')
            ->markdown('emails.asignacion_realizada');
    }
}
