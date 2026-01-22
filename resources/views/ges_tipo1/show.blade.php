{{-- resources/views/ges_tipo1/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalle Gestante')

@section('content_header')
    <h1 class="mb-0 text-primary">
        <i class="fas fa-user-circle mr-2"></i>Detalle de la Gestante
    </h1>
@stop

@section('content')

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Volver --}}
    <div class="mb-4">
        <a href="{{ route('ges_tipo1.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    {{-- Card: Datos Personales --}}
    <div class="card card-outline card-primary mb-4">
        <div class="card-header bg-white">
            <h3 class="card-title">
                <i class="fas fa-id-card-alt text-primary mr-2"></i>Datos Personales
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Tabla izquierda --}}
                <div class="col-md-6 mb-4">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-50">Nombre Completo</th>
                                <td>{{ trim("{$gestante->primer_nombre} {$gestante->segundo_nombre} {$gestante->primer_apellido} {$gestante->segundo_apellido}") }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tipo ID</th>
                                <td>{{ $gestante->tipo_de_identificacion_de_la_usuaria ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">No. ID Usuario</th>
                                <td>{{ $gestante->no_id_del_usuario ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Número Carnet</th>
                                <td>{{ $gestante->numero_carnet ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Fecha Nacimiento</th>
                                <td>{{ $gestante->fecha_de_nacimiento ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Probable Parto</th>
                                <td>{{ $gestante->fecha_probable_de_parto ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Tabla derecha --}}
                <div class="col-md-6 mb-4">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-50">Tipo de Registro</th>
                                <td>{{ $gestante->tipo_de_registro ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Consecutivo</th>
                                <td>{{ $gestante->consecutivo ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">País</th>
                                <td>{{ $gestante->pais_de_la_nacionalidad ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Municipio</th>
                                <td>{{ $gestante->municipio_de_residencia_habitual ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Zona Territorial</th>
                                <td>{{ $gestante->zona_territorial_de_residencia ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">IPS Primaria</th>
                                <td>{{ $gestante->codigo_de_habilitacion_ips_primaria_de_la_gestante ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                {{-- Segunda fila: más datos --}}
                <div class="col-md-6 mb-4">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-50">Étnica</th>
                                <td>{{ $gestante->codigo_pertenencia_etnica ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ocupación</th>
                                <td>{{ $gestante->codigo_de_ocupacion ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Nivel Educ.</th>
                                <td>{{ $gestante->codigo_nivel_educativo_de_la_gestante ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Dirección</th>
                                <td>{{ $gestante->direccion_de_residencia_de_la_gestante ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 mb-4">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-50">Tabaco</th>
                                <td>{{ $gestante->consumo_tabaco_durante_la_gestacion ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Embarazo Múltiple</th>
                                <td>{{ $gestante->embarazo_multiple ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Intergenésico</th>
                                <td>{{ $gestante->periodo_intergenesico ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Método Concepción</th>
                                <td>{{ $gestante->metodo_de_concepcion ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-right text-sm text-muted">
                Creado: {{ optional($gestante->created_at)->format('d/m/Y H:i') ?? '-' }} &middot;
                Actualizado: {{ optional($gestante->updated_at)->format('d/m/Y H:i') ?? '-' }}
            </div>
        </div>
    </div>

    {{-- =====================  SEGUIMIENTOS  ===================== --}}
    @php
        use Illuminate\Support\Str;

        // Colección de seguimientos (ordenada desc por id)
        $segs = method_exists($gestante, 'seguimientos')
            ? ($gestante->relationLoaded('seguimientos') ? $gestante->seguimientos : $gestante->seguimientos()->orderByDesc('id')->get())
            : collect();

        $ultimo = $segs->first();

        $gesKey = method_exists($gestante, 'getKey') ? $gestante->getKey() : ($gestante->id ?? null);

        // create
        try {
            $urlCreate = route('ges_tipo1.seguimientos.create', ['ges' => $gesKey]);
        } catch (\Throwable $e) {
            $urlCreate = $gesKey ? url("ges_tipo1/{$gesKey}/seguimientos/create") : '#';
        }

        // edit último
        try {
            $urlEditLast = $ultimo ? route('ges_tipo1.seguimientos.edit', ['ges' => $gesKey, 'seg' => $ultimo->id]) : '#';
        } catch (\Throwable $e) {
            $urlEditLast = ($ultimo && $gesKey) ? url("ges_tipo1/{$gesKey}/seguimientos/{$ultimo->id}/edit") : '#';
        }

        // ✅ URL pública del PDF (IGUAL que te funciona en el form)
        $pdfPublicUrl = function($value) {
            if (!is_string($value) || $value === '') return null;
            if (Str::startsWith($value, ['http://','https://'])) return $value;
            $path = ltrim($value, '/'); // ej: seguimientos/xxx.pdf
            return asset('storage/' . $path); // ej: http://localhost/Desnutricion/public/storage/seguimientos/xxx.pdf
        };

        $pdfFields = [
            'vih_tamiz1_resultado','vih_tamiz2_resultado','vih_tamiz3_resultado',
            'sifilis_rapida1_resultado','sifilis_rapida2_resultado','sifilis_rapida3_resultado',
            'sifilis_no_trep_resultado','urocultivo_resultado','glicemia_resultado',
            'pto_glucosa_resultado','hemoglobina_resultado','hemoclasificacion_resultado',
            'ag_hbs_resultado','toxoplasma_resultado','rubeola_resultado','citologia_resultado',
            'frotis_vaginal_resultado','estreptococo_resultado','malaria_resultado','chagas_resultado',
        ];
    @endphp

    <div class="card card-outline card-warning mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h3 class="card-title">
                <i class="fas fa-notes-medical text-warning mr-2"></i>Seguimientos
            </h3>
            <div>
                <a href="{{ $urlCreate }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus-circle mr-1"></i> Nuevo seguimiento
                </a>

                @if($ultimo)
                    <a href="{{ $urlEditLast }}" class="btn btn-sm btn-warning ml-2">
                        <i class="fas fa-edit mr-1"></i> Editar último
                    </a>
                @else
                    <button class="btn btn-sm btn-warning ml-2" disabled title="Sin seguimientos">
                        <i class="fas fa-edit mr-1"></i> Editar último
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body">
            @if($segs->isEmpty())
                <div class="alert alert-info mb-0">
                    No hay seguimientos registrados para esta gestante.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha seguimiento</th>
                                <th>Fecha contacto</th>
                                <th>Tipo contacto</th>
                                <th>Estado</th>
                                <th>Próximo contacto</th>
                                <th>Observaciones</th>
                                <th style="width:260px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($segs as $i => $seg)
                                @php
                                    // URL editar de cada fila
                                    try {
                                        $urlEdit = route('ges_tipo1.seguimientos.edit', ['ges' => $gesKey, 'seg' => $seg->id]);
                                    } catch (\Throwable $e) {
                                        $urlEdit = $gesKey ? url("ges_tipo1/{$gesKey}/seguimientos/{$seg->id}/edit") : '#';
                                    }

                                    $tipoContactoTxt = [
                                        '1' => 'Telefónico',
                                        '2' => 'Domiciliario',
                                        '3' => 'Otro',
                                    ][(string)($seg->tipo_contacto ?? '')] ?? '';

                                    // ✅ buscar el primer PDF real en esos campos
                                    $firstPdfUrl = null;
                                    $firstPdfField = null;
                                    foreach ($pdfFields as $f) {
                                        $v = $seg->getAttribute($f);
                                        if (is_string($v) && Str::endsWith(Str::lower($v), '.pdf')) {
                                            $firstPdfUrl = $pdfPublicUrl($v);
                                            $firstPdfField = $f;
                                            break;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $seg->fecha_seguimiento ?? '' }}</td>
                                    <td>{{ $seg->fecha_contacto ?? '' }}</td>
                                    <td>{{ $tipoContactoTxt }}</td>
                                    <td>{{ $seg->estado ?? '' }}</td>
                                    <td>{{ $seg->proximo_contacto ?? '' }}</td>
                                    <td class="text-truncate" style="max-width:260px">{{ $seg->observaciones ?? '' }}</td>
                                    <td>
                            <a href="{{ $urlEdit }}" class="btn btn-xs btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>

                            @php
                                // ====== RECOLECTAR TODOS LOS PDF DEL SEGUIMIENTO ======
                                $pdfs = []; // [ ['field'=>..., 'label'=>..., 'url'=>...], ... ]

                                // labels bonitos
                                $labels = [
                                    'vih_tamiz1_resultado' => 'VIH Tamiz 1',
                                    'vih_tamiz2_resultado' => 'VIH Tamiz 2',
                                    'vih_tamiz3_resultado' => 'VIH Tamiz 3',
                                    'sifilis_rapida1_resultado' => 'Sífilis rápida 1',
                                    'sifilis_rapida2_resultado' => 'Sífilis rápida 2',
                                    'sifilis_rapida3_resultado' => 'Sífilis rápida 3',
                                    'sifilis_no_trep_resultado' => 'Sífilis No Treponémica',
                                    'urocultivo_resultado' => 'Urocultivo',
                                    'glicemia_resultado' => 'Glicemia',
                                    'pto_glucosa_resultado' => 'PTO Glucosa',
                                    'hemoglobina_resultado' => 'Hemoglobina',
                                    'hemoclasificacion_resultado' => 'Hemoclasificación',
                                    'ag_hbs_resultado' => 'Ag HBs',
                                    'toxoplasma_resultado' => 'Toxoplasma',
                                    'rubeola_resultado' => 'Rubéola',
                                    'citologia_resultado' => 'Citología',
                                    'frotis_vaginal_resultado' => 'Frotis vaginal',
                                    'estreptococo_resultado' => 'Estreptococo',
                                    'malaria_resultado' => 'Malaria',
                                    'chagas_resultado' => 'Chagas',
                                ];

                                foreach ($pdfFields as $f) {
                                    $v = $seg->getAttribute($f);
                                    $isPdf = is_string($v) && Str::endsWith(Str::lower($v), '.pdf');

                                    if ($isPdf) {
                                        $url = $pdfPublicUrl($v); // helper que ya tienes arriba en el show
                                        if ($url) {
                                            $pdfs[] = [
                                                'field' => $f,
                                                'label' => $labels[$f] ?? $f,
                                                'url'   => $url,
                                            ];
                                        }
                                    }
                                }

                                $modalId = 'pdfModalSeg_' . $seg->id;
                            @endphp

                            @if(count($pdfs))
                                <button type="button"
                                        class="btn btn-xs btn-outline-primary mt-1"
                                        data-toggle="modal"
                                        data-target="#{{ $modalId }}">
                                    <i class="fas fa-file-pdf"></i> Ver PDFs ({{ count($pdfs) }})
                                </button>
                            @else
                                <span class="text-muted small d-block mt-1">Sin PDFs</span>
                            @endif

                            {{-- ========= MODAL: TODOS LOS PDF DEL SEGUIMIENTO ========= --}}
                            @if(count($pdfs))
                                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-file-pdf text-danger mr-1"></i>
                                                    PDFs del seguimiento #{{ $seg->id }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">

                                                <div class="alert alert-light border mb-3">
                                                    <div class="row">
                                                        <div class="col-md-4"><strong>Fecha seguimiento:</strong> {{ $seg->fecha_seguimiento ?? '-' }}</div>
                                                        <div class="col-md-4"><strong>Fecha contacto:</strong> {{ $seg->fecha_contacto ?? '-' }}</div>
                                                        <div class="col-md-4"><strong>Estado:</strong> {{ $seg->estado ?? '-' }}</div>
                                                    </div>
                                                </div>

                                                {{-- Lista de PDFs con acordeón --}}
                                                <div id="accordion-pdfs-{{ $seg->id }}">
                                                    @foreach($pdfs as $idx => $p)
                                                        @php
                                                            $collapseId = "collapsePdf_{$seg->id}_{$idx}";
                                                            $headingId  = "headingPdf_{$seg->id}_{$idx}";
                                                        @endphp

                                                        <div class="card mb-2">
                                                            <div class="card-header" id="{{ $headingId }}">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <button class="btn btn-link p-0"
                                                                            type="button"
                                                                            data-toggle="collapse"
                                                                            data-target="#{{ $collapseId }}"
                                                                            aria-expanded="{{ $idx===0 ? 'true' : 'false' }}"
                                                                            aria-controls="{{ $collapseId }}">
                                                                        <i class="fas fa-file-pdf text-danger mr-1"></i>
                                                                        <strong>{{ $p['label'] }}</strong>
                                                                        <span class="text-muted small">({{ $p['field'] }})</span>
                                                                    </button>

                                                                    <a href="{{ $p['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-external-link-alt"></i> Abrir
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div id="{{ $collapseId }}"
                                                                class="collapse {{ $idx===0 ? 'show' : '' }}"
                                                                aria-labelledby="{{ $headingId }}"
                                                                data-parent="#accordion-pdfs-{{ $seg->id }}">

                                                                <div class="card-body">
                                                                    <iframe src="{{ $p['url'] }}#toolbar=1"
                                                                            style="width:100%;height:75vh;border:0;"></iframe>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    <i class="fas fa-times"></i> Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Card: Antecedentes Médicos --}}
    <div class="card card-outline card-info mb-4">
        <div class="card-header bg-white">
            <h3 class="card-title">
                <i class="fas fa-notes-medical text-info mr-2"></i>Antecedentes Médicos
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach([
                    'Hipertensión'        => $gestante->antecedente_hipertension_cronica ?? '-',
                    'Preeclampsia'        => $gestante->antecedente_preeclampsia ?? '-',
                    'Diabetes'            => $gestante->antecedente_diabetes ?? '-',
                    'Autoinmune'          => $gestante->antecedente_les_enfermedad_autoinmune ?? '-',
                    'Síndrome Metabólico' => $gestante->antecedente_sindrome_metabolico ?? '-',
                    'ERC'                 => $gestante->antecedente_erc ?? '-',
                    'Trombofilia/TVP'     => $gestante->antecedente_trombofilia_o_trombosis_venosa_profunda ?? '-',
                    'Anemia Falciformes'  => $gestante->antecedentes_anemia_celulas_falciformes ?? '-',
                    'Sepsis Previas'      => $gestante->antecedente_sepsis_durante_gestaciones_previas ?? '-',
                ] as $label => $value)
                    <div class="col-md-4 mb-2">
                        <strong>{{ $label }}:</strong> {{ $value }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Card: Registros Tipo 3 --}}
    <div class="card card-outline card-success">
        <div class="card-header bg-white">
            <h3 class="card-title">
                <i class="fas fa-file-medical-alt text-success mr-2"></i>Registros Tipo 3 Asociados
            </h3>
        </div>
        <div class="card-body">
            @php
                $t3 = isset($tipo3)
                    ? ( $tipo3 instanceof \Illuminate\Support\Collection ? $tipo3 : collect($tipo3) )
                    : ( property_exists($gestante, 'tipo3')
                        ? ( $gestante->tipo3 instanceof \Illuminate\Support\Collection ? $gestante->tipo3 : collect($gestante->tipo3) )
                        : collect() );
            @endphp

            @if($t3->isEmpty())
                <div class="alert alert-warning mb-0">
                    No hay registros de Tipo 3 para esta gestante.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha Tec.</th>
                                <th>CUPS</th>
                                <th>Finalidad</th>
                                <th>Riesgo Gest.</th>
                                <th>Riesgo Pree.</th>
                                <th>ASA</th>
                                <th>Ác. Fólico</th>
                                <th>Ferroso</th>
                                <th>Calcio</th>
                                <th>Fecha Post</th>
                                <th>Met. Post</th>
                                <th>Salida</th>
                                <th>Term.</th>
                                <th>Tipo Term.</th>
                                <th>PAS</th>
                                <th>PAD</th>
                                <th>IMC</th>
                                <th>Hb</th>
                                <th>Índice Puls.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($t3 as $i => $reg)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $reg->fecha_tecnologia_en_salud ?? '' }}</td>
                                    <td>{{ $reg->codigo_cups_de_la_tecnologia_en_salud ?? '' }}</td>
                                    <td>{{ $reg->finalidad_de_la_tecnologia_en_salud ?? '' }}</td>
                                    <td>{{ $reg->clasificacion_riesgo_gestacional ?? '' }}</td>
                                    <td>{{ $reg->clasificacion_riesgo_preeclampsia ?? '' }}</td>
                                    <td>{{ (int)($reg->suministro_acido_acetilsalicilico_ASA ?? 0) }}</td>
                                    <td>{{ (int)($reg->suministro_acido_folico_en_el_control_prenatal ?? 0) }}</td>
                                    <td>{{ (int)($reg->suministro_sulfato_ferroso_en_el_control_prenatal ?? 0) }}</td>
                                    <td>{{ (int)($reg->suministro_calcio_en_el_control_prenatal ?? 0) }}</td>
                                    <td>{{ $reg->fecha_suministro_de_anticonceptivo_post_evento_obstetrico ?? '' }}</td>
                                    <td>{{ (int)($reg->suministro_metodo_anticonceptivo_post_evento_obstetrico ?? 0) }}</td>
                                    <td>{{ $reg->fecha_de_salida_de_aborto_o_atencion_del_parto_o_cesarea ?? '' }}</td>
                                    <td>{{ $reg->fecha_de_terminacion_de_la_gestacion ?? '' }}</td>
                                    <td>{{ $reg->tipo_de_terminacion_de_la_gestacion ?? '' }}</td>
                                    <td>{{ $reg->tension_arterial_sistolica_PAS_mmHg ?? '' }}</td>
                                    <td>{{ $reg->tension_arterial_diastolica_PAD_mmHg ?? '' }}</td>
                                    <td>{{ $reg->indice_de_masa_corporal ?? '' }}</td>
                                    <td>{{ $reg->resultado_de_la_hemoglobina ?? '' }}</td>
                                    <td>{{ $reg->indice_de_pulsatilidad_de_arterias_uterinas ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop
