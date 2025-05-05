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
use App\Exports\GeneralExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Models\User;
use DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
    public function index(Request $request)
    {
        // 1) Si es una petición AJAX (o tiene 'search'), devolvemos JSON con la tabla filtrada.
        if ($request->ajax()) {
            // Toma la variable 'search' (o 'busqueda') de la petición
            $search = $request->input('search') ?? $request->input('busqueda');
    
            // Dependiendo del usertype, usas la consulta que corresponda.
            if (Auth::user()->usertype == 2) {
                // Para usertype == 2:
                $query = Sivigila::select(
                        'sivigilas.num_ide_',
                        'sivigilas.pri_nom_',
                        'sivigilas.seg_nom_',
                        'sivigilas.pri_ape_',
                        'sivigilas.seg_ape_',
                        'seguimientos.id as id',
                        'sivigilas.Ips_at_inicial',
                        'seguimientos.fecha_consulta',
                        'seguimientos.fecha_proximo_control',
                        'seguimientos.estado',
                        'seguimientos.motivo_reapuertura',
                        'sivigilas.semana',
                        'sivigilas.created_at as creado'
                    )
                    ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
                    ->where('seguimientos.user_id', Auth::user()->id)
                    ->whereYear('seguimientos.created_at', '>', 2023)
                    ->orderBy('seguimientos.created_at', 'desc');
    
                // Si llega algo en search, filtra por num_ide_
                if (!empty($search)) {
                    $query->where('sivigilas.num_ide_', 'LIKE', "%{$search}%");
                }
    
                // Retornamos todos los resultados filtrados (sin paginar)
                $incomeedit = $query->get();
            } else {
                // Para usertype != 2:
                $query = Seguimiento::select(
                        's.num_ide_',
                        's.pri_nom_',
                        's.seg_nom_',
                        's.pri_ape_',
                        's.seg_ape_',
                        'users.name',
                        'seguimientos.id as id',
                        'seguimientos.fecha_consulta',
                        'seguimientos.fecha_proximo_control',
                        'seguimientos.estado',
                        'seguimientos.motivo_reapuertura',
                        's.semana',
                        's.created_at as creado'
                    )
                    ->join('sivigilas as s', 's.id', '=', 'seguimientos.sivigilas_id')
                    ->join('users', 'users.id', '=', 's.user_id')
                    ->whereYear('seguimientos.created_at', '>', 2023)
                    ->orderBy('seguimientos.created_at', 'desc');
    
                if (!empty($search)) {
                    $query->where('s.num_ide_', 'LIKE', "%{$search}%");
                }
    
                $incomeedit = $query->get();
            }
    
            // Devolvemos un JSON con la misma clave que tu JS espera ("incomeedit")
            return response()->json([
                'incomeedit' => $incomeedit
            ]);
        }
    
        // 2) Si NO es AJAX, seguimos con la lógica normal que retorna la vista.
    
        $user_id = Auth::User()->usertype;
        $user_id1 = Auth::User()->id == '2';
    
        if (Auth::User()->usertype == 2) {
            $incomeedit = Sivigila::select(
                    'sivigilas.num_ide_',
                    'sivigilas.pri_nom_',
                    'sivigilas.seg_nom_',
                    'sivigilas.pri_ape_',
                    'sivigilas.seg_ape_',
                    'seguimientos.id as idin',
                    'sivigilas.Ips_at_inicial',
                    'seguimientos.fecha_consulta',
                    'seguimientos.id',
                    'seguimientos.fecha_proximo_control',
                    'seguimientos.estado',
                    'seguimientos.id',
                    'seguimientos.motivo_reapuertura',
                    'sivigilas.semana',
                    'sivigilas.created_at as creado'
                )
                ->orderBy('seguimientos.created_at', 'desc')
                ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
                ->where('seguimientos.user_id', Auth::user()->id)
                ->whereYear('seguimientos.created_at', '>', 2023)
                ->paginate(10);
        } else {
            $incomeedit = Seguimiento::select(
                    's.num_ide_',
                    's.pri_nom_',
                    's.seg_nom_',
                    's.pri_ape_',
                    's.seg_ape_',
                    'seguimientos.id as idin',
                    'users.name',
                    'seguimientos.fecha_consulta',
                    'seguimientos.fecha_proximo_control',
                    'seguimientos.estado',
                    'seguimientos.id',
                    'seguimientos.motivo_reapuertura',
                    's.semana',
                    's.created_at as creado'
                )
                ->orderBy('seguimientos.created_at', 'desc')
                ->whereYear('seguimientos.created_at', '>', 2023)
                ->join('sivigilas as s', 's.id', '=', 'seguimientos.sivigilas_id')
                ->join('users', 'users.id', '=', 's.user_id')
                ->paginate(10);
        }
    
        if (Auth::User()->usertype == 2) {
            $conteo = Seguimiento::where('estado', 1)
                        ->where('user_id', Auth::user()->id)
                        ->count('id');
        } else {
            $conteo = Seguimiento::where('estado', 1)
                        ->whereYear('seguimientos.created_at', '>', 2023)
                        ->count('id');
        }
    
        $seguimientos = Seguimiento::all()->where('estado', 1);
    
        $otro =  Sivigila::select(
                    'sivigilas.num_ide_',
                    'sivigilas.pri_nom_',
                    'sivigilas.seg_nom_',
                    'sivigilas.pri_ape_',
                    'sivigilas.seg_ape_',
                    'sivigilas.id as idin',
                    'sivigilas.Ips_at_inicial',
                    'seguimientos.id',
                    'seguimientos.fecha_proximo_control',
                    'seguimientos.estado as est',
                    'seguimientos.user_id as usr'
                )
                ->orderBy('seguimientos.created_at', 'desc')
                ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
                ->where('seguimientos.estado', 1)
                ->get();
    
        return view('seguimiento.index', compact('incomeedit','seguimientos','conteo','otro'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $incomeedit = DB::table('sivigilas')
        ->select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','sivigilas.id as idin')
        // ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->where('sivigilas.estado', '=', 1)
        ->where('user_id', Auth::user()->id)
        ->whereYear('sivigilas.created_at', '>', 2023) 
        ->get();

        $income12 =  DB::connection('sqlsrv_1')->table('refIps')->select('descrip')
        ->where('refIps.codigoDepartamento', 44)
        ->get();

        return view('seguimiento.create',compact('incomeedit','income12'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 1) Validación
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
            'fecha_proximo_control'                      => 'nullable|date|after_or_equal:today',
            'sivigilas_id'                               => 'required|exists:sivigilas,id',
            'Esquemq_complrto_pai_edad'                  => 'required|string',
            'Atecion_primocion_y_mantenimiento_res3280_2018' => 'required|string',
            'pdf'                                        => 'nullable|mimes:pdf|max:5048',
            'estado'                                     => 'required|in:0,1',
        ],[
            'required' => 'El campo :attribute es obligatorio.',
            'numeric'  => 'El campo :attribute debe ser numérico.',
            'date'     => 'El campo :attribute debe ser una fecha válida.',
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

        // 3) Crear todo en transacción
        DB::transaction(function() use ($request, &$seguimiento) {
            $data = $request->only([
                'fecha_consulta','peso_kilos','talla_cm','puntajez',
                'clasificacion','requerimiento_energia_ftlc','fecha_entrega_ftlc',
                'motivo_reapuertura','observaciones','est_act_menor',
                'tratamiento_f75','fecha_recibio_tratf75','fecha_proximo_control',
                'sivigilas_id','Esquemq_complrto_pai_edad',
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
                    . "Próximo control: <strong>{$seguimiento->fecha_proximo_control}</strong><br>";

                $html = "Hola, se ha registrado un seguimiento para el paciente:<br>"
                      . $bodyText
                      . "Por favor gestiona lo antes posible en "
                      . "<a href=\"".url('login')."\">la aplicación</a>.";

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
                Log::warning("Usuario ID {$sivigila->user_id} sin email válido");
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
        // 1) Validación
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
                // ── USAR SSL IMPLÍCITO EN PUERTO 465 ──
                $transport = new EsmtpTransport(
                    env('MAIL_HOST', 'smtp.gmail.com'),
                    465,
                    'ssl'
                );
                $transport->setUsername(env('MAIL_USERNAME'));
                $transport->setPassword(env('MAIL_PASSWORD'));

                // Log de la configuración real:
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
                        '<a href="' . url('login') . '">aquí</a>.'
                    );

                try {
                    $mailer->send($email);
                    Log::info("Correo enviado exitosamente a {$user->email}");
                } catch (\Throwable $e) {
                    Log::warning('Error SMTP al enviar correo: ' . $e->getMessage());
                }
            } else {
                Log::warning("No se envía correo: paciente o usuario no encontrado para Seguimiento ID {$id}");
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
        return Excel::download(new GeneralExport, 'general.xls');


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




// Método para ver el PDF
public function viewPDF($id)
{
    $seguimiento = Seguimiento::findOrFail($id);

    // Si en DB guardas el path completo relativo al disco "public", p.ej. "pdf/archivo.pdf",
    // úsalo directamente:
    $relative = $seguimiento->pdf; 

    // Comprueba que exista en el disco "public"
    if (! Storage::disk('public')->exists($relative) ) {
        abort(404, "El archivo PDF no fue encontrado.");
    }

    // Devuelve el fichero
    return response()->file(
        Storage::disk('public')->path($relative)
    );
}
public function detallePrestador($id)
{
    // Verifica si el ID está llegando al controlador
    \Log::info('ID recibido en el controlador: ' . $id);

    // Realiza la consulta para obtener los detalles del prestador
$detalles = DB::table('DESNUTRICION.dbo.cargue412s')
    ->join('DESNUTRICION.dbo.users', 'cargue412s.user_id', '=', 'users.id')
    ->leftJoin('DESNUTRICION.dbo.seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
    ->where('users.id', $id) // Filtrar por el ID del usuario (dinámico)
    ->whereNull('seguimiento_412s.cargue412_id') // Filtro para registros sin seguimiento
    ->whereRaw('YEAR(cargue412s.created_at) > ?', [2023]) // Filtro para los registros creados después de 2023
    ->select(
        'cargue412s.primer_nombre',
        'cargue412s.segundo_nombre',
        'cargue412s.primer_apellido',
        'cargue412s.segundo_apellido',
        'cargue412s.tipo_identificacion',
        'cargue412s.numero_identificacion',
        'cargue412s.fecha_captacion',
    )
    ->get();


        

    // Verificación en logs
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


    
    // Obtener los datos de la base de datos para la gráfica de barras
    $estados = DB::table('seguimientos')
        ->select('estado', DB::raw('count(*) as total'))
        ->groupBy('estado')
        ->whereYear('seguimientos.created_at', '>', 2023)
        ->get();

    // Preparar los datos para la gráfica de barras
    $estados_labels = [];
    $estados_data = [];

    foreach ($estados as $estado) {
        $estados_labels[] = $estado->estado == 1 ? 'Abierto' : 'Cerrado';
        $estados_data[] = $estado->total;
    }

    // Obtener los datos de la base de datos para la gráfica de torta
    $clasificaciones = DB::table('seguimientos')
        ->select('clasificacion', DB::raw('count(*) as total'))
        ->groupBy('clasificacion')
        ->whereYear('seguimientos.created_at', '>', 2023)
        ->get();

    // Preparar los datos para la gráfica de torta
    $clasificaciones_labels = [];
    $clasificaciones_data = [];

    foreach ($clasificaciones as $clasificacion) {
        $clasificaciones_labels[] = $clasificacion->clasificacion;
        $clasificaciones_data[] = $clasificacion->total;
    }
    $results = DB::table('DESNUTRICION.dbo.users AS a')
    ->select(
        'a.id',
        'a.name',
        DB::raw("COUNT(CASE WHEN c.sivigilas_id IS NULL THEN 1 ELSE NULL END) AS total_Sin_Seguimientos"),
        DB::raw('COUNT(DISTINCT b.id) AS cant_casos_asignados')
    )
    ->join('DESNUTRICION.dbo.sivigilas AS b', 'a.id', '=', 'b.user_id')
    ->leftJoin('DESNUTRICION.dbo.seguimientos AS c', 'b.id', '=', 'c.sivigilas_id')
    ->whereRaw('YEAR(b.created_at) > ?', [2023])
    ->groupBy('a.id', 'a.name')
    ->orderBy(DB::raw('COUNT(b.id)'), 'desc')
    ->get();

    $results_412 = DB::table('DESNUTRICION.dbo.users AS a')
    ->select(
        'a.id',
        'a.name',
        DB::raw("COUNT(CASE WHEN c.cargue412_id IS NULL THEN 1 ELSE NULL END) AS total_Sin_Seguimientos"),
        DB::raw('COUNT(DISTINCT b.id) AS cant_casos_asignados')
    )
    ->join('DESNUTRICION.dbo.cargue412s AS b', 'a.id', '=', 'b.user_id')
    ->leftJoin('DESNUTRICION.dbo.seguimiento_412s AS c', 'b.id', '=', 'c.cargue412_id')
    ->whereRaw('YEAR(b.created_at) > ?', [2023])
    ->groupBy('a.id', 'a.name')
    ->orderBy(DB::raw('COUNT(b.id)'), 'desc')
    ->get();




    return view('seguimiento.grafica-barras', compact('estados_labels', 'estados_data', 'clasificaciones_labels', 'clasificaciones_data','results','results_412'));
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
                DB::raw('COUNT(c.sivigilas_id) AS total_Seguimientos'), // Contamos el número total de seguimientos
                DB::raw('COUNT(b.user_id) AS cant_casos_asignados') // Contamos el número total de casos asignados
            )
            // Unimos la tabla de sivigilas con la tabla de usuarios en base al ID del usuario
            ->join('DESNUTRICION.dbo.sivigilas AS b', 'a.id', '=', 'b.user_id')
            // Unimos la tabla de seguimientos con la tabla de sivigilas en base al ID de sivigilas
            ->leftJoin('DESNUTRICION.dbo.seguimientos AS c', 'b.id', '=', 'c.sivigilas_id')
            // Aplicamos un filtro para seleccionar solo los registros creados en el año 2023 o posterior
            ->whereRaw('YEAR(b.created_at) > ?', [2023])
            // Agrupamos los resultados por ID y nombre del usuario
            ->groupBy('a.id', 'a.name');

        // Usamos DataTables para manejar la consulta
        return DataTables::of($query)
            // Aplicamos un filtro para la búsqueda
            ->filter(function ($query) use ($request) {
                // Verificamos si hay un valor de búsqueda
                if ($request->has('search.value')) {
                    $search = $request->input('search.value'); // Obtenemos el valor de búsqueda
                    // Aplicamos la búsqueda en los campos id y name de la tabla users
                    $query->where(function($query) use ($search) {
                        $query->where('a.name', 'like', "%{$search}%") // Búsqueda parcial por nombre
                              ->orWhere('a.id', 'like', "%{$search}%"); // Búsqueda parcial por ID
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

public function detallePrestador_113($id)
{
    // Verifica si el ID está llegando al controlador
    \Log::info('ID recibido en el controlador: ' . $id);

    // Realiza la consulta para obtener los detalles del prestador según la consulta proporcionada
    $detalles = DB::table('DESNUTRICION.dbo.sivigilas AS siv')
        ->join('DESNUTRICION.dbo.users AS usr', 'siv.user_id', '=', 'usr.id') // Unión con la tabla de usuarios
        ->leftJoin('DESNUTRICION.dbo.seguimientos AS seg', 'siv.id', '=', 'seg.sivigilas_id') // Unión con la tabla de seguimientos
        ->where('usr.id', $id) // Filtro por el ID del usuario
        ->whereNull('seg.sivigilas_id') // Filtro para los registros sin seguimientos
        ->whereRaw('YEAR(siv.created_at) > ?', [2023]) // Filtro para los registros creados después del año 2023
        ->select(
            'siv.tip_ide_',
            'siv.num_ide_',
            'siv.pri_nom_',
            'siv.seg_nom_',
            'siv.pri_ape_',
            'siv.seg_ape_',
            'siv.semana',
            //'usr.name' // Añadido el nombre del usuario
        )
        ->get();

    // Verificación en logs
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

    
        
