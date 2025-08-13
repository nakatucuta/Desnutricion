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
    // Buscar el caso
    $caso = \App\Models\MaestroSiv549::where('tip_ide_', $request->tip_ide_)
        ->where('num_ide_', $request->num_ide_)
        ->where('fec_not', $request->fec_not)
        ->firstOrFail();

    $datosCaso = $caso->toArray();

    // Buscar IPS primaria
    $codigo_habilitacion = null;
    $nombre_ips_primaria = null;

    if ($caso->num_ide_) {
        $afiliado = \DB::connection('sqlsrv_1')->table('maestroAfiliados as a')
            ->join('maestroips as b', 'a.numeroCarnet', '=', 'b.numeroCarnet')
            ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
            ->join('maestroIpsGruDet as d', function($join) {
                $join->on('c.id', '=', 'd.idd')
                    ->where('d.servicio', '=', 1);
            })
            ->join('refIps as e', 'd.idIps', '=', 'e.idIps')
            ->select(\DB::raw('CAST(e.codigo AS BIGINT) as codigo_habilitacion'), 'e.descrip as nombre_ips')
            ->where('a.identificacion', $caso->num_ide_)
            ->first();

        if ($afiliado !== null) {
            $codigo_habilitacion = $afiliado->codigo_habilitacion;
            $nombre_ips_primaria = $afiliado->nombre_ips;
        }
    }

    // Trae TODOS los usuarios
    $usuarios = \App\Models\User::select('id', 'name', 'email', 'codigohabilitacion')->orderBy('name')->get();

    // Filtra los que cumplen las condiciones
    $usuarios_prestador_primario = $usuarios->filter(function($user) use ($codigo_habilitacion) {
        return $user->codigohabilitacion == $codigo_habilitacion && \Str::endsWith($user->name, '_ges');
    })->pluck('id')->toArray();

    // Selecciona por defecto el primero que cumple, si existe
    $usuario_destacado = count($usuarios_prestador_primario) > 0 ? $usuarios_prestador_primario[0] : null;

    return view('asignaciones_maestrosiv549.create', compact(
        'datosCaso', 'usuarios', 'codigo_habilitacion', 'nombre_ips_primaria', 'usuarios_prestador_primario', 'usuario_destacado'
    ));
}






    public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'tip_ide_' => 'required',
        'num_ide_' => 'required',
        'fec_not' => 'required',
        'nom_eve' => 'required',
        // ...todos los campos requeridos aquí si lo deseas
    ]);

    // Guarda la asignación
    $asignacion = AsignacionesMaestrosiv549::create($request->all());

    // Trae el usuario que asigna (autenticado) y el usuario asignado (IPS)
    $usuarioAsignador = auth()->user();
    $usuarioAsignado = \App\Models\User::find($request->user_id);

    // Envía correo al usuario asignado (IPS)
    \Mail::to($usuarioAsignado->email)
        ->send(new \App\Mail\CasoAsignadoMail($asignacion, $usuarioAsignado, $usuarioAsignador));

    // Envía correo al usuario autenticado (quien asignó)
    \Mail::to($usuarioAsignador->email)
        ->send(new \App\Mail\AsignacionRealizadaMail($asignacion, $usuarioAsignado, $usuarioAsignador));

    return redirect()->route('maestrosiv549.index')
        ->with('success', 'Caso asignado correctamente y correos enviados.');
}

}
