<?php

namespace App\Http\Controllers;

use App\Exports\GesTipo3Export;
use App\Imports\GesTipo3Import;
use App\Jobs\ImportGesTipo3ExcelJob;
use App\Models\GesTipo3;
use App\Models\ImportJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class GesTipo3Controller extends Controller
{
    private function isAdminUser(): bool
    {
        return (int) (Auth::user()->usertype ?? 0) === 1;
    }

    public function showImportForm()
    {
        return view('ges_tipo3.import');
    }

    // Flujo sincronico (fallback)
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

            $importer = new GesTipo3Import((int) Auth::id(), $batchId);
            Excel::import($importer, $fullPath);
            $importer->finalize();

            $errors = $importer->getErrores();
            if (!empty($errors)) {
                GesTipo3::where('batch_verifications_id', $batchId)->delete();
                DB::table('batch_verifications')->where('id', $batchId)->delete();

                return back()->with('error', implode("<br>", array_slice($errors, 0, 120)));
            }

            return redirect()
                ->route('ges_tipo1.index')
                ->with('success', 'Datos ges_tipo3 importados correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', nl2br($e->getMessage()));
        } finally {
            @unlink($fullPath);
        }
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

            ImportGesTipo3ExcelJob::dispatch(
                (int) $jobRow->id,
                $fullPath,
                $userId,
                $token,
                (string) $request->file('file')->getClientOriginalName()
            )->onQueue((string) config('import_queues.gestantes_tipo3', 'imports_gestantes_t3'));

            return response()->json(['ok' => true, 'token' => $token]);
        } catch (\Throwable $e) {
            Log::error('GesTipo3 startAsyncImport ERROR: ' . $e->getMessage(), [
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

    public function exportTipo3(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($request->query('from'))->format('Ymd');
        $to = Carbon::parse($request->query('to'))->format('Ymd');

        return Excel::download(
            new GesTipo3Export($from, $to),
            "tipo3_created_{$from}_to_{$to}.xlsx"
        );
    }
}
