<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HitoVencidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $seguimiento;
    public $asignacion;
    public $hito;
    public $fechaLimite; // Carbon
    public $diasAtraso;
    public $editUrl;

    public function __construct($seguimiento, string $hito, $fechaLimite, int $diasAtraso, string $editUrl)
    {
        $this->seguimiento  = $seguimiento;
        $this->asignacion   = $seguimiento->asignacion;
        $this->hito         = $hito;
        $this->fechaLimite  = $fechaLimite;
        $this->diasAtraso   = $diasAtraso;
        $this->editUrl      = $editUrl;
    }

    public function build()
    {
        $paciente = $this->asignacion
            ? trim("{$this->asignacion->pri_nom_} {$this->asignacion->seg_nom_} {$this->asignacion->pri_ape_} {$this->asignacion->seg_ape_}")
            : 'Paciente';

        return $this->subject("Alerta: Hito vencido ({$this->hito}) - {$paciente}")
                    ->markdown('emails.hitos.vencido');
    }
}
