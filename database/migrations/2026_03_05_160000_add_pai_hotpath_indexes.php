<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vacunas: indices orientados a consultas criticas de PAI.
        DB::statement("
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_afiliado_fecha_vacuna'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
CREATE INDEX idx_vacunas_afiliado_fecha_vacuna
ON dbo.vacunas (afiliado_id, fecha_vacuna)
");

        DB::statement("
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_afiliado_vacuna_docis'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
CREATE INDEX idx_vacunas_afiliado_vacuna_docis
ON dbo.vacunas (afiliado_id, vacunas_id, docis)
");

        DB::statement("
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_batch_afiliado'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
CREATE INDEX idx_vacunas_batch_afiliado
ON dbo.vacunas (batch_verifications_id, afiliado_id)
");

        // Queue table: reduce scans al hacer pop de jobs en BD.
        DB::statement("
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_jobs_queue_reserved_available_id'
      AND object_id = OBJECT_ID('dbo.jobs')
)
CREATE INDEX idx_jobs_queue_reserved_available_id
ON dbo.jobs (queue, reserved_at, available_at, id)
");
    }

    public function down(): void
    {
        DB::statement("
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_jobs_queue_reserved_available_id'
      AND object_id = OBJECT_ID('dbo.jobs')
)
DROP INDEX idx_jobs_queue_reserved_available_id ON dbo.jobs
");

        DB::statement("
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_batch_afiliado'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
DROP INDEX idx_vacunas_batch_afiliado ON dbo.vacunas
");

        DB::statement("
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_afiliado_vacuna_docis'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
DROP INDEX idx_vacunas_afiliado_vacuna_docis ON dbo.vacunas
");

        DB::statement("
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'idx_vacunas_afiliado_fecha_vacuna'
      AND object_id = OBJECT_ID('dbo.vacunas')
)
DROP INDEX idx_vacunas_afiliado_fecha_vacuna ON dbo.vacunas
");
    }
};

