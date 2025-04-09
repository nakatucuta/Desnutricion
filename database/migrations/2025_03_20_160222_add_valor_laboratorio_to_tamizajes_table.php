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
            // Si deseas un valor decimal:
            // $table->decimal('valor_laboratorio', 8, 2)->nullable();
            // O si quieres texto (ej: ">11 mmHg"):
            $table->string('valor_laboratorio')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tamizajes', function (Blueprint $table) {
            $table->dropColumn('valor_laboratorio');
        });
    }
};
