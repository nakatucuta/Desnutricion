<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->createIndexIfMissing(
            'dbo.ciclo_vida_cache_records',
            'idx_cv_cache_event_patient_rollup',
            'CREATE INDEX idx_cv_cache_event_patient_rollup ON dbo.ciclo_vida_cache_records ([record_type], [event_date], [course_key], [module_key], [tipo_identificacion], [identificacion]) INCLUDE ([ips_primaria])'
        );
    }

    public function down(): void
    {
        $this->dropIndexIfExists('dbo.ciclo_vida_cache_records', 'idx_cv_cache_event_patient_rollup');
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
