<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cargue412;
use App\Models\User;

class Seguimiento_412 extends Model
{
    use HasFactory;

    /**
     * Los campos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'estado',
        'fecha_consulta',
        'peso_kilos',
        'talla_cm',
        'puntajez',
        'clasificacion',
        'medicamento',
        'motivo_reapuertura',
        'observaciones',
        'est_act_menor',
        'requerimiento_energia_ftlc',
        'fecha_proximo_control',
        'pdf',
        'cargue412_id',
        'Esquemq_complrto_pai_edad',
        'Atecion_primocion_y_mantenimiento_res3280_2018',
        'perimetro_braqueal',
        'user_id',
    ];

    /**
     * Formato de fecha personalizado.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-d-m h:m:s';
    }

    /**
     * Relación con el modelo Cargue412.
     */
    public function cargue412()
    {
        return $this->belongsTo(Cargue412::class, 'cargue412_id');
    }

    /**
     * Relación con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
