<?php

namespace App\Http\Controllers;

use App\Models\AsignacionesMaestrosiv549;
use App\Models\SeguimientMaestrosiv549;
use App\Services\Seguimiento549AlertService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SeguimientMaestrosiv549Controller extends Controller
{
    public function __construct(private readonly Seguimiento549AlertService $alertService)
    {
        $this->middleware('auth');
    }

    public function create(AsignacionesMaestrosiv549 $asignacion)
    {
        $this->authorizeAsignacionAccess($asignacion);

        $seguimientoExistente = $this->findSharedSeguimiento($asignacion);
        if ($seguimientoExistente) {
            return redirect()
                ->route('asignaciones.seguimientmaestrosiv549.edit', [$asignacion->id, $seguimientoExistente->id])
                ->with('success', 'Este caso ya tiene seguimiento activo. Puedes continuarlo y actualizarlo.');
        }

        $seguimiento = null;
        return view('seguimientmaestrosiv549.create', compact('asignacion','seguimiento'));
    }

    public function store(Request $request, AsignacionesMaestrosiv549 $asignacion)
    {
        $this->authorizeAsignacionAccess($asignacion);

        $seguimientoExistente = $this->findSharedSeguimiento($asignacion);
        if ($seguimientoExistente) {
            return redirect()
                ->route('asignaciones.seguimientmaestrosiv549.edit', [$asignacion->id, $seguimientoExistente->id])
                ->with('success', 'Ya existe un seguimiento activo para este caso/período. Puedes editarlo.');
        }

        $data = $this->rules($request);
        $data = $this->normalizeBooleans($request, $data);
        $this->validateConditionalBlocks($request);
        $data = $this->persistSupportFiles($request, $data);

        $data['asignacion_id'] = $this->resolveCanonicalAsignacionId($asignacion);

        $seguimiento = SeguimientMaestrosiv549::create($data);
        $seguimiento->load('asignacion.user');
        $this->alertService->syncSuperImmediateAlert($seguimiento);

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento creado correctamente.');
    }

    public function show(AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        $this->authorizeSeguimientoAccess($asignacion, $seguimiento);

        return view('seguimientmaestrosiv549.show', compact('asignacion', 'seguimiento'));
    }

    public function edit(AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        $this->authorizeSeguimientoAccess($asignacion, $seguimiento);

        return view('seguimientmaestrosiv549.edit', compact('asignacion','seguimiento'));
    }

    public function update(Request $request, AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        $this->authorizeSeguimientoAccess($asignacion, $seguimiento);

        $data = $this->rules($request);
        $data = $this->normalizeBooleans($request, $data);
        $this->validateConditionalBlocks($request);
        $data = $this->persistSupportFiles($request, $data, $seguimiento);

        $seguimiento->update($data);
        $seguimiento->refresh();
        $seguimiento->load('asignacion.user');
        $this->alertService->syncSuperImmediateAlert($seguimiento);

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy(Request $request, AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento)
    {
        $this->authorizeSeguimientoAccess($asignacion, $seguimiento);
        $this->authorizeDelete();

        foreach ($this->supportFields() as $field) {
            $path = (string) ($seguimiento->{$field} ?? '');
            if ($path !== '') {
                Storage::disk('public')->delete($path);
            }
        }

        $seguimiento->delete();

        $message = 'Seguimiento eliminado.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('seguimientos.index')->with('success', $message);
    }

    private function authorizeAsignacionAccess(AsignacionesMaestrosiv549 $asignacion): void
    {
        abort_if(!auth()->check(), 401, 'No autenticado');

        $user = auth()->user();
        $usertype = (int) ($user->usertype ?? 0);
        $canSeeAll = $usertype === 1;
        $isPrestador = $usertype === 2;

        if ($canSeeAll) {
            return;
        }

        abort_unless($isPrestador, 403, 'Solo administradores (tipo 1) y prestadores (tipo 2) pueden acceder al modulo 549.');

        $grupoIds = $this->sameCasePeriodAssignmentsQuery($asignacion)->pluck('id');
        $puedeAcceder = !empty($grupoIds) && \DB::table('asignaciones_maestrosiv549_colaboradores')
            ->whereIn('asignacion_id', $grupoIds)
            ->where('user_id', (int) $user->id)
            ->exists();

        abort_unless($puedeAcceder, 403, 'No autorizado para acceder a esta asignacion.');
    }

    private function authorizeSeguimientoAccess(AsignacionesMaestrosiv549 $asignacion, SeguimientMaestrosiv549 $seguimiento): void
    {
        $grupoIds = $this->sameCasePeriodAssignmentsQuery($asignacion)->pluck('id');
        abort_unless($grupoIds->contains((int) $seguimiento->asignacion_id), 404, 'Seguimiento no asociado al caso solicitado.');
        $this->authorizeAsignacionAccess($asignacion);
    }

    private function authorizeDelete(): void
    {
        abort_unless((int) (auth()->user()->usertype ?? 0) === 1, 403, 'Solo los administradores pueden eliminar seguimientos.');
    }

    /**
     * ✅ VALIDACIÓN
     * - Criterios: checkbox => 0/1 (hidden + checkbox)
     * - Seguimiento efectivo: SELECT => 0/1
     */
    private function rules(Request $request): array
    {
        $data = $request->validate([
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
            'seguimiento_efectivo_inmediato' => 'nullable|in:0,1',

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
            'seguimiento_efectivo_2' => 'nullable|in:0,1',

            'fecha_seguimiento_3' => 'nullable|date',
            'tipo_seguimiento_3' => 'nullable|integer',
            'paciente_sigue_embarazo_3' => 'nullable|in:0,1',
            'fecha_control_3' => 'nullable|date',
            'fecha_consulta_rn_3' => 'nullable|date',
            'entrega_medicamentos_labs_3' => 'nullable|string',
            'gestion_segunda_semana' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_3' => 'nullable|in:0,1',

            'fecha_seguimiento_4' => 'nullable|date',
            'tipo_seguimiento_4' => 'nullable|integer',
            'paciente_sigue_embarazo_4' => 'nullable|in:0,1',
            'fecha_control_4' => 'nullable|date',
            'fecha_consulta_rn_4' => 'nullable|date',
            'entrega_medicamentos_labs_4' => 'nullable|string',
            'gestion_tercera_semana' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_4' => 'nullable|in:0,1',

            'fecha_seguimiento_5' => 'nullable|date',
            'tipo_seguimiento_5' => 'nullable|integer',
            'paciente_sigue_embarazo_5' => 'nullable|in:0,1',
            'fecha_control_5' => 'nullable|date',
            'fecha_consulta_rn_5' => 'nullable|date',
            'entrega_medicamentos_labs_5' => 'nullable|string',

            // ✅ SELECT 0/1
            'seguimiento_efectivo_5' => 'nullable|in:0,1',

            'fecha_consulta_lactancia' => 'nullable|date',
            'fecha_control_metodo' => 'nullable|date',
            'gestion_despues_mes' => 'nullable|string',

            'fecha_consulta_6_meses' => 'nullable|date',
            'fecha_consulta_1_ano' => 'nullable|date',

            // Soportes PDF (historia clinica por seguimiento)
            'soporte_inmediato_pdf' => 'nullable|file|mimetypes:application/pdf|max:5120',
            'soporte_seguimiento_1_pdf' => 'nullable|file|mimetypes:application/pdf|max:5120',
            'soporte_seguimiento_2_pdf' => 'nullable|file|mimetypes:application/pdf|max:5120',
            'soporte_seguimiento_3_pdf' => 'nullable|file|mimetypes:application/pdf|max:5120',
            'soporte_seguimiento_4_pdf' => 'nullable|file|mimetypes:application/pdf|max:5120',
            'soporte_seguimiento_5_pdf' => 'nullable|file|mimetypes:application/pdf|max:5120',
        ], [
            'soporte_inmediato_pdf.mimetypes' => 'Los soportes deben estar en formato PDF.',
            'soporte_seguimiento_1_pdf.mimetypes' => 'Los soportes deben estar en formato PDF.',
            'soporte_seguimiento_2_pdf.mimetypes' => 'Los soportes deben estar en formato PDF.',
            'soporte_seguimiento_3_pdf.mimetypes' => 'Los soportes deben estar en formato PDF.',
            'soporte_seguimiento_4_pdf.mimetypes' => 'Los soportes deben estar en formato PDF.',
            'soporte_seguimiento_5_pdf.mimetypes' => 'Los soportes deben estar en formato PDF.',
            'soporte_inmediato_pdf.max' => 'El archivo de historia clinica no puede superar 5 MB.',
            'soporte_seguimiento_1_pdf.max' => 'El archivo de historia clinica no puede superar 5 MB.',
            'soporte_seguimiento_2_pdf.max' => 'El archivo de historia clinica no puede superar 5 MB.',
            'soporte_seguimiento_3_pdf.max' => 'El archivo de historia clinica no puede superar 5 MB.',
            'soporte_seguimiento_4_pdf.max' => 'El archivo de historia clinica no puede superar 5 MB.',
            'soporte_seguimiento_5_pdf.max' => 'El archivo de historia clinica no puede superar 5 MB.',
        ]);

        $this->normalizeDateFields($data, [
            'fecha_hospitalizacion',
            'fecha_egreso',
            'fecha_control_rn_inmediato',
            'fecha_seguimiento_1',
            'fecha_control_1',
            'fecha_consulta_rn_1',
            'fecha_seguimiento_2',
            'fecha_control_2',
            'fecha_consulta_rn_2',
            'fecha_seguimiento_3',
            'fecha_control_3',
            'fecha_consulta_rn_3',
            'fecha_seguimiento_4',
            'fecha_control_4',
            'fecha_consulta_rn_4',
            'fecha_seguimiento_5',
            'fecha_control_5',
            'fecha_consulta_rn_5',
            'fecha_consulta_lactancia',
            'fecha_control_metodo',
            'fecha_consulta_6_meses',
            'fecha_consulta_1_ano',
        ]);

        return $data;
    }

    private function persistSupportFiles(Request $request, array $data, ?SeguimientMaestrosiv549 $seguimiento = null): array
    {
        foreach ($this->supportFields() as $field) {
            if (!$request->hasFile($field)) {
                unset($data[$field]);
                continue;
            }

            $file = $request->file($field);
            if (!$file || !$file->isValid()) {
                unset($data[$field]);
                continue;
            }

            $newPath = $file->store('seguimiento549/soportes', 'public');
            if (!$newPath) {
                unset($data[$field]);
                continue;
            }

            $oldPath = (string) ($seguimiento?->{$field} ?? '');
            if ($oldPath !== '' && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $data[$field] = $newPath;
        }

        return $data;
    }

    private function supportFields(): array
    {
        return [
            'soporte_inmediato_pdf',
            'soporte_seguimiento_1_pdf',
            'soporte_seguimiento_2_pdf',
            'soporte_seguimiento_3_pdf',
            'soporte_seguimiento_4_pdf',
            'soporte_seguimiento_5_pdf',
        ];
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
            $val = $request->input($f, null);
            $data[$f] = ($val === null || $val === '') ? 0 : (int) $val;
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

    private function normalizeDateFields(array &$data, array $fields): void
    {
        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if ($value === null || $value === '') {
                $data[$field] = null;
                continue;
            }

            try {
                $data[$field] = Carbon::parse((string) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                $data[$field] = null;
            }
        }
    }

    private function validateConditionalBlocks(Request $request): void
    {
        $errors = [];

        // Si se inicia el bloque inmediato, exige seguimiento efectivo inmediato.
        if ($this->hasAnyInput($request, [
            'descripcion_seguimiento_inmediato',
            'fecha_control_rn_inmediato',
            'soporte_inmediato_pdf',
        ]) && $request->input('seguimiento_efectivo_inmediato', '') === '') {
            $errors['seguimiento_efectivo_inmediato'] = 'Debes seleccionar si el seguimiento inmediato fue efectivo.';
        }

        // Para seguimientos 2..5: si se inicia el bloque, exige fecha y efectivo.
        foreach ([2, 3, 4, 5] as $idx) {
            $touchFields = [
                'fecha_seguimiento_'.$idx,
                'paciente_sigue_embarazo_'.$idx,
                'fecha_control_'.$idx,
                'fecha_consulta_rn_'.$idx,
                'entrega_medicamentos_labs_'.$idx,
                'soporte_seguimiento_'.$idx.'_pdf',
            ];

            if ($idx === 3) {
                $touchFields[] = 'gestion_segunda_semana';
                $touchFields[] = 'tipo_seguimiento_3';
            } elseif ($idx === 4) {
                $touchFields[] = 'gestion_tercera_semana';
                $touchFields[] = 'tipo_seguimiento_4';
            } elseif ($idx === 2) {
                $touchFields[] = 'gestion_primera_semana';
            } elseif ($idx === 5) {
                $touchFields[] = 'tipo_seguimiento_5';
            }

            if ($this->hasAnyInput($request, $touchFields)) {
                if (trim((string) $request->input('fecha_seguimiento_'.$idx, '')) === '') {
                    $errors['fecha_seguimiento_'.$idx] = 'Debes registrar la fecha del Seguimiento '.$idx.'.';
                }
                if (trim((string) $request->input('seguimiento_efectivo_'.$idx, '')) === '') {
                    $errors['seguimiento_efectivo_'.$idx] = 'Debes seleccionar si el Seguimiento '.$idx.' fue efectivo.';
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function hasAnyInput(Request $request, array $fields): bool
    {
        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                return true;
            }

            $value = $request->input($field, null);
            if ($value === null) {
                continue;
            }

            if (is_array($value) && !empty($value)) {
                return true;
            }

            if (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function sameCasePeriodAssignmentsQuery(AsignacionesMaestrosiv549 $asignacion)
    {
        return AsignacionesMaestrosiv549::query()
            ->whereRaw("LTRIM(RTRIM(COALESCE(tip_ide_, ''))) = ?", [trim((string) ($asignacion->tip_ide_ ?? ''))])
            ->whereRaw("LTRIM(RTRIM(COALESCE(num_ide_, ''))) = ?", [trim((string) ($asignacion->num_ide_ ?? ''))])
            ->whereRaw("LTRIM(RTRIM(COALESCE(nom_eve, ''))) = ?", [trim((string) ($asignacion->nom_eve ?? ''))])
            ->whereRaw(
                "COALESCE(NULLIF(LTRIM(RTRIM(COALESCE([year], ''))), ''), CONVERT(varchar(4), YEAR(fec_not)), '0000') = COALESCE(NULLIF(LTRIM(RTRIM(COALESCE(?, ''))), ''), CONVERT(varchar(4), YEAR(?)), '0000')",
                [trim((string) ($asignacion->year ?? '')), $asignacion->fec_not]
            )
            ->whereRaw(
                "COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(semana, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, fec_not)), 2), '00') = COALESCE(NULLIF(RIGHT('00' + LTRIM(RTRIM(COALESCE(?, ''))), 2), ''), RIGHT('00' + CONVERT(varchar(2), DATEPART(ISO_WEEK, ?)), 2), '00')",
                [trim((string) ($asignacion->semana ?? '')), $asignacion->fec_not]
            );
    }

    private function findSharedSeguimiento(AsignacionesMaestrosiv549 $asignacion): ?SeguimientMaestrosiv549
    {
        $groupIds = $this->sameCasePeriodAssignmentsQuery($asignacion)->pluck('id');
        if ($groupIds->isEmpty()) {
            return null;
        }

        return SeguimientMaestrosiv549::query()
            ->whereIn('asignacion_id', $groupIds)
            ->orderByDesc('id')
            ->first();
    }

    private function resolveCanonicalAsignacionId(AsignacionesMaestrosiv549 $asignacion): int
    {
        return (int) $this->sameCasePeriodAssignmentsQuery($asignacion)->min('id');
    }
}
