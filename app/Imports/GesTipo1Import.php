<?php

namespace App\Imports;

use App\Models\GesTipo1;
use App\Models\batch_verifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class GesTipo1Import implements OnEachRow, WithStartRow, WithChunkReading, WithEvents
{
    private int $batch_verifications_id;

    private array $buffer = [];
    private array $sinCarnet = [];
    private array $yaExistentes = [];

    private ?int $maxRowsPerInsert = null;

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

    // Tamaño del chunk de lectura del Excel (puedes subir/bajar)
    public function chunkSize(): int
    {
        return 500;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                DB::connection('sqlsrv')->disableQueryLog();
                DB::beginTransaction();
            },

            AfterImport::class => function (AfterImport $event) {
                // Inserta lo último que quede en buffer
                $this->flushBuffer();

                // Si hubo problemas, rollback y lanza error
                if (!empty($this->yaExistentes)) {
                    DB::rollBack();
                    throw new Exception(
                        "Import DETENIDO: usuarios ya existen y su FPP no ha pasado:\n\n" . implode("\n", $this->yaExistentes)
                    );
                }

                if (!empty($this->sinCarnet)) {
                    DB::rollBack();
                    throw new Exception(
                        "Import DETENIDO: usuarios sin carnet:\n\n" . implode("\n", $this->sinCarnet)
                    );
                }

                // Todo ok
                DB::commit();
            },

            ImportFailed::class => function (ImportFailed $event) {
                // Si algo falla en medio del proceso, rollback
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            },
        ];
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

    public function onRow(Row $row)
    {
        $excelRowNumber = (int) $row->getIndex(); // número real de fila en Excel
        $r = $row->toArray();

        // 1) Fechas
        $fechaN = $this->parseExcelDate($r[12] ?? null, $excelRowNumber, 'fecha_de_nacimiento');
        $fechaP = $this->parseExcelDate($r[16] ?? null, $excelRowNumber, 'fecha_probable_de_parto');

        // 2) Identificación
        $tipoIdent = trim((string)($r[6] ?? ''));
        $noId      = trim((string)($r[7] ?? ''));

        if ($tipoIdent === '' || $noId === '') {
            throw new Exception("Fila {$excelRowNumber}: tipo_identificación o no_id_del_usuario vacío.");
        }

        // 3) Si ya existe y su FPP no pasó => registrar y saltar
        if (GesTipo1::where('tipo_de_identificacion_de_la_usuaria', $tipoIdent)
            ->where('no_id_del_usuario', $noId)
            ->exists())
        {
            $hoy = Carbon::today();
            $fp  = Carbon::parse($fechaP);

            if ($hoy->lte($fp)) {
                $nombreCompleto = trim(($r[10] ?? '')." ".($r[11] ?? '')." ".($r[8] ?? '')." ".($r[9] ?? ''));
                $this->yaExistentes[] = "{$nombreCompleto} (ID: {$noId}) — fecha_probable_de_parto: {$fechaP}";
                return;
            }
        }

        // 4) Carnet
        $numeroCarnet = DB::connection('sqlsrv_1')
            ->table('maestroIdentificaciones')
            ->where('identificacion', $noId)
            ->where('tipoIdentificacion', $tipoIdent)
            ->value('numeroCarnet');

        if (!$numeroCarnet) {
            $nombreCompleto = trim(($r[10] ?? '')." ".($r[11] ?? '')." ".($r[8] ?? '')." ".($r[9] ?? ''));
            $this->sinCarnet[] = "{$nombreCompleto} (ID: {$noId})";
            return;
        }

        // 5) Armar fila
        $data = [
            'user_id'   => Auth::id(),
            'tipo_de_registro'                                    => $r[0] ?? null,
            'consecutivo'                                         => $r[1] ?? null,
            'pais_de_la_nacionalidad'                             => $r[2] ?? null,
            'municipio_de_residencia_habitual'                    => $r[3] ?? null,
            'zona_territorial_de_residencia'                      => $r[4] ?? null,
            'codigo_de_habilitacion_ips_primaria_de_la_gestante'  => $r[5] ?? null,
            'tipo_de_identificacion_de_la_usuaria'                => $tipoIdent,
            'no_id_del_usuario'                                   => $noId,
            'numero_carnet'                                       => $numeroCarnet,
            'primer_apellido'                                     => $r[8] ?? null,
            'segundo_apellido'                                    => $r[9] ?? null,
            'primer_nombre'                                       => $r[10] ?? null,
            'segundo_nombre'                                      => $r[11] ?? null,
            'fecha_de_nacimiento'                                 => $fechaN,
            'codigo_pertenencia_etnica'                           => $r[13] ?? null,
            'codigo_de_ocupacion'                                 => $r[14] ?? null,
            'codigo_nivel_educativo_de_la_gestante'               => $r[15] ?? null,
            'fecha_probable_de_parto'                             => $fechaP,
            'direccion_de_residencia_de_la_gestante'              => $r[17] ?? null,
            'antecedente_hipertension_cronica'                    => $r[18] ?? null,
            'antecedente_preeclampsia'                            => $r[19] ?? null,
            'antecedente_diabetes'                                => $r[20] ?? null,
            'antecedente_les_enfermedad_autoinmune'               => $r[21] ?? null,
            'antecedente_sindrome_metabolico'                     => $r[22] ?? null,
            'antecedente_erc'                                     => $r[23] ?? null,
            'antecedente_trombofilia_o_trombosis_venosa_profunda' => $r[24] ?? null,
            'antecedentes_anemia_celulas_falciformes'             => $r[25] ?? null,
            'antecedente_sepsis_durante_gestaciones_previas'      => $r[26] ?? null,
            'consumo_tabaco_durante_la_gestacion'                 => $r[27] ?? null,
            'periodo_intergenesico'                               => $r[28] ?? null,
            'embarazo_multiple'                                   => $r[29] ?? null,
            'metodo_de_concepcion'                                => $r[30] ?? null,
            'batch_verifications_id'                              => $this->batch_verifications_id,

            // timestamps por SQL Server
            'created_at' => DB::raw('GETDATE()'),
            'updated_at' => DB::raw('GETDATE()'),
        ];

        $this->buffer[] = $data;

        // Calcular maxRowsPerInsert una sola vez (límite 2100 params; usamos 2000 por margen)
        if ($this->maxRowsPerInsert === null) {
            $columnsCount = count($data); // columnas insertadas
            $this->maxRowsPerInsert = max(1, intdiv(2000, max(1, $columnsCount)));
        }

        // Cuando llegue al tope, insert y vaciar
        if (count($this->buffer) >= $this->maxRowsPerInsert) {
            $this->flushBuffer();
        }
    }

    private function flushBuffer(): void
    {
        if (empty($this->buffer)) return;

        DB::table('ges_tipo1')->insert($this->buffer);
        $this->buffer = [];
    }
}
