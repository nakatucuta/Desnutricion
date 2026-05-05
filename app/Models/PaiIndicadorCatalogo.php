<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiIndicadorCatalogo extends Model
{
    protected $table = 'pai_indicadores_catalogo';

    protected $fillable = [
        'codigo_key',
        'indicador',
        'biologico',
        'vaccine_key',
        'population_rule',
        'dose_rule',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];
}

