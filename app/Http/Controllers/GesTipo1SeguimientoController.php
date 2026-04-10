<?php

namespace App\Http\Controllers;

use App\Models\GesTipo1;
use App\Models\GesTipo1Seguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// ✅ ALERTAS
use App\Services\GestantesAlertService;

class GesTipo1SeguimientoController extends Controller
{
    public function create(GesTipo1 $ges)
    {
        $ultimo = $ges->seguimientos()->reorder()->latest('id')->first();
        $prefillDemografia = $this->buildDemografiaPrefill($ges);

        return view('ges_tipo1.seguimientos.create', compact('ges', 'ultimo', 'prefillDemografia'));
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
        $prefillDemografia = [];
        return view('ges_tipo1.seguimientos.edit', compact('ges', 'seg', 'prefillDemografia'));
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

    private function buildDemografiaPrefill(GesTipo1 $ges): array
    {
        $documento = trim((string) ($ges->no_id_del_usuario ?? ''));
        $tipoDocumento = trim((string) ($ges->tipo_de_identificacion_de_la_usuaria ?? ''));

        $sgaAfiliado = $this->fetchSgaAfiliado($documento);
        $afiliado = null;
        if ($documento !== '') {
            $afiliado = DB::table('afiliados')
                ->when($tipoDocumento !== '', function ($q) use ($tipoDocumento) {
                    $q->where('tipo_identificacion', $tipoDocumento);
                })
                ->where('numero_identificacion', $documento)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        }

        $fechaNacimiento = $this->formatDateForInput(
            $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['fechaNacimiento', 'fecha_nacimiento', 'fecNacimiento', 'fec_nacimiento']),
                $afiliado->fecha_nacimiento ?? null,
                $ges->fecha_de_nacimiento ?? null
            )
        );

        $edadAnios = $this->firstFilled(
            $this->pickValue($sgaAfiliado, ['edad', 'edadAnos', 'edad_anos']),
            $afiliado->edad_anos ?? null
        );
        if (($edadAnios === null || $edadAnios === '') && $fechaNacimiento) {
            $edadAnios = Carbon::parse($fechaNacimiento)->age;
        }

        $codigoAgente = (string) $this->firstFilled(
            $this->pickValue($sgaAfiliado, ['codigoAgente', 'codigo_agente']),
            ''
        );

        $regimenDesdeCodigo = $this->mapRegimenByCodigoAgente($codigoAgente);
        $regimenDesdeSga = $this->pickValue($sgaAfiliado, ['regimen', 'tipoAfiliacion', 'tipo_afiliacion']);
        $tipoDocSga = $this->pickValue($sgaAfiliado, ['tipoIdentificacion', 'tipo_identificacion']);
        $docSga = $this->pickValue($sgaAfiliado, ['identificacion', 'numeroIdentificacion', 'numero_identificacion']);
        $telefonoSga = $this->firstFilled(
            $this->pickValue($sgaAfiliado, ['celular']),
            $this->pickValue($sgaAfiliado, ['telefono', 'telefonoFijo', 'telefono_fijo']),
            null
        );
        $zonaSga = $this->pickValue($sgaAfiliado, ['zona', 'area']);
        $ipsPrimariaSga = $this->fetchSgaIpsPrimariaByCarnet(
            (string) $this->pickValue($sgaAfiliado, ['numeroCarnet', 'numero_carnet'])
        );
        $residenciaSga = $this->fetchSgaResidenciaByDocumento($documento);
        $codigoDepartamento = $this->firstFilled(
            $this->pickValue($sgaAfiliado, ['codigoDepartamento', 'cod_dpto_o']),
            null
        );
        $codigoMunicipio = $this->firstFilled(
            $this->pickValue($sgaAfiliado, ['codigoMunicipio', 'cod_mun_o']),
            null
        );

