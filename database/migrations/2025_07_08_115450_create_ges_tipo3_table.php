<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
                Schema::create('ges_tipo3', function (Blueprint $table) {
                $table->increments('id');

                       $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');

                $table->unsignedInteger('ges_tipo1_id');
                $table->foreign('ges_tipo1_id')
                    ->references('id')
                    ->on('ges_tipo1')
                    ->onDelete('cascade');

                $table->unsignedInteger('batch_verifications_id');
                $table->foreign('batch_verifications_id')
                  ->references('id')
                  ->on('batch_verifications');

                $table->string('tipo_de_registro');
                $table->integer('consecutivo_de_registro');
                $table->string('tipo_identificacion_de_la_usuaria');
                $table->string('no_id_del_usuario');
                $table->date  ('fecha_tecnologia_en_salud');
                $table->string('codigo_cups_de_la_tecnologia_en_salud')->nullable();
                $table->string('finalidad_de_la_tecnologia_en_salud')->nullable();
                $table->integer('clasificacion_riesgo_gestacional')->nullable();
                $table->integer('clasificacion_riesgo_preeclampsia')->nullable();
                $table->boolean('suministro_acido_acetilsalicilico_ASA')->nullable();
                $table->boolean('suministro_acido_folico_en_el_control_prenatal')->nullable();
                $table->boolean('suministro_sulfato_ferroso_en_el_control_prenatal')->nullable();
                $table->boolean('suministro_calcio_en_el_control_prenatal')->nullable();
                $table->date   ('fecha_suministro_de_anticonceptivo_post_evento_obstetrico')->nullable();
                $table->boolean('suministro_metodo_anticonceptivo_post_evento_obstetrico')->nullable();
                $table->date   ('fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea')->nullable();
                $table->date   ('fecha_de_terminacion_de_la_gestacion')->nullable();
                $table->integer('tipo_de_terminacion_de_la_gestacion')->nullable();
                $table->integer('tension_arterial_sistolica_PAS_mmHg')->nullable();
                $table->integer('tension_arterial_diastolica_PAD_mmHg')->nullable();
                $table->decimal('indice_de_masa_corporal', 5, 2)->nullable();
                $table->decimal('resultado_de_la_hemoglobina', 5, 2)->nullable();
                $table->decimal('indice_de_pulsatilidad_de_arterias_uterinas', 6, 3)->nullable();

           

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ges_tipo3');
    }
};
