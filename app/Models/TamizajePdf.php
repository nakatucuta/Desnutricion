<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TamizajePdf extends Model
{
    protected $table = 'tamizaje_pdfs';
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }
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
