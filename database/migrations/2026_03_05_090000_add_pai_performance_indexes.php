<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $table->index('afiliado_id', 'idx_vacunas_afiliado_id');
            $table->index('batch_verifications_id', 'idx_vacunas_batch_verifications_id');
            $table->index('user_id', 'idx_vacunas_user_id');
            $table->index('created_at', 'idx_vacunas_created_at');
            $table->index(['afiliado_id', 'created_at'], 'idx_vacunas_afiliado_created_at');
        });

        Schema::table('afiliados', function (Blueprint $table) {
            $table->index('numero_identificacion', 'idx_afiliados_numero_identificacion');
            $table->index('numero_carnet', 'idx_afiliados_numero_carnet');
            $table->index('batch_verifications_id', 'idx_afiliados_batch_verifications_id');
            $table->index('created_at', 'idx_afiliados_created_at');
        });

        Schema::table('correos_enviados', function (Blueprint $table) {
            $table->index(['user_id', 'patient_id'], 'idx_correos_user_patient');
            $table->index('sent_at', 'idx_correos_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('correos_enviados', function (Blueprint $table) {
            $table->dropIndex('idx_correos_user_patient');
            $table->dropIndex('idx_correos_sent_at');
        });

        Schema::table('afiliados', function (Blueprint $table) {
            $table->dropIndex('idx_afiliados_numero_identificacion');
            $table->dropIndex('idx_afiliados_numero_carnet');
            $table->dropIndex('idx_afiliados_batch_verifications_id');
            $table->dropIndex('idx_afiliados_created_at');
        });

        Schema::table('vacunas', function (Blueprint $table) {
            $table->dropIndex('idx_vacunas_afiliado_id');
            $table->dropIndex('idx_vacunas_batch_verifications_id');
            $table->dropIndex('idx_vacunas_user_id');
            $table->dropIndex('idx_vacunas_created_at');
            $table->dropIndex('idx_vacunas_afiliado_created_at');
        });
    }
};

