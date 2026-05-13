<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ciclo_vida_coverage_snapshots', function (Blueprint $table): void {
            $table->string('filter_key', 64)->default('default')->after('range_to');
            $table->longText('filter_json')->nullable()->after('filter_key');
        });

        DB::statement("UPDATE ciclo_vida_coverage_snapshots SET filter_key = 'default' WHERE filter_key IS NULL OR filter_key = ''");

        Schema::table('ciclo_vida_coverage_snapshots', function (Blueprint $table): void {
            $table->dropUnique('cv_cov_snap_unique');
            $table->unique(['preset_key', 'range_from', 'range_to', 'filter_key'], 'cv_cov_snap_unique_v2');
            $table->index(['filter_key', 'range_from', 'range_to'], 'cv_cov_snap_filter_range_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ciclo_vida_coverage_snapshots', function (Blueprint $table): void {
            $table->dropUnique('cv_cov_snap_unique_v2');
            $table->dropIndex('cv_cov_snap_filter_range_idx');
            $table->unique(['preset_key', 'range_from', 'range_to'], 'cv_cov_snap_unique');
            $table->dropColumn(['filter_key', 'filter_json']);
        });
    }
};
