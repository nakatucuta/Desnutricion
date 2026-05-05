<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pai_indicadores_catalogo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_key', 80)->unique();
            $table->string('indicador', 220);
            $table->string('biologico', 180);
            $table->string('vaccine_key', 80)->nullable();
            $table->string('population_rule', 80)->nullable();
            $table->string('dose_rule', 80)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('pai_programacion_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('vigencia');
            $table->string('municipio', 120);
            $table->unsignedInteger('ips_user_id')->nullable();
            $table->string('ips_nombre_fuente', 200)->nullable();
            $table->string('regimen', 80);
            $table->unsignedInteger('indicador_catalogo_id');
            $table->unsignedInteger('poblacion_programada_anual')->default(0);
            $table->string('fuente_tipo', 60)->nullable();
            $table->string('fuente_archivo', 255)->nullable();
            $table->string('fuente_hoja', 100)->nullable();
            $table->unsignedInteger('fuente_fila')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('ips_user_id')->references('id')->on('users');
            $table->foreign('indicador_catalogo_id', 'fk_pai_prog_meta_catalogo')
                ->references('id')->on('pai_indicadores_catalogo');
            $table->index(['vigencia', 'municipio', 'regimen'], 'idx_pai_prog_scope');
            $table->index(['vigencia', 'ips_user_id'], 'idx_pai_prog_ips');
            $table->index(['indicador_catalogo_id', 'vigencia'], 'idx_pai_prog_catalogo');
        });

        Schema::create('pai_programacion_import_lotes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('vigencia');
            $table->string('root_path', 400)->nullable();
            $table->unsignedInteger('archivos_procesados')->default(0);
            $table->unsignedInteger('registros_cargados')->default(0);
            $table->string('estado', 30)->default('ok');
            $table->text('detalle')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
        });

        $now = now();
        DB::table('pai_indicadores_catalogo')->insert([
            [
                'codigo_key' => 'bcg',
                'indicador' => 'COBERTURA NIÑO Y NIÑAS MENOR DE UN AÑO',
                'biologico' => 'BCG',
                'vaccine_key' => 'BCG',
                'population_rule' => 'lt_12m',
                'dose_rule' => 'any',
                'orden' => 10,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'penta_3',
                'indicador' => 'COBERTURA NIÑO Y NIÑAS MENOR DE UN AÑO',
                'biologico' => '3ra DE PENTAVALENTE',
                'vaccine_key' => 'PENTAVALENTE',
                'population_rule' => 'lt_12m',
                'dose_rule' => 'third',
                'orden' => 20,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'triple_viral_1',
                'indicador' => 'COBERTURA NIÑO Y NIÑAS DE 1 AÑO',
                'biologico' => 'TRIPLE VIRAL',
                'vaccine_key' => 'TRIPLE_VIRAL',
                'population_rule' => '12_to_23m',
                'dose_rule' => 'first_or_unique',
                'orden' => 30,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'triple_viral_ref',
                'indicador' => 'COBERTURAS EN NIÑOS Y NIÑAS DE 18 MESES',
                'biologico' => 'REFUERZO TRIPLE VIRAL',
                'vaccine_key' => 'TRIPLE_VIRAL',
                'population_rule' => '18_to_23m',
                'dose_rule' => 'refuerzo',
                'orden' => 40,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'penta_ref',
                'indicador' => 'COBERTURAS EN NIÑOS Y NIÑAS DE 18 MESES',
                'biologico' => 'REFUERZO PENTAVALANTE',
                'vaccine_key' => 'PENTAVALENTE',
                'population_rule' => '18_to_23m',
                'dose_rule' => 'refuerzo',
                'orden' => 50,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'dpt_ref2',
                'indicador' => 'COBERTURA NIÑOS DE 5 AÑOS',
                'biologico' => '2do REFUERZO DPT',
                'vaccine_key' => 'DPT',
                'population_rule' => '60_to_71m',
                'dose_rule' => 'second_refuerzo',
                'orden' => 60,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'tdap_gestante',
                'indicador' => 'GESTANTE',
                'biologico' => 'TDAP',
                'vaccine_key' => 'TDAP',
                'population_rule' => 'gestante',
                'dose_rule' => 'any',
                'orden' => 70,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'vph_f',
                'indicador' => 'COBERTURA EN NIÑAS DE 9 A 17 AÑOS',
                'biologico' => 'DOSIS UNICA',
                'vaccine_key' => 'VPH',
                'population_rule' => '9_to_17_f',
                'dose_rule' => 'first_or_unique',
                'orden' => 80,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_key' => 'vph_m',
                'indicador' => 'COBERTURA EN NIÑOS DE 9 A 17 AÑOS',
                'biologico' => 'DOSIS UNICA',
                'vaccine_key' => 'VPH',
                'population_rule' => '9_to_17_m',
                'dose_rule' => 'first_or_unique',
                'orden' => 90,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pai_programacion_import_lotes');
        Schema::dropIfExists('pai_programacion_metas');
        Schema::dropIfExists('pai_indicadores_catalogo');
    }
};

