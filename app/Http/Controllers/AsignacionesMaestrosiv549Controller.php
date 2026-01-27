<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AsignacionesMaestrosiv549;
use Illuminate\Support\Str;

class AsignacionesMaestrosiv549Controller extends Controller
{
    // Mostrar el formulario para asignar
public function create(Request $request)
{
    $caso = \App\Models\MaestroSiv549::where('tip_ide_', $request->tip_ide_)
        ->where('num_ide_', $request->num_ide_)
        ->where('fec_not', $request->fec_not)
        ->firstOrFail();

    $datosCaso = $caso->toArray();

    $codigo_habilitacion = null;
    $nombre_ips_primaria = null;

    // 1) Buscar IPS primaria (codigo habilitación) desde afiliado
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

    /**
     * ✅ CLAVE:
     * - $usuarios = TODOS (siempre)
     * - $usuariosSugeridos = solo los del código habilitación (para marcar/preseleccionar)
     */

    // 2) TODOS los usuarios (para que el select SIEMPRE muestre todo)
    $usuarios = \App\Models\User::select('id', 'name', 'email', 'codigohabilitacion')
        ->orderBy('name')
        ->get();

    // 3) Sugeridos (pueden ser vacíos)
    $usuariosSugeridos = collect();
    if (!empty($codigo_habilitacion)) {
        $usuariosSugeridos = \App\Models\User::select('id', 'name', 'email', 'codigohabilitacion')
            ->where('codigohabilitacion', $codigo_habilitacion)
            ->orderBy('name')
            ->get();
    }

    // 4) ids a preseleccionar (solo los sugeridos)
    $usuarios_prestador_primario = $usuariosSugeridos->pluck('id')->toArray();

    // 5) bandera informativa
    $mostrando_todos = $usuariosSugeridos->isEmpty();

    return view('asignaciones_maestrosiv549.create', compact(
        'datosCaso',
        'usuarios',                    // <-- SIEMPRE TODOS
        'codigo_habilitacion',
        'nombre_ips_primaria',
        'usuarios_prestador_primario', // <-- SOLO sugeridos
        'mostrando_todos'
    ));
}






public function store(Request $request)
{
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

        // ✅ IMPORTANTE: poner user_id antes de crear
        $data = $baseData;
        $data['user_id'] = $usuarioAsignado->id;

        $asignacion = \App\Models\AsignacionesMaestrosiv549::create($data);

        // ✅ correo al asignado
        \Mail::to($usuarioAsignado->email)
            ->send(new \App\Mail\CasoAsignadoMail($asignacion, $usuarioAsignado, $usuarioAsignador));

        // ✅ correo al asignador (uno por cada usuario, sin cambiar tu mailable)
        \Mail::to($usuarioAsignador->email)
            ->send(new \App\Mail\AsignacionRealizadaMail($asignacion, $usuarioAsignado, $usuarioAsignador));
    }

    return redirect()->route('maestrosiv549.index')
        ->with('success', 'Caso asignado correctamente y correos enviados a todos los usuarios seleccionados.');
}


}
