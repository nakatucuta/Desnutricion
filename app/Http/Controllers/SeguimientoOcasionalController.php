<?php

namespace App\Http\Controllers;

use App\Models\seguimiento_ocasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Auth;
class SeguimientoOcasionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $consultapaciente = DB::table('sivigilas')->select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin')
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.estado', 1)
        ->where('seguimientos.id', '=', $id)
        ->get();

        $incomeedit = DB::table('sivigilas')
        ->select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin')
        ->join('seguimientos', 'sivigilas.id', '=', 'seguimientos.sivigilas_id')
        ->where('seguimientos.estado', '=', 1)
        
        ->where('sivigilas.user_id', Auth::user()->id)
        ->get();

        $income12 =  DB::connection('sqlsrv_1')->table('refIps')->select('descrip')
        ->where('refIps.codigoDepartamento', 44)
        ->get();

        return view('seguimiento_ocasional.create',compact('incomeedit','income12','consultapaciente'));
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
            'seguimiento_id' => 'required',
            'motivo_seguimiento' => 'required',
            // 'archivo_pdf' => 'required|mimes:pdf|max:2048',


        ];

        $mensajes=[
            'required'=>'El :attribute es requerido',
           
      
        ];

        $this->validate($request, $campos, $mensajes);

     
        
        // $seguimientoExistente = seguimiento_ocasional::where('sivigilas_id', $request->sivigilas_id)
        // ->where('fecha_proximo_control', '>', Carbon::now())
        // ->first();

       
                
                $entytistore = new seguimiento_ocasional;
                $entytistore->motivo_seguimiento = $request->motivo_seguimiento;
                $entytistore->estado = $request->estado;
                $entytistore->fecha_consulta = $request->fecha_consulta;
                $entytistore->peso_kilos = $request->peso_kilos;
                $entytistore->talla_cm = $request->talla_cm;
                $entytistore->puntajez = $request->puntajez;
                $entytistore->clasificacion = $request->clasificacion;
                $entytistore->requerimiento_energia_ftlc = $request->requerimiento_energia_ftlc;
                $entytistore->fecha_entrega_ftlc = $request->fecha_entrega_ftlc;
                $entytistore->medicamento = implode(',', $request->input('medicamento'));
                
                // $entytistore->resultados_seguimientos = $request->resultados_seguimientos;
                // $entytistore->ips_realiza_seguuimiento = $request->ips_realiza_seguuimiento;
                $entytistore->Esquemq_complrto_pai_edad = $request->Esquemq_complrto_pai_edad;

                $entytistore->Atecion_primocion_y_mantenimiento_res3280_2018 = $request->Atecion_primocion_y_mantenimiento_res3280_2018;
               
                $entytistore->observaciones = $request->observaciones;
                // if (empty($request->fecha_proximo_control)) { cod para saber cuando un campo esta vacio haga esto
                    // $entytistore->fecha_proximo_control = date('Y-m-d');
                // } else {
                    $entytistore->est_act_menor = $request->est_act_menor;
                    $entytistore->tratamiento_f75 = $request->tratamiento_f75;
                    $entytistore->fecha_recibio_tratf75 = $request->fecha_recibio_tratf75;
                    
                    $entytistore->fecha_proximo_control = $request->fecha_proximo_control;
                // }
                $entytistore->seguimiento_id = $request->seguimiento_id;
                $entytistore->user_id = auth()->user()->id;
                //codigo para subir pdf
                $file = $request->file('pdf');
                $request->validate([
                    'pdf' => [
                        'required',
                        'mimes:pdf',
                        'max:1048', // Maximo 2 MB (2048 KB)
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

               
       
        
           
           $entytistore->save();
           return redirect()->route('Seguimiento.index');
           
            
    }

    /**
     * Display the specified resource.
     */
    public function show(seguimiento_ocasional $seguimiento_ocasional)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(seguimiento_ocasional $seguimiento_ocasional)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, seguimiento_ocasional $seguimiento_ocasional)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(seguimiento_ocasional $seguimiento_ocasional)
    {
        //
    }
}
