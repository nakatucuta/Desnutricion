<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\Seguimiento;
use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Excel;


class GeneralExport implements  FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('sivigilas')
    ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
    ->join('users as users_sivigilas', 'sivigilas.user_id', '=', 'users_sivigilas.id')
    ->join('users as users_seguimientos', 'seguimientos.user_id', '=', 'users_seguimientos.id')
    ->select(DB::raw("sivigilas.id, sivigilas.cod_eve, sivigilas.semana, sivigilas.fec_not, 
    sivigilas.year, sivigilas.dpto, sivigilas.mun, sivigilas.tip_ide_, sivigilas.num_ide_, 
    sivigilas.pri_nom_, sivigilas.seg_nom_, sivigilas.pri_ape_, sivigilas.seg_ape_, 
    sivigilas.sexo_, sivigilas.fecha_nto_, sivigilas.edad_ges, sivigilas.telefono_,
     sivigilas.nom_grupo_, sivigilas.regimen, sivigilas.Ips_at_inicial, 
     sivigilas.fecha_aten_inicial, users_sivigilas.name as user_sivigilas_name, 
     sivigilas.Caso_confirmada_desnutricion_etiologia_primaria, 
     sivigilas.Ips_manejo_hospitalario,  
     sivigilas.nombreips_manejo_hospita, 
     IIF(seguimientos.estado = 1, 'Activo', 'Inactivo') as Estado, 
     seguimientos.fecha_consulta, seguimientos.peso_kilos, seguimientos.talla_cm, 
     seguimientos.puntajez, seguimientos.clasificacion, 
     seguimientos.requerimiento_energia_ftlc, seguimientos.fecha_entrega_ftlc, 
     seguimientos.medicamento, seguimientos.observaciones, seguimientos.est_act_menor, 
     seguimientos.tratamiento_f75, seguimientos.fecha_recibio_tratf75, 
     seguimientos.fecha_proximo_control,seguimientos.Esquemq_complrto_pai_edad, 
     seguimientos.Atecion_primocion_y_mantenimiento_res3280_2018, seguimientos.created_at, 
     users_seguimientos.name as user_seguimientos_name"))
    ->get();

    }

    public function headings(): array
    {
        return [

            
            //sivigila
            'ID',
            'Cod_eve',
            'Semana de notificacion',
            'Fecha de notificacion',
            'Año',
            'Departamento',
            'Municipio de residencia',
            'Tipo id',
            'CC',
            'Nombre',
            'Nombre2',
            'Apellido',
            'Apellido2',
            'Sexo',
            'Fecha de nacimiento',
            'Edad en meses',
            'Telefono',
            'Etnia',
            'Regimen de afiliacion',
            'Entidad - APB',
            'Fecha de antencion inicial',
            'Ips seguimiento ambulatorio',
           'Caso confirmada desnutricion etiologia_primaria',
           'Ips manejo  hosptalario',
         
           'nombreips_manejo_hospita',


            '(seguimiento)Estado',

             '(seguimiento)Fecha de consulta',
             '(seguimiento)Peso en kilos y un decimal',
             '(seguimiento)Talla en centimetros',
             '(seguimiento)Puntaje z(peso/talla)',
             '(seguimiento)Calificacion',
             '(seguimiento)Requerimiento de energia FTLC',
             '(seguimiento)Fecha de entrega FTLC',
             '(seguimiento)Medicamento',
             '(seguimiento)Obvservaciones',

             '(seguimientos)estado actual del menor',
               '(seguimientos)tratmiento f75',
               '(seguimientos)fecha en la que recibe tratmiento f75',
               
             '(seguimiento)Fecha del proximo seguimiento',
             'Esquema pai completo para la edad',
             'Atecion primocion_mantenimiento_res3280_2018',
             '(seguimiento)Fecha de creacion del dato',
             '(seguimiento) Usuario que realizo seguimiento'
        
         
        ];
    
    }

    public function registerEvents(): array
    {
      
         return [
            
            AfterSheet::class    => function(AfterSheet $event) {
                // $cellRange3 = 'A2:AT2'; //RANGO PARA LOS FILTROS
                // $cellRange1 = 'AO1:AP1'; // All headers
                // $cellRange2 = 'AQ1:AT1';
                 $cellRange = 'A1:AQ1'; // All headers
                
                //  $event->sheet->getDelegate()->mergeCells($cellRange2);
                //  $event->sheet->getDelegate()->mergeCells($cellRange1);//ojo debes buscar la
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                 $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                 $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                 ->getStartColor()->setARGB('e6ffe6');
                 $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal('center');
                 $event->sheet->setAutoFilter($cellRange);
                 $event->sheet->getDelegate()->getStyle('B2:AN2000')->applyFromArray([
                     'alignment' => [
                         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                     ]
                 ]
                );

                 
             },
         ];

     }

     
}
