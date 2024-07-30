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
            $table->string('nombre_profesional')->nullable();
            $table->string('numero_profesional')->nullable();
            $table->string('uds')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargue412s', function (Blueprint $table) {
            $table->dropColumn(['nombre_profesional', 'numero_profesional','uds']);
            // Elimina los campos añadidos en up()
        });
    }
};
