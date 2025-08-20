<?php

namespace App\Http\Controllers;

use App\Models\AsignacionesMaestrosiv549;
use App\Models\SeguimientMaestrosiv549;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SeguimientosExport;
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
    $muestraTodo = in_array((int) $user->usertype, [1, 2], true); // 1 y 2 ven todo

    // ID del último seguimiento por asignación
    $subUltimo = \App\Models\SeguimientMaestrosiv549::select('id')
        ->whereColumn('asignacion_id', 'asignaciones_maestrosiv549.id')
        ->orderByDesc('id')
        ->limit(1);

    $q = \App\Models\AsignacionesMaestrosiv549::query()
        ->with('user')
        // Sigue mostrando mientras NO esté completo
        ->whereDoesntHave('seguimientosMaestrosiv549', function ($sq) {
            $sq->completos();
        })
        ->select([
            'id', 'user_id', 'tip_ide_', 'num_ide_',
            'pri_nom_', 'seg_nom_', 'pri_ape_', 'seg_ape_',
            'fec_not', 'nom_eve'
        ])
        ->addSelect(['ultimo_seguimiento_id' => $subUltimo]);

    if (!$muestraTodo) {
        $q->where('user_id', $user->id);
    }

    return \DataTables::of($q)
        ->addColumn('paciente', function ($row) {
            $nombre = trim("{$row->pri_nom_} {$row->seg_nom_} {$row->pri_ape_} {$row->seg_ape_}");
            return $nombre !== '' ? $nombre : 'N/D';
        })
        // <<< columnas separadas >>>
        ->editColumn('tip_ide_', function ($row) {
            return trim((string) $row->tip_ide_);
        })
        ->editColumn('num_ide_', function ($row) {
            // quitamos espacios de los extremos (dataset viene acolchonado)
            return trim((string) $row->num_ide_);
        })
        // <<< fin columnas separadas >>>
        ->addColumn('prestador', function ($row) {
            return optional($row->user)->name ?? 'N/D';
        })
        ->addColumn('acciones', function ($row) {
            if (!empty($row->ultimo_seguimiento_id)) {
                $url   = route('asignaciones.seguimientmaestrosiv549.edit', [$row->id, $row->ultimo_seguimiento_id]);
                $title = 'Continuar (editar último seguimiento)';
                $cls   = 'btn-warning';
                $icon  = 'fa-edit';
            } else {
                $url   = route('asignaciones.seguimientmaestrosiv549.create', $row->id);
                $title = 'Iniciar seguimiento';
                $cls   = 'btn-primary';
                $icon  = 'fa-notes-medical';
            }

            return '<a href="'.$url.'" class="btn btn-sm '.$cls.'" title="'.$title.'">
                        <i class="fas '.$icon.'"></i>
                    </a>';
        })
        ->rawColumns(['acciones'])
        ->make(true);
}




    // Tabla 2: Seguimientos YA realizados
   // Tabla 2: Seguimientos YA realizados
