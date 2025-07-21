<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DataImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $records;

    /**
     * @param array|\Illuminate\Database\Eloquent\Collection $records
     */
    public function __construct($records)
    {
        $this->records = collect($records);
    }

 public function build()
{
    // Ruta absoluta al logo
    $logoPath = public_path('vendor/adminlte/dist/img/logo.png');

    // Adjuntamos el logo como inline con el nombre “escudo.png”
    $this->attach($logoPath, [
        'as'     => 'escudo.png',
        'mime'   => 'image/png',
        'inline' => true,
    ]);

    return $this
        ->subject('Importación Tipo 3 completada')
        ->markdown('emails.data_imported');
}
}
