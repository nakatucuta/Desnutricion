<?php

namespace App\Http\Controllers;

use App\Models\ApiConsumptionState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiludiconController extends Controller
{
    private function afiliadosQuery()
    {
        return DB::connection('sqlsrv_1')
            ->table('api_ludycom.dbo.datos')
            ->selectRaw('*, [año] as anio_api');
    }

    public function index_all(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $perPage = $validated['per_page'] ?? 100;

        try {
            $datos = $this->afiliadosQuery()
                ->orderBy('año')
                ->orderBy('mes')
                ->orderBy('numeroCarnet')
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'meta' => [
                    'current_page' => $datos->currentPage(),
                    'per_page' => $datos->perPage(),
                    'total' => $datos->total(),
                    'last_page' => $datos->lastPage(),
                ],
                'data' => $datos->items(),
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error index_all api_ludycom', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al consultar afiliados',
                'data' => [],
            ], 500);
        }
    }

    public function index_all_nuevos(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $perPage = $validated['per_page'] ?? 100;
        $user = $request->user();

        $state = ApiConsumptionState::firstOrCreate(
            ['user_id' => $user->id, 'endpoint' => 'ludycom_afiliados_all'],
            ['last_carnet' => null]
        );

        try {
            $query = $this->afiliadosQuery();

            if (!empty($state->last_carnet)) {
                $query->where('numeroCarnet', '>', $state->last_carnet);
            }

            $datos = $query
                ->orderBy('numeroCarnet')
                ->limit($perPage)
                ->get();

            if ($datos->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No hay nuevos registros para este usuario',
                    'cursor' => [
                        'last_carnet' => $state->last_carnet,
                    ],
                    'data' => [],
                ], 200);
            }

            $ultimo = $datos->last();

            $state->update([
                'last_carnet' => $ultimo->numeroCarnet,
            ]);

            return response()->json([
                'status' => 'success',
                'cursor' => [
                    'last_carnet' => $state->last_carnet,
                ],
                'count' => $datos->count(),
                'data' => $datos,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error index_all_nuevos api_ludycom', [
                'error' => $e->getMessage(),
                'user' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al consultar afiliados nuevos',
                'data' => [],
            ], 500);
        }
    }

    public function reset_consumo_all(Request $request)
    {
        $user = $request->user();

        ApiConsumptionState::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => 'ludycom_afiliados_all'],
            ['last_carnet' => null]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Cursor reseteado. El siguiente llamado traera datos desde cero.',
        ], 200);
    }

    public function show_by_identificacion(Request $request, $identificacion)
    {
        $validated = $request->validate([
            'tipoIdentificacion' => ['nullable', 'string', 'max:5'],
        ]);

        $tipoIdentificacion = $validated['tipoIdentificacion'] ?? null;

        try {
            $query = $this->afiliadosQuery()
                ->where('identificacion', $identificacion);

            if ($tipoIdentificacion) {
                $query->where('tipoIdentificacion', $tipoIdentificacion);
            }

            $datos = $query->orderBy('numeroCarnet')->get();

            if ($datos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontro informacion para la identificacion indicada',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'params' => [
                    'identificacion' => $identificacion,
                    'tipoIdentificacion' => $tipoIdentificacion,
                ],
                'count' => $datos->count(),
                'data' => $datos,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error show_by_identificacion api_ludycom', [
                'error' => $e->getMessage(),
                'identificacion' => $identificacion,
                'tipoIdentificacion' => $tipoIdentificacion,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al consultar afiliado',
                'data' => [],
            ], 500);
        }
    }

    public function show_by_numeroCarnet($numeroCarnet)
    {
        try {
            $registro = $this->afiliadosQuery()
                ->where('numeroCarnet', $numeroCarnet)
                ->orderBy('numeroCarnet')
                ->first();

            if (!$registro) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontro informacion para el numeroCarnet indicado',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'params' => [
                    'numeroCarnet' => $numeroCarnet,
                ],
                'data' => $registro,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error show_by_numeroCarnet api_ludycom', [
                'error' => $e->getMessage(),
                'numeroCarnet' => $numeroCarnet,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al consultar afiliado por numeroCarnet',
                'data' => null,
            ], 500);
        }
    }
}

