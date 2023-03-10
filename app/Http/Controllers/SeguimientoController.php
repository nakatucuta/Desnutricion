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
class SeguimientoController extends Controller
{

    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        $this->middleware('auth');
        // $this->middleware('Admin_seguimiento', ['only' =>'create']);
        //  $this->middleware('Admin_seguimiento', ['only' =>'index']);
        //  $this->middleware('Admin_seguimiento', ['only' =>'alerta']);
        $this->middleware('Admin_seguimiento', ['only' =>'reporte2']);
        $this->middleware('Admin_seguimiento', ['only' =>'resporte']);
        $this->middleware('Admin_nutric_seguimiento', ['only' =>'edit']);
        $this->middleware('Admin_nutric_seguimiento', ['only' =>'destroy']);
    
       

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
        'seguimientos.fecha_proximo_control','seguimientos.estado','seguimientos.id')
        ->orderBy('seguimientos.created_at', 'desc')
        
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        // ->where('seguimientos.estado',1)
        ->where('seguimientos.user_id', Auth::User()->id )
        ->paginate(3000);
        } else {  

            $incomeedit = Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
            'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin','sivigilas.Ips_at_inicial',
            'seguimientos.fecha_consulta','seguimientos.id','seguimientos.fecha_proximo_control','seguimientos.estado')
            ->orderBy('seguimientos.created_at', 'desc')
          
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            // ->where('seguimientos.estado',1)
            
            ->paginate(3000);

        } 

        if (Auth::User()->usertype == 2) {
        $conteo = Seguimiento::where('estado', 1)
                    ->where('user_id', Auth::user()->id)
                    ->count('id');
        }else{
            $conteo = Seguimiento::where('estado', 1)->count('id');

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
        $campos= [
            'fecha_consulta' => 'required|string|max:100',
            'peso_kilos' => 'required',
            'talla_cm' => 'required',
            'puntajez' => 'required',
            'clasificacion' => 'required',
            'requerimiento_energia_ftlc' => 'required',
            'fecha_entrega_ftlc' => 'required',
            'medicamento' => 'required',
            // 'recomendaciones_manejo' => 'required',
            // 'resultados_seguimientos' => 'required',
            // 'ips_realiza_seguuimiento' => 'required',
            'observaciones' => 'required',
            // 'fecha_proximo_control' => 'nullable|date|after_or_equal:today',
            'sivigilas_id' => 'required',
            // 'archivo_pdf' => 'required|mimes:pdf|max:2048',


        ];

        $mensajes=[
            'required'=>'El :attribute es requerido',
            'fecha_proximo_control.after_or_equal' => 'La fecha de ingreso no puede ser anterior a la fecha actual.',
      
        ];

        $this->validate($request, $campos, $mensajes);

        // $datosEmpleado = request()->except('_token');
        // Seguimiento::insert($datosEmpleado);
        
        $seguimientoExistente = Seguimiento::where('sivigilas_id', $request->sivigilas_id)
        ->where('fecha_proximo_control', '>', Carbon::now())
        ->first();

        if (!$seguimientoExistente) {

                $entytistore = new Seguimiento;
                $entytistore->estado = $request->estado;
                $entytistore->fecha_consulta = $request->fecha_consulta;
                $entytistore->peso_kilos = $request->peso_kilos;
                $entytistore->talla_cm = $request->talla_cm;
                $entytistore->puntajez = $request->puntajez;
                $entytistore->clasificacion = $request->clasificacion;
                $entytistore->requerimiento_energia_ftlc = $request->requerimiento_energia_ftlc;
                $entytistore->fecha_entrega_ftlc = $request->fecha_entrega_ftlc;
                $entytistore->medicamento = $request->medicamento;
                // $entytistore->recomendaciones_manejo = $request->recomendaciones_manejo;
                // $entytistore->resultados_seguimientos = $request->resultados_seguimientos;
                // $entytistore->ips_realiza_seguuimiento = $request->ips_realiza_seguuimiento;
                $entytistore->observaciones = $request->observaciones;
                // if (empty($request->fecha_proximo_control)) { cod para saber cuando un campo esta vacio haga esto
                    // $entytistore->fecha_proximo_control = date('Y-m-d');
                // } else {
                    $entytistore->est_act_menor = $request->est_act_menor;
                    $entytistore->tratamiento_f75 = $request->tratamiento_f75;
                    $entytistore->fecha_recibio_tratf75 = $request->fecha_recibio_tratf75;
                    
                    $entytistore->fecha_proximo_control = $request->fecha_proximo_control;
                // }
                $entytistore->sivigilas_id = $request->sivigilas_id;
                $entytistore->user_id = auth()->user()->id;
                
               

               
       
        if ( $request->estado == 0) {
            DB::table('sivigilas')
            ->where('sivigilas.id',  $entytistore->sivigilas_id)
            ->update(['estado' => '0',]);
            DB::table('seguimientos')
             ->join('sivigilas', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
             ->where('sivigilas.id',  $entytistore->sivigilas_id)
             ->update(['estado' => '0',]);
           } 
           
           $entytistore->save();

            //obtener id anterior
            $registroAnterior = DB::table('seguimientos')
->where('sivigilas_id', $request->sivigilas_id)
    ->where('id', '<', $entytistore->id)
    ->orderBy('id', 'desc')
    ->first();

// Actualizar el estado del registro anterior
if ($registroAnterior) {
    DB::table('seguimientos')
        ->where('id', $registroAnterior->id)
        ->update(['estado' => '0',]);
}
           if ( $entytistore->estado == 1) {

           //para enviarle un consulta al correo 
            // aqui empieza el tema de envio de correos entonces si el estado es 1
            //creamos una consulta
           $results = DB::table('sivigilas')->select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
           'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idseg','seguimientos.fecha_proximo_control as fec')
          
           ->where('seguimientos.estado',1)
           ->where('seguimientos.id', $entytistore->id)
           ->where('seguimientos.user_id', Auth::User()->id )
           ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
            ->get();
            
             $bodyText = ':<br>';
             
            foreach ($results as $result) {
            $bodyText .= 'ID: ' .'<strong>' . $result->idseg . '</strong><br>';
            $bodyText .= 'Identificaci??n: ' .'<strong>' . $result->num_ide_ . '</strong><br>';
            $bodyText .= 'Primer nombre: ' .'<strong>' . $result->pri_nom_ . '</strong><br>';
            $bodyText .= 'Segundo nombre: ' .'<strong>' . $result->seg_nom_ . '</strong><br>';
            $bodyText .= 'Primer apellido: ' .'<strong>' . $result->pri_ape_ . '</strong><br>';
            $bodyText .= 'Segundo apellido: ' .'<strong>' . $result->seg_ape_ . '</strong><br>';
            $bodyText .= 'Recuerde que la pr??xima fecha de control es: ' .'<strong>' . $result->fec . '</strong><br>';
             }
            //aqui termina la consulta que enviaremos al cuerpo del correo

             

             
           $transport = new EsmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION'));
           $transport->setUsername(env('MAIL_USERNAME'))
                     ->setPassword(env('MAIL_PASSWORD'));
           
           $mailer = new Mailer($transport);
           
           $email = (new Email())
                   ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                   ->to(new Address(Auth::user()->email))
                   ->subject('Recordatorio de control')
                   ->html('Hola, acabas de realizarle un seguimiento a'.$bodyText);
            
           if ($mailer->send($email)) {
               return redirect()->route('Seguimiento.index')
                  ->with('mensaje', 'El seguimiento fue guardado exitosamente');
           } else {
            
               return redirect()->route('Seguimiento.index')
                  ->with('mensaje', 'El seguimiento fue guardado exitosamente');
           }
        } else  {

            return redirect()->route('Seguimiento.index')
                  ->with('mensaje', 'El seguimiento fue guardado exitosamente');
        }
    } else{

        return redirect()->route('Seguimiento.index')
        ->with('error1', 'No puedes hacer un seguimiento porque la fecha de control no se ha cumplido');

    }
           //para enviarle un consulta al correo 
           //$results = DB::table('mi_tabla')->where('condicion', '=', 'valor')->get();
            // $bodyText = 'La lista de resultados es:<br>';
            // foreach ($results as $result) {
            //     $bodyText .= $result->atributo . '<br>';
            // }
            // $email = (new Email())
            //     ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
            //     ->to(new Address('juancamilosuarezcantero@gmail.com'))
            //     ->subject('Recordatorio de control')
            //     ->html($bodyText);
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
    public function update(Request $request, Seguimiento $seguimiento,$id)
    {
        $datosEmpleado = request()->except(['_token','_method']);
      $seg =  Seguimiento::where('id','=',$id )->update($datosEmpleado);

        if ($seg) {
            DB::table('sivigilas')
                ->where('id', $request->sivigilas_id)
                ->update(['estado' => '1',]);
        }
        
        return redirect()->route('Seguimiento.index');
        // return view('seguimiento.index', compact('empleado'),["incomeedit"=>$incomeedit]);
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
        


    
}
    
        
