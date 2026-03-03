<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Cargue412AssignmentAuditExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Fecha Hora',
            'Tipo Movimiento',
            'ID Registro 412',
            'Identificacion',
            'Paciente',
            'Municipio',
            'Fecha Captacion',
            'Asignado Antes (ID)',
            'Asignado Antes (Nombre)',
            'Asignado Antes (Codigo)',
            'Asignado Nuevo (ID)',
            'Asignado Nuevo (Nombre)',
            'Asignado Nuevo (Codigo)',
            'Usuario que Asigno',
            'IP Cliente',
        ];
    }
}

