<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AsignacionesMaestrosiv549Colaborador extends Pivot
{
    protected $table = 'asignaciones_maestrosiv549_colaboradores';

    public function getDateFormat()
    {
        return 'Ymd H:i:s';
    }
}

