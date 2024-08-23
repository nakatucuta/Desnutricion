<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('afiliados', function (Blueprint $table) {
            $table->increments('id');
            // $table->integer('idAfiliado')->primary();
            $table->date('fecha_atencion')->nullable();
            $table->string('tipo_identificacion')->nullable();
            $table->string('numero_identificacion')->nullable();
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad_anos')->nullable();
            $table->integer('edad_meses')->nullable();
            $table->integer('edad_dias')->nullable();
            $table->integer('total_meses')->nullable();
            $table->string('esquema_completo')->nullable();
            $table->string('sexo')->nullable();
            $table->string('genero')->nullable();
            $table->string('orientacion_sexual')->nullable();
            $table->integer('edad_gestacional')->nullable();
            $table->string('pais_nacimiento')->nullable();
            $table->string('estatus_migratorio')->nullable();
            $table->string('lugar_atencion_parto')->nullable();
            $table->string('regimen')->nullable();
            $table->string('aseguradora')->nullable();
            $table->string('pertenencia_etnica')->nullable();
            $table->string('desplazado')->nullable();
            $table->string('discapacitado')->nullable();
            $table->string('fallecido')->nullable();
            $table->string('victima_conflicto')->nullable();
            $table->string('estudia')->nullable();
            $table->string('pais_residencia')->nullable();
            $table->string('departamento_residencia')->nullable();
            $table->string('municipio_residencia')->nullable();
            $table->string('comuna')->nullable();
            $table->string('area')->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono_fijo')->nullable();
            $table->string('celular')->nullable();
            $table->string('email')->nullable();
            $table->string('autoriza_llamadas')->nullable();
            $table->string('autoriza_correos')->nullable();
            $table->string('contraindicacion_vacuna')->nullable();
            $table->string('enfermedad_contraindicacion')->nullable();
            $table->string('reaccion_biologicos')->nullable();
            $table->string('sintomas_reaccion')->nullable();
            $table->string('condicion_usuaria')->nullable();
            $table->string('fecha_ultima_menstruacion')->nullable();
            $table->string('semanas_gestacion')->nullable();
            $table->date('fecha_prob_parto')->nullable();
            $table->integer('embarazos_previos')->nullable();
            $table->date('fecha_antecedente')->nullable();
            $table->string('tipo_antecedente')->nullable();
            $table->text('descripcion_antecedente')->nullable();
            $table->text('observaciones_especiales')->nullable();
            $table->string('madre_tipo_identificacion')->nullable();
            $table->string('madre_identificacion')->nullable();
            $table->string('madre_primer_nombre')->nullable();
            $table->string('madre_segundo_nombre')->nullable();
            $table->string('madre_primer_apellido')->nullable();
            $table->string('madre_segundo_apellido')->nullable();
            $table->string('madre_correo')->nullable();
            $table->string('madre_telefono')->nullable();
            $table->string('madre_celular')->nullable();
            $table->string('madre_regimen')->nullable();
            $table->string('madre_pertenencia_etnica')->nullable();
            $table->string('madre_desplazada')->nullable();
            $table->string('cuidador_tipo_identificacion')->nullable();
            $table->string('cuidador_identificacion')->nullable();
            $table->string('cuidador_primer_nombre')->nullable();
            $table->string('cuidador_segundo_nombre')->nullable();
            $table->string('cuidador_primer_apellido')->nullable();
            $table->string('cuidador_segundo_apellido')->nullable();
            $table->string('cuidador_parentesco')->nullable();
            $table->string('cuidador_correo')->nullable();
            $table->string('cuidador_telefono')->nullable();
            $table->string('cuidador_celular')->nullable();
            $table->string('esquema_vacunacion')->nullable();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('batch_verifications_id')->nullable();
            $table->foreign('batch_verifications_id')->references('id')->on('batch_verifications');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliados');
    }
};
