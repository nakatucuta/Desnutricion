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
use App\Models\referencia_vacuna; // Importa el modelo aquí

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
    
        $file = $request->file('file');
    
        if ($file) {
            if (!Auth::check()) {
                return redirect()->route('afiliado')->with('error', 'Usuario no autenticado.');
            }
    
            $import = new \App\Imports\AfiliadoImport();
            $batch_id = $import->getBatchVerificationsID();
            $user = Auth::user()->name;
    
            Log::info("Iniciando validación: $batch_id - user: $user");
    
            Excel::import($import, $file);
            $errores = $import->getErrores();
    
            if (!empty($errores)) {
                Log::info("Errores: $batch_id - user: $user");
                return redirect()->route('afiliado')->with('error1', $errores);
            }
    
            Log::info("Terminada la validación: $batch_id - user: $user");
    
            if ($import->debeGuardar()) {
                $filasParaGuardar = $import->getFilasParaGuardar();
                $totalFilas = count($filasParaGuardar);
                $newAfil = 0;
                $oldAfil = 0;
                $newVacuna = 0;
                $oldVacuna = 0;
    
                DB::transaction(function () use ($filasParaGuardar, &$newAfil, &$oldAfil, &$newVacuna, &$oldVacuna, $user, $batch_id) {
                    foreach ($filasParaGuardar as $fila) {
                        // Asignar el 'user_id' al afiliado
                        $fila['afiliado']['user_id'] = Auth::id();
                        $afiliado = Afiliado::where('numero_carnet', $fila['afiliado']['numero_carnet'])->first();
    
                        if (!$fila['existe'] && !$afiliado) {
                            $afiliado = Afiliado::create($fila['afiliado']);
                            $newAfil++;
                        } else {
                            $oldAfil++;
                        }
    
                        foreach ($fila['vacunas'] as $vacunaData) {
                            // Verifica que `vacunas_id` esté definido antes de intentar buscar
                            if (!isset($vacunaData['vacunas_id']) || empty($vacunaData['vacunas_id'])) {
                                Log::error("El campo `vacunas_id` no está definido o está vacío en los datos de la vacuna.");
                                continue; // Saltar esta iteración
                            }
                          //consulta que obtiene la vacuna comparando con la tabla  referncia
                            // Obtener el `nombre_vacuna` desde la tabla `referencia_vacunas` usando Query Builder
                            $referenciaVacuna = DB::table('referencia_vacunas')
                                ->where('id', $vacunaData['vacunas_id'])
                                ->first();
                        
                            if (!$referenciaVacuna) {
                                // Si no encuentra la referencia, registrar un error y asignar un nombre predeterminado
                                Log::error("Vacuna no encontrada en `referencia_vacunas` con ID: {$vacunaData['vacunas_id']}");
                                $vacunaData['nombre_vacuna'] = 'Desconocida'; // Nombre predeterminado
                            } else {
                                // Asignar el nombre de la vacuna desde la tabla `referencia_vacunas`
                                $vacunaData['nombre_vacuna'] = $referenciaVacuna->nombre;
                            }
                        
                            // Comprobar si la vacuna ya existe en la tabla `vacunas`
                            $existeVacuna = Vacuna::where('afiliado_id', $afiliado->id)
                                ->whereRaw("docis COLLATE Latin1_General_CI_AI = ?", [$vacunaData['docis']])
                                ->where('vacunas_id', $vacunaData['vacunas_id'])
                                ->first();
                        
                            if (!$existeVacuna) {
                                // Si la vacuna no existe, crearla
                                $vacunaData['afiliado_id'] = $afiliado->id;
                                Vacuna::create($vacunaData);
                                $newVacuna++;
                            } else {
                                $oldVacuna++;
                            }
                        }
                        
                        
                    }
                });
    
                // Ruta del archivo TXT
                $filePath = storage_path('app/public/vacunas_cargadas_' . $batch_id . '.txt');
    
                // Generar el archivo
                $this->generarArchivoVacunas($filasParaGuardar, $filePath);
    
                // Enviar el correo
                $this->enviarCorreoConAdjunto($filePath, $user);
    
                Log::info("Proceso finalizado exitosamente: $batch_id - user: $user");
                return redirect()->route('afiliado')->with('success', 'Datos importados y correo enviado correctamente');
            } else {
                Log::info("Errores de validación: $batch_id - user: $user");
                return redirect()->route('afiliado')->with('error1', 'No se pudo cargar el archivo debido a errores en los datos.');
            }
        } else {
            return redirect()->route('afiliado')->with('error', 'Por favor, sube un archivo Excel.');
        }
    }
    
    /**
     * Enviar un correo con el archivo adjunto.
     */
    protected function generarArchivoVacunas($data, $filePath)
    {
        // Encabezado inicial del archivo
        $contenido = "Reporte de Vacunas Cargadas:\n";
        $contenido .= "-------------------------------------------\n";
    
        foreach ($data as $linea) {
            // Validar que exista 'afiliado' y que contenga los datos necesarios
            if (!isset($linea['afiliado'])) {
                Log::error("Datos del afiliado no encontrados en la línea: " . json_encode($linea));
                $contenido .= "Datos del afiliado no disponibles.\n";
                $contenido .= "-------------------------------------------\n";
                continue; // Saltar al siguiente registro
            }
    
            $afiliado = $linea['afiliado'];
            $primerNombre = $afiliado['primer_nombre'] ?? 'vacio';
            $segundoNombre = $afiliado['segundo_nombre'] ?? 'vacio';
            $primerApellido = $afiliado['primer_apellido'] ?? 'vacio';
            $segundoApellido = $afiliado['segundo_apellido'] ?? 'vacio';
            $numeroIdentificacion = $afiliado['numero_identificacion'] ?? 'No disponible';
    
            // Agregar datos del afiliado al contenido
            $contenido .= "Paciente: {$primerNombre} {$segundoNombre} {$primerApellido} {$segundoApellido}\n";
            $contenido .= "Documento de Identidad: {$numeroIdentificacion}\n";
    
            // Validar y procesar las vacunas
            if (isset($linea['vacunas']) && is_array($linea['vacunas'])) {
                foreach ($linea['vacunas'] as $vacuna) {
                    if (!isset($vacuna['vacunas_id'])) {
                        Log::error("Campo `vacunas_id` no encontrado en la vacuna: " . json_encode($vacuna));
                        $contenido .= "- Vacuna: Vacuna desconocida | Dosis: Dosis desconocida\n";
                        continue; // Saltar esta iteración
                    }
    
                    // Obtener el nombre de la vacuna desde la tabla `referencia_vacunas`
                    $referenciaVacuna = \DB::table('referencia_vacunas')
                        ->where('id', $vacuna['vacunas_id'])
                        ->value('nombre'); // Obtiene directamente el nombre de la vacuna
    
                    // Asignar valores predeterminados si no se encuentran
                    $nombreVacuna = $referenciaVacuna ?? 'Vacuna desconocida';
                    $dosis = $vacuna['docis'] ?? 'Dosis desconocida';
    
                    // Agregar vacuna al contenido
                    $contenido .= "- Vacuna: {$nombreVacuna} | Dosis: {$dosis}\n";
                }
            } else {
                $contenido .= "No se encontraron vacunas asociadas.\n";
            }
    
            // Usuario cargador
            $usuarioCargador = Auth::check() ? Auth::user()->name : 'Usuario desconocido';
            $contenido .= "Cargado por: {$usuarioCargador}\n";
    
            $contenido .= "-------------------------------------------\n";
        }
    
        // Crear o sobrescribir el archivo
        if (file_put_contents($filePath, $contenido) === false) {
            Log::error("No se pudo generar el archivo en: $filePath");
            throw new \Exception("No se pudo generar el archivo en: $filePath");
        }
    
        Log::info("Archivo generado en: $filePath");
    }
    
    

    protected function enviarCorreoConAdjunto($filePath)
    {
        if (file_exists($filePath)) {
            // Obtener el nombre del usuario autenticado
            $usuario = Auth::check() ? Auth::user()->name : 'Usuario desconocido';
    
            // Obtener el correo electrónico del usuario autenticado
            $correoUsuario = Auth::check() ? Auth::user()->email : null;
    
            // Validar que el usuario autenticado tenga un correo
            if (!$correoUsuario) {
                Log::error("El usuario autenticado no tiene un correo válido.");
                return;
            }
    
            // Crear un nuevo correo con el archivo adjunto
            $email = new \App\Mail\VacunasCargadas($filePath, $usuario);
    
            // Log de información
            Log::info("Intentando enviar correo con vista 'mail.vacunas_cargadas' y archivo: $filePath");
    
            // Enviar el correo al usuario autenticado con copia a otros destinatarios
            Mail::to($correoUsuario)
                ->cc(['jsuarez@epsianaswayuu.com', 'pai@epsianaswayuu.com'])
                ->send($email);
    
            // Log de éxito
            Log::info("Correo enviado con éxito al usuario autenticado ({$correoUsuario}), jsuarez@epsianaswayuu.com y pai@epsianaswayuu.com con el archivo: $filePath");
        } else {
            // Log de error si no encuentra el archivo
            Log::error("Archivo no encontrado para enviar: $filePath");
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
    ->join('users as c', 'a.user_id', '=', 'c.id')
    ->join('referencia_vacunas as d', 'a.vacunas_id', '=', 'd.id')
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroafiliados] as x'), 'b.numero_carnet', '=', 'x.numeroCarnet')
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as y'), 'x.numeroCarnet', '=', 'y.numeroCarnet')
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as z'), 'y.idGrupoIps', '=', 'z.id')    
    ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[municipios] as w'), function($join) {
        $join->on('w.codigoDepartamento', '=', 'x.codigoDepartamento')
             ->on('w.codigoMunicipio', '=', 'x.codigoMunicipio');
    })
    ->select(
        'd.nombre as nombre_vacuna',
        DB::raw("
            CASE 
                WHEN a.vacunas_id IN (23, 24) AND a.docis IS NULL 
                    THEN CONCAT(a.num_frascos_utilizados, ' frascos')
                ELSE a.docis
            END as docis_vacuna
        "),
        'a.fecha_vacuna as fecha_vacunacion',
        'b.fecha_nacimiento',
        'c.name as nombre_usuario',
        'b.primer_nombre as prim_nom',
        'b.segundo_nombre as seg_nom',
        'b.primer_apellido as pri_ape',
        'b.segundo_apellido as seg_ape',
        'b.tipo_identificacion as tipo_id',
        'b.numero_identificacion as numero_id',
        'x.genero as genero',
        'z.descrip as ips',
        'w.descrip as municipio',
        DB::raw('FLOOR((CAST(CONVERT(varchar(8), CONVERT(DATE, a.fecha_vacuna), 112) AS int) - CAST(CONVERT(varchar(8), CONVERT(DATE, b.fecha_nacimiento), 112) AS int)) / 10000) AS edad_anos'),
        DB::raw('DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna) AS total_meses'),
        'a.responsable as responsable'
    )
    ->where(function ($query) use ($id, $numeroCarnet) {
        $query->where('b.id', $id)
              ->orWhere('b.numero_carnet', $numeroCarnet);
    })
    ->orderBy('a.fecha_vacuna', 'asc') // Ordenar por fecha_vacuna ascendente
    ->orderBy('d.nombre', 'asc') // Luego ordenar por nombre_vacuna ascendente
    ->get();

   

    //Calcular la edad
    $hoy = Carbon::now();
    $fechaNacimiento = Carbon::createFromFormat('Y-m-d', $vacunas[0]->fecha_nacimiento);    
    // Calcular la diferencia exacta en años, meses y días
    $edad = $hoy->diff($fechaNacimiento);

    $vacunas[0]->age = $edad->y . 'a ' . $edad->m . 'm ' . $edad->d . 'd';

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
