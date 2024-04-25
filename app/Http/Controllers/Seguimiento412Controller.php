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

class Seguimiento412Controller extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function index( Request $request )
    {
        $busqueda = $request->busqueda;
        $busqueda = $request->busqueda;
        $user_id = Auth::User()->usertype;
        $user_id1 = Auth::User()->id == '2';
        //pra mostrar lo que cada usuario ingrese 

        if (Auth::User()->usertype == 2) {
            $incomeedit = Cargue412::select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
            'cargue412s.primer_apellido','cargue412s.segundo_apellido','seguimiento_412s.id as idin',
            'seguimiento_412s.fecha_proximo_control','seguimiento_412s.id',
            'seguimiento_412s.estado','seguimiento_412s.id','cargue412s.nombre_coperante')
            ->orderBy('seguimiento_412s.created_at', 'desc')
            ->join('seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
            ->where('seguimiento_412s.user_id', Auth::user()->id)
            ->whereYear('seguimiento_412s.created_at', '>', 2023) // Agregar la condición para el año
            ->paginate(3000);
        
        } else {  

            $incomeedit = Cargue412::select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
            'cargue412s.primer_apellido','cargue412s.segundo_apellido','seguimiento_412s.id as idin',
            'seguimiento_412s.fecha_proximo_control','seguimiento_412s.id',
            'seguimiento_412s.estado','seguimiento_412s.id','cargue412s.nombre_coperante')
            ->orderBy('seguimiento_412s.created_at', 'desc')
            ->join('seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
         
            ->whereYear('seguimiento_412s.created_at', '>', 2023) // Agregar la condición para el año
            ->paginate(3000);

        } 

        if (Auth::User()->usertype == 2) {
        $conteo = Seguimiento_412::where('estado', 1)
                    ->where('user_id', Auth::user()->id)
                    ->count('id');
        }else{
            $conteo = Seguimiento_412::where('estado', 1)
            ->whereYear('seguimiento_412s.created_at', '>', 2023)
            ->count('id');

        }
        $seguimiento_412s = Seguimiento_412::all()->where('estado',1);
        $otro =  Cargue412::select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
        'cargue412s.primer_apellido','cargue412s.segundo_apellido','cargue412s.id as idin',
        'seguimiento_412s.id','seguimiento_412s.estado as est',
        'seguimiento_412s.user_id as usr')
        ->orderBy('seguimiento_412s.created_at', 'desc')
        ->join('seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
        // ->join('seguimiento_412s', 'seguimiento_412s.id', '=', 'seguimiento_412s.cargue412s_id')
        ->where('seguimiento_412s.estado',1)
        ->get();
        return view('seguimiento_412.index',compact('incomeedit','seguimiento_412s','conteo','otro'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $sivigilas2030 = Cargue412::
        where('user_id', Auth::user()->id)->get();
        

        $incomeedit = DB::table('cargue412s')
        ->select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
        'cargue412s.primer_apellido','cargue412s.segundo_apellido','cargue412s.id as idin')
        // ->join('seguimientos', 'cargue412s.id', '=', 'seguimientos.cargue412s_id')
        ->where('cargue412s.estado', '=', 1)
        ->where('user_id', Auth::user()->id)
        // ->whereYear('cargue412s.created_at', '>', 2023) 
        ->get();

        $income12 =  DB::connection('sqlsrv_1')->table('refIps')->select('descrip')
        ->where('refIps.codigoDepartamento', 44)
        ->get();

        return view('seguimiento_412.create',compact('incomeedit','income12','sivigilas2030'));
    }

    /**
     * Store a newly created resource in storage.
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
            // 'fecha_entrega_ftlc' => 'required',
            'medicamento' => 'required',
            //  'motivo_reapuertura' => 'required',
            // 'resultados_seguimientos' => 'required',
            // 'ips_realiza_seguuimiento' => 'required',
            'Esquemq_complrto_pai_edad' => 'required',

            'Atecion_primocion_y_mantenimiento_res3280_2018' => 'required',
            'observaciones' => 'required',
            // 'fecha_proximo_control' => 'nullable|date|after_or_equal:today',
            'cargue412_id' => 'required',
            // 'archivo_pdf' => 'required|mimes:pdf|max:2048',


        ];

        $mensajes=[
            'required'=>'El :attribute es requerido',
            'fecha_proximo_control.after_or_equal' => 'La fecha de ingreso no puede ser anterior a la fecha actual.',
      
        ];

        $this->validate($request, $campos, $mensajes);

        // $datosEmpleado = request()->except('_token');
        // Seguimiento::insert($datosEmpleado);
        
        $seguimientoExistente = Seguimiento_412::where('cargue412_id', $request->cargue412_id)
        ->where('fecha_proximo_control', '>', Carbon::now())
        ->first();

        if (!$seguimientoExistente) {
                
                $entytistore = new Seguimiento_412;
                $entytistore->estado = $request->estado;
                $entytistore->fecha_consulta = $request->fecha_consulta;
                $entytistore->peso_kilos = $request->peso_kilos;
                $entytistore->talla_cm = $request->talla_cm;
                $entytistore->puntajez = $request->puntajez;
                $entytistore->clasificacion = $request->clasificacion;
                // $entytistore->requerimiento_energia_ftlc = $request->requerimiento_energia_ftlc;
                // $entytistore->fecha_entrega_ftlc = $request->fecha_entrega_ftlc;
                $entytistore->medicamento = implode(',', $request->input('medicamento'));
                 $entytistore->motivo_reapuertura = $request->motivo_reapuertura;
                // $entytistore->resultados_seguimientos = $request->resultados_seguimientos;
                // $entytistore->ips_realiza_seguuimiento = $request->ips_realiza_seguuimiento;
                $entytistore->Esquemq_complrto_pai_edad = $request->Esquemq_complrto_pai_edad;

                $entytistore->Atecion_primocion_y_mantenimiento_res3280_2018 = $request->Atecion_primocion_y_mantenimiento_res3280_2018;
               
                $entytistore->observaciones = $request->observaciones;
                // if (empty($request->fecha_proximo_control)) { cod para saber cuando un campo esta vacio haga esto
                    // $entytistore->fecha_proximo_control = date('Y-m-d');
                // } else {
                    $entytistore->est_act_menor = $request->est_act_menor;
                    $entytistore->requerimiento_energia_ftlc = $request->requerimiento_energia_ftlc;
                    // $entytistore->tratamiento_f75 = $request->tratamiento_f75;
                    // $entytistore->fecha_recibio_tratf75 = $request->fecha_recibio_tratf75;
                    $entytistore->perimetro_braqueal = $request->perimetro_braqueal;
                    $entytistore->fecha_proximo_control = $request->fecha_proximo_control;
                // }
                $entytistore->cargue412_id = $request->cargue412_id;
                $entytistore->user_id = auth()->user()->id;
                //codigo para subir pdf
                $file = $request->file('pdf');
                $request->validate([
                    'pdf' => [
                        'required',
                        'mimes:pdf',
                        'max:5048', // Maximo 2 MB (2048 KB)
                    ],
                ], [
                    'pdf.required' => 'El archivo PDF es requerido.',
                    'pdf.mimes' => 'El archivo debe ser un PDF válido.',
                    'pdf.max' => 'El tamaño del archivo PDF no puede ser mayor a :max kilobytes.',
                ]);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/pdf', $filename);//ojo esto se guarda en 
                //la carpeta storage/public/pdf
                
                $entytistore->pdf = $filename;
               //aqui termina codigo para subir pdf

               
       
        if ( $request->estado == 0) {
            DB::table('cargue412s')
            ->where('cargue412s.id',  $entytistore->cargue412_id)
            ->update(['estado' => '0',]);
            DB::table('seguimiento_412s')
             ->join('cargue412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
             ->where('cargue412s.id',  $entytistore->cargue412_id)
             ->update(['estado' => '0',]);
           } 
           
           $entytistore->save();

            //obtener id anterior
            $registroAnterior = DB::table('seguimiento_412s')
->where('cargue412_id', $request->cargue412_id)
    ->where('id', '<', $entytistore->id)
    ->orderBy('id', 'desc')
    ->first();

// Actualizar el estado del registro anterior
if ($registroAnterior) {
    DB::table('seguimiento_412s')
        ->where('id', $registroAnterior->id)
        ->update(['estado' => '0',]);
}
           if ( $entytistore->estado == 1) {

           //para enviarle un consulta al correo 
            // aqui empieza el tema de envio de correos entonces si el estado es 1
            //creamos una consulta
           $results = DB::table('cargue412s')->select('cargue412s.numero_identificacion','cargue412s.primer_nombre','cargue412s.segundo_nombre',
           'cargue412s.primer_apellido','cargue412s.segundo_apellido','seguimiento_412s.id as idseg','seguimiento_412s.fecha_proximo_control as fec')
          
           ->where('seguimiento_412s.estado',1)
           ->where('seguimiento_412s.id', $entytistore->id)
           ->where('seguimiento_412s.user_id', Auth::User()->id )
           ->join('seguimiento_412s', 'cargue412s.id', '=', 'seguimiento_412s.cargue412_id')
            // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
            ->get();
            
             $bodyText = ':<br>';
             
            foreach ($results as $result) {
            $bodyText .= 'ID: ' .'<strong>' . $result->idseg . '</strong><br>';
            $bodyText .= 'Identificación: ' .'<strong>' . $result->numero_identificacion . '</strong><br>';
            $bodyText .= 'Primer nombre: ' .'<strong>' . $result->primer_nombre . '</strong><br>';
            $bodyText .= 'Segundo nombre: ' .'<strong>' . $result->segundo_nombre . '</strong><br>';
            $bodyText .= 'Primer apellido: ' .'<strong>' . $result->primer_apellido . '</strong><br>';
            $bodyText .= 'Segundo apellido: ' .'<strong>' . $result->segundo_apellido . '</strong><br>';
            $bodyText .= 'Recuerde que la próxima fecha de control es: ' .'<strong>' . $result->fec . '</strong><br>';
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
                   ->html('Hola, acabas de realizarle un seguimiento a'.$bodyText.'se solicita gestionarlo lo antes posible ingresando a este enlace <br>
                   https://dnt.epsianaswayuu.com:58222/Desnutricion/public/');
            
           if ($mailer->send($email)) {
               return redirect()->route('new412_seguimiento.index')
                  ->with('mensaje', 'El seguimiento fue guardado exitosamente');
           } else {
            
               return redirect()->route('new412_seguimiento.index')
                  ->with('mensaje', 'El seguimiento fue guardado exitosamente');
           }
        } else  {

            return redirect()->route('new412_seguimiento.index')
                  ->with('mensaje', 'El seguimiento fue guardado exitosamente');
        }
    } else{

        return redirect()->route('new412_seguimiento.index')
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
    public function update(Request $request, Seguimiento_412 $seguimiento_412,$id)
    {
        $datosEmpleado = request()->except(['_token','_method']);
        $medicamentos = implode(',', $datosEmpleado['medicamento']);
        $datosEmpleado['medicamento'] = $medicamentos;
        $seg =  Seguimiento_412::where('id', $id)->update($datosEmpleado);

        //OJO DEBES MODIFICAR ESTE PEDAZO PARAQUE CUANDO ACTUALIZE FUNCIONE
        if ($request->estado == 1) {
            DB::table('cargue412s')
                ->where('id', $request->cargue412_id) // Agregar esta línea
                ->update(['estado' => '1']);
        } else {
            DB::table('cargue412s')
            ->where('id', $request->cargue412_id) // Agregar esta línea
                ->update(['estado' => '0']);
        }

         //para enviarle un consulta al correo 
            // aqui empieza el tema de envio de correos entonces si el estado es 1
            //creamos una consulta
            $results = DB::table('seguimiento_412s')
             ->select('motivo_reapuertura', 'seguimiento_412s.id','cargue412s.primer_nombre','cargue412s.segundo_nombre',
             'cargue412s.primer_apellido','cargue412s.segundo_apellido')
             ->where('seguimiento_412s.id', $id)
             ->join('cargue412s', 'seguimiento_412s.cargue412_id', '=', 'cargue412s.id')
             ->get();
            
             $bodyText = ':<br>';
             
             foreach ($results as $result) {

            $bodyText .= 'Id de seguimiento: ' .'<strong>' . $result->id . '</strong><br>';

             $bodyText .= 'Motivo de reapuertura: ' .'<strong>' . $result->motivo_reapuertura . '</strong><br>';
             $bodyText .= 'Primer nombre: ' .'<strong>' . $result->primer_nombre . '</strong><br>';
             $bodyText .= 'Segundo nombre: ' .'<strong>' . $result->segundo_nombre . '</strong><br>';
             $bodyText .= 'Primer apellido: ' .'<strong>' . $result->primer_apellido . '</strong><br>';
             $bodyText .= 'Segundo apellido: ' .'<strong>' . $result->segundo_apellido . '</strong><br>';

               }
            //aqui termina la consulta que enviaremos al cuerpo del correo

             
            $sivigila = Cargue412::find($datosEmpleado['cargue412_id']);
            $user = User::find($sivigila->user_id);
             
           $transport = new EsmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION'));
           $transport->setUsername(env('MAIL_USERNAME'))
                     ->setPassword(env('MAIL_PASSWORD'));
           
           $mailer = new Mailer($transport);
           
           $email = (new Email())
                   ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                   ->to(new Address($user->email))
                   ->subject('Recordatorio de control')
                   ->html('Hola, tu seguimiento acaba de ser actualizado por el administrador debido a  algun inconveniente comunicate
                   con la EPSI'.$bodyText.'se solicita gestionarlo lo antes posible ingresando a este enlace <br>
                   https://dnt.epsianaswayuu.com:58222/Desnutricion/public/');
                   if ($mailer->send($email)) {
            return redirect()->route('Seguimiento.index')
           ->with('mensaje',' El dato fue agregado a la base de datos Exitosamente..!');
                   }else{
                    return redirect()->route('Seguimiento.index')
           ->with('mensaje',' El dato fue agregado a la base de datos Exitosamente..!');
            
                   }
        
        return redirect()->route('Seguimiento_412.index');
        // return view('seguimiento.index', compact('empleado'),["incomeedit"=>$incomeedit]);
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
