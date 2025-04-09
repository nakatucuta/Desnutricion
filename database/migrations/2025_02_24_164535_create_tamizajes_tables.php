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
        // Tabla de tipos de tamizajes
        Schema::create('tipo_tamizajes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre')->unique(); // Ej: "TAMIZAJE CANCER DE MAMA"
            $table->timestamps();
        });

        // Tabla de resultados de tamizajes
        Schema::create('resultado_tamizajes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');        // Ej: "0", "4", "5", "21", etc.
            $table->string('description')->nullable(); // Descripción del código
            $table->unsignedInteger('tipo_tamizaje_id'); // Relación con tipo_tamizajes
            $table->foreign('tipo_tamizaje_id')
                  ->references('id')
                  ->on('tipo_tamizajes')
                  ->onDelete('cascade');
            $table->timestamps();
        });

        // Tabla principal de tamizajes
        Schema::create('tamizajes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo_identificacion');   // CC, TI, CE, etc.
            $table->string('numero_identificacion'); // Número de documento
            $table->date('fecha_tamizaje');          // Fecha del tamizaje
            $table->string('numero_carnet')->nullable();
            // Llave foránea al tipo de tamizaje
            $table->unsignedInteger('tipo_tamizaje_id');
            $table->foreign('tipo_tamizaje_id')
                  ->references('id')
                  ->on('tipo_tamizajes');

            // Llave foránea al resultado
            $table->unsignedInteger('resultado_tamizaje_id');
            $table->foreign('resultado_tamizaje_id')
                  ->references('id')
                  ->on('resultado_tamizajes');
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
        Schema::dropIfExists('tamizajes');
        Schema::dropIfExists('resultado_tamizajes');
        Schema::dropIfExists('tipo_tamizajes');
    }
};
