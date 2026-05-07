<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_assignable',
    ];

    protected $casts = [
        'is_assignable' => 'boolean',
    ];

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserModulePermission::class, 'module_permission_id');
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(ModuleAccessRequest::class, 'module_permission_id');
    }
}
