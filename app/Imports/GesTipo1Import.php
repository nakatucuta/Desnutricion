<?php

namespace App\Imports;

use App\Models\GesTipo1;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GesTipo1Import implements OnEachRow, WithStartRow, WithChunkReading, SkipsEmptyRows
{
    private int $batchVerificationsId;
    private int $userId;

    private array $buffer = [];
    private ?int $maxRowsPerInsert = null;
    private array $carnetCache = [];
    private array $seenInFile = [];
    private array $errores = [];
    private bool $rowHasErrors = false;
    private bool $errorsCapNoticeAdded = false;

    private int $rowsTotal = 0;
    private int $rowsCreated = 0;
    private int $rowsInvalid = 0;
    private int $rowsSkipped = 0;
    private int $rowsDuplicated = 0;

    public function __construct(int $userId, int $batchVerificationsId)
    {
        $this->userId = $userId;
        $this->batchVerificationsId = $batchVerificationsId;
    }

    public function getBatchVerificationsId(): int
    {
        return $this->batchVerificationsId;
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
        // La fila debe marcarse invalida aunque no se pueda anexar mas mensajes al arreglo.
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

    private function parseDate($value, int $excelRow, string $field): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, $field, 'fecha vacia');
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

    private function parseInteger($value, int $excelRow, string $field, bool $required = false, ?int $min = null, ?int $max = null): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            if ($required) {
                $this->addError($excelRow, $field, 'campo obligatorio');
            }
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!preg_match('/^-?\d+(\.0+)?$/', $normalized)) {
            $this->addError($excelRow, $field, 'debe ser numero entero', $value);
            return null;
        }

        $n = (int) round((float) $normalized);

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

    private function parseTinyCode($value, int $excelRow, string $field): ?int
    {
        return $this->parseInteger($value, $excelRow, $field, true, 0, 2);
    }

    private function parseZona($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Zona territorial de residencia', 'campo obligatorio');
            return null;
        }

        $v = mb_strtoupper(trim((string) $value), 'UTF-8');
        $map = [
            'URBANA' => 'U',
            'U' => 'U',
            'RURAL' => 'R',
            'R' => 'R',
        ];

        if (!isset($map[$v])) {
            $this->addError($excelRow, 'Zona territorial de residencia', 'solo permite U/R o Urbana/Rural', $value);
            return null;
        }

        return $map[$v];
    }

    private function parseTipoDocumento($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Tipo identificacion', 'campo obligatorio');
            return null;
        }

        $v = mb_strtoupper(trim((string) $value), 'UTF-8');
        // Normalizaciones comunes en fuentes de salud/aseguramiento.
        if ($v === 'PPT') {
            $v = 'PT';
        }
        if ($v === 'TE') {
            $v = 'CE';
        }

        // Catalogo amplio usado en reportes de salud en Colombia (RIPS/maestro personas).
        $allowed = ['CC', 'TI', 'RC', 'CE', 'PA', 'AS', 'MS', 'CD', 'SC', 'PE', 'PT', 'CN', 'DE', 'SI', 'NIT', 'NUIP'];

        if (!in_array($v, $allowed, true)) {
            $this->addError($excelRow, 'Tipo identificacion', 'valor no permitido', $value);
            return null;
        }

        return $v;
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

    private function getNumeroCarnet(string $tipoIdent, string $noId): ?string
    {
        $key = $tipoIdent . '|' . $noId;
        if (array_key_exists($key, $this->carnetCache)) {
            return $this->carnetCache[$key];
        }

        $carnet = DB::connection('sqlsrv_1')
            ->table('maestroIdentificaciones')
            ->where('identificacion', $noId)
            ->where('tipoIdentificacion', $tipoIdent)
            ->value('numeroCarnet');

        $this->carnetCache[$key] = $carnet ? (string) $carnet : null;

        return $this->carnetCache[$key];
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

        if (count($r) < 31) {
            $this->addError($excelRow, 'Estructura', 'el archivo no tiene las 31 columnas esperadas');
            $this->rowsInvalid++;
            return;
        }

        if (count($r) > 31) {
            foreach ($r as $idx => $val) {
                if ((int) $idx > 30 && $this->clean($val) !== null) {
                    $this->addError($excelRow, 'Estructura', 'se detectaron columnas adicionales no permitidas');
                    $this->rowsInvalid++;
                    return;
                }
            }
        }

        $tipoRegistro = $this->parseInteger($r[0] ?? null, $excelRow, 'Tipo de registro', true, 1, 9);
        if ($tipoRegistro !== null && $tipoRegistro !== 2) {
            $this->addError($excelRow, 'Tipo de registro', 'debe ser 2 para cargue tipo 1', $tipoRegistro);
        }

        $consecutivo = $this->parseInteger($r[1] ?? null, $excelRow, 'Consecutivo', true, 1);
        $paisNacionalidad = $this->parseInteger($r[2] ?? null, $excelRow, 'Pais nacionalidad', true, 1);
        $municipioResidencia = $this->parseInteger($r[3] ?? null, $excelRow, 'Municipio residencia habitual', true, 1);
        $zona = $this->parseZona($r[4] ?? null, $excelRow);
        $codigoIps = $this->parseString($r[5] ?? null, $excelRow, 'Codigo IPS primaria', true, 30);
        $tipoIdent = $this->parseTipoDocumento($r[6] ?? null, $excelRow);
        $noId = $this->parseString($r[7] ?? null, $excelRow, 'No ID usuario', true, 30);

        if ($noId !== null && !preg_match('/^[A-Za-z0-9\-]+$/', $noId)) {
            $this->addError($excelRow, 'No ID usuario', 'solo permite letras, numeros y guion', $noId);
        }

        $primerApellido = $this->parseString($r[8] ?? null, $excelRow, 'Primer apellido', true, 100);
        $segundoApellido = $this->parseString($r[9] ?? null, $excelRow, 'Segundo apellido', false, 100) ?? '';
        $primerNombre = $this->parseString($r[10] ?? null, $excelRow, 'Primer nombre', true, 100);
        $segundoNombre = $this->parseString($r[11] ?? null, $excelRow, 'Segundo nombre', false, 100) ?? '';

        $fechaNacimiento = $this->parseDate($r[12] ?? null, $excelRow, 'Fecha nacimiento');
        $codigoEtnia = $this->parseInteger($r[13] ?? null, $excelRow, 'Codigo pertenencia etnica', true, 0);
        $codigoOcupacion = $this->parseInteger($r[14] ?? null, $excelRow, 'Codigo ocupacion', true, 0);
        $codigoNivelEducativo = $this->parseInteger($r[15] ?? null, $excelRow, 'Codigo nivel educativo', true, 0);
        $fechaProbableParto = $this->parseDate($r[16] ?? null, $excelRow, 'Fecha probable parto');
        $direccion = $this->parseString($r[17] ?? null, $excelRow, 'Direccion residencia', true, 500);

        $hta = $this->parseTinyCode($r[18] ?? null, $excelRow, 'Antecedente HTA cronica');
        $preeclampsia = $this->parseTinyCode($r[19] ?? null, $excelRow, 'Antecedente preeclampsia');
        $diabetes = $this->parseTinyCode($r[20] ?? null, $excelRow, 'Antecedente diabetes');
        $autoinmune = $this->parseTinyCode($r[21] ?? null, $excelRow, 'Antecedente LES/autoinmune');
        $sindrome = $this->parseTinyCode($r[22] ?? null, $excelRow, 'Antecedente sindrome metabolico');
        $erc = $this->parseTinyCode($r[23] ?? null, $excelRow, 'Antecedente ERC');
        $trombofilia = $this->parseTinyCode($r[24] ?? null, $excelRow, 'Antecedente trombofilia');
        $anemia = $this->parseTinyCode($r[25] ?? null, $excelRow, 'Antecedente anemia celulas falciformes');
        $sepsis = $this->parseTinyCode($r[26] ?? null, $excelRow, 'Antecedente sepsis previa');
        $tabaco = $this->parseTinyCode($r[27] ?? null, $excelRow, 'Consumo tabaco durante gestacion');
        $periodoIntergenesico = $this->parseInteger($r[28] ?? null, $excelRow, 'Periodo intergenesico', true, 0, 999);
        $embarazoMultiple = $this->parseTinyCode($r[29] ?? null, $excelRow, 'Embarazo multiple');
        $metodoConcepcion = $this->parseInteger($r[30] ?? null, $excelRow, 'Metodo de concepcion', true, 0, 9);

        if ($fechaNacimiento && Carbon::parse($fechaNacimiento)->gt(Carbon::today())) {
            $this->addError($excelRow, 'Fecha nacimiento', 'no puede ser futura', $fechaNacimiento);
        }

        if ($fechaProbableParto && $fechaNacimiento && Carbon::parse($fechaProbableParto)->lt(Carbon::parse($fechaNacimiento))) {
            $this->addError($excelRow, 'Fecha probable parto', 'no puede ser menor a fecha nacimiento');
        }

        if ($tipoIdent !== null && $noId !== null) {
            $key = $tipoIdent . '|' . $noId;
            if (isset($this->seenInFile[$key])) {
                $this->addError($excelRow, 'Identificacion', 'registro duplicado dentro del archivo', $noId);
            } else {
                $this->seenInFile[$key] = true;
            }

            $exists = GesTipo1::where('tipo_de_identificacion_de_la_usuaria', $tipoIdent)
                ->where('no_id_del_usuario', $noId)
                ->exists();

            if ($exists && $fechaProbableParto !== null && Carbon::today()->lte(Carbon::parse($fechaProbableParto))) {
                $this->rowsDuplicated++;
                $this->addError($excelRow, 'Identificacion', 'ya existe caso activo (FPP no vencida)', $noId);
            }
        }

        $numeroCarnet = null;
        if ($tipoIdent !== null && $noId !== null) {
            $numeroCarnet = $this->getNumeroCarnet($tipoIdent, $noId);
            if (empty($numeroCarnet)) {
                $this->addError($excelRow, 'Numero carnet', 'no se encontro en maestroIdentificaciones');
            }
        }

        if ($this->rowHasErrors) {
            $this->rowsInvalid++;
            return;
        }

        $data = [
            'user_id' => $this->userId,
            'tipo_de_registro' => $tipoRegistro,
            'consecutivo' => $consecutivo,
            'pais_de_la_nacionalidad' => $paisNacionalidad,
            'municipio_de_residencia_habitual' => $municipioResidencia,
            'zona_territorial_de_residencia' => $zona,
            'codigo_de_habilitacion_ips_primaria_de_la_gestante' => $codigoIps,
            'tipo_de_identificacion_de_la_usuaria' => $tipoIdent,
            'no_id_del_usuario' => $noId,
            'numero_carnet' => $numeroCarnet,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'fecha_de_nacimiento' => $fechaNacimiento,
            'codigo_pertenencia_etnica' => $codigoEtnia,
            'codigo_de_ocupacion' => $codigoOcupacion,
            'codigo_nivel_educativo_de_la_gestante' => $codigoNivelEducativo,
            'fecha_probable_de_parto' => $fechaProbableParto,
            'direccion_de_residencia_de_la_gestante' => $direccion,
            'antecedente_hipertension_cronica' => $hta,
            'antecedente_preeclampsia' => $preeclampsia,
            'antecedente_diabetes' => $diabetes,
            'antecedente_les_enfermedad_autoinmune' => $autoinmune,
            'antecedente_sindrome_metabolico' => $sindrome,
            'antecedente_erc' => $erc,
            'antecedente_trombofilia_o_trombosis_venosa_profunda' => $trombofilia,
            'antecedentes_anemia_celulas_falciformes' => $anemia,
            'antecedente_sepsis_durante_gestaciones_previas' => $sepsis,
            'consumo_tabaco_durante_la_gestacion' => $tabaco,
            'periodo_intergenesico' => $periodoIntergenesico,
            'embarazo_multiple' => $embarazoMultiple,
            'metodo_de_concepcion' => $metodoConcepcion,
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
        DB::table('ges_tipo1')->insert($this->buffer);
        $this->rowsCreated += $count;
        $this->buffer = [];
    }
}
