<?php

namespace App\Services\CicloVida;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Support\CicloVidaCatalog;
use RuntimeException;

class CicloVidaCacheRefresher
{
    public function refreshCourse(string $courseKey, array $moduleKeys, Carbon $from, Carbon $to): array
    {
        $course = config("ciclosvida.courses.{$courseKey}");
        if (!is_array($course)) {
            throw new RuntimeException("Curso de vida no configurado: {$courseKey}");
        }

        $availableModules = $course['modules'] ?? [];
        if (empty($moduleKeys)) {
            $moduleKeys = CicloVidaCatalog::materializedModules($courseKey);
        }

        $results = [];
        foreach ($moduleKeys as $moduleKey) {
            $module = $availableModules[$moduleKey] ?? null;
            if (!is_array($module)) {
                throw new RuntimeException("Modulo no configurado: {$courseKey}.{$moduleKey}");
            }

            $results[] = $this->refreshModule($courseKey, $moduleKey, $module, $from, $to);
        }

        return $results;
    }

    public function refreshModule(string $courseKey, string $moduleKey, array $module, Carbon $from, Carbon $to): array
    {
        $startedAt = $this->dbDateTime(now());
        $moduleLabel = $this->safeDbText((string) ($module['label'] ?? $moduleKey));

        $runId = DB::table('ciclo_vida_cache_runs')->insertGetId([
            'course_key' => $courseKey,
            'module_key' => $moduleKey,
            'module_label' => $moduleLabel,
            'started_at' => $startedAt,
            'range_start' => $this->dbDate($from),
            'range_end' => $this->dbDate($to),
            'status' => 'running',
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);

        try {
            $rows = $this->extractRows($moduleKey, $module, $from, $to);
            $rows = $this->enrichRowsWithAffiliates($rows);
            $recordsLoaded = 0;

            DB::transaction(function () use ($courseKey, $moduleKey, $module, $from, $to, $runId, $rows, &$recordsLoaded): void {
                $this->deleteWindowRecords($courseKey, $moduleKey, $from, $to);

                $seenHashes = [];
                $metrics = [
                    'total' => 0,
                    'patients' => [],
                    'ips' => [],
                    'services' => [],
                ];

                foreach (array_chunk($rows, 500) as $sourceChunk) {
                    $records = $this->normalizeRows($courseKey, $moduleKey, $module, $sourceChunk, $from, $to, $runId);
                    $filtered = [];

                    foreach ($records as $record) {
                        $hash = $record['record_hash'];
                        if (isset($seenHashes[$hash])) {
                            continue;
                        }

                        $seenHashes[$hash] = true;
                        $filtered[] = $record;
                        $this->accumulateMetrics($metrics, $record);
                    }

                    if (!empty($filtered)) {
                        $recordsLoaded += count($filtered);
                        $batchSize = $this->resolveBatchSize($filtered[0] ?? []);
                        foreach (array_chunk($filtered, $batchSize) as $chunk) {
                            DB::table('ciclo_vida_cache_records')->insert($chunk);
                        }
                    }

                    unset($records, $filtered);
                    gc_collect_cycles();
                }

                $this->storeSummary($courseKey, $moduleKey, $from, $to, $metrics, $runId);
            });

            DB::table('ciclo_vida_cache_runs')
                ->where('id', $runId)
                ->update([
                    'status' => 'success',
                    'finished_at' => $this->dbDateTime(now()),
                    'records_loaded' => $recordsLoaded,
                    'updated_at' => $this->dbDateTime(now()),
                ]);

            return [
                'course' => $courseKey,
                'module' => $moduleKey,
                'records' => $recordsLoaded,
                'status' => 'success',
            ];
        } catch (\Throwable $e) {
            DB::table('ciclo_vida_cache_runs')
                ->where('id', $runId)
                ->update([
                    'status' => 'failed',
                    'finished_at' => $this->dbDateTime(now()),
                    'error_message' => $this->safeDbText(Str::limit($e->getMessage(), 65000, '')),
                    'updated_at' => $this->dbDateTime(now()),
                ]);

            throw $e;
        }
    }

    protected function extractRows(string $moduleKey, array $module, Carbon $from, Carbon $to): array
    {
        $source = $module['source'] ?? null;
        if (is_array($source) && !empty($source['type'])) {
            return $this->extractRowsFromSource($source, $from, $to);
        }

        if ($moduleKey === 'alertas') {
            return $this->extractAlertRows($from, $to);
        }

        $path = $this->resolveSqlPath((string) ($module['sql_file'] ?? ''));
        $sql = $this->prepareSql(File::get($path), $from, $to);

        return DB::connection((string) config('ciclosvida.source_connection', 'sqlsrv_1'))
            ->select($sql);
    }

    protected function enrichRowsWithAffiliates(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $pairs = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $type = $this->firstString($data, ['tipoIdentificacion', 'tipoDocumentoIdentificacion']);
            $id = $this->firstString($data, ['identificacion', 'numDocumentoIdentificacion']);

            if ($type === null || $id === null) {
                continue;
            }

            $key = $type.'|'.$id;
            $pairs[$key] = ['tipo' => $type, 'id' => $id];
        }

        if ($pairs === []) {
            return $rows;
        }

        $types = collect($pairs)->pluck('tipo')->unique()->values()->all();
        $ids = collect($pairs)->pluck('id')->unique()->values()->all();
        $affiliateRows = collect();

        foreach (array_chunk($ids, 1000) as $idChunk) {
            $chunkRows = DB::connection((string) config('ciclosvida.source_connection', 'sqlsrv_1'))
                ->table(DB::raw('sga..maestroidentificaciones as X'))
                ->join(DB::raw('sga..maestroafiliados as afi'), 'X.numeroCarnet', '=', 'afi.numeroCarnet')
                ->leftJoin(DB::raw('sga..maestroIps as g'), 'X.numeroCarnet', '=', 'g.numeroCarnet')
                ->leftJoin(DB::raw('sga..maestroIpsGru as h'), 'g.idGrupoIps', '=', 'h.id')
                ->leftJoin(DB::raw('sga..maestroipsgrudet as i'), function ($join): void {
                    $join->on('h.id', '=', 'i.idd')
                        ->where('i.servicio', '=', 1);
                })
                ->whereIn('X.tipoIdentificacion', $types)
                ->whereIn('X.identificacion', $idChunk)
                ->get([
                    'X.tipoIdentificacion',
                    'X.identificacion',
                    'afi.primerNombre',
                    'afi.segundoNombre',
                    'afi.primerApellido',
                    'afi.segundoApellido',
                    'afi.fechaNacimiento',
                    'h.descrip as ips_Prim',
                    'i.codigo as codigoHabilitacion',
                ]);

            foreach ($chunkRows as $item) {
                $affiliateRows->put($item->tipoIdentificacion.'|'.$item->identificacion, $item);
            }
        }

        $enriched = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $type = $this->firstString($data, ['tipoIdentificacion', 'tipoDocumentoIdentificacion']);
            $id = $this->firstString($data, ['identificacion', 'numDocumentoIdentificacion']);
            $affiliate = $affiliateRows->get($type.'|'.$id);

            if ($affiliate !== null) {
                foreach ([
                    'primerNombre',
                    'segundoNombre',
                    'primerApellido',
                    'segundoApellido',
                    'fechaNacimiento',
                    'ips_Prim',
                    'codigoHabilitacion',
                ] as $field) {
                    if (($data[$field] ?? null) === null || $data[$field] === '') {
                        $data[$field] = $affiliate->{$field} ?? null;
                    }
                }
            }

            $enriched[] = $data;
        }

