<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Cell as ExcelCell;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GesTipo1Import implements OnEachRow, WithStartRow, WithChunkReading, SkipsEmptyRows
{
    private const EDAD_MINIMA_FERTIL_ANIOS = 10;
    private const GESTACION_NORMAL_MAXIMA_DIAS = 294;

    private int $batchVerificationsId;
    private int $userId;

    private array $buffer = [];
    private ?int $maxRowsPerInsert = null;
    private array $carnetCache = [];
    private array $municipioCache = [];
    private array $ipsCodigoCache = [];
    private array $seenInFile = [];
    private array $errores = [];
    private bool $rowHasErrors = false;
    private bool $errorsCapNoticeAdded = false;

    private const CODIGOS_PERTENENCIA_ETNICA_VALIDOS = [1, 2, 3, 4, 5, 6, 7];

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

    private function parseAntecedenteCode($value, int $excelRow, string $field): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, $field, 'campo obligatorio');
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!preg_match('/^-?\d+(\.0+)?$/', $normalized)) {
            $this->addError($excelRow, $field, 'solo permite las opciones 1 y 2', $value);
            return null;
        }

        $codigo = (int) round((float) $normalized);
        if (!in_array($codigo, [1, 2], true)) {
            $this->addError($excelRow, $field, 'solo permite las opciones 1 y 2', $value);
            return null;
        }

        return $codigo;
    }

    private function parsePeriodoIntergenesico($value, int $excelRow): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Periodo intergenesico', 'campo obligatorio');
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!preg_match('/^-?\d+(\.0+)?$/', $normalized)) {
            $this->addError($excelRow, 'Periodo intergenesico', 'solo permite los codigos 0, 1, 2 y 3', $value);
            return null;
        }

        $codigo = (int) round((float) $normalized);
        if (!in_array($codigo, [0, 1, 2, 3], true)) {
            $this->addError($excelRow, 'Periodo intergenesico', 'solo permite los codigos 0, 1, 2 y 3', $value);
            return null;
        }

        return $codigo;
    }

    private function parseMetodoConcepcion($value, int $excelRow): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Metodo de concepcion', 'campo obligatorio');
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!preg_match('/^-?\d+(\.0+)?$/', $normalized)) {
            $this->addError($excelRow, 'Metodo de concepcion', 'solo permite los codigos 1, 2 y 3', $value);
            return null;
        }

        $codigo = (int) round((float) $normalized);
        if (!in_array($codigo, [1, 2, 3], true)) {
            $this->addError($excelRow, 'Metodo de concepcion', 'solo permite los codigos 1, 2 y 3', $value);
            return null;
        }

        return $codigo;
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

    private function parseMunicipioDane($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Municipio residencia habitual', 'campo obligatorio');
            return null;
        }

        $codigo = trim((string) $value);

        if (!preg_match('/^\d{5}$/', $codigo)) {
            $this->addError($excelRow, 'Municipio residencia habitual', 'debe ser un codigo DANE de 5 digitos', $value);
            return null;
        }

        if (!$this->municipioExiste($codigo, $excelRow)) {
            $this->addError($excelRow, 'Municipio residencia habitual', 'el codigo DANE no es valido', $codigo);
            return null;
        }

        return $codigo;
    }

    private function municipioExiste(string $codigo, int $excelRow): bool
    {
        if (array_key_exists($codigo, $this->municipioCache)) {
            return $this->municipioCache[$codigo];
        }

        try {
            $exists = DB::connection('sqlsrv_1')
                ->table('sga.dbo.municipios')
                ->where('codigoDepartamento', substr($codigo, 0, 2))
                ->where('codigoMunicipio', substr($codigo, 2, 3))
                ->exists();
        } catch (\Throwable $e) {
            $this->addError(
                $excelRow,
                'Municipio residencia habitual',
                'no fue posible validar el codigo DANE en este momento',
                $codigo
            );

            $this->municipioCache[$codigo] = false;
            return false;
        }

        $this->municipioCache[$codigo] = $exists;

        return $exists;
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

    private function parseCodigoIps($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Codigo IPS primaria', 'campo obligatorio');
            return null;
        }

        $codigo = trim((string) $value);

        if (!preg_match('/^\d{12}$/', $codigo)) {
            $this->addError($excelRow, 'Codigo IPS primaria', 'debe contener exactamente 12 digitos', $value);
            return null;
        }

        if (!$this->codigoIpsExiste($codigo, $excelRow)) {
            $this->addError($excelRow, 'Codigo IPS primaria', 'el codigo no es valido', $codigo);
            return null;
        }

        return $codigo;
    }

    private function parsePertenenciaEtnica($value, int $excelRow): ?int
    {
        $codigo = $this->parseInteger($value, $excelRow, 'Codigo pertenencia etnica', true, 0);
        if ($codigo === null) {
            return null;
        }

        if (!in_array($codigo, self::CODIGOS_PERTENENCIA_ETNICA_VALIDOS, true)) {
            $this->addError(
                $excelRow,
                'Codigo pertenencia etnica',
                'solo permite los codigos 1, 2, 3, 4, 5, 6 y 7',
                $value
            );

            return null;
        }

        return $codigo;
    }

    private function codigoIpsExiste(string $codigo, int $excelRow): bool
    {
        if (array_key_exists($codigo, $this->ipsCodigoCache)) {
            return $this->ipsCodigoCache[$codigo];
        }

        try {
            $exists = DB::connection('sqlsrv_1')
                ->table('sga.dbo.refips')
                ->where('codigo', $codigo)
                ->exists();
        } catch (\Throwable $e) {
            $this->addError(
                $excelRow,
                'Codigo IPS primaria',
                'no fue posible validar el codigo en este momento',
                $codigo
            );

            $this->ipsCodigoCache[$codigo] = false;
            return false;
        }

        $this->ipsCodigoCache[$codigo] = $exists;

        return $exists;
    }

    private function getFormattedCellValue(Row $row, string $column)
    {
        try {
            $worksheet = $row->getDelegate()->getWorksheet();
            $coordinate = $column . $row->getIndex();

            return ExcelCell::make($worksheet, $coordinate)->getValue(null, false, true);
        } catch (\Throwable $e) {
            return null;
        }
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
        $tipoIdent = mb_strtoupper(trim((string) $tipoIdent), 'UTF-8');
        $noId = preg_replace('/\s+/', '', trim((string) $noId));

        $key = $tipoIdent . '|' . $noId;

        if (array_key_exists($key, $this->carnetCache)) {
            return $this->carnetCache[$key];
        }

        try {
            $numeroCarnet = DB::connection('sqlsrv_1')
                ->table('sga.dbo.maestroIdentificaciones as A')
                ->join('sga.dbo.maestroafiliados as B', function ($join) {
                    $join->on('A.tipoIdentificacion', '=', 'B.tipoIdentificacion')
                        ->on('A.identificacion', '=', 'B.identificacion');
                })
                ->join('sga.dbo.refEstadoActual as C', 'B.estadoActual', '=', 'C.codigo')
                ->where('A.identificacion', $noId)
                ->where('A.tipoIdentificacion', $tipoIdent)
                ->where('C.estado', 'AC')
                ->value('A.numeroCarnet');
        } catch (\Throwable $e) {
            $this->addError(
                0,
                'DB externa',
                'error consultando sqlsrv_1: ' . $e->getMessage()
            );

            $this->carnetCache[$key] = null;
            return null;
        }

        $this->carnetCache[$key] = !empty($numeroCarnet)
            ? (string) $numeroCarnet
            : null;

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
        $municipioValue = $this->getFormattedCellValue($row, 'D');
        $municipioResidencia = $this->parseMunicipioDane($municipioValue ?? ($r[3] ?? null), $excelRow);
        $zona = $this->parseZona($r[4] ?? null, $excelRow);
        $codigoIpsValue = $this->getFormattedCellValue($row, 'F');
        $codigoIps = $this->parseCodigoIps($codigoIpsValue ?? ($r[5] ?? null), $excelRow);
        $tipoIdent = $this->parseTipoDocumento($r[6] ?? null, $excelRow);
        $noId = $this->parseString($r[7] ?? null, $excelRow, 'No ID usuario', true, 30);

        if ($noId !== null) {
            $noId = preg_replace('/\s+/', '', trim((string) $noId));
        }

        if ($tipoIdent !== null) {
            $tipoIdent = mb_strtoupper(trim((string) $tipoIdent), 'UTF-8');
        }

        if ($noId !== null && !preg_match('/^[A-Za-z0-9\-]+$/', $noId)) {
            $this->addError($excelRow, 'No ID usuario', 'solo permite letras, numeros y guion', $noId);
        }

        $primerApellido = $this->parseString($r[8] ?? null, $excelRow, 'Primer apellido', true, 100);
        $segundoApellido = $this->parseString($r[9] ?? null, $excelRow, 'Segundo apellido', false, 100) ?? '';
        $primerNombre = $this->parseString($r[10] ?? null, $excelRow, 'Primer nombre', true, 100);
        $segundoNombre = $this->parseString($r[11] ?? null, $excelRow, 'Segundo nombre', false, 100) ?? '';

        $fechaNacimiento = $this->parseDate($r[12] ?? null, $excelRow, 'Fecha nacimiento');
        $codigoEtnia = $this->parsePertenenciaEtnica($r[13] ?? null, $excelRow);
        $codigoOcupacion = $this->parseInteger($r[14] ?? null, $excelRow, 'Codigo ocupacion', true, 0);
        $codigoNivelEducativo = $this->parseInteger($r[15] ?? null, $excelRow, 'Codigo nivel educativo', true, 0);
        $fechaProbableParto = $this->parseDate($r[16] ?? null, $excelRow, 'Fecha probable parto');
        $direccion = $this->parseString($r[17] ?? null, $excelRow, 'Direccion residencia', true, 500);

        $hta = $this->parseAntecedenteCode($r[18] ?? null, $excelRow, 'Antecedente HTA cronica');
        $preeclampsia = $this->parseAntecedenteCode($r[19] ?? null, $excelRow, 'Antecedente preeclampsia');
        $diabetes = $this->parseAntecedenteCode($r[20] ?? null, $excelRow, 'Antecedente diabetes');
        $autoinmune = $this->parseAntecedenteCode($r[21] ?? null, $excelRow, 'Antecedente LES/autoinmune');
        $sindrome = $this->parseAntecedenteCode($r[22] ?? null, $excelRow, 'Antecedente sindrome metabolico');
        $erc = $this->parseAntecedenteCode($r[23] ?? null, $excelRow, 'Antecedente ERC');
        $trombofilia = $this->parseAntecedenteCode($r[24] ?? null, $excelRow, 'Antecedente trombofilia');
        $anemia = $this->parseAntecedenteCode($r[25] ?? null, $excelRow, 'Antecedente anemia celulas falciformes');
        $sepsis = $this->parseAntecedenteCode($r[26] ?? null, $excelRow, 'Antecedente sepsis previa');
        $tabaco = $this->parseAntecedenteCode($r[27] ?? null, $excelRow, 'Consumo tabaco durante gestacion');
        $periodoIntergenesico = $this->parsePeriodoIntergenesico($r[28] ?? null, $excelRow);
        $embarazoMultiple = $this->parseAntecedenteCode($r[29] ?? null, $excelRow, 'Embarazo multiple');
        $metodoConcepcion = $this->parseMetodoConcepcion($r[30] ?? null, $excelRow);

        $today = Carbon::today();

        if ($fechaNacimiento && Carbon::parse($fechaNacimiento)->gt($today)) {
            $this->addError($excelRow, 'Fecha nacimiento', 'no puede ser futura', $fechaNacimiento);
        }

        if ($fechaProbableParto && $fechaNacimiento && Carbon::parse($fechaProbableParto)->lt(Carbon::parse($fechaNacimiento))) {
            $this->addError($excelRow, 'Fecha probable parto', 'no puede ser menor a fecha nacimiento');
        }

        if ($fechaProbableParto) {
            $fpp = Carbon::parse($fechaProbableParto);

            if ($fpp->lt($today)) {
                $this->addError($excelRow, 'Fecha probable parto', 'debe ser igual o posterior a la fecha actual', $fechaProbableParto);
            }

            if ($fpp->gt($today->copy()->addDays(self::GESTACION_NORMAL_MAXIMA_DIAS))) {
                $this->addError(
                    $excelRow,
                    'Fecha probable parto',
                    'debe estar dentro de un periodo de gestacion normal',
                    $fechaProbableParto
                );
            }

            if ($fechaNacimiento) {
                $fechaMinimaFertil = Carbon::parse($fechaNacimiento)->addYears(self::EDAD_MINIMA_FERTIL_ANIOS);
                if ($fechaMinimaFertil->gt($fpp)) {
                    $this->addError(
                        $excelRow,
                        'Fecha probable parto',
                        'no es coherente con una edad minima fertil de 10 anos',
                        $fechaProbableParto
                    );
                }
            }
        }

        if ($tipoIdent !== null && $noId !== null) {
            $key = $tipoIdent . '|' . $noId;
            if (isset($this->seenInFile[$key])) {
                $this->addError($excelRow, 'Identificacion', 'registro duplicado dentro del archivo', $noId);
            } else {
                $this->seenInFile[$key] = true;
            }
        }

        $numeroCarnet = null;
        if ($tipoIdent !== null && $noId !== null) {
            $numeroCarnet = $this->getNumeroCarnet($tipoIdent, $noId);

            if (empty($numeroCarnet)) {
                $this->addError($excelRow, 'Numero carnet', 'no se encontro afiliado activo en DB externa con esa identificacion', $tipoIdent . ' - ' . $noId);
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
