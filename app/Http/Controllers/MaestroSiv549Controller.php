<?php

namespace App\Http\Controllers;

use App\Exports\MaestroSiv549DesignerExport;
use App\Exports\MaestroSiv549Export;
use App\Models\AsignacionesMaestrosiv549;
use App\Models\MaestroSiv549;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class MaestroSiv549Controller extends Controller
{
    public function index()
    {
        $years = $this->distinctResolvedYears(20);
        $defaultYear = (string) now()->year;

        if (!in_array($defaultYear, $years, true)) {
            array_unshift($years, $defaultYear);
            $years = array_values(array_unique($years));
        }

        $availableQuery = $this->latestGestanteNotificationPerYearQuery()
            ->where('year_resolved', $defaultYear);

        $assignmentKey = DB::raw("
            CONCAT(
                LTRIM(RTRIM(ISNULL(CAST([tip_ide_] AS NVARCHAR(50)), ''))),
                '|',
                LTRIM(RTRIM(ISNULL(CAST([num_ide_] AS NVARCHAR(100)), ''))),
                '|',
                LTRIM(RTRIM(ISNULL(CAST([year] AS NVARCHAR(10)), '')))
            )
        ");

        $stats = [
            'total' => (clone $availableQuery)->count(),
            'asignados' => AsignacionesMaestrosiv549::query()
                ->where('year', $defaultYear)
                ->distinct()
                ->count($assignmentKey),
            'sin_seguimientos' => AsignacionesMaestrosiv549::query()
                ->where('year', $defaultYear)
                ->doesntHave('seguimientosMaestrosiv549')
                ->distinct()
                ->count($assignmentKey),
            'semanas' => (clone $availableQuery)
                ->whereNotNull('semana')
                ->where('semana', '<>', '')
                ->distinct('semana')
                ->count('semana'),
        ];

        $filterOptions = [
            'years' => $years,
            'weeks' => $this->distinctOptions('semana', 53, true),
            'tiposId' => $this->distinctOptions('tip_ide_', 20),
            'sexos' => $this->distinctOptions('sexo_', 10),
            'eventos' => $this->distinctOptions('nom_eve', 120),
            'areas' => $this->distinctOptions('area_', 10),
            'tiposCaso' => $this->distinctOptions('tip_cas_', 20),
            'aseguramientos' => $this->distinctOptions('tip_ss_', 20),
            'etnias' => $this->distinctOptions('nom_grupo_', 50),
            'municipiosResi' => $this->distinctOptions('nmun_resi', 100),
            'municipiosNotif' => $this->distinctOptions('nmun_notif', 100),
            'upgd' => $this->distinctOptions('nom_upgd', 120),
        ];

        $reportColumns = $this->reportColumnCatalog();
        $defaultReportColumns = [
            'fec_not',
            'year',
            'semana',
            'tip_ide_',
            'num_ide_',
            'nombre_completo',
            'edad_',
            'sexo_',
            'telefono_',
            'nmun_resi',
            'nom_upgd',
            'nom_eve',
        ];

        return view('maestrosiv549.index', compact(
            'stats',
            'filterOptions',
            'reportColumns',
            'defaultReportColumns',
            'defaultYear'
        ));
    }

    public function data(Request $request)
    {
        $canAssign = auth()->check() && (int) (auth()->user()->usertype ?? 0) === 1;
        $assignedIds = AsignacionesMaestrosiv549::query()
            ->pluck('num_ide_')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $query = $this->buildFilteredQuery($request)->select([
            'tip_ide_',
            'num_ide_',
            'pri_nom_',
            'seg_nom_',
            'pri_ape_',
            'seg_ape_',
            'edad_',
            'sexo_',
            'fec_not',
            'fec_not_ultima',
            'fec_not_sort',
            'semana',
            'year_resolved as year',
            'ocupacion_',
            'telefono_',
            'dir_res_',
            'nom_eve',
            'nom_upgd',
            'nmun_resi',
            'nmun_notif',
            'tip_cas_',
            'area_',
        ]);

        return DataTables::eloquent($query)
            ->filterColumn('nombre_completo', function (Builder $query, $keyword) {
                $query->where(function (Builder $subQuery) use ($keyword) {
                    $like = '%' . $keyword . '%';
                    $subQuery->where('pri_nom_', 'like', $like)
                        ->orWhere('seg_nom_', 'like', $like)
                        ->orWhere('pri_ape_', 'like', $like)
                        ->orWhere('seg_ape_', 'like', $like);
                });
            })
            ->addColumn('nombre_completo', function ($row) {
                return trim(implode(' ', array_filter([
                    $row->pri_nom_,
                    $row->seg_nom_,
                    $row->pri_ape_,
                    $row->seg_ape_,
                ])));
            })
            ->editColumn('fec_not', function ($row) {
                $raw = $row->fec_not_ultima ?? $row->fec_not ?? null;
                if (!$raw) {
                    return '';
                }

                try {
                    $fecha = Carbon::parse((string) $raw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $fecha = (string) $raw;
                }

                return $fecha . ' <span class="badge badge-info ml-1">Ultima notificacion</span>';
            })
            ->addColumn('acciones', function ($row) use ($assignedIds, $canAssign) {
                $asignado = in_array(trim((string) $row->num_ide_), $assignedIds, true);
                $url = route('asignaciones-maestrosiv549.create', [
                    'tip_ide_' => $row->tip_ide_,
                    'num_ide_' => $row->num_ide_,
                    'fec_not' => $row->fec_not,
                    'nom_eve' => $row->nom_eve,
                ]);

                $check = $asignado
                    ? '<span class="icon-action badge-checklist mr-1" title="Asignado"><i class="fas fa-check"></i></span>'
                    : '';

                $btn = $canAssign
                    ? '<a href="' . e($url) . '" class="icon-action btn-asignar" title="Asignar o reasignar este caso"><i class="fas fa-user-plus"></i></a>'
                    : '';

                return '<div class="acciones-flex">' . $check . $btn . '</div>';
            })
            ->setRowClass(function ($row) use ($assignedIds) {
                return in_array(trim((string) $row->num_ide_), $assignedIds, true) ? 'row-asignado' : '';
            })
            ->rawColumns(['fec_not', 'acciones'])
            ->make(true);
    }

    public function export(Request $request)
    {
        abort_if(!auth()->check(), 401);

        $filename = 'reporte_maestro_549_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new MaestroSiv549Export($request->all()), $filename);
    }

    public function reportPreview(Request $request)
    {
        $catalog = $this->reportColumnCatalog();
        $columns = $this->normalizeRequestedColumns($request->input('columns', []), array_keys($catalog));

        if (empty($columns)) {
            $columns = ['fec_not', 'year', 'semana', 'tip_ide_', 'num_ide_', 'nombre_completo', 'nom_eve'];
        }

        [$selects, $selectedDbKeys] = $this->buildReportSelects($columns, $catalog);
        foreach (['pri_nom_', 'seg_nom_', 'pri_ape_', 'seg_ape_'] as $namePart) {
            if (!in_array($namePart, $selectedDbKeys, true)) {
                $selects[] = DB::raw("[$namePart] as [$namePart]");
            }
        }

        $rows = $this->buildFilteredQuery($request)
            ->select($selects)
            ->orderByDesc('fec_not')
            ->limit(40)
            ->get()
            ->map(fn ($row) => $this->mapReportRow($row, $columns))
            ->values();

        $headings = [];
        foreach ($columns as $key) {
            $headings[$key] = $catalog[$key]['label'];
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

        $catalog = $this->reportColumnCatalog();
        $columns = $this->normalizeRequestedColumns($request->input('columns', []), array_keys($catalog));

        if (empty($columns)) {
            $columns = ['fec_not', 'year', 'semana', 'tip_ide_', 'num_ide_', 'nombre_completo', 'nom_eve'];
        }

        [$selects, $selectedDbKeys] = $this->buildReportSelects($columns, $catalog);
        foreach (['pri_nom_', 'seg_nom_', 'pri_ape_', 'seg_ape_'] as $namePart) {
            if (!in_array($namePart, $selectedDbKeys, true)) {
                $selects[] = DB::raw("[$namePart] as [$namePart]");
            }
        }

        $query = $this->buildFilteredQuery($request)
            ->select($selects)
            ->orderByDesc('fec_not');

        $headings = array_map(fn ($column) => $catalog[$column]['label'], $columns);
        $format = strtolower((string) $request->input('format', 'xlsx'));
        if (!in_array($format, ['xlsx', 'csv'], true)) {
            $format = 'xlsx';
        }

        $fileBase = 'maestro_siv549_reporte_disenado_' . now()->format('Ymd_His');
        $export = new MaestroSiv549DesignerExport($query, $headings, $columns);

        if ($format === 'csv') {
            return Excel::download($export, $fileBase . '.csv', ExcelFormat::CSV);
        }

        return Excel::download($export, $fileBase . '.xlsx', ExcelFormat::XLSX);
    }

    public function summaryData(Request $request)
    {
        $mode = trim((string) $request->input('mode', 'asignados'));
        $hasCreatedAt = Schema::hasColumn('asignaciones_maestrosiv549', 'created_at');

        $query = AsignacionesMaestrosiv549::query()->select([
            'id',
            'tip_ide_',
            'num_ide_',
            'pri_nom_',
            'seg_nom_',
            'pri_ape_',
            'seg_ape_',
        ]);

        if ($hasCreatedAt) {
            $query->addSelect('created_at');
        }

        if ($mode === 'sin_seguimientos') {
            $query->doesntHave('seguimientosMaestrosiv549');
        } else {
            $query->whereNotNull('num_ide_');
        }

        return DataTables::eloquent($query)
            ->addColumn('nombre_completo', function ($row) {
                return trim(implode(' ', array_filter([
                    $row->pri_nom_,
                    $row->seg_nom_,
                    $row->pri_ape_,
                    $row->seg_ape_,
                ])));
            })
            ->addColumn('fecha_asignacion', function ($row) use ($hasCreatedAt) {
                if (!$hasCreatedAt || empty($row->created_at)) {
                    return 'Sin fecha';
                }

                return optional($row->created_at)->format('Y-m-d H:i');
            })
            ->addColumn('estado_resumen', function ($row) use ($mode) {
                if ($mode === 'sin_seguimientos') {
                    return '<span class="badge badge-warning">Sin seguimiento</span>';
                }

                $count = $row->seguimientosMaestrosiv549()->count();
                if ($count > 0) {
                    return '<span class="badge badge-success">Con seguimiento</span>';
                }

                return '<span class="badge badge-secondary">Asignado</span>';
            })
            ->rawColumns(['estado_resumen'])
            ->make(true);
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        $query = $this->latestGestanteNotificationPerYearQuery();

        $this->applyFilters($query, $request);

        return $query;
    }

    private function latestGestanteNotificationPerYearQuery(): Builder
    {
        $fecNotExpr = $this->fecNotDateExpr();
        $yearFromFecNotExpr = $this->resolvedYearExpr();
        $monthFromFecNotExpr = $this->resolvedMonthExpr();

        $ranked = MaestroSiv549::query()
            ->select('maestrosiv549.*')
            ->selectRaw("
                {$fecNotExpr} AS [fec_not_sort]
            ")
            ->selectRaw("
                {$yearFromFecNotExpr} AS [year_resolved]
            ")
            ->selectRaw("
                {$monthFromFecNotExpr} AS [month_resolved]
            ")
            ->selectRaw("
                MAX({$fecNotExpr}) OVER (
                    PARTITION BY
                        LTRIM(RTRIM(ISNULL(CAST([tip_ide_] AS NVARCHAR(50)), ''))),
                        LTRIM(RTRIM(ISNULL(CAST([num_ide_] AS NVARCHAR(100)), ''))),
                        {$yearFromFecNotExpr}
                ) AS [fec_not_ultima]
            ")
            ->selectRaw("
                ROW_NUMBER() OVER (
                    PARTITION BY
                        LTRIM(RTRIM(ISNULL(CAST([tip_ide_] AS NVARCHAR(50)), ''))),
                        LTRIM(RTRIM(ISNULL(CAST([num_ide_] AS NVARCHAR(100)), ''))),
                        {$yearFromFecNotExpr}
                    ORDER BY
                        {$fecNotExpr} DESC,
                        [fec_not] DESC,
                        [nreg] DESC
                ) AS [row_num]
            ");

        return MaestroSiv549::query()
            ->fromSub($ranked, 'maestrosiv549')
            ->where('row_num', 1);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $year = trim((string) $request->input('year', ''));
        if ($year !== '') {
            $query->where('year_resolved', $year);
        }

        $equals = [
            'semana',
            'tip_ide_',
            'sexo_',
            'nom_eve',
            'area_',
            'tip_cas_',
            'tip_ss_',
            'nom_grupo_',
            'nmun_resi',
            'nmun_notif',
            'nom_upgd',
            'cod_eve',
        ];

        foreach ($equals as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '') {
                $query->where($field, $value);
            }
        }

        if ($request->filled('fec_desde')) {
            $query->whereRaw($this->fecNotDateExpr() . ' >= TRY_CONVERT(date, ?, 23)', [
                $request->input('fec_desde'),
            ]);
        }

        if ($request->filled('fec_hasta')) {
            $query->whereRaw($this->fecNotDateExpr() . ' <= TRY_CONVERT(date, ?, 23)', [
                $request->input('fec_hasta'),
            ]);
        }

        if ($request->filled('edad_desde')) {
            $query->where('edad_', '>=', $request->input('edad_desde'));
        }

        if ($request->filled('edad_hasta')) {
            $query->where('edad_', '<=', $request->input('edad_hasta'));
        }

        if ($request->filled('sem_ges_desde')) {
            $query->where('sem_ges', '>=', $request->input('sem_ges_desde'));
        }

        if ($request->filled('sem_ges_hasta')) {
            $query->where('sem_ges', '<=', $request->input('sem_ges_hasta'));
        }

        $likeFilters = [
            'num_ide_',
            'telefono_',
            'ocupacion_',
            'dir_res_',
            'pri_nom_',
            'pri_ape_',
        ];

        foreach ($likeFilters as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '') {
                $query->where($field, 'like', '%' . $value . '%');
            }
        }

        $assigned = trim((string) $request->input('asignado', ''));
        if ($assigned !== '') {
            $assignedIds = AsignacionesMaestrosiv549::query()
                ->pluck('num_ide_')
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($assigned === '1') {
                $query->whereIn('num_ide_', $assignedIds ?: ['__sin_coincidencias__']);
            }

            if ($assigned === '0') {
                if (!empty($assignedIds)) {
                    $query->whereNotIn('num_ide_', $assignedIds);
                }
            }
        }

        $quick = trim((string) $request->input('q', ''));
        if ($quick !== '') {
            $query->where(function (Builder $subQuery) use ($quick) {
                $like = '%' . $quick . '%';
                $subQuery->where('num_ide_', 'like', $like)
                    ->orWhere('tip_ide_', 'like', $like)
                    ->orWhere('pri_nom_', 'like', $like)
                    ->orWhere('seg_nom_', 'like', $like)
                    ->orWhere('pri_ape_', 'like', $like)
                    ->orWhere('seg_ape_', 'like', $like)
                    ->orWhere('telefono_', 'like', $like)
                    ->orWhere('nom_eve', 'like', $like)
                    ->orWhere('nom_upgd', 'like', $like)
                    ->orWhere('nmun_resi', 'like', $like)
                    ->orWhere('nmun_notif', 'like', $like)
                    ->orWhere('dir_res_', 'like', $like);
            });
        }
    }

    private function distinctOptions(string $column, int $limit = 60, bool $desc = false): array
    {
        $query = MaestroSiv549::query()
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->limit($limit);

        $query = $desc ? $query->orderByDesc($column) : $query->orderBy($column);

        return $query->pluck($column)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function fecNotDateExpr(): string
    {
        return "COALESCE(
            TRY_CONVERT(date, [fec_not], 120),
            TRY_CONVERT(date, [fec_not], 103),
            TRY_CONVERT(date, [fec_not], 23)
        )";
    }

    private function resolvedYearExpr(): string
    {
        return "COALESCE(CONVERT(varchar(4), YEAR(" . $this->fecNotDateExpr() . ")), '')";
    }

    private function resolvedMonthExpr(): string
    {
        return "COALESCE(RIGHT('00' + CONVERT(varchar(2), MONTH(" . $this->fecNotDateExpr() . ")), 2), '')";
    }

    private function distinctResolvedYears(int $limit = 20): array
    {
        return MaestroSiv549::query()
            ->selectRaw($this->resolvedYearExpr() . ' as year_resolved')
            ->whereRaw($this->resolvedYearExpr() . " <> ''")
            ->groupByRaw($this->resolvedYearExpr())
            ->orderByDesc('year_resolved')
            ->limit($limit)
            ->pluck('year_resolved')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function reportColumnCatalog(): array
    {
        $labels = [
            'nombre_completo' => 'Nombre completo',
            'fec_not' => 'Fecha notificacion',
            'year' => 'Año',
            'semana' => 'Semana',
            'tip_ide_' => 'Tipo ID',
            'num_ide_' => 'Numero ID',
            'edad_' => 'Edad',
            'sexo_' => 'Sexo',
            'telefono_' => 'Telefono',
            'dir_res_' => 'Direccion',
            'ocupacion_' => 'Ocupacion',
            'nom_eve' => 'Evento',
            'nom_upgd' => 'UPGD notificadora',
            'nmun_resi' => 'Municipio residencia',
            'nmun_notif' => 'Municipio notificacion',
            'nom_grupo_' => 'Grupo etnico',
            'tip_ss_' => 'Tipo aseguramiento',
            'tip_cas_' => 'Tipo caso',
            'sem_ges' => 'Semanas gestacion',
            'cod_eve' => 'Codigo evento',
        ];

        $catalog = [
            'nombre_completo' => ['label' => $labels['nombre_completo'], 'db' => null],
        ];

        foreach ((new MaestroSiv549())->getFillable() as $field) {
            $catalog[$field] = [
                'label' => $labels[$field] ?? $this->humanizeLabel($field),
                'db' => $field,
            ];
        }

        if (isset($catalog['year'])) {
            $catalog['year']['db'] = 'year_resolved';
        }

        return $catalog;
    }

    private function humanizeLabel(string $field): string
    {
        $clean = trim($field, '_');
        $clean = str_replace('_', ' ', $clean);

        return ucfirst($clean);
    }

    private function normalizeRequestedColumns($requested, array $allowed): array
    {
        if (!is_array($requested)) {
            return [];
        }

        return array_values(array_filter($requested, fn ($column) => in_array($column, $allowed, true)));
    }

    private function buildReportSelects(array $columns, array $catalog): array
    {
        $selects = [];
        $selectedDbKeys = [];

        foreach ($columns as $key) {
            if (!empty($catalog[$key]['db'])) {
                $db = $catalog[$key]['db'];
                $selects[] = DB::raw("[$db] as [$key]");
                $selectedDbKeys[] = $db;
            }
        }

        return [$selects, $selectedDbKeys];
    }

    private function mapReportRow($row, array $columns): array
    {
        $fullName = trim(implode(' ', array_filter([
            $row->pri_nom_ ?? '',
            $row->seg_nom_ ?? '',
            $row->pri_ape_ ?? '',
            $row->seg_ape_ ?? '',
        ])));

        $data = [];
        foreach ($columns as $column) {
            $data[$column] = $column === 'nombre_completo'
                ? $fullName
                : ($row->{$column} ?? '');
        }

        return $data;
    }
}
