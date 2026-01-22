<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GesTipo1 extends Model
{
    public $timestamps = false;

    // ✨ NO sobreescribas getDateFormat aquí
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
        'batch_verifications_id',
    ];

    protected $casts = [
    'fecha_de_nacimiento' => 'date:Y-m-d',
    'fecha_probable_de_parto' => 'date:Y-m-d',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Si tu Tipo3 realmente cuelga por ges_tipo1_id, deja esto tal cual.
    // (Si en tu BD se relaciona por no_id_del_usuario, cámbialo a ->hasMany(GesTipo3::class, 'no_id_del_usuario', 'no_id_del_usuario'))
    public function tipo3()
    {
        return $this->hasMany(GesTipo3::class, 'ges_tipo1_id');
    }

    public function batchverification()
    {
        return $this->belongsTo(\App\Models\batch_verifications::class, 'batch_verifications_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(GesTipo1Seguimiento::class, 'ges_tipo1_id')->orderByDesc('id');
    }

    public function ultimoSeguimiento()
    {
        return $this->hasOne(GesTipo1Seguimiento::class, 'ges_tipo1_id')->latestOfMany('id');
        // Alternativa si tu Laravel no soporta latestOfMany(): ->latest('id')
    }
}
