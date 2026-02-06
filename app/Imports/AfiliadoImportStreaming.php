<?php

namespace App\Imports;

use App\Models\batch_verifications;
use App\Models\afiliado as Afiliado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AfiliadoImportStreaming implements ToModel, WithStartRow, WithChunkReading
{
    protected array $errores = [];
    protected bool $guardar = true;

    private int $batch_verifications_id;
    private int $userId;
    private ?string $token;

    // Cache en memoria
    private array $carnetCache = [];     // "TIPO|ID" => carnet|null
    private array $afiliadoIdCache = []; // carnet => afiliado_id (0 si no existe)

    // Buffer (NO acumula todo el archivo)
    private array $bufferRows = []; // ['carnet'=>..., 'existe'=>bool, 'afiliadoData'=>array|null, 'vacunas'=>array]
    private int $bufferLimit = 250;

    // Stats
    private int $newAfil = 0;
    private int $oldAfil = 0;
    private int $newVacuna = 0;
    private int $oldVacuna = 0;

    public function __construct(int $userId, ?string $uploadToken = null)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $this->userId = $userId;
        $this->token  = $uploadToken;

        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->save();

        $this->batch_verifications_id = (int)$verificacion->id;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    private function progress(int $percent, string $message, string $step, bool $done = false, bool $ok = true, array $doneSteps = []): void
    {
        if (!$this->token) return;

        Cache::put("import_progress:{$this->token}", [
            'percent'     => $percent,
            'message'     => $message,
            'step'        => $step,
            'done'        => $done,
            'ok'          => $ok,
            'done_steps'  => $doneSteps,
        ], now()->addMinutes(30));
    }

    public function getBatchVerificationsID(): int
    {
        return $this->batch_verifications_id;
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function debeGuardar(): bool
    {
        return $this->guardar;
    }

    public function getStats(): array
    {
        return [
            'newAfil'   => $this->newAfil,
            'oldAfil'   => $this->oldAfil,
            'newVacuna' => $this->newVacuna,
            'oldVacuna' => $this->oldVacuna,
        ];
    }

    public function model(array $row)
    {
        // Evita Undefined offset
        $row = array_replace(array_fill(0, 272, null), $row);

        $clean = function ($v) {
            if ($v === null) return null;
            $v = is_string($v) ? trim($v) : $v;
            if ($v === '' || strtoupper((string)$v) === 'NONE') return null;
            return $v;
        };

        $excelDateToYmd = function ($v) {
            if ($v === null || $v === '') return null;

            if (is_string($v)) {
                $vv = trim($v);
                if ($vv === '' || strtoupper($vv) === 'NONE') return null;
                try { return Carbon::parse($vv)->format('Y-m-d'); } catch (\Throwable $e) { return null; }
            }

            try { return Date::excelToDateTimeObject($v)->format('Y-m-d'); } catch (\Throwable $e) { return null; }
        };

        $tipo_identifi   = $clean((string)($row[1] ?? null));
        $numero_identifi = $clean(isset($row[2]) ? (string)$row[2] : null);

        if (!$tipo_identifi || !$numero_identifi) {
            $this->errores[] = "Fila sin identificación (tipo o número vacío).";
            $this->guardar = false;
            return null;
        }

        // Validación mínima
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
            foreach ($validator->errors()->all() as $error) {
                Log::error("VALIDATION IMPORT: ".$error." | ".$tipo_identifi." ".$numero_identifi);
            }
            throw new ValidationException($validator);
        }

        // Fechas
        $fechaatencion    = $excelDateToYmd($row[0]);
        $fechaNacimiento  = $excelDateToYmd($row[7]);
        $fechaProbParto   = $excelDateToYmd($row[46]);
        $fechaAntecedente = $excelDateToYmd($row[48]);

        $responsable           = $clean($row[251]);
        $fuen_ingresado_paiweb = $clean($row[252]);
        $motivo_noingreso      = $clean($row[253]);
        $observaciones         = $clean($row[254]);

        // --- carnet externo con cache ---
        $cacheKey = $tipo_identifi . '|' . $numero_identifi;

        if (!array_key_exists($cacheKey, $this->carnetCache)) {
            $ext = DB::connection('sqlsrv_1')
                ->table('maestroIdentificaciones')
                ->select('numeroCarnet')
                ->where('identificacion', $numero_identifi)
                ->where('tipoIdentificacion', $tipo_identifi)
                ->first();

            $this->carnetCache[$cacheKey] = $ext->numeroCarnet ?? null;
        }

        $numero_carnet = $this->carnetCache[$cacheKey];

        if (!$numero_carnet) {
            $this->errores[] = "No se encontró afiliado en DB externa con identificación: $numero_identifi y tipo: $tipo_identifi";
            $this->guardar = false;
            return null;
        }

        // --- existe afiliado local? (cache) ---
        if (!isset($this->afiliadoIdCache[$numero_carnet])) {
            $local = Afiliado::select('id')->where('numero_carnet', $numero_carnet)->first();
            $this->afiliadoIdCache[$numero_carnet] = $local ? (int)$local->id : 0;
        }
        $afiliado_id_local = (int)$this->afiliadoIdCache[$numero_carnet];

        // --- armar afiliadoData solo si NO existe ---
        $afiliadoData = null;
        if ($afiliado_id_local === 0) {
            $afiliadoData = [
                'fecha_atencion' => $fechaatencion,
                'tipo_identificacion' => $tipo_identifi,
                'numero_identificacion' => $numero_identifi,
                'numero_carnet' => $numero_carnet,

                'primer_nombre' => $clean($row[3]),
                'segundo_nombre' => $clean($row[4]),
                'primer_apellido' => $clean($row[5]),
                'segundo_apellido' => $clean($row[6]),
                'fecha_nacimiento' => $fechaNacimiento,

                'edad_anos' => $clean($row[8]),
                'edad_meses' => $clean($row[9]),
                'edad_dias' => $clean($row[10]),
                'total_meses' => $clean($row[11]),
                'esquema_completo' => $clean($row[12]),
                'sexo' => $clean($row[13]),
                'genero' => $clean($row[14]),
                'orientacion_sexual' => $clean($row[15]),
                'edad_gestacional' => $clean($row[16]),
                'pais_nacimiento' => $clean($row[17]),
                'estatus_migratorio' => $clean($row[18]),
                'lugar_atencion_parto' => $clean($row[19]),
                'regimen' => $clean($row[20]),
                'aseguradora' => $clean($row[21]),
                'pertenencia_etnica' => $clean($row[22]),
                'desplazado' => $clean($row[23]),
                'discapacitado' => $clean($row[24]),
                'fallecido' => $clean($row[25]),
                'victima_conflicto' => $clean($row[26]),
                'estudia' => $clean($row[27]),
                'pais_residencia' => $clean($row[28]),
                'departamento_residencia' => $clean($row[29]),
                'municipio_residencia' => $clean($row[30]),
                'comuna' => $clean($row[31]),
                'area' => $clean($row[32]),
                'direccion' => $clean($row[33]),
                'telefono_fijo' => $clean($row[34]),
                'celular' => $clean($row[35]),
                'email' => $clean($row[36]),

                'autoriza_llamadas' => $clean($row[37]),
                'autoriza_correos' => $clean($row[38]),
                'contraindicacion_vacuna' => $clean($row[39]),
                'enfermedad_contraindicacion' => $clean($row[40]),
                'reaccion_biologicos' => $clean($row[41]),
                'sintomas_reaccion' => $clean($row[42]),
                'condicion_usuaria' => $clean($row[43]),
                'fecha_ultima_menstruacion' => $excelDateToYmd($row[44]),
                'semanas_gestacion' => $clean($row[45]),
                'fecha_prob_parto' => $fechaProbParto,
                'embarazos_previos' => $clean($row[47]),
                'fecha_antecedente' => $fechaAntecedente,
                'tipo_antecedente' => $clean($row[49]),
                'descripcion_antecedente' => $clean($row[50]),
                'observaciones_especiales' => $clean($row[51]),

                'madre_tipo_identificacion' => $clean($row[52]),
                'madre_identificacion' => $clean($row[53]),
                'madre_primer_nombre' => $clean($row[54]),
                'madre_segundo_nombre' => $clean($row[55]),
                'madre_primer_apellido' => $clean($row[56]),
                'madre_segundo_apellido' => $clean($row[57]),
                'madre_correo' => $clean($row[58]),
                'madre_telefono' => $clean($row[59]),
                'madre_celular' => $clean($row[60]),
                'madre_regimen' => $clean($row[61]),
                'madre_pertenencia_etnica' => $clean($row[62]),
                'madre_desplazada' => $clean($row[63]),

                'cuidador_tipo_identificacion' => $clean($row[64]),
                'cuidador_identificacion' => $clean($row[65]),
                'cuidador_primer_nombre' => $clean($row[66]),
                'cuidador_segundo_nombre' => $clean($row[67]),
                'cuidador_primer_apellido' => $clean($row[68]),
                'cuidador_segundo_apellido' => $clean($row[69]),
                'cuidador_parentesco' => $clean($row[70]),
                'cuidador_correo' => $clean($row[71]),
                'cuidador_telefono' => $clean($row[72]),
                'cuidador_celular' => $clean($row[73]),
                'esquema_vacunacion' => $clean($row[74]),

                'user_id' => $this->userId,
                'batch_verifications_id' => $this->batch_verifications_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // --- vacunas (TU extractor completo) ---
        $vacunasData = $this->extraerVacunasOptimizado(
            $row,
            $fechaatencion,
            $responsable,
            $fuen_ingresado_paiweb,
            $motivo_noingreso,
            $observaciones
        );

        // Guardar en buffer
        $this->bufferRows[] = [
            'carnet'       => $numero_carnet,
            'existe'       => ($afiliado_id_local !== 0),
            'afiliadoData' => $afiliadoData,
            'vacunas'      => $vacunasData,
        ];

        // Flush por límite
        if (count($this->bufferRows) >= $this->bufferLimit) {
            $this->flushBuffer();
        }

        return null;
    }

    public function __destruct()
    {
        try {
            $this->flushBuffer();
        } catch (\Throwable $e) {
            Log::error("ERROR flushBuffer __destruct: " . $e->getMessage());
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
        if (empty($this->bufferRows)) return;
        if (!$this->guardar) { $this->bufferRows = []; return; }

        $this->progress(25, 'Guardando afiliados…', 'afiliados', false, true, ['validacion']);

        // Copia y libera RAM
        $buffer = $this->bufferRows;
        $this->bufferRows = [];

        DB::transaction(function () use ($buffer) {

            $carnets = array_values(array_unique(array_filter(array_map(fn($x) => $x['carnet'] ?? null, $buffer))));

            // Afiliados existentes
            $afiliadosMap = Afiliado::query()
                ->select('id', 'numero_carnet')
                ->whereIn('numero_carnet', $carnets)
                ->get()
                ->keyBy('numero_carnet');

            // Insert afiliados faltantes
            $insertAfiliados = [];
            foreach ($buffer as $fila) {
                $carnet = $fila['carnet'] ?? null;
                if (!$carnet) continue;

                if (!$afiliadosMap->has($carnet) && !empty($fila['afiliadoData'])) {
                    $insertAfiliados[] = $fila['afiliadoData'];
                }
            }

            if (!empty($insertAfiliados)) {
                DB::table('afiliados')->insert($insertAfiliados);
                $this->newAfil += count($insertAfiliados);

                // Recargar map con IDs nuevos
                $afiliadosMap = Afiliado::query()
                    ->select('id', 'numero_carnet')
                    ->whereIn('numero_carnet', $carnets)
                    ->get()
                    ->keyBy('numero_carnet');
            }

            // Conteo de afiliados que ya existían
            foreach ($buffer as $fila) {
                if (!empty($fila['existe'])) $this->oldAfil++;
            }

            $afiliadoIdPorCarnet = $afiliadosMap->map(fn($a) => (int)$a->id)->all();
            $afiliadoIds = array_values(array_unique(array_filter($afiliadoIdPorCarnet)));

            $this->progress(45, 'Preparando vacunas…', 'vacunas', false, true, ['validacion','afiliados']);

            // Vacunas existentes (dedup)
            $existingKeys = [];
            if (!empty($afiliadoIds)) {
                $exist = DB::table('vacunas')
                    ->select('afiliado_id', 'vacunas_id', 'docis')
                    ->whereIn('afiliado_id', $afiliadoIds)
                    ->get();

                foreach ($exist as $e) {
                    $k = (int)$e->afiliado_id . '|' . (int)$e->vacunas_id . '|' . ($this->normalizeDocis($e->docis) ?? '');
                    $existingKeys[$k] = true;
                }
            }

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

                    // Solo columnas reales
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

            if (!empty($insertVacunas)) {
                foreach (array_chunk($insertVacunas, 1000) as $vacChunk) {
                    DB::table('vacunas')->insert($vacChunk);
                }
            }

            $this->progress(80, 'Bloque guardado correctamente…', 'vacunas', false, true, ['validacion','afiliados','vacunas']);
        });
    }

    /**
     * TU extractor completo (igual al que pegaste).
     * Rango: 75..250
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

        // Si todo el bloque 75..250 está vacío, no recorremos nada
        $allEmpty = true;
        for ($i = 75; $i <= 250; $i++) {
            if ($cellHasValue($row[$i] ?? null)) {
                $allEmpty = false;
                break;
            }
        }
        if ($allEmpty) return [];

        for ($i = 75; $i <= 250; $i++) {
            if (!$cellHasValue($row[$i] ?? null)) {
                continue;
            }

            $vacunaNombre = null;
            $docis = $laboratorio = $lote = $jeringa = $lote_jeringa = $diluyente = $lote_diluyente =
            $observacion = $gotero = $tipo_neumococo = $num_frascos_utilizados = null;

            // 1
            if ($i >= 75 && $i <= 80) {
                $vacunaNombre = 1;
                $docis = isset($row[75]) ? trim((string)$row[75]) : null;
                $laboratorio = $row[76] ?? null;
                $lote = $row[77] ?? null;
                $jeringa = $row[78] ?? null;
                $lote_jeringa = $row[79] ?? null;
                $diluyente = $row[80] ?? null;
                $i = 80;

            // 2
            } elseif ($i >= 81 && $i <= 86) {
                $vacunaNombre = 2;
                $docis = isset($row[81]) ? trim((string)$row[81]) : null;
                $lote = $row[82] ?? null;
                $jeringa = $row[83] ?? null;
                $lote_jeringa = $row[84] ?? null;
                $lote_diluyente = $row[85] ?? null;
                $observacion = $row[86] ?? null;
                $i = 86;

            // 3
            } elseif ($i >= 87 && $i <= 91) {
                $vacunaNombre = 3;
                $docis = isset($row[87]) ? trim((string)$row[87]) : null;
                $lote = $row[88] ?? null;
                $jeringa = $row[89] ?? null;
                $lote_jeringa = $row[90] ?? null;
                $observacion = $row[91] ?? null;
                $i = 91;

            // 4
            } elseif ($i >= 92 && $i <= 96) {
                $vacunaNombre = 4;
                $docis = isset($row[92]) ? trim((string)$row[92]) : null;
                $lote = $row[93] ?? null;
                $jeringa = $row[94] ?? null;
                $lote_jeringa = $row[95] ?? null;
                $observacion = $row[96] ?? null;
                $i = 96;

            // 5
            } elseif ($i >= 97 && $i <= 99) {
                $vacunaNombre = 5;
                $docis = isset($row[97]) ? trim((string)$row[97]) : null;
                $lote = $row[98] ?? null;
                $gotero = $row[99] ?? null;
                $i = 99;

            // 6
            } elseif ($i >= 100 && $i <= 104) {
                $vacunaNombre = 6;
                $docis = isset($row[100]) ? trim((string)$row[100]) : null;
                $lote = $row[101] ?? null;
                $jeringa = $row[102] ?? null;
                $lote_jeringa = $row[103] ?? null;
                $observacion = $row[104] ?? null;
                $i = 104;

            // 7
            } elseif ($i >= 105 && $i <= 108) {
                $vacunaNombre = 7;
                $docis = isset($row[105]) ? trim((string)$row[105]) : null;
                $lote = $row[106] ?? null;
                $jeringa = $row[107] ?? null;
                $lote_jeringa = $row[108] ?? null;
                $i = 108;

            // 8
            } elseif ($i >= 109 && $i <= 112) {
                $vacunaNombre = 8;
                $docis = isset($row[109]) ? trim((string)$row[109]) : null;
                $lote = $row[110] ?? null;
                $jeringa = $row[111] ?? null;
                $lote_jeringa = $row[112] ?? null;
                $i = 112;

            // 9
            } elseif ($i >= 113 && $i <= 116) {
                $vacunaNombre = 9;
                $docis = isset($row[113]) ? trim((string)$row[113]) : null;
                $lote = $row[114] ?? null;
                $jeringa = $row[115] ?? null;
                $lote_jeringa = $row[116] ?? null;
                $i = 116;

            // 10
            } elseif ($i >= 117 && $i <= 120) {
                $vacunaNombre = 10;
                $docis = isset($row[117]) ? trim((string)$row[117]) : null;
                $lote = $row[118] ?? null;
                $jeringa = $row[119] ?? null;
                $lote_jeringa = $row[120] ?? null;
                $i = 120;

            // 11
            } elseif ($i >= 121 && $i <= 122) {
                $vacunaNombre = 11;
                $docis = isset($row[121]) ? trim((string)$row[121]) : null;
                $lote = $row[122] ?? null;
                $i = 122;

            // 12
            } elseif ($i >= 123 && $i <= 127) {
                $vacunaNombre = 12;
                $tipo_neumococo = isset($row[123]) ? trim((string)$row[123]) : null;
                $docis = $row[124] ?? null;
                $lote = $row[125] ?? null;
                $jeringa = $row[126] ?? null;
                $lote_jeringa = $row[127] ?? null;
                $i = 127;

            // 13
            } elseif ($i >= 128 && $i <= 132) {
                $vacunaNombre = 13;
                $docis = isset($row[128]) ? trim((string)$row[128]) : null;
                $lote = $row[129] ?? null;
                $jeringa = $row[130] ?? null;
                $lote_jeringa = $row[131] ?? null;
                $lote_diluyente = $row[132] ?? null;
                $i = 132;

            // 14
            } elseif ($i >= 133 && $i <= 137) {
                $vacunaNombre = 14;
                $docis = isset($row[133]) ? trim((string)$row[133]) : null;
                $lote = $row[134] ?? null;
                $jeringa = $row[135] ?? null;
                $lote_jeringa = $row[136] ?? null;
                $lote_diluyente = $row[137] ?? null;
                $i = 137;

            // 15
            } elseif ($i >= 138 && $i <= 142) {
                $vacunaNombre = 15;
                $docis = isset($row[138]) ? trim((string)$row[138]) : null;
                $lote = $row[139] ?? null;
                $jeringa = $row[140] ?? null;
                $lote_jeringa = $row[141] ?? null;
                $lote_diluyente = $row[142] ?? null;
                $i = 142;

            // 16
            } elseif ($i >= 143 && $i <= 146) {
                $vacunaNombre = 16;
                $docis = isset($row[143]) ? trim((string)$row[143]) : null;
                $lote = $row[144] ?? null;
                $jeringa = $row[145] ?? null;
                $lote_jeringa = $row[146] ?? null;
                $i = 146;

            // 17
            } elseif ($i >= 147 && $i <= 151) {
                $vacunaNombre = 17;
                $docis = isset($row[147]) ? trim((string)$row[147]) : null;
                $lote = $row[148] ?? null;
                $jeringa = $row[149] ?? null;
                $lote_jeringa = $row[150] ?? null;
                $lote_diluyente = $row[151] ?? null;
                $i = 151;

            // 18
            } elseif ($i >= 152 && $i <= 155) {
                $vacunaNombre = 18;
                $docis = isset($row[152]) ? trim((string)$row[152]) : null;
                $lote = $row[153] ?? null;
                $jeringa = $row[154] ?? null;
                $lote_jeringa = $row[155] ?? null;
                $i = 155;

            // 19
            } elseif ($i >= 156 && $i <= 159) {
                $vacunaNombre = 19;
                $docis = isset($row[156]) ? trim((string)$row[156]) : null;
                $lote = $row[157] ?? null;
                $jeringa = $row[158] ?? null;
                $lote_jeringa = $row[159] ?? null;
                $i = 159;

            // 20
            } elseif ($i >= 160 && $i <= 164) {
                $vacunaNombre = 20;
                $docis = isset($row[160]) ? trim((string)$row[160]) : null;
                $lote = $row[161] ?? null;
                $jeringa = $row[162] ?? null;
                $lote_jeringa = $row[163] ?? null;
                $observacion = $row[164] ?? null;
                $i = 164;

            // 21
            } elseif ($i >= 165 && $i <= 168) {
                $vacunaNombre = 21;
                $docis = isset($row[165]) ? trim((string)$row[165]) : null;
                $lote = $row[166] ?? null;
                $jeringa = $row[167] ?? null;
                $lote_jeringa = $row[168] ?? null;
                $i = 168;

            // 22
            } elseif ($i >= 169 && $i <= 174) {
                $vacunaNombre = 22;
                $docis = isset($row[169]) ? trim((string)$row[169]) : null;
                $lote = $row[170] ?? null;
                $jeringa = $row[171] ?? null;
                $lote_jeringa = $row[172] ?? null;
                $lote_diluyente = $row[173] ?? null;
                $observacion = $row[174] ?? null;
                $i = 174;

            // 23
            } elseif ($i >= 175 && $i <= 176) {
                $vacunaNombre = 23;
                $num_frascos_utilizados = $row[175] ?? null;
                $lote = $row[176] ?? null;
                $i = 176;

            // 24
            } elseif ($i >= 177 && $i <= 181) {
                $vacunaNombre = 24;
                $num_frascos_utilizados = $row[177] ?? null;
                $lote = $row[178] ?? null;
                $jeringa = $row[179] ?? null;
                $lote_jeringa = $row[180] ?? null;
                $observacion = $row[181] ?? null;
                $i = 181;

            // 25
            } elseif ($i >= 182 && $i <= 185) {
                $vacunaNombre = 25;
                $num_frascos_utilizados = $row[182] ?? null;
                $lote = $row[183] ?? null;
                $jeringa = $row[184] ?? null;
                $lote_jeringa = $row[185] ?? null;
                $i = 185;

            // 26
            } elseif ($i >= 186 && $i <= 189) {
                $vacunaNombre = 26;
                $num_frascos_utilizados = $row[186] ?? null;
                $lote = $row[187] ?? null;
                $jeringa = $row[188] ?? null;
                $lote_jeringa = $row[189] ?? null;
                $i = 189;

            // 27
            } elseif ($i >= 190 && $i <= 194) {
                $vacunaNombre = 27;
                $docis = isset($row[190]) ? trim((string)$row[190]) : null;
                $lote = $row[191] ?? null;
                $jeringa = $row[192] ?? null;
                $lote_jeringa = $row[193] ?? null;
                $lote_diluyente = $row[194] ?? null;
                $i = 194;

            // 28
            } elseif ($i >= 195 && $i <= 196) {
                $vacunaNombre = 28;
                $docis = isset($row[195]) ? trim((string)$row[195]) : null;
                $lote = $row[196] ?? null;
                $i = 196;

            // 29
            } elseif ($i >= 197 && $i <= 198) {
                $vacunaNombre = 29;
                $docis = isset($row[197]) ? trim((string)$row[197]) : null;
                $lote = $row[198] ?? null;
                $i = 198;

            // 30
            } elseif ($i >= 199 && $i <= 200) {
                $vacunaNombre = 30;
                $docis = isset($row[199]) ? trim((string)$row[199]) : null;
                $lote = $row[200] ?? null;
                $i = 200;

            // 31
            } elseif ($i >= 201 && $i <= 202) {
                $vacunaNombre = 31;
                $docis = isset($row[201]) ? trim((string)$row[201]) : null;
                $lote = $row[202] ?? null;
                $i = 202;

            // 32
            } elseif ($i >= 203 && $i <= 204) {
                $vacunaNombre = 32;
                $docis = isset($row[203]) ? trim((string)$row[203]) : null;
                $lote = $row[204] ?? null;
                $i = 204;

            // 33
            } elseif ($i >= 205 && $i <= 206) {
                $vacunaNombre = 33;
                $docis = isset($row[205]) ? trim((string)$row[205]) : null;
                $lote = $row[206] ?? null;
                $i = 206;

            // 34
            } elseif ($i >= 207 && $i <= 208) {
                $vacunaNombre = 34;
                $docis = isset($row[207]) ? trim((string)$row[207]) : null;
                $lote = $row[208] ?? null;
                $i = 208;

            // 35
            } elseif ($i >= 209 && $i <= 210) {
                $vacunaNombre = 35;
                $docis = isset($row[209]) ? trim((string)$row[209]) : null;
                $lote = $row[210] ?? null;
                $i = 210;

            // 36
            } elseif ($i >= 211 && $i <= 212) {
                $vacunaNombre = 36;
                $docis = isset($row[211]) ? trim((string)$row[211]) : null;
                $lote = $row[212] ?? null;
                $i = 212;

            // 37
            } elseif ($i >= 213 && $i <= 214) {
                $vacunaNombre = 37;
                $docis = isset($row[213]) ? trim((string)$row[213]) : null;
                $lote = $row[214] ?? null;
                $i = 214;

            // 38
            } elseif ($i >= 215 && $i <= 216) {
                $vacunaNombre = 38;
                $docis = isset($row[215]) ? trim((string)$row[215]) : null;
                $lote = $row[216] ?? null;
                $i = 216;

            // 39
            } elseif ($i >= 217 && $i <= 218) {
                $vacunaNombre = 39;
                $docis = isset($row[217]) ? trim((string)$row[217]) : null;
                $lote = $row[218] ?? null;
                $i = 218;

            // 40
            } elseif ($i >= 219 && $i <= 220) {
                $vacunaNombre = 40;
                $docis = isset($row[219]) ? trim((string)$row[219]) : null;
                $lote = $row[220] ?? null;
                $i = 220;

            // 41
            } elseif ($i >= 221 && $i <= 222) {
                $vacunaNombre = 41;
                $docis = isset($row[221]) ? trim((string)$row[221]) : null;
                $lote = $row[222] ?? null;
                $i = 222;

            // 42
            } elseif ($i >= 223 && $i <= 224) {
                $vacunaNombre = 42;
                $docis = isset($row[223]) ? trim((string)$row[223]) : null;
                $lote = $row[224] ?? null;
                $i = 224;

            // 43
            } elseif ($i >= 225 && $i <= 226) {
                $vacunaNombre = 43;
                $docis = isset($row[225]) ? trim((string)$row[225]) : null;
                $lote = $row[226] ?? null;
                $i = 226;

            // 44
            } elseif ($i >= 227 && $i <= 228) {
                $vacunaNombre = 44;
                $docis = isset($row[227]) ? trim((string)$row[227]) : null;
                $lote = $row[228] ?? null;
                $i = 228;

            // 45
            } elseif ($i >= 229 && $i <= 230) {
                $vacunaNombre = 45;
                $docis = isset($row[229]) ? trim((string)$row[229]) : null;
                $lote = $row[230] ?? null;
                $i = 230;

            // 46
            } elseif ($i >= 231 && $i <= 232) {
                $vacunaNombre = 46;
                $docis = isset($row[231]) ? trim((string)$row[231]) : null;
                $lote = $row[232] ?? null;
                $i = 232;

            // 47
            } elseif ($i >= 233 && $i <= 235) {
                $vacunaNombre = 47;
                $docis = isset($row[233]) ? trim((string)$row[233]) : null;
                $lote = $row[234] ?? null;
                $observacion = $row[235] ?? null;
                $i = 235;

            // 48
            } elseif ($i >= 236 && $i <= 237) {
                $vacunaNombre = 48;
                $num_frascos_utilizados = $row[236] ?? null;
                $lote = $row[237] ?? null;
                $i = 237;

            // 49
            } elseif ($i >= 238 && $i <= 240) {
                $vacunaNombre = 49;
                $num_frascos_utilizados = $row[238] ?? null;
                $lote = $row[239] ?? null;
                $observacion = $row[240] ?? null;
                $i = 240;

            // 50
            } elseif ($i >= 241 && $i <= 242) {
                $vacunaNombre = 50;
                $num_frascos_utilizados = $row[241] ?? null;
                $lote = $row[242] ?? null;
                $i = 242;

            // 51
            } elseif ($i >= 243 && $i <= 244) {
                $vacunaNombre = 51;
                $num_frascos_utilizados = $row[243] ?? null;
                $lote = $row[244] ?? null;
                $i = 244;

            // 52
            } elseif ($i >= 245 && $i <= 246) {
                $vacunaNombre = 52;
                $docis = isset($row[245]) ? trim((string)$row[245]) : null;
                $lote = $row[246] ?? null;
                $i = 246;

            // 53
            } elseif ($i >= 247 && $i <= 248) {
                $vacunaNombre = 53;
                $docis = isset($row[247]) ? trim((string)$row[247]) : null;
                $lote = $row[248] ?? null;
                $i = 248;

            // 54
            } elseif ($i >= 249 && $i <= 250) {
                $vacunaNombre = 54;
                $docis = isset($row[249]) ? trim((string)$row[249]) : null;
                $lote = $row[250] ?? null;
                $i = 250;
            } else {
                continue;
            }

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
