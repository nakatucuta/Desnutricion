<?php

namespace App\Exports;

use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class reportesinseguimientos implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $resultados = DB::table('DESNUTRICION.dbo.sivigilas as a')
        ->leftJoin('DESNUTRICION.dbo.seguimientos as b', 'a.id', '=', 'b.sivigilas_id')
        ->leftJoin('DESNUTRICION.dbo.users as c', 'a.user_id', '=', 'c.id')
        ->select('c.name as PRESTADOR', 'a.*')
        ->whereNull('b.sivigilas_id')
        ->where('a.year', '>', '2023')
        ->get();
    

        return $resultados;
    }


    public function headings(): array
    {
        return [
            'Ips asignada',
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
            'Regimen',
            'Ips atencion Ambulatorio',
            'Estado',
            'Fecha atencion Inicial',
           
           
            'Caso confirmada de desnutricion (etiologia-primaria)',
            'Ips Manejo Hospitalario',
            'nombre hospitalario',
            
            
            'Id prestador',
            'fecha creacion',
            'fecha de edicion'

         
        ];
    
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:AD1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('e6ffe6');
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal('center');
                $event->sheet->setAutoFilter($cellRange);
                $event->sheet->getDelegate()->getStyle('B2:V200')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ]
                ]);
            },
        ];

    }
}
