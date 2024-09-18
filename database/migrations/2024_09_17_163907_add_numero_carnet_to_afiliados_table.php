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
        Schema::table('afiliados', function (Blueprint $table) {
            $table->string('numero_carnet')->nullable()->after('numero_identificacion');  // Agregar campo numero_carnet después del campo numero_identificacion

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afiliados', function (Blueprint $table) {
            $table->dropColumn('numero_carnet');  // Eliminar el campo en caso de revertir la migración

        });
    }
};
