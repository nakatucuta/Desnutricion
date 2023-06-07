<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sivigila extends Model
{
    use HasFactory;
    // protected $fillable = [
    //     'cod_eve',
    //     'semana',
    //     'fec_not',
    //     'year',
    //     'dpto',
    //     'mun',
    //     'tip_ide_',
    //     'num_ide_',
    //     'pri_nom_',
    //     'seg_nom_',
    //     'pri_ape_',
    //     'seg_ape_',
    //     'sexo_',
    //     'fecha_nto_',
    //     'edad_ges',
    //     'telefono_',
    //     'nom_grupo_',
    //     'regimen',
    //     'Ips_at_inicial',
    //     'estado',
    //     'fecha_aten_inicial',
    //     'Caso_confirmada_desnutricion_etiologia_primaria',
    //     'Ips_manejo_hospitalario',
    //     'Esquemq_complrto_pai_edad',
    //     'Atecion_primocion_y_mantenimiento_res3280_2018',
    //     'nombreips_manejo_hospita',
    //     'user_id',
    //     'created_at',
    //     'updated_at',
    // ];


    public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }

        public function user()
        {
        
            return $this->belongsTo('App\User');
        
        }
}

