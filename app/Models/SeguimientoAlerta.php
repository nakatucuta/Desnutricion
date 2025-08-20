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
        'sent_at',
    ];

    // Casts de fechas como DateTime (no strings)
    protected $casts = [
        'sent_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Fuerza a Eloquent a usar el formato ISO correcto para SQL Server
    protected $dateFormat = 'Y-d-m h:m:s'; // muy importante

    public function seguimiento()
    {
        return $this->belongsTo(\App\Models\SeguimientMaestrosiv549::class, 'seguimiento_id');
    }
}
