<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaestroSiv549;
use Yajra\DataTables\DataTables;
use App\Exports\MaestroSiv549Export;
use Maatwebsite\Excel\Facades\Excel;

class MaestroSiv549Controller extends Controller
{
    public function index()
    {
        return view('maestrosiv549.index');
    }

public function data(Request $request)
{
    $casos_asignados = \App\Models\AsignacionesMaestrosiv549::pluck('num_ide_')->toArray();

    $query = MaestroSiv549::select([
        'tip_ide_',
        'num_ide_',
        'pri_nom_',
        'seg_nom_',
        'pri_ape_',
        'seg_ape_',
        'edad_',
        'sexo_',
        'fec_not',
        'semana',
        'year',
        'ocupacion_',
        'telefono_',
        'dir_res_',
        'nom_eve'
    ]);

    return \DataTables::of($query)
        ->addColumn('nombre_completo', function($row) {
            return trim("{$row->pri_nom_} {$row->seg_nom_} {$row->pri_ape_} {$row->seg_ape_}");
        })
        ->addColumn('acciones', function($row) use ($casos_asignados) {
            $asignado = in_array($row->num_ide_, $casos_asignados);
            $url = route('asignaciones-maestrosiv549.create', [
                'tip_ide_' => $row->tip_ide_,
                'num_ide_' => $row->num_ide_,
                'fec_not'  => $row->fec_not,
                'nom_eve'  => $row->nom_eve
            ]);

            $check = $asignado
                ? '<span class="icon-action badge-checklist mr-1" title=\'Asignado\'><i class="fas fa-check"></i></span>'
                : '';

            $btn = '<a href="'.$url.'" class="icon-action btn-asignar" title="Asignar o reasignar este caso">
                        <i class="fas fa-user-plus"></i>
                    </a>';

            return '<div class="acciones-flex">'.$check.$btn.'</div>';
        })
        ->setRowClass(function($row) use ($casos_asignados) {
            return in_array($row->num_ide_, $casos_asignados) ? 'row-asignado' : '';
        })
        ->rawColumns(['acciones'])
        ->make(true);
}

public function export(Request $request)
    {
        abort_if(!auth()->check(), 401);

        // Puedes pasar filtros del querystring si los usas en el export
        $filters  = $request->all();
        $filename = 'reporte_maestro_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new MaestroSiv549Export($filters), $filename);
    }


}
