<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }

        public function user()
        {
        
            return $this->belongsTo('App\User');
        
        }

}
