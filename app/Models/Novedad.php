<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    use HasFactory;

    protected $table = 'novedades';

    protected $fillable = [
        'title',
        'message',
        'created_by',
        'is_active',
        'is_mandatory',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
    ];

    public function getDateFormat()
    {
        return 'Ymd H:i:s';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads()
    {
        return $this->hasMany(NovedadRead::class, 'novedad_id');
    }

    public static function unreadCountForUser(int $userId): int
    {
        return static::query()
            ->where('is_active', true)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();
    }
}
