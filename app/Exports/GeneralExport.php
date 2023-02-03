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
        ->Join('ingresos', 'sivigilas.id', '=', 'ingresos.sivigilas_id')
        ->Join('seguimientos', 'ingresos.id', '=', 'seguimientos.ingresos_id')
        ->select(DB::raw("sivigilas.id,sivigilas.cod_eve,sivigilas.semana,sivigilas.fec_not,sivigilas.year,sivigilas.dpto,sivigilas.mun,sivigilas.tip_ide_
        
        ,sivigilas.num_ide_,sivigilas.pri_nom_,sivigilas.seg_nom_,sivigilas.pri_ape_,sivigilas.seg_ape_,
        sivigilas.sexo_,sivigilas.fecha_nto_,sivigilas.edad_ges,sivigilas.telefono_,sivigilas.nom_grupo_,sivigilas.regimen,sivigilas.Ips_at_inicial,
        sivigilas.fecha_aten_inicial,sivigilas.Ips_seguimiento_Ambulatorio,sivigilas.Caso_confirmada_desnutricion_etiologia_primaria,
        sivigilas.Tipo_ajuste,sivigilas.Promedio_dias_oportuna_remision,sivigilas.Esquemq_complrto_pai_edad,sivigilas.Atecion_primocion_y_mantenimiento_res3280_2018,
        sivigilas.est_act_menor,sivigilas.tratamiento_f75,sivigilas.fecha_recibio_tratf75,sivigilas.nombreips_manejo_hospita,

        ingresos.Fecha_ingreso_ingres,
         IIF(seguimientos.estado = 1, 'Activo', 'Inactivo') as Estado, seguimientos.fecha_consulta,
          seguimientos.peso_kilos,seguimientos.talla_cm,seguimientos.puntajez,seguimientos.clasificacion,
          seguimientos.requerimiento_energia_ftlc,seguimientos.fecha_entrega_ftlc,seguimientos.medicamento,seguimientos.recomendaciones_manejo,
          seguimientos.resultados_seguimientos,seguimientos.ips_realiza_seguuimiento,seguimientos.observaciones,
          seguimientos.fecha_proximo_control"))
        ->get();
    }

    public function headings(): array
    {
        return [

            

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
            'Ips que realiza el seguimiento ambulatorio',
            'Caso confiamda de desnutricion de etiologia primaria',
            'Tipo de ajuste',
            'Promedio en dias en hacerse efectiva y oportuna la remision',
            'Esquema pai completo para la edad',
            'Atencion en la ruta de promocion y mantenimieto de acuerdo a la  -RESOLUCION 3280 /2018 PRIMERA INFANCIA',
            'ESTADO ACTUAL DEL MENOR(RECUPERADO, EN PROCESO DE RECUPERCAION, RECAIDA, FALLECIDO, BUSQUEDA FALLIDA)',
            'RECIBE  TRATAMIENTO CON F75 (DURANTE LAS PRIMERAS 48 HORAS)',
            'FECHA EN QUE RECIBIO EL TRATAMIENTO CON F75',
            'NOMBRE IPS DE MANEJO HOSPITALARIO ',
            'Fecha_Ingreso',
            'Estado',
            'Fecha de consulta',
            'Peso en kilos y un decimal',
            'Talla en centimetros',
            'Puntaje z(peso/talla)',
            'Calificacion',
            'Requerimiento de energia FTLC',
            'Fecha de entrega FTLC',
            'Medicamento',
            'Recomendacion de manejo',
            'Resultados seguimiento',
            'Ips que realiza el seguimiento',
            'Obvservaciones',
            'Fecha del proeximo seguimiento'
        
         
        ];
    
    }

    public function registerEvents(): array
    {
      
         return [
            
            AfterSheet::class    => function(AfterSheet $event) {
                // $cellRange3 = 'A2:AT2'; //RANGO PARA LOS FILTROS
                // $cellRange1 = 'AO1:AP1'; // All headers
                // $cellRange2 = 'AQ1:AT1';
                 $cellRange = 'A1:AT1'; // All headers
                
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
