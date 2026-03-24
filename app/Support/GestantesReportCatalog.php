<?php

namespace App\Support;

class GestantesReportCatalog
{
    public static function tipo1(): array
    {
        return [
            'tipo_registro' => ['label' => 'Tipo registro', 'db' => 'g.tipo_de_registro'],
            'consecutivo' => ['label' => 'Consecutivo', 'db' => 'g.consecutivo'],
            'pais_nacionalidad' => ['label' => 'Pais de la nacionalidad', 'db' => 'g.pais_de_la_nacionalidad'],
            'municipio' => ['label' => 'Municipio de residencia habitual', 'db' => 'g.municipio_de_residencia_habitual'],
            'zona' => ['label' => 'Zona territorial de residencia', 'db' => 'g.zona_territorial_de_residencia'],
            'ips_primaria' => ['label' => 'Codigo de habilitacion IPS primaria de la gestante', 'db' => 'g.codigo_de_habilitacion_ips_primaria_de_la_gestante'],
            'tipo_identificacion' => ['label' => 'Tipo de identificacion de la usuaria', 'db' => 'g.tipo_de_identificacion_de_la_usuaria'],
            'no_id_del_usuario' => ['label' => 'Numero de identificacion de la usuaria', 'db' => 'g.no_id_del_usuario'],
            'primer_apellido' => ['label' => 'Primer apellido', 'db' => 'g.primer_apellido'],
            'segundo_apellido' => ['label' => 'Segundo apellido', 'db' => 'g.segundo_apellido'],
            'primer_nombre' => ['label' => 'Primer nombre', 'db' => 'g.primer_nombre'],
            'segundo_nombre' => ['label' => 'Segundo nombre', 'db' => 'g.segundo_nombre'],
            'fecha_de_nacimiento' => ['label' => 'Fecha de nacimiento', 'db' => 'g.fecha_de_nacimiento'],
            'codigo_pertenencia_etnica' => ['label' => 'Codigo pertenencia etnica', 'db' => 'g.codigo_pertenencia_etnica'],
            'codigo_ocupacion' => ['label' => 'Codigo de ocupacion', 'db' => 'g.codigo_de_ocupacion'],
            'codigo_nivel_educativo' => ['label' => 'Codigo nivel educativo de la gestante', 'db' => 'g.codigo_nivel_educativo_de_la_gestante'],
            'fecha_probable_de_parto' => ['label' => 'Fecha probable de parto', 'db' => 'g.fecha_probable_de_parto'],
            'direccion' => ['label' => 'Direccion de residencia de la gestante', 'db' => 'g.direccion_de_residencia_de_la_gestante'],
            'hta' => ['label' => 'Antecedente hipertension cronica', 'db' => 'g.antecedente_hipertension_cronica'],
            'preeclampsia' => ['label' => 'Antecedente preeclampsia', 'db' => 'g.antecedente_preeclampsia'],
            'diabetes' => ['label' => 'Antecedente diabetes', 'db' => 'g.antecedente_diabetes'],
            'autoinmune' => ['label' => 'Antecedente LES o enfermedad autoinmune', 'db' => 'g.antecedente_les_enfermedad_autoinmune'],
            'sindrome_metabolico' => ['label' => 'Antecedente sindrome metabolico', 'db' => 'g.antecedente_sindrome_metabolico'],
            'erc' => ['label' => 'Antecedente ERC', 'db' => 'g.antecedente_erc'],
            'trombofilia' => ['label' => 'Antecedente trombofilia o trombosis venosa profunda', 'db' => 'g.antecedente_trombofilia_o_trombosis_venosa_profunda'],
            'anemia_celulas_falciformes' => ['label' => 'Antecedente anemia de celulas falciformes', 'db' => 'g.antecedentes_anemia_celulas_falciformes'],
            'sepsis_previa' => ['label' => 'Antecedente sepsis durante gestaciones previas', 'db' => 'g.antecedente_sepsis_durante_gestaciones_previas'],
            'consumo_tabaco' => ['label' => 'Consumo tabaco durante la gestacion', 'db' => 'g.consumo_tabaco_durante_la_gestacion'],
            'periodo_intergenesico' => ['label' => 'Periodo intergenesico', 'db' => 'g.periodo_intergenesico'],
            'embarazo_multiple' => ['label' => 'Embarazo multiple', 'db' => 'g.embarazo_multiple'],
            'metodo_de_concepcion' => ['label' => 'Metodo de concepcion', 'db' => 'g.metodo_de_concepcion'],
            'nombre_completo' => ['label' => 'Nombre completo', 'db' => "LTRIM(RTRIM(CONCAT(COALESCE(g.primer_nombre,''), ' ', COALESCE(g.segundo_nombre,''), ' ', COALESCE(g.primer_apellido,''), ' ', COALESCE(g.segundo_apellido,''))))"],
            'numero_carnet' => ['label' => 'Numero carnet', 'db' => 'g.numero_carnet'],
            'seguimiento_estado' => ['label' => 'Estado seguimiento', 'db' => "CASE WHEN EXISTS (SELECT 1 FROM ges_tipo1_seguimientos s WHERE s.ges_tipo1_id = g.id) THEN 'Con seguimiento' ELSE 'Sin seguimiento' END"],
            'batch_verifications_id' => ['label' => 'Cod unico de cargue', 'db' => 'g.batch_verifications_id'],
            'id' => ['label' => 'ID', 'db' => 'g.id'],
            'user_id' => ['label' => 'Usuario', 'db' => 'g.user_id'],
            'created_at' => ['label' => 'Creado', 'db' => 'g.created_at'],
            'updated_at' => ['label' => 'Actualizado', 'db' => 'g.updated_at'],
        ];
    }

