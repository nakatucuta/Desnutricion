<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preconcepcionales', function (Blueprint $table) {
            $table->id();

            // --- Datos de identificación ---
            $table->integer('no')->nullable(); // Columna "No" del Excel (si la usan)
            $table->string('tipo_documento')->nullable();
            $table->string('numero_identificacion')->nullable()->index();

            $table->string('apellido_1')->nullable();
            $table->string('apellido_2')->nullable();
            $table->string('nombre_1')->nullable();
            $table->string('nombre_2')->nullable();

            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad')->nullable();
            $table->string('sexo')->nullable();

            $table->string('regimen_afiliacion')->nullable();
            $table->string('pertenencia_etnica')->nullable();
            $table->string('grupo_poblacional')->nullable();
            $table->string('departamento_residencia')->nullable();
            $table->string('municipio_residencia')->nullable();
            $table->string('zona')->nullable();
            $table->string('etnia')->nullable();
            $table->string('asentamiento')->nullable(); // Asentamiento/Rancheria/Comunidad
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('nivel_educativo')->nullable();
            $table->string('discapacidad')->nullable();
            $table->string('mujer_cabeza_hogar')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('control_tradicional')->nullable();
            $table->string('gestante_renuente')->nullable();
            $table->string('inasistente')->nullable();
            $table->string('nombre_ips_primaria')->nullable();

            // --- Antecedentes personales en salud ---
            $table->string('hipertension_personal')->nullable();
            $table->string('diabetes_mellitus')->nullable();
            $table->string('enfermedad_renal')->nullable();
            $table->string('cardiopatias')->nullable();
            $table->string('epilepsia')->nullable();
            $table->string('enfermedades_autoinmunes')->nullable();
            $table->string('trastornos_mentales')->nullable();
            $table->string('cancer')->nullable();
            $table->string('enfermedades_infecciosas_cronicas')->nullable();
            $table->string('uso_permanente_medicamentos')->nullable();
            $table->string('alergias')->nullable();

            // --- Antecedentes gineco-obstétricos ---
            $table->integer('edad_menarquia')->nullable();
            $table->date('fecha_ultimo_periodo_mestrual')->nullable();

            $table->integer('numero_gestaciones_previas')->nullable();
            $table->integer('partos_vaginales')->nullable();
            $table->integer('cesareas')->nullable();
            $table->integer('abortos')->nullable();
            $table->string('complicaciones_obstetricas_previas')->nullable();

            // --- Antecedentes familiares ---
            $table->string('hipertension_familiar')->nullable();
            $table->string('diabetes_familiar')->nullable();
            $table->string('malformaciones_congenitas')->nullable();
            $table->string('enfermedades_geneticas')->nullable();
            $table->string('enfermedades_mentales_familia')->nullable();
            $table->string('muerte_materna_familia')->nullable();

            // --- Salud sexual y reproductiva ---
            $table->integer('inicio_vida_sexual')->nullable();
            $table->integer('numero_parejas_sexuales')->nullable();
            $table->string('uso_actual_metodos_anticonceptivos')->nullable();
            $table->string('antecedentes_its')->nullable();
            $table->string('deseo_reproductivo')->nullable();

            // --- Estilo de vida y factores de riesgo ---
            $table->string('consumo_tabaco')->nullable();
            $table->string('consumo_alcohol')->nullable();
            $table->string('consumo_sustancias_psicoactivas')->nullable();
            $table->string('actividad_fisica')->nullable();
            $table->string('alimentacion_saludable')->nullable();
            $table->string('violencias')->nullable();

            // --- Evaluación nutricional ---
            $table->decimal('peso', 8, 2)->nullable();
            $table->decimal('talla', 8, 2)->nullable();
            $table->decimal('imc', 8, 2)->nullable();
            $table->string('riesgo_nutricional')->nullable();
            $table->string('suplementacion_acido_folico')->nullable();

            // --- Inmunización ---
            $table->string('tetanos')->nullable();
            $table->string('influenza')->nullable();
            $table->string('covid_19')->nullable();

            // --- Tamizajes ---
            $table->date('fecha_tamizaje_sifilis')->nullable();
            $table->string('resultado_sifilis')->nullable();

            $table->date('fecha_tamizaje_vih')->nullable();
            $table->string('resultado_vih')->nullable();

            $table->date('fecha_tamizaje_hepatitis_b')->nullable();
            $table->string('resultado_hepatitis_b')->nullable();

            $table->string('citologia')->nullable();
            $table->string('tamizaje_salud_mental')->nullable();

            // --- Clasificación del riesgo ---
            $table->string('riesgo_preconcepcional')->nullable();

            // --- Plan de intervención ---
            $table->string('consejeria_preconcepcional')->nullable();
            $table->string('educacion_planificacion_familiar')->nullable();
            $table->string('recomendaciones_nutricionales')->nullable();
            $table->text('ordenes_medicas')->nullable();

            $table->timestamps();

            // Opcional: evitar duplicados por tipo+identificación
            $table->unique(['tipo_documento', 'numero_identificacion'], 'precon_doc_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preconcepcionales');
    }
};
