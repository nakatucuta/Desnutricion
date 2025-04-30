<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Sivigila extends Model
{
    use HasFactory;

    /**
     * Los campos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'cod_eve',
        'semana',
        'fec_not',
        'year',
        'dpto',
        'mun',
        'tip_ide_',
        'num_ide_',
        'pri_nom_',
        'seg_nom_',
        'pri_ape_',
        'seg_ape_',
        'edad_',
        'sexo_',
        'fecha_nto_',
        'edad_ges',
        'telefono_',
        'nom_grupo_',
        'regimen',
        'Ips_at_inicial',
        'estado',
        'fecha_aten_inicial',
        'Caso_confirmada_desnutricion_etiologia_primaria',
        'Ips_manejo_hospitalario',
        'nombreips_manejo_hospita',
        'user_id',
    ];

    /**
     * El formato de fecha para el modelo.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-d-m h:i:s';
    }

    /**
     * Convertir atributos a fechas (Carbon).
     *
     * @var array
     */
    protected $dates = [
        'fec_not',
        'fecha_nto_',
        'fecha_aten_inicial',
        'created_at',
        'updated_at',
    ];

    /**
     * Define la relación con el usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
