<?php

namespace App\Http\Controllers;

use App\Models\AsignacionesMaestrosiv549;
use App\Models\SeguimientMaestrosiv549;
use Illuminate\Http\Request;

class SeguimientMaestrosiv549Controller extends Controller
{
    public function create(AsignacionesMaestrosiv549 $asignacion)
    {
        return view('seguimientmaestrosiv549.create', compact('asignacion'));
    }

    public function store(Request $request, AsignacionesMaestrosiv549 $asignacion)
    {
        $data = $this->rules($request);
        $data['asignacion_id'] = $asignacion->id;
        SeguimientMaestrosiv549::create($data);

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento creado correctamente.');
    }

    public function edit(AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        return view('seguimientmaestrosiv549.edit', compact('asignacion','seguimiento'));
    }

    public function update(Request $request, AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        $data = $this->rules($request);
        $seguimiento->update($data);

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy(AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        $seguimiento->delete();

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento eliminado.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'fecha_hospitalizacion' => 'nullable|date',
            'gestion_hospitalizacion' => 'nullable|string',
            'fecha_egreso' => 'nullable|date',
            'descripcion_seguimiento_inmediato' => 'nullable|string',
            'fecha_seguimiento_1' => 'nullable|date',
            'tipo_seguimiento_1' => 'nullable|integer',
            'paciente_sigue_embarazo_1' => 'nullable|boolean',
            'fecha_control_1' => 'nullable|date',
            'metodo_anticonceptivo' => 'nullable|string',
            'fecha_consulta_rn_1' => 'nullable|date',
            'entrega_medicamentos_labs_1' => 'nullable|string',
            'gestion_posegreso_1' => 'nullable|string',
            'fecha_seguimiento_2' => 'nullable|date',
            'paciente_sigue_embarazo_2' => 'nullable|boolean',
            'fecha_control_2' => 'nullable|date',
            'fecha_consulta_rn_2' => 'nullable|date',
            'entrega_medicamentos_labs_2' => 'nullable|string',
            'gestion_primera_semana' => 'nullable|string',
            'fecha_seguimiento_3' => 'nullable|date',
            'tipo_seguimiento_3' => 'nullable|integer',
            'paciente_sigue_embarazo_3' => 'nullable|boolean',
            'fecha_control_3' => 'nullable|date',
            'fecha_consulta_rn_3' => 'nullable|date',
            'entrega_medicamentos_labs_3' => 'nullable|string',
            'gestion_segunda_semana' => 'nullable|string',
            'fecha_seguimiento_4' => 'nullable|date',
            'tipo_seguimiento_4' => 'nullable|integer',
            'paciente_sigue_embarazo_4' => 'nullable|boolean',
            'fecha_control_4' => 'nullable|date',
            'fecha_consulta_rn_4' => 'nullable|date',
            'entrega_medicamentos_labs_4' => 'nullable|string',
            'gestion_tercera_semana' => 'nullable|string',
            'fecha_seguimiento_5' => 'nullable|date',
            'tipo_seguimiento_5' => 'nullable|integer',
            'paciente_sigue_embarazo_5' => 'nullable|boolean',
            'fecha_control_5' => 'nullable|date',
            'fecha_consulta_rn_5' => 'nullable|date',
            'entrega_medicamentos_labs_5' => 'nullable|string',
            'fecha_consulta_lactancia' => 'nullable|date',
            'fecha_control_metodo' => 'nullable|date',
            'gestion_despues_mes' => 'nullable|string',
            'fecha_consulta_6_meses' => 'nullable|date',
            'fecha_consulta_1_ano' => 'nullable|date',
        ]);
    }
}
