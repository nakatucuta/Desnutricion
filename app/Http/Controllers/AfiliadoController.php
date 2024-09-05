<?php

namespace App\Http\Controllers;

use App\Models\afiliado;
use Illuminate\Http\Request;
use App\Models\vacuna;
use App\Models\batch_verifications;
use App\Imports\AfiliadoImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitudMail;
use App\Models\CorreoEnviado;


class AfiliadoController extends Controller
{
    /**
     * Muestra la vista principal con los datos de los afiliados.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // $sivigilas1 = DB::connection('sqlsrv_1')
        // ->table('users')  // Sin alias
        // ->select('*') 
        // ->get();
        // $authenticatedUser = auth()->user()->name;
        // $userInQuery = $sivigilas1->firstWhere('name', $authenticatedUser->name);


        // $sivigilas = DB::table('afiliados as b')

        //     ->select('b.id', 'b.primer_nombre', 'b.segundo_nombre', 'b.primer_apellido', 'b.segundo_apellido', 'b.numero_identificacion')
        //     ->get();


        $sivigilas = DB::table(DB::raw('afiliados AS b'))
            ->select(
                'b.id',
                'b.primer_nombre',
                'b.segundo_nombre',
                'b.primer_apellido',
                'b.segundo_apellido',
                'b.numero_identificacion',
                'c.batch_verifications_id'
            )
            ->join(DB::raw('vacunas AS c'), 'b.id', '=', 'c.afiliado_id')
            ->get(); // Usamos get() en lugar de paginate() porque DataTables manejará la paginación.
    
           
           
            $sivigilas_usernormal = DB::table('afiliados as b')

            ->select('b.id', 'b.primer_nombre', 'b.segundo_nombre', 'b.primer_apellido', 'b.segundo_apellido', 
            'b.numero_identificacion')
            // ->join(DB::raw('vacunas AS c'), 'b.id', '=', 'c.afiliado_id')

           ->get();
             // Usamos get() en lugar de paginate() porque DataTables manejará la paginación.
        return view('livewire.afiliado', compact('sivigilas','sivigilas_usernormal'));
    }
  
      /**
     * Muestra el formulario para importar archivos Excel.
     *
     * @return \Illuminate\View\View
     */
    public function showImportForm()
    {
        // Puedes añadir lógica aquí si necesitas un formulario separado para la importación.
        return view('livewire.import-excel');
    }


    /**
     * Importa los datos desde un archivo Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        if ($file) {
            Excel::import(new AfiliadoImport, $file);
            return redirect()->route('afiliado')->with('success', 'Datos importados correctamente');
        } else {
            return redirect()->route('afiliado')->with('error', 'Por favor, sube un archivo Excel.');
        }
    }

    /**
     * Obtiene las vacunas asociadas a un afiliado específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVacunas($id)
    {
        $vacunas = DB::table('vacunas as a')
            ->join('afiliados as b', 'a.afiliado_id', '=', 'b.id')
            ->where('b.id', $id)
            ->select('a.nombre as nombre_vacuna')
            ->get();

        return response()->json($vacunas);
    }

    /**
     * Elimina un registro de Batch_verification y sus afiliados y vacunas asociados.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $batchVerification = batch_verifications::findOrFail($id);
            $afiliados = afiliado::where('batch_verifications_id', $batchVerification->id)->get();

            foreach ($afiliados as $afiliado) {
                $vacunas = vacuna::where('afiliado_id', $afiliado->id)
                                 ->where('batch_verifications_id', $batchVerification->id)
                                 ->get();

                if ($vacunas->isNotEmpty()) {
                    foreach ($vacunas as $vacuna) {
                        $vacuna->delete();
                    }
                    $afiliado->delete();
                }
            }

            vacuna::where('batch_verifications_id', $batchVerification->id)->delete();
            $batchVerification->delete();

            DB::commit();

            return redirect()->route('afiliado')->with('success', 'Los registros fueron eliminados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('afiliado')->with('error', 'Hubo un problema al eliminar los registros: ' . $e->getMessage());
        }
    }

//METODO PARA  EL ENVIO DE CORREO EN PAI  OJO 

    public function sendEmail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'patientId' => 'required|integer'
        ]);
    
        // Verifica si ya se ha enviado un correo para este paciente
        $userId = auth()->id();
        $patientId = $request->patientId;
    
        $correoExistente = CorreoEnviado::where('user_id', $userId)
            ->where('patient_id', $patientId)
            ->first();
    
        if ($correoExistente) {
            return redirect()->back()->with('error', 'Ya has enviado un correo de solicitud para este paciente.');
        }
    
        // Obtener el usuario actual
        $fromEmail = auth()->user()->email;
    
        // Preparar datos para el correo
        $details = [
            'subject' => $request->subject,
            'message' => $request->message,
            'fromEmail' => $fromEmail,
            'patientName' => $request->patientName,  // El nombre del paciente
        ];
    
        // Enviar el correo
        Mail::to('jsuarez@epsianaswayuu.com')->send(new SolicitudMail($details));
    
        // Registrar que se ha enviado un correo para este paciente
        CorreoEnviado::create([
            'user_id' => $userId,
            'patient_id' => $patientId,
            'sent_at' => now(),
        ]);
    
        return redirect()->back()->with('success', 'Correo enviado exitosamente.');
    }
}
