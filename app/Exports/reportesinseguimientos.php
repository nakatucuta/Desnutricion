<?php

namespace App\Exports;

use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;


class reportesinseguimientos implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $resultados = DB::table('DESNUTRICION.dbo.sivigilas as a')
        ->leftJoin('DESNUTRICION.dbo.seguimientos as b', 'a.id', '=', 'b.sivigilas_id')
        ->leftJoin('DESNUTRICION.dbo.users as c', 'a.user_id', '=', 'c.id')
        ->select('c.name as PRESTADOR', 'a.*')
        ->whereNull('b.sivigilas_id')
        ->where('a.year', '>', '2023')
        ->get();
    

        return $resultados;
    }
}
