@extends('adminlte::page')

@section('title', 'Expediente Integral de Gestante')

@section('content_header')
    <div class="expediente-toolbar d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-1 text-primary">
                <i class="fas fa-notes-medical mr-2"></i>Expediente Integral de Gestante
            </h1>
            <small class="text-muted">Historia consolidada desde todos los modulos disponibles del sistema</small>
        </div>
        <div class="mt-2 mt-md-0 no-print">
            <a href="{{ route('ges_tipo1.index') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
            <button type="button" class="btn btn-outline-danger mr-2" data-toggle="modal" data-target="#qrPrintModal">
                <i class="fas fa-qrcode mr-1"></i> Imprimir QR
            </button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Imprimir expediente
            </button>
        </div>
    </div>
@stop

@section('content')
@php
    $paciente = $expediente['paciente'] ?? [];
    $resumen = $expediente['resumen'] ?? [];
    $qr = $expediente['qr'] ?? [];
    $pdfPublicUrl = function($value) {
        if (!is_string($value) || trim($value) === '') return null;
        if (\Illuminate\Support\Str::startsWith($value, ['http://', 'https://'])) return $value;
        return asset('storage/' . ltrim($value, '/'));
    };
    $pdfFields = [
        'vih_tamiz1_resultado','vih_tamiz2_resultado','vih_tamiz3_resultado',
        'sifilis_rapida1_resultado','sifilis_rapida2_resultado','sifilis_rapida3_resultado',
        'sifilis_no_trep_resultado','urocultivo_resultado','glicemia_resultado',
        'pto_glucosa_resultado','hemoglobina_resultado','hemoclasificacion_resultado',
        'ag_hbs_resultado','toxoplasma_resultado','rubeola_resultado','citologia_resultado',
        'frotis_vaginal_resultado','estreptococo_resultado','malaria_resultado','chagas_resultado',
    ];
    $pdfLabels = [
        'vih_tamiz1_resultado' => 'VIH Tamiz 1',
        'vih_tamiz2_resultado' => 'VIH Tamiz 2',
        'vih_tamiz3_resultado' => 'VIH Tamiz 3',
        'sifilis_rapida1_resultado' => 'Sifilis rapida 1',
        'sifilis_rapida2_resultado' => 'Sifilis rapida 2',
        'sifilis_rapida3_resultado' => 'Sifilis rapida 3',
        'sifilis_no_trep_resultado' => 'Sifilis No Treponemica',
        'urocultivo_resultado' => 'Urocultivo',
        'glicemia_resultado' => 'Glicemia',
        'pto_glucosa_resultado' => 'PTO Glucosa',
        'hemoglobina_resultado' => 'Hemoglobina',
        'hemoclasificacion_resultado' => 'Hemoclasificacion',
        'ag_hbs_resultado' => 'Ag HBs',
        'toxoplasma_resultado' => 'Toxoplasma',
        'rubeola_resultado' => 'Rubeola',
        'citologia_resultado' => 'Citologia',
        'frotis_vaginal_resultado' => 'Frotis vaginal',
        'estreptococo_resultado' => 'Estreptococo',
        'malaria_resultado' => 'Malaria',
        'chagas_resultado' => 'Chagas',
    ];
    $renderValue = function ($value) {
        $value = trim((string) $value);
        return $value !== '' ? $value : 'Sin dato';
    };
