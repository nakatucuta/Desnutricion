<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GesTipo1;

class GestanteAlerta extends Model
{
    protected $table = 'gestantes_alertas';

    protected $fillable = [
        'user_id',
        'ges_tipo1_id',
        'seguimiento_id',
        'modulo',
        'campo',
        'examen',
        'resultado',
        'severidad',
        'pdf_path',
        'hash',
        'seen_at',
        'resolved_at',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // ✅ FORMATO SEGURO PARA SQL SERVER
    protected $dateFormat = 'Ymd H:i:s';

    public function gestante()
    {
        return $this->belongsTo(GesTipo1::class, 'ges_tipo1_id', 'id');
    }
}
