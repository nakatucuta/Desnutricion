<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) borrar índice único actual
        DB::statement("DROP INDEX precon_doc_id_unique ON dbo.preconcepcionales");

        // 2) crear nuevo índice único por lote
        DB::statement("CREATE UNIQUE INDEX precon_doc_id_unique_batch
            ON dbo.preconcepcionales (created_batch_id, tipo_documento, numero_identificacion)");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX precon_doc_id_unique_batch ON dbo.preconcepcionales");

        DB::statement("CREATE UNIQUE INDEX precon_doc_id_unique
            ON dbo.preconcepcionales (tipo_documento, numero_identificacion)");
    }
};
