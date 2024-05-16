<?php

namespace App\Exports;

use App\Models\Seguimiento_412;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
class Seguimiento412Export implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = DB::table('DESNUTRICION..cargue412s as a')
    ->join('DESNUTRICION..users as c', 'a.user_id', '=', 'c.id')
    ->join('DESNUTRICION..seguimiento_412s as b', 'a.id', '=', 'b.cargue412_id')
    ->select('a.primer_nombre', 'a.segundo_nombre', 'a.primer_apellido', 'a.segundo_apellido', 'a.tipo_identificacion', 'a.numero_identificacion', 'c.name as PRESTADOR_PRIMARIO', 'b.*')
    ->get();


    return  $data;
    }

    public function headings(): array
    {
        return [
            'Primer nombre',
            'Segundo nombre',
            'Primer apellido',
            'Segundo apellido',
            'Tipo id',
            'Identificacion',
            'Id',
            'Estado',
            'Fecha consulta',
            'Peso en kilos',
            'Talla cm',
            'Puntaje z',
            'Clasificacion',
            'Codigo medicamento',
            'Motivo re apertura',
            'Observaciones',
            'Estado actual del menor',
            'Requerimiento energia ftlc',
            'Fecha proximo control',
            'PDF',
            'Codigo prestador',
            'Esquema pai ',
            'Atencion promocion y  mantenimiento',
            'Perimetro braqueal',
           
           
            'Codigo  prestador',
            'Fecha creacion del dato',
            'Fecha edicion del dato'
            
            
           

            
         
        ];
    
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:AA1'; // All headers
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
