<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('import_jobs', 'errors')) {
                $table->longText('errors')->nullable(); // guardaremos JSON
            }
            if (!Schema::hasColumn('import_jobs', 'errors_count')) {
                $table->integer('errors_count')->default(0);
            }
            if (!Schema::hasColumn('import_jobs', 'report_path')) {
                $table->string('report_path', 255)->nullable();
            }
            if (!Schema::hasColumn('import_jobs', 'batch_verifications_id')) {
                $table->bigInteger('batch_verifications_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('import_jobs', 'errors')) $table->dropColumn('errors');
            if (Schema::hasColumn('import_jobs', 'errors_count')) $table->dropColumn('errors_count');
            if (Schema::hasColumn('import_jobs', 'report_path')) $table->dropColumn('report_path');
            if (Schema::hasColumn('import_jobs', 'batch_verifications_id')) $table->dropColumn('batch_verifications_id');
        });
    }
};
