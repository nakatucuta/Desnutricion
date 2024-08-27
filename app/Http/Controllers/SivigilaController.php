<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MaestroSiv113;
use App\Models\Sivigila;
use App\Models\Seguimiento;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SivigilaExport;
use App\Exports\sivigilaslpExport;
use App\Exports\reportesinseguimientos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use DataTables;


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


        

        // con max
        // $sivigilas = DB::connection('sqlsrv_1')
        // ->table('maestroSiv113 AS m')
        // ->select(DB::raw("CAST(MAX(m.fec_not) AS DATE) as fec_noti, m.tip_ide_, m.num_ide_, m.pri_nom_, m.seg_nom_, m.pri_ape_, m.seg_ape_"))
        // ->where('m.cod_eve', 113)
 
        // ->whereBetween(DB::raw("YEAR(m.fec_not)"), [2022, 2023])
 
        // ->whereBetween(DB::raw("YEAR(m.fec_not)"), [2023, 2023])
 
        // ->groupBy('m.tip_ide_', 'm.num_ide_', 'm.pri_nom_', 'm.seg_nom_', 'm.pri_ape_', 'm.seg_ape_')
        //  ->orderBy('fec_noti', 'desc')
        // ->paginate(10000);

        
        
       
        


        

       $sivigilas = DB::connection('sqlsrv_1')
    ->table('maestroSiv113 AS m')
    ->select(DB::raw("
        CAST(m.fec_not AS DATE) AS fec_noti,
        m.tip_ide_,
        m.num_ide_,
        m.pri_nom_,
        m.seg_nom_,
        m.pri_ape_,
        m.seg_ape_,
        m.semana,
        MAX(m.nom_upgd) AS nom_upgd
    "))
    ->join(DB::raw("
        (SELECT 
            m.tip_ide_, 
            m.num_ide_, 
            MAX(m.fec_not) AS fec_not, 
            m.semana
        FROM 
            maestroSiv113 AS m
        GROUP BY 
            m.tip_ide_, 
            m.num_ide_, 
            m.semana
        ) AS s
    "), function($join) {
        $join->on('m.tip_ide_', '=', 's.tip_ide_')
            ->on('m.num_ide_', '=', 's.num_ide_')
            ->on('m.semana', '=', 's.semana')
            ->on('m.fec_not', '=', 's.fec_not');
    })
    ->where('m.cod_eve', 113)
    ->whereYear('m.fec_not', '>=', 2024)
    ->groupBy(
        'm.semana', 
        'm.tip_ide_', 
        'm.num_ide_', 
        'm.fec_not', 
        'm.pri_nom_', 
        'm.seg_nom_', 
        'm.pri_ape_', 
        'm.seg_ape_'
    )
    ->get();

   

       
       $T1 = DB::connection('sqlsrv_1')->table('maestroSiv113 AS m')
       ->select(DB::raw("CAST(m.fec_not AS DATE) AS fec_noti, m.tip_ide_, m.num_ide_, m.pri_nom_, m.seg_nom_, m.pri_ape_, m.seg_ape_"))
       ->where('m.cod_eve', 113)
       ->whereBetween(DB::raw("YEAR(m.fec_not)"), [2023, 2023])
       ->groupBy('m.fec_not', 'm.tip_ide_', 'm.num_ide_', 'm.pri_nom_', 'm.seg_nom_', 'm.pri_ape_', 'm.seg_ape_');
   
   $results = DB::connection('sqlsrv_1')->table(DB::raw("({$T1->toSql()}) as A"))
       ->mergeBindings($T1)
       ->leftJoin('DESNUTRICION.dbo.sivigilas AS B', function ($join) {
           $join->on('A.fec_noti', '=', 'B.fec_not')
               ->on('A.num_ide_', '=', 'B.num_ide_');
       })
       ->whereNull('B.num_ide_')
       ->whereYear('B.created_at', '>', 2023)
       ->get();
   
       
        $count1234 = $results->count();
        $sivi2 = DB::table('sivigilas')
        
        ->whereBetween(DB::raw('YEAR(fec_not)'), [2024, 2024])
        ->count('id');
        $count123 =  $sivi2 - $count1234;

        $totalFilas = DB::connection('sqlsrv_1')
    ->table('maestroSiv113 AS m')
    ->where('m.cod_eve', 113)
    ->whereBetween(DB::raw('YEAR(m.fec_not)'), [2024, 2024])
    ->count();
    
    
    // Obtener el total de filas
    $resultados = DB::connection('sqlsrv_1')
    ->table(DB::raw("(
        SELECT 
            CAST(m.fec_not AS DATE) AS fec_noti,
            m.tip_ide_,
            m.num_ide_,
            m.pri_nom_,
            m.seg_nom_,
            m.pri_ape_,
            m.seg_ape_,
            m.semana,
            MAX(m.nom_upgd) AS nom_upgd
        FROM 
            maestroSiv113 AS m
        INNER JOIN (
            SELECT 
                m.tip_ide_, 
                m.num_ide_, 
                MAX(m.fec_not) AS fec_not, 
                m.semana
            FROM 
                maestroSiv113 AS m
            GROUP BY 
                m.tip_ide_, 
                m.num_ide_, 
                m.semana
        ) AS s ON m.tip_ide_ = s.tip_ide_
              AND m.num_ide_ = s.num_ide_
              AND m.semana = s.semana
              AND m.fec_not = s.fec_not
        WHERE 
            m.cod_eve = 113
            AND YEAR(m.fec_not) >= 2024
        GROUP BY 
            m.semana, 
            m.tip_ide_, 
            m.num_ide_, 
            m.fec_not, 
            m.pri_nom_, 
            m.seg_nom_, 
            m.pri_ape_, 
            m.seg_ape_
    ) AS derived"))
    ->count();
    
        
        



        
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
        
        
        if (Auth::User()->usertype == 2) {
            $conteo = Seguimiento::where('estado', 1)
                        ->where('user_id', Auth::user()->id)
                        ->count('id');
            }else{
                $conteo = Seguimiento::where('estado', 1)->count('id');
    
            }
            $otro =  Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
            'sivigilas.pri_ape_','sivigilas.seg_ape_','sivigilas.id as idin','sivigilas.Ips_at_inicial',
            'seguimientos.id','seguimientos.fecha_proximo_control','seguimientos.estado as est',
            'seguimientos.user_id as usr')
            ->orderBy('seguimientos.created_at', 'desc')
            ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
            // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
            ->where('seguimientos.estado',1)
            ->get();
        
        return view('sivigila.index', compact('sivigilas','sivi','conteo','otro','count123','sivi2','resultados','totalFilas'));


       
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

        $incomeedit8 = DB::connection('sqlsrv_1')
        ->table('maestroSiv113')
        ->selectRaw('CAST(fecha_nto_ AS DATE )')
        ->where('num_ide_', $num_ide_)
        ->whereNotNull('fecha_nto_')
        ->where('fecha_nto_', '<>', '')
        ->whereRaw("ISDATE(fecha_nto_) = 1") // Agrega esta línea para filtrar solo valores válidos
        ->value('fecha_nto_');
    

        $incomeedit9 = DB::connection('sqlsrv_1')
        ->table('maestroSiv113')
        ->selectRaw("CASE WHEN ISNULL(edad_ges, '') != '' THEN CAST(REPLACE(edad_ges, ',', '.') AS DECIMAL(10,1)) ELSE COALESCE(NULLIF(edad_ges, ''), 0) END AS edad_ges")
        ->where('num_ide_', $num_ide_)
        ->where('num_ide_', $num_ide_)
        ->whereRaw('CAST(fec_not AS DATE) = ?', [$fecha_casteada])
        ->value('edad_ges');
    
    
    
    


        $incomeedit10 = DB::connection('sqlsrv_1')->table('maestroSiv113')->selectRaw('CONCAT(trim(cod_pre),trim(cod_sub)) ')
        // ->join('refIps as r', 'CONCAT(trim(cod_pre),trim(cod_sub))', '=', 'r.codigo' )
        ->whereRaw('CAST(fec_not AS DATE) = ?', [$fecha_casteada])
        ->where('num_ide_', $num_ide_)
        ->VALUE('ipspres');

        $income11 = DB::connection('sqlsrv_1')->table('refIps')->selectRaw('refIps.descrip as nombrepres ')
        // ->join('refIps as r', 'CONCAT(trim(cod_pre),trim(cod_sub))', '=', 'r.codigo' )
      
        ->where('refIps.codigo', $incomeedit10)
        
        ->value('nombrepres');

      

        $incomeedit13 = DB::connection('sqlsrv_1')->table('maestroAfiliados')
        ->select(DB::raw("IIF(codigoAgente = 'EPSI04', 'subsidiado', 'contributivo') as tipo_afiliacion"))
        ->where('identificacion', $num_ide_)
        ->value('tipo_afiliacion');

        $incomeedit14 = DB::connection('sqlsrv_1')->table('maestroAfiliados as a')
        ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
        ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
        ->join('maestroIpsGruDet as d', function($join) {
            $join->on('c.id', '=', 'd.idd')
                 ->where('d.servicio', '=', 1);
        })
        ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
        ->select(DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'))
        ->where('a.identificacion', $num_ide_)
        ->first(); // Obtener el primer registro de la consulta
    
        if ($incomeedit14 !== null) {
            $income12 = DB::table('users')
                ->select('name', 'id', 'codigohabilitacion')
                ->where('codigohabilitacion', $incomeedit14->codigo_habilitacion)
                
                ->get();
            // Resto del código que maneja el resultado de la segunda consulta...
        } else {
            // Asignar un valor predeterminado a $income12 si $incomeedit14 es null
            $income12 = [];
        }



        $incomeedit15 =  DB::table('users')->select('name', 'id','codigohabilitacion')
        // ->where('usertype', 2)
        ->get();
        // $incomeedit15 = DB::connection('sqlsrv_1')->table('maestroIpsGru as a')
        // ->join('maestroIpsGruDet as b', function ($join) {
        //     $join->on('a.id', '=', 'b.idd')
        //          ->where('b.servicio', '=', 1);
        // })
        // ->join('refIps as c', 'b.idIps', '=', 'c.idIps')
        // ->select('c.codigo as cod1', 'a.descrip as nomipsprim')
        // ->get(); // Obtener solo las ips  primaria 

        $incomeedit16 = DB::connection('sqlsrv_1')
                ->table('refIps')
                ->select('refIps.descrip as nombrepres', 'refIps.codigo as cod')
                ->where('codigoDepartamento', 44)
                ->orWhere('codigoDepartamento', 47)
                ->orWhere('codigoDepartamento', 8)
                ->get();


        return view('sivigila.create',["incomeedit"=>$incomeedit,"incomeedit1"=>$incomeedit1,"incomeedit2"=>$incomeedit2,
         "incomeedit3"=>$incomeedit3,"incomeedit4"=>$incomeedit4,"incomeedit5"=>$incomeedit5,
         "incomeedit6"=>$incomeedit6,"incomeedit7"=>$incomeedit7,"incomeedit8"=>$incomeedit8,
         "incomeedit9"=>$incomeedit9, "incomeedit10"=>$incomeedit10, "income11"=>$income11,"income12"=>$income12 
         ,"incomeedit13"=>$incomeedit13,"incomeedit14"=>$incomeedit14,"incomeedit15"=>$incomeedit15 ,"incomeedit16"=>$incomeedit16]);
    }


    public function create1()
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
        // ->where('a.identificacion', $num_ide_)
        ->first(); // Obtener el primer registro de la consulta
    
        if ($incomeedit14 !== null) {
            $income12 = DB::table('users')
                ->select('name', 'id', 'codigohabilitacion')
                ->where('codigohabilitacion', $incomeedit14->codigo_habilitacion)
                
                ->get();
            // Resto del código que maneja el resultado de la segunda consulta...
        } else {
            // Asignar un valor predeterminado a $income12 si $incomeedit14 es null
            $income12 = [];
        }



        $incomeedit15 =  DB::table('users')->select('name', 'id','codigohabilitacion')
        // ->where('usertype', 2)
        ->get();

        $incomeedit16 = DB::connection('sqlsrv_1')
        ->table('refIps')
        ->select('refIps.descrip as nombrepres', 'refIps.codigo as cod')
        ->where('codigoDepartamento', 44)
        ->orWhere('codigoDepartamento', 47)
        ->orWhere('codigoDepartamento', 8)
        ->get();
        return view('sivigila.create1',["income12"=>$income12 
        ,"incomeedit14"=>$incomeedit14,"incomeedit15"=>$incomeedit15,"incomeedit16"=>$incomeedit16]);

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
            'edad_ges' => 'required|numeric|min:0.01',
            
            'user_id' => 'required',
            'Caso_confirmada_desnutricion_etiologia_primaria' => 'required',
            
           
            
            
            
            
            'nombreips_manejo_hospita' => 'required',
            

        ];

        $mensajes=[
            'required'=>'El :attribute es requerido',
            'min' => 'El :attribute debe ser mayor que CERO :min',
      
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
            
            // $entytistore->Ips_seguimiento_Ambulatorio = $request->Ips_seguimiento_Ambulatorio;
            $entytistore->Caso_confirmada_desnutricion_etiologia_primaria = $request->Caso_confirmada_desnutricion_etiologia_primaria;
            $entytistore->Ips_manejo_hospitalario = $request->Ips_manejo_hospitalario;
            
            $entytistore->nombreips_manejo_hospita = $request->nombreips_manejo_hospita;
            $entytistore->user_id = $request->user_id;
        
            $entytistore->save();

            //para enviarle una consulta al correo 
            // aqui empieza el tema de envio de correos entonces si el estado es 1
            //creamos una consulta
            $results = DB::table('sivigilas')
            ->select('num_ide_', 'pri_nom_','seg_nom_','pri_ape_','seg_ape_','fec_not')
            ->where('num_ide_', $request->num_ide_)
            ->where('fec_not', $request->fec_not)
            ->get();
            
             $bodyText = ':<br>';
             
            foreach ($results as $result) {
            $bodyText .= 'Fecha de notificacion: ' .'<strong>' . $result->fec_not . '</strong><br>';
            $bodyText .= 'Identificación: ' .'<strong>' . $result->num_ide_ . '</strong><br>';
            $bodyText .= 'Primer Nombre: ' .'<strong>' . $result->pri_nom_ . '</strong><br>';
            $bodyText .= 'Segundo Nombre: ' .'<strong>' . $result->seg_nom_ . '</strong><br>';
            $bodyText .= 'Primer Apellido: ' .'<strong>' . $result->pri_ape_ . '</strong><br>';
            $bodyText .= 'Segundo Apellido: ' .'<strong>' . $result->seg_ape_ . '</strong><br>';
              }
            //aqui termina la consulta que enviaremos al cuerpo del correo

             

             
           $transport = new EsmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION'));
           $transport->setUsername(env('MAIL_USERNAME'))
                     ->setPassword(env('MAIL_PASSWORD'));
           
           $mailer = new Mailer($transport);
           
           $email = (new Email())
                   ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                   ->to(new Address(User::find($request->user_id)->email))
                   ->subject('Recordatorio de control')
                   ->html('FAVOR NO CONTESTAR ESTE MENSAJE <br>
                    Hola, te acaban de asignar un paciente de desnutricion por parte de la
                   EPSI anas wayuu, se solicita gestionarlo lo antes posible ingresando a este enlace <br>
                   http://app.epsianaswayuu.com/Desnutricion/public/login'.$bodyText);
                   if ($mailer->send($email)) {
            return redirect()->route('sivigila.index')
           ->with('mensaje',' El dato fue agregado a la base de datos Exitosamente..!');
                   }else{
                    return redirect()->route('sivigila.index')
           ->with('mensaje',' El dato fue agregado a la base de datos Exitosamente..!');
            
                   }
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

    public function reSporte2()
    {   
        return Excel::download(new sivigilaslpExport, 'sivigila.xls');


    }


    public function reSporte_sinseguimiento()
    {   
        return Excel::download(new reportesinseguimientos, 'sin_seguimiento.xls');


    }





    // public function import()
    // {
    //     $filePath = 'C:\xampp\htdocs\Desnutricion\storage\app\sivigilas.txt';
    //     $file = fopen($filePath, 'r');
    
    //     while (($data = fgetcsv($file, 0, "\t")) !== false) {
    //         if (count($data) == 29) { // Verificar que el array tenga 29 elementos
    //             $cod_eve = intval($data[0]); // Convertir a entero
    
    //             $fec_not = date_create($data[2]);
    //             $fecha_aten_inicial = date_create($data[20]);
    
    //             $telefono = strval(trim($data[15], '"')); // Eliminar las comillas dobles
    
    //             if ($cod_eve && $fec_not && $fecha_aten_inicial && is_numeric($telefono)) {
    //                 $record = [
    //                     'cod_eve' => $cod_eve,
    //                     'semana' => intval($data[1]),
    //                     'fec_not' => date_format($fec_not, 'Y-m-d'),
    //                     'year' => $data[3], // Mantener como nvarchar
    //                     'dpto' => $data[4],
    //                     'mun' => $data[5],
    //                     'tip_ide_' => $data[6],
    //                     'num_ide_' => $data[7],
    //                     'pri_nom_' => $data[8],
    //                     'seg_nom_' => $data[9],
    //                     'pri_ape_' => $data[10],
    //                     'seg_ape_' => $data[11],
    //                     'edad_' => intval($data[13]), // Utilizar el índice correcto para el campo 'edad_'
    //                     'sexo_' => $data[12],
    //                     'fecha_nto_' => $data[14],
    //                     'edad_ges' => intval($data[16]),
    //                     'telefono_' => $telefono, // Usar el valor convertido
    //                     'nom_grupo_' => $data[17],
    //                     'regimen' => $data[18],
    //                     'Ips_at_inicial' => $data[19],
    //                     'estado' => intval($data[21]), // Convertir a entero
    //                     'fecha_aten_inicial' => date_format($fecha_aten_inicial, 'Y-m-d'),
    //                     'Caso_confirmada_desnutricion_etiologia_primaria' => $data[22],
    //                     'Ips_manejo_hospitalario' => intval($data[23]), // Convertir a entero
    //                     'Esquemq_complrto_pai_edad' => $data[24],
    //                     'Atecion_primocion_y_mantenimiento_res3280_2018' => $data[25],
    //                     'nombreips_manejo_hospita' => $data[26],
    //                     'user_id' => intval($data[27]),
    //                     'created_at' => date('Y-m-d H:i:s', strtotime($data[28])),
    //                     'updated_at' => date('Y-m-d H:i:s', strtotime($data[29])),
    //                 ];
    
    //                 // Insertar el registro en la base de datos
    //                 Sivigila::create($record);
    //             } else {
    //                 // Manejar el caso en el que los datos no son válidos
    //                 // Por ejemplo, puedes omitir la inserción o registrar un error
    //             }
    //         } else {
    //             // Manejar el caso en el que el número de elementos no es el esperado
    //             // Por ejemplo, puedes omitir la inserción o registrar un error
    //         }
    //     }
    
    //     fclose($file);
    
    //     return 'Datos importados exitosamente.';
    // }

    

    public function downloadPdf()
    {
        $pdfPath = 'C:/xampp/htdocs/Desnutricion/public/img/manual_de_usario.pdf'; // Ruta al archivo PDF en tu servidor
        $pdfName = 'manual_de_usario.pdf'; // Nombre del archivo que se descargará

        return Response::download($pdfPath, $pdfName);
    }
    

   
  

    public function getData1(Request $request)
    {
        $query = DB::connection('sqlsrv_1')
        ->table('maestroSiv113 AS m')
        ->select(DB::raw("CAST(m.fec_not AS DATE) as fec_noti, m.tip_ide_, m.num_ide_, m.pri_nom_, m.seg_nom_, m.pri_ape_, m.seg_ape_, m.semana,m.nom_upgd"))
        ->where('m.cod_eve', 113)
        ->whereBetween(DB::raw("YEAR(m.fec_not)"), [2024, 2024]);
        return DataTables::of($query)
             ->make(true);

    }


    public function checkStatus(Request $request)
{
    $num_ide = $request->input('num_ide');
    $fec_noti = $request->input('fec_noti');

    $exists = DB::connection('sqlsrv_1')->table('maestroSiv113')
        ->where('num_ide_', $num_ide)
        ->exists() && 
        DB::connection('sqlsrv')->table('sivigilas')
        ->where('num_ide_', $num_ide)
        ->where('fec_not', $fec_noti)
        ->exists();

    return response()->json(['processed' => $exists]);
}





    // En tu controlador de Laravel

// public function getData()
// {
//     $data = DB::connection('sqlsrv_1')
//         ->table('maestroAfiliados as a')
//         ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
//         ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
//         ->join('maestroIpsGruDet as d', function ($join) {
//             $join->on('c.id', '=', 'd.idd')
//                 ->where('d.servicio', '=', 1);
//         })
//         ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
//         ->select('a.identificacion', 'a.fec_noti', 'a.semana', 'a.tip_ide_', 'a.num_ide_', 'a.pri_nom_', 'a.seg_nom_', 'a.pri_ape_', 'a.seg_ape_', 'a.nom_upgd', DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'))
//         ->whereYear('a.fecha_documento', '>=', 2023)
//         ->get();

//     foreach ($data as $item) {
//         $income12 = DB::table('users')
//             ->select('name')
//             ->where('codigohabilitacion', $item->codigo_habilitacion)
//             ->first();

//         if ($income12 === null) {
//             $item->ips_primaria = 'Sin datos, NO ASIGNAR hasta confirmar prestador primario';
//         } else {
//             $item->ips_primaria = $income12->name;
//         }
//     }

//     return response()->json(['data' => $data]);
// }



    

        }

   


    

