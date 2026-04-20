<?php

namespace App\Http\Controllers;

use App\Exports\SeguimientosExport;
use App\Models\AsignacionesMaestrosiv549;
use App\Models\SeguimientMaestrosiv549;
use App\Services\Seguimiento549AlertService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SeguimientosHubController extends Controller
{
    public function __construct(private readonly Seguimiento549AlertService $alertService)
    {
    }

    public function index()
    {
        return view('seguimientos1ges.index');
    }

    public function dataAsignados(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = (int) ($user->usertype ?? 0) === 1;

        $idsUnicos = DB::table('asignaciones_maestrosiv549 as a2')
            ->selectRaw('MAX(a2.id) as id')
            ->when(!$muestraTodo, function ($sq) use ($user) {
                $sq->where('a2.user_id', $user->id);
            })
            ->groupBy(
                'a2.user_id',
                DB::raw("LTRIM(RTRIM(COALESCE(a2.tip_ide_, '')))"),
                DB::raw("LTRIM(RTRIM(COALESCE(a2.num_ide_, '')))"),
                DB::raw('CAST(a2.fec_not as date)'),
                DB::raw("LTRIM(RTRIM(COALESCE(a2.nom_eve, '')))")
            );

        $subUltimo = SeguimientMaestrosiv549::select('id')
            ->whereColumn('asignacion_id', 'asignaciones_maestrosiv549.id')
            ->orderByDesc('id')
            ->limit(1);

        $q = AsignacionesMaestrosiv549::query()
            ->with('user')
            ->whereDoesntHave('seguimientosMaestrosiv549', function ($sq) {
                $sq->completos();
            })
            ->select([
                'id',
                'user_id',
                'tip_ide_',
                'num_ide_',
                'pri_nom_',
                'seg_nom_',
                'pri_ape_',
                'seg_ape_',
                'fec_not',
                'nom_eve',
            ])
            ->whereIn('id', $idsUnicos)
            ->addSelect(['ultimo_seguimiento_id' => $subUltimo]);

        if (!$muestraTodo) {
            $q->where('user_id', $user->id);
        }

        return DataTables::of($q)
            ->addColumn('paciente', function ($row) {
                $nombre = trim("{$row->pri_nom_} {$row->seg_nom_} {$row->pri_ape_} {$row->seg_ape_}");
                return $nombre !== '' ? $nombre : 'N/D';
            })
            ->editColumn('tip_ide_', function ($row) {
                return trim((string) $row->tip_ide_);
            })
            ->editColumn('num_ide_', function ($row) {
                return trim((string) $row->num_ide_);
            })
            ->addColumn('prestador', function ($row) {
                return optional($row->user)->name ?? 'N/D';
            })
            ->addColumn('acciones', function ($row) {
                if (!empty($row->ultimo_seguimiento_id)) {
                    $url = route('asignaciones.seguimientmaestrosiv549.edit', [$row->id, $row->ultimo_seguimiento_id]);
                    $showUrl = route('asignaciones.seguimientmaestrosiv549.show', [$row->id, $row->ultimo_seguimiento_id]);
                    $title = 'Continuar (editar ultimo seguimiento)';
                    $cls = 'btn-warning';
                    $icon = 'fa-edit';

                    return '
                        <a href="'.$showUrl.'" class="btn btn-sm btn-info mr-1" title="Ver detalle ultimo seguimiento">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="'.$url.'" class="btn btn-sm '.$cls.'" title="'.$title.'">
                            <i class="fas '.$icon.'"></i>
                        </a>
                    ';
                } else {
                    $url = route('asignaciones.seguimientmaestrosiv549.create', $row->id);
                    $title = 'Iniciar seguimiento';
                    $cls = 'btn-primary';
                    $icon = 'fa-notes-medical';

                    return '<a href="'.$url.'" class="btn btn-sm '.$cls.'" title="'.$title.'">
                                <i class="fas '.$icon.'"></i>
                            </a>';
                }
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function dataRealizados(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = (int) ($user->usertype ?? 0) === 1;
        $canDelete = (int) ($user->usertype ?? 0) === 1;

        $q = SeguimientMaestrosiv549::with(['asignacion.user'])
            ->select([
                'id',
                'asignacion_id',
                'created_at',
                'fecha_hospitalizacion',
                'fecha_egreso',
                'fecha_seguimiento_1',
                'fecha_seguimiento_2',
                'fecha_seguimiento_3',
                'fecha_seguimiento_4',
                'fecha_seguimiento_5',
            ]);

        if (!$muestraTodo) {
            $q->whereHas('asignacion', function ($qa) use ($user) {
                $qa->where('user_id', $user->id);
            });
        }

        return DataTables::of($q)
            ->addColumn('paciente', function ($s) {
                $a = $s->asignacion;
                if (!$a) {
                    return 'N/D';
                }

                $nombre = trim("{$a->pri_nom_} {$a->seg_nom_} {$a->pri_ape_} {$a->seg_ape_}");
                return $nombre !== '' ? $nombre : 'N/D';
            })
            ->addColumn('tip_ide_', function ($s) {
                $a = $s->asignacion;
                return $a ? trim((string) $a->tip_ide_) : 'N/D';
            })
            ->addColumn('num_ide_', function ($s) {
                $a = $s->asignacion;
                return $a ? trim((string) $a->num_ide_) : 'N/D';
            })
            ->addColumn('prestador', function ($s) {
                return optional(optional($s->asignacion)->user)->name ?? 'N/D';
            })
            ->addColumn('ultimo_hito', function ($s) {
                $fechas = array_filter([
                    $s->fecha_seguimiento_5,
                    $s->fecha_seguimiento_4,
                    $s->fecha_seguimiento_3,
                    $s->fecha_seguimiento_2,
                    $s->fecha_seguimiento_1,
                    $s->fecha_egreso,
                    $s->fecha_hospitalizacion,
                ]);

                return $fechas ? reset($fechas) : ($s->created_at?->format('Y-m-d') ?? 'N/D');
            })
            ->addColumn('acciones', function ($s) use ($canDelete) {
                $showUrl = route('asignaciones.seguimientmaestrosiv549.show', [$s->asignacion_id, $s->id]);
                $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);
                $html = '
                    <a href="'.$showUrl.'" class="btn btn-sm btn-info mr-1" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-sm btn-warning mr-1" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                ';

                if ($canDelete) {
                    $delUrl = route('asignaciones.seguimientmaestrosiv549.destroy', [$s->asignacion_id, $s->id]);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $html .= '
                        <form action="'.$delUrl.'" method="POST" style="display:inline-block" onsubmit="return confirm(\'Eliminar seguimiento?\')">
                            '.$csrf.$method.'
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    ';
                }

                return $html;
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function dataAlertas(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = (int) ($user->usertype ?? 0) === 1;

        $query = SeguimientMaestrosiv549::with(['asignacion.user'])
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
            ]);

        if (!$muestraTodo) {
            $query->whereHas('asignacion', function ($qa) use ($user) {
                $qa->where('user_id', $user->id);
            });
        }

        $now = Carbon::now();
        $rows = [];

        foreach ($query->get() as $s) {
            $a = $s->asignacion;
            if (!$a) {
                continue;
            }

            $snapshot = $this->alertService->evaluate($s, $now);
            $timeline = $snapshot['timeline'];
            $risk = $snapshot['risk'];

            $hasTimelineAlert = in_array($timeline['status'], ['yellow', 'red'], true);
            $hasRiskAlert = in_array($risk['level'], ['high', 'very_high', 'critical'], true);
            if (!$hasTimelineAlert && !$hasRiskAlert) {
                continue;
            }

            $nombre = trim("{$a->pri_nom_} {$a->seg_nom_} {$a->pri_ape_} {$a->seg_ape_}");
            $showUrl = route('asignaciones.seguimientmaestrosiv549.show', [$s->asignacion_id, $s->id]);
            $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);
            $hito = $timeline['first_pending']['label'] ?? 'Sin hito pendiente';
            $fechaLimite = $timeline['first_due'] ? $timeline['first_due']->toDateString() : 'N/D';
            $diasAtraso = ($timeline['minutes_to_due'] ?? 0) < 0 ? intdiv(abs($timeline['minutes_to_due']), 1440) : 0;

            $semColor = 'success';
            if ($timeline['status'] === 'yellow') {
                $semColor = 'warning';
            } elseif ($timeline['status'] === 'red') {
                $semColor = 'danger';
            }

            $riskBadgeColor = match ($risk['level']) {
                'critical' => 'danger',
                'very_high' => 'warning',
                'high' => 'info',
                'moderate' => 'secondary',
                default => 'light',
            };

            $rows[] = [
                'id' => $s->id,
                'asignacion_id' => $s->asignacion_id,
                'paciente' => $nombre !== '' ? $nombre : 'N/D',
                'tip_ide_' => trim((string) $a->tip_ide_) ?: 'N/D',
                'num_ide_' => trim((string) $a->num_ide_) ?: 'N/D',
                'prestador' => optional($a->user)->name ?? 'N/D',
                'hito' => $hito,
                'fecha_limite' => $fechaLimite,
                'dias_atraso' => $diasAtraso,
                'riesgo' => '<span class="badge badge-'.$riskBadgeColor.'">'.$risk['label'].' ('.$risk['score'].')</span>',
                'semaforo' => '<span class="badge badge-'.$semColor.'">'.$timeline['status_label'].'</span>',
                'temporizador' => $timeline['countdown'],
                'acciones' => '
                    <a href="'.$showUrl.'" class="btn btn-sm btn-info mr-1" title="Ver detalle"><i class="fas fa-eye"></i></a>
                    <a href="'.$editUrl.'" class="btn btn-sm btn-danger" title="Atender alerta"><i class="fas fa-exclamation-triangle"></i></a>
                ',
                'created_at' => optional($s->created_at)->format('Y-m-d H:i'),
            ];
        }

        return DataTables::of(collect($rows))
            ->rawColumns(['acciones', 'riesgo', 'semaforo'])
            ->make(true);
    }

    public function dataIndicadores(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = (int) ($user->usertype ?? 0) === 1;
        $desde = $this->parseDateInput($request->get('fec_desde'));
        $hasta = $this->parseDateInput($request->get('fec_hasta'));

        $query = SeguimientMaestrosiv549::with('asignacion.user')
            ->select([
                'id',
                'asignacion_id',
                'created_at',
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
                'cirugia_adicional',
                'causa_agrupada',
            ]);

        if (!$muestraTodo) {
            $query->whereHas('asignacion', function ($qa) use ($user) {
                $qa->where('user_id', $user->id);
            });
        }

        $now = Carbon::now();
        $rows = $query->get();

        $total = 0;
        $onTimeNotificacion = 0;
        $crit3mas = 0;
        $muertes = 0;
        $altoRiesgo = 0;
        $superInmediataVencida = 0;

        $causas = [];
        $ips = [];
        $eapb = [];
        $municipios = [];
        $semanas = [];

        foreach ($rows as $s) {
            $a = $s->asignacion;
            if (!$a) {
                continue;
            }

            $fecNot = $this->tryParseDate($a->fec_not ?? null);
            $fechaEvento = $fecNot ?: ($s->created_at instanceof Carbon ? $s->created_at->copy() : null);

            if ($desde && (!$fechaEvento || $fechaEvento->copy()->startOfDay()->lt($desde->copy()->startOfDay()))) {
                continue;
            }

            if ($hasta && (!$fechaEvento || $fechaEvento->copy()->startOfDay()->gt($hasta->copy()->startOfDay()))) {
                continue;
            }

            $total++;

            $snapshot = $this->alertService->evaluate($s, $now);
            $risk = $snapshot['risk'];
            $timeline = $snapshot['timeline'];

            if (in_array($risk['level'], ['high', 'very_high', 'critical'], true)) {
                $altoRiesgo++;
            }
            if (($timeline['first_pending_key'] ?? null) === '48h72h' && ($timeline['minutes_to_due'] ?? 1) < 0) {
                $superInmediataVencida++;
            }
            if (!empty($a->fec_def_)) {
                $muertes++;
            }

            if ($risk['criteria_count'] >= 3) {
                $crit3mas++;
            }

            if ($fecNot) {
                if ($s->created_at && $s->created_at->copy()->lessThanOrEqualTo($fecNot->copy()->endOfDay())) {
                    $onTimeNotificacion++;
                }

                $week = 'SE '.str_pad((string) $fecNot->weekOfYear, 2, '0', STR_PAD_LEFT);
                $semanas[$week] = ($semanas[$week] ?? 0) + 1;
            }

            $causa = trim((string) ($s->causa_agrupada ?: $a->caus_agrup ?? 'Sin clasificar'));
            $ipsLabel = trim((string) ($a->nom_upgd ?: optional($a->user)->name ?: 'Sin IPS'));
            $eapbLabel = trim((string) ($a->cod_ase_ ?: 'Sin EAPB'));
            $munLabel = trim((string) ($a->nmun_resi ?: 'Sin municipio'));

            $causas[$causa] = ($causas[$causa] ?? 0) + 1;
            $ips[$ipsLabel] = ($ips[$ipsLabel] ?? 0) + 1;
            $eapb[$eapbLabel] = ($eapb[$eapbLabel] ?? 0) + 1;
            $municipios[$munLabel] = ($municipios[$munLabel] ?? 0) + 1;
        }

        return response()->json([
            'totales' => [
                'casos' => $total,
                'oportunidad_notificacion_pct' => $total > 0 ? round(($onTimeNotificacion / $total) * 100, 1) : 0.0,
                'casos_3_criterios_pct' => $total > 0 ? round(($crit3mas / $total) * 100, 1) : 0.0,
                'letalidad_pct' => $total > 0 ? round(($muertes / $total) * 100, 2) : 0.0,
                'alto_riesgo' => $altoRiesgo,
                'super_inmediata_vencida' => $superInmediataVencida,
            ],
            'filtro_periodo' => [
                'desde' => $desde?->toDateString(),
                'hasta' => $hasta?->toDateString(),
            ],
            'causas_agrupadas' => $this->toTopList($causas),
            'por_ips' => $this->toTopList($ips),
            'por_eapb' => $this->toTopList($eapb),
            'por_municipio' => $this->toTopList($municipios),
            'por_semana' => $this->toTopList($semanas),
        ]);
    }

    public function exportExcel(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $canSeeAll = (int) ($user->usertype ?? 0) === 1;
        $filters = $request->only(['tip_ide_', 'num_ide_', 'fec_desde', 'fec_hasta']);

        $filename = 'seguimientos_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new SeguimientosExport($filters, $canSeeAll, $user->id), $filename);
    }

    private function toTopList(array $values, int $limit = 8): array
    {
        arsort($values);

        $out = [];
        foreach (array_slice($values, 0, $limit, true) as $label => $count) {
            $out[] = ['label' => $label, 'count' => $count];
        }

        return $out;
    }

    private function tryParseDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        $raw = trim((string) $value);
        $formats = ['d/m/Y', 'd/m/Y H:i:s', 'Y-m-d', 'Y-m-d H:i:s', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $raw);
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDateInput($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', trim((string) $value));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
