<?php

namespace App\Exports;

use App\Models\Cargue412;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class Cargue412Export implements FromCollection
{/**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Adaptación de la consulta para la exportación a Excel
        return Cargue412::from('cargue412s as a')
            ->select('a.*', 'd.descrip as ips_primaria')
            ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroidentificaciones] as b'), function($join) {
                $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
                     ->on('a.numero_identificacion', '=', 'b.identificacion');
            })
            ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as c'), 'b.numeroCarnet', '=', 'c.numeroCarnet')
            ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as d'), 'c.idGrupoIps', '=', 'd.id')
            ->get();
    }
}
