<?php

namespace App\Http\Controllers;

use App\Models\Cargue412;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\report412Export;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Models\User;
use Session;
use App\Exports\Cargue412Export;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecordatorioControl;        // <-- Importa tu Mailable desde App\Mail
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Exports\Cargue412DesignerExport;
use App\Models\Cargue412AssignmentAudit;

class Cargue412Controller extends Controller
{


    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        $this->middleware('auth');
        // $this->middleware('Admin_seguimiento', ['only' =>'create']);
        //  $this->middleware('Admin_seguimiento', ['only' =>'index']);
        //  $this->middleware('Admin_seguimiento', ['only' =>'alerta']);
        $this->middleware('Admin_seguimiento', ['only' =>'showImportForm']);
        // $this->middleware('Admin_seguimiento', ['only' =>'resporte']);
        // $this->middleware('Admin_seguimiento', ['only' =>'edit']);
        $this->middleware('Admin_seguimiento', ['only' =>'importExcel']);
        // $this->middleware('Admin_nutric_seguimiento', ['only' =>'edit']);
        $this->middleware('Admin_nutric_seguimiento', ['only' =>'index']);
    
       

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $busqueda = $request->busqueda;
        $busqueda = $request->busqueda;
        $user_id = Auth::User()->usertype;
        $user_id1 = Auth::User()->id == '2';
        //pra mostrar lo que cada usuario ingrese 

