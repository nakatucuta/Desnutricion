<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 64)->unique();

            $table->string('status', 20)->default('queued'); // queued|running|done|failed
            $table->unsignedTinyInteger('percent')->default(0);
            $table->string('step')->nullable();
            $table->text('message')->nullable();

            $table->longText('errors')->nullable(); // json
            $table->unsignedInteger('errors_count')->default(0);

            $table->string('report_path')->nullable();
            $table->unsignedBigInteger('batch_verifications_id')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};
