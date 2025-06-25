<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;          // ✅  ESTA línea basta
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
// use DataTables;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;



class SivigilaController extends Controller
{


    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        // $this->middleware('auth');
        $this->middleware('Admin_sivigila', ['only' =>'create']);
         $this->middleware('Admin_sivigila', ['only' =>'index']);
        $this->middleware('Admin_sivigila', ['only' =>'show']);
        $this->middleware('auth')->except('index_api');
        
       

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 1) Distintos años para filtro
        $years = DB::connection('sqlsrv_1')
            ->table('maestroSiv113')
            ->selectRaw('YEAR(fec_not) AS year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // tus contadores…
        // (reproduzco tu lógica resumida)
        $missing = DB::connection('sqlsrv_1')
            ->table('maestroSiv113 as m')
            ->select('m.tip_ide_','m.num_ide_','m.semana','m.fec_not')
            ->where('m.cod_eve',113)
            ->groupBy('m.tip_ide_','m.num_ide_','m.semana','m.fec_not')
            ->leftJoin('DESNUTRICION.dbo.sivigilas as b', function($j){
                $j->on('m.num_ide_','b.num_ide_')
                  ->on('m.fec_not','b.fec_not');
            })
            ->whereNull('b.num_ide_')
            ->whereYear('b.created_at','>',2023)
            ->get();
        $countMissing = $missing->count();

        $sivi2 = DB::table('sivigilas')
            ->whereYear('fec_not',2024)
            ->count();

        $count123 = $sivi2 - $countMissing;

        $resultados = DB::connection('sqlsrv_1')
            ->table(DB::raw("(
                SELECT 
                  CAST(m.fec_not AS DATE) AS fec_noti,
                  m.tip_ide_,m.num_ide_,m.pri_nom_,m.seg_nom_,
                  m.pri_ape_,m.seg_ape_,m.semana,MAX(m.nom_upgd) AS nom_upgd
                FROM maestroSiv113 AS m
                WHERE m.cod_eve=113
                  AND YEAR(m.fec_not)>=2024
                GROUP BY m.fec_not,m.semana,m.tip_ide_,
                         m.num_ide_,m.pri_nom_,m.seg_nom_,
                         m.pri_ape_,m.seg_ape_
            ) as derived"))
            ->count();

        // notificaciones
        if (auth()->user()->usertype==2) {
            $conteo = Seguimiento::where('estado',1)
                        ->where('user_id',auth()->id())
                        ->count();
        } else {
            $conteo = Seguimiento::where('estado',1)->count();
        }

        // pase al view
        return view('sivigila.index', compact(
            'years','conteo','sivi2',
            'count123','resultados'
        ));
    }

   
    public function data(Request $request)
{
    $year = $request->get('year');

    $query = DB::connection('sqlsrv_1')
        ->table('DESNUTRICION.dbo.vSiv113CargaOptima as v')
        ->select([
            'v.fec_noti',
            'v.semana',
            'v.tip_ide_',
            'v.num_ide_',
            'v.pri_nom_',
            'v.seg_nom_',
            'v.pri_ape_',
            'v.seg_ape_',
            'v.nom_upgd',
            'v.procesado'
        ])
        ->when($year, fn($q) => $q->whereYear('v.fec_noti', $year));

    return DataTables::of($query)
        ->addColumn('acciones', function ($row) {
            if ($row->procesado) {
                return '<button class="btn btn-secondary btn-sm" disabled>
                          <i class="fas fa-stop"></i> Procesado
                        </button>';
            }
            // ya es YYYY-MM-DD, sin time
            $url = url("sivigila/{$row->num_ide_}/{$row->fec_noti}/create");
            return '<a href="'. e($url) .'" class="btn btn-success btn-sm">
                      <i class="fas fa-zoom-in"></i> Seguimiento
                    </a>';
        })
        ->rawColumns(['acciones'])
        ->make(true);
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
        // 1) Validación
        $request->validate([
            'cod_eve'                                      => 'required',
            'semana'                                       => 'required',
            'fec_not'                                      => 'required|date',
            'year'                                         => 'required',
            'tip_ide_'                                     => 'required',
            'num_ide_'                                     => 'required',
            'pri_nom_'                                     => 'required',
            'pri_ape_'                                     => 'required',
            'edad_'                                        => 'required',
            'sexo_'                                        => 'required',
            'fecha_nto_'                                   => 'required|date',
            'edad_ges'                                     => 'required|numeric|min:0.01',
            'telefono_'                                    => 'required',
            'user_id'                                      => 'required|exists:users,id',
            'Caso_confirmada_desnutricion_etiologia_primaria' => 'required',
            'nombreips_manejo_hospita'                     => 'required',
        ], [
            'required' => 'El :attribute es requerido',
            'min'      => 'El :attribute debe ser mayor que cero',
        ]);

        // 2) Crear el registro (mass assignment; asegúrate de tener $fillable en tu modelo)
        $data = $request->only([
            'cod_eve','semana','fec_not','year',
            'tip_ide_','num_ide_','pri_nom_','seg_nom_',
            'pri_ape_','seg_ape_','edad_','sexo_',
            'fecha_nto_','edad_ges','telefono_','nom_grupo_',
            'regimen','Ips_at_inicial','fecha_aten_inicial',
            'Caso_confirmada_desnutricion_etiologia_primaria',
            'Ips_manejo_hospitalario','nombreips_manejo_hospita',
            'user_id'
        ]);
        $data['estado'] = 1;

        $sivigila = Sivigila::create($data);

        // 3) Construir el body del mail
        $bodyText = '<br>';
        $results = Sivigila::select('fec_not','num_ide_','pri_nom_','seg_nom_','pri_ape_','seg_ape_')
            ->where('num_ide_', $sivigila->num_ide_)
            ->where('fec_not', $sivigila->fec_not)
            ->get();

        foreach ($results as $r) {
            $bodyText .= "Fecha de notificación: <strong>{$r->fec_not}</strong><br>";
            $bodyText .= "Identificación: <strong>{$r->num_ide_}</strong><br>";
            $bodyText .= "Primer Nombre: <strong>{$r->pri_nom_}</strong><br>";
            $bodyText .= "Segundo Nombre: <strong>{$r->seg_nom_}</strong><br>";
            $bodyText .= "Primer Apellido: <strong>{$r->pri_ape_}</strong><br>";
            $bodyText .= "Segundo Apellido: <strong>{$r->seg_ape_}</strong><br>";
        }

        // 4) Preparar y enviar correo
        $mailSent = false;
        $user = User::find($sivigila->user_id);

        if ($user && $user->email) {
            $loginUrl = url('login');
            $html = 'FAVOR NO CONTESTAR ESTE MENSAJE<br>'
                  . 'Hola, te acaban de asignar un paciente de desnutrición (EVENTO 113)por parte de la EPSI Anas Wayuu.<br>'
                  . 'Por favor, gestiona lo antes posible ingresando al siguiente enlace:<br>'
                  . "<a href=\"{$loginUrl}\">Ingresar al sistema</a>"
                  . $bodyText;

            // SMTP SSL en puerto 465
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
                Log::info("Correo enviado a {$user->email} para nuevo registro sivigila ID {$sivigila->id}");
            } catch (\Throwable $e) {
                Log::warning("Error SMTP al enviar a {$user->email}: {$e->getMessage()}");
            }
        } else {
            Log::warning("No se envía correo: usuario ID {$sivigila->user_id} sin email o no encontrado");
        }

        // 5) Redirigir con feedback
        $msg = $mailSent
            ? 'El dato fue agregado y el correo se envió correctamente.'
            : 'El dato fue agregado, pero no se pudo enviar el correo.';
        return redirect()
            ->route('sivigila.index')
            ->with('mensaje', $msg);
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



public function index_api(Request $request)
{
   // Consulta básica (puedes agregar filtros más adelante)
   $query = DB::connection('sqlsrv_1')
   ->table('maestroafiliados')
   ->limit(10); // 🔹 Aquí limitas a 10 resultados

// Ejecuta y devuelve como JSON
$datos = $query->get();

return response()->json([
   'status' => 'success',
   'data' => $datos
], 200);
}



        }

   


    

