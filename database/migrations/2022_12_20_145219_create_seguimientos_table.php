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
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estado');
            $table->date('fecha_consulta');
            $table->float('peso_kilos');
            $table->float('talla_cm');
            $table->float('puntajez');
            $table->string('clasificacion');
            $table->string('requerimiento_energia_ftlc');
            $table->date('fecha_entrega_ftlc');
            $table->string('medicamento');
            $table->string('recomendaciones_manejo');
            $table->string('resultados_seguimientos');
            // $table->string('ips_realiza_seguuimiento');
            $table->string('observaciones');
            
            // esto son lo que movi de  sivigilas
            $table->string('est_act_menor')->nullable();
            $table->string('tratamiento_f75')->nullable();
            $table->date('fecha_recibio_tratf75')->nullable();

            $table->date('fecha_proximo_control')->nullable();
            $table->integer('sivigilas_id');//relacion uno a uno 
            $table->foreign('sivigilas_id')->references('id')->on('sivigilas')->onDelete('cascade')->onUpdate('cascade');
            
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
        Schema::dropIfExists('seguimientos');
    }
};
