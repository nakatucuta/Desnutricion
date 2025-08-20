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
    protected $description = 'Envía correos por hitos vencidos en seguimientos (basado en created_at) y registra cada alerta enviada en una tabla aparte.';

    public function handle()
    {
        $now = Carbon::now();
        $totalEnviados = 0;

        SeguimientMaestrosiv549::with(['asignacion.user'])
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($now, &$totalEnviados) {
                foreach ($chunk as $s) {
                    $a = $s->asignacion;
                    if (!$a) {
                        continue;
                    }
                    $to = optional($a->user)->email;
                    if (!$to) {
                        continue; // sin correo destino
                    }

                    // Base: SIEMPRE created_at del seguimiento
                    $base = $s->created_at ? Carbon::parse($s->created_at) : $now;

                    // Orden de hitos a evaluar
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

                    // Cargar alertas ya enviadas de este seguimiento (para no duplicar)
                    $enviadas = $s->alertasEnviadas()->pluck('hito')->all();
                    $enviadas = array_flip($enviadas); // acceso O(1)

                    // Enviar solo la PRIMERA alerta vencida pendiente en esta pasada
                    foreach ($milestones as $key => $m) {
                        if ($m['done']) continue;               // hito ya cumplido
                        if ($now->lte($m['due'])) continue;      // aún no vence
                        if (isset($enviadas[$key])) continue;    // ya se envió antes

                        // URL para editar
                        $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);

                        // Enviar correo
                        $mail = new HitoVencidoMail(
                            $s,
                            $m['label'],
                            $m['due'],
                            $m['due']->diffInDays($now),
                            $editUrl
                        );

                        $mailer = Mail::to($to);
                        $cc = env('SEGUIMIENTOS_ALERT_CC');
                        if (!empty($cc)) {
                            $mailer->cc($cc);
                        }
                        $mailer->send($mail);

                        // Registrar alerta enviada
                        SeguimientoAlerta::create([
                            'seguimiento_id' => $s->id,
                            'hito'           => $key,
                            'sent_at'        => Carbon::now(),
                        ]);

                        $totalEnviados++;
                        break; // solo una alerta por pasada
                    }
                }
            });

        $this->info("Alertas enviadas: {$totalEnviados}");
        return Command::SUCCESS;
    }
}
