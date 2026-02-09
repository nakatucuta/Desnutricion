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

    /** ✅ lista dedicada para NO afiliados (para mostrarle al cliente qué filas borrar) */
    protected array $noAfiliados = [];

    private int $batch_verifications_id;
    private int $userId;
    private ?string $token;

    private int $totalRows;
    private int $processedRows = 0;

    /** @var null|callable(int $pct, string $step, string $msg): void */
    private $progressFn = null;

    private array $carnetCache = [];
    private array $afiliadoIdCache = [];

    /**
     * ✅ Conexión LOCAL (tu BD PRUEBA_DESNUTRICION)
     * Importante para que el worker NO se vaya a otra BD por config cacheada.
     */
    private string $localConn = 'sqlsrv';

    /**
     * Buffer principal de filas a persistir (afiliado + vacunas)
     * Cada elemento:
     *  [
     *    'excelRow' => int,
     *    'tipo' => string,
     *    'numero' => string,
     *    'carnet' => string,
     *    'existe' => bool,
     *    'afiliadoData' => array|null,
     *    'vacunas' => array
     *  ]
     */
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

    /** cache de columnas numéricas reales de SQL Server (para evitar error IPUANA->int) */
    private ?array $afiliadosNumericCols = null;

    /** ✅ log 1 sola vez el target real del worker */
    private bool $loggedTarget = false;

    /**
     * ✅ Columnas de TEXTO donde un "0" NO tiene sentido y debe volverse NULL
     * (esto evita exactamente tu caso: apellidos/nombres con 0).
     */
    private array $textZeroNullCols = [
        // datos básicos
        'primer_nombre','segundo_nombre','primer_apellido','segundo_apellido',
        'genero','orientacion_sexual','pais_nacimiento','estatus_migratorio','lugar_atencion_parto',
        'aseguradora','pertenencia_etnica','pais_residencia','departamento_residencia','municipio_residencia',
        'comuna','area','direccion','telefono_fijo','celular','email',
        'enfermedad_contraindicacion','sintomas_reaccion','condicion_usuaria','tipo_antecedente','descripcion_antecedente',
        'observaciones_especiales',

        // madre
        'madre_primer_nombre','madre_segundo_nombre','madre_primer_apellido','madre_segundo_apellido',
        'madre_correo','madre_telefono','madre_celular','madre_regimen','madre_pertenencia_etnica',

        // cuidador
        'cuidador_primer_nombre','cuidador_segundo_nombre','cuidador_primer_apellido','cuidador_segundo_apellido',
        'cuidador_parentesco','cuidador_correo','cuidador_telefono','cuidador_celular',

        // esquema vacunación
        'esquema_vacunacion',
    ];

    /**
     * ORDEN + COMPLETITUD de columnas para INSERT MASIVO.
     * ✅ Debe incluir TODAS las columnas que vas a insertar en afiliados.
     * ✅ Cada fila insertada debe traer EXACTAMENTE estas llaves.
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
        'tipo_identificacion',
        'total_meses',
        'updated_at',
        'user_id',
        'victima_conflicto',
    ];

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

        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->save();

        $this->batch_verifications_id = (int)$verificacion->id;

        $this->currentExcelRow = $this->startRow();
    }

    public function startRow(): int { return 3; }
    public function chunkSize(): int { return 200; }
    public function batchSize(): int { return 20; }

    public function getBatchVerificationsID(): int { return $this->batch_verifications_id; }
    public function getErrores(): array { return $this->errores; }

    /** ✅ para mostrar en UI: listado limpio de NO afiliados (para que el cliente borre esas filas del Excel) */
    public function getNoAfiliados(): array { return $this->noAfiliados; }

    public function getStats(): array
    {
        return [
            'newAfil'     => $this->newAfil,
            'oldAfil'     => $this->oldAfil,
            'newVacuna'   => $this->newVacuna,
            'oldVacuna'   => $this->oldVacuna,
            'noAfiliados' => count($this->noAfiliados),
        ];
    }

    public function finalize(): void
    {
        if ($this->isFinalized) return;
        $this->isFinalized = true;

        try {
            $this->flushBuffer();
        } catch (\Throwable $e) {
            Log::error("AfiliadoImportStreaming finalize ERROR: " . $e->getMessage());
        }

        // ✅ Persistir NO afiliados + errores en un JSON para que el frontend pueda mostrarlos/descargarlos
        try {
            if ($this->token) {
                $payload = [
                    'batch_verifications_id' => $this->batch_verifications_id,
                    'stats' => $this->getStats(),
                    'no_afiliados' => $this->noAfiliados,
                    'errores' => $this->errores,
                    'generated_at' => now()->toDateTimeString(),
                ];
                $path = "imports/{$this->token}_resultado.json";
                Storage::put($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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

    /** ✅ registra NO afiliado de forma estructurada (además del error en texto) */
    private function addNoAfiliado(int $excelRow, string $tipo, string $numero, string $motivo = 'No existe en BD externa'): void
    {
        $item = [
            'fila_excel' => $excelRow,
            'tipo_identificacion' => $tipo,
            'numero_identificacion' => $numero,
            'motivo' => $motivo,
        ];
        $this->noAfiliados[] = $item;

        if (count($this->noAfiliados) > 5000) {
            $this->noAfiliados = array_slice($this->noAfiliados, 0, 5000);
        }
    }

    /**
     * ✅ true si el valor es "0" o equivalente (excel a veces manda 0, 0.0, "0 ", etc.)
     */
    private function isZeroLike($v): bool
    {
        if ($v === null) return false;

        if (is_int($v) || is_float($v)) {
            return ((float)$v === 0.0);
        }

        $s = trim((string)$v);
        if ($s === '') return false;

        $s = str_replace(',', '.', $s);
        // 0, 0.0, 00, 000.00 etc.
        return is_numeric($s) && ((float)$s === 0.0);
    }

    /**
     * ✅ Limpia texto: null, '', NONE, '?' y TAMBIÉN convierte 0-like a null
     */
    private function cleanText($v)
    {
        if ($v === null) return null;
        $v = is_string($v) ? trim($v) : $v;

        if ($v === '' || strtoupper((string)$v) === 'NONE' || $v === '?') return null;
        if ($this->isZeroLike($v)) return null;

        return is_string($v) ? trim($v) : $v;
    }

    /**
     * Limpieza general (NO convierte 0 a null, porque en algunos campos puede ser válido)
     */
    private function cleanAny($v)
    {
        if ($v === null) return null;
        $v = is_string($v) ? trim($v) : $v;
        if ($v === '' || strtoupper((string)$v) === 'NONE' || $v === '?') return null;
        return $v;
    }

    /**
     * Aplica regla "0 => null" SOLO en columnas sensibles de texto (apellidos, nombres, correos, etc.)
     */
    private function sanitizeTextZeroColumns(array $data, int $excelRow): array
    {
        foreach ($this->textZeroNullCols as $col) {
            if (!array_key_exists($col, $data)) continue;

            // Si llega "0" en un campo de texto sensible, lo limpiamos a null
            if ($this->isZeroLike($data[$col])) {
                $data[$col] = null;
            }
            // también limpiar basura tipo "NONE", "?"
            if (is_string($data[$col])) {
                $s = trim($data[$col]);
                if ($s === '' || strtoupper($s) === 'NONE' || $s === '?') {
                    $data[$col] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Normaliza (ordena + completa) un row de afiliados para insert masivo.
     * Evita que SQL Server "baraje" columnas cuando hay llaves faltantes o extras.
     */
    private function normalizeAfiliadoRow(array $row): array
    {
        $template = array_fill_keys($this->afiliadoColumns, null);
        $row = array_intersect_key($row, $template);
        return array_replace($template, $row);
    }

    private function toIntOrNull($v): ?int
    {
        if ($v === null) return null;
        if (is_string($v)) {
            $v = trim($v);
            if ($v === '' || strtoupper($v) === 'NONE' || $v === '?') return null;
            $v = str_replace(',', '.', $v);
        }
        if (!is_numeric($v)) return null;
        return (int) floor((float) $v);
    }

    /**
     * Lee una sola vez qué columnas son numéricas en SQL Server para la tabla afiliados.
     * ✅ OJO: SIEMPRE usando la conexión local (sqlsrv).
     */
    private function getAfiliadosNumericColumns(): array
    {
        if ($this->afiliadosNumericCols !== null) return $this->afiliadosNumericCols;

        try {
            $rows = DB::connection($this->localConn)->select("
                SELECT COLUMN_NAME, DATA_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo'
                  AND TABLE_NAME = 'afiliados'
                  AND DATA_TYPE IN ('int','bigint','smallint','tinyint','decimal','numeric','float','real','money','smallmoney')
            ");

            $map = [];
            foreach ($rows as $r) {
                $col = (string)$r->COLUMN_NAME;
                $type = (string)$r->DATA_TYPE;
                $map[$col] = $type;
            }

            $this->afiliadosNumericCols = $map;
            return $map;
        } catch (\Throwable $e) {
            $this->afiliadosNumericCols = [];
            return [];
        }
    }

    /**
     * Sanitiza SOLO las columnas numéricas reales de SQL Server.
     * ✅ Si llega texto (ej "IPUANA") a una columna int => se vuelve NULL y se registra error.
     */
    private function sanitizeAfiliadoInsert(array $data, int $excelRow): array
    {
        $numCols = $this->getAfiliadosNumericColumns();
        if (empty($numCols)) return $data;

        foreach ($numCols as $col => $type) {
            if (!array_key_exists($col, $data)) continue;

            $v = $data[$col];

            if ($v === null || $v === '') {
                $data[$col] = null;
                continue;
            }

            if (is_string($v)) {
                $vv = trim($v);
                if ($vv === '' || strtoupper($vv) === 'NONE' || $vv === '?') {
                    $data[$col] = null;
                    continue;
                }
                $vv = str_replace(',', '.', $vv);
                $v = $vv;
            }

            if (!is_numeric($v)) {
                $filaTxt = $excelRow > 0 ? "Fila {$excelRow}" : "Fila (chunk)";
                $this->addError("{$filaTxt}: la columna '{$col}' en afiliados es {$type} pero llegó '".mb_substr((string)$v, 0, 120)."'");
                $data[$col] = null;
                continue;
            }

            if (in_array($type, ['int','bigint','smallint','tinyint'], true)) {
                $data[$col] = (int) floor((float)$v);
            } else {
                // decimal/numeric/float: se deja como string para no perder precisión
                $data[$col] = (string)$v;
            }
        }

        return $data;
    }

    /**
     * Valida que todas las filas tengan las mismas llaves (y orden) antes del insert masivo.
     */
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

    public function model(array $row)
    {
        $row = array_replace(array_fill(0, 272, null), $row);

        $excelRow = $this->currentExcelRow++;
        $this->processedRows++;

        $excelDateToYmd = function ($v) {
            if ($v === null || $v === '') return null;

            if (is_string($v)) {
                $vv = trim($v);
                if ($vv === '' || strtoupper($vv) === 'NONE') return null;
                try { return Carbon::parse($vv)->format('Y-m-d'); } catch (\Throwable $e) { return null; }
            }

            try { return Date::excelToDateTimeObject($v)->format('Y-m-d'); } catch (\Throwable $e) { return null; }
        };

        // ✅ IDs: aquí NO queremos aplicar 0=>null automáticamente porque a veces Excel trae 0 por error,
        // pero preferimos que caiga en "identificación incompleta"
        $tipo_identifi   = $this->cleanAny((string)($row[1] ?? null));
        $numero_identifi = $this->cleanAny(isset($row[2]) ? (string)$row[2] : null);

        if (!$tipo_identifi || !$numero_identifi) {
            $this->addError("Fila {$excelRow}: identificación incompleta (tipo o número vacío).");
            $this->pushProgress('validacion', "Validando filas… (fila {$excelRow})");
            return null;
        }

        // Validación mínima (ints)
        $data = [
            'edad_anos' => $row[8],
            'edad_meses' => $row[9],
            'edad_dias' => $row[10],
            'total_meses' => $row[11],
            'edad_gestacional' => $row[16],
            'embarazos_previos' => $row[47],
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

        // Fechas
        $fechaatencion    = $excelDateToYmd($row[0]);
        $fechaNacimiento  = $excelDateToYmd($row[7]);
        $fechaProbParto   = $excelDateToYmd($row[46]);
        $fechaAntecedente = $excelDateToYmd($row[48]);

        // estos 4 son texto libre -> 0 no tiene sentido
        $responsable           = $this->cleanText($row[251]);
        $fuen_ingresado_paiweb = $this->cleanText($row[252]);
        $motivo_noingreso      = $this->cleanText($row[253]);
        $observaciones         = $this->cleanText($row[254]);

        // --- carnet externo con cache ---
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

        /** ✅ NO AFILIADO */
        if (!$numero_carnet) {
            $msg = "Fila {$excelRow}: NO es afiliado (no existe en BD externa) => {$tipo_identifi} {$numero_identifi}";
            $this->addError($msg);
            $this->addNoAfiliado($excelRow, (string)$tipo_identifi, (string)$numero_identifi, 'No existe en BD externa');
            Log::warning("IMPORT NO AFILIADO: ".$msg);
            $this->pushProgress('no_afiliado', "Detectando NO afiliados… (fila {$excelRow})");
            if ($this->stopOnNonAffiliate) throw new \RuntimeException($msg);
            return null;
        }

        // --- existe afiliado local? (cache por carnet) ---
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
            $afiliadoData = [
                'fecha_atencion' => $fechaatencion,
                'tipo_identificacion' => $tipo_identifi,
                'numero_identificacion' => $numero_identifi,
                'numero_carnet' => $numero_carnet,

                // ✅ nombres/apellidos: usar cleanText (0 => null)
                'primer_nombre' => $this->cleanText($row[3]),
                'segundo_nombre' => $this->cleanText($row[4]),
                'primer_apellido' => $this->cleanText($row[5]),
                'segundo_apellido' => $this->cleanText($row[6]),
                'fecha_nacimiento' => $fechaNacimiento,

                // ints: mantener cleanAny (no convertir 0)
                'edad_anos' => $this->cleanAny($row[8]),
                'edad_meses' => $this->cleanAny($row[9]),
                'edad_dias' => $this->cleanAny($row[10]),
                'total_meses' => $this->cleanAny($row[11]),

                // texto
                'esquema_completo' => $this->cleanText($row[12]),
                'sexo' => $this->cleanText($row[13]),
                'genero' => $this->cleanText($row[14]),
                'orientacion_sexual' => $this->cleanText($row[15]),
                'edad_gestacional' => $this->cleanAny($row[16]),
                'pais_nacimiento' => $this->cleanText($row[17]),
                'estatus_migratorio' => $this->cleanText($row[18]),
                'lugar_atencion_parto' => $this->cleanText($row[19]),
                'regimen' => $this->cleanText($row[20]),
                'aseguradora' => $this->cleanText($row[21]),
                'pertenencia_etnica' => $this->cleanText($row[22]),
                'desplazado' => $this->cleanText($row[23]),
                'discapacitado' => $this->cleanText($row[24]),
                'fallecido' => $this->cleanText($row[25]),
                'victima_conflicto' => $this->cleanText($row[26]),
                'estudia' => $this->cleanText($row[27]),
                'pais_residencia' => $this->cleanText($row[28]),
                'departamento_residencia' => $this->cleanText($row[29]),
                'municipio_residencia' => $this->cleanText($row[30]),
                'comuna' => $this->cleanText($row[31]),
                'area' => $this->cleanText($row[32]),
                'direccion' => $this->cleanText($row[33]),
                'telefono_fijo' => $this->cleanText($row[34]),
                'celular' => $this->cleanText($row[35]),
                'email' => $this->cleanText($row[36]),

                'autoriza_llamadas' => $this->cleanText($row[37]),
                'autoriza_correos' => $this->cleanText($row[38]),
                'contraindicacion_vacuna' => $this->cleanText($row[39]),
                'enfermedad_contraindicacion' => $this->cleanText($row[40]),
                'reaccion_biologicos' => $this->cleanText($row[41]),
                'sintomas_reaccion' => $this->cleanText($row[42]),
                'condicion_usuaria' => $this->cleanText($row[43]),
                'fecha_ultima_menstruacion' => $excelDateToYmd($row[44]),
                'semanas_gestacion' => $this->toIntOrNull($row[45] ?? null),
                'fecha_prob_parto' => $fechaProbParto,
                'embarazos_previos' => $this->cleanAny($row[47]),
                'fecha_antecedente' => $fechaAntecedente,
                'tipo_antecedente' => $this->cleanText($row[49]),
                'descripcion_antecedente' => $this->cleanText($row[50]),
                'observaciones_especiales' => $this->cleanText($row[51]),

                'madre_tipo_identificacion' => $this->cleanText($row[52]),
                'madre_identificacion' => $this->cleanAny($row[53]),
                'madre_primer_nombre' => $this->cleanText($row[54]),
                'madre_segundo_nombre' => $this->cleanText($row[55]),
                'madre_primer_apellido' => $this->cleanText($row[56]),
                'madre_segundo_apellido' => $this->cleanText($row[57]),
                'madre_correo' => $this->cleanText($row[58]),
                'madre_telefono' => $this->cleanText($row[59]),
                'madre_celular' => $this->cleanText($row[60]),
                'madre_regimen' => $this->cleanText($row[61]),
                'madre_pertenencia_etnica' => $this->cleanText($row[62]),
                'madre_desplazada' => $this->cleanText($row[63]),

                'cuidador_tipo_identificacion' => $this->cleanText($row[64]),
                'cuidador_identificacion' => $this->cleanAny($row[65]),
                'cuidador_primer_nombre' => $this->cleanText($row[66]),
                'cuidador_segundo_nombre' => $this->cleanText($row[67]),
                'cuidador_primer_apellido' => $this->cleanText($row[68]),
                'cuidador_segundo_apellido' => $this->cleanText($row[69]),
                'cuidador_parentesco' => $this->cleanText($row[70]),
                'cuidador_correo' => $this->cleanText($row[71]),
                'cuidador_telefono' => $this->cleanText($row[72]),
                'cuidador_celular' => $this->cleanText($row[73]),
                'esquema_vacunacion' => $this->cleanText($row[74]),

                'user_id' => $this->userId,
                'batch_verifications_id' => $this->batch_verifications_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ✅ 0 => null SOLO en columnas de texto sensibles (apellidos/nombres/correos/dir/etc.)
            $afiliadoData = $this->sanitizeTextZeroColumns($afiliadoData, $excelRow);

            // 1) sanea numéricos según tipos reales
            $afiliadoData = $this->sanitizeAfiliadoInsert($afiliadoData, $excelRow);

            // 2) ordena/completa para insert masivo (ANTI-CORRIMIENTO)
            $afiliadoData = $this->normalizeAfiliadoRow($afiliadoData);
        }

        $vacunasData = $this->extraerVacunasOptimizado(
            $row,
            $fechaatencion,
            $responsable,
            $fuen_ingresado_paiweb,
            $motivo_noingreso,
            $observaciones
        );

        $this->bufferRows[] = [
            'excelRow'    => $excelRow,
            'tipo'        => (string)$tipo_identifi,
            'numero'      => (string)$numero_identifi,
            'carnet'      => $numero_carnet,
            'existe'      => ($afiliado_id_local !== 0),
            'afiliadoData'=> $afiliadoData, // null si ya existe
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

    /**
     * ✅ GUARDA EL BUFFER COMPLETO (afiliados + vacunas)
     * CAMBIOS CLAVE:
     *  - TODO lo local va por DB::connection('sqlsrv')
     *  - transactionLevel/begin/commit/rollback son por esa conexión
     *  - log 1 sola vez DB_NAME y tipos de columnas (para confirmar que es la tabla real)
     */
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

            // ✅ LOG 1 vez: confirma BD real del worker
            if (!$this->loggedTarget) {
                $this->loggedTarget = true;
                try {
                    $info = $db->selectOne("SELECT DB_NAME() AS db, @@SERVERNAME AS server");
                    Log::info('IMPORT TARGET SQLSRV', (array)$info);

                    $types = $db->select("
                        SELECT c.name AS col, t.name AS type_name, c.max_length
                        FROM sys.columns c
                        JOIN sys.types t ON c.user_type_id = t.user_type_id
                        WHERE c.object_id = OBJECT_ID('dbo.afiliados')
                          AND c.name IN ('primer_apellido','segundo_apellido','madre_primer_apellido','madre_segundo_apellido','cuidador_primer_apellido','cuidador_segundo_apellido')
                    ");
                    Log::info('AFILIADOS COLUMN TYPES', ['types' => $types]);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo loguear target SQLSRV: '.$e->getMessage());
                }
            }

            // Evita transacciones anidadas
            $startedHere = false;
            if ($db->transactionLevel() === 0) {
                $db->beginTransaction();
                $startedHere = true;
            }

            try {
                // 1) Carnets en el bloque
                $carnets = array_values(array_unique(array_filter(array_map(
                    fn($x) => $x['carnet'] ?? null,
                    $buffer
                ))));

                // 2) Mapa afiliados existentes en DB (por carnet)
                $afRows = $db->table('afiliados')
                    ->select('id', 'numero_carnet')
                    ->whereIn('numero_carnet', $carnets)
                    ->get();

                $afiliadosMap = collect($afRows)->keyBy('numero_carnet');

                // 3) Construir insert de afiliados (solo nuevos)
                $insertAfiliados = [];
                foreach ($buffer as $fila) {
                    $row = $fila['afiliadoData'] ?? null;
                    if (empty($row) || !is_array($row)) continue;

                    // ✅ blindaje total ANTES de insert
                    $row = $this->sanitizeTextZeroColumns($row, (int)($fila['excelRow'] ?? 0));
                    $row = $this->sanitizeAfiliadoInsert($row, (int)($fila['excelRow'] ?? 0));
                    $row = $this->normalizeAfiliadoRow($row);

                    $insertAfiliados[] = $row;
                }

                // 4) Insert afiliados (en chunks) ✅ SIEMPRE con sqlsrv
                if (!empty($insertAfiliados)) {
                    $this->hardValidateSameKeys($insertAfiliados);

                    foreach (array_chunk($insertAfiliados, 20) as $afilChunk) {
                        $db->table('afiliados')->insert($afilChunk);
                    }

                    $this->newAfil += count($insertAfiliados);

                    // refrescar mapa afiliados del bloque para obtener IDs de los nuevos
                    $afRows = $db->table('afiliados')
                        ->select('id', 'numero_carnet')
                        ->whereIn('numero_carnet', $carnets)
                        ->get();

                    $afiliadosMap = collect($afRows)->keyBy('numero_carnet');
                }

                // 5) Contar afiliados ya existentes (en el archivo/bloque)
                foreach ($buffer as $fila) {
                    if (!empty($fila['existe'])) $this->oldAfil++;
                }

                // 6) Carnet -> afiliado_id
                $afiliadoIdPorCarnet = $afiliadosMap->map(fn($a) => (int)$a->id)->all();
                $afiliadoIds = array_values(array_unique(array_filter($afiliadoIdPorCarnet)));

                // 7) Cargar llaves existentes de vacunas para evitar duplicados
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

                // 8) Construir insert vacunas (dedupe)
                $insertVacunas = [];
                $seenInChunk = [];

                foreach ($buffer as $fila) {
                    $carnet = $fila['carnet'] ?? null;
                    if (!$carnet) continue;

                    $afiliadoId = $afiliadoIdPorCarnet[$carnet] ?? null;
                    if (!$afiliadoId) continue;

                    $vacunas = $fila['vacunas'] ?? [];
                    if (empty($vacunas)) continue;

                    foreach ($vacunas as $vacunaData) {
                        $vacunasId = $vacunaData['vacunas_id'] ?? null;
                        if (!$vacunasId) continue;

                        $docisNorm = $this->normalizeDocis($vacunaData['docis'] ?? null);
                        $key = (int)$afiliadoId . '|' . (int)$vacunasId . '|' . ($docisNorm ?? '');

                        if (isset($existingKeys[$key]) || isset($seenInChunk[$key])) {
                            $this->oldVacuna++;
                            continue;
                        }

                        $seenInChunk[$key] = true;

                        $vacunaData['afiliado_id'] = $afiliadoId;
                        $vacunaData['user_id'] = $this->userId;
                        $vacunaData['batch_verifications_id'] = $this->batch_verifications_id;
                        $vacunaData['created_at'] = $vacunaData['created_at'] ?? now();
                        $vacunaData['updated_at'] = $vacunaData['updated_at'] ?? now();

                        $vacunaData = $this->forceStringLots($vacunaData);

                        $allowed = [
                            'docis','laboratorio','lote','jeringa','lote_jeringa','diluyente','lote_diluyente',
                            'observacion','gotero','tipo_neumococo','num_frascos_utilizados','fecha_vacuna',
                            'responsable','fuen_ingresado_paiweb','motivo_noingreso','observaciones',
                            'batch_verifications_id','afiliado_id','vacunas_id','user_id','created_at','updated_at',
                        ];
                        $vacunaData = array_intersect_key($vacunaData, array_flip($allowed));

                        $insertVacunas[] = $vacunaData;
                        $this->newVacuna++;
                    }
                }

                // 9) Insert vacunas en chunks ✅ SIEMPRE con sqlsrv
                if (!empty($insertVacunas)) {
                    foreach (array_chunk($insertVacunas, 1000) as $vacChunk) {
                        $db->table('vacunas')->insert($vacChunk);
                    }
                }

                if ($startedHere) $db->commit();
            } catch (\Throwable $e) {
                if ($startedHere && $db->transactionLevel() > 0) {
                    $db->rollBack();
                }
                throw $e;
            }
        } finally {
            $this->isFlushing = false;
        }
    }

    /**
     * TU extractor (sin cambios)
     */
    private function extraerVacunasOptimizado(
        array $row,
        ?string $fechaatencion,
        $responsable,
        $fuen_ingresado_paiweb,
        $motivo_noingreso,
        $observaciones
    ): array {
        $vacunas = [];

        $cellHasValue = function ($v) {
            if ($v === null) return false;
            if (is_string($v)) return trim($v) !== '' && strtoupper(trim($v)) !== 'NONE';
            return true;
        };

        $allEmpty = true;
        for ($i = 75; $i <= 250; $i++) {
            if ($cellHasValue($row[$i] ?? null)) { $allEmpty = false; break; }
        }
        if ($allEmpty) return [];

        // 👇 Dejo TODO tu bloque igual (tal cual lo pegaste)
        for ($i = 75; $i <= 250; $i++) {
            if (!$cellHasValue($row[$i] ?? null)) continue;

            $vacunaNombre = null;
            $docis = $laboratorio = $lote = $jeringa = $lote_jeringa = $diluyente = $lote_diluyente =
            $observacion = $gotero = $tipo_neumococo = $num_frascos_utilizados = null;

            if ($i >= 75 && $i <= 80) { $vacunaNombre = 1; $docis = isset($row[75]) ? trim((string)$row[75]) : null; $laboratorio=$row[76]??null; $lote=$row[77]??null; $jeringa=$row[78]??null; $lote_jeringa=$row[79]??null; $diluyente=$row[80]??null; $i=80; }
            elseif ($i >= 81 && $i <= 86) { $vacunaNombre = 2; $docis = isset($row[81]) ? trim((string)$row[81]) : null; $lote=$row[82]??null; $jeringa=$row[83]??null; $lote_jeringa=$row[84]??null; $lote_diluyente=$row[85]??null; $observacion=$row[86]??null; $i=86; }
            elseif ($i >= 87 && $i <= 91) { $vacunaNombre = 3; $docis = isset($row[87]) ? trim((string)$row[87]) : null; $lote=$row[88]??null; $jeringa=$row[89]??null; $lote_jeringa=$row[90]??null; $observacion=$row[91]??null; $i=91; }
            elseif ($i >= 92 && $i <= 96) { $vacunaNombre = 4; $docis = isset($row[92]) ? trim((string)$row[92]) : null; $lote=$row[93]??null; $jeringa=$row[94]??null; $lote_jeringa=$row[95]??null; $observacion=$row[96]??null; $i=96; }
            elseif ($i >= 97 && $i <= 99) { $vacunaNombre = 5; $docis = isset($row[97]) ? trim((string)$row[97]) : null; $lote=$row[98]??null; $gotero=$row[99]??null; $i=99; }
            elseif ($i >= 100 && $i <= 104) { $vacunaNombre = 6; $docis = isset($row[100]) ? trim((string)$row[100]) : null; $lote=$row[101]??null; $jeringa=$row[102]??null; $lote_jeringa=$row[103]??null; $observacion=$row[104]??null; $i=104; }
            elseif ($i >= 105 && $i <= 108) { $vacunaNombre = 7; $docis = isset($row[105]) ? trim((string)$row[105]) : null; $lote=$row[106]??null; $jeringa=$row[107]??null; $lote_jeringa=$row[108]??null; $i=108; }
            elseif ($i >= 109 && $i <= 112) { $vacunaNombre = 8; $docis = isset($row[109]) ? trim((string)$row[109]) : null; $lote=$row[110]??null; $jeringa=$row[111]??null; $lote_jeringa=$row[112]??null; $i=112; }
            elseif ($i >= 113 && $i <= 116) { $vacunaNombre = 9; $docis = isset($row[113]) ? trim((string)$row[113]) : null; $lote=$row[114]??null; $jeringa=$row[115]??null; $lote_jeringa=$row[116]??null; $i=116; }
            elseif ($i >= 117 && $i <= 120) { $vacunaNombre = 10; $docis = isset($row[117]) ? trim((string)$row[117]) : null; $lote=$row[118]??null; $jeringa=$row[119]??null; $lote_jeringa=$row[120]??null; $i=120; }
            elseif ($i >= 121 && $i <= 122) { $vacunaNombre = 11; $docis = isset($row[121]) ? trim((string)$row[121]) : null; $lote=$row[122]??null; $i=122; }
            elseif ($i >= 123 && $i <= 127) { $vacunaNombre = 12; $tipo_neumococo = isset($row[123]) ? trim((string)$row[123]) : null; $docis=$row[124]??null; $lote=$row[125]??null; $jeringa=$row[126]??null; $lote_jeringa=$row[127]??null; $i=127; }
            elseif ($i >= 128 && $i <= 132) { $vacunaNombre = 13; $docis = isset($row[128]) ? trim((string)$row[128]) : null; $lote=$row[129]??null; $jeringa=$row[130]??null; $lote_jeringa=$row[131]??null; $lote_diluyente=$row[132]??null; $i=132; }
            elseif ($i >= 133 && $i <= 137) { $vacunaNombre = 14; $docis = isset($row[133]) ? trim((string)$row[133]) : null; $lote=$row[134]??null; $jeringa=$row[135]??null; $lote_jeringa=$row[136]??null; $lote_diluyente=$row[137]??null; $i=137; }
            elseif ($i >= 138 && $i <= 142) { $vacunaNombre = 15; $docis = isset($row[138]) ? trim((string)$row[138]) : null; $lote=$row[139]??null; $jeringa=$row[140]??null; $lote_jeringa=$row[141]??null; $lote_diluyente=$row[142]??null; $i=142; }
            elseif ($i >= 143 && $i <= 146) { $vacunaNombre = 16; $docis = isset($row[143]) ? trim((string)$row[143]) : null; $lote=$row[144]??null; $jeringa=$row[145]??null; $lote_jeringa=$row[146]??null; $i=146; }
            elseif ($i >= 147 && $i <= 151) { $vacunaNombre = 17; $docis = isset($row[147]) ? trim((string)$row[147]) : null; $lote=$row[148]??null; $jeringa=$row[149]??null; $lote_jeringa=$row[150]??null; $lote_diluyente=$row[151]??null; $i=151; }
            elseif ($i >= 152 && $i <= 155) { $vacunaNombre = 18; $docis = isset($row[152]) ? trim((string)$row[152]) : null; $lote=$row[153]??null; $jeringa=$row[154]??null; $lote_jeringa=$row[155]??null; $i=155; }
            elseif ($i >= 156 && $i <= 159) { $vacunaNombre = 19; $docis = isset($row[156]) ? trim((string)$row[156]) : null; $lote=$row[157]??null; $jeringa=$row[158]??null; $lote_jeringa=$row[159]??null; $i=159; }
            elseif ($i >= 160 && $i <= 164) { $vacunaNombre = 20; $docis = isset($row[160]) ? trim((string)$row[160]) : null; $lote=$row[161]??null; $jeringa=$row[162]??null; $lote_jeringa=$row[163]??null; $observacion=$row[164]??null; $i=164; }
            elseif ($i >= 165 && $i <= 168) { $vacunaNombre = 21; $docis = isset($row[165]) ? trim((string)$row[165]) : null; $lote=$row[166]??null; $jeringa=$row[167]??null; $lote_jeringa=$row[168]??null; $i=168; }
            elseif ($i >= 169 && $i <= 174) { $vacunaNombre = 22; $docis = isset($row[169]) ? trim((string)$row[169]) : null; $lote=$row[170]??null; $jeringa=$row[171]??null; $lote_jeringa=$row[172]??null; $lote_diluyente=$row[173]??null; $observacion=$row[174]??null; $i=174; }
            elseif ($i >= 175 && $i <= 176) { $vacunaNombre = 23; $num_frascos_utilizados=$row[175]??null; $lote=$row[176]??null; $i=176; }
            elseif ($i >= 177 && $i <= 181) { $vacunaNombre = 24; $num_frascos_utilizados=$row[177]??null; $lote=$row[178]??null; $jeringa=$row[179]??null; $lote_jeringa=$row[180]??null; $observacion=$row[181]??null; $i=181; }
            elseif ($i >= 182 && $i <= 185) { $vacunaNombre = 25; $num_frascos_utilizados=$row[182]??null; $lote=$row[183]??null; $jeringa=$row[184]??null; $lote_jeringa=$row[185]??null; $i=185; }
            elseif ($i >= 186 && $i <= 189) { $vacunaNombre = 26; $num_frascos_utilizados=$row[186]??null; $lote=$row[187]??null; $jeringa=$row[188]??null; $lote_jeringa=$row[189]??null; $i=189; }
            elseif ($i >= 190 && $i <= 194) { $vacunaNombre = 27; $docis = isset($row[190]) ? trim((string)$row[190]) : null; $lote=$row[191]??null; $jeringa=$row[192]??null; $lote_jeringa=$row[193]??null; $lote_diluyente=$row[194]??null; $i=194; }
            elseif ($i >= 195 && $i <= 196) { $vacunaNombre = 28; $docis = isset($row[195]) ? trim((string)$row[195]) : null; $lote=$row[196]??null; $i=196; }
            elseif ($i >= 197 && $i <= 198) { $vacunaNombre = 29; $docis = isset($row[197]) ? trim((string)$row[197]) : null; $lote=$row[198]??null; $i=198; }
            elseif ($i >= 199 && $i <= 200) { $vacunaNombre = 30; $docis = isset($row[199]) ? trim((string)$row[199]) : null; $lote=$row[200]??null; $i=200; }
            elseif ($i >= 201 && $i <= 202) { $vacunaNombre = 31; $docis = isset($row[201]) ? trim((string)$row[201]) : null; $lote=$row[202]??null; $i=202; }
            elseif ($i >= 203 && $i <= 204) { $vacunaNombre = 32; $docis = isset($row[203]) ? trim((string)$row[203]) : null; $lote=$row[204]??null; $i=204; }
            elseif ($i >= 205 && $i <= 206) { $vacunaNombre = 33; $docis = isset($row[205]) ? trim((string)$row[205]) : null; $lote=$row[206]??null; $i=206; }
            elseif ($i >= 207 && $i <= 208) { $vacunaNombre = 34; $docis = isset($row[207]) ? trim((string)$row[207]) : null; $lote=$row[208]??null; $i=208; }
            elseif ($i >= 209 && $i <= 210) { $vacunaNombre = 35; $docis = isset($row[209]) ? trim((string)$row[209]) : null; $lote=$row[210]??null; $i=210; }
            elseif ($i >= 211 && $i <= 212) { $vacunaNombre = 36; $docis = isset($row[211]) ? trim((string)$row[211]) : null; $lote=$row[212]??null; $i=212; }
            elseif ($i >= 213 && $i <= 214) { $vacunaNombre = 37; $docis = isset($row[213]) ? trim((string)$row[213]) : null; $lote=$row[214]??null; $i=214; }
            elseif ($i >= 215 && $i <= 216) { $vacunaNombre = 38; $docis = isset($row[215]) ? trim((string)$row[215]) : null; $lote=$row[216]??null; $i=216; }
            elseif ($i >= 217 && $i <= 218) { $vacunaNombre = 39; $docis = isset($row[217]) ? trim((string)$row[217]) : null; $lote=$row[218]??null; $i=218; }
            elseif ($i >= 219 && $i <= 220) { $vacunaNombre = 40; $docis = isset($row[219]) ? trim((string)$row[219]) : null; $lote=$row[220]??null; $i=220; }
            elseif ($i >= 221 && $i <= 222) { $vacunaNombre = 41; $docis = isset($row[221]) ? trim((string)$row[221]) : null; $lote=$row[222]??null; $i=222; }
            elseif ($i >= 223 && $i <= 224) { $vacunaNombre = 42; $docis = isset($row[223]) ? trim((string)$row[223]) : null; $lote=$row[224]??null; $i=224; }
            elseif ($i >= 225 && $i <= 226) { $vacunaNombre = 43; $docis = isset($row[225]) ? trim((string)$row[225]) : null; $lote=$row[226]??null; $i=226; }
            elseif ($i >= 227 && $i <= 228) { $vacunaNombre = 44; $docis = isset($row[227]) ? trim((string)$row[227]) : null; $lote=$row[228]??null; $i=228; }
            elseif ($i >= 229 && $i <= 230) { $vacunaNombre = 45; $docis = isset($row[229]) ? trim((string)$row[229]) : null; $lote=$row[230]??null; $i=230; }
            elseif ($i >= 231 && $i <= 232) { $vacunaNombre = 46; $docis = isset($row[231]) ? trim((string)$row[231]) : null; $lote=$row[232]??null; $i=232; }
            elseif ($i >= 233 && $i <= 235) { $vacunaNombre = 47; $docis = isset($row[233]) ? trim((string)$row[233]) : null; $lote=$row[234]??null; $observacion=$row[235]??null; $i=235; }
            elseif ($i >= 236 && $i <= 237) { $vacunaNombre = 48; $num_frascos_utilizados=$row[236]??null; $lote=$row[237]??null; $i=237; }
            elseif ($i >= 238 && $i <= 240) { $vacunaNombre = 49; $num_frascos_utilizados=$row[238]??null; $lote=$row[239]??null; $observacion=$row[240]??null; $i=240; }
            elseif ($i >= 241 && $i <= 242) { $vacunaNombre = 50; $num_frascos_utilizados=$row[241]??null; $lote=$row[242]??null; $i=242; }
            elseif ($i >= 243 && $i <= 244) { $vacunaNombre = 51; $num_frascos_utilizados=$row[243]??null; $lote=$row[244]??null; $i=244; }
            elseif ($i >= 245 && $i <= 246) { $vacunaNombre = 52; $docis = isset($row[245]) ? trim((string)$row[245]) : null; $lote=$row[246]??null; $i=246; }
            elseif ($i >= 247 && $i <= 248) { $vacunaNombre = 53; $docis = isset($row[247]) ? trim((string)$row[247]) : null; $lote=$row[248]??null; $i=248; }
            elseif ($i >= 249 && $i <= 250) { $vacunaNombre = 54; $docis = isset($row[249]) ? trim((string)$row[249]) : null; $lote=$row[250]??null; $i=250; }
            else { continue; }

            if ($vacunaNombre) {
                $vacunas[] = [
                    'docis' => $docis ?? null,
                    'laboratorio' => $laboratorio ?? null,
                    'lote' => $lote ?? null,
                    'jeringa' => $jeringa ?? null,
                    'lote_jeringa' => $lote_jeringa ?? null,
                    'diluyente' => $diluyente ?? null,
                    'lote_diluyente' => $lote_diluyente ?? null,
                    'observacion' => $observacion ?? null,
                    'gotero' => $gotero ?? null,
                    'num_frascos_utilizados' => $num_frascos_utilizados ?? null,
                    'tipo_neumococo' => $tipo_neumococo ?? null,
                    'fecha_vacuna' => $fechaatencion ?? null,

                    'responsable' => $responsable ?? null,
                    'fuen_ingresado_paiweb' => $fuen_ingresado_paiweb ?? null,
                    'motivo_noingreso' => $motivo_noingreso ?? null,
                    'observaciones' => $observaciones ?? null,

                    'vacunas_id' => $vacunaNombre,
                    'user_id' => $this->userId,
                    'batch_verifications_id' => $this->batch_verifications_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $vacunas;
    }
}
