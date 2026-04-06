<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CicloVidaCoverageMissingExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn (array $row) => self::mapRow($row));
    }

    public function headings(): array
    {
        return self::headingsRow();
    }

    public static function headingsRow(): array
    {
        return [
            'Curso de vida',
            'Atencion faltante',
            'Regla normativa',
            'Tipo identificacion',
            'Identificacion',
            'Primer nombre',
            'Segundo nombre',
            'Primer apellido',
            'Segundo apellido',
            'Nombre completo',
            'Fecha nacimiento',
            'Genero',
            'Estado afiliado',
            'Departamento',
            'Municipio',
            'Zona',
            'IPS responsable',
            'Esperadas',
            'Aplicadas',
            'Faltantes',
        ];
    }

    public static function mapRow(array $row): array
    {
        return [
            'course_label' => $row['course_label'] ?? '',
            'module_label' => $row['module_label'] ?? '',
            'rule_label' => $row['rule_label'] ?? '',
            'tipo_identificacion' => $row['tipo_identificacion'] ?? '',
            'identificacion' => $row['identificacion'] ?? '',
            'primer_nombre' => $row['primer_nombre'] ?? '',
            'segundo_nombre' => $row['segundo_nombre'] ?? '',
            'primer_apellido' => $row['primer_apellido'] ?? '',
            'segundo_apellido' => $row['segundo_apellido'] ?? '',
            'nombre_completo' => $row['nombre_completo'] ?? '',
            'fecha_nacimiento' => $row['fecha_nacimiento'] ?? '',
            'genero' => $row['genero'] ?? '',
            'estado_actual' => $row['estado_actual'] ?? '',
            'departamento' => $row['departamento'] ?? '',
            'municipio' => $row['municipio'] ?? '',
            'zona' => $row['zona'] ?? '',
            'ips_responsable' => $row['ips_responsable'] ?? '',
            'expected_attentions' => $row['expected_attentions'] ?? 0,
            'valid_attentions' => $row['valid_attentions'] ?? 0,
            'missing_attentions' => $row['missing_attentions'] ?? 0,
        ];
    }
}
