<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\Seguimiento;
use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SeguimientoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $user = auth()->user(); // Obtener el usuario activo

        $data = DB::table('sivigilas')
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            ->join('users', 'users.id', '=', 'sivigilas.user_id')
            ->select('sivigilas.num_ide_', 'sivigilas.pri_nom_', 'sivigilas.seg_nom_',
                     'sivigilas.pri_ape_', 'sivigilas.seg_ape_', 
                     DB::raw("CASE WHEN seguimientos.estado = 1 THEN 'Activo' ELSE 'Inactivo' END as Estado"), 
                     'seguimientos.fecha_consulta', 'seguimientos.peso_kilos',
                     'seguimientos.talla_cm', 'seguimientos.puntajez',
                     'seguimientos.clasificacion', 'seguimientos.requerimiento_energia_ftlc',
                     'seguimientos.fecha_entrega_ftlc', 'seguimientos.medicamento',
                     'seguimientos.observaciones', 'seguimientos.est_act_menor',
                     'seguimientos.tratamiento_f75', 'seguimientos.fecha_recibio_tratf75',
                     'seguimientos.fecha_proximo_control','seguimientos.Esquemq_complrto_pai_edad', 
                     'seguimientos.Atecion_primocion_y_mantenimiento_res3280_2018')
            ->where('users.id', $user->id) // Filtrar por el usuario activo
            ->get();
    
        return $data;
    }

    public function headings(): array
    {
        return [
            'CC',
            'Nombre',
            'Segundo Nombre',
            'Apellido',
            'Segundo apellido',
            
            'Estado',
            'Fecha de consulta',
            'Peso en kilos y un decimal',
            'Talla en centimetros',

            'Puntaje z(peso/talla)',
            'Calificacion',
            'Requerimiento de energia FTLC',
            'Fecha de entrega FTLC',
            'Medicamento',
        
            'Observaciones',
            'Estado actual del menor',
            'Tratamiento f75',
            'Fecha recibio trat f75',
           
            'Fecha del proximo seguimiento',
            'Esquema pai completo para la edad',
            'Atecion primocion_mantenimiento_res3280_2018'

         
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
