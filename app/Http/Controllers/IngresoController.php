<?php

namespace App\Http\Controllers;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sivigila;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IngresoExport;
use App\Http\Middleware\Admin_ingreso;
use PDF;
use Storage;
use Illuminate\Support\Facades\Auth;
class IngresoController extends Controller
{

    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        $this->middleware('auth');
      
        $this->middleware('Admin_ingreso_edit', ['only' =>'edit']);
        $this->middleware('Admin_ingreso_edit', ['only' =>'reporte']);
        $this->middleware('Admin_ingreso_destroy', ['only' =>'destroy']);
        $this->middleware('Admin_ingreso', ['only' =>'edit']);
        $this->middleware('Admin_nutric_ingres', ['only' =>'destroy']);
        
       
 
        /*$this->middleware('edit_product', ['only' =>'edit']);
        */
        
        
       

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        $busqueda = $request->busqueda;
        $user_id = Auth::User()->usertype;
        $user_id1 = Auth::User()->id == '2';


        if (Auth::User()->usertype == 2) {
            $students2 = Ingreso::select('pri_nom_','ingresos.id','seg_nom_','pri_ape_','Fecha_ingreso_ingres','num_ide_','Nom_ips_at_prim')
                ->orderBy('ingresos.created_at', 'desc')
                
                ->join('sivigilas as m', 'ingresos.sivigilas_id', '=', 'm.id' )
                ->join('users as u', 'ingresos.user_id', '=', 'u.id' )
                ->where('ingresos.user_id', Auth::User()->id )
                // ->where('u.id', '=', DB::raw('ingresos.user_id'))
                ->paginate(3000);
        } else {

            $students2 = Ingreso::select('pri_nom_','ingresos.id','seg_nom_','pri_ape_','Fecha_ingreso_ingres','num_ide_','Nom_ips_at_prim')
                ->orderBy('ingresos.created_at', 'desc')
                
                ->join('sivigilas as m', 'ingresos.sivigilas_id', '=', 'm.id' )
                ->paginate(3000);
            //$students2 = collect(); // Colección vacía si el usuario no es el usuario con id 2
        }
     

        $datos['ingreso']=Ingreso::paginate(20); //aqui estoy guardando enla variable datos 
        return view('ingreso.index',$datos,["master"=>$students2]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // $incomeedit = \DB::table('sivigilas')
        // ->select( \DB::raw('CAST(num_ide_ AS INT)as nene')
        // ,'sivigilas.id')->get()->pluck('id','nene');

        // $incomeedit = DB::connection('sqlsrv')->table('sivigilas')
        // ->get()->pluck('id','num_ide_');
        $incomeedit = Sivigila::all()->where('estado',1);
        
        $income12 =  DB::connection('sqlsrv_1')->table('refIps')->select('descrip')
        ->where('refIps.codigoDepartamento', 44)
        ->get();

        return view('ingreso.create',compact('incomeedit'),["income12"=>$income12]);
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
            'Fecha_ingreso_ingres' => 'required',
            'peso_ingres' => 'required',
            'talla_ingres' => 'required',
            'puntaje_z' => 'required',
            'calificacion' => 'required',
            'Edema' => 'required',
            'Emaciacion' => 'required',
            'perimetro_brazo' => 'required',
            'interpretacion_p_braqueal' => 'required',
            'requ_energia_dia' => 'required',
            'mes_entrega_FTLC' => 'required',
            'fecha_entrega_FTLC' => 'required',
            'Menor_anos_des_aguda' => 'required',
            'medicamentos' => 'required',

            'remite_alguna_inst_apoyo' => 'required',
            'Nom_ips_at_prim' => 'required',
            'sivigilas_id' => 'required',
            

        ];

        $mensajes=[
            'required'=>'El :attribute es requerido',
      
        ];

        $this->validate($request, $campos, $mensajes);


            $entytistore = new Ingreso;
            $entytistore->Fecha_ingreso_ingres = $request->Fecha_ingreso_ingres;
            $entytistore->peso_ingres = $request->peso_ingres;
            $entytistore->talla_ingres = $request->talla_ingres;
            $entytistore->puntaje_z = $request->puntaje_z;
            $entytistore->calificacion = $request->calificacion;
            $entytistore->Edema = $request->Edema;
            $entytistore->Emaciacion = $request->Emaciacion;
            $entytistore->perimetro_brazo = $request->perimetro_brazo;
            $entytistore->interpretacion_p_braqueal = $request->interpretacion_p_braqueal;
            
