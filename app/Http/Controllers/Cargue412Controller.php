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
    public function edit(Cargue412 $cargue412, $id, $numero_identificacion)
    {
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
        
        $edit_cargue = Cargue412::findOrFail($id); 
        return view('new_412.edit', compact('edit_cargue', 'incomeedit15', 'income12', 'incomeedit14'));
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cargue412 $cargue412 ,$id)
    {
        $datosEmpleado = $request->except(['_token', '_method']);
    
    DB::transaction(function () use ($id, $datosEmpleado) {
        // Actualizar el registro específico con los datos proporcionados
        $seg = Cargue412::where('id', $id)->update($datosEmpleado);

        // Verificar si la actualización fue exitosa y luego actualizar el campo 'estado'
        if ($seg) {
            DB::table('cargue412s')
                ->where('id', $id)
                ->update(['estado' => 1]);
        }
    });
        //para enviarle una consulta al correo 
            // aqui empieza el tema de envio de correos entonces si el estado es 1
            //creamos una consulta
            $results = DB::table('cargue412s')
            ->select('numero_identificacion', 'primer_nombre','segundo_nombre','primer_apellido','segundo_apellido','fecha_captacion')
            ->where('numero_identificacion', $request->numero_identificacion)
            ->where('fecha_captacion', $request->fecha_captacion)
            ->get();
            
             $bodyText = ':<br>';
             
            foreach ($results as $result) {
            $bodyText .= 'Fecha de notificacion: ' .'<strong>' . $result->fecha_captacion . '</strong><br>';
            $bodyText .= 'Identificación: ' .'<strong>' . $result->numero_identificacion . '</strong><br>';
            $bodyText .= 'Primer Nombre: ' .'<strong>' . $result->primer_nombre . '</strong><br>';
            $bodyText .= 'Segundo Nombre: ' .'<strong>' . $result->segundo_nombre . '</strong><br>';
            $bodyText .= 'Primer Apellido: ' .'<strong>' . $result->primer_apellido . '</strong><br>';
            $bodyText .= 'Segundo Apellido: ' .'<strong>' . $result->segundo_apellido . '</strong><br>';
              }
            //aqui termina la consulta que enviaremos al cuerpo del correo

             
                        // Validar que el usuario exista
            $user = User::find($request->user_id);

            if ($user && $user->email) {
                try {
                    $transport = new EsmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION'));
                    $transport->setUsername(env('MAIL_USERNAME'))
                            ->setPassword(env('MAIL_PASSWORD'));

                    $mailer = new Mailer($transport);

                    $email = (new Email())
                        ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                        ->to(new Address($user->email))
                        ->subject('Recordatorio de control')
                        ->html('FAVOR NO CONTESTAR ESTE MENSAJE <br>
                        Hola, te acaban de asignar un paciente de desnutrición (412) por parte de la EPSI Anas Wayuu. 
                        Por favor, gestionarlo lo antes posible ingresando al siguiente enlace: <br>
                        <a href="https://app.epsianaswayuu.com/rutasintegrales/login">Ingresar</a><br><br>' . $bodyText);

                    $mailer->send($email);

                } catch (\Exception $e) {
                    \Log::error('Error al enviar el correo: ' . $e->getMessage());
                    // Puedes opcionalmente mostrar un mensaje si quieres
                }
            }

        return redirect()->route('import-excel');
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


    public function showImportForm()
    {

       
        $seguimientoen113 = DB::table(DB::connection('sqlsrv')->raw('[DESNUTRICION].[dbo].[sivigilas] as a'))
        ->distinct()
        ->select('a.num_ide_ as identificacion')
        ->join(DB::connection('sqlsrv')->raw('[DESNUTRICION].[dbo].[seguimientos] as b'), 'a.id', '=', 'b.sivigilas_id')
        ->where('b.estado', 1)
        ->whereIn('a.num_ide_', function($query) {
            $query->select('numero_identificacion')
                  ->from(DB::connection('sqlsrv')->raw('[DESNUTRICION].[dbo].[cargue412s]'));
        })
        ->whereIn('a.tip_ide_', function($query) {
            $query->select('tipo_identificacion')
                  ->from(DB::connection('sqlsrv')->raw('[DESNUTRICION].[dbo].[cargue412s]'));
        })
        ->pluck('identificacion');
    
    // Establecer estado_anulado a 1 donde se cumple la condición
    DB::table('cargue412s')
        ->whereIn('numero_identificacion', $seguimientoen113)
        ->update(['estado_anulado' => 1]);
    
    // Establecer estado_anulado a 0 donde NO se cumple la condición
    DB::table('cargue412s')
        ->whereNotIn('numero_identificacion', $seguimientoen113)
        ->update(['estado_anulado' => 0]);

    



        // Consulta utilizando el modelo Cargue412
    $sivigilas = Cargue412::from('cargue412s as a')
    ->select('a.*', 'd.descrip as ips_primaria')
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroidentificaciones] as b'), function($join) {
        $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
             ->on('a.numero_identificacion', '=', 'b.identificacion');
    })
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as c'), 'b.numeroCarnet', '=', 'c.numeroCarnet')
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as d'), 'c.idGrupoIps', '=', 'd.id')
    ->get();

