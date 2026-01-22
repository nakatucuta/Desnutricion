<?php

namespace App\Http\Controllers;

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

    private function applyUserFilter($query, string $userCol = 'user_id')
    {
        if (Auth::user() && (int) Auth::user()->usertype === 2) {
            if (Schema::hasColumn($query->from, $userCol)) {
                $query->where($userCol, Auth::id());
            }
        }
        return $query;
    }

    private function safeCount(string $table): int
    {
        if (!Schema::hasTable($table)) return 0;

        $q = DB::table($table);

        if (Schema::hasColumn($table, 'user_id')) {
            $q = $this->applyUserFilter($q, 'user_id');
        }

        return (int) $q->count();
    }

    private function monthlyCounts(string $table, int $monthsBack = 6): array
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'created_at')) {
            return ['labels' => [], 'values' => []];
        }

        $q = DB::table($table)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as c')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)');

        if (Schema::hasColumn($table, 'user_id')) {
            $q = $this->applyUserFilter($q, 'user_id');
        }

        $rows = $q->get();

        $labels = [];
        $values = [];
        foreach ($rows as $r) {
            $labels[] = sprintf('%04d-%02d', $r->y, $r->m);
            $values[] = (int)$r->c;
        }

        if (count($labels) > $monthsBack) {
            $labels = array_slice($labels, -$monthsBack);
            $values = array_slice($values, -$monthsBack);
        }

        return compact('labels', 'values');
    }

    public function index()
    {
        $tables = [
            'preconcepcional' => 'preconcepcionales',
            'tipo1'           => 'ges_tipo1',
            'tipo3'           => 'ges_tipo3',
            'siv549'          => 'asignaciones_maestrosiv549',
        ];

        $stats = [
            'preconcepcional' => [
                'table'  => $tables['preconcepcional'],
                'exists' => Schema::hasTable($tables['preconcepcional']),
                'count'  => $this->safeCount($tables['preconcepcional']),
            ],
            'tipo1' => [
                'table'  => $tables['tipo1'],
                'exists' => Schema::hasTable($tables['tipo1']),
                'count'  => $this->safeCount($tables['tipo1']),
            ],
            'tipo3' => [
                'table'  => $tables['tipo3'],
                'exists' => Schema::hasTable($tables['tipo3']),
                'count'  => $this->safeCount($tables['tipo3']),
            ],
            'siv549' => [
                'table'  => $tables['siv549'],
                'exists' => Schema::hasTable($tables['siv549']),
                'count'  => $this->safeCount($tables['siv549']),
            ],
        ];

        // Extra Tipo1 (FPP)
        $tipo1Extra = ['proximas_4_semanas' => 0, 'vencidas' => 0];
        if ($stats['tipo1']['exists']) {
            $q = DB::table('ges_tipo1');
            $q = $this->applyUserFilter($q, 'user_id');

            $tipo1Extra['proximas_4_semanas'] = (int) (clone $q)
                ->whereBetween('fecha_probable_de_parto', [now()->toDateString(), now()->addDays(28)->toDateString()])
                ->count();

            $tipo1Extra['vencidas'] = (int) (clone $q)
                ->where('fecha_probable_de_parto', '<', now()->toDateString())
                ->count();
        }

        // Extra Precon (riesgo)
        $preconExtra = ['alto_riesgo' => 0, 'medio_riesgo' => 0, 'bajo_riesgo' => 0];
        if ($stats['preconcepcional']['exists'] && Schema::hasColumn('preconcepcionales', 'riesgo_preconcepcional')) {
            $q = DB::table('preconcepcionales');
            $preconExtra['alto_riesgo']  = (int) (clone $q)->where('riesgo_preconcepcional', 'like', '%ALTO%')->count();
            $preconExtra['medio_riesgo'] = (int) (clone $q)->where('riesgo_preconcepcional', 'like', '%MEDIO%')->count();
            $preconExtra['bajo_riesgo']  = (int) (clone $q)->where('riesgo_preconcepcional', 'like', '%BAJO%')->count();
        }

        // Chart módulo
        $chartModules = [
            'labels' => ['Preconcepcional', 'Tipo 1', 'Tipo 3', 'SIV549'],
            'values' => [
                $stats['preconcepcional']['count'],
                $stats['tipo1']['count'],
                $stats['tipo3']['count'],
                $stats['siv549']['count'],
            ],
        ];

        // Charts mensuales
        $chartTipo1Monthly  = $this->monthlyCounts('ges_tipo1', 6);
        $chartTipo3Monthly  = $this->monthlyCounts('ges_tipo3', 6);
        $chartPreconMonthly = $this->monthlyCounts('preconcepcionales', 6);
        $chart549Monthly    = $this->monthlyCounts('asignaciones_maestrosiv549', 6);

        // Chart riesgo
        $chartPreconRiesgo = [
            'labels' => ['ALTO', 'MEDIO', 'BAJO'],
            'values' => [$preconExtra['alto_riesgo'], $preconExtra['medio_riesgo'], $preconExtra['bajo_riesgo']],
        ];

        return view('gestantes.stats.index', compact(
            'stats',
            'tipo1Extra',
            'preconExtra',
            'chartModules',
            'chartTipo1Monthly',
            'chartTipo3Monthly',
            'chartPreconMonthly',
            'chart549Monthly',
            'chartPreconRiesgo'
        ));
    }

    public function detail(string $modulo)
    {
        if (!request()->ajax()) abort(404);

        switch ($modulo) {

            // ===========================
            // PRECONCEPCIONAL
            // ===========================
            case 'preconcepcional':

                if (!Schema::hasTable('preconcepcionales')) {
                    return response()->json(['ok' => false, 'message' => 'Tabla preconcepcionales no encontrada.'], 404);
                }

                $base = DB::table('preconcepcionales');

                $summary = [
                    'Total' => (int)(clone $base)->count(),
                    'Con teléfono' => Schema::hasColumn('preconcepcionales', 'telefono')
                        ? (int)(clone $base)->whereNotNull('telefono')->where('telefono', '<>', '')->count()
                        : 0,
                    'Con IMC' => Schema::hasColumn('preconcepcionales', 'imc')
                        ? (int)(clone $base)->whereNotNull('imc')->count()
                        : 0,
                    'Alto riesgo' => Schema::hasColumn('preconcepcionales', 'riesgo_preconcepcional')
                        ? (int)(clone $base)->where('riesgo_preconcepcional', 'like', '%ALTO%')->count()
                        : 0,
                ];

                // ✅ NO TOP: conteo por municipio (TODOS)
                $municipios = Schema::hasColumn('preconcepcionales', 'municipio_residencia')
                    ? DB::table('preconcepcionales')
                        ->selectRaw("COALESCE(NULLIF(municipio_residencia,''), 'SIN MUNICIPIO') as municipio, COUNT(*) as total")
                        ->groupByRaw("COALESCE(NULLIF(municipio_residencia,''), 'SIN MUNICIPIO')")
                        ->orderByDesc('total')
                        ->get()
                    : collect();

                $riesgo = Schema::hasColumn('preconcepcionales', 'riesgo_preconcepcional')
                    ? DB::table('preconcepcionales')
                        ->selectRaw("COALESCE(NULLIF(riesgo_preconcepcional,''), 'SIN RIESGO') as riesgo, COUNT(*) as total")
                        ->groupByRaw("COALESCE(NULLIF(riesgo_preconcepcional,''), 'SIN RIESGO')")
                        ->orderByDesc('total')
                        ->get()
                    : collect();

                $latest = DB::table('preconcepcionales')
                    ->select(
                        'id',
                        'tipo_documento',
                        'numero_identificacion',
                        'apellido_1',
                        'apellido_2',
                        'nombre_1',
                        'nombre_2',
                        'municipio_residencia',
                        'telefono',
                        'riesgo_preconcepcional',
                        'created_at'
                    )
                    ->orderByDesc('id')
                    ->limit(30)
                    ->get();

                $batches = Schema::hasTable('preconcepcional_import_batches')
                    ? DB::table('preconcepcional_import_batches')
                        ->select('id', 'original_name', 'rows_total', 'rows_created', 'rows_updated', 'rows_skipped', 'duration_seconds', 'created_at')
                        ->orderByDesc('id')
                        ->limit(15)
                        ->get()
                    : collect();

                return response()->json([
                    'ok' => true,
                    'title' => 'Preconcepcional',
                    'summary' => $summary,
                    'blocks' => [
                        [
                            'name' => 'Municipios (conteo)',
                            'type' => 'mini_table',
                            'columns' => ['municipio', 'total'],
                            'rows' => $municipios,
                        ],
                        [
                            'name' => 'Riesgo (conteo)',
                            'type' => 'mini_table',
                            'columns' => ['riesgo', 'total'],
                            'rows' => $riesgo,
                        ],
                        [
                            'name' => 'Últimos registros',
                            'type' => 'table',
                            'rows' => $latest,
                        ],
                        [
                            'name' => 'Últimos lotes importados',
                            'type' => 'table',
                            'rows' => $batches,
                        ],
                    ],
                ]);

            // ===========================
            // TIPO 1
            // ===========================
            case 'tipo1':

                if (!Schema::hasTable('ges_tipo1')) {
                    return response()->json(['ok' => false, 'message' => 'Tabla ges_tipo1 no encontrada.'], 404);
                }

                $base = DB::table('ges_tipo1');
                $base = $this->applyUserFilter($base, 'user_id');

                $summary = [
                    'Total' => (int)(clone $base)->count(),
                    'FPP próximas 4 semanas' => (int)(clone $base)
                        ->whereBetween('fecha_probable_de_parto', [now()->toDateString(), now()->addDays(28)->toDateString()])
                        ->count(),
                    'FPP vencidas' => (int)(clone $base)->where('fecha_probable_de_parto', '<', now()->toDateString())->count(),
                    'Cargadas hoy' => Schema::hasColumn('ges_tipo1', 'created_at')
                        ? (int)(clone $base)->whereDate('created_at', now()->toDateString())->count()
                        : 0,
                ];

                // ✅ NO TOP: conteo por municipio con JOIN a sga..municipios
                // tu campo: ges_tipo1.municipio_de_residencia_habitual = CONCAT(M.codigoDepartamento, M.codigoMunicipio)
                $municipios = DB::table('ges_tipo1 as A')
                    ->leftJoin(DB::raw('sga..municipios as M'), function ($join) {
                        $join->on(
                            DB::raw('A.municipio_de_residencia_habitual'),
                            '=',
                            DB::raw("CONCAT(M.codigoDepartamento, M.codigoMunicipio)")
                        );
                    })
                    ->selectRaw("
                        COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.municipio_de_residencia_habitual, ')')) as municipio,
                        COUNT(*) as total
                    ")
                    ->groupByRaw("COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.municipio_de_residencia_habitual, ')'))")
                    ->orderByDesc('total');

                $municipios = $this->applyUserFilter($municipios, 'user_id')->get();

                $latest = DB::table('ges_tipo1')
                    ->select(
                        'id','primer_nombre','segundo_nombre','primer_apellido','segundo_apellido',
                        'tipo_de_identificacion_de_la_usuaria','no_id_del_usuario',
                        'municipio_de_residencia_habitual','fecha_probable_de_parto','created_at'
                    )
                    ->orderByDesc('id');

                $latest = $this->applyUserFilter($latest, 'user_id')->limit(30)->get();

                return response()->json([
                    'ok' => true,
                    'title' => 'Gestantes Tipo 1',
                    'summary' => $summary,
                    'blocks' => [
                        [
                            'name' => 'Municipios (conteo)',
                            'type' => 'mini_table',
                            'columns' => ['municipio', 'total'],
                            'rows' => $municipios,
                        ],
                        [
                            'name' => 'Últimos registros',
                            'type' => 'table',
                            'rows' => $latest,
                        ],
                    ],
                ]);

            // ===========================
            // TIPO 3
            // ===========================
            case 'tipo3':

                if (!Schema::hasTable('ges_tipo3')) {
                    return response()->json(['ok' => false, 'message' => 'Tabla ges_tipo3 no encontrada.'], 404);
                }

                $base = DB::table('ges_tipo3');
                $base = $this->applyUserFilter($base, 'user_id');

                $summary = [
                    'Total' => (int)(clone $base)->count(),
                    'Con CUPS' => (int)(clone $base)->whereNotNull('codigo_cups_de_la_tecnologia_en_salud')->count(),
                    'Con riesgo gestacional' => (int)(clone $base)->whereNotNull('clasificacion_riesgo_gestacional')->count(),
                    'Registros hoy' => Schema::hasColumn('ges_tipo3', 'created_at')
                        ? (int)(clone $base)->whereDate('created_at', now()->toDateString())->count()
                        : 0,
                ];

                $topCups = DB::table('ges_tipo3')
                    ->select('codigo_cups_de_la_tecnologia_en_salud as cups', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('codigo_cups_de_la_tecnologia_en_salud')
                    ->groupBy('codigo_cups_de_la_tecnologia_en_salud')
                    ->orderByDesc('total');

                $topCups = $this->applyUserFilter($topCups, 'user_id')->limit(10)->get();

                $latest = DB::table('ges_tipo3')
                    ->select(
                        'id','ges_tipo1_id','tipo_identificacion_de_la_usuaria','no_id_del_usuario',
                        'fecha_tecnologia_en_salud','codigo_cups_de_la_tecnologia_en_salud',
                        'clasificacion_riesgo_gestacional','clasificacion_riesgo_preeclampsia','created_at'
                    )
                    ->orderByDesc('id');

                $latest = $this->applyUserFilter($latest, 'user_id')->limit(30)->get();

                return response()->json([
                    'ok' => true,
                    'title' => 'Gestantes Tipo 3',
                    'summary' => $summary,
                    'blocks' => [
                        [
                            'name' => 'Top CUPS',
                            'type' => 'mini_table',
                            'columns' => ['cups', 'total'],
                            'rows' => $topCups,
                        ],
                        [
                            'name' => 'Últimos registros',
                            'type' => 'table',
                            'rows' => $latest,
                        ],
                    ],
                ]);

            // ===========================
            // SIV549
            // ===========================
            case 'siv549':

                if (!Schema::hasTable('asignaciones_maestrosiv549')) {
                    return response()->json(['ok' => false, 'message' => 'Tabla asignaciones_maestrosiv549 no encontrada.'], 404);
                }

                $base = DB::table('asignaciones_maestrosiv549');
                $base = $this->applyUserFilter($base, 'user_id');

                $summary = [
                    'Total' => (int)(clone $base)->count(),
                    'Notificados hoy' => Schema::hasColumn('asignaciones_maestrosiv549', 'fec_not')
                        ? (int)(clone $base)->whereDate('fec_not', now()->toDateString())->count()
                        : 0,
                ];

                // ✅ NO TOP: conteo por municipio con JOIN a sga..municipios
                $municipios = DB::table('asignaciones_maestrosiv549 as A')
                    ->leftJoin(DB::raw('sga..municipios as M'), function ($join) {
                        $join->on('A.cod_dpto_o', '=', 'M.codigoDepartamento')
                             ->on('A.cod_mun_o', '=', 'M.codigoMunicipio');
                    })
                    ->selectRaw("
                        COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.cod_dpto_o, '-', A.cod_mun_o, ')')) as municipio,
                        COUNT(*) as total
                    ")
                    ->groupByRaw("COALESCE(M.descrip, CONCAT('SIN MAPEO (', A.cod_dpto_o, '-', A.cod_mun_o, ')'))")
                    ->orderByDesc('total');

                $municipios = $this->applyUserFilter($municipios, 'user_id')->get();

                $latest = DB::table('asignaciones_maestrosiv549')
                    ->select(
                        'id','fec_not','semana','pri_nom_','seg_nom_','pri_ape_','seg_ape_',
                        'tip_ide_','num_ide_','cod_dpto_o','cod_mun_o','telefono_','created_at'
                    )
                    ->orderByDesc('id');

                $latest = $this->applyUserFilter($latest, 'user_id')->limit(30)->get();

                return response()->json([
                    'ok' => true,
                    'title' => 'MaestroSIV549 (Asignaciones)',
                    'summary' => $summary,
                    'blocks' => [
                        [
                            'name' => 'Municipios (conteo)',
                            'type' => 'mini_table',
                            'columns' => ['municipio', 'total'],
                            'rows' => $municipios,
                        ],
                        [
                            'name' => 'Últimos registros',
                            'type' => 'table',
                            'rows' => $latest,
                        ],
                    ],
                ]);

            default:
                return response()->json(['ok' => false, 'message' => 'Módulo inválido.'], 404);
        }
    }
}
