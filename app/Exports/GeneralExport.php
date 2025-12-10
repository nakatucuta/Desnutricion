<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class GeneralExport implements FromQuery, WithHeadings, ShouldAutoSize, WithEvents
{
    public function query()
    {
        // PRIMERA CONSULTA (seguimientos normales)
        $data = DB::table('sivigilas')
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            ->join('users as users_sivigilas', 'sivigilas.user_id', '=', 'users_sivigilas.id')
            ->join('users as users_seguimientos', 'seguimientos.user_id', '=', 'users_seguimientos.id')
            ->select(
                'sivigilas.id',
                'sivigilas.cod_eve',
                'sivigilas.semana',
                'sivigilas.fec_not',
                'sivigilas.year',
                'sivigilas.dpto',
                'sivigilas.mun',
                'sivigilas.tip_ide_',
                'sivigilas.num_ide_',
                'sivigilas.pri_nom_',
                'sivigilas.seg_nom_',
                'sivigilas.pri_ape_',
                'sivigilas.seg_ape_',
                'sivigilas.sexo_',
                'sivigilas.fecha_nto_',
                'sivigilas.edad_ges',
                'sivigilas.telefono_',
                'sivigilas.nom_grupo_',
                'sivigilas.regimen',
                'sivigilas.Ips_at_inicial',
                'sivigilas.fecha_aten_inicial',
                'users_sivigilas.name as user_sivigilas_name',
                'sivigilas.Caso_confirmada_desnutricion_etiologia_primaria',
                'sivigilas.Ips_manejo_hospitalario',
                'sivigilas.nombreips_manejo_hospita',
                DB::raw("CASE WHEN seguimientos.estado = 1 THEN 'Activo' ELSE 'Inactivo' END as Estado"),
                'seguimientos.fecha_consulta as seguimiento_fecha_consulta',
                'seguimientos.peso_kilos as pesokilos1',
                'seguimientos.talla_cm as talla1',
                'seguimientos.puntajez as puntajez1',
                'seguimientos.clasificacion as clasificacion1',
                'seguimientos.requerimiento_energia_ftlc as energia1',
                'seguimientos.fecha_entrega_ftlc as fechaentrega1',
                'seguimientos.medicamento as medicamento1',
                'seguimientos.observaciones as observaciones1',
                'seguimientos.est_act_menor as est1',
                'seguimientos.tratamiento_f75 as f751',
                'seguimientos.fecha_recibio_tratf75 as fecf751',
                'seguimientos.fecha_proximo_control as fecproxcontrl1',
                'seguimientos.Esquemq_complrto_pai_edad as a',
                'seguimientos.Atecion_primocion_y_mantenimiento_res3280_2018 as b',
                'users_seguimientos.name as user_seguimientos_name'
            );

        // SEGUNDA CONSULTA (seguimiento_ocasionals)
        // MISMAS columnas, MISMO orden, MISMOS alias:
        $secondQuery = DB::table('sivigilas')
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            ->join('users as users_sivigilas', 'sivigilas.user_id', '=', 'users_sivigilas.id')
            ->join('users as users_seguimientos', 'seguimientos.user_id', '=', 'users_seguimientos.id')
            ->join('seguimiento_ocasionals', 'seguimientos.id', '=', 'seguimiento_ocasionals.seguimiento_id')
            ->select(
                'sivigilas.id',
                'sivigilas.cod_eve',
                'sivigilas.semana',
                'sivigilas.fec_not',
                'sivigilas.year',
                'sivigilas.dpto',
                'sivigilas.mun',
                'sivigilas.tip_ide_',
                'sivigilas.num_ide_',
                'sivigilas.pri_nom_',
                'sivigilas.seg_nom_',
                'sivigilas.pri_ape_',
                'sivigilas.seg_ape_',
                'sivigilas.sexo_',
                'sivigilas.fecha_nto_',
                'sivigilas.edad_ges',
                'sivigilas.telefono_',
                'sivigilas.nom_grupo_',
                'sivigilas.regimen',
                'sivigilas.Ips_at_inicial',
                'sivigilas.fecha_aten_inicial',
                'users_sivigilas.name as user_sivigilas_name',
                'sivigilas.Caso_confirmada_desnutricion_etiologia_primaria',
                'sivigilas.Ips_manejo_hospitalario',
                'sivigilas.nombreips_manejo_hospita',
                DB::raw("CASE WHEN seguimientos.estado = 1 THEN 'Activo' ELSE 'Inactivo' END as Estado"),
                'seguimiento_ocasionals.fecha_consulta as seguimiento_fecha_consulta',
                'seguimiento_ocasionals.peso_kilos as pesokilos1',
                'seguimiento_ocasionals.talla_cm as talla1',
                'seguimiento_ocasionals.puntajez as puntajez1',
                'seguimiento_ocasionals.clasificacion as clasificacion1',
                'seguimiento_ocasionals.requerimiento_energia_ftlc as energia1',
                'seguimiento_ocasionals.fecha_entrega_ftlc as fechaentrega1',
                'seguimiento_ocasionals.medicamento as medicamento1',
                'seguimiento_ocasionals.observaciones as observaciones1',
                'seguimiento_ocasionals.est_act_menor as est1',
                'seguimiento_ocasionals.tratamiento_f75 as f751',
                'seguimiento_ocasionals.fecha_recibio_tratf75 as fecf751',
                'seguimiento_ocasionals.fecha_proximo_control as fecproxcontrl1',
                'seguimiento_ocasionals.Esquemq_complrto_pai_edad as a',
                'seguimiento_ocasionals.Atecion_primocion_y_mantenimiento_res3280_2018 as b',
                'users_seguimientos.name as user_seguimientos_name'
            );

        // NO get()
        return $data
            ->unionAll($secondQuery)
            ->orderBy('seguimiento_fecha_consulta');
    }

    public function headings(): array
    {
        return [
            'ID','Cod_eve','Semana de notificacion','Fecha de notificacion','Año','Departamento',
            'Municipio de residencia','Tipo id','CC','Nombre','Nombre2','Apellido','Apellido2',
            'Sexo','Fecha de nacimiento','Edad en meses','Telefono','Etnia','Regimen de afiliacion',
            'Entidad - APB','Fecha de antencion inicial','Ips seguimiento ambulatorio',
            'Caso confirmada desnutricion etiologia_primaria','Ips manejo  hosptalario',
            'nombreips_manejo_hospita','(seguimiento)Estado','(seguimiento)Fecha de consulta',
            '(seguimiento)Peso en kilos y un decimal','(seguimiento)Talla en centimetros',
            '(seguimiento)Puntaje z(peso/talla)','(seguimiento)Calificacion',
            '(seguimiento)Requerimiento de energia FTLC','(seguimiento)Fecha de entrega FTLC',
            '(seguimiento)Medicamento','(seguimiento)Obvservaciones',
            '(seguimientos)estado actual del menor','(seguimientos)tratmiento f75',
            '(seguimientos)fecha en la que recibe tratmiento f75',
            '(seguimiento)Fecha del proximo seguimiento','Esquema pai completo para la edad',
            'Atecion primocion_mantenimiento_res3280_2018',
            '(seguimiento)Fecha de creacion del dato','(seguimiento) Usuario que realizo seguimiento'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {

                $cellRange = 'A1:AQ1';

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
                ]);
            },
        ];
    }
}
