<?php

namespace App\Http\Controllers;

use App\Imports\GesTipo1Import;
use App\Mail\GesTipo1ImportedMail;
use App\Models\GesTipo1;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\GesTipo1Export;
use Throwable;

class GesTipo1Controller extends Controller
{
    /**
     * Mostrar formulario de importación
     */
    public function showImportForm()
    {
        return view('ges_tipo1.import');
    }

    /**
     * Procesar el Excel, guardar registros y enviar correo
     */
   public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        // 1) Guardar el Excel temporalmente
        $file       = $request->file('excel_file');
        $storedPath = $file->store('imports');
        $fullPath   = storage_path('app/' . $storedPath);

        try {
            // 2) Instanciar el importador (para obtener el batch ID)
            $importer = new GesTipo1Import;
            Excel::import($importer, $fullPath);

            // 3) Recuperar el batch ID creado por el importador
            $batchId = $importer->getBatchVerificationsId();

            // 4) Traer todos los registros importados en este batch
            $records = GesTipo1::where('batch_verifications_id', $batchId)->get();

            // 5) Definir destinatarios: fijos + el usuario autenticado
            $recipients = [
                'rutamp@epsianaswayuu.com',
                'jsuarez@epsianaswayuu.com',
                auth()->user()->email,
            ];

            // 6) Enviar el correo con la colección de $records
            Mail::to($recipients)
                ->send(new GesTipo1ImportedMail($records));

            // 7) Redirigir con mensaje de éxito
            return redirect()
                ->route('ges_tipo1.index')
                ->with('success', '¡Datos importados correctamente y notificación enviada por correo!');
        } catch (Throwable $e) {
            // 8) En caso de error, volver con mensaje
            return back()
                ->with('error', nl2br($e->getMessage()));
        }
    }

    /**
     * Listado de gestantes (DataTables + filtro por usertype)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = GesTipo1::query()->select([
                'id',
                'primer_nombre',
                'segundo_nombre',
                'primer_apellido',
                'segundo_apellido',
                'no_id_del_usuario',
                'numero_carnet',
                'fecha_de_nacimiento',
                'fecha_probable_de_parto',
                'tipo_de_identificacion_de_la_usuaria',
            ]);

            // Si es gestor (usertype=2), mostrar solo sus registros
            if (Auth::user()->usertype === 2) {
                $query->where('user_id', Auth::id());
            }

            return DataTables::of($query)
                ->addColumn('full_name', function($row) {
                    return trim("{$row->primer_nombre} {$row->segundo_nombre} {$row->primer_apellido} {$row->segundo_apellido}");
                })
                ->addColumn('acciones', function($row) {
                    $show = route('ges_tipo1.show', $row->id);
                    return <<<HTML
<a href="{$show}" class="btn btn-sm btn-gradient">
    <i class="fas fa-eye mr-1"></i> Ver
</a>
HTML;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        return view('ges_tipo1.index');
    }

    /**
     * Mostrar detalle de una gestante y sus Tipo3 asociados
     */
    public function show($id)
    {
        $gestante = GesTipo1::with('tipo3')->findOrFail($id);
        return view('ges_tipo1.show', compact('gestante'));
    }

    /**
     * Exportar a Excel rango por created_at
     */
    public function exportTipo1(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        // Formatear para SQL Server
        $from = Carbon::parse($request->query('from'))->format('Ymd');
        $to   = Carbon::parse($request->query('to'))->format('Ymd');

        return Excel::download(
            new GesTipo1Export($from, $to),
            "gestantes_created_{$from}_to_{$to}.xlsx"
        );
    }
}
