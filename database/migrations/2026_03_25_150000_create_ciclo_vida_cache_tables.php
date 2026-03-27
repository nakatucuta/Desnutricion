<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclo_vida_cache_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('course_key', 80);
            $table->string('module_key', 80);
            $table->string('module_label', 160)->nullable();
            $table->date('range_start');
            $table->date('range_end');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('records_loaded')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['course_key', 'module_key']);
            $table->index(['status', 'started_at']);
        });

        Schema::create('ciclo_vida_cache_records', function (Blueprint $table): void {
            $table->id();
            $table->string('course_key', 80);
            $table->string('module_key', 80);
            $table->string('module_label', 160)->nullable();
            $table->string('record_type', 30)->default('event');
            $table->date('range_start');
            $table->date('range_end');
            $table->date('event_date')->nullable();
            $table->string('tipo_identificacion', 20)->nullable();
            $table->string('identificacion', 40)->nullable();
            $table->string('primer_nombre', 120)->nullable();
            $table->string('segundo_nombre', 120)->nullable();
            $table->string('primer_apellido', 120)->nullable();
            $table->string('segundo_apellido', 120)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad')->nullable();
            $table->integer('edad_meses')->nullable();
            $table->string('rango_edad', 80)->nullable();
            $table->string('codigo_ips', 50)->nullable();
            $table->string('ips_primaria', 200)->nullable();
            $table->string('codigo_servicio', 60)->nullable();
            $table->string('descripcion_servicio', 255)->nullable();
            $table->string('diagnostico_principal', 60)->nullable();
            $table->string('finalidad', 60)->nullable();
            $table->string('record_hash', 64);
            $table->longText('payload')->nullable();
            $table->unsignedBigInteger('source_run_id')->nullable();
            $table->timestamps();

            $table->unique('record_hash');
            $table->index(['course_key', 'module_key', 'event_date'], 'cv_cache_course_module_event_idx');
            $table->index(['course_key', 'module_key', 'identificacion'], 'cv_cache_course_module_doc_idx');
            $table->index(['course_key', 'module_key', 'range_start', 'range_end'], 'cv_cache_course_module_range_idx');
            $table->index(['course_key', 'module_key', 'record_type'], 'cv_cache_course_module_type_idx');
        });

        Schema::create('ciclo_vida_cache_summaries', function (Blueprint $table): void {
            $table->id();
            $table->string('course_key', 80);
            $table->string('module_key', 80);
            $table->date('range_start');
            $table->date('range_end');
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('unique_patients')->default(0);
            $table->unsignedInteger('unique_ips')->default(0);
            $table->unsignedInteger('unique_services')->default(0);
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->unique(['course_key', 'module_key', 'range_start', 'range_end'], 'cv_cache_summaries_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclo_vida_cache_summaries');
        Schema::dropIfExists('ciclo_vida_cache_records');
        Schema::dropIfExists('ciclo_vida_cache_runs');
    }
};
