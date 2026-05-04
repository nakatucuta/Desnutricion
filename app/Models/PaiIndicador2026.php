<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiIndicador2026 extends Model
{
    protected $table = 'pai_indicadores_2026';

    protected $fillable = [
        'vigencia',
        'municipio',
        'ips_user_id',
        'ips_nombre_excel',
        'regimen',
        'indicador',
        'biologico',
        'poblacion_programada_anual',
        'fuente',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'vigencia' => 'integer',
        'ips_user_id' => 'integer',
        'poblacion_programada_anual' => 'integer',
        'activo' => 'boolean',
    ];
}