        return $enriched;
    }

    protected function extractRowsFromSource(array $source, Carbon $from, Carbon $to): array
    {
        return match ((string) ($source['type'] ?? '')) {
            'function' => $this->extractFunctionRows($source, $from, $to),
            'view' => $this->extractViewRows($source, $from, $to),
            default => throw new RuntimeException('Tipo de fuente no soportado para ciclos de vida.'),
        };
    }

    protected function extractFunctionRows(array $source, Carbon $from, Carbon $to): array
    {
        return match ((string) ($source['name'] ?? '')) {
            'fn_pi_alertas' => $this->extractAlertRows($from, $to),
            'fn_pi_lactancia' => $this->extractLactanciaRows($from, $to),
            'fn_pi_vitamina_a' => $this->extractVitaminaARows($from, $to),
            default => throw new RuntimeException('Funcion de fuente no soportada para ciclos de vida.'),
        };
    }

    protected function extractViewRows(array $source, Carbon $from, Carbon $to): array
    {
        $table = (string) ($source['table'] ?? '');
        $dateColumn = (string) ($source['date_column'] ?? '');

        if ($table === '' || $dateColumn === '') {
            throw new RuntimeException('La fuente tipo view requiere table y date_column.');
        }

        $query = DB::connection((string) ($source['connection'] ?? config('ciclosvida.source_connection', 'sqlsrv_1')))
            ->table($table)
            ->where($dateColumn, '>=', $from->toDateString())
            ->where($dateColumn, '<', $to->toDateString());

        foreach ((array) ($source['filters'] ?? []) as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $type = (string) ($filter['type'] ?? '');
            $column = (string) ($filter['column'] ?? '');
            if ($column === '') {
                continue;
            }

            if ($type === 'between') {
                $query->whereBetween($column, [$filter['from'] ?? null, $filter['to'] ?? null]);
                continue;
            }

            if ($type === 'eq') {
                $query->where($column, $filter['value'] ?? null);
            }
        }

        foreach ((array) ($source['order_by'] ?? []) as $order) {
            if (!is_array($order) || empty($order['column'])) {
                continue;
            }

            $query->orderBy((string) $order['column'], strtolower((string) ($order['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc');
        }

        return $query->get()->all();
    }

    protected function extractAlertRows(Carbon $from, Carbon $to): array
    {
        $sql = "
            SET NOCOUNT ON;
            SET ARITHABORT ON;
            SET ANSI_WARNINGS ON;
            SET QUOTED_IDENTIFIER ON;

            SELECT *
            FROM PRUEBA_DESNUTRICION.dbo.fn_pi_alertas(
                CONVERT(date, ?),
                CONVERT(date, ?),
                CONVERT(bit, ?),
                CONVERT(int, ?),
                CONVERT(int, ?)
            )
            ORDER BY primerApellido, segundoApellido, primerNombre, segundoNombre, descrip
            OPTION (RECOMPILE, MAXDOP 1);
        ";

        return DB::connection((string) config('ciclosvida.source_connection', 'sqlsrv_1'))
            ->select($sql, [
                $from->toDateString(),
                $to->toDateString(),
                1,
                0,
                5,
            ]);
    }

    protected function extractLactanciaRows(Carbon $from, Carbon $to): array
    {
        $sql = "
            SET NOCOUNT ON;
            SET ARITHABORT ON;
            SET ANSI_WARNINGS ON;
            SET QUOTED_IDENTIFIER ON;

            SELECT *
            FROM PRUEBA_DESNUTRICION.dbo.fn_pi_lactancia(
                CONVERT(date, ?),
                CONVERT(date, ?)
            )
            ORDER BY primerApellido, segundoApellido, primerNombre, segundoNombre, fechaConsulta
            OPTION (RECOMPILE, MAXDOP 1);
        ";

        return DB::connection((string) config('ciclosvida.source_connection', 'sqlsrv_1'))
            ->select($sql, [
                $from->toDateString(),
                $to->toDateString(),
            ]);
    }

    protected function extractVitaminaARows(Carbon $from, Carbon $to): array
    {
        $sql = "
            SET NOCOUNT ON;
            SET ARITHABORT ON;
            SET ANSI_WARNINGS ON;
            SET QUOTED_IDENTIFIER ON;

            SELECT *
            FROM PRUEBA_DESNUTRICION.dbo.fn_pi_vitamina_a(
                CONVERT(date, ?),
                CONVERT(date, ?)
            )
            ORDER BY fechaConsulta DESC, primerApellido, segundoApellido, primerNombre
            OPTION (RECOMPILE, MAXDOP 1);
        ";

        return DB::connection((string) config('ciclosvida.source_connection', 'sqlsrv_1'))
            ->select($sql, [
                $from->toDateString(),
                $to->toDateString(),
            ]);
    }

    protected function resolveSqlPath(string $fileName): string
    {
        $basePath = (string) config('ciclosvida.sql_base_path');
        $path = $basePath.DIRECTORY_SEPARATOR.$fileName;

        if (!File::exists($path)) {
            $fallbackRoot = dirname($basePath);
            $matches = collect(File::allFiles($fallbackRoot))
                ->filter(fn ($file) => strcasecmp($file->getFilename(), $fileName) === 0)
                ->values();

            if ($matches->isEmpty()) {
                throw new RuntimeException("No se encontro el SQL fuente: {$path}");
            }

            return $matches->first()->getPathname();
        }

        return $path;
    }

    protected function prepareSql(string $sql, Carbon $from, Carbon $to): string
    {
        $sql = $this->normalizeSqlEncoding($sql);
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;

        $patterns = [
            '/^\s*DECLARE\s+@Desde\s+date\s*=.*?;\s*$/im',
            '/^\s*DECLARE\s+@HastaExclusivo\s+date\s*=.*?;\s*$/im',
            '/^\s*DECLARE\s+@Hoy\s+date\s*=.*?;\s*$/im',
        ];

        foreach ($patterns as $pattern) {
            $sql = preg_replace($pattern, '', $sql) ?? $sql;
        }

        $sql = preg_replace(
            "/([A-Za-z0-9_\\.]+)\\s+between\\s+'\\d{4}-\\d{2}-\\d{2}'\\s+and\\s+'\\d{4}-\\d{2}-\\d{2}'/i",
            '$1 >= @Desde AND $1 < @HastaExclusivo',
            $sql
        ) ?? $sql;

        $prefix = implode(PHP_EOL, [
            "DECLARE @Desde date = '{$from->format('Ymd')}';",
            "DECLARE @HastaExclusivo date = '{$to->format('Ymd')}';",
            'DECLARE @Hoy date = CAST(GETDATE() AS date);',
            '',
        ]);

        return $this->cleanupSqlText($prefix.$sql);
    }

    protected function normalizeRows(
        string $courseKey,
        string $moduleKey,
        array $module,
        array $rows,
        Carbon $from,
        Carbon $to,
        int $runId
    ): array {
        $createdAt = $this->dbDateTime(now());
        $normalized = [];

        foreach ($rows as $row) {
            $data = (array) $row;
            $eventDate = $this->normalizeDate($data, [
                'fechaConsulta',
                'fechaProcedimiento',
                'fechaAtencion',
                'fechaInicio',
                'fechaSuministroTecnologia',
            ]);

            $serviceCode = $this->firstString($data, [
                'codigoConsulta',
                'codigoProcedimiento',
                'codigoServicio',
                'codigoCups',
                'codTecnologiaSalud',
                'codConsulta',
                'codProcedimiento',
                'codigo',
            ]);

            $serviceDescription = $this->firstString($data, [
                'descrip',
                'procedimiento',
                'actividad',
            ]);

            $diagnosticCode = $this->firstString($data, [
                'diagnosticoPrincipal',
                'diagnostico',
                'diag',
            ]);

            $recordHash = hash('sha256', json_encode([
                $courseKey,
                $moduleKey,
                $this->firstString($data, ['tipoIdentificacion']),
                $this->firstString($data, ['identificacion']),
                $eventDate,
                $serviceCode,
                $serviceDescription,
                $diagnosticCode,
                $this->firstString($data, ['descrip', 'procedimiento']),
            ], JSON_UNESCAPED_UNICODE));

            $birthDate = $this->normalizeDate($data, ['fechaNacimiento']);
            $resolvedAge = $this->firstNumeric($data, ['edad', 'edadAnios']);
            $resolvedAgeMonths = $this->firstNumeric($data, ['edadMeses', 'edadMeses_Hoy']);

            if ($resolvedAge === null && $birthDate !== null) {
                $ageReference = $eventDate ?? now()->toDateString();
                $resolvedAge = Carbon::parse($birthDate)->diffInYears(Carbon::parse($ageReference));
            }

            if ($resolvedAgeMonths === null && $birthDate !== null) {
                $ageReference = $eventDate ?? now()->toDateString();
                $resolvedAgeMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::parse($ageReference));
            }

            $record = [
                'course_key' => $courseKey,
                'module_key' => $moduleKey,
                'module_label' => $this->fitDbText((string) ($module['label'] ?? $moduleKey), 160),
                'record_type' => (string) ($module['record_type'] ?? $module['type'] ?? 'event'),
                'range_start' => $this->dbDate($from),
                'range_end' => $this->dbDate($to),
                'event_date' => $eventDate ? Carbon::parse($eventDate) : null,
                'tipo_identificacion' => $this->fitDbText($this->firstString($data, ['tipoIdentificacion']), 20),
                'identificacion' => $this->fitDbText($this->firstString($data, ['identificacion']), 40),
                'primer_nombre' => $this->fitDbText($this->firstString($data, ['primerNombre']), 120),
                'segundo_nombre' => $this->fitDbText($this->firstString($data, ['segundoNombre']), 120),
                'primer_apellido' => $this->fitDbText($this->firstString($data, ['primerApellido']), 120),
                'segundo_apellido' => $this->fitDbText($this->firstString($data, ['segundoApellido']), 120),
                'fecha_nacimiento' => $birthDate
                    ? Carbon::parse($birthDate)
                    : null,
                'edad' => $resolvedAge,
                'edad_meses' => $resolvedAgeMonths,
                'rango_edad' => $this->fitDbText($this->firstString($data, ['rangoEdad']), 80),
                'codigo_ips' => $this->fitDbText($this->firstString($data, ['codigoIps', 'codPrestador', 'codigoHabilitacion', 'codigohabilitacion']), 50),
                'ips_primaria' => $this->fitDbText($this->firstString($data, ['ips_Prim']), 200),
                'codigo_servicio' => $this->fitDbText($serviceCode, 60),
                'descripcion_servicio' => $this->fitDbText($serviceDescription, 255),
                'diagnostico_principal' => $this->fitDbText($diagnosticCode, 60),
                'finalidad' => $this->fitDbText($this->firstString($data, [
                    'finalidadConsulta',
                    'finalidadProcedimiento',
                    'finalidadTecnologiaSalud',
                    'finalidad',
                ]), 60),
                'record_hash' => $recordHash,
                'payload' => $this->safeDbText(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                'source_run_id' => $runId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if ($record['edad'] !== null) {
                $record['edad'] = (int) floor((float) $record['edad']);
            }

            if ($record['edad_meses'] !== null) {
                $record['edad_meses'] = (int) floor((float) $record['edad_meses']);
            }

            if (!$this->matchesModuleFilters($record, (array) ($module['filters'] ?? []))) {
                continue;
            }

            $normalized[] = $record;
        }

        return $normalized;
    }

    protected function matchesModuleFilters(array $record, array $filters): bool
    {
        if ($filters === []) {
            return true;
        }

        $age = isset($record['edad']) && is_numeric($record['edad']) ? (int) $record['edad'] : null;
        if (isset($filters['age_year_min']) && ($age === null || $age < (int) $filters['age_year_min'])) {
            return false;
        }

        if (isset($filters['age_year_max']) && ($age === null || $age > (int) $filters['age_year_max'])) {
            return false;
        }

        if (!empty($filters['age_years'])) {
            $allowedYears = array_map('intval', (array) $filters['age_years']);
            if ($age === null || !in_array($age, $allowedYears, true)) {
                return false;
            }
        }

        if (isset($filters['semester'])) {
            $month = null;
            if ($record['event_date'] instanceof Carbon) {
                $month = (int) $record['event_date']->month;
            }

            if ($month === null) {
                return false;
            }

            $semester = (int) $filters['semester'];
            if ($semester === 1 && !in_array($month, [1, 2, 3, 4, 5, 6], true)) {
                return false;
            }

            if ($semester === 2 && !in_array($month, [7, 8, 9, 10, 11, 12], true)) {
                return false;
            }
        }

        return true;
    }

    protected function deleteWindowRecords(string $courseKey, string $moduleKey, Carbon $from, Carbon $to): void
    {
        DB::table('ciclo_vida_cache_records')
            ->where('course_key', $courseKey)
            ->where('module_key', $moduleKey)
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('event_date', [$from, $to->copy()->subDay()])
                    ->orWhere(function ($nested) use ($from, $to): void {
                        $nested->whereNull('event_date')
                            ->where('range_start', $from)
                            ->where('range_end', $to);
                    });
            })
            ->delete();

        DB::table('ciclo_vida_cache_summaries')
            ->where('course_key', $courseKey)
            ->where('module_key', $moduleKey)
            ->where('range_start', $from)
            ->where('range_end', $to)
            ->delete();
    }

    protected function storeSummary(
        string $courseKey,
        string $moduleKey,
        Carbon $from,
        Carbon $to,
        array $metrics,
        int $runId
    ): void {
        DB::table('ciclo_vida_cache_summaries')->insert([
            'course_key' => $courseKey,
            'module_key' => $moduleKey,
            'range_start' => $from,
            'range_end' => $to,
            'total_records' => (int) ($metrics['total'] ?? 0),
            'unique_patients' => count($metrics['patients'] ?? []),
            'unique_ips' => count($metrics['ips'] ?? []),
            'unique_services' => count($metrics['services'] ?? []),
            'metadata' => json_encode([
                'source_run_id' => $runId,
                'generated_at' => $this->dbDateTime(now()),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => $this->dbDateTime(now()),
            'updated_at' => $this->dbDateTime(now()),
        ]);
    }

    protected function accumulateMetrics(array &$metrics, array $record): void
    {
        $metrics['total'] = (int) ($metrics['total'] ?? 0) + 1;

        $patientKey = trim(($record['tipo_identificacion'] ?? '').'|'.($record['identificacion'] ?? ''), '|');
        if ($patientKey !== '') {
            $metrics['patients'][$patientKey] = true;
        }

        $ipsKey = trim((string) ($record['ips_primaria'] ?? ''));
        if ($ipsKey !== '') {
            $metrics['ips'][$ipsKey] = true;
        }

        $serviceKey = trim(($record['codigo_servicio'] ?? '').'|'.($record['descripcion_servicio'] ?? ''), '|');
        if ($serviceKey !== '') {
            $metrics['services'][$serviceKey] = true;
        }
    }

    protected function dbDate(Carbon $date): Carbon
    {
        return $date->copy()->startOfDay();
    }

    protected function dbDateTime(Carbon $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    protected function normalizeSqlEncoding(string $sql): string
    {
        if (mb_check_encoding($sql, 'UTF-8')) {
            return $sql;
        }

        foreach (['Windows-1252', 'ISO-8859-1'] as $encoding) {
            $converted = @mb_convert_encoding($sql, 'UTF-8', $encoding);
            if (is_string($converted) && $converted !== '' && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        return $sql;
    }

    protected function cleanupSqlText(string $sql): string
    {
        // Corrige casos de mojibake donde aparecieron apostrofes dentro de palabras:
        // invalida -> inv'alida, multiples -> m'ultiples, mas -> m'as.
        $sql = preg_replace("/([A-Za-z])'([A-Za-z])/", '$1$2', $sql) ?? $sql;

        return $sql;
    }

    protected function safeDbText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = $this->normalizeSqlEncoding($value);
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($converted === false) {
            $converted = preg_replace('/[^\x20-\x7E]/', '', $value) ?? $value;
        }

        $converted = preg_replace('/\s+/', ' ', $converted ?? '') ?? '';
        $converted = trim($converted);

        return $converted === '' ? null : $converted;
    }

    protected function fitDbText(?string $value, int $maxLength): ?string
    {
        $value = $this->safeDbText($value);
        if ($value === null) {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    protected function resolveBatchSize(array $sampleRow): int
    {
        $columnCount = max(count($sampleRow), 1);
        $maxParams = 1800;
        $batchSize = (int) floor($maxParams / $columnCount);

        return max(1, min($batchSize, 50));
    }

    protected function normalizeDate(array $data, array $keys): ?string
    {
        $lookup = $this->normalizeDataLookup($data);

        foreach ($keys as $key) {
            $value = $lookup[strtolower($key)] ?? null;
            if (empty($value)) {
                continue;
            }

            try {
                return Carbon::parse((string) $value)->toDateString();
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    protected function firstString(array $data, array $keys): ?string
    {
        $lookup = $this->normalizeDataLookup($data);

        foreach ($keys as $key) {
            $value = $lookup[strtolower($key)] ?? null;
            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function firstNumeric(array $data, array $keys): ?int
    {
        $lookup = $this->normalizeDataLookup($data);

        foreach ($keys as $key) {
            $value = $lookup[strtolower($key)] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return (int) floor((float) $value);
            }
        }

        return null;
    }

    protected function normalizeDataLookup(array $data): array
    {
        $lookup = [];

        foreach ($data as $key => $value) {
            $lookup[strtolower((string) $key)] = $value;
        }

        return $lookup;
    }
}
