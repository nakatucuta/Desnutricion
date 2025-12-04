<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiConsumptionState extends Model
{
    protected $table = 'api_consumption_states';

    protected $fillable = [
        'user_id',
        'endpoint',
        'last_anio',
        'last_mes',
        'last_carnet',
    ];
}
