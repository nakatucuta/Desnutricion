<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vacunas', 'ips_primaria_codigo')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->string('ips_primaria_codigo', 60)->nullable()->after('condicion_usuaria');
                $table->string('ips_primaria_nombre', 200)->nullable()->after('ips_primaria_codigo');
                $table->dateTime('ips_primaria_resolved_at')->nullable()->after('ips_primaria_nombre');
                $table->index('ips_primaria_codigo', 'idx_vacunas_ips_primaria_codigo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vacunas', 'ips_primaria_codigo')) {
            Schema::table('vacunas', function (Blueprint $table) {
                $table->dropIndex('idx_vacunas_ips_primaria_codigo');
                $table->dropColumn([
                    'ips_primaria_codigo',
                    'ips_primaria_nombre',
                    'ips_primaria_resolved_at',
                ]);
            });
        }
    }
};
