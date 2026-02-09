<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $table = 'import_jobs';
    protected $connection = 'sqlsrv';

    protected $fillable = [
        'user_id',
        'token',
        'status',
        'percent',
        'step',
        'message',
        'errors', // JSON string
        'errors_count',
        'report_path',
        'batch_verifications_id',
    ];

    protected $casts = [
        'percent' => 'integer',
        'errors_count' => 'integer',
        'batch_verifications_id' => 'integer',
    ];
}
