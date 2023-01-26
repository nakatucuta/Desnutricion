<?php

namespace App\Exports;

use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class SivigilaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents

{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Sivigila::
        select('*')
        ->where('cod_eve', 113)
        
        ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'Cod eve',
            'Semana de notificacion',
            'Ultima  fecha de notificacion',
            'Año',
            'Departamento',
            'Municipio',
            'Tipo de identificacion',
            'Identificacion',
            'Primer nombre',
            'Segundo nombre',
            'Primer apellido',
            'Segundo apellido',
            'Edad',
            'Sexo',
            'Fecha de nacimiento',
            'Edad en meses',
            'Telefono',
            'Etnia',
            'Ips atencion inicial',
            'Estado',
            'Fecha atencion inicial',
            'Ips seguimiento ambulatorio',
            'Caso confirmada de desnutricion (etiologia-primaria)',
            'Tipo de  ajuste',
            'Promedio dias de portuna revision',
            'Esquema completo PAI por edad',
            'Atencion promocion y mantemiento res3280 2018',
            'Estado actual del menor',
            'Tratamiento f75',
            'Fecha en la que recibio tratamientof75',
            'Nombre Ips manejo hospitalario'
         
        ];
    
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:AF1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('e6ffe6');
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal('center');
                $event->sheet->setAutoFilter($cellRange);
                $event->sheet->getDelegate()->getStyle('B2:AF2000')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ]
                ]);
            },
        ];

    }
}
