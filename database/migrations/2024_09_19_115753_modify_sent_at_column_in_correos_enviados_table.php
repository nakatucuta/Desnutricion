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
        Schema::table('correos_enviados', function (Blueprint $table) {
              // Cambiar el tipo de la columna sent_at a varchar
              $table->string('sent_at', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correos_enviados', function (Blueprint $table) {
          // Revertir el cambio a datetime si es necesario
          $table->datetime('sent_at')->change();
        });
    }
};
