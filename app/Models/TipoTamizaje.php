<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTamizaje extends Model
{
    protected $table = 'tipo_tamizajes';
    protected $fillable = ['nombre'];
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function resultados()
    {
        return $this->hasMany(ResultadoTamizaje::class, 'tipo_tamizaje_id');
    }

    public function tamizajes()
    {
        return $this->hasMany(Tamizaje::class, 'tipo_tamizaje_id');
    }
}