        if (Auth::User()->usertype == 2) {
            $incomeedit = Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
            'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin','sivigilas.Ips_at_inicial',
            'seguimientos.fecha_consulta','seguimientos.id',
            'seguimientos.fecha_proximo_control','seguimientos.estado','seguimientos.id',
            'seguimientos.motivo_reapuertura')
            ->orderBy('seguimientos.created_at', 'desc')
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            ->where('seguimientos.user_id', Auth::user()->id)
            ->whereYear('seguimientos.created_at', '>', 2023) // Agregar la condición para el año
            ->paginate(3000);
        
        } else {  

            $incomeedit = Seguimiento::select('s.num_ide_','s.pri_nom_','s.seg_nom_',
        's.pri_ape_','s.seg_ape_','seguimientos.id as idin','users.name',
        'seguimientos.fecha_consulta','seguimientos.fecha_proximo_control','seguimientos.estado','seguimientos.id',
        'seguimientos.motivo_reapuertura')
        ->orderBy('seguimientos.created_at', 'desc')
        ->whereYear('seguimientos.created_at', '>', 2023)
        ->join('sivigilas as s', 's.id', '=', 'seguimientos.sivigilas_id')
        ->join('users', 'users.id', '=', 's.user_id')
        ->paginate(3000);

        } 

        if (Auth::User()->usertype == 2) {
        $conteo = Seguimiento::where('estado', 1)
                    ->where('user_id', Auth::user()->id)
                    ->count('id');
        }else{
            $conteo = Seguimiento::where('estado', 1)
            ->whereYear('seguimientos.created_at', '>', 2023)
            ->count('id');

        }
        $seguimientos = Seguimiento::all()->where('estado',1);
        $otro =  Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','sivigilas.id as idin','sivigilas.Ips_at_inicial',
        'seguimientos.id','seguimientos.fecha_proximo_control','seguimientos.estado as est',
        'seguimientos.user_id as usr')
        ->orderBy('seguimientos.created_at', 'desc')
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.estado',1)
        ->get();
        return view('seguimiento.index',compact('incomeedit','seguimientos','conteo','otro'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cargue412 $cargue412)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, $numero_identificacion = null)
    {
        $edit_cargue = Cargue412::find($id);
        if (!$edit_cargue && !empty($numero_identificacion)) {
            $edit_cargue = Cargue412::where('numero_identificacion', $numero_identificacion)
                ->orderByDesc('id')
                ->first();
        }
        if (!$edit_cargue) {
            return redirect()
                ->route('import-excel-form')
                ->with('error', 'No se encontró el registro en este ambiente.');
        }

        if (empty($numero_identificacion)) {
            $numero_identificacion = $edit_cargue->numero_identificacion;
        }

        $incomeedit14 = DB::connection('sqlsrv_1')->table('maestroAfiliados as a')
            ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
            ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
            ->join('maestroIpsGruDet as d', function($join) {
                $join->on('c.id', '=', 'd.idd')
                     ->where('d.servicio', '=', 1);
            })
            ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
            ->select(DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'))
            ->where('a.identificacion', $numero_identificacion)
            ->first(); // Obtener el primer registro de la consulta
        
        if ($incomeedit14 !== null) {
            $income12 = DB::table('users')
                ->select('name', 'id', 'codigohabilitacion')
                ->where('codigohabilitacion', $incomeedit14->codigo_habilitacion)
                ->get();
        } else {
            $income12 = [];
        }
    
        // Obtener los IDs de los usuarios que ya están en income12
        $idsEnIncome12 = $income12->pluck('id')->toArray();
    
        // Filtrar incomeedit15 para que no incluya los IDs que ya están en income12
        $incomeedit15 = DB::table('users')
            ->select('name', 'id', 'codigohabilitacion')
            ->where('usertype', 1)
            ->whereNotIn('id', $idsEnIncome12) // Evitar duplicados
            ->get();
        
        return view('new_412.edit', compact('edit_cargue', 'incomeedit15', 'income12', 'incomeedit14'));
    }
    

    /**
     * Update the specified resource in storage.
     */
  
    
     public function update(Request $request, $id)
{
    // 1) Validar los datos entrantes
    $request->validate([
        'numero_identificacion' => 'required',
        'fecha_captacion'       => 'required|date',
        'primer_nombre'         => 'required',
        'segundo_nombre'        => 'nullable',
        'primer_apellido'       => 'required',
        'segundo_apellido'      => 'nullable',
        'user_id'               => 'required|exists:users,id',
    ], [
        'required' => 'El campo :attribute es obligatorio.',
    ]);

    $before = Cargue412::findOrFail($id);
    $oldAssigned = $before->user_id ? User::find($before->user_id) : null;

    // 2) Preparar datos para la actualización
    $datosEmpleado = $request->except(['_token', '_method']);

    // 3) Ejecutar la transacción y obtener el registro actualizado
    $registro = null;

    DB::transaction(function () use ($id, $datosEmpleado, &$registro) {
        // Actualizar datos
        $seg = Cargue412::where('id', $id)->update($datosEmpleado);

        if ($seg) {
            DB::connection('sqlsrv')->table('dbo.cargue412s')
                ->where('id', $id)
                ->update(['estado' => 1]);
        }

        // Obtener el registro actualizado
        $registro = Cargue412::find($id);
    });

    $newAssigned = $registro && $registro->user_id ? User::find($registro->user_id) : null;
    $oldUserId = $before->user_id ? (int) $before->user_id : null;
    $newUserId = $registro && $registro->user_id ? (int) $registro->user_id : null;

    if ($newUserId !== null) {
        $actionType = 'sin_cambio';
        if ($oldUserId === null && $newUserId !== null) {
            $actionType = 'asignacion';
        } elseif ($oldUserId !== null && $oldUserId !== $newUserId) {
            $actionType = 'reasignacion';
        }

        Cargue412AssignmentAudit::create([
            'cargue412_id' => (int) $registro->id,
            'performed_by_user_id' => Auth::id(),
            'old_assigned_user_id' => $oldUserId,
            'new_assigned_user_id' => $newUserId,
            'action_type' => $actionType,
            'numero_identificacion' => $registro->numero_identificacion,
            'paciente_nombre' => trim(implode(' ', array_filter([
                $registro->primer_nombre,
                $registro->segundo_nombre,
                $registro->primer_apellido,
                $registro->segundo_apellido,
            ]))),
            'municipio' => $registro->municipio,
            'fecha_captacion' => $registro->fecha_captacion,
            'old_assigned_name' => $oldAssigned?->name,
            'new_assigned_name' => $newAssigned?->name,
            'old_assigned_email' => $oldAssigned?->email,
            'new_assigned_email' => $newAssigned?->email,
            'old_assigned_code' => $oldAssigned?->codigohabilitacion,
            'new_assigned_code' => $newAssigned?->codigohabilitacion,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'changes' => [
                'old_user_id' => $oldUserId,
                'new_user_id' => $newUserId,
            ],
        ]);
    }

    // 4) Preparar datos para el correo
    $datosCorreo = [
        'id'                     => $registro->id,
        'fecha_captacion'        => $registro->fecha_captacion,
        'numero_identificacion'  => $registro->numero_identificacion,
        'primer_nombre'          => $registro->primer_nombre,
        'segundo_nombre'         => $registro->segundo_nombre,
        'primer_apellido'        => $registro->primer_apellido,
        'segundo_apellido'       => $registro->segundo_apellido,
    ];

    // 5) Construir el cuerpo del correo (sin duplicar)
    $r = $registro;

    $bodyText = '<br>';
    $bodyText .= "Fecha de notificación: <strong>{$r->fecha_captacion}</strong><br>";
    $bodyText .= "Identificación: <strong>{$r->numero_identificacion}</strong><br>";
    $bodyText .= "Primer Nombre: <strong>{$r->primer_nombre}</strong><br>";
    $bodyText .= "Segundo Nombre: <strong>{$r->segundo_nombre}</strong><br>";
    $bodyText .= "Primer Apellido: <strong>{$r->primer_apellido}</strong><br>";
    $bodyText .= "Segundo Apellido: <strong>{$r->segundo_apellido}</strong><br>";

    // 6) Enviar el correo
    $mailSent = false;
    $user = User::find($request->user_id);

    if ($user && $user->email) {
        try {
            Mail::to($user->email)
                ->send(new RecordatorioControl($datosCorreo, $bodyText));
            $mailSent = true;
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo: '.$e->getMessage());
        }
    } else {
        Log::warning("Usuario ID {$request->user_id} sin email válido.");
    }

    // 7) Redirigir con mensaje flash
    $flashKey = $mailSent ? 'success' : 'warning';
    $flashMsg = $mailSent
        ? 'Registro actualizado y correo enviado correctamente.'
        : 'Registro actualizado, pero no se pudo enviar el correo.';

    return redirect()
        ->route('import-excel')
        ->with($flashKey, $flashMsg);
}

     



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cargue412 $cargue412, $id)
    {
        Cargue412::destroy($id);
        // Session::flash('error','El registro se ha agregado correctamente');
        return redirect('import-excel')->with('error', 'Empleado borrado con exito');
    }


      // Muestra la vista (no hace queries pesadas)
    public function showImportForm()
    {
        $years = Cache::remember('cargue412_years_optima', 900, function () {
            return DB::connection('sqlsrv')
                ->table('dbo.vCargue412Optima')
                ->selectRaw('YEAR(fecha_captacion) as year')
                ->whereNotNull('fecha_captacion')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year');
        });
        $defaultYear = $years->first();
        $reportColumns = $this->reportColumnCatalog();
        $defaultReportColumns = [
            'fecha_cargue',
            'id',
            'nombre_coperante',
            'fecha_captacion',
            'municipio',
            'nombres_completos',
            'tipo_identificacion',
            'numero_identificacion',
            'sexo',
            'edad_meses',
            'ips_primaria',
        ];
        return view('new_412.form', compact('years', 'defaultYear', 'reportColumns', 'defaultReportColumns'));
    }

    // Endpoint para DataTables
    public function getData(Request $request)
    {
        $query = $this->build412Query($request)->select([
            'fecha_cargue',
            'id',
            'nombre_coperante',
            'fecha_captacion',
            'municipio',
            'nombres_completos',
            'tipo_identificacion',
            'numero_identificacion',
            'sexo',
            'edad_meses',
            'ips_primaria',
            'user_id',
        ]);

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                return $this->renderAcciones($row);
            })
            ->skipTotalRecords()
            ->rawColumns(['acciones'])
            ->toJson();
    }

    /**
     * Genera el HTML de las acciones según procesado y user_id.
     */
    private function build412Query(Request $request)
    {
        $query = DB::connection('sqlsrv')->table('dbo.vCargue412Optima');

        $year = $request->get('year');
        if (empty($year)) {
            $year = Cache::remember('cargue412_default_year_optima', 900, function () {
                return DB::connection('sqlsrv')
                    ->table('dbo.vCargue412Optima')
                    ->selectRaw('MAX(YEAR(fecha_captacion)) as y')
                    ->value('y');
            });
        }
        if (!empty($year)) {
            $query->whereYear('fecha_captacion', $year);
        }

        $captacionDesde = $request->get('captacion_desde');
        if (!empty($captacionDesde)) {
            $query->whereDate('fecha_captacion', '>=', $captacionDesde);
        }

        $captacionHasta = $request->get('captacion_hasta');
        if (!empty($captacionHasta)) {
            $query->whereDate('fecha_captacion', '<=', $captacionHasta);
        }

        $municipio = trim((string) $request->get('municipio', ''));
        if ($municipio !== '') {
            $query->where('municipio', 'like', '%' . $municipio . '%');
        }

        $sexo = trim((string) $request->get('sexo', ''));
        if ($sexo !== '') {
            $query->where('sexo', $sexo);
        }

        // Procesado por ambiente:
        // se considera procesado cuando el caso ya tiene asignado user_id en la BD del entorno actual.
        $procesado = trim((string) $request->get('procesado', ''));
        if ($procesado !== '') {
            if ($procesado === '1') {
                $query->whereNotNull('user_id');
            } elseif ($procesado === '0') {
                $query->whereNull('user_id');
            }
        }

        $ipsPrimaria = trim((string) $request->get('ips_primaria', ''));
        if ($ipsPrimaria !== '') {
            $query->where('ips_primaria', 'like', '%' . $ipsPrimaria . '%');
        }

        $busquedaRapida = trim((string) $request->get('q', ''));
        if ($busquedaRapida !== '') {
            $query->where(function ($q) use ($busquedaRapida) {
                $q->where('numero_identificacion', 'like', '%' . $busquedaRapida . '%')
                    ->orWhere('nombres_completos', 'like', '%' . $busquedaRapida . '%')
                    ->orWhere('nombre_coperante', 'like', '%' . $busquedaRapida . '%')
                    ->orWhere('municipio', 'like', '%' . $busquedaRapida . '%')
                    ->orWhere('ips_primaria', 'like', '%' . $busquedaRapida . '%');
            });
        }

        return $query;
    }

    private function reportColumnCatalog(): array
    {
        return [
            'fecha_cargue' => ['label' => 'Fecha Cargue', 'db' => 'fecha_cargue'],
            'id' => ['label' => 'ID', 'db' => 'id'],
            'numero_orden' => ['label' => 'Numero Orden', 'db' => 'numero_orden'],
            'nombre_coperante' => ['label' => 'Nombre Coperante', 'db' => 'nombre_coperante'],
            'fecha_captacion' => ['label' => 'Fecha Captacion', 'db' => 'fecha_captacion'],
            'municipio' => ['label' => 'Municipio', 'db' => 'municipio'],
            'nombres_completos' => ['label' => 'Nombres Completos', 'db' => 'nombres_completos'],
            'tipo_identificacion' => ['label' => 'Tipo Identificacion', 'db' => 'tipo_identificacion'],
            'numero_identificacion' => ['label' => 'Numero Identificacion', 'db' => 'numero_identificacion'],
            'sexo' => ['label' => 'Sexo', 'db' => 'sexo'],
            'edad_meses' => ['label' => 'Edad Meses', 'db' => 'edad_meses'],
            'ips_primaria' => ['label' => 'IPS Primaria', 'db' => 'ips_primaria'],
            'nombre_rancheria' => ['label' => 'Rancheria', 'db' => 'nombre_rancheria'],
            'ubicacion_casa' => ['label' => 'Ubicacion Casa', 'db' => 'ubicacion_casa'],
            'nombre_cuidador' => ['label' => 'Nombre Cuidador', 'db' => 'nombre_cuidador'],
            'identioficacion_cuidador' => ['label' => 'ID Cuidador', 'db' => 'identioficacion_cuidador'],
            'telefono_cuidador' => ['label' => 'Telefono Cuidador', 'db' => 'telefono_cuidador'],
            'regimen_afiliacion' => ['label' => 'Regimen Afiliacion', 'db' => 'regimen_afiliacion'],
            'nombre_eapb_menor' => ['label' => 'EAPB Menor', 'db' => 'nombre_eapb_menor'],
            'peso_kg' => ['label' => 'Peso (Kg)', 'db' => 'peso_kg'],
            'logitud_talla_cm' => ['label' => 'Talla (cm)', 'db' => 'logitud_talla_cm'],
            'perimetro_braqueal' => ['label' => 'Perimetro Braqueal', 'db' => 'perimetro_braqueal'],
            'puntaje_z' => ['label' => 'Puntaje Z', 'db' => 'puntaje_z'],
            'calsificacion_antropometrica' => ['label' => 'Clasificacion Antropometrica', 'db' => 'calsificacion_antropometrica'],
            'procesado' => ['label' => 'Procesado', 'db' => 'procesado'],
            'nombre_profesional' => ['label' => 'Profesional Asignado', 'db' => 'nombre_profesional'],
            'user_id' => ['label' => 'ID Usuario', 'db' => 'user_id'],
            'created_at' => ['label' => 'Creado En', 'db' => 'created_at'],
            'updated_at' => ['label' => 'Actualizado En', 'db' => 'updated_at'],
        ];
    }

    public function reportPreview(Request $request)
    {
        $catalog = $this->reportColumnCatalog();
        $requestedColumns = $request->input('columns', []);
        if (!is_array($requestedColumns)) {
            $requestedColumns = [];
        }

        $columns = array_values(array_filter($requestedColumns, fn($c) => isset($catalog[$c])));
        if (empty($columns)) {
            $columns = ['fecha_cargue', 'id', 'nombre_coperante', 'fecha_captacion', 'municipio', 'nombres_completos', 'numero_identificacion'];
        }

        $selects = [];
        foreach ($columns as $key) {
            $db = $catalog[$key]['db'];
            $selects[] = DB::raw("[$db] as [$key]");
        }

        $rows = $this->build412Query($request)
            ->select($selects)
            ->orderByDesc('fecha_captacion')
            ->limit(40)
            ->get()
            ->map(function ($row) use ($columns) {
                $arr = [];
                foreach ($columns as $col) {
                    $arr[$col] = $row->{$col} ?? null;
                }
                return $arr;
            })
            ->values();

        $headings = [];
        foreach ($columns as $key) {
            $headings[$key] = $catalog[$key]['label'];
        }

        return response()->json([
            'ok' => true,
            'columns' => $columns,
            'headings' => $headings,
            'rows' => $rows,
        ]);
    }

    public function reportExport(Request $request)
    {
        $catalog = $this->reportColumnCatalog();
        $requestedColumns = $request->input('columns', []);
        if (!is_array($requestedColumns)) {
            $requestedColumns = [];
        }

        $columns = array_values(array_filter($requestedColumns, fn($c) => isset($catalog[$c])));
        if (empty($columns)) {
            $columns = ['fecha_cargue', 'id', 'nombre_coperante', 'fecha_captacion', 'municipio', 'nombres_completos', 'numero_identificacion'];
        }

        $format = strtolower((string) $request->input('format', 'csv'));
        if (!in_array($format, ['csv', 'xlsx'])) {
            $format = 'csv';
        }

        $selects = [];
        foreach ($columns as $key) {
            $db = $catalog[$key]['db'];
            $selects[] = DB::raw("[$db] as [$key]");
        }

        $rows = $this->build412Query($request)
            ->select($selects)
            ->orderByDesc('fecha_captacion')
            ->limit(100000)
            ->get()
            ->map(function ($row) use ($columns) {
                $arr = [];
                foreach ($columns as $col) {
                    $arr[$col] = $row->{$col} ?? '';
                }
                return $arr;
            })
            ->values()
            ->all();

        $headings = array_map(fn($col) => $catalog[$col]['label'], $columns);
        $fileBase = '412_reporte_disenado_' . now()->format('Ymd_His');

        if ($format === 'xlsx') {
            return Excel::download(
                new Cargue412DesignerExport($rows, $headings, $columns),
                $fileBase . '.xlsx',
                ExcelFormat::XLSX
            );
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileBase . '.csv"',
        ];

        $callback = function () use ($rows, $columns, $headings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headings);
            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $line[] = $row[$col] ?? '';
                }
                fputcsv($handle, $line);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function renderAcciones($row)
    {
        $token = csrf_token();
        // URLs y formulario oculto
        $editUrl = url("new412/{$row->id}/{$row->numero_identificacion}/edit");
        $delUrl  = url("new412/{$row->id}");
        $formId  = "del-{$row->id}";

        $deleteForm = <<<HTML
<form id="{$formId}" action="{$delUrl}" method="POST" style="display:none">
  <input type="hidden" name="_method" value="DELETE">
  <input type="hidden" name="_token" value="{$token}">
</form>
HTML;

        // 3) Si no tiene user_id: permitir borrar y editar
        if (is_null($row->user_id)) {
            return <<<HTML
<a href="{$delUrl}" class="btn btn-sm aw-btn-delete"
   onclick="event.preventDefault(); if(confirm('¿Eliminar registro?')) document.getElementById('{$formId}').submit();">
  <i class="fas fa-trash"></i>
</a>
{$deleteForm}
<a href="{$editUrl}" class="btn btn-sm aw-btn-edit">
  <i class="fas fa-edit"></i>
</a>
HTML;
        }

        // 4) Ya tiene user_id: mostrar procesado + permitir editar y borrar
        return <<<HTML
<a class="btn btn-sm aw-btn-processed" title="Procesado">
  <i class="fas fa-stop"></i> Procesado
</a>
<a href="{$editUrl}" class="btn btn-sm aw-btn-edit">
  <i class="fas fa-tools"></i>
</a>
<a href="{$delUrl}" class="btn btn-sm aw-btn-delete"
   onclick="event.preventDefault(); if(confirm('¿Eliminar registro?')) document.getElementById('{$formId}').submit();">
  <i class="fas fa-trash"></i>
</a>
{$deleteForm}
HTML;
    }
    public function importExcel(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        Excel::import(new report412Export, $file);

        return redirect()->route('import-excel-form')->with('success', 'Datos importados correctamente');
    }

