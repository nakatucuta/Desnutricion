<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;
use App\Models\Ingreso;
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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $incomeedit = Sivigila::select('sivigilas.num_ide_','sivigilas.pri_nom_','sivigilas.seg_nom_',
        'sivigilas.pri_ape_','sivigilas.seg_ape_','seguimientos.id as idin','sivigilas.Ips_at_inicial',
        'seguimientos.fecha_consulta','seguimientos.id','seguimientos.fecha_proximo_control')
        ->orderBy('seguimientos.created_at', 'desc')
      
        ->join('seguimientos', 'seguimientos.id', '=', 'seguimientos.sivigilas_id')
        // ->where('seguimientos.estado',1)
        
        ->paginate(3000);

        return view('revision.index',compact('incomeedit'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        
        return view('revision.create');
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
