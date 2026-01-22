<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 👉 Convertimos TODAS las columnas de fecha a DATE NULL
        // (si alguna estaba DATETIME, esto arregla el "out of range")
        $dates = [
            'fecha_contacto',
            'proximo_contacto',
            'fecha_seguimiento',
            'fecha_nacimiento',
            'fecha_ingreso_cpn',
            'fum',
            'fpp',

            'vih_tamiz1_fecha',
            'vih_tamiz2_fecha',
            'vih_tamiz3_fecha',
            'vih_confirmatoria_fecha',

            'sifilis_rapida1_fecha',
            'sifilis_rapida2_fecha',
            'sifilis_rapida3_fecha',
            'sifilis_no_trep_fecha',

            'urocultivo_fecha',
            'glicemia_fecha',
            'pto_glucosa_fecha',
            'hemoglobina_fecha',
            'ag_hbs_fecha',
            'toxoplasma_fecha',
            'rubeola_fecha',
            'citologia_fecha',
            'frotis_vaginal_fecha',
            'estreptococo_fecha',
            'malaria_fecha',
            'chagas_fecha',

            'vac_influenza_fecha',
            'vac_toxoide_fecha',
            'vac_dpt_acelular_fecha',
            'consulta_odontologica_fecha',

            'desparasitacion_fecha',

            'cpn1_fecha',
            'cpn2_fecha',
            'cpn3_fecha',
            'cpn4_fecha',
            'cpn5_fecha',
            'cpn6_fecha',
            'cpn7_fecha',
            'cpn8_fecha',
            'cpn9_fecha',

            'defuncion_fecha',
        ];

        foreach ($dates as $col) {
            DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN [$col] DATE NULL");
        }

        // 👉 timestamps mejor como DATETIME2 (evita problemas también)
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN [created_at] DATETIME2(3) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN [updated_at] DATETIME2(3) NULL");
    }

    public function down(): void
    {
        // Si quieres revertir, lo dejo como DATETIME (no recomendado)
        $dates = [
            'fecha_contacto','proximo_contacto','fecha_seguimiento','fecha_nacimiento','fecha_ingreso_cpn','fum','fpp',
            'vih_tamiz1_fecha','vih_tamiz2_fecha','vih_tamiz3_fecha','vih_confirmatoria_fecha',
            'sifilis_rapida1_fecha','sifilis_rapida2_fecha','sifilis_rapida3_fecha','sifilis_no_trep_fecha',
            'urocultivo_fecha','glicemia_fecha','pto_glucosa_fecha','hemoglobina_fecha','ag_hbs_fecha',
            'toxoplasma_fecha','rubeola_fecha','citologia_fecha','frotis_vaginal_fecha','estreptococo_fecha',
            'malaria_fecha','chagas_fecha',
            'vac_influenza_fecha','vac_toxoide_fecha','vac_dpt_acelular_fecha','consulta_odontologica_fecha',
            'desparasitacion_fecha',
            'cpn1_fecha','cpn2_fecha','cpn3_fecha','cpn4_fecha','cpn5_fecha','cpn6_fecha','cpn7_fecha','cpn8_fecha','cpn9_fecha',
            'defuncion_fecha',
        ];

        foreach ($dates as $col) {
            DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN [$col] DATETIME NULL");
        }

        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN [created_at] DATETIME NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN [updated_at] DATETIME NULL");
    }
};
