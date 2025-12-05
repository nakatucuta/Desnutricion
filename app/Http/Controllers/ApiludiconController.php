<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ApiConsumptionState;

class ApiludiconController extends Controller
{
    /**
     * ✅ 1) Devuelve TODO paginado (sin rango de fechas)
     *
     * GET /api/ludycom/afiliados?per_page=100&page=1
     */
    public function index_all(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $perPage = $validated['per_page'] ?? 100;

        try {
            $query = DB::connection('sqlsrv_1')
                ->table('api_ludycom.dbo.datos')
                ->select([
                    'numeroCarnet',
                    'codigoAgente',
                    'tipoIdenCabezaFamilia',
                    'idenCabezaFamilia',
                    'serial',
                    'tipoIdentificacion',
                    'identificacion',
                    'primerApellido',
                    'segundoApellido',
                    'primerNombre',
                    'segundoNombre',
                    'fechaNacimiento',
                    'genero',
                    'ESTADOACTUAL',
                    'GRUPOIPS',
                    'codigoMunicipio',
                    'zona',
                    'tipoAfiliado',
                    'grupoPoblacional',
                    'nivelSisben',
                    'puntajeSisben',
                    'discapacidad',
                    'descripcion_discapacidad',
                    'barrio',
                    'direccion',
                    'telefono',
                    'telefono1',
                    'telefono2',
                    'email',
                    'fechaAfiliacionArs',
                    'fechaAfiliacionSistema',
                    'fechaCambioEstado',
                    'portabilidad',
                    'IPS_PORTABILIDAD',
                    'poblacion_victima',
                    'poblacion_altocosto',
                    'erc',
                    'terapia_reemplazo_renal',
                    'diabetes',
                    'hta',
                    'vih',
                    'enfermedades_huerfanas',
                    'hemofilia',
                    'cancer',
                    'artritis',
                    'nefroproteccion',
                    'desnutricion',
                    'poblacion_gestante',
                    'fpp',
                    'contratacion_especial',
                    'contratacion_nefro',
                    'contratacion_cercana',
                    'edad',
                    'etareos',
                    'cursos_vida',
                    'edad_meses',
                    'pai',
                    'cruce_bdex_rnec',
                    'marca_sisben_IV_III',
                    'fecha_actualizacion',
                    'mes',
                    DB::raw('[año] as año'),
                ])
                ->orderBy('año')
                ->orderBy('mes')
                ->orderBy('numeroCarnet');

            $datos = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'meta'   => [
                    'current_page' => $datos->currentPage(),
                    'per_page'     => $datos->perPage(),
                    'total'        => $datos->total(),
                    'last_page'    => $datos->lastPage(),
                ],
                'data' => $datos->items(),
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Error index_all api_ludycom', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno al consultar afiliados',
                'data'    => [],
            ], 500);
        }
    }

    /**
     * 🔥 1B) Devuelve SOLO NUEVOS por usuario/token (SIN fechas)
     * Usa cursor numeroCarnet guardado en api_consumption_states.
     *
     * GET /api/ludycom/afiliados/nuevos?per_page=100
     * (no usa page, cada llamada avanza sola)
     */
    public function index_all_nuevos(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $perPage = $validated['per_page'] ?? 100;
        $user = $request->user();

        // ✅ Crea o trae cursor del usuario para este endpoint
        $state = ApiConsumptionState::firstOrCreate(
            ['user_id' => $user->id, 'endpoint' => 'ludycom_afiliados_all'],
            ['last_carnet' => null]
        );

        try {
            $query = DB::connection('sqlsrv_1')
                ->table('api_ludycom.dbo.datos')
                ->select([
                    'numeroCarnet',
                    'codigoAgente',
                    'tipoIdenCabezaFamilia',
                    'idenCabezaFamilia',
                    'serial',
                    'tipoIdentificacion',
                    'identificacion',
                    'primerApellido',
                    'segundoApellido',
                    'primerNombre',
                    'segundoNombre',
                    'fechaNacimiento',
                    'genero',
                    'ESTADOACTUAL',
                    'GRUPOIPS',
                    'codigoMunicipio',
                    'zona',
                    'tipoAfiliado',
                    'grupoPoblacional',
                    'nivelSisben',
                    'puntajeSisben',
                    'discapacidad',
                    'descripcion_discapacidad',
                    'barrio',
                    'direccion',
                    'telefono',
                    'telefono1',
                    'telefono2',
                    'email',
                    'fechaAfiliacionArs',
                    'fechaAfiliacionSistema',
                    'fechaCambioEstado',
                    'portabilidad',
                    'IPS_PORTABILIDAD',
                    'poblacion_victima',
                    'poblacion_altocosto',
                    'erc',
                    'terapia_reemplazo_renal',
                    'diabetes',
                    'hta',
                    'vih',
                    'enfermedades_huerfanas',
                    'hemofilia',
                    'cancer',
                    'artritis',
                    'nefroproteccion',
                    'desnutricion',
                    'poblacion_gestante',
                    'fpp',
                    'contratacion_especial',
                    'contratacion_nefro',
                    'contratacion_cercana',
                    'edad',
                    'etareos',
                    'cursos_vida',
                    'edad_meses',
                    'pai',
                    'cruce_bdex_rnec',
                    'marca_sisben_IV_III',
                    'fecha_actualizacion',
                    'mes',
                    DB::raw('[año] as año'),
                ]);

            // ✅ Si ya tiene cursor, trae solo mayores a last_carnet
            if (!empty($state->last_carnet)) {
                $query->where('numeroCarnet', '>', $state->last_carnet);
            }

            // ✅ Orden estable + limit
            $datos = $query
                ->orderBy('numeroCarnet')
                ->limit($perPage)
                ->get();

            // Si no hay más nuevos
            if ($datos->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'No hay nuevos registros para este usuario',
                    'cursor'  => [
                        'last_carnet' => $state->last_carnet
                    ],
                    'data'    => [],
                ], 200);
            }

            // ✅ Actualiza cursor con el último entregado
            $ultimo = $datos->last();

            $state->update([
                'last_carnet' => $ultimo->numeroCarnet
            ]);

            return response()->json([
                'status' => 'success',
                'cursor' => [
                    'last_carnet' => $state->last_carnet
                ],
                'count'  => $datos->count(),
                'data'   => $datos,
            ], 200);

        } catch (\Throwable $e) {

            \Log::error('Error index_all_nuevos api_ludycom', [
                'error' => $e->getMessage(),
                'user'  => $user->id,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno al consultar afiliados nuevos',
                'data'    => [],
            ], 500);
        }
    }

    /**
     * 🧹 Reset del cursor de nuevos (solo para este usuario)
     *
     * POST /api/ludycom/afiliados/reset
     */
    public function reset_consumo_all(Request $request)
    {
        $user = $request->user();

        ApiConsumptionState::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => 'ludycom_afiliados_all'],
            ['last_carnet' => null]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Cursor reseteado. El siguiente llamado traerá datos desde cero.'
        ], 200);
    }

    /**
     * ✅ 2) Devuelve afiliados por IDENTIFICACION
     *
     * GET /api/ludycom/afiliados/identificacion/1020304050
     * GET /api/ludycom/afiliados/identificacion/1020304050?tipoIdentificacion=ti
     */
    public function show_by_identificacion(Request $request, $identificacion)
    {
        $validated = $request->validate([
            'tipoIdentificacion' => ['nullable', 'string', 'max:5'],
        ]);

        $tipoIdentificacion = $validated['tipoIdentificacion'] ?? null;

        try {
            $query = DB::connection('sqlsrv_1')
                ->table('api_ludycom.dbo.datos')
                ->select([
                    'numeroCarnet',
                    'codigoAgente',
                    'tipoIdenCabezaFamilia',
                    'idenCabezaFamilia',
                    'serial',
                    'tipoIdentificacion',
                    'identificacion',
                    'primerApellido',
                    'segundoApellido',
                    'primerNombre',
                    'segundoNombre',
                    'fechaNacimiento',
                    'genero',
                    'ESTADOACTUAL',
                    'GRUPOIPS',
                    'codigoMunicipio',
                    'zona',
                    'tipoAfiliado',
                    'grupoPoblacional',
                    'nivelSisben',
                    'puntajeSisben',
                    'discapacidad',
                    'descripcion_discapacidad',
                    'barrio',
                    'direccion',
                    'telefono',
                    'telefono1',
                    'telefono2',
                    'email',
                    'fechaAfiliacionArs',
                    'fechaAfiliacionSistema',
                    'fechaCambioEstado',
                    'portabilidad',
                    'IPS_PORTABILIDAD',
                    'poblacion_victima',
                    'poblacion_altocosto',
                    'erc',
                    'terapia_reemplazo_renal',
                    'diabetes',
                    'hta',
                    'vih',
                    'enfermedades_huerfanas',
                    'hemofilia',
                    'cancer',
                    'artritis',
                    'nefroproteccion',
                    'desnutricion',
                    'poblacion_gestante',
                    'fpp',
                    'contratacion_especial',
                    'contratacion_nefro',
                    'contratacion_cercana',
                    'edad',
                    'etareos',
                    'cursos_vida',
                    'edad_meses',
                    'pai',
                    'cruce_bdex_rnec',
                    'marca_sisben_IV_III',
                    'fecha_actualizacion',
                    'mes',
                    DB::raw('[año] as año'),
                ])
                ->where('identificacion', $identificacion);

            if ($tipoIdentificacion) {
                $query->where('tipoIdentificacion', $tipoIdentificacion);
            }

            $datos = $query->orderBy('numeroCarnet')->get();

            if ($datos->isEmpty()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No se encontró información para la identificación indicada',
                    'data'    => [],
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'params' => [
                    'identificacion'     => $identificacion,
                    'tipoIdentificacion' => $tipoIdentificacion,
                ],
                'count'  => $datos->count(),
                'data'   => $datos,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Error show_by_identificacion api_ludycom', [
                'error' => $e->getMessage(),
                'identificacion' => $identificacion,
                'tipoIdentificacion' => $tipoIdentificacion,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno al consultar afiliado',
                'data'    => [],
            ], 500);
        }
    }
}
