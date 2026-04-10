<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeguimientoInteligenciaController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('anio', now()->year);
        $documento = trim((string) $request->input('documento', ''));
        $hasRevision412 = Schema::hasTable('revisions_412');

        $casos = $this->baseCases($year, $hasRevision412);
        $riesgos = $this->buildRiskSemaphore($casos);
        $alertas48h = $this->buildPreventiveAlerts48h($casos);
        $ranking = $this->buildRankingSinSeguimiento($year);
        $indicadores = $this->buildMonthlyIndicators($year);
        $timeline = $this->buildTimeline($documento, $hasRevision412);

        return view('seguimiento.inteligencia', compact(
            'year',
            'documento',
            'riesgos',
            'alertas48h',
            'ranking',
            'indicadores',
            'timeline'
        ));
    }

    private function baseCases(int $year, bool $hasRevision412): Collection
    {
        $q113 = DB::table('seguimientos as seg')
            ->join('sivigilas as s', 's.id', '=', 'seg.sivigilas_id')
            ->join('users as u', 'u.id', '=', 'seg.user_id')
            ->select([
                DB::raw("'113' as modulo"),
                'seg.id as registro_id',
                's.num_ide_ as documento',
                DB::raw("CONCAT(COALESCE(s.pri_nom_,''),' ',COALESCE(s.seg_nom_,''),' ',COALESCE(s.pri_ape_,''),' ',COALESCE(s.seg_ape_,'')) as paciente"),
                'u.name as responsable',
                'seg.clasificacion',
                'seg.estado',
                'seg.fecha_consulta',
                'seg.fecha_proximo_control',
                'seg.motivo_reapuertura',
                'seg.created_at',
                'seg.updated_at',
            ])
            ->whereYear('seg.created_at', $year);

        if (!$hasRevision412) {
            return $q113->get();
        }

        $q412 = DB::table('seguimiento_412s as seg')
            ->join('cargue412s as c', 'c.id', '=', 'seg.cargue412_id')
            ->join('users as u', 'u.id', '=', 'seg.user_id')
            ->select([
                DB::raw("'412' as modulo"),
                'seg.id as registro_id',
                'c.numero_identificacion as documento',
                DB::raw("CONCAT(COALESCE(c.primer_nombre,''),' ',COALESCE(c.segundo_nombre,''),' ',COALESCE(c.primer_apellido,''),' ',COALESCE(c.segundo_apellido,'')) as paciente"),
                'u.name as responsable',
                'seg.clasificacion',
                'seg.estado',
                'seg.fecha_consulta',
                'seg.fecha_proximo_control',
                'seg.motivo_reapuertura',
                'seg.created_at',
                'seg.updated_at',
            ])
            ->whereYear('seg.created_at', $year);

        return $q113->unionAll($q412)->get();
    }

    private function buildRiskSemaphore(Collection $casos): array
    {
        $now = Carbon::now();
        $rows = $casos->map(function ($row) use ($now) {
            $clasificacion = mb_strtolower((string) ($row->clasificacion ?? ''), 'UTF-8');
            $hasReopen = trim((string) ($row->motivo_reapuertura ?? '')) !== '';
            $isOpen = (int) ($row->estado ?? 0) === 1;
            $dias = null;

            if (!empty($row->fecha_proximo_control)) {
                try {
                    $dias = $now->diffInDays(Carbon::parse($row->fecha_proximo_control), false);
                } catch (\Throwable $e) {
                    $dias = null;
                }
            }

            $risk = 'verde';
            $score = 0;
            if ($isOpen && $dias !== null && $dias < 0) {
                $score += 2;
            }
            if (str_contains($clasificacion, 'sev')) {
                $score += 2;
            } elseif (str_contains($clasificacion, 'mod')) {
                $score += 1;
            }
            if ($hasReopen) {
                $score += 1;
            }
            if ($isOpen && $dias !== null && $dias >= 0 && $dias <= 2) {
                $score += 1;
            }

            if ($score >= 3) {
                $risk = 'rojo';
            } elseif ($score >= 1) {
                $risk = 'amarillo';
            }

            return (object) [
                'modulo' => $row->modulo,
                'registro_id' => $row->registro_id,
                'documento' => $row->documento,
                'paciente' => trim((string) $row->paciente),
                'responsable' => $row->responsable,
                'clasificacion' => $row->clasificacion,
                'fecha_proximo_control' => $row->fecha_proximo_control,
                'dias_control' => $dias,
                'riesgo' => $risk,
                'score' => $score,
                'estado' => (int) ($row->estado ?? 0),
            ];
        })
        ->sortByDesc('score')
        ->values();

        return [
            'rows' => $rows,
            'totales' => [
                'rojo' => $rows->where('riesgo', 'rojo')->count(),
                'amarillo' => $rows->where('riesgo', 'amarillo')->count(),
                'verde' => $rows->where('riesgo', 'verde')->count(),
            ],
        ];
    }

    private function buildPreventiveAlerts48h(Collection $casos): Collection
    {
        $now = Carbon::now();
        $max = $now->copy()->addHours(48);

        return $casos->filter(function ($row) use ($now, $max) {
            if ((int) ($row->estado ?? 0) !== 1 || empty($row->fecha_proximo_control)) {
                return false;
            }
            try {
                $fecha = Carbon::parse($row->fecha_proximo_control);
            } catch (\Throwable $e) {
                return false;
            }
            return $fecha->between($now, $max);
        })
        ->sortBy('fecha_proximo_control')
        ->values();
    }

    private function buildRankingSinSeguimiento(int $year): Collection
    {
        $rows113 = DB::table('users as u')
            ->join('sivigilas as s', 's.user_id', '=', 'u.id')
            ->leftJoin('seguimientos as seg', 'seg.sivigilas_id', '=', 's.id')
            ->selectRaw("
                '113' as modulo,
                u.id as user_id,
                u.name as prestador,
                COUNT(DISTINCT s.id) as asignados,
                COUNT(DISTINCT CASE WHEN seg.id IS NOT NULL THEN s.id END) as con_seguimiento
            ")
            ->whereYear('s.created_at', $year)
            ->groupBy('u.id', 'u.name')
            ->get();

        $rows412 = DB::table('users as u')
            ->join('cargue412s as c', 'c.user_id', '=', 'u.id')
            ->leftJoin('seguimiento_412s as seg', 'seg.cargue412_id', '=', 'c.id')
            ->selectRaw("
                '412' as modulo,
                u.id as user_id,
                u.name as prestador,
                COUNT(DISTINCT c.id) as asignados,
                COUNT(DISTINCT CASE WHEN seg.id IS NOT NULL THEN c.id END) as con_seguimiento
            ")
            ->whereYear('c.created_at', $year)
            ->groupBy('u.id', 'u.name')
            ->get();

        return $rows113->concat($rows412)->map(function ($r) {
            $asignados = (int) $r->asignados;
            $con = (int) $r->con_seguimiento;
            $sin = max(0, $asignados - $con);
            $cumplimiento = $asignados > 0 ? round(($con / $asignados) * 100, 2) : 0;

            return (object) [
                'modulo' => $r->modulo,
                'prestador' => $r->prestador,
                'asignados' => $asignados,
                'con_seguimiento' => $con,
                'sin_seguimiento' => $sin,
                'cumplimiento' => $cumplimiento,
            ];
        })->sortByDesc('sin_seguimiento')->values();
    }

    private function buildMonthlyIndicators(int $year): Collection
    {
        $agg113 = DB::table('seguimientos')
            ->selectRaw("
                MONTH(created_at) as mes,
                COUNT(*) as total,
                SUM(CASE WHEN estado <> 1 THEN 1 ELSE 0 END) as cerrados,
                SUM(CASE WHEN estado <> 1 AND (fecha_proximo_control IS NULL OR updated_at <= fecha_proximo_control) THEN 1 ELSE 0 END) as cierre_oportuno,
                AVG(CAST(DATEDIFF(day, created_at, ISNULL(updated_at, GETDATE())) AS float)) as tiempo_promedio
            ")
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('mes');

        $agg412 = DB::table('seguimiento_412s')
            ->selectRaw("
                MONTH(created_at) as mes,
                COUNT(*) as total,
                SUM(CASE WHEN estado <> 1 THEN 1 ELSE 0 END) as cerrados,
                SUM(CASE WHEN estado <> 1 AND (fecha_proximo_control IS NULL OR updated_at <= fecha_proximo_control) THEN 1 ELSE 0 END) as cierre_oportuno,
                AVG(CAST(DATEDIFF(day, created_at, ISNULL(updated_at, GETDATE())) AS float)) as tiempo_promedio
            ")
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('mes');

        return collect(range(1, 12))->map(function ($mes) use ($agg113, $agg412) {
            $a = $agg113->get($mes);
            $b = $agg412->get($mes);
            $total = (int) (($a->total ?? 0) + ($b->total ?? 0));
            $cerrados = (int) (($a->cerrados ?? 0) + ($b->cerrados ?? 0));
            $oportunos = (int) (($a->cierre_oportuno ?? 0) + ($b->cierre_oportuno ?? 0));
            $cumplimiento = $total > 0 ? round(($cerrados / $total) * 100, 2) : 0;
            $oportunidad = $cerrados > 0 ? round(($oportunos / $cerrados) * 100, 2) : 0;
            $avgA = (float) ($a->tiempo_promedio ?? 0);
            $avgB = (float) ($b->tiempo_promedio ?? 0);
            $div = (($a ? 1 : 0) + ($b ? 1 : 0));
            $promedio = $div > 0 ? round(($avgA + $avgB) / $div, 2) : 0;

            return (object) [
                'mes' => $mes,
                'total' => $total,
                'cerrados' => $cerrados,
                'cumplimiento' => $cumplimiento,
                'cierre_oportuno' => $oportunidad,
                'tiempo_promedio' => $promedio,
            ];
        });
    }

    private function buildTimeline(string $documento, bool $hasRevision412): Collection
    {
        if ($documento === '') {
            return collect();
        }

        $events113 = DB::table('seguimientos as seg')
            ->join('sivigilas as s', 's.id', '=', 'seg.sivigilas_id')
            ->join('users as u', 'u.id', '=', 'seg.user_id')
            ->select([
                DB::raw("'113' as modulo"),
                DB::raw("'seguimiento' as evento"),
                'seg.id as registro_id',
                'seg.created_at as fecha_evento',
                's.num_ide_ as documento',
                DB::raw("CONCAT(COALESCE(s.pri_nom_,''),' ',COALESCE(s.seg_nom_,''),' ',COALESCE(s.pri_ape_,''),' ',COALESCE(s.seg_ape_,'')) as paciente"),
                'u.name as actor',
                'seg.clasificacion',
                'seg.motivo_reapuertura',
                'seg.fecha_proximo_control',
            ])
            ->where('s.num_ide_', 'like', '%' . $documento . '%')
            ->get();

        $review113 = DB::table('revisions as r')
            ->join('seguimientos as seg', 'seg.id', '=', 'r.seguimientos_id')
            ->join('sivigilas as s', 's.id', '=', 'seg.sivigilas_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->select([
                DB::raw("'113' as modulo"),
                DB::raw("'auditoria_revision' as evento"),
                'seg.id as registro_id',
                'r.created_at as fecha_evento',
                's.num_ide_ as documento',
                DB::raw("CONCAT(COALESCE(s.pri_nom_,''),' ',COALESCE(s.seg_nom_,''),' ',COALESCE(s.pri_ape_,''),' ',COALESCE(s.seg_ape_,'')) as paciente"),
                DB::raw("COALESCE(u.name, 'Sistema') as actor"),
                'seg.clasificacion',
                'seg.motivo_reapuertura',
                'seg.fecha_proximo_control',
            ])
            ->where('s.num_ide_', 'like', '%' . $documento . '%')
            ->get();

        $events = $events113->concat($review113);

        $events412 = DB::table('seguimiento_412s as seg')
            ->join('cargue412s as c', 'c.id', '=', 'seg.cargue412_id')
            ->join('users as u', 'u.id', '=', 'seg.user_id')
            ->select([
                DB::raw("'412' as modulo"),
                DB::raw("'seguimiento' as evento"),
                'seg.id as registro_id',
                'seg.created_at as fecha_evento',
                'c.numero_identificacion as documento',
                DB::raw("CONCAT(COALESCE(c.primer_nombre,''),' ',COALESCE(c.segundo_nombre,''),' ',COALESCE(c.primer_apellido,''),' ',COALESCE(c.segundo_apellido,'')) as paciente"),
                'u.name as actor',
                'seg.clasificacion',
                'seg.motivo_reapuertura',
                'seg.fecha_proximo_control',
            ])
            ->where('c.numero_identificacion', 'like', '%' . $documento . '%')
            ->get();

        $events = $events->concat($events412);

        if ($hasRevision412) {
            $review412 = DB::table('revisions_412 as r')
                ->join('seguimiento_412s as seg', 'seg.id', '=', 'r.seguimiento_412_id')
                ->join('cargue412s as c', 'c.id', '=', 'seg.cargue412_id')
                ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
                ->select([
                    DB::raw("'412' as modulo"),
                    DB::raw("'auditoria_revision' as evento"),
                    'seg.id as registro_id',
                    'r.created_at as fecha_evento',
                    'c.numero_identificacion as documento',
                    DB::raw("CONCAT(COALESCE(c.primer_nombre,''),' ',COALESCE(c.segundo_nombre,''),' ',COALESCE(c.primer_apellido,''),' ',COALESCE(c.segundo_apellido,'')) as paciente"),
                    DB::raw("COALESCE(u.name, 'Sistema') as actor"),
                    'seg.clasificacion',
                    'seg.motivo_reapuertura',
                    'seg.fecha_proximo_control',
                ])
                ->where('c.numero_identificacion', 'like', '%' . $documento . '%')
                ->get();
            $events = $events->concat($review412);
        }

        return $events->sortByDesc('fecha_evento')->values();
    }
}

