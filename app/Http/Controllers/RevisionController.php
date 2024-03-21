<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;

use App\Models\Sivigila;
use App\Models\Seguimiento;

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
use PDF;
use Storage;

class RevisionController extends Controller
{

    public function __construct(){/*3.se crea este contruct en el controlador a trabajar*/

        $this->middleware('auth');
        $this->middleware('adminrevision', ['only' =>'index']);
        // $this->middleware('adminrevision', ['only' =>'destroy']);
        

       
       

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $incomeedit = Sivigila::select('sivigilas.id', 'sivigilas.num_ide_', 
        'sivigilas.pri_nom_', 'sivigilas.seg_nom_', 'sivigilas.pri_ape_',
         'sivigilas.seg_ape_', 'users.name as hospi')
   // ->where('seguimientos.estado', 0)
    ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
    ->join('users', 'sivigilas.user_id', '=', 'users.id')
    ->groupBy('sivigilas.id', 'sivigilas.num_ide_', 
    'sivigilas.pri_nom_', 'sivigilas.seg_nom_', 
    'sivigilas.pri_ape_', 'sivigilas.seg_ape_',
     'users.name')
     ->whereBetween(DB::raw("YEAR(seguimientos.created_at)"), [2024, 2024])
    ->paginate(3000);

    $conteo = Seguimiento::where('estado', 1)->count('id');
    $otro =  Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
    'sivigilas.pri_ape_','sivigilas.seg_ape_','sivigilas.id as idin','sivigilas.Ips_at_inicial',
    'seguimientos.id','seguimientos.fecha_proximo_control','seguimientos.estado as est',
    'seguimientos.user_id as usr')
    ->orderBy('seguimientos.created_at', 'desc')
    ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
    // ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
    ->where('seguimientos.estado',1)
    ->get();

        return view('revision.index',compact('incomeedit','conteo','otro'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {

        $segene = DB::table('seguimientos')
        ->select('seguimientos.id','sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_', 'seguimientos.fecha_consulta','seguimientos.peso_kilos',
        'seguimientos.talla_cm','seguimientos.puntajez','seguimientos.clasificacion','seguimientos.requerimiento_energia_ftlc',
        'seguimientos.fecha_entrega_ftlc','seguimientos.medicamento','seguimientos.observaciones',
        'seguimientos.est_act_menor','seguimientos.tratamiento_f75','seguimientos.fecha_recibio_tratf75',
        'seguimientos.fecha_proximo_control')
        ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
        ->where('seguimientos.sivigilas_id', $id)
        ->orderBy('seguimientos.id', 'desc')
        // ->limit(1)
        ->get();

      

  
        

       
       
        
        return view('revision.create',["segene"=>$segene]);

 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $entytistore = new Revision;
            $entytistore->estado = 1 ;
            $entytistore->seguimientos_id = $request->seguimientos_id;
            $entytistore->user_id = auth()->user()->id;
            $entytistore->save();

            return redirect()->route('revision.index');
          
    }

    /**
     * Display the specified resource.
     */
    public function show(Revision $revision)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Revision $revision)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revision $revision)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revision $revision)
    {
        //
    }


    public function reportepdf($id)
    {
        $segene = DB::table('seguimientos')
            ->select('seguimientos.id', 'sivigilas.num_ide_', 'sivigilas.pri_nom_', 'sivigilas.seg_nom_',
                'sivigilas.pri_ape_', 'sivigilas.seg_ape_', 'seguimientos.fecha_consulta', 'seguimientos.peso_kilos',
                'seguimientos.talla_cm', 'seguimientos.puntajez', 'seguimientos.clasificacion', 'seguimientos.requerimiento_energia_ftlc',
                'seguimientos.fecha_entrega_ftlc', 'seguimientos.medicamento', 'seguimientos.observaciones',
                'seguimientos.est_act_menor', 'seguimientos.tratamiento_f75', 'seguimientos.fecha_recibio_tratf75',
                'seguimientos.fecha_proximo_control')
            ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
            ->where('seguimientos.sivigilas_id', $id)
            ->orderBy('seguimientos.id', 'desc')
            ->get();
    
        $pdf = PDF::loadView('revision.reporte', compact('segene')) //OJO RECUERDAS AQUI LE MANDAS LAS VARIABLES 
        ->setPaper('letter', 'landscape'); // establece el tamaño de papel como Legal y la orientación como horizontal
    
        return $pdf->stream('revision.reporte.pdf');
    }
}
