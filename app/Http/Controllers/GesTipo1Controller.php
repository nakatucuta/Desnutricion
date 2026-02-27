<?php

namespace App\Http\Controllers;

use App\Exports\GesTipo1Export;
use App\Imports\GesTipo1Import;
use App\Jobs\ImportGesTipo1ExcelJob;
use App\Models\GesTipo1;
use App\Models\ImportJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class GesTipo1Controller extends Controller
{
    private function isAdminUser(): bool
    {
        return (int) (Auth::user()->usertype ?? 0) === 1;
    }

    /**
     * Mostrar formulario de importacion
     */
    public function showImportForm()
    {
        return view('ges_tipo1.import');
    }

    /**
     * Flujo sincronico (fallback)
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('excel_file');
        $storedPath = $file->store('imports');
        $fullPath = storage_path('app/' . $storedPath);
        $batchId = 0;

        try {
            $batchId = (int) DB::table('batch_verifications')->insertGetId([
                'fecha_cargue' => DB::raw('CONVERT(date, GETDATE())'),
                'created_at' => DB::raw('GETDATE()'),
                'updated_at' => DB::raw('GETDATE()'),
            ]);

            $importer = new GesTipo1Import((int) Auth::id(), $batchId);
            Excel::import($importer, $fullPath);
            $importer->finalize();

            $errors = $importer->getErrores();
            if (!empty($errors)) {
                GesTipo1::where('batch_verifications_id', $batchId)->delete();
                DB::table('batch_verifications')->where('id', $batchId)->delete();

                return back()->with('error', implode("<br>", array_slice($errors, 0, 120)));
            }

            return redirect()
                ->route('ges_tipo1.index')
                ->with('success', 'Datos importados correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', nl2br($e->getMessage()));
        } finally {
            @unlink($fullPath);
        }
    }

    /**
     * Iniciar cargue asincrono por cola
     */
    public function startAsyncImport(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            ]);

            $userId = (int) Auth::id();
            if ($userId <= 0) {
                return response()->json(['ok' => false, 'message' => 'No autenticado.'], 401);
            }

            $path = $request->file('file')->store('imports');
            $fullPath = storage_path('app/' . $path);
            $token = (string) Str::uuid();

            $jobRow = ImportJob::create([
                'user_id' => $userId,
                'token' => $token,
                'status' => 'queued',
                'percent' => 0,
                'step' => 'cola',
                'message' => 'En cola...',
                'errors' => null,
                'errors_count' => 0,
                'report_path' => null,
                'batch_verifications_id' => null,
            ]);

            ImportGesTipo1ExcelJob::dispatch(
                (int) $jobRow->id,
                $fullPath,
                $userId,
                $token,
                (string) $request->file('file')->getClientOriginalName()
            )->onQueue('imports');

            return response()->json(['ok' => true, 'token' => $token]);
        } catch (Throwable $e) {
            Log::error('GesTipo1 startAsyncImport ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'userId' => Auth::id(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo iniciar la importacion: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consultar estado del cargue asincrono
     */
    public function asyncImportStatus(string $token)
    {
        $job = ImportJob::where('token', $token)->latest('id')->first();

        if (!$job) {
            return response()->json([
                'status' => 'not_found',
                'percent' => 0,
                'step' => 'init',
                'message' => 'No existe importacion con ese token.',
                'errors' => [],
                'errors_count' => 0,
                'batch_id' => null,
            ]);
        }

        if (!$this->isAdminUser() && (int) $job->user_id !== (int) Auth::id()) {
            return response()->json([
                'status' => 'forbidden',
                'percent' => 0,
                'step' => 'auth',
                'message' => 'No tienes permiso para ver este proceso.',
                'errors' => [],
                'errors_count' => 0,
                'batch_id' => null,
            ], 403);
        }

        $errors = [];
        if (!empty($job->errors)) {
            $decoded = json_decode((string) $job->errors, true);
            $errors = is_array($decoded) ? $decoded : [(string) $job->errors];
        }

        return response()->json([
            'status' => (string) $job->status,
            'percent' => (int) ($job->percent ?? 0),
            'step' => (string) ($job->step ?? ''),
            'message' => (string) ($job->message ?? ''),
            'errors' => $errors,
            'errors_count' => (int) ($job->errors_count ?? count($errors)),
            'batch_id' => $job->batch_verifications_id ? (int) $job->batch_verifications_id : null,
        ]);
    }

    /**
     * Listado de gestantes (DataTables + filtro por usertype)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $subLastSeg = DB::raw('(SELECT MAX(s.id) FROM ges_tipo1_seguimientos s WHERE s.ges_tipo1_id = ges_tipo1.id) AS last_seg_id');

            $query = GesTipo1::query()->select([
                'id',
                'primer_nombre',
                'segundo_nombre',
                'primer_apellido',
                'segundo_apellido',
                'no_id_del_usuario',
                'numero_carnet',
                'fecha_de_nacimiento',
                'fecha_probable_de_parto',
                'tipo_de_identificacion_de_la_usuaria',
                $subLastSeg,
            ]);

            if ((int) (Auth::user()->usertype ?? 0) === 2) {
                $query->where('user_id', (int) Auth::id());
            }

            return DataTables::of($query)
                ->addColumn('full_name', function ($row) {
                    return trim("{$row->primer_nombre} {$row->segundo_nombre} {$row->primer_apellido} {$row->segundo_apellido}");
                })
                ->addColumn('acciones', function ($row) {
                    $show = route('ges_tipo1.show', ['ges' => $row->id]);

                    if (RouteFacade::has('ges_tipo1.seguimientos.create')) {
                        $seguimientoCreate = route('ges_tipo1.seguimientos.create', ['ges' => $row->id]);
                    } else {
                        $seguimientoCreate = url("ges_tipo1/{$row->id}/seguimientos/create");
                    }

                    if (!empty($row->last_seg_id)) {
                        if (RouteFacade::has('ges_tipo1.seguimientos.edit')) {
                            $editUrl = route('ges_tipo1.seguimientos.edit', [
                                'ges' => $row->id,
                                'seg' => $row->last_seg_id,
                            ]);
                        } else {
                            $editUrl = url("ges_tipo1/{$row->id}/seguimientos/{$row->last_seg_id}/edit");
                        }

                        $seguimientoBtn = <<<HTML
<button class="btn btn-sm btn-primary" title="Ya existe un seguimiento" disabled>
  <i class="fas fa-notes-medical"></i> Seguimiento
</button>
HTML;

                        $editBtn = <<<HTML
<a href="{$editUrl}" class="btn btn-sm btn-warning ml-1" title="Editar ultimo seguimiento">
  <i class="fas fa-edit"></i> Editar
</a>
HTML;
                    } else {
                        $seguimientoBtn = <<<HTML
<a href="{$seguimientoCreate}" class="btn btn-sm btn-primary" title="Nuevo seguimiento">
  <i class="fas fa-notes-medical"></i> Seguimiento
</a>
HTML;

                        $editBtn = <<<HTML
<button class="btn btn-sm btn-warning ml-1" title="Sin seguimientos" disabled>
  <i class="fas fa-edit"></i> Editar
</button>
HTML;
                    }

                    return <<<HTML
<a href="{$show}" class="btn btn-sm btn-gradient mr-1">
  <i class="fas fa-eye mr-1"></i> Ver
</a>
{$seguimientoBtn}
{$editBtn}
HTML;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        return view('ges_tipo1.index');
    }

    /**
     * Mostrar el detalle de una gestante + sus registros relacionados.
     */
    public function show(\App\Models\GesTipo1 $ges)
    {
        $ges->load(['seguimientos', 'tipo3']);
        $segs = $ges->seguimientos->sortByDesc('id')->values();
        $ultimo = $segs->first();

        return view('ges_tipo1.show', [
            'gestante' => $ges,
            'segs' => $segs,
            'ultimo' => $ultimo,
            'tipo3' => $ges->tipo3 ?? collect(),
            'segsCount' => $segs->count(),
        ]);
    }

    /**
     * Exportar a Excel rango por created_at
     */
    public function exportTipo1(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($request->query('from'))->format('Ymd');
        $to = Carbon::parse($request->query('to'))->format('Ymd');

        return Excel::download(
            new GesTipo1Export($from, $to),
            "gestantes_created_{$from}_to_{$to}.xlsx"
        );
    }
}

