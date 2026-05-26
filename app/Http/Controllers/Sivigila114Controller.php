<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use App\Models\Sivigila;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SivigilaDesignerExport;
use Yajra\DataTables\Facades\DataTables;

class Sivigila114Controller extends Controller
{
    private string $sourceTable = 'sga.dbo.maestrosiv114';

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('Admin_sivigila');
    }

    public function index()
    {
        $years = DB::connection('sqlsrv_1')
            ->table($this->sourceTable . ' as m')
            ->selectRaw('YEAR(m.fec_not) AS year')
            ->where('m.cod_eve', 114)
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $defaultYear = $years->first();

        $resultados = DB::connection('sqlsrv_1')
            ->table($this->sourceTable . ' as m')
            ->where('m.cod_eve', 114)
            ->whereYear('m.fec_not', '>=', 2024)
            ->count();

        $sivi2 = DB::table('sivigilas')->where('cod_eve', 114)->count();
        $count123 = DB::table('seguimientos as seg')
            ->join('sivigilas as s', 's.id', '=', 'seg.sivigilas_id')
            ->where('s.cod_eve', 114)
            ->distinct('s.id')
            ->count('s.id');

        $conteo = Auth::user()->usertype == 2
            ? Seguimiento::where('estado', 1)->where('user_id', Auth::id())->count()
            : Seguimiento::where('estado', 1)->count();

        $reportColumns = $this->reportColumnCatalog();
        $defaultReportColumns = [
            'fec_noti',
            'semana',
            'tip_ide_',
            'num_ide_',
            'nombres_completos',
            'nom_upgd',
            'procesado',
        ];

        return view('sivigila114.index', compact(
            'years',
            'defaultYear',
            'resultados',
            'sivi2',
            'count123',
            'conteo',
            'reportColumns',
            'defaultReportColumns'
        ));
    }

    public function data(Request $request)
    {
        $canManageCrud = Auth::check() && (int) Auth::user()->usertype !== 2;

        $query = DB::connection('sqlsrv_1')
            ->table($this->sourceTable . ' as m')
            ->selectRaw("
                CAST(m.fec_not AS DATE) as fec_noti,
                114 as cod_eve,
                m.semana,
                m.tip_ide_,
                CAST(m.num_ide_ AS VARCHAR(40)) as num_ide_,
                m.pri_nom_,
                m.seg_nom_,
                m.pri_ape_,
                m.seg_ape_,
                m.nom_upgd
            ")
            ->where('m.cod_eve', 114);

        if ($request->filled('year')) {
            $query->whereYear('m.fec_not', (int) $request->input('year'));
        }
        if ($request->filled('semana')) {
            $query->where('m.semana', $request->input('semana'));
        }
        if ($request->filled('tip_ide_')) {
            $query->where('m.tip_ide_', 'like', '%' . trim((string) $request->input('tip_ide_')) . '%');
        }
        // El filtro "procesado" se calcula en capa aplicacion para evitar inconsistencias
        // de cruce entre conexiones sqlsrv_1 (fuente) y sqlsrv local (asignaciones).
        if ($request->filled('nom_upgd')) {
            $query->where('m.nom_upgd', 'like', '%' . trim((string) $request->input('nom_upgd')) . '%');
        }
        if ($request->filled('q')) {
            $quick = trim((string) $request->input('q'));
            $query->where(function ($q) use ($quick) {
                $q->where('m.num_ide_', 'like', '%' . $quick . '%')
                    ->orWhere('m.pri_nom_', 'like', '%' . $quick . '%')
                    ->orWhere('m.seg_nom_', 'like', '%' . $quick . '%')
                    ->orWhere('m.pri_ape_', 'like', '%' . $quick . '%')
                    ->orWhere('m.seg_ape_', 'like', '%' . $quick . '%')
                    ->orWhere('m.nom_upgd', 'like', '%' . $quick . '%');
            });
        }

        return DataTables::of($query)
            ->addColumn('procesado_badge', function ($row) {
                $doc = trim((string) $row->num_ide_);
                $fec = trim((string) $row->fec_noti);
                $isProcessed = DB::table('sivigilas')
                    ->where('cod_eve', 114)
                    ->whereRaw("LTRIM(RTRIM(CAST(num_ide_ AS VARCHAR(40)))) = ?", [$doc])
                    ->whereDate('fec_not', $fec)
                    ->exists();

                return $isProcessed
                    ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Procesado</span>'
                    : '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Pendiente</span>';
            })
            ->addColumn('acciones', function ($row) use ($canManageCrud) {
                $doc = trim((string) $row->num_ide_);
                $fec = trim((string) $row->fec_noti);
                $local = Sivigila::query()
                    ->where('cod_eve', 114)
                    ->whereRaw("LTRIM(RTRIM(CAST(num_ide_ AS VARCHAR(40)))) = ?", [$doc])
                    ->whereDate('fec_not', $fec)
                    ->orderByDesc('id')
                    ->first();

                // Fallback: usar la ultima asignacion del evento 114 para ese documento.
                if (!$local) {
                    $local = Sivigila::query()
                        ->where('cod_eve', 114)
                        ->whereRaw("LTRIM(RTRIM(CAST(num_ide_ AS VARCHAR(40)))) = ?", [$doc])
                        ->orderByDesc('fec_not')
                        ->orderByDesc('id')
                        ->first();
                }

                $url = route('sivigila114.create', ['num_ide_' => $row->num_ide_, 'fec_not' => $row->fec_noti]);
                $localId = $local?->id;
                $isProcessed = (bool) $localId;
                $hasSeguimiento = $localId
                    ? Seguimiento::where('sivigilas_id', $localId)->exists()
                    : false;

                $dropdown = '<div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs mr-1"></i> Acciones
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm">';

                if (!$isProcessed) {
                    $dropdown .= '<a class="dropdown-item" href="' . e($url) . '">
                                    <i class="fas fa-notes-medical text-success mr-2"></i> Asignar
                                  </a>';
                } else {
                    $dropdown .= '<span class="dropdown-item text-muted">
                                    <i class="fas fa-check-circle text-secondary mr-2"></i> Procesado
                                  </span>';
                }

                if ($canManageCrud && $localId) {
                    $dropdown .= '<div class="dropdown-divider"></div>';
                    $dropdown .= '<a class="dropdown-item" href="' . route('sivigila.show', ['sivigila' => $localId, 'redirect_route' => 'sivigila114.index']) . '">
                                    <i class="fas fa-eye text-info mr-2"></i> Visualizar asignacion
                                  </a>';
                    $dropdown .= '<a class="dropdown-item" href="' . route('sivigila.edit', ['sivigila' => $localId, 'redirect_route' => 'sivigila114.index']) . '">
                                    <i class="fas fa-edit text-warning mr-2"></i> Editar asignacion
                                  </a>';

                    if ($hasSeguimiento) {
                        $dropdown .= '<span class="dropdown-item text-muted">
                                        <i class="fas fa-lock text-secondary mr-2"></i> No se puede eliminar (ya tiene seguimiento)
                                      </span>';
                    } else {
                        $dropdown .= '<form method="POST" action="' . route('sivigila.destroy', $localId) . '" onsubmit="return confirm(\'Seguro que deseas eliminar esta asignacion?\');">
                                        ' . csrf_field() . method_field('DELETE') . '
                                        <input type="hidden" name="redirect_route" value="sivigila114.index">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash-alt mr-2"></i> Eliminar
                                        </button>
                                      </form>';
                    }
                }

                $dropdown .= '</div></div>';
                return $dropdown;
            })
            ->rawColumns(['procesado_badge', 'acciones'])
            ->make(true);
    }

    public function create($num_ide_, $fec_not)
    {
        $num_ide_ = trim((string) $num_ide_);
        $fec_not = trim((string) $fec_not);

        $registro = DB::connection('sqlsrv_1')
            ->table($this->sourceTable)
            ->where('num_ide_', $num_ide_)
            ->whereRaw('CAST(fec_not AS DATE) = CAST(? AS DATE)', [$fec_not])
            ->where('cod_eve', 114)
            ->first();

        if (!$registro) {
            return redirect()->route('sivigila114.index')->with('error', 'No se encontro el caso solicitado en maestrosiv114.');
        }

        $incomeedit = $num_ide_;
        $incomeedit1 = $registro;
        $incomeedit2 = DB::connection('sqlsrv_1')
            ->table($this->sourceTable)
            ->where('num_ide_', $num_ide_)
            ->whereRaw('CAST(fec_not AS DATE) = CAST(? AS DATE)', [$fec_not])
            ->value(DB::raw('CAST(fec_not AS DATE)'));
        $incomeedit3 = $registro->edad_ ?? null;
        $incomeedit4 = $registro->year ?? date('Y');
        $incomeedit5 = $registro->semana ?? null;
        $incomeedit6 = $registro->nmun_resi ?? null;
        $incomeedit7 = $registro->ndep_resi ?? null;
        $fechaNacimiento = $this->normalizeSourceDate($registro->fecha_nto_ ?? null);
        $fechaReferencia = $this->normalizeSourceDate($incomeedit2) ?? Carbon::today();
        $edadMeses = null;
        if ($fechaNacimiento) {
            $edadMeses = (int) floor((float) $fechaNacimiento->diffInMonths($fechaReferencia));
            if ($edadMeses < 0) {
                $edadMeses = 0;
            }
        }

        $incomeedit8 = $fechaNacimiento ? $fechaNacimiento->toDateString() : null;
        $incomeedit9 = $edadMeses;
        $incomeedit10 = null;
        $income11 = $registro->nom_upgd ?? null;

        $incomeedit13 = DB::connection('sqlsrv_1')->table('maestroAfiliados')
            ->select(DB::raw("IIF(codigoAgente = 'EPSI04', 'subsidiado', 'contributivo') as tipo_afiliacion"))
            ->where('identificacion', $num_ide_)
            ->value('tipo_afiliacion');

        $incomeedit14 = DB::connection('sqlsrv_1')->table('maestroAfiliados as a')
            ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
            ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
            ->join('maestroIpsGruDet as d', function ($join) {
                $join->on('c.id', '=', 'd.idd')
                    ->where('d.servicio', '=', 1);
            })
            ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
            ->select(DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'))
            ->where('a.identificacion', $num_ide_)
            ->first();

        $income12 = $incomeedit14
            ? DB::table('users')
                ->select('name', 'id', 'codigohabilitacion')
                ->where('usertype', 2)
                ->where('codigohabilitacion', $incomeedit14->codigo_habilitacion)
                ->orderBy('id')
                ->get()
            : collect();

        $incomeedit15 = DB::table('users')
            ->select('name', 'id', 'codigohabilitacion')
            ->where('usertype', 2)
            ->orderBy('name')
            ->get();
        $incomeedit16 = DB::connection('sqlsrv_1')
            ->table('refIps')
            ->select('refIps.descrip as nombrepres', 'refIps.codigo as cod')
            ->whereIn('codigoDepartamento', [44, 47, 8])
            ->get();

        return view('sivigila114.create', compact(
            'incomeedit',
            'incomeedit1',
            'incomeedit2',
            'incomeedit3',
            'incomeedit4',
            'incomeedit5',
            'incomeedit6',
            'incomeedit7',
            'incomeedit8',
            'incomeedit9',
            'incomeedit10',
            'income11',
            'income12',
            'incomeedit13',
            'incomeedit14',
            'incomeedit15',
            'incomeedit16'
        ));
    }

    private function normalizeSourceDate($value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || in_array($raw, ['0000-00-00', '1900-01-01'], true)) {
            return null;
        }

        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd/m/Y H:i:s', 'Ymd'];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $raw);
                if ($dt !== false) {
                    return $dt->startOfDay();
                }
            } catch (\Throwable $e) {
                // continuar con siguiente formato
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function reportColumnCatalog(): array
    {
        return [
            'fec_noti' => ['label' => 'Fecha Notificacion', 'db' => 'fec_noti'],
            'semana' => ['label' => 'Semana', 'db' => 'semana'],
            'tip_ide_' => ['label' => 'Tipo ID', 'db' => 'tip_ide_'],
            'num_ide_' => ['label' => 'Identificacion', 'db' => 'num_ide_'],
            'pri_nom_' => ['label' => 'Primer Nombre', 'db' => 'pri_nom_'],
            'seg_nom_' => ['label' => 'Segundo Nombre', 'db' => 'seg_nom_'],
            'pri_ape_' => ['label' => 'Primer Apellido', 'db' => 'pri_ape_'],
            'seg_ape_' => ['label' => 'Segundo Apellido', 'db' => 'seg_ape_'],
            'nombres_completos' => ['label' => 'Nombres Completos', 'db' => null],
            'nom_upgd' => ['label' => 'UPGD Notificadora', 'db' => 'nom_upgd'],
            'procesado' => ['label' => 'Procesado', 'db' => null],
        ];
    }

    private function buildBaseQuery(Request $request)
    {
        $query = DB::connection('sqlsrv_1')
            ->table($this->sourceTable . ' as m')
            ->selectRaw("
                CAST(m.fec_not AS DATE) as fec_noti,
                114 as cod_eve,
                m.semana,
                m.tip_ide_,
                CAST(m.num_ide_ AS VARCHAR(40)) as num_ide_,
                m.pri_nom_,
                m.seg_nom_,
                m.pri_ape_,
                m.seg_ape_,
                m.nom_upgd
            ")
            ->where('m.cod_eve', 114);

        if ($request->filled('year')) {
            $query->whereYear('m.fec_not', (int) $request->input('year'));
        }
        if ($request->filled('semana')) {
            $query->where('m.semana', $request->input('semana'));
        }
        if ($request->filled('tip_ide_')) {
            $query->where('m.tip_ide_', 'like', '%' . trim((string) $request->input('tip_ide_')) . '%');
        }
        if ($request->filled('nom_upgd')) {
            $query->where('m.nom_upgd', 'like', '%' . trim((string) $request->input('nom_upgd')) . '%');
        }
        if ($request->filled('q')) {
            $quick = trim((string) $request->input('q'));
            $query->where(function ($q) use ($quick) {
                $q->where('m.num_ide_', 'like', '%' . $quick . '%')
                    ->orWhere('m.pri_nom_', 'like', '%' . $quick . '%')
                    ->orWhere('m.seg_nom_', 'like', '%' . $quick . '%')
                    ->orWhere('m.pri_ape_', 'like', '%' . $quick . '%')
                    ->orWhere('m.seg_ape_', 'like', '%' . $quick . '%')
                    ->orWhere('m.nom_upgd', 'like', '%' . $quick . '%');
            });
        }

        return $query;
    }

    public function reportPreview(Request $request)
    {
        $payload = $this->buildReportPayload($request, true);
        return response()->json($payload);
    }

    public function reportExport(Request $request)
    {
        $payload = $this->buildReportPayload($request, false);
        $format = strtolower((string) $request->input('format', 'xlsx'));
        if (!in_array($format, ['csv', 'xlsx'], true)) {
            $format = 'xlsx';
        }

        $fileName = 'sivigila114_reporte_disenado_' . now()->format('Ymd_His');
        $export = new SivigilaDesignerExport(collect($payload['rowsAssoc']), $payload['headers'], $payload['keys']);

        if ($format === 'csv') {
            return Excel::download($export, $fileName . '.csv', ExcelFormat::CSV);
        }
        return Excel::download($export, $fileName . '.xlsx', ExcelFormat::XLSX);
    }

    private function buildReportPayload(Request $request, bool $preview): array
    {
        $catalog = $this->reportColumnCatalog();
        $requestedColumns = $request->input('columns', []);
        if (!is_array($requestedColumns)) {
            $requestedColumns = [];
        }
        $columns = array_values(array_filter($requestedColumns, fn($c) => isset($catalog[$c])));
        if (empty($columns)) {
            $columns = ['fec_noti', 'semana', 'tip_ide_', 'num_ide_', 'nombres_completos', 'nom_upgd', 'procesado'];
        }

        $rows = $this->buildBaseQuery($request)
            ->orderByDesc('fec_noti')
            ->limit($preview ? 40 : 100000)
            ->get();

        $rowsAssoc = [];
        foreach ($rows as $row) {
            $fullName = trim(implode(' ', array_filter([
                $row->pri_nom_ ?? '',
                $row->seg_nom_ ?? '',
                $row->pri_ape_ ?? '',
                $row->seg_ape_ ?? '',
            ])));

            $isProcessed = DB::table('sivigilas')
                ->where('cod_eve', 114)
                ->whereRaw("LTRIM(RTRIM(CAST(num_ide_ AS VARCHAR(40)))) = ?", [trim((string) ($row->num_ide_ ?? ''))])
                ->whereDate('fec_not', (string) ($row->fec_noti ?? ''))
                ->exists();

            $arr = [];
            foreach ($columns as $col) {
                if ($col === 'nombres_completos') {
                    $arr[$col] = $fullName;
                } elseif ($col === 'procesado') {
                    $arr[$col] = $isProcessed ? 'SI' : 'NO';
                } else {
                    $arr[$col] = $row->{$col} ?? '';
                }
            }
            $rowsAssoc[] = $arr;
        }

        $headers = [];
        foreach ($columns as $key) {
            $headers[] = $catalog[$key]['label'];
        }

        return [
            'ok' => true,
            'keys' => $columns,
            'headers' => $headers,
            'rows' => array_map(fn($r) => array_values($r), $rowsAssoc),
            'rowsAssoc' => $rowsAssoc,
            'meta' => ['total' => count($rowsAssoc)],
        ];
    }
}
