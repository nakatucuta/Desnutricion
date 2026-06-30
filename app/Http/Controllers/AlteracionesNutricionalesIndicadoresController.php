<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlteracionesNutricionalesIndicadoresController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:access-nutricional']);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'evento' => 'nullable|in:all,113,412,114',
            'clasificacion' => 'nullable|string',
        ]);

        $selectedClasificacion = $this->normalizeClassFilter($validated['clasificacion'] ?? 'all');
        $desde = Carbon::parse($validated['desde'] ?? now()->subDays(30))->startOfDay();
        $hasta = Carbon::parse($validated['hasta'] ?? now())->endOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return view('alteraciones_nutricionales.indicadores', [
            'defaultDesde' => $desde->toDateString(),
            'defaultHasta' => $hasta->toDateString(),
            'selectedEvento' => $validated['evento'] ?? 'all',
            'selectedClasificacion' => $selectedClasificacion,
            'initialPayload' => $this->buildPayload($desde, $hasta, $validated['evento'] ?? 'all', $selectedClasificacion),
            'classificationOptions' => $this->buildPayload($desde, $hasta, $validated['evento'] ?? 'all', 'all')['clasificaciones_totales'],
            'selectedCodigo' => $request->query('codigo'),
        ]);
    }

    public function data(Request $request)
    {
        $validated = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'evento' => 'nullable|in:all,113,412,114',
            'clasificacion' => 'nullable|string',
        ]);

        $selectedClasificacion = $this->normalizeClassFilter($validated['clasificacion'] ?? 'all');
        $desde = Carbon::parse($validated['desde'] ?? now()->subDays(30))->startOfDay();
        $hasta = Carbon::parse($validated['hasta'] ?? now())->endOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return response()->json($this->buildPayload($desde, $hasta, $validated['evento'] ?? 'all', $selectedClasificacion));
    }

    public function trace(Request $request)
    {
        $validated = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'codigo' => 'required|string',
            'evento' => 'nullable|in:all,113,412,114',
            'clasificacion' => 'nullable|string',
        ]);

        $selectedClasificacion = $this->normalizeClassFilter($validated['clasificacion'] ?? 'all');
        $desde = Carbon::parse($validated['desde'] ?? now()->subDays(30))->startOfDay();
        $hasta = Carbon::parse($validated['hasta'] ?? now())->endOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return response()->json($this->buildTracePayload($validated['codigo'], $desde, $hasta, $validated['evento'] ?? 'all', $selectedClasificacion));
    }

    public function gaps(Request $request)
    {
        $validated = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'evento' => 'nullable|in:all,113,412,114',
            'clasificacion' => 'nullable|string',
        ]);

        $selectedClasificacion = $this->normalizeClassFilter($validated['clasificacion'] ?? 'all');
        $desde = Carbon::parse($validated['desde'] ?? now()->subDays(30))->startOfDay();
        $hasta = Carbon::parse($validated['hasta'] ?? now())->endOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return response()->json($this->buildGapPayload($desde, $hasta, $validated['evento'] ?? 'all', $selectedClasificacion));
    }

    private function buildPayload(Carbon $desde, Carbon $hasta, string $eventoFiltro = 'all', string $clasificacionFiltro = 'all'): array
    {
        $eventos = $this->eventList($eventoFiltro);
        $clasificacionFiltro = $this->normalizeClassFilter($clasificacionFiltro);
        $stats = [];

        foreach ($eventos as $evento) {
            $assignmentQuery = $this->assignmentQuery($evento, $desde, $hasta);
            $assignmentRows = $assignmentQuery->get();
            $followUpDetails = $this->followUpDetails($evento, $desde, $hasta, $assignmentQuery, '');
            $followUpsByCase = $followUpDetails->groupBy(fn ($row) => (string) ($row->case_id ?? ''));
            $latestFollowUps = $this->latestFollowUpsByCase($followUpDetails);

            foreach ($assignmentRows->groupBy('codigo') as $codigo => $group) {
                $stats[$codigo]['codigo'] = $codigo;
                $stats[$codigo]['events'][$evento]['asignados'] = (int) $group->count();
                $stats[$codigo]['users'][$evento] = $group
                    ->groupBy('user_id')
                    ->map(function (Collection $usersGroup, $userId) {
                        $first = $usersGroup->first();

                        return [
                            'id' => (int) $userId,
                            'name' => (string) ($first->user_name ?? $first->name ?? 'Sin nombre'),
                            'count' => (int) $usersGroup->count(),
                        ];
                    })
                    ->sortByDesc('count')
                    ->values()
                    ->all();

                $eventFollowUps = $followUpDetails->filter(function ($row) use ($group) {
                    $caseIds = $group->pluck('case_id')->map(fn ($value) => (string) $value)->all();

                    return in_array((string) ($row->case_id ?? ''), $caseIds, true);
                })->values();
                $eventFollowUpCases = $this->latestFollowUpsByCase($eventFollowUps);
                if ($clasificacionFiltro !== 'all') {
                    $eventFollowUpCases = $eventFollowUpCases->filter(function ($row) use ($clasificacionFiltro) {
                        return $this->normalizeLabel($row->clasificacion ?? '', 'SIN CLASIFICACION') === $clasificacionFiltro;
                    })->values();
                }
                $eventQuality = $this->summarizeEventQuality($group, $eventFollowUps, $followUpsByCase);

                $stats[$codigo]['events'][$evento]['primer_seguimiento_promedio'] = $eventQuality['primer_seguimiento_promedio'];
                $stats[$codigo]['events'][$evento]['primer_seguimiento_dias_sum'] = $eventQuality['primer_seguimiento_dias_sum'];
                $stats[$codigo]['events'][$evento]['primer_seguimiento_casos'] = $eventQuality['primer_seguimiento_casos'];
                $stats[$codigo]['events'][$evento]['casos_clasificacion_final'] = $eventQuality['casos_clasificacion_final'];
                $stats[$codigo]['events'][$evento]['calidad_total'] = $eventQuality['calidad_total'];
                $stats[$codigo]['events'][$evento]['calidad_completos'] = $eventQuality['calidad_completos'];
                $stats[$codigo]['events'][$evento]['calidad_incompletos'] = $eventQuality['calidad_incompletos'];
                $stats[$codigo]['events'][$evento]['calidad_inconsistentes'] = $eventQuality['calidad_inconsistentes'];
                $stats[$codigo]['events'][$evento]['calidad_porcentaje'] = $eventQuality['calidad_porcentaje'];
                $stats[$codigo]['events'][$evento]['seguimientos'] = (int) $eventFollowUpCases->count();
                $stats[$codigo]['events'][$evento]['clasificaciones'] = $eventFollowUpCases
                    ->groupBy(function ($followUp) {
                        return $this->normalizeLabel($followUp->clasificacion ?? '', 'SIN CLASIFICACION');
                    })
                    ->map(fn (Collection $caseGroup, $label) => [
                        'label' => $label,
                        'count' => (int) $caseGroup->count(),
                    ])
                    ->values()
                    ->all();
            }
        }

        $usersByCode = $this->usersGroupedByCode();

        $rows = collect($stats)
            ->map(function (array $row) use ($usersByCode, $eventos, $clasificacionFiltro) {
                $codigo = $row['codigo'];
                $users = $usersByCode[$codigo] ?? collect();
                $topUser = collect($row['users'] ?? [])
                    ->flatten(1)
                    ->sortByDesc('count')
                    ->first();
                $topUserName = $topUser['name'] ?? null;
                $topUserCount = (int) ($topUser['count'] ?? 0);
                $eventData = [];
                $clasificacionesTotales = [];
                $primerSeguimientoDiasSum = 0.0;
                $primerSeguimientoCasos = 0;
                $casosClasificacionFinal = 0;
                $calidadTotal = 0;
                $calidadCompletos = 0;
                $calidadIncompletos = 0;
                $calidadInconsistentes = 0;

                foreach ($eventos as $evento) {
                    $eventoData = $row['events'][$evento] ?? [];
                    $asignados = (int) ($eventoData['asignados'] ?? 0);
                    $clasificaciones = collect($eventoData['clasificaciones'] ?? [])
                        ->map(fn ($item) => [
                            'label' => $this->normalizeLabel($item['label'] ?? '', 'SIN CLASIFICACION'),
                            'count' => (int) ($item['count'] ?? 0),
                        ])
                        ->sortByDesc('count')
                        ->values();

                    if ($clasificacionFiltro !== 'all') {
                        $clasificaciones = $clasificaciones->filter(function (array $item) use ($clasificacionFiltro) {
                            return $this->normalizeLabel($item['label'] ?? '', 'SIN CLASIFICACION') === $clasificacionFiltro;
                        })->values();
                    }

                    $seguimientos = (int) $clasificaciones->sum('count');
                    $cobertura = $asignados > 0 ? round(($seguimientos / $asignados) * 100, 2) : 0.0;
                    $clasificaciones = $clasificaciones->all();
                    $primerSeguimientoPromedio = $eventoData['primer_seguimiento_promedio'] ?? null;
                    $eventoDiasSum = (float) ($eventoData['primer_seguimiento_dias_sum'] ?? 0);
                    $eventoCasosPrimerSeg = (int) ($eventoData['primer_seguimiento_casos'] ?? 0);
                    if ($eventoCasosPrimerSeg > 0) {
                        $primerSeguimientoDiasSum += $eventoDiasSum;
                        $primerSeguimientoCasos += $eventoCasosPrimerSeg;
                    }
                    $casosClasificacionFinal += (int) ($eventoData['casos_clasificacion_final'] ?? 0);
                    $calidadTotal += (int) ($eventoData['calidad_total'] ?? 0);
                    $calidadCompletos += (int) ($eventoData['calidad_completos'] ?? 0);
                    $calidadIncompletos += (int) ($eventoData['calidad_incompletos'] ?? 0);
                    $calidadInconsistentes += (int) ($eventoData['calidad_inconsistentes'] ?? 0);

                    foreach ($clasificaciones as $clasificacion) {
                        $clasificacionesTotales[$clasificacion['label']] = (int) ($clasificacionesTotales[$clasificacion['label']] ?? 0) + (int) $clasificacion['count'];
                    }

                    $eventData[$evento] = [
                        'asignados' => $asignados,
                        'seguimientos' => $seguimientos,
                        'cobertura' => $cobertura,
                        'clasificaciones' => $clasificaciones,
                    ];
                }

                arsort($clasificacionesTotales);
                $totalAsignados = collect($eventData)->sum('asignados');
                $totalSeguimientos = collect($eventData)->sum('seguimientos');
                $totalCobertura = $totalAsignados > 0 ? round(($totalSeguimientos / $totalAsignados) * 100, 2) : 0.0;
                $primerSeguimientoPromedio = $primerSeguimientoCasos > 0 ? round($primerSeguimientoDiasSum / $primerSeguimientoCasos, 2) : null;
                $calidadDato = $calidadTotal > 0 ? round(($calidadCompletos / $calidadTotal) * 100, 2) : null;
                $usersCount = $users->count();
                $usersLabel = $users->take(4)->pluck('name')->implode(' · ');

                if ($usersCount > 4) {
                    $usersLabel .= ' · +' . ($usersCount - 4) . ' mas';
                }

                return [
                    'codigo' => $codigo,
                    'usuario_destacado' => $topUserName !== null ? $topUserName : ($users->first()['name'] ?? 'Sin usuario destacado'),
                    'usuario_destacado_count' => $topUserCount,
                    'usuarios_count' => $usersCount,
                    'usuarios_label' => $usersLabel !== '' ? $usersLabel : 'Sin usuarios asociados',
                    'usuarios' => $users->values()->all(),
                    'events' => $eventData,
                    'active_events' => collect($eventData)->filter(fn ($i) => ((int) ($i['asignados'] ?? 0) + (int) ($i['seguimientos'] ?? 0)) > 0)->keys()->values()->all(),
                    'total_asignados' => $totalAsignados,
                    'total_seguimientos' => $totalSeguimientos,
                    'sin_seguimiento' => max(0, $totalAsignados - $totalSeguimientos),
                    'total_cobertura' => $totalCobertura,
                    'total_gap' => max(0, $totalAsignados - $totalSeguimientos),
                    'primer_seguimiento_promedio' => $primerSeguimientoPromedio,
                    'primer_seguimiento_dias_sum' => $primerSeguimientoDiasSum,
                    'primer_seguimiento_casos' => $primerSeguimientoCasos,
                    'casos_clasificacion_final' => $casosClasificacionFinal,
                    'calidad_total' => $calidadTotal,
                    'calidad_completos' => $calidadCompletos,
                    'calidad_incompletos' => $calidadIncompletos,
                    'calidad_inconsistentes' => $calidadInconsistentes,
                    'calidad_dato' => $calidadDato,
                    'trace' => collect($eventData)->map(function (array $eventoData, string $evento) {
                        return [
                            'evento' => $evento,
                            'label' => 'Evento ' . $evento,
                            'asignados' => (int) ($eventoData['asignados'] ?? 0),
                            'seguimientos' => (int) ($eventoData['seguimientos'] ?? 0),
                            'clasificaciones' => collect($eventoData['clasificaciones'] ?? [])->values()->all(),
                            'primer_seguimiento_promedio' => $eventoData['primer_seguimiento_promedio'] ?? null,
                            'primer_seguimiento_dias_sum' => (float) ($eventoData['primer_seguimiento_dias_sum'] ?? 0),
                            'primer_seguimiento_casos' => (int) ($eventoData['primer_seguimiento_casos'] ?? 0),
                            'casos_clasificacion_final' => (int) ($eventoData['casos_clasificacion_final'] ?? 0),
                            'calidad_total' => (int) ($eventoData['calidad_total'] ?? 0),
                            'calidad_completos' => (int) ($eventoData['calidad_completos'] ?? 0),
                            'calidad_incompletos' => (int) ($eventoData['calidad_incompletos'] ?? 0),
                            'calidad_inconsistentes' => (int) ($eventoData['calidad_inconsistentes'] ?? 0),
                            'calidad_dato' => $eventoData['calidad_porcentaje'] ?? null,
                        ];
                    })->values()->all(),
                    'clasificaciones' => collect($clasificacionesTotales)
                        ->map(fn ($count, $label) => [
                            'label' => $label,
                            'count' => (int) $count,
                        ])
                        ->values()
                        ->all(),
                    'clasificaciones_label' => collect($clasificacionesTotales)
                        ->take(3)
                        ->map(fn ($count, $label) => $label . ' (' . $count . ')')
                        ->implode(' · '),
                ];
            })
            ->filter(function (array $row) use ($clasificacionFiltro) {
                if ($clasificacionFiltro === 'all') {
                    return true;
                }

                return collect($row['events'] ?? [])
                    ->contains(function (array $eventoData) use ($clasificacionFiltro) {
                        return collect($eventoData['clasificaciones'] ?? [])
                            ->contains(fn ($item) => $this->normalizeLabel($item['label'] ?? '', 'SIN CLASIFICACION') === $clasificacionFiltro);
                    });
            })
            ->sortByDesc('total_cobertura')
            ->values();

        $rowsForOptions = $rows->values();

        $totales = [
            'prestadores' => $rows->count(),
            'usuarios' => collect($rows)->sum('usuarios_count'),
            'asignados' => (int) $rows->sum('total_asignados'),
            'seguimientos' => (int) $rows->sum('total_seguimientos'),
            'brecha' => (int) $rows->sum('total_gap'),
            'primer_seguimiento_dias_sum' => (float) $rows->sum('primer_seguimiento_dias_sum'),
            'primer_seguimiento_casos' => (int) $rows->sum('primer_seguimiento_casos'),
            'casos_clasificacion_final' => (int) $rows->sum('casos_clasificacion_final'),
            'calidad_total' => (int) $rows->sum('calidad_total'),
            'calidad_completos' => (int) $rows->sum('calidad_completos'),
            'calidad_incompletos' => (int) $rows->sum('calidad_incompletos'),
            'calidad_inconsistentes' => (int) $rows->sum('calidad_inconsistentes'),
        ];
        $totales['cobertura'] = $totales['asignados'] > 0
            ? round(($totales['seguimientos'] / $totales['asignados']) * 100, 2)
            : 0.0;
        $totales['primer_seguimiento_promedio'] = $totales['primer_seguimiento_casos'] > 0
            ? round($totales['primer_seguimiento_dias_sum'] / $totales['primer_seguimiento_casos'], 2)
            : null;
        $totales['calidad_dato'] = $totales['calidad_total'] > 0
            ? round(($totales['calidad_completos'] / $totales['calidad_total']) * 100, 2)
            : 0.0;

        $eventSummary = collect($eventos)->mapWithKeys(function (string $evento) use ($rows) {
            $eventoRows = $rows->filter(fn ($row) => isset($row['events'][$evento]));
            $asignados = (int) $eventoRows->sum(fn ($row) => (int) ($row['events'][$evento]['asignados'] ?? 0));
            $seguimientos = (int) $eventoRows->sum(fn ($row) => (int) ($row['events'][$evento]['seguimientos'] ?? 0));
            $cobertura = $asignados > 0 ? round(($seguimientos / $asignados) * 100, 2) : 0.0;

            return [$evento => [
                'asignados' => $asignados,
                'seguimientos' => $seguimientos,
                'cobertura' => $cobertura,
                'prestadores' => $eventoRows->count(),
            ]];
        });

        $classificationTotals = $rowsForOptions
            ->flatMap(fn ($row) => $row['clasificaciones'])
            ->groupBy('label')
            ->map(fn ($group, $label) => [
                'label' => $label,
                'count' => (int) $group->sum('count'),
            ])
            ->sortByDesc('count')
            ->values();

        return [
            'ok' => true,
            'range' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
            'totales' => $totales,
            'eventos' => $eventSummary,
            'clasificaciones_totales' => $classificationTotals,
            'rows' => $rows,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function assignmentQuery(string $evento, Carbon $desde, Carbon $hasta)
    {
        if ($evento === '412') {
            return DB::table('cargue412s as c')
                ->join('users as u', 'u.id', '=', 'c.user_id')
                ->whereRaw('TRY_CONVERT(datetime2, c.fecha_captacion) BETWEEN ? AND ?', [$desde, $hasta])
                ->selectRaw($this->codeExpr('u') . ' as codigo, c.id as case_id, u.id as user_id, u.name as user_name, c.primer_nombre as patient_first_name, c.segundo_nombre as patient_second_name, c.primer_apellido as patient_first_lastname, c.segundo_apellido as patient_second_lastname, c.edad_meses as patient_age, c.fecha_captacion as fecha_asignacion, c.tipo_identificacion as tipo_identificacion, c.numero_identificacion as numero_identificacion')
                ;
        }

        return DB::table('sivigilas as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('s.cod_eve', (int) $evento)
            ->whereRaw('TRY_CONVERT(datetime2, s.fec_not) BETWEEN ? AND ?', [$desde, $hasta])
            ->selectRaw($this->codeExpr('u') . ' as codigo, s.id as case_id, u.id as user_id, u.name as user_name, s.pri_nom_ as patient_first_name, s.seg_nom_ as patient_second_name, s.pri_ape_ as patient_first_lastname, s.seg_ape_ as patient_second_lastname, CAST(s.edad_ as NVARCHAR(80)) as patient_age, s.fec_not as fecha_asignacion, s.tip_ide_ as tipo_identificacion, s.num_ide_ as numero_identificacion')
            ;
    }

    private function followUpRows(string $evento, Carbon $desde, Carbon $hasta, $assignmentQuery): Collection
    {
        if ($evento === '412') {
            return DB::table('seguimiento_412s as seg')
                ->joinSub($assignmentQuery, 'a', function ($join) {
                    $join->on('a.case_id', '=', 'seg.cargue412_id');
                })
                ->whereRaw('TRY_CONVERT(datetime2, seg.created_at) BETWEEN ? AND ?', [$desde, $hasta])
                ->selectRaw('a.codigo as codigo, ' . $this->classExpr('seg') . ' as clasificacion, COUNT(DISTINCT seg.id) as total')
                ->groupByRaw('a.codigo, ' . $this->classExpr('seg'))
                ->orderBy('a.codigo')
                ->get();
        }

        return DB::table('seguimientos as seg')
            ->joinSub($assignmentQuery, 'a', function ($join) {
                $join->on('a.case_id', '=', 'seg.sivigilas_id');
            })
            ->whereRaw('TRY_CONVERT(datetime2, seg.created_at) BETWEEN ? AND ?', [$desde, $hasta])
            ->selectRaw('a.codigo as codigo, ' . $this->classExpr('seg') . ' as clasificacion, COUNT(DISTINCT seg.id) as total')
            ->groupByRaw('a.codigo, ' . $this->classExpr('seg'))
            ->orderBy('a.codigo')
            ->get();
    }

    private function followUpDetails(string $evento, Carbon $desde, Carbon $hasta, $assignmentQuery, string $codigo): Collection
    {
        if ($evento === '412') {
            return DB::table('seguimiento_412s as seg')
                ->joinSub($assignmentQuery, 'a', function ($join) {
                    $join->on('a.case_id', '=', 'seg.cargue412_id');
                })
                ->leftJoin('users as uu', 'uu.id', '=', 'seg.user_id')
                ->whereRaw('TRY_CONVERT(datetime2, seg.created_at) BETWEEN ? AND ?', [$desde, $hasta])
                ->selectRaw('
                    seg.id,
                    a.case_id,
                    seg.estado,
                    seg.fecha_consulta,
                    seg.peso_kilos,
                    seg.talla_cm,
                    seg.puntajez,
                    ' . $this->classExpr('seg') . ' as clasificacion,
                    seg.observaciones,
                    seg.fecha_proximo_control,
                    seg.perimetro_braqueal,
                    seg.user_id,
                    uu.name as user_name,
                    seg.created_at,
                    a.codigo,
                    a.patient_first_name,
                    a.patient_second_name,
                    a.patient_first_lastname,
                    a.patient_second_lastname,
                    a.patient_age
                ')
                ->orderByDesc('seg.created_at')
                ->get();
        }

        return DB::table('seguimientos as seg')
            ->joinSub($assignmentQuery, 'a', function ($join) {
                $join->on('a.case_id', '=', 'seg.sivigilas_id');
            })
            ->leftJoin('users as uu', 'uu.id', '=', 'seg.user_id')
            ->whereRaw('TRY_CONVERT(datetime2, seg.created_at) BETWEEN ? AND ?', [$desde, $hasta])
                ->selectRaw('
                    seg.id,
                    a.case_id,
                    seg.estado,
                    seg.fecha_consulta,
                    seg.peso_kilos,
                seg.talla_cm,
                seg.puntajez,
                ' . $this->classExpr('seg') . ' as clasificacion,
                seg.observaciones,
                seg.fecha_proximo_control,
                seg.medicamento,
                seg.motivo_reapuertura,
                seg.sivigilas_id,
                seg.user_id,
                uu.name as user_name,
                a.codigo,
                a.patient_first_name,
                a.patient_second_name,
                a.patient_first_lastname,
                a.patient_second_lastname,
                a.patient_age
            ')
            ->orderByDesc('seg.created_at')
            ->get();
    }

    private function summarizeEventQuality(Collection $assignments, Collection $followUps, Collection $followUpsByCase): array
    {
        $firstFollowUpDays = [];
        $finalClassifiedCases = 0;
        $qualityComplete = 0;
        $qualityIncomplete = 0;
        $qualityInconsistent = 0;

        foreach ($assignments as $assignment) {
            $caseId = (string) ($assignment->case_id ?? '');
            if ($caseId === '') {
                continue;
            }

            $caseFollowUps = $followUpsByCase->get($caseId, collect());
            if ($caseFollowUps->isNotEmpty()) {
                $firstFollowUp = $caseFollowUps->sortBy(fn ($row) => $this->traceDateValue($row->fecha_consulta ?? $row->created_at ?? null))->first();
                $assignmentDate = $this->traceDate($assignment->fecha_asignacion ?? null);
                $firstDate = $this->traceDate($firstFollowUp->fecha_consulta ?? $firstFollowUp->created_at ?? null);

                if ($assignmentDate !== null && $firstDate !== null) {
                    $firstFollowUpDays[] = $assignmentDate->diffInDays($firstDate);
                }

                $lastFollowUp = $caseFollowUps->sortByDesc(fn ($row) => $this->traceDateValue($row->fecha_consulta ?? $row->created_at ?? null))->first();
                $finalClassification = $this->normalizeLabel($lastFollowUp->clasificacion ?? '', 'SIN CLASIFICACION');
                if ($finalClassification !== 'SIN CLASIFICACION') {
                    $finalClassifiedCases++;
                }
            }
        }

        foreach ($followUps as $followUp) {
            $classification = $this->normalizeLabel($followUp->clasificacion ?? '', 'SIN CLASIFICACION');
            $peso = $followUp->peso_kilos ?? null;
            $talla = $followUp->talla_cm ?? null;
            $puntaje = $followUp->puntajez ?? null;

            $hasMissingCoreData = $classification === 'SIN CLASIFICACION'
                || $this->isBlankValue($peso)
                || $this->isBlankValue($talla)
                || $this->isBlankValue($puntaje);

            if ($hasMissingCoreData) {
                $qualityIncomplete++;
                continue;
            }

            if ($this->isInvalidNumeric($peso) || $this->isInvalidNumeric($talla) || $this->isInvalidNumeric($puntaje)) {
                $qualityInconsistent++;
                continue;
            }

            $qualityComplete++;
        }

        $qualityTotal = $qualityComplete + $qualityIncomplete + $qualityInconsistent;
        $firstFollowUpPromedio = count($firstFollowUpDays) > 0
            ? round(array_sum($firstFollowUpDays) / count($firstFollowUpDays), 2)
            : null;

        return [
            'primer_seguimiento_promedio' => $firstFollowUpPromedio,
            'primer_seguimiento_dias_sum' => array_sum($firstFollowUpDays),
            'primer_seguimiento_casos' => count($firstFollowUpDays),
            'casos_clasificacion_final' => $finalClassifiedCases,
            'calidad_total' => $qualityTotal,
            'calidad_completos' => $qualityComplete,
            'calidad_incompletos' => $qualityIncomplete,
            'calidad_inconsistentes' => $qualityInconsistent,
            'calidad_porcentaje' => $qualityTotal > 0 ? round(($qualityComplete / $qualityTotal) * 100, 2) : null,
        ];
    }

    private function latestFollowUpsByCase(Collection $followUps): Collection
    {
        return $followUps
            ->groupBy(fn ($row) => (string) ($row->case_id ?? ''))
            ->map(function (Collection $group) {
                return $group->sortByDesc(function ($row) {
                    return $this->traceDateValue($row->fecha_consulta ?? $row->created_at ?? null);
                })->first();
            })
            ->filter()
            ->values();
    }

    private function traceDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function traceDateValue($value): int
    {
        $date = $this->traceDate($value);

        return $date ? $date->timestamp : PHP_INT_MAX;
    }

    private function isBlankValue($value): bool
    {
        return trim((string) $value) === '';
    }

    private function isInvalidNumeric($value): bool
    {
        if ($this->isBlankValue($value)) {
            return true;
        }

        if (!is_numeric($value)) {
            return true;
        }

        return (float) $value <= 0;
    }

    private function eventList(string $eventoFiltro): array
    {
        return in_array($eventoFiltro, ['113', '412', '114'], true)
            ? [$eventoFiltro]
            : ['113', '412', '114'];
    }

    private function buildTracePayload(string $codigoInput, Carbon $desde, Carbon $hasta, string $eventoFiltro = 'all', string $clasificacionFiltro = 'all'): array
    {
        $codigo = $this->normalizeCode($codigoInput);
        $events = $this->eventList($eventoFiltro);
        $clasificacionFiltro = $this->normalizeClassFilter($clasificacionFiltro);
        $usersByCode = $this->usersGroupedByCode();
        $users = $usersByCode[$codigo] ?? collect();
        $trace = [];
        $patientHighlight = null;
        $patientAgeHighlight = null;

        foreach ($events as $evento) {
            $assignmentQuery = $this->assignmentQuery($evento, $desde, $hasta)->whereRaw($this->codeExpr('u') . ' = ?', [$codigo]);
            $assignmentRows = $assignmentQuery->get();
            $followUps = $this->followUpDetails($evento, $desde, $hasta, $assignmentQuery, $codigo);
            $followUpsByCase = $followUps->groupBy(fn ($row) => (string) ($row->case_id ?? ''));
            $followUpCases = $this->latestFollowUpsByCase($followUps);
            $eventQuality = $this->summarizeEventQuality($assignmentRows, $followUps, $followUpsByCase);
            $sinSeguimientoRows = $assignmentRows->filter(function ($row) use ($followUpsByCase) {
                return !$followUpsByCase->has((string) ($row->case_id ?? ''));
            })->values();

            if ($clasificacionFiltro !== 'all') {
                $followUpCases = $followUpCases->filter(function ($row) use ($clasificacionFiltro) {
                    return $this->normalizeLabel($row->clasificacion ?? '', 'SIN CLASIFICACION') === $clasificacionFiltro;
                })->values();
                $sinSeguimientoRows = $sinSeguimientoRows->filter(function ($row) use ($clasificacionFiltro, $followUpsByCase) {
                    $caseId = (string) ($row->case_id ?? '');
                    if (!$followUpsByCase->has($caseId)) {
                        return true;
                    }

                    return !$followUpsByCase->get($caseId, collect())->contains(function ($followUp) use ($clasificacionFiltro) {
                        return $this->normalizeLabel($followUp->clasificacion ?? '', 'SIN CLASIFICACION') === $clasificacionFiltro;
                    });
                })->values();
            }

            $trace[$evento] = [
                'asignados' => $assignmentRows->count(),
                'seguimientos' => $followUpCases->count(),
                'sin_seguimiento' => $sinSeguimientoRows->count(),
                'primer_seguimiento_promedio' => $eventQuality['primer_seguimiento_promedio'],
                'primer_seguimiento_dias_sum' => $eventQuality['primer_seguimiento_dias_sum'],
                'primer_seguimiento_casos' => $eventQuality['primer_seguimiento_casos'],
                'casos_clasificacion_final' => $eventQuality['casos_clasificacion_final'],
                'calidad_total' => $eventQuality['calidad_total'],
                'calidad_completos' => $eventQuality['calidad_completos'],
                'calidad_incompletos' => $eventQuality['calidad_incompletos'],
                'calidad_inconsistentes' => $eventQuality['calidad_inconsistentes'],
                'calidad_dato' => $eventQuality['calidad_porcentaje'],
                'asignaciones' => $assignmentRows->map(function ($row) {
                    $patientName = trim(implode(' ', array_filter([
                        $row->patient_first_name ?? null,
                        $row->patient_second_name ?? null,
                        $row->patient_first_lastname ?? null,
                        $row->patient_second_lastname ?? null,
                    ])));

                    return [
                        'case_id' => (int) $row->case_id,
                        'usuario' => (string) ($row->user_name ?? ''),
                        'codigo' => (string) $row->codigo,
                        'paciente' => $patientName !== '' ? $patientName : 'Sin nombre',
                        'edad' => (string) ($row->patient_age ?? 'Sin dato'),
                        'fecha_asignacion' => $row->fecha_asignacion ?? null,
                        'tipo_identificacion' => (string) ($row->tipo_identificacion ?? 'Sin dato'),
                        'numero_identificacion' => (string) ($row->numero_identificacion ?? 'Sin dato'),
                    ];
                })->values(),
                'sin_seguimiento_detalle' => $sinSeguimientoRows->map(function ($row) {
                    $patientName = trim(implode(' ', array_filter([
                        $row->patient_first_name ?? null,
                        $row->patient_second_name ?? null,
                        $row->patient_first_lastname ?? null,
                        $row->patient_second_lastname ?? null,
                    ])));

                    return [
                        'case_id' => (int) $row->case_id,
                        'usuario' => (string) ($row->user_name ?? ''),
                        'codigo' => (string) $row->codigo,
                        'paciente' => $patientName !== '' ? $patientName : 'Sin nombre',
                        'edad' => (string) ($row->patient_age ?? 'Sin dato'),
                        'fecha_asignacion' => $row->fecha_asignacion ?? null,
                        'tipo_identificacion' => (string) ($row->tipo_identificacion ?? 'Sin dato'),
                        'numero_identificacion' => (string) ($row->numero_identificacion ?? 'Sin dato'),
                    ];
                })->values(),
                'seguimientos_detalle' => $followUps->map(function ($row) {
                    $patientName = trim(implode(' ', array_filter([
                        $row->patient_first_name ?? null,
                        $row->patient_second_name ?? null,
                        $row->patient_first_lastname ?? null,
                        $row->patient_second_lastname ?? null,
                    ])));

                    return [
                        'id' => (int) $row->id,
                        'estado' => (string) ($row->estado ?? ''),
                        'fecha_consulta' => $row->fecha_consulta ?? ($row->created_at ?? null),
                        'clasificacion' => (string) ($row->clasificacion ?? 'SIN CLASIFICACION'),
                        'peso_kilos' => $row->peso_kilos ?? null,
                        'talla_cm' => $row->talla_cm ?? null,
                        'puntajez' => $row->puntajez ?? null,
                        'observaciones' => (string) ($row->observaciones ?? ''),
                        'fecha_proximo_control' => $row->fecha_proximo_control ?? null,
                        'perimetro_braqueal' => $row->perimetro_braqueal ?? null,
                        'medicamento' => $row->medicamento ?? null,
                        'motivo_reapuertura' => $row->motivo_reapuertura ?? null,
                        'usuario_id' => isset($row->user_id) ? (int) $row->user_id : null,
                        'usuario' => (string) ($row->user_name ?? 'Sin usuario'),
                        'paciente' => $patientName !== '' ? $patientName : 'Sin nombre',
                        'edad' => (string) ($row->patient_age ?? 'Sin dato'),
                        'case_id' => isset($row->case_id) ? (int) $row->case_id : null,
                    ];
                })->values(),
            ];

            if ($patientHighlight === null && $assignmentRows->isNotEmpty()) {
                $first = $assignmentRows->first();
                $patientHighlight = trim(implode(' ', array_filter([
                    $first->patient_first_name ?? null,
                    $first->patient_second_name ?? null,
                    $first->patient_first_lastname ?? null,
                    $first->patient_second_lastname ?? null,
                ])));
                $patientAgeHighlight = (string) ($first->patient_age ?? 'Sin dato');
            }
        }

        return [
            'ok' => true,
            'codigo' => $codigo,
            'paciente' => $patientHighlight !== '' ? $patientHighlight : 'Sin paciente',
            'edad' => $patientAgeHighlight ?? 'Sin dato',
            'usuarios' => $users->values(),
            'trace' => $trace,
            'range' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
        ];
    }

    private function buildGapPayload(Carbon $desde, Carbon $hasta, string $eventoFiltro = 'all', string $clasificacionFiltro = 'all'): array
    {
        $events = $this->eventList($eventoFiltro);
        $clasificacionFiltro = $this->normalizeClassFilter($clasificacionFiltro);
        $items = [];
        $eventSummary = [];

        foreach ($events as $evento) {
            $assignmentQuery = $this->assignmentQuery($evento, $desde, $hasta);
            $assignmentRows = $assignmentQuery->get();
            $followUps = $this->followUpDetails($evento, $desde, $hasta, $assignmentQuery, '');
            $followUpsByCase = $followUps->groupBy(fn ($row) => (string) ($row->case_id ?? ''));

            $sinSeguimientoRows = $assignmentRows->filter(function ($row) use ($followUpsByCase) {
                return !$followUpsByCase->has((string) ($row->case_id ?? ''));
            })->values();

            if ($clasificacionFiltro !== 'all') {
                $sinSeguimientoRows = $sinSeguimientoRows->filter(function ($row) use ($clasificacionFiltro, $followUpsByCase) {
                    $caseId = (string) ($row->case_id ?? '');
                    if (!$followUpsByCase->has($caseId)) {
                        return true;
                    }

                    return !$followUpsByCase->get($caseId, collect())->contains(function ($followUp) use ($clasificacionFiltro) {
                        return $this->normalizeLabel($followUp->clasificacion ?? '', 'SIN CLASIFICACION') === $clasificacionFiltro;
                    });
                })->values();
            }

            $eventSummary[$evento] = [
                'label' => "Evento {$evento}",
                'asignados' => (int) $assignmentRows->count(),
                'sin_seguimiento' => (int) $sinSeguimientoRows->count(),
            ];

            foreach ($sinSeguimientoRows as $row) {
                $patientName = trim(implode(' ', array_filter([
                    $row->patient_first_name ?? null,
                    $row->patient_second_name ?? null,
                    $row->patient_first_lastname ?? null,
                    $row->patient_second_lastname ?? null,
                ])));

                $items[] = [
                    'evento' => $evento,
                    'evento_label' => "Evento {$evento}",
                    'codigo' => (string) $row->codigo,
                    'case_id' => (int) $row->case_id,
                    'paciente' => $patientName !== '' ? $patientName : 'Sin nombre',
                    'edad' => (string) ($row->patient_age ?? 'Sin dato'),
                    'fecha_asignacion' => $row->fecha_asignacion ?? null,
                    'tipo_identificacion' => (string) ($row->tipo_identificacion ?? 'Sin dato'),
                    'numero_identificacion' => (string) ($row->numero_identificacion ?? 'Sin dato'),
                    'usuario' => (string) ($row->user_name ?? 'Sin usuario'),
                ];
            }
        }

        usort($items, function (array $a, array $b): int {
            $dateA = $this->traceDateValue($a['fecha_asignacion'] ?? null);
            $dateB = $this->traceDateValue($b['fecha_asignacion'] ?? null);

            return $dateB <=> $dateA;
        });

        $firstDate = !empty($items) ? $items[array_key_last($items)]['fecha_asignacion'] ?? null : null;
        $lastDate = !empty($items) ? $items[0]['fecha_asignacion'] ?? null : null;

        return [
            'ok' => true,
            'range' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
            'totales' => [
                'asignados' => array_sum(array_map(fn ($item) => $item['asignados'], $eventSummary)),
                'sin_seguimiento' => count($items),
            ],
            'eventos' => $eventSummary,
            'rows' => $items,
            'summary' => [
                'personas' => count($items),
                'total' => count($items),
                'primera_fecha' => $firstDate ? $this->traceDate($firstDate)?->format('d/m/Y') ?? 'Sin dato' : 'Sin dato',
                'ultima_fecha' => $lastDate ? $this->traceDate($lastDate)?->format('d/m/Y') ?? 'Sin dato' : 'Sin dato',
            ],
        ];
    }

    private function usersGroupedByCode(): Collection
    {
        return DB::table('users')
            ->select('id', 'name', 'email', 'codigohabilitacion')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $codigo = $this->normalizeCode($user->codigohabilitacion);

                return [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) ($user->email ?? ''),
                    'codigohabilitacion' => (string) ($user->codigohabilitacion ?? ''),
                    'codigo' => $codigo,
                ];
            })
            ->groupBy('codigo')
            ->map(fn (Collection $group) => $group->values());
    }

    private function codeExpr(string $alias): string
    {
        return "COALESCE(NULLIF(UPPER(LTRIM(RTRIM(CAST({$alias}.codigohabilitacion AS NVARCHAR(80))))), ''), 'SIN CODIGO')";
    }

    private function classExpr(string $alias): string
    {
        return "COALESCE(NULLIF(UPPER(LTRIM(RTRIM(CAST({$alias}.clasificacion AS NVARCHAR(120))))), ''), 'SIN CLASIFICACION')";
    }

    private function normalizeCode($value): string
    {
        $normalized = strtoupper(trim((string) $value));

        return $normalized !== '' ? $normalized : 'SIN CODIGO';
    }

    private function normalizeLabel($value, string $fallback): string
    {
        $normalized = strtoupper(trim((string) $value));

        return $normalized !== '' ? $normalized : $fallback;
    }

    private function normalizeClassFilter($value): string
    {
        $normalized = $this->normalizeLabel($value, 'ALL');

        return $normalized === 'ALL' ? 'all' : $normalized;
    }
}
