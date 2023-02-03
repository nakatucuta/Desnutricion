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
        Schema::create('ingresos', function (Blueprint $table) {
            $table->increments('id');
            $table->date('Fecha_ingreso_ingres');
            $table->float('peso_ingres');
            $table->float('talla_ingres');
            $table->float('puntaje_z');
            $table->string('calificacion');
            $table->string('Edema');
            $table->string('Emaciacion');
            $table->float('perimetro_brazo');
            $table->string('interpretacion_p_braqueal');
            $table->integer('requ_energia_dia');
            $table->string('mes_entrega_FTLC');
            $table->date('fecha_entrega_FTLC')->nullable();
            $table->string('Menor_anos_des_aguda');
            $table->string('medicamentos');
            $table->string('remite_alguna_inst_apoyo');
            $table->string('Nom_ips_at_prim');
            $table->integer('estado');
            $table->integer('sivigilas_id')->unsigned();//relacion uno a uno 
            $table->foreign('sivigilas_id')->references('id')->on('sivigilas')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('ingresos');
    }
};
