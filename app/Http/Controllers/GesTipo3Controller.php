<?php

namespace App\Http\Controllers;

use App\Exports\GesTipo3Export;
use App\Exports\GestantesDesignerExport;
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
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class GesTipo3Controller extends Controller
{
    private const EXPORT_FORMATS = ['xlsx', 'csv', 'tsv', 'json'];

    private function isAdminUser(): bool
    {
        return (int) (Auth::user()->usertype ?? 0) === 1;
    }

    private function exportFormat(Request $request): string
    {
        $format = strtolower((string) $request->input('format', 'csv'));
        return in_array($format, self::EXPORT_FORMATS, true) ? $format : 'csv';
    }

    private function exportBounds(Request $request): array
    {
        $from = Carbon::parse($request->input('from'))->startOfDay();
        $to = Carbon::parse($request->input('to'))->endOfDay();

        return [$from, $to];
    }

    private function tipo3ExportColumns(): array
    {
        return [
            'id',
            'user_id',
            'ges_tipo1_id',
            'tipo_de_registro',
            'consecutivo_de_registro',
            'tipo_identificacion_de_la_usuaria',
            'no_id_del_usuario',
            'fecha_tecnologia_en_salud',
            'codigo_cups_de_la_tecnologia_en_salud',
            'finalidad_de_la_tecnologia_en_salud',
            'clasificacion_riesgo_gestacional',
            'clasificacion_riesgo_preeclampsia',
            'suministro_acido_acetilsalicilico_ASA',
            'suministro_acido_folico_en_el_control_prenatal',
            'suministro_sulfato_ferroso_en_el_control_prenatal',
            'suministro_calcio_en_el_control_prenatal',
            'fecha_suministro_de_anticonceptivo_post_evento_obstetrico',
            'suministro_metodo_anticonceptivo_post_evento_obstetrico',
            'fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea',
            'fecha_de_terminacion_de_la_gestacion',
            'tipo_de_terminacion_de_la_gestacion',
            'tension_arterial_sistolica_PAS_mmHg',
            'tension_arterial_diastolica_PAD_mmHg',
            'indice_de_masa_corporal',
            'resultado_de_la_hemoglobina',
            'indice_de_pulsatilidad_de_arterias_uterinas',
            'created_at',
            'updated_at',
        ];
    }

    private function tipo3ExportHeadings(): array
    {
        return [
            'ID','Usuario','Gestante','Tipo Reg.','Consec.','Tipo ID','No ID','F. Tec.','CUPS','Finalidad',
            'Riesgo Gest.','Riesgo Pree.','ASA','Ac. Folico','Ferroso','Calcio','F. Post','Met. Post',
            'F. Salida','F. Term.','Tipo Term.','PAS','PAD','IMC','Hb','IPAU','Creado','Actualizado'
        ];
    }

    private function tipo3ExportQuery(Carbon $from, Carbon $to)
    {
        $query = DB::table('ges_tipo3')
            ->select($this->tipo3ExportColumns())
            ->whereBetween('created_at', [$from, $to]);

        if ((int) (Auth::user()->usertype ?? 0) === 2) {
            $query->where('user_id', (int) Auth::id());
        }

        return $query->orderBy('id');
    }

    private function tipo3ReportColumns(): array
    {
        return [
            'id' => ['label' => 'ID', 'db' => 't3.id'],
            'user_id' => ['label' => 'Usuario', 'db' => 't3.user_id'],
            'ges_tipo1_id' => ['label' => 'Gestante', 'db' => 't3.ges_tipo1_id'],
            'nombre_completo' => ['label' => 'Nombre completo', 'db' => "LTRIM(RTRIM(CONCAT(COALESCE(t1.primer_nombre,''), ' ', COALESCE(t1.segundo_nombre,''), ' ', COALESCE(t1.primer_apellido,''), ' ', COALESCE(t1.segundo_apellido,''))))"],
            'tipo_identificacion' => ['label' => 'Tipo ID', 'db' => 't3.tipo_identificacion_de_la_usuaria'],
            'no_id_del_usuario' => ['label' => 'Numero identificacion', 'db' => 't3.no_id_del_usuario'],
            'fecha_tecnologia_en_salud' => ['label' => 'Fecha tecnologia', 'db' => 't3.fecha_tecnologia_en_salud'],
            'cups' => ['label' => 'CUPS', 'db' => 't3.codigo_cups_de_la_tecnologia_en_salud'],
            'finalidad' => ['label' => 'Finalidad', 'db' => 't3.finalidad_de_la_tecnologia_en_salud'],
            'riesgo_gestacional' => ['label' => 'Riesgo gestacional', 'db' => 't3.clasificacion_riesgo_gestacional'],
            'riesgo_preeclampsia' => ['label' => 'Riesgo preeclampsia', 'db' => 't3.clasificacion_riesgo_preeclampsia'],
            'asa' => ['label' => 'ASA', 'db' => 't3.suministro_acido_acetilsalicilico_ASA'],
            'acido_folico' => ['label' => 'Acido folico', 'db' => 't3.suministro_acido_folico_en_el_control_prenatal'],
            'sulfato_ferroso' => ['label' => 'Sulfato ferroso', 'db' => 't3.suministro_sulfato_ferroso_en_el_control_prenatal'],
            'calcio' => ['label' => 'Calcio', 'db' => 't3.suministro_calcio_en_el_control_prenatal'],
            'pas' => ['label' => 'PAS', 'db' => 't3.tension_arterial_sistolica_PAS_mmHg'],
            'pad' => ['label' => 'PAD', 'db' => 't3.tension_arterial_diastolica_PAD_mmHg'],
            'imc' => ['label' => 'IMC', 'db' => 't3.indice_de_masa_corporal'],
            'hemoglobina' => ['label' => 'Hemoglobina', 'db' => 't3.resultado_de_la_hemoglobina'],
            'ipau' => ['label' => 'IPAU', 'db' => 't3.indice_de_pulsatilidad_de_arterias_uterinas'],
            'created_at' => ['label' => 'Creado', 'db' => 't3.created_at'],
            'updated_at' => ['label' => 'Actualizado', 'db' => 't3.updated_at'],
        ];
    }

    private function normalizeReportColumns($requested, array $allowed, array $defaults): array
    {
        if (!is_array($requested)) {
            return $defaults;
        }

        $filtered = array_values(array_filter($requested, fn ($column) => in_array($column, $allowed, true)));
        return !empty($filtered) ? $filtered : $defaults;
    }

    private function tipo3ReportBaseQuery(Request $request)
    {
        [$from, $to] = $this->exportBounds($request);

        $query = DB::table('ges_tipo3 as t3')
            ->leftJoin('ges_tipo1 as t1', 't1.id', '=', 't3.ges_tipo1_id')
            ->whereBetween('t3.created_at', [$from, $to]);

        if ((int) (Auth::user()->usertype ?? 0) === 2) {
            $query->where('t3.user_id', (int) Auth::id());
        }

        return $query;
    }

    private function buildReportSelects(array $columns, array $catalog): array
    {
        $selects = [];
        foreach ($columns as $key) {
            if (!empty($catalog[$key]['db'])) {
                $selects[] = DB::raw($catalog[$key]['db'] . ' as [' . $key . ']');
            }
        }

        return $selects;
    }

    private function streamReportSeparatedFile(string $filename, array $headings, array $columns, $query, string $delimiter)
    {
        return response()->streamDownload(function () use ($headings, $columns, $query, $delimiter) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(239) . chr(187) . chr(191));
            fputcsv($out, $headings, $delimiter);

            $query->chunk(1000, function ($rows) use ($out, $columns, $delimiter) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($columns as $column) {
                        $line[] = $row->{$column} ?? '';
                    }
                    fputcsv($out, $line, $delimiter);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => $delimiter === "\t" ? 'text/tab-separated-values; charset=UTF-8' : 'text/csv; charset=UTF-8',
        ]);
    }

    private function streamReportJsonFile(string $filename, array $columns, $query)
    {
        return response()->streamDownload(function () use ($columns, $query) {
            echo '[';
            $first = true;

            $query->chunk(1000, function ($rows) use (&$first, $columns) {
                foreach ($rows as $row) {
                    $payload = [];
                    foreach ($columns as $column) {
                        $payload[$column] = $row->{$column} ?? '';
                    }

                    if (!$first) {
                        echo ',';
                    }
                    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $first = false;
                }
            });

            echo ']';
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    private function streamSeparatedFile(string $filename, array $headings, $query, string $delimiter)
    {
        return response()->streamDownload(function () use ($headings, $query, $delimiter) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(239) . chr(187) . chr(191));
            fputcsv($out, $headings, $delimiter);

            $query->chunk(1000, function ($rows) use ($out, $delimiter) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(fn($value) => $value, (array) $row), $delimiter);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => $delimiter === "\t" ? 'text/tab-separated-values; charset=UTF-8' : 'text/csv; charset=UTF-8',
        ]);
    }

    private function streamJsonFile(string $filename, $query)
    {
        return response()->streamDownload(function () use ($query) {
            echo '[';
            $first = true;

            $query->chunk(1000, function ($rows) use (&$first) {
                foreach ($rows as $row) {
                    if (!$first) {
                        echo ',';
                    }
                    echo json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $first = false;
                }
            });

            echo ']';
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
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

    public function reportPreview(Request $request)
    {
        abort_if(!auth()->check(), 401);

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $catalog = $this->tipo3ReportColumns();
        $defaults = array_keys($catalog);
        $columns = $this->normalizeReportColumns($request->input('columns', []), array_keys($catalog), $defaults);

        $rows = $this->tipo3ReportBaseQuery($request)
            ->select($this->buildReportSelects($columns, $catalog))
            ->orderByDesc('t3.created_at')
            ->limit(25)
            ->get()
            ->map(function ($row) use ($columns) {
                $data = [];
                foreach ($columns as $column) {
                    $data[$column] = $row->{$column} ?? '';
                }
                return $data;
            })
            ->values();

        $headings = [];
        foreach ($columns as $column) {
            $headings[$column] = $catalog[$column]['label'];
        }

        return response()->json([
            'ok' => true,
            'columns' => $columns,
            'headings' => $headings,
            'rows' => $rows,
        ]);
    }

    public function reportExport(Request $request)
    {
        abort_if(!auth()->check(), 401);

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'format' => 'nullable|in:xlsx,csv,tsv,json',
        ]);

        $catalog = $this->tipo3ReportColumns();
        $defaults = array_keys($catalog);
        $columns = $this->normalizeReportColumns($request->input('columns', []), array_keys($catalog), $defaults);
        $headings = array_map(fn ($column) => $catalog[$column]['label'], $columns);
        $format = $this->exportFormat($request);
        [$from, $to] = $this->exportBounds($request);
        $fileBase = 'gestantes_tipo3_reporte_disenado_' . $from->format('Ymd') . '_a_' . $to->format('Ymd');

        $query = $this->tipo3ReportBaseQuery($request)
            ->select($this->buildReportSelects($columns, $catalog))
            ->orderByDesc('t3.created_at');

        if ($format === 'xlsx') {
            return Excel::download(
                new GestantesDesignerExport($query, $headings, $columns),
                $fileBase . '.xlsx',
                ExcelFormat::XLSX
            );
        }

        if ($format === 'json') {
            return $this->streamReportJsonFile($fileBase . '.json', $columns, $query);
        }

        return $this->streamReportSeparatedFile(
            $fileBase . '.' . $format,
            $headings,
            $columns,
            $query,
            $format === 'tsv' ? "\t" : ','
        );
    }

    public function exportTipo3(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'format' => 'nullable|in:xlsx,csv,tsv,json',
        ]);

        [$from, $to] = $this->exportBounds($request);
        $format = $this->exportFormat($request);
        $fromLabel = $from->format('Ymd');
        $toLabel = $to->format('Ymd');

        if ($format === 'xlsx') {
            return Excel::download(
                new GesTipo3Export($from->toDateString(), $to->toDateString()),
                "tipo3_created_{$fromLabel}_to_{$toLabel}.xlsx"
            );
        }

        $query = $this->tipo3ExportQuery($from, $to);

        if ($format === 'json') {
            return $this->streamJsonFile("tipo3_created_{$fromLabel}_to_{$toLabel}.json", $query);
        }

        $delimiter = $format === 'tsv' ? "\t" : ',';
        return $this->streamSeparatedFile(
            "tipo3_created_{$fromLabel}_to_{$toLabel}.{$format}",
            $this->tipo3ExportHeadings(),
            $query,
            $delimiter
        );
    }
}
