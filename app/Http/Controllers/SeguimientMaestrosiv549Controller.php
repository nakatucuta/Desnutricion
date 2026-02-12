<?php

namespace App\Http\Controllers;

use App\Models\AsignacionesMaestrosiv549;
use App\Models\SeguimientMaestrosiv549;
use Illuminate\Http\Request;

class SeguimientMaestrosiv549Controller extends Controller
{
    public function create(AsignacionesMaestrosiv549 $asignacion)
    {
        $seguimiento = null;
        return view('seguimientmaestrosiv549.create', compact('asignacion','seguimiento'));
    }

    public function store(Request $request, AsignacionesMaestrosiv549 $asignacion)
    {
        $data = $this->rules($request);
        $data = $this->normalizeBooleans($request, $data);

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
        $data = $this->normalizeBooleans($request, $data);

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

    /**
     * ✅ VALIDACIÓN
     * - Criterios: checkbox => 0/1 (hidden + checkbox)
     * - Seguimiento efectivo: SELECT => 0/1
     */
    private function rules(Request $request): array
    {
        return $request->validate([
            'fecha_hospitalizacion' => 'nullable|date',
            'fecha_egreso' => 'nullable|date',
            'institucion_egreso_paciente' => 'nullable|string|max:255',
            'gestion_hospitalizacion' => 'nullable|string',

            // ✅ CHECKBOX (hidden 0 + checkbox 1)
            'eclampsia' => 'nullable|in:0,1',
            'preeclampsia_severa' => 'nullable|in:0,1',
            'sepsis_infeccion_sistemica_severa' => 'nullable|in:0,1',
            'hemorragia_obstetrica_severa' => 'nullable|in:0,1',
            'ruptura_uterina' => 'nullable|in:0,1',
            'falla_cardiovascular' => 'nullable|in:0,1',
            'falla_renal' => 'nullable|in:0,1',
            'falla_hepatica' => 'nullable|in:0,1',
            'falla_cerebral' => 'nullable|in:0,1',
            'falla_respiratoria' => 'nullable|in:0,1',
            'falla_coagulacion' => 'nullable|in:0,1',
            'cirugia_adicional' => 'nullable|in:0,1',

            'ttl_criter' => 'nullable|integer|min:0|max:50',
            'diagnostico_cie10' => 'nullable|string|max:20',
            'causa_agrupada' => 'nullable|string|max:255',

            'descripcion_seguimiento_inmediato' => 'nullable|string',
            'fecha_control_rn_inmediato' => 'nullable|date',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_inmediato' => 'required|in:0,1',

            'fecha_seguimiento_1' => 'nullable|date',
            'tipo_seguimiento_1' => 'nullable|integer',
            'paciente_sigue_embarazo_1' => 'nullable|in:0,1',
            'fecha_control_1' => 'nullable|date',
            'metodo_anticonceptivo' => 'nullable|string',
            'fecha_consulta_rn_1' => 'nullable|date',
            'entrega_medicamentos_labs_1' => 'nullable|string',
            'gestion_posegreso_1' => 'nullable|string',

            'fecha_seguimiento_2' => 'nullable|date',
            'paciente_sigue_embarazo_2' => 'nullable|in:0,1',
            'fecha_control_2' => 'nullable|date',
            'fecha_consulta_rn_2' => 'nullable|date',
            'entrega_medicamentos_labs_2' => 'nullable|string',
            'gestion_primera_semana' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_2' => 'required|in:0,1',

            'fecha_seguimiento_3' => 'nullable|date',
            'tipo_seguimiento_3' => 'nullable|integer',
            'paciente_sigue_embarazo_3' => 'nullable|in:0,1',
            'fecha_control_3' => 'nullable|date',
            'fecha_consulta_rn_3' => 'nullable|date',
            'entrega_medicamentos_labs_3' => 'nullable|string',
            'gestion_segunda_semana' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_3' => 'required|in:0,1',

            'fecha_seguimiento_4' => 'nullable|date',
            'tipo_seguimiento_4' => 'nullable|integer',
            'paciente_sigue_embarazo_4' => 'nullable|in:0,1',
            'fecha_control_4' => 'nullable|date',
            'fecha_consulta_rn_4' => 'nullable|date',
            'entrega_medicamentos_labs_4' => 'nullable|string',
            'gestion_tercera_semana' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_4' => 'required|in:0,1',

            'fecha_seguimiento_5' => 'nullable|date',
            'tipo_seguimiento_5' => 'nullable|integer',
            'paciente_sigue_embarazo_5' => 'nullable|in:0,1',
            'fecha_control_5' => 'nullable|date',
            'fecha_consulta_rn_5' => 'nullable|date',
            'entrega_medicamentos_labs_5' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_5' => 'required|in:0,1',

            'fecha_consulta_lactancia' => 'nullable|date',
            'fecha_control_metodo' => 'nullable|date',
            'gestion_despues_mes' => 'nullable|string',

            'fecha_consulta_6_meses' => 'nullable|date',
            'fecha_consulta_1_ano' => 'nullable|date',
        ]);
    }

    /**
     * ✅ NORMALIZA A ENTEROS 0/1
     * - CHECKBOX: toma input (viene del hidden 0 o checkbox 1)
     * - SELECT: viene 0/1 siempre
     */
    private function normalizeBooleans(Request $request, array $data): array
    {
        // ✅ criterios checkbox
        $checkboxes = [
            'eclampsia',
            'preeclampsia_severa',
            'sepsis_infeccion_sistemica_severa',
            'hemorragia_obstetrica_severa',
            'ruptura_uterina',
            'falla_cardiovascular',
            'falla_renal',
            'falla_hepatica',
            'falla_cerebral',
            'falla_respiratoria',
            'falla_coagulacion',
            'cirugia_adicional',
        ];

        foreach ($checkboxes as $f) {
            $data[$f] = (int) $request->input($f, 0);
        }

        // ✅ selects seguimiento efectivo
        $selects = [
            'seguimiento_efectivo_inmediato',
            'seguimiento_efectivo_2',
            'seguimiento_efectivo_3',
            'seguimiento_efectivo_4',
            'seguimiento_efectivo_5',
        ];

        foreach ($selects as $f) {
            $data[$f] = (int) $request->input($f, 0);
        }

        // ✅ paciente_sigue_embarazo_* también viene del select 0/1 (si lo dejas así)
        $embarazo = [
            'paciente_sigue_embarazo_1',
            'paciente_sigue_embarazo_2',
            'paciente_sigue_embarazo_3',
            'paciente_sigue_embarazo_4',
            'paciente_sigue_embarazo_5',
        ];

        foreach ($embarazo as $f) {
            // si el campo viene vacío (opción "--"), lo dejamos null
            $val = $request->input($f, null);
            $data[$f] = ($val === null || $val === '') ? null : (int) $val;
        }

        return $data;
    }
}
