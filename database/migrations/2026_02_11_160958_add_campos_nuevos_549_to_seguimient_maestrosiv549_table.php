<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('seguimient_maestrosiv549', function (Blueprint $table) {

            // ===== Hospitalización: datos de egreso + criterios =====
            $table->string('institucion_egreso_paciente')->nullable()->after('fecha_egreso');

            $table->boolean('eclampsia')->default(false)->after('institucion_egreso_paciente');
            $table->boolean('preeclampsia_severa')->default(false)->after('eclampsia');
            $table->boolean('sepsis_infeccion_sistemica_severa')->default(false)->after('preeclampsia_severa');
            $table->boolean('hemorragia_obstetrica_severa')->default(false)->after('sepsis_infeccion_sistemica_severa');
            $table->boolean('ruptura_uterina')->default(false)->after('hemorragia_obstetrica_severa');

            $table->boolean('falla_cardiovascular')->default(false)->after('ruptura_uterina');
            $table->boolean('falla_renal')->default(false)->after('falla_cardiovascular');
            $table->boolean('falla_hepatica')->default(false)->after('falla_renal');
            $table->boolean('falla_cerebral')->default(false)->after('falla_hepatica');
            $table->boolean('falla_respiratoria')->default(false)->after('falla_cerebral');
            $table->boolean('falla_coagulacion')->default(false)->after('falla_respiratoria');

            $table->boolean('cirugia_adicional')->default(false)->after('falla_coagulacion');
            $table->unsignedTinyInteger('ttl_criter')->nullable()->after('cirugia_adicional');

            $table->string('diagnostico_cie10', 20)->nullable()->after('ttl_criter');
            $table->string('causa_agrupada')->nullable()->after('diagnostico_cie10');

            // ===== Seguimiento inmediato (48–72h) =====
            $table->date('fecha_control_rn_inmediato')->nullable()->after('descripcion_seguimiento_inmediato');
            $table->boolean('seguimiento_efectivo_inmediato')->default(false)->after('fecha_control_rn_inmediato');

            // ===== Seguimientos 2..5 (efectivo) =====
            $table->boolean('seguimiento_efectivo_2')->default(false)->after('gestion_primera_semana');
            $table->boolean('seguimiento_efectivo_3')->default(false)->after('gestion_segunda_semana');
            $table->boolean('seguimiento_efectivo_4')->default(false)->after('gestion_tercera_semana');
            $table->boolean('seguimiento_efectivo_5')->default(false)->after('gestion_despues_mes');
            
        });
    }

    public function down()
    {
        Schema::table('seguimient_maestrosiv549', function (Blueprint $table) {
            $table->dropColumn([
                'institucion_egreso_paciente',
                'eclampsia',
                'preeclampsia_severa',
                'sepsis_infeccion_sistemica_severa',
                'hemorragia_obstetrica_severa',
                'ruptura_uterina',
                'falla_cardiovascular',
                'falla_renal',
                'falla_hepatica',
                'falla_cerebral',
                'falla_respiratoria',
                'falla_coagulacion',
                'cirugia_adicional',
                'ttl_criter',
                'diagnostico_cie10',
                'causa_agrupada',
                'fecha_control_rn_inmediato',
                'seguimiento_efectivo_inmediato',
                'seguimiento_efectivo_2',
                'seguimiento_efectivo_3',
                'seguimiento_efectivo_4',
                'seguimiento_efectivo_5',
            ]);
        });
    }
};
