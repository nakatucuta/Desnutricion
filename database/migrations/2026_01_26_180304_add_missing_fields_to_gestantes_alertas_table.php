<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gestantes_alertas', function (Blueprint $table) {

            // ✅ Campos que usa tu service/model
            if (!Schema::hasColumn('gestantes_alertas', 'examen')) {
                $table->string('examen', 150)->nullable()->after('campo');
            }

            if (!Schema::hasColumn('gestantes_alertas', 'severidad')) {
                $table->string('severidad', 20)->nullable()->after('resultado'); // baja/media/alta
            }

            if (!Schema::hasColumn('gestantes_alertas', 'hash')) {
                $table->string('hash', 64)->nullable()->after('pdf_path')->index();
            }

            if (!Schema::hasColumn('gestantes_alertas', 'seen_at')) {
                $table->dateTime('seen_at')->nullable()->after('correo_enviado_at');
            }

            if (!Schema::hasColumn('gestantes_alertas', 'resolved_at')) {
                $table->dateTime('resolved_at')->nullable()->after('seen_at');
            }
        });

        /**
         * ✅ SQL Server: UNIQUE normal en hash puede fallar con NULLs
         * Mejor: índice único filtrado (solo cuando hash no es null)
         */
        DB::statement("
            IF NOT EXISTS (
                SELECT 1 FROM sys.indexes 
                WHERE name = 'gestantes_alertas_hash_unique' 
                AND object_id = OBJECT_ID('gestantes_alertas')
            )
            CREATE UNIQUE INDEX gestantes_alertas_hash_unique
            ON gestantes_alertas (hash)
            WHERE hash IS NOT NULL
        ");
    }

    public function down(): void
    {
        // eliminar índice filtrado si existe
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes 
                WHERE name = 'gestantes_alertas_hash_unique' 
                AND object_id = OBJECT_ID('gestantes_alertas')
            )
            DROP INDEX gestantes_alertas_hash_unique ON gestantes_alertas
        ");

        Schema::table('gestantes_alertas', function (Blueprint $table) {
            if (Schema::hasColumn('gestantes_alertas', 'resolved_at')) $table->dropColumn('resolved_at');
            if (Schema::hasColumn('gestantes_alertas', 'seen_at')) $table->dropColumn('seen_at');
            if (Schema::hasColumn('gestantes_alertas', 'hash')) $table->dropColumn('hash');
            if (Schema::hasColumn('gestantes_alertas', 'severidad')) $table->dropColumn('severidad');
            if (Schema::hasColumn('gestantes_alertas', 'examen')) $table->dropColumn('examen');
        });
    }
};
