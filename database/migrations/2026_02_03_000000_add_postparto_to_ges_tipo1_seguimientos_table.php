<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ges_tipo1_seguimientos', function (Blueprint $table) {

            // =========================
            // POSTPARTO - DATOS DEL MENOR
            // =========================
            $table->string('pp_menor_tipo_documento', 10)->nullable();
            $table->string('pp_menor_numero_identificacion', 50)->nullable();
            $table->string('pp_menor_apellido_1', 100)->nullable();
            $table->string('pp_menor_apellido_2', 100)->nullable();
            $table->string('pp_menor_nombre_1', 100)->nullable();
            $table->string('pp_menor_nombre_2', 100)->nullable();
            $table->date('pp_menor_fecha_nacimiento')->nullable();
            $table->integer('pp_menor_edad')->nullable();

            // =========================
            // POSTPARTO - EVALUACIÓN CLÍNICA COMPLETA (MENOR)
            // =========================
            $table->decimal('pp_menor_temperatura', 5, 2)->nullable();
            $table->integer('pp_menor_frecuencia_cardiaca')->nullable();
            $table->integer('pp_menor_frecuencia_respiratoria')->nullable();
            $table->decimal('pp_menor_peso', 8, 2)->nullable();
            $table->decimal('pp_menor_talla', 8, 2)->nullable();
            $table->decimal('pp_menor_imc', 8, 2)->nullable();
            $table->decimal('pp_menor_perimetro_cefalico', 8, 2)->nullable();
            $table->text('pp_menor_examen_fisico')->nullable();

            // =========================
            // POSTPARTO - ALIMENTACIÓN Y LACTANCIA
            // =========================
            $table->string('pp_tipo_alimentacion', 100)->nullable();
            $table->string('pp_educacion_tecnica_agarre', 10)->nullable(); // SI/NO

            // =========================
            // POSTPARTO - VACUNACIÓN
            // =========================
            $table->string('pp_vac_bcg', 10)->nullable();          // SI/NO
            $table->string('pp_vac_hepatitis_b', 10)->nullable();  // SI/NO

            // =========================
            // POSTPARTO - SIGNOS DE ALARMA (MENOR)
            // =========================
            $table->string('pp_alarma_fiebre', 10)->nullable(); // SI/NO
            $table->string('pp_alarma_dificultad_respiratoria', 10)->nullable(); // SI/NO
            $table->string('pp_alarma_vomitos', 10)->nullable(); // SI/NO
            $table->string('pp_alarma_alteraciones_ombligo', 10)->nullable(); // SI/NO
            $table->string('pp_clasificacion_riesgo_menor', 100)->nullable();

            // =========================
            // POSTPARTO - REGISTRO Y SEGUIMIENTO
            // =========================
            $table->text('pp_programacion_proximos_controles')->nullable();

            // =========================
            // POSTPARTO - VALORACIÓN GENERAL (MADRE)
            // =========================
            $table->string('pp_madre_presion_arterial', 20)->nullable();
            $table->integer('pp_madre_frecuencia_cardiaca')->nullable();
            $table->integer('pp_madre_frecuencia_respiratoria')->nullable();
            $table->decimal('pp_madre_temperatura', 5, 2)->nullable();
            $table->text('pp_madre_examen_fisico')->nullable();

            // =========================
            // POSTPARTO - SALUD MENTAL Y EMOCIONAL
            // =========================
            $table->string('pp_tamizaje_depresion_posparto', 150)->nullable();
            $table->text('pp_evaluacion_ansiedad_estres')->nullable();
            $table->text('pp_redes_apoyo')->nullable();

            // =========================
            // POSTPARTO - NUTRICIÓN Y RECUPERACIÓN FÍSICA
            // =========================
            $table->string('pp_entrega_hierro', 10)->nullable();       // SI/NO
            $table->string('pp_entrega_acido_folico', 10)->nullable(); // SI/NO
            $table->string('pp_entrega_calcio', 10)->nullable();       // SI/NO

            // =========================
            // POSTPARTO - MEDICACIÓN Y ADHERENCIA
            // =========================
            $table->text('pp_revision_medicamentos')->nullable();
            $table->text('pp_educacion_uso_medicamentos')->nullable();

            // =========================
            // POSTPARTO - PLANIFICACIÓN FAMILIAR
            // =========================
            $table->text('pp_consejeria_metodos_anticonceptivos')->nullable();
            $table->date('pp_fecha_colocacion_metodo')->nullable();
            $table->string('pp_metodo', 150)->nullable();
        });
    }

    public function down()
    {
        Schema::table('ges_tipo1_seguimientos', function (Blueprint $table) {
            $table->dropColumn([
                'pp_menor_tipo_documento',
                'pp_menor_numero_identificacion',
                'pp_menor_apellido_1',
                'pp_menor_apellido_2',
                'pp_menor_nombre_1',
                'pp_menor_nombre_2',
                'pp_menor_fecha_nacimiento',
                'pp_menor_edad',

                'pp_menor_temperatura',
                'pp_menor_frecuencia_cardiaca',
                'pp_menor_frecuencia_respiratoria',
                'pp_menor_peso',
                'pp_menor_talla',
                'pp_menor_imc',
                'pp_menor_perimetro_cefalico',
                'pp_menor_examen_fisico',

                'pp_tipo_alimentacion',
                'pp_educacion_tecnica_agarre',

                'pp_vac_bcg',
                'pp_vac_hepatitis_b',

                'pp_alarma_fiebre',
                'pp_alarma_dificultad_respiratoria',
                'pp_alarma_vomitos',
                'pp_alarma_alteraciones_ombligo',
                'pp_clasificacion_riesgo_menor',

                'pp_programacion_proximos_controles',

                'pp_madre_presion_arterial',
                'pp_madre_frecuencia_cardiaca',
                'pp_madre_frecuencia_respiratoria',
                'pp_madre_temperatura',
                'pp_madre_examen_fisico',

                'pp_tamizaje_depresion_posparto',
                'pp_evaluacion_ansiedad_estres',
                'pp_redes_apoyo',

                'pp_entrega_hierro',
                'pp_entrega_acido_folico',
                'pp_entrega_calcio',

                'pp_revision_medicamentos',
                'pp_educacion_uso_medicamentos',

                'pp_consejeria_metodos_anticonceptivos',
                'pp_fecha_colocacion_metodo',
                'pp_metodo',
            ]);
        });
    }
};
