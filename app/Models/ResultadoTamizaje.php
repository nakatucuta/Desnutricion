<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultadoTamizaje extends Model
{
    use HasFactory;

    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }

    
    protected $table = 'resultado_tamizajes';
    protected $fillable = ['code', 'description', 'tipo_tamizaje_id'];
    
    // Forzar formato de fecha compatible con SQL Server
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function tipoTamizaje()
    {
        return $this->belongsTo(TipoTamizaje::class, 'tipo_tamizaje_id');
    }

    public function tamizajes()
    {
        return $this->hasMany(Tamizaje::class, 'resultado_tamizaje_id');
    }
}
