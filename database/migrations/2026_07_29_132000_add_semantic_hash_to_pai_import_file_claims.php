<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv')->table('pai_import_file_claims', function (Blueprint $table) {
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_sha256')) {
                $table->char('content_sha256', 64)->nullable()->after('file_sha256');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_rows')) {
                $table->unsignedInteger('content_rows')->nullable()->after('file_size');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_columns')) {
                $table->unsignedInteger('content_columns')->nullable()->after('content_rows');
            }
        });

        Schema::connection('sqlsrv')->table('import_jobs', function (Blueprint $table) {
            if (! Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_sha256')) {
                $table->char('content_sha256', 64)->nullable()->after('file_sha256');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_rows')) {
                $table->unsignedInteger('content_rows')->nullable()->after('file_size');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_columns')) {
                $table->unsignedInteger('content_columns')->nullable()->after('content_rows');
            }
        });

        DB::connection('sqlsrv')->statement("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'uq_pai_import_claim_content_hash_version'
                  AND object_id = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                CREATE UNIQUE INDEX uq_pai_import_claim_content_hash_version
                ON dbo.pai_import_file_claims (content_sha256, format_version)
                WHERE content_sha256 IS NOT NULL
            END
        ");
    }

    public function down(): void
    {
        DB::connection('sqlsrv')->statement("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE name = 'uq_pai_import_claim_content_hash_version'
                  AND object_id = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                DROP INDEX uq_pai_import_claim_content_hash_version ON dbo.pai_import_file_claims
            END
        ");

        Schema::connection('sqlsrv')->table('import_jobs', function (Blueprint $table) {
            $drop = array_values(array_filter([
                Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_sha256') ? 'content_sha256' : null,
                Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_rows') ? 'content_rows' : null,
                Schema::connection('sqlsrv')->hasColumn('import_jobs', 'content_columns') ? 'content_columns' : null,
            ]));
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });

        Schema::connection('sqlsrv')->table('pai_import_file_claims', function (Blueprint $table) {
            $drop = array_values(array_filter([
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_sha256') ? 'content_sha256' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_rows') ? 'content_rows' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'content_columns') ? 'content_columns' : null,
            ]));
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