            $entytistore->requ_energia_dia = $request->requ_energia_dia;
            $entytistore->mes_entrega_FTLC = $request->mes_entrega_FTLC;
            $entytistore->fecha_entrega_FTLC = $request->fecha_entrega_FTLC;
            $entytistore->Menor_anos_des_aguda = $request->Menor_anos_des_aguda;
            $entytistore->medicamentos = $request->medicamentos;
            $entytistore->remite_alguna_inst_apoyo = $request->remite_alguna_inst_apoyo;
            $entytistore->Nom_ips_at_prim = $request->Nom_ips_at_prim;
            $entytistore->estado = 1;
            $entytistore->sivigilas_id = $request->sivigilas_id;
            $entytistore->sivigilas_id = $request->sivigilas_id;
            $entytistore->user_id = auth()->user()->id;
            $entytistore->save();
        // $datosEmpleado = request()->except('_token');
        // Ingreso::insert($datosEmpleado);

        // Insert into first table
// $insertId = DB::table('first_table')->insertGetId([
//     'field_1' => 'value_1',
//     'field_2' => 'value_2',
//     'field_3' => 'value_3',

// ]);

// Update second table

    DB::table('sivigilas')->where('sivigilas.id',  $entytistore->sivigilas_id)->update([
        'estado' => '0',
    ]);



    return view('ingreso.certificado')
    ->with('mensaje',' El Ingreso fue guardado Exitosamente..!');
        //esto es para la vista index ojo 
        // // return redirect()->route('Ingreso.index')
        // ->with('mensaje',' El Ingreso fue guardado Exitosamente..!');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ingreso  $ingreso
     * @return \Illuminate\Http\Response
     */
    public function show(Ingreso $ingreso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ingreso  $ingreso
     * @return \Illuminate\Http\Response
     */
    public function edit(Ingreso $ingreso, $id)
    {    
        // $incomeedit = DB:: connection('sqlsrv')->table('ingresos')
        // ->selectRaw('s.pri_nom_ as nom')
        // ->join('sivigilas as s', 's.id', '=', 'ingresos.sivigilas_id' )
        // ->where('sivigilas_id', $id)
        // ->first(); 
        

        // $students2 = Ingreso::select('pri_nom_','ingresos.id','seg_nom_','pri_ape_')
        // ->join('sivigilas as m', 'ingresos.sivigilas_id', '=', 'm.id' )->find($id)
        // ->get();

        
        // $students2 = Ingreso::selectRaw("CONCAT(pri_nom_ ,' ', seg_nom_ ,'  ', pri_ape_) as afiliation_consulta")
        // ->join('sivigilas as m', 'ingresos.sivigilas_id', '=', 'm.id' )
        // ->where('ingresos.sivigilas_id', 21)
        // ->get()->pluck('afiliation_consulta','id');

        $empleado = Ingreso::findOrFail($id); 
        $students2 = Sivigila::all();
        // $students2 = Sivigila::pluck('pri_nom_','id');
        



        
        
        //bucars la info a travez del id se alamcena
        return view('ingreso.edit', compact('empleado','students2'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ingreso  $ingreso
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ingreso $ingreso,$id)
    {
        $datosEmpleado = request()->except(['_token','_method']);
        Ingreso::where('id','=',$id )->update($datosEmpleado);

        $empleado = Ingreso::findOrFail($id);

           $students2 = Ingreso::select('pri_nom_','ingresos.id','seg_nom_','pri_ape_','Fecha_ingreso_ingres','num_ide_')
        ->join('sivigilas as m', 'ingresos.sivigilas_id', '=', 'm.id' )
        ->paginate(5);


        return view('ingreso.index', compact('empleado'),["master"=>$students2]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ingreso  $ingreso
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ingreso $ingreso,$id)
    {
        Ingreso::destroy($id);

        return redirect('Ingreso')->with('mensaje', 'Empleado borrado con exito');
    }


    
    public function reporte()
    {
        return Excel::download(new IngresoExport, 'Ingreso.xls');


    }


    public function reporte2pdf()
    {
        $pdf = PDF::loadView('ingreso.pdf');
        // $pdf->loadHTML('<h1>Test</h1>');
        $url = Storage::url('public/img/logo.jpg');
        
        // return $pdf->stream('ingreso.pdf',compact('url')); para que lo cargue ne lugar de descragar 
        return $pdf->download('ingreso.pdf',compact('url'));

        // $students2 = Ingreso::paginate();
        // return view('ingreso.pdf');

    }



}
