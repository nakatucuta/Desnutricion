<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiIpsReferencia extends Model
{
    protected $table = 'pai_ips_referenciadas';

    /**
     * Use SQL Server's unambiguous date format regardless of DATEFORMAT/language.
     */
    protected $dateFormat = 'Ymd H:i:s';

    protected $fillable = [
        'vigencia',
        'municipio',
        'ips_vacunadora_user_id',
        'ips_vacunadora_codigo',
        'ips_vacunadora_nombre',
        'ips_primaria_codigo',
        'ips_primaria_nombre',
        'activo',
    ];

    protected $casts = [
        'vigencia' => 'integer',
        'ips_vacunadora_user_id' => 'integer',
        'activo' => 'boolean',
    ];
}
