<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Sivigila;
use App\Models\Seguimiento;
use Illuminate\Support\Facades\DB;
use Session;
use App\Models\Seguimiento_412;
use App\Models\Cargue412;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Seguimiento412Export;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;



class Seguimiento412Controller extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $user = Auth::user();

     // Contador de casos abiertos
     $conteo = DB::table('seguimiento_412s')
     ->when($user->usertype == 2, fn($q) => $q->where('user_id', $user->id))
     ->where('estado', 1)
     ->whereYear('created_at', '>', 2023)
     ->count();

 // Contador de próximos controles (fecha_proximo_control futura)
 $otro = DB::table('seguimiento_412s')
     ->when($user->usertype == 2, fn($q) => $q->where('user_id', $user->id))
     ->where('estado', 1)
     ->whereNotNull('fecha_proximo_control')
     ->whereDate('fecha_proximo_control', '>', now())
     ->get();

 // Contador de cerrados
 $cerrados = DB::table('seguimiento_412s')
     ->when($user->usertype == 2, fn($q) => $q->where('user_id', $user->id))
     ->where('estado', 0)
     ->whereYear('created_at', '>', 2023)
     ->count();

    $otro = DB::table('cargue412s')
        ->join('seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
        ->select(
            'cargue412s.numero_identificacion',
            'cargue412s.primer_nombre',
            'cargue412s.segundo_nombre',
            'cargue412s.primer_apellido',
            'cargue412s.segundo_apellido',
            'cargue412s.id as idin',
            'seguimiento_412s.id',
            'seguimiento_412s.estado as est',
            'seguimiento_412s.user_id as usr'
        )
        ->where('seguimiento_412s.estado', 1)
        ->orderByDesc('seguimiento_412s.created_at')
        ->get();

        return view('seguimiento_412.index', compact('conteo', 'otro', 'cerrados'));
    }