// Preparar los datos para la vista
foreach ($sivigilas as $student2) {
    $incomeedit14 = DB::connection('sqlsrv_1')
        ->table('maestroAfiliados as a')
        ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
        ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
        ->join('maestroIpsGruDet as d', function ($join) {
            $join->on('c.id', '=', 'd.idd')
                ->where('d.servicio', '=', 1);
        })
        ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
        ->select(DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'))
        ->where('a.identificacion', $student2->numero_identificacion)
        ->first();

    if ($incomeedit14 !== null) {
        $income12 = DB::table('users')
            ->select('name', 'id', 'codigohabilitacion')
            ->where('codigohabilitacion', $incomeedit14->codigo_habilitacion)
            ->first();

        if ($income12 === null) {
            $student2->displayText = 'Sin datos, NO ASIGNAR hasta confirmar prestador primario';
            $student2->textColor = 'red';
        } else {
            $student2->displayText = $income12->name;
            $student2->textColor = 'black'; // Color negro (o cualquier color por defecto)
        }
    } else {
        $student2->displayText = 'Sin datos, NO ASIGNAR hasta confirmar prestador primario';
        $student2->textColor = 'red';
    }
}





        // $sivigilas = Cargue412::from('cargue412s as a')
        // ->select('a.*', 'd.descrip as ips_primaria')
        // ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroidentificaciones] as b'), function($join) {
        //     $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
        //          ->on('a.numero_identificacion', '=', 'b.identificacion');
        // })
        // ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as c'), 'b.numeroCarnet', '=', 'c.numeroCarnet')
        // ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as d'), 'c.idGrupoIps', '=', 'd.id')
        // ->get();

        //OTRA FROMA DE HACERLO 
        // $sivigilas = DB::table(DB::raw('[DESNUTRICION]..[cargue412s] AS [a]'))
        // ->select(
        //     'a.*',
        //     'd.descrip as ips_primaria'
        // )
        // ->leftJoin(DB::raw('[sga]..[maestroidentificaciones] AS [b]'), function($join) {
        //     $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
        //          ->on('a.numero_identificacion', '=', 'b.identificacion');
        // })
        // ->leftJoin(DB::raw('[sga]..[maestroips] AS [c]'), 'b.numeroCarnet', '=', 'c.numeroCarnet')
        // ->leftJoin(DB::raw('[sga]..[maestroIpsGru] AS [d]'), 'c.idGrupoIps', '=', 'd.id')
        // ->get();
        return view('new_412.form',compact('sivigilas','seguimientoen113'));

        
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
        return Excel::download(new Cargue412Export, '412_.xls');


    }
}
