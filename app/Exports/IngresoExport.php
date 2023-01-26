<?php

namespace App\Exports;

use App\Models\Ingreso;
use Illuminate\Support\Facades\DB;
use App\Models\Seguimiento;
use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class IngresoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return  $students2 = Ingreso::select('ingresos.id','num_ide_','pri_nom_','seg_nom_','pri_ape_','seg_ape_','seg_nom_','pri_ape_',
        'Fecha_ingreso_ingres','num_ide_','Nom_ips_at_prim','peso_ingres','talla_ingres','puntaje_z',
        'calificacion','Edema','Emaciacion','perimetro_brazo','interpretacion_p_braqueal','requ_energia_dia','mes_entrega_FTLC',
        'fecha_entrega_FTLC','Menor_anos_des_aguda','medicamentos','remite_alguna_inst_apoyo')
        ->join('sivigilas as m', 'ingresos.sivigilas_id', '=', 'm.id' )->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'CC',
            'Nombre',
            'Nombre2',
            'Apellido',
            'Apellido2',
            'Fecha_Ingreso',
            'Ips atencion primaria',
            'Peso en kilos y un decimal',
            'Talla en cms y un decimal',
            'Puntaje Z',
            'Calificacion',
            'Edema',
            'Emaciacion',
            'Perimetro del brazo',
            'Interpretacion del perimetro braqueal',
            'Requerimiento de energia para cubrir FTLC - kcal/dia',
            'Mes',
            'Fecha en la que se entrega FTLC',
            'Menor de 5 años con desnutricion aguda cuenta con prescripcion',
            'Medicamentos',
            'Se remite a alguna institucion para apoyo'
         
        ];
    
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:V1'; // All headers
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
