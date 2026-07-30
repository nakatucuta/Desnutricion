<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_identidad_clinica'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
CREATE INDEX idx_vacunas_identidad_clinica
ON dbo.vacunas (afiliado_id, vacunas_id, docis, fecha_vacuna)
");
    }

    public function down(): void
    {
        DB::statement("
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_identidad_clinica'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
DROP INDEX idx_vacunas_identidad_clinica ON dbo.vacunas
");
    }
};
