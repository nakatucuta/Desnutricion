<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclo_vida_coverage_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('preset_key', 40);
            $table->date('range_from');
            $table->date('range_to');
            $table->longText('payload_json');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['preset_key', 'range_from', 'range_to'], 'cv_cov_snap_unique');
            $table->index(['range_from', 'range_to'], 'cv_cov_snap_range_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclo_vida_coverage_snapshots');
    }
};