        $codigoMunicipioPlano = trim((string) $this->firstFilled(
            $afiliado->municipio_residencia ?? null,
            $ges->municipio_de_residencia_habitual ?? null
        ));
        if (
            ($codigoDepartamento === null || $codigoDepartamento === '') &&
            ($codigoMunicipio === null || $codigoMunicipio === '') &&
            preg_match('/^\d{5}$/', $codigoMunicipioPlano)
        ) {
            $codigoDepartamento = substr($codigoMunicipioPlano, 0, 2);
            $codigoMunicipio = substr($codigoMunicipioPlano, 2, 3);
        }

        $residenciaPorCodigo = $this->fetchSgaResidenciaByCodes($codigoDepartamento, $codigoMunicipio);
        $departamentoNombre = $this->firstFilled(
            $residenciaSga['departamento'] ?? null,
            $residenciaPorCodigo['departamento'] ?? null
        );
        $municipioNombre = $this->firstFilled(
            $residenciaSga['municipio'] ?? null,
            $residenciaPorCodigo['municipio'] ?? null
        );

        return [
            'tipo_documento' => $this->firstFilled($tipoDocSga, $afiliado->tipo_identificacion ?? null, $tipoDocumento),
            'numero_identificacion' => $this->firstFilled($docSga, $afiliado->numero_identificacion ?? null, $documento),
            'apellido_1' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['primerApellido', 'primer_apellido', 'pri_ape_', 'priApe']),
                $afiliado->primer_apellido ?? null,
                $ges->primer_apellido ?? null
            ),
            'apellido_2' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['segundoApellido', 'segundo_apellido', 'seg_ape_', 'segApe']),
                $afiliado->segundo_apellido ?? null,
                $ges->segundo_apellido ?? null
            ),
            'nombre_1' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['primerNombre', 'primer_nombre', 'pri_nom_', 'priNom']),
                $afiliado->primer_nombre ?? null,
                $ges->primer_nombre ?? null
            ),
            'nombre_2' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['segundoNombre', 'segundo_nombre', 'seg_nom_', 'segNom']),
                $afiliado->segundo_nombre ?? null,
                $ges->segundo_nombre ?? null
            ),
            'fecha_nacimiento' => $fechaNacimiento,
            'edad_anios' => $edadAnios,
            'sexo' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['genero', 'sexo']),
                $afiliado->sexo ?? null
            ),
            'regimen_afiliacion' => $this->firstFilled($regimenDesdeCodigo, $regimenDesdeSga, $afiliado->regimen ?? null),
            'pertenencia_etnica' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['pertenenciaEtnica', 'pertenencia_etnica', 'etnia']),
                $afiliado->pertenencia_etnica ?? null,
                $ges->codigo_pertenencia_etnica ?? null
            ),
            'grupo_poblacional' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['grupoPoblacional', 'grupo_poblacional', 'condicionUsuaria', 'condicion_usuaria']),
                $afiliado->condicion_usuaria ?? null
            ),
            'departamento_residencia' => $this->firstFilled(
                $departamentoNombre,
                $this->pickValue($sgaAfiliado, ['departamentoResidencia', 'departamento_residencia']),
                $afiliado->departamento_residencia ?? null
            ),
            'municipio_residencia' => $this->firstFilled(
                $municipioNombre,
                $this->pickValue($sgaAfiliado, ['municipioResidencia', 'municipio_residencia']),
                $afiliado->municipio_residencia ?? null,
                $ges->municipio_de_residencia_habitual ?? null
            ),
            'zona' => $this->firstFilled($zonaSga, $afiliado->area ?? null, $ges->zona_territorial_de_residencia ?? null),
            'etnia' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['etnia', 'pertenenciaEtnica', 'pertenencia_etnica']),
                $afiliado->pertenencia_etnica ?? null
            ),
            'asentamiento' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['asentamiento', 'comuna']),
                $afiliado->comuna ?? null
            ),
            'telefono_usuaria' => $this->firstFilled($telefonoSga, $afiliado->celular ?? null, $afiliado->telefono_fijo ?? null),
            'direccion' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['direccion', 'direccionResidencia', 'direccion_residencia']),
                $afiliado->direccion ?? null,
                $ges->direccion_de_residencia_de_la_gestante ?? null
            ),
            'nivel_educativo' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['nivelEducativo', 'nivel_educativo']),
                $ges->codigo_nivel_educativo_de_la_gestante ?? null
            ),
            'discapacidad' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['discapacidad', 'discapacitado']),
                $afiliado->discapacitado ?? null
            ),
            'mujer_cabeza_hogar' => null,
            'ocupacion' => $this->firstFilled(
                $this->pickValue($sgaAfiliado, ['ocupacion', 'codigoOcupacion', 'codigo_ocupacion']),
                $ges->codigo_de_ocupacion ?? null
            ),
            'estado_civil' => null,
            'control_tradicional' => null,
            'gestante_renuente' => null,
            'inasistente' => null,
            'ips_primaria' => $this->firstFilled(
                $ipsPrimariaSga,
                $this->pickValue($sgaAfiliado, ['ipsPrimaria', 'ips_primaria']),
                $ges->codigo_de_habilitacion_ips_primaria_de_la_gestante ?? null
            ),
        ];
    }

    private function formatDateForInput($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchSgaAfiliado(string $documento): ?object
    {
        if ($documento === '') {
            return null;
        }

        try {
            return DB::connection('sqlsrv_1')
                ->table('maestroAfiliados as a')
                ->select('a.*')
                ->where('a.identificacion', $documento)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('No se pudo consultar SGA maestroafiliados para prefill de seguimiento.', [
                'documento' => $documento,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function fetchSgaIpsPrimariaByCarnet(string $numeroCarnet): ?string
    {
        if ($numeroCarnet === '') {
            return null;
        }

        try {
            return DB::connection('sqlsrv_1')
                ->table('maestroips as b')
                ->join('maestroIpsGru as c', 'b.idGrupoIps', '=', 'c.id')
                ->where('b.numeroCarnet', $numeroCarnet)
                ->value('c.descrip');
        } catch (\Throwable $e) {
            Log::warning('No se pudo consultar IPS primaria en SGA para prefill de seguimiento.', [
                'numero_carnet' => $numeroCarnet,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function fetchSgaResidenciaByDocumento(string $documento): array
    {
        if ($documento === '') {
            return ['departamento' => null, 'municipio' => null];
        }

        try {
            // Intento principal: SQL exacto solicitado por negocio
            $directo = DB::connection('sqlsrv_1')
                ->table(DB::raw('sga..maestroafiliados A'))
                ->leftJoin(DB::raw('sga..municipios B'), function ($join) {
                    $join->on('A.codigoDepartamento', '=', 'B.codigoDepartamento')
                        ->on('A.codigoMunicipio', '=', 'B.codigoMunicipio');
                })
                ->leftJoin(DB::raw('sga..departamentos C'), 'A.codigoDepartamento', '=', 'C.codigo')
                ->selectRaw('A.numeroCarnet, C.descrip as nombreDepartamento, B.descrip as nombreMunicipio')
                ->where('A.identificacion', $documento)
                ->first();

            $depDirecto = trim((string) ($directo->nombreDepartamento ?? ''));
            $munDirecto = trim((string) ($directo->nombreMunicipio ?? ''));
            if ($depDirecto !== '' || $munDirecto !== '') {
                return [
                    'departamento' => $depDirecto !== '' ? $depDirecto : null,
                    'municipio' => $munDirecto !== '' ? $munDirecto : null,
                ];
            }

            $row = DB::connection('sqlsrv_1')
                ->table('maestroafiliados as A')
                ->select('A.numeroCarnet', 'A.codigoDepartamento', 'A.codigoMunicipio')
                ->where('A.identificacion', $documento)
                ->first();

            $municipioDirecto = null;
            try {
                $municipioDirecto = DB::connection('sqlsrv_1')
                    ->table('maestroafiliados as A')
                    ->leftJoin('municipios as B', function ($join) {
                        $join->on('A.codigoDepartamento', '=', 'B.codigoDepartamento')
                            ->on('A.codigoMunicipio', '=', 'B.codigoMunicipio');
                    })
                    ->where('A.identificacion', $documento)
                    ->value(DB::raw('B.descrip'));
            } catch (\Throwable $e) {
                // continua a estrategia por codigo
            }

            $dep = trim((string) ($row->codigoDepartamento ?? ''));
            $mun = trim((string) ($row->codigoMunicipio ?? ''));

            if (($dep === '' || $mun === '') && preg_match('/^\d{5}$/', $mun)) {
                $dep = substr($mun, 0, 2);
                $mun = substr($mun, 2, 3);
            }

            $resuelta = $this->fetchSgaResidenciaByCodes($dep, $mun);
            if (trim((string) $municipioDirecto) !== '' && empty($resuelta['municipio'])) {
                $resuelta['municipio'] = $municipioDirecto;
            }

            return $resuelta;
        } catch (\Throwable $e) {
            Log::warning('No se pudo consultar municipio/departamento en SGA para prefill de seguimiento.', [
                'documento' => $documento,
                'error' => $e->getMessage(),
            ]);

            return ['departamento' => null, 'municipio' => null];
        }
    }

    private function fetchSgaResidenciaByCodes($codigoDepartamento, $codigoMunicipio): array
    {
        $dep = trim((string) ($codigoDepartamento ?? ''));
        $mun = trim((string) ($codigoMunicipio ?? ''));
        if ($dep === '' || $mun === '') {
            return ['departamento' => null, 'municipio' => null];
        }

        $municipio = null;
        foreach (['[sga].[dbo].[municipios] as B', 'municipios as B'] as $municipiosTable) {
            try {
                $municipio = DB::connection('sqlsrv_1')
                    ->table(DB::raw($municipiosTable))
                    ->whereRaw("RIGHT('00' + CAST(B.codigoDepartamento AS VARCHAR(2)), 2) = RIGHT('00' + ?, 2)", [$dep])
                    ->whereRaw("RIGHT('000' + CAST(B.codigoMunicipio AS VARCHAR(3)), 3) = RIGHT('000' + ?, 3)", [$mun])
                    ->value(DB::raw('B.descrip'));

                if (trim((string) $municipio) !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                // sigue con siguiente estrategia
            }
        }

        $departamento = null;
        foreach (['[sga].[dbo].[departamentos] as D', 'departamentos as D'] as $departamentosTable) {
            try {
                $departamento = DB::connection('sqlsrv_1')
                    ->table(DB::raw($departamentosTable))
                    ->where(function ($q) use ($dep) {
                        $q->whereRaw("RIGHT('00' + CAST(D.codigoDepartamento AS VARCHAR(2)), 2) = RIGHT('00' + ?, 2)", [$dep])
                          ->orWhereRaw("RIGHT('00' + CAST(D.codigo AS VARCHAR(2)), 2) = RIGHT('00' + ?, 2)", [$dep]);
                    })
                    ->value(DB::raw('D.descrip'));

                if (trim((string) $departamento) !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                // sigue con siguiente estrategia
            }
        }

        if (trim((string) $municipio) === '' && trim((string) $departamento) === '') {
            Log::warning('No se pudo resolver municipio/departamento SGA por codigo para prefill de seguimiento.', [
                'codigo_departamento' => $dep,
                'codigo_municipio' => $mun,
            ]);
        }

        return [
            'departamento' => trim((string) $departamento) !== '' ? $departamento : null,
            'municipio' => trim((string) $municipio) !== '' ? $municipio : null,
        ];
    }

    private function mapRegimenByCodigoAgente(?string $codigoAgente): ?string
    {
        $codigo = strtoupper(trim((string) $codigoAgente));
        if ($codigo === '') {
            return null;
        }

        if (str_ends_with($codigo, 'C4')) {
            return 'CONTRIBUTIVO';
        }

        if (str_ends_with($codigo, '04')) {
            return 'SUBSIDIADO';
        }

        return null;
    }

    private function pickValue($row, array $keys)
    {
        if (!$row) {
            return null;
        }

        $normalized = [];
        foreach ((array) $row as $k => $v) {
            $normalized[strtolower((string) $k)] = $v;
        }

        foreach ($keys as $k) {
            $lk = strtolower((string) $k);
            if (!array_key_exists($lk, $normalized)) {
                continue;
            }

            $v = $normalized[$lk];
            if ($v !== null && trim((string) $v) !== '') {
                return $v;
            }
        }

        return null;
    }

    private function firstFilled(...$values)
    {
        foreach ($values as $v) {
            if ($v === null) {
                continue;
            }

            if (!is_string($v)) {
                return $v;
            }

            if (trim($v) !== '') {
                return $v;
            }
        }

        return null;
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

            // =========================
            // ✅ POSTPARTO - MENOR
            // =========================
            'pp_menor_tipo_documento' => 'nullable|string|max:10',
            'pp_menor_numero_identificacion' => 'nullable|string|max:50',
            'pp_menor_apellido_1' => 'nullable|string|max:100',
            'pp_menor_apellido_2' => 'nullable|string|max:100',
            'pp_menor_nombre_1' => 'nullable|string|max:100',
            'pp_menor_nombre_2' => 'nullable|string|max:100',
            'pp_menor_fecha_nacimiento' => 'nullable',
            'pp_menor_edad' => 'nullable|integer',

            'pp_menor_temperatura' => 'nullable|numeric',
            'pp_menor_frecuencia_cardiaca' => 'nullable|integer',
            'pp_menor_frecuencia_respiratoria' => 'nullable|integer',
            'pp_menor_peso' => 'nullable|numeric',
            'pp_menor_talla' => 'nullable|numeric',
            'pp_menor_imc' => 'nullable|numeric',
            'pp_menor_perimetro_cefalico' => 'nullable|numeric',
            'pp_menor_examen_fisico' => 'nullable|string',

            // Alimentación / lactancia
            'pp_tipo_alimentacion' => 'nullable|string|max:100',
            'pp_educacion_tecnica_agarre' => 'nullable|string|max:10',

            // Vacunación
            'pp_vac_bcg' => 'nullable|string|max:10',
            'pp_vac_hepatitis_b' => 'nullable|string|max:10',

            // Alarmas
            'pp_alarma_fiebre' => 'nullable|string|max:10',
            'pp_alarma_dificultad_respiratoria' => 'nullable|string|max:10',
            'pp_alarma_vomitos' => 'nullable|string|max:10',
            'pp_alarma_alteraciones_ombligo' => 'nullable|string|max:10',
            'pp_clasificacion_riesgo_menor' => 'nullable|string|max:100',

            // Registro
            'pp_programacion_proximos_controles' => 'nullable|string',

            // =========================
            // ✅ POSTPARTO - MADRE
            // =========================
            'pp_madre_presion_arterial' => 'nullable|string|max:20',
            'pp_madre_frecuencia_cardiaca' => 'nullable|integer',
            'pp_madre_frecuencia_respiratoria' => 'nullable|integer',
            'pp_madre_temperatura' => 'nullable|numeric',
            'pp_madre_examen_fisico' => 'nullable|string',

            // Salud mental
            'pp_tamizaje_depresion_posparto' => 'nullable|string|max:150',
            'pp_evaluacion_ansiedad_estres' => 'nullable|string',
            'pp_redes_apoyo' => 'nullable|string',

            // Nutrición / recuperación
            'pp_entrega_hierro' => 'nullable|string|max:10',
            'pp_entrega_acido_folico' => 'nullable|string|max:10',
            'pp_entrega_calcio' => 'nullable|string|max:10',

            // Medicación
            'pp_revision_medicamentos' => 'nullable|string',
            'pp_educacion_uso_medicamentos' => 'nullable|string',

            // Planificación familiar
            'pp_consejeria_metodos_anticonceptivos' => 'nullable|string',
            'pp_fecha_colocacion_metodo' => 'nullable',
            'pp_metodo' => 'nullable|string|max:150',
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

            // ✅ POSTPARTO FECHAS
            'pp_menor_fecha_nacimiento',
            'pp_fecha_colocacion_metodo',
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