// PARA EL DATATABLE
public function data(Request $request)
{
    $user = Auth::user();

    $query = DB::table('vw_seguimientos_412')
        ->select([
            'seguimiento_id',
            'estado',
            'seguimiento_created_at',
            'fecha_proximo_control',
            'seguimiento_user_id',
            'motivo_reapuertura',
            'numero_identificacion',
            'nombre_completo',
            'nombre_coperante'
        ]);

    // FILTRO POR USUARIO
    if ($user->usertype == 2) {
        $query->where('seguimiento_user_id', $user->id);
    }

    // FILTRO POR ESTADO (1 = abierto, 0 = cerrado)
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    // FILTRO POR PRÓXIMO CONTROL
    if ($request->filled('proximo')) {
        $query->whereNotNull('fecha_proximo_control')
              ->whereDate('fecha_proximo_control', '>', now());
    }

    // ✅ FILTRO POR AÑO DE seguimiento_created_at
    if ($request->filled('anio')) {
        $query->whereYear('seguimiento_created_at', $request->anio);
    }

    return DataTables::of($query)
        ->setRowId('seguimiento_id')
        ->editColumn('seguimiento_created_at', fn($r) =>
            \Carbon\Carbon::parse($r->seguimiento_created_at)->format('Y-m-d')
        )
        ->editColumn('fecha_proximo_control', function ($r) {
            return $r->fecha_proximo_control
                ? \Carbon\Carbon::parse($r->fecha_proximo_control)->format('Y-m-d')
                : ($r->seguimiento_created_at
                    ? \Carbon\Carbon::parse($r->seguimiento_created_at)->format('Y-m-d')
                    : 'Finalizado');
        })
        ->editColumn('estado', function ($r) {
            return $r->estado == 1
                ? '<span class="badge badge-success">Abierto</span>'
                : '<span class="badge badge-secondary">Cerrado</span>';
        })
        ->addColumn('acciones', function ($r) use ($user) {
            $dropdown = '<div class="dropdown">
                <button class="btn btn-sm btn-acciones dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cogs mr-1"></i> Acciones
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">';
        
            $dropdown .= '<a class="dropdown-item" href="' . route('new412_seguimiento.edit', $r->seguimiento_id) . '">
                            <i class="fas fa-edit text-success mr-2"></i>Editar</a>';
        
            if ($r->motivo_reapuertura) {
                $dropdown .= '<a class="dropdown-item" href="' . route('detalleseguimiento', $r->seguimiento_id) . '">
                                <i class="far fa-eye text-primary mr-2"></i>Ver Detalles</a>';
            }
        
            $dropdown .= '<a class="dropdown-item" href="' . route('seguimiento.view-pdf_412', $r->seguimiento_id) . '" target="_blank">
                            <i class="far fa-file-pdf text-danger mr-2"></i>Ver PDF</a>';
        
            if ($user->usertype != 3) {
                $dropdown .= '<form method="POST" action="' . route('new412_seguimiento.destroy', $r->seguimiento_id) . '" onsubmit="return confirm(\'¿Seguro que deseas eliminar?\')" style="display:inline;">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="fas fa-trash-alt mr-2"></i>Eliminar
                                </button>
                              </form>';
            }
        
            $dropdown .= '</div></div>';
            return $dropdown;
        })
        
        ->rawColumns(['estado', 'acciones'])
        ->toJson();
}





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $sivigilas2030 = Cargue412::
        where('cargue412s.estado', '=', 1)
        ->where('user_id', Auth::user()->id)->get();

        
        // $yearActual = Carbon::now()->year;
        $incomeedit = DB::table('cargue412s')
        ->select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
        'cargue412s.primer_apellido','cargue412s.segundo_apellido','cargue412s.id as idin','cargue412s.created_at')
        // ->join('seguimientos', 'cargue412s.id', '=', 'seguimientos.cargue412s_id')
        ->where('cargue412s.estado', '=', 1)
        ->where('user_id', Auth::user()->id)
        ->whereYear('cargue412s.created_at', '>', 2023)// ->whereYear('cargue412s.created_at', '=', $yearActual) 
        ->get();

        $income12 =  DB::connection('sqlsrv_1')->table('refIps')->select('descrip')
        ->where('refIps.codigoDepartamento', 44)
        ->get();

        return view('seguimiento_412.create', compact('incomeedit','income12','sivigilas2030'))
        ->with('empleado', null);
 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1) Validación
        $request->validate([
            'fecha_consulta'   => 'required|string|max:100',
            'peso_kilos'       => 'required',
            'talla_cm'         => 'required',
            'puntajez'         => 'required',
            'clasificacion'    => 'required',
            'requerimiento_energia_ftlc' => 'required',
            'medicamento'      => 'required|array',
            'Esquemq_complrto_pai_edad'  => 'required',
            'Atecion_primocion_y_mantenimiento_res3280_2018' => 'required',
            'observaciones'    => 'required',
            'cargue412_id'     => 'required|exists:cargue412s,id',
            'pdf'              => 'required|mimes:pdf|max:5048',
            'estado'           => 'required|in:0,1',
        ],[
            'required' => 'El :attribute es requerido',
            'pdf.required' => 'El archivo PDF es requerido.',
            'pdf.mimes'    => 'El archivo debe ser un PDF válido.',
            'pdf.max'      => 'El tamaño del PDF no puede exceder :max kilobytes.',
        ]);
    
        // 2) Evitar seguimientos duplicados
        if (Seguimiento_412::where('cargue412_id', $request->cargue412_id)
            ->where('fecha_proximo_control', '>', now())
            ->exists()
        ) {
            return redirect()
                ->route('new412_seguimiento.index')
                ->with('error1', 'No puedes hacer un seguimiento porque la fecha de control no se ha cumplido');
        }
    
        // 3) Crear seguimiento
        $seguimiento = new Seguimiento_412($request->except(['_token','_method','medicamento','pdf']));
        $seguimiento->medicamento = implode(',', $request->medicamento);
        $seguimiento->user_id     = Auth::id();
        $seguimiento->estado      = $request->estado;
    
        // Subir PDF
        $file     = $request->file('pdf');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->storeAs('public/pdf', $filename);
        $seguimiento->pdf = $filename;
        $seguimiento->save();
    
        // 4) Actualizar estados
        DB::table('cargue412s')
            ->where('id', $seguimiento->cargue412_id)
            ->update(['estado' => $seguimiento->estado]);
        if ($prev = Seguimiento_412::where('cargue412_id', $seguimiento->cargue412_id)
            ->where('id','<',$seguimiento->id)
            ->latest('id')->first()
        ) {
            $prev->update(['estado'=>0]);
        }
    
        // 5) Enviar correo si estado==1
        $mailSent = false;
        if ($seguimiento->estado == 1) {
            $cargue = Cargue412::find($seguimiento->cargue412_id);
            $user   = $cargue ? User::find($cargue->user_id) : null;
    
            if ($user && $user->email) {
                // Construir bodyText
                $data = DB::table('seguimiento_412s')
                    ->join('cargue412s','seguimiento_412s.cargue412_id','=','cargue412s.id')
                    ->select(
                        'seguimiento_412s.id as idseg',
                        'cargue412s.numero_identificacion',
                        'cargue412s.primer_nombre',
                        'cargue412s.segundo_nombre',
                        'cargue412s.primer_apellido',
                        'cargue412s.segundo_apellido',
                        'seguimiento_412s.fecha_proximo_control as fec'
                    )
                    ->where('seguimiento_412s.id',$seguimiento->id)
                    ->first();
    
                $bodyText = '<br>'
                    ."ID: <strong>{$data->idseg}</strong><br>"
                    ."Identificación: <strong>{$data->numero_identificacion}</strong><br>"
                    ."Primer nombre: <strong>{$data->primer_nombre}</strong><br>"
                    ."Segundo nombre: <strong>{$data->segundo_nombre}</strong><br>"
                    ."Primer apellido: <strong>{$data->primer_apellido}</strong><br>"
                    ."Segundo apellido: <strong>{$data->segundo_apellido}</strong><br>"
                    ."Recuerde que la próxima fecha de control es: <strong>{$data->fec}</strong><br>";
    
                $html = 'Hola, acabas de realizarle un seguimiento a '.$bodyText
                      .'se solicita gestionarlo lo antes posible ingresando a este enlace <br>'
                      .url('login');
    
                // ——— CAMBIO CLAVE: puerto 465, encryption ssl ———
                $transport = new EsmtpTransport(
                    env('MAIL_HOST', 'smtp.gmail.com'),
                    465,
                    'ssl'
                );
                $transport->setUsername(env('MAIL_USERNAME'));
                $transport->setPassword(env('MAIL_PASSWORD'));
    
                Log::info("SMTP configurado: host=".env('MAIL_HOST')." port=465 encryption=ssl");
    
                $mailer = new Mailer($transport);
                $email  = (new Email())
                    ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                    ->to(new Address($user->email))
                    ->subject('Recordatorio de control')
                    ->html($html);
    
                try {
                    $mailer->send($email);
                    $mailSent = true;
                    Log::info("Correo enviado a {$user->email} (seguimiento ID {$seguimiento->id})");
                } catch (\Throwable $e) {
                    Log::warning("Error SMTP: {$e->getMessage()}");
                }
            } else {
                Log::warning("No se envía correo: usuario (ID {$cargue->user_id}) sin email o no encontrado");
            }
        }
    
        // 6) Redirigir con feedback
        $flashKey = $mailSent ? 'success' : 'warning';
        $flashMsg = $mailSent
            ? 'Seguimiento guardado y correo enviado correctamente.'
            : 'Seguimiento guardado, pero no se pudo enviar el correo.';
    
        return redirect()
            ->route('new412_seguimiento.index')
            ->with($flashKey, $flashMsg);
    }
    


    /**
     * Display the specified resource.
     */
    public function show(Seguimiento_412 $seguimiento_412)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seguimiento_412 $seguimiento_412, $id)
    {
        $incomeedit = DB::table('cargue412s')->select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
        'cargue412s.primer_apellido','cargue412s.segundo_apellido','cargue412s.id as idin')
        ->join('seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
        ->where('seguimiento_412s.id', $id)
        ->get();
        $empleado = Seguimiento_412::findOrFail($id); 
        return view('seguimiento_412.edit',compact('empleado','incomeedit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seguimiento_412 $seguimiento_412, $id)
    {
        $datosEmpleado = $request->except(['_token','_method']);
        $medicamentos = implode(',', $datosEmpleado['medicamento']);
        $datosEmpleado['medicamento'] = $medicamentos;
    
        Seguimiento_412::where('id', $id)->update($datosEmpleado);
    
        DB::table('cargue412s')
            ->where('id', $request->cargue412_id)
            ->update(['estado' => $request->estado == 1 ? '1' : '0']);
    
        // Envío de correo solo si estado == 1
        $mailSent = false;
        if ($request->estado == 1) {
            $seguimiento = Seguimiento_412::find($id);
            $cargue = Cargue412::find($seguimiento->cargue412_id);
            $user = $cargue ? User::find($cargue->user_id) : null;
    
            if ($user && $user->email) {
                $data = DB::table('seguimiento_412s')
                    ->join('cargue412s', 'seguimiento_412s.cargue412_id', '=', 'cargue412s.id')
                    ->select(
                        'seguimiento_412s.id as idseg',
                        'seguimiento_412s.motivo_reapuertura',
                        'cargue412s.numero_identificacion',
                        'cargue412s.primer_nombre',
                        'cargue412s.segundo_nombre',
                        'cargue412s.primer_apellido',
                        'cargue412s.segundo_apellido',
                        'seguimiento_412s.fecha_proximo_control as fec'
                    )
                    ->where('seguimiento_412s.id', $id)
                    ->first();
    
                $bodyText = '<br>'
                    ."<strong>ID:</strong> {$data->idseg}<br>"
                    ."<strong>Identificación:</strong> {$data->numero_identificacion}<br>"
                    ."<strong>Motivo reapertura:</strong> {$data->motivo_reapuertura}<br>"
                    ."<strong>Nombre:</strong> {$data->primer_nombre} {$data->segundo_nombre} {$data->primer_apellido} {$data->segundo_apellido}<br>"
                    ."<strong>Próximo control:</strong> {$data->fec}<br>";
    
                $html = 'Hola, tu seguimiento fue actualizado por el administrador:<br>'
                      . $bodyText
                      . 'Por favor, gestiona el caso accediendo al sistema:<br>'
                      . url('login');
    
                // Configurar transporte SMTP manualmente con puerto 465 y ssl
                $transport = new EsmtpTransport(
                    env('MAIL_HOST', 'smtp.gmail.com'),
                    465,
                    'ssl'
                );
                $transport->setUsername(env('MAIL_USERNAME'));
                $transport->setPassword(env('MAIL_PASSWORD'));
    
                $mailer = new Mailer($transport);
                $email = (new Email())
                    ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                    ->to(new Address($user->email))
                    ->subject('Recordatorio de control actualizado')
                    ->html($html);
    
                try {
                    $mailer->send($email);
                    $mailSent = true;
                    Log::info("Correo enviado a {$user->email} (seguimiento ID {$id})");
                } catch (\Throwable $e) {
                    Log::warning("Error al enviar correo: " . $e->getMessage());
                }
            }
        }
    
        $flashKey = $mailSent ? 'success' : 'warning';
        $flashMsg = $mailSent
            ? 'Seguimiento actualizado y correo enviado correctamente.'
            : 'Seguimiento actualizado, pero no se pudo enviar el correo.';
    
        return redirect()
            ->route('new412_seguimiento.index')
            ->with($flashKey, $flashMsg);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seguimiento_412 $seguimiento_412 ,$id)
    {
        Seguimiento_412::destroy($id);
        // Session::flash('error','El registro se ha agregado correctamente');
        return redirect('new412_seguimiento')->with('error', 'Empleado borrado con exito .!');
    }


    // Método para ver el PDF
public function viewPDF($id)
{
    $seguimiento = Seguimiento_412::findOrFail($id);
    $filePath = storage_path('app/public/pdf/' . $seguimiento->pdf);

    return response()->file($filePath);
}




public function reporte_seguimiento412()
{   
    return Excel::download(new Seguimiento412Export, 'seguimientos_412.xls');


}
}
