<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ges_tipo3', function (Blueprint $table) {
            $table->smallInteger('suministro_acido_acetilsalicilico_ASA')->nullable()->change();
            $table->smallInteger('suministro_acido_folico_en_el_control_prenatal')->nullable()->change();
            $table->smallInteger('suministro_sulfato_ferroso_en_el_control_prenatal')->nullable()->change();
            $table->smallInteger('suministro_calcio_en_el_control_prenatal')->nullable()->change();
            $table->smallInteger('suministro_metodo_anticonceptivo_post_evento_obstetrico')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach ([
            'suministro_acido_acetilsalicilico_ASA',
            'suministro_acido_folico_en_el_control_prenatal',
            'suministro_sulfato_ferroso_en_el_control_prenatal',
            'suministro_calcio_en_el_control_prenatal',
            'suministro_metodo_anticonceptivo_post_evento_obstetrico',
        ] as $column) {
            DB::table('ges_tipo3')
                ->where($column, '>', 1)
                ->update([$column => 1]);
        }

        Schema::table('ges_tipo3', function (Blueprint $table) {
            $table->boolean('suministro_acido_acetilsalicilico_ASA')->nullable()->change();
            $table->boolean('suministro_acido_folico_en_el_control_prenatal')->nullable()->change();
            $table->boolean('suministro_sulfato_ferroso_en_el_control_prenatal')->nullable()->change();
            $table->boolean('suministro_calcio_en_el_control_prenatal')->nullable()->change();
            $table->boolean('suministro_metodo_anticonceptivo_post_evento_obstetrico')->nullable()->change();
        });
    }
};
