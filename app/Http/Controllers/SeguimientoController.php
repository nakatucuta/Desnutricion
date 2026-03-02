<?php

namespace App\Http\Controllers;
use App\Models\Ingreso;
use App\Models\Sivigila;
use App\Models\Seguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SeguimientoExport;
use App\Exports\DashboardSeguimientoExport;
use App\Exports\GeneralExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Models\User;
// use DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SeguimientoController extends Controller
{

    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        $this->middleware('auth');
        // $this->middleware('Admin_seguimiento', ['only' =>'create']);
        //  $this->middleware('Admin_seguimiento', ['only' =>'index']);
        //  $this->middleware('Admin_seguimiento', ['only' =>'alerta']);
        $this->middleware('Admin_seguimiento', ['only' =>'reporte2']);
        // $this->middleware('Admin_seguimiento', ['only' =>'resporte']);
        // $this->middleware('Admin_seguimiento', ['only' =>'edit']);
        $this->middleware('Admin_seguimiento', ['only' =>'destroy']);
        // $this->middleware('Admin_nutric_seguimiento', ['only' =>'edit']);
        $this->middleware('Admin_nutric_seguimiento', ['only' =>'destroy']);
    
       

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function data(Request $request)
    {
        $user = Auth::user();
    
        $query = DB::table('vw_seguimientos')
            ->select([
                'id',
                'creado',
                'num_ide',
                'semana',
                'nombre',
                'estado',
                'fec_creado',
                DB::raw("CASE WHEN '{$user->usertype}' = 1 THEN usuario_ips ELSE usuario_ips END as ips"),
                'fecha_proximo_control',
                'motivo_reapuertura',
                'user_id',
            ]);
    
        // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ FILTRADO DE ACCESO
        if (!in_array($user->usertype, [1, 3])) {
            $query->where('user_id', $user->id);
        }
        
    


        // ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â filtro por aÃƒÆ’Ã‚Â±o (campo fec_creado)
        if ($request->filled('anio')) {
            $query->whereYear('creado', $request->anio);
        }

        // ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â filtros de "estado" (abiertos/cerrados)
        if ($request->filled('estado')) {
            if ($request->estado === '1') {
                $query->where('estado', 1);
            } elseif ($request->estado === '0') {
                $query->where('estado', '!=', 1);
            }
        }
    
        // ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â filtro "prÃƒÆ’Ã‚Â³ximos"
        if ($request->proximo === '1') {
            $query->whereNotNull('fecha_proximo_control')
                  ->whereDate('fecha_proximo_control', '>=', Carbon::today());
        }
    
        return DataTables::of($query)
            ->editColumn('creado', fn($r) => Carbon::parse($r->creado)->format('Y-m-d'))
            ->editColumn('fecha_proximo_control', fn($r) =>
                $r->fecha_proximo_control
                    ? Carbon::parse($r->fecha_proximo_control)->format('Y-m-d')
                    : '-'
            )
            ->editColumn('estado', fn($r) =>
                $r->estado == 1
                    ? '<span class="badge badge-success">Abierto</span>'
                    : '<span class="badge badge-secondary">Cerrado</span>'
            )
            ->addColumn('acciones', function($r) use ($user) {
                $dropdown = '<div class="dropdown">
                    <button class="btn btn-sm btn-acciones dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs mr-1"></i> Acciones
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">';
            
                $dropdown .= '<a class="dropdown-item" href="'.route('Seguimiento.edit', $r->id).'">
                                <i class="fas fa-edit mr-2 text-success"></i>Editar</a>';
            
                if (!empty($r->motivo_reapuertura)) {
                    $dropdown .= '<a class="dropdown-item" href="'.route('detalleseguimiento', $r->id).'">
                                    <i class="far fa-eye mr-2 text-primary"></i>Ver Detalles</a>';
                }
            
                $dropdown .= '<a class="dropdown-item" href="'.route('seguimiento.view-pdf', $r->id).'" target="_blank">
                                <i class="far fa-file-pdf mr-2 text-danger"></i>Ver PDF</a>';
            
                if ($user->usertype != 2) {
                    $dropdown .= '<form method="POST" action="'.route('Seguimiento.destroy', $r->id).'" onsubmit="return confirm(\'Ãƒâ€šÃ‚Â¿Seguro que deseas eliminar?\')" style="display:inline;">
                                    '.csrf_field().method_field('DELETE').'
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="fas fa-trash-alt mr-2"></i>Eliminar
                                    </button>
                                  </form>';
                }
            
                $dropdown .= '</div></div>';
                return $dropdown;
            })
            
            
            ->rawColumns(['estado','acciones'])
            ->filter(function($builder) use ($request) {
                if ($search = $request->input('search.value')) {
                    $builder->where('num_ide', 'like', "%{$search}%");
                }
            })
            ->toJson();
    }
    
    /**
     * 2) Vista principal: contadores + tabla.
     */
    public function index()
{
    $user = Auth::user();
    $isAdmin = in_array($user->usertype, [1, 3]);

    // ABIERTO
    $conteo = Seguimiento::where('estado', 1)
                ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
                ->whereYear('created_at', '>', 2023)
                ->count();

    // PRÃƒÆ’Ã¢â‚¬Å“XIMOS
    $otro = DB::table('sivigilas')
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->select([
            'sivigilas.num_ide_',
            'sivigilas.pri_nom_',
            'sivigilas.seg_nom_',
            'sivigilas.pri_ape_',
            'sivigilas.seg_ape_',
            'sivigilas.id as idin',
            'sivigilas.Ips_at_inicial',
            'seguimientos.id as seguimiento_id',
            'seguimientos.fecha_proximo_control',
            'seguimientos.estado as est',
            'seguimientos.user_id as usr',
            'seguimientos.estado as estado',

        ])
        ->where('seguimientos.estado', 1)
        ->when(!$isAdmin, fn($q) => $q->where('seguimientos.user_id', $user->id))
        ->orderBy('seguimientos.created_at', 'desc')
        ->get();

    // CERRADOS
    $cerrados = Seguimiento::where('estado', '!=', 1)
                ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
                ->whereYear('created_at', '>', 2023)
                ->count();

    // Para mantener compatibilidad si la vista usa esta colecciÃƒÆ’Ã‚Â³n
    $seguimientos = Seguimiento::where('estado', 1)
                      ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
                      ->get();

    return view('seguimiento.index', compact(
        'conteo',
        'seguimientos',
        'otro',
        'cerrados'
    ));
}


public function viewPDF($id)
{
    $seguimiento = Seguimiento::findOrFail($id);
    $nombreArchivo = $seguimiento->pdf;

    // Ruta 1: raÃƒÆ’Ã‚Â­z de /public
    $ruta1 = $nombreArchivo;

    // Ruta 2: subcarpeta /public/pdf
    $ruta2 = 'pdf/' . $nombreArchivo;

    if (Storage::disk('public')->exists($ruta1)) {
        \Log::info("viewPDF: encontrado en disk(public): {$ruta1}");
        return redirect(Storage::url($ruta1));
    }

    if (Storage::disk('public')->exists($ruta2)) {
        \Log::info("viewPDF: encontrado en disk(public): {$ruta2}");
        return redirect(Storage::url($ruta2));
    }

    // No encontrado en ninguna de las dos rutas
    \Log::error("viewPDF ERROR: no existe en disk(public): {$ruta1} ni {$ruta2}");
    abort(404, 'El archivo PDF no se encuentra disponible.');
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $yearActual = Carbon::now()->year;
    
        $incomeedit = DB::table('sivigilas')
            ->select(
                'sivigilas.num_ide_',
                'sivigilas.pri_nom_',
                'sivigilas.seg_nom_',
                'sivigilas.pri_ape_',
                'sivigilas.seg_ape_',
                'sivigilas.id as idin',
                'sivigilas.fec_not'
            )
            ->where('sivigilas.estado', 1)
            ->where('user_id', Auth::id())
            ->whereYear('sivigilas.created_at', '>', 2023)
            ->get();
    
        $income12 = DB::connection('sqlsrv_1')
            ->table('refIps')->select('descrip')
            ->where('codigoDepartamento', 44)
            ->get();
    
        return view('seguimiento.create', compact('incomeedit','income12'));
    }

    // AJAX: devuelve JSON con hasta 30 pacientes buscados
    public function searchPacientes(Request $request)
    {
        $q = $request->get('q', '');

        $lista = DB::table('sivigilas')
            ->select('id as idin', 'pri_nom_', 'pri_ape_', 'fec_not')
            ->where('estado', 1)
            ->where('user_id', Auth::id())
            ->where(function($w) use ($q) {
                $w->where('pri_nom_', 'like', "%{$q}%")
                  ->orWhere('pri_ape_', 'like', "%{$q}%")
                  ->orWhere('id', 'like', "%{$q}%");
            })
            ->limit(30)
            ->get();

        $result = $lista->map(function($p){
            $fecha = $p->fec_not
                ? Carbon::parse($p->fec_not)->format('Y-m-d')
                : 'SIN FECHA';
            return [
                'idin' => $p->idin,
                'text' => "({$fecha}) {$p->pri_nom_} {$p->pri_ape_}"
            ];
        });

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 1) ValidaciÃƒÆ’Ã‚Â³n
        $request->validate([
            'fecha_consulta'                             => 'required|date',
            'peso_kilos'                                 => 'required|numeric',
            'talla_cm'                                   => 'required|numeric',
            'puntajez'                                   => 'required|numeric',
            'clasificacion'                              => 'required|string',
            'requerimiento_energia_ftlc'                 => 'required|string',
            'fecha_entrega_ftlc'                         => 'nullable|date',
            'medicamento'                                => 'required|array',
            'motivo_reapuertura'                         => 'nullable|string',
            'observaciones'                              => 'required|string',
            'est_act_menor'                              => 'nullable|string',
            'tratamiento_f75'                            => 'nullable|string',
            'fecha_recibio_tratf75'                      => 'nullable|date',
            'fecha_proximo_control'                      => 'nullable|date',  //|after_or_equal:today
            'sivigilas_id'                               => 'required|exists:sivigilas,id',
            'Esquemq_complrto_pai_edad'                  => 'required|string',
            'Atecion_primocion_y_mantenimiento_res3280_2018' => 'required|string',
            'pdf'                                        => 'required|nullable|mimes:pdf|max:5048',
            'estado'                                     => 'required|in:0,1',
        ],[
            'required' => 'El campo :attribute es obligatorio.',
            'numeric'  => 'El campo :attribute debe ser numÃƒÆ’Ã‚Â©rico.',
            'date'     => 'El campo :attribute debe ser una fecha vÃƒÆ’Ã‚Â¡lida.',
            'after_or_equal' => 'El campo :attribute debe ser igual o posterior a LA FECHA ACTUAL NO PUEDE COLOCAR UNA  FECHA DE PROXIMO SEGUIMIENTO MENOR A LA  ACTUAL.',

        ]);

        // 2) Evitar duplicados futuros
        if (Seguimiento::where('sivigilas_id', $request->sivigilas_id)
            ->where('fecha_proximo_control', '>', now())
            ->exists()
        ) {
            return redirect()
                ->route('Seguimiento.index')
                ->with('error1', 'No puedes hacer un seguimiento porque la fecha de control no se ha cumplido');
        }

        // 3) Crear todo en transacciÃƒÆ’Ã‚Â³n
        DB::transaction(function() use ($request, &$seguimiento) {
            $data = $request->only([
                'fecha_consulta','peso_kilos','talla_cm','puntajez',
                'clasificacion','requerimiento_energia_ftlc','fecha_entrega_ftlc',
                'motivo_reapuertura','observaciones','est_act_menor',
                'tratamiento_f75','fecha_recibio_tratf75','fecha_proximo_control',
                'sivigilas_id','Esquemq_complrto_pai_edad','perimetro_braqueal',
                'Atecion_primocion_y_mantenimiento_res3280_2018','estado'
            ]);
            $data['medicamento'] = implode(',', $request->medicamento);
            $data['user_id']     = Auth::id();

            // pdf opcional
            if ($request->hasFile('pdf')) {
                $data['pdf'] = $request->file('pdf')->store('pdf','public');
            }

            $seguimiento = Seguimiento::create($data);

            // actualizar estado en Sivigila
            Sivigila::where('id', $data['sivigilas_id'])
                ->update(['estado' => $data['estado']]);

            // desactivar antiguos
            Seguimiento::where('sivigilas_id', $data['sivigilas_id'])
                ->where('id','<',$seguimiento->id)
                ->update(['estado'=>0]);
        });

        // 4) Enviar correo si es estado=1
        $mailSent = false;
        if ($seguimiento->estado == 1) {
            $sivigila = Sivigila::find($seguimiento->sivigilas_id);
            $user     = $sivigila ? User::find($sivigila->user_id) : null;

            if ($user && $user->email) {
                $bodyText = "<br>"
                    . "ID seguimiento: <strong>{$seguimiento->id}</strong><br>"
                    . "Paciente: <strong>{$sivigila->pri_nom_} {$sivigila->seg_nom_} "
                                ."{$sivigila->pri_ape_} {$sivigila->seg_ape_}</strong><br>"
                    . "PrÃƒÆ’Ã‚Â³ximo control: <strong>{$seguimiento->fecha_proximo_control}</strong><br>";

                $html = "Hola, se ha registrado un seguimiento para el paciente:<br>"
                      . $bodyText
                      . "Por favor gestiona lo antes posible en "
                      . "<a href=\"".url('login')."\">la aplicaciÃƒÆ’Ã‚Â³n</a>.";

                // SMTP SSL puerto 465
                $transport = new EsmtpTransport(
                    env('MAIL_HOST','smtp.gmail.com'),
                    465,
                    'ssl'
                );
                $transport->setUsername(env('MAIL_USERNAME'));
                $transport->setPassword(env('MAIL_PASSWORD'));

                Log::info("SMTP configurado: ".env('MAIL_HOST').":465 ssl");

                $mailer = new Mailer($transport);
                $email  = (new Email())
                    ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                    ->to(new Address($user->email))
                    ->subject('Nuevo seguimiento asignado')
                    ->html($html);

                try {
                    $mailer->send($email);
                    $mailSent = true;
                    Log::info("Correo de seguimiento enviado a {$user->email}");
                } catch (\Throwable $e) {
                    Log::warning("Error SMTP: {$e->getMessage()}");
                }
            } else {
                Log::warning("Usuario ID {$sivigila->user_id} sin email vÃƒÆ’Ã‚Â¡lido");
            }
        }

        // 5) Redirigir con mensaje
        $key = $mailSent ? 'success' : 'warning';
        $msg = $mailSent
            ? 'Seguimiento guardado y correo enviado.'
            : 'Seguimiento guardado, pero no se pudo enviar correo.';

        return redirect()
            ->route('Seguimiento.index')
            ->with($key, $msg);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Seguimiento  $seguimiento
     * @return \Illuminate\Http\Response
     */
    public function show(Seguimiento $seguimiento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Seguimiento  $seguimiento
     * @return \Illuminate\Http\Response
     */
    public function edit(Seguimiento $seguimiento , $id)
    {   

       


        $incomeedit = DB::table('sivigilas')->select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','sivigilas.id as idin')
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.id', $id)
        ->get();

        
        $empleado = Seguimiento::findOrFail($id); 
        return view('seguimiento.edit',compact('empleado','incomeedit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Seguimiento  $seguimiento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seguimiento $seguimiento, $id)
    {
        // 1) ValidaciÃƒÆ’Ã‚Â³n
        $request->validate([
            'fecha_consulta' => 'required|string|max:100',
            'peso_kilos'     => 'required',
            'talla_cm'       => 'required',
            'puntajez'       => 'required',
            'clasificacion'  => 'required',
            'requerimiento_energia_ftlc' => 'required',
            'medicamento'    => 'required',
            'Esquemq_complrto_pai_edad'  => 'required',
            'Atecion_primocion_y_mantenimiento_res3280_2018' => 'required',
            'observaciones'  => 'required',
            'sivigilas_id'   => 'required|exists:sivigilas,id',
            'estado'         => 'required|in:0,1',
            'pdf'            => 'nullable|mimes:pdf|max:2048',
        ], [
            'required' => 'El campo :attribute es requerido (DEBE LLENARLO PARA CONTINUAR).',
        ]);

        // 2) Preparamos datos y actualizamos el seguimiento
        $datos = $request->except(['_token','_method']);
        $datos['medicamento'] = implode(',', $datos['medicamento']);

        if ($request->hasFile('pdf')) {
            $datos['pdf'] = $request->file('pdf')->store('pdf','public');
        }

        Seguimiento::where('id', $id)->update($datos);

        // 3) Reflejar estado en Sivigila
        DB::table('sivigilas')
            ->where('id', $request->sivigilas_id)
            ->update(['estado' => $request->estado ? 1 : 0]);

        // 4) Construir body del correo
        $resultados = DB::table('seguimientos')
            ->select(
                'seguimientos.id',
                'seguimientos.motivo_reapuertura',
                'sivigilas.pri_nom_',
                'sivigilas.seg_nom_',
                'sivigilas.pri_ape_',
                'sivigilas.seg_ape_'
            )
            ->where('seguimientos.id', $id)
            ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
            ->get();

        $bodyText = '<br>';
        foreach ($resultados as $r) {
            $bodyText .= "Id de seguimiento: <strong>{$r->id}</strong><br>";
            $bodyText .= "Motivo de reapertura: <strong>{$r->motivo_reapuertura}</strong><br>";
            $bodyText .= "Primer nombre: <strong>{$r->pri_nom_}</strong><br>";
            $bodyText .= "Segundo nombre: <strong>{$r->seg_nom_}</strong><br>";
            $bodyText .= "Primer apellido: <strong>{$r->pri_ape_}</strong><br>";
            $bodyText .= "Segundo apellido: <strong>{$r->seg_ape_}</strong><br>";
        }

        // 5) Enviar correo si el estado es 1
        if ((int)$request->estado === 1) {
            $sivigila = Sivigila::find($request->sivigilas_id);
            if ($sivigila && $user = User::find($sivigila->user_id)) {
                // ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ USAR SSL IMPLÃƒÆ’Ã‚ÂCITO EN PUERTO 465 ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬
                $transport = new EsmtpTransport(
                    env('MAIL_HOST', 'smtp.gmail.com'),
                    465,
                    'ssl'
                );
                $transport->setUsername(env('MAIL_USERNAME'));
                $transport->setPassword(env('MAIL_PASSWORD'));

                // Log de la configuraciÃƒÆ’Ã‚Â³n real:
                Log::info("SMTP configurado: host=" . env('MAIL_HOST') .
                          " port=465 encryption=ssl");

                $mailer = new Mailer($transport);

                $email = (new Email())
                    ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                    ->to(new Address($user->email))
                    ->subject('Recordatorio de control')
                    ->html(
                        'Hola, tu seguimiento acaba de ser actualizado por el administrador.' .
                        $bodyText .
                        ' Se solicita gestionarlo lo antes posible ingresando ' .
                        '<a href="' . url('login') . '">aquÃƒÆ’Ã‚Â­</a>.'
                    );

                try {
                    $mailer->send($email);
                    Log::info("Correo enviado exitosamente a {$user->email}");
                } catch (\Throwable $e) {
                    Log::warning('Error SMTP al enviar correo: ' . $e->getMessage());
                }
            } else {
                Log::warning("No se envÃƒÆ’Ã‚Â­a correo: paciente o usuario no encontrado para Seguimiento ID {$id}");
            }
        }

        // 6) Redirigir
        return redirect()->route('Seguimiento.index')
            ->with('success', 'Seguimiento actualizado correctamente.');
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Seguimiento  $seguimiento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seguimiento $seguimiento, $id)
    {
        Seguimiento::destroy($id);
        // Session::flash('error','El registro se ha agregado correctamente');
        return redirect('Seguimiento')->with('error', 'Empleado borrado con exito');
    }

    public function search(Request $request)
    {

        $query = $request->get('q');
 
    

        $incomeedit = Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin','sivigilas.Ips_at_inicial',
        'seguimientos.Fecha_ingreso_ingres','seguimientos.id')
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.estado',1)
        ->where('sivigilas.num_ide_', 'LIKE', '%'.$query.'%')
        ->get();

      


        return view('seguimiento.index', ['incomeedit' => $incomeedit ,'query' => $query]);
    }


    public function resporte()
    {   
        return Excel::download(new SeguimientoExport, 'seguimiento.xls');


    }


    public function alerta()
    {


                // $fecha_actual = Carbon::now();
                // $fechas_anteriores = Carbon::now()->subDays(2);
                    
                // $incomeedit = Seguimiento::whereBetween('fecha_proximo_control', 
                // [$fechas_anteriores, $fecha_actual])
                // ->get();
                // $fecha = Carbon::now()->addDays(2);

            //    $incomeedit = Seguimiento::
            //    where('fecha_proximo_control', Carbon::now()->addDays(2)->format('Y-m-d'));
            
            $seguimientos = Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
            'sivigilas.pri_ape_','sivigilas.seg_ape_','sivigilas.id as idin','sivigilas.Ips_at_inicial',
            'seguimientos.id','seguimientos.fecha_proximo_control','seguimientos.estado as est',
            'seguimientos.user_id as usr')
            ->orderBy('seguimientos.created_at', 'desc')
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
            ->where('seguimientos.estado',1)
            ->get();
            // foreach ($seguimientos as $seguimiento) {
            //     if (Carbon::now() > $seguimiento->fecha_proximo_control) {
            //         DB::table('seguimientos')
            //         ->where('seguimientos.id',$seguimiento->id)
            //         ->update(['estado' => '0',]);
            //     }
            // }
            
            return view('seguimiento.alertas', compact('seguimientos'));
    }



public function reporte2()
{
    return Excel::download(
        new GeneralExportCsv,
        'general.csv',
        ExcelFormat::CSV,
        [
            'Content-Type' => 'text/csv',
        ]
    );
}




    public function detail ($id){

        $seguimientodetail = Seguimiento::find($id);
    $seguimientoshow = DB::table('sivigilas')
        
        ->Join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->select(DB::raw("sivigilas.num_ide_,sivigilas.pri_nom_,sivigilas.seg_nom_,
        sivigilas.pri_ape_,sivigilas.seg_ape_,seguimientos.motivo_reapuertura"))
        ->where('seguimientos.id',$id)
        ->first();

   
    return view('seguimiento.detail',["seguimientoshow"=>$seguimientoshow,"seguimientodetail"=>$seguimientodetail]);

}




// MÃƒÆ’Ã‚Â©todo para ver el PDF

// public function viewPDF($id)
//     {
//         $seguimiento = Seguimiento::findOrFail($id);

//         // 1) Valor bruto de la BD (puede traer "foo.pdf" o "pdf/foo.pdf" o con prefijo storage/)
//         $raw = $seguimiento->pdf;

//         // 2) Limpiar prefijos redundantes
//         $trimmed = preg_replace('#^(storage/|public/)#', '', $raw);
//         $trimmed = ltrim($trimmed, '/');

//         // 3) Si no hay carpeta, asumir 'pdf/'
//         if (! Str::contains($trimmed, '/')) {
//             $relative = 'pdf/' . $trimmed;
//         } else {
//             $relative = $trimmed;
//         }

//         // 4) Verificar que exista en el disk 'public'
//         if (! Storage::disk('public')->exists($relative)) {
//             abort(404, "PDF no encontrado en public/{$relative}");
//         }

//         // 5) Devolver el archivo
//         return response()->file(
//             Storage::disk('public')->path($relative)
//         );
//     }

public function detallePrestador($id, Request $request)
{
    $anio = (int) ($request->input('anio', now()->year));
    // Verifica si el ID estÃƒÆ’Ã‚Â¡ llegando al controlador
    \Log::info('ID recibido en el controlador: ' . $id);

    // Realiza la consulta para obtener los detalles del prestador
    $detalles = DB::table('DESNUTRICION.dbo.cargue412s as cgr')
    ->join('DESNUTRICION.dbo.users as usr', 'usr.id', '=', 'cgr.user_id')
    ->leftJoin('DESNUTRICION.dbo.seguimiento_412s as seg', 'seg.cargue412_id', '=', 'cgr.id')
    ->where('cgr.user_id', $id)                             // solo registros asignados al usuario $id
    ->whereNull('seg.id')                                   // que no tengan NINGÃƒÆ’Ã…Â¡N seguimiento en seguimiento_412s
    ->whereYear('cgr.fecha_captacion', $anio)// cuya fecha_captacion sea del aÃƒÆ’Ã‚Â±o actual
    ->select([
        'cgr.primer_nombre',
        'cgr.segundo_nombre',
        'cgr.primer_apellido',
        'cgr.segundo_apellido',
        'cgr.tipo_identificacion',
        'cgr.numero_identificacion',
        'cgr.fecha_captacion',
        'usr.id           as asignado_id',
        'usr.name         as asignado_nombre',
        'usr.email        as asignado_email',
    ])
    ->get();



        

    // VerificaciÃƒÆ’Ã‚Â³n en logs
    if ($detalles->isEmpty()) {
        \Log::info('No se encontraron detalles para el ID: ' . $id);
    } else {
        \Log::info('Detalles obtenidos: ' . $detalles);
    }

    // Devuelve los detalles en formato JSON
    return response()->json($detalles);
}


    
public function graficaBarras()
{
    $data = $this->buildGraficaBarrasDashboardData(request());

    return view('seguimiento.grafica-barras', $data);
}

public function graficaTortaClasificacion()
{
    return redirect()->route('grafica.barras', request()->query());
}

public function graficaBarrasExportExcel(Request $request)
{
    $data = $this->buildGraficaBarrasDashboardData($request);
    $fileName = 'dashboard_seguimiento_' . $data['filters']['anio'] . '_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(new DashboardSeguimientoExport($data), $fileName);
}

public function graficaBarrasExportPdf(Request $request)
{
    $data = $this->buildGraficaBarrasDashboardData($request);

    $pdf = Pdf::loadView('seguimiento.grafica-barras-pdf', [
        'filters' => $data['filters'],
        'kpis' => $data['kpis'],
        'rows' => $data['rows'],
        'generatedAt' => now()->format('d/m/Y H:i:s'),
    ])->setPaper('a4', 'landscape');

    $fileName = 'dashboard_seguimiento_' . $data['filters']['anio'] . '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->download($fileName);
}

private function buildGraficaBarrasDashboardData(Request $request): array
{
    $currentYear = (int) now()->year;

    $validated = $request->validate([
        'anio' => "nullable|integer|min:2020|max:{$currentYear}",
        'prestador_id' => 'nullable|integer|exists:users,id',
        'estado' => 'nullable|in:todos,con_sin,solo_sin,solo_con,alto_riesgo',
        'orden' => 'nullable|in:sin_desc,cobertura_asc,cobertura_desc,nombre_asc,nombre_desc',
        'q' => 'nullable|string|max:120',
    ]);

    $filters = [
        'anio' => (int)($validated['anio'] ?? $currentYear),
        'prestador_id' => isset($validated['prestador_id']) ? (int)$validated['prestador_id'] : null,
        'evento' => '113_y_412',
        'estado' => $validated['estado'] ?? 'con_sin',
        'orden' => $validated['orden'] ?? 'sin_desc',
        'q' => trim((string)($validated['q'] ?? '')),
    ];

    $prestadores = DB::table('users as u')
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('sivigilas as s')
                ->whereColumn('s.user_id', 'u.id');
        })
        ->orWhereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('cargue412s as c')
                ->whereColumn('c.user_id', 'u.id');
        })
        ->orderBy('u.name')
        ->get(['u.id', 'u.name']);

    $availableYears = DB::table('sivigilas')
        ->selectRaw('YEAR(fec_not) as anio')
        ->whereNotNull('fec_not')
        ->union(
            DB::table('cargue412s')
                ->selectRaw('YEAR(fecha_captacion) as anio')
                ->whereNotNull('fecha_captacion')
        )
        ->get()
        ->pluck('anio')
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();

    if ($availableYears->isEmpty()) {
        $availableYears = collect([$currentYear]);
    }

    $q = $filters['q'];

    $results113 = DB::table('users as a')
        ->join('sivigilas as b', 'a.id', '=', 'b.user_id')
        ->leftJoin('seguimientos as c', 'b.id', '=', 'c.sivigilas_id')
        ->whereYear('b.fec_not', $filters['anio'])
        ->when($filters['prestador_id'], fn($query) => $query->where('a.id', $filters['prestador_id']))
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('a.name', 'like', "%{$q}%")
                    ->orWhere('b.num_ide_', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(COALESCE(b.pri_nom_, ''), ' ', COALESCE(b.seg_nom_, ''), ' ', COALESCE(b.pri_ape_, ''), ' ', COALESCE(b.seg_ape_, '')) like ?", ["%{$q}%"]);
            });
        })
        ->groupBy('a.id', 'a.name')
        ->select([
            'a.id',
            'a.name',
            DB::raw("'113' as evento"),
            DB::raw('COUNT(DISTINCT b.id) as cant_casos_asignados'),
            DB::raw('COUNT(DISTINCT CASE WHEN c.id IS NULL THEN b.id END) as total_sin_seguimientos'),
        ])
        ->get()
        ->map(function ($row) {
            $asignados = (int)$row->cant_casos_asignados;
            $sin = (int)$row->total_sin_seguimientos;
            $con = max(0, $asignados - $sin);
            $cobertura = $asignados > 0 ? round(($con / $asignados) * 100, 2) : 0;

            $row->casos_con_seguimiento = $con;
            $row->cobertura_pct = $cobertura;
            $row->nivel_riesgo = $sin === 0 ? 'Bajo' : ($cobertura < 70 ? 'Alto' : 'Medio');
            return $row;
        });

    $results412 = DB::table('users as a')
        ->join('cargue412s as b', 'a.id', '=', 'b.user_id')
        ->leftJoin('seguimiento_412s as c', 'b.id', '=', 'c.cargue412_id')
        ->whereYear('b.fecha_captacion', $filters['anio'])
        ->when($filters['prestador_id'], fn($query) => $query->where('a.id', $filters['prestador_id']))
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('a.name', 'like', "%{$q}%")
                    ->orWhere('b.numero_identificacion', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(COALESCE(b.primer_nombre, ''), ' ', COALESCE(b.segundo_nombre, ''), ' ', COALESCE(b.primer_apellido, ''), ' ', COALESCE(b.segundo_apellido, '')) like ?", ["%{$q}%"]);
            });
        })
        ->groupBy('a.id', 'a.name')
        ->select([
            'a.id',
            'a.name',
            DB::raw("'412' as evento"),
            DB::raw('COUNT(DISTINCT b.id) as cant_casos_asignados'),
            DB::raw('COUNT(DISTINCT CASE WHEN c.id IS NULL THEN b.id END) as total_sin_seguimientos'),
        ])
        ->get()
        ->map(function ($row) {
            $asignados = (int)$row->cant_casos_asignados;
            $sin = (int)$row->total_sin_seguimientos;
            $con = max(0, $asignados - $sin);
            $cobertura = $asignados > 0 ? round(($con / $asignados) * 100, 2) : 0;

            $row->casos_con_seguimiento = $con;
            $row->cobertura_pct = $cobertura;
            $row->nivel_riesgo = $sin === 0 ? 'Bajo' : ($cobertura < 70 ? 'Alto' : 'Medio');
            return $row;
        });

    $rows = $results113->concat($results412);

    if ($filters['estado'] === 'solo_sin') {
        $rows = $rows->where('total_sin_seguimientos', '>', 0)->values();
    } elseif ($filters['estado'] === 'solo_con') {
        $rows = $rows->where('total_sin_seguimientos', 0)->values();
    } elseif ($filters['estado'] === 'alto_riesgo') {
        $rows = $rows->where('cobertura_pct', '<', 70)->values();
    }

    $rows = match ($filters['orden']) {
        'cobertura_asc' => $rows->sortBy('cobertura_pct')->values(),
        'cobertura_desc' => $rows->sortByDesc('cobertura_pct')->values(),
        'nombre_asc' => $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
        'nombre_desc' => $rows->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
        default => $rows->sortByDesc('total_sin_seguimientos')->values(),
    };

    $totalAsignados = (int)$rows->sum('cant_casos_asignados');
    $totalSin = (int)$rows->sum('total_sin_seguimientos');
    $totalCon = (int)$rows->sum('casos_con_seguimiento');
    $totalPrestadores = $rows->count();
    $alertas = $rows->where('total_sin_seguimientos', '>', 0)->count();
    $coberturaGlobal = $totalAsignados > 0 ? round(($totalCon / $totalAsignados) * 100, 2) : 0;

    $kpis = [
        'total_prestadores' => $totalPrestadores,
        'total_asignados' => $totalAsignados,
        'total_con_seguimiento' => $totalCon,
        'total_sin_seguimiento' => $totalSin,
        'prestadores_con_alerta' => $alertas,
        'cobertura_global_pct' => $coberturaGlobal,
    ];

    $eventoChart = $rows
        ->groupBy('evento')
        ->map(function ($group) {
            return [
                'sin' => (int)$group->sum('total_sin_seguimientos'),
                'con' => (int)$group->sum('casos_con_seguimiento'),
            ];
        });

    $topSin = $rows
        ->sortByDesc('total_sin_seguimientos')
        ->take(10)
        ->values();

    $seguimientosBase = DB::table('seguimientos as s')
        ->join('sivigilas as sv', 'sv.id', '=', 's.sivigilas_id')
        ->whereYear('s.created_at', $filters['anio'])
        ->when($filters['prestador_id'], fn($query) => $query->where('sv.user_id', $filters['prestador_id']));

    $estadoSeguimientos = (clone $seguimientosBase)
        ->select('s.estado', DB::raw('COUNT(*) as total'))
        ->groupBy('s.estado')
        ->get();

    $clasificaciones = (clone $seguimientosBase)
        ->select('s.clasificacion', DB::raw('COUNT(*) as total'))
        ->groupBy('s.clasificacion')
        ->get();

    $estadosLabels = $estadoSeguimientos
        ->map(fn($e) => (string)$e->estado === '1' ? 'Abierto' : 'Cerrado')
        ->values();
    $estadosData = $estadoSeguimientos->pluck('total')->map(fn($v) => (int)$v)->values();

    $clasifLabels = $clasificaciones
        ->map(fn($c) => $c->clasificacion ?: 'Sin clasificacion')
        ->values();
    $clasifData = $clasificaciones->pluck('total')->map(fn($v) => (int)$v)->values();

    return [
        'filters' => $filters,
        'availableYears' => $availableYears,
        'prestadores' => $prestadores,
        'kpis' => $kpis,
        'rows' => $rows,
        'charts' => [
            'seguimiento_vs_sin' => [
                'labels' => ['Con seguimiento', 'Sin seguimiento'],
                'data' => [$totalCon, $totalSin],
            ],
            'evento' => [
                'labels' => $eventoChart->keys()->map(fn($k) => 'Evento ' . $k)->values(),
                'sin' => $eventoChart->pluck('sin')->values(),
                'con' => $eventoChart->pluck('con')->values(),
            ],
            'top_sin' => [
                'labels' => $topSin->map(fn($r) => mb_strimwidth($r->name, 0, 24, '...') . " ({$r->evento})")->values(),
                'data' => $topSin->pluck('total_sin_seguimientos')->values(),
            ],
            'estado_seguimientos' => [
                'labels' => $estadosLabels,
                'data' => $estadosData,
            ],
            'clasificaciones' => [
                'labels' => $clasifLabels,
                'data' => $clasifData,
            ],
        ],
    ];
}

