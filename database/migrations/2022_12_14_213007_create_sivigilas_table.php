<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sivigilas', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('cod_eve');
            $table->integer('semana')->nullable();
            $table->date('fec_not');
            $table->string('year');
            $table->string('dpto')->nullable();
            $table->string('mun')->nullable();
            $table->string('tip_ide_');
            $table->string('num_ide_');
            $table->string('pri_nom_');
            $table->string('seg_nom_')->nullable();
            $table->string('pri_ape_');
            $table->string('seg_ape_')->nullable();
            $table->integer('edad_');
            $table->string('sexo_');
            $table->date('fecha_nto_');
            $table->integer('edad_ges');
            $table->string('telefono_');
            $table->string('nom_grupo_')->nullable();
            $table->string('regimen')->nullable();
            $table->string('Ips_at_inicial')->nullable();
            $table->integer('estado');
            $table->date('fecha_aten_inicial')->nullable();

            $table->string('Ips_seguimiento_Ambulatorio')->nullable();
            $table->string('Caso_confirmada_desnutricion_etiologia_primaria')->nullable();
            $table->integer('Tipo_ajuste')->nullable();
            $table->integer('Promedio_dias_oportuna_remision')->nullable();
            $table->string('Esquemq_complrto_pai_edad')->nullable();
            $table->string('Atecion_primocion_y_mantenimiento_res3280_2018')->nullable();
            $table->string('est_act_menor')->nullable();
            $table->string('tratamiento_f75')->nullable();
            $table->date('fecha_recibio_tratf75')->nullable();
            $table->string('nombreips_manejo_hospita')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sivigilas');
    }
};
