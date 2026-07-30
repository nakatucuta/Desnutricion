<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('sqlsrv')->statement("
            IF OBJECT_ID('dbo.pai_import_file_claims', 'U') IS NOT NULL
            BEGIN
                ALTER TABLE dbo.pai_import_file_claims ALTER COLUMN created_at datetime2(0) NULL;
                ALTER TABLE dbo.pai_import_file_claims ALTER COLUMN updated_at datetime2(0) NULL;
            END
        ");
    }

    public function down(): void
    {
        DB::connection('sqlsrv')->statement("
            IF OBJECT_ID('dbo.pai_import_file_claims', 'U') IS NOT NULL
            BEGIN
                ALTER TABLE dbo.pai_import_file_claims ALTER COLUMN created_at datetime NULL;
                ALTER TABLE dbo.pai_import_file_claims ALTER COLUMN updated_at datetime NULL;
            END
        ");
    }
};
