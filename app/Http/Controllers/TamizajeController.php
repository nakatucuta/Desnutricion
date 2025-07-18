<?php

namespace App\Http\Controllers;
use App\Models\batch_verifications;
use Illuminate\Http\Request;
use App\Models\Tamizaje;
use App\Models\TipoTamizaje;
use App\Models\ResultadoTamizaje;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // ESTE ES EL CORRECTO
use App\Exports\TamizajesExport;    // Export personalizado
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;  // <-- este use faltaba
use Illuminate\Support\Facades\Mail;
class TamizajeController extends Controller
{

    private $batch_verifications_id; // Almacena el ID único para esta importación

    public function __construct()
    {
        // Creando una única instancia de Batch_verification al inicio de la importación
        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->save();

        // Almacenar el ID para su uso posterior
        $this->batch_verifications_id = $verificacion->id;
    }
    
    /**
     * Muestra el formulario para subir el archivo Excel.
     */
    public function index()
    {
        return view('tamizajes.excel_import');
    }

    /**
     * Procesa el archivo Excel y guarda la información en la base de datos.
     */

    // ...

    
//     public function import(Request $request)
// {
//     $usuario_activo = Auth::id();

//     // 1) Validar el archivo Excel
//     $request->validate([
//         'excel_file' => 'required|file|mimes:xlsx,xls'
//     ]);

//     $file = $request->file('excel_file');
//     // Convertimos la primera hoja en un array
//     $data = Excel::toArray([], $file);

//     if (empty($data) || !isset($data[0])) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => 'El archivo no contiene datos válidos.'
//         ], 400);
//     }

//     // 2) Obtenemos la primera hoja
//     $rows = $data[0];
//     // Asumimos que la fila 0 es encabezado y la omitimos
//     $startRow = 1;

//     DB::beginTransaction();

//     try {
//         $identificacionesSinCarnet = [];

//         foreach (array_slice($rows, $startRow) as $rowIndex => $row) {
//             // Esperamos al menos 6 columnas:
//             // [0] => TIPO_ID, [1] => ID, [2] => FECHA, [3] => tipo_tamizaje_id, [4] => resultado, [5] => puntuación
//             if (count($row) < 5) {
//                 continue;
//             }

//             // Mapeo básico
//             $tipoIdentificacion   = (string)$row[0];
//             $numeroIdentificacion = (string)$row[1];

//             // 3) Parsear la fecha
//             try {
//                 if (is_numeric($row[2])) {
//                     // Fecha Excel (número)
//                     $fechaT = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]);
//                     $fechaTamizaje = $fechaT->format('Y-m-d');
//                 } else {
//                     // Fecha string tipo "2026-02-15"
//                     $fechaTamizaje = \Carbon\Carbon::parse($row[2])->format('Y-m-d');
//                 }
//             } catch (\Exception $e) {
//                 $excelRow = $rowIndex + $startRow + 1;
//                 DB::rollBack();
//                 return response()->json([
//                     'status'  => 'error',
//                     'message' => "Error al parsear la fecha en la fila {$excelRow}. No se cargó nada."
//                 ], 422);
//             }

//             // 4) tipoTamizajeId, codigoResultado
//             $tipoTamizajeId  = (int)$row[3];  // p.ej 11 o 12
//             $codeResultado   = (int)$row[4];  // p.ej 1 o 2
//             // Posible puntuación en col 5
//             $scoreRaw = isset($row[5]) ? $row[5] : null;
//             $score    = $scoreRaw !== null ? (int)$scoreRaw : null;

//             // Buscar tipo de tamizaje
//             $tipoTamizaje = \App\Models\TipoTamizaje::find($tipoTamizajeId);
//             if (!$tipoTamizaje) {
//                 $excelRow = $rowIndex + $startRow + 1;
//                 DB::rollBack();
//                 return response()->json([
//                     'status'  => 'error',
//                     'message' => "El ID de tipo_tamizaje '{$tipoTamizajeId}' no existe (fila {$excelRow})."
//                 ], 422);
//             }

//             // Buscar resultado (por code)
//             $resultadoTamizaje = \App\Models\ResultadoTamizaje::where('tipo_tamizaje_id', $tipoTamizajeId)
//                 ->where('code', $codeResultado)
//                 ->first();

//             if (!$resultadoTamizaje) {
//                 $excelRow = $rowIndex + $startRow + 1;
//                 DB::rollBack();
//                 return response()->json([
//                     'status'  => 'error',
//                     'message' => "El resultado '{$codeResultado}' no existe para tipo_tamizaje='{$tipoTamizajeId}' (fila {$excelRow})."
//                 ], 422);
//             }

//             // 5) Buscar carnet en la BD externa
//             $numero_carnet = DB::connection('sqlsrv_1')
//                 ->table('maestroIdentificaciones')
//                 ->where('identificacion', $numeroIdentificacion)
//                 ->where('tipoIdentificacion', $tipoIdentificacion)
//                 ->value('numeroCarnet');

//             if (is_null($numero_carnet)) {
//                 $identificacionesSinCarnet[] = $numeroIdentificacion;
//                 continue;
//             }

//             // Por defecto
//             $valorLaboratorio  = null;
//             $descriptResultado = null;

//             // 6) Si es tipoTamizaje 11 => (rango ASSIST - tabaco / alcohol)
//             //    Si es tipoTamizaje 12 => (rango ALCOHOL, hombres / mujeres)
//             //    Ajusta la lógica a tu gusto:

//             // Lógica anterior para tipoTamizaje=11, code=1,2 ...
//             if ($tipoTamizaje->id == 11 && $score !== null) {
//                 if ($resultadoTamizaje->id == 66) {
//                     // 0-3, 4-26, 27+
//                     $valorLaboratorio = $score;
//                     if ($score <= 3) {
//                         $descriptResultado = "RIESGO BAJO (0-3) - SIN INTERVENCIÓN";
//                     } elseif ($score <= 26) {
//                         $descriptResultado = "RIESGO MODERADO (4-26) - INTERVENCIÓN BREVE";
//                     } else {
//                         $descriptResultado = "RIESGO ALTO (27+) - TRATAMIENTO MÁS INTENSIVO";
//                     }
//                 } elseif ($resultadoTamizaje->id == 67) {
//                     // 0-10, 11-26, 27+
//                     $valorLaboratorio = $score;
//                     if ($score <= 10) {
//                         $descriptResultado = "RIESGO BAJO (0-10) - SIN INTERVENCIÓN";
//                     } elseif ($score <= 26) {
//                         $descriptResultado = "RIESGO MODERADO (11-26) - INTERVENCIÓN BREVE";
//                     } else {
//                         $descriptResultado = "RIESGO ALTO (27+) - TRATAMIENTO MÁS INTENSIVO";
//                     }
//                 }
//             }

//             // NUEVO: si es tipoTamizaje=12, code=1 => HOMBRES
//             //   8+ => Fuerte probabilidad de daños
//             //   15+ => Indicativo de una probable dependencia
//             //   20+ => Dependencia del alcohol
//             // code=2 => MUJERES
//             //   7+ => Fuerte prob, 13+ => probable dependencia, 20+ => dependencia

//             if ($tipoTamizaje->id == 12 && $score !== null) {
//                 $valorLaboratorio = $score; // lo guardamos directo
//                 if ($resultadoTamizaje->id == 68) {
//                     // HOMBRES
//                     // Chequeamos de mayor a menor
//                     if ($score >= 20) {
//                         $descriptResultado = "Dependencia del alcohol (20+ puntos)";
//                     } elseif ($score >= 15) {
//                         $descriptResultado = "Indicativo de una probable dependencia (15+ puntos)";
//                     } elseif ($score >= 8) {
//                         $descriptResultado = "Fuerte probabilidad de daños debido al consumo de alcohol (8+ puntos)";
//                     }
//                 } elseif ($resultadoTamizaje->id == 69) {
//                     // MUJERES
//                     if ($score >= 20) {
//                         $descriptResultado = "Dependencia del alcohol (20+ puntos)";
//                     } elseif ($score >= 13) {
//                         $descriptResultado = "Indicativo de una probable dependencia (13+ puntos)";
//                     } elseif ($score >= 7) {
//                         $descriptResultado = "Fuerte probabilidad de daños debido al consumo de alcohol (7+ puntos)";
//                     }
//                 }
//             }

//             if ($tipoTamizaje->id == 13 && $score !== null) {
//                 $valorLaboratorio = $score; // Guardamos el valor directamente
//                 if ($resultadoTamizaje->id == 70) {
//                     if ($score <= 20) {
//                         $descriptResultado = "Dependencia Total";
//                     } elseif ($score <= 60) {
//                         $descriptResultado = "Dependencia Severa";
//                     } elseif ($score <= 89) {
//                         $descriptResultado = "Dependencia Moderada";
//                     } elseif ($score == 90) {
//                         $descriptResultado = "Independencia *Uso de silla de ruedas";
//                     } elseif ($score <= 99) {
//                         $descriptResultado = "Dependencia Escasa";
//                     } elseif ($score == 100) {
//                         $descriptResultado = "Independencia";
//                     }
//                 }
//             }
            
//             if ($tipoTamizaje->id == 14 && $score !== null) {
//                 $valorLaboratorio = $score; // Guarda el valor directo
            
//                 // Caso HOMBRES (resultadoTamizaje->id == 1)
//                 if ($resultadoTamizaje->id == 71) {
//                     if ($score == 0) {
//                         $descriptResultado = "Dependencia total";
//                     } elseif ($score == 1) {
//                         $descriptResultado = "Dependencia grave";
//                     } elseif ($score >= 2 && $score <= 3) {
//                         $descriptResultado = "Dependencia moderada";
//                     } elseif ($score == 4) {
//                         $descriptResultado = "Dependencia ligera";
//                     } elseif ($score == 5) {
//                         $descriptResultado = "Autónomo";
//                     }
            
//                 // Caso MUJERES (resultadoTamizaje->id == 2)
//                 } elseif ($resultadoTamizaje->id == 72) {
//                     if ($score >= 0 && $score <= 1) {
//                         $descriptResultado = "Dependencia total";
//                     } elseif ($score >= 2 && $score <= 3) {
//                         $descriptResultado = "Dependencia grave";
//                     } elseif ($score >= 4 && $score <= 5) {
//                         $descriptResultado = "Dependencia moderada";
//                     } elseif ($score >= 6 && $score <= 7) {
//                         $descriptResultado = "Dependencia ligera";
//                     } elseif ($score == 8) {
//                         $descriptResultado = "Autónoma";
//                     }
//                 }
//             }

//             if ($tipoTamizaje->id == 18 && $score !== null) {
//                 // Ejemplo: supón que el resultadoTamizaje->id == 50
//                 // (ajusta a tus IDs reales)
//                 if ($resultadoTamizaje->id == 82) {
            
//                     // Guardamos el valor directo
//                     $valorLaboratorio = $score;
            
//                     if ($score < 5) {
//                         $descriptResultado = "Leve ( < 5 )";
//                     } elseif ($score <= 15) {
//                         $descriptResultado = "Moderado (5 - 15)";
//                     } elseif ($score <= 25) {
//                         $descriptResultado = "Grave (16 - 25)";
//                     } else {
//                         // $score > 25
//                         $descriptResultado = "Muy grave ( > 25 )";
//                     }
//                 }
//             }
            
            

//             // Insertar fila a fila
//             \App\Models\Tamizaje::create([
//                 'tipo_identificacion'   => $tipoIdentificacion,
//                 'numero_identificacion' => $numeroIdentificacion,
//                 'fecha_tamizaje'        => $fechaTamizaje,
//                 'numero_carnet'         => $numero_carnet,
//                 'tipo_tamizaje_id'      => $tipoTamizaje->id,
//                 'resultado_tamizaje_id' => $resultadoTamizaje->id,
//                 'user_id'               => $usuario_activo,
//                 'valor_laboratorio'     => $valorLaboratorio,
//                 'descript_resultado'    => $descriptResultado,
//                 'batch_verifications_id' => $this->batch_verifications_id, // Clave foránea
//             ]);
//         }

//         // Si hay filas sin carnet
//         if (!empty($identificacionesSinCarnet)) {
//             DB::rollBack();
//             $listado = implode(', ', $identificacionesSinCarnet);
//             return response()->json([
//                 'status'  => 'error',
//                 'message' => "No se encontró número de carnet para: {$listado}. No se cargó nada."
//             ], 422);
//         }

//         DB::commit();
//         return response()->json([
//             'status'  => 'success',
//             'message' => '¡Datos importados exitosamente!'
//         ], 200);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json([
//             'status'  => 'error',
//             'message' => "Error inesperado: {$e->getMessage()}"
//         ], 500);
//     }
// }

public function import(Request $request)
    {
        $usuario_activo = Auth::id();
        $user           = Auth::user();

        // Aquí defines los correos adicionales que también deben recibir el resumen:
        $extraEmails = [
            'jefe@dominio.com',
            'colega@dominio.com',
        ];

        // 1) Validar el archivo Excel
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $file = $request->file('excel_file');
        // Convertimos la primera hoja en un array
        $data = Excel::toArray([], $file);

        if (empty($data) || !isset($data[0])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El archivo no contiene datos válidos.'
            ], 400);
        }

        // 2) Obtenemos la primera hoja
        $rows                      = $data[0];
        // Asumimos que la fila 0 es encabezado y la omitimos
        $startRow                  = 1;
        $identificacionesSinCarnet = [];
        // Para el correo, guardamos un resumen de cada fila insertada
        $detallesImportados        = [];

        DB::beginTransaction();

        try {
            foreach (array_slice($rows, $startRow) as $rowIndex => $row) {
                // Esperamos al menos 6 columnas
                if (count($row) < 5) {
                    continue;
                }

                // Mapeo básico
                $tipoIdentificacion   = (string)$row[0];
                $numeroIdentificacion = (string)$row[1];

                // 3) Parsear la fecha
                try {
                    if (is_numeric($row[2])) {
                        // Fecha Excel (número)
                        $fechaT = ExcelDate::excelToDateTimeObject($row[2]);
                        $fechaTamizaje = $fechaT->format('Y-m-d');
                    } else {
                        // Fecha string tipo "2026-02-15"
                        $fechaTamizaje = Carbon::parse($row[2])->format('Y-m-d');
                    }
                } catch (Exception $e) {
                    $excelRow = $rowIndex + $startRow + 1;
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Error al parsear la fecha en la fila {$excelRow}. No se cargó nada."
                    ], 422);
                }

                // 4) tipoTamizajeId, codigoResultado
                $tipoTamizajeId  = (int)$row[3];  // p.ej 11 o 12
                $codeResultado   = (int)$row[4];  // p.ej 1 o 2
                $scoreRaw        = $row[5] ?? null;
                $score           = $scoreRaw !== null ? (int)$scoreRaw : null;

                // 5) Buscar tipo de tamizaje
                $tipoTamizaje = \App\Models\TipoTamizaje::find($tipoTamizajeId);
                if (!$tipoTamizaje) {
                    $excelRow = $rowIndex + $startRow + 1;
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "El ID de tipo_tamizaje '{$tipoTamizajeId}' no existe (fila {$excelRow})."
                    ], 422);
                }

                // 6) Buscar resultado (por code)
                $resultadoTamizaje = \App\Models\ResultadoTamizaje::where('tipo_tamizaje_id', $tipoTamizajeId)
                    ->where('code', $codeResultado)
                    ->first();
                if (!$resultadoTamizaje) {
                    $excelRow = $rowIndex + $startRow + 1;
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "El resultado '{$codeResultado}' no existe para tipo_tamizaje='{$tipoTamizajeId}' (fila {$excelRow})."
                    ], 422);
                }

                // 7) Buscar carnet en la BD externa
                $numero_carnet = DB::connection('sqlsrv_1')
                    ->table('maestroIdentificaciones')
                    ->where('identificacion',    $numeroIdentificacion)
                    ->where('tipoIdentificacion', $tipoIdentificacion)
                    ->value('numeroCarnet');

                if (is_null($numero_carnet)) {
                    $identificacionesSinCarnet[] = $numeroIdentificacion;
                    continue;
                }

                // 8) Cálculo de valor_laboratorio y descript_resultado (tu lógica)
                $valorLaboratorio  = null;
                $descriptResultado = null;
                // ... aquí va exactamente tu código para cada tipoTamizaje ...
                   // 6) Si es tipoTamizaje 11 => (rango ASSIST - tabaco / alcohol)
            //    Si es tipoTamizaje 12 => (rango ALCOHOL, hombres / mujeres)
            //    Ajusta la lógica a tu gusto:

            // Lógica anterior para tipoTamizaje=11, code=1,2 ...
            if ($tipoTamizaje->id == 11 && $score !== null) {
                if ($resultadoTamizaje->id == 66) {
                    // 0-3, 4-26, 27+
                    $valorLaboratorio = $score;
                    if ($score <= 3) {
                        $descriptResultado = "RIESGO BAJO (0-3) - SIN INTERVENCIÓN";
                    } elseif ($score <= 26) {
                        $descriptResultado = "RIESGO MODERADO (4-26) - INTERVENCIÓN BREVE";
                    } else {
                        $descriptResultado = "RIESGO ALTO (27+) - TRATAMIENTO MÁS INTENSIVO";
                    }
                } elseif ($resultadoTamizaje->id == 67) {
                    // 0-10, 11-26, 27+
                    $valorLaboratorio = $score;
                    if ($score <= 10) {
                        $descriptResultado = "RIESGO BAJO (0-10) - SIN INTERVENCIÓN";
                    } elseif ($score <= 26) {
                        $descriptResultado = "RIESGO MODERADO (11-26) - INTERVENCIÓN BREVE";
                    } else {
                        $descriptResultado = "RIESGO ALTO (27+) - TRATAMIENTO MÁS INTENSIVO";
                    }
                }
            }

            // NUEVO: si es tipoTamizaje=12, code=1 => HOMBRES
            //   8+ => Fuerte probabilidad de daños
            //   15+ => Indicativo de una probable dependencia
            //   20+ => Dependencia del alcohol
            // code=2 => MUJERES
            //   7+ => Fuerte prob, 13+ => probable dependencia, 20+ => dependencia

            if ($tipoTamizaje->id == 12 && $score !== null) {
                $valorLaboratorio = $score; // lo guardamos directo
                if ($resultadoTamizaje->id == 68) {
                    // HOMBRES
                    // Chequeamos de mayor a menor
                    if ($score >= 20) {
                        $descriptResultado = "Dependencia del alcohol (20+ puntos)";
                    } elseif ($score >= 15) {
                        $descriptResultado = "Indicativo de una probable dependencia (15+ puntos)";
                    } elseif ($score >= 8) {
                        $descriptResultado = "Fuerte probabilidad de daños debido al consumo de alcohol (8+ puntos)";
                    }
                } elseif ($resultadoTamizaje->id == 69) {
                    // MUJERES
                    if ($score >= 20) {
                        $descriptResultado = "Dependencia del alcohol (20+ puntos)";
                    } elseif ($score >= 13) {
                        $descriptResultado = "Indicativo de una probable dependencia (13+ puntos)";
                    } elseif ($score >= 7) {
                        $descriptResultado = "Fuerte probabilidad de daños debido al consumo de alcohol (7+ puntos)";
                    }
                }
            }

            if ($tipoTamizaje->id == 13 && $score !== null) {
                $valorLaboratorio = $score; // Guardamos el valor directamente
                if ($resultadoTamizaje->id == 70) {
                    if ($score <= 20) {
                        $descriptResultado = "Dependencia Total";
                    } elseif ($score <= 60) {
                        $descriptResultado = "Dependencia Severa";
                    } elseif ($score <= 89) {
                        $descriptResultado = "Dependencia Moderada";
                    } elseif ($score == 90) {
                        $descriptResultado = "Independencia *Uso de silla de ruedas";
                    } elseif ($score <= 99) {
                        $descriptResultado = "Dependencia Escasa";
                    } elseif ($score == 100) {
                        $descriptResultado = "Independencia";
                    }
                }
            }
            
            if ($tipoTamizaje->id == 14 && $score !== null) {
                $valorLaboratorio = $score; // Guarda el valor directo
            
                // Caso HOMBRES (resultadoTamizaje->id == 1)
                if ($resultadoTamizaje->id == 71) {
                    if ($score == 0) {
                        $descriptResultado = "Dependencia total";
                    } elseif ($score == 1) {
                        $descriptResultado = "Dependencia grave";
                    } elseif ($score >= 2 && $score <= 3) {
                        $descriptResultado = "Dependencia moderada";
                    } elseif ($score == 4) {
                        $descriptResultado = "Dependencia ligera";
                    } elseif ($score == 5) {
                        $descriptResultado = "Autónomo";
                    }
            
                // Caso MUJERES (resultadoTamizaje->id == 2)
                } elseif ($resultadoTamizaje->id == 72) {
                    if ($score >= 0 && $score <= 1) {
                        $descriptResultado = "Dependencia total";
                    } elseif ($score >= 2 && $score <= 3) {
                        $descriptResultado = "Dependencia grave";
                    } elseif ($score >= 4 && $score <= 5) {
                        $descriptResultado = "Dependencia moderada";
                    } elseif ($score >= 6 && $score <= 7) {
                        $descriptResultado = "Dependencia ligera";
                    } elseif ($score == 8) {
                        $descriptResultado = "Autónoma";
                    }
                }
            }

            if ($tipoTamizaje->id == 18 && $score !== null) {
                // Ejemplo: supón que el resultadoTamizaje->id == 50
                // (ajusta a tus IDs reales)
                if ($resultadoTamizaje->id == 82) {
            
                    // Guardamos el valor directo
                    $valorLaboratorio = $score;
            
                    if ($score < 5) {
                        $descriptResultado = "Leve ( < 5 )";
                    } elseif ($score <= 15) {
                        $descriptResultado = "Moderado (5 - 15)";
                    } elseif ($score <= 25) {
                        $descriptResultado = "Grave (16 - 25)";
                    } else {
                        // $score > 25
                        $descriptResultado = "Muy grave ( > 25 )";
                    }
                }
            }
                // 9) Insertar fila
                $tamizaje = \App\Models\Tamizaje::create([
                    'tipo_identificacion'    => $tipoIdentificacion,
                    'numero_identificacion'  => $numeroIdentificacion,
                    'fecha_tamizaje'         => $fechaTamizaje,
                    'numero_carnet'          => $numero_carnet,
                    'tipo_tamizaje_id'       => $tipoTamizaje->id,
                    'resultado_tamizaje_id'  => $resultadoTamizaje->id,
                    'user_id'                => $usuario_activo,
                    'valor_laboratorio'      => $valorLaboratorio,
                    'descript_resultado'     => $descriptResultado,
                    'batch_verifications_id' => $this->batch_verifications_id ?? null,
                ]);

                // Guardamos un resumen para el correo
                $detallesImportados[] = [
                    'id'               => $tamizaje->id,
                    'identificacion'   => $tamizaje->numero_identificacion,
                    'fecha'            => $tamizaje->fecha_tamizaje,
                    'tipo'             => $tipoTamizaje->nombre,
                ];
            }

            // 10) Si hay identificaciones sin carnet, abortar todo
            if (!empty($identificacionesSinCarnet)) {
                DB::rollBack();
                $listado = implode(', ', $identificacionesSinCarnet);
                return response()->json([
                    'status'  => 'error',
                    'message' => "No se encontró número de carnet para: {$listado}. No se cargó nada."
                ], 422);
            }

            // 11) Confirmar todo
            DB::commit();

            // 12) Preparar y enviar el correo de resumen
            $count   = count($detallesImportados);
            $subject = "Importación finalizada: {$count} tamizajes";
            $body    = "Hola {$user->name},\n\n" .
                       "Se importaron {$count} registros correctamente:\n\n";

            foreach ($detallesImportados as $d) {
                $body .= "- [ID: {$d['id']}] {$d['identificacion']} | {$d['fecha']} | {$d['tipo']}\n";
            }

            Mail::raw($body, function ($message) use ($user, $extraEmails, $subject) {
                $message->to($user->email)
                        ->cc($extraEmails)
                        ->subject($subject);
            });

            return response()->json([
                'status'  => 'success',
                'message' => '¡Datos importados exitosamente y correo enviado!'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => "Error inesperado: {$e->getMessage()}"
            ], 500);
        }
    }


    
