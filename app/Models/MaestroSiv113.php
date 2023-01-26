<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaestroSiv113 extends Model
{  
    protected $connection = 'sqlsrv_1';

   
    protected $fillable = [

        'cod_eve',
         'fech_not',
         'semana',
         'year',
          'cod_pre',
          'cod_sub',
         'pri_nom_',
         'seg_nom_',
         'pri_ape_',
          'seg_ape_',
          'tip_ide',
          
          'num_ide_',
         'edad',
         'uni_med_',
         'nacionali',
          'nombre_nacionalidad',
          'sexo_',
         'cod_pais_o',
         'cod_dpto_o',
         'cod_mun_o',
          'area_',
          'localidad',
          'cen_pobla_',

          'vereda_',
         'bar_ver_',
         'dir_res_',
         'ocupacion_',
          'tip_ss_',
          'cod_ase_',
         'per_etn_',
         'nom_grupo',
         'estrato',
          'gp_discapa',
          'gp_desplaz',
          'gp_migran',

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
          'fm_fuerza',
          'fm_unidad',
           'fm_grado',
           'fecha_nto_',
           'cer_def_',

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
      'nreg',
    
    ];
 
}