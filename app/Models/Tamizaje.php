<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Tamizaje extends Model
{
    use HasFactory;

    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }
    protected $table = 'tamizajes';
    protected $fillable = [
        'tipo_identificacion',
        'numero_identificacion',
        'fecha_tamizaje',
        'numero_carnet',
        'tipo_tamizaje_id',
        'resultado_tamizaje_id',
        'user_id',
        'valor_laboratorio',  // <- Nuevo campo
        'descript_resultado',  // <- nuevo campo

    ];
    // protected $dateFormat = 'Y-m-d H:i:s';

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    //     'fecha_tamizaje' => 'datetime:Y-m-d',
    // ];



     // Relación “un tamizaje tiene muchos PDFs”
     public function pdfs()
     {
         return $this->hasMany(TamizajePdf::class);
     }
 
     // (Opcional) Relación filtrando por persona
     public function personPdfs()
     {
         return $this->hasMany(TamizajePdf::class, 'numero_identificacion', 'numero_identificacion')
                     ->where('tipo_identificacion', $this->tipo_identificacion);
     }

    public function tipo()
    {
        return $this->belongsTo(TipoTamizaje::class, 'tipo_tamizaje_id');
    }

    public function resultado()
    {
        return $this->belongsTo(ResultadoTamizaje::class, 'resultado_tamizaje_id');
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

    
}
