<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class AsignacionesMaestrosiv549 extends Model
{

     public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }
    protected $table = 'asignaciones_maestrosiv549';
    protected $fillable = [
        'user_id',
        // Agrega **todos** los campos copiados de MaestroSiv549 aquí:
        'cod_eve', 'fec_not', 'semana', 'year', 'cod_pre', 'cod_sub', 'pri_nom_', 'seg_nom_',
        'pri_ape_', 'seg_ape_', 'tip_ide_', 'num_ide_', 'edad_', 'uni_med_', 'nacionali_',
        'nombre_nacionalidad', 'sexo_', 'iden_gener', 'otra_ident', 'orient_sex', 'otra_orien',
        'cod_pais_o', 'cod_dpto_o', 'cod_mun_o', 'area_', 'localidad_', 'cen_pobla_', 'vereda_',
        'bar_ver_', 'dir_res_', 'lat_dir', 'long_dir', 'ocupacion_', 'tip_ss_', 'cod_ase_',
        'per_etn_', 'nom_grupo_', 'estrato', 'gp_discapa', 'gp_desplaz', 'gp_migrant',
        'gp_carcela', 'gp_gestan', 'sem_ges', 'gp_indigen', 'gp_pobicbf', 'gp_mad_com',
        'gp_desmovi', 'gp_psiquia', 'gp_vic_vio', 'gp_otros', 'fuente', 'cod_pais_r',
        'cod_dpto_r', 'cod_mun_r', 'fec_con_', 'ini_sin_', 'tip_cas_', 'pac_hos_', 'fec_hos_',
        'con_fin_', 'fec_def_', 'ajuste_', 'telefono_', 'fecha_nto_', 'cer_def_', 'cbmte_',
        'uni_modif', 'nuni_modif', 'fec_arc_xl', 'nom_dil_f_', 'tel_dil_f_', 'fec_aju_',
        'nit_upgd', 'fm_fuerza', 'fm_unidad', 'fm_grado', 'version_', 'pte_remtda', 'codinst_r1',
        'inst_refe1', 'codinst_r2', 'inst_refe2', 'tiem_remis', 'num_gestac', 'num_parvag',
        'num_cesare', 'num_aborto', 'num_molas', 'num_ectopi', 'num_muerto', 'num_vivos',
        'fec_ul_ges', 'no_con_pre', 'sem_c_pren', 'term_gesta', 'moc_rel_tg', 'falla_card',
        'falla_rena', 'falla_hepa', 'falla_cere', 'falla_resp', 'falla_coag', 'eclampsia',
        'preclampsi', 'choq_septi', 'hemorragia_obstetrica_severa', 'rupt_uteri', 'cir_adicio',
        'ttl_criter', 'cir_adic_1', 'adic1_otro', 'cir_adic_2', 'adic2_otro', 'fec_egreso',
        'dias_hospi', 'egreso', 'caus_princ', 'caus_agrup', 'caus_asoc1', 'caus_asoc2',
        'caus_asoc3', 'peso_rnacx', 'peso_rnacd', 'emb_mult', 'estado_rna', 'ed_ter_ges',
        'regul_fecu', 'aborto_sep', 'emb_ectopi', 'autoinmune', 'hematologi', 'oncologica',
        'endoc_meta', 'renales', 'gastrointe', 'eve_trombo', 'card_cereb', 'otras_enfe',
        'accidente', 'enfe_molar', 'intox_acci', 'inten_suic', 'vic_violen', 'otros_even',
        'cual_event', 'ingres_uci', 'transfusio', 'unds_trans', 'falla_meta', 'dias_c_int',
        'nom_eve', 'nom_upgd', 'npais_proce', 'ndep_proce', 'nmun_proce', 'npais_resi',
        'ndep_resi', 'nmun_resi', 'ndep_notif', 'nmun_notif', 'FechaHora', 'nreg'
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function seguimientosMaestrosiv549()
{
    return $this->hasMany(\App\Models\SeguimientMaestrosiv549::class, 'asignacion_id');
}
}
