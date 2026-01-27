@extends('adminlte::page')

@section('title', 'Alertas')

@section('content_header')
@php
    $estado = request('estado');
@endphp
<div class="d-flex flex-wrap align-items-center justify-content-between">
    <div class="mb-2 mb-md-0">
        <h1 class="m-0">
            <i class="fas fa-bell text-danger"></i>
            Alertas de resultados positivos/anormales
        </h1>
        <small class="text-muted">Gestiona alertas, visualiza PDFs y marca seguimiento.</small>
    </div>

    <form method="GET" class="form-inline">
        <div class="input-group">
            <select name="estado" class="form-control">
                <option value="">Todas</option>
                <option value="pendientes" {{ $estado==='pendientes'?'selected':'' }}>Pendientes</option>
                <option value="resueltas" {{ $estado==='resueltas'?'selected':'' }}>Resueltas</option>
            </select>
            <div class="input-group-append">
                <button class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@section('content')

@if(session('ok'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('ok') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- Resumen superior (simple y útil) --}}
@php
    $total = method_exists($alertas, 'total') ? $alertas->total() : count($alertas);
@endphp
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-layer-group"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total</span>
                <span class="info-box-number">{{ $total }}</span>
                <span class="text-muted" style="font-size:12px;">alertas encontradas</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pendientes</span>
                <span class="info-box-number">
                    {{ $alertas->whereNull('resolved_at')->count() }}
                </span>
                <span class="text-muted" style="font-size:12px;">en esta página</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Resueltas</span>
                <span class="info-box-number">
                    {{ $alertas->whereNotNull('resolved_at')->count() }}
                </span>
                <span class="text-muted" style="font-size:12px;">en esta página</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
