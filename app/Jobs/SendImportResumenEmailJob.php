<?php

namespace App\Jobs;

use App\Mail\ImportResumenMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendImportResumenEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(
        public int $userId,
        public int $batchId,
        public array $stats = [],
        public array $errores = [],
        public array $noAfiliados = [],
        public array $cc = ['jsuarez@epsianaswayuu.com', 'pai@epsianaswayuu.com'],
    ) {}

    public function handle(): void
    {
       $user = User::find($this->userId);

            if ($user && !empty($user->email)) {
                Mail::to($user->email) // ✅ al que cargó
                    ->cc(['jsuarez@epsianaswayuu.com','pai@epsianaswayuu.com']) // ✅ copias
                    ->queue(
                        (new VacunasCargadasResumen($batchId, $insertedAfil, $insertedVac, $oldAfil, $oldVacuna))
                            ->onQueue('imports')  // ✅ para que lo procese tu worker actual
                    );
            } else {
                Log::warning("IMPORT MAIL: usuario sin email, userId={$this->userId}");
            }
        // ✅ mandar solo una muestra para no hacer el correo gigante
        $errores = is_array($this->errores) ? array_slice($this->errores, 0, 200) : [];
        $noAf    = is_array($this->noAfiliados) ? array_slice($this->noAfiliados, 0, 200) : [];

        Mail::to($user->email)
            ->cc($this->cc)
            ->queue(new ImportResumenMail(
                $user->name ?? 'SIN_NOMBRE',
                $this->batchId,
                $this->stats,
                $errores,
                $noAf
            ));

        Log::info("MAIL IMPORT: encolado OK", [
            'to' => $user->email,
            'batch' => $this->batchId,
        ]);
    }
}
