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
            $table->integer('cod_eve');
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

           
            $table->string('Caso_confirmada_desnutricion_etiologia_primaria')->nullable();
            $table->string('Ips_manejo_hospitalario')->nullable();
         
           
           
            $table->string('nombreips_manejo_hospita')->nullable();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
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
