<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiConsumptionState extends Model
{
     public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }
    protected $table = 'api_consumption_states';

    protected $fillable = [
        'user_id',
        'endpoint',
        'last_anio',
        'last_mes',
        'last_carnet',
    ];
}