@forelse($alertas as $a)
    @php
        $isResolved = !is_null($a->resolved_at);
        $isSeen = !is_null($a->seen_at);

        $sev = strtolower($a->severidad ?? '');
        $badge = $sev === 'alta' ? 'danger' : ($sev === 'media' ? 'warning' : 'info');

        // Card estilo: si está resuelta -> gris, si no -> según severidad
        $cardTheme = $isResolved ? 'secondary' : ($badge === 'danger' ? 'danger' : ($badge === 'warning' ? 'warning' : 'info'));

        $pdfExists = !empty($a->pdf_path);

        $g = $a->gestante; // viene por with('gestante') en el controlador

        $pacienteNombre = $g
            ? trim(($g->primer_nombre ?? '').' '.($g->segundo_nombre ?? '').' '.($g->primer_apellido ?? '').' '.($g->segundo_apellido ?? ''))
            : 'N/D';

        $tipoDoc = $g->tipo_de_identificacion_de_la_usuaria ?? '';
        $numDoc  = $g->no_id_del_usuario ?? '';
        $pacienteDocumento = trim($tipoDoc.' '.$numDoc) ?: 'N/D';

        // Header dinámico: si NO vista, resaltamos; si vista, más sobrio
        $headerClass = $isSeen ? '' : 'bg-light';
    @endphp

    <div class="col-lg-6">
        <div class="card card-outline card-{{ $cardTheme }} shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header d-flex justify-content-between align-items-start {{ $headerClass }}" style="border-top-left-radius:12px;border-top-right-radius:12px;">
                <div>
                    <div class="d-flex align-items-center flex-wrap">
                        <h5 class="mb-0 mr-2">
                            <i class="fas fa-vial"></i> {{ $a->examen }}
                        </h5>

                        <span class="badge badge-{{ $badge }} mr-2" style="font-size:12px;">
                            {{ strtoupper($a->resultado) }}
                        </span>

                        @if(!$isSeen)
                            <span class="badge badge-primary mr-2" style="font-size:12px;">
                                <i class="fas fa-bolt"></i> NUEVA
                            </span>
                        @else
                            <span class="badge badge-success mr-2" style="font-size:12px;">
                                <i class="fas fa-eye"></i> VISTA
                            </span>
                        @endif

                        @if($isResolved)
                            <span class="badge badge-secondary" style="font-size:12px;">
                                <i class="fas fa-check"></i> RESUELTA
                            </span>
                        @endif
                    </div>

                    <div class="mt-2 text-muted" style="font-size:13px;">
                        <i class="fas fa-user"></i>
                        <strong class="text-dark">{{ $pacienteNombre }}</strong>
                        <span class="mx-2">•</span>
                        <i class="fas fa-id-card"></i>
                        <span>{{ $pacienteDocumento }}</span>
                    </div>
                </div>

                <div class="text-right">
                    <small class="text-muted d-block">
                        <i class="far fa-clock"></i> {{ $a->created_at->format('Y-m-d H:i') }}
                    </small>
                    <small class="text-muted">
                        Severidad: <strong class="text-{{ $badge }}">{{ strtoupper($sev ?: 'N/D') }}</strong>
                    </small>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="p-3" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;">
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <div class="text-muted" style="font-size:12px;">Módulo</div>
                                    <div class="font-weight-bold">{{ $a->modulo }}</div>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <div class="text-muted" style="font-size:12px;">Campo</div>
                                    <div class="font-weight-bold">{{ $a->campo }}</div>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <div class="text-muted" style="font-size:12px;">Gestante Tipo1 ID</div>
                                    <div class="font-weight-bold">{{ $a->ges_tipo1_id }}</div>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <div class="text-muted" style="font-size:12px;">Seguimiento ID</div>
                                    <div class="font-weight-bold">{{ $a->seguimiento_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap align-items-center">
                    @if($pdfExists)
                        <button class="btn btn-outline-primary btn-sm mr-2 mb-2" data-toggle="modal" data-target="#pdfModal{{ $a->id }}">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </button>
                        <a href="{{ route('alertas.pdf', $a->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                            <i class="fas fa-external-link-alt"></i> Abrir PDF
                        </a>
                    @else
                        <span class="text-muted small mb-2">
                            <i class="far fa-file"></i> No hay PDF asociado.
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center" style="border-bottom-left-radius:12px;border-bottom-right-radius:12px;">
                <div class="d-flex flex-wrap align-items-center">
                    {{-- ✅ Marcar vista: al hacerlo se verá el badge "VISTA" y la fecha en la derecha --}}
                    <form action="{{ route('alertas.seen', $a->id) }}" method="POST" class="d-inline mr-2 mb-2">
                        @csrf
                        <button class="btn btn-sm {{ $isSeen ? 'btn-success' : 'btn-outline-dark' }}" {{ $isSeen ? 'disabled' : '' }}>
                            <i class="fas fa-eye"></i> {{ $isSeen ? 'Vista' : 'Marcar vista' }}
                        </button>
                    </form>

                    <form action="{{ route('alertas.resolve', $a->id) }}" method="POST" class="d-inline mb-2">
                        @csrf
                        <button class="btn btn-sm {{ $isResolved ? 'btn-secondary' : 'btn-success' }}" {{ $isResolved ? 'disabled' : '' }}>
                            <i class="fas fa-check"></i> {{ $isResolved ? 'Resuelta' : 'Resolver' }}
                        </button>
                    </form>
                </div>

                <div class="text-muted small text-right">
                    @if($isSeen)
                        <div><i class="fas fa-eye"></i> Vista: {{ $a->seen_at->format('Y-m-d H:i') }}</div>
                    @else
                        <div><i class="fas fa-eye-slash"></i> Aún no vista</div>
                    @endif

                    @if($isResolved)
                        <div><i class="fas fa-check-circle"></i> Resuelta: {{ $a->resolved_at->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PDF --}}
    @if($pdfExists)
        <div class="modal fade" id="pdfModal{{ $a->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content" style="border-radius:12px;overflow:hidden;">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">
                            <i class="fas fa-file-pdf"></i>
                            PDF - {{ $a->examen }} ({{ strtoupper($a->resultado) }})
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-0">
                        <iframe src="{{ route('alertas.pdf', $a->id) }}#toolbar=1"
                                style="width:100%;height:80vh;border:0;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

@empty
    <div class="col-12">
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle"></i> No hay alertas registradas.
        </div>
    </div>
@endforelse
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $alertas->withQueryString()->links() }}
</div>

@stop
