<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('preconcepcional_import_batches', 'rows_duplicate')) {
            Schema::table('preconcepcional_import_batches', function (Blueprint $table) {
                $table->integer('rows_duplicate')->default(0)->after('rows_skipped');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('preconcepcional_import_batches', 'rows_duplicate')) {
            Schema::table('preconcepcional_import_batches', function (Blueprint $table) {
                $table->dropColumn('rows_duplicate');
            });
        }
    }
};
