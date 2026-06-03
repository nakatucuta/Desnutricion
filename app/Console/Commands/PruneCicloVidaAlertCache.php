<?php

namespace App\Console\Commands;

use App\Support\CicloVidaCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneCicloVidaAlertCache extends Command
{
    protected $signature = 'ciclosvida:cache-prune-alerts
                            {--course= : Limita la limpieza a un curso}
                            {--module= : Limita la limpieza a un modulo}
                            {--batch=50000 : Registros a borrar por lote cuando se usa --force}
                            {--force : Ejecuta el borrado; sin esta opcion solo muestra diagnostico}';

    protected $description = 'Elimina cortes viejos de cache de alertas y conserva solo el ultimo corte exitoso.';

    public function handle(): int
    {
        @set_time_limit(0);

        $targets = $this->alertTargets();
        if ($targets === []) {
            $this->info('No hay modulos de alertas materializados para limpiar.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $batch = max(1000, (int) $this->option('batch'));
        $summary = [];

        foreach ($targets as $target) {
            $courseKey = $target['course'];
            $moduleKey = $target['module'];
            $latest = DB::table('ciclo_vida_cache_runs')
                ->where('course_key', $courseKey)
                ->where('module_key', $moduleKey)
                ->where('status', 'success')
                ->orderByDesc('id')
                ->first(['range_start', 'range_end']);

            if (!$latest) {
                $this->warn("{$courseKey}.{$moduleKey}: no tiene ejecucion exitosa para conservar.");
                continue;
            }

            $total = $this->countAlertRecords($courseKey, $moduleKey);
            $keep = $this->countLatestAlertRecords($courseKey, $moduleKey, (string) $latest->range_start, (string) $latest->range_end);
            $toDelete = max(0, $total - $keep);
            $deleted = 0;

            if ($force && $toDelete > 0) {
                $deleted = $this->deleteOldAlertRecords(
                    $courseKey,
                    $moduleKey,
                    (string) $latest->range_start,
                    (string) $latest->range_end,
                    $batch
                );

                DB::table('ciclo_vida_cache_summaries')
                    ->where('course_key', $courseKey)
                    ->where('module_key', $moduleKey)
                    ->where(function ($query) use ($latest): void {
                        $query->where('range_start', '<>', (string) $latest->range_start)
                            ->orWhere('range_end', '<>', (string) $latest->range_end);
                    })
                    ->delete();
            }

            $summary[] = [
                $courseKey,
                $moduleKey,
                (string) $latest->range_start,
                (string) $latest->range_end,
                $total,
                $keep,
                $toDelete,
                $deleted,
            ];
        }

        $this->table(
            ['Curso', 'Modulo', 'Conserva desde', 'Conserva hasta', 'Total alertas', 'Conserva', 'Borraria', 'Borrado'],
            $summary
        );

        if (!$force) {
            $this->warn('Modo diagnostico: no se borro nada. Ejecuta con --force para limpiar.');
        }

        return self::SUCCESS;
    }

    protected function alertTargets(): array
    {
        $courseFilter = trim((string) $this->option('course'));
        $moduleFilter = trim((string) $this->option('module'));
        $targets = [];

        foreach (CicloVidaCatalog::courses() as $courseKey => $course) {
            if ($courseFilter !== '' && $courseKey !== $courseFilter) {
                continue;
            }

            foreach ((array) ($course['modules'] ?? []) as $moduleKey => $module) {
                if ($moduleFilter !== '' && $moduleKey !== $moduleFilter) {
                    continue;
                }

                if (!empty($module['materialized']) && (string) ($module['record_type'] ?? 'event') === 'alert') {
                    $targets[] = ['course' => $courseKey, 'module' => $moduleKey];
                }
            }
        }

        return $targets;
    }

    protected function countAlertRecords(string $courseKey, string $moduleKey): int
    {
        $row = DB::selectOne(
            'SELECT COUNT_BIG(*) AS total
             FROM ciclo_vida_cache_records
             WHERE course_key = ? AND module_key = ? AND record_type = ?',
            [$courseKey, $moduleKey, 'alert']
        );

        return (int) ($row->total ?? 0);
    }

    protected function countLatestAlertRecords(string $courseKey, string $moduleKey, string $from, string $to): int
    {
        $row = DB::selectOne(
            'SELECT COUNT_BIG(*) AS total
             FROM ciclo_vida_cache_records
             WHERE course_key = ? AND module_key = ? AND record_type = ?
               AND range_start = ? AND range_end = ?',
            [$courseKey, $moduleKey, 'alert', $from, $to]
        );

        return (int) ($row->total ?? 0);
    }

    protected function deleteOldAlertRecords(string $courseKey, string $moduleKey, string $from, string $to, int $batch): int
    {
        $deleted = 0;

        do {
            $affected = DB::affectingStatement(
                "DELETE TOP ({$batch})
                 FROM ciclo_vida_cache_records
                 WHERE course_key = ? AND module_key = ? AND record_type = ?
                   AND NOT (range_start = ? AND range_end = ?)",
                [$courseKey, $moduleKey, 'alert', $from, $to]
            );

            $deleted += $affected;
            if ($affected > 0) {
                $this->line("{$courseKey}.{$moduleKey}: borrados {$deleted} registros...");
            }
        } while ($affected > 0);

        return $deleted;
    }
}
