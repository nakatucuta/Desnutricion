<?php

namespace App\Http\Controllers;

use App\Models\GesTipo1;
use App\Models\GesTipo1Seguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// ✅ ALERTAS
use App\Services\GestantesAlertService;

class GesTipo1SeguimientoController extends Controller
{
    public function create(GesTipo1 $ges)
    {
        $ultimo = $ges->seguimientos()->reorder()->latest('id')->first();
        return view('ges_tipo1.seguimientos.create', compact('ges','ultimo'));
    }

    public function store(Request $request, GesTipo1 $ges)
    {
        $data = $request->validate($this->rules());

        $this->limpiarVacios($data);
        $this->normalizarFechasSqlServer($data);

        // PDF -> guarda ruta en *_resultado (como lo tienes actualmente)
        $this->ingestarArchivosResultado($request, $data);

        $data['ges_tipo1_id'] = $ges->id;
        $data['user_id']      = Auth::id();

        try {
            $seg = GesTipo1Seguimiento::create($data);

            // ✅ CREAR ALERTAS + ENVIAR CORREO
            GestantesAlertService::scanAndCreateFromSeguimiento(
                $request,
                (int) $ges->id,
                (int) $seg->id,
                Auth::id(),
                $this->resultadoLabels()
            );

        } catch (\Throwable $e) {
            Log::error('❌ ERROR INSERT SEGUIMIENTO (payload de fechas)', [
                'ges_tipo1_id' => $ges->id,
                'user_id'      => Auth::id(),
                'date_fields'  => $this->extraerSoloFechas($data),
                'message'      => $e->getMessage(),
            ]);
            throw $e;
        }

        return redirect()
            ->route('ges_tipo1.show', ['ges' => $ges->id])
            ->with('success', 'Seguimiento creado correctamente.');
    }

    public function edit(GesTipo1 $ges, GesTipo1Seguimiento $seg)
    {
        return view('ges_tipo1.seguimientos.edit', compact('ges','seg'));
    }

