<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VacunasCargadas extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $usuario;

    public function __construct($filePath, $usuario)
    {
        $this->filePath = $filePath;
        $this->usuario = $usuario;
    }

    public function build()
    {
        $email = $this->view('mail.vacunas_cargadas')
            ->subject('Vacunas Cargadas')
            ->with([
                'usuario' => $this->usuario, // Pasar el nombre del usuario a la vista
            ]);

        if (file_exists($this->filePath)) {
            $email->attach($this->filePath, [
                'as' => 'vacunas_cargadas.txt',
                'mime' => 'text/plain',
            ]);
        } else {
            Log::error("Archivo no encontrado para adjuntar: {$this->filePath}");
        }

        return $email;
    }
}
