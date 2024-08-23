<?php

namespace App\Models;
use  Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
class Seguimiento extends Model
{
  

    use HasFactory;
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
      }

      protected $fillable = [
        'fecha_proximo_control',
    ];


    public function scopeAlertasProximoControl($query){
        return $query->where('fecha_proximo_control', Carbon::now()->subDays(2))->count();
    }

       
}

class Seguimiento1 implements FromCollection
{

    public function collection()
    {
    return Seguimiento::all();
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
