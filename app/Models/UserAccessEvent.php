<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserAccessEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_type',
        'login_identifier',
        'identifier_hash',
        'auth_method',
        'ip_address',
        'user_agent',
        'session_id',
        'route_name',
        'details',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('event_type', 'login_success');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('event_type', 'login_failed');
    }

    public function scopeLogout(Builder $query): Builder
    {
        return $query->where('event_type', 'logout');
    }
}
