<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('novedad_reads', 'archived_at')) {
            Schema::table('novedad_reads', function (Blueprint $table) {
                $table->dateTime('archived_at')->nullable()->after('read_at');
                $table->index(['user_id', 'archived_at'], 'ix_novedad_reads_user_archivedat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('novedad_reads', 'archived_at')) {
            Schema::table('novedad_reads', function (Blueprint $table) {
                $table->dropIndex('ix_novedad_reads_user_archivedat');
                $table->dropColumn('archived_at');
            });
        }
    }
};
