<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimient_maestrosiv549', function (Blueprint $table) {
            if (!Schema::hasColumn('seguimient_maestrosiv549', 'soporte_inmediato_pdf')) {
                $table->string('soporte_inmediato_pdf')->nullable();
            }
            if (!Schema::hasColumn('seguimient_maestrosiv549', 'soporte_seguimiento_1_pdf')) {
                $table->string('soporte_seguimiento_1_pdf')->nullable();
            }
            if (!Schema::hasColumn('seguimient_maestrosiv549', 'soporte_seguimiento_2_pdf')) {
                $table->string('soporte_seguimiento_2_pdf')->nullable();
            }
            if (!Schema::hasColumn('seguimient_maestrosiv549', 'soporte_seguimiento_3_pdf')) {
                $table->string('soporte_seguimiento_3_pdf')->nullable();
            }
            if (!Schema::hasColumn('seguimient_maestrosiv549', 'soporte_seguimiento_4_pdf')) {
                $table->string('soporte_seguimiento_4_pdf')->nullable();
            }
            if (!Schema::hasColumn('seguimient_maestrosiv549', 'soporte_seguimiento_5_pdf')) {
                $table->string('soporte_seguimiento_5_pdf')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('seguimient_maestrosiv549', function (Blueprint $table) {
            foreach ([
                'soporte_inmediato_pdf',
                'soporte_seguimiento_1_pdf',
                'soporte_seguimiento_2_pdf',
                'soporte_seguimiento_3_pdf',
                'soporte_seguimiento_4_pdf',
                'soporte_seguimiento_5_pdf',
            ] as $column) {
                if (Schema::hasColumn('seguimient_maestrosiv549', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