    public function update(Request $request, GesTipo1 $ges, GesTipo1Seguimiento $seg)
    {
        $data = $request->validate($this->rules());

        $this->limpiarVacios($data);
        $this->normalizarFechasSqlServer($data);
        $this->ingestarArchivosResultado($request, $data);

        /**
         * ✅ CLAVE:
         * NO borres rutas PDF si no mandan archivo.
         * (Como en rules() NO validamos *_resultado_pdf, esos campos NO vienen en $data,
         * entonces update() no los toca y quedan intactos)
         */
        try {
            $seg->update($data);

            // ✅ CREAR ALERTAS + ENVIAR CORREO (también en update)
            GestantesAlertService::scanAndCreateFromSeguimiento(
                $request,
                (int) $ges->id,
                (int) $seg->id,
                Auth::id(),
                $this->resultadoLabels()
            );

        } catch (\Throwable $e) {
            Log::error('❌ ERROR UPDATE SEGUIMIENTO (payload de fechas)', [
                'seg_id'       => $seg->id,
                'ges_tipo1_id' => $ges->id,
                'user_id'      => Auth::id(),
                'date_fields'  => $this->extraerSoloFechas($data),
                'message'      => $e->getMessage(),
            ]);
            throw $e;
        }

        return redirect()
            ->route('ges_tipo1.show', ['ges' => $ges->id])
            ->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy(GesTipo1 $ges, GesTipo1Seguimiento $seg)
    {
        $seg->delete();

        return redirect()
            ->route('ges_tipo1.show', ['ges' => $ges->id])
            ->with('success', 'Seguimiento eliminado.');
    }

    /**
     * ✅ Labels bonitos para el módulo Alertas
     */
    private function resultadoLabels(): array
    {
        return [
            'vih_tamiz1_resultado' => 'VIH Tamiz 1',
            'vih_tamiz2_resultado' => 'VIH Tamiz 2',
            'vih_tamiz3_resultado' => 'VIH Tamiz 3',

            'sifilis_rapida1_resultado' => 'Sífilis rápida 1',
            'sifilis_rapida2_resultado' => 'Sífilis rápida 2',
            'sifilis_rapida3_resultado' => 'Sífilis rápida 3',
            'sifilis_no_trep_resultado' => 'Sífilis no treponémica',

            'urocultivo_resultado' => 'Urocultivo',
            'glicemia_resultado' => 'Glicemia',
            'pto_glucosa_resultado' => 'PTO glucosa',
            'hemoglobina_resultado' => 'Hemoglobina',
            'hemoclasificacion_resultado' => 'Hemoclasificación',
            'ag_hbs_resultado' => 'Hepatitis B (Ag HBs)',
            'toxoplasma_resultado' => 'Toxoplasma',
            'rubeola_resultado' => 'Rubéola',
            'citologia_resultado' => 'Citología',
            'frotis_vaginal_resultado' => 'Frotis vaginal',
            'estreptococo_resultado' => 'Estreptococo',
            'malaria_resultado' => 'Malaria',
            'chagas_resultado' => 'Chagas',
        ];
    }

    private function rules(): array
    {
        $rules = [
            // Cabecera
            'fecha_contacto'    => 'nullable',
            'tipo_contacto'     => 'nullable|integer|in:1,2,3',
            'estado'            => 'nullable|string|max:50',
            'proximo_contacto'  => 'nullable',
            'fecha_seguimiento' => 'nullable',
            'observaciones'     => 'nullable|string',

            // Identificación / demografía
            'tipo_documento'           => 'nullable|string|max:10',
            'numero_identificacion'    => 'nullable|string|max:50',
            'apellido_1'               => 'nullable|string|max:100',
            'apellido_2'               => 'nullable|string|max:100',
            'nombre_1'                 => 'nullable|string|max:100',
            'nombre_2'                 => 'nullable|string|max:100',
            'fecha_nacimiento'         => 'nullable',
            'edad_anios'               => 'nullable|integer',
            'sexo'                     => 'nullable|string|max:20',
            'regimen_afiliacion'       => 'nullable|string|max:50',
            'pertenencia_etnica'       => 'nullable|string|max:100',
            'grupo_poblacional'        => 'nullable|string|max:100',
            'departamento_residencia'  => 'nullable|string|max:100',
            'municipio_residencia'     => 'nullable|string|max:100',
            'zona'                     => 'nullable|string|max:50',
            'etnia'                    => 'nullable|string|max:100',
            'asentamiento'             => 'nullable|string|max:150',
            'telefono_usuaria'         => 'nullable|string|max:50',
            'direccion'                => 'nullable|string|max:200',
            'nivel_educativo'          => 'nullable|string|max:100',
            'discapacidad'             => 'nullable|string|max:100',
            'mujer_cabeza_hogar'       => 'nullable|string|max:10',
            'ocupacion'                => 'nullable|string|max:100',
            'estado_civil'             => 'nullable|string|max:50',
            'control_tradicional'      => 'nullable|string|max:10',
            'gestante_renuente'        => 'nullable|string|max:10',
            'inasistente'              => 'nullable|string|max:10',
            'ips_primaria'             => 'nullable|string|max:150',

            // Gestación
            'fecha_ingreso_cpn'        => 'nullable',
            'fum'                      => 'nullable',
            'fpp'                      => 'nullable',
            'dias_para_parto'          => 'nullable|integer',
            'alarma'                   => 'nullable|string|max:10',
            'edad_gest_inicio_control' => 'nullable|string|max:50',
            'trimestre_inicio_control' => 'nullable|string|max:50',
            'formula_obstetrica'       => 'nullable|string|max:100',

            // Morbilidades
            'hipertension_arterial'    => 'nullable|string|max:10',
            'diabetes'                 => 'nullable|string|max:10',
            'vih'                      => 'nullable|string|max:10',
            'sifilis'                  => 'nullable|string|max:10',
            'tuberculosis'             => 'nullable|string|max:10',
            'otras_condiciones_graves' => 'nullable|string',
            'apoyo_familiar'           => 'nullable|string|max:10',
            'embarazo_deseado'         => 'nullable|string|max:10',
            'habitos_riesgo'           => 'nullable|string',
            'violencia'                => 'nullable|string|max:10',
            'abuso_sexual'             => 'nullable|string|max:10',
            'periodo_intergenesico'    => 'nullable|string|max:50',

            // Antropometría
            'peso_inicial'             => 'nullable|numeric',
            'talla'                    => 'nullable|numeric',
            'imc'                      => 'nullable|numeric',
            'clasificacion_imc'        => 'nullable|string|max:100',
            'riesgos_psicosociales'    => 'nullable|string',
            'ive_causales'             => 'nullable|string',
            'clasificacion_riesgo'     => 'nullable|string|max:100',
            'alto_riesgo_causas'       => 'nullable|string',
            'otras_cuales'             => 'nullable|string',

            // Asesorías
            'remitida_especialista'    => 'nullable|string|max:10',
            'asesoria_vih'             => 'nullable|string|max:10',
            'asesoria_vih_trimestre'   => 'nullable|string|max:50',

            // Resultados / fechas
            'vih_tamiz1_fecha'         => 'nullable',
            'vih_tamiz1_resultado'     => 'nullable|string',
            'vih_tamiz1_trimestre'     => 'nullable|string|max:50',

            'vih_tamiz2_fecha'         => 'nullable',
            'vih_tamiz2_resultado'     => 'nullable|string',
            'vih_tamiz2_trimestre'     => 'nullable|string|max:50',

            'vih_tamiz3_fecha'         => 'nullable',
            'vih_tamiz3_resultado'     => 'nullable|string',
            'vih_tamiz3_trimestre'     => 'nullable|string|max:50',

            'vih_confirmatoria_fecha'      => 'nullable',
            'vih_confirmatoria_trimestre'  => 'nullable|string|max:50',

            'sifilis_rapida1_fecha'     => 'nullable',
            'sifilis_rapida1_resultado' => 'nullable|string',
            'sifilis_rapida1_trimestre' => 'nullable|string|max:50',

            'sifilis_rapida2_fecha'     => 'nullable',
            'sifilis_rapida2_resultado' => 'nullable|string',
            'sifilis_rapida2_trimestre' => 'nullable|string|max:50',

            'sifilis_rapida3_fecha'     => 'nullable',
            'sifilis_rapida3_resultado' => 'nullable|string',
            'sifilis_rapida3_trimestre' => 'nullable|string|max:50',

            'sifilis_no_trep_fecha'     => 'nullable',
            'sifilis_no_trep_resultado' => 'nullable|string',
            'sifilis_no_trep_trimestre' => 'nullable|string|max:50',

            'urocultivo_fecha'          => 'nullable',
            'urocultivo_resultado'      => 'nullable|string',

            'glicemia_fecha'            => 'nullable',
            'glicemia_resultado'        => 'nullable|string',

            'pto_glucosa_fecha'         => 'nullable',
            'pto_glucosa_resultado'     => 'nullable|string',

            'hemoglobina_fecha'         => 'nullable',
            'hemoglobina_resultado'     => 'nullable|string',

            'hemoclasificacion_resultado' => 'nullable|string',

            'ag_hbs_fecha'              => 'nullable',
            'ag_hbs_resultado'          => 'nullable|string',

            'toxoplasma_fecha'          => 'nullable',
            'toxoplasma_resultado'      => 'nullable|string',

            'rubeola_fecha'             => 'nullable',
            'rubeola_resultado'         => 'nullable|string',

            'citologia_fecha'           => 'nullable',
            'citologia_resultado'       => 'nullable|string',

            'frotis_vaginal_fecha'      => 'nullable',
            'frotis_vaginal_resultado'  => 'nullable|string',

            'estreptococo_fecha'        => 'nullable',
            'estreptococo_resultado'    => 'nullable|string',

            'malaria_fecha'             => 'nullable',
            'malaria_resultado'         => 'nullable|string',

            'chagas_fecha'              => 'nullable',
            'chagas_resultado'          => 'nullable|string',

            // Vacunas / controles
            'vac_influenza_fecha'           => 'nullable',
            'vac_toxoide_fecha'             => 'nullable',
            'vac_dpt_acelular_fecha'        => 'nullable',
            'consulta_odontologica_fecha'   => 'nullable',

            // Ecos y suministros
            'eco_translucencia'         => 'nullable|string',
            'eco_anomalias'             => 'nullable|string',
            'eco_otras'                 => 'nullable|string',
            'suministro_acido_folico'   => 'nullable|string|max:10',
            'suministro_calcio'         => 'nullable|string|max:10',
            'suministro_hierro'         => 'nullable|string|max:10',
            'suministro_asa'            => 'nullable|string|max:10',
            'desparasitacion_fecha'     => 'nullable',
            'informacion_en_salud'      => 'nullable|string|max:10',

            // CPN
            'cpn1_fecha' => 'nullable', 'cpn1_quien' => 'nullable|string|max:150',
            'cpn2_fecha' => 'nullable', 'cpn2_quien' => 'nullable|string|max:150',
            'cpn3_fecha' => 'nullable', 'cpn3_quien' => 'nullable|string|max:150',
            'cpn4_fecha' => 'nullable', 'cpn4_quien' => 'nullable|string|max:150',
            'cpn5_fecha' => 'nullable', 'cpn5_quien' => 'nullable|string|max:150',
            'cpn6_fecha' => 'nullable', 'cpn6_quien' => 'nullable|string|max:150',
            'cpn7_fecha' => 'nullable', 'cpn7_quien' => 'nullable|string|max:150',
            'cpn8_fecha' => 'nullable', 'cpn8_quien' => 'nullable|string|max:150',
            'cpn9_fecha' => 'nullable', 'cpn9_quien' => 'nullable|string|max:150',
            'num_total_cpn' => 'nullable|integer',
            'ultimo_cpn'    => 'nullable|string|max:150',

            // Consultas especialistas
            'cons_ginecologia_1'    => 'nullable|string|max:10',
            'cons_ginecologia_2'    => 'nullable|string|max:10',
            'cons_ginecologia_3'    => 'nullable|string|max:10',
            'cons_nutricion'        => 'nullable|string|max:10',
            'cons_psicologia'       => 'nullable|string|max:10',
            'cons_otro_especialista'=> 'nullable|string|max:10',
            'cons_otro_quien'       => 'nullable|string|max:150',
            'especialistas_describe'=> 'nullable|string',

            // Parto y RN
            'parto_tipo'            => 'nullable|string|max:100',
            'parto_sem_gest'        => 'nullable|string|max:50',
            'parto_complicaciones'  => 'nullable|string',
            'uci_materna'           => 'nullable|string|max:10',
            'its_intraparto_toma'   => 'nullable|string|max:10',
            'its_intraparto_positivo'=> 'nullable|string|max:10',
            'defuncion_fecha'       => 'nullable',
            'defuncion_causa'       => 'nullable|string',
            'multiplicidad_embarazo'=> 'nullable|string|max:50',

            // RN1
            'rn1_registro_civil'    => 'nullable|string|max:10',
            'rn1_nombre'            => 'nullable|string|max:150',
            'rn1_sexo'              => 'nullable|string|max:20',
            'rn1_peso'              => 'nullable|integer',
            'rn1_condicion'         => 'nullable|string|max:150',
            'rn1_tsh'               => 'nullable|string|max:150',
            'rn1_hipotiroideo_dx'   => 'nullable|string|max:10',
            'rn1_trat_hipotiroideo' => 'nullable|string|max:10',
            'rn1_uci'               => 'nullable|string|max:10',
            'rn1_vac_bcg'           => 'nullable|string|max:10',
            'rn1_vac_hepb'          => 'nullable|string|max:10',

            // RN2
            'rn2_registro_civil'    => 'nullable|string|max:10',
            'rn2_nombre'            => 'nullable|string|max:150',
            'rn2_sexo'              => 'nullable|string|max:20',
            'rn2_peso'              => 'nullable|integer',
            'rn2_condicion'         => 'nullable|string|max:150',
            'rn2_tsh'               => 'nullable|string|max:150',
            'rn2_hipotiroideo_dx'   => 'nullable|string|max:10',
            'rn2_trat_hipotiroideo' => 'nullable|string|max:10',
            'rn2_uci'               => 'nullable|string|max:10',
            'rn2_vac_bcg'           => 'nullable|string|max:10',
            'rn2_vac_hepb'          => 'nullable|string|max:10',
        ];

        // ✅ reglas para archivos + descripciones
        foreach ($this->resultadoFields() as $base) {
            $rules[$base . '_file'] = 'nullable|file|mimetypes:application/pdf|max:20480';
            $rules[$base . '_desc'] = 'nullable|string';
        }

        return $rules;
    }

    private function resultadoFields(): array
    {
        return [
            'vih_tamiz1_resultado','vih_tamiz2_resultado','vih_tamiz3_resultado',
            'sifilis_rapida1_resultado','sifilis_rapida2_resultado','sifilis_rapida3_resultado',
            'sifilis_no_trep_resultado','urocultivo_resultado','glicemia_resultado',
            'pto_glucosa_resultado','hemoglobina_resultado','hemoclasificacion_resultado',
            'ag_hbs_resultado','toxoplasma_resultado','rubeola_resultado','citologia_resultado',
            'frotis_vaginal_resultado','estreptococo_resultado','malaria_resultado','chagas_resultado',
        ];
    }

    protected function ingestarArchivosResultado(Request $request, array &$data): void
    {
        $campos = [
            'vih_tamiz1','vih_tamiz2','vih_tamiz3',
            'sifilis_rapida1','sifilis_rapida2','sifilis_rapida3',
            'sifilis_no_trep','urocultivo','glicemia','pto_glucosa',
            'hemoglobina','hemoclasificacion','ag_hbs','toxoplasma',
            'rubeola','citologia','frotis_vaginal','estreptococo',
            'malaria','chagas',
        ];

        foreach ($campos as $c) {
            $fileKey = "{$c}_resultado_file";
            $dbField = "{$c}_resultado"; // ✅ ruta del PDF

            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store("seguimientos", "public");
                $data[$dbField] = $path;
            }
        }
    }

