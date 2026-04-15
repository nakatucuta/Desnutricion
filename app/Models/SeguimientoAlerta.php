<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoAlerta extends Model
{
    protected $table = 'seguimiento_alertas';

    // Solo estos campos se escriben; NO pongas created_at/updated_at aquí
    protected $fillable = [
        'seguimiento_id',
        'hito',
        'tipo',
        'nivel_riesgo',
        'prioridad',
        'sent_at',
        'due_at',
        'estado',
    ];

    // Casts de fechas como DateTime (no strings)
    protected $casts = [
        'sent_at'    => 'datetime',
        'due_at'     => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Formato estable para escritura de fechas en SQL Server
    protected $dateFormat = 'Ymd H:i:s';

    public function seguimiento()
    {
        return $this->belongsTo(\App\Models\SeguimientMaestrosiv549::class, 'seguimiento_id');
    }
}
