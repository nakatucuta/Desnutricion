<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('ges_tipo1_seguimientos', function (Blueprint $table) {
            $table->increments('id');

            // FKs
            $table->unsignedInteger('ges_tipo1_id');
            $table->foreign('ges_tipo1_id')->references('id')->on('ges_tipo1')->onDelete('cascade');

            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            // Cabecera del seguimiento (las que ya usabas)
            $table->date('fecha_contacto')->nullable();
            $table->tinyInteger('tipo_contacto')->nullable(); // 1,2,3
            $table->string('estado', 50)->nullable();
            $table->date('proximo_contacto')->nullable();

            // Campos sueltos que tenías antes (si los usas)
            $table->date('fecha_seguimiento')->nullable();
            $table->text('observaciones')->nullable();

            // ====== TODAS LAS COLUMNAS QUE ESTABAN EN EL JSON ======
            // Identificación / demografía
            $table->string('tipo_documento', 10)->nullable();
            $table->string('numero_identificacion', 50)->nullable();
            $table->string('apellido_1', 100)->nullable();
            $table->string('apellido_2', 100)->nullable();
            $table->string('nombre_1', 100)->nullable();
            $table->string('nombre_2', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad_anios')->nullable();
            $table->string('sexo', 20)->nullable();
            $table->string('regimen_afiliacion', 50)->nullable();
            $table->string('pertenencia_etnica', 100)->nullable();
            $table->string('grupo_poblacional', 100)->nullable();
            $table->string('departamento_residencia', 100)->nullable();
            $table->string('municipio_residencia', 100)->nullable();
            $table->string('zona', 50)->nullable();
            $table->string('etnia', 100)->nullable();
            $table->string('asentamiento', 150)->nullable();
            $table->string('telefono_usuaria', 50)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('nivel_educativo', 100)->nullable();
            $table->string('discapacidad', 100)->nullable();
            $table->string('mujer_cabeza_hogar', 10)->nullable();
            $table->string('ocupacion', 100)->nullable();
            $table->string('estado_civil', 50)->nullable();
            $table->string('control_tradicional', 10)->nullable();
            $table->string('gestante_renuente', 10)->nullable();
            $table->string('inasistente', 10)->nullable();
            $table->string('ips_primaria', 150)->nullable();

            // Gestación / fechas
            $table->date('fecha_ingreso_cpn')->nullable();
            $table->date('fum')->nullable();
            $table->date('fpp')->nullable();
            $table->integer('dias_para_parto')->nullable();
            $table->string('alarma', 10)->nullable();
            $table->string('edad_gest_inicio_control', 50)->nullable();
            $table->string('trimestre_inicio_control', 50)->nullable();
            $table->string('formula_obstetrica', 50)->nullable();

            // Morbilidades
            $table->string('hipertension_arterial', 10)->nullable();
            $table->string('diabetes', 10)->nullable();
            $table->string('vih', 10)->nullable();
            $table->string('sifilis', 10)->nullable();
            $table->string('tuberculosis', 10)->nullable();
            $table->string('otras_condiciones_graves', 200)->nullable();
            $table->string('apoyo_familiar', 10)->nullable();
            $table->string('embarazo_deseado', 10)->nullable();
            $table->string('habitos_riesgo', 200)->nullable();
            $table->string('violencia', 10)->nullable();
            $table->string('abuso_sexual', 10)->nullable();
            $table->string('periodo_intergenesico', 50)->nullable();

            // Antropometría
            $table->decimal('peso_inicial', 8, 2)->nullable();
            $table->decimal('talla', 8, 2)->nullable();
            $table->decimal('imc', 8, 2)->nullable();
            $table->string('clasificacion_imc', 50)->nullable();
            $table->string('riesgos_psicosociales', 200)->nullable();

            // Riesgo
            $table->string('ive_causales', 200)->nullable();
            $table->string('clasificacion_riesgo', 100)->nullable();
            $table->string('alto_riesgo_causas', 200)->nullable();
            $table->string('otras_cuales', 200)->nullable();

            // Remisiones / asesorías
            $table->string('remitida_especialista', 10)->nullable();
            $table->string('asesoria_vih', 10)->nullable();
            $table->string('asesoria_vih_trimestre', 50)->nullable();

            // Tamizajes VIH
            $table->date('vih_tamiz1_fecha')->nullable();
            $table->string('vih_tamiz1_resultado', 50)->nullable();
            $table->string('vih_tamiz1_trimestre', 50)->nullable();

            $table->date('vih_tamiz2_fecha')->nullable();
            $table->string('vih_tamiz2_resultado', 50)->nullable();
            $table->string('vih_tamiz2_trimestre', 50)->nullable();

            $table->date('vih_tamiz3_fecha')->nullable();
            $table->string('vih_tamiz3_resultado', 50)->nullable();
            $table->string('vih_tamiz3_trimestre', 50)->nullable();

            // Sífilis rápida
            $table->date('sifilis_rapida1_fecha')->nullable();
            $table->string('sifilis_rapida1_resultado', 50)->nullable();
            $table->string('sifilis_rapida1_trimestre', 50)->nullable();

            $table->date('sifilis_rapida2_fecha')->nullable();
            $table->string('sifilis_rapida2_resultado', 50)->nullable();
            $table->string('sifilis_rapida2_trimestre', 50)->nullable();

            $table->date('sifilis_rapida3_fecha')->nullable();
            $table->string('sifilis_rapida3_resultado', 50)->nullable();
            $table->string('sifilis_rapida3_trimestre', 50)->nullable();

            // Otros paraclínicos
            $table->date('vih_confirmatoria_fecha')->nullable();
            $table->string('vih_confirmatoria_trimestre', 50)->nullable();

            $table->date('sifilis_no_trep_fecha')->nullable();
            $table->string('sifilis_no_trep_resultado', 50)->nullable();
            $table->string('sifilis_no_trep_trimestre', 50)->nullable();

            $table->date('urocultivo_fecha')->nullable();
            $table->string('urocultivo_resultado', 50)->nullable();

            $table->date('glicemia_fecha')->nullable();
            $table->string('glicemia_resultado', 50)->nullable();

            $table->date('pto_glucosa_fecha')->nullable();
            $table->string('pto_glucosa_resultado', 50)->nullable();

            $table->date('hemoglobina_fecha')->nullable();
            $table->string('hemoglobina_resultado', 50)->nullable();

            $table->string('hemoclasificacion_resultado', 50)->nullable();

            $table->date('ag_hbs_fecha')->nullable();
            $table->string('ag_hbs_resultado', 50)->nullable();

            $table->date('toxoplasma_fecha')->nullable();
            $table->string('toxoplasma_resultado', 50)->nullable();

            $table->date('rubeola_fecha')->nullable();
            $table->string('rubeola_resultado', 50)->nullable();

            $table->date('citologia_fecha')->nullable();
            $table->string('citologia_resultado', 50)->nullable();

            $table->date('frotis_vaginal_fecha')->nullable();
            $table->string('frotis_vaginal_resultado', 50)->nullable();

            $table->date('estreptococo_fecha')->nullable();
            $table->string('estreptococo_resultado', 50)->nullable();

            $table->date('malaria_fecha')->nullable();
            $table->string('malaria_resultado', 50)->nullable();

            $table->date('chagas_fecha')->nullable();
            $table->string('chagas_resultado', 50)->nullable();

            // Vacunas / controles
            $table->date('vac_influenza_fecha')->nullable();
            $table->date('vac_toxoide_fecha')->nullable();
            $table->date('vac_dpt_acelular_fecha')->nullable();
            $table->date('consulta_odontologica_fecha')->nullable();

            // Ecos
            $table->string('eco_translucencia', 50)->nullable();
            $table->string('eco_anomalias', 50)->nullable();
            $table->string('eco_otras', 100)->nullable();

            // Suministros
            $table->string('suministro_acido_folico', 10)->nullable();
            $table->string('suministro_calcio', 10)->nullable();
            $table->string('suministro_hierro', 10)->nullable();
            $table->string('suministro_asa', 10)->nullable();

            // Otros
            $table->date('desparasitacion_fecha')->nullable();
            $table->string('informacion_en_salud', 10)->nullable();

            // Controles prenatales (CPN)
            $table->date('cpn1_fecha')->nullable();
            $table->string('cpn1_quien', 100)->nullable();
            $table->date('cpn2_fecha')->nullable();
            $table->string('cpn2_quien', 100)->nullable();
            $table->date('cpn3_fecha')->nullable();
            $table->string('cpn3_quien', 100)->nullable();
            $table->date('cpn4_fecha')->nullable();
            $table->string('cpn4_quien', 100)->nullable();
            $table->date('cpn5_fecha')->nullable();
            $table->string('cpn5_quien', 100)->nullable();
            $table->date('cpn6_fecha')->nullable();
            $table->string('cpn6_quien', 100)->nullable();
            $table->date('cpn7_fecha')->nullable();
            $table->string('cpn7_quien', 100)->nullable();
            $table->date('cpn8_fecha')->nullable();
            $table->string('cpn8_quien', 100)->nullable();
            $table->date('cpn9_fecha')->nullable();
            $table->string('cpn9_quien', 100)->nullable();
            $table->integer('num_total_cpn')->nullable();
            $table->string('ultimo_cpn', 100)->nullable();

            // Especialistas
            $table->string('cons_ginecologia_1', 10)->nullable();
            $table->string('cons_ginecologia_2', 10)->nullable();
            $table->string('cons_ginecologia_3', 10)->nullable();
            $table->string('cons_nutricion', 10)->nullable();
            $table->string('cons_psicologia', 10)->nullable();
            $table->string('cons_otro_especialista', 10)->nullable();
            $table->string('cons_otro_quien', 100)->nullable();
            $table->string('especialistas_describe', 200)->nullable();

            // Parto / RN
            $table->string('parto_tipo', 50)->nullable();
            $table->string('parto_sem_gest', 50)->nullable();
            $table->string('parto_complicaciones', 200)->nullable();
            $table->string('uci_materna', 10)->nullable();

            $table->string('its_intraparto_toma', 10)->nullable();
            $table->string('its_intraparto_positivo', 10)->nullable();

            $table->date('defuncion_fecha')->nullable();
            $table->string('defuncion_causa', 200)->nullable();
            $table->string('multiplicidad_embarazo', 50)->nullable();

            // RN1
            $table->string('rn1_registro_civil', 10)->nullable();
            $table->string('rn1_nombre', 150)->nullable();
            $table->string('rn1_sexo', 10)->nullable();
            $table->decimal('rn1_peso', 8, 2)->nullable();
            $table->string('rn1_condicion', 50)->nullable();
            $table->string('rn1_tsh', 50)->nullable();
            $table->string('rn1_hipotiroideo_dx', 10)->nullable();
            $table->string('rn1_trat_hipotiroideo', 10)->nullable();
            $table->string('rn1_uci', 10)->nullable();
            $table->string('rn1_vac_bcg', 10)->nullable();
            $table->string('rn1_vac_hepb', 10)->nullable();

            // RN2
            $table->string('rn2_registro_civil', 10)->nullable();
            $table->string('rn2_nombre', 150)->nullable();
            $table->string('rn2_sexo', 10)->nullable();
            $table->decimal('rn2_peso', 8, 2)->nullable();
            $table->string('rn2_condicion', 50)->nullable();
            $table->string('rn2_tsh', 50)->nullable();
            $table->string('rn2_hipotiroideo_dx', 10)->nullable();
            $table->string('rn2_trat_hipotiroideo', 10)->nullable();
            $table->string('rn2_uci', 10)->nullable();
            $table->string('rn2_vac_bcg', 10)->nullable();
            $table->string('rn2_vac_hepb', 10)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ges_tipo1_seguimientos');
    }
};
