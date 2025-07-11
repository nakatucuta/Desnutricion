<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Afiliado;
use App\Models\vacuna;
use App\Models\Tamizaje;
use App\Models\GesTipo1;
use App\Models\GesTipo3;
class batch_verifications extends Model
{
    use HasFactory;

    public function getDateFormat(){
        return 'Y-d-m h:m:s';
       }

    protected $table = 'batch_verifications';


    protected $fillable = [
        
       'fecha_cargue',
       

    ];
    
    /**
     * Get the afiliados for the user.
     */
    public function afiliados()
    {
        return $this->hasMany(Afiliado::class);
    }

    public function vacunas()
    {
        return $this->hasMany(vacuna::class);
    }

    public function tamizajes()
    {
        return $this->hasMany(Tamizaje::class);
    }

      public function Gestipo1()
    {
        return $this->hasMany(GesTipo1::class);
    }

       public function Gestipo3()
    {
        return $this->hasMany(GesTipo3::class);
    }
}

