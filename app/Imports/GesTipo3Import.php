<?php

namespace App\Imports;

use App\Models\GesTipo1;
use App\Models\GesTipo3;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GesTipo3Import implements OnEachRow, WithStartRow, WithChunkReading, SkipsEmptyRows
{
    private int $batchVerificationsId;
    private int $userId;

    private array $buffer = [];
    private ?int $maxRowsPerInsert = null;

    private array $errores = [];
    private bool $rowHasErrors = false;
    private bool $errorsCapNoticeAdded = false;

    private array $seenInFile = [];
    private array $cupsSet = [];
    private array $parentCache = [];

    private int $rowsTotal = 0;
    private int $rowsCreated = 0;
    private int $rowsInvalid = 0;
    private int $rowsSkipped = 0;
    private int $rowsDuplicated = 0;

    public function __construct(int $userId, int $batchVerificationsId)
    {
        $this->userId = $userId;
        $this->batchVerificationsId = $batchVerificationsId;

        $this->cupsSet = DB::connection('sqlsrv_1')
            ->table('refcups')
            ->pluck('codigo')
            ->map(fn ($c) => mb_strtoupper(trim((string) $c), 'UTF-8'))
            ->flip()
            ->toArray();
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function getCounters(): array
    {
        return [
            'rows_total' => $this->rowsTotal,
            'rows_created' => $this->rowsCreated,
            'rows_invalid' => $this->rowsInvalid,
            'rows_skipped' => $this->rowsSkipped,
            'rows_duplicated' => $this->rowsDuplicated,
            'errors_count' => count($this->errores),
        ];
    }

    public function finalize(): void
    {
        $this->flushBuffer();
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function clean($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            // Limpia espacios no separables u ocultos frecuentes en Excel.
            $value = str_replace(
                ["\xC2\xA0", "\u{00A0}", "\u{2007}", "\u{202F}", "\u{200B}"],
                ' ',
                $value
            );
            $value = trim($value);
            $upper = mb_strtoupper($value, 'UTF-8');
            if ($value === '' || $upper === 'N/A' || $upper === 'NA' || $upper === 'NULL' || $upper === 'NONE') {
                return null;
            }
        }

        return $value;
    }

    private function addError(int $excelRow, string $field, string $message, $value = null): void
    {
        $this->rowHasErrors = true;

        if (count($this->errores) >= 5000) {
            if (!$this->errorsCapNoticeAdded) {
                $this->errores[] = 'Se alcanzo el limite de 5000 errores mostrables. Corrige el archivo y vuelve a intentar.';
                $this->errorsCapNoticeAdded = true;
            }
            return;
        }

        $msg = "Fila {$excelRow} | {$field}: {$message}";
        $value = $this->clean($value);
        if ($value !== null) {
            $msg .= ' (valor: ' . mb_substr((string) $value, 0, 120) . ')';
        }
        $this->errores[] = $msg;
    }

    private function parseDate($value, int $excelRow, string $field, bool $required = true): ?string
    {
        $value = $this->clean($value);

        if ($value === null) {
            if ($required) {
                $this->addError($excelRow, $field, 'fecha vacia');
            }
            return null;
        }

        $dt = null;

        if ($value instanceof \DateTimeInterface) {
            $dt = Carbon::instance($value);
        }

        if ($dt === null && is_numeric($value)) {
            try {
                $dt = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable $e) {
                $this->addError($excelRow, $field, 'fecha invalida', $value);
                return null;
            }
        }

        if ($dt === null) {
            $raw = (string) $value;
            foreach (['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y'] as $fmt) {
                try {
                    $dt = Carbon::createFromFormat($fmt, $raw);
                    break;
                } catch (\Throwable $e) {
                }
            }
        }

        if ($dt === null) {
            try {
                $dt = Carbon::parse((string) $value);
            } catch (\Throwable $e) {
                $this->addError($excelRow, $field, 'fecha invalida', $value);
                return null;
            }
        }

        if ((int) $dt->year < 1753 || (int) $dt->year > 9999) {
            $this->addError($excelRow, $field, 'fecha fuera de rango SQL Server', $value);
            return null;
        }

        return $dt->format('Y-m-d');
    }

    private function parseInteger(
        $value,
        int $excelRow,
        string $field,
        bool $required = false,
        ?int $min = null,
        ?int $max = null,
        bool $allowDecimal = false
    ): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            if ($required) {
                $this->addError($excelRow, $field, 'campo obligatorio');
            }
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!is_numeric($normalized)) {
            $this->addError($excelRow, $field, 'debe ser numero entero', $value);
            return null;
        }

        $floatValue = (float) $normalized;
        $rounded = (int) round($floatValue);

        if (!$allowDecimal && abs($floatValue - $rounded) > 0.0000001) {
            $this->addError($excelRow, $field, 'debe ser numero entero', $value);
            return null;
        }

        $n = $rounded;

        if ($min !== null && $n < $min) {
            $this->addError($excelRow, $field, "debe ser >= {$min}", $value);
            return null;
        }
        if ($max !== null && $n > $max) {
            $this->addError($excelRow, $field, "debe ser <= {$max}", $value);
            return null;
        }

        return $n;
    }

    private function parseDecimal($value, int $excelRow, string $field, bool $required = false, ?float $min = null, ?float $max = null): ?float
    {
        $value = $this->clean($value);
        if ($value === null) {
            if ($required) {
                $this->addError($excelRow, $field, 'campo obligatorio');
            }
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!is_numeric($normalized)) {
            $this->addError($excelRow, $field, 'debe ser numerico', $value);
            return null;
        }

        $n = (float) $normalized;

        if ($min !== null && $n < $min) {
            $this->addError($excelRow, $field, "debe ser >= {$min}", $value);
            return null;
        }
        if ($max !== null && $n > $max) {
            $this->addError($excelRow, $field, "debe ser <= {$max}", $value);
            return null;
        }

        return $n;
    }

    private function parseTipoDocumento($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Tipo identificacion', 'campo obligatorio');
            return null;
        }

        $v = mb_strtoupper(trim((string) $value), 'UTF-8');

        if ($v === 'PPT') {
            $v = 'PT';
        }
        if ($v === 'TE') {
            $v = 'CE';
        }

        $allowed = ['CC', 'TI', 'RC', 'CE', 'PA', 'AS', 'MS', 'CD', 'SC', 'PE', 'PT', 'CN', 'DE', 'SI', 'NIT', 'NUIP'];

        if (!in_array($v, $allowed, true)) {
            $this->addError($excelRow, 'Tipo identificacion', 'valor no permitido', $value);
            return null;
        }

        return $v;
    }

    private function parseBoolCode($value, int $excelRow, string $field): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $v = mb_strtoupper(trim((string) $value), 'UTF-8');
        $map = [
            '1' => 1,
            'SI' => 1,
            'S' => 1,
            'TRUE' => 1,
            '0' => 0,
            'NO' => 0,
            'N' => 0,
            'FALSE' => 0,
            '2' => 0,
            '21' => 1,
        ];

        if (!array_key_exists($v, $map)) {
            if (is_numeric($v)) {
                return (((float) $v) > 0) ? 1 : 0;
            }
            $this->addError($excelRow, $field, 'solo permite SI/NO o 1/0', $value);
            return null;
        }

        return $map[$v];
    }

    private function parseString($value, int $excelRow, string $field, bool $required = false, int $max = 255): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            if ($required) {
                $this->addError($excelRow, $field, 'campo obligatorio');
            }
            return null;
        }

        $v = trim((string) $value);
        if (mb_strlen($v) > $max) {
            $this->addError($excelRow, $field, "supera {$max} caracteres");
            return null;
        }

        return $v;
    }

    private function getParentGestanteId(string $tipoIdent, string $noId): ?int
    {
        $key = $tipoIdent . '|' . $noId;
        if (array_key_exists($key, $this->parentCache)) {
            return $this->parentCache[$key];
        }

        $id = GesTipo1::query()
            ->where('tipo_de_identificacion_de_la_usuaria', $tipoIdent)
            ->where('no_id_del_usuario', $noId)
            ->orderByDesc('id')
            ->value('id');

        $this->parentCache[$key] = $id ? (int) $id : null;

        return $this->parentCache[$key];
    }

    public function onRow(Row $row)
    {
        $excelRow = (int) $row->getIndex();
        $this->rowsTotal++;
        $this->rowHasErrors = false;

        $r = $row->toArray();

        $hasAny = false;
        foreach ($r as $v) {
            if ($this->clean($v) !== null) {
                $hasAny = true;
                break;
            }
        }

        if (!$hasAny) {
            $this->rowsSkipped++;
            return;
        }

        if (count($r) < 23) {
            $this->addError($excelRow, 'Estructura', 'el archivo no tiene las 23 columnas esperadas');
            $this->rowsInvalid++;
            return;
        }

        if (count($r) > 23) {
            foreach ($r as $idx => $val) {
                if ((int) $idx > 22 && $this->clean($val) !== null) {
                    $this->addError($excelRow, 'Estructura', 'se detectaron columnas adicionales no permitidas');
                    $this->rowsInvalid++;
                    return;
                }
            }
        }

        $tipoRegistro = $this->parseInteger($r[0] ?? null, $excelRow, 'Tipo de registro', true, 1, 9);
        if ($tipoRegistro !== null && $tipoRegistro !== 3) {
            $this->addError($excelRow, 'Tipo de registro', 'debe ser 3 para cargue tipo 3', $tipoRegistro);
        }

        $consecutivo = $this->parseInteger($r[1] ?? null, $excelRow, 'Consecutivo de registro', true, 1);
        $tipoIdent = $this->parseTipoDocumento($r[2] ?? null, $excelRow);
        $noId = $this->parseString($r[3] ?? null, $excelRow, 'No ID usuario', true, 30);
        if ($noId !== null && !preg_match('/^[A-Za-z0-9\-]+$/', $noId)) {
            $this->addError($excelRow, 'No ID usuario', 'solo permite letras, numeros y guion', $noId);
        }

        $fechaTecnologia = $this->parseDate($r[4] ?? null, $excelRow, 'Fecha tecnologia en salud', true);
        if ($fechaTecnologia !== null && Carbon::parse($fechaTecnologia)->gt(Carbon::today())) {
            $this->addError($excelRow, 'Fecha tecnologia en salud', 'no puede ser futura', $fechaTecnologia);
        }

        $cups = $this->parseString($r[5] ?? null, $excelRow, 'Codigo CUPS', true, 30);
        if ($cups !== null) {
            $cups = mb_strtoupper($cups, 'UTF-8');
            if (!isset($this->cupsSet[$cups])) {
                $this->addError($excelRow, 'Codigo CUPS', 'no existe en referencia CUPS (refcups)', $cups);
            }
        }

        $finalidad = $this->parseString($r[6] ?? null, $excelRow, 'Finalidad tecnologia en salud', false, 50);
        $riesgoGest = $this->parseInteger($r[7] ?? null, $excelRow, 'Clasificacion riesgo gestacional', false, 0, 99);
        $riesgoPree = $this->parseInteger($r[8] ?? null, $excelRow, 'Clasificacion riesgo preeclampsia', false, 0, 99);

        $asa = $this->parseBoolCode($r[9] ?? null, $excelRow, 'Suministro ASA');
        $folico = $this->parseBoolCode($r[10] ?? null, $excelRow, 'Suministro acido folico');
        $ferroso = $this->parseBoolCode($r[11] ?? null, $excelRow, 'Suministro sulfato ferroso');
        $calcio = $this->parseBoolCode($r[12] ?? null, $excelRow, 'Suministro calcio');

        $fechaAnticonceptivo = $this->parseDate($r[13] ?? null, $excelRow, 'Fecha suministro anticonceptivo post evento', false);
        $metodoAnticonceptivo = $this->parseBoolCode($r[14] ?? null, $excelRow, 'Suministro metodo anticonceptivo post evento');
        $fechaSalida = $this->parseDate($r[15] ?? null, $excelRow, 'Fecha salida aborto/parto/cesarea', false);
        $fechaTerminacion = $this->parseDate($r[16] ?? null, $excelRow, 'Fecha terminacion gestacion', false);

        $tipoTerminacion = $this->parseInteger($r[17] ?? null, $excelRow, 'Tipo terminacion gestacion', false, 0, 99);
        $pas = $this->parseInteger($r[18] ?? null, $excelRow, 'Tension arterial sistolica PAS', false, 40, 300, true);
        $pad = $this->parseInteger($r[19] ?? null, $excelRow, 'Tension arterial diastolica PAD', false, 20, 200, true);

        if ($pas !== null && $pad !== null && $pas <= $pad) {
            $this->addError($excelRow, 'Tension arterial', 'PAS debe ser mayor que PAD');
        }

        $imc = $this->parseDecimal($r[20] ?? null, $excelRow, 'Indice de masa corporal', false, 5, 80);
        $hemoglobina = $this->parseDecimal($r[21] ?? null, $excelRow, 'Resultado hemoglobina', false, 0, 30);
        $ipUterinas = $this->parseDecimal($r[22] ?? null, $excelRow, 'Indice de pulsatilidad arterias uterinas', false, 0, 20);

        $gesTipo1Id = null;
        if ($tipoIdent !== null && $noId !== null) {
            $gesTipo1Id = $this->getParentGestanteId($tipoIdent, $noId);
            if ($gesTipo1Id === null) {
                $this->addError($excelRow, 'Relacion ges_tipo2', 'no existe registro padre para tipo/no identificacion');
            }
        }

        if ($tipoIdent !== null && $noId !== null && $consecutivo !== null && $fechaTecnologia !== null && $cups !== null) {
            $dupKey = implode('|', [$tipoIdent, $noId, $consecutivo, $fechaTecnologia, $cups]);
            if (isset($this->seenInFile[$dupKey])) {
                $this->rowsDuplicated++;
                $this->addError($excelRow, 'Duplicado en archivo', 'registro repetido en el mismo Excel');
            } else {
                $this->seenInFile[$dupKey] = true;
            }

            $exists = GesTipo3::query()
                ->where('tipo_identificacion_de_la_usuaria', $tipoIdent)
                ->where('no_id_del_usuario', $noId)
                ->where('consecutivo_de_registro', $consecutivo)
                ->where('fecha_tecnologia_en_salud', $fechaTecnologia)
                ->where('codigo_cups_de_la_tecnologia_en_salud', $cups)
                ->exists();

            if ($exists) {
                $this->rowsDuplicated++;
                $this->addError($excelRow, 'Duplicado en base', 'ya existe el registro en ges_tipo3');
            }
        }

        if ($this->rowHasErrors) {
            $this->rowsInvalid++;
            return;
        }

        $data = [
            'user_id' => $this->userId,
            'ges_tipo1_id' => $gesTipo1Id,
            'tipo_de_registro' => (string) $tipoRegistro,
            'consecutivo_de_registro' => $consecutivo,
            'tipo_identificacion_de_la_usuaria' => $tipoIdent,
            'no_id_del_usuario' => $noId,
            'fecha_tecnologia_en_salud' => $fechaTecnologia,
            'codigo_cups_de_la_tecnologia_en_salud' => $cups,
            'finalidad_de_la_tecnologia_en_salud' => $finalidad,
            'clasificacion_riesgo_gestacional' => $riesgoGest,
            'clasificacion_riesgo_preeclampsia' => $riesgoPree,
            'suministro_acido_acetilsalicilico_ASA' => $asa,
            'suministro_acido_folico_en_el_control_prenatal' => $folico,
            'suministro_sulfato_ferroso_en_el_control_prenatal' => $ferroso,
            'suministro_calcio_en_el_control_prenatal' => $calcio,
            'fecha_suministro_de_anticonceptivo_post_evento_obstetrico' => $fechaAnticonceptivo,
            'suministro_metodo_anticonceptivo_post_evento_obstetrico' => $metodoAnticonceptivo,
            'fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea' => $fechaSalida,
            'fecha_de_terminacion_de_la_gestacion' => $fechaTerminacion,
            'tipo_de_terminacion_de_la_gestacion' => $tipoTerminacion,
            'tension_arterial_sistolica_PAS_mmHg' => $pas,
            'tension_arterial_diastolica_PAD_mmHg' => $pad,
            'indice_de_masa_corporal' => $imc,
            'resultado_de_la_hemoglobina' => $hemoglobina,
            'indice_de_pulsatilidad_de_arterias_uterinas' => $ipUterinas,
            'batch_verifications_id' => $this->batchVerificationsId,
            'created_at' => DB::raw('GETDATE()'),
            'updated_at' => DB::raw('GETDATE()'),
        ];

        $this->buffer[] = $data;

        if ($this->maxRowsPerInsert === null) {
            $columnsCount = count($data);
            $this->maxRowsPerInsert = max(1, intdiv(2000, max(1, $columnsCount)));
        }

        if (count($this->buffer) >= $this->maxRowsPerInsert) {
            $this->flushBuffer();
        }
    }

    private function flushBuffer(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $count = count($this->buffer);
        DB::table('ges_tipo3')->insert($this->buffer);
        $this->rowsCreated += $count;
        $this->buffer = [];
    }
}
