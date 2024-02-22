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
        Schema::create('cargue412s', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero_orden')->nullable();
            $table->string('nombre_coperante')->nullable();
            $table->date('fecha_captacion')->nullable();
            $table->string('municipio')->nullable();
            $table->string('nombre_rancheria')->nullable();
            $table->string('ubicacion_casa')->nullable();
            $table->string('nombre_cuidador')->nullable();
            $table->string('identioficacion_cuidador')->nullable();
            $table->string('telefono_cuidador')->nullable();
            $table->string('nombre_eapb_cuidador')->nullable();
            $table->string('nombre_autoridad_trad_ansestral')->nullable();
            $table->string('datos_contacto_autoridad')->nullable();
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('tipo_identificacion')->nullable();
            $table->string('numero_identificacion')->nullable();
            $table->string('sexo')->nullable();
            $table->date('fecha_nacimieto_nino')->nullable();
            $table->string('edad_meses')->nullable();
            $table->string('regimen_afiliacion')->nullable();
            $table->string('nombre_eapb_menor')->nullable();
            $table->string('peso_kg')->nullable();
            $table->string('logitud_talla_cm')->nullable();
            $table->string('perimetro_braqueal')->nullable();
            $table->string('signos_peligro_infeccion_respiratoria')->nullable();
            $table->string('sexosignos_desnutricion')->nullable();
            $table->string('puntaje_z')->nullable();
            $table->string('calsificacion_antropometrica')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargue412s');
    }
};
