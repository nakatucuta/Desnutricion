<?php

namespace App\Http\Controllers;

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

class TamizajeController extends Controller
{
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

    
    public function import(Request $request)
{
    $usuario_activo = Auth::id();

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
    $rows = $data[0];
    // Asumimos que la fila 0 es encabezado y la omitimos
    $startRow = 1;

    DB::beginTransaction();

    try {
        $identificacionesSinCarnet = [];

        foreach (array_slice($rows, $startRow) as $rowIndex => $row) {
            // Esperamos al menos 6 columnas:
            // [0] => TIPO_ID, [1] => ID, [2] => FECHA, [3] => tipo_tamizaje_id, [4] => resultado, [5] => puntuación
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
                    $fechaT = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2]);
                    $fechaTamizaje = $fechaT->format('Y-m-d');
                } else {
                    // Fecha string tipo "2026-02-15"
                    $fechaTamizaje = \Carbon\Carbon::parse($row[2])->format('Y-m-d');
                }
            } catch (\Exception $e) {
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
            // Posible puntuación en col 5
            $scoreRaw = isset($row[5]) ? $row[5] : null;
            $score    = $scoreRaw !== null ? (int)$scoreRaw : null;

            // Buscar tipo de tamizaje
            $tipoTamizaje = \App\Models\TipoTamizaje::find($tipoTamizajeId);
            if (!$tipoTamizaje) {
                $excelRow = $rowIndex + $startRow + 1;
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => "El ID de tipo_tamizaje '{$tipoTamizajeId}' no existe (fila {$excelRow})."
                ], 422);
            }

            // Buscar resultado (por code)
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

            // 5) Buscar carnet en la BD externa
            $numero_carnet = DB::connection('sqlsrv_1')
                ->table('maestroIdentificaciones')
                ->where('identificacion', $numeroIdentificacion)
                ->where('tipoIdentificacion', $tipoIdentificacion)
                ->value('numeroCarnet');

            if (is_null($numero_carnet)) {
                $identificacionesSinCarnet[] = $numeroIdentificacion;
                continue;
            }

            // Por defecto
            $valorLaboratorio  = null;
            $descriptResultado = null;

            // 6) Si es tipoTamizaje 11 => (rango ASSIST - tabaco / alcohol)
            //    Si es tipoTamizaje 12 => (rango ALCOHOL, hombres / mujeres)
            //    Ajusta la lógica a tu gusto:

            // Lógica anterior para tipoTamizaje=11, code=1,2 ...
            if ($tipoTamizaje->id == 11 && $score !== null) {
                if ($resultadoTamizaje->id == 81) {
                    // 0-3, 4-26, 27+
                    $valorLaboratorio = $score;
                    if ($score <= 3) {
                        $descriptResultado = "RIESGO BAJO (0-3) - SIN INTERVENCIÓN";
                    } elseif ($score <= 26) {
                        $descriptResultado = "RIESGO MODERADO (4-26) - INTERVENCIÓN BREVE";
                    } else {
                        $descriptResultado = "RIESGO ALTO (27+) - TRATAMIENTO MÁS INTENSIVO";
                    }
                } elseif ($resultadoTamizaje->id == 82) {
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
                if ($resultadoTamizaje->id == 83) {
                    // HOMBRES
                    // Chequeamos de mayor a menor
                    if ($score >= 20) {
                        $descriptResultado = "Dependencia del alcohol (20+ puntos)";
                    } elseif ($score >= 15) {
                        $descriptResultado = "Indicativo de una probable dependencia (15+ puntos)";
                    } elseif ($score >= 8) {
                        $descriptResultado = "Fuerte probabilidad de daños debido al consumo de alcohol (8+ puntos)";
                    }
                } elseif ($resultadoTamizaje->id == 84) {
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
                if ($resultadoTamizaje->id == 85) {
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
                if ($resultadoTamizaje->id == 86) {
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
                } elseif ($resultadoTamizaje->id == 87) {
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
                if ($resultadoTamizaje->id == 97) {
            
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
            
            

            // Insertar fila a fila
            \App\Models\Tamizaje::create([
                'tipo_identificacion'   => $tipoIdentificacion,
                'numero_identificacion' => $numeroIdentificacion,
                'fecha_tamizaje'        => $fechaTamizaje,
                'numero_carnet'         => $numero_carnet,
                'tipo_tamizaje_id'      => $tipoTamizaje->id,
                'resultado_tamizaje_id' => $resultadoTamizaje->id,
                'user_id'               => $usuario_activo,
                'valor_laboratorio'     => $valorLaboratorio,
                'descript_resultado'    => $descriptResultado,
            ]);
        }

        // Si hay filas sin carnet
        if (!empty($identificacionesSinCarnet)) {
            DB::rollBack();
            $listado = implode(', ', $identificacionesSinCarnet);
            return response()->json([
                'status'  => 'error',
                'message' => "No se encontró número de carnet para: {$listado}. No se cargó nada."
            ], 422);
        }

        DB::commit();
        return response()->json([
            'status'  => 'success',
            'message' => '¡Datos importados exitosamente!'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 'error',
            'message' => "Error inesperado: {$e->getMessage()}"
        ], 500);
    }
}

    
    

    

    public function table(Request $request)
    {
        // Construimos la query base con las relaciones
        $query = Tamizaje::with(['tipo','resultado','user'])
                         ->orderBy('tamizajes.id', 'desc'); // Orden por defecto (ajústalo a tu gusto)
    
        // Si viene "?sort=user", filtramos por user_id = Auth::id() y NO paginamos
        if ($request->get('sort') === 'user') {
            $query->where('user_id', Auth::id());
            $tamizajes = $query->get(); // Trae todos los registros sin paginar
        } else {
            // De lo contrario, paginamos
            $tamizajes = $query->paginate(10);
        }
    
        // Retornamos la vista
        return view('tamizajes.excel_table', compact('tamizajes'));
    }
    
    
    public function generateExcel(Request $request)
    {
        // Obtenemos el rango de fechas del formulario
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
    
        // Construimos la query base (orden por id desc)
        $query = Tamizaje::with(['tipo', 'resultado','user'])
                         ->orderBy('tamizajes.id', 'desc');
    
        // Si ambas fechas están presentes, filtramos por created_at
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        // Obtenemos la colección final
        $tamizajes = $query->get();
    
        // Retornamos la descarga usando la clase export personalizada
        return Excel::download(
            new TamizajesExport($tamizajes),
            'reporte_tamizajes_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
    


    
}
