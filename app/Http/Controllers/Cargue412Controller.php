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
 
         // 2) Actualizar todos los campos (incluyendo user_id) y, si hubo cambios, marcar estado=1
         $datosEmpleado = $request->except(['_token', '_method']);
         
         DB::transaction(function () use ($id, $datosEmpleado) {
             // $seg será el número de filas afectadas por el update
             $seg = Cargue412::where('id', $id)->update($datosEmpleado);
 
             if ($seg) {
                 // sólo si realmente se actualizó algo, fijamos estado=1
                 DB::table('cargue412s')
                     ->where('id', $id)
                     ->update(['estado' => 1]);
             }
         });
 
         // 3) Recoger el registro ya actualizado
         $registro = Cargue412::findOrFail($id);
         $datosCorreo = [
             'id'                     => $registro->id,
             'fecha_captacion'        => $registro->fecha_captacion,
             'numero_identificacion'  => $registro->numero_identificacion,
             'primer_nombre'          => $registro->primer_nombre,
             'segundo_nombre'         => $registro->segundo_nombre,
             'primer_apellido'        => $registro->primer_apellido,
             'segundo_apellido'       => $registro->segundo_apellido,
         ];
 
         // 4) Construir el HTML adicional (bodyText)
         $results = DB::table('cargue412s')
             ->select(
                 'fecha_captacion',
                 'numero_identificacion',
                 'primer_nombre',
                 'segundo_nombre',
                 'primer_apellido',
                 'segundo_apellido'
             )
             ->where('numero_identificacion', $request->numero_identificacion)
             ->where('fecha_captacion', $request->fecha_captacion)
             ->get();
 
         $bodyText = '<br>';
         foreach ($results as $r) {
             $bodyText .= "Fecha de notificación: <strong>{$r->fecha_captacion}</strong><br>";
             $bodyText .= "Identificación: <strong>{$r->numero_identificacion}</strong><br>";
             $bodyText .= "Primer Nombre: <strong>{$r->primer_nombre}</strong><br>";
             $bodyText .= "Segundo Nombre: <strong>{$r->segundo_nombre}</strong><br>";
             $bodyText .= "Primer Apellido: <strong>{$r->primer_apellido}</strong><br>";
             $bodyText .= "Segundo Apellido: <strong>{$r->segundo_apellido}</strong><br>";
         }
 
         // 5) Enviar el correo
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
 
         // 6) Redirigir siempre a import-excel con mensaje flash
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
