<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiProgramacionMeta extends Model
{
    protected $table = 'pai_programacion_metas';

    protected $fillable = [
        'vigencia',
        'municipio',
        'ips_user_id',
        'ips_nombre_fuente',
        'regimen',
        'indicador_catalogo_id',
        'poblacion_programada_anual',
        'fuente_tipo',
        'fuente_archivo',
        'fuente_hoja',
        'fuente_fila',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'vigencia' => 'integer',
        'ips_user_id' => 'integer',
        'indicador_catalogo_id' => 'integer',
        'poblacion_programada_anual' => 'integer',
        'fuente_fila' => 'integer',
        'activo' => 'boolean',
    ];
}

