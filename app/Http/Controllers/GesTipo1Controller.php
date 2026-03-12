<?php

namespace App\Http\Controllers;

use App\Exports\GesTipo1Export;
use App\Exports\GestantesDesignerExport;
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
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class GesTipo1Controller extends Controller
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

    private function tipo1ExportColumns(): array
    {
        return [
            'id',
            'user_id',
            'tipo_de_registro',
            'consecutivo',
            'pais_de_la_nacionalidad',
            'municipio_de_residencia_habitual',
            'zona_territorial_de_residencia',
            'codigo_de_habilitacion_ips_primaria_de_la_gestante',
            'tipo_de_identificacion_de_la_usuaria',
            'no_id_del_usuario',
            'numero_carnet',
            'primer_apellido',
            'segundo_apellido',
            'primer_nombre',
            'segundo_nombre',
            'fecha_de_nacimiento',
            'codigo_pertenencia_etnica',
            'codigo_de_ocupacion',
            'codigo_nivel_educativo_de_la_gestante',
            'fecha_probable_de_parto',
            'direccion_de_residencia_de_la_gestante',
            'antecedente_hipertension_cronica',
            'antecedente_preeclampsia',
            'antecedente_diabetes',
            'antecedente_les_enfermedad_autoinmune',
            'antecedente_sindrome_metabolico',
            'antecedente_erc',
            'antecedente_trombofilia_o_trombosis_venosa_profunda',
            'antecedentes_anemia_celulas_falciformes',
            'antecedente_sepsis_durante_gestaciones_previas',
            'consumo_tabaco_durante_la_gestacion',
            'periodo_intergenesico',
            'embarazo_multiple',
            'metodo_de_concepcion',
            'created_at',
            'updated_at',
        ];
    }

    private function tipo1ExportHeadings(): array
    {
        return [
            'ID','Usuario','Tipo Registro','Consec.','Pais','Municipio','Zona','IPS Primaria',
            'Tipo ID','No ID','Carnet','1er Apellido','2do Apellido','1er Nombre','2do Nombre',
            'F. Nac.','Cod Etnico','Cod Ocupacion','Nivel Educ.','FPP','Direccion',
            'HTA','Preeclampsia','Diabetes','Autoinmune','S. Metabolico','ERC','Trombofilia',
            'Anemia','Sepsis','Tabaco','Intergenesico','Multiple','Metodo Concepcion',
            'Creado','Actualizado'
        ];
    }

    private function tipo1ExportQuery(Carbon $from, Carbon $to)
    {
        $query = DB::table('ges_tipo1')
            ->select($this->tipo1ExportColumns())
            ->whereBetween('created_at', [$from, $to]);

        if ((int) (Auth::user()->usertype ?? 0) === 2) {
            $query->where('user_id', (int) Auth::id());
        }

        return $query->orderBy('id');
    }

    private function tipo1ReportColumns(): array
    {
        return [
            'id' => ['label' => 'ID', 'db' => 'g.id'],
            'user_id' => ['label' => 'Usuario', 'db' => 'g.user_id'],
            'tipo_registro' => ['label' => 'Tipo registro', 'db' => 'g.tipo_de_registro'],
            'consecutivo' => ['label' => 'Consecutivo', 'db' => 'g.consecutivo'],
            'tipo_identificacion' => ['label' => 'Tipo ID', 'db' => 'g.tipo_de_identificacion_de_la_usuaria'],
            'no_id_del_usuario' => ['label' => 'Numero identificacion', 'db' => 'g.no_id_del_usuario'],
            'numero_carnet' => ['label' => 'Numero carnet', 'db' => 'g.numero_carnet'],
            'nombre_completo' => ['label' => 'Nombre completo', 'db' => "LTRIM(RTRIM(CONCAT(COALESCE(g.primer_nombre,''), ' ', COALESCE(g.segundo_nombre,''), ' ', COALESCE(g.primer_apellido,''), ' ', COALESCE(g.segundo_apellido,''))))"],
            'fecha_de_nacimiento' => ['label' => 'Fecha nacimiento', 'db' => 'g.fecha_de_nacimiento'],
            'fecha_probable_de_parto' => ['label' => 'FPP', 'db' => 'g.fecha_probable_de_parto'],
            'municipio' => ['label' => 'Municipio', 'db' => 'g.municipio_de_residencia_habitual'],
            'zona' => ['label' => 'Zona', 'db' => 'g.zona_territorial_de_residencia'],
            'ips_primaria' => ['label' => 'IPS primaria', 'db' => 'g.codigo_de_habilitacion_ips_primaria_de_la_gestante'],
            'direccion' => ['label' => 'Direccion', 'db' => 'g.direccion_de_residencia_de_la_gestante'],
            'hta' => ['label' => 'HTA', 'db' => 'g.antecedente_hipertension_cronica'],
            'preeclampsia' => ['label' => 'Preeclampsia', 'db' => 'g.antecedente_preeclampsia'],
            'diabetes' => ['label' => 'Diabetes', 'db' => 'g.antecedente_diabetes'],
            'embarazo_multiple' => ['label' => 'Embarazo multiple', 'db' => 'g.embarazo_multiple'],
            'metodo_de_concepcion' => ['label' => 'Metodo concepcion', 'db' => 'g.metodo_de_concepcion'],
            'seguimiento_estado' => ['label' => 'Estado seguimiento', 'db' => "CASE WHEN EXISTS (SELECT 1 FROM ges_tipo1_seguimientos s WHERE s.ges_tipo1_id = g.id) THEN 'Con seguimiento' ELSE 'Sin seguimiento' END"],
            'created_at' => ['label' => 'Creado', 'db' => 'g.created_at'],
            'updated_at' => ['label' => 'Actualizado', 'db' => 'g.updated_at'],
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

    private function tipo1ReportBaseQuery(Request $request)
    {
        [$from, $to] = $this->exportBounds($request);

        $query = DB::table('ges_tipo1 as g')
            ->whereBetween('g.created_at', [$from, $to]);

        if ((int) (Auth::user()->usertype ?? 0) === 2) {
            $query->where('g.user_id', (int) Auth::id());
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
            )->onQueue((string) config('import_queues.gestantes_tipo1', 'imports_gestantes_t1'));

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
                    $bracelet = route('ges_tipo1.bracelet', [
                        'ges' => $row->id,
                        'token' => $this->braceletToken($row),
                    ]);
                    $statusBadge = !empty($row->last_seg_id)
                        ? '<span class="badge badge-success gest-action-badge">Con seguimiento</span>'
                        : '<span class="badge badge-secondary gest-action-badge">Sin seguimiento</span>';

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

                        $seguimientoItem = <<<HTML
<button class="dropdown-item text-muted" type="button" disabled>
  <i class="fas fa-notes-medical mr-2"></i>Seguimiento ya registrado
</button>
HTML;

                        $editItem = <<<HTML
<a href="{$editUrl}" class="dropdown-item" title="Editar ultimo seguimiento">
  <i class="fas fa-edit mr-2 text-warning"></i>Editar seguimiento
</a>
HTML;
                    } else {
                        $seguimientoItem = <<<HTML
<a href="{$seguimientoCreate}" class="dropdown-item" title="Nuevo seguimiento">
  <i class="fas fa-notes-medical mr-2 text-primary"></i>Crear seguimiento
</a>
HTML;

                        $editItem = <<<HTML
<button class="dropdown-item text-muted" type="button" disabled>
  <i class="fas fa-edit mr-2"></i>Editar seguimiento
</button>
HTML;
                    }

                    return <<<HTML
<div class="gest-action-menu">
  {$statusBadge}
  <div class="dropdown">
    <button class="btn btn-sm gest-dropdown-toggle dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fas fa-ellipsis-h mr-1"></i> Acciones
    </button>
    <div class="dropdown-menu dropdown-menu-right gest-dropdown-menu">
      <a href="{$show}" class="dropdown-item">
        <i class="fas fa-eye mr-2 text-info"></i>Ver detalle
      </a>
      <a href="{$bracelet}" class="dropdown-item" target="_blank" rel="noopener">
        <i class="fas fa-qrcode mr-2 text-danger"></i>Vista pulsera
      </a>
      {$seguimientoItem}
      {$editItem}
    </div>
  </div>
</div>
HTML;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        $tipo1Columns = $this->tipo1ReportColumns();
        $tipo3Columns = [
                'id' => ['label' => 'ID'],
                'user_id' => ['label' => 'Usuario'],
                'ges_tipo1_id' => ['label' => 'Gestante'],
                'nombre_completo' => ['label' => 'Nombre completo'],
                'tipo_identificacion' => ['label' => 'Tipo ID'],
                'no_id_del_usuario' => ['label' => 'Numero identificacion'],
                'fecha_tecnologia_en_salud' => ['label' => 'Fecha tecnologia'],
                'cups' => ['label' => 'CUPS'],
                'finalidad' => ['label' => 'Finalidad'],
                'riesgo_gestacional' => ['label' => 'Riesgo gestacional'],
                'riesgo_preeclampsia' => ['label' => 'Riesgo preeclampsia'],
                'asa' => ['label' => 'ASA'],
                'acido_folico' => ['label' => 'Acido folico'],
                'sulfato_ferroso' => ['label' => 'Sulfato ferroso'],
                'calcio' => ['label' => 'Calcio'],
                'pas' => ['label' => 'PAS'],
                'pad' => ['label' => 'PAD'],
                'imc' => ['label' => 'IMC'],
                'hemoglobina' => ['label' => 'Hemoglobina'],
                'ipau' => ['label' => 'IPAU'],
                'created_at' => ['label' => 'Creado'],
                'updated_at' => ['label' => 'Actualizado'],
            ];

        return view('ges_tipo1.index', [
            'tipo1ReportColumns' => $tipo1Columns,
            'tipo1DefaultReportColumns' => array_keys($tipo1Columns),
            'tipo3ReportColumns' => $tipo3Columns,
            'tipo3DefaultReportColumns' => array_keys($tipo3Columns),
        ]);
    }

    public function reportPreview(Request $request)
    {
        abort_if(!auth()->check(), 401);

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $catalog = $this->tipo1ReportColumns();
        $defaults = array_keys($catalog);
        $columns = $this->normalizeReportColumns($request->input('columns', []), array_keys($catalog), $defaults);

        $rows = $this->tipo1ReportBaseQuery($request)
            ->select($this->buildReportSelects($columns, $catalog))
            ->orderByDesc('g.created_at')
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

        $catalog = $this->tipo1ReportColumns();
        $defaults = array_keys($catalog);
        $columns = $this->normalizeReportColumns($request->input('columns', []), array_keys($catalog), $defaults);
        $headings = array_map(fn ($column) => $catalog[$column]['label'], $columns);
        $format = $this->exportFormat($request);
        [$from, $to] = $this->exportBounds($request);
        $fileBase = 'gestantes_tipo2_reporte_disenado_' . $from->format('Ymd') . '_a_' . $to->format('Ymd');

        $query = $this->tipo1ReportBaseQuery($request)
            ->select($this->buildReportSelects($columns, $catalog))
            ->orderByDesc('g.created_at');

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

    /**
     * Mostrar el detalle de una gestante + sus registros relacionados.
     */
    public function show(\App\Models\GesTipo1 $ges)
    {
        $expediente = $this->buildExpedienteData($ges);
        $segs = $ges->seguimientos->sortByDesc('id')->values();
        $ultimo = $segs->first();

        return view('ges_tipo1.show', [
            'gestante' => $ges,
            'segs' => $segs,
            'ultimo' => $ultimo,
            'tipo3' => $ges->tipo3 ?? collect(),
            'segsCount' => $segs->count(),
            'expediente' => $expediente,
        ]);
    }

    public function bracelet(\App\Models\GesTipo1 $ges, string $token)
    {
        abort_unless(hash_equals($this->braceletToken($ges), $token), 403);

        $expediente = $this->buildExpedienteData($ges);
        $segs = $ges->seguimientos->sortByDesc('id')->values();
        $tipo3 = $ges->tipo3->sortByDesc('id')->values();
        $ultimoSeg = $segs->first();
        $ultimoTipo3 = $tipo3->first();

        return view('ges_tipo1.bracelet', [
            'gestante' => $ges,
            'expediente' => $expediente,
            'ultimoSeg' => $ultimoSeg,
            'ultimoTipo3' => $ultimoTipo3,
            'alertas' => collect($expediente['alertas'] ?? [])->take(5),
            'preconcepcional' => collect($expediente['preconcepcional'] ?? [])->first(),
            'sivigila' => collect($expediente['sivigila'] ?? [])->take(3),
            'maestro549' => collect($expediente['maestro549'] ?? [])->take(3),
        ]);
    }

    private function buildExpedienteData(\App\Models\GesTipo1 $ges): array
    {
        $ges->loadMissing(['seguimientos', 'tipo3']);
        $segs = $ges->seguimientos->sortByDesc('id')->values();
        $documento = $this->normalizeIdentifier($ges->no_id_del_usuario);
        $tipoDocumento = trim((string) ($ges->tipo_de_identificacion_de_la_usuaria ?? ''));

        $preconcepcional = \App\Models\Preconcepcional::query()
            ->when($tipoDocumento !== '', fn ($q) => $q->where('tipo_documento', $tipoDocumento))
            ->where('numero_identificacion', $documento)
            ->orderByDesc('id')
            ->get();

        $sivigila = \App\Models\Sivigila::query()
            ->when($tipoDocumento !== '', fn ($q) => $q->where('tip_ide_', $tipoDocumento))
            ->where('num_ide_', $documento)
            ->orderByDesc('fec_not')
            ->orderByDesc('id')
            ->get();

        $maestro549 = \App\Models\MaestroSiv549::query()
            ->when($tipoDocumento !== '', fn ($q) => $q->where('tip_ide_', $tipoDocumento))
            ->where('num_ide_', $documento)
            ->orderByDesc('fec_not')
            ->get();

        $asignaciones549 = \App\Models\AsignacionesMaestrosiv549::query()
            ->with(['seguimientosMaestrosiv549' => fn ($q) => $q->orderByDesc('id')])
            ->when($tipoDocumento !== '', fn ($q) => $q->where('tip_ide_', $tipoDocumento))
            ->where('num_ide_', $documento)
            ->orderByDesc('id')
            ->get();

        $alertas = \App\Models\GestanteAlerta::query()
            ->where('ges_tipo1_id', $ges->id)
            ->orderByDesc('id')
            ->get();

        $otrosRegistrosTipo2 = \App\Models\GesTipo1::query()
            ->where('no_id_del_usuario', $documento)
            ->where('id', '<>', $ges->id)
            ->orderByDesc('id')
            ->get();

        $qrTarget = route('ges_tipo1.bracelet', [
            'ges' => $ges->id,
            'token' => $this->braceletToken($ges),
        ]);

        return [
            'paciente' => [
                'nombre' => trim(implode(' ', array_filter([
                    $ges->primer_nombre,
                    $ges->segundo_nombre,
                    $ges->primer_apellido,
                    $ges->segundo_apellido,
                ]))),
                'tipo_documento' => $tipoDocumento ?: 'Sin dato',
                'documento' => $documento ?: 'Sin dato',
                'fpp' => $this->formatValue($ges->fecha_probable_de_parto),
                'fecha_nacimiento' => $this->formatValue($ges->fecha_de_nacimiento),
            ],
            'resumen' => [
                ['label' => 'Registros Tipo 2', 'value' => 1 + $otrosRegistrosTipo2->count()],
                ['label' => 'Seguimientos Tipo 2', 'value' => $segs->count()],
                ['label' => 'Registros Tipo 3', 'value' => $ges->tipo3->count()],
                ['label' => 'Alertas', 'value' => $alertas->count()],
                ['label' => 'Preconcepcional', 'value' => $preconcepcional->count()],
                ['label' => 'Sivigila', 'value' => $sivigila->count()],
                ['label' => 'SIV 549', 'value' => $maestro549->count()],
                ['label' => 'Seguimientos SIV 549', 'value' => $asignaciones549->sum(fn ($row) => $row->seguimientosMaestrosiv549->count())],
            ],
            'gestanteFicha' => $this->buildSectionCards($ges, [
                'Identificacion y residencia' => [
                    'tipo_de_registro', 'consecutivo', 'tipo_de_identificacion_de_la_usuaria',
                    'no_id_del_usuario', 'numero_carnet', 'primer_nombre', 'segundo_nombre',
                    'primer_apellido', 'segundo_apellido', 'fecha_de_nacimiento',
                    'pais_de_la_nacionalidad', 'municipio_de_residencia_habitual',
                    'zona_territorial_de_residencia', 'direccion_de_residencia_de_la_gestante',
                    'codigo_de_habilitacion_ips_primaria_de_la_gestante', 'fecha_probable_de_parto',
                ],
                'Antecedentes y factores de riesgo' => [
                    'codigo_pertenencia_etnica', 'codigo_de_ocupacion',
                    'codigo_nivel_educativo_de_la_gestante', 'antecedente_hipertension_cronica',
                    'antecedente_preeclampsia', 'antecedente_diabetes',
                    'antecedente_les_enfermedad_autoinmune', 'antecedente_sindrome_metabolico',
                    'antecedente_erc', 'antecedente_trombofilia_o_trombosis_venosa_profunda',
                    'antecedentes_anemia_celulas_falciformes',
                    'antecedente_sepsis_durante_gestaciones_previas',
                    'consumo_tabaco_durante_la_gestacion', 'periodo_intergenesico',
                    'embarazo_multiple', 'metodo_de_concepcion', 'created_at', 'updated_at',
                ],
            ]),
            'otrosTipo2' => $otrosRegistrosTipo2->map(fn ($row) => [
                'title' => 'Registro Tipo 2 #' . $row->id,
                'subtitle' => 'Creado: ' . $this->formatValue($row->created_at),
                'values' => $this->presentRecord($row, ['id'], [
                    'tipo_de_registro', 'consecutivo', 'fecha_probable_de_parto',
                    'municipio_de_residencia_habitual', 'codigo_de_habilitacion_ips_primaria_de_la_gestante',
                ]),
            ])->values()->all(),
            'seguimientos' => $segs->map(function ($seg, $index) {
                return [
                    'title' => 'Seguimiento #' . ($index + 1),
                    'subtitle' => 'Fecha seguimiento: ' . $this->formatValue($seg->fecha_seguimiento),
                    'values' => $this->presentRecord($seg, ['id', 'ges_tipo1_id', 'user_id', 'created_at', 'updated_at']),
                ];
            })->values()->all(),
            'tipo3' => $ges->tipo3->sortByDesc('id')->values()->map(function ($row, $index) {
                return [
                    'title' => 'Registro Tipo 3 #' . ($index + 1),
                    'subtitle' => 'Fecha tecnologia: ' . $this->formatValue($row->fecha_tecnologia_en_salud),
                    'values' => $this->presentRecord($row, ['id', 'ges_tipo1_id', 'user_id', 'batch_verifications_id']),
                ];
            })->values()->all(),
            'alertas' => $alertas->map(function ($row, $index) {
                return [
                    'title' => 'Alerta #' . ($index + 1),
                    'subtitle' => 'Modulo: ' . ($row->modulo ?: 'Sin dato'),
                    'values' => $this->presentRecord($row, ['id', 'user_id', 'ges_tipo1_id', 'seguimiento_id', 'hash']),
                ];
            })->values()->all(),
            'preconcepcional' => $preconcepcional->map(function ($row, $index) {
                return [
                    'title' => 'Preconcepcional #' . ($index + 1),
                    'subtitle' => 'Riesgo: ' . ($row->riesgo_preconcepcional ?: 'Sin dato'),
                    'values' => $this->presentRecord($row, ['id', 'created_batch_id', 'last_batch_id']),
                ];
            })->values()->all(),
            'sivigila' => $sivigila->map(function ($row, $index) {
                return [
                    'title' => 'Sivigila #' . ($index + 1),
                    'subtitle' => 'Evento: ' . ($row->cod_eve ?: 'Sin dato'),
                    'values' => $this->presentRecord($row, ['id', 'user_id']),
                ];
            })->values()->all(),
            'maestro549' => $maestro549->map(function ($row, $index) {
                return [
                    'title' => 'Caso SIV 549 #' . ($index + 1),
                    'subtitle' => 'Evento: ' . (($row->nom_eve ?? '') ?: 'Sin dato'),
                    'values' => $this->presentRecord($row, ['nreg']),
                ];
            })->values()->all(),
            'asignaciones549' => $asignaciones549->map(function ($row, $index) {
                return [
                    'title' => 'Asignacion SIV 549 #' . ($index + 1),
                    'subtitle' => 'UPGD: ' . (($row->nom_upgd ?? '') ?: 'Sin dato'),
                    'values' => $this->presentRecord($row, ['id', 'user_id']),
                    'seguimientos' => $row->seguimientosMaestrosiv549->map(function ($seg, $segIndex) {
                        return [
                            'title' => 'Seguimiento SIV 549 #' . ($segIndex + 1),
                            'subtitle' => 'Fecha hospitalizacion: ' . $this->formatValue($seg->fecha_hospitalizacion),
                            'values' => $this->presentRecord($seg, ['id', 'asignacion_id', 'created_at', 'updated_at']),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
            'qr' => [
                'target' => $qrTarget,
                'caption' => 'Pulsera clinica segura',
                'payload' => $qrTarget,
            ],
            'generado' => now()->format('d/m/Y H:i'),
        ];
    }

    private function braceletToken(\App\Models\GesTipo1 $ges): string
    {
        $payload = implode('|', [
            'gestante-bracelet',
            (string) $ges->id,
            $this->normalizeIdentifier($ges->no_id_del_usuario),
            trim((string) ($ges->fecha_probable_de_parto ?? '')),
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    private function normalizeIdentifier($value): string
    {
        return trim((string) $value);
    }

    private function buildSectionCards($record, array $sections): array
    {
        $cards = [];

        foreach ($sections as $title => $fields) {
            $cards[] = [
                'title' => $title,
                'values' => $this->presentRecord($record, [], $fields),
            ];
        }

        return $cards;
    }

    private function presentRecord($record, array $exclude = [], array $priority = []): array
    {
        $array = is_array($record) ? $record : $record->toArray();
        $exclude = array_flip($exclude);
        $values = [];

        foreach ($priority as $field) {
            if (array_key_exists($field, $array) && !isset($exclude[$field]) && $this->hasDisplayValue($array[$field] ?? null)) {
                $values[] = [
                    'field' => $field,
                    'label' => $this->humanLabel($field),
                    'value' => $this->formatValue($array[$field]),
                ];
            }
        }

        foreach ($array as $field => $value) {
            if (isset($exclude[$field]) || in_array($field, $priority, true) || !$this->hasDisplayValue($value)) {
                continue;
            }

            $values[] = [
                'field' => $field,
                'label' => $this->humanLabel($field),
                'value' => $this->formatValue($value),
            ];
        }

        return $values;
    }

    private function hasDisplayValue($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value) || is_object($value)) {
            return false;
        }

        return true;
    }

    private function formatValue($value): string
    {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Si' : 'No';
        }

        if (is_numeric($value) && in_array((string) $value, ['0', '1'], true)) {
            return (string) $value === '1' ? 'Si' : 'No';
        }

        return trim((string) $value);
    }

    private function humanLabel(string $field): string
    {
        $custom = [
            'no_id_del_usuario' => 'Numero de identificacion',
            'tipo_de_identificacion_de_la_usuaria' => 'Tipo de identificacion',
            'codigo_de_habilitacion_ips_primaria_de_la_gestante' => 'IPS primaria',
            'fecha_probable_de_parto' => 'Fecha probable de parto',
            'codigo_nivel_educativo_de_la_gestante' => 'Nivel educativo',
            'direccion_de_residencia_de_la_gestante' => 'Direccion',
            'fecha_tecnologia_en_salud' => 'Fecha de tecnologia en salud',
            'codigo_cups_de_la_tecnologia_en_salud' => 'Codigo CUPS',
            'clasificacion_riesgo_gestacional' => 'Clasificacion riesgo gestacional',
            'clasificacion_riesgo_preeclampsia' => 'Clasificacion riesgo preeclampsia',
            'numero_identificacion' => 'Numero de identificacion',
            'tipo_documento' => 'Tipo de documento',
            'num_ide_' => 'Numero de identificacion',
            'tip_ide_' => 'Tipo de identificacion',
            'fec_not' => 'Fecha de notificacion',
            'fecha_nto_' => 'Fecha de nacimiento',
            'telefono_' => 'Telefono',
            'dir_res_' => 'Direccion de residencia',
            'nom_eve' => 'Nombre del evento',
            'nom_upgd' => 'UPGD',
            'ttl_criter' => 'Total criterios',
        ];

        return $custom[$field] ?? Str::headline(str_replace('_', ' ', $field));
    }

    /**
     * Exportar a Excel rango por created_at
     */
    public function exportTipo1(Request $request)
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
                new GesTipo1Export($from->toDateString(), $to->toDateString()),
                "gestantes_created_{$fromLabel}_to_{$toLabel}.xlsx"
            );
        }

        $query = $this->tipo1ExportQuery($from, $to);

        if ($format === 'json') {
            return $this->streamJsonFile("gestantes_created_{$fromLabel}_to_{$toLabel}.json", $query);
        }

        $delimiter = $format === 'tsv' ? "\t" : ',';
        return $this->streamSeparatedFile(
            "gestantes_created_{$fromLabel}_to_{$toLabel}.{$format}",
            $this->tipo1ExportHeadings(),
            $query,
            $delimiter
        );
    }
}

