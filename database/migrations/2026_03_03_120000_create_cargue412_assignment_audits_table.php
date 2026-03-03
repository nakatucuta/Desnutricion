<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargue412_assignment_audits', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('cargue412_id')->nullable()->index();
            $table->unsignedInteger('performed_by_user_id')->nullable()->index();

            $table->unsignedInteger('old_assigned_user_id')->nullable()->index();
            $table->unsignedInteger('new_assigned_user_id')->nullable()->index();

            $table->string('action_type', 30)->index(); // asignacion|reasignacion|sin_cambio

            $table->string('numero_identificacion', 40)->nullable()->index();
            $table->string('paciente_nombre', 180)->nullable();
            $table->string('municipio', 120)->nullable()->index();
            $table->date('fecha_captacion')->nullable()->index();

            $table->string('old_assigned_name', 180)->nullable();
            $table->string('new_assigned_name', 180)->nullable();
            $table->string('old_assigned_email', 180)->nullable();
            $table->string('new_assigned_email', 180)->nullable();
            $table->string('old_assigned_code', 60)->nullable();
            $table->string('new_assigned_code', 60)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('changes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargue412_assignment_audits');
    }
};

