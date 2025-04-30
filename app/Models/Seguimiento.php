<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Seguimiento extends Model
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
        'requerimiento_energia_ftlc',
        'fecha_entrega_ftlc',
        'medicamento',
        'motivo_reapuertura',
        'observaciones',
        'est_act_menor',
        'tratamiento_f75',
        'fecha_recibio_tratf75',
        'fecha_proximo_control',
        'pdf',
        'sivigilas_id',
        'Esquemq_complrto_pai_edad',
        'Atecion_primocion_y_mantenimiento_res3280_2018',
        'user_id',
    ];

    /**
     * Los atributos que deben tratarse como fechas.
     *
     * @var array
     */
    protected $dates = [
        'fecha_consulta',
        'fecha_entrega_ftlc',
        'fecha_recibio_tratf75',
        'fecha_proximo_control',
        'created_at',
        'updated_at',
    ];

    /**
     * Formato personalizado para almacenar las fechas en la base.
     *
     * @return string
     */
    public function getDateFormat(): string
    {
        return 'Y-d-m H:i:s';
    }

    /**
     * Relación con el modelo Sivigila.
     *
     * @return BelongsTo
     */
    public function sivigila(): BelongsTo
    {
        return $this->belongsTo(Sivigila::class, 'sivigilas_id');
    }

    /**
     * Relación con el modelo User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para contar alertas de próximo control.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return int
     */
    public function scopeAlertasProximoControl($query): int
    {
        return $query->where('fecha_proximo_control', '<=', Carbon::now()->addDays(2))
                     ->where('estado', 1)
                     ->count();
    }
}
