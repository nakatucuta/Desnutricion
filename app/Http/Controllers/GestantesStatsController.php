<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GestantesStatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function userScope($q, string $table)
    {
        if (Auth::user() && (int) Auth::user()->usertype === 2 && Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
            $q->where('user_id', Auth::id());
        }
        return $q;
    }

    private function dateVal(?string $v): ?string
    {
        if (!$v) {
            return null;
        }
        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function filters(Request $r): array
    {
        return [
            'module' => (string) $r->input('module', 'todos'),
            'from' => $this->dateVal($r->input('from')),
            'to' => $this->dateVal($r->input('to')),
            'months' => max(3, min(24, (int) $r->input('months', 12))),
            'identificacion' => trim((string) $r->input('identificacion', '')),
            'municipio' => trim((string) $r->input('municipio', '')),
            'riesgo_precon' => trim((string) $r->input('riesgo_precon', '')),
            'riesgo_gestacional' => trim((string) $r->input('riesgo_gestacional', '')),
            'fpp_estado' => trim((string) $r->input('fpp_estado', 'todos')),
            'semana' => trim((string) $r->input('semana', '')),
        ];
    }

    private function applyDate($q, string $table, string $col, array $f)
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $col)) {
            return $q;
        }
        if (!empty($f['from'])) {
            $q->whereDate($col, '>=', $f['from']);
        }
        if (!empty($f['to'])) {
            $q->whereDate($col, '<=', $f['to']);
        }
        return $q;
    }

    private function months(int $n): array
    {
        $labels = [];
        $cur = now()->startOfMonth()->subMonths($n - 1);
        for ($i = 0; $i < $n; $i++) {
            $labels[] = $cur->format('Y-m');
            $cur->addMonth();
        }
        return $labels;
    }

    private function monthly($q, string $col, int $months): array
    {
        $rows = (clone $q)
            ->selectRaw("YEAR($col) y, MONTH($col) m, COUNT(*) c")
            ->groupByRaw("YEAR($col), MONTH($col)")
            ->orderByRaw("YEAR($col), MONTH($col)")
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[sprintf('%04d-%02d', $r->y, $r->m)] = (int) $r->c;
        }

        $labels = $this->months($months);
        return [
            'labels' => $labels,
            'values' => array_map(fn($m) => $map[$m] ?? 0, $labels),
        ];
    }

    private function options(): array
    {
        $distinct = function (string $table, string $col): array {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $col)) {
                return [];
            }
            $q = DB::table($table)->select($col)->whereNotNull($col)->where($col, '<>','');
            $q = $this->userScope($q, $table);
            return $q->distinct()->orderBy($col)->limit(80)->pluck($col)->map(fn($v) => (string) $v)->values()->all();
        };

        return [
            'riesgo_precon' => $distinct('preconcepcionales', 'riesgo_preconcepcional'),
            'riesgo_gestacional' => $distinct('ges_tipo3', 'clasificacion_riesgo_gestacional'),
            'semanas' => $distinct('asignaciones_maestrosiv549', 'semana'),
            'fpp_estado' => [
                ['value' => 'todos', 'label' => 'Todas'],
                ['value' => 'vigentes', 'label' => 'FPP vigentes'],
                ['value' => 'vencidas', 'label' => 'FPP vencidas'],
                ['value' => 'proximas_7', 'label' => 'FPP proximas 7 dias'],
                ['value' => 'proximas_30', 'label' => 'FPP proximas 30 dias'],
            ],
            'modules' => [
                ['value' => 'todos', 'label' => 'Todos los modulos'],
                ['value' => 'preconcepcional', 'label' => 'Preconcepcional'],
                ['value' => 'tipo1', 'label' => 'Gestantes Tipo 1'],
                ['value' => 'tipo3', 'label' => 'Gestantes Tipo 3'],
                ['value' => 'siv549', 'label' => 'SIV549'],
            ],
        ];
    }

    private function precon(array $f): array
    {
        if (!Schema::hasTable('preconcepcionales')) {
            return ['label' => 'Preconcepcional', 'summary' => [], 'chart_monthly' => ['labels' => [], 'values' => []], 'chart_main' => ['labels' => [], 'values' => []], 'tables' => ['a' => [], 'b' => []]];
        }
        $q = DB::table('preconcepcionales');
        $q = $this->userScope($q, 'preconcepcionales');
        $q = $this->applyDate($q, 'preconcepcionales', 'created_at', $f);
        if ($f['identificacion'] !== '') {
            $q->where('numero_identificacion', 'like', '%' . $f['identificacion'] . '%');
        }
        if ($f['municipio'] !== '') {
            $q->where('municipio_residencia', 'like', '%' . $f['municipio'] . '%');
        }
        if ($f['riesgo_precon'] !== '') {
            $q->where('riesgo_preconcepcional', 'like', '%' . $f['riesgo_precon'] . '%');
        }

        $total = (int) (clone $q)->count();
        $alto = (int) (clone $q)->where('riesgo_preconcepcional', 'like', '%ALTO%')->count();
        $medio = (int) (clone $q)->where('riesgo_preconcepcional', 'like', '%MEDIO%')->count();
        $bajo = (int) (clone $q)->where('riesgo_preconcepcional', 'like', '%BAJO%')->count();

        return [
            'label' => 'Preconcepcional',
            'summary' => [
                'total' => $total,
                'alto_riesgo' => $alto,
                'medio_riesgo' => $medio,
                'bajo_riesgo' => $bajo,
                'sin_telefono' => (int) (clone $q)->where(function ($x) {
                    $x->whereNull('telefono')->orWhere('telefono', '');
                })->count(),
                'sin_imc' => (int) (clone $q)->whereNull('imc')->count(),
            ],
            'chart_monthly' => $this->monthly($q, 'created_at', $f['months']),
            'chart_main' => ['labels' => ['Alto', 'Medio', 'Bajo'], 'values' => [$alto, $medio, $bajo]],
            'tables' => [
                'a' => (clone $q)->selectRaw("COALESCE(NULLIF(municipio_residencia,''),'SIN MUNICIPIO') municipio, COUNT(*) total")
                    ->groupByRaw("COALESCE(NULLIF(municipio_residencia,''),'SIN MUNICIPIO')")
                    ->orderByDesc('total')->limit(20)->get()->map(fn($r) => ['municipio' => $r->municipio, 'total' => (int) $r->total])->all(),
                'b' => (clone $q)->select('id', 'numero_identificacion', 'apellido_1', 'nombre_1', 'municipio_residencia', 'telefono', 'riesgo_preconcepcional', 'created_at')
                    ->orderByDesc('id')->limit(25)->get()->map(fn($r) => [
                        'id' => (int) $r->id,
                        'identificacion' => $r->numero_identificacion,
                        'nombre' => trim(($r->apellido_1 ?? '') . ' ' . ($r->nombre_1 ?? '')),
                        'municipio' => $r->municipio_residencia,
                        'telefono' => $r->telefono,
                        'riesgo' => $r->riesgo_preconcepcional,
                        'fecha' => optional($r->created_at)->format('Y-m-d H:i'),
                    ])->all(),
            ],
        ];
    }

    private function tipo1(array $f): array
    {
        if (!Schema::hasTable('ges_tipo1')) {
            return ['label' => 'Gestantes Tipo 1', 'summary' => [], 'chart_monthly' => ['labels' => [], 'values' => []], 'chart_main' => ['labels' => [], 'values' => []], 'tables' => ['a' => [], 'b' => []]];
        }
        $q = DB::table('ges_tipo1');
        $q = $this->userScope($q, 'ges_tipo1');
        $q = $this->applyDate($q, 'ges_tipo1', 'created_at', $f);
        if ($f['identificacion'] !== '') {
            $q->where('no_id_del_usuario', 'like', '%' . $f['identificacion'] . '%');
        }
        if ($f['municipio'] !== '') {
            $q->whereRaw("CAST(municipio_de_residencia_habitual AS VARCHAR(40)) like ?", ['%' . $f['municipio'] . '%']);
        }
        if ($f['fpp_estado'] === 'vencidas') {
            $q->where('fecha_probable_de_parto', '<', now()->toDateString());
        } elseif ($f['fpp_estado'] === 'proximas_7') {
            $q->whereBetween('fecha_probable_de_parto', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        } elseif ($f['fpp_estado'] === 'proximas_30') {
            $q->whereBetween('fecha_probable_de_parto', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        } elseif ($f['fpp_estado'] === 'vigentes') {
            $q->where('fecha_probable_de_parto', '>=', now()->toDateString());
        }

        $total = (int) (clone $q)->count();
        $venc = (int) (clone $q)->where('fecha_probable_de_parto', '<', now()->toDateString())->count();
        $p7 = (int) (clone $q)->whereBetween('fecha_probable_de_parto', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
        $p30 = (int) (clone $q)->whereBetween('fecha_probable_de_parto', [now()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $sin = (int) (clone $q)->whereNull('fecha_probable_de_parto')->count();

        $municipiosTable = [];
        $latestTable = [];
        try {
            $baseJoin = DB::table('ges_tipo1 as A')
                ->leftJoin(DB::raw('sga..municipios as M'), function ($join) {
                    $join->on(
                        DB::raw("RIGHT('00000' + CAST(A.municipio_de_residencia_habitual AS VARCHAR(5)), 5)"),
                        '=',
                        DB::raw("RIGHT('00' + CAST(M.codigoDepartamento AS VARCHAR(2)), 2) + RIGHT('000' + CAST(M.codigoMunicipio AS VARCHAR(3)), 3)")
                    );
                });

            if (Auth::user() && (int) Auth::user()->usertype === 2) {
                $baseJoin->where('A.user_id', Auth::id());
            }
            if (!empty($f['from'])) {
                $baseJoin->whereDate('A.created_at', '>=', $f['from']);
            }
            if (!empty($f['to'])) {
                $baseJoin->whereDate('A.created_at', '<=', $f['to']);
            }
            if ($f['identificacion'] !== '') {
                $baseJoin->where('A.no_id_del_usuario', 'like', '%' . $f['identificacion'] . '%');
            }
            if ($f['municipio'] !== '') {
                $baseJoin->where(function ($x) use ($f) {
                    $x->whereRaw("CAST(A.municipio_de_residencia_habitual AS VARCHAR(40)) like ?", ['%' . $f['municipio'] . '%'])
                        ->orWhere('M.descrip', 'like', '%' . $f['municipio'] . '%');
                });
            }
            if ($f['fpp_estado'] === 'vencidas') {
                $baseJoin->where('A.fecha_probable_de_parto', '<', now()->toDateString());
            } elseif ($f['fpp_estado'] === 'proximas_7') {
                $baseJoin->whereBetween('A.fecha_probable_de_parto', [now()->toDateString(), now()->addDays(7)->toDateString()]);
            } elseif ($f['fpp_estado'] === 'proximas_30') {
                $baseJoin->whereBetween('A.fecha_probable_de_parto', [now()->toDateString(), now()->addDays(30)->toDateString()]);
            } elseif ($f['fpp_estado'] === 'vigentes') {
                $baseJoin->where('A.fecha_probable_de_parto', '>=', now()->toDateString());
            }

            $municipiosTable = (clone $baseJoin)
                ->selectRaw("
                    COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.municipio_de_residencia_habitual, ')')) as municipio,
                    COUNT(*) as total
                ")
                ->groupByRaw("COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.municipio_de_residencia_habitual, ')'))")
                ->orderByDesc('total')
                ->limit(20)
                ->get()
                ->map(fn($r) => ['municipio' => $r->municipio, 'total' => (int) $r->total])
                ->all();

            $latestTable = (clone $baseJoin)
                ->selectRaw("
                    A.id,
                    A.no_id_del_usuario,
                    A.primer_nombre,
                    A.primer_apellido,
                    A.municipio_de_residencia_habitual,
                    A.fecha_probable_de_parto,
                    A.created_at,
                    COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.municipio_de_residencia_habitual, ')')) as municipio_nombre
                ")
                ->orderByDesc('A.id')
                ->limit(25)
                ->get()
                ->map(fn($r) => [
                    'id' => (int) $r->id,
                    'identificacion' => $r->no_id_del_usuario,
                    'nombre' => trim(($r->primer_apellido ?? '') . ' ' . ($r->primer_nombre ?? '')),
                    'municipio' => $r->municipio_nombre,
                    'fpp' => $r->fecha_probable_de_parto ? Carbon::parse($r->fecha_probable_de_parto)->toDateString() : null,
                    'fecha' => optional($r->created_at)->format('Y-m-d H:i'),
                ])
                ->all();
        } catch (\Throwable $e) {
            $municipiosTable = (clone $q)->selectRaw("CAST(municipio_de_residencia_habitual AS VARCHAR(40)) municipio, COUNT(*) total")
                ->groupByRaw("CAST(municipio_de_residencia_habitual AS VARCHAR(40))")
                ->orderByDesc('total')->limit(20)->get()->map(fn($r) => ['municipio' => $r->municipio, 'total' => (int) $r->total])->all();

            $latestTable = (clone $q)->select('id', 'no_id_del_usuario', 'primer_nombre', 'primer_apellido', 'municipio_de_residencia_habitual', 'fecha_probable_de_parto', 'created_at')
                ->orderByDesc('id')->limit(25)->get()->map(fn($r) => [
                    'id' => (int) $r->id,
                    'identificacion' => $r->no_id_del_usuario,
                    'nombre' => trim(($r->primer_apellido ?? '') . ' ' . ($r->primer_nombre ?? '')),
                    'municipio' => (string) $r->municipio_de_residencia_habitual,
                    'fpp' => $r->fecha_probable_de_parto ? Carbon::parse($r->fecha_probable_de_parto)->toDateString() : null,
                    'fecha' => optional($r->created_at)->format('Y-m-d H:i'),
                ])->all();
        }

        return [
            'label' => 'Gestantes Tipo 1',
            'summary' => [
                'total' => $total,
                'fpp_vencidas' => $venc,
                'fpp_proximas_7' => $p7,
                'fpp_proximas_30' => $p30,
                'sin_fpp' => $sin,
                'registros_hoy' => (int) (clone $q)->whereDate('created_at', now()->toDateString())->count(),
            ],
            'chart_monthly' => $this->monthly($q, 'created_at', $f['months']),
            'chart_main' => ['labels' => ['Vencidas', 'Proximas 7 dias', 'Proximas 30 dias', 'Sin FPP'], 'values' => [$venc, $p7, $p30, $sin]],
            'tables' => [
                'a' => $municipiosTable,
                'b' => $latestTable,
            ],
        ];
    }

    private function tipo3(array $f): array
    {
        if (!Schema::hasTable('ges_tipo3')) {
            return ['label' => 'Gestantes Tipo 3', 'summary' => [], 'chart_monthly' => ['labels' => [], 'values' => []], 'chart_main' => ['labels' => [], 'values' => []], 'tables' => ['a' => [], 'b' => []]];
        }
        $q = DB::table('ges_tipo3');
        $q = $this->userScope($q, 'ges_tipo3');
        $q = $this->applyDate($q, 'ges_tipo3', 'created_at', $f);
        if ($f['identificacion'] !== '') {
            $q->where('no_id_del_usuario', 'like', '%' . $f['identificacion'] . '%');
        }
        if ($f['riesgo_gestacional'] !== '') {
            $q->where('clasificacion_riesgo_gestacional', $f['riesgo_gestacional']);
        }

        $riskRows = (clone $q)
            ->selectRaw("COALESCE(CAST(clasificacion_riesgo_gestacional AS VARCHAR(10)),'SIN DATO') r, COUNT(*) t")
            ->groupByRaw("COALESCE(CAST(clasificacion_riesgo_gestacional AS VARCHAR(10)),'SIN DATO')")
            ->orderByDesc('t')
            ->get();

        return [
            'label' => 'Gestantes Tipo 3',
            'summary' => [
                'total' => (int) (clone $q)->count(),
                'con_cups' => (int) (clone $q)->whereNotNull('codigo_cups_de_la_tecnologia_en_salud')->where('codigo_cups_de_la_tecnologia_en_salud', '<>', '')->count(),
                'con_riesgo_gestacional' => (int) (clone $q)->whereNotNull('clasificacion_riesgo_gestacional')->count(),
                'registros_hoy' => (int) (clone $q)->whereDate('created_at', now()->toDateString())->count(),
            ],
            'chart_monthly' => $this->monthly($q, 'created_at', $f['months']),
            'chart_main' => [
                'labels' => $riskRows->pluck('r')->values()->all(),
                'values' => $riskRows->pluck('t')->map(fn($x) => (int) $x)->values()->all(),
            ],
            'tables' => [
                'a' => (clone $q)->whereNotNull('codigo_cups_de_la_tecnologia_en_salud')->where('codigo_cups_de_la_tecnologia_en_salud', '<>', '')
                    ->selectRaw("codigo_cups_de_la_tecnologia_en_salud cups, COUNT(*) total")
                    ->groupBy('codigo_cups_de_la_tecnologia_en_salud')->orderByDesc('total')->limit(20)->get()->map(fn($r) => ['cups' => $r->cups, 'total' => (int) $r->total])->all(),
                'b' => (clone $q)->select('id', 'no_id_del_usuario', 'codigo_cups_de_la_tecnologia_en_salud', 'clasificacion_riesgo_gestacional', 'fecha_tecnologia_en_salud', 'created_at')
                    ->orderByDesc('id')->limit(25)->get()->map(fn($r) => [
                        'id' => (int) $r->id,
                        'identificacion' => $r->no_id_del_usuario,
                        'cups' => $r->codigo_cups_de_la_tecnologia_en_salud,
                        'riesgo' => $r->clasificacion_riesgo_gestacional,
                        'fecha_tecnologia' => $r->fecha_tecnologia_en_salud ? Carbon::parse($r->fecha_tecnologia_en_salud)->toDateString() : null,
                        'fecha' => optional($r->created_at)->format('Y-m-d H:i'),
                    ])->all(),
            ],
        ];
    }

    private function siv549(array $f): array
    {
        if (!Schema::hasTable('asignaciones_maestrosiv549')) {
            return ['label' => 'Maestro SIV549', 'summary' => [], 'chart_monthly' => ['labels' => [], 'values' => []], 'chart_main' => ['labels' => [], 'values' => []], 'tables' => ['a' => [], 'b' => []]];
        }
        $q = DB::table('asignaciones_maestrosiv549');
        $q = $this->userScope($q, 'asignaciones_maestrosiv549');
        $dateCol = Schema::hasColumn('asignaciones_maestrosiv549', 'fec_not') ? 'fec_not' : 'created_at';
        $q = $this->applyDate($q, 'asignaciones_maestrosiv549', $dateCol, $f);
        if ($f['identificacion'] !== '') {
            $q->where('num_ide_', 'like', '%' . $f['identificacion'] . '%');
        }
        if ($f['semana'] !== '') {
            $q->where('semana', $f['semana']);
        }
        if ($f['municipio'] !== '') {
            $q->where(function ($x) use ($f) {
                $x->where('nmun_resi', 'like', '%' . $f['municipio'] . '%')
                    ->orWhere('cod_mun_o', 'like', '%' . $f['municipio'] . '%')
                    ->orWhere('cod_dpto_o', 'like', '%' . $f['municipio'] . '%');
            });
        }

        $municipiosTable = [];
        $latestTable = [];
        try {
            $baseJoin = DB::table('asignaciones_maestrosiv549 as A')
                ->leftJoin(DB::raw('sga..municipios as M'), function ($join) {
                    $join->on(
                        DB::raw("RIGHT('00' + CAST(A.cod_dpto_o AS VARCHAR(2)), 2)"),
                        '=',
                        DB::raw("RIGHT('00' + CAST(M.codigoDepartamento AS VARCHAR(2)), 2)")
                    )->on(
                        DB::raw("RIGHT('000' + CAST(A.cod_mun_o AS VARCHAR(3)), 3)"),
                        '=',
                        DB::raw("RIGHT('000' + CAST(M.codigoMunicipio AS VARCHAR(3)), 3)")
                    );
                });

            if (Auth::user() && (int) Auth::user()->usertype === 2) {
                $baseJoin->where('A.user_id', Auth::id());
            }
            if (!empty($f['from'])) {
                $baseJoin->whereDate("A.$dateCol", '>=', $f['from']);
            }
            if (!empty($f['to'])) {
                $baseJoin->whereDate("A.$dateCol", '<=', $f['to']);
            }
            if ($f['identificacion'] !== '') {
                $baseJoin->where('A.num_ide_', 'like', '%' . $f['identificacion'] . '%');
            }
            if ($f['semana'] !== '') {
                $baseJoin->where('A.semana', $f['semana']);
            }
            if ($f['municipio'] !== '') {
                $baseJoin->where(function ($x) use ($f) {
                    $x->where('M.descrip', 'like', '%' . $f['municipio'] . '%')
                        ->orWhere('A.nmun_resi', 'like', '%' . $f['municipio'] . '%')
                        ->orWhere('A.cod_mun_o', 'like', '%' . $f['municipio'] . '%')
                        ->orWhere('A.cod_dpto_o', 'like', '%' . $f['municipio'] . '%');
                });
            }

            $municipiosTable = (clone $baseJoin)
                ->selectRaw("COALESCE(M.descrip, COALESCE(NULLIF(A.nmun_resi,''), CONCAT('COD ', A.cod_dpto_o, '-', A.cod_mun_o))) as municipio, COUNT(*) as total")
                ->groupByRaw("COALESCE(M.descrip, COALESCE(NULLIF(A.nmun_resi,''), CONCAT('COD ', A.cod_dpto_o, '-', A.cod_mun_o)))")
                ->orderByDesc('total')
                ->limit(20)
                ->get()
                ->map(fn($r) => ['municipio' => $r->municipio, 'total' => (int) $r->total])
                ->all();

            $latestTable = (clone $baseJoin)
                ->selectRaw("
                    A.id,
                    A.num_ide_,
                    A.pri_nom_,
                    A.pri_ape_,
                    A.semana,
                    A.fec_not,
                    A.telefono_,
                    A.nmun_resi,
                    A.created_at,
                    COALESCE(M.descrip, COALESCE(NULLIF(A.nmun_resi,''), CONCAT('COD ', A.cod_dpto_o, '-', A.cod_mun_o))) as municipio_nombre
                ")
                ->orderByDesc('A.id')
                ->limit(25)
                ->get()
                ->map(fn($r) => [
                    'id' => (int) $r->id,
                    'identificacion' => $r->num_ide_,
                    'nombre' => trim(($r->pri_ape_ ?? '') . ' ' . ($r->pri_nom_ ?? '')),
                    'semana' => $r->semana,
                    'fec_not' => $r->fec_not ? Carbon::parse($r->fec_not)->toDateString() : null,
                    'telefono' => $r->telefono_,
                    'municipio' => $r->municipio_nombre,
                    'fecha' => optional($r->created_at)->format('Y-m-d H:i'),
                ])
                ->all();
        } catch (\Throwable $e) {
            $municipiosTable = (clone $q)->selectRaw("COALESCE(NULLIF(nmun_resi,''), CONCAT('COD ', cod_dpto_o, '-', cod_mun_o)) municipio, COUNT(*) total")
                ->groupByRaw("COALESCE(NULLIF(nmun_resi,''), CONCAT('COD ', cod_dpto_o, '-', cod_mun_o))")
                ->orderByDesc('total')->limit(20)->get()->map(fn($r) => ['municipio' => $r->municipio, 'total' => (int) $r->total])->all();

            $latestTable = (clone $q)->select('id', 'num_ide_', 'pri_nom_', 'pri_ape_', 'semana', 'fec_not', 'telefono_', 'nmun_resi', 'created_at')
                ->orderByDesc('id')->limit(25)->get()->map(fn($r) => [
                    'id' => (int) $r->id,
                    'identificacion' => $r->num_ide_,
                    'nombre' => trim(($r->pri_ape_ ?? '') . ' ' . ($r->pri_nom_ ?? '')),
                    'semana' => $r->semana,
                    'fec_not' => $r->fec_not ? Carbon::parse($r->fec_not)->toDateString() : null,
                    'telefono' => $r->telefono_,
                    'municipio' => $r->nmun_resi,
                    'fecha' => optional($r->created_at)->format('Y-m-d H:i'),
                ])->all();
        }

        return [
            'label' => 'Maestro SIV549',
            'summary' => [
                'total' => (int) (clone $q)->count(),
                'notificados_hoy' => (int) (clone $q)->whereDate($dateCol, now()->toDateString())->count(),
                'con_telefono' => (int) (clone $q)->whereNotNull('telefono_')->where('telefono_', '<>', '')->count(),
                'sin_telefono' => (int) (clone $q)->where(function ($x) {
                    $x->whereNull('telefono_')->orWhere('telefono_', '');
                })->count(),
            ],
            'chart_monthly' => Schema::hasColumn('asignaciones_maestrosiv549', 'created_at') ? $this->monthly($q, 'created_at', $f['months']) : ['labels' => [], 'values' => []],
            'chart_main' => [
                'labels' => (clone $q)->selectRaw("COALESCE(NULLIF(semana,''), 'SIN SEMANA') s")->groupByRaw("COALESCE(NULLIF(semana,''), 'SIN SEMANA')")->orderBy('s')->limit(16)->pluck('s')->values()->all(),
                'values' => (clone $q)->selectRaw("COALESCE(NULLIF(semana,''), 'SIN SEMANA') s, COUNT(*) t")->groupByRaw("COALESCE(NULLIF(semana,''), 'SIN SEMANA')")->orderBy('s')->limit(16)->pluck('t')->map(fn($x) => (int) $x)->values()->all(),
            ],
            'tables' => [
                'a' => $municipiosTable,
                'b' => $latestTable,
            ],
        ];
    }

    private function dashboard(array $f): array
    {
        $modules = [
            'preconcepcional' => $this->precon($f),
            'tipo1' => $this->tipo1($f),
            'tipo3' => $this->tipo3($f),
            'siv549' => $this->siv549($f),
        ];

        $totals = [
            'Preconcepcional' => $modules['preconcepcional']['summary']['total'] ?? 0,
            'Tipo 1' => $modules['tipo1']['summary']['total'] ?? 0,
            'Tipo 3' => $modules['tipo3']['summary']['total'] ?? 0,
            'SIV549' => $modules['siv549']['summary']['total'] ?? 0,
        ];

        $insights = [];
        $t1 = (int) ($modules['tipo1']['summary']['total'] ?? 0);
        if ($t1 > 0) {
            $v = (int) ($modules['tipo1']['summary']['fpp_vencidas'] ?? 0);
            $p = round(($v / $t1) * 100, 1);
            $insights[] = ['indicador' => 'FPP vencidas', 'valor' => "$v/$t1 ($p%)", 'prioridad' => $p >= 20 ? 'Alta' : ($p >= 10 ? 'Media' : 'Baja'), 'accion' => 'Priorizar seguimiento de vencidas'];
        }
        $pr = (int) ($modules['preconcepcional']['summary']['total'] ?? 0);
        if ($pr > 0) {
            $a = (int) ($modules['preconcepcional']['summary']['alto_riesgo'] ?? 0);
            $p = round(($a / $pr) * 100, 1);
            $insights[] = ['indicador' => 'Precon alto riesgo', 'valor' => "$a/$pr ($p%)", 'prioridad' => $p >= 25 ? 'Alta' : ($p >= 12 ? 'Media' : 'Baja'), 'accion' => 'Intervencion prioritaria'];
        }

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'filters' => $f,
            'kpis' => [
                'total_general' => array_sum($totals),
                'precon_total' => $totals['Preconcepcional'],
                'tipo1_total' => $totals['Tipo 1'],
                'tipo3_total' => $totals['Tipo 3'],
                'siv549_total' => $totals['SIV549'],
                'fpp_vencidas' => $modules['tipo1']['summary']['fpp_vencidas'] ?? 0,
                'precon_alto_riesgo' => $modules['preconcepcional']['summary']['alto_riesgo'] ?? 0,
                'tipo3_con_riesgo' => $modules['tipo3']['summary']['con_riesgo_gestacional'] ?? 0,
                'siv_notificados_hoy' => $modules['siv549']['summary']['notificados_hoy'] ?? 0,
            ],
            'charts' => [
                'modules' => ['labels' => array_keys($totals), 'values' => array_values($totals)],
                'monthly_comparison' => [
                    'labels' => $this->months($f['months']),
                    'datasets' => [
                        ['label' => 'Preconcepcional', 'data' => $modules['preconcepcional']['chart_monthly']['values'] ?? []],
                        ['label' => 'Tipo 1', 'data' => $modules['tipo1']['chart_monthly']['values'] ?? []],
                        ['label' => 'Tipo 3', 'data' => $modules['tipo3']['chart_monthly']['values'] ?? []],
                        ['label' => 'SIV549', 'data' => $modules['siv549']['chart_monthly']['values'] ?? []],
                    ],
                ],
                'precon_riesgo' => $modules['preconcepcional']['chart_main'] ?? ['labels' => [], 'values' => []],
                'tipo1_fpp' => $modules['tipo1']['chart_main'] ?? ['labels' => [], 'values' => []],
                'tipo3_riesgo' => $modules['tipo3']['chart_main'] ?? ['labels' => [], 'values' => []],
                'siv_semana' => $modules['siv549']['chart_main'] ?? ['labels' => [], 'values' => []],
            ],
            'insights' => $insights,
            'modules' => $modules,
        ];
    }

    public function index(Request $request)
    {
        $f = $this->filters($request);
        return view('gestantes.stats.index', [
            'filters' => $f,
            'filterOptions' => $this->options(),
            'initialPayload' => $this->dashboard($f),
        ]);
    }

    public function data(Request $request)
    {
        return response()->json(['ok' => true, 'data' => $this->dashboard($this->filters($request))]);
    }

    public function detail(Request $request, string $modulo)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $payload = $this->dashboard($this->filters($request));
        if (!isset($payload['modules'][$modulo])) {
            return response()->json(['ok' => false, 'message' => 'Modulo invalido.'], 404);
        }

        $m = $payload['modules'][$modulo];
        $name = $m['label'] ?? strtoupper($modulo);

        $blocks = [];
        if ($modulo === 'preconcepcional') {
            $blocks[] = ['name' => 'Municipios', 'type' => 'mini_table', 'columns' => ['municipio', 'total'], 'rows' => $m['tables']['a'] ?? []];
            $blocks[] = ['name' => 'Ultimos registros', 'type' => 'table', 'rows' => $m['tables']['b'] ?? []];
        } elseif ($modulo === 'tipo1') {
            $blocks[] = ['name' => 'Municipios', 'type' => 'mini_table', 'columns' => ['municipio', 'total'], 'rows' => $m['tables']['a'] ?? []];
            $blocks[] = ['name' => 'Ultimos registros', 'type' => 'table', 'rows' => $m['tables']['b'] ?? []];
        } elseif ($modulo === 'tipo3') {
            $blocks[] = ['name' => 'Top CUPS', 'type' => 'mini_table', 'columns' => ['cups', 'total'], 'rows' => $m['tables']['a'] ?? []];
            $blocks[] = ['name' => 'Ultimos registros', 'type' => 'table', 'rows' => $m['tables']['b'] ?? []];
        } else {
            $blocks[] = ['name' => 'Municipios', 'type' => 'mini_table', 'columns' => ['municipio', 'total'], 'rows' => $m['tables']['a'] ?? []];
            $blocks[] = ['name' => 'Ultimos registros', 'type' => 'table', 'rows' => $m['tables']['b'] ?? []];
        }

        return response()->json(['ok' => true, 'title' => $name, 'summary' => $m['summary'] ?? [], 'blocks' => $blocks]);
    }

    public function exportPdf(Request $request)
    {
        $payload = json_decode((string) $request->input('payload', '{}'), true);
        if (!is_array($payload) || empty($payload)) {
            $payload = $this->dashboard($this->filters($request));
        }

        $chartImages = json_decode((string) $request->input('chart_images', '{}'), true);
        if (!is_array($chartImages)) {
            $chartImages = [];
        }

        $pdf = Pdf::loadView('gestantes.stats.pdf', [
            'payload' => $payload,
            'chartImages' => $chartImages,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('tablero_gestantes_' . now()->format('Ymd_His') . '.pdf');
    }
}
