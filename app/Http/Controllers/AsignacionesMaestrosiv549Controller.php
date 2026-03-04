<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',

            'tip_ide_' => 'required',
            'num_ide_' => 'required',
            'fec_not'  => 'required',
            'nom_eve'  => 'required',
        ]);

        $usuarioAsignador = auth()->user();

        // Datos del caso (sin user_ids)
        $baseData = $request->except(['user_ids']);

        // Usuarios destino
        $usuariosAsignados = \App\Models\User::whereIn('id', $request->user_ids)->get();

        foreach ($usuariosAsignados as $usuarioAsignado) {
            // Importante: poner user_id antes de crear
            $data = $baseData;
            $data['user_id'] = $usuarioAsignado->id;

            $asignacion = \App\Models\AsignacionesMaestrosiv549::create($data);

            \Mail::to($usuarioAsignado->email)
                ->send(new \App\Mail\CasoAsignadoMail($asignacion, $usuarioAsignado, $usuarioAsignador));

            \Mail::to($usuarioAsignador->email)
                ->send(new \App\Mail\AsignacionRealizadaMail($asignacion, $usuarioAsignado, $usuarioAsignador));
        }

        return redirect()->route('maestrosiv549.index')
            ->with('success', 'Caso asignado correctamente y correos enviados a todos los usuarios seleccionados.');
    }
}