public function dataRealizados(Request $request)
{
    abort_if(!auth()->check(), 401, 'No autenticado');

    $user = auth()->user();
    $muestraTodo = in_array((int) $user->usertype, [1, 2], true); // 1 y 2 ven todo

    // Cargamos relación para poder mostrar datos del caso
    $q = \App\Models\SeguimientMaestrosiv549::with(['asignacion.user'])
        ->select([
            'id','asignacion_id','created_at',
            'fecha_hospitalizacion','fecha_egreso',
            'fecha_seguimiento_1','fecha_seguimiento_2','fecha_seguimiento_3','fecha_seguimiento_4','fecha_seguimiento_5'
        ]);

    // Si NO es admin (1 ó 2), filtra a sus asignaciones
    if (!$muestraTodo) {
        $q->whereHas('asignacion', function ($qa) use ($user) {
            $qa->where('user_id', $user->id);
        });
    }

    return \DataTables::of($q)
        ->addColumn('paciente', function ($s) {
            $a = $s->asignacion;
            if (!$a) return 'N/D';
            $nombre = trim("{$a->pri_nom_} {$a->seg_nom_} {$a->pri_ape_} {$a->seg_ape_}");
            return $nombre !== '' ? $nombre : 'N/D';
        })
        // <<< columnas separadas >>>
        ->addColumn('tip_ide_', function ($s) {
            $a = $s->asignacion;
            return $a ? trim((string) $a->tip_ide_) : 'N/D';
        })
        ->addColumn('num_ide_', function ($s) {
            $a = $s->asignacion;
            return $a ? trim((string) $a->num_ide_) : 'N/D';
        })
        // <<< fin columnas separadas >>>
        ->addColumn('prestador', function ($s) {
            return optional(optional($s->asignacion)->user)->name ?? 'N/D';
        })
        ->addColumn('ultimo_hito', function ($s) {
            // Última fecha no nula entre los hitos
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
            $editUrl   = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);
            $delUrl    = route('asignaciones.seguimientmaestrosiv549.destroy', [$s->asignacion_id, $s->id]);
            $csrf      = csrf_field();
            $method    = method_field('DELETE');

            return '
                <a href="'.$editUrl.'" class="btn btn-sm btn-warning" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="'.$delUrl.'" method="POST" style="display:inline-block" onsubmit="return confirm(\'¿Eliminar seguimiento?\')">
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
    $muestraTodo = in_array((int) $user->usertype, [1, 2], true); // 1 y 2 ven todo

    // Traemos seguimientos con su asignación y usuario
    $query = \App\Models\SeguimientMaestrosiv549::with(['asignacion.user'])
        ->select([
            'id','asignacion_id','created_at',
            'descripcion_seguimiento_inmediato',
            'fecha_seguimiento_2','fecha_seguimiento_3','fecha_seguimiento_4','fecha_seguimiento_5',
            'fecha_consulta_6_meses','fecha_consulta_1_ano'
        ]);

    if (!$muestraTodo) {
        $query->whereHas('asignacion', function ($qa) use ($user) {
            $qa->where('user_id', $user->id);
        });
    }

    $now  = Carbon::now();
    $rows = [];

    foreach ($query->get() as $s) {
        $a = $s->asignacion;
        if (!$a) {
            continue;
        }

        // Base SIEMPRE por created_at del seguimiento (ya no por fecha_egreso)
        $base = $s->created_at ? Carbon::parse($s->created_at) : $now;

        // Definición de hitos (label, fecha_límite, campo que marca "cumplido")
        $milestones = [
            [
                'label' => '48–72h',
                // alerta si pasó el límite superior (72h) y no hay descripción inmediata
                'due'   => (clone $base)->addHours(72),
                'done'  => !empty($s->descripcion_seguimiento_inmediato),
            ],
            [
                'label' => '7 días',
                'due'   => (clone $base)->addDays(7),
                'done'  => !empty($s->fecha_seguimiento_2),
            ],
            [
                'label' => '14 días',
                'due'   => (clone $base)->addDays(14),
                'done'  => !empty($s->fecha_seguimiento_3),
            ],
            [
                'label' => '21 días',
                'due'   => (clone $base)->addDays(21),
                'done'  => !empty($s->fecha_seguimiento_4),
            ],
            [
                'label' => '28 días',
                'due'   => (clone $base)->addDays(28),
                'done'  => !empty($s->fecha_seguimiento_5),
            ],
            [
                'label' => '6 meses',
                'due'   => (clone $base)->addMonthsNoOverflow(6),
                'done'  => !empty($s->fecha_consulta_6_meses),
            ],
            [
                'label' => '1 año',
                'due'   => (clone $base)->addYearNoOverflow(1),
                'done'  => !empty($s->fecha_consulta_1_ano),
            ],
        ];

        // Encontrar el primer hito VENCIDO (no cumplido y now > due)
        $vencido = null;
        foreach ($milestones as $m) {
            if (!$m['done'] && $now->gt($m['due'])) {
                $vencido = $m;
                break;
            }
        }

        if ($vencido) {
            $nombre  = trim("{$a->pri_nom_} {$a->seg_nom_} {$a->pri_ape_} {$a->seg_ape_}");
            $editUrl = route('asignaciones.seguimientmaestrosiv549.edit', [$s->asignacion_id, $s->id]);

            $rows[] = [
                'id'            => $s->id,
                'asignacion_id' => $s->asignacion_id,
                'paciente'      => $nombre !== '' ? $nombre : 'N/D',
                'tip_ide_'      => trim((string) $a->tip_ide_) ?: 'N/D',
                'num_ide_'      => trim((string) $a->num_ide_) ?: 'N/D',
                'prestador'     => optional($a->user)->name ?? 'N/D',
                'hito'          => $vencido['label'],
                'fecha_limite'  => $vencido['due']->toDateString(),
                'dias_atraso'   => $vencido['due']->diffInDays($now),
                'acciones'      => '<a href="'.$editUrl.'" class="btn btn-sm btn-danger" title="Atender alerta"><i class="fas fa-exclamation-triangle"></i></a>',
                'created_at'    => optional($s->created_at)->format('Y-m-d H:i'),
            ];
        }
    }

    // Devolvemos colección ya filtrada (solo vencidos)
    return \DataTables::of(collect($rows))
        ->rawColumns(['acciones'])
        ->make(true);
}




public function exportExcel(Request $request)
{
    abort_if(!auth()->check(), 401, 'No autenticado');

    $user        = auth()->user();
    $canSeeAll   = in_array((int) $user->usertype, [1, 2], true); // 1 y 2 ven todo
    $filters     = $request->only(['tip_ide_', 'num_ide_', 'fec_desde', 'fec_hasta']);

    $filename = 'seguimientos_'.now()->format('Ymd_His').'.xlsx';

    return Excel::download(new SeguimientosExport($filters, $canSeeAll, $user->id), $filename);
}

}
