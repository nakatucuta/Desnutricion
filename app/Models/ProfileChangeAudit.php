<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileChangeAudit extends Model
{
    use HasFactory;

    public function getDateFormat()
    {
        // Evita conversiones regionales ambiguas de fecha en SQL Server.
        return 'Ymd H:i:s';
    }

    protected $fillable = [
        'user_id',
        'changed_by_id',
        'changed_fields',
        'old_values',
        'new_values',
        'ip',
        'user_agent',
        'changed_at',
    ];

    protected $casts = [
        'changed_fields' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
