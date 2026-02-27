<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Preconcepcional;

class PreconcepcionalImportBatch extends Model
{
    protected $table = 'preconcepcional_import_batches';

    public function getDateFormat()
    {
        // Formato no ambiguo para SQL Server.
        return 'Ymd H:i:s';
    }

    protected $fillable = [
        'original_name',
        'user_id',
        'file_hash',
        'file_size',
        'rows_total',
        'rows_created',
        'rows_updated',
        'rows_skipped',
        'rows_duplicate',
        'duration_seconds',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Ymd H:i:s',
        'updated_at' => 'datetime:Ymd H:i:s',
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
