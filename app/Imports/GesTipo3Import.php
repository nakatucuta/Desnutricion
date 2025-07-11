<?php

namespace App\Imports;

use App\Models\GesTipo1;
use App\Models\GesTipo3;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Exception;
use App\Models\batch_verifications;
use Illuminate\Support\Facades\Auth;  

class GesTipo3Import implements ToCollection, WithStartRow
{

     private $batch_verifications_id; // Almacena el ID único para esta importación

    public function __construct()
    {
        // Creando una única instancia de Batch_verification al inicio de la importación
        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->save();

        // Almacenar el ID para su uso posterior
        $this->batch_verifications_id = $verificacion->id;
    }



    public function startRow(): int
    {
        return 2; // arrancamos en la fila 2
    }

    public function collection(Collection $rows)
    {
        $toInsert = [];
        $sinPadre = [];

        foreach ($rows as $row) {
            // ————————— Fechas obligatorias y opcionales —————————
            $fechaTec    = $this->parseDate($row[4]);
            $fechaPost   = $this->optionalDate($row[13]);
            $fechaSalida = $this->optionalDate($row[15]);
            $fechaTerm   = $this->optionalDate($row[16]);

            // ————————— Buscar el registro padre en ges_tipo1 —————————
            $tipoIdent = trim((string)$row[2]);
            $noId      = trim((string)$row[3]);

            $padre = GesTipo1::where('tipo_de_identificacion_de_la_usuaria', $tipoIdent)
                             ->where('no_id_del_usuario', $noId)
                             ->first();

            if (! $padre) {
                $sinPadre[] = "{$tipoIdent}-{$noId}";
                continue;
            }

            // ————————— Bools de suministro (cast a bool|null) —————————
            $asa   = isset($row[9])  ? ((int)$row[9]  > 0) : null;
            $fol   = isset($row[10]) ? ((int)$row[10] > 0) : null;
            $fer   = isset($row[11]) ? ((int)$row[11] > 0) : null;
            $calc  = isset($row[12]) ? ((int)$row[12] > 0) : null;
            $metPo = isset($row[14]) ? ((int)$row[14] > 0) : null;

            // —————— Acumular usando EXACTAMENTE los nombres de columna de la migración ——————
            $toInsert[] = [
                'ges_tipo1_id'   => $padre->id,
                'tipo_de_registro'   => $row[0] ?? null,
                'consecutivo_de_registro' => is_numeric($row[1]) ? (int)$row[1] : null,
                'tipo_identificacion_de_la_usuaria' => $tipoIdent,
                'no_id_del_usuario' => $noId,
                'fecha_tecnologia_en_salud' => $fechaTec,
                'codigo_cups_de_la_tecnologia_en_salud' => $row[5] ?? null,
                'finalidad_de_la_tecnologia_en_salud' => $row[6] ?? null,
                'clasificacion_riesgo_gestacional'    => is_numeric($row[7]) ? (int)$row[7] : null,
                'clasificacion_riesgo_preeclampsia'   => is_numeric($row[8]) ? (int)$row[8] : null,
                'suministro_acido_acetilsalicilico_ASA' => $asa,
                'suministro_acido_folico_en_el_control_prenatal' => $fol,
                'suministro_sulfato_ferroso_en_el_control_prenatal' => $fer,
                'suministro_calcio_en_el_control_prenatal' => $calc,
                'fecha_suministro_de_anticonceptivo_post_evento_obstetrico' => $fechaPost,
                'suministro_metodo_anticonceptivo_post_evento_obstetrico'  => $metPo,
                'fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea' => $fechaSalida,
                'fecha_de_terminacion_de_la_gestacion' => $fechaTerm,
                'tipo_de_terminacion_de_la_gestacion' => is_numeric($row[17]) ? (int)$row[17] : null,
                'tension_arterial_sistolica_PAS_mmHg' => is_numeric($row[18]) ? (int)$row[18] : null,
                'tension_arterial_diastolica_PAD_mmHg' => is_numeric($row[19]) ? (int)$row[19] : null,
                'indice_de_masa_corporal' => is_numeric($row[20]) ? (float)$row[20] : null,
                'resultado_de_la_hemoglobina' => is_numeric($row[21]) ? (float)$row[21] : null,
                'indice_de_pulsatilidad_de_arterias_uterinas' => is_numeric($row[22]) ? (float)$row[22] : null,
                'batch_verifications_id' => $this->batch_verifications_id ?? null,
                'user_id'   => Auth::id(),
            ];
        }

        if (! empty($sinPadre)) {
            throw new Exception('Abortado: sin padre en ges_tipo1 para los identificadores: ' 
                                . implode(', ', $sinPadre));
        }

        // ————————— Guardar todo en bloque —————————
        foreach ($toInsert as $data) {
            GesTipo3::create($data);
        }
    }

    /**
     * Convierte Excel-date o string a Y-m-d
     */
    private function parseDate($value): string
    {
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        return Carbon::parse($value)->format('Y-m-d');
    }

    /**
     * Igual que parseDate pero admite nulls
     */
    private function optionalDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        return Carbon::parse($value)->format('Y-m-d');
    }
}
