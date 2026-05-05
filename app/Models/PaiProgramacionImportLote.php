<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiProgramacionImportLote extends Model
{
    protected $table = 'pai_programacion_import_lotes';

    protected $fillable = [
        'vigencia',
        'root_path',
        'archivos_procesados',
        'registros_cargados',
        'estado',
        'detalle',
        'created_by',
    ];

    protected $casts = [
        'vigencia' => 'integer',
        'archivos_procesados' => 'integer',
        'registros_cargados' => 'integer',
        'created_by' => 'integer',
    ];
}

