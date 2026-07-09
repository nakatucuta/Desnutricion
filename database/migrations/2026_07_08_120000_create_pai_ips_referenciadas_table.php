<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pai_ips_referenciadas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('vigencia');
            $table->string('municipio', 120)->nullable();
            $table->unsignedInteger('ips_vacunadora_user_id')->nullable();
            $table->string('ips_vacunadora_codigo', 60);
            $table->string('ips_vacunadora_nombre', 200)->nullable();
            $table->string('ips_primaria_codigo', 60);
            $table->string('ips_primaria_nombre', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('ips_vacunadora_user_id', 'fk_pai_ips_ref_user')
                ->references('id')->on('users');
            $table->unique(
                ['vigencia', 'municipio', 'ips_vacunadora_codigo', 'ips_primaria_codigo'],
                'uq_pai_ips_ref_scope'
            );
            $table->index(['vigencia', 'municipio', 'ips_vacunadora_codigo'], 'idx_pai_ips_ref_vacunadora');
            $table->index(['vigencia', 'ips_primaria_codigo'], 'idx_pai_ips_ref_primaria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pai_ips_referenciadas');
    }
};
