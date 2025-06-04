<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TamizajePdf extends Model
{
    protected $table = 'tamizaje_pdfs';

    protected $fillable = [
        'tamizaje_id',
        'tipo_identificacion',
        'numero_identificacion',
        'original_name',
        'file_path',
    ];

    public function tamizaje()
    {
        return $this->belongsTo(Tamizaje::class);
    }
}
