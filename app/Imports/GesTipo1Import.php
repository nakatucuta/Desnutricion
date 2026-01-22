<?php

namespace App\Imports;

use App\Models\GesTipo1;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Exception;
use App\Models\batch_verifications;

class GesTipo1Import implements ToCollection, WithStartRow
{
    private int $batch_verifications_id;

    public function __construct()
    {
        $verificacion = new batch_verifications([
            'fecha_cargue' => now()->format('Y-m-d H:i:s'),
        ]);
        $verificacion->save();

        $this->batch_verifications_id = (int) $verificacion->id;
    }

    public function getBatchVerificationsId(): int
    {
        return $this->batch_verifications_id;
    }

    public function startRow(): int
    {
        return 2;
    }

    private function parseExcelDate($value, int $excelRowNumber, string $campo): string
    {
        if ($value === null) {
            throw new Exception("Fecha vacía en fila {$excelRowNumber} ({$campo}).");
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || $value === '0' || strtoupper($value) === 'N/A') {
                throw new Exception("Fecha vacía en fila {$excelRowNumber} ({$campo}).");
            }
        }

        // Serial de Excel
        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$value);
                return Carbon::instance($dt)->format('Y-m-d');
            } catch (\Throwable $e) {
                throw new Exception("Fecha inválida en fila {$excelRowNumber} ({$campo}): '{$value}'");
            }
        }

        // Strings comunes
        $formats = [
            'Y-m-d', 'Y/m/d',
            'd-m-Y', 'd/m/Y',
            'm-d-Y', 'm/d/Y',
            'd-m-y', 'd/m/y',
            'm-d-y', 'm/d/y',
        ];

        foreach ($formats as $fmt) {
            try {
                $c = Carbon::createFromFormat($fmt, (string)$value);
                if ($c && $c->year >= 1753 && $c->year <= 9999) {
                    return $c->format('Y-m-d');
                }
            } catch (\Throwable $e) {}
        }

        // último intento
        try {
            $c = Carbon::parse((string)$value);
            if ($c->year >= 1753 && $c->year <= 9999) {
                return $c->format('Y-m-d');
            }
        } catch (\Throwable $e) {}

        throw new Exception("Fecha inválida en fila {$excelRowNumber} ({$campo}): '{$value}'");
    }

    public function collection(Collection $rows)
    {
        $toInsert     = [];
        $sinCarnet    = [];
        $yaExistentes = [];

        $excelRowNumber = $this->startRow();

        foreach ($rows as $row) {

            // 1) Fechas (date)
            $fechaN = $this->parseExcelDate($row[12] ?? null, $excelRowNumber, 'fecha_de_nacimiento');
            $fechaP = $this->parseExcelDate($row[16] ?? null, $excelRowNumber, 'fecha_probable_de_parto');

            // 2) Identificación
            $tipoIdent = trim((string)($row[6] ?? ''));
            $noId      = trim((string)($row[7] ?? ''));

            if ($tipoIdent === '' || $noId === '') {
                throw new Exception("Fila {$excelRowNumber}: tipo_identificación o no_id_del_usuario vacío.");
            }

            // 3) Si ya existe y su FPP no pasó => abortar al final
            if ($existing = GesTipo1::where('tipo_de_identificacion_de_la_usuaria', $tipoIdent)
                ->where('no_id_del_usuario', $noId)
                ->first())
            {
                $hoy = Carbon::today();
                $fp  = Carbon::parse($fechaP);

                if ($hoy->lte($fp)) {
                    $nombreCompleto = trim(($row[10] ?? '')." ".($row[11] ?? '')." ".($row[8] ?? '')." ".($row[9] ?? ''));
                    $yaExistentes[] = "{$nombreCompleto} (ID: {$noId}) — fecha_probable_de_parto: {$fechaP}";
                    $excelRowNumber++;
                    continue;
                }
            }

            // 4) Carnet
            $numeroCarnet = DB::connection('sqlsrv_1')
                ->table('maestroIdentificaciones')
                ->where('identificacion', $noId)
                ->where('tipoIdentificacion', $tipoIdent)
                ->value('numeroCarnet');

            if (!$numeroCarnet) {
                $nombreCompleto = trim(($row[10] ?? '')." ".($row[11] ?? '')." ".($row[8] ?? '')." ".($row[9] ?? ''));
                $sinCarnet[]    = "{$nombreCompleto} (ID: {$noId})";
                $excelRowNumber++;
                continue;
            }

            // 5) Preparar fila para INSERT directo (sin Eloquent)
            $toInsert[] = [
                'user_id'   => Auth::id(),
                'tipo_de_registro'                                    => $row[0] ?? null,
                'consecutivo'                                         => $row[1] ?? null,
                'pais_de_la_nacionalidad'                             => $row[2] ?? null,
                'municipio_de_residencia_habitual'                    => $row[3] ?? null,
                'zona_territorial_de_residencia'                      => $row[4] ?? null,
                'codigo_de_habilitacion_ips_primaria_de_la_gestante'  => $row[5] ?? null,
                'tipo_de_identificacion_de_la_usuaria'                => $tipoIdent,
                'no_id_del_usuario'                                   => $noId,
                'numero_carnet'                                       => $numeroCarnet,
                'primer_apellido'                                     => $row[8] ?? null,
                'segundo_apellido'                                    => $row[9] ?? null,
                'primer_nombre'                                       => $row[10] ?? null,
                'segundo_nombre'                                      => $row[11] ?? null,
                'fecha_de_nacimiento'                                 => $fechaN,
                'codigo_pertenencia_etnica'                           => $row[13] ?? null,
                'codigo_de_ocupacion'                                 => $row[14] ?? null,
                'codigo_nivel_educativo_de_la_gestante'               => $row[15] ?? null,
                'fecha_probable_de_parto'                             => $fechaP,
                'direccion_de_residencia_de_la_gestante'              => $row[17] ?? null,
                'antecedente_hipertension_cronica'                    => $row[18] ?? null,
                'antecedente_preeclampsia'                            => $row[19] ?? null,
                'antecedente_diabetes'                                => $row[20] ?? null,
                'antecedente_les_enfermedad_autoinmune'               => $row[21] ?? null,
                'antecedente_sindrome_metabolico'                     => $row[22] ?? null,
                'antecedente_erc'                                     => $row[23] ?? null,
                'antecedente_trombofilia_o_trombosis_venosa_profunda' => $row[24] ?? null,
                'antecedentes_anemia_celulas_falciformes'             => $row[25] ?? null,
                'antecedente_sepsis_durante_gestaciones_previas'      => $row[26] ?? null,
                'consumo_tabaco_durante_la_gestacion'                 => $row[27] ?? null,
                'periodo_intergenesico'                               => $row[28] ?? null,
                'embarazo_multiple'                                   => $row[29] ?? null,
                'metodo_de_concepcion'                                => $row[30] ?? null,
                'batch_verifications_id'                              => $this->batch_verifications_id,

                // 🔥 CLAVE: timestamps generados por SQL Server (cero nvarchar->datetime)
                'created_at' => DB::raw('GETDATE()'),
                'updated_at' => DB::raw('GETDATE()'),
            ];

            $excelRowNumber++;
        }

        if (!empty($yaExistentes)) {
            throw new Exception(
                "Import DETENIDO: usuarios ya existen y su FPP no ha pasado:\n\n" . implode("\n", $yaExistentes)
            );
        }

        if (!empty($sinCarnet)) {
            throw new Exception(
                "Import DETENIDO: usuarios sin carnet:\n\n" . implode("\n", $sinCarnet)
            );
        }

        // ✅ Insert masivo en transacción (rápido y sin problemas de formato)
        DB::transaction(function () use ($toInsert) {
            if (!empty($toInsert)) {
                DB::table('ges_tipo1')->insert($toInsert);
            }
        });
    }
}