public function reporte1cargue412()
{
    $fileName = '412_reporte.csv';
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$fileName\"",
    ];

    $callback = function() {
        $handle = fopen('php://output', 'w');

        // 1) Encabezados en el mismo orden
        fputcsv($handle, [
            'id',
            'fecha_captacion',
            'ips_primaria',
            'IPS_ASIGNADA',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'tipo_identificacion',
            'numero_identificacion',
            'sexo',
            'fecha_nacimieto_nino',
            'edad_meses',
            'regimen_afiliacion',
            'nombre_eapb_menor',
            'peso_kg',
            'logitud_talla_cm',
            'perimetro_braqueal',
            'signos_peligro_infeccion_respiratoria',
            'sexosignos_desnutricion',
            'puntaje_z',
            'calsificacion_antropometrica',
            // el resto a tu parecer:
            'numero_orden',
            'nombre_coperante',
            
            'municipio',
            'nombre_rancheria',
            'ubicacion_casa',
            'nombre_cuidador',
            'identioficacion_cuidador',
            'telefono_cuidador',
            'nombre_eapb_cuidador',
            'nombre_autoridad_trad_ansestral',
            'datos_contacto_autoridad',
            'estado',
            'user_id',
            'created_at',
            'updated_at',
            'numero_profesional',
            'uds',
        ]);

        // 2) Leer y escribir en chunks
        Cargue412::from('dbo.cargue412s as a')
            ->select(
                'a.id',
                'a.fecha_captacion',
                'd.descrip as ips_primaria',
                'u.name as nombre_profesional',
                'a.primer_nombre',
                'a.segundo_nombre',
                'a.primer_apellido',
                'a.segundo_apellido',
                'a.tipo_identificacion',
                'a.numero_identificacion',
                'a.sexo',
                'a.fecha_nacimieto_nino',
                'a.edad_meses',
                'a.regimen_afiliacion',
                'a.nombre_eapb_menor',
                'a.peso_kg',
                'a.logitud_talla_cm',
                'a.perimetro_braqueal',
                'a.signos_peligro_infeccion_respiratoria',
                'a.sexosignos_desnutricion',
                'a.puntaje_z',
                'a.calsificacion_antropometrica',
                // resto de campos:
                'a.numero_orden',
                'a.nombre_coperante',
                
                'a.municipio',
                'a.nombre_rancheria',
                'a.ubicacion_casa',
                'a.nombre_cuidador',
                'a.identioficacion_cuidador',
                'a.telefono_cuidador',
                'a.nombre_eapb_cuidador',
                'a.nombre_autoridad_trad_ansestral',
                'a.datos_contacto_autoridad',
                'a.estado',
                'a.user_id',
                'a.created_at',
                'a.updated_at',
                'a.numero_profesional',
                'a.uds'
            )
            ->leftJoin(
                DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroidentificaciones] as b'),
                function($join) {
                    $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
                         ->on('a.numero_identificacion', '=', 'b.identificacion');
                }
            )
            ->leftJoin(
                DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as c'),
                'b.numeroCarnet', '=', 'c.numeroCarnet'
            )
            ->leftJoin(
                DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as d'),
                'c.idGrupoIps', '=', 'd.id'
            )
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->orderBy('a.id', 'asc')
            ->chunk(500, function($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                          $row->fecha_captacion,
                        $row->ips_primaria,
                        $row->nombre_profesional,
                        $row->primer_nombre,
                        $row->segundo_nombre,
                        $row->primer_apellido,
                        $row->segundo_apellido,
                        $row->tipo_identificacion,
                        $row->numero_identificacion,
                        $row->sexo,
                        $row->fecha_nacimieto_nino,
                        $row->edad_meses,
                        $row->regimen_afiliacion,
                        $row->nombre_eapb_menor,
                        $row->peso_kg,
                        $row->logitud_talla_cm,
                        $row->perimetro_braqueal,
                        $row->signos_peligro_infeccion_respiratoria,
                        $row->sexosignos_desnutricion,
                        $row->puntaje_z,
                        $row->calsificacion_antropometrica,
                        // resto de campos:
                        $row->numero_orden,
                        $row->nombre_coperante,
                      
                        $row->municipio,
                        $row->nombre_rancheria,
                        $row->ubicacion_casa,
                        $row->nombre_cuidador,
                        $row->identioficacion_cuidador,
                        $row->telefono_cuidador,
                        $row->nombre_eapb_cuidador,
                        $row->nombre_autoridad_trad_ansestral,
                        $row->datos_contacto_autoridad,
                        $row->estado,
                        $row->user_id,
                        $row->created_at,
                        $row->updated_at,
                        $row->numero_profesional,
                        $row->uds,
                    ]);
                }
            });

        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
}

}







