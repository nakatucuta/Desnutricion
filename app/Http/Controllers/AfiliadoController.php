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
use App\Exports\VacunaExport;             // <- agrega
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str; // ✅ ESTE ES EL FIX
use App\Models\ImportJob;
use App\Jobs\ImportAfiliadosExcelJob;
  // ✅ ESTE ES EL JOB (cola)
use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;


class AfiliadoController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless((int) (Auth::user()->usertype ?? 0) === 1, 403, 'Acceso solo para administradores.');
    }

    /**
     * Muestra la vista principal con los datos de los afiliados.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $kpiVacunas = DB::table('vacunas')
            ->distinct()
            ->count('afiliado_id');

        $kpiAfiliados = DB::table('afiliados')
            ->count('id');

        return view('livewire.afiliado', compact('kpiVacunas', 'kpiAfiliados'));
    }


    
    
    // METODO PARA EL BUSCADOR  EN INDEX
    
    public function buscarAfiliados(Request $request)
    {
        $search = $request->input('search');
        if (! $search) {
            return response()->json([]);
        }
    
        // 1) Conexiones
        $connSGA = DB::connection('sqlsrv_1');  // SGA
        $connDES = DB::connection('sqlsrv');    // DESNUTRICION
    
        // 2) Subconsulta de carnets filtrados (evita whereIn masivo > 2100 parámetros)
        $filteredCarnets = $connSGA->table('maestroidentificaciones as x')
            ->select('x.numeroCarnet')
            ->where('x.identificacion', 'LIKE', "%{$search}%")
            ->groupBy('x.numeroCarnet');

        // 3) Por cada carnet filtrado, obtener identificación máxima (SGA)
        $maxPerCarnet = $connSGA->table('maestroidentificaciones as mi')
            ->joinSub($filteredCarnets, 'fc', function ($join) {
                $join->on('fc.numeroCarnet', '=', 'mi.numeroCarnet');
            })
            ->select('mi.numeroCarnet', DB::raw('MAX(mi.identificacion) as numero_identificacion'))
            ->groupBy('mi.numeroCarnet');
    
        // 4) Obtener datos del afiliado + maestro + solo la fila "máxima" por carnet (SGA)
        $results = $connSGA->table('maestroafiliados as c')
            ->joinSub($maxPerCarnet, 'm', function($join) {
                $join->on('c.numeroCarnet', '=', 'm.numeroCarnet');
            })
            ->join('maestroidentificaciones as d', function($join) {
                $join->on('d.numeroCarnet', '=', 'm.numeroCarnet')
                     ->on('d.identificacion', '=', 'm.numero_identificacion');
            })
            ->select([
                'c.numeroCarnet       as numero_carnet',
                'c.primerNombre       as primer_nombre',
                'c.segundoNombre      as segundo_nombre',
                'c.primerApellido     as primer_apellido',
                'c.segundoApellido    as segundo_apellido',
                'c.tipoIdentificacion as tipo_identificacion',
                'c.identificacion     as numero_identificacion',
            ])
            ->limit(20)
            ->get()
            ->unique('numero_identificacion')  // quitar duplicados
            ->values();

        if ($results->isEmpty()) {
            return response()->json([]);
        }
    
        // 5) Actualizar la tabla afiliados en DESNUTRICION solo si hay cambios
        foreach ($results as $row) {
            $current = $connDES->table('afiliados')
                ->where('numero_carnet', $row->numero_carnet)
                ->first([
                    'tipo_identificacion',
                    'numero_identificacion',
                    'primer_nombre',
                    'segundo_nombre',
                    'primer_apellido',
                    'segundo_apellido'
                ]);
    
            $update = [];
    
            if (!$current || $current->tipo_identificacion   !== $row->tipo_identificacion)   {
                $update['tipo_identificacion']   = $row->tipo_identificacion;
            }
            if (!$current || $current->numero_identificacion !== $row->numero_identificacion) {
                $update['numero_identificacion'] = $row->numero_identificacion;
            }
            if (!$current || $current->primer_nombre          !== $row->primer_nombre)          {
                $update['primer_nombre']          = $row->primer_nombre;
            }
            if (!$current || $current->segundo_nombre         !== $row->segundo_nombre)         {
                $update['segundo_nombre']         = $row->segundo_nombre;
            }
            if (!$current || $current->primer_apellido        !== $row->primer_apellido)        {
                $update['primer_apellido']        = $row->primer_apellido;
            }
            if (!$current || $current->segundo_apellido       !== $row->segundo_apellido)       {
                $update['segundo_apellido']       = $row->segundo_apellido;
            }
    
            if (!empty($update)) {
                $connDES->table('afiliados')
                    ->where('numero_carnet', $row->numero_carnet)
                    ->update($update);
            }
        }
    
        // 6) Devolver JSON con los datos (ya sincronizados)
        return response()->json($results);
    }

    public function dataTable(Request $request)
    {
        $user = Auth::user();
        $isAdmin = (int) ($user->usertype ?? 0) === 1;

        if ($isAdmin) {
            $query = DB::table('afiliados as b')
                ->join('vacunas as c', 'b.id', '=', 'c.afiliado_id')
                ->select([
                    'b.id',
                    'b.primer_nombre',
                    'b.segundo_nombre',
                    'b.primer_apellido',
                    'b.segundo_apellido',
                    'b.numero_identificacion',
                    'b.numero_carnet',
                    DB::raw('MAX(c.batch_verifications_id) AS batch_verifications_id'),
                ])
                ->groupBy(
                    'b.id',
                    'b.primer_nombre',
                    'b.segundo_nombre',
                    'b.primer_apellido',
                    'b.segundo_apellido',
                    'b.numero_identificacion',
                    'b.numero_carnet'
                );
        } else {
            $query = DB::table('afiliados as b')
                ->leftJoin('correos_enviados as ce', function ($join) use ($user) {
                    $join->on('ce.patient_id', '=', 'b.id')
                        ->where('ce.user_id', '=', $user->id);
                })
                ->select([
                    'b.id',
                    'b.primer_nombre',
                    'b.segundo_nombre',
                    'b.primer_apellido',
                    'b.segundo_apellido',
                    'b.numero_identificacion',
                    'b.numero_carnet',
                    DB::raw('CASE WHEN ce.patient_id IS NULL THEN 0 ELSE 1 END AS correo_enviado'),
                ]);
        }

        return DataTables::of($query)
            ->filter(function ($builder) use ($request) {
                $search = trim((string) data_get($request->input('search'), 'value', ''));
                if ($search === '') {
                    return;
                }

                $builder->where(function ($q) use ($search) {
                    $q->where('b.numero_identificacion', 'LIKE', "%{$search}%")
                        ->orWhere('b.primer_nombre', 'LIKE', "%{$search}%")
                        ->orWhere('b.segundo_nombre', 'LIKE', "%{$search}%")
                        ->orWhere('b.primer_apellido', 'LIKE', "%{$search}%")
                        ->orWhere('b.segundo_apellido', 'LIKE', "%{$search}%");
                });
            })
            ->addColumn('id_badge', function ($row) {
                return '<span class="pai-badge-id">#' . e($row->id) . '</span>';
            })
            ->addColumn('documento', function ($row) {
                $doc = e($row->numero_identificacion ?? '');
                $id = e($row->id);
                $carnet = e($row->numero_carnet ?? '');

                return '<a href="#" class="numero-identificacion pai-doclink" data-id="' . $id . '" data-carnet="' . $carnet . '">' .
                    '<span class="pai-dot"></span>' .
                    '<span class="pai-doclink__text">' . $doc . '</span>' .
                    '</a>' .
                    '<div class="pai-muted">Clic para ver vacunas</div>';
            })
            ->addColumn('paciente', function ($row) {
                $fullName = trim(implode(' ', array_filter([
                    $row->primer_nombre ?? '',
                    $row->segundo_nombre ?? '',
                    $row->primer_apellido ?? '',
                    $row->segundo_apellido ?? '',
                ])));

                $initials = strtoupper(
                    mb_substr((string) ($row->primer_nombre ?? 'P'), 0, 1) .
                    mb_substr((string) ($row->primer_apellido ?? 'A'), 0, 1)
                );

                return '<div class="pai-person">' .
                    '<div class="pai-avatar">' . e($initials) . '</div>' .
                    '<div class="pai-person__meta">' .
                    '<div class="pai-person__name">' . e($fullName) . '</div>' .
                    '</div>' .
                    '</div>';
            })
            ->addColumn('lote_carnet', function ($row) use ($isAdmin) {
                $value = $isAdmin ? ($row->batch_verifications_id ?? '') : ($row->numero_carnet ?? '');
                return '<span class="pai-pill">' . e($value) . '</span>';
            })
            ->addColumn('acciones', function ($row) use ($isAdmin) {
                if ($isAdmin) {
                    return '<span class="text-muted font-weight-bold">Gestion desde Lotes</span>';
                }

                $fullName = trim(implode(' ', array_filter([
                    $row->primer_nombre ?? '',
                    $row->segundo_nombre ?? '',
                    $row->primer_apellido ?? '',
                    $row->segundo_apellido ?? '',
                ])));

                if ((int) ($row->correo_enviado ?? 0) === 1) {
                    return '<div class="pai-actions"><button class="btn btn-pai btn-pai-pastel-neutral btn-sm" disabled>'
                        . '<i class="fas fa-envelope mr-1"></i> Correo enviado</button></div>';
                }

                return '<div class="pai-actions"><a href="#" class="btn btn-pai btn-pai-pastel-primary btn-sm send-email"'
                    . ' data-toggle="modal" data-target="#emailModal"'
                    . ' data-id="' . e($row->id) . '"'
                    . ' data-name="' . e($fullName) . '">'
                    . '<i class="fas fa-envelope mr-1"></i> Solicitud</a></div>';
            })
            ->rawColumns(['id_badge', 'documento', 'paciente', 'lote_carnet', 'acciones'])
            ->toJson();
    }
    
    

//ESTE ME UESTRA  LOS DOCUMENTOS DE IDENTIDAD  ASOCIADOS  AUN NUMERO DE CARNET

    // public function buscarAfiliados(Request $request)
    // {
    //     $search = $request->input('search');
    
    //     if (! $search) {
    //         return response()->json([]);
    //     }
    
    //     // 1) Obtener los carnet(s) asociados a la identificación buscada
    //     $carnets = DB::table('sga.dbo.maestroidentificaciones')
    //         ->where('identificacion', 'LIKE', "%{$search}%")
    //         ->pluck('numeroCarnet');
    
    //     if ($carnets->isEmpty()) {
    //         return response()->json([]);
    //     }
    
    //     // 2) Traer **todas** las identificaciones de esos carnet(s)
    //     $results = DB::table('DESNUTRICION.dbo.afiliados as b')
    //         ->join('sga.dbo.maestroafiliados      as c', 'b.numero_carnet', '=', 'c.numeroCarnet')
    //         ->join('sga.dbo.maestroidentificaciones as d', 'b.numero_carnet', '=', 'd.numeroCarnet')
    //         ->select([
    //             'b.id',
    //             'c.primerNombre       as primer_nombre',
    //             'c.segundoNombre      as segundo_nombre',
    //             'c.primerApellido     as primer_apellido',
    //             'c.segundoApellido    as segundo_apellido',
    //             'd.tipoIdentificacion as tipo_identificacion',
    //             'd.identificacion     as numero_identificacion',
    //             'c.numeroCarnet       as numero_carnet',
    //         ])
    //         ->whereIn('b.numero_carnet', $carnets)
    //         ->get();
    
    //     return response()->json($results);
    // }
    
    
    
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
        'upload_token' => 'nullable|string',
    ]);

    if (!Auth::check()) {
        return redirect()->route('afiliado')->with('error', 'Usuario no autenticado.');
    }

    $file   = $request->file('file');
    $userId = Auth::id();
    $user   = Auth::user()->name ?? 'SIN_NOMBRE';
    $token  = $request->input('upload_token');

    // Guardar el archivo en storage/app/imports
    $storedPath = $file->storeAs(
        'imports',
        'import_' . $userId . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension()
    );

    if ($token) {
        Cache::put("import_progress:{$token}", [
            'percent' => 1,
            'message' => 'Archivo recibido. Encolando procesamiento…',
            'step'    => 'encolado',
            'done'    => false,
            'ok'      => true,
            'done_steps' => ['encolado'],
        ], now()->addMinutes(60));
    }

    // ✅ Disparar el job (background)
    \App\Jobs\ImportAfiliadosExcelJob::dispatch($storedPath, $userId, $user, $token);

    // Responder rápido (NO se cae la página por procesamiento)
    return redirect()->route('afiliado')->with(
        'success',
        'Archivo recibido. El sistema lo está procesando en segundo plano. Puedes ver el progreso en pantalla.'
    );
}


private function generarArchivoVacunasDesdeDB(int $batchId, string $filePath): void
{
    $handle = fopen($filePath, 'w');
    if (!$handle) {
        throw new \RuntimeException("No se pudo crear el TXT en: {$filePath}");
    }

    // Ajusta columnas según lo que tú escribías antes en el TXT
    $query = DB::table('vacunas as v')
        ->join('afiliados as a', 'a.id', '=', 'v.afiliado_id')
        ->select([
            'a.numero_carnet',
            'a.tipo_identificacion',
            'a.numero_identificacion',
            'v.vacunas_id',
            'v.docis',
            'v.lote',
            'v.fecha_vacuna',
            'v.responsable',
            'v.regimen',
            'v.observaciones',
        ])
        ->where('v.batch_verifications_id', $batchId)
        ->orderBy('a.numero_carnet')
        ->orderBy('v.vacunas_id');

    // Streaming por chunks para no cargar todo el TXT en memoria
    $query->chunk(2000, function ($rows) use ($handle) {
        foreach ($rows as $r) {
            $line = implode('|', [
                $r->numero_carnet ?? '',
                $r->tipo_identificacion ?? '',
                $r->numero_identificacion ?? '',
                $r->vacunas_id ?? '',
                $r->docis ?? '',
                $r->lote ?? '',
                $r->fecha_vacuna ?? '',
                $r->responsable ?? '',
                $r->regimen ?? '',
                $r->observaciones ?? '',
            ]);
            fwrite($handle, $line . PHP_EOL);
        }
    });

    fclose($handle);
}


    
    /**
     * Enviar un correo con el archivo adjunto.
     */
    protected function generarArchivoVacunas($data, $filePath)
    {
        $contenido = "Reporte de Vacunas Cargadas:\n";
        $contenido .= "-------------------------------------------\n";
    
        foreach ($data as $linea) {
            // Validar que exista 'afiliado'
            if (!isset($linea['afiliado'])) {
                Log::error("Datos del afiliado no encontrados en la línea: " . json_encode($linea));
                $contenido .= "Datos del afiliado no disponibles.\n";
                $contenido .= "-------------------------------------------\n";
                continue;
            }
    
            $afiliado = $linea['afiliado'];
    
            // Si faltan campos clave, obtener desde la base de datos
            if (
                empty($afiliado['primer_nombre']) ||
                empty($afiliado['numero_identificacion']) ||
                !isset($afiliado['id'])
            ) {
                $afiliadoDB = \DB::table('afiliados')
                    ->where('numero_carnet', $afiliado['numero_carnet'] ?? '')
                    ->orWhere('id', $afiliado['id'] ?? 0)
                    ->first();
    
                if ($afiliadoDB) {
                    $afiliado = (array) $afiliadoDB;
                }
            }
    
            $primerNombre = $afiliado['primer_nombre'] ?? 'vacio';
            $segundoNombre = $afiliado['segundo_nombre'] ?? 'vacio';
            $primerApellido = $afiliado['primer_apellido'] ?? 'vacio';
            $segundoApellido = $afiliado['segundo_apellido'] ?? 'vacio';
            $numeroIdentificacion = $afiliado['numero_identificacion'] ?? 'No disponible';
    
            $contenido .= "Paciente: {$primerNombre} {$segundoNombre} {$primerApellido} {$segundoApellido}\n";
            $contenido .= "Documento de Identidad: {$numeroIdentificacion}\n";
    
            if (isset($linea['vacunas']) && is_array($linea['vacunas'])) {
                foreach ($linea['vacunas'] as $vacuna) {
                    if (!isset($vacuna['vacunas_id'])) {
                        Log::error("Campo `vacunas_id` no encontrado en la vacuna: " . json_encode($vacuna));
                        $contenido .= "- Vacuna: Vacuna desconocida | Dosis: Dosis desconocida\n";
                        continue;
                    }
    
                    $referenciaVacuna = \DB::table('referencia_vacunas')
                        ->where('id', $vacuna['vacunas_id'])
                        ->value('nombre');
    
                    $nombreVacuna = $referenciaVacuna ?? 'Vacuna desconocida';
                    $dosis = $vacuna['docis'] ?? 'Dosis desconocida';
                    $regimenVacuna = $vacuna['regimen'] ?? 'Sin regimen';

                    $contenido .= "- Vacuna: {$nombreVacuna} | Dosis: {$dosis} | Regimen: {$regimenVacuna}\n";
                }
            } else {
                $contenido .= "No se encontraron vacunas asociadas.\n";
            }
    
            $usuarioCargador = Auth::check() ? Auth::user()->name : 'Usuario desconocido';
            $contenido .= "Cargado por: {$usuarioCargador}\n";
            $contenido .= "-------------------------------------------\n";
        }
    
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
                ->cc(['jsuarez@epsianaswayuu.com','pai@epsianaswayuu.com'])
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
  public function getVacunas($id, $numeroCarnet = null)
{
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
            'a.responsable as responsable',
            'a.regimen as regimen_vacuna'
        )
        ->where(function ($query) use ($id, $numeroCarnet) {
            $query->where('b.id', $id);

            // ✅ solo filtra por carnet si viene
            if (!empty($numeroCarnet)) {
                $query->orWhere('b.numero_carnet', $numeroCarnet);
            }
        })
        ->orderBy('a.fecha_vacuna', 'asc')
        ->orderBy('d.nombre', 'asc')
        ->get();

    // ✅ IMPORTANTÍSIMO: si no hay vacunas, no uses $vacunas[0]
    if ($vacunas->isEmpty()) {
        return response()->json([]);
    }

    // Calcular edad
    $hoy = Carbon::now();
    $fechaNacimiento = Carbon::parse($vacunas[0]->fecha_nacimiento);
    $edad = $hoy->diff($fechaNacimiento);

    $vacunas[0]->age = $edad->y . 'a ' . $edad->m . 'm ' . $edad->d . 'd';

    return response()->json($vacunas);
}

