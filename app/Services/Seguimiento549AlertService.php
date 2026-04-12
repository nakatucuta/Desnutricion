<?php

namespace App\Services;

use App\Mail\HitoVencidoMail;
use App\Models\SeguimientoAlerta;
use App\Models\SeguimientMaestrosiv549;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class Seguimiento549AlertService
{
    public function evaluate(SeguimientMaestrosiv549 $seguimiento, ?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : Carbon::now();
        $asignacion = $seguimiento->asignacion;
        $base = $this->resolveAlertBase($seguimiento, $now);
        $milestones = $this->buildMilestones($seguimiento, $base);
        $risk = $this->buildRisk($seguimiento, $asignacion);
        $timeline = $this->buildTimeline($milestones, $now);

        return [
            'base' => $base,
            'milestones' => $milestones,
            'risk' => $risk,
            'timeline' => $timeline,
        ];
    }

    public function syncSuperImmediateAlert(SeguimientMaestrosiv549 $seguimiento, ?Carbon $now = null): void
    {
        $snapshot = $this->evaluate($seguimiento, $now);
        $timeline = $snapshot['timeline'];

        $alert = SeguimientoAlerta::firstOrNew([
            'seguimiento_id' => $seguimiento->id,
            'hito' => 'super_inm',
        ]);

        $alert->tipo = 'super_inmediata';
        $alert->nivel_riesgo = $snapshot['risk']['level'];
        $alert->prioridad = $snapshot['risk']['score'];
        $alert->due_at = $timeline['first_due'];
        $alert->estado = $timeline['status'];
        if ($timeline['status'] === 'red' && !$alert->sent_at) {
            $alert->sent_at = Carbon::now();
        }
        $alert->save();
    }

    public function dispatchOverdueAlerts(SeguimientMaestrosiv549 $seguimiento, ?Carbon $now = null): int
    {
        $now = $now ? $now->copy() : Carbon::now();
        $snapshot = $this->evaluate($seguimiento, $now);
        $timeline = $snapshot['timeline'];
        $risk = $snapshot['risk'];
        $asignacion = $seguimiento->asignacion;
        $to = optional($asignacion?->user)->email;

        if (!$asignacion || !$to || !$timeline['first_pending_key']) {
            return 0;
        }

        $sent = 0;
        $first = $timeline['first_pending'];
        $hitoKey = $timeline['first_pending_key'];

        if ($timeline['minutes_to_due'] < 0) {
            $alert = SeguimientoAlerta::firstOrNew([
                'seguimiento_id' => $seguimiento->id,
                'hito' => $hitoKey,
            ]);

            $alreadySent = !empty($alert->sent_at);

            $alert->tipo = 'atraso';
            $alert->nivel_riesgo = $risk['level'];
            $alert->prioridad = $risk['score'];
            $alert->due_at = $first['due'];
            $alert->estado = 'red';

            if (!$alreadySent) {
                $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$seguimiento->asignacion_id, $seguimiento->id]);
                $mail = new HitoVencidoMail(
                    $seguimiento,
                    $first['label'],
                    $first['due'],
                    abs($timeline['minutes_to_due']) >= 1440 ? intdiv(abs($timeline['minutes_to_due']), 1440) : 0,
                    $editUrl
                );

                $mailer = Mail::to($to);
                $cc = env('SEGUIMIENTOS_ALERT_CC');
                if (!empty($cc)) {
                    $mailer->cc($cc);
                }
                $mailer->send($mail);
                $alert->sent_at = Carbon::now();
                $sent++;
            }

            $alert->save();
        }

        $mustEscalate = in_array($risk['level'], ['critical', 'very_high'], true)
            && $timeline['minutes_to_due'] <= -240;

        if ($mustEscalate) {
            $escAlert = SeguimientoAlerta::firstOrNew([
                'seguimiento_id' => $seguimiento->id,
                'hito' => 'esc_'.$hitoKey,
            ]);

            if (empty($escAlert->sent_at)) {
                $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$seguimiento->asignacion_id, $seguimiento->id]);
                $mailer = Mail::to($to);
                $cc = env('SEGUIMIENTOS_ALERT_CC');
                if (!empty($cc)) {
                    $mailer->cc($cc);
                }
                $mailer->send(new HitoVencidoMail(
                    $seguimiento,
                    'Escalamiento '.$first['label'].' (riesgo '.$risk['label'].')',
                    $first['due'],
                    abs($timeline['minutes_to_due']) >= 1440 ? intdiv(abs($timeline['minutes_to_due']), 1440) : 0,
                    $editUrl
                ));
                $escAlert->sent_at = Carbon::now();
                $sent++;
            }

            $escAlert->tipo = 'escalamiento';
            $escAlert->nivel_riesgo = $risk['level'];
            $escAlert->prioridad = $risk['score'];
            $escAlert->due_at = $first['due'];
            $escAlert->estado = 'escalated';
            $escAlert->save();
        }

        return $sent;
    }

    public function resolveAlertBase(SeguimientMaestrosiv549 $seguimiento, Carbon $fallback): Carbon
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

    public function buildMilestones(SeguimientMaestrosiv549 $seguimiento, Carbon $base): array
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

    public function buildRisk(SeguimientMaestrosiv549 $seguimiento, $asignacion): array
    {
        $criticalCriteria = [
            'eclampsia',
            'preeclampsia_severa',
            'sepsis_infeccion_sistemica_severa',
            'hemorragia_obstetrica_severa',
            'ruptura_uterina',
            'falla_cardiovascular',
            'falla_renal',
            'falla_hepatica',
            'falla_cerebral',
            'falla_respiratoria',
            'falla_coagulacion',
        ];

        $criteriaCount = 0;
        foreach ($criticalCriteria as $field) {
            if ((int) ($seguimiento->{$field} ?? 0) === 1) {
                $criteriaCount++;
            }
        }

        $score = $criteriaCount * 12;
        $factors = [];

        if ((int) ($asignacion->ingres_uci ?? 0) === 1) {
            $score += 30;
            $factors[] = 'Ingreso UCI';
        }
        if ((int) ($asignacion->transfusio ?? 0) === 1) {
            $score += 20;
            $factors[] = 'Transfusion';
        }
        if ((int) ($seguimiento->cirugia_adicional ?? 0) === 1) {
            $score += 16;
            $factors[] = 'Cirugia adicional';
        }
        if ((int) ($asignacion->unds_trans ?? 0) >= 3) {
            $score += 15;
            $factors[] = '3+ unidades transfundidas';
        }

        if ($criteriaCount >= 3) {
            $score += 14;
            $factors[] = '3+ criterios clinicos';
        }

        $level = 'moderate';
        $label = 'Moderado';
        if ($score >= 80) {
            $level = 'critical';
            $label = 'Critico';
        } elseif ($score >= 55) {
            $level = 'very_high';
            $label = 'Muy alto';
        } elseif ($score >= 30) {
            $level = 'high';
            $label = 'Alto';
        } elseif ($score < 15) {
            $level = 'low';
            $label = 'Bajo';
        }

        return [
            'score' => $score,
            'level' => $level,
            'label' => $label,
            'criteria_count' => $criteriaCount,
            'factors' => $factors,
        ];
    }

    public function buildTimeline(array $milestones, Carbon $now): array
    {
        $firstPendingKey = null;
        $firstPending = null;

        foreach ($milestones as $key => $milestone) {
            if (!$milestone['done']) {
                $firstPendingKey = $key;
                $firstPending = $milestone;
                break;
            }
        }

        if (!$firstPendingKey || !$firstPending) {
            return [
                'first_pending_key' => null,
                'first_pending' => null,
                'first_due' => null,
                'minutes_to_due' => null,
                'status' => 'green',
                'status_label' => 'En tiempo',
                'countdown' => 'Sin pendientes',
            ];
        }

        $minutesToDue = $now->diffInMinutes($firstPending['due'], false);
        $status = 'green';
        $statusLabel = 'En tiempo';

        if ($minutesToDue < 0) {
            $status = 'red';
            $statusLabel = 'Vencido';
        } elseif ($minutesToDue <= 720) {
            $status = 'yellow';
            $statusLabel = 'Por vencer';
        }

        return [
            'first_pending_key' => $firstPendingKey,
            'first_pending' => $firstPending,
            'first_due' => $firstPending['due'],
            'minutes_to_due' => $minutesToDue,
            'status' => $status,
            'status_label' => $statusLabel,
            'countdown' => $this->formatCountdown($minutesToDue),
        ];
    }

    private function formatCountdown(?int $minutes): string
    {
        if ($minutes === null) {
            return 'Sin pendientes';
        }

        $abs = abs($minutes);
        $days = intdiv($abs, 1440);
        $hours = intdiv($abs % 1440, 60);
        $mins = $abs % 60;
        $prefix = $minutes < 0 ? 'Atraso ' : 'Restan ';

        if ($days > 0) {
            return $prefix.$days.'d '.$hours.'h';
        }
        if ($hours > 0) {
            return $prefix.$hours.'h '.$mins.'m';
        }

        return $prefix.$mins.'m';
    }
}

