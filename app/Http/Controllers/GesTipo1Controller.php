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
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\DB;

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

        // Subconsulta: id del último seguimiento por caso
        $subLastSeg = DB::raw('(SELECT MAX(s.id) FROM ges_tipo1_seguimientos s WHERE s.ges_tipo1_id = ges_tipo1.id) AS last_seg_id');

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
            $subLastSeg, // último seguimiento
        ]);

        // Si es gestor (usertype=2), mostrar solo sus registros
        if (Auth::user()->usertype === 2) {
            $query->where('user_id', Auth::id());
        }

        return DataTables::of($query)
            ->addColumn('full_name', function ($row) {
                return trim("{$row->primer_nombre} {$row->segundo_nombre} {$row->primer_apellido} {$row->segundo_apellido}");
            })
            ->addColumn('acciones', function ($row) {

                // ✅ FIX: esta ruta ahora es gestantes/{ges}
                $show = route('ges_tipo1.show', ['ges' => $row->id]);

                // Crear seguimiento: prefix real -> ges_tipo1/{ges}/seguimientos/create
                if (RouteFacade::has('ges_tipo1.seguimientos.create')) {
                    $seguimientoCreate = route('ges_tipo1.seguimientos.create', ['ges' => $row->id]);
                } else {
                    $seguimientoCreate = url("ges_tipo1/{$row->id}/seguimientos/create");
                }

                // Editar último seguimiento (si existe) -> ges_tipo1/{ges}/seguimientos/{seg}/edit
                if (!empty($row->last_seg_id)) {
                    if (RouteFacade::has('ges_tipo1.seguimientos.edit')) {
                        $editUrl = route('ges_tipo1.seguimientos.edit', [
                            'ges' => $row->id,
                            'seg' => $row->last_seg_id,
                        ]);
                    } else {
                        $editUrl = url("ges_tipo1/{$row->id}/seguimientos/{$row->last_seg_id}/edit");
                    }

                    $seguimientoBtn = <<<HTML
<button class="btn btn-sm btn-primary" title="Ya existe un seguimiento" disabled>
  <i class="fas fa-notes-medical"></i> Seguimiento
</button>
HTML;

                    $editBtn = <<<HTML
<a href="{$editUrl}" class="btn btn-sm btn-warning ml-1" title="Editar último seguimiento">
  <i class="fas fa-edit"></i> Editar
</a>
HTML;
                } else {

                    $seguimientoBtn = <<<HTML
<a href="{$seguimientoCreate}" class="btn btn-sm btn-primary" title="Nuevo seguimiento">
  <i class="fas fa-notes-medical"></i> Seguimiento
</a>
HTML;

                    $editBtn = <<<HTML
<button class="btn btn-sm btn-warning ml-1" title="Sin seguimientos" disabled>
  <i class="fas fa-edit"></i> Editar
</button>
HTML;
                }

                return <<<HTML
<a href="{$show}" class="btn btn-sm btn-gradient mr-1">
  <i class="fas fa-eye mr-1"></i> Ver
</a>
{$seguimientoBtn}
{$editBtn}
HTML;
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    return view('ges_tipo1.index');
}
    /**
     * Mostrar el detalle de una gestante + sus registros relacionados.
     */
  public function show(\App\Models\GesTipo1 $ges)
{
    // Carga relaciones sin ordenar en SQL
    $ges->load(['seguimientos', 'tipo3']);

    // Ordena en PHP para evitar ORDER BY duplicado en SQL Server
    $segs   = $ges->seguimientos->sortByDesc('id')->values();
    $ultimo = $segs->first();

    return view('ges_tipo1.show', [
        'gestante'  => $ges,
        'segs'      => $segs,              // <-- pasa la colección ya ordenada
        'ultimo'    => $ultimo,
        'tipo3'     => $ges->tipo3 ?? collect(),
        'segsCount' => $segs->count(),
    ]);
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
