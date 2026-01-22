<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preconcepcionales', function (Blueprint $table) {

            // ✅ INT porque batches.id es increments()
            $table->integer('created_batch_id')->nullable()->index();
            $table->integer('last_batch_id')->nullable()->index();

            // ✅ SOLO UNA FK con SET NULL (SQL Server permite 1 ruta)
            $table->foreign('created_batch_id')
                ->references('id')->on('preconcepcional_import_batches')
                ->nullOnDelete();

            // ❌ NO hacemos FK para last_batch_id (evita "multiple cascade paths")
            // Si el batch se borra, last_batch_id quedará con un ID "huérfano",
            // pero no pasa nada: es solo historial, y si quieres, lo limpias con job/manual.
        });
    }

    public function down(): void
    {
        Schema::table('preconcepcionales', function (Blueprint $table) {

            // 🔥 primero borramos la FK que sí existe
            $table->dropForeign(['created_batch_id']);

            // luego columnas
            $table->dropColumn(['created_batch_id', 'last_batch_id']);
        });
    }
};
