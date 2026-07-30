<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv')->table('pai_import_file_claims', function (Blueprint $table) {
            $drop = array_values(array_filter([
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_file_sha256') ? 'released_file_sha256' : null,
                Schema::connection('sqlsrv')->hasColumn('pai_import_file_claims', 'released_content_sha256') ? 'released_content_sha256' : null,
            ]));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
