<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GesTipo1 extends Model
{
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }
    protected $table = 'ges_tipo1';

    protected $fillable = [
        'user_id',
        'tipo_de_registro',
        'consecutivo',
        'pais_de_la_nacionalidad',
        'municipio_de_residencia_habitual',
        'zona_territorial_de_residencia',
        'codigo_de_habilitacion_ips_primaria_de_la_gestante',
        'tipo_de_identificacion_de_la_usuaria',
        'no_id_del_usuario',
        'numero_carnet',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'segundo_nombre',
        'fecha_de_nacimiento',
        'codigo_pertenencia_etnica',
        'codigo_de_ocupacion',
        'codigo_nivel_educativo_de_la_gestante',
        'fecha_probable_de_parto',
        'direccion_de_residencia_de_la_gestante',
        'antecedente_hipertension_cronica',
        'antecedente_preeclampsia',
        'antecedente_diabetes',
        'antecedente_les_enfermedad_autoinmune',
        'antecedente_sindrome_metabolico',
        'antecedente_erc',
        'antecedente_trombofilia_o_trombosis_venosa_profunda',
        'antecedentes_anemia_celulas_falciformes',
        'antecedente_sepsis_durante_gestaciones_previas',
        'consumo_tabaco_durante_la_gestacion',
        'periodo_intergenesico',
        'embarazo_multiple',
        'metodo_de_concepcion',
    ];

    // Relación inversa: cada registro pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
