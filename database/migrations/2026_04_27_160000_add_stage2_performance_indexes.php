<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->createIndexIfMissing('dbo.afiliados', 'idx_afiliados_user_id', 'CREATE INDEX idx_afiliados_user_id ON dbo.afiliados ([user_id])');
        $this->createIndexIfMissing('dbo.seguimientos', 'idx_seguimientos_user_estado_created', 'CREATE INDEX idx_seguimientos_user_estado_created ON dbo.seguimientos ([user_id], [estado], [created_at])');
        $this->createIndexIfMissing('dbo.ciclo_vida_cache_records', 'idx_cv_cache_range_type', 'CREATE INDEX idx_cv_cache_range_type ON dbo.ciclo_vida_cache_records ([course_key], [module_key], [range_start], [range_end], [record_type])');
        $this->createIndexIfMissing('dbo.ciclo_vida_cache_records', 'idx_cv_cache_type_event_inc', 'CREATE INDEX idx_cv_cache_type_event_inc ON dbo.ciclo_vida_cache_records ([record_type], [event_date]) INCLUDE ([module_key], [rango_edad], [ips_primaria])');
        $this->createIndexIfMissing('dbo.vacunas', 'idx_vacunas_fecha_vacuna', 'CREATE INDEX idx_vacunas_fecha_vacuna ON dbo.vacunas ([fecha_vacuna])');
        $this->createIndexIfMissing('dbo.vacunas', 'idx_vacunas_regimen', 'CREATE INDEX idx_vacunas_regimen ON dbo.vacunas ([regimen])');
        $this->createIndexIfMissing('dbo.import_jobs', 'idx_import_jobs_status_updated', 'CREATE INDEX idx_import_jobs_status_updated ON dbo.import_jobs ([status], [updated_at])');
        $this->createIndexIfMissing('dbo.seguimiento_412s', 'idx_seguimiento412_user_estado_created', 'CREATE INDEX idx_seguimiento412_user_estado_created ON dbo.seguimiento_412s ([user_id], [estado], [created_at]) INCLUDE ([motivo_reapuertura], [fecha_proximo_control], [cargue412_id])');
        $this->createIndexIfMissing('dbo.sivigilas', 'idx_sivigilas_num_ide', 'CREATE INDEX idx_sivigilas_num_ide ON dbo.sivigilas ([num_ide_])');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('dbo.afiliados', 'idx_afiliados_user_id');
        $this->dropIndexIfExists('dbo.seguimientos', 'idx_seguimientos_user_estado_created');
        $this->dropIndexIfExists('dbo.ciclo_vida_cache_records', 'idx_cv_cache_range_type');
        $this->dropIndexIfExists('dbo.ciclo_vida_cache_records', 'idx_cv_cache_type_event_inc');
        $this->dropIndexIfExists('dbo.vacunas', 'idx_vacunas_fecha_vacuna');
        $this->dropIndexIfExists('dbo.vacunas', 'idx_vacunas_regimen');
        $this->dropIndexIfExists('dbo.import_jobs', 'idx_import_jobs_status_updated');
        $this->dropIndexIfExists('dbo.seguimiento_412s', 'idx_seguimiento412_user_estado_created');
        $this->dropIndexIfExists('dbo.sivigilas', 'idx_sivigilas_num_ide');
    }

    private function createIndexIfMissing(string $table, string $indexName, string $createSql): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement($createSql);
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement("DROP INDEX {$indexName} ON {$table}");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT 1 AS [exists] FROM sys.indexes WHERE name = ? AND object_id = OBJECT_ID(?)',
            [$indexName, $table]
        );

        return $row !== null;
    }
};

