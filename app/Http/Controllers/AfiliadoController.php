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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


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
                'c.batch_verifications_id',
                // 'b.numero_carnet'
            )
            ->join(DB::raw('vacunas AS c'), 'b.id', '=', 'c.afiliado_id')
            ->get(); // Usamos get() en lugar de paginate() porque DataTables manejará la paginación.
    
           
           
            $sivigilas_usernormal = DB::table('afiliados as b')

            ->select('b.id', 'b.primer_nombre', 'b.segundo_nombre', 'b.primer_apellido', 'b.segundo_apellido', 
            'b.numero_identificacion','b.numero_carnet')
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
        // Validar que se haya subido un archivo y que sea de tipo 'xlsx' o 'xls'
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
    
        // Obtenemos el archivo del request
        $file = $request->file('file');
    
        if ($file) {
            // Verificar que el usuario esté autenticado, si no está autenticado, redirigir con un error
            if (!Auth::check()) {
                return redirect()->route('afiliado')->with('error', 'Usuario no autenticado.');
            }
    
            // Instanciar la clase AfiliadoImport que se encargará de procesar el archivo
            $import = new \App\Imports\AfiliadoImport();
    
            // Importar los datos del archivo Excel usando la clase AfiliadoImport
            Excel::import($import, $file);
    
            // Obtener cualquier error que haya ocurrido durante la importación
            $errores = $import->getErrores();
    
            // Si hay errores, redirigir con un mensaje de error y no continuar el proceso
            if (!empty($errores)) {
                // Si hay algún error, redirigir inmediatamente sin guardar ningún dato
                return redirect()->route('afiliado')->with('error1', $errores);
            }
    
            // Verificar si se debe guardar los datos; esto depende del proceso de validación dentro de AfiliadoImport
            if ($import->debeGuardar()) {
                // Obtener las filas validadas para guardar (tanto afiliados como vacunas)
                $filasParaGuardar = $import->getFilasParaGuardar();
    
                // Iterar sobre cada fila validada
                foreach ($filasParaGuardar as $fila) {
                    // Asignar el 'user_id' al afiliado
                    $fila['afiliado']['user_id'] = Auth::id();
                    
                    // Verificar si el afiliado ya existe en la base de datos
                    $afiliado = Afiliado::where('numero_identificacion', $fila['afiliado']['numero_identificacion'])
                                        ->first();
    
                    if (!$afiliado) {
                        // Si el afiliado no existe, crear un nuevo registro
                        $afiliado = Afiliado::create($fila['afiliado']);
                                // Si el afiliado ya existe, actualizar su número de carnet
                    }
    
                   // Guardar las vacunas asociadas al afiliado
                        foreach ($fila['vacunas'] as $vacunaData) {
                            // Verificar si ya existe una vacuna con la misma dosis y el mismo nombre para el mismo afiliado
                            $existeVacuna = Vacuna::where('afiliado_id', $afiliado->id)
                                                ->where('docis', $vacunaData['docis'])
                                                ->where('nombre', $vacunaData['nombre'])  // Verifica también el nombre de la vacuna
                                                ->first();

                            if (!$existeVacuna) {
                                // Si no existe la vacuna con la misma dosis y el mismo nombre, crearla
                                $vacunaData['afiliado_id'] = $afiliado->id;
                                Vacuna::create($vacunaData);  // Guardar la vacuna asociada
                            }
                        }
                }
    
                // Redirigir con un mensaje de éxito si todo se ha guardado correctamente
                return redirect()->route('afiliado')->with('success', 'Datos importados correctamente');
            } else {
                // Si no se deben guardar los datos, redirigir con un mensaje de error
                return redirect()->route('afiliado')->with('error1', 'No se pudo cargar el archivo debido a errores en los datos.');
            }
        } else {
            // Si no se sube ningún archivo, mostrar un mensaje de error al usuario
            return redirect()->route('afiliado')->with('error', 'Por favor, sube un archivo Excel.');
        }
    }
    
    

    

    
    
    

    

    

    

    

    /**
     * Obtiene las vacunas asociadas a un afiliado específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
  // Método que obtiene las vacunas asociadas a un afiliado por id y número de carnet
  public function getVacunas($id, $numeroCarnet)
{
    // Consulta para obtener las vacunas que coincidan con el id o el número de carnet
    $vacunas = DB::table('vacunas as a')
        ->join('afiliados as b', 'a.afiliado_id', '=', 'b.id')
        ->join('users as c', 'a.user_id', '=', 'c.id')  // Unir con la tabla users
        // La condición es que se cumpla el id o el numero_carnet
        ->where(function($query) use ($id, $numeroCarnet) {
            $query->where('b.id', $id)
                  ->orWhere('b.numero_carnet', $numeroCarnet);
        })
        // Seleccionar los campos que queremos devolver
        ->select(
            'a.nombre as nombre_vacuna', 
            'a.docis as docis_vacuna', 
            'a.fecha_vacuna as fecha_vacunacion',
            'c.name as nombre_usuario',  // Campo del nombre del usuario responsable
            'b.primer_nombre as prim_nom',
            'b.segundo_nombre as seg_nom',
            'b.primer_apellido as pri_ape',
            'b.segundo_apellido as seg_ape', // Campo del correo del usuario responsable
        )
        // Ejecutar la consulta y obtener los resultados
        ->get();

    // Retornar la respuesta en formato JSON con los datos obtenidos
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
            'sent_at' => Carbon::now()->toDateTimeString(),  // Devuelve la fecha en formato compatible con SQL Server
        ]);
    
        return redirect()->back()->with('success', 'Correo enviado exitosamente.');
    }
}
