<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Aumentamos a NVARCHAR(300) y dejamos NULL
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN vih_tamiz1_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN vih_tamiz2_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN vih_tamiz3_resultado NVARCHAR(300) NULL");

        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_rapida1_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_rapida2_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_rapida3_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_no_trep_resultado NVARCHAR(300) NULL");

        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN urocultivo_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN glicemia_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN pto_glucosa_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN hemoglobina_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN hemoclasificacion_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN ag_hbs_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN toxoplasma_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN rubeola_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN citologia_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN frotis_vaginal_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN estreptococo_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN malaria_resultado NVARCHAR(300) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN chagas_resultado NVARCHAR(300) NULL");
    }

    public function down(): void
    {
        // Revertir a NVARCHAR(50) si alguna vez lo necesitas
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN vih_tamiz1_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN vih_tamiz2_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN vih_tamiz3_resultado NVARCHAR(50) NULL");

        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_rapida1_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_rapida2_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_rapida3_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN sifilis_no_trep_resultado NVARCHAR(50) NULL");

        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN urocultivo_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN glicemia_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN pto_glucosa_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN hemoglobina_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN hemoclasificacion_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN ag_hbs_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN toxoplasma_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN rubeola_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN citologia_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN frotis_vaginal_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN estreptococo_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN malaria_resultado NVARCHAR(50) NULL");
        DB::statement("ALTER TABLE ges_tipo1_seguimientos ALTER COLUMN chagas_resultado NVARCHAR(50) NULL");
    }
};
