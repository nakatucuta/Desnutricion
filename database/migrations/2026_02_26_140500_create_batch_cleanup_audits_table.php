<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_cleanup_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 30)->default('bulk_delete')->index();
            $table->unsignedInteger('batches_count')->default(0);
            $table->unsignedInteger('afiliados_count')->default(0);
            $table->unsignedInteger('vacunas_count')->default(0);
            $table->text('batch_ids')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_cleanup_audits');
    }
};

