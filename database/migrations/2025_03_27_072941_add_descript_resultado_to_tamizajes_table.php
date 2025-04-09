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
        Schema::table('tamizajes', function (Blueprint $table) {
            // Agregamos el nuevo campo descript_resultado como string
            $table->string('descript_resultado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tamizajes', function (Blueprint $table) {
              // Eliminamos la columna si damos rollback
              $table->dropColumn('descript_resultado');
        });
    }
};
