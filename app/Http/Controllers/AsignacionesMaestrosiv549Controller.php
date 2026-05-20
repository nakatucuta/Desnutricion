<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AsignacionesMaestrosiv549Controller extends Controller
{
    private const EVENTO_FIJO_549 = 'morbilidad materna extrema';

    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && (int) (auth()->user()->usertype ?? 0) === 1, 403, 'Acceso solo para administradores.');
    }

    // Mostrar el formulario para asignar
    public function create(Request $request)
    {
        $this->ensureAdmin();

        $tipIde = trim((string) $request->tip_ide_);
        $numIde = trim((string) $request->num_ide_);
        $fecNotNorm = $this->normalizeDate($request->fec_not);

        $casoQuery = \App\Models\MaestroSiv549::query()
            ->where('tip_ide_', $tipIde)
            ->where('num_ide_', $numIde);

        if ($fecNotNorm !== null) {
            $fecNotSql = $this->toSqlServerDateLiteral($fecNotNorm);
            $casoQuery->whereRaw("CONVERT(date, fec_not) = CONVERT(date, ?, 112)", [$fecNotSql]);
        }

        $caso = $casoQuery->firstOrFail();

        $datosCaso = $caso->toArray();
        $datosCaso['nom_eve'] = self::EVENTO_FIJO_549;

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

        // 3) Elegibles: solo _ges y mismo codigo de habilitacion
        $usuariosElegibles = collect();
        $usaFallbackGes = false;
        $sin_usuario_gestante_por_codigo = false;
        if (!empty($codigo_habilitacion)) {
            $usuariosElegibles = $usuariosGestante
                ->where('codigohabilitacion', (string) $codigo_habilitacion)
                ->values();
            $sin_usuario_gestante_por_codigo = $usuariosElegibles->isEmpty();
        }

        // Si no hay coincidencias por codigo (o no hay codigo), mostrar _ges como respaldo
        if ($usuariosElegibles->isEmpty()) {
            $usuariosElegibles = $usuariosGestante->values();
            $usaFallbackGes = true;
        }

        $fecNotCasoNorm = $this->normalizeDate($caso->fec_not ?? null);
        [$periodYear, $periodSemana] = $this->resolvePeriodo($caso->year ?? null, $caso->semana ?? null, $fecNotCasoNorm);

        $asignacionExistente = \App\Models\AsignacionesMaestrosiv549::query()
            ->with('colaboradores:id,name,email,codigohabilitacion')
            ->whereRaw("LTRIM(RTRIM(COALESCE(tip_ide_, ''))) = ?", [trim((string) ($caso->tip_ide_ ?? ''))])
            ->whereRaw("LTRIM(RTRIM(COALESCE(num_ide_, ''))) = ?", [trim((string) ($caso->num_ide_ ?? ''))])
            ->whereRaw("LOWER(LTRIM(RTRIM(COALESCE(nom_eve, '')))) = ?", [self::EVENTO_FIJO_549])
            ->whereRaw("COALESCE(NULLIF(LTRIM(RTRIM(COALESCE([year], ''))), ''), CONVERT(varchar(4), YEAR(fec_not)), '0000') = ?", [$periodYear])
            ->whereRaw("COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, fec_not)), 2), '00') = ?", [$periodSemana])
            ->orderBy('id')
            ->first();

        $asignacionesExistentes = collect();
        $assignedIds = collect();
        if ($asignacionExistente) {
            $asignacionesExistentes = $asignacionExistente->colaboradores->map(function ($u) {
                return (object) ['user' => $u];
            })->values();
            $assignedIds = $asignacionExistente->colaboradores->pluck('id')->map(fn ($v) => (int) $v);
        }

        $usuarios_prestador_primario = [];
        if (!$usaFallbackGes && !empty($codigo_habilitacion)) {
            $usuarios_prestador_primario = $usuariosElegibles
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->diff($assignedIds)
                ->values()
                ->toArray();
        }
        return view('asignaciones_maestrosiv549.create', compact(
            'datosCaso',
            'usuariosGestante',
            'usuariosElegibles',
            'codigo_habilitacion',
            'nombre_ips_primaria',
            'usuarios_prestador_primario',
            'sin_usuario_gestante_por_codigo',
            'asignacionesExistentes',
            'periodYear',
            'periodSemana',
            'usaFallbackGes',
            'asignacionExistente'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',

            'tip_ide_' => 'required',
            'num_ide_' => 'required',
            'fec_not'  => 'required',
        ], [
            'user_ids.required' => 'Debes seleccionar un prestador para la asignacion.',
            'user_ids.array' => 'El formato de prestador seleccionado no es valido.',
            'user_ids.min' => 'Debes seleccionar un prestador para la asignacion.',
            'user_ids.*.exists' => 'El prestador seleccionado no existe.',
            'tip_ide_.required' => 'El campo Tipo de identificacion es obligatorio.',
            'num_ide_.required' => 'El campo Numero de identificacion es obligatorio.',
            'fec_not.required' => 'El campo Fecha de notificacion es obligatorio.',
        ]);

        $usuarioAsignador = auth()->user();
        $selectedUsers = array_values(array_unique(array_map('intval', (array) ($validated['user_ids'] ?? []))));

        if (count($selectedUsers) === 0) {
            throw ValidationException::withMessages([
                'user_ids' => 'Debes seleccionar un prestador para la asignacion.',
            ]);
        }

        // Datos del caso (sin user_ids)
        $baseData = $request->except(['user_ids']);
        $baseData['tip_ide_'] = trim((string) ($baseData['tip_ide_'] ?? ''));
        $baseData['num_ide_'] = trim((string) ($baseData['num_ide_'] ?? ''));
        $baseData['nom_eve'] = self::EVENTO_FIJO_549;
        $baseData['fec_not'] = $this->normalizeDate($baseData['fec_not'] ?? null);
        if ($baseData['fec_not'] === null) {
            throw ValidationException::withMessages([
                'fec_not' => 'La fecha de notificacion es invalida. Usa formato DD/MM/AAAA o AAAA-MM-DD.',
            ]);
        }
        [$periodYear, $periodSemana] = $this->resolvePeriodo(
            $baseData['year'] ?? null,
            $baseData['semana'] ?? null,
            $baseData['fec_not']
        );
        $baseData['year'] = $periodYear;
        $baseData['semana'] = $periodSemana;

        $codigoHabilitacion = $this->resolveCodigoHabilitacionByNumIde($baseData['num_ide_']);

        $usuariosGesBase = \App\Models\User::query()
            ->where('usertype', 2)
            ->whereRaw("LOWER(name) LIKE ?", ['%_ges']);

        $usaFiltroPorCodigo = false;
        if (!empty($codigoHabilitacion)) {
            $coincidenPorCodigo = (clone $usuariosGesBase)
                ->where('codigohabilitacion', (string) $codigoHabilitacion)
                ->exists();

            $usaFiltroPorCodigo = $coincidenPorCodigo;
        }

        $usuariosValidosQuery = \App\Models\User::query()
            ->whereIn('id', $selectedUsers)
            ->where('usertype', 2)
            ->whereRaw("LOWER(name) LIKE ?", ['%_ges']);

        if ($usaFiltroPorCodigo) {
            $usuariosValidosQuery->where('codigohabilitacion', (string) $codigoHabilitacion);
        }

        $usuariosValidos = $usuariosValidosQuery->get(['id', 'name', 'email']);

        if ($usuariosValidos->count() !== count($selectedUsers)) {
            throw ValidationException::withMessages([
                'user_ids' => $usaFiltroPorCodigo
                    ? 'Solo puedes asignar a usuarios _ges con el mismo codigo de habilitacion del caso.'
                    : 'Solo puedes asignar a usuarios del modulo gestantes que terminen en _ges.',
            ]);
        }

        try {
            DB::beginTransaction();
            $asignacion = \App\Models\AsignacionesMaestrosiv549::query()
                ->whereRaw("LTRIM(RTRIM(COALESCE(tip_ide_, ''))) = ?", [$baseData['tip_ide_']])
                ->whereRaw("LTRIM(RTRIM(COALESCE(num_ide_, ''))) = ?", [$baseData['num_ide_']])
                ->whereRaw("LOWER(LTRIM(RTRIM(COALESCE(nom_eve, '')))) = ?", [self::EVENTO_FIJO_549])
                ->whereRaw("COALESCE(NULLIF(LTRIM(RTRIM(COALESCE([year], ''))), ''), CONVERT(varchar(4), YEAR(fec_not)), '0000') = ?", [$periodYear])
                ->whereRaw("COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, fec_not)), 2), '00') = ?", [$periodSemana])
                ->orderBy('id')
                ->first();

            if (!$asignacion) {
                $data = $baseData;
                $data['fec_not'] = $this->toSqlServerDateLiteral($data['fec_not']);
                $data['user_id'] = (int) $usuariosValidos->first()->id;
                $asignacion = \App\Models\AsignacionesMaestrosiv549::create($data);
            }

            $existingIds = $asignacion->colaboradores()->pluck('users.id')->map(fn ($v) => (int) $v)->toArray();
            $targetIds = $usuariosValidos->pluck('id')->map(fn ($v) => (int) $v)->toArray();
            $newIds = array_values(array_diff($targetIds, $existingIds));
            $removedIds = array_values(array_diff($existingIds, $targetIds));
            $keepIds = array_values(array_intersect($existingIds, $targetIds));

            // La seleccion enviada en el formulario es la fuente de verdad:
            // agrega los nuevos y quita los que el usuario retiro manualmente.
            $asignacion->colaboradores()->sync($targetIds);

            $creadas = count($newIds);
            $removidas = count($removedIds);
            $omitidas = count($keepIds);
            if ($creadas > 0) {
                $nuevosUsuarios = $usuariosValidos->whereIn('id', $newIds);
                foreach ($nuevosUsuarios as $usuarioAsignado) {
                    \Mail::to($usuarioAsignado->email)
                        ->send(new \App\Mail\CasoAsignadoMail($asignacion, $usuarioAsignado, $usuarioAsignador));

                    \Mail::to($usuarioAsignador->email)
                        ->send(new \App\Mail\AsignacionRealizadaMail($asignacion, $usuarioAsignado, $usuarioAsignador));
                }
            }

            DB::commit();

            if ($creadas === 0 && $removidas === 0 && $omitidas > 0) {
                return back()
                    ->withInput()
                    ->with('asig_duplicate', true)
                    ->withErrors([
                        'user_ids' => "Ya existian asignaciones para todos los usuarios seleccionados en {$periodYear}-SE{$periodSemana}.",
                    ]);
            }

            if ($omitidas > 0 || $removidas > 0) {
                return redirect()->route('maestrosiv549.index')
                    ->with('success', "Equipo actualizado. Prestadores agregados: {$creadas}. Prestadores retirados: {$removidas}. Ya asignados (sin cambios): {$omitidas}.");
            }
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
            ->with('success', 'Caso asignado correctamente y equipo de prestadores actualizado.');
    }

    public function destroyByAdmin(Request $request, \App\Models\AsignacionesMaestrosiv549 $asignacion)
    {
        $this->ensureAdmin();

        $asignacion->load('colaboradores:id,name,email');
        $colaboradores = $asignacion->colaboradores
            ->filter(fn ($u) => !empty($u->email))
            ->values();

        if ($colaboradores->isEmpty()) {
            $owner = \App\Models\User::find($asignacion->user_id, ['id', 'name', 'email']);
            if ($owner && !empty($owner->email)) {
                $colaboradores = collect([$owner]);
            }
        }

        $tipIde = trim((string) ($asignacion->tip_ide_ ?? 'N/D'));
        $numIde = trim((string) ($asignacion->num_ide_ ?? 'N/D'));
        $evento = trim((string) ($asignacion->nom_eve ?? 'N/D'));
        $periodoYear = trim((string) ($asignacion->year ?? '')) ?: (string) optional($asignacion->fec_not)->format('Y');
        $periodoSemana = trim((string) ($asignacion->semana ?? ''));
        $periodo = $periodoYear !== '' ? ($periodoYear.'-SE'.str_pad($periodoSemana !== '' ? $periodoSemana : '00', 2, '0', STR_PAD_LEFT)) : 'N/D';
        $adminName = (string) (auth()->user()->name ?? 'Administrador');

        foreach ($colaboradores as $u) {
            Mail::raw(
                "Hola {$u->name},\n\n".
                "Se eliminó una asignación del evento 549 por ajuste administrativo.\n\n".
                "Caso: {$tipIde} {$numIde}\n".
                "Evento: {$evento}\n".
                "Periodo: {$periodo}\n".
                "Acción realizada por: {$adminName}\n\n".
                "Si necesitas más información, por favor contacta al administrador.",
                function ($message) use ($u) {
                    $message->to($u->email)
                        ->subject('Aviso: asignación 549 eliminada');
                }
            );
        }

        $asignacion->delete();

        $message = 'Asignación eliminada y correo enviado a los prestadores asignados.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('seguimientos.index')->with('success', $message);
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

    private function resolveCodigoHabilitacionByNumIde(?string $numIde): ?string
    {
        $numIde = trim((string) ($numIde ?? ''));
        if ($numIde === '') {
            return null;
        }

        $afiliado = \DB::connection('sqlsrv_1')->table('maestroAfiliados as a')
            ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
            ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
            ->join('maestroIpsGruDet as d', function ($join) {
                $join->on('c.id', '=', 'd.idd')->where('d.servicio', '=', 1);
            })
            ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
            ->select(\DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'))
            ->where('a.identificacion', $numIde)
            ->first();

        return $afiliado ? (string) $afiliado->codigo_habilitacion : null;
    }

    private function toSqlServerDateLiteral(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Ymd');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