    public static function tipo3(): array
    {
        return [
            'tipo_registro' => ['label' => 'Tipo registro', 'db' => 't3.tipo_de_registro'],
            'consecutivo' => ['label' => 'Consecutivo de registro', 'db' => 't3.consecutivo_de_registro'],
            'tipo_identificacion' => ['label' => 'Tipo de identificacion de la usuaria', 'db' => 't3.tipo_identificacion_de_la_usuaria'],
            'no_id_del_usuario' => ['label' => 'Numero de identificacion de la usuaria', 'db' => 't3.no_id_del_usuario'],
            'fecha_tecnologia_en_salud' => ['label' => 'Fecha de la tecnologia en salud', 'db' => 't3.fecha_tecnologia_en_salud'],
            'cups' => ['label' => 'Codigo CUPS de la tecnologia en salud', 'db' => 't3.codigo_cups_de_la_tecnologia_en_salud'],
            'finalidad' => ['label' => 'Finalidad de la tecnologia en salud', 'db' => 't3.finalidad_de_la_tecnologia_en_salud'],
            'riesgo_gestacional' => ['label' => 'Clasificacion del riesgo gestacional', 'db' => 't3.clasificacion_riesgo_gestacional'],
            'riesgo_preeclampsia' => ['label' => 'Clasificacion del riesgo preeclampsia', 'db' => 't3.clasificacion_riesgo_preeclampsia'],
            'asa' => ['label' => 'Suministro de acido acetilsalicilico - ASA', 'db' => 't3.suministro_acido_acetilsalicilico_ASA'],
            'acido_folico' => ['label' => 'Suministro de acido folico en el control prenatal', 'db' => 't3.suministro_acido_folico_en_el_control_prenatal'],
            'sulfato_ferroso' => ['label' => 'Suministro de sulfato ferroso en el control prenatal', 'db' => 't3.suministro_sulfato_ferroso_en_el_control_prenatal'],
            'calcio' => ['label' => 'Suministro de calcio en el control prenatal', 'db' => 't3.suministro_calcio_en_el_control_prenatal'],
            'fecha_anticonceptivo_post_evento' => ['label' => 'Fecha de suministro de anticonceptivo post evento obstetrico', 'db' => 't3.fecha_suministro_de_anticonceptivo_post_evento_obstetrico'],
            'metodo_anticonceptivo_post_evento' => ['label' => 'Suministro de metodo anticonceptivo post evento obstetrico', 'db' => 't3.suministro_metodo_anticonceptivo_post_evento_obstetrico'],
            'fecha_salida' => ['label' => 'Fecha de salida de aborto o atencion del parto o cesarea', 'db' => 't3.fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea'],
            'fecha_terminacion' => ['label' => 'Fecha de terminacion de la gestacion', 'db' => 't3.fecha_de_terminacion_de_la_gestacion'],
            'tipo_terminacion' => ['label' => 'Tipo de terminacion de la gestacion', 'db' => 't3.tipo_de_terminacion_de_la_gestacion'],
            'pas' => ['label' => 'Tension arterial sistolica (PAS) mmHg', 'db' => 't3.tension_arterial_sistolica_PAS_mmHg'],
            'pad' => ['label' => 'Tension arterial diastolica (PAD) mmHg', 'db' => 't3.tension_arterial_diastolica_PAD_mmHg'],
            'imc' => ['label' => 'Indice de masa corporal', 'db' => 't3.indice_de_masa_corporal'],
            'hemoglobina' => ['label' => 'Resultado de la hemoglobina', 'db' => 't3.resultado_de_la_hemoglobina'],
            'ipau' => ['label' => 'Indice de pulsatilidad de arterias uterinas', 'db' => 't3.indice_de_pulsatilidad_de_arterias_uterinas'],
            'nombre_completo' => ['label' => 'Nombre completo', 'db' => "LTRIM(RTRIM(CONCAT(COALESCE(t1.primer_nombre,''), ' ', COALESCE(t1.segundo_nombre,''), ' ', COALESCE(t1.primer_apellido,''), ' ', COALESCE(t1.segundo_apellido,''))))"],
            'ges_tipo1_id' => ['label' => 'Gestante', 'db' => 't3.ges_tipo1_id'],
            'batch_verifications_id' => ['label' => 'Cod unico de cargue', 'db' => 't3.batch_verifications_id'],
            'id' => ['label' => 'ID', 'db' => 't3.id'],
            'user_id' => ['label' => 'Usuario', 'db' => 't3.user_id'],
            'created_at' => ['label' => 'Creado', 'db' => 't3.created_at'],
            'updated_at' => ['label' => 'Actualizado', 'db' => 't3.updated_at'],
        ];
    }
}
