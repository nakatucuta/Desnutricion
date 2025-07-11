<?php

namespace App\Imports;

use App\Models\GesTipo1;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;           // ← para obtener el user_id
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;                             // ← para comparar fechas
use Exception;
use App\Models\batch_verifications;
class GesTipo1Import implements ToCollection, WithStartRow
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
        return 2; // saltamos la fila de encabezados
    }

    public function collection(Collection $rows)
    {
        $toInsert     = [];
        $sinCarnet    = [];
        $yaExistentes = [];

        foreach ($rows as $row) {
            // 1) Convertir fechas Excel a Y-m-d
            $fechaN = $row[12];
            if (is_numeric($fechaN)) {
                $fechaN = Date::excelToDateTimeObject($fechaN)->format('Y-m-d');
            }
            $fechaP = $row[16];
            if (is_numeric($fechaP)) {
                $fechaP = Date::excelToDateTimeObject($fechaP)->format('Y-m-d');
            }

            // 2) Forzar identificaciones como strings
            $tipoIdent = trim((string) $row[6]);
            $noId      = trim((string) $row[7]);

            // 3) Validación: si YA existe en ges_tipo1
            if ($existing = GesTipo1::where('tipo_de_identificacion_de_la_usuaria', $tipoIdent)
                                    ->where('no_id_del_usuario', $noId)
                                    ->first())
            {
                // Comparamos la FECHA ACTUAL con fecha_probable_de_parto
                $hoy = Carbon::today();
                $fp  = Carbon::parse($fechaP);

                if ($hoy->gt($fp)) {
                    // La fecha probable de parto ya pasó → permitimos reinsertar
                } else {
                    // Aún no pasa → NO permitimos e informamos
                    $nombreCompleto = trim("{$row[10]} {$row[11]} {$row[8]} {$row[9]}");
                    $yaExistentes[] = "{$nombreCompleto} (ID: {$noId}) — fecha_probable_de_parto: {$fechaP}";
                    continue;
                }
            }

            // 4) Buscar numeroCarnet en sqlsrv_1
            $numeroCarnet = DB::connection('sqlsrv_1')
                ->table('maestroIdentificaciones')
                ->where('identificacion',    $noId)
                ->where('tipoIdentificacion', $tipoIdent)
                ->value('numeroCarnet');

            // 5) Si no tiene carnet, acumular y saltar
            if (! $numeroCarnet) {
                $nombreCompleto = trim("{$row[10]} {$row[11]} {$row[8]} {$row[9]}");
                $sinCarnet[]    = "{$nombreCompleto} (ID: {$noId})";
                continue;
            }

            // 6) Preparar datos para inserción (incluyendo user_id)
            $toInsert[] = [
                'user_id'   => Auth::id(),
                'tipo_de_registro'                                    => $row[0],
                'consecutivo'                                         => $row[1],
                'pais_de_la_nacionalidad'                             => $row[2],
                'municipio_de_residencia_habitual'                    => $row[3],
                'zona_territorial_de_residencia'                      => $row[4],
                'codigo_de_habilitacion_ips_primaria_de_la_gestante'  => $row[5],
                'tipo_de_identificacion_de_la_usuaria'                => $tipoIdent,
                'no_id_del_usuario'                                   => $noId,
                'numero_carnet'                                       => $numeroCarnet,
                'primer_apellido'                                     => $row[8],
                'segundo_apellido'                                    => $row[9],
                'primer_nombre'                                       => $row[10],
                'segundo_nombre'                                      => $row[11],
                'fecha_de_nacimiento'                                 => $fechaN,
                'codigo_pertenencia_etnica'                           => $row[13],
                'codigo_de_ocupacion'                                 => $row[14],
                'codigo_nivel_educativo_de_la_gestante'               => $row[15],
                'fecha_probable_de_parto'                             => $fechaP,
                'direccion_de_residencia_de_la_gestante'               => $row[17],
                'antecedente_hipertension_cronica'                    => $row[18],
                'antecedente_preeclampsia'                            => $row[19],
                'antecedente_diabetes'                                => $row[20],
                'antecedente_les_enfermedad_autoinmune'               => $row[21],
                'antecedente_sindrome_metabolico'                     => $row[22],
                'antecedente_erc'                                     => $row[23],
                'antecedente_trombofilia_o_trombosis_venosa_profunda' => $row[24],
                'antecedentes_anemia_celulas_falciformes'             => $row[25],
                'antecedente_sepsis_durante_gestaciones_previas'      => $row[26],
                'consumo_tabaco_durante_la_gestacion'                 => $row[27],
                'periodo_intergenesico'                               => $row[28],
                'embarazo_multiple'                                   => $row[29],
                'metodo_de_concepcion'                                => $row[30],
                'batch_verifications_id' => $this->batch_verifications_id ?? null,
            ];
        }

        // 7) Si hay duplicados, abortar con mensaje
        if (! empty($yaExistentes)) {
            $lista = implode("\n", $yaExistentes);
            throw new Exception(
                "Import DETENIDO: los siguientes usuarios NO fueron importados porque YA EXISTEN y su fecha_probable_de_parto no ha pasado:\n\n{$lista}"
            );
        }

        // 8) Si hay usuarios sin carnet, abortar con mensaje
        if (! empty($sinCarnet)) {
            $lista = implode("\n", $sinCarnet);
            throw new Exception(
                "Import DETENIDO: los siguientes usuarios NO están afiliados o no tienen número de carnet:\n\n{$lista}"
            );
        }

        // 9) Insertar en lote
        foreach ($toInsert as $data) {
            GesTipo1::create($data);
        }
    }
}
