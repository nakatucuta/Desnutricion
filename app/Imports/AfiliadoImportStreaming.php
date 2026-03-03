<?php

namespace App\Imports;

use App\Models\batch_verifications;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AfiliadoImportStreaming implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts
{
    protected array $errores = [];
    protected array $noAfiliados = [];

    // ✅ NUEVO: vacunas omitidas (duplicadas ya existentes o repetidas en el mismo Excel / FK inválida)
    protected array $vacunasOmitidas = [];

    private int $batch_verifications_id;
    private int $userId;
    private ?string $token;

    private int $totalRows;
    private int $processedRows = 0;

    /** @var null|callable(int $pct, string $step, string $msg): void */
    private $progressFn = null;

    private array $carnetCache = [];
    private array $afiliadoIdCache = [];

    /** ✅ Conexión LOCAL (sqlsrv) */
    private string $localConn = 'sqlsrv';

    private array $bufferRows = [];
    private int $bufferLimit = 20;

    private int $newAfil = 0;
    private int $oldAfil = 0;
    private int $newVacuna = 0;
    private int $oldVacuna = 0;

    private int $currentExcelRow;
    private bool $stopOnNonAffiliate = false;

    private bool $isFinalized = false;
    private bool $isFlushing = false;

    /** cache: columnas numéricas reales por tabla (leídas de sys.columns) */
    private array $numericColsCache = [];

    private bool $loggedTarget = false;

    /**
     * ✅ MODO NO ESTRICTO:
     * - "NO TIENE" / "SIN DATO" / etc => NULL
     * - Si un campo numérico llega con texto raro => NULL + error (no tumba el proceso)
     */
    private bool $modoEstricto = false;

    /** Tokens que tratamos como "vacío" */
    private array $nullTokens = [
        'NO TIENE', 'N/A', 'NA', 'SIN DATO', 'NULL', 'NONE', '?', 'NO APLICA', 'NO APLIQUE'
    ];

    private array $zeroIsValidNumericCols = [
        'edad_anos', 'edad_meses', 'edad_dias', 'total_meses',
    ];

    private array $fallbackNumericColsAfiliados = [
        'id' => 'int',
        'edad_anos' => 'int',
        'edad_meses' => 'int',
        'edad_dias' => 'int',
        'total_meses' => 'int',
        'edad_gestacional' => 'int',
        'embarazos_previos' => 'int',
        'semanas_gestacion' => 'int',
        'user_id' => 'int',
        'batch_verifications_id' => 'int',
    ];

    private array $fallbackNumericColsVacunas = [
        'id' => 'int',
        'afiliado_id' => 'int',
        'vacunas_id' => 'int',
        'num_frascos_utilizados' => 'int',
        'user_id' => 'int',
        'batch_verifications_id' => 'int',
    ];

    private array $textZeroNullCols = [
        'primer_nombre','segundo_nombre','primer_apellido','segundo_apellido',
        'genero','orientacion_sexual','pais_nacimiento','estatus_migratorio','lugar_atencion_parto',
        'aseguradora','pertenencia_etnica','pais_residencia','departamento_residencia','municipio_residencia',
        'comuna','area','direccion','telefono_fijo','celular','email',
        'enfermedad_contraindicacion','sintomas_reaccion','condicion_usuaria','tipo_antecedente','descripcion_antecedente',
        'observaciones_especiales',
        'madre_primer_nombre','madre_segundo_nombre','madre_primer_apellido','madre_segundo_apellido',
        'madre_correo','madre_telefono','madre_celular','madre_regimen','madre_pertenencia_etnica',
        'cuidador_primer_nombre','cuidador_segundo_nombre','cuidador_primer_apellido','cuidador_segundo_apellido',
        'cuidador_parentesco','cuidador_correo','cuidador_telefono','cuidador_celular',
        'esquema_vacunacion',
    ];

    /**
     * SQL Server puede inferir tipos numéricos en INSERT multi-row cuando mezcla
     * valores numéricos y texto por columna. Estas columnas deben viajar SIEMPRE
     * como string para evitar conversiones implícitas a bigint.
     */
    private array $forceStringCols = [
        'tipo_identificacion',
        'numero_identificacion',
        'numero_carnet',
        'telefono_fijo',
        'celular',
        'madre_identificacion',
        'madre_telefono',
        'madre_celular',
        'cuidador_identificacion',
        'cuidador_telefono',
        'cuidador_celular',
    ];

    /**
     * ✅ IMPORTANTE:
     * - Incluye tipo_identificacion para que no se pierda en normalizeAfiliadoRow()
     * - (Si tu tabla afiliados NO tiene tipo_identificacion, elimínala aquí y del insert)
     */
    private array $afiliadoColumns = [
        'area',
        'aseguradora',
        'autoriza_correos',
        'autoriza_llamadas',
        'batch_verifications_id',
        'celular',
        'comuna',
        'condicion_usuaria',
        'contraindicacion_vacuna',
        'created_at',
        'cuidador_celular',
        'cuidador_correo',
        'cuidador_identificacion',
        'cuidador_parentesco',
        'cuidador_primer_apellido',
        'cuidador_primer_nombre',
        'cuidador_segundo_apellido',
        'cuidador_segundo_nombre',
        'cuidador_telefono',
        'cuidador_tipo_identificacion',
        'departamento_residencia',
        'descripcion_antecedente',
        'desplazado',
        'direccion',
        'discapacitado',
        'edad_anos',
        'edad_dias',
        'edad_gestacional',
        'edad_meses',
        'email',
        'embarazos_previos',
        'enfermedad_contraindicacion',
        'esquema_completo',
        'esquema_vacunacion',
        'estatus_migratorio',
        'estudia',
        'fallecido',
        'fecha_antecedente',
        'fecha_atencion',
        'fecha_nacimiento',
        'fecha_prob_parto',
        'fecha_ultima_menstruacion',
        'genero',
        'lugar_atencion_parto',
        'madre_celular',
        'madre_correo',
        'madre_desplazada',
        'madre_identificacion',
        'madre_pertenencia_etnica',
        'madre_primer_apellido',
        'madre_primer_nombre',
        'madre_regimen',
        'madre_segundo_apellido',
        'madre_segundo_nombre',
        'madre_telefono',
        'madre_tipo_identificacion',
        'municipio_residencia',
        'numero_carnet',
        'numero_identificacion',
        'observaciones_especiales',
        'orientacion_sexual',
        'pais_nacimiento',
        'pais_residencia',
        'pertenencia_etnica',
        'primer_apellido',
        'primer_nombre',
        'reaccion_biologicos',
        'regimen',
        'segundo_apellido',
        'segundo_nombre',
        'semanas_gestacion',
        'sexo',
        'sintomas_reaccion',
        'telefono_fijo',
        'tipo_antecedente',
        'tipo_identificacion',      // ✅
        'total_meses',
        'updated_at',
        'user_id',
        'victima_conflicto',
    ];

    // ✅ NUEVO: cache para resolver nombre de vacuna desde referencia_vacunas
    private array $vacunaNombreCache = [];
    private ?string $vacunaNombreColumn = null;

    public function __construct(
        int $userId,
        ?string $uploadToken = null,
        ?int $totalRows = null,
        ?callable $progressFn = null
    ) {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $this->userId = $userId;
        $this->token  = $uploadToken;

        $this->totalRows = max(1, (int)($totalRows ?? 1));
        $this->progressFn = $progressFn;

        // ✅ crear batch_verifications EN sqlsrv
        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->setConnection($this->localConn);
        $verificacion->save();

        $this->batch_verifications_id = (int)$verificacion->id;
        $this->currentExcelRow = $this->startRow();
    }

    public function startRow(): int { return 3; }
    public function chunkSize(): int { return 200; }
    public function batchSize(): int { return 20; }

    public function getBatchVerificationsID(): int { return $this->batch_verifications_id; }
    public function getErrores(): array { return $this->errores; }
    public function getNoAfiliados(): array { return $this->noAfiliados; }
    public function getVacunasOmitidas(): array { return $this->vacunasOmitidas; } // ✅ nuevo

    public function getStats(): array
    {
        return [
            'newAfil'     => $this->newAfil,
            'oldAfil'     => $this->oldAfil,
            'newVacuna'   => $this->newVacuna,
            'oldVacuna'   => $this->oldVacuna,
            'noAfiliados' => count($this->noAfiliados),
            'vacunasOmitidas' => count($this->vacunasOmitidas), // ✅ nuevo
        ];
    }

    public function finalize(): void
    {
        if ($this->isFinalized) return;
        $this->isFinalized = true;

        try { $this->flushBuffer(); }
        catch (\Throwable $e) { Log::error("AfiliadoImportStreaming finalize ERROR: " . $e->getMessage()); }

        try {
            if ($this->token) {
                $payload = [
                    'batch_verifications_id' => $this->batch_verifications_id,
                    'stats' => $this->getStats(),
                    'no_afiliados' => $this->noAfiliados,
                    'vacunas_omitidas' => $this->vacunasOmitidas, // ✅ nuevo (para mostrarlo en la UI)
                    'errores' => $this->errores,
                    'generated_at' => now()->toDateTimeString(),
                ];
                Storage::put("imports/{$this->token}_resultado.json", json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo guardar JSON de resultado: " . $e->getMessage());
        }
    }

    private function pushProgress(string $step, string $msg, ?int $forcePct = null): void
    {
        if (!$this->progressFn) return;

        $pct = $forcePct;
        if ($pct === null) {
            $ratio = $this->processedRows / max(1, $this->totalRows);
            $pct = 10 + (int)round($ratio * 85);
            $pct = max(0, min(99, $pct));
        }

        try { ($this->progressFn)($pct, $step, $msg); } catch (\Throwable $e) {}
    }

    private function addError(string $msg): void
    {
        $msg = trim($msg);
        if ($msg === '') return;

        $this->errores[] = $msg;

        if (count($this->errores) > 5000) {
            $this->errores = array_slice($this->errores, 0, 5000);
        }
    }

    private function addNoAfiliado(int $excelRow, string $tipo, string $numero, string $motivo = 'No existe en BD externa'): void
    {
        $this->noAfiliados[] = [
            'fila_excel' => $excelRow,
            'tipo_identificacion' => $tipo,
            'numero_identificacion' => $numero,
            'motivo' => $motivo,
        ];

        if (count($this->noAfiliados) > 5000) {
            $this->noAfiliados = array_slice($this->noAfiliados, 0, 5000);
        }
    }

    // ✅ NUEVO: registrar vacunas omitidas (duplicadas / repetidas / FK inválida)
    private function addVacunaOmitida(
        int $excelRow,
        string $tipo,
        string $numero,
        ?string $carnet,
        ?int $afiliadoId,
        int $vacunasId,
        ?string $vacunaNombre,
        ?string $docis,
        ?string $fechaVacuna,
        string $motivo
    ): void {
        $this->vacunasOmitidas[] = [
            'fila_excel' => $excelRow,
            'tipo_identificacion' => $tipo,
            'numero_identificacion' => $numero,
            'numero_carnet' => $carnet,
            'afiliado_id' => $afiliadoId,
            'vacunas_id' => $vacunasId,
            'vacuna_nombre' => $vacunaNombre,
            'docis' => $docis,
            'fecha_vacuna' => $fechaVacuna,
            'motivo' => $motivo,
        ];

        if (count($this->vacunasOmitidas) > 5000) {
            $this->vacunasOmitidas = array_slice($this->vacunasOmitidas, 0, 5000);
        }
    }

    private function isZeroLike($v): bool
    {
        if ($v === null) return false;

        if (is_int($v) || is_float($v)) return ((float)$v === 0.0);

        $s = trim((string)$v);
        if ($s === '') return false;

        $s = str_replace(',', '.', $s);
        return is_numeric($s) && ((float)$s === 0.0);
    }

    private function isNullToken($v): bool
    {
        if ($v === null) return true;
        $s = is_string($v) ? trim($v) : trim((string)$v);
        if ($s === '') return true;
        $upper = mb_strtoupper($s, 'UTF-8');
        return in_array($upper, $this->nullTokens, true);
    }

    private function cleanText($v)
    {
        if ($v === null) return null;

        $v = is_string($v) ? trim($v) : $v;

        // ✅ Modo no estricto: NO TIENE => NULL
        if ($this->isNullToken($v)) return null;

        // ✅ Para texto: 0 suele ser “relleno” => lo volvemos null
        if ($this->isZeroLike($v)) return null;

        return is_string($v) ? trim($v) : $v;
    }

    private function cleanAny($v)
    {
        if ($v === null) return null;

        $v = is_string($v) ? trim($v) : $v;

        // ✅ Modo no estricto: NO TIENE => NULL
        if ($this->isNullToken($v)) return null;

        if ($this->isZeroLike($v)) return null;

        return $v;
    }

    private function sanitizeTextZeroColumns(array $data, int $excelRow): array
    {
        foreach ($this->textZeroNullCols as $col) {
            if (!array_key_exists($col, $data)) continue;

            // ✅ 0-like a NULL
            if ($this->isZeroLike($data[$col])) $data[$col] = null;

            // ✅ tokens tipo NO TIENE / ? / etc a NULL
            if ($this->isNullToken($data[$col])) $data[$col] = null;

            if (is_string($data[$col])) {
                $s = trim($data[$col]);
                if ($s === '') $data[$col] = null;
            }
        }
        return $data;
    }

    private function normalizeAfiliadoRow(array $row): array
    {
        $template = array_fill_keys($this->afiliadoColumns, null);
        $row = array_intersect_key($row, $template);
        $row = array_replace($template, $row);

        foreach ($this->forceStringCols as $col) {
            if (!array_key_exists($col, $row)) continue;
            if ($row[$col] === null) continue;

            $v = is_string($row[$col]) ? trim($row[$col]) : trim((string)$row[$col]);
            $row[$col] = ($v === '' || $this->isNullToken($v)) ? null : $v;
        }

        return $row;
    }

    private function toIntOrNull($v): ?int
    {
        if ($v === null) return null;

        if ($this->isNullToken($v)) return null;

        if (is_string($v)) {
            $v = trim($v);
            $v = str_replace(',', '.', $v);
        }

        if (!is_numeric($v)) return null;
        return (int) floor((float) $v);
    }

    /**
     * ✅ FECHAS: convierte Excel/strings (dd/mm/yyyy) a Y-m-d.
     * Vacío / NO TIENE / ? => null (evita 22007).
     */
    private function toSqlDate($v): ?string
    {
        if ($v === null) return null;

        if ($this->isNullToken($v)) return null;

        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }

        // Excel serial
        if (is_int($v) || is_float($v) || (is_string($v) && is_numeric(trim($v)))) {
            try {
                $dt = Date::excelToDateTimeObject((float)$v);
                return $dt ? $dt->format('Y-m-d') : null;
            } catch (\Throwable $e) {
                // sigue
            }
        }

        $s = trim((string)$v);
        if ($s === '' || $s === '-' || $s === '0') return null;

        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'Y/m/d'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $s);
                if ($dt !== false) return $dt->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ✅ timestamps compatibles con datetime (sin milisegundos)
     */
    private function nowSqlDateTime(): string
    {
        return now()->format('Y-m-d H:i:s');
    }

    /**
     * ✅ FECHAS ESTRICTAS ANTES DE INSERTAR:
     * Si no queda YYYY-MM-DD => NULL y log.
     */
    private function sanitizeDateColumnsStrict(array $row, int $excelRow): array
    {
        $dateCols = [
            'fecha_antecedente',
            'fecha_atencion',
            'fecha_nacimiento',
            'fecha_prob_parto',
            'fecha_ultima_menstruacion',
        ];

        foreach ($dateCols as $k) {
            if (!array_key_exists($k, $row)) continue;

            $raw = $row[$k];
            $row[$k] = $this->toSqlDate($raw);

            if ($row[$k] !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$row[$k])) {
                Log::warning("IMPORT FECHA INVALIDA => NULL", [
                    'excelRow' => $excelRow,
                    'col' => $k,
                    'raw' => is_scalar($raw) ? (string)$raw : gettype($raw),
                    'after' => $row[$k],
                ]);
                $row[$k] = null;
            }
        }

        return $row;
    }

    /**
     * ✅ LECTURA REAL DE TIPOS EN SQL SERVER (evita que se cuele texto a INT)
     */
    private function getNumericColumns(string $tableName): array
    {
        if (isset($this->numericColsCache[$tableName])) {
            return $this->numericColsCache[$tableName];
        }

        try {
            $rows = DB::connection($this->localConn)->select("
                SELECT
                    c.name AS COLUMN_NAME,
                    t.name AS DATA_TYPE
                FROM sys.columns c
                INNER JOIN sys.objects o ON c.object_id = o.object_id
                INNER JOIN sys.types t
                    ON c.system_type_id = t.system_type_id
                   AND c.user_type_id   = t.user_type_id
                WHERE o.type = 'U'
                  AND o.name = ?
                  AND t.is_user_defined = 0
                  AND t.name IN ('int','bigint','smallint','tinyint','decimal','numeric','float','real','money','smallmoney')
            ", [$tableName]);

            $map = [];
            foreach ($rows as $r) {
                $map[(string)$r->COLUMN_NAME] = (string)$r->DATA_TYPE;
            }

            if (empty($map)) {
                $map = ($tableName === 'afiliados') ? $this->fallbackNumericColsAfiliados : $this->fallbackNumericColsVacunas;
                Log::warning("IMPORT: sys.columns devolvió vacío para {$tableName}. Usando fallback.");
            }

            Log::info("IMPORT NUMERIC MAP {$tableName}", $map);

            return $this->numericColsCache[$tableName] = $map;

        } catch (\Throwable $e) {
            Log::warning("IMPORT: no pude leer sys.columns para {$tableName}: ".$e->getMessage());
            $map = ($tableName === 'afiliados') ? $this->fallbackNumericColsAfiliados : $this->fallbackNumericColsVacunas;
            return $this->numericColsCache[$tableName] = $map;
        }
    }

    /**
     * ✅ MODO NO ESTRICTO:
     * - Tokens "NO TIENE" => NULL
     * - Si llega texto raro en numérico => NULL + error (pero NO tumba)
     */
    private function sanitizeNumericByMap(array $data, array $numCols, int $excelRow, string $tableName): array
    {
        foreach ($numCols as $col => $type) {
            if (!array_key_exists($col, $data)) continue;

            $v = $data[$col];

            if ($v === null || $v === '') {
                $data[$col] = null;
                continue;
            }

            if ($this->isNullToken($v)) {
                $data[$col] = null;
                continue;
            }

            if (is_string($v)) {
                $vv = trim($v);
                $vv = str_replace(',', '.', $vv);
                $v = $vv;
            }

            if (!is_numeric($v)) {
                $this->addError("Fila {$excelRow}: '{$tableName}.{$col}' es {$type} pero llegó '".mb_substr((string)$v, 0, 120)."' -> lo guardo NULL");
                $data[$col] = null;
                continue;
            }

            if (in_array($type, ['int','bigint','smallint','tinyint'], true)) {
                $data[$col] = (int) floor((float)$v);
            } else {
                $data[$col] = (string)$v;
            }
        }

        return $data;
    }

    private function sanitizeAfiliadoInsert(array $data, int $excelRow): array
    {
        $numCols = $this->getNumericColumns('afiliados');
        return $this->sanitizeNumericByMap($data, $numCols, $excelRow, 'afiliados');
    }

    private function sanitizeVacunaInsert(array $data, int $excelRow): array
    {
        $numCols = $this->getNumericColumns('vacunas');
        return $this->sanitizeNumericByMap($data, $numCols, $excelRow, 'vacunas');
    }

    private function hardValidateSameKeys(array $rows): void
    {
        if (empty($rows)) return;

        $keys0 = array_keys($rows[0]);
        foreach ($rows as $i => $r) {
            if (array_keys($r) !== $keys0) {
                Log::error("AFILIADOS DESALINEADOS en flushBuffer fila={$i}");
                Log::error("keys0=" . implode(',', $keys0));
                Log::error("keys{$i}=" . implode(',', array_keys($r)));
                throw new \RuntimeException("Chunk afiliados desalineado");
            }
        }
    }

    // ✅ NUEVO: detectar columna de "nombre" en referencia_vacunas sin romper si no existe
    private function detectVacunaNombreColumn(): string
    {
        if ($this->vacunaNombreColumn) return $this->vacunaNombreColumn;

        $candidates = ['nombre', 'biologico', 'vacuna', 'descripcion', 'descripcion_vacuna', 'nombre_vacuna'];

        try {
            $rows = DB::connection($this->localConn)->select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = 'referencia_vacunas'
            ");

            $cols = array_map(fn($r) => mb_strtolower((string)$r->COLUMN_NAME, 'UTF-8'), $rows);

            foreach ($candidates as $cand) {
                if (in_array($cand, $cols, true)) {
                    $this->vacunaNombreColumn = $cand;
                    return $this->vacunaNombreColumn;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo detectar columna nombre en referencia_vacunas: ".$e->getMessage());
        }

        // fallback: si no existe nada, devolvemos 'id' (y luego nombre queda null)
        $this->vacunaNombreColumn = 'id';
        return $this->vacunaNombreColumn;
    }

    // ✅ NUEVO: precargar nombres de vacunas por IDs (cache)
    private function preloadVacunaNames(array $vacIds): array
    {
        $vacIds = array_values(array_unique(array_filter($vacIds, fn($x) => is_int($x) && $x > 0)));

        $missing = [];
        foreach ($vacIds as $id) {
            if (!array_key_exists($id, $this->vacunaNombreCache)) $missing[] = $id;
        }
        if (empty($missing)) return $this->vacunaNombreCache;

        $col = $this->detectVacunaNombreColumn();

        // Si no logramos columna de nombre, no hacemos query de nombre (evita SQL error)
        if ($col === 'id') {
            foreach ($missing as $id) $this->vacunaNombreCache[$id] = null;
            return $this->vacunaNombreCache;
        }

        try {
            $rows = DB::connection($this->localConn)
                ->table('referencia_vacunas')
                ->select(['id', DB::raw("CAST([$col] AS NVARCHAR(255)) AS vacuna_nombre")])
                ->whereIn('id', $missing)
                ->get();

            foreach ($rows as $r) {
                $id = (int)$r->id;
                $name = $r->vacuna_nombre !== null ? trim((string)$r->vacuna_nombre) : null;
                $this->vacunaNombreCache[$id] = ($name === '' ? null : $name);
            }

            // los que no aparecieron => null
            foreach ($missing as $id) {
                if (!array_key_exists($id, $this->vacunaNombreCache)) $this->vacunaNombreCache[$id] = null;
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo precargar nombres de referencia_vacunas: ".$e->getMessage());
            foreach ($missing as $id) $this->vacunaNombreCache[$id] = null;
        }

        return $this->vacunaNombreCache;
    }

    public function model(array $row)
    {
        $minCols = 272;
        $need = max($minCols, count($row));
        $row = array_replace(array_fill(0, $need, null), $row);

        $excelRow = $this->currentExcelRow++;
        $this->processedRows++;

        if ($excelRow === $this->startRow()) {
            Log::info('IMPORT DEBUG - TOTAL COLUMNAS FILA', [
                'excelRow' => $excelRow,
                'count' => count($row),
                'lastIndex' => count($row) - 1,
            ]);
        }

        if ($excelRow === 3) {
            Log::info("IMPORT CLASS RUNNING => ".__CLASS__, [
                'conn' => $this->localConn,
                'batch' => $this->batch_verifications_id,
            ]);
        }

        $tipo_identifi   = $this->cleanAny((string)($row[1] ?? null));
        $numero_identifi = $this->cleanAny(isset($row[2]) ? (string)$row[2] : null);

        if (!$tipo_identifi || !$numero_identifi) {
            $this->addError("Fila {$excelRow}: identificación incompleta (tipo o número vacío).");
            $this->pushProgress('validacion', "Validando filas… (fila {$excelRow})");
            return null;
        }

        // ✅ Fechas robustas
        $fechaatencion    = $this->toSqlDate($row[0] ?? null);
        $fechaNacimiento  = $this->toSqlDate($row[7] ?? null);
        $fechaProbParto   = $this->toSqlDate($row[46] ?? null);
        $fechaAntecedente = $this->toSqlDate($row[48] ?? null);

        // ✅ Edades
        $edad_anos         = $this->toIntOrNull($row[8]  ?? null);
        $edad_meses        = $this->toIntOrNull($row[9]  ?? null);
        $edad_dias         = $this->toIntOrNull($row[10] ?? null);
        $total_meses       = $this->toIntOrNull($row[11] ?? null);

        $edad_gestacional  = $this->toIntOrNull($row[16] ?? null);
        $embarazos_previos = $this->toIntOrNull($row[47] ?? null);

        $data = [
            'edad_anos' => $edad_anos,
            'edad_meses' => $edad_meses,
            'edad_dias' => $edad_dias,
            'total_meses' => $total_meses,
            'edad_gestacional' => $edad_gestacional,
            'embarazos_previos' => $embarazos_previos,
        ];

        $rules = [
            'edad_anos' => 'nullable|integer',
            'edad_meses' => 'nullable|integer',
            'edad_dias' => 'nullable|integer',
            'total_meses' => 'nullable|integer',
            'edad_gestacional' => 'nullable|integer',
            'embarazos_previos' => 'nullable|integer',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $errs = implode(' | ', $validator->errors()->all());
            $this->addError("Fila {$excelRow}: error de validación: {$errs} ({$tipo_identifi} {$numero_identifi})");
            $this->pushProgress('validacion', "Validando filas… (fila {$excelRow})");
            return null;
        }

        // Cola final (formato nuevo: 255..258, formato anterior: 251..254)
        [$responsable, $fuen_ingresado_paiweb, $motivo_noingreso, $observaciones] = $this->resolveFinalColumns($row);
        $regimenVacuna         = $this->cleanText($row[20] ?? null);

        // carnet externo con cache
        $cacheKey = $tipo_identifi . '|' . $numero_identifi;

        if (!array_key_exists($cacheKey, $this->carnetCache)) {
            try {
                $ext = DB::connection('sqlsrv_1')
                    ->table('maestroIdentificaciones')
                    ->select('numeroCarnet')
                    ->where('identificacion', $numero_identifi)
                    ->where('tipoIdentificacion', $tipo_identifi)
                    ->first();

                $this->carnetCache[$cacheKey] = $ext->numeroCarnet ?? null;
            } catch (\Throwable $e) {
                $this->carnetCache[$cacheKey] = null;
                $msg = "Fila {$excelRow}: error consultando DB externa: ".$e->getMessage();
                $this->addError($msg);
                $this->pushProgress('error', 'Error consultando BD externa.', 99);
                throw new \RuntimeException($msg);
            }
        }

        $numero_carnet = $this->carnetCache[$cacheKey];

        if (!$numero_carnet) {
            $msg = "Fila {$excelRow}: NO es afiliado (no existe en BD externa) => {$tipo_identifi} {$numero_identifi}";
            $this->addError($msg);
            $this->addNoAfiliado($excelRow, (string)$tipo_identifi, (string)$numero_identifi, 'No existe en BD externa');
            Log::warning("IMPORT NO AFILIADO: ".$msg);
            $this->pushProgress('no_afiliado', "Detectando NO afiliados… (fila {$excelRow})");
            if ($this->stopOnNonAffiliate) throw new \RuntimeException($msg);
            return null;
        }

        // existe afiliado local? (cache por carnet)
        if (!isset($this->afiliadoIdCache[$numero_carnet])) {
            $local = DB::connection($this->localConn)
                ->table('afiliados')
                ->select('id')
                ->where('numero_carnet', $numero_carnet)
                ->first();

            $this->afiliadoIdCache[$numero_carnet] = $local ? (int)$local->id : 0;
        }

        $afiliado_id_local = (int)$this->afiliadoIdCache[$numero_carnet];

        $afiliadoData = null;
        if ($afiliado_id_local === 0) {
            $nowTs = $this->nowSqlDateTime();

            $afiliadoData = [
                'fecha_atencion' => $fechaatencion,
                'tipo_identificacion' => $tipo_identifi,
                'numero_identificacion' => $numero_identifi,
                'numero_carnet' => $numero_carnet,

                'primer_nombre' => $this->cleanText($row[3] ?? null),
                'segundo_nombre' => $this->cleanText($row[4] ?? null),
                'primer_apellido' => $this->cleanText($row[5] ?? null),
                'segundo_apellido' => $this->cleanText($row[6] ?? null),
                'fecha_nacimiento' => $fechaNacimiento,

                'edad_anos' => $edad_anos,
                'edad_meses' => $edad_meses,
                'edad_dias' => $edad_dias,
                'total_meses' => $total_meses,

                'esquema_completo' => $this->cleanText($row[12] ?? null),
                'sexo' => $this->cleanText($row[13] ?? null),
                'genero' => $this->cleanText($row[14] ?? null),
                'orientacion_sexual' => $this->cleanText($row[15] ?? null),
                'edad_gestacional' => $edad_gestacional,
                'pais_nacimiento' => $this->cleanText($row[17] ?? null),
                'estatus_migratorio' => $this->cleanText($row[18] ?? null),
                'lugar_atencion_parto' => $this->cleanText($row[19] ?? null),
                'regimen' => $this->cleanText($row[20] ?? null),
                'aseguradora' => $this->cleanText($row[21] ?? null),
                'pertenencia_etnica' => $this->cleanText($row[22] ?? null),
                'desplazado' => $this->cleanText($row[23] ?? null),
                'discapacitado' => $this->cleanText($row[24] ?? null),
                'fallecido' => $this->cleanText($row[25] ?? null),
                'victima_conflicto' => $this->cleanText($row[26] ?? null),
                'estudia' => $this->cleanText($row[27] ?? null),
                'pais_residencia' => $this->cleanText($row[28] ?? null),
                'departamento_residencia' => $this->cleanText($row[29] ?? null),
                'municipio_residencia' => $this->cleanText($row[30] ?? null),
                'comuna' => $this->cleanText($row[31] ?? null),
                'area' => $this->cleanText($row[32] ?? null),
                'direccion' => $this->cleanText($row[33] ?? null),

                // ✅ modo no estricto: NO TIENE => NULL (evita nvarchar->bigint si tu columna fuera numérica)
                'telefono_fijo' => $this->cleanAny($row[34] ?? null),
                'celular' => $this->cleanAny($row[35] ?? null),

                'email' => $this->cleanText($row[36] ?? null),

                'autoriza_llamadas' => $this->cleanText($row[37] ?? null),
                'autoriza_correos' => $this->cleanText($row[38] ?? null),
                'contraindicacion_vacuna' => $this->cleanText($row[39] ?? null),
                'enfermedad_contraindicacion' => $this->cleanText($row[40] ?? null),
                'reaccion_biologicos' => $this->cleanText($row[41] ?? null),
                'sintomas_reaccion' => $this->cleanText($row[42] ?? null),
                'condicion_usuaria' => $this->cleanText($row[43] ?? null),

                'fecha_ultima_menstruacion' => $this->toSqlDate($row[44] ?? null),
                'semanas_gestacion' => $this->toIntOrNull($row[45] ?? null),
                'fecha_prob_parto' => $fechaProbParto,
                'embarazos_previos' => $embarazos_previos,
                'fecha_antecedente' => $fechaAntecedente,

                'tipo_antecedente' => $this->cleanText($row[49] ?? null),
                'descripcion_antecedente' => $this->cleanText($row[50] ?? null),
                'observaciones_especiales' => $this->cleanText($row[51] ?? null),

                'madre_tipo_identificacion' => $this->cleanText($row[52] ?? null),
                'madre_identificacion' => $this->cleanAny($row[53] ?? null),
                'madre_primer_nombre' => $this->cleanText($row[54] ?? null),
                'madre_segundo_nombre' => $this->cleanText($row[55] ?? null),
                'madre_primer_apellido' => $this->cleanText($row[56] ?? null),
                'madre_segundo_apellido' => $this->cleanText($row[57] ?? null),
                'madre_correo' => $this->cleanText($row[58] ?? null),
                'madre_telefono' => $this->cleanAny($row[59] ?? null),
                'madre_celular' => $this->cleanAny($row[60] ?? null),
                'madre_regimen' => $this->cleanText($row[61] ?? null),
                'madre_pertenencia_etnica' => $this->cleanText($row[62] ?? null),
                'madre_desplazada' => $this->cleanText($row[63] ?? null),

                'cuidador_tipo_identificacion' => $this->cleanText($row[64] ?? null),
                'cuidador_identificacion' => $this->cleanAny($row[65] ?? null),
                'cuidador_primer_nombre' => $this->cleanText($row[66] ?? null),
                'cuidador_segundo_nombre' => $this->cleanText($row[67] ?? null),
                'cuidador_primer_apellido' => $this->cleanText($row[68] ?? null),
                'cuidador_segundo_apellido' => $this->cleanText($row[69] ?? null),
                'cuidador_parentesco' => $this->cleanText($row[70] ?? null),
                'cuidador_correo' => $this->cleanText($row[71] ?? null),
                'cuidador_telefono' => $this->cleanAny($row[72] ?? null),
                'cuidador_celular' => $this->cleanAny($row[73] ?? null),

                'esquema_vacunacion' => $this->cleanText($row[74] ?? null),

                'user_id' => $this->userId,
                'batch_verifications_id' => $this->batch_verifications_id,

                'created_at' => $nowTs,
                'updated_at' => $nowTs,
            ];

            // Sanitiza por si algo raro se coló
            $afiliadoData = $this->sanitizeDateColumnsStrict($afiliadoData, $excelRow);
            $afiliadoData = $this->sanitizeTextZeroColumns($afiliadoData, $excelRow);
            $afiliadoData = $this->sanitizeAfiliadoInsert($afiliadoData, $excelRow);
            $afiliadoData = $this->normalizeAfiliadoRow($afiliadoData);
        }

        // Vacunas
        $vacunasData = $this->extraerVacunasOptimizado(
            $row,
            $fechaatencion,
            $responsable,
            $fuen_ingresado_paiweb,
            $motivo_noingreso,
            $observaciones,
            $regimenVacuna
        );

        $this->bufferRows[] = [
            'excelRow'    => $excelRow,
            'tipo'        => (string)$tipo_identifi,
            'numero'      => (string)$numero_identifi,
            'carnet'      => $numero_carnet,
            'existe'      => ($afiliado_id_local !== 0),
            'afiliadoData'=> $afiliadoData,
            'vacunas'     => $vacunasData,
        ];

        $this->pushProgress('procesando', "Procesando filas… (fila {$excelRow})");

        if (count($this->bufferRows) >= $this->bufferLimit) {
            $this->flushBuffer();
        }

        return null;
    }

    public function __destruct()
    {
        if ($this->isFinalized) return;

        try {
            $this->finalize();
            $this->pushProgress('finalizando', 'Finalizando…', 99);
        } catch (\Throwable $e) {
            Log::error("ERROR finalize __destruct: " . $e->getMessage());
        }
    }

    private function normalizeDocis($value): ?string
    {
        if ($value === null) return null;
        $s = trim((string)$value);
        if ($s === '' || strtoupper($s) === 'NONE') return null;

        $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = preg_replace('/\s+/', ' ', $s);
        return mb_strtoupper($s, 'UTF-8');
    }

    private function forceStringLots(array $row): array
    {
        foreach (['lote', 'lote_jeringa', 'lote_diluyente'] as $k) {
            if (array_key_exists($k, $row)) {
                $row[$k] = ($row[$k] === null) ? null : trim((string)$row[$k]);
                if ($row[$k] === '') $row[$k] = null;
            }
        }
        return $row;
    }

    private function flushBuffer(): void
    {
        if ($this->isFlushing) return;
        $this->isFlushing = true;

        try {
            if (empty($this->bufferRows)) return;

            $buffer = $this->bufferRows;
            $this->bufferRows = [];

            $this->pushProgress('guardando', 'Guardando bloque…');

            $db = DB::connection($this->localConn);

            if (!$this->loggedTarget) {
                $this->loggedTarget = true;
                try {
                    $info = $db->selectOne("SELECT DB_NAME() AS db, @@SERVERNAME AS server");
                    Log::info('IMPORT TARGET SQLSRV', (array)$info);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo loguear target SQLSRV: '.$e->getMessage());
                }
            }

            $startedHere = false;
            if ($db->transactionLevel() === 0) {
                $db->beginTransaction();
                $startedHere = true;
            }

            try {
                // 1) Map afiliados existentes por carnet
                $carnets = array_values(array_unique(array_filter(array_map(
                    fn($x) => $x['carnet'] ?? null,
                    $buffer
                ))));

                $afRows = $db->table('afiliados')
                    ->select('id', 'numero_carnet')
                    ->whereIn('numero_carnet', $carnets)
                    ->get();

                $afiliadosMap = collect($afRows)->keyBy('numero_carnet');

                // 2) Insert afiliados nuevos
                $insertAfiliados = [];
                $insertAfiliadosExcelRows = [];

                foreach ($buffer as $fila) {
                    $row = $fila['afiliadoData'] ?? null;
                    if (empty($row) || !is_array($row)) continue;

                    $excelRow = (int)($fila['excelRow'] ?? 0);

                    // ✅ fechas vacías/basura => NULL
                    $row = $this->sanitizeDateColumnsStrict($row, $excelRow);

                    // ✅ timestamps (sin ms)
                    $row['created_at'] = $this->nowSqlDateTime();
                    $row['updated_at'] = $this->nowSqlDateTime();

                    $row = $this->sanitizeTextZeroColumns($row, $excelRow);
                    $row = $this->sanitizeAfiliadoInsert($row, $excelRow);
                    $row = $this->normalizeAfiliadoRow($row);

                    $insertAfiliados[] = $row;
                    $insertAfiliadosExcelRows[] = $excelRow;
                }

                if (!empty($insertAfiliados)) {
                    $this->hardValidateSameKeys($insertAfiliados);

                    // SQL Server puede inferir tipos entre filas en INSERT multi-row
                    // y provocar conversiones nvarchar->bigint. Insertar fila a fila
                    // evita esa inferencia cruzada.
                    foreach ($insertAfiliados as $i => $afilRow) {
                        try {
                            $db->table('afiliados')->insert($afilRow);
                        } catch (\Throwable $e) {
                            $excelRow = (int)($insertAfiliadosExcelRows[$i] ?? 0);
                            Log::error('IMPORT AFILIADO INSERT ROW ERROR', [
                                'excelRow' => $excelRow,
                                'numero_identificacion' => $afilRow['numero_identificacion'] ?? null,
                                'numero_carnet' => $afilRow['numero_carnet'] ?? null,
                                'error' => $e->getMessage(),
                            ]);
                            throw $e;
                        }
                    }

                    $this->newAfil += count($insertAfiliados);

                    // refrescar mapa
                    $afRows = $db->table('afiliados')
                        ->select('id', 'numero_carnet')
                        ->whereIn('numero_carnet', $carnets)
                        ->get();

                    $afiliadosMap = collect($afRows)->keyBy('numero_carnet');
                }

                // cuenta afiliados viejos
                foreach ($buffer as $fila) {
                    if (!empty($fila['existe'])) $this->oldAfil++;
                }

                // 3) Vacunas existentes para evitar duplicados
                $afiliadoIdPorCarnet = $afiliadosMap->map(fn($a) => (int)$a->id)->all();
                $afiliadoIds = array_values(array_unique(array_filter($afiliadoIdPorCarnet)));

                $existingKeys = [];
                if (!empty($afiliadoIds)) {
                    $exist = $db->table('vacunas')
                        ->select('afiliado_id', 'vacunas_id', 'docis')
                        ->whereIn('afiliado_id', $afiliadoIds)
                        ->get();

                    foreach ($exist as $e) {
                        $k = (int)$e->afiliado_id . '|' . (int)$e->vacunas_id . '|' . ($this->normalizeDocis($e->docis) ?? '');
                        $existingKeys[$k] = true;
                    }
                }

                // ✅ 3.5) Validar FK de vacunas_id contra referencia_vacunas (y precargar nombres)
                $vacIdsInChunk = [];
                foreach ($buffer as $fila) {
                    foreach (($fila['vacunas'] ?? []) as $v) {
                        $id = $v['vacunas_id'] ?? null;
                        if ($id !== null && $id !== '' && is_numeric($id)) $vacIdsInChunk[(int)$id] = true;
                    }
                }
                $vacIdsInChunk = array_keys($vacIdsInChunk);

                $validVacIdSet = [];
                if (!empty($vacIdsInChunk)) {
                    $valid = $db->table('referencia_vacunas')
                        ->whereIn('id', $vacIdsInChunk)
                        ->pluck('id')
                        ->all();

                    foreach ($valid as $vid) $validVacIdSet[(int)$vid] = true;

                    // ✅ precargar nombres solo de los válidos
                    $this->preloadVacunaNames(array_keys($validVacIdSet));
                }

                // 4) Insert vacunas con template fijo
                $insertVacunas = [];
                $seenInChunk = [];

                $VACUNA_TEMPLATE = [
                    'docis' => null,
                    'laboratorio' => null,
                    'lote' => null,
                    'jeringa' => null,
                    'lote_jeringa' => null,
                    'diluyente' => null,
                    'lote_diluyente' => null,
                    'observacion' => null,
                    'gotero' => null,
                    'tipo_neumococo' => null,
                    'num_frascos_utilizados' => null,
                    'fecha_vacuna' => null,
                    'responsable' => null,
                    'fuen_ingresado_paiweb' => null,
                    'motivo_noingreso' => null,
                    'observaciones' => null,
                    'regimen' => null,
                    'batch_verifications_id' => null,
                    'afiliado_id' => null,
                    'vacunas_id' => null,
                    'user_id' => null,
                    'created_at' => null,
                    'updated_at' => null,
                ];

                foreach ($buffer as $fila) {
                    $carnet = $fila['carnet'] ?? null;
                    if (!$carnet) continue;

                    $afiliadoId = $afiliadoIdPorCarnet[$carnet] ?? null;
                    if (!$afiliadoId) continue;

                    $vacunas = $fila['vacunas'] ?? [];
                    if (empty($vacunas)) continue;

                    foreach ($vacunas as $vacunaData) {
                        $vacunasId = $vacunaData['vacunas_id'] ?? null;

                        if ($vacunasId === null || $vacunasId === '' || !is_numeric($vacunasId)) {
                            Log::warning("IMPORT VACUNA SKIP: vacunas_id inválido", [
                                'excelRow' => $fila['excelRow'] ?? null,
                                'vacunas_id' => $vacunasId,
                                'carnet' => $fila['carnet'] ?? null,
                            ]);
                            continue;
                        }

                        $vacunasId = (int)$vacunasId;

                        $docisNorm = $this->normalizeDocis($vacunaData['docis'] ?? null);
                        $fechaVac = $this->toSqlDate($vacunaData['fecha_vacuna'] ?? null);
                        $vacunaNombre = $this->vacunaNombreCache[$vacunasId] ?? null;

                        // ✅ si no existe en referencia_vacunas => NO insertamos y lo mostramos al usuario
                        if (!isset($validVacIdSet[$vacunasId])) {
                            $this->oldVacuna++;
                            $this->addVacunaOmitida(
                                (int)($fila['excelRow'] ?? 0),
                                (string)($fila['tipo'] ?? ''),
                                (string)($fila['numero'] ?? ''),
                                (string)($fila['carnet'] ?? null),
                                (int)$afiliadoId,
                                $vacunasId,
                                $vacunaNombre,
                                $docisNorm,
                                $fechaVac,
                                "La vacuna (vacunas_id={$vacunasId}) no existe en referencia_vacunas. No se insertó."
                            );
                            continue;
                        }

                        $key = (int)$afiliadoId . '|' . $vacunasId . '|' . ($docisNorm ?? '');

                        // ✅ YA EXISTE EN BD => no insertamos y lo reportamos
                        if (isset($existingKeys[$key])) {
                            $this->oldVacuna++;
                            $this->addVacunaOmitida(
                                (int)($fila['excelRow'] ?? 0),
                                (string)($fila['tipo'] ?? ''),
                                (string)($fila['numero'] ?? ''),
                                (string)($fila['carnet'] ?? null),
                                (int)$afiliadoId,
                                $vacunasId,
                                $vacunaNombre,
                                $docisNorm,
                                $fechaVac,
                                "El afiliado ya tiene registrada esta vacuna. No se insertó."
                            );
                            continue;
                        }

                        // ✅ REPETIDA EN EL MISMO EXCEL => no insertamos y lo reportamos
                        if (isset($seenInChunk[$key])) {
                            $this->oldVacuna++;
                            $this->addVacunaOmitida(
                                (int)($fila['excelRow'] ?? 0),
                                (string)($fila['tipo'] ?? ''),
                                (string)($fila['numero'] ?? ''),
                                (string)($fila['carnet'] ?? null),
                                (int)$afiliadoId,
                                $vacunasId,
                                $vacunaNombre,
                                $docisNorm,
                                $fechaVac,
                                "Vacuna repetida dentro del mismo archivo Excel. No se insertó."
                            );
                            continue;
                        }

                        $seenInChunk[$key] = true;

                        $vacunaData['afiliado_id'] = (int)$afiliadoId;
                        $vacunaData['user_id'] = (int)$this->userId;
                        $vacunaData['batch_verifications_id'] = (int)$this->batch_verifications_id;

                        // ✅ timestamps nativos SQL Server (evita 22007)
                        $vacunaData['created_at'] = DB::raw('GETDATE()');
                        $vacunaData['updated_at'] = DB::raw('GETDATE()');

                        // ✅ fecha vacuna segura
                        if (array_key_exists('fecha_vacuna', $vacunaData)) {
                            $vacunaData['fecha_vacuna'] = $this->toSqlDate($vacunaData['fecha_vacuna']);
                        }

                        $vacunaData = $this->forceStringLots($vacunaData);

                        $vacunaData = array_intersect_key($vacunaData, $VACUNA_TEMPLATE);
                        $vacunaData = array_replace($VACUNA_TEMPLATE, $vacunaData);

                        foreach ([
                            'docis','laboratorio','lote','jeringa','lote_jeringa','diluyente','lote_diluyente',
                            'observacion','gotero','tipo_neumococo','responsable','fuen_ingresado_paiweb',
                            'motivo_noingreso','observaciones','regimen'
                        ] as $k) {
                            if ($this->isNullToken($vacunaData[$k] ?? null)) {
                                $vacunaData[$k] = null;
                                continue;
                            }
                            if (is_string($vacunaData[$k])) {
                                $v = trim($vacunaData[$k]);
                                if ($v === '') $v = null;
                                $vacunaData[$k] = $v;
                            }
                        }

                        $vacunaData = $this->sanitizeVacunaInsert($vacunaData, (int)($fila['excelRow'] ?? 0));

                        $insertVacunas[] = $vacunaData;
                        $this->newVacuna++;
                    }
                }

                if (!empty($insertVacunas)) {
                    foreach (array_chunk($insertVacunas, 1000) as $vacChunk) {
                        $db->table('vacunas')->insert($vacChunk);
                    }
                }

                if ($startedHere) $db->commit();

            } catch (\Throwable $e) {
                if ($startedHere && $db->transactionLevel() > 0) $db->rollBack();
                throw $e;
            }

        } finally {
            $this->isFlushing = false;
        }
    }

    /**
     * ✅ EXTRACTOR VACUNAS (igual que el tuyo)
     */
    private function extraerVacunasOptimizado(
        array $row,
        ?string $fechaatencion,
        $responsable,
        $fuen_ingresado_paiweb,
        $motivo_noingreso,
        $observaciones,
        $regimenVacuna
    ): array {
        $vacunas = [];
        $nowTs = $this->nowSqlDateTime();

        $cell = function(int $idx) use (&$row) {
            return $row[$idx] ?? null;
        };

        $doc = function(int $idx) use (&$row) {
            $v = $row[$idx] ?? null;
            if ($v === null) return null;
            $s = trim((string)$v);
            if ($s === '' || strtoupper($s) === 'NONE') return null;
            return $s;
        };

        $val = function(int $idx) use (&$row) {
            $v = $row[$idx] ?? null;
            if ($v === null) return null;
            if (is_string($v)) {
                $s = trim($v);
                if ($s === '' || strtoupper($s) === 'NONE') return null;
                return $s;
            }
            return $v;
        };

        $hasAny = function(array $idxs) use ($cell): bool {
            foreach ($idxs as $k) {
                $v = $cell($k);
                if ($v === null) continue;
                if (is_string($v)) {
                    $t = trim($v);
                    if ($t !== '' && strtoupper($t) !== 'NONE') return true;
                } else {
                    return true;
                }
            }
            return false;
        };

        // 👇 (tu bloque de vacunas completo, sin cambios de índices)
        $blocks = [
            [1,  range(75,80),  function() use ($doc,$val){ return ['docis'=>$doc(75),'laboratorio'=>$val(76),'lote'=>$val(77),'jeringa'=>$val(78),'lote_jeringa'=>$val(79),'diluyente'=>$val(80)]; }],
            [2,  range(81,86),  function() use ($doc,$val){ return ['docis'=>$doc(81),'lote'=>$val(82),'jeringa'=>$val(83),'lote_jeringa'=>$val(84),'lote_diluyente'=>$val(85),'observacion'=>$val(86)]; }],
            [3,  range(87,91),  function() use ($doc,$val){ return ['docis'=>$doc(87),'lote'=>$val(88),'jeringa'=>$val(89),'lote_jeringa'=>$val(90),'observacion'=>$val(91)]; }],
            [4,  range(92,96),  function() use ($doc,$val){ return ['docis'=>$doc(92),'lote'=>$val(93),'jeringa'=>$val(94),'lote_jeringa'=>$val(95),'observacion'=>$val(96)]; }],
            [5,  range(97,99),  function() use ($doc,$val){ return ['docis'=>$doc(97),'lote'=>$val(98),'gotero'=>$val(99)]; }],
            [6,  range(100,104),function() use ($doc,$val){ return ['docis'=>$doc(100),'lote'=>$val(101),'jeringa'=>$val(102),'lote_jeringa'=>$val(103),'observacion'=>$val(104)]; }],
            [7,  range(105,108),function() use ($doc,$val){ return ['docis'=>$doc(105),'lote'=>$val(106),'jeringa'=>$val(107),'lote_jeringa'=>$val(108)]; }],
            [8,  range(109,112),function() use ($doc,$val){ return ['docis'=>$doc(109),'lote'=>$val(110),'jeringa'=>$val(111),'lote_jeringa'=>$val(112)]; }],
            [9,  range(113,116),function() use ($doc,$val){ return ['docis'=>$doc(113),'lote'=>$val(114),'jeringa'=>$val(115),'lote_jeringa'=>$val(116)]; }],
            [10, range(117,120),function() use ($doc,$val){ return ['docis'=>$doc(117),'lote'=>$val(118),'jeringa'=>$val(119),'lote_jeringa'=>$val(120)]; }],
            [11, range(121,122),function() use ($doc,$val){ return ['docis'=>$doc(121),'lote'=>$val(122)]; }],
            [12, range(123,127),function() use ($doc,$val){ return ['tipo_neumococo'=>$doc(123),'docis'=>$val(124),'lote'=>$val(125),'jeringa'=>$val(126),'lote_jeringa'=>$val(127)]; }],
            [13, range(128,132),function() use ($doc,$val){ return ['docis'=>$doc(128),'lote'=>$val(129),'jeringa'=>$val(130),'lote_jeringa'=>$val(131),'lote_diluyente'=>$val(132)]; }],
            [14, range(133,137),function() use ($doc,$val){ return ['docis'=>$doc(133),'lote'=>$val(134),'jeringa'=>$val(135),'lote_jeringa'=>$val(136),'lote_diluyente'=>$val(137)]; }],
            [15, range(138,142),function() use ($doc,$val){ return ['docis'=>$doc(138),'lote'=>$val(139),'jeringa'=>$val(140),'lote_jeringa'=>$val(141),'lote_diluyente'=>$val(142)]; }],
            [16, range(143,146),function() use ($doc,$val){ return ['docis'=>$doc(143),'lote'=>$val(144),'jeringa'=>$val(145),'lote_jeringa'=>$val(146)]; }],
            [17, range(147,151),function() use ($doc,$val){ return ['docis'=>$doc(147),'lote'=>$val(148),'jeringa'=>$val(149),'lote_jeringa'=>$val(150),'lote_diluyente'=>$val(151)]; }],
            [18, range(152,155),function() use ($doc,$val){ return ['docis'=>$doc(152),'lote'=>$val(153),'jeringa'=>$val(154),'lote_jeringa'=>$val(155)]; }],
            [19, range(156,159),function() use ($doc,$val){ return ['docis'=>$doc(156),'lote'=>$val(157),'jeringa'=>$val(158),'lote_jeringa'=>$val(159)]; }],
            [20, range(160,164),function() use ($doc,$val){ return ['docis'=>$doc(160),'lote'=>$val(161),'jeringa'=>$val(162),'lote_jeringa'=>$val(163),'observacion'=>$val(164)]; }],
            [21, range(165,168),function() use ($doc,$val){ return ['docis'=>$doc(165),'lote'=>$val(166),'jeringa'=>$val(167),'lote_jeringa'=>$val(168)]; }],
            [22, range(169,174),function() use ($doc,$val){ return ['docis'=>$doc(169),'lote'=>$val(170),'jeringa'=>$val(171),'lote_jeringa'=>$val(172),'lote_diluyente'=>$val(173),'observacion'=>$val(174)]; }],
            [23, range(175,176),function() use ($val){ return ['num_frascos_utilizados'=>$val(175),'lote'=>$val(176)]; }],
            [24, range(177,181),function() use ($val){ return ['num_frascos_utilizados'=>$val(177),'lote'=>$val(178),'jeringa'=>$val(179),'lote_jeringa'=>$val(180),'observacion'=>$val(181)]; }],
            [25, range(182,185),function() use ($val){ return ['num_frascos_utilizados'=>$val(182),'lote'=>$val(183),'jeringa'=>$val(184),'lote_jeringa'=>$val(185)]; }],
            [26, range(186,189),function() use ($val){ return ['num_frascos_utilizados'=>$val(186),'lote'=>$val(187),'jeringa'=>$val(188),'lote_jeringa'=>$val(189)]; }],
            [27, range(190,194),function() use ($doc,$val){ return ['docis'=>$doc(190),'lote'=>$val(191),'jeringa'=>$val(192),'lote_jeringa'=>$val(193),'lote_diluyente'=>$val(194)]; }],
            [55, range(195,196),function() use ($doc,$val){ return ['docis'=>$doc(195),'lote'=>$val(196)]; }],
            [56, range(197,198),function() use ($doc,$val){ return ['docis'=>$doc(197),'lote'=>$val(198)]; }],
            [28, range(199,200),function() use ($doc,$val){ return ['docis'=>$doc(199),'lote'=>$val(200)]; }],
            [29, range(201,202),function() use ($doc,$val){ return ['docis'=>$doc(201),'lote'=>$val(202)]; }],
            [30, range(203,204),function() use ($doc,$val){ return ['docis'=>$doc(203),'lote'=>$val(204)]; }],
            [31, range(205,206),function() use ($doc,$val){ return ['docis'=>$doc(205),'lote'=>$val(206)]; }],
            [32, range(207,208),function() use ($doc,$val){ return ['docis'=>$doc(207),'lote'=>$val(208)]; }],
            [33, range(209,210),function() use ($doc,$val){ return ['docis'=>$doc(209),'lote'=>$val(210)]; }],
            [34, range(211,212),function() use ($doc,$val){ return ['docis'=>$doc(211),'lote'=>$val(212)]; }],
            [35, range(213,214),function() use ($doc,$val){ return ['docis'=>$doc(213),'lote'=>$val(214)]; }],
            [36, range(215,216),function() use ($doc,$val){ return ['docis'=>$doc(215),'lote'=>$val(216)]; }],
            [37, range(217,218),function() use ($doc,$val){ return ['docis'=>$doc(217),'lote'=>$val(218)]; }],
            [38, range(219,220),function() use ($doc,$val){ return ['docis'=>$doc(219),'lote'=>$val(220)]; }],
            [39, range(221,222),function() use ($doc,$val){ return ['docis'=>$doc(221),'lote'=>$val(222)]; }],
            [40, range(223,224),function() use ($doc,$val){ return ['docis'=>$doc(223),'lote'=>$val(224)]; }],
            [41, range(225,226),function() use ($doc,$val){ return ['docis'=>$doc(225),'lote'=>$val(226)]; }],
            [42, range(227,228),function() use ($doc,$val){ return ['docis'=>$doc(227),'lote'=>$val(228)]; }],
            [43, range(229,230),function() use ($doc,$val){ return ['docis'=>$doc(229),'lote'=>$val(230)]; }],
            [44, range(231,232),function() use ($doc,$val){ return ['docis'=>$doc(231),'lote'=>$val(232)]; }],
            [45, range(233,234),function() use ($doc,$val){ return ['docis'=>$doc(233),'lote'=>$val(234)]; }],
            [46, range(235,236),function() use ($doc,$val){ return ['docis'=>$doc(235),'lote'=>$val(236)]; }],
            [47, range(237,239),function() use ($doc,$val){ return ['docis'=>$doc(237),'lote'=>$val(238),'observacion'=>$val(239)]; }],
            [48, range(240,241),function() use ($val){ return ['num_frascos_utilizados'=>$val(240),'lote'=>$val(241)]; }],
            [49, range(242,244),function() use ($val){ return ['num_frascos_utilizados'=>$val(242),'lote'=>$val(243),'observacion'=>$val(244)]; }],
            [50, range(245,246),function() use ($val){ return ['num_frascos_utilizados'=>$val(245),'lote'=>$val(246)]; }],
            [51, range(247,248),function() use ($val){ return ['num_frascos_utilizados'=>$val(247),'lote'=>$val(248)]; }],
            [52, range(249,250),function() use ($doc,$val){ return ['docis'=>$doc(249),'lote'=>$val(250)]; }],
            [53, range(251,252),function() use ($doc,$val){ return ['docis'=>$doc(251),'lote'=>$val(252)]; }],
            [54, range(253,254),function() use ($doc,$val){ return ['docis'=>$doc(253),'lote'=>$val(254)]; }],
        ];

        foreach ($blocks as [$vacunasId, $idxs, $build]) {
            $idxs = array_filter($idxs, fn($x) => $x < count($row));
            if (empty($idxs)) continue;

            if (!$hasAny($idxs)) continue;

            $payload = $build();

            $vacunas[] = array_merge([
                'docis' => $payload['docis'] ?? null,
                'fecha_vacuna' => $this->toSqlDate($fechaatencion ?? null),
                'responsable' => $responsable ?? null,
                'fuen_ingresado_paiweb' => $fuen_ingresado_paiweb ?? null,
                'motivo_noingreso' => $motivo_noingreso ?? null,
                'observaciones' => $observaciones ?? null,
                'regimen' => $regimenVacuna ?? null,
                'vacunas_id' => (int)$vacunasId,
                'user_id' => (int)$this->userId,
                'batch_verifications_id' => (int)$this->batch_verifications_id,
                'created_at' => $nowTs,
                'updated_at' => $nowTs,
            ], $payload);
        }

        return $vacunas;
    }

    private function resolveFinalColumns(array $row): array
    {
        $hasValue = function ($v): bool {
            if ($v === null) return false;
            if (is_string($v)) {
                $t = trim($v);
                return $t !== '' && strtoupper($t) !== 'NONE';
            }
            return true;
        };

        // Prioriza formato nuevo.
        $maps = [
            [255, 256, 257, 258],
            [251, 252, 253, 254],
        ];

        foreach ($maps as [$iResp, $iFue, $iMot, $iObs]) {
            if (
                $hasValue($row[$iResp] ?? null) ||
                $hasValue($row[$iFue] ?? null) ||
                $hasValue($row[$iMot] ?? null) ||
                $hasValue($row[$iObs] ?? null)
            ) {
                return [
                    $this->cleanText($row[$iResp] ?? null),
                    $this->cleanText($row[$iFue] ?? null),
                    $this->cleanText($row[$iMot] ?? null),
                    $this->cleanText($row[$iObs] ?? null),
                ];
            }
        }

        return [null, null, null, null];
    }
}
