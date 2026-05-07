<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModulePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module_permission_id',
        'granted_by_user_id',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function modulePermission(): BelongsTo
    {
        return $this->belongsTo(ModulePermission::class, 'module_permission_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}
