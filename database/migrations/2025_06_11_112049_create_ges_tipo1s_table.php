<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGesTipo1sTable extends Migration
{
    public function up()
    {
        Schema::create('ges_tipo1', function (Blueprint $table) {
            $table->increments('id');

            // Relación uno-a-muchos con users
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Campos existentes...
            $table->string('tipo_de_registro');
            $table->integer('consecutivo');
            $table->integer('pais_de_la_nacionalidad');
            $table->integer('municipio_de_residencia_habitual');
            $table->string('zona_territorial_de_residencia');
            $table->string('codigo_de_habilitacion_ips_primaria_de_la_gestante');
            $table->string('tipo_de_identificacion_de_la_usuaria');
            $table->string('no_id_del_usuario');
            $table->string('numero_carnet');
            $table->string('primer_apellido');
            $table->string('segundo_apellido');
            $table->string('primer_nombre');
            $table->string('segundo_nombre');
            $table->date  ('fecha_de_nacimiento');
            $table->integer('codigo_pertenencia_etnica');
            $table->integer('codigo_de_ocupacion');
            $table->integer('codigo_nivel_educativo_de_la_gestante');
            $table->date  ('fecha_probable_de_parto');
            $table->text  ('direccion_de_residencia_de_la_gestante');
            $table->tinyInteger('antecedente_hipertension_cronica');
            $table->tinyInteger('antecedente_preeclampsia');
            $table->tinyInteger('antecedente_diabetes');
            $table->tinyInteger('antecedente_les_enfermedad_autoinmune');
            $table->tinyInteger('antecedente_sindrome_metabolico');
            $table->tinyInteger('antecedente_erc');
            $table->tinyInteger('antecedente_trombofilia_o_trombosis_venosa_profunda');
            $table->tinyInteger('antecedentes_anemia_celulas_falciformes');
            $table->tinyInteger('antecedente_sepsis_durante_gestaciones_previas');
            $table->tinyInteger('consumo_tabaco_durante_la_gestacion');
            $table->integer('periodo_intergenesico');
            $table->tinyInteger('embarazo_multiple');
            $table->string('metodo_de_concepcion');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ges_tipo1');
    }
}
