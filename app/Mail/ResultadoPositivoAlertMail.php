<?php

namespace App\Mail;

use App\Models\GestanteAlerta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ResultadoPositivoAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public GestanteAlerta $alert;
    public string $pacienteNombre;
    public string $pacienteDocumento;
    public ?string $pdfUrl;

    public function __construct(GestanteAlerta $alert)
    {
        // ✅ cargamos la gestante para tener nombre/documento
        $this->alert = $alert->loadMissing('gestante');

        $g = $this->alert->gestante;

        $this->pacienteNombre = $g
            ? trim("{$g->primer_nombre} {$g->segundo_nombre} {$g->primer_apellido} {$g->segundo_apellido}")
            : 'N/D';

        $tipoDoc = $g->tipo_de_identificacion_de_la_usuaria ?? '';
        $numDoc  = $g->no_id_del_usuario ?? '';

        $this->pacienteDocumento = trim($tipoDoc . ' ' . $numDoc) ?: 'N/D';

        // ✅ url pública del pdf si existe
        $this->pdfUrl = null;
        if (!empty($this->alert->pdf_path)) {
            if (Str::startsWith($this->alert->pdf_path, ['http://','https://'])) {
                $this->pdfUrl = $this->alert->pdf_path;
            } else {
                $this->pdfUrl = asset('storage/' . ltrim($this->alert->pdf_path, '/'));
            }
        }
    }

    public function build()
    {
        $subject = "⚠️ ALERTA: {$this->alert->examen} ({$this->alert->resultado}) - {$this->pacienteDocumento}";

        return $this->subject($subject)
            ->view('emails.alerta_resultado_positivo')

            ->with([
                'alert' => $this->alert,
                'pacienteNombre' => $this->pacienteNombre,
                'pacienteDocumento' => $this->pacienteDocumento,
                'pdfUrl' => $this->pdfUrl,
            ]);
    }
}
