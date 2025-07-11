<?php

namespace App\Http\Controllers;

use App\Imports\GesTipo1Import;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
// Atrapa verdaderamente cualquier error
use App\Models\GesTipo1;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\GesTipo1Export;
use Throwable;
use App\Models\batch_verifications;



class GesTipo1Controller extends Controller
{

    
    public function showImportForm()
    {
        return view('ges_tipo1.import');
    }

    /**
     * POST /gestantes/import
     * Procesa el archivo y atrapa cualquier excepción
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new GesTipo1Import, $request->file('excel_file'));

            return redirect()
                ->route('ges_tipo3.import')
                ->with('success', 'Datos importados correctamente.');
        }
        catch (Throwable $e) {
            return redirect()
                ->route('ges_tipo1.import.form')
                ->with('error', nl2br($e->getMessage()));
        }
    }


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


          // 2) Si es usuario tipo 2 (p.ej. “gestor”), limitar sólo a sus propios registros
            if (Auth::user()->usertype == 2) {
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



   public function show($id)
    {
        // Cargamos la gestante y sus Tipo3 asociados
        $gestante = GesTipo1::with('tipo3')->findOrFail($id);

        return view('ges_tipo1.show', compact('gestante'));
    }



    public function exportTipo1(Request $request)
{
    $request->validate([
        'from' => 'required|date',
        'to'   => 'required|date|after_or_equal:from',
    ]);

    $from = $request->query('from');
    $to   = $request->query('to');

    return Excel::download(
        new GesTipo1Export($from, $to),
        "gestantes_created_{$from}_to_{$to}.xlsx"
    );
}
}