public function obtenerDatosEnJson()
{
    $results = DB::table('DESNUTRICION.dbo.users AS a')
        ->select(
            'a.id',
            'a.name',
            DB::raw('COUNT(c.sivigilas_id) AS total_Seguimientos'),
            DB::raw('COUNT(b.id) AS cant_casos_asignados')
        )
        ->join('DESNUTRICION.dbo.sivigilas AS b', 'a.id', '=', 'b.user_id')
        ->leftJoin('DESNUTRICION.dbo.seguimientos AS c', 'b.id', '=', 'c.sivigilas_id')
        ->whereRaw('YEAR(b.created_at) > ?', [2023])
        ->groupBy('a.id', 'a.name')
        ->orderBy(DB::raw('COUNT(b.id)'), 'desc')
        ->get();

    // Devolvemos los resultados como JSON
    return response()->json($results);
}


// public function indicador()
// {




//     return view('seguimiento.grafica-barras', compact('results'));


// }





    public function getData(Request $request)
    {
        // Definimos la consulta principal a la base de datos
        $query = DB::table('DESNUTRICION.dbo.users AS a')
            ->select(
                'a.id', // Seleccionamos el ID del usuario
                'a.name', // Seleccionamos el nombre del usuario
                DB::raw('COUNT(c.sivigilas_id) AS total_Seguimientos'), // Contamos el nÃƒÆ’Ã‚Âºmero total de seguimientos
                DB::raw('COUNT(b.user_id) AS cant_casos_asignados') // Contamos el nÃƒÆ’Ã‚Âºmero total de casos asignados
            )
            // Unimos la tabla de sivigilas con la tabla de usuarios en base al ID del usuario
            ->join('DESNUTRICION.dbo.sivigilas AS b', 'a.id', '=', 'b.user_id')
            // Unimos la tabla de seguimientos con la tabla de sivigilas en base al ID de sivigilas
            ->leftJoin('DESNUTRICION.dbo.seguimientos AS c', 'b.id', '=', 'c.sivigilas_id')
            // Aplicamos un filtro para seleccionar solo los registros creados en el aÃƒÆ’Ã‚Â±o 2023 o posterior
            ->whereRaw('YEAR(b.created_at) > ?', [2023])
            // Agrupamos los resultados por ID y nombre del usuario
            ->groupBy('a.id', 'a.name');

        // Usamos DataTables para manejar la consulta
        return DataTables::of($query)
            // Aplicamos un filtro para la bÃƒÆ’Ã‚Âºsqueda
            ->filter(function ($query) use ($request) {
                // Verificamos si hay un valor de bÃƒÆ’Ã‚Âºsqueda
                if ($request->has('search.value')) {
                    $search = $request->input('search.value'); // Obtenemos el valor de bÃƒÆ’Ã‚Âºsqueda
                    // Aplicamos la bÃƒÆ’Ã‚Âºsqueda en los campos id y name de la tabla users
                    $query->where(function($query) use ($search) {
                        $query->where('a.name', 'like', "%{$search}%") // BÃƒÆ’Ã‚Âºsqueda parcial por nombre
                              ->orWhere('a.id', 'like', "%{$search}%"); // BÃƒÆ’Ã‚Âºsqueda parcial por ID
                    });
                }
            })
            // Retornamos el resultado en formato JSON para que DataTables pueda procesarlo
            ->make(true);
    }