public function table(Request $request)
{
    if ($request->ajax()) {
        // Leemos de la vista ligera y juntamos con tamizajes para filtrar por user_id
        $query = DB::table('dbo.vw_tamizajes_fast as v')
            ->join('tamizajes as t', 'v.id', '=', 't.id')
            ->select([
                'v.id',
                'v.tipo_identificacion',
                'v.numero_identificacion',
                'v.nombre_completo',
                'v.fecha_tamizaje',
                'v.tipo_tamizaje',
                'v.codigo_resultado',
                'v.descripcion_codigo',
                'v.valor_laboratorio',
                'v.descript_resultado',
                'v.usuario',
                'v.created_at',
            ]);

        // Si es usertype 2, limitar al propio user_id (comparamos ==, no ===)
        if (Auth::user()->usertype == 2) {
            $query->where('t.user_id', Auth::id());
        }

        return DataTables::query($query)
            ->addColumn('acciones', function($row){
                $url = route('tamizajes.show', ['tamizaje' => $row->id]);
                return <<<HTML
                <a href="{$url}" class="btn btn-sm btn-info" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                </a>
                HTML;
            })
            ->rawColumns(['acciones'])
            ->editColumn('created_at', function($row){
                return \Carbon\Carbon::parse($row->created_at)
                                     ->format('Y-m-d H:i');
            })
            ->toJson();
    }

    return view('tamizajes.excel_table');
}



