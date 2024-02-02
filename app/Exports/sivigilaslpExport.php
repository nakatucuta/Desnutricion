<?php

namespace App\Exports;

use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class sivigilaslpExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return DB::connection('sqlsrv_1')
            ->table('maestroSiv113')
            ->select('*') // Considera especificar las columnas explícitamente para mejorar el rendimiento
            ->where('cod_eve', 113)
            ->whereBetween(DB::raw("YEAR(fec_not)"), [2024, 2024])
            ->get();
    }

    public function headings(): array
    {
        return [
            'cod_eve',
            'fec_not',
            'semana',
            'year',
            'cod_pre',
            'cod_sub',
            'pri_nom_',
            'seg_nom_',
            'pri_ape_',
            'seg_ape_',
            'tip_ide_',
            'num_ide_',
            'edad_',
            'uni_med_',
            'nacionali_',
            'nombre_nacionalidad',
            'sexo_',
            'cod_pais_o',
            'cod_dpto_o',
            'cod_mun_o',
            'area_',
            'localidad_',
            'cen_pobla_',
            'vereda_',
            'bar_ver_',
            'dir_res_',
            'ocupacion_',
            'tip_ss_',
            'cod_ase_',
            'per_etn_',
            'nom_grupo_',
            'estrato',
            'gp_discapa',
            'gp_desplaz',
            'gp_migrant',
            'gp_carcela',
            'gp_gestan',
            'sem_ges',
            'gp_indigen',
            'gp_pobicbf',
            'gp_mad_com',
            'gp_desmovi',
            'gp_psiquia',
            'gp_vic_vio',
            'gp_otros',
            'fuente',
            'cod_pais_r',
            'cod_dpto_r',
            'cod_mun_r',
            'fec_con_',
            'ini_sin_',
            'tip_cas_',
            'pac_hos_',
            'fec_hos_',
            'con_fin_',
            'fec_def_',
            'ajuste_',
            'telefono_',
            'fecha_nto_',
            'cer_def_',
            'cbmte_',
            'uni_modif',
            'nuni_modif',
            'fec_arc_xl',
            'nom_dil_f_',
            'tel_dil_f_',
            'fec_aju_',
            'nit_upgd',
            'fm_fuerza',
            'fm_unidad',
            'fm_grado',
            'version',
            'pri_nom_ma',
            'seg_nom_ma',
            'pri_ape_ma',
            'seg_ape_ma',
            'tip_ide_ma',
            'num_ide_ma',
            'niv_educat',
            'menores',
            'peso_nac',
            'talla_nac',
            'edad_ges',
            't_lechem',
            'e_complem',
            'crec_dllo',
            'esq_vac',
            'carne_vac',
            'peso_act',
            'talla_act',
            'per_braqui',
            'res_pr_ape',
            'imc',
            'zscore_pt',
            'clas_peso',
            'zscore_te',
            'clas_talla',
            'edema',
            'delgadez',
            'piel_rese',
            'hiperpigm',
            'cambios_cabello',
            'palidez',
            'ruta_atenc',
            'tipo_manej',
            'diag_medic',
            'estrato_datos_complementarios',
            'nom_eve',
            'nom_upgd',
            'npais_proce',
            'ndep_proce',
            'nmun_proce',
            'npais_resi',
            'ndep_resi',
            'nmun_resi',
            'ndep_notif',
            'nmun_notif',
            'nreg'
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:DN1'; // All headers
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