    private function limpiarVacios(array &$data): void
    {
        foreach ($data as $k => $v) {
            if (!is_string($v)) continue;

            $vv = trim($v);

            if ($vv === '' || $vv === 'N/A' || $vv === '--' || $vv === '00/00/0000' || $vv === '0000-00-00') {
                $data[$k] = null;
            }
        }
    }

    private function normalizarFechasSqlServer(array &$data): void
    {
        $dateFields = [
            'fecha_contacto','proximo_contacto','fecha_seguimiento','fecha_nacimiento',
            'fecha_ingreso_cpn','fum','fpp',
            'vih_tamiz1_fecha','vih_tamiz2_fecha','vih_tamiz3_fecha','vih_confirmatoria_fecha',
            'sifilis_rapida1_fecha','sifilis_rapida2_fecha','sifilis_rapida3_fecha','sifilis_no_trep_fecha',
            'urocultivo_fecha','glicemia_fecha','pto_glucosa_fecha','hemoglobina_fecha','ag_hbs_fecha',
            'toxoplasma_fecha','rubeola_fecha','citologia_fecha','frotis_vaginal_fecha','estreptococo_fecha',
            'malaria_fecha','chagas_fecha',
            'vac_influenza_fecha','vac_toxoide_fecha','vac_dpt_acelular_fecha','consulta_odontologica_fecha',
            'desparasitacion_fecha',
            'cpn1_fecha','cpn2_fecha','cpn3_fecha','cpn4_fecha','cpn5_fecha','cpn6_fecha','cpn7_fecha','cpn8_fecha','cpn9_fecha',
            'defuncion_fecha',
        ];

        foreach ($dateFields as $f) {
            if (!array_key_exists($f, $data) || $data[$f] === null) continue;

            $raw = is_string($data[$f]) ? trim($data[$f]) : $data[$f];

            try {
                if (is_string($raw) && preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $raw)) {
                    $dt = Carbon::createFromFormat('d/m/Y', $raw);
                } elseif (is_string($raw) && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $raw)) {
                    $dt = Carbon::createFromFormat('Y-m-d', $raw);
                } else {
                    $dt = Carbon::parse($raw);
                }

                if ((int)$dt->format('Y') < 1753) {
                    $data[$f] = null;
                    continue;
                }

                $data[$f] = $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                $data[$f] = null;
            }
        }
    }

    private function extraerSoloFechas(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (str_contains($k, 'fecha') || in_array($k, ['fum','fpp'], true) || str_ends_with($k, '_fecha')) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    public function verArchivo(GesTipo1Seguimiento $seg, string $field)
    {
        $permitidos = [
            'vih_tamiz1_resultado_pdf','vih_tamiz2_resultado_pdf','vih_tamiz3_resultado_pdf',
            'sifilis_rapida1_resultado_pdf','sifilis_rapida2_resultado_pdf','sifilis_rapida3_resultado_pdf',
            'sifilis_no_trep_resultado_pdf','urocultivo_resultado_pdf','glicemia_resultado_pdf',
            'pto_glucosa_resultado_pdf','hemoglobina_resultado_pdf','hemoclasificacion_resultado_pdf',
            'ag_hbs_resultado_pdf','toxoplasma_resultado_pdf','rubeola_resultado_pdf','citologia_resultado_pdf',
            'frotis_vaginal_resultado_pdf','estreptococo_resultado_pdf','malaria_resultado_pdf','chagas_resultado_pdf',
        ];

        if (!in_array($field, $permitidos, true)) {
            abort(404);
        }

        $value = $seg->getAttribute($field);
        if (!$value || !is_string($value)) {
            abort(404);
        }

        $value = str_replace('\\', '/', trim($value));

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return redirect()->away($value);
        }

        $value = ltrim($value, '/');
        if (str_starts_with($value, 'storage/')) $value = substr($value, strlen('storage/'));
        if (str_starts_with($value, 'public/'))  $value = substr($value, strlen('public/'));

        if (!str_starts_with($value, 'seguimientos/')) {
            $value = 'seguimientos/' . $value;
        }

        if (!\Storage::disk('public')->exists($value)) {
            abort(404);
        }

        return \Storage::disk('public')->response($value, null, [
            'Content-Disposition' => 'inline',
        ]);
    }
}
