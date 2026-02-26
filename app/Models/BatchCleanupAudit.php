<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchCleanupAudit extends Model
{
    use HasFactory;

    protected $table = 'batch_cleanup_audits';

    protected $fillable = [
        'user_id',
        'action',
        'batches_count',
        'afiliados_count',
        'vacunas_count',
        'batch_ids',
        'ip_address',
        'user_agent',
    ];
}

