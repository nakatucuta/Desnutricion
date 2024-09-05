<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorreoEnviado extends Model
{
    use HasFactory;

    protected $table = 'correos_enviados';

    protected $fillable = [
        'user_id',
        'patient_id',
        'sent_at',
    ];
}