public function getVacunasPdf($id, $numeroCarnet = null)
{
    $vacunas = DB::table('vacunas as a')
        ->join('afiliados as b', 'a.afiliado_id', '=', 'b.id')
        ->join('users as c', 'a.user_id', '=', 'c.id')
        ->join('referencia_vacunas as d', 'a.vacunas_id', '=', 'd.id')
        ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroafiliados] as x'), 'b.numero_carnet', '=', 'x.numeroCarnet')
        ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroips] as y'), 'x.numeroCarnet', '=', 'y.numeroCarnet')
        ->leftJoin(DB::connection('sqlsrv_1')->raw('[sga].[dbo].[maestroIpsGru] as z'), 'y.idGrupoIps', '=', 'z.id')
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
            DB::raw('FLOOR((CAST(CONVERT(varchar(8), CONVERT(DATE, a.fecha_vacuna), 112) AS int) - CAST(CONVERT(varchar(8), CONVERT(DATE, b.fecha_nacimiento), 112) AS int)) / 10000) AS edad_anos'),
            DB::raw('DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna) AS total_meses'),
            'a.responsable as responsable',
            'a.regimen as regimen_vacuna'
        )
        ->where(function ($query) use ($id, $numeroCarnet) {
            $query->where('b.id', $id);
            if (!empty($numeroCarnet)) {
                $query->orWhere('b.numero_carnet', $numeroCarnet);
            }
        })
        ->orderBy('a.fecha_vacuna', 'asc')
        ->orderBy('d.nombre', 'asc')
        ->get();

    $paciente = null;
    if ($vacunas->isNotEmpty()) {
        $primero = $vacunas->first();
        $edad = Carbon::parse($primero->fecha_nacimiento)->diff(Carbon::now());
        $paciente = [
            'nombre' => trim(($primero->prim_nom ?? '') . ' ' . ($primero->seg_nom ?? '') . ' ' . ($primero->pri_ape ?? '') . ' ' . ($primero->seg_ape ?? '')),
            'tipo_id' => $primero->tipo_id ?? '',
            'numero_id' => $primero->numero_id ?? '',
            'sexo' => $primero->genero ?? '',
            'fecha_nacimiento' => $primero->fecha_nacimiento ?? '',
            'ips' => $primero->ips ?? '',
            'edad' => $edad->y . 'a ' . $edad->m . 'm ' . $edad->d . 'd',
        ];
    } else {
        $afiliado = DB::table('afiliados')
            ->select('primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'tipo_identificacion', 'numero_identificacion', 'fecha_nacimiento')
            ->where('id', $id)
            ->first();

        $paciente = [
            'nombre' => $afiliado ? trim(($afiliado->primer_nombre ?? '') . ' ' . ($afiliado->segundo_nombre ?? '') . ' ' . ($afiliado->primer_apellido ?? '') . ' ' . ($afiliado->segundo_apellido ?? '')) : 'Afiliado no encontrado',
            'tipo_id' => $afiliado->tipo_identificacion ?? '',
            'numero_id' => $afiliado->numero_identificacion ?? '',
            'sexo' => '',
            'fecha_nacimiento' => $afiliado->fecha_nacimiento ?? '',
            'ips' => '',
            'edad' => '',
        ];
    }

    $pdf = Pdf::loadView('pdf.vacunas_afiliado', [
        'paciente' => $paciente,
        'vacunas' => $vacunas,
        'fechaGeneracion' => now()->format('Y-m-d H:i:s'),
    ])->setPaper('a4', 'portrait');

    $fileName = 'reporte_vacunas_' . ($paciente['numero_id'] ?: $id) . '_' . now()->format('Ymd_His') . '.pdf';
    return $pdf->download($fileName);
}

    public function batchCleanupIndex(Request $request)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->input('search', ''));

        $afiliadosSub = DB::table('afiliados')
            ->select('batch_verifications_id', DB::raw('COUNT(*) as afiliados_count'))
            ->groupBy('batch_verifications_id');

        $vacunasSub = DB::table('vacunas')
            ->select('batch_verifications_id', DB::raw('COUNT(*) as vacunas_count'))
            ->groupBy('batch_verifications_id');

        $lotes = DB::table('batch_verifications as b')
            ->leftJoinSub($afiliadosSub, 'a', function ($join) {
                $join->on('a.batch_verifications_id', '=', 'b.id');
            })
            ->leftJoinSub($vacunasSub, 'v', function ($join) {
                $join->on('v.batch_verifications_id', '=', 'b.id');
            })
            ->select([
                'b.id',
                'b.fecha_cargue',
                DB::raw('COALESCE(a.afiliados_count, 0) as afiliados_count'),
                DB::raw('COALESCE(v.vacunas_count, 0) as vacunas_count'),
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('b.id', 'LIKE', "%{$search}%")
                        ->orWhere('b.fecha_cargue', 'LIKE', "%{$search}%");
                });
            })
            ->orderByDesc('b.id')
            ->paginate(18)
            ->withQueryString();

        $stats = [
            'total_lotes' => DB::table('batch_verifications')->count(),
            'total_afiliados' => DB::table('afiliados')->count(),
            'total_vacunas' => DB::table('vacunas')->count(),
        ];

        $audits = DB::table('batch_cleanup_audits as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select([
                'a.action',
                'a.batches_count',
                'a.afiliados_count',
                'a.vacunas_count',
                'a.batch_ids',
                'a.created_at',
                'u.name as user_name',
            ])
            ->orderByDesc('a.id')
            ->limit(30)
            ->get();

        return view('livewire.batch_cleanup', compact('lotes', 'stats', 'search', 'audits'));
    }

    public function destroyMultipleBatches(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['integer'],
        ], [
            'batch_ids.required' => 'Selecciona al menos un lote para eliminar.',
        ]);

        $deleted = $this->deleteBatchSets($data['batch_ids']);
        $this->registerCleanupAudit($request, 'bulk_delete', $deleted);

        return redirect()
            ->route('batch.cleanup.index')
            ->with('success', "Se eliminaron {$deleted['deleted_batches']} lote(s), {$deleted['deleted_afiliados']} afiliado(s) y {$deleted['deleted_vacunas']} vacuna(s).");
    }

    private function deleteBatchSets(array $ids): array
    {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return [
                'batch_ids' => [],
                'deleted_batches' => 0,
                'deleted_afiliados' => 0,
                'deleted_vacunas' => 0,
            ];
        }

        $existingIds = DB::table('batch_verifications')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($existingIds)) {
            return [
                'batch_ids' => [],
                'deleted_batches' => 0,
                'deleted_afiliados' => 0,
                'deleted_vacunas' => 0,
            ];
        }

        $totalAfiliados = (int) DB::table('afiliados')
            ->whereIn('batch_verifications_id', $existingIds)
            ->count();

        $totalVacunas = (int) DB::table('vacunas')
            ->whereIn('batch_verifications_id', $existingIds)
            ->count();

        $deletedBatches = 0;

        DB::transaction(function () use ($existingIds, &$deletedBatches) {
            foreach (array_chunk($existingIds, 500) as $chunk) {
                DB::table('vacunas')
                    ->whereIn('batch_verifications_id', $chunk)
                    ->delete();

                DB::table('afiliados')
                    ->whereIn('batch_verifications_id', $chunk)
                    ->delete();

                $deletedBatches += DB::table('batch_verifications')
                    ->whereIn('id', $chunk)
                    ->delete();
            }
        });

        return [
            'batch_ids' => $existingIds,
            'deleted_batches' => (int) $deletedBatches,
            'deleted_afiliados' => $totalAfiliados,
            'deleted_vacunas' => $totalVacunas,
        ];
    }

    private function registerCleanupAudit(Request $request, string $action, array $result): void
    {
        try {
            DB::table('batch_cleanup_audits')->insert([
                'user_id' => Auth::id(),
                'action' => $action,
                'batches_count' => (int) ($result['deleted_batches'] ?? 0),
                'afiliados_count' => (int) ($result['deleted_afiliados'] ?? 0),
                'vacunas_count' => (int) ($result['deleted_vacunas'] ?? 0),
                'batch_ids' => !empty($result['batch_ids']) ? implode(',', $result['batch_ids']) : null,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                // Evita conversiones regionales nvarchar->datetime en SQL Server
                'created_at' => DB::raw('GETDATE()'),
                'updated_at' => DB::raw('GETDATE()'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar auditoria de limpieza de lotes', [
                'error' => $e->getMessage(),
            ]);
        }
    }


  
  


    /**
     * Elimina un registro de Batch_verification y sus afiliados y vacunas asociados.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin();
        try {
            $deleted = $this->deleteBatchSets([(int) $id]);
            if (($deleted['deleted_batches'] ?? 0) === 0) {
                return redirect()->back()->with('error', 'El lote no existe o ya fue eliminado.');
            }
            $this->registerCleanupAudit($request, 'single_delete', $deleted);
            return redirect()->back()->with('success', 'Lote eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hubo un problema al eliminar los registros: ' . $e->getMessage());
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
        $destinatarios = ['jsuarez@epsianaswayuu.com'];
    
        $correoExistente = CorreoEnviado::where('user_id', $userId)
            ->where('patient_id', $patientId)
            ->first();
    
        if ($correoExistente) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ya has enviado un correo de solicitud para este paciente.',
                ], 409);
            }
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
        Mail::to($destinatarios)->send(new SolicitudMail($details));
    
        // Registrar envio usando fecha del motor SQL Server para evitar problemas de formato regional.
        DB::table('correos_enviados')->insert([
            'user_id' => $userId,
            'patient_id' => $patientId,
            'sent_at' => DB::raw('GETDATE()'),
            'created_at' => DB::raw('GETDATE()'),
            'updated_at' => DB::raw('GETDATE()'),
        ]);
    
        $successMessage = 'Correo enviado exitosamente.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $successMessage,
                'destinatarios' => $destinatarios,
            ]);
        }

        return redirect()->back()->with('success', $successMessage . ' Destinatarios: ' . implode(', ', $destinatarios));
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


      public function exportVacunas(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    if (! $startDate || ! $endDate) {
        return redirect()->back()->withErrors(['msg' => 'Fechas no proporcionadas']);
    }

    $fileName = "vacunas_{$startDate}_{$endDate}.csv";
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$fileName\"",
    ];

    $callback = function() use ($startDate, $endDate) {
        $out = fopen('php://output','w');

        // Escribimos el BOM para que Excel reconozca UTF-8
        fputs($out, "\xEF\xBB\xBF");

        // 1) Encabezados
        fputcsv($out, [
            'Prestador','IPS PRIMARIA','Fecha de Atención','Tipo de Identificación',
            'Número de Identificación','Primer Nombre','Segundo Nombre','Primer Apellido',
            'Segundo Apellido','Fecha de Nacimiento','Edad (Años)','Edad (Meses)',
            'Edad (Días)','Total de Meses','Esquema Completo','Sexo','Género',
            'Orientación Sexual','Edad Gestacional','País de Nacimiento',
            'Estatus Migratorio','Lugar de Atención del Parto','Régimen','Aseguradora',
            'Pertenencia Étnica','Desplazado','Discapacitado','Fallecido',
            'Víctima de Conflicto','Estudia','País de Residencia','Departamento de Residencia',
            'Municipio de Residencia','Comuna','Área','Dirección','Teléfono Fijo','Celular',
            'Email','Autoriza Llamadas','Autoriza Correos','Contraindicación Vacuna',
            'Enfermedad Contraindicación','Reacción a Biológicos','Síntomas Reacción',
            'Condición Usuaria','Fecha Última Menstruación','Semanas de Gestación',
            'Fecha Probable de Parto','Embarazos Previos','Fecha Antecedente',
            'Tipo de Antecedente','Descripción del Antecedente','Observaciones Especiales',
            'Madre Tipo de Identificación','Madre Identificación','Madre Primer Nombre',
            'Madre Segundo Nombre','Madre Primer Apellido','Madre Segundo Apellido',
            'Madre Correo','Madre Teléfono','Madre Celular','Madre Régimen',
            'Madre Pertenencia Étnica','Madre Desplazada','Cuidador Tipo de Identificación',
            'Cuidador Identificación','Cuidador Primer Nombre','Cuidador Segundo Nombre',
            'Cuidador Primer Apellido','Cuidador Segundo Apellido','Cuidador Parentesco',
            'Cuidador Correo','Cuidador Teléfono','Cuidador Celular','Esquema de Vacunación',
            'Nombre de la Vacuna','Dosis','Laboratorio','Lote','Jeringa','Lote de Jeringa',
            'Diluyente','Lote de Diluyente','Observación','Gotero','Tipo Neumococo',
            'Número de Frascos Utilizados','Fecha de Vacunación','Responsable',
            'Fuente Ingresado en PAIWEB','Motivo No Ingreso','Observaciones','Regimen de la Vacuna',
            'Fecha de Creación',
        ]);

        // 2) Chunked query y escritura
        DB::table('DESNUTRICION.dbo.vacunas as a')
            ->join('afiliados as b',           'b.id',            '=', 'a.afiliado_id')
            ->join('referencia_vacunas as d',  'd.id',            '=', 'a.vacunas_id')
            ->join('users as u',               'u.id',            '=', 'a.user_id')
            ->leftJoin('SGA.dbo.maestroIps as j','b.numero_Carnet', '=', 'j.numeroCarnet')
            ->leftJoin('SGA.dbo.maestroIpsGru as k','j.idGrupoIps', '=', 'k.id')
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                $q->whereBetween('a.fecha_vacuna', [$startDate, $endDate]);
            })
            ->select([
                'u.name as Prestador',
                'k.descrip as ips_primaria',
                'b.fecha_atencion',
                'b.tipo_identificacion',
                'b.numero_identificacion',
                'b.primer_nombre',
                'b.segundo_nombre',
                'b.primer_apellido',
                'b.segundo_apellido',
                'b.fecha_nacimiento',
                DB::raw('DATEDIFF(YEAR, b.fecha_nacimiento, a.fecha_vacuna) AS edad_anos'),
                DB::raw('DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna) % 12 AS edad_meses'),
                DB::raw("
                    CASE
                      WHEN DATEDIFF(
                             DAY,
                             DATEADD(
                               MONTH,
                               DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna),
                               b.fecha_nacimiento
                             ),
                             a.fecha_vacuna
                           ) < 0
                      THEN 0
                      ELSE DATEDIFF(
                             DAY,
                             DATEADD(
                               MONTH,
                               DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna),
                               b.fecha_nacimiento
                             ),
                             a.fecha_vacuna
                           )
                    END AS edad_dias
                "),
                'b.total_meses',
                'b.esquema_completo',
                'b.sexo',
                'b.genero',
                'b.orientacion_sexual',
                'b.edad_gestacional',
                'b.pais_nacimiento',
                'b.estatus_migratorio',
                'b.lugar_atencion_parto',
                'b.regimen',
                'b.aseguradora',
                'b.pertenencia_etnica',
                'b.desplazado',
                'b.discapacitado',
                'b.fallecido',
                'b.victima_conflicto',
                'b.estudia',
                'b.pais_residencia',
                'b.departamento_residencia',
                'b.municipio_residencia',
                'b.comuna',
                'b.area',
                'b.direccion',
                'b.telefono_fijo',
                'b.celular',
                'b.email',
                'b.autoriza_llamadas',
                'b.autoriza_correos',
                'b.contraindicacion_vacuna',
                'b.enfermedad_contraindicacion',
                'b.reaccion_biologicos',
                'b.sintomas_reaccion',
                'b.condicion_usuaria',
                'b.fecha_ultima_menstruacion',
                'b.semanas_gestacion',
                'b.fecha_prob_parto',
                'b.embarazos_previos',
                'b.fecha_antecedente',
                'b.tipo_antecedente',
                'b.descripcion_antecedente',
                'b.observaciones_especiales',
                'b.madre_tipo_identificacion',
                'b.madre_identificacion',
                'b.madre_primer_nombre',
                'b.madre_segundo_nombre',
                'b.madre_primer_apellido',
                'b.madre_segundo_apellido',
                'b.madre_correo',
                'b.madre_telefono',
                'b.madre_celular',
                'b.madre_regimen',
                'b.madre_pertenencia_etnica',
                'b.madre_desplazada',
                'b.cuidador_tipo_identificacion',
                'b.cuidador_identificacion',
                'b.cuidador_primer_nombre',
                'b.cuidador_segundo_nombre',
                'b.cuidador_primer_apellido',
                'b.cuidador_segundo_apellido',
                'b.cuidador_parentesco',
                'b.cuidador_correo',
                'b.cuidador_telefono',
                'b.cuidador_celular',
                'b.esquema_vacunacion',
                'd.nombre as vacuna_nombre',
                'a.docis',
                'a.laboratorio',
                'a.lote',
                'a.jeringa',
                'a.lote_jeringa',
                'a.diluyente',
                'a.lote_diluyente',
                'a.observacion',
                'a.gotero',
                'a.tipo_neumococo',
                'a.num_frascos_utilizados',
                'a.fecha_vacuna',
                'a.responsable',
                'a.fuen_ingresado_paiweb',
                'a.motivo_noingreso',
                'a.observaciones',
                'a.regimen as regimen_vacuna',
                'a.created_at',
            ])
            ->orderBy('a.created_at','desc')
            ->chunk(500, function($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, (array) $r);
                }
            });

        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}

public function importProgress($token)
{
    $data = Cache::get("import_progress:{$token}");

    if (!$data) {
        return response()->json([
            'percent' => 0,
            'message' => 'En espera…',
            'step'    => 'validacion',
            'done'    => false,
            'ok'      => true,
            'done_steps' => [],
        ]);
    }

    return response()->json($data);
}

public function startImport(Request $request)
{
    try {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'No autenticado'], 401);
        }

        $path = $request->file('file')->store('imports');
        $fullPath = storage_path('app/' . $path);

        $token = (string) \Illuminate\Support\Str::uuid();

        $jobRow = \App\Models\ImportJob::create([
            'user_id' => $userId,
            'token' => $token,
            'status' => 'queued',
            'percent' => 0,
            'step' => 'cola',
            'message' => 'En cola…',
            'errors' => null,
            'errors_count' => 0,
            'report_path' => null,
            'batch_verifications_id' => null,
        ]);

        \App\Jobs\ImportAfiliadosExcelJob::dispatch($jobRow->id, $fullPath, $userId, $token)
            ->onQueue('imports');

        return response()->json(['ok' => true, 'token' => $token]);

    } catch (\Throwable $e) {
        Log::error('startImport ERROR: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'userId' => Auth::id(),
        ]);

        return response()->json([
            'ok' => false,
            'message' => 'Error en startImport: '.$e->getMessage(),
        ], 500);
    }
}

public function importStatus(string $token)
{
    $job = ImportJob::where('token', $token)->latest('id')->first();

    if (!$job) {
        return response()->json([
            'status' => 'not_found',
            'percent' => 0,
            'step' => 'init',
            'message' => 'No existe import con ese token.',
            'errors' => [],
            'no_afiliados' => [],
            'vacunas_omitidas' => [],
            'batch_verifications_id' => null,
        ]);
    }

    // 1) Errores del job (como ya lo tienes)
    $errors = [];
    if (!empty($job->errors)) {
        $decoded = json_decode($job->errors, true);
        if (is_array($decoded)) {
            $errors = $decoded;
        } else {
            $errors = [(string)$job->errors];
        }
    }

    // 2) ✅ Leer el resultado final (no_afiliados / vacunas_omitidas / errores) desde Storage JSON
    $noAfiliados = [];
    $vacunasOmitidas = [];
    $erroresJson = [];
    $loadedDetails = [];

    $path = "imports/{$token}_resultado.json";
    if (Storage::exists($path)) {
        $payload = json_decode(Storage::get($path), true);

        if (is_array($payload)) {
            $noAfiliados = $payload['no_afiliados'] ?? [];
            $vacunasOmitidas = $payload['vacunas_omitidas'] ?? [];
            $erroresJson = $payload['errores'] ?? [];
        }
    }

    // 2.5) ✅ Detalle de lo cargado (solo si terminó bien)
    $status = strtolower((string)($job->status ?? ''));
    if ($status === 'done' && empty($errors)) {
        $loadedDetails = $this->buildLoadedDetailsForConsole((int)($job->batch_verifications_id ?? 0));
    }

    // 3) ✅ Unir errores del job + errores del json (sin duplicar)
    if (is_array($erroresJson) && !empty($erroresJson)) {
        foreach ($erroresJson as $e) {
            $e = trim((string)$e);
            if ($e !== '' && !in_array($e, $errors, true)) {
                $errors[] = $e;
            }
        }
    }

    // 4) ✅ PARA QUE SALGA EN LA VENTANITA NEGRA SIN CAMBIAR TU JS:
    //    Convertimos NO afiliados y Vacunas omitidas en líneas de texto dentro de errors[]
    //    (Tu JS solo pinta errors[], entonces aquí lo resolvemos)
    if (is_array($noAfiliados) && count($noAfiliados) > 0) {
        $errors[] = "==============================";
        $errors[] = "NO AFILIADOS (no existen en BD externa)";
        $errors[] = "==============================";

        foreach ($noAfiliados as $x) {
            if (!is_array($x)) continue;

            $fila  = $x['fila_excel'] ?? '?';
            $tipo  = $x['tipo_identificacion'] ?? '';
            $num   = $x['numero_identificacion'] ?? '';
            $mot   = $x['motivo'] ?? 'No existe en BD externa';

            $errors[] = "Fila {$fila}: {$tipo} {$num} — {$mot}";
        }
    }

    if (is_array($vacunasOmitidas) && count($vacunasOmitidas) > 0) {
        $errors[] = "==============================";
        $errors[] = "VACUNAS NO INSERTADAS (ya existen / repetidas / inválidas)";
        $errors[] = "==============================";

        foreach ($vacunasOmitidas as $x) {
            if (!is_array($x)) continue;

            $fila   = $x['fila_excel'] ?? '?';
            $tipo = $x['tipo_identificacion'] ?? '';
            $num  = $x['numero_identificacion'] ?? '';

            $who = trim("{$tipo} {$num}");
            if ($who === '') $who = 'Identificación desconocida';

            $vacunaNombre = $x['vacuna_nombre'] ?? null;
            $vacId = $x['vacunas_id'] ?? null;
            $vacuna = $vacunaNombre ? $vacunaNombre : ("vacunas_id=" . ($vacId ?? '?'));

            $docis = $x['docis'] ?? null;
            $docisTxt = $docis ? " DOCIS:{$docis}" : "";

            $motivo = $x['motivo'] ?? 'No se insertó.';

            $errors[] = "Fila {$fila}: {$who} — {$vacuna}{$docisTxt} — {$motivo}";
        }
    }

    // (Opcional) evitar que el payload se vuelva enorme en UI
    if (count($errors) > 5000) {
        $errors = array_slice($errors, 0, 5000);
        $errors[] = "… (Se truncó la lista de mensajes a 5000 líneas)";
    }

    return response()->json([
        'status' => (string)$job->status,                 // queued | running | done | failed
        'percent' => (int)($job->percent ?? 0),           // 0..100
        'step' => (string)($job->step ?? ''),
        'message' => (string)($job->message ?? ''),

        // ✅ Esto es lo que tu JS pinta en la ventanita negra
        'errors' => $errors,

        // ✅ Además los mando separados (por si luego quieres pestañas)
        'no_afiliados' => $noAfiliados,
        'vacunas_omitidas' => $vacunasOmitidas,
        'loaded_details' => $loadedDetails,

        'batch_verifications_id' => $job->batch_verifications_id ?? null,
    ]);
}

private function buildLoadedDetailsForConsole(int $batchId, int $limitVacunas = 400): array
{
    if ($batchId <= 0) {
        return [];
    }

    try {
        $rows = DB::table('vacunas as v')
            ->join('afiliados as a', 'a.id', '=', 'v.afiliado_id')
            ->join('referencia_vacunas as rv', 'rv.id', '=', 'v.vacunas_id')
            ->where('v.batch_verifications_id', $batchId)
            ->select([
                'a.tipo_identificacion',
                'a.numero_identificacion',
                'a.numero_carnet',
                'a.primer_nombre',
                'a.segundo_nombre',
                'a.primer_apellido',
                'a.segundo_apellido',
                'rv.nombre as vacuna_nombre',
                'v.docis',
                'v.lote',
                'v.regimen',
            ])
            ->orderBy('a.numero_identificacion')
            ->orderBy('rv.id')
            ->limit($limitVacunas)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $lines = [];
        $lines[] = "==============================";
        $lines[] = "DETALLE CARGADO (AFILIADOS Y VACUNAS)";
        $lines[] = "==============================";

        $lastAfiliadoKey = null;
        foreach ($rows as $r) {
            $afiliadoKey = trim(($r->tipo_identificacion ?? '') . '|' . ($r->numero_identificacion ?? ''));
            if ($afiliadoKey !== $lastAfiliadoKey) {
                $nombre = trim(implode(' ', array_filter([
                    $r->primer_nombre ?? '',
                    $r->segundo_nombre ?? '',
                    $r->primer_apellido ?? '',
                    $r->segundo_apellido ?? '',
                ])));

                $tipo = (string)($r->tipo_identificacion ?? '');
                $num = (string)($r->numero_identificacion ?? '');
                $carnet = (string)($r->numero_carnet ?? '');
                $lines[] = "Afiliado: {$tipo} {$num} | Carnet: {$carnet} | Nombre: {$nombre}";
                $lastAfiliadoKey = $afiliadoKey;
            }

            $vacuna = (string)($r->vacuna_nombre ?? 'Vacuna');
            $dosis = (string)($r->docis ?? 'SIN DOSIS');
            $lote = (string)($r->lote ?? 'SIN LOTE');
            $regimen = (string)($r->regimen ?? 'SIN REGIMEN');
            $lines[] = "  - {$vacuna} | Dosis: {$dosis} | Lote: {$lote} | Regimen: {$regimen}";
        }

        $totalVacunas = (int) DB::table('vacunas')->where('batch_verifications_id', $batchId)->count();
        if ($totalVacunas > $limitVacunas) {
            $faltantes = $totalVacunas - $limitVacunas;
            $lines[] = "… (Se muestran {$limitVacunas} de {$totalVacunas} vacunas. Faltan {$faltantes} líneas)";
        }

        return $lines;
    } catch (\Throwable $e) {
        Log::warning('No se pudo construir detalle cargado para consola', [
            'batch' => $batchId,
            'error' => $e->getMessage(),
        ]);

        return [];
    }
}

public function loadSummary(Request $request)
{
    [$startDate, $endDate] = $this->validatedDateRange($request);
    $filters = $this->extractLoadSummaryFilters($request);
    $report = $this->buildLoadSummaryReport($startDate, $endDate, $filters);

    return response()->json(array_merge([
        'ok' => true,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'filters' => $filters,
    ], $report));
}

public function loadSummaryPdf(Request $request)
{
    [$startDate, $endDate] = $this->validatedDateRange($request);
    $filters = $this->extractLoadSummaryFilters($request);
    $report = $this->buildLoadSummaryReport($startDate, $endDate, $filters);

    $pdf = Pdf::loadView('livewire.reporte_cargue_pdf', [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'rows' => $report['rows'],
        'totals' => $report['totals'],
        'filters' => $filters,
        'generatedAt' => now()->format('d/m/Y H:i:s'),
    ])->setPaper('a4', 'landscape');

    return $pdf->download("informe_cargue_{$startDate}_{$endDate}.pdf");
}

private function validatedDateRange(Request $request): array
{
    $validated = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    return [$validated['start_date'], $validated['end_date']];
}

private function extractLoadSummaryFilters(Request $request): array
{
    $validated = $request->validate([
        'user_id' => 'nullable|integer|exists:users,id',
        'only_without_load' => 'nullable',
    ]);

    return [
        'user_id' => isset($validated['user_id']) ? (int) $validated['user_id'] : null,
        'only_without_load' => filter_var($request->input('only_without_load', false), FILTER_VALIDATE_BOOLEAN),
    ];
}

private function buildLoadSummaryReport(string $startDate, string $endDate, array $filters = []): array
{
    $summaryQuery = DB::table('users as u')
        ->leftJoin('vacunas as v', function ($join) use ($startDate, $endDate) {
            $join->on('u.id', '=', 'v.user_id')
                ->whereBetween(DB::raw('CAST(v.created_at AS DATE)'), [$startDate, $endDate]);
        })
        ->select([
            'u.id',
            'u.name',
            'u.email',
            DB::raw('COUNT(v.id) as vacunas_count'),
            DB::raw('COUNT(DISTINCT v.afiliado_id) as afiliados_count'),
            DB::raw('COUNT(DISTINCT v.batch_verifications_id) as lotes_count'),
            DB::raw('MAX(v.created_at) as last_load_at'),
        ])
        ->groupBy('u.id', 'u.name', 'u.email')
        ->orderByRaw('COUNT(v.id) DESC')
        ->orderBy('u.name');

    if (!empty($filters['user_id'])) {
        $summaryQuery->where('u.id', (int) $filters['user_id']);
    }

    $summaryRows = $summaryQuery->get();

    $afiliadoQuery = DB::table('vacunas as v')
        ->join('afiliados as a', 'a.id', '=', 'v.afiliado_id')
        ->whereBetween(DB::raw('CAST(v.created_at AS DATE)'), [$startDate, $endDate])
        ->select([
            'v.user_id',
            'a.id as afiliado_id',
            DB::raw("COALESCE(NULLIF(a.numero_identificacion, ''), CAST(a.id as varchar(50))) as afiliado_documento"),
            DB::raw("LTRIM(RTRIM(CONCAT(ISNULL(a.primer_nombre,''), ' ', ISNULL(a.segundo_nombre,''), ' ', ISNULL(a.primer_apellido,''), ' ', ISNULL(a.segundo_apellido,'')))) as afiliado_nombre"),
        ])
        ->groupBy(
            'v.user_id',
            'a.id',
            'a.numero_identificacion',
            'a.primer_nombre',
            'a.segundo_nombre',
            'a.primer_apellido',
            'a.segundo_apellido'
        )
        ->orderBy('v.user_id')
        ->orderBy('afiliado_nombre');

    if (!empty($filters['user_id'])) {
        $afiliadoQuery->where('v.user_id', (int) $filters['user_id']);
    }

    $afiliadoRows = $afiliadoQuery->get();

    $solicitudesQuery = DB::table('correos_enviados')
        ->select([
            'user_id',
            DB::raw('COUNT(id) as solicitudes_count'),
        ])
        ->whereBetween(DB::raw('CAST(sent_at AS DATE)'), [$startDate, $endDate])
        ->groupBy('user_id');

    if (!empty($filters['user_id'])) {
        $solicitudesQuery->where('user_id', (int) $filters['user_id']);
    }

    $solicitudesRows = $solicitudesQuery->get()->keyBy('user_id');

    $afiliadosByUser = [];
    foreach ($afiliadoRows as $af) {
        $afiliadosByUser[$af->user_id][] = [
            'id' => (int) $af->afiliado_id,
            'documento' => (string) $af->afiliado_documento,
            'nombre' => trim((string) $af->afiliado_nombre),
        ];
    }

    $rowsCollection = $summaryRows->map(function ($row) use ($afiliadosByUser, $solicitudesRows) {
        $userAfiliados = $afiliadosByUser[$row->id] ?? [];
        $preview = collect($userAfiliados)
            ->take(6)
            ->map(function ($a) {
                return $a['documento'] . ' - ' . $a['nombre'];
            })
            ->values()
            ->all();

        return [
            'user_id' => (int) $row->id,
            'usuario' => $row->name ?: 'Sin nombre',
            'correo' => $row->email ?: 'Sin correo',
            'vacunas_count' => (int) $row->vacunas_count,
            'afiliados_count' => (int) $row->afiliados_count,
            'lotes_count' => (int) $row->lotes_count,
            'last_load_at' => $row->last_load_at ? Carbon::parse($row->last_load_at)->format('Y-m-d H:i:s') : null,
            'solicitudes_count' => (int) ($solicitudesRows[$row->id]->solicitudes_count ?? 0),
            'afiliados' => $userAfiliados,
            'afiliados_preview' => $preview,
            'afiliados_extra_count' => max(count($userAfiliados) - count($preview), 0),
        ];
    });

    if (!empty($filters['only_without_load'])) {
        $rowsCollection = $rowsCollection->where('vacunas_count', '=', 0);
    }

    $rows = $rowsCollection->values()->all();

    $usersWithLoad = collect($rows)->where('vacunas_count', '>', 0)->count();
    $usersWithoutLoad = collect($rows)->where('vacunas_count', '=', 0)->count();
    $totalVacunas = collect($rows)->sum('vacunas_count');
    $totalAfiliados = collect($rows)
        ->flatMap(function ($r) {
            return collect($r['afiliados'] ?? [])->pluck('id');
        })
        ->unique()
        ->count();

    return [
        'rows' => $rows,
        'users_catalog' => collect($summaryRows)->map(function ($u) {
            return [
                'id' => (int) $u->id,
                'name' => $u->name ?: 'Sin nombre',
            ];
        })->values()->all(),
        'totals' => [
            'users_total' => count($rows),
            'users_with_load' => $usersWithLoad,
            'users_without_load' => $usersWithoutLoad,
            'vacunas_total' => (int) $totalVacunas,
            'afiliados_total' => (int) $totalAfiliados,
        ],
    ];
}


}
