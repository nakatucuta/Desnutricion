<?php

namespace App\Http\Controllers;

use App\Exports\PreconcepcionalExport;
use App\Imports\PreconcepcionalImport;
use App\Jobs\ImportPreconcepcionalExcelJob;
use App\Models\ImportJob;
use App\Models\Preconcepcional;
use App\Models\PreconcepcionalImportBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PreconcepcionalController extends Controller
{
    private function isAdminUser(): bool
    {
        return (int) (Auth::user()->usertype ?? 0) === 1;
    }

    private function applyUserVisibilityScope(Builder $query): Builder
    {
        if ($this->isAdminUser()) {
            return $query;
        }

        $userId = (int) Auth::id();

        return $query->whereExists(function ($sub) use ($userId) {
            $sub->selectRaw('1')
                ->from('preconcepcional_import_batches as pb')
                ->whereColumn('pb.id', 'preconcepcionales.created_batch_id')
                ->where('pb.user_id', $userId);
        });
    }

    private function canSeePreconcepcionalRow(Preconcepcional $preconcepcional): bool
    {
        if ($this->isAdminUser()) {
            return true;
        }

        $userId = (int) Auth::id();
        if ($userId <= 0 || empty($preconcepcional->created_batch_id)) {
            return false;
        }

        return PreconcepcionalImportBatch::where('id', (int) $preconcepcional->created_batch_id)
            ->where('user_id', $userId)
            ->exists();
    }

    public function index()
    {
        return view('preconcepcional.index');
    }

    public function data(Request $request)
    {
        $q = $this->applyUserVisibilityScope(Preconcepcional::query())
            ->select([
                'id',
                'tipo_documento',
                'numero_identificacion',
                'nombre_1',
                'nombre_2',
                'apellido_1',
                'apellido_2',
                'municipio_residencia',
                'riesgo_preconcepcional',
            ]);

        return DataTables::of($q)
            ->addColumn('nombres', function ($r) {
                return trim(($r->nombre_1 ?? '') . ' ' . ($r->nombre_2 ?? ''));
            })
            ->addColumn('apellidos', function ($r) {
                return trim(($r->apellido_1 ?? '') . ' ' . ($r->apellido_2 ?? ''));
            })
            ->addColumn('acciones', function ($r) {
                $url = route('preconcepcional.show', $r->id);
                return '
                    <a class="btn btn-sm btn-primary" href="' . $url . '" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function create()
    {
        return view('preconcepcional.import');
    }

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

            ImportPreconcepcionalExcelJob::dispatch(
                (int) $jobRow->id,
                $fullPath,
                $userId,
                $token,
                (string) $request->file('file')->getClientOriginalName()
            )->onQueue('imports');

            return response()->json(['ok' => true, 'token' => $token]);
        } catch (\Throwable $e) {
            Log::error('Preconcepcional startAsyncImport ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'userId' => Auth::id(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo iniciar la importacion: ' . $e->getMessage(),
            ], 500);
        }
    }

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

    public function store(Request $request)
    {
        Log::channel('single')->info('ENTRO AL STORE PRECONCEPCIONAL', [
            'time' => now()->format('Ymd H:i:s'),
            'user_id' => auth()->id(),
            'files' => array_keys($request->allFiles()),
            'has_file' => $request->hasFile('file'),
        ]);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            Log::channel('single')->error('VALIDACION REQUEST FALLO', [
                'errors' => $validator->errors()->toArray(),
                'files' => $request->allFiles(),
            ]);

            return back()
                ->with('warning', 'No esta llegando el archivo al backend. Revisa name="file" y enctype.')
                ->withErrors($validator)
                ->withInput();
        }

        $file = $request->file('file');
        $path = $file->getRealPath();
        $hash = $path ? hash_file('sha256', $path) : null;
        $size = $file->getSize();
        $now = now()->format('Ymd H:i:s');

        $batch = PreconcepcionalImportBatch::withoutTimestamps(function () use ($file, $hash, $size, $now) {
            return PreconcepcionalImportBatch::create([
                'original_name' => $file->getClientOriginalName(),
                'user_id' => auth()->id(),
                'file_hash' => $hash,
                'file_size' => $size,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        $t0 = microtime(true);
        $import = new PreconcepcionalImport((int) $batch->id);
        Excel::import($import, $file);
        $seconds = round(microtime(true) - $t0, 2);

        PreconcepcionalImportBatch::withoutTimestamps(function () use ($batch, $import, $seconds, $now) {
            $batch->update(array_merge(
                $import->getCounters(),
                [
                    'duration_seconds' => $seconds,
                    'updated_at' => $now,
                ]
            ));
        });

        return redirect()
            ->route('preconcepcional.batches')
            ->with('success', "Importacion lista. Lote #{$batch->id} | Tiempo: {$seconds}s");
    }

    public function show(Preconcepcional $preconcepcional)
    {
        abort_unless($this->canSeePreconcepcionalRow($preconcepcional), 403, 'No tienes permiso para ver este registro.');
        return view('preconcepcional.show', compact('preconcepcional'));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $search = $request->input('search.value');
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columnsMap = [
            0 => 'id',
            1 => 'tipo_documento',
            2 => 'numero_identificacion',
            3 => 'nombre_1',
            4 => 'apellido_1',
            5 => 'municipio_residencia',
            6 => 'riesgo_preconcepcional',
        ];

        $orderBy = $columnsMap[$orderColIndex] ?? 'id';

        $q = $this->applyUserVisibilityScope(Preconcepcional::query());

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('tipo_documento', 'like', "%{$search}%")
                    ->orWhere('numero_identificacion', 'like', "%{$search}%")
                    ->orWhere('nombre_1', 'like', "%{$search}%")
                    ->orWhere('nombre_2', 'like', "%{$search}%")
                    ->orWhere('apellido_1', 'like', "%{$search}%")
                    ->orWhere('apellido_2', 'like', "%{$search}%")
                    ->orWhere('municipio_residencia', 'like', "%{$search}%")
                    ->orWhere('riesgo_preconcepcional', 'like', "%{$search}%");
            });
        }

        $q->orderBy($orderBy, $orderDir);
        $rows = $q->select('*')->get();
        $fileBase = 'preconcepcional_full_' . now()->format('Ymd_His');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileBase}.csv\"",
            ];

            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

                if ($rows->count() > 0) {
                    fputcsv($out, array_keys($rows->first()->getAttributes()));
                } else {
                    fputcsv($out, ['Sin datos']);
                    fclose($out);
                    return;
                }

                foreach ($rows as $r) {
                    $data = $r->getAttributes();
                    foreach ($data as $k => $v) {
                        if ($v instanceof \DateTimeInterface) {
                            $data[$k] = $v->format('Y-m-d H:i:s');
                        }
                    }
                    fputcsv($out, array_values($data));
                }

                fclose($out);
            };

            return Response::stream($callback, 200, $headers);
        }

        return Excel::download(new PreconcepcionalExport($rows), "{$fileBase}.xlsx");
    }

    public function batches()
    {
        $batchesQuery = PreconcepcionalImportBatch::with('user')->orderByDesc('id');

        if (!$this->isAdminUser()) {
            $batchesQuery->where('user_id', (int) Auth::id());
        }

        $batches = $batchesQuery->paginate(20);

        return view('preconcepcional.batches', compact('batches'));
    }

    public function batchShow(PreconcepcionalImportBatch $batch)
    {
        if (!$this->isAdminUser()) {
            abort_unless((int) $batch->user_id === (int) auth()->id(), 403, 'No tienes permiso para ver este lote.');
        }

        $batch->load('user');

        $registros = Preconcepcional::where('created_batch_id', $batch->id)
            ->orderByDesc('id')
            ->paginate(25);

        return view('preconcepcional.batch_show', compact('batch', 'registros'));
    }

    public function destroyBatch(PreconcepcionalImportBatch $batch)
    {
        if (!$this->isAdminUser()) {
            abort_unless((int) $batch->user_id === (int) auth()->id(), 403, 'No tienes permiso para eliminar este lote.');
        }

        $deleted = Preconcepcional::where('created_batch_id', $batch->id)->delete();
        $batch->delete();

        return redirect()
            ->route('preconcepcional.batches')
            ->with('success', "Lote #{$batch->id} eliminado. Registros borrados: {$deleted}");
    }
}
