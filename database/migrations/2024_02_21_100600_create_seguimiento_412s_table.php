<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seguimiento_412s', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estado');
            $table->date('fecha_consulta');
            $table->float('peso_kilos');
            $table->float('talla_cm');
            $table->float('puntajez');
            $table->string('clasificacion');
            // $table->string('requerimiento_energia_ftlc');
            // $table->date('fecha_entrega_ftlc')->nullable();
            $table->string('medicamento');
             $table->string('motivo_reapuertura')->nullable();
            // $table->string('resultados_seguimientos');
            // $table->string('ips_realiza_seguuimiento');
            $table->string('observaciones', 1000); // Establece una longitud máxima de 500 caracteres

            
            // esto son lo que movi de  sivigilas
            $table->string('est_act_menor')->nullable();
            // $table->string('tratamiento_f75')->nullable();
            // $table->date('fecha_recibio_tratf75')->nullable();

            $table->date('fecha_proximo_control')->nullable();
            $table->string('pdf')->nullable();
            $table->integer('cargue412_id');//relacion uno a uno 
            $table->foreign('cargue412_id')->references('id')->on('cargue412s')->onDelete('cascade')->onUpdate('cascade');
            //ESQUEMA PAI Y ATENCION Y PROM MANTENIMIENTO
            $table->string('Esquemq_complrto_pai_edad')->nullable();
            $table->string('Atecion_primocion_y_mantenimiento_res3280_2018')->nullable();
            //AQUI TERMINA 
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_412s');
    }
};
