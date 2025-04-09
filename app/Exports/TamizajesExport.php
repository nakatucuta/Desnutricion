<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TamizajesExport implements FromCollection, WithHeadings
{
    protected $tamizajes;

    public function __construct($tamizajes)
    {
        $this->tamizajes = $tamizajes;
    }

    public function collection()
    {
        // Mapear cada tamizaje a un array de columnas
        return $this->tamizajes->map(function($t){
            return [
                'ID'                 => $t->id,
                'Tipo Ident'         => $t->tipo_identificacion,
                'Número Ident'       => $t->numero_identificacion,
                'Fecha Tamizaje'     => $t->fecha_tamizaje,
                'Tipo Tamizaje'      => optional($t->tipo)->nombre,
                'Código Resultado'   => optional($t->resultado)->code,
                'Desc. Resultado'    => optional($t->resultado)->description,
                'Usuario'            => optional($t->user)->name,
                'Creado en'          => $t->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo Ident',
            'Número Ident',
            'Fecha Tamizaje',
            'Tipo Tamizaje',
            'Código Resultado',
            'Desc. Resultado',
            'Usuario',
            'Creado en'
        ];
    }
}
