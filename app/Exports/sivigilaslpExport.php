<?php

namespace App\Exports;

use App\Models\Sivigila;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class sivigilaslpExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::connection('sqlsrv_1')
            ->table('maestroSiv113')
            ->select('*')
            ->where('cod_eve', 113)
            ->whereBetween(DB::raw("YEAR(fec_not)"), [2024, 2024])
            ->get();
    }
}