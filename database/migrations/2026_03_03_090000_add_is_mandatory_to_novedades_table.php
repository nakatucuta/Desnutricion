<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('novedades', 'is_mandatory')) {
            Schema::table('novedades', function (Blueprint $table) {
                $table->boolean('is_mandatory')->default(false)->after('is_active');
                $table->index(['is_active', 'is_mandatory'], 'ix_novedades_active_mandatory');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('novedades', 'is_mandatory')) {
            Schema::table('novedades', function (Blueprint $table) {
                $table->dropIndex('ix_novedades_active_mandatory');
                $table->dropColumn('is_mandatory');
            });
        }
    }
};

