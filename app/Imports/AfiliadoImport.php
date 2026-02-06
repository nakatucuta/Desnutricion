<?php

namespace App\Imports;

use App\Models\batch_verifications;
use App\Models\afiliado as Afiliado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AfiliadoImport implements ToModel, WithStartRow, WithChunkReading
{
    protected $errores = [];
    protected $guardar = true;
    protected $filasParaGuardar = [];

    private $batch_verifications_id;

    // Cache en memoria para no consultar DB externa por cada fila repetida
    // key: "TIPO|IDENTIFICACION" => numeroCarnet|string|null
    private array $carnetCache = [];

    // Cache afiliados locales por numero_carnet (evita queries repetidas)
    // key: numeroCarnet => afiliado_id
    private array $afiliadoIdCache = [];

    public function __construct()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->save();

        $this->batch_verifications_id = $verificacion->id;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function chunkSize(): int
    {
        // Puedes subirlo a 300 si tu server aguanta bien
        return 200;
    }

    public function model(array $row)
    {
        // Evita Notice/Undefined offset cuando la fila viene corta
        $row = array_replace(array_fill(0, 272, null), $row);

        $usuario_activo = Auth::id();

        // ---------- Helpers rápidos ----------
        $clean = function ($v) {
            if ($v === null) return null;
            $v = is_string($v) ? trim($v) : $v;
            if ($v === '' || strtoupper((string)$v) === 'NONE') return null;
            return $v;
        };

        $excelDateToYmd = function ($v) {
            if ($v === null || $v === '') return null;

            // Si ya viene como string tipo 2025-01-31 o 31/01/2025, intenta parsear
            if (is_string($v)) {
                $vv = trim($v);
                if ($vv === '' || strtoupper($vv) === 'NONE') return null;

                try {
                    return Carbon::parse($vv)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }

            // Si viene como número excel
            try {
                return Date::excelToDateTimeObject($v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        // ---------- Datos base ----------
        $tipo_identifi   = $clean((string)($row[1] ?? null));
        $numero_identifi = $clean(isset($row[2]) ? (string)$row[2] : null);

        // Si viene vacía la identificación, no hagas nada
        if (!$tipo_identifi || !$numero_identifi) {
            $this->errores[] = "Fila sin identificación (tipo o número vacío).";
            $this->guardar = false;
            return null;
        }

        // Fechas (robustas)
        $fechaatencion      = $excelDateToYmd($row[0]);
        $fechaNacimiento    = $excelDateToYmd($row[7]);
        $fechaProbParto     = $excelDateToYmd($row[46]);
        $fechaAntecedente   = $excelDateToYmd($row[48]);

        // Campos finales
        $responsable          = $clean($row[251]);
        $fuen_ingresado_paiweb= $clean($row[252]);
        $motivo_noingreso     = $clean($row[253]);
        $observaciones        = $clean($row[254]);

        // ---------- Validación rápida ----------
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

        // ---------- Consulta DB externa con CACHE ----------
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

        // ---------- Resolver afiliado local por CACHE ----------
        if (!isset($this->afiliadoIdCache[$numero_carnet])) {
            $local = Afiliado::select('id', 'numero_carnet')
                ->where('numero_carnet', $numero_carnet)
                ->first();

            $this->afiliadoIdCache[$numero_carnet] = $local ? (int)$local->id : 0;
        }

        $afiliado_id_local = $this->afiliadoIdCache[$numero_carnet];

        // ---------- Armar datos afiliado (solo si no existe) ----------
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

                'user_id' => $usuario_activo,
                'batch_verifications_id' => $this->batch_verifications_id,

                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];

            $vacunasData = $this->extraerVacunas(
                $row,
                $fechaatencion,
                $responsable,
                $fuen_ingresado_paiweb,
                $motivo_noingreso,
                $observaciones,
                $usuario_activo
            );

            $this->filasParaGuardar[] = [
                'afiliado' => $afiliadoData,
                'vacunas' => $vacunasData,
                'existe' => false,
            ];

        } else {

            // Afiliado existe, solo vacunas
            $vacunasData = $this->extraerVacunas(
                $row,
                $fechaatencion,
                $responsable,
                $fuen_ingresado_paiweb,
                $motivo_noingreso,
                $observaciones,
                $usuario_activo
            );

            $this->filasParaGuardar[] = [
                'afiliado' => ['numero_carnet' => $numero_carnet],
                'vacunas' => $vacunasData,
                'existe' => true,
            ];
        }

        return null;
    }

    /**
     * EXTRAER VACUNAS (OPTIMIZADO 1:1)
     * - Sin barrer 75..250.
     * - Revisa solo el “gatillo” de cada bloque.
     * - Extrae exactamente las mismas columnas que tu lógica anterior.
     */
    private function extraerVacunas(
        array $row,
        ?string $fechaatencion,
        $responsable,
        $fuen_ingresado_paiweb,
        $motivo_noingreso,
        $observaciones,
        $usuario_activo
    ): array {

        $cellHasValue = function ($v) {
            if ($v === null) return false;
            if (is_string($v)) {
                $t = trim($v);
                return $t !== '' && strtoupper($t) !== 'NONE';
            }
            return $v !== '';
        };

        // Cada vacuna: [vacunas_id, triggerIndex]
        // triggerIndex = columna que determina si ese bloque se considera diligenciado.
        $triggers = [
            1=>75,  2=>81,  3=>87,  4=>92,  5=>97,  6=>100, 7=>105, 8=>109, 9=>113, 10=>117,
            11=>121, 12=>124, 13=>128, 14=>133, 15=>138, 16=>143, 17=>147, 18=>152, 19=>156, 20=>160,
            21=>165, 22=>169, 23=>175, 24=>177, 25=>182, 26=>186, 27=>190, 28=>195, 29=>197, 30=>199,
            31=>201, 32=>203, 33=>205, 34=>207, 35=>209, 36=>211, 37=>213, 38=>215, 39=>217, 40=>219,
            41=>221, 42=>223, 43=>225, 44=>227, 45=>229, 46=>231, 47=>233, 48=>236, 49=>238, 50=>241,
            51=>243, 52=>245, 53=>247, 54=>249,
        ];

        $vacunas = [];

        foreach ($triggers as $vacunaNombre => $trigger) {

            // Caso especial vacuna 12: puede venir el tipo_neumococo (123) aunque docis (124) venga vacío
            if ($vacunaNombre === 12) {
                if (!$cellHasValue($row[123] ?? null) && !$cellHasValue($row[124] ?? null)) {
                    continue;
                }
            } else {
                if (!$cellHasValue($row[$trigger] ?? null)) {
                    continue;
                }
            }

            $docis = $laboratorio = $lote = $jeringa = $lote_jeringa = $diluyente = $lote_diluyente =
            $observacion = $gotero = $tipo_neumococo = $num_frascos_utilizados = null;

            // === 1:1 con tu lógica anterior ===
            switch ($vacunaNombre) {

                case 1:
                    $docis = isset($row[75]) ? trim((string)$row[75]) : null;
                    $laboratorio = $row[76] ?? null;
                    $lote = $row[77] ?? null;
                    $jeringa = $row[78] ?? null;
                    $lote_jeringa = $row[79] ?? null;
                    $diluyente = $row[80] ?? null;
                    break;

                case 2:
                    $docis = isset($row[81]) ? trim((string)$row[81]) : null;
                    $lote = $row[82] ?? null;
                    $jeringa = $row[83] ?? null;
                    $lote_jeringa = $row[84] ?? null;
                    $lote_diluyente = $row[85] ?? null;
                    $observacion = $row[86] ?? null;
                    break;

                case 3:
                    $docis = isset($row[87]) ? trim((string)$row[87]) : null;
                    $lote = $row[88] ?? null;
                    $jeringa = $row[89] ?? null;
                    $lote_jeringa = $row[90] ?? null;
                    $observacion = $row[91] ?? null;
                    break;

                case 4:
                    $docis = isset($row[92]) ? trim((string)$row[92]) : null;
                    $lote = $row[93] ?? null;
                    $jeringa = $row[94] ?? null;
                    $lote_jeringa = $row[95] ?? null;
                    $observacion = $row[96] ?? null;
                    break;

                case 5:
                    $docis = isset($row[97]) ? trim((string)$row[97]) : null;
                    $lote = $row[98] ?? null;
                    $gotero = $row[99] ?? null;
                    break;

                case 6:
                    $docis = isset($row[100]) ? trim((string)$row[100]) : null;
                    $lote = $row[101] ?? null;
                    $jeringa = $row[102] ?? null;
                    $lote_jeringa = $row[103] ?? null;
                    $observacion = $row[104] ?? null;
                    break;

                case 7:
                    $docis = isset($row[105]) ? trim((string)$row[105]) : null;
                    $lote = $row[106] ?? null;
                    $jeringa = $row[107] ?? null;
                    $lote_jeringa = $row[108] ?? null;
                    break;

                case 8:
                    $docis = isset($row[109]) ? trim((string)$row[109]) : null;
                    $lote = $row[110] ?? null;
                    $jeringa = $row[111] ?? null;
                    $lote_jeringa = $row[112] ?? null;
                    break;

                case 9:
                    $docis = isset($row[113]) ? trim((string)$row[113]) : null;
                    $lote = $row[114] ?? null;
                    $jeringa = $row[115] ?? null;
                    $lote_jeringa = $row[116] ?? null;
                    break;

                case 10:
                    $docis = isset($row[117]) ? trim((string)$row[117]) : null;
                    $lote = $row[118] ?? null;
                    $jeringa = $row[119] ?? null;
                    $lote_jeringa = $row[120] ?? null;
                    break;

                case 11:
                    $docis = isset($row[121]) ? trim((string)$row[121]) : null;
                    $lote = $row[122] ?? null;
                    break;

                case 12:
                    $tipo_neumococo = isset($row[123]) ? trim((string)$row[123]) : null;
                    $docis = $row[124] ?? null;
                    $lote = $row[125] ?? null;
                    $jeringa = $row[126] ?? null;
                    $lote_jeringa = $row[127] ?? null;
                    break;

                case 13:
                    $docis = isset($row[128]) ? trim((string)$row[128]) : null;
                    $lote = $row[129] ?? null;
                    $jeringa = $row[130] ?? null;
                    $lote_jeringa = $row[131] ?? null;
                    $lote_diluyente = $row[132] ?? null;
                    break;

                case 14:
                    $docis = isset($row[133]) ? trim((string)$row[133]) : null;
                    $lote = $row[134] ?? null;
                    $jeringa = $row[135] ?? null;
                    $lote_jeringa = $row[136] ?? null;
                    $lote_diluyente = $row[137] ?? null;
                    break;

                case 15:
                    $docis = isset($row[138]) ? trim((string)$row[138]) : null;
                    $lote = $row[139] ?? null;
                    $jeringa = $row[140] ?? null;
                    $lote_jeringa = $row[141] ?? null;
                    $lote_diluyente = $row[142] ?? null;
                    break;

                case 16:
                    $docis = isset($row[143]) ? trim((string)$row[143]) : null;
                    $lote = $row[144] ?? null;
                    $jeringa = $row[145] ?? null;
                    $lote_jeringa = $row[146] ?? null;
                    break;

                case 17:
                    $docis = isset($row[147]) ? trim((string)$row[147]) : null;
                    $lote = $row[148] ?? null;
                    $jeringa = $row[149] ?? null;
                    $lote_jeringa = $row[150] ?? null;
                    $lote_diluyente = $row[151] ?? null;
                    break;

                case 18:
                    $docis = isset($row[152]) ? trim((string)$row[152]) : null;
                    $lote = $row[153] ?? null;
                    $jeringa = $row[154] ?? null;
                    $lote_jeringa = $row[155] ?? null;
                    break;

                case 19:
                    $docis = isset($row[156]) ? trim((string)$row[156]) : null;
                    $lote = $row[157] ?? null;
                    $jeringa = $row[158] ?? null;
                    $lote_jeringa = $row[159] ?? null;
                    break;

                case 20:
                    $docis = isset($row[160]) ? trim((string)$row[160]) : null;
                    $lote = $row[161] ?? null;
                    $jeringa = $row[162] ?? null;
                    $lote_jeringa = $row[163] ?? null;
                    $observacion = $row[164] ?? null;
                    break;

                case 21:
                    $docis = isset($row[165]) ? trim((string)$row[165]) : null;
                    $lote = $row[166] ?? null;
                    $jeringa = $row[167] ?? null;
                    $lote_jeringa = $row[168] ?? null;
                    break;

                case 22:
                    $docis = isset($row[169]) ? trim((string)$row[169]) : null;
                    $lote = $row[170] ?? null;
                    $jeringa = $row[171] ?? null;
                    $lote_jeringa = $row[172] ?? null;
                    $lote_diluyente = $row[173] ?? null;
                    $observacion = $row[174] ?? null;
                    break;

                case 23:
                    $num_frascos_utilizados = $row[175] ?? null;
                    $lote = $row[176] ?? null;
                    break;

                case 24:
                    $num_frascos_utilizados = $row[177] ?? null;
                    $lote = $row[178] ?? null;
                    $jeringa = $row[179] ?? null;
                    $lote_jeringa = $row[180] ?? null;
                    $observacion = $row[181] ?? null;
                    break;

                case 25:
                    $num_frascos_utilizados = $row[182] ?? null;
                    $lote = $row[183] ?? null;
                    $jeringa = $row[184] ?? null;
                    $lote_jeringa = $row[185] ?? null;
                    break;

                case 26:
                    $num_frascos_utilizados = $row[186] ?? null;
                    $lote = $row[187] ?? null;
                    $jeringa = $row[188] ?? null;
                    $lote_jeringa = $row[189] ?? null;
                    break;

                case 27:
                    $docis = isset($row[190]) ? trim((string)$row[190]) : null;
                    $lote = $row[191] ?? null;
                    $jeringa = $row[192] ?? null;
                    $lote_jeringa = $row[193] ?? null;
                    $lote_diluyente = $row[194] ?? null;
                    break;

                case 28:
                    $docis = isset($row[195]) ? trim((string)$row[195]) : null;
                    $lote = $row[196] ?? null;
                    break;

                case 29:
                    $docis = isset($row[197]) ? trim((string)$row[197]) : null;
                    $lote = $row[198] ?? null;
                    break;

                case 30:
                    $docis = isset($row[199]) ? trim((string)$row[199]) : null;
                    $lote = $row[200] ?? null;
                    break;

                case 31:
                    $docis = isset($row[201]) ? trim((string)$row[201]) : null;
                    $lote = $row[202] ?? null;
                    break;

                case 32:
                    $docis = isset($row[203]) ? trim((string)$row[203]) : null;
                    $lote = $row[204] ?? null;
                    break;

                case 33:
                    $docis = isset($row[205]) ? trim((string)$row[205]) : null;
                    $lote = $row[206] ?? null;
                    break;

                case 34:
                    $docis = isset($row[207]) ? trim((string)$row[207]) : null;
                    $lote = $row[208] ?? null;
                    break;

                case 35:
                    $docis = isset($row[209]) ? trim((string)$row[209]) : null;
                    $lote = $row[210] ?? null;
                    break;

                case 36:
                    $docis = isset($row[211]) ? trim((string)$row[211]) : null;
                    $lote = $row[212] ?? null;
                    break;

                case 37:
                    $docis = isset($row[213]) ? trim((string)$row[213]) : null;
                    $lote = $row[214] ?? null;
                    break;

                case 38:
                    $docis = isset($row[215]) ? trim((string)$row[215]) : null;
                    $lote = $row[216] ?? null;
                    break;

                case 39:
                    $docis = isset($row[217]) ? trim((string)$row[217]) : null;
                    $lote = $row[218] ?? null;
                    break;

                case 40:
                    $docis = isset($row[219]) ? trim((string)$row[219]) : null;
                    $lote = $row[220] ?? null;
                    break;

                case 41:
                    $docis = isset($row[221]) ? trim((string)$row[221]) : null;
                    $lote = $row[222] ?? null;
                    break;

                case 42:
                    $docis = isset($row[223]) ? trim((string)$row[223]) : null;
                    $lote = $row[224] ?? null;
                    break;

                case 43:
                    $docis = isset($row[225]) ? trim((string)$row[225]) : null;
                    $lote = $row[226] ?? null;
                    break;

                case 44:
                    $docis = isset($row[227]) ? trim((string)$row[227]) : null;
                    $lote = $row[228] ?? null;
                    break;

                case 45:
                    $docis = isset($row[229]) ? trim((string)$row[229]) : null;
                    $lote = $row[230] ?? null;
                    break;

                case 46:
                    $docis = isset($row[231]) ? trim((string)$row[231]) : null;
                    $lote = $row[232] ?? null;
                    break;

                case 47:
                    $docis = isset($row[233]) ? trim((string)$row[233]) : null;
                    $lote = $row[234] ?? null;
                    $observacion = $row[235] ?? null;
                    break;

                case 48:
                    $num_frascos_utilizados = $row[236] ?? null;
                    $lote = $row[237] ?? null;
                    break;

                case 49:
                    $num_frascos_utilizados = $row[238] ?? null;
                    $lote = $row[239] ?? null;
                    $observacion = $row[240] ?? null;
                    break;

                case 50:
                    $num_frascos_utilizados = $row[241] ?? null;
                    $lote = $row[242] ?? null;
                    break;

                case 51:
                    $num_frascos_utilizados = $row[243] ?? null;
                    $lote = $row[244] ?? null;
                    break;

                case 52:
                    $docis = isset($row[245]) ? trim((string)$row[245]) : null;
                    $lote = $row[246] ?? null;
                    break;

                case 53:
                    $docis = isset($row[247]) ? trim((string)$row[247]) : null;
                    $lote = $row[248] ?? null;
                    break;

                case 54:
                    $docis = isset($row[249]) ? trim((string)$row[249]) : null;
                    $lote = $row[250] ?? null;
                    break;
            }

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
                'user_id' => $usuario_activo ?? null,
                'batch_verifications_id' => $this->batch_verifications_id,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
        }

        return $vacunas;
    }

    public function getErrores()
    {
        return $this->errores;
    }

    public function debeGuardar()
    {
        return $this->guardar;
    }

    public function getFilasParaGuardar()
    {
        return $this->filasParaGuardar;
    }

    public function getBatchVerificationsID()
    {
        return $this->batch_verifications_id;
    }
}
