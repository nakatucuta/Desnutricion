<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NovedadRead extends Model
{
    use HasFactory;

    protected $table = 'novedad_reads';

    protected $fillable = [
        'novedad_id',
        'user_id',
        'read_at',
        'archived_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function getDateFormat()
    {
        return 'Ymd H:i:s';
    }

    public function novedad()
    {
        return $this->belongsTo(Novedad::class, 'novedad_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
