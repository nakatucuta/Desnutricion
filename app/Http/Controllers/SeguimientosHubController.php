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
        $this->authorizeHubAccess();
        return view('seguimientos1ges.index');
    }

    public function dataAsignados(Request $request)
    {
        [$user, $muestraTodo] = $this->authorizeHubAccess();
        $canDelete = $muestraTodo;
        $userFilterSql = $muestraTodo ? '' : "
            WHERE EXISTS (
                SELECT 1
                FROM asignaciones_maestrosiv549_colaboradores c2
                WHERE c2.asignacion_id = a2.id
                  AND c2.user_id = ".(int) $user->id."
            )
        ";
        $idsUnicosSql = "
            SELECT d.id
            FROM (
                SELECT
                    a2.id,
                    ROW_NUMBER() OVER (
                        PARTITION BY
                            LTRIM(RTRIM(COALESCE(a2.tip_ide_, ''))),
                            LTRIM(RTRIM(COALESCE(a2.num_ide_, ''))),
                            LTRIM(RTRIM(COALESCE(a2.nom_eve, ''))),
                            COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(a2.[year], ''))), ''), CONVERT(varchar(4), YEAR(a2.fec_not)), '0000'),
                            COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(a2.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, a2.fec_not)), 2), '00')
                        ORDER BY
                            CASE
                                WHEN EXISTS (
                                    SELECT 1
                                    FROM seguimient_maestrosiv549 s
                                    WHERE s.asignacion_id = a2.id
                                      AND s.fecha_consulta_1_ano IS NOT NULL
                                ) THEN 1
                                ELSE 0
                            END ASC,
                            a2.id DESC
                    ) AS rn
                FROM asignaciones_maestrosiv549 a2
                {$userFilterSql}
            ) d
            WHERE d.rn = 1
        ";
        $idsUnicos = DB::table(DB::raw("({$idsUnicosSql}) as dd"))->select('dd.id');

        $subUltimo = SeguimientMaestrosiv549::select('id')
            ->whereIn('asignacion_id', function ($sq) {
                $sq->from('asignaciones_maestrosiv549 as ag')
                    ->select('ag.id')
                    ->whereRaw("LTRIM(RTRIM(COALESCE(ag.tip_ide_, ''))) = LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.tip_ide_, '')))")
                    ->whereRaw("LTRIM(RTRIM(COALESCE(ag.num_ide_, ''))) = LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.num_ide_, '')))")
                    ->whereRaw("LTRIM(RTRIM(COALESCE(ag.nom_eve, ''))) = LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.nom_eve, '')))")
                    ->whereRaw("COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(ag.[year], ''))), ''), CONVERT(varchar(4), YEAR(ag.fec_not)), '0000') = COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.[year], ''))), ''), CONVERT(varchar(4), YEAR(asignaciones_maestrosiv549.fec_not)), '0000')")
                    ->whereRaw("COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(ag.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, ag.fec_not)), 2), '00') = COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, asignaciones_maestrosiv549.fec_not)), 2), '00')");
            })
            ->orderByDesc('id')
            ->limit(1);

        $q = AsignacionesMaestrosiv549::query()
            ->with(['user', 'colaboradores:id,name,email'])
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
            ->addSelect([
                'asignados_count' => DB::table('asignaciones_maestrosiv549 as agc')
                    ->join('asignaciones_maestrosiv549_colaboradores as col', 'col.asignacion_id', '=', 'agc.id')
                    ->selectRaw('COUNT(DISTINCT col.user_id)')
                    ->whereRaw("LTRIM(RTRIM(COALESCE(agc.tip_ide_, ''))) = LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.tip_ide_, '')))")
                    ->whereRaw("LTRIM(RTRIM(COALESCE(agc.num_ide_, ''))) = LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.num_ide_, '')))")
                    ->whereRaw("LTRIM(RTRIM(COALESCE(agc.nom_eve, ''))) = LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.nom_eve, '')))")
                    ->whereRaw("COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(agc.[year], ''))), ''), CONVERT(varchar(4), YEAR(agc.fec_not)), '0000') = COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.[year], ''))), ''), CONVERT(varchar(4), YEAR(asignaciones_maestrosiv549.fec_not)), '0000')")
                    ->whereRaw("COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(agc.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, agc.fec_not)), 2), '00') = COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(asignaciones_maestrosiv549.semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, asignaciones_maestrosiv549.fec_not)), 2), '00')")
            ])
            ->addSelect(['ultimo_seguimiento_id' => $subUltimo]);

        if (!$muestraTodo) {
            $q->whereIn('id', $this->visibleAsignacionesSubquery((int) $user->id));
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
            ->addColumn('prestador', function ($row) use ($muestraTodo) {
                $count = (int) ($row->asignados_count ?? 1);
                if ($muestraTodo && $count > 1) {
                    $prestadores = $row->colaboradores
                        ->map(function ($u) {
                            return [
                                'name' => trim((string) ($u->name ?? 'N/D')),
                                'email' => trim((string) ($u->email ?? 'Sin correo')),
                            ];
                        })
                        ->values()
                        ->all();

                    $payload = e(json_encode($prestadores, JSON_UNESCAPED_UNICODE));

                    return '<button type="button" class="btn btn-link p-0 align-baseline seg-prestadores-link" data-prestadores="'.$payload.'">'.$count.' prestadores asignados</button>';
                }

                return optional($row->user)->name ?? 'N/D';
            })
            ->addColumn('acciones', function ($row) use ($canDelete) {
                if (!empty($row->ultimo_seguimiento_id)) {
                    $url = route('asignaciones.seguimientmaestrosiv549.edit', [$row->id, $row->ultimo_seguimiento_id]);
                    $showUrl = route('asignaciones.seguimientmaestrosiv549.show', [$row->id, $row->ultimo_seguimiento_id]);
                    $title = 'Continuar (editar ultimo seguimiento)';
                    $cls = 'btn-warning';
                    $icon = 'fa-edit';

                    $html = '
                        <a href="'.$showUrl.'" class="btn btn-sm btn-info mr-1" title="Ver detalle ultimo seguimiento">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="'.$url.'" class="btn btn-sm '.$cls.'" title="'.$title.'">
                            <i class="fas '.$icon.'"></i>
                        </a>
                    ';

                    if ($canDelete) {
                        $delUrl = route('asignaciones-maestrosiv549.destroy', $row->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $html .= '
                            <form action="'.$delUrl.'" method="POST" class="js-delete-async" data-confirm="Eliminar asignacion? Se notificara por correo a los asignados." style="display:inline-block">
                                '.$csrf.$method.'
                                <button type="submit" class="btn btn-sm btn-danger ml-1" title="Eliminar asignación">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        ';
                    }

                    return $html;
                } else {
                    $url = route('asignaciones.seguimientmaestrosiv549.create', $row->id);
                    $title = 'Iniciar seguimiento';
                    $cls = 'btn-primary';
                    $icon = 'fa-notes-medical';
                    $html = '<a href="'.$url.'" class="btn btn-sm '.$cls.'" title="'.$title.'">
                                <i class="fas '.$icon.'"></i>
                            </a>';
                    if ($canDelete) {
                        $delUrl = route('asignaciones-maestrosiv549.destroy', $row->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $html .= '
                            <form action="'.$delUrl.'" method="POST" class="js-delete-async" data-confirm="Eliminar asignacion? Se notificara por correo a los asignados." style="display:inline-block">
                                '.$csrf.$method.'
                                <button type="submit" class="btn btn-sm btn-danger ml-1" title="Eliminar asignación">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        ';
                    }

                    return $html;
                }
            })
            ->rawColumns(['acciones', 'prestador'])
            ->make(true);
    }

    public function dataRealizados(Request $request)
    {
        [$user, $muestraTodo] = $this->authorizeHubAccess();
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
            $q->whereIn('asignacion_id', $this->visibleAsignacionesSubquery((int) $user->id));
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
                        <form action="'.$delUrl.'" method="POST" class="js-delete-async" data-confirm="Eliminar seguimiento?" style="display:inline-block">
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
        [$user, $muestraTodo] = $this->authorizeHubAccess();

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
            $query->whereIn('asignacion_id', $this->visibleAsignacionesSubquery((int) $user->id));
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
        [$user, $muestraTodo] = $this->authorizeHubAccess();
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
            $query->whereIn('asignacion_id', $this->visibleAsignacionesSubquery((int) $user->id));
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
        [$user, $canSeeAll] = $this->authorizeHubAccess();
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

    private function visibleAsignacionesSubquery(int $userId)
    {
        return DB::table('asignaciones_maestrosiv549_colaboradores as c')
            ->where('c.user_id', $userId)
            ->selectRaw('DISTINCT c.asignacion_id');
    }

    private function authorizeHubAccess(): array
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $usertype = (int) ($user->usertype ?? 0);
        $isAdmin = $usertype === 1;
        $isPrestador = $usertype === 2;

        abort_unless($isAdmin || $isPrestador, 403, 'Solo administradores (tipo 1) y prestadores (tipo 2) pueden acceder al modulo 549.');

        return [$user, $isAdmin];
    }
}
