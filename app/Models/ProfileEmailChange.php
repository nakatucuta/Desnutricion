<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileEmailChange extends Model
{
    use HasFactory;

    public function getDateFormat()
    {
        return 'Ymd H:i:s';
    }

    protected $fillable = [
        'user_id',
        'new_email',
        'token_hash',
        'requested_at',
        'expires_at',
        'confirmed_at',
        'requested_ip',
        'requested_user_agent',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActiveForUser($query, int $userId)
    {
        return $query
            ->where('user_id', $userId)
            ->whereNull('confirmed_at');
    }
}
