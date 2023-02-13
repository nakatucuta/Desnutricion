<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MaestroSiv113;
use App\Models\Sivigila;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SivigilaExport;
class SivigilaController extends Controller
{


    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        $this->middleware('auth');
        $this->middleware('Admin_sivigila', ['only' =>'create']);
        $this->middleware('Admin_sivigila', ['only' =>'index']);
        $this->middleware('Admin_sivigila', ['only' =>'show']);
        
       

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()

    {  
       $sivigilas = DB::connection('sqlsrv_1')
        ->table('maestroSiv113 AS m')
        ->select(DB::raw("CAST(MAX(m.fec_not) AS DATE) as fec_noti, m.tip_ide_, m.num_ide_, m.pri_nom_, m.seg_nom_, m.pri_ape_, m.seg_ape_"))
        ->where('m.cod_eve', 113)
        ->whereBetween(DB::raw("YEAR(m.fec_not)"), [2022, 2023])
        ->groupBy('m.tip_ide_', 'm.num_ide_', 'm.pri_nom_', 'm.seg_nom_', 'm.pri_ape_', 'm.seg_ape_')
        ->paginate(200);

        $sivi = Sivigila::all();
      
        
        //$datos = MaestroSiv113::orderBy('cod_eve')->paginate();
        //$students2 = DB::table('maestroSiv113')
            //->paginate(15);

       // $hola = '1124544296';
        //$students2 = DB::connection('sqlsrv_1')->select('SELECT * FROM maestroSiv113 ');
;
       // $students3 = DB::select('SELECT * FROM sga..maestroSiv113' );
        //$students2 = Student::on('mysql2')->get();   
        //$datos['empleados']=Empleado::on('sqlsrv_1')->paginate(20); //aqui estoy guardando enla variable datos 
        
        
       // $students2 = DB::select('SELECT pri_nom_ , count(cod_sub) as hola FROM sga..maestroSiv113 WHERE estrato = 2 
        //GROUP BY pri_nom_ ' );
        //$students2 = Student::on('mysql2')->get();   
        
        return view('sivigila.index', compact('sivigilas','sivi'));


       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($num_ide_,$fec_not)
    { 
        $fecha_casteada = DB::connection('sqlsrv_1')->table('maestroSiv113')
        ->selectRaw("CONVERT(CHAR(10), '$fec_not', 105) as fec_not")
        // ->selectRaw("CAST(fec_not AS DATE) as fecha_casteada")
        ->where('num_ide_', $num_ide_)
        ->value('fec_not'); //ojo esto lo hiciste para el incomeedit5 pero lo vas  aimplementar pra lo demas

        
        $incomeedit = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CAST(num_ide_ AS VARCHAR)')
        ->where('num_ide_', $num_ide_)->value('num_ide_');

        
        $incomeedit1 = DB::connection('sqlsrv_1')->table('maestroSiv113')
        ->where('num_ide_', $num_ide_)
        ->whereRaw('CAST(fec_not AS DATE) = ?', [$fecha_casteada])
        ->first();

       
        $incomeedit2 = DB::connection('sqlsrv_1')->table('maestroSiv113')
        ->selectRaw("CONVERT(VARCHAR(10), '$fec_not', 105) as fec_not")
        ->selectRaw("CAST(fec_not AS DATE) as fecha_casteada")
        ->where('num_ide_', $num_ide_)
        ->VALUE('fecha_casteada');//GET() O VALUE() O FIRST()  //ULTIMO AÑO DE REPORTE ->selectRaw("'".$fec_not."' as fec_not") CAST(MAX(fec_not)AS DATE )

        $incomeedit3 = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('MAX(edad_)')
        ->where('num_ide_', $num_ide_)->VALUE('edad_'); //LA EDAD MAYOR ->selectRaw('MAX(edad_)')

        $incomeedit4 = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CAST(year(year)AS INT )')
        
        ->where('num_ide_', $num_ide_)
        ->whereRaw('CAST(fec_not AS DATE) = ?', [$fecha_casteada])
        ->VALUE('year'); //ULTIMO AÑO DE REPORTE O AÑO MAYOR ->selectRaw('MAX(CAST(year(year)AS INT ))')




       
       
        

        $incomeedit5 = DB:: connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CAST(semana AS INT)')
        ->where('num_ide_', $num_ide_)
        ->whereRaw('CAST(fec_not AS DATE) = ?', [$fecha_casteada])
        ->value('semana'); //semana mayor  de notificacion ->selectRaw('MAX(CAST(semana AS INT) )')

        $incomeedit6 = DB:: connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('m.descrip as mun')
        ->join('municipios as m', 'maestroSiv113.cod_mun_o', '=', 'm.codigoMunicipio' )
        ->join('departamentos', 'maestroSiv113.cod_dpto_o', '=', 'm.codigoDepartamento' )
        // ->join('maestroSiv113', 'departamentos.codigo', '=', 'maestroSiv113.cod_dpto_o')
        ->where('num_ide_', $num_ide_)->value('mun');
        //muetra el municipio

        $incomeedit7 = DB:: connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('departamentos.descrip as dep')
        ->join('departamentos', 'maestroSiv113.cod_dpto_o', '=', 'departamentos.codigo' )
        ->where('num_ide_', $num_ide_)->value('dep'); // MUESTRA EL DEPARTAMENTO

        $incomeedit8 = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CAST(fecha_nto_ AS DATE )')
        ->where('num_ide_', $num_ide_)->VALUE('fecha_nto_'); //PARA FECHA DE NACIMIENTO

        $incomeedit9 = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CAST(edad_ges AS INT )')
        ->where('num_ide_', $num_ide_)
        ->where('num_ide_', $num_ide_)
        ->whereRaw('CAST(fec_not AS DATE) = ?', [$fecha_casteada])
        
        ->VALUE('edad_ges'); //PARA EDAD EN MESES


        $incomeedit10 = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CONCAT(trim(cod_pre),trim(cod_sub)) ')
        // ->join('refIps as r', 'CONCAT(trim(cod_pre),trim(cod_sub))', '=', 'r.codigo' )
        ->where('num_ide_', $num_ide_)
        ->VALUE('ipspres');

        $income11 = DB::connection('sqlsrv_1')->table('refIps')->selectRaw('refIps.descrip as nombrepres ')
        // ->join('refIps as r', 'CONCAT(trim(cod_pre),trim(cod_sub))', '=', 'r.codigo' )
        ->where('refIps.codigo', $incomeedit10)
        
        ->value('nombrepres');

        $income12 =  DB::connection('sqlsrv_1')->table('refIps')->select('descrip')
        ->where('refIps.codigoDepartamento', 44)
        ->get();

        $incomeedit13 = DB::connection('sqlsrv_1')->table('maestroAfiliados')
        ->select(DB::raw("IIF(codigoAgente = 'EPSI04', 'subsidiado', 'contributivo') as tipo_afiliacion"))
        ->where('identificacion', $num_ide_)
        ->value('tipo_afiliacion');
        

        return view('sivigila.create',["incomeedit"=>$incomeedit,"incomeedit1"=>$incomeedit1,"incomeedit2"=>$incomeedit2,
         "incomeedit3"=>$incomeedit3,"incomeedit4"=>$incomeedit4,"incomeedit5"=>$incomeedit5,
         "incomeedit6"=>$incomeedit6,"incomeedit7"=>$incomeedit7,"incomeedit8"=>$incomeedit8,
         "incomeedit9"=>$incomeedit9, "incomeedit10"=>$incomeedit10, "income11"=>$income11,"income12"=>$income12 
         ,"incomeedit13"=>$incomeedit13]);
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
            'cod_eve' => 'required',
            'semana' => 'required',
            'fec_not' => 'required',
            'year' => 'required',
           
            'tip_ide_' => 'required',
            'num_ide_' => 'required',
            'pri_nom_' => 'required',
            
            'pri_ape_' => 'required',
            
            'edad_' => 'required',
            'sexo_' => 'required',
            
            'fecha_nto_' => 'required',
            'edad_ges' => 'required',
            'telefono_' => 'required',
           
            
            'Ips_seguimiento_Ambulatorio' => 'required',
            'Caso_confirmada_desnutricion_etiologia_primaria' => 'required',
            'Tipo_ajuste' => 'required',
            'Promedio_dias_oportuna_remision' => 'required',
            'Esquemq_complrto_pai_edad' => 'required',

            'Atecion_primocion_y_mantenimiento_res3280_2018' => 'required',
            'est_act_menor' => 'required',
            'tratamiento_f75' => 'required',
            'fecha_recibio_tratf75' => 'required',
            'nombreips_manejo_hospita' => 'required',
            

        ];

        $mensajes=[
            'required'=>'El :attribute es requerido',
      
        ];

        $this->validate($request, $campos, $mensajes);
        
            $entytistore = new Sivigila;
            $entytistore->cod_eve = $request->cod_eve;
            $entytistore->semana = $request->semana;
            $entytistore->fec_not = $request->fec_not;
            $entytistore->year = $request->year;
            $entytistore->dpto = $request->dpto;
            $entytistore->mun = $request->mun;
            $entytistore->tip_ide_ = $request->tip_ide_;
            $entytistore->num_ide_ = $request->num_ide_;
            $entytistore->pri_nom_ = $request->pri_nom_;
                    
            $entytistore->seg_nom_ = $request->seg_nom_;
            $entytistore->pri_ape_ = $request->pri_ape_;
            $entytistore->seg_ape_ = $request->seg_ape_;
            $entytistore->edad_ = $request->edad_;
            $entytistore->sexo_ = $request->sexo_;
            $entytistore->fecha_nto_ = $request->fecha_nto_;
            $entytistore->edad_ges = $request->edad_ges;
            $entytistore->telefono_ = $request->telefono_;
            $entytistore->nom_grupo_ = $request->nom_grupo_;
            $entytistore->regimen = $request->regimen;
            $entytistore->Ips_at_inicial = $request->Ips_at_inicial;
            $entytistore->estado = 1;
            $entytistore->fecha_aten_inicial = $request->fecha_aten_inicial;
            
            $entytistore->Ips_seguimiento_Ambulatorio = $request->Ips_seguimiento_Ambulatorio;
            $entytistore->Caso_confirmada_desnutricion_etiologia_primaria = $request->Caso_confirmada_desnutricion_etiologia_primaria;
            $entytistore->Tipo_ajuste = $request->Tipo_ajuste;
            $entytistore->Promedio_dias_oportuna_remision = $request->Promedio_dias_oportuna_remision;
            $entytistore->Esquemq_complrto_pai_edad = $request->Esquemq_complrto_pai_edad;
            $entytistore->Atecion_primocion_y_mantenimiento_res3280_2018 = $request->Atecion_primocion_y_mantenimiento_res3280_2018;
            $entytistore->est_act_menor = $request->est_act_menor;
            $entytistore->tratamiento_f75 = $request->tratamiento_f75;
            $entytistore->fecha_recibio_tratf75 = $request->fecha_recibio_tratf75;
            $entytistore->nombreips_manejo_hospita = $request->nombreips_manejo_hospita;
            $entytistore->user_id = auth()->user()->id;
        
            $entytistore->save();
            return redirect()->route('sivigila.index')
           ->with('mensaje',' El dato fue agregado a la base de datos Exitosamente..!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sivigila  $empleado
     * @return \Illuminate\Http\Response
     */
    public function show(Sivigila $empleado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sivigila  $empleado
     * @return \Illuminate\Http\Response
     */
    public function edit($num_ide_)
    {
        
     

        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sivigila  $empleado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sivigila $empleado)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sivigila  $empleado
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sivigila $empleado)
    {
        //
    }

    public function search(Request $request)
{

        $query = $request->get('q');
 
    

        $sivigilas = DB::connection('sqlsrv_1')->table('maestroSiv113')
        ->select(DB::raw("CAST(MAX(fec_not) AS DATE) as fec_noti,tip_ide_,num_ide_,pri_nom_,seg_nom_,
        pri_ape_,seg_ape_"))
        ->where('cod_eve', 113)
       
        ->where('num_ide_', 'LIKE', '%'.$query.'%')
        ->groupBy('tip_ide_ ','num_ide_','pri_nom_','seg_nom_','pri_ape_','seg_ape_')
        
        ->paginate(15);

        // return response()->json($posts);

        // $query = $request->input('query');
        // $data = DB::connection('sqlsrv')->table('sivigilas')->selectRaw('num_ide_')
        // ->where('num_ide_', 'LIKE', "%{$query}%")->get();
        // return response()->json($data);


        return view('sivigila.index', ['sivigilas' => $sivigilas ,'query' => $query]);
    }


    public function reporte1()
    {   
        return Excel::download(new SivigilaExport, 'sivigila.xls');


    }

}