public function show($id)
{
    $tamizaje = DB::table('tamizajes')
        ->join('tipo_tamizajes',      'tamizajes.tipo_tamizaje_id',    '=', 'tipo_tamizajes.id')
        ->join('resultado_tamizajes', 'tamizajes.resultado_tamizaje_id','=', 'resultado_tamizajes.id')
        ->leftJoin('users',           'tamizajes.user_id',              '=', 'users.id')
        ->join(DB::raw('sga..maestroafiliados as i'),
              'tamizajes.numero_carnet', '=', 'i.numerocarnet')
              ->join(DB::raw('sga..datossocioeconomicos as j'),
              'j.numerocarnet', '=', 'i.numerocarnet')
        ->select([
            // Todas las columnas de tamizajes
            'tamizajes.id',
            'tamizajes.tipo_identificacion',
            'tamizajes.numero_identificacion',
            'tamizajes.numero_carnet',
            'tamizajes.fecha_tamizaje',
            'tamizajes.tipo_tamizaje_id',
            'tamizajes.resultado_tamizaje_id',
            'tamizajes.user_id',
            'tamizajes.created_at',
            'tamizajes.updated_at',

            // Datos del afiliado
            'i.primerNombre',
            'i.segundoNombre',
            'i.primerApellido',
            'i.segundoApellido',
            'j.direccion',
            'j.telefono',
            // Si tienes más campos en maestroafiliados, agrégalos aquí:
            // 'i.sexo', 'i.fechaNacimiento', etc.

            // Concatenado para mostrar el nombre completo
            DB::raw("CONCAT(
                i.primerNombre, ' ',
                COALESCE(i.segundoNombre, ''), ' ',
                i.primerApellido, ' ',
                COALESCE(i.segundoApellido, '')
            ) as nombre_completo"),

            // Datos de tipo y resultado
            'tipo_tamizajes.nombre       as tipo_tamizaje',
            'resultado_tamizajes.code    as codigo_resultado',
            'resultado_tamizajes.description as descripcion_codigo',

            // Valor y descripción del resultado
            'tamizajes.valor_laboratorio',
            'tamizajes.descript_resultado',

            // Usuario que registró
            'users.name                  as usuario',
        ])
        ->where('tamizajes.id', $id)
        ->first();

    if (! $tamizaje) {
        abort(404, 'Tamizaje no encontrado');
    }

    return view('tamizajes.show', compact('tamizaje'));
} 





    
public function generateExcel(Request $request) 
{
    // Rango de fechas
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    // Construimos la consulta con todos los joins y campos
    $tamizajes = DB::table('tamizajes')
        ->join('tipo_tamizajes',      'tamizajes.tipo_tamizaje_id',     '=', 'tipo_tamizajes.id')
        ->join('resultado_tamizajes', 'tamizajes.resultado_tamizaje_id', '=', 'resultado_tamizajes.id')
        ->leftJoin('users',           'tamizajes.user_id',               '=', 'users.id')
        ->join(DB::raw('sga..maestroafiliados as i'),
              'tamizajes.numero_carnet', '=', 'i.numerocarnet')
        ->join(DB::raw('sga..datossocioeconomicos as j'),
              'j.numerocarnet', '=', 'i.numerocarnet')
        // Filtrado por rango de fecha si se indicó
        ->when($startDate && $endDate, function($q) use($startDate, $endDate) {
            return $q->whereBetween('tamizajes.fecha_tamizaje', [$startDate, $endDate]);
        })
        ->select([
            // columnas de tamizajes
            'tamizajes.id',
            'tamizajes.tipo_identificacion',
            'tamizajes.numero_identificacion',
            'tamizajes.numero_carnet',
            'tamizajes.fecha_tamizaje',
            'tamizajes.tipo_tamizaje_id',
            'tamizajes.resultado_tamizaje_id',
            'tamizajes.user_id',
            'tamizajes.created_at',
            'tamizajes.updated_at',

            // datos del afiliado
            'i.primerNombre',
            'i.segundoNombre',
            'i.primerApellido',
            'i.segundoApellido',
            'j.direccion',
            'j.telefono',

            // nombre completo concatenado
            DB::raw("CONCAT(
                i.primerNombre, ' ',
                COALESCE(i.segundoNombre, ''), ' ',
                i.primerApellido, ' ',
                COALESCE(i.segundoApellido, '')
            ) as nombre_completo"),

            // datos de tipo y resultado
            'tipo_tamizajes.nombre       as tipo_tamizaje',
            'resultado_tamizajes.code    as codigo_resultado',
            'resultado_tamizajes.description as descripcion_codigo',

            // valor y descripción del resultado
            'tamizajes.valor_laboratorio',
            'tamizajes.descript_resultado',

            // usuario que registró
            'users.name                  as usuario',
        ])
        ->orderBy('tamizajes.id', 'desc')
        ->get();

    // Descarga el Excel con todos los campos
    return Excel::download(
        new TamizajesExport($tamizajes),
        'reporte_tamizajes_' . now()->format('Ymd_His') . '.xlsx'
    );
}
    


    
}
