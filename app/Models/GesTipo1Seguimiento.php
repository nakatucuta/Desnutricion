<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GesTipo1Seguimiento extends Model
{
    protected $table = 'ges_tipo1_seguimientos';

    protected $fillable = [
        'ges_tipo1_id','user_id',
        'fecha_contacto','tipo_contacto','estado','proximo_contacto',
        'fecha_seguimiento','observaciones',

        'tipo_documento','numero_identificacion','apellido_1','apellido_2','nombre_1','nombre_2',
        'fecha_nacimiento','edad_anios','sexo','regimen_afiliacion','pertenencia_etnica','grupo_poblacional',
        'departamento_residencia','municipio_residencia','zona','etnia','asentamiento','telefono_usuaria',
        'direccion','nivel_educativo','discapacidad','mujer_cabeza_hogar','ocupacion','estado_civil',
        'control_tradicional','gestante_renuente','inasistente','ips_primaria',

        'fecha_ingreso_cpn','fum','fpp','dias_para_parto','alarma','edad_gest_inicio_control','trimestre_inicio_control','formula_obstetrica',
        'hipertension_arterial','diabetes','vih','sifilis','tuberculosis','otras_condiciones_graves','apoyo_familiar','embarazo_deseado',
        'habitos_riesgo','violencia','abuso_sexual','periodo_intergenesico',

        'peso_inicial','talla','imc','clasificacion_imc','riesgos_psicosociales',
        'ive_causales','clasificacion_riesgo','alto_riesgo_causas','otras_cuales',

        'remitida_especialista','asesoria_vih','asesoria_vih_trimestre',

        // ===== PARACLÍNICOS (RUTA PDF O TEXTO EN CAMPO *_resultado) =====
        'vih_tamiz1_fecha','vih_tamiz1_resultado','vih_tamiz1_trimestre',
        'vih_tamiz2_fecha','vih_tamiz2_resultado','vih_tamiz2_trimestre',
        'vih_tamiz3_fecha','vih_tamiz3_resultado','vih_tamiz3_trimestre',

        'sifilis_rapida1_fecha','sifilis_rapida1_resultado','sifilis_rapida1_trimestre',
        'sifilis_rapida2_fecha','sifilis_rapida2_resultado','sifilis_rapida2_trimestre',
        'sifilis_rapida3_fecha','sifilis_rapida3_resultado','sifilis_rapida3_trimestre',

        'vih_confirmatoria_fecha','vih_confirmatoria_trimestre',

        'sifilis_no_trep_fecha','sifilis_no_trep_resultado','sifilis_no_trep_trimestre',
        'urocultivo_fecha','urocultivo_resultado',
        'glicemia_fecha','glicemia_resultado',
        'pto_glucosa_fecha','pto_glucosa_resultado',
        'hemoglobina_fecha','hemoglobina_resultado',
        'hemoclasificacion_resultado',
        'ag_hbs_fecha','ag_hbs_resultado',
        'toxoplasma_fecha','toxoplasma_resultado',
        'rubeola_fecha','rubeola_resultado',
        'citologia_fecha','citologia_resultado',
        'frotis_vaginal_fecha','frotis_vaginal_resultado',
        'estreptococo_fecha','estreptococo_resultado',
        'malaria_fecha','malaria_resultado',
        'chagas_fecha','chagas_resultado',

        'vac_influenza_fecha','vac_toxoide_fecha','vac_dpt_acelular_fecha','consulta_odontologica_fecha',
        'eco_translucencia','eco_anomalias','eco_otras',
        'suministro_acido_folico','suministro_calcio','suministro_hierro','suministro_asa',
        'desparasitacion_fecha','informacion_en_salud',

        'cpn1_fecha','cpn1_quien','cpn2_fecha','cpn2_quien','cpn3_fecha','cpn3_quien','cpn4_fecha','cpn4_quien',
        'cpn5_fecha','cpn5_quien','cpn6_fecha','cpn6_quien','cpn7_fecha','cpn7_quien','cpn8_fecha','cpn8_quien',
        'cpn9_fecha','cpn9_quien','num_total_cpn','ultimo_cpn',

        'cons_ginecologia_1','cons_ginecologia_2','cons_ginecologia_3','cons_nutricion','cons_psicologia',
        'cons_otro_especialista','cons_otro_quien','especialistas_describe',

        'parto_tipo','parto_sem_gest','parto_complicaciones','uci_materna',
        'its_intraparto_toma','its_intraparto_positivo',

        'defuncion_fecha','defuncion_causa','multiplicidad_embarazo',

        'rn1_registro_civil','rn1_nombre','rn1_sexo','rn1_peso','rn1_condicion','rn1_tsh','rn1_hipotiroideo_dx',
        'rn1_trat_hipotiroideo','rn1_uci','rn1_vac_bcg','rn1_vac_hepb',

        'rn2_registro_civil','rn2_nombre','rn2_sexo','rn2_peso','rn2_condicion','rn2_tsh','rn2_hipotiroideo_dx',
        'rn2_trat_hipotiroideo','rn2_uci','rn2_vac_bcg','rn2_vac_hepb',

        // ===== ✅ DESCRIPCIONES NUEVAS (ESTO ERA LO QUE TE FALTABA) =====
        'vih_tamiz1_resultado_desc',
        'vih_tamiz2_resultado_desc',
        'vih_tamiz3_resultado_desc',
        'sifilis_rapida1_resultado_desc',
        'sifilis_rapida2_resultado_desc',
        'sifilis_rapida3_resultado_desc',
        'sifilis_no_trep_resultado_desc',
        'urocultivo_resultado_desc',
        'glicemia_resultado_desc',
        'pto_glucosa_resultado_desc',
        'hemoglobina_resultado_desc',
        'hemoclasificacion_resultado_desc',
        'ag_hbs_resultado_desc',
        'toxoplasma_resultado_desc',
        'rubeola_resultado_desc',
        'citologia_resultado_desc',
        'frotis_vaginal_resultado_desc',
        'estreptococo_resultado_desc',
        'malaria_resultado_desc',
        'chagas_resultado_desc',
    ];

    protected $casts = [
        'fecha_contacto' => 'date',
        'proximo_contacto' => 'date',
        'fecha_seguimiento' => 'date',
        'fecha_nacimiento' => 'date',
        'fecha_ingreso_cpn' => 'date',
        'fum' => 'date',
        'fpp' => 'date',
        'vih_tamiz1_fecha' => 'date',
        'vih_tamiz2_fecha' => 'date',
        'vih_tamiz3_fecha' => 'date',
        'vih_confirmatoria_fecha' => 'date',
        'sifilis_no_trep_fecha' => 'date',
        'urocultivo_fecha' => 'date',
        'glicemia_fecha' => 'date',
        'pto_glucosa_fecha' => 'date',
        'hemoglobina_fecha' => 'date',
        'ag_hbs_fecha' => 'date',
        'toxoplasma_fecha' => 'date',
        'rubeola_fecha' => 'date',
        'citologia_fecha' => 'date',
        'frotis_vaginal_fecha' => 'date',
        'estreptococo_fecha' => 'date',
        'malaria_fecha' => 'date',
        'chagas_fecha' => 'date',
        'vac_influenza_fecha' => 'date',
        'vac_toxoide_fecha' => 'date',
        'vac_dpt_acelular_fecha' => 'date',
        'consulta_odontologica_fecha' => 'date',
        'desparasitacion_fecha' => 'date',
        'cpn1_fecha' => 'date',
        'cpn2_fecha' => 'date',
        'cpn3_fecha' => 'date',
        'cpn4_fecha' => 'date',
        'cpn5_fecha' => 'date',
        'cpn6_fecha' => 'date',
        'cpn7_fecha' => 'date',
        'cpn8_fecha' => 'date',
        'cpn9_fecha' => 'date',
        'defuncion_fecha' => 'date',
    ];

    public function caso()
    {
        return $this->belongsTo(GesTipo1::class, 'ges_tipo1_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
