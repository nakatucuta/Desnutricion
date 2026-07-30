<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv')->create('pai_import_file_claims', function (Blueprint $table) {
            $table->id();
            $table->char('file_sha256', 64);
            $table->char('content_sha256', 64)->nullable();
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('content_rows')->nullable();
            $table->unsignedInteger('content_columns')->nullable();
            $table->string('format_version', 80);
            $table->string('first_original_name', 255);
            $table->string('last_original_name', 255);
            $table->unsignedBigInteger('first_user_id');
            $table->unsignedBigInteger('last_user_id');
            $table->unsignedBigInteger('current_import_job_id')->nullable();
            $table->unsignedInteger('batch_verifications_id')->nullable();
            $table->string('status', 20)->default('queued');
            $table->unsignedInteger('submission_count')->default(1);
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamps(0);

            $table->unique(
                ['file_sha256', 'format_version'],
                'uq_pai_import_claim_hash_version'
            );
            $table->index('current_import_job_id', 'idx_pai_import_claim_job');
        });

        DB::connection('sqlsrv')->statement("
            CREATE UNIQUE INDEX uq_pai_import_claim_content_hash_version
            ON dbo.pai_import_file_claims (content_sha256, format_version)
            WHERE content_sha256 IS NOT NULL
        ");

        Schema::connection('sqlsrv')->table('import_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('file_claim_id')->nullable();
            $table->char('file_sha256', 64)->nullable();
            $table->char('content_sha256', 64)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('content_rows')->nullable();
            $table->unsignedInteger('content_columns')->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('format_version', 80)->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->boolean('retryable')->default(false);

            $table->index('file_claim_id', 'idx_import_jobs_file_claim');
        });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv')->table('import_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_import_jobs_file_claim');
            $table->dropColumn([
                'file_claim_id',
                'file_sha256',
                'content_sha256',
                'file_size',
                'content_rows',
                'content_columns',
                'original_name',
                'format_version',
                'retry_count',
                'retryable',
            ]);
        });

        Schema::connection('sqlsrv')->dropIfExists('pai_import_file_claims');
    }
};
