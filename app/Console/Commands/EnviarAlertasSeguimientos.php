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
    protected $description = 'Envia correos por hitos vencidos en seguimientos con control de una alerta por hito.';

    public function handle()
    {
        $now = Carbon::now();
        $totalEnviados = 0;

        SeguimientMaestrosiv549::with(['asignacion.user', 'alertasEnviadas'])
            ->select([
                'id',
                'asignacion_id',
                'created_at',
                'fecha_hospitalizacion',
                'fecha_egreso',
                'fecha_control_rn_inmediato',
                'descripcion_seguimiento_inmediato',
                'fecha_seguimiento_1',
                'fecha_seguimiento_2',
                'fecha_seguimiento_3',
                'fecha_seguimiento_4',
                'fecha_seguimiento_5',
                'fecha_consulta_6_meses',
                'fecha_consulta_1_ano',
            ])
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($now, &$totalEnviados) {
                foreach ($chunk as $seguimiento) {
                    $asignacion = $seguimiento->asignacion;
                    if (!$asignacion) {
                        continue;
                    }

                    $to = optional($asignacion->user)->email;
                    if (!$to) {
                        continue;
                    }

                    $base = $this->resolveAlertBase($seguimiento, $now);
                    $milestones = $this->buildMilestones($seguimiento, $base);
                    $enviadas = $seguimiento->alertasEnviadas->pluck('hito')->flip();

                    $firstUndoneKey = null;
                    foreach ($milestones as $key => $milestone) {
                        if (!$milestone['done']) {
                            $firstUndoneKey = $key;
                            break;
                        }
                    }

                    if (!$firstUndoneKey) {
                        continue;
                    }

                    $first = $milestones[$firstUndoneKey];

                    if ($now->lte($first['due'])) {
                        continue;
                    }

                    if (isset($enviadas[$firstUndoneKey])) {
                        continue;
                    }

                    $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$seguimiento->asignacion_id, $seguimiento->id]);

                    $mail = new HitoVencidoMail(
                        $seguimiento,
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

                    SeguimientoAlerta::create([
                        'seguimiento_id' => $seguimiento->id,
                        'hito' => $firstUndoneKey,
                        'sent_at' => Carbon::now(),
                    ]);

                    $totalEnviados++;
                }
            });

        $this->info("Alertas enviadas: {$totalEnviados}");

        return Command::SUCCESS;
    }

    private function resolveAlertBase(SeguimientMaestrosiv549 $seguimiento, Carbon $fallback): Carbon
    {
        if ($seguimiento->fecha_egreso instanceof Carbon) {
            return $seguimiento->fecha_egreso->copy()->startOfDay();
        }

        if ($seguimiento->fecha_hospitalizacion instanceof Carbon) {
            return $seguimiento->fecha_hospitalizacion->copy()->startOfDay();
        }

        if ($seguimiento->created_at instanceof Carbon) {
            return $seguimiento->created_at->copy();
        }

        return $fallback->copy();
    }

    private function buildMilestones(SeguimientMaestrosiv549 $seguimiento, Carbon $base): array
    {
        return [
            '48h72h' => [
                'label' => '48-72h',
                'due' => $base->copy()->addHours(72),
                'done' => !empty($seguimiento->descripcion_seguimiento_inmediato) || !empty($seguimiento->fecha_control_rn_inmediato),
            ],
            'post_egreso' => [
                'label' => 'Post egreso',
                'due' => $base->copy()->addDays(3),
                'done' => !empty($seguimiento->fecha_seguimiento_1),
            ],
            '7d' => [
                'label' => '7 dias',
                'due' => $base->copy()->addDays(7),
                'done' => !empty($seguimiento->fecha_seguimiento_2),
            ],
            '14d' => [
                'label' => '14 dias',
                'due' => $base->copy()->addDays(14),
                'done' => !empty($seguimiento->fecha_seguimiento_3),
            ],
            '21d' => [
                'label' => '21 dias',
                'due' => $base->copy()->addDays(21),
                'done' => !empty($seguimiento->fecha_seguimiento_4),
            ],
            '28d' => [
                'label' => '28 dias',
                'due' => $base->copy()->addDays(28),
                'done' => !empty($seguimiento->fecha_seguimiento_5),
            ],
            '6m' => [
                'label' => '6 meses',
                'due' => $base->copy()->addMonthsNoOverflow(6),
                'done' => !empty($seguimiento->fecha_consulta_6_meses),
            ],
            '1y' => [
                'label' => '1 ano',
                'due' => $base->copy()->addYearNoOverflow(1),
                'done' => !empty($seguimiento->fecha_consulta_1_ano),
            ],
        ];
    }
}
