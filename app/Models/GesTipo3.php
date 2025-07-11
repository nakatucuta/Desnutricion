<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GesTipo1;
use App\Models\batch_verifications;
class GesTipo3 extends Model
{
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }
   protected $table = 'ges_tipo3';

   // O bien, declara explícitamente todos los campos que vas a llenar:
    protected $fillable = [
        'ges_tipo1_id',
        'tipo_de_registro',
        'consecutivo_de_registro',
        'tipo_identificacion_de_la_usuaria',
        'no_id_del_usuario',
        'fecha_tecnologia_en_salud',
        'codigo_cups_de_la_tecnologia_en_salud',
        'finalidad_de_la_tecnologia_en_salud',
        'clasificacion_riesgo_gestacional',
        'clasificacion_riesgo_preeclampsia',
        'suministro_acido_acetilsalicilico_ASA',
        'suministro_acido_folico_en_el_control_prenatal',
        'suministro_sulfato_ferroso_en_el_control_prenatal',
        'suministro_calcio_en_el_control_prenatal',
        'fecha_suministro_de_anticonceptivo_post_evento_obstetrico',
        'suministro_metodo_anticonceptivo_post_evento_obstetrico',
        'fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea',
        'fecha_de_terminacion_de_la_gestacion',
        'tipo_de_terminacion_de_la_gestacion',
        'tension_arterial_sistolica_PAS_mmHg',
        'tension_arterial_diastolica_PAD_mmHg',
        'indice_de_masa_corporal',
        'resultado_de_la_hemoglobina',
        'indice_de_pulsatilidad_de_arterias_uterinas',
        'batch_verifications_id',
        'user_id',
    ];


     public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function gesTipo1()
    {
        return $this->belongsTo(GesTipo1::class, 'ges_tipo1_id');
    }

     public function batchverification()
    {
        return $this->belongsTo(batch_verifications::class, 'batch_verifications_id');
    }
}