<?php

namespace App\Console\Commands;

use App\Services\CicloVida\CicloVidaCacheRefresher;
use App\Support\CicloVidaCatalog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BackfillCicloVidaCache extends Command
{
    protected $signature = 'ciclosvida:cache-backfill
                            {course=all : Curso de vida o "all" para todos}
                            {--module=* : Uno o varios modulos a cargar}
                            {--from= : Fecha inicial YYYY-MM-DD}
                            {--to= : Fecha final exclusiva YYYY-MM-DD}
                            {--chunk=month : month|quarter|year}
                            {--step=1 : Cantidad de chunks por iteracion}
                            {--resume : Omite ventanas ya cargadas con success/running}';

    protected $description = 'Carga historica por ventanas para las tablas cache de ciclos de vida.';

    public function handle(CicloVidaCacheRefresher $refresher): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', env('CICLO_VIDA_CACHE_MEMORY_LIMIT', '1536M'));

        try {
            [$globalFrom, $globalTo] = $this->resolveDateRange();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $courses = $this->resolveCourses();
        $chunk = strtolower((string) $this->option('chunk'));
        $step = max(1, (int) $this->option('step'));
        $resume = (bool) $this->option('resume');

        $summary = [];

        foreach ($courses as $courseKey) {
            $moduleKeys = $this->resolveModules($courseKey);
            if ($moduleKeys === []) {
                $this->warn("{$courseKey}: no hay modulos materializables configurados.");
                continue;
            }

            $cursor = $globalFrom->copy();
            while ($cursor->lt($globalTo)) {
                $windowFrom = $cursor->copy()->startOfDay();
                $windowTo = $this->advanceWindow($windowFrom, $chunk, $step);
                if ($windowTo->gt($globalTo)) {
                    $windowTo = $globalTo->copy();
                }

                $windowModules = $moduleKeys;
                if ($resume) {
                    $done = $this->skipModules($courseKey, $windowFrom, $windowTo);
                    $windowModules = array_values(array_filter($windowModules, fn (string $key) => !in_array($key, $done, true)));
                }

                $label = "{$courseKey} {$windowFrom->toDateString()} -> {$windowTo->toDateString()}";
                if ($windowModules === []) {
                    $this->line("Saltando {$label}: ya estaba cargado.");
                    $cursor = $windowTo;
                    continue;
                }

                $this->info("Procesando {$label}");

                try {
                    $results = $refresher->refreshCourse($courseKey, $windowModules, $windowFrom, $windowTo);
                } catch (\Throwable $e) {
                    $this->error("Fallo en {$label}: ".$e->getMessage());

                    return self::FAILURE;
                }

                foreach ($results as $row) {
                    $summary[] = [
                        'course' => $row['course'],
                        'window' => $windowFrom->toDateString().' -> '.$windowTo->toDateString(),
                        'module' => $row['module'],
                        'status' => $row['status'],
                        'records' => $row['records'],
                    ];
                }

                $cursor = $windowTo;
            }
        }

        $this->table(
            ['Curso', 'Ventana', 'Modulo', 'Estado', 'Registros'],
            $summary
        );

        return self::SUCCESS;
    }

    protected function resolveDateRange(): array
    {
        $from = trim((string) $this->option('from'));
        $to = trim((string) $this->option('to'));

        if ($from === '' || $to === '') {
            throw new RuntimeException('El backfill requiere --from y --to para controlar la carga historica.');
        }

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->startOfDay();

        if ($toDate->lessThanOrEqualTo($fromDate)) {
            throw new RuntimeException('La fecha --to debe ser mayor que --from.');
        }

        return [$fromDate, $toDate];
    }

    protected function resolveCourses(): array
    {
        $course = trim((string) $this->argument('course'));

        if ($course === 'all') {
            return array_keys(CicloVidaCatalog::courses());
        }

        if (!array_key_exists($course, CicloVidaCatalog::courses())) {
            throw new RuntimeException("Curso no configurado: {$course}");
        }

        return [$course];
    }

    protected function resolveModules(string $courseKey): array
    {
        $requested = array_values(array_filter((array) $this->option('module')));
        if ($requested === []) {
            return CicloVidaCatalog::materializedModules($courseKey);
        }

        $available = array_keys((array) data_get(config("ciclosvida.courses.{$courseKey}"), 'modules', []));

        return array_values(array_filter($requested, fn (string $key) => in_array($key, $available, true)));
    }

    protected function advanceWindow(Carbon $from, string $chunk, int $step): Carbon
    {
        return match ($chunk) {
            'month' => $from->copy()->addMonths($step),
            'quarter' => $from->copy()->addMonths($step * 3),
            'year' => $from->copy()->addYears($step),
            default => throw new RuntimeException('Chunk no soportado. Usa month, quarter o year.'),
        };
    }

    protected function skipModules(string $courseKey, Carbon $from, Carbon $to): array
    {
        return collect(DB::table('ciclo_vida_cache_runs')
            ->where('course_key', $courseKey)
            ->where('range_start', $from->toDateString())
            ->where('range_end', $to->toDateString())
            ->whereIn('status', ['success', 'running'])
            ->pluck('module_key'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
