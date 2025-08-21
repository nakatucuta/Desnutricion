<?php

namespace App\Console\Commands;

use App\Mail\HitoVencidoMail;
use App\Models\SeguimientoAlerta;
use App\Models\SeguimientMaestrosiv549;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAlertasSeguimientos extends Command
{
    protected $signature = 'seguimientos:enviar-alertas';
    protected $description = 'Envía correos por hitos vencidos en seguimientos (basado en created_at) con anti-spam por hito.';

    public function handle()
    {
        $now = Carbon::now();
        $totalEnviados = 0;

        SeguimientMaestrosiv549::with(['asignacion.user', 'alertasEnviadas'])
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($now, &$totalEnviados) {

                foreach ($chunk as $s) {
                    $a = $s->asignacion;
                    if (!$a) {
                        continue;
                    }

                    $to = optional($a->user)->email;
                    if (!$to) {
                        continue; // sin destino
                    }

                    // Base: SIEMPRE created_at del seguimiento
                    $base = $s->created_at ? Carbon::parse($s->created_at) : $now;

                    // Orden canónico de hitos
                    $milestones = [
                        '48h72h' => [
                            'label' => '48–72h',
                            'due'   => (clone $base)->addHours(72),
                            'done'  => !empty($s->descripcion_seguimiento_inmediato),
                        ],
                        '7d' => [
                            'label' => '7 días',
                            'due'   => (clone $base)->addDays(7),
                            'done'  => !empty($s->fecha_seguimiento_2),
                        ],
                        '14d' => [
                            'label' => '14 días',
                            'due'   => (clone $base)->addDays(14),
                            'done'  => !empty($s->fecha_seguimiento_3),
                        ],
                        '21d' => [
                            'label' => '21 días',
                            'due'   => (clone $base)->addDays(21),
                            'done'  => !empty($s->fecha_seguimiento_4),
                        ],
                        '28d' => [
                            'label' => '28 días',
                            'due'   => (clone $base)->addDays(28),
                            'done'  => !empty($s->fecha_seguimiento_5),
                        ],
                        '6m' => [
                            'label' => '6 meses',
                            'due'   => (clone $base)->addMonthsNoOverflow(6),
                            'done'  => !empty($s->fecha_consulta_6_meses),
                        ],
                        '1y' => [
                            'label' => '1 año',
                            'due'   => (clone $base)->addYearNoOverflow(1),
                            'done'  => !empty($s->fecha_consulta_1_ano),
                        ],
                    ];

                    // Alertas ya enviadas (por clave de hito)
                    $enviadas = $s->alertasEnviadas->pluck('hito')->flip();

                    // 1) Encuentra el PRIMER hito NO cumplido (en orden)
                    $firstUndoneKey = null;
                    foreach ($milestones as $key => $m) {
                        if (!$m['done']) {
                            $firstUndoneKey = $key;
                            break;
                        }
                    }

                    // Si todo está cumplido, nada que hacer
                    if (!$firstUndoneKey) {
                        continue;
                    }

                    $first = $milestones[$firstUndoneKey];

                    // 2) Solo consideramos ese primer no-cumplido:
                    //    - Si aún no vence -> nada
                    //    - Si ya venció y NO se ha notificado -> enviar una sola vez
                    //    - Si ya venció y YA se notificó -> nada (espera a que lo cumplan)
                    if ($now->lte($first['due'])) {
                        continue;
                    }

                    if (isset($enviadas[$firstUndoneKey])) {
                        continue; // ya avisado este hito; no avanzamos a los siguientes
                    }

                    // Armar y enviar correo
                    $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);

                    $mail = new HitoVencidoMail(
                        $s,
                        $first['label'],
                        $first['due'],
                        $first['due']->diffInDays($now),
                        $editUrl
                    );

                    $mailer = Mail::to($to);
                    $cc = env('SEGUIMIENTOS_ALERT_CC');
                    if (!empty($cc)) {
                        $mailer->cc($cc);
                    }
                    $mailer->send($mail);

                    // Registrar que ESTE hito ya fue notificado
                    SeguimientoAlerta::create([
                        'seguimiento_id' => $s->id,
                        'hito'           => $firstUndoneKey,
                        'sent_at'        => Carbon::now(),
                    ]);

                    $totalEnviados++;
                    // Importante: NO avanzamos a los hitos siguientes.
                }

            });

        $this->info("Alertas enviadas: {$totalEnviados}");
        return Command::SUCCESS;
    }
}
