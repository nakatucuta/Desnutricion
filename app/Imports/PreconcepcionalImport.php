<?php

namespace App\Imports;

use App\Models\Preconcepcional;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class PreconcepcionalImport implements OnEachRow, WithStartRow, SkipsEmptyRows
{
    private int $batchId;

    private int $rowsTotal = 0;
    private int $rowsCreated = 0;
    private int $rowsSkipped = 0;
    private int $rowsDuplicate = 0;
    private int $rowsInvalid = 0;

    private bool $structureValidated = false;
    private bool $structureOk = true;

    private array $errores = [];
    private array $seenFileKeys = [];

    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function getCounters(): array
    {
        return [
            'rows_total' => $this->rowsTotal,
            'rows_created' => $this->rowsCreated,
            'rows_skipped' => $this->rowsSkipped,
            'rows_duplicate' => $this->rowsDuplicate,
            'rows_invalid' => $this->rowsInvalid,
            'errors_count' => count($this->errores),
        ];
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function startRow(): int
    {
        // Datos comienzan en la fila 3
        return 3;
    }

    private function addError(int $excelRow, string $field, string $message, $value = null): void
    {
        if (count($this->errores) >= 5000) {
            return;
        }

        $msg = "Fila {$excelRow} | {$field}: {$message}";

        $value = $this->clean($value);
        if ($value !== null) {
            $str = trim((string) $value);
            $str = mb_substr($str, 0, 120);
            $msg .= " (valor: {$str})";
        }

        $this->errores[] = $msg;
    }

    private function clean($v)
    {
        if ($v === null) {
            return null;
        }

        if (is_string($v)) {
            $v = trim($v);
            $upper = mb_strtoupper($v, 'UTF-8');
            if (
                $v === '' ||
                $v === '?' ||
                $upper === 'N/A' ||
                $upper === 'NA' ||
                $upper === 'NULL' ||
                $upper === 'NONE' ||
                $upper === 'SIN DATO' ||
                $upper === 'NO APLICA'
            ) {
                return null;
            }

            return $v;
        }

        return $v;
    }

    private function cell(array $row, int $index)
    {
        return $this->clean($row[$index] ?? null);
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($this->clean($v) !== null) {
                return false;
            }
        }
        return true;
    }

    private function parseDate($value, int $excelRow, string $field, bool $allowFuture = false): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $dt = null;

        if ($value instanceof \DateTimeInterface) {
            $dt = Carbon::instance($value);
        }

        if ($dt === null && is_numeric($value)) {
            try {
                $dt = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Throwable $e) {
                $this->addError($excelRow, $field, 'fecha invalida', $value);
                return null;
            }
        }

        if ($dt === null) {
            $value = (string) $value;
            $value = preg_replace('/\s+.*/', '', $value);
            $value = preg_replace('/\s+/', '', $value);

            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'Y/m/d', 'd.m.Y'] as $fmt) {
                try {
                    $dt = Carbon::createFromFormat($fmt, $value);
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
            $this->addError($excelRow, $field, 'fecha fuera de rango para SQL Server', $value);
            return null;
        }

        if (!$allowFuture && $dt->gt(Carbon::today())) {
            $this->addError($excelRow, $field, 'la fecha no puede ser futura', $value);
            return null;
        }

        return $dt->format('Y-m-d');
    }

    private function parseInteger($value, int $excelRow, string $field, ?int $min = null, ?int $max = null): ?int
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!preg_match('/^-?\d+(\.0+)?$/', $normalized)) {
            $this->addError($excelRow, $field, 'debe ser numero entero', $value);
            return null;
        }

        $number = (int) round((float) $normalized);
        if ($min !== null && $number < $min) {
            $this->addError($excelRow, $field, "debe ser mayor o igual a {$min}", $value);
            return null;
        }
        if ($max !== null && $number > $max) {
            $this->addError($excelRow, $field, "debe ser menor o igual a {$max}", $value);
            return null;
        }

        return $number;
    }

    private function parseDecimal($value, int $excelRow, string $field, ?float $min = null, ?float $max = null): ?float
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!is_numeric($normalized)) {
            $this->addError($excelRow, $field, 'debe ser numero decimal', $value);
            return null;
        }

        $number = (float) $normalized;
        if ($min !== null && $number < $min) {
            $this->addError($excelRow, $field, "debe ser mayor o igual a {$min}", $value);
            return null;
        }
        if ($max !== null && $number > $max) {
            $this->addError($excelRow, $field, "debe ser menor o igual a {$max}", $value);
            return null;
        }

        return round($number, 2);
    }

    private function parseTalla($value, int $excelRow): ?float
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);
        if (!is_numeric($normalized)) {
            $this->addError($excelRow, 'Talla', 'debe ser numero decimal', $value);
            return null;
        }

        $number = (float) $normalized;

        // Si llega en centimetros (ej: 158), lo convertimos a metros.
        if ($number > 3 && $number <= 250) {
            $number = $number / 100;
        }

        if ($number < 0.5 || $number > 2.5) {
            $this->addError($excelRow, 'Talla', 'valor fuera de rango esperado', $value);
            return null;
        }

        return round($number, 2);
    }

    private function parseText($value, int $excelRow, string $field, int $maxLength = 255): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if (mb_strlen($text) > $maxLength) {
            $this->addError($excelRow, $field, "supera el maximo de {$maxLength} caracteres");
            return null;
        }

        return $text;
    }

    private function normalizeToken($value): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        $norm = mb_strtolower((string) $value, 'UTF-8');
        $norm = \Illuminate\Support\Str::ascii($norm);
        $norm = preg_replace('/[^a-z0-9]+/', '_', $norm);
        return trim((string) $norm, '_');
    }

    private function parseYesNo($value, int $excelRow, string $field): ?string
    {
        $norm = $this->normalizeToken($value);
        if ($norm === null) {
            return null;
        }

        $yes = ['si', 's', '1', 'true', 'verdadero', 'yes', 'x'];
        $no = ['no', 'n', '0', 'false', 'falso'];

        if (in_array($norm, $yes, true)) {
            return 'SI';
        }

        if (in_array($norm, $no, true)) {
            return 'NO';
        }

        $this->addError($excelRow, $field, 'debe ser SI o NO', $value);
        return null;
    }

    private function parseEnum($value, int $excelRow, string $field, array $allowedMap): ?string
    {
        $norm = $this->normalizeToken($value);
        if ($norm === null) {
            return null;
        }

        if (isset($allowedMap[$norm])) {
            return (string) $allowedMap[$norm];
        }

        $this->addError($excelRow, $field, 'valor no permitido', $value);
        return null;
    }

    private function parseTipoDocumento($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Tipo documento', 'campo obligatorio');
            return null;
        }

        return $this->parseEnum($value, $excelRow, 'Tipo documento', [
            'cc' => 'CC',
            'c_c' => 'CC',
            'ti' => 'TI',
            't_i' => 'TI',
            'ce' => 'CE',
            'c_e' => 'CE',
            'rc' => 'RC',
            'r_c' => 'RC',
            'pa' => 'PA',
            'p_a' => 'PA',
            'pep' => 'PEP',
            'ppt' => 'PPT',
            'as' => 'AS',
            'ms' => 'MS',
            'cd' => 'CD',
            'nit' => 'NIT',
            'nuip' => 'NUIP',
        ]);
    }

    private function parseNumeroDocumento($value, int $excelRow): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            $this->addError($excelRow, 'Numero identificacion', 'campo obligatorio');
            return null;
        }

        if (is_numeric($value)) {
            $value = sprintf('%.0f', (float) $value);
        }

        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/\s+/', '', $value);

        if ($value === '') {
            $this->addError($excelRow, 'Numero identificacion', 'campo obligatorio');
            return null;
        }

        if (mb_strlen($value) > 30) {
            $this->addError($excelRow, 'Numero identificacion', 'maximo 30 caracteres', $value);
            return null;
        }

        if (!preg_match('/^[A-Z0-9\.\-_]+$/', $value)) {
            $this->addError($excelRow, 'Numero identificacion', 'contiene caracteres no permitidos', $value);
            return null;
        }

        return $value;
    }

    private function parseSexo($value, int $excelRow): ?string
    {
        return $this->parseEnum($value, $excelRow, 'Sexo', [
            'f' => 'FEMENINO',
            'femenino' => 'FEMENINO',
            'mujer' => 'FEMENINO',
            'm' => 'MASCULINO',
            'masculino' => 'MASCULINO',
            'hombre' => 'MASCULINO',
            'intersexual' => 'INTERSEXUAL',
            'i' => 'INTERSEXUAL',
            'otro' => 'OTRO',
        ]);
    }

    private function parseResultadoTamizaje($value, int $excelRow, string $field): ?string
    {
        return $this->parseEnum($value, $excelRow, $field, [
            'positivo' => 'POSITIVO',
            'negativo' => 'NEGATIVO',
            'reactivo' => 'REACTIVO',
            'no_reactivo' => 'NO REACTIVO',
            'noreactivo' => 'NO REACTIVO',
            'indeterminado' => 'INDETERMINADO',
            'pendiente' => 'PENDIENTE',
        ]);
    }

    private function parseRiesgo($value, int $excelRow): ?string
    {
        return $this->parseEnum($value, $excelRow, 'Riesgo preconcepcional', [
            'alto' => 'ALTO',
            'alto_riesgo' => 'ALTO',
            'medio' => 'MEDIO',
            'mediano' => 'MEDIO',
            'riesgo_medio' => 'MEDIO',
            'bajo' => 'BAJO',
            'bajo_riesgo' => 'BAJO',
            'sin_riesgo' => 'SIN RIESGO',
            'ninguno' => 'SIN RIESGO',
        ]);
    }

    private function validateConsistency(array $data, int $excelRow): void
    {
        if (!empty($data['fecha_nacimiento']) && $data['edad'] !== null) {
            $edadCalculada = Carbon::parse($data['fecha_nacimiento'])->age;
            if (abs($edadCalculada - (int) $data['edad']) > 2) {
                $this->addError(
                    $excelRow,
                    'Edad',
                    "no coincide con fecha nacimiento (edad aprox: {$edadCalculada})",
                    $data['edad']
                );
            }
        }

        if ($data['edad'] !== null && $data['edad_menarquia'] !== null && $data['edad_menarquia'] > $data['edad']) {
            $this->addError($excelRow, 'Edad menarquia', 'no puede ser mayor que la edad actual', $data['edad_menarquia']);
        }

        if (
            $data['numero_gestaciones_previas'] !== null &&
            $data['partos_vaginales'] !== null &&
            $data['cesareas'] !== null &&
            $data['abortos'] !== null
        ) {
            $totalEventos = (int) $data['partos_vaginales'] + (int) $data['cesareas'] + (int) $data['abortos'];
            if ($totalEventos > (int) $data['numero_gestaciones_previas']) {
                $this->addError(
                    $excelRow,
                    'Gestaciones previas',
                    'partos + cesareas + abortos no puede superar numero_gestaciones_previas',
                    $data['numero_gestaciones_previas']
                );
            }
        }

        if ($data['peso'] !== null && $data['talla'] !== null && $data['imc'] !== null && $data['talla'] > 0) {
            $imcCalculado = round($data['peso'] / ($data['talla'] * $data['talla']), 2);
            if (abs($imcCalculado - (float) $data['imc']) > 5) {
                $this->addError(
                    $excelRow,
                    'IMC',
                    "no coincide con peso/talla (imc aprox: {$imcCalculado})",
                    $data['imc']
                );
            }
        }
    }

    public function onRow(Row $row)
    {
        $excelRow = (int) $row->getIndex();
        $this->rowsTotal++;

        $raw = $row->toArray();

        if ($this->isRowEmpty($raw)) {
            $this->rowsSkipped++;
            return;
        }

        if (!$this->structureValidated) {
            $this->structureValidated = true;
            $maxIndex = empty($raw) ? -1 : max(array_keys($raw));
            if ($maxIndex < 86) {
                $this->structureOk = false;
                $this->addError(
                    $excelRow,
                    'Estructura',
                    'el archivo no trae todas las columnas esperadas (minimo 87 columnas de datos)'
                );
            }
        }

        if (!$this->structureOk) {
            $this->rowsInvalid++;
            return;
        }

        $errorsBefore = count($this->errores);

        // Mapeo por posicion real de columnas (evita perdida por encabezados duplicados).
        $tipoDoc = $this->parseTipoDocumento($this->cell($raw, 1), $excelRow);
        $numId = $this->parseNumeroDocumento($this->cell($raw, 2), $excelRow);

        $data = [
            'no' => $this->parseInteger($this->cell($raw, 0), $excelRow, 'No', 0, 100000000),
            'tipo_documento' => $tipoDoc,
            'numero_identificacion' => $numId,

            'apellido_1' => $this->parseText($this->cell($raw, 3), $excelRow, 'Apellido 1', 120),
            'apellido_2' => $this->parseText($this->cell($raw, 4), $excelRow, 'Apellido 2', 120),
            'nombre_1' => $this->parseText($this->cell($raw, 5), $excelRow, 'Nombre 1', 120),
            'nombre_2' => $this->parseText($this->cell($raw, 6), $excelRow, 'Nombre 2', 120),
            'fecha_nacimiento' => $this->parseDate($this->cell($raw, 7), $excelRow, 'Fecha nacimiento'),
            'edad' => $this->parseInteger($this->cell($raw, 8), $excelRow, 'Edad', 0, 120),
            'sexo' => $this->parseSexo($this->cell($raw, 9), $excelRow),

            'regimen_afiliacion' => $this->parseText($this->cell($raw, 10), $excelRow, 'Regimen afiliacion'),
            'pertenencia_etnica' => $this->parseText($this->cell($raw, 11), $excelRow, 'Pertenencia etnica'),
            'grupo_poblacional' => $this->parseText($this->cell($raw, 12), $excelRow, 'Grupo poblacional'),
            'departamento_residencia' => $this->parseText($this->cell($raw, 13), $excelRow, 'Departamento residencia'),
            'municipio_residencia' => $this->parseText($this->cell($raw, 14), $excelRow, 'Municipio residencia'),
            'zona' => $this->parseText($this->cell($raw, 15), $excelRow, 'Zona'),
            'etnia' => $this->parseText($this->cell($raw, 16), $excelRow, 'Etnia'),
            'asentamiento' => $this->parseText($this->cell($raw, 17), $excelRow, 'Asentamiento'),
            'telefono' => $this->parseText($this->cell($raw, 18), $excelRow, 'Telefono', 30),
            'direccion' => $this->parseText($this->cell($raw, 19), $excelRow, 'Direccion'),
            'nivel_educativo' => $this->parseText($this->cell($raw, 20), $excelRow, 'Nivel educativo'),
            'discapacidad' => $this->parseYesNo($this->cell($raw, 21), $excelRow, 'Discapacidad'),
            'mujer_cabeza_hogar' => $this->parseYesNo($this->cell($raw, 22), $excelRow, 'Mujer cabeza hogar'),
            'ocupacion' => $this->parseText($this->cell($raw, 23), $excelRow, 'Ocupacion'),
            'estado_civil' => $this->parseText($this->cell($raw, 24), $excelRow, 'Estado civil'),
            'control_tradicional' => $this->parseYesNo($this->cell($raw, 25), $excelRow, 'Control tradicional'),
            'gestante_renuente' => $this->parseYesNo($this->cell($raw, 26), $excelRow, 'Gestante renuente'),
            'inasistente' => $this->parseYesNo($this->cell($raw, 27), $excelRow, 'Inasistente'),
            'nombre_ips_primaria' => $this->parseText($this->cell($raw, 28), $excelRow, 'IPS primaria'),

            'hipertension_personal' => $this->parseYesNo($this->cell($raw, 29), $excelRow, 'Hipertension personal'),
            'diabetes_mellitus' => $this->parseYesNo($this->cell($raw, 30), $excelRow, 'Diabetes mellitus'),
            'enfermedad_renal' => $this->parseYesNo($this->cell($raw, 31), $excelRow, 'Enfermedad renal'),
            'cardiopatias' => $this->parseYesNo($this->cell($raw, 32), $excelRow, 'Cardiopatias'),
            'epilepsia' => $this->parseYesNo($this->cell($raw, 33), $excelRow, 'Epilepsia'),
            'enfermedades_autoinmunes' => $this->parseYesNo($this->cell($raw, 34), $excelRow, 'Autoinmunes'),
            'trastornos_mentales' => $this->parseYesNo($this->cell($raw, 35), $excelRow, 'Trastornos mentales'),
            'cancer' => $this->parseYesNo($this->cell($raw, 36), $excelRow, 'Cancer'),
            'enfermedades_infecciosas_cronicas' => $this->parseYesNo($this->cell($raw, 37), $excelRow, 'Infecciosas cronicas'),
            'uso_permanente_medicamentos' => $this->parseYesNo($this->cell($raw, 38), $excelRow, 'Uso permanente medicamentos'),
            'alergias' => $this->parseText($this->cell($raw, 39), $excelRow, 'Alergias'),

            'edad_menarquia' => $this->parseInteger($this->cell($raw, 40), $excelRow, 'Edad menarquia', 6, 30),
            'fecha_ultimo_periodo_mestrual' => $this->parseDate($this->cell($raw, 41), $excelRow, 'FUM'),
            'numero_gestaciones_previas' => $this->parseInteger($this->cell($raw, 42), $excelRow, 'Numero gestaciones previas', 0, 30),
            'partos_vaginales' => $this->parseInteger($this->cell($raw, 43), $excelRow, 'Partos vaginales', 0, 30),
            'cesareas' => $this->parseInteger($this->cell($raw, 44), $excelRow, 'Cesareas', 0, 30),
            'abortos' => $this->parseInteger($this->cell($raw, 45), $excelRow, 'Abortos', 0, 30),
            'complicaciones_obstetricas_previas' => $this->parseText($this->cell($raw, 46), $excelRow, 'Complicaciones obstetricas previas'),

            'hipertension_familiar' => $this->parseYesNo($this->cell($raw, 47), $excelRow, 'Hipertension familiar'),
            'diabetes_familiar' => $this->parseYesNo($this->cell($raw, 48), $excelRow, 'Diabetes familiar'),
            'malformaciones_congenitas' => $this->parseText($this->cell($raw, 49), $excelRow, 'Malformaciones congenitas'),
            'enfermedades_geneticas' => $this->parseText($this->cell($raw, 50), $excelRow, 'Enfermedades geneticas'),
            'enfermedades_mentales_familia' => $this->parseText($this->cell($raw, 51), $excelRow, 'Enfermedades mentales familia'),
            'muerte_materna_familia' => $this->parseYesNo($this->cell($raw, 52), $excelRow, 'Muerte materna familia'),

            'inicio_vida_sexual' => $this->parseInteger($this->cell($raw, 53), $excelRow, 'Inicio vida sexual', 5, 80),
            'numero_parejas_sexuales' => $this->parseInteger($this->cell($raw, 54), $excelRow, 'Numero parejas sexuales', 0, 200),
            'uso_actual_metodos_anticonceptivos' => $this->parseText($this->cell($raw, 55), $excelRow, 'Uso metodos anticonceptivos'),
            'antecedentes_its' => $this->parseText($this->cell($raw, 56), $excelRow, 'Antecedentes ITS'),
            'deseo_reproductivo' => $this->parseText($this->cell($raw, 57), $excelRow, 'Deseo reproductivo'),

            'consumo_tabaco' => $this->parseYesNo($this->cell($raw, 58), $excelRow, 'Consumo tabaco'),
            'consumo_alcohol' => $this->parseYesNo($this->cell($raw, 59), $excelRow, 'Consumo alcohol'),
            'consumo_sustancias_psicoactivas' => $this->parseYesNo($this->cell($raw, 60), $excelRow, 'Consumo SPA'),
            'actividad_fisica' => $this->parseText($this->cell($raw, 61), $excelRow, 'Actividad fisica'),
            'alimentacion_saludable' => $this->parseText($this->cell($raw, 62), $excelRow, 'Alimentacion saludable'),
            'violencias' => $this->parseText($this->cell($raw, 63), $excelRow, 'Violencias'),

            'peso' => $this->parseDecimal($this->cell($raw, 64), $excelRow, 'Peso', 20, 300),
            'talla' => $this->parseTalla($this->cell($raw, 65), $excelRow),
            'imc' => $this->parseDecimal($this->cell($raw, 66), $excelRow, 'IMC', 8, 90),
            'riesgo_nutricional' => $this->parseText($this->cell($raw, 67), $excelRow, 'Riesgo nutricional'),
            'suplementacion_acido_folico' => $this->parseYesNo($this->cell($raw, 68), $excelRow, 'Suplementacion acido folico'),

            'tetanos' => $this->parseText($this->cell($raw, 69), $excelRow, 'Tetanos'),
            'influenza' => $this->parseText($this->cell($raw, 70), $excelRow, 'Influenza'),
            'covid_19' => $this->parseText($this->cell($raw, 71), $excelRow, 'COVID-19'),
            'fecha_tamizaje_sifilis' => $this->parseDate($this->cell($raw, 72), $excelRow, 'Fecha tamizaje sifilis'),
            'resultado_sifilis' => $this->parseResultadoTamizaje($this->cell($raw, 73), $excelRow, 'Resultado sifilis'),
            'fecha_tamizaje_vih' => $this->parseDate($this->cell($raw, 74), $excelRow, 'Fecha tamizaje VIH'),
            'resultado_vih' => $this->parseResultadoTamizaje($this->cell($raw, 75), $excelRow, 'Resultado VIH'),
            'fecha_tamizaje_hepatitis_b' => $this->parseDate($this->cell($raw, 76), $excelRow, 'Fecha tamizaje Hepatitis B'),
            'resultado_hepatitis_b' => $this->parseResultadoTamizaje($this->cell($raw, 77), $excelRow, 'Resultado Hepatitis B'),
            'citologia' => $this->parseText($this->cell($raw, 78), $excelRow, 'Citologia'),
            'tamizaje_salud_mental' => $this->parseText($this->cell($raw, 79), $excelRow, 'Tamizaje salud mental'),
            'riesgo_preconcepcional' => $this->parseRiesgo($this->cell($raw, 80), $excelRow),

            // 81 y 82 en tu plantilla son columnas vacias/auxiliares y se ignoran.
            'consejeria_preconcepcional' => $this->parseYesNo($this->cell($raw, 83), $excelRow, 'Consejeria preconcepcional'),
            'educacion_planificacion_familiar' => $this->parseYesNo($this->cell($raw, 84), $excelRow, 'Educacion planificacion familiar'),
            'recomendaciones_nutricionales' => $this->parseText($this->cell($raw, 85), $excelRow, 'Recomendaciones nutricionales'),
            'ordenes_medicas' => $this->parseText($this->cell($raw, 86), $excelRow, 'Ordenes medicas', 4000),
        ];

        if ($tipoDoc !== null && $numId !== null) {
            $fileKey = "{$tipoDoc}|{$numId}";
            if (isset($this->seenFileKeys[$fileKey])) {
                $this->addError($excelRow, 'Identificacion', 'duplicada dentro del mismo archivo', $numId);
            } else {
                $this->seenFileKeys[$fileKey] = true;
            }

            $exists = Preconcepcional::where('tipo_documento', $tipoDoc)
                ->where('numero_identificacion', $numId)
                ->exists();

            if ($exists) {
                $this->rowsDuplicate++;
            }
        }

        $this->validateConsistency($data, $excelRow);

        if (count($this->errores) > $errorsBefore) {
            $this->rowsInvalid++;
            return;
        }

        $now = now()->format('Ymd H:i:s');

        $data['created_batch_id'] = $this->batchId;
        $data['last_batch_id'] = $this->batchId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        Preconcepcional::withoutTimestamps(function () use ($data) {
            Preconcepcional::create($data);
        });

        $this->rowsCreated++;
    }
}
