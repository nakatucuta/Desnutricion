<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GesTipo3Import;
use App\Exports\GesTipo3Export;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\DataImportedMail;
class GesTipo3Controller extends Controller
{
    public function showImportForm()
    {
        return view('ges_tipo3.import');
    }

  public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        // 1) Guardar temporalmente el archivo
        $file       = $request->file('excel_file');
        $storedPath = $file->store('imports');
        $fullPath   = storage_path('app/' . $storedPath);

        try {
            // 2) Crear la instancia del importador y ejecutar
            $importer = new GesTipo3Import();
            Excel::import($importer, $fullPath);

            // 3) Obtener los registros recién insertados
            $records = $importer->inserted;

            // 4) Destinatarios fijos + correo del usuario actual
            $recipients = [
                'rutamp@epsianaswayuu.com',
                'jsuarez@epsianaswayuu.com',
                Auth::user()->email,
            ];

            // 5) Enviar el correo con la colección
            Mail::to($recipients)
                ->send(new DataImportedMail($records));

            // 6) Redirigir con mensaje de éxito
            return redirect()
                ->route('ges_tipo1.index')
                ->with('success', '¡Datos ges_tipo3 importados correctamente y notificación enviada!');
        } catch (\Exception $e) {
            // 7) En caso de error, volver con el mensaje
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