<?php
namespace App\Imports;

use App\Models\batch_verifications;
use App\Models\afiliado;
use App\Models\vacuna;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AfiliadoImport implements ToModel, WithStartRow
{
    private $batch_verifications_id;

    public function __construct()
    {
        $verificacion = batch_verifications::create(['fecha_cargue' => Carbon::now()]);
        $this->batch_verifications_id = $verificacion->id;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function model(array $row)
    {
        $data = $this->parseData($row);
        $this->validateData($data);
        
        $afiliado = afiliado::updateOrCreate(
            ['numero_identificacion' => $data['numero_identificacion']],
            $data
        );
        
        if ($this->hasVacunas($row)) {
            $this->processVacunas($afiliado->id, $row);
        }

        Log::info('Afiliado guardado con éxito:', $afiliado->toArray());
        return $afiliado;
    }

    private function parseData(array $row)
    {
        return [
            'fecha_atencion' => $this->parseDate($row[0]),
            'tipo_identificacion' => $row[1] ?? null,
            'numero_identificacion' => isset($row[2]) ? (string)$row[2] : null,
            'primer_nombre' => $row[3] ?? null,
            'segundo_nombre' => $row[4] ?? null,
            'primer_apellido' => $row[5] ?? null,
            'segundo_apellido' => $row[6] ?? null,
            'fecha_nacimiento' => $this->parseDate($row[7]),
            'edad_anos' => $row[8] ?? null,
            'edad_meses' => $row[9] ?? null,
            'edad_dias' => $row[10] ?? null,
            'total_meses' => $row[11] ?? null,
            'esquema_completo' => $row[12] ?? null,
            'sexo' => $row[13] ?? null,
            'genero' => $row[14] ?? null,
            'orientacion_sexual' => $row[15] ?? null,
            'edad_gestacional' => $row[16] ?? null,
            'pais_nacimiento' => $row[17] ?? null,
            'estatus_migratorio' => $row[18] ?? null,
            'lugar_atencion_parto' => $row[19] ?? null,
            'regimen' => $row[20] ?? null,
            'aseguradora' => $row[21] ?? null,
            'pertenencia_etnica' => $row[22] ?? null,
            'desplazado' => $row[23] ?? null,
            'discapacitado' => $row[24] ?? null,
            'fallecido' => $row[25] ?? null,
            'victima_conflicto' => $row[26] ?? null,
            'estudia' => $row[27] ?? null,
            'pais_residencia' => $row[28] ?? null,
            'departamento_residencia' => $row[29] ?? null,
            'municipio_residencia' => $row[30] ?? null,
            'comuna' => $row[31] ?? null,
            'area' => $row[32] ?? null,
            'direccion' => $row[33] ?? null,
            'telefono_fijo' => $row[34] ?? null,
            'celular' => $row[35] ?? null,
            'email' => $row[36] ?? null,
            'autoriza_llamadas' => $row[37] ?? null,
            'autoriza_correos' => $row[38] ?? null,
            'contraindicacion_vacuna' => $row[39] ?? null,
            'enfermedad_contraindicacion' => $row[40] ?? null,
            'reaccion_biologicos' => $row[41] ?? null,
            'sintomas_reaccion' => $row[42] ?? null,
            'condicion_usuaria' => $row[43] ?? null,
            'fecha_ultima_menstruacion' => $this->parseDate($row[44]),
            'semanas_gestacion' => $row[45] ?? null,
            'fecha_prob_parto' => $this->parseDate($row[46]),
            'embarazos_previos' => $row[47] ?? null,
            'fecha_antecedente' => $this->parseDate($row[48]),
            'tipo_antecedente' => $row[49] ?? null,
            'descripcion_antecedente' => $row[50] ?? null,
            'observaciones_especiales' => $row[51] ?? null,
            'madre_tipo_identificacion' => $row[52] ?? null,
            'madre_identificacion' => $row[53] ?? null,
            'madre_primer_nombre' => $row[54] ?? null,
            'madre_segundo_nombre' => $row[55] ?? null,
            'madre_primer_apellido' => $row[56] ?? null,
            'madre_segundo_apellido' => $row[57] ?? null,
            'madre_correo' => $row[58] ?? null,
            'madre_telefono' => $row[59] ?? null,
            'madre_celular' => $row[60] ?? null,
            'madre_regimen' => $row[61] ?? null,
            'madre_pertenencia_etnica' => $row[62] ?? null,
            'madre_desplazada' => $row[63] ?? null,
            'cuidador_tipo_identificacion' => $row[64] ?? null,
            'cuidador_identificacion' => $row[65] ?? null,
            'cuidador_primer_nombre' => $row[66] ?? null,
            'cuidador_segundo_nombre' => $row[67] ?? null,
            'cuidador_primer_apellido' => $row[68] ?? null,
            'cuidador_segundo_apellido' => $row[69] ?? null,
            'cuidador_parentesco' => $row[70] ?? null,
            'cuidador_correo' => $row[71] ?? null,
            'cuidador_telefono' => $row[72] ?? null,
            'cuidador_celular' => $row[73] ?? null,
            'esquema_vacunacion' => $row[74] ?? null,
            'user_id' => Auth::id(),
            'batch_verifications_id' => $this->batch_verifications_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    private function validateData(array $data)
    {
        $rules = [
            'fecha_atencion' => 'nullable|date',
            'tipo_identificacion' => 'nullable|string',
            'numero_identificacion' => 'nullable|string',
            'primer_nombre' => 'nullable|string',
            'primer_apellido' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'edad_anos' => 'nullable|integer',
            'sexo' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            Log::error('Errores de validación:', $validator->errors()->toArray());
            throw new ValidationException($validator);
        }
    }

    private function hasVacunas(array $row)
    {
        return !empty(array_filter(array_slice($row, 75, 180), fn($value) => !empty(trim($value))));
    }

    private function processVacunas($afiliadoId, array $row)
    {
        $vacunasData = $this->getVacunasData($row);
        foreach ($vacunasData as $vacunaData) {
            $this->storeVacuna($afiliadoId, $vacunaData);
        }
    }

    private function getVacunasData(array $row)
    {
        $vacunasConfig = [
            ['name' => 'Covid 19', 'columns' => range(75, 80)],
            ['name' => 'BCG', 'columns' => range(81, 86)],
            ['name' => 'HEPATITIS B', 'columns' => range(87, 91)],
            ['name' => 'POLIO INACTIVADO', 'columns' => range(92, 96)],
            ['name' => 'POLIO ORAL', 'columns' => range(97, 99)],
            ['name' => 'PENTAVALENTE', 'columns' => range(100, 104)],
            ['name' => 'HEXAVALENTE', 'columns' => range(105, 108)],
            ['name' => 'DIFTERIA, TOS FERINA Y TÉTANOS - DPT', 'columns' => range(109, 112)],
            ['name' => 'DTPA PEDIÁTRICO', 'columns' => range(113, 116)],
            ['name' => 'TD PEDIÁTRICO', 'columns' => range(117, 120)],
            ['name' => 'ROTAVIRUS (VACUNA ORAL)', 'columns' => range(121, 122)],
            ['name' => 'NEUMOCOCO', 'columns' => range(123, 127)],
            ['name' => 'TRIPLE VIRAL - SRP', 'columns' => range(128, 132)],
            ['name' => 'SARAMPIÓN - RUBEOLA - SR MULTIDOSIS', 'columns' => range(133, 137)],
            ['name' => 'FIEBRE AMARILLA', 'columns' => range(138, 142)],
            ['name' => 'HEPATITIS A PEDIÁTRICA', 'columns' => range(143, 146)],
            ['name' => 'VARICELA', 'columns' => range(147, 151)],
            ['name' => 'TOXOIDE TETÁNICO Y DIFTÉRICO DE ADULTO', 'columns' => range(152, 155)],
            ['name' => 'DTPA ADULTO', 'columns' => range(156, 159)],
            ['name' => 'INFLUENZA', 'columns' => range(160, 164)],
            ['name' => 'VPH', 'columns' => range(165, 168)],
            ['name' => 'ANTIRRÁBICA HUMANA (VACUNA)', 'columns' => range(169, 174)],
            ['name' => 'ANTIRRÁBICO HUMANO (SUERO)', 'columns' => range(175, 176)],
            ['name' => 'HEPATITIS B (INMUNOGLOBULINA)', 'columns' => range(177, 181)],
            ['name' => 'INMUNOGLOBULINA ANTI TETANICA (Suero homólogo)', 'columns' => range(182, 185)],
            ['name' => 'ANTI TOXINA TETANICA (Suero heterólogo)', 'columns' => range(186, 189)],
            ['name' => 'Meningococo de los serogrupos A, C, W-135 e Y', 'columns' => range(190, 194)],
            ['name' => 'HEPATITIS B', 'columns' => range(195, 196)],
            ['name' => 'PENTAVALENTE (DPaT, HiB, VPI)', 'columns' => range(197, 198)],
            ['name' => 'HEXAVALENTE (DPaT, HiB, HB, VPI)', 'columns' => range(199, 200)],
            ['name' => 'TETRAVALENTE (DPaT, VPI)', 'columns' => range(201, 202)],
            ['name' => 'DPT ACELULAR PEDIÁTRICO', 'columns' => range(203, 204)],
            ['name' => 'TOXOIDE TETÁNICO Y DIFTERICO (TD) PEDIÁTRICO', 'columns' => range(205, 206)],
            ['name' => 'ROTAVIRUS', 'columns' => range(207, 208)],
            ['name' => 'NEUMOCOCO CONJUGADA', 'columns' => range(209, 210)],
            ['name' => 'NEUMO POLISACARIDO', 'columns' => range(211, 212)],
            ['name' => 'TRIPLE VIRAL', 'columns' => range(213, 214)],
            ['name' => 'VARICELA + TRIPLE VIRAL', 'columns' => range(215, 216)],
            ['name' => 'FIEBRE AMARILLA', 'columns' => range(217, 218)],
            ['name' => 'HEPATITIS A', 'columns' => range(219, 220)],
            ['name' => 'HEPATITIS A, HEPATITIS B', 'columns' => range(221, 222)],
            ['name' => 'VARICELA', 'columns' => range(223, 224)],
            ['name' => 'TOXOIDE TETÁNICO/DIFTERICO ADULTOS', 'columns' => range(225, 226)],
            ['name' => 'DPT ACELULAR ADULTO', 'columns' => range(227, 228)],
            ['name' => 'INFLUENZA', 'columns' => range(229, 230)],
            ['name' => 'VPH', 'columns' => range(231, 232)],
            ['name' => 'ANTIRRÁBICA PROFILÁCTICA', 'columns' => range(233, 235)],
            ['name' => 'INMUNOGLOBULINA ANTI TETANICA (Suero homólogo)', 'columns' => range(236, 237)],
            ['name' => 'INMUNOGLOBULINA ANTI HEPATITIS B', 'columns' => range(238, 240)],
            ['name' => 'ANTI TOXINA TETANICA (Suero heterólogo)', 'columns' => range(243, 244)],
            ['name' => 'MENINGOCOCO CONJUGADO', 'columns' => range(245, 246)],
            ['name' => 'FIEBRE TIFOIDEA', 'columns' => range(247, 248)],
            ['name' => 'HERPES ZOSTER', 'columns' => range(249, 250)],
        ];
        

        $vacunas = [];
        foreach ($vacunasConfig as $config) {
            $data = array_intersect_key($row, array_flip($config['columns']));
            if ($this->isVacunaFilled($data)) {
                $vacunas[] = array_merge(['nombre' => $config['name']], $data);
            }
        }
        return $vacunas;
    }

    private function storeVacuna($afiliadoId, array $vacunaData)
    {
        vacuna::create([
            'afiliado_id' => $afiliadoId,
            'nombre' => $vacunaData['nombre'],
            'docis' => $vacunaData['docis'] ?? null,
            'laboratorio' => $vacunaData['laboratorio'] ?? null,
            'lote' => $vacunaData['lote'] ?? null,
            'jeringa' => $vacunaData['jeringa'] ?? null,
            'lote_jeringa' => $vacunaData['lote_jeringa'] ?? null,
            'diluyente' => $vacunaData['diluyente'] ?? null,
            'lote_diluyente' => $vacunaData['lote_diluyente'] ?? null,
            'observacion' => $vacunaData['observacion'] ?? null,
            'gotero' => $vacunaData['gotero'] ?? null,
            'num_frascos_utilizados'=> $vacunaData['num_frascos_utilizados'] ?? null,
            'tipo_neumococo' => $vacunaData['tipo_neumococo'] ?? null,
            'fecha_vacuna' => Carbon::now()->format('Y-m-d'),
            'responsable' => $vacunaData['responsable'] ?? null,
            'fuen_ingresado_paiweb' => $vacunaData['fuen_ingresado_paiweb'] ?? null,
            'motivo_noingreso' => $vacunaData['motivo_noingreso'] ?? null,
            'observaciones' => $vacunaData['observaciones'] ?? null,
            'user_id' => Auth::id(),
            'batch_verifications_id' => $this->batch_verifications_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function isVacunaFilled(array $vacunaData)
    {
        return !empty(array_filter($vacunaData, fn($value) => !empty(trim($value))));
    }

    private function parseDate($date)
    {
        return isset($date) ? Date::excelToDateTimeObject($date)->format('Y-m-d') : null;
    }
}
