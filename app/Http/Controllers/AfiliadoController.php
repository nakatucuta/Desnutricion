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
use Illuminate\Support\Facades\Storage;
use ZipArchive;


class AfiliadoController extends Controller
{
    /**
     * Muestra la vista principal con los datos de los afiliados.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
{
    $search = $request->input('search');

    // Si se selecciona un número de identificación, filtramos los resultados
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
        ->when($search, function ($query, $search) {
            return $query->where('b.numero_identificacion', 'LIKE', "%{$search}%");
        })
        ->paginate(10);

    // Consulta para otros usuarios
    $sivigilas_usernormal = DB::table('afiliados as b')
        ->select(
            'b.id',
            'b.primer_nombre',
            'b.segundo_nombre',
            'b.primer_apellido',
            'b.segundo_apellido',
            'b.numero_identificacion',
            'b.numero_carnet'
        )
        ->when($search, function ($query, $search) {
            return $query->where('b.numero_identificacion', 'LIKE', "%{$search}%");
        })
        ->paginate(10);

    // Verificar si la solicitud es AJAX
    if ($request->ajax()) {
        return response()->json([
            'sivigilas' => $sivigilas->items(),
            'sivigilas_usernormal' => $sivigilas_usernormal->items(),
        ]);
    }

    // Si no es una solicitud AJAX, devolvemos la vista normalmente
    return view('livewire.afiliado', compact('sivigilas', 'sivigilas_usernormal', 'search'));
}
    // METODO PARA EL BUSCADOR  EN INDEX
    
    public function buscarAfiliados(Request $request)
    {
        $search = $request->input('search');
    
        // Verifica si hay un término de búsqueda
        if ($search) {
            $results = DB::table('afiliados as b')
                ->select(
                    'b.id',
                    'b.primer_nombre',
                    'b.segundo_nombre',
                    'b.primer_apellido',
                    'b.segundo_apellido',
                    'b.numero_identificacion'
                )
                ->where('b.numero_identificacion', 'LIKE', "%{$search}%")
                ->limit(10)  // Limitar el número de resultados
                ->get();
    
            return response()->json($results);  // Devolver JSON
        }
    
        return response()->json([]);  // Si no hay búsqueda, devolver un array vacío
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
                                                ->where('vacunas_id', $vacunaData['vacunas_id'])  // Verifica también el nombre de la vacuna
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
        ->join('referencia_vacunas as d', 'a.vacunas_id', '=', 'd.id')
        // La condición es que se cumpla el id o el numero_carnet
        ->where(function($query) use ($id, $numeroCarnet) {
            $query->where('b.id', $id)
                  ->orWhere('b.numero_carnet', $numeroCarnet);
        })
        // Seleccionar los campos que queremos devolver
        ->select(
            'd.nombre as nombre_vacuna', 
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

// METODO PARA DESCARGAR EL  FORMATO EXCEL 
 
    // Método para descargar el archivo Excel
    public function downloadExcel()
{
    // Rutas de los archivos que deseas incluir en el ZIP
    $excelPath = 'public/Formato pai_.xlsx';
    $pdfPath = 'public/Manual para el uso de registro diario pai.pdf';  // Cambia esta ruta al archivo PDF que deseas descargar

    // Verificar si los archivos existen
    if (!Storage::exists($excelPath) || !Storage::exists($pdfPath)) {
        abort(404, 'Uno o ambos archivos no existen.');
    }

    // Crear un archivo ZIP
    $zipFileName = 'documentos.zip';
    $zipFilePath = storage_path($zipFileName);  // Ubicación temporal del archivo ZIP

    $zip = new ZipArchive;

    if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
        // Agregar el archivo Excel
        $zip->addFile(Storage::path($excelPath), 'formato_registro_diario.xlsx');

        // Agregar el archivo PDF
        $zip->addFile(Storage::path($pdfPath), 'manual.pdf');

        // Cerrar el archivo ZIP
        $zip->close();
    } else {
        abort(500, 'No se pudo crear el archivo ZIP.');
    }

    // Descargar el archivo ZIP
    return response()->download($zipFilePath)->deleteFileAfterSend(true);  // Elimina el ZIP después de la descarga
}

}
