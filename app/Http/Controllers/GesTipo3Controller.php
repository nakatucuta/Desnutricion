<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GesTipo3Import;
use App\Exports\GesTipo3Export;
use Carbon\Carbon;
class GesTipo3Controller extends Controller
{
    public function showImportForm()
    {
        return view('ges_tipo3.import');
    }

   public function import(Request $request)
{
    // 1) Validar el archivo Excel
    $request->validate([
        'excel_file' => 'required|file|mimes:xlsx,xls'
    ]);

    try {
        // 2) Ejecutar el import
        Excel::import(new GesTipo3Import, $request->file('excel_file'));

        // 3) Éxito → redirigir al listado de gestantes con mensaje
        return redirect()
            ->route('ges_tipo1.index')
            ->with('success', '¡Datos ges_tipo3 importados correctamente!');
    } catch (\Exception $e) {
        // 4) Error → vuelve a la misma vista de import con el mensaje de error
        return back()
            ->with('error', nl2br($e->getMessage()));
    }
}



 public function exportTipo3(Request $request)
{
    $request->validate([
        'from' => 'required|date',
        'to'   => 'required|date|after_or_equal:from',
    ]);

 
        $from = Carbon::parse($request->query('from'))->format('Ymd');
        $to   = Carbon::parse($request->query('to'))->format('Ymd');

    return Excel::download(
        new GesTipo3Export($from, $to),
        "tipo3_created_{$from}_to_{$to}.xlsx"
    );
}

}