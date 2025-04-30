<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecordatorioControl extends Mailable
{
    use Queueable, SerializesModels;

    /** Datos básicos (ID, nombres, etc.) */
    public $datos;

    /** El HTML extra que construyes en el controlador */
    protected $bodyText;

    /**
     * @param array  $datos
     * @param string $bodyText
     */
    public function __construct(array $datos, string $bodyText)
    {
        $this->datos    = $datos;
        $this->bodyText = $bodyText;
    }

    /**
     * Construye el correo con tu mensaje personalizado.
     */
    public function build()
    {
        // URL de login
        $loginUrl = 'https://app.epsianaswayuu.com/rutasintegrales/';

        // Ensamblamos el HTML completo
        $html = 
            'FAVOR NO CONTESTAR ESTE MENSAJE<br>' .
            'Hola, te acaban de asignar un paciente de desnutrición (412) por parte de la EPSI Anas Wayuu.<br>' .
            'Por favor, gestionarlo lo antes posible ingresando al siguiente enlace:<br>' .
            "<a href=\"{$loginUrl}\">Ingresar al sistema</a><br><br>" .
            $this->bodyText;

        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Recordatorio de control')
            ->html($html);
    }
}
