<?php

namespace App\Exports;

use App\Models\Cargue412;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
class Cargue412Export implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{/**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Adaptación de la consulta para la exportación a Excel
        return Cargue412::from('cargue412s as a')
        ->select('u.name', 'd.descrip as ips_primaria', 'a.*')
        ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroidentificaciones] as b'), function($join) {
            $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
                 ->on('a.numero_identificacion', '=', 'b.identificacion');
        })
        ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as c'), 'b.numeroCarnet', '=', 'c.numeroCarnet')
        ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as d'), 'c.idGrupoIps', '=', 'd.id')
        ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
        ->get();
    
    }


    public function headings(): array
{
    return [
        'IPS ASIGNADA',
        'PRESTADOR PRIMARIO',
        'id',
        'numero_orden',
        'nombre_coperante',
        'fecha_captacion',
        'municipio',
        'nombre_rancheria',
        'ubicacion_casa',
        'nombre_cuidador',
        'identioficacion_cuidador',
        'telefono_cuidador',
        'nombre_eapb_cuidador',
        'nombre_autoridad_trad_ansestral',
        'datos_contacto_autoridad',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_identificacion',
        'numero_identificacion',
        'sexo',
        'fecha_nacimieto_nino',
        'edad_meses',
        'regimen_afiliacion',
        'nombre_eapb_menor',
        'peso_kg',
        'logitud_talla_cm',
        'perimetro_braqueal',
        'signos_peligro_infeccion_respiratoria',
        'sexosignos_desnutricion',
        'puntaje_z',
        'calsificacion_antropometrica',
        'estado',
        'user_id',
        'created_at',
        'updated_at',
        'nombre_profesional',
        'numero_profesional',
        'uds',
        
    ];
}

public function registerEvents(): array
{
    return [
        AfterSheet::class    => function(AfterSheet $event) {
            $cellRange = 'A1:AN1'; // All headers
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
