<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\Seguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class RevisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('adminrevision', ['only' => ['index', 'data']]);
    }

    public function index(Request $request)
    {
        $year = (int) $request->input('anio', now()->year);
        $modulo = $request->input('modulo', 'all');
        $status = $request->input('status', 'pending');

        $hasRevision412Table = Schema::hasTable('revisions_412');

        $pending113 = DB::table('seguimientos as seg')
            ->leftJoin('revisions as r', 'r.seguimientos_id', '=', 'seg.id')
            ->whereYear('seg.created_at', $year)
            ->whereNull('r.id')
            ->count();

        $pending412 = 0;
        if ($hasRevision412Table) {
            $pending412 = DB::table('seguimiento_412s as seg')
                ->leftJoin('revisions_412 as r', 'r.seguimiento_412_id', '=', 'seg.id')
                ->whereYear('seg.created_at', $year)
                ->whereNull('r.id')
                ->count();
        }

        $totalPending = $pending113 + $pending412;

        $recentPending113 = DB::table('seguimientos as seg')
            ->join('sivigilas as s', 's.id', '=', 'seg.sivigilas_id')
            ->leftJoin('revisions as r', 'r.seguimientos_id', '=', 'seg.id')
            ->select([
                DB::raw("'113' as modulo"),
                'seg.id as seguimiento_id',
                's.id as paciente_id',
                DB::raw("CONCAT(COALESCE(s.pri_nom_,''),' ',COALESCE(s.seg_nom_,''),' ',COALESCE(s.pri_ape_,''),' ',COALESCE(s.seg_ape_,'')) as nombre_paciente"),
                's.num_ide_ as documento',
                'seg.created_at as fecha_registro',
            ])
            ->whereYear('seg.created_at', $year)
            ->whereNull('r.id')
            ->orderByDesc('seg.created_at')
            ->limit(10)
            ->get();

        $recentPending412 = collect();
        if ($hasRevision412Table) {
            $recentPending412 = DB::table('seguimiento_412s as seg')
                ->join('cargue412s as c', 'c.id', '=', 'seg.cargue412_id')
                ->leftJoin('revisions_412 as r', 'r.seguimiento_412_id', '=', 'seg.id')
                ->select([
                    DB::raw("'412' as modulo"),
                    'seg.id as seguimiento_id',
                    'c.id as paciente_id',
                    DB::raw("CONCAT(COALESCE(c.primer_nombre,''),' ',COALESCE(c.segundo_nombre,''),' ',COALESCE(c.primer_apellido,''),' ',COALESCE(c.segundo_apellido,'')) as nombre_paciente"),
                    'c.numero_identificacion as documento',
                    'seg.created_at as fecha_registro',
                ])
                ->whereYear('seg.created_at', $year)
                ->whereNull('r.id')
                ->orderByDesc('seg.created_at')
                ->limit(10)
                ->get();
        }

        $recentPending = $recentPending113
            ->concat($recentPending412)
            ->sortByDesc('fecha_registro')
            ->take(12)
            ->values();

        return view('revision.index', compact(
            'year',
            'modulo',
            'status',
            'pending113',
            'pending412',
            'totalPending',
            'recentPending',
            'hasRevision412Table'
        ));
    }

    public function data(Request $request)
    {
        try {
            $year = (int) $request->input('anio', now()->year);
            $modulo = $request->input('modulo', 'all');
            $status = $request->input('status', 'all');
            $hasRevision412Table = Schema::hasTable('revisions_412');

            $rows113 = DB::table('seguimientos as seg')
                ->join('sivigilas as s', 's.id', '=', 'seg.sivigilas_id')
                ->join('users as u', 'u.id', '=', 'seg.user_id')
                ->leftJoin('revisions as r', 'r.seguimientos_id', '=', 'seg.id')
                ->select([
                    DB::raw("'113' as modulo"),
                    'seg.id as registro_id',
                    's.id as paciente_id',
                    's.num_ide_ as documento',
                    DB::raw("CONCAT(COALESCE(s.pri_nom_,''),' ',COALESCE(s.seg_nom_,''),' ',COALESCE(s.pri_ape_,''),' ',COALESCE(s.seg_ape_,'')) as nombre_paciente"),
                    'u.name as responsable',
                    'seg.fecha_consulta',
                    'seg.clasificacion',
                    'seg.estado',
                    'seg.fecha_proximo_control',
                    DB::raw('CASE WHEN r.id IS NULL THEN 0 ELSE 1 END as revisado_flag'),
                    'seg.created_at as fecha_registro',
                ])
                ->whereYear('seg.created_at', $year);

            $baseQuery = $rows113;

            if ($hasRevision412Table) {
                $rows412 = DB::table('seguimiento_412s as seg')
                    ->join('cargue412s as c', 'c.id', '=', 'seg.cargue412_id')
                    ->join('users as u', 'u.id', '=', 'seg.user_id')
                    ->leftJoin('revisions_412 as r', 'r.seguimiento_412_id', '=', 'seg.id')
                    ->select([
                        DB::raw("'412' as modulo"),
                        'seg.id as registro_id',
                        'c.id as paciente_id',
                        'c.numero_identificacion as documento',
                        DB::raw("CONCAT(COALESCE(c.primer_nombre,''),' ',COALESCE(c.segundo_nombre,''),' ',COALESCE(c.primer_apellido,''),' ',COALESCE(c.segundo_apellido,'')) as nombre_paciente"),
                        'u.name as responsable',
                        'seg.fecha_consulta',
                        'seg.clasificacion',
                        'seg.estado',
                        'seg.fecha_proximo_control',
                        DB::raw('CASE WHEN r.id IS NULL THEN 0 ELSE 1 END as revisado_flag'),
                        'seg.created_at as fecha_registro',
                    ])
                    ->whereYear('seg.created_at', $year);

                $baseQuery = $rows113->unionAll($rows412);
            }

            $query = DB::query()->fromSub($baseQuery, 'rev');

            if (in_array($modulo, ['113', '412'], true)) {
                $query->where('modulo', $modulo);
            }

            if ($status === 'pending') {
                $query->where('revisado_flag', 0);
            } elseif ($status === 'done') {
                $query->where('revisado_flag', 1);
            }

            return DataTables::of($query)
                ->editColumn('fecha_consulta', function ($r) {
                    return $r->fecha_consulta ? (string) $r->fecha_consulta : '-';
                })
                ->editColumn('fecha_proximo_control', function ($r) {
                    return $r->fecha_proximo_control ?: '-';
                })
                ->addColumn('modulo_badge', function ($r) {
                    if ($r->modulo === '412') {
                        return '<span class="badge badge-info">Seguimiento 412</span>';
                    }
                    return '<span class="badge badge-primary">Seguimiento 113</span>';
                })
                ->addColumn('estado_badge', function ($r) {
                    return (int) $r->estado === 1
                        ? '<span class="badge badge-success">Abierto</span>'
                        : '<span class="badge badge-secondary">Cerrado</span>';
                })
                ->addColumn('revision_badge', function ($r) {
                    return (int) $r->revisado_flag === 1
                        ? '<span class="badge badge-success">Revisado</span>'
                        : '<span class="badge badge-warning">Pendiente</span>';
                })
                ->addColumn('acciones', function ($r) {
                    $detailUrl = route('detalle_revisiones', [$r->paciente_id]) . '?modulo=' . $r->modulo . '&seguimiento=' . $r->registro_id;

                    $reviewAction = (int) $r->revisado_flag === 1
                        ? '<span class="btn btn-sm btn-success disabled"><i class="fas fa-check"></i> Revisado</span>'
                        : '<a class="btn btn-sm btn-warning" href="' . $detailUrl . '"><i class="fas fa-clipboard-check"></i> Revisar</a>';

                    return '<div class="btn-group" role="group">' . $reviewAction . '</div>';
                })
                ->filter(function ($builder) use ($request) {
                    $search = trim((string) $request->input('search.value', ''));
                    if ($search !== '') {
                        $builder->where(function ($w) use ($search) {
                            $w->where('modulo', 'like', "%{$search}%")
                              ->orWhere('registro_id', 'like', "%{$search}%")
                              ->orWhere('documento', 'like', "%{$search}%")
                              ->orWhere('nombre_paciente', 'like', "%{$search}%")
                              ->orWhere('responsable', 'like', "%{$search}%")
                              ->orWhere('fecha_consulta', 'like', "%{$search}%")
                              ->orWhere('clasificacion', 'like', "%{$search}%")
                              ->orWhere('fecha_proximo_control', 'like', "%{$search}%")
                              ->orWhereRaw("CASE WHEN CAST(estado as int)=1 THEN 'abierto' ELSE 'cerrado' END like ?", ["%{$search}%"])
                              ->orWhereRaw("CASE WHEN CAST(revisado_flag as int)=1 THEN 'revisado' ELSE 'pendiente' END like ?", ["%{$search}%"])
                              ->orWhereRaw("CASE WHEN modulo='113' THEN 'seguimiento 113' WHEN modulo='412' THEN 'seguimiento 412' ELSE modulo END like ?", ["%{$search}%"]);
                        });
                    }
                })
                ->orderColumn('registro_id', function ($q, $order) {
                    $q->orderBy('registro_id', $order);
                })
                ->rawColumns(['modulo_badge', 'estado_badge', 'revision_badge', 'acciones'])
                ->toJson();
        } catch (\Throwable $e) {
            Log::error('revision.data failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'No fue posible cargar la tabla de revision. Intenta recargar.',
            ]);
        }
    }

    public function create(Request $request, $id = null)
    {
        if ($id === null) {
            return redirect()->route('revision.index');
        }

        $modulo = $request->input('modulo', '113');
        $seguimientoId = (int) $request->input('seguimiento', 0);
        $hasRevision412Table = Schema::hasTable('revisions_412');

        if ($modulo === '412') {
            $segene = DB::table('seguimiento_412s')
                ->select(
                    'seguimiento_412s.id',
                    'cargue412s.numero_identificacion as num_ide_',
                    'cargue412s.primer_nombre as pri_nom_',
                    'cargue412s.segundo_nombre as seg_nom_',
                    'cargue412s.primer_apellido as pri_ape_',
                    'cargue412s.segundo_apellido as seg_ape_',
                    'seguimiento_412s.fecha_consulta',
                    'seguimiento_412s.peso_kilos',
                    'seguimiento_412s.talla_cm',
                    'seguimiento_412s.puntajez',
                    'seguimiento_412s.clasificacion',
                    'seguimiento_412s.requerimiento_energia_ftlc',
                    DB::raw('NULL as fecha_entrega_ftlc'),
                    'seguimiento_412s.medicamento',
                    'seguimiento_412s.observaciones',
                    'seguimiento_412s.est_act_menor',
                    DB::raw('NULL as tratamiento_f75'),
                    DB::raw('NULL as fecha_recibio_tratf75'),
                    'seguimiento_412s.fecha_proximo_control',
                    'seguimiento_412s.motivo_reapuertura',
                    'seguimiento_412s.Esquemq_complrto_pai_edad',
                    'seguimiento_412s.Atecion_primocion_y_mantenimiento_res3280_2018',
                    'seguimiento_412s.perimetro_braqueal'
                )
                ->join('cargue412s', 'seguimiento_412s.cargue412_id', '=', 'cargue412s.id')
                ->where('seguimiento_412s.cargue412_id', $id)
                ->orderByDesc('seguimiento_412s.id')
                ->get();

            $latest = $seguimientoId > 0
                ? $segene->firstWhere('id', $seguimientoId)
                : $segene->first();

            $isReviewed = false;
            if ($hasRevision412Table && $latest) {
                $isReviewed = DB::table('revisions_412')
                    ->where('seguimiento_412_id', $latest->id)
                    ->exists();
            }

            return view('revision.create', [
                'segene' => $segene,
                'latest' => $latest,
                'modulo' => '412',
                'isReviewed' => $isReviewed,
                'hasRevision412Table' => $hasRevision412Table,
            ]);
        }

        $segene = DB::table('seguimientos')
            ->select(
                'seguimientos.id',
                'sivigilas.num_ide_',
                'sivigilas.pri_nom_',
                'sivigilas.seg_nom_',
                'sivigilas.pri_ape_',
                'sivigilas.seg_ape_',
                'seguimientos.fecha_consulta',
                'seguimientos.peso_kilos',
                'seguimientos.talla_cm',
                'seguimientos.puntajez',
                'seguimientos.clasificacion',
                'seguimientos.requerimiento_energia_ftlc',
                'seguimientos.fecha_entrega_ftlc',
                'seguimientos.medicamento',
                'seguimientos.observaciones',
                'seguimientos.est_act_menor',
                'seguimientos.tratamiento_f75',
                'seguimientos.fecha_recibio_tratf75',
                'seguimientos.fecha_proximo_control',
                'seguimientos.motivo_reapuertura',
                'seguimientos.Esquemq_complrto_pai_edad',
                'seguimientos.Atecion_primocion_y_mantenimiento_res3280_2018',
                'seguimientos.perimetro_braqueal'
            )
            ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
            ->where('seguimientos.sivigilas_id', $id)
            ->orderByDesc('seguimientos.id')
            ->get();

        $latest = $seguimientoId > 0
            ? $segene->firstWhere('id', $seguimientoId)
            : $segene->first();

        $isReviewed = false;
        if ($latest) {
            $isReviewed = DB::table('revisions')
                ->where('seguimientos_id', $latest->id)
                ->exists();
        }

        return view('revision.create', [
            'segene' => $segene,
            'latest' => $latest,
            'modulo' => '113',
            'isReviewed' => $isReviewed,
            'hasRevision412Table' => $hasRevision412Table,
        ]);
    }

    public function store(Request $request)
    {
        $modulo = $request->input('modulo', '113');

        if ($modulo === '412') {
            if (!Schema::hasTable('revisions_412')) {
                return redirect()->route('revision.index')
                    ->with('error', 'Para revisar seguimientos 412 debes ejecutar la migracion pendiente de revisions_412.');
            }

            $request->validate([
                'seguimiento_412_id' => 'required|exists:seguimiento_412s,id',
            ]);

            DB::table('revisions_412')->updateOrInsert(
                ['seguimiento_412_id' => (int) $request->seguimiento_412_id],
                [
                    'estado' => 1,
                    'user_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return redirect()->route('revision.index')->with('success', 'Seguimiento 412 marcado como revisado.');
        }

        $request->validate([
            'seguimientos_id' => 'required|exists:seguimientos,id',
        ]);

        DB::table('revisions')->updateOrInsert(
            ['seguimientos_id' => (int) $request->seguimientos_id],
            [
                'estado' => 1,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->route('revision.index')->with('success', 'Seguimiento 113 marcado como revisado.');
    }

    public function show(Revision $revision)
    {
    }

    public function edit(Revision $revision)
    {
    }

    public function update(Request $request, Revision $revision)
    {
    }

    public function destroy(Revision $revision)
    {
    }

    public function reportepdf($id)
    {
        $segene = DB::table('seguimientos')
            ->select(
                'seguimientos.id',
                'sivigilas.num_ide_',
                'sivigilas.pri_nom_',
                'sivigilas.seg_nom_',
                'sivigilas.pri_ape_',
                'sivigilas.seg_ape_',
                'seguimientos.fecha_consulta',
                'seguimientos.peso_kilos',
                'seguimientos.talla_cm',
                'seguimientos.puntajez',
                'seguimientos.clasificacion',
                'seguimientos.requerimiento_energia_ftlc',
                'seguimientos.fecha_entrega_ftlc',
                'seguimientos.medicamento',
                'seguimientos.observaciones',
                'seguimientos.est_act_menor',
                'seguimientos.tratamiento_f75',
                'seguimientos.fecha_recibio_tratf75',
                'seguimientos.fecha_proximo_control'
            )
            ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
            ->where('seguimientos.sivigilas_id', $id)
            ->orderBy('seguimientos.id', 'desc')
            ->get();

        $pdf = Pdf::loadView('revision.reporte', compact('segene'))
            ->setPaper('letter', 'landscape');

        return $pdf->stream('revision.reporte.pdf');
    }
}
