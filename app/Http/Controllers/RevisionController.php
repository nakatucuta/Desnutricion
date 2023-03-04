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
        $incomeedit = Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin','sivigilas.Ips_at_inicial',
        'seguimientos.fecha_consulta','seguimientos.id','seguimientos.fecha_proximo_control')
        ->orderBy('seguimientos.created_at', 'desc')
        ->where('seguimientos.estado', 0)
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        // ->where('seguimientos.estado',1)
        
        ->paginate(3000);

        return view('revision.index',compact('incomeedit'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {

        $segene = DB::table('seguimientos')
        // ->where('seguimientos.estado',1)
        ->where('id', $id)
        ->first();


        $segene1 = DB::table('sivigilas')
        ->select(DB::raw("CONCAT( sivigilas.pri_nom_, ' ', sivigilas.seg_nom_, ' ', sivigilas.pri_ape_,' ',sivigilas.seg_ape_) AS nombre_completo"))
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.id', $id)
        ->value('nombre_completo');

        $segene2 = DB::table('sivigilas')
        ->select(DB::raw("sivigilas.num_ide_ as identifiqui"))
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.id', $id)
        ->value('identifiqui');

       
        
        return view('revision.create',["segene"=>$segene,"segene1"=>$segene1,"segene2"=>$segene2]);

 
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
}
