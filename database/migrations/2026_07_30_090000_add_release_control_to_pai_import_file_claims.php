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
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_file_sha256')) {
                $table->char('released_file_sha256', 64)->nullable()->after('file_sha256');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_content_sha256')) {
                $table->char('released_content_sha256', 64)->nullable()->after('content_sha256');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_at')) {
                $table->dateTime('released_at', 0)->nullable()->after('status');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_by_user_id')) {
                $table->unsignedBigInteger('released_by_user_id')->nullable()->after('released_at');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'release_reason')) {
                $table->string('release_reason', 255)->nullable()->after('released_by_user_id');
            }
            if (! Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_from_batch_verifications_id')) {
                $table->unsignedInteger('released_from_batch_verifications_id')->nullable()->after('release_reason');
            }
        });

        DB::connection('sqlsrv')->unprepared("
            IF EXISTS (
                SELECT 1
                FROM sys.key_constraints
                WHERE [name] = 'uq_pai_import_claim_hash_version'
                  AND [parent_object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                ALTER TABLE dbo.pai_import_file_claims
                DROP CONSTRAINT uq_pai_import_claim_hash_version
            END
        ");

        DB::connection('sqlsrv')->unprepared("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE [name] = 'uq_pai_import_claim_content_hash_version'
                  AND [object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                DROP INDEX uq_pai_import_claim_content_hash_version ON dbo.pai_import_file_claims
            END
        ");

        DB::connection('sqlsrv')->unprepared("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE [name] = 'uq_pai_import_claim_hash_version_active'
                  AND [object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                CREATE UNIQUE INDEX uq_pai_import_claim_hash_version_active
                ON dbo.pai_import_file_claims (file_sha256, format_version)
                WHERE released_at IS NULL
            END
        ");

        DB::connection('sqlsrv')->unprepared("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE [name] = 'uq_pai_import_claim_content_hash_version_active'
                  AND [object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                CREATE UNIQUE INDEX uq_pai_import_claim_content_hash_version_active
                ON dbo.pai_import_file_claims (content_sha256, format_version)
                WHERE content_sha256 IS NOT NULL
                  AND released_at IS NULL
            END
        ");
    }

    public function down(): void
    {
        DB::connection('sqlsrv')->unprepared("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE [name] = 'uq_pai_import_claim_hash_version_active'
                  AND [object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                DROP INDEX uq_pai_import_claim_hash_version_active ON dbo.pai_import_file_claims
            END
        ");

        DB::connection('sqlsrv')->unprepared("
            IF EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE [name] = 'uq_pai_import_claim_content_hash_version_active'
                  AND [object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                DROP INDEX uq_pai_import_claim_content_hash_version_active ON dbo.pai_import_file_claims
            END
        ");

        DB::connection('sqlsrv')->unprepared("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.key_constraints
                WHERE [name] = 'uq_pai_import_claim_hash_version'
                  AND [parent_object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                ALTER TABLE dbo.pai_import_file_claims
                ADD CONSTRAINT uq_pai_import_claim_hash_version
                UNIQUE (file_sha256, format_version)
            END
        ");

        DB::connection('sqlsrv')->unprepared("
            IF NOT EXISTS (
                SELECT 1
                FROM sys.indexes
                WHERE [name] = 'uq_pai_import_claim_content_hash_version'
                  AND [object_id] = OBJECT_ID('dbo.pai_import_file_claims')
            )
            BEGIN
                CREATE UNIQUE INDEX uq_pai_import_claim_content_hash_version
                ON dbo.pai_import_file_claims (content_sha256, format_version)
                WHERE content_sha256 IS NOT NULL
            END
        ");

        Schema::connection('sqlsrv')->table('pai_import_file_claims', function (Blueprint $table) {
            $drop = array_values(array_filter([
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_at') ? 'released_at' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_file_sha256') ? 'released_file_sha256' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_content_sha256') ? 'released_content_sha256' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_by_user_id') ? 'released_by_user_id' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'release_reason') ? 'release_reason' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_from_batch_verifications_id') ? 'released_from_batch_verifications_id' : null,
            ]));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
