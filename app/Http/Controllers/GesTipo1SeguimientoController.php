<?php

namespace App\Http\Controllers;

use App\Models\GesTipo1;
use App\Models\GesTipo1Seguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GesTipo1SeguimientoController extends Controller
{
    public function create(GesTipo1 $ges)
    {
        $ultimo = $ges->seguimientos()->latest('id')->first();
        return view('ges_tipo1.seguimientos.create', compact('ges','ultimo'));
    }

    public function store(Request $request, GesTipo1 $ges)
    {
        // 1) Validamos texto/fechas + posibles PDFs
        $data = $request->validate($this->rules());

        // 2) Procesamos todos los *_resultado_file (si hubo subida)
        $this->ingestarArchivosResultado($request, $data);

        // 3) Atributos base
        $data['ges_tipo1_id'] = $ges->id;
        $data['user_id']      = Auth::id();

        GesTipo1Seguimiento::create($data);

        return redirect()
            ->route('ges_tipo1.show', $ges->id)
            ->with('success', 'Seguimiento creado correctamente (archivos soportados).');
    }

    public function edit(GesTipo1 $ges, GesTipo1Seguimiento $seg)
    {
        return view('ges_tipo1.seguimientos.edit', compact('ges','seg'));
    }

    public function update(Request $request, GesTipo1 $ges, GesTipo1Seguimiento $seg)
    {
        // 1) Validamos texto/fechas + posibles PDFs
        $data = $request->validate($this->rules());

        // 2) Procesamos nuevos PDFs (si vienen), sobreescribiendo el campo *_resultado
        $this->ingestarArchivosResultado($request, $data);

        $seg->update($data);

        return redirect()
            ->route('ges_tipo1.show', $ges->id)
            ->with('success', 'Seguimiento actualizado correctamente (archivos soportados).');
    }

    public function destroy(GesTipo1 $ges, GesTipo1Seguimiento $seg)
    {
        $seg->delete();
        return redirect()
            ->route('ges_tipo1.show', $ges->id)
            ->with('success', 'Seguimiento eliminado.');
    }

    /**
     * Reglas de validación (texto/fecha/num) + reglas de archivos PDF para todos los *_resultado_file
     */
    private function rules(): array
    {
        // Reglas base (iguales a las que ya tenías)
        $rules = [
            // Cabecera
            'fecha_contacto'    => 'nullable|date',
            'tipo_contacto'     => 'nullable|integer|in:1,2,3',
            'estado'            => 'nullable|string|max:50',
            'proximo_contacto'  => 'nullable|date',
            'fecha_seguimiento' => 'nullable|date',
            'observaciones'     => 'nullable|string',

            // Identificación / demografía
            'tipo_documento'           => 'nullable|string|max:10',
            'numero_identificacion'    => 'nullable|string|max:50',
            'apellido_1'               => 'nullable|string|max:100',
            'apellido_2'               => 'nullable|string|max:100',
            'nombre_1'                 => 'nullable|string|max:100',
            'nombre_2'                 => 'nullable|string|max:100',
            'fecha_nacimiento'         => 'nullable|date',
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
            'fecha_ingreso_cpn'        => 'nullable|date',
            'fum'                      => 'nullable|date',
            'fpp'                      => 'nullable|date',
            'dias_para_parto'          => 'nullable|integer',
            'alarma'                   => 'nullable|string|max:10',
            'edad_gest_inicio_control' => 'nullable|string|max:50',
            'trimestre_inicio_control' => 'nullable|string|max:50',
            'formula_obstetrica'       => 'nullable|string|max:50',

            // Morbilidades y factores
            'hipertension_arterial'    => 'nullable|string|max:10',
            'diabetes'                 => 'nullable|string|max:10',
            'vih'                      => 'nullable|string|max:10',
            'sifilis'                  => 'nullable|string|max:10',
            'tuberculosis'             => 'nullable|string|max:10',
            'otras_condiciones_graves' => 'nullable|string|max:200',
            'apoyo_familiar'           => 'nullable|string|max:10',
            'embarazo_deseado'         => 'nullable|string|max:10',
            'habitos_riesgo'           => 'nullable|string|max:200',
            'violencia'                => 'nullable|string|max:10',
            'abuso_sexual'             => 'nullable|string|max:10',
            'periodo_intergenesico'    => 'nullable|string|max:50',

            // Antropometría y riesgo
            'peso_inicial'             => 'nullable|numeric',
            'talla'                    => 'nullable|numeric',
            'imc'                      => 'nullable|numeric',
            'clasificacion_imc'        => 'nullable|string|max:50',
            'riesgos_psicosociales'    => 'nullable|string|max:200',
            'ive_causales'             => 'nullable|string|max:200',
            'clasificacion_riesgo'     => 'nullable|string|max:100',
            'alto_riesgo_causas'       => 'nullable|string|max:200',
            'otras_cuales'             => 'nullable|string|max:200',

            // Asesorías / Remisiones
            'remitida_especialista'    => 'nullable|string|max:10',
            'asesoria_vih'             => 'nullable|string|max:10',
            'asesoria_vih_trimestre'   => 'nullable|string|max:50',

            // Tamizajes/Resultados/Fechas
            'vih_tamiz1_fecha'         => 'nullable|date',
            'vih_tamiz1_resultado'     => 'nullable|string|max:300',
            'vih_tamiz1_trimestre'     => 'nullable|string|max:50',
            'vih_tamiz2_fecha'         => 'nullable|date',
            'vih_tamiz2_resultado'     => 'nullable|string|max:300',
            'vih_tamiz2_trimestre'     => 'nullable|string|max:50',
            'vih_tamiz3_fecha'         => 'nullable|date',
            'vih_tamiz3_resultado'     => 'nullable|string|max:300',
            'vih_tamiz3_trimestre'     => 'nullable|string|max:50',

            'vih_confirmatoria_fecha'      => 'nullable|date',
            'vih_confirmatoria_trimestre'  => 'nullable|string|max:50',

            'sifilis_rapida1_fecha'    => 'nullable|date',
            'sifilis_rapida1_resultado'=> 'nullable|string|max:300',
            'sifilis_rapida1_trimestre'=> 'nullable|string|max:50',
            'sifilis_rapida2_fecha'    => 'nullable|date',
            'sifilis_rapida2_resultado'=> 'nullable|string|max:300',
            'sifilis_rapida2_trimestre'=> 'nullable|string|max:50',
            'sifilis_rapida3_fecha'    => 'nullable|date',
            'sifilis_rapida3_resultado'=> 'nullable|string|max:300',
            'sifilis_rapida3_trimestre'=> 'nullable|string|max:50',

            'sifilis_no_trep_fecha'    => 'nullable|date',
            'sifilis_no_trep_resultado'=> 'nullable|string|max:300',
            'sifilis_no_trep_trimestre'=> 'nullable|string|max:50',

            'urocultivo_fecha'         => 'nullable|date',
            'urocultivo_resultado'     => 'nullable|string|max:300',

            'glicemia_fecha'           => 'nullable|date',
            'glicemia_resultado'       => 'nullable|string|max:300',

            'pto_glucosa_fecha'        => 'nullable|date',
            'pto_glucosa_resultado'    => 'nullable|string|max:300',

            'hemoglobina_fecha'        => 'nullable|date',
            'hemoglobina_resultado'    => 'nullable|string|max:300',

            'hemoclasificacion_resultado' => 'nullable|string|max:300',

            'ag_hbs_fecha'             => 'nullable|date',
            'ag_hbs_resultado'         => 'nullable|string|max:300',

            'toxoplasma_fecha'         => 'nullable|date',
            'toxoplasma_resultado'     => 'nullable|string|max:300',

            'rubeola_fecha'            => 'nullable|date',
            'rubeola_resultado'        => 'nullable|string|max:300',

            'citologia_fecha'          => 'nullable|date',
            'citologia_resultado'      => 'nullable|string|max:300',

            'frotis_vaginal_fecha'     => 'nullable|date',
            'frotis_vaginal_resultado' => 'nullable|string|max:300',

            'estreptococo_fecha'       => 'nullable|date',
            'estreptococo_resultado'   => 'nullable|string|max:300',

            'malaria_fecha'            => 'nullable|date',
            'malaria_resultado'        => 'nullable|string|max:300',

            'chagas_fecha'             => 'nullable|date',
            'chagas_resultado'         => 'nullable|string|max:300',

            // Vacunas / controles / ecos / suministros
            'vac_influenza_fecha'      => 'nullable|date',
            'vac_toxoide_fecha'        => 'nullable|date',
            'vac_dpt_acelular_fecha'   => 'nullable|date',
            'consulta_odontologica_fecha' => 'nullable|date',

            'eco_translucencia'        => 'nullable|string|max:50',
            'eco_anomalias'            => 'nullable|string|max:50',
            'eco_otras'                => 'nullable|string|max:100',

            'suministro_acido_folico'  => 'nullable|string|max:10',
            'suministro_calcio'        => 'nullable|string|max:10',
            'suministro_hierro'        => 'nullable|string|max:10',
            'suministro_asa'           => 'nullable|string|max:10',

            'desparasitacion_fecha'    => 'nullable|date',
            'informacion_en_salud'     => 'nullable|string|max:10',

            // CPN
            'cpn1_fecha' => 'nullable|date', 'cpn1_quien' => 'nullable|string|max:100',
            'cpn2_fecha' => 'nullable|date', 'cpn2_quien' => 'nullable|string|max:100',
            'cpn3_fecha' => 'nullable|date', 'cpn3_quien' => 'nullable|string|max:100',
            'cpn4_fecha' => 'nullable|date', 'cpn4_quien' => 'nullable|string|max:100',
            'cpn5_fecha' => 'nullable|date', 'cpn5_quien' => 'nullable|string|max:100',
            'cpn6_fecha' => 'nullable|date', 'cpn6_quien' => 'nullable|string|max:100',
            'cpn7_fecha' => 'nullable|date', 'cpn7_quien' => 'nullable|string|max:100',
            'cpn8_fecha' => 'nullable|date', 'cpn8_quien' => 'nullable|string|max:100',
            'cpn9_fecha' => 'nullable|date', 'cpn9_quien' => 'nullable|string|max:100',
            'num_total_cpn' => 'nullable|integer',
            'ultimo_cpn'    => 'nullable|string|max:100',

            // Especialistas
            'cons_ginecologia_1'     => 'nullable|string|max:10',
            'cons_ginecologia_2'     => 'nullable|string|max:10',
            'cons_ginecologia_3'     => 'nullable|string|max:10',
            'cons_nutricion'         => 'nullable|string|max:10',
            'cons_psicologia'        => 'nullable|string|max:10',
            'cons_otro_especialista' => 'nullable|string|max:10',
            'cons_otro_quien'        => 'nullable|string|max:100',
            'especialistas_describe' => 'nullable|string|max:200',

            // Parto y RN
            'parto_tipo'              => 'nullable|string|max:50',
            'parto_sem_gest'          => 'nullable|string|max:50',
            'parto_complicaciones'    => 'nullable|string|max:200',
            'uci_materna'             => 'nullable|string|max:10',
            'its_intraparto_toma'     => 'nullable|string|max:10',
            'its_intraparto_positivo' => 'nullable|string|max:10',
            'defuncion_fecha'         => 'nullable|date',
            'defuncion_causa'         => 'nullable|string|max:200',
            'multiplicidad_embarazo'  => 'nullable|string|max:50',

            'rn1_registro_civil'      => 'nullable|string|max:10',
            'rn1_nombre'              => 'nullable|string|max:150',
            'rn1_sexo'                => 'nullable|string|max:10',
            'rn1_peso'                => 'nullable|numeric',
            'rn1_condicion'           => 'nullable|string|max:50',
            'rn1_tsh'                 => 'nullable|string|max:50',
            'rn1_hipotiroideo_dx'     => 'nullable|string|max:10',
            'rn1_trat_hipotiroideo'   => 'nullable|string|max:10',
            'rn1_uci'                 => 'nullable|string|max:10',
            'rn1_vac_bcg'             => 'nullable|string|max:10',
            'rn1_vac_hepb'            => 'nullable|string|max:10',

            'rn2_registro_civil'      => 'nullable|string|max:10',
            'rn2_nombre'              => 'nullable|string|max:150',
            'rn2_sexo'                => 'nullable|string|max:10',
            'rn2_peso'                => 'nullable|numeric',
            'rn2_condicion'           => 'nullable|string|max:50',
            'rn2_tsh'                 => 'nullable|string|max:50',
            'rn2_hipotiroideo_dx'     => 'nullable|string|max:10',
            'rn2_trat_hipotiroideo'   => 'nullable|string|max:10',
            'rn2_uci'                 => 'nullable|string|max:10',
            'rn2_vac_bcg'             => 'nullable|string|max:10',
            'rn2_vac_hepb'            => 'nullable|string|max:10',
        ];

        /**
         * Reglas de archivo para **todos** los campos *_resultado_file:
         * - Solo PDF
         * - Máx 20 MB
         */
        foreach ($this->resultadoFields() as $base) {
            $rules[$base . '_file'] = 'nullable|file|mimetypes:application/pdf|max:20480';
        }

        return $rules;
    }

    /**
     * Lista centralizada de campos "resultado" para los cuales habrá input de archivo *_resultado_file
     */
    private function resultadoFields(): array
    {
        return [
            'vih_tamiz1_resultado',
            'vih_tamiz2_resultado',
            'vih_tamiz3_resultado',
            'sifilis_rapida1_resultado',
            'sifilis_rapida2_resultado',
            'sifilis_rapida3_resultado',
            'sifilis_no_trep_resultado',
            'urocultivo_resultado',
            'glicemia_resultado',
            'pto_glucosa_resultado',
            'hemoglobina_resultado',
            'hemoclasificacion_resultado',
            'ag_hbs_resultado',
            'toxoplasma_resultado',
            'rubeola_resultado',
            'citologia_resultado',
            'frotis_vaginal_resultado',
            'estreptococo_resultado',
            'malaria_resultado',
            'chagas_resultado',
        ];
    }

    /**
     * Sube los PDFs venidos como *_resultado_file y pone la ruta en el campo *_resultado correspondiente.
     * Modifica el array $data por referencia.
     */
    private function ingestarArchivosResultado(Request $request, array &$data): void
    {
        foreach ($this->resultadoFields() as $base) {
            $fileKey = $base . '_file';
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('seguimientos', 'public'); // storage/app/public/seguimientos
                $data[$base] = $path; // guardamos la ruta en el campo original
            }
        }
    }


    public function verArchivo(GesTipo1Seguimiento $seg, string $field)
{
    // Lista blanca de campos que pueden tener PDF
    $permitidos = [
        'vih_tamiz1_resultado','vih_tamiz2_resultado','vih_tamiz3_resultado',
        'sifilis_rapida1_resultado','sifilis_rapida2_resultado','sifilis_rapida3_resultado',
        'sifilis_no_trep_resultado','urocultivo_resultado','glicemia_resultado',
        'pto_glucosa_resultado','hemoglobina_resultado','hemoclasificacion_resultado',
        'ag_hbs_resultado','toxoplasma_resultado','rubeola_resultado','citologia_resultado',
        'frotis_vaginal_resultado','estreptococo_resultado','malaria_resultado','chagas_resultado',
    ];

    if (!in_array($field, $permitidos, true)) {
        abort(404);
    }

    $value = $seg->getAttribute($field);
    if (!$value) {
        abort(404);
    }

    // Si guardaste sólo el filename, lo normalizamos a "seguimientos/<file>"
    $path = $value;
    if (!str_starts_with($path, 'seguimientos/')) {
        $path = 'seguimientos/' . ltrim($path, '/');
    }

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    // Devuelve inline (el navegador abre el PDF en pestaña), sin necesitar symlink
    return Storage::disk('public')->response($path);
}
}