@endphp

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="expediente-shell">
    <section class="expediente-hero card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="expediente-logo-wrap mr-3 mb-3 mb-md-0">
                            <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="expediente-logo">
                        </div>
                        <div>
                            <div class="expediente-kicker">Empresa Promotora de Salud</div>
                            <h2 class="expediente-name mb-1">{{ $renderValue($paciente['nombre'] ?? '') }}</h2>
                            <div class="expediente-meta">
                                <span><strong>Documento:</strong> {{ $renderValue($paciente['tipo_documento'] ?? '') }} {{ $renderValue($paciente['documento'] ?? '') }}</span>
                                <span><strong>F. Nacimiento:</strong> {{ $renderValue($paciente['fecha_nacimiento'] ?? '') }}</span>
                                <span><strong>FPP:</strong> {{ $renderValue($paciente['fpp'] ?? '') }}</span>
                            </div>
                            <p class="expediente-description mb-0">
                                Informe detallado y completo de la gestante, consolidado automaticamente desde Tipo 2, Tipo 3,
                                seguimientos, alertas, preconcepcional, Sivigila y SIV 549.
                            </p>
                            <div class="hero-pills mt-3">
                                <span class="hero-pill hero-pill-primary"><i class="fas fa-laptop-medical mr-1"></i> Vista operativa</span>
                                <span class="hero-pill"><i class="fas fa-mobile-alt mr-1"></i> Responsive</span>
                                <span class="hero-pill"><i class="fas fa-shield-alt mr-1"></i> Historial institucional</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="qr-panel">
                        <div class="qr-title">Codigo QR para pulsera e impresion</div>
                        <div id="gestanteQrCode" class="qr-code-box"></div>
                        <div class="qr-caption">{{ $renderValue($qr['caption'] ?? '') }}</div>
                        <div class="qr-target">{{ $renderValue($paciente['documento'] ?? '') }}</div>
                        <div class="qr-helper">Este QR abre la vista pulsera segura</div>
                    </div>
                </div>
            </div>
            <div class="expediente-generated">
                Generado por el sistema: {{ $renderValue($expediente['generado'] ?? '') }}
            </div>
        </div>
    </section>

    <section class="expediente-nav no-print">
        <a href="#ficha-principal" class="expediente-nav-link"><i class="fas fa-id-card-alt mr-1"></i> Ficha</a>
        <a href="#seguimientos" class="expediente-nav-link"><i class="fas fa-notes-medical mr-1"></i> Seguimientos</a>
        @if(!empty($expediente['tipo3']))
            <a href="#tipo3" class="expediente-nav-link"><i class="fas fa-file-medical-alt mr-1"></i> Tipo 3</a>
        @endif
        @if(!empty($expediente['alertas']))
            <a href="#alertas" class="expediente-nav-link"><i class="fas fa-bell mr-1"></i> Alertas</a>
        @endif
        @if(!empty($expediente['preconcepcional']))
            <a href="#preconcepcional" class="expediente-nav-link"><i class="fas fa-seedling mr-1"></i> Preconcepcional</a>
        @endif
        @if(!empty($expediente['sivigila']))
            <a href="#sivigila" class="expediente-nav-link"><i class="fas fa-clipboard-list mr-1"></i> Sivigila</a>
        @endif
        @if(!empty($expediente['maestro549']) || !empty($expediente['asignaciones549']))
            <a href="#siv549" class="expediente-nav-link"><i class="fas fa-hospital-user mr-1"></i> SIV 549</a>
        @endif
    </section>

    <section class="summary-grid">
        @foreach($resumen as $item)
            <article class="summary-card">
                <div class="summary-label">{{ $item['label'] ?? 'Indicador' }}</div>
                <div class="summary-value">{{ $item['value'] ?? 0 }}</div>
            </article>
        @endforeach
    </section>

    <section class="module-card card shadow-sm" id="ficha-principal">
        <div class="card-header bg-white">
            <h3 class="card-title mb-0"><i class="fas fa-id-card-alt mr-2 text-primary"></i>Ficha principal de la gestante</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach(($expediente['gestanteFicha'] ?? []) as $card)
                    <div class="col-lg-6 mb-4">
                        <div class="data-panel h-100">
                            <h4>{{ $card['title'] ?? 'Datos' }}</h4>
                            <div class="data-grid">
                                @foreach(($card['values'] ?? []) as $value)
                                    <div class="data-item">
                                        <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                        <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="module-card card shadow-sm" id="seguimientos">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-notes-medical mr-2 text-warning"></i>Seguimientos de gestante</h3>
            <span class="badge badge-warning">{{ $segsCount }} registros</span>
        </div>
        <div class="card-body">
            @if($segs->isEmpty())
                <div class="alert alert-info mb-0">No hay seguimientos registrados para esta gestante.</div>
            @else
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover table-sm expediente-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha seguimiento</th>
                                <th>Fecha contacto</th>
                                <th>Tipo contacto</th>
                                <th>Estado</th>
                                <th>Proximo contacto</th>
                                <th>Observaciones</th>
                                <th class="no-print">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($segs as $i => $seg)
                                @php
                                    $tipoContactoTxt = [
                                        '1' => 'Telefonico',
                                        '2' => 'Domiciliario',
                                        '3' => 'Otro',
                                    ][(string)($seg->tipo_contacto ?? '')] ?? $renderValue($seg->tipo_contacto ?? '');

                                    $segPdfs = [];
                                    foreach ($pdfFields as $field) {
                                        $value = $seg->getAttribute($field);
                                        if (is_string($value) && \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($value), '.pdf')) {
                                            $url = $pdfPublicUrl($value);
                                            if ($url) {
                                                $segPdfs[] = [
                                                    'field' => $field,
                                                    'label' => $pdfLabels[$field] ?? $field,
                                                    'url' => $url,
                                                ];
                                            }
                                        }
                                    }
                                    $collapseId = 'seguimientoDetalle_' . $seg->id;
                                    $modalId = 'pdfModalSeg_' . $seg->id;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $renderValue($seg->fecha_seguimiento ?? '') }}</td>
                                    <td>{{ $renderValue($seg->fecha_contacto ?? '') }}</td>
                                    <td>{{ $tipoContactoTxt }}</td>
                                    <td>{{ $renderValue($seg->estado ?? '') }}</td>
                                    <td>{{ $renderValue($seg->proximo_contacto ?? '') }}</td>
                                    <td>{{ $renderValue($seg->observaciones ?? '') }}</td>
                                    <td class="no-print">
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <i class="fas fa-eye mr-1"></i> Ver detalle
                                        </button>
                                        @if(count($segPdfs))
                                            <button type="button" class="btn btn-sm btn-outline-danger mb-1" data-toggle="modal" data-target="#{{ $modalId }}">
                                                <i class="fas fa-file-pdf mr-1"></i> PDFs ({{ count($segPdfs) }})
                                            </button>
                                        @else
                                            <span class="text-muted small d-block">Sin PDFs</span>
                                        @endif

                                        @if(count($segPdfs))
                                            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-xl" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-file-pdf text-danger mr-1"></i> PDFs del seguimiento #{{ $seg->id }}
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-light border mb-3">
                                                                <div class="row">
                                                                    <div class="col-md-3"><strong>Fecha seguimiento:</strong> {{ $renderValue($seg->fecha_seguimiento ?? '') }}</div>
                                                                    <div class="col-md-3"><strong>Fecha contacto:</strong> {{ $renderValue($seg->fecha_contacto ?? '') }}</div>
                                                                    <div class="col-md-3"><strong>Estado:</strong> {{ $renderValue($seg->estado ?? '') }}</div>
                                                                    <div class="col-md-3"><strong>Tipo contacto:</strong> {{ $tipoContactoTxt }}</div>
                                                                </div>
                                                            </div>
                                                            <div id="accordion-pdfs-{{ $seg->id }}">
                                                                @foreach($segPdfs as $idx => $pdf)
                                                                    @php
                                                                        $headingId = 'headingPdf_' . $seg->id . '_' . $idx;
                                                                        $collapsePdfId = 'collapsePdf_' . $seg->id . '_' . $idx;
                                                                    @endphp
                                                                    <div class="card mb-2">
                                                                        <div class="card-header" id="{{ $headingId }}">
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <button class="btn btn-link p-0 text-left" type="button" data-toggle="collapse" data-target="#{{ $collapsePdfId }}" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="{{ $collapsePdfId }}">
                                                                                    <i class="fas fa-file-pdf text-danger mr-1"></i>
                                                                                    <strong>{{ $pdf['label'] }}</strong>
                                                                                </button>
                                                                                <a href="{{ $pdf['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="fas fa-external-link-alt mr-1"></i> Abrir
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div id="{{ $collapsePdfId }}" class="collapse {{ $idx === 0 ? 'show' : '' }}" aria-labelledby="{{ $headingId }}" data-parent="#accordion-pdfs-{{ $seg->id }}">
                                                                            <div class="card-body p-2">
                                                                                <iframe src="{{ $pdf['url'] }}#toolbar=1" style="width:100%;height:75vh;border:0;"></iframe>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="collapse-row">
                                    <td colspan="8" class="p-0 border-0">
                                        <div id="{{ $collapseId }}" class="collapse seguimiento-collapse">
                                            @php
                                                $record = ($expediente['seguimientos'][$i] ?? null);
                                            @endphp
                                            @if($record)
                                                <div class="record-card record-card-inline">
                                                    <div class="record-head">
                                                        <div>
                                                            <h4>{{ $record['title'] ?? 'Seguimiento' }}</h4>
                                                            <p>{{ $record['subtitle'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="data-grid">
                                                        @foreach(($record['values'] ?? []) as $value)
                                                            <div class="data-item">
                                                                <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                                                <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    @if(!empty($expediente['tipo3']))
        <section class="module-card card shadow-sm" id="tipo3">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0"><i class="fas fa-file-medical-alt mr-2 text-success"></i>Registros Tipo 3</h3>
            </div>
            <div class="card-body">
                @foreach($expediente['tipo3'] as $record)
                    <div class="record-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Tipo 3' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($expediente['alertas']))
        <section class="module-card card shadow-sm" id="alertas">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0"><i class="fas fa-bell mr-2 text-danger"></i>Alertas generadas</h3>
            </div>
            <div class="card-body">
                @foreach($expediente['alertas'] as $record)
                    <div class="record-card compact-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Alerta' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($expediente['preconcepcional']))
        <section class="module-card card shadow-sm" id="preconcepcional">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0"><i class="fas fa-seedling mr-2 text-info"></i>Historial preconcepcional</h3>
            </div>
            <div class="card-body">
                @foreach($expediente['preconcepcional'] as $record)
                    <div class="record-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Preconcepcional' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($expediente['sivigila']))
        <section class="module-card card shadow-sm" id="sivigila">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0"><i class="fas fa-clipboard-list mr-2 text-secondary"></i>Eventos en Sivigila</h3>
            </div>
            <div class="card-body">
                @foreach($expediente['sivigila'] as $record)
                    <div class="record-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Sivigila' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($expediente['maestro549']) || !empty($expediente['asignaciones549']))
        <section class="module-card card shadow-sm" id="siv549">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0"><i class="fas fa-hospital-user mr-2 text-dark"></i>Casos y seguimientos SIV 549</h3>
            </div>
            <div class="card-body">
                @foreach(($expediente['maestro549'] ?? []) as $record)
                    <div class="record-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Caso SIV 549' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @foreach(($expediente['asignaciones549'] ?? []) as $record)
                    <div class="record-card highlight-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Asignacion SIV 549' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid mb-3">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>

                        @if(!empty($record['seguimientos']))
                            <div class="nested-title">Seguimientos asociados</div>
                            @foreach($record['seguimientos'] as $seguimiento549)
                                <div class="record-card nested-record">
                                    <div class="record-head">
                                        <div>
                                            <h4>{{ $seguimiento549['title'] ?? 'Seguimiento' }}</h4>
                                            <p>{{ $seguimiento549['subtitle'] ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div class="data-grid">
                                        @foreach(($seguimiento549['values'] ?? []) as $value)
                                            <div class="data-item">
                                                <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                                <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($expediente['otrosTipo2']))
        <section class="module-card card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0"><i class="fas fa-copy mr-2 text-muted"></i>Otros registros Tipo 2 encontrados</h3>
            </div>
            <div class="card-body">
                @foreach($expediente['otrosTipo2'] as $record)
                    <div class="record-card compact-card">
                        <div class="record-head">
                            <div>
                                <h4>{{ $record['title'] ?? 'Registro adicional' }}</h4>
                                <p>{{ $record['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="data-grid">
                            @foreach(($record['values'] ?? []) as $value)
                                <div class="data-item">
                                    <div class="data-label">{{ $value['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($value['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<div class="modal fade no-print" id="qrPrintModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-qrcode mr-2 text-danger"></i>Imprimir QR para pulsera</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="qrSizePreset"><strong>Tamano del QR</strong></label>
                            <select id="qrSizePreset" class="form-control">
                                <option value="25">25 mm - Pulsera pequena</option>
                                <option value="30" selected>30 mm - Pulsera estandar</option>
                                <option value="35">35 mm - Pulsera amplia</option>
                                <option value="40">40 mm - Sticker grande</option>
                                <option value="50">50 mm - Tarjeta o marbete</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="qrLabelMode"><strong>Texto inferior</strong></label>
                            <select id="qrLabelMode" class="form-control">
                                <option value="doc" selected>Documento</option>
                                <option value="name">Nombre corto</option>
                                <option value="both">Nombre y documento</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="qrCutGuide"><strong>Formato de impresion</strong></label>
                            <select id="qrCutGuide" class="form-control">
                                <option value="bracelet" selected>Etiqueta para pulsera</option>
                                <option value="square">Solo QR cuadrado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="qr-print-preview">
                            <div class="qr-print-preview-head">Vista previa</div>
                            <div id="qrPrintPreviewCard" class="qr-badge qr-badge-bracelet" style="--qr-size: 30mm;">
                                <div class="qr-badge-brand">
                                    <img src="{{ asset('img/logo.png') }}" alt="Logo">
                                    <span>EPSI ANAS WAYUU</span>
                                </div>
                                <div id="qrPrintPreview" class="qr-badge-code"></div>
                                <div id="qrPrintPreviewLabel" class="qr-badge-label">{{ $renderValue($paciente['documento'] ?? '') }}</div>
                            </div>
                            <small class="text-muted d-block mt-3">
                                Usa esta opcion para imprimir solo el QR y adaptarlo fisicamente a la pulsera.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" id="btnPrintQrOnly">
                    <i class="fas fa-print mr-1"></i> Imprimir solo QR
                </button>
            </div>
        </div>
    </div>
</div>

<div id="qrPrintOnlyArea" class="qr-print-sheet">
    <div id="qrPrintCard" class="qr-badge qr-badge-bracelet" style="--qr-size: 30mm;">
        <div class="qr-badge-brand">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
            <span>EPSI ANAS WAYUU</span>
        </div>
        <div id="qrPrintOnlyCode" class="qr-badge-code"></div>
        <div id="qrPrintOnlyLabel" class="qr-badge-label">{{ $renderValue($paciente['documento'] ?? '') }}</div>
    </div>
</div>
@stop

@section('css')
<style>
    html {
        scroll-behavior: smooth;
    }

    .content-wrapper {
        background:
            linear-gradient(180deg, #f4f7fb 0%, #f8fafc 42%, #ffffff 100%);
    }

    body.print-qr-only .wrapper,
    body.print-qr-only .main-header,
    body.print-qr-only .main-sidebar,
    body.print-qr-only .main-footer,
    body.print-qr-only .content-header,
    body.print-qr-only .content,
    body.print-qr-only .content-wrapper > *:not(.content),
    body.print-qr-only .expediente-shell {
        display: none !important;
    }

    body.print-qr-only .content-wrapper,
    body.print-qr-only .content {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .expediente-shell {
        display: grid;
        gap: 1.5rem;
        padding-bottom: 2rem;
    }

    .expediente-hero {
        border: 0;
        background:
            radial-gradient(circle at top right, rgba(205, 160, 82, 0.16), transparent 30%),
            linear-gradient(135deg, #f8fbff 0%, #ffffff 50%, #fff5e6 100%);
        overflow: hidden;
    }

    .expediente-logo-wrap {
        width: 92px;
        height: 92px;
        border-radius: 24px;
        background: linear-gradient(145deg, #ffffff, #f3efe3);
        box-shadow: 0 18px 36px rgba(31, 45, 61, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .expediente-logo {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .expediente-kicker {
        text-transform: uppercase;
        letter-spacing: 0.22em;
        font-size: 0.74rem;
        color: #9b6a16;
        margin-bottom: 0.45rem;
        font-weight: 700;
    }

    .expediente-name {
        font-size: 2rem;
        color: #16324f;
        font-weight: 800;
    }

    .expediente-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.4rem;
        color: #425466;
        margin-bottom: 0.75rem;
    }

    .expediente-description {
        color: #546273;
        max-width: 900px;
    }

    .hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .hero-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.48rem 0.82rem;
        border-radius: 999px;
        background: rgba(22, 50, 79, 0.07);
        color: #26435d;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .hero-pill-primary {
        background: rgba(155, 106, 22, 0.16);
        color: #8b5b10;
    }

    .expediente-generated {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(22, 50, 79, 0.09);
        color: #6b7785;
        font-size: 0.92rem;
    }

    .qr-panel {
        background: #fff;
        border: 1px solid rgba(22, 50, 79, 0.08);
        border-radius: 20px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 12px 30px rgba(25, 36, 48, 0.08);
    }

    .qr-title {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #7c8a99;
        margin-bottom: 0.8rem;
        font-weight: 700;
    }

    .qr-code-box {
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-caption {
        font-weight: 700;
        color: #1f2d3d;
        margin-top: 0.6rem;
    }

    .qr-target {
        font-size: 0.85rem;
        color: #6b7785;
        margin-top: 0.35rem;
        word-break: break-all;
    }

    .qr-helper {
        margin-top: 0.55rem;
        font-size: 0.8rem;
        color: #8b97a5;
    }

    .qr-print-preview {
        background: #f8fafc;
        border: 1px solid #e3ebf3;
        border-radius: 22px;
        padding: 1rem;
        min-height: 100%;
    }

    .qr-print-preview-head {
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.72rem;
        color: #7e8a96;
        font-weight: 800;
        margin-bottom: 0.9rem;
    }

    .qr-badge {
        width: fit-content;
        min-width: calc(var(--qr-size) + 22mm);
        background: #fff;
        border: 1px dashed #b9c6d2;
        border-radius: 16px;
        padding: 4mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2.6mm;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        margin: 0 auto;
    }

    .qr-badge-bracelet {
        min-width: calc(var(--qr-size) + 28mm);
    }

    .qr-badge-square {
        min-width: calc(var(--qr-size) + 8mm);
    }

    .qr-badge-brand {
        display: flex;
        align-items: center;
        gap: 2mm;
        font-size: 2.8mm;
        font-weight: 800;
        color: #16324f;
        letter-spacing: 0.06em;
    }

    .qr-badge-brand img {
        width: 7mm;
        height: 7mm;
        object-fit: contain;
    }

    .qr-badge-code {
        width: var(--qr-size);
        height: var(--qr-size);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .qr-badge-label {
        text-align: center;
        font-size: 2.9mm;
        font-weight: 700;
        color: #354657;
        line-height: 1.2;
        max-width: calc(var(--qr-size) + 16mm);
        word-break: break-word;
    }

    .qr-print-sheet {
        display: none;
    }

    body.print-qr-only .qr-print-sheet {
        min-height: 100vh;
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: #fff;
    }

    .expediente-nav {
        position: sticky;
        top: 0.75rem;
        z-index: 50;
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.85rem;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        backdrop-filter: blur(10px);
    }

    .expediente-nav-link {
        display: inline-flex;
        align-items: center;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        background: #f4f7fb;
        color: #27435b;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid #e0e8f0;
        transition: all 0.2s ease;
    }

    .expediente-nav-link:hover {
        text-decoration: none;
        color: #16324f;
        background: #fff7eb;
        border-color: #f0d6a7;
        transform: translateY(-1px);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.9rem;
    }

    .summary-card {
        background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
        border-radius: 18px;
        padding: 1rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
        border: 1px solid rgba(15, 23, 42, 0.05);
    }

    .summary-label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #748091;
        margin-bottom: 0.35rem;
        font-weight: 700;
    }

    .summary-value {
        font-size: 2rem;
        line-height: 1;
        font-weight: 800;
        color: #16324f;
    }

    .module-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        scroll-margin-top: 5rem;
    }

    .module-card .card-header {
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        background:
            linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(249,251,253,1) 100%);
        padding: 1rem 1.2rem;
    }

    .data-panel,
    .record-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        padding: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        break-inside: avoid;
    }

    .record-card + .record-card {
        margin-top: 1rem;
    }

    .record-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        padding-bottom: 0.7rem;
    }

    .record-head h4,
    .data-panel h4 {
        margin: 0;
        font-size: 1rem;
        color: #15334b;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .record-head p {
        margin: 0.3rem 0 0;
        color: #6b7785;
        font-size: 0.92rem;
    }

    .data-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.8rem;
    }

    .data-item {
        background: #f8fafc;
        border: 1px solid #e6edf4;
        border-radius: 14px;
        padding: 0.85rem;
        min-height: 88px;
    }

    .data-label {
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #7b8794;
        margin-bottom: 0.35rem;
    }

    .data-value {
        color: #1f2d3d;
        font-weight: 600;
        word-break: break-word;
    }

    .expediente-table thead th {
        background: #15334b;
        color: #fff;
        border-color: #15334b;
        white-space: nowrap;
        vertical-align: middle;
    }

    .expediente-table tbody td {
        vertical-align: middle;
    }

    .highlight-card {
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }

    .seguimiento-collapse {
        padding: 0 0 1rem;
    }

    .record-card-inline {
        margin-top: 0.85rem;
    }

    .collapse-row td {
        background: transparent !important;
    }

    .compact-card .data-item {
        background: #fcfcfd;
    }

    .nested-title {
        font-weight: 800;
        color: #15334b;
        margin: 0.5rem 0 0.9rem;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
    }

    .nested-record {
        background: #fbfcfe;
        border-style: dashed;
    }

    @media (max-width: 767.98px) {
        .content-header {
            padding-bottom: 0.5rem;
        }

        .expediente-name {
            font-size: 1.5rem;
        }

        .expediente-nav {
            top: 0.35rem;
            padding: 0.7rem;
            overflow-x: auto;
            flex-wrap: nowrap;
        }

        .expediente-nav-link {
            white-space: nowrap;
            font-size: 0.84rem;
        }

        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .data-grid {
            grid-template-columns: 1fr;
        }

        .expediente-table {
            min-width: 980px;
        }

        .qr-panel {
            margin-top: 1rem;
        }
    }

    @media (min-width: 992px) {
        .expediente-shell {
            gap: 1.25rem;
        }

        .record-card {
            padding: 1.15rem;
        }
    }

    @media print {
        .no-print,
        .main-header,
        .main-sidebar,
        .main-footer,
        .content-header .breadcrumb {
            display: none !important;
        }

        .content-wrapper,
        .content,
        .content-header {
            margin: 0 !important;
            padding: 0 !important;
        }

        .card,
        .record-card,
        .data-panel,
        .summary-card {
            box-shadow: none !important;
        }

        .module-card,
        .record-card,
        .summary-card,
        .data-panel {
            break-inside: avoid;
        }

        .expediente-shell {
            gap: 1rem;
        }

        body {
            background: #fff !important;
        }

        body.print-qr-only .qr-print-sheet {
            display: flex !important;
        }

        body.print-qr-only #qrPrintCard {
            box-shadow: none !important;
            border-style: solid;
            border-color: #777;
        }
    }
</style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            var target = @json($expediente['qr']['payload'] ?? '');
            var qrElement = document.getElementById('gestanteQrCode');
            var previewElement = document.getElementById('qrPrintPreview');
            var printElement = document.getElementById('qrPrintOnlyCode');
            var sizePreset = document.getElementById('qrSizePreset');
            var labelMode = document.getElementById('qrLabelMode');
            var cutGuide = document.getElementById('qrCutGuide');
            var previewCard = document.getElementById('qrPrintPreviewCard');
            var printCard = document.getElementById('qrPrintCard');
            var previewLabel = document.getElementById('qrPrintPreviewLabel');
            var printLabel = document.getElementById('qrPrintOnlyLabel');
            var printButton = document.getElementById('btnPrintQrOnly');
            var patientName = @json($paciente['nombre'] ?? '');
            var patientDoc = @json($paciente['documento'] ?? '');

            if (!qrElement || !target || typeof QRCode === 'undefined') {
                if (qrElement && target) {
                    qrElement.innerHTML = '<div class="text-muted small">No fue posible generar el QR.</div>';
                }
                return;
            }

            function buildQr(el, sizePx) {
                if (!el) return;
                el.innerHTML = '';
                new QRCode(el, {
                    text: target,
                    width: sizePx,
                    height: sizePx,
                    colorDark: '#0f1720',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }

            function buildLabel() {
                var mode = labelMode ? labelMode.value : 'doc';
                if (mode === 'name') return patientName || patientDoc;
                if (mode === 'both') return (patientName ? patientName + ' - ' : '') + patientDoc;
                return patientDoc || patientName;
            }

            function applyQrPrintSettings() {
                var sizeMm = sizePreset ? sizePreset.value : '30';
                var sizePx = Math.round(parseInt(sizeMm, 10) * 3.78);
                var label = buildLabel();
                var mode = cutGuide ? cutGuide.value : 'bracelet';

                if (previewCard) {
                    previewCard.style.setProperty('--qr-size', sizeMm + 'mm');
                    previewCard.classList.toggle('qr-badge-bracelet', mode === 'bracelet');
                    previewCard.classList.toggle('qr-badge-square', mode === 'square');
                }

                if (printCard) {
                    printCard.style.setProperty('--qr-size', sizeMm + 'mm');
                    printCard.classList.toggle('qr-badge-bracelet', mode === 'bracelet');
                    printCard.classList.toggle('qr-badge-square', mode === 'square');
                }

                if (previewLabel) previewLabel.textContent = label;
                if (printLabel) printLabel.textContent = label;

                buildQr(previewElement, sizePx);
                buildQr(printElement, sizePx);
            }

            buildQr(qrElement, 168);
            applyQrPrintSettings();

            [sizePreset, labelMode, cutGuide].forEach(function (control) {
                if (!control) return;
                control.addEventListener('change', applyQrPrintSettings);
            });

            if (printButton) {
                printButton.addEventListener('click', function () {
                    applyQrPrintSettings();
                    document.body.classList.add('print-qr-only');
                    window.print();
                });
            }

            window.addEventListener('afterprint', function () {
                document.body.classList.remove('print-qr-only');
            });
        })();
    </script>
@stop
