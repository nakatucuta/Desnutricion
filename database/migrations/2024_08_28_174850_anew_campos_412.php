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
        Schema::table('cargue412s', function (Blueprint $table) {
            $table->integer('estado_anulado')->nullable();
            $table->string('observaciones', 1000)->nullable(); // Establece una longitud máxima de 500 caracteres

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargue412s', function (Blueprint $table) {
            $table->integer('estado_anulado')->nullable();
            $table->string('observaciones', 1000)->nullable(); // Establece una longitud máxima de 500 caracteres

        });
    }
};
