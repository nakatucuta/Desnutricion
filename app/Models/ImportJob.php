<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $table = 'import_jobs';
    protected $connection = 'sqlsrv';
    protected $dateFormat = 'Ymd H:i:s';

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
        'file_claim_id',
        'file_sha256',
        'content_sha256',
        'file_size',
        'content_rows',
        'content_columns',
        'original_name',
        'format_version',
        'retry_count',
        'retryable',
    ];

    protected $casts = [
        'percent' => 'integer',
        'errors_count' => 'integer',
        'batch_verifications_id' => 'integer',
        'file_claim_id' => 'integer',
        'file_size' => 'integer',
        'content_rows' => 'integer',
        'content_columns' => 'integer',
        'retry_count' => 'integer',
        'retryable' => 'boolean',
    ];
}
