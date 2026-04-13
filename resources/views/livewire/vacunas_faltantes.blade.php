@extends('adminlte::page')

@section('title', 'PAI - Faltantes Normativos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="m-0">Vacunas Faltantes - Normativo Fase 2</h1>
        <small class="text-muted">Vista poblacional por afiliado y prestador.</small>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('afiliado') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Cargue PAI
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('vacunas.faltantes.normativo') }}" class="row">
                <div class="col-md-5">
                    <label class="small text-muted">Buscar afiliado</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Documento, carnet o nombre"
                    >
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Prestador</label>
                    <select name="prestador_id" class="form-control">
                        <option value="0">Todos</option>
                        @foreach($prestadores as $p)
                            <option value="{{ $p->id }}" {{ (int)($filters['prestador_id'] ?? 0) === (int)$p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Filas</label>
                    <select name="per_page" class="form-control">
                        @foreach([10,25,50,100] as $n)
                            <option value="{{ $n }}" {{ (int)($filters['per_page'] ?? 25) === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th>Afiliado</th>
                        <th>Edad</th>
                        <th>Documento</th>
                        <th>Prestador</th>
                        <th class="text-center">Faltantes</th>
                        <th>Resumen de faltantes (Normativo)</th>
                        <th style="width:120px;">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r['id'] }}</td>
                            <td>
                                <a href="#" data-toggle="collapse" data-target="#detail-{{ $r['id'] }}" aria-expanded="false" aria-controls="detail-{{ $r['id'] }}">
                                    {{ $r['nombre'] ?: 'Sin nombre' }}
                                </a>
                            </td>
                            <td>{{ $r['edad_texto'] ?? 'N/A' }}</td>
                            <td>
                                {{ trim(($r['tipo_identificacion'] ?? '').' '.($r['numero_identificacion'] ?? '')) }}
                                @if(!empty($r['numero_carnet']))
                                    <div class="text-muted small">Carnet: {{ $r['numero_carnet'] }}</div>
                                @endif
                            </td>
                            <td>{{ $r['prestador'] }}</td>
                            <td class="text-center">
                                @if(($r['faltantes_count'] ?? 0) > 0)
                                    <span class="badge badge-danger">{{ $r['faltantes_count'] }}</span>
                                @else
                                    <span class="badge badge-success">0</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($r['faltantes_text']))
                                    {{ implode(' | ', $r['faltantes_text']) }}
                                @else
                                    <span class="text-success">Sin faltantes en reglas normativas</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#detail-{{ $r['id'] }}" aria-expanded="false" aria-controls="detail-{{ $r['id'] }}">
                                    Ver
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse bg-light" id="detail-{{ $r['id'] }}">
                            <td colspan="8" class="p-0">
                                <div class="p-3">
                                    <h6 class="mb-2">Detalle normativo por vacuna faltante</h6>
                                    @if(!empty($r['faltantes_detail']))
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Regla</th>
                                                        <th class="text-center">Aplicadas</th>
                                                        <th class="text-center">Requeridas</th>
                                                        <th class="text-center">Faltan</th>
                                                        <th>Edad actual</th>
                                                        <th>Criterio etario</th>
                                                        <th>Motivo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($r['faltantes_detail'] as $d)
                                                        <tr>
                                                            <td>{{ $d['nombre'] }}</td>
                                                            <td class="text-center">{{ $d['aplicadas'] }}</td>
                                                            <td class="text-center">{{ $d['requeridas'] }}</td>
                                                            <td class="text-center"><span class="badge badge-danger">{{ $d['faltan'] }}</span></td>
                                                            <td>{{ $d['edad_actual'] }}</td>
                                                            <td>{{ $d['criterio_edad'] }}</td>
                                                            <td>{{ $d['motivo'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <small class="text-muted d-block mt-2">Base normativa: {{ $r['faltantes_detail'][0]['fuente'] ?? 'PAI Colombia 2026' }}</small>
                                    @else
                                        <div class="text-success">No registra faltantes para el esquema normativo vigente.</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Sin resultados para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $paginator->links() }}
        </div>
    </div>

    <div class="alert alert-info">
        <strong>Normativo fase 2:</strong> esta vista incluye reglas ampliadas (riesgo territorial, especiales y catch-up).
        Puedes ajustar municipios priorizados de dengue/fiebre amarilla desde variables de entorno.
    </div>
</div>
@stop
