<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE ges_tipo1
            ALTER COLUMN municipio_de_residencia_habitual VARCHAR(5) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE ges_tipo1
            ALTER COLUMN municipio_de_residencia_habitual INT NOT NULL
        ");
    }
};
