<?php

namespace App\Imports;

use App\Models\Preconcepcional;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class PreconcepcionalImport implements OnEachRow, WithHeadingRow, WithStartRow, WithValidation, SkipsEmptyRows
{
    private int $batchId;

    private int $rowsTotal     = 0;
    private int $rowsCreated   = 0;
    private int $rowsSkipped   = 0;
    private int $rowsDuplicate = 0; // ✅ NUEVO contador

    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function getCounters(): array
    {
        return [
            'rows_total'     => $this->rowsTotal,
            'rows_created'   => $this->rowsCreated,
            'rows_skipped'   => $this->rowsSkipped,
            'rows_duplicate' => $this->rowsDuplicate, // ✅ NUEVO
        ];
    }

    public function headingRow(): int { return 2; }
    public function startRow(): int { return 3; }

    public function prepareForValidation($data, $index)
    {
        $soloVacios = true;
        foreach ($data as $v) {
            if ($v !== null && trim((string)$v) !== '') { $soloVacios = false; break; }
        }
        return $soloVacios ? [] : $data;
    }

    public function rules(): array
    {
        return [
            'tipo_de_documento_de_identidad' => ['required'],
            'no_de_identificacion'           => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tipo_de_documento_de_identidad.required' => 'Falta "Tipo de documento de identidad".',
            'no_de_identificacion.required'           => 'Falta "No. De Identificación".',
        ];
    }

    private function clean($v)
    {
        if ($v === null) return null;

        if (is_string($v)) {
            $v = trim($v);
            $upper = strtoupper($v);
            if ($v === '' || $v === '?' || $upper === 'N/A' || $upper === 'NA') return null;
            return $v;
        }
        return $v;
    }

    private function parseDate($value): ?string
    {
        $value = $this->clean($value);
        if ($value === null) return null;

        if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->format('Y-m-d');

        if (is_numeric($value)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return Carbon::instance($dt)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = (string)$value;
        $value = preg_replace('/\s+.*/', '', $value);
        $value = preg_replace('/\s+/', '', $value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, $value)->format('Y-m-d'); } catch (\Throwable $e) {}
        }

        return null;
    }

    public function onRow(Row $row)
    {
        $this->rowsTotal++;

        $r = $row->toArray();

        $tipoDoc = $this->clean(Arr::get($r, 'tipo_de_documento_de_identidad'));
        $numId   = $this->clean(Arr::get($r, 'no_de_identificacion'));

        if (!$tipoDoc || !$numId) {
            $this->rowsSkipped++;
            return;
        }

        // ✅ contamos si YA existe (solo para métrica)
        $exists = Preconcepcional::where('tipo_documento', $tipoDoc)
            ->where('numero_identificacion', $numId)
            ->exists();

        if ($exists) $this->rowsDuplicate++;

        $resultadoSifilis = $this->clean(Arr::get($r, 'resultado'));
        $resultadoVih     = $this->clean(Arr::get($r, 'resultado_1'));
        $resultadoHepB    = $this->clean(Arr::get($r, 'resultado_2'));

        $now = now()->format('Y-m-d H:i:s');

        $data = [
            'no' => $this->clean(Arr::get($r, 'no')),
            'tipo_documento'        => $tipoDoc,
            'numero_identificacion' => $numId,

            'apellido_1' => $this->clean(Arr::get($r, 'apellido_1')),
            'apellido_2' => $this->clean(Arr::get($r, 'apellido_2')),
            'nombre_1'   => $this->clean(Arr::get($r, 'nombre_1')),
            'nombre_2'   => $this->clean(Arr::get($r, 'nombre_2')),

            'fecha_nacimiento'              => $this->parseDate(Arr::get($r, 'fecha_de_nacimiento')),
            'fecha_ultimo_periodo_mestrual' => $this->parseDate(Arr::get($r, 'fecha_del_ultimo_periodo_mestrual')),
            'fecha_tamizaje_sifilis'        => $this->parseDate(Arr::get($r, 'fecha_tamizaje_para_sifilis')),
            'fecha_tamizaje_vih'            => $this->parseDate(Arr::get($r, 'fecha_del_tamizaje_para_vih')),
            'fecha_tamizaje_hepatitis_b'    => $this->parseDate(Arr::get($r, 'fecha_del_tamizaje_para_hepatitis_b')),

            'edad' => $this->clean(Arr::get($r, 'edad_anos')),
            'sexo' => $this->clean(Arr::get($r, 'sexo')),

            'regimen_afiliacion'      => $this->clean(Arr::get($r, 'regimen_afiliacion')),
            'pertenencia_etnica'      => $this->clean(Arr::get($r, 'pertenecia_etnica')),
            'grupo_poblacional'       => $this->clean(Arr::get($r, 'grupo_poblacional')),
            'departamento_residencia' => $this->clean(Arr::get($r, 'departamento_residencia')),
            'municipio_residencia'    => $this->clean(Arr::get($r, 'municipio_de_residencia')),
            'zona'                    => $this->clean(Arr::get($r, 'zona')),
            'etnia'                   => $this->clean(Arr::get($r, 'etnia')),
            'asentamiento'            => $this->clean(Arr::get($r, 'asentamiento_rancheria_comunidad')),
            'telefono'                => $this->clean(Arr::get($r, 'telefono_usuaria')),
            'direccion'               => $this->clean(Arr::get($r, 'direccion')),
            'nivel_educativo'         => $this->clean(Arr::get($r, 'nivel_educativo')),
            'discapacidad'            => $this->clean(Arr::get($r, 'discapacidad')),
            'mujer_cabeza_hogar'      => $this->clean(Arr::get($r, 'mujer_cabeza_de_hogar')),
            'ocupacion'               => $this->clean(Arr::get($r, 'ocupacion')),
            'estado_civil'            => $this->clean(Arr::get($r, 'estado_civil')),
            'control_tradicional'     => $this->clean(Arr::get($r, 'control_tradicional')),
            'gestante_renuente'       => $this->clean(Arr::get($r, 'gestante_renuente')),
            'inasistente'             => $this->clean(Arr::get($r, 'inasistente')),
            'nombre_ips_primaria'     => $this->clean(Arr::get($r, 'nombre_de_la_ips_primaria')),

            'resultado_sifilis'     => $resultadoSifilis,
            'resultado_vih'         => $resultadoVih,
            'resultado_hepatitis_b' => $resultadoHepB,

            // ✅ tracking de lote
            'created_batch_id' => $this->batchId,
            'last_batch_id'    => $this->batchId,

            // ✅ timestamps sin ms
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // ✅ INSERT SIEMPRE (aunque esté repetido)
        Preconcepcional::withoutTimestamps(function () use ($data) {
            Preconcepcional::create($data);
        });

        $this->rowsCreated++;
    }
}
