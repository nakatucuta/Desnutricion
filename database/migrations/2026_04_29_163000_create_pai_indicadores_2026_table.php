<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pai_indicadores_2026', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('vigencia')->default(2026);
            $table->string('municipio', 120);
            $table->unsignedInteger('ips_user_id')->nullable();
            $table->string('ips_nombre_excel', 200)->nullable();
            $table->string('regimen', 80);
            $table->string('indicador', 220);
            $table->string('biologico', 180);
            $table->unsignedInteger('poblacion_programada_anual')->default(0);
            $table->string('fuente', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('ips_user_id')->references('id')->on('users');
            $table->index(['vigencia', 'municipio', 'regimen'], 'idx_pai_ind_2026_scope');
            $table->index(['ips_user_id', 'vigencia'], 'idx_pai_ind_2026_ips');
            $table->index(['indicador', 'biologico'], 'idx_pai_ind_2026_indicator');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pai_indicadores_2026');
    }
};

