<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SivigilaAssignmentAudit extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dbo.sivigila_assignment_audits';

    protected $fillable = [
        'sivigila_id',
        'performed_by_user_id',
        'old_assigned_user_id',
        'new_assigned_user_id',
        'action_type',
        'num_ide_',
        'paciente_nombre',
        'fec_not',
        'nom_upgd',
        'old_assigned_name',
        'new_assigned_name',
        'old_assigned_email',
        'new_assigned_email',
        'old_assigned_code',
        'new_assigned_code',
        'ip_address',
        'user_agent',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'fec_not' => 'date',
    ];

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}

