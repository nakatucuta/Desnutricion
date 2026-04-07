<?php

namespace App\Http\Controllers;

use App\Exports\SeguimientosExport;
use App\Models\AsignacionesMaestrosiv549;
use App\Models\SeguimientMaestrosiv549;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SeguimientosHubController extends Controller
{
    public function index()
    {
        return view('seguimientos1ges.index');
    }

    public function dataAsignados(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = in_array((int) $user->usertype, [1, 2], true);

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
                    $title = 'Continuar (editar ultimo seguimiento)';
                    $cls = 'btn-warning';
                    $icon = 'fa-edit';
                } else {
                    $url = route('asignaciones.seguimientmaestrosiv549.create', $row->id);
                    $title = 'Iniciar seguimiento';
                    $cls = 'btn-primary';
                    $icon = 'fa-notes-medical';
                }

                return '<a href="'.$url.'" class="btn btn-sm '.$cls.'" title="'.$title.'">
                            <i class="fas '.$icon.'"></i>
                        </a>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function dataRealizados(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = in_array((int) $user->usertype, [1, 2], true);

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
            ->addColumn('acciones', function ($s) {
                $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);
                $delUrl = route('asignaciones.seguimientmaestrosiv549.destroy', [$s->asignacion_id, $s->id]);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <a href="'.$editUrl.'" class="btn btn-sm btn-warning" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="'.$delUrl.'" method="POST" style="display:inline-block" onsubmit="return confirm(\'Eliminar seguimiento?\')">
                        '.$csrf.$method.'
                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function dataAlertas(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $muestraTodo = in_array((int) $user->usertype, [1, 2], true);

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

            $base = $this->resolveSeguimientoAlertBase($s, $now);
            $milestones = [
                [
                    'label' => '48-72h',
                    'due' => $base->copy()->addHours(72),
                    'done' => !empty($s->descripcion_seguimiento_inmediato) || !empty($s->fecha_control_rn_inmediato),
                ],
                [
                    'label' => 'Post egreso',
                    'due' => $base->copy()->addDays(3),
                    'done' => !empty($s->fecha_seguimiento_1),
                ],
                [
                    'label' => '7 dias',
                    'due' => $base->copy()->addDays(7),
                    'done' => !empty($s->fecha_seguimiento_2),
                ],
                [
                    'label' => '14 dias',
                    'due' => $base->copy()->addDays(14),
                    'done' => !empty($s->fecha_seguimiento_3),
                ],
                [
                    'label' => '21 dias',
                    'due' => $base->copy()->addDays(21),
                    'done' => !empty($s->fecha_seguimiento_4),
                ],
                [
                    'label' => '28 dias',
                    'due' => $base->copy()->addDays(28),
                    'done' => !empty($s->fecha_seguimiento_5),
                ],
                [
                    'label' => '6 meses',
                    'due' => $base->copy()->addMonthsNoOverflow(6),
                    'done' => !empty($s->fecha_consulta_6_meses),
                ],
                [
                    'label' => '1 ano',
                    'due' => $base->copy()->addYearNoOverflow(1),
                    'done' => !empty($s->fecha_consulta_1_ano),
                ],
            ];

            $vencido = null;
            foreach ($milestones as $milestone) {
                if (!$milestone['done'] && $now->gt($milestone['due'])) {
                    $vencido = $milestone;
                    break;
                }
            }

            if (!$vencido) {
                continue;
            }

            $nombre = trim("{$a->pri_nom_} {$a->seg_nom_} {$a->pri_ape_} {$a->seg_ape_}");
            $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);

            $rows[] = [
                'id' => $s->id,
                'asignacion_id' => $s->asignacion_id,
                'paciente' => $nombre !== '' ? $nombre : 'N/D',
                'tip_ide_' => trim((string) $a->tip_ide_) ?: 'N/D',
                'num_ide_' => trim((string) $a->num_ide_) ?: 'N/D',
                'prestador' => optional($a->user)->name ?? 'N/D',
                'hito' => $vencido['label'],
                'fecha_limite' => $vencido['due']->toDateString(),
                'dias_atraso' => $vencido['due']->diffInDays($now),
                'acciones' => '<a href="'.$editUrl.'" class="btn btn-sm btn-danger" title="Atender alerta"><i class="fas fa-exclamation-triangle"></i></a>',
                'created_at' => optional($s->created_at)->format('Y-m-d H:i'),
            ];
        }

        return DataTables::of(collect($rows))
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $canSeeAll = in_array((int) $user->usertype, [1, 2], true);
        $filters = $request->only(['tip_ide_', 'num_ide_', 'fec_desde', 'fec_hasta']);

        $filename = 'seguimientos_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new SeguimientosExport($filters, $canSeeAll, $user->id), $filename);
    }

    private function resolveSeguimientoAlertBase(SeguimientMaestrosiv549 $seguimiento, Carbon $fallback): Carbon
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
}
