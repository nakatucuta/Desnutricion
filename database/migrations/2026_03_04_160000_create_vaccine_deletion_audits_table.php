<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccine_deletion_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vacuna_id');
            $table->string('vacuna_nombre', 180)->nullable();
            $table->unsignedBigInteger('afiliado_id')->nullable();
            $table->string('afiliado_documento', 80)->nullable();
            $table->string('afiliado_nombre', 220)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('vacuna_id');
            $table->index('afiliado_id');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_deletion_audits');
    }
};

