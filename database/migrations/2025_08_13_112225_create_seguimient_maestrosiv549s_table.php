<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seguimient_maestrosiv549', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')
                  ->constrained('asignaciones_maestrosiv549')
                  ->onDelete('cascade');

            // Hospitalización y egreso
            $table->date('fecha_hospitalizacion')->nullable();
            $table->text('gestion_hospitalizacion')->nullable();
            $table->date('fecha_egreso')->nullable();
            $table->text('descripcion_seguimiento_inmediato')->nullable();

            // Seguimiento post-egreso (1)
            $table->date('fecha_seguimiento_1')->nullable();
            $table->tinyInteger('tipo_seguimiento_1')->nullable();
            $table->boolean('paciente_sigue_embarazo_1')->nullable();
            $table->date('fecha_control_1')->nullable();
            $table->string('metodo_anticonceptivo')->nullable();
            $table->date('fecha_consulta_rn_1')->nullable();
            $table->text('entrega_medicamentos_labs_1')->nullable();
            $table->text('gestion_posegreso_1')->nullable();

            // Seguimiento 7 días
            $table->date('fecha_seguimiento_2')->nullable();
            $table->boolean('paciente_sigue_embarazo_2')->nullable();
            $table->date('fecha_control_2')->nullable();
            $table->date('fecha_consulta_rn_2')->nullable();
            $table->text('entrega_medicamentos_labs_2')->nullable();
            $table->text('gestion_primera_semana')->nullable();

            // Seguimiento 14 días
            $table->date('fecha_seguimiento_3')->nullable();
            $table->tinyInteger('tipo_seguimiento_3')->nullable();
            $table->boolean('paciente_sigue_embarazo_3')->nullable();
            $table->date('fecha_control_3')->nullable();
            $table->date('fecha_consulta_rn_3')->nullable();
            $table->text('entrega_medicamentos_labs_3')->nullable();
            $table->text('gestion_segunda_semana')->nullable();

            // Seguimiento 21 días
            $table->date('fecha_seguimiento_4')->nullable();
            $table->tinyInteger('tipo_seguimiento_4')->nullable();
            $table->boolean('paciente_sigue_embarazo_4')->nullable();
            $table->date('fecha_control_4')->nullable();
            $table->date('fecha_consulta_rn_4')->nullable();
            $table->text('entrega_medicamentos_labs_4')->nullable();
            $table->text('gestion_tercera_semana')->nullable();

            // Seguimiento 28 días + extras
            $table->date('fecha_seguimiento_5')->nullable();
            $table->tinyInteger('tipo_seguimiento_5')->nullable();
            $table->boolean('paciente_sigue_embarazo_5')->nullable();
            $table->date('fecha_control_5')->nullable();
            $table->date('fecha_consulta_rn_5')->nullable();
            $table->text('entrega_medicamentos_labs_5')->nullable();
            $table->date('fecha_consulta_lactancia')->nullable();
            $table->date('fecha_control_metodo')->nullable();
            $table->text('gestion_despues_mes')->nullable();

            // Largo plazo
            $table->date('fecha_consulta_6_meses')->nullable();
            $table->date('fecha_consulta_1_ano')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seguimient_maestrosiv549');
    }
};