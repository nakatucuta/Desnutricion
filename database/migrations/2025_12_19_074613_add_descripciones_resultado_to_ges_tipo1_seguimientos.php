<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $fields = [
            'vih_tamiz1',
            'vih_tamiz2',
            'vih_tamiz3',
            'sifilis_rapida1',
            'sifilis_rapida2',
            'sifilis_rapida3',
            'sifilis_no_trep',
            'urocultivo',
            'glicemia',
            'pto_glucosa',
            'hemoglobina',
            'hemoclasificacion',
            'ag_hbs',
            'toxoplasma',
            'rubeola',
            'citologia',
            'frotis_vaginal',
            'estreptococo',
            'malaria',
            'chagas',
        ];

        foreach ($fields as $f) {
            DB::statement("
                ALTER TABLE ges_tipo1_seguimientos
                ADD {$f}_resultado_desc NVARCHAR(500) NULL
            ");
        }
    }

    public function down(): void
    {
        $fields = [
            'vih_tamiz1','vih_tamiz2','vih_tamiz3',
            'sifilis_rapida1','sifilis_rapida2','sifilis_rapida3',
            'sifilis_no_trep','urocultivo','glicemia','pto_glucosa',
            'hemoglobina','hemoclasificacion','ag_hbs','toxoplasma',
            'rubeola','citologia','frotis_vaginal','estreptococo',
            'malaria','chagas',
        ];

        foreach ($fields as $f) {
            DB::statement("
                ALTER TABLE ges_tipo1_seguimientos
                DROP COLUMN {$f}_resultado_desc
            ");
        }
    }
};
