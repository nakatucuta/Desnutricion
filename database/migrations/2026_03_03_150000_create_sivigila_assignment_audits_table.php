<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sivigila_assignment_audits', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('sivigila_id')->nullable()->index();
            $table->unsignedInteger('performed_by_user_id')->nullable()->index();

            $table->unsignedInteger('old_assigned_user_id')->nullable()->index();
            $table->unsignedInteger('new_assigned_user_id')->nullable()->index();

            $table->string('action_type', 30)->index();
            $table->date('fec_not')->nullable()->index();
            $table->string('num_ide_', 40)->nullable()->index();
            $table->string('paciente_nombre', 180)->nullable();
            $table->string('nom_upgd', 180)->nullable()->index();

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
        Schema::dropIfExists('sivigila_assignment_audits');
    }
};