//PARA UNA CONSULTA NORMAL 
// $query = DB::table('DESNUTRICION.dbo.users AS a')
// ->select(
//     'a.id',
//     'a.name',
//     DB::raw('COUNT(c.sivigilas_id) AS total_Seguimientos'),
//     DB::raw('COUNT(b.id) AS cant_casos_asignados')
// )
// ->join('DESNUTRICION.dbo.sivigilas AS b', 'a.id', '=', 'b.user_id')
// ->leftJoin('DESNUTRICION.dbo.seguimientos AS c', 'b.id', '=', 'c.sivigilas_id')
// ->whereRaw('YEAR(b.created_at) > ?', [2023])
// ->groupBy('a.id', 'a.name')
// ;

// return DataTables::of($query)
// ->make(true);
// }

public function detallePrestador_113($id, Request $request)
{
    $anio = (int) ($request->input('anio', now()->year));
    // Verifica si el ID estÃƒÆ’Ã‚Â¡ llegando al controlador
    \Log::info('ID recibido en el controlador: ' . $id);

    // Realiza la consulta para obtener los detalles del prestador segÃƒÆ’Ã‚Âºn la consulta proporcionada
    $detalles = DB::table('DESNUTRICION.dbo.sivigilas as siv')
    ->join('DESNUTRICION.dbo.users as usr', 'usr.id', '=', 'siv.user_id')
    ->leftJoin('DESNUTRICION.dbo.seguimientos as seg', 'seg.sivigilas_id', '=', 'siv.id')
    ->where('siv.user_id', $id)                            // solo pacientes asignados al prestador $id
    ->whereNull('seg.id')                                 // que no tengan NINGÃƒÆ’Ã…Â¡N seguimiento
    ->whereYear('siv.fec_not', $anio)     // cuya fec_not es del aÃƒÆ’Ã‚Â±o actual
    ->select([
        'siv.tip_ide_',
        'siv.num_ide_',
        'siv.pri_nom_',
        'siv.seg_nom_',
        'siv.pri_ape_',
        'siv.seg_ape_',
        'siv.semana',
        'usr.id        as asignado_id',
        'usr.name      as asignado_nombre',
        'usr.email     as asignado_email',
    ])
    ->get();


    // VerificaciÃƒÆ’Ã‚Â³n en logs
    if ($detalles->isEmpty()) {
        \Log::info('No se encontraron detalles para el ID: ' . $id);
    } else {
        \Log::info('Detalles obtenidos: ' . $detalles);
    }

    // Devuelve los detalles en formato JSON
    return response()->json($detalles);
}


public function buscarSeguimiento(Request $request)
{
    $search = $request->input('search');

    if ($search) {
        $results = DB::table('sivigilas')
            ->select(
                'sivigilas.num_ide_ AS numero_identificacion',
                'sivigilas.pri_nom_ AS primer_nombre',
                'sivigilas.seg_nom_ AS segundo_nombre',
                'sivigilas.pri_ape_ AS primer_apellido',
                'sivigilas.seg_ape_ AS segundo_apellido'
            )
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            ->where(function($q) use ($search) {
                $q->where('sivigilas.num_ide_', 'LIKE', "%{$search}%")
                  ->orWhere('sivigilas.pri_nom_', 'LIKE', "%{$search}%")
                  ->orWhere('sivigilas.seg_nom_', 'LIKE', "%{$search}%")
                  ->orWhere('sivigilas.pri_ape_', 'LIKE', "%{$search}%")
                  ->orWhere('sivigilas.seg_ape_', 'LIKE', "%{$search}%");
            })
            ->limit(8)
            ->get();

        return response()->json($results);
    }

    return response()->json([]);
}


    

}

    
        

