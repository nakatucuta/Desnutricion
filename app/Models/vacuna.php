<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Batch_verification;


class vacuna extends Model
{
    use HasFactory;

    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }

    protected $table = 'vacunas';


    protected $fillable = [
        
        'nombre',
        'docis',
        'laboratorio',
        'lote',
        'jeringa',
        'lote_jeringa',
        'laboratorio',
        'diluyente',
        'lote_diluyente',
        'afiliado_id',
        'observacion',
        'gotero',
        'tipo_neumococo',
        'num_frascos_utilizados',
        'fecha_vacuna',
        'responsable',
        'fuen_ingresado_paiweb',
        'motivo_noingreso',
        'observaciones',
        'vacunas_id',
        'user_id',
        'batch_verifications_id',

       

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Batch_verifications()
    {
        return $this->belongsTo(Batch_verification::class);
    }
}
