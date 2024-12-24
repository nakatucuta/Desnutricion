<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class referencia_vacuna extends Model
{
    use HasFactory;

    // Define la tabla asociada al modelo
    protected $table = 'referencia_vacunas'; // Tabla asociada
    protected $primaryKey = 'id'; // Clave primaria
    protected $fillable = ['nombre', 'created_at', 'updated_at'];
}
