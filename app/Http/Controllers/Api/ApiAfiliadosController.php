<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiConsumptionState;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApiAfiliadosController extends Controller
{
    private function afiliadosQuery()
    {
        return DB::connection('sqlsrv_1')
            ->table('api_ludycom.dbo.datos');
    }

    private function serializeRows($rows): array
    {
        return Collection::make($rows)
            ->map(fn ($row) => (array) $row)
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'anio_inicio' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes_inicio' => ['nullable', 'integer', 'min:1', 'max:12'],
            'anio_fin' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes_fin' => ['nullable', 'integer', 'min:1', 'max:12'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $anio_inicio = $validated['anio_inicio'] ?? 2025;
        $mes_inicio = $validated['mes_inicio'] ?? 10;
        $anio_fin = $validated['anio_fin'] ?? 2025;
        $mes_fin = $validated['mes_fin'] ?? 10;
        $perPage = $validated['per_page'] ?? 100;

        try {
            $datos = $this->afiliadosQuery()
                ->where(function ($q) use ($anio_inicio, $mes_inicio) {
                    $q->whereRaw('[año] > ?', [$anio_inicio])
                        ->orWhere(function ($q2) use ($anio_inicio, $mes_inicio) {
                            $q2->whereRaw('[año] = ?', [$anio_inicio])
                                ->where('mes', '>=', $mes_inicio);
                        });
                })
                ->where(function ($q) use ($anio_fin, $mes_fin) {
                    $q->whereRaw('[año] < ?', [$anio_fin])
                        ->orWhere(function ($q2) use ($anio_fin, $mes_fin) {
                            $q2->whereRaw('[año] = ?', [$anio_fin])
                                ->where('mes', '<=', $mes_fin);
                        });
                })
                ->orderByRaw('[año]')
                ->orderBy('mes')
                ->paginate($perPage);

            if ($datos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontraron datos para el rango indicado',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'range' => compact('anio_inicio', 'mes_inicio', 'anio_fin', 'mes_fin'),
                'meta' => [
                    'current_page' => $datos->currentPage(),
                    'per_page' => $datos->perPage(),
                    'total' => $datos->total(),
                    'last_page' => $datos->lastPage(),
                ],
                'data' => $this->serializeRows($datos->items()),
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error index_api afiliados', [
                'error' => $e->getMessage(),
                'range' => compact('anio_inicio', 'mes_inicio', 'anio_fin', 'mes_fin'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al consultar afiliados',
                'data' => [],
            ], 500);
        }
    }

    public function nuevos(Request $request)
    {
        $validated = $request->validate([
            'anio_inicio' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes_inicio' => ['nullable', 'integer', 'min:1', 'max:12'],
            'anio_fin' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes_fin' => ['nullable', 'integer', 'min:1', 'max:12'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $anio_inicio = $validated['anio_inicio'] ?? 2025;
        $mes_inicio = $validated['mes_inicio'] ?? 10;
        $anio_fin = $validated['anio_fin'] ?? 2025;
        $mes_fin = $validated['mes_fin'] ?? 10;
        $perPage = $validated['per_page'] ?? 100;

        $user = $request->user();

        $state = ApiConsumptionState::firstOrCreate(
            ['user_id' => $user->id, 'endpoint' => 'afiliados'],
            ['last_anio' => null, 'last_mes' => null, 'last_carnet' => null]
        );

        try {
            $query = $this->afiliadosQuery()
                ->where(function ($q) use ($anio_inicio, $mes_inicio) {
                    $q->whereRaw('[año] > ?', [$anio_inicio])
                        ->orWhere(function ($q2) use ($anio_inicio, $mes_inicio) {
                            $q2->whereRaw('[año] = ?', [$anio_inicio])
                                ->where('mes', '>=', $mes_inicio);
                        });
                })
                ->where(function ($q) use ($anio_fin, $mes_fin) {
                    $q->whereRaw('[año] < ?', [$anio_fin])
                        ->orWhere(function ($q2) use ($anio_fin, $mes_fin) {
                            $q2->whereRaw('[año] = ?', [$anio_fin])
                                ->where('mes', '<=', $mes_fin);
                        });
                });

            if ($state->last_anio !== null && $state->last_mes !== null) {
                $lastAnio = $state->last_anio;
                $lastMes = $state->last_mes;
                $lastCarnet = $state->last_carnet ?? '';

                $query->where(function ($q) use ($lastAnio, $lastMes, $lastCarnet) {
                    $q->whereRaw('[año] > ?', [$lastAnio])
                        ->orWhere(function ($q2) use ($lastAnio, $lastMes, $lastCarnet) {
                            $q2->whereRaw('[año] = ?', [$lastAnio])
                                ->where(function ($q3) use ($lastMes, $lastCarnet) {
                                    $q3->where('mes', '>', $lastMes)
                                        ->orWhere(function ($q4) use ($lastMes, $lastCarnet) {
                                            $q4->where('mes', '=', $lastMes)
                                                ->where('numeroCarnet', '>', $lastCarnet);
                                        });
                                });
                        });
                });
            }

            $datos = $query
                ->orderByRaw('[año]')
                ->orderBy('mes')
                ->orderBy('numeroCarnet')
                ->limit($perPage)
                ->get();

            if ($datos->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No hay nuevos registros para este usuario en el rango indicado',
                    'range' => compact('anio_inicio', 'mes_inicio', 'anio_fin', 'mes_fin'),
                    'data' => [],
                ], 200);
            }

            $ultimo = $datos->last();

            $state->update([
                'last_anio' => $ultimo->{'año'},
                'last_mes' => $ultimo->mes,
                'last_carnet' => $ultimo->numeroCarnet,
            ]);

            return response()->json([
                'status' => 'success',
                'range' => compact('anio_inicio', 'mes_inicio', 'anio_fin', 'mes_fin'),
                'cursor' => [
                    'last_anio' => $state->last_anio,
                    'last_mes' => $state->last_mes,
                    'last_carnet' => $state->last_carnet,
                ],
                'count' => $datos->count(),
                'data' => $this->serializeRows($datos),
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error afiliados nuevos', [
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

    public function reset(Request $request)
    {
        $user = $request->user();

        ApiConsumptionState::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => 'afiliados'],
            ['last_anio' => null, 'last_mes' => null, 'last_carnet' => null]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Cursor reseteado. El proximo llamado traera datos desde cero.',
        ], 200);
    }
}
