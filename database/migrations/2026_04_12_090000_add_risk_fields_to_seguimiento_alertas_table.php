<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('seguimiento_alertas', function (Blueprint $table) {
            if (!Schema::hasColumn('seguimiento_alertas', 'tipo')) {
                $table->string('tipo', 30)->nullable()->after('hito');
            }
            if (!Schema::hasColumn('seguimiento_alertas', 'nivel_riesgo')) {
                $table->string('nivel_riesgo', 20)->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('seguimiento_alertas', 'prioridad')) {
                $table->unsignedSmallInteger('prioridad')->nullable()->after('nivel_riesgo');
            }
            if (!Schema::hasColumn('seguimiento_alertas', 'due_at')) {
                $table->dateTime('due_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('seguimiento_alertas', 'estado')) {
                $table->string('estado', 20)->nullable()->after('due_at');
            }
        });
    }

    public function down()
    {
        Schema::table('seguimiento_alertas', function (Blueprint $table) {
            if (Schema::hasColumn('seguimiento_alertas', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('seguimiento_alertas', 'due_at')) {
                $table->dropColumn('due_at');
            }
            if (Schema::hasColumn('seguimiento_alertas', 'prioridad')) {
                $table->dropColumn('prioridad');
            }
            if (Schema::hasColumn('seguimiento_alertas', 'nivel_riesgo')) {
                $table->dropColumn('nivel_riesgo');
            }
            if (Schema::hasColumn('seguimiento_alertas', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }
};

