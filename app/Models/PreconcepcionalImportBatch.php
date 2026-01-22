<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Preconcepcional;

class PreconcepcionalImportBatch extends Model
{
    protected $table = 'preconcepcional_import_batches';

    // ✅ SQL Server datetime SIN milisegundos
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'original_filename',
        'user_id',
        'file_hash',
        'file_size_bytes',
        'duration_ms',
        'total_rows',
        'created_rows',
        'updated_rows',
        'skipped_rows',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdRecords()
    {
        return $this->hasMany(Preconcepcional::class, 'created_batch_id');
    }
}
