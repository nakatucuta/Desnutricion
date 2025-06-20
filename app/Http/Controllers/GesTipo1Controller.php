<?php

namespace App\Http\Controllers;

use App\Imports\GesTipo1Import;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
// Atrapa verdaderamente cualquier error
use App\Models\GesTipo1;
use DataTables;

use Throwable;

class GesTipo1Controller extends Controller
{
    /**
     * GET /gestantes/import
     * Muestra el formulario de subida
     */
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
                ->route('ges_tipo1.import.form')
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
                // añade aquí otros campos si quieres:
                //'pais_de_la_nacionalidad',
                //'municipio_de_residencia_habitual',
            ]);

            return DataTables::of($query)
                ->addColumn('full_name', function($row) {
                    return trim("{$row->primer_nombre} {$row->segundo_nombre} {$row->primer_apellido} {$row->segundo_apellido}");
                })
                ->addColumn('acciones', function($row) {
                    $show = route('ges_tipo1.show', $row->id);
                    return "<a href=\"{$show}\" class=\"btn btn-sm btn-primary\">Ver</a>";
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        return view('ges_tipo1.index');
    }


    public function show($id)
    {
        // 1) Carga la gestante
        $gestante = GesTipo1::findOrFail($id);

        // 2) Obtiene los PDFs relacionados (por tipo y número de identificación)
        // $pdfs = TamizajePdf::where('tipo_identificacion', $gestante->tipo_identificacion)
        //                   ->where('numero_identificacion', $gestante->numero_identificacion)
        //                   ->orderByDesc('created_at')
        //                   ->get();

        // 3) Envía ambos a la vista
        return view('ges_tipo1.show', compact('gestante'));
    }

}
