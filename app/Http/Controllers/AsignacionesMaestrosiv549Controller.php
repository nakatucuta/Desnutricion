<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionesMaestrosiv549Controller extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && (int) (auth()->user()->usertype ?? 0) === 1, 403, 'Acceso solo para administradores.');
    }

    // Mostrar el formulario para asignar
    public function create(Request $request)
    {
        $this->ensureAdmin();

        $caso = \App\Models\MaestroSiv549::where('tip_ide_', $request->tip_ide_)
            ->where('num_ide_', $request->num_ide_)
            ->where('fec_not', $request->fec_not)
            ->firstOrFail();

        $datosCaso = $caso->toArray();

        $codigo_habilitacion = null;
        $nombre_ips_primaria = null;

        // 1) Buscar IPS primaria (codigo habilitacion) desde afiliado
        if (!empty($caso->num_ide_)) {
            $afiliado = \DB::connection('sqlsrv_1')->table('maestroAfiliados as a')
                ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
                ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
                ->join('maestroIpsGruDet as d', function ($join) {
                    $join->on('c.id', '=', 'd.idd')->where('d.servicio', '=', 1);
                })
                ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
                ->select(\DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'), 'e.descrip as nombre_ips')
                ->where('a.identificacion', $caso->num_ide_)
                ->first();

            if ($afiliado) {
                $codigo_habilitacion = $afiliado->codigo_habilitacion;
                $nombre_ips_primaria = $afiliado->nombre_ips;
            }
        }

        // 2) Usuarios del modulo gestante: usertype=2 y name terminado en _ges
        $usuariosGestante = \App\Models\User::select('id', 'name', 'email', 'codigohabilitacion')
            ->where('usertype', 2)
            ->whereRaw("LOWER(name) LIKE ?", ['%_ges'])
            ->orderBy('name')
            ->get();

        // 3) Todos los usuarios (fallback manual)
        $usuarios = \App\Models\User::select('id', 'name', 'email', 'codigohabilitacion')
            ->orderBy('name')
            ->get();

        // 4) Sugeridos solo dentro de modulo gestante por codigo habilitacion
        $usuariosSugeridos = collect();
        if (!empty($codigo_habilitacion)) {
            $usuariosSugeridos = $usuariosGestante
                ->where('codigohabilitacion', (string) $codigo_habilitacion)
                ->values();
        }

        $usuarios_prestador_primario = $usuariosSugeridos->pluck('id')->toArray();
        $sin_usuario_gestante_por_codigo = !empty($codigo_habilitacion) && $usuariosSugeridos->isEmpty();

        return view('asignaciones_maestrosiv549.create', compact(
            'datosCaso',
            'usuarios',
            'usuariosGestante',
            'usuariosSugeridos',
            'codigo_habilitacion',
            'nombre_ips_primaria',
            'usuarios_prestador_primario',
            'sin_usuario_gestante_por_codigo'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_ids'   => 'required|array|min:1|max:1',
            'user_ids.*' => 'exists:users,id',

            'tip_ide_' => 'required',
            'num_ide_' => 'required',
            'fec_not'  => 'required',
            'nom_eve'  => 'required',
        ], [
            'user_ids.required' => 'Debes seleccionar un prestador para la asignacion.',
            'user_ids.array' => 'El formato de prestador seleccionado no es valido.',
            'user_ids.min' => 'Debes seleccionar un prestador para la asignacion.',
            'user_ids.max' => 'Solo puedes seleccionar un prestador por caso.',
            'user_ids.*.exists' => 'El prestador seleccionado no existe.',
        ]);

        $usuarioAsignador = auth()->user();
        $selectedUsers = array_values(array_unique(array_map('intval', (array) ($validated['user_ids'] ?? []))));

        if (count($selectedUsers) > 1) {
            throw ValidationException::withMessages([
                'user_ids' => 'Este modulo permite solo una asignacion por caso. Selecciona un unico prestador.',
            ]);
        }

        if (count($selectedUsers) === 0) {
            throw ValidationException::withMessages([
                'user_ids' => 'Debes seleccionar un prestador para la asignacion.',
            ]);
        }

        // Datos del caso (sin user_ids)
        $baseData = $request->except(['user_ids']);
        $baseData['tip_ide_'] = trim((string) ($baseData['tip_ide_'] ?? ''));
        $baseData['num_ide_'] = trim((string) ($baseData['num_ide_'] ?? ''));
        $baseData['nom_eve'] = trim((string) ($baseData['nom_eve'] ?? ''));
        $baseData['fec_not'] = $this->normalizeDate($baseData['fec_not'] ?? null);
        [$periodYear, $periodSemana] = $this->resolvePeriodo(
            $baseData['year'] ?? null,
            $baseData['semana'] ?? null,
            $baseData['fec_not']
        );
        $baseData['year'] = $periodYear;
        $baseData['semana'] = $periodSemana;

        $existeCasoAsignado = \App\Models\AsignacionesMaestrosiv549::query()
            ->whereRaw("LTRIM(RTRIM(COALESCE(tip_ide_, ''))) = ?", [$baseData['tip_ide_']])
            ->whereRaw("LTRIM(RTRIM(COALESCE(num_ide_, ''))) = ?", [$baseData['num_ide_']])
            ->whereRaw("LTRIM(RTRIM(COALESCE(nom_eve, ''))) = ?", [$baseData['nom_eve']])
            ->whereRaw("COALESCE(NULLIF(LTRIM(RTRIM(COALESCE([year], ''))), ''), CONVERT(varchar(4), YEAR(fec_not)), '0000') = ?", [$periodYear])
            ->whereRaw("COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, fec_not)), 2), '00') = ?", [$periodSemana])
            ->exists();

        if ($existeCasoAsignado) {
            return back()
                ->withInput()
                ->with('asig_duplicate', true)
                ->withErrors([
                    'user_ids' => "Este caso ya tiene una asignacion en el periodo {$periodYear}-SE{$periodSemana}.",
                ]);
        }

        $usuarioAsignado = \App\Models\User::findOrFail($selectedUsers[0]);
        $data = $baseData;
        $data['user_id'] = $usuarioAsignado->id;

        try {
            DB::beginTransaction();

            $asignacion = \App\Models\AsignacionesMaestrosiv549::create($data);

            \Mail::to($usuarioAsignado->email)
                ->send(new \App\Mail\CasoAsignadoMail($asignacion, $usuarioAsignado, $usuarioAsignador));

            \Mail::to($usuarioAsignador->email)
                ->send(new \App\Mail\AsignacionRealizadaMail($asignacion, $usuarioAsignado, $usuarioAsignador));

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            if ($sqlState === '23000') {
                return back()
                    ->withInput()
                    ->with('asig_duplicate', true)
                    ->withErrors([
                        'user_ids' => "Este caso ya tiene una asignacion en el periodo {$periodYear}-SE{$periodSemana}.",
                    ]);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('maestrosiv549.index')
            ->with('success', 'Caso asignado correctamente y correos enviados.');
    }

    private function normalizeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $raw = trim((string) $value);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd/m/Y H:i:s'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolvePeriodo($year, $semana, ?string $fecNot): array
    {
        $yearNorm = trim((string) ($year ?? ''));
        $semanaNorm = trim((string) ($semana ?? ''));

        if ($fecNot) {
            try {
                $dt = Carbon::parse($fecNot);
                if ($yearNorm === '') {
                    $yearNorm = $dt->format('Y');
                }
                if ($semanaNorm === '') {
                    $semanaNorm = str_pad((string) $dt->isoWeek(), 2, '0', STR_PAD_LEFT);
                }
            } catch (\Throwable $e) {
            }
        }

        if ($yearNorm === '') {
            $yearNorm = now()->format('Y');
        }
        if ($semanaNorm === '') {
            $semanaNorm = '00';
        }

        $semanaNorm = str_pad(preg_replace('/\D+/', '', $semanaNorm) ?: '00', 2, '0', STR_PAD_LEFT);

        return [$yearNorm, $semanaNorm];
    }
}
