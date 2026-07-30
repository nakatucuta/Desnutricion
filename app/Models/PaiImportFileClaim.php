<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiImportFileClaim extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'pai_import_file_claims';

    protected $dateFormat = 'Ymd H:i:s';

    protected $fillable = [
        'file_sha256',
        'released_file_sha256',
        'content_sha256',
        'released_content_sha256',
        'file_size',
        'content_rows',
        'content_columns',
        'format_version',
        'first_original_name',
        'last_original_name',
        'first_user_id',
        'last_user_id',
        'current_import_job_id',
        'batch_verifications_id',
        'status',
        'released_at',
        'released_by_user_id',
        'release_reason',
        'released_from_batch_verifications_id',
        'submission_count',
        'retry_count',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'content_rows' => 'integer',
        'content_columns' => 'integer',
        'first_user_id' => 'integer',
        'last_user_id' => 'integer',
        'current_import_job_id' => 'integer',
        'batch_verifications_id' => 'integer',
        'released_by_user_id' => 'integer',
        'released_from_batch_verifications_id' => 'integer',
        'submission_count' => 'integer',
        'retry_count' => 'integer',
    ];
}
