@extends('adminlte::page')

@section('title', 'Detalle Revision')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    :root {
        --rvd-primary: #0f6f7e;
        --rvd-secondary: #119199;
        --rvd-accent: #17a75f;
        --rvd-border: #d8eaee;
        --rvd-text: #163c44;
    }

    .rvd-page { padding: 1.05rem 0 1.4rem; }

    .rvd-hero {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        border-radius:20px;
        color:#fff;
        padding:1.2rem 1.35rem;
        margin-bottom:1rem;
        background:
            radial-gradient(circle at 85% 18%, rgba(255,255,255,.22), transparent 35%),
            linear-gradient(130deg, #0f6f7e, #119199 52%, #17a75f);
        box-shadow:0 16px 32px rgba(11, 70, 80, .24);
    }

    .rvd-brand { display:flex; align-items:center; gap:.9rem; }
    .rvd-logo-wrap { width:72px; height:72px; border-radius:18px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.24); }
    .rvd-logo { width:46px; height:auto; object-fit:contain; }

    .rvd-tag {
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        padding:.36rem .68rem;
        font-size:.74rem;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        background: rgba(255,255,255,.16);
        border:1px solid rgba(255,255,255,.25);
    }

    .rvd-card {
        border:1px solid var(--rvd-border);
        border-radius:18px;
        background:#fff;
        overflow:hidden;
        box-shadow:0 14px 28px rgba(16, 68, 80, .1);
        margin-bottom:.95rem;
    }

    .rvd-card__head {
        padding:.75rem 1rem;
        border-bottom:1px solid #e1edf0;
        background:#f2fafb;
        font-weight:800;
        color:#2b5961;
    }

    .rvd-grid {
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:.8rem;
        padding:1rem;
    }

    .rvd-item {
        border:1px solid #e2edf0;
        border-radius:12px;
        padding:.62rem .68rem;
        background:#fbfeff;
    }

    .rvd-item__label { display:block; font-size:.75rem; color:#648289; text-transform:uppercase; letter-spacing:.04em; font-weight:800; margin-bottom:.22rem; }
    .rvd-item__value { display:block; color:#1b4750; font-size:.9rem; font-weight:700; line-height:1.35; word-break:break-word; }

    .rvd-table-wrap { overflow-x:auto; }
    .rvd-table { width:100%; margin:0; }

    .rvd-table thead th {
        border-top:none;
        border-bottom:1px solid #e1edf0;
        background:#eef8fa;
        color:#456a72;
        font-size:.77rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.03em;
        white-space:nowrap;
    }

    .rvd-table tbody td {
        color:#264c55;
        font-size:.84rem;
        border-color:#e8f0f2;
        vertical-align:middle;
        white-space:nowrap;
    }

    .rvd-actions {
        display:flex;
        justify-content:center;
        gap:.65rem;
        flex-wrap:wrap;
        margin-top:.2rem;
        padding:0 1rem 1rem;
    }

    .rvd-btn {
        border:none;
        border-radius:11px;
        min-height:42px;
        padding:.6rem 1rem;
        font-weight:700;
    }

    .rvd-btn--primary { color:#fff; background:linear-gradient(135deg, #0f7f8e, #179f59); }
    .rvd-btn--light { color:#2a5a63; background:#e9f6f8; border:1px solid #cbe5e9; text-decoration:none; display:inline-flex; align-items:center; }
    .rvd-btn--done { color:#166534; background:#dcfce7; border:1px solid #bbf7d0; }

    @media (max-width: 1200px) { .rvd-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 768px) {
        .rvd-hero { flex-direction:column; }
        .rvd-grid { grid-template-columns:1fr; }
        .rvd-actions { flex-direction:column; }
        .rvd-btn, .rvd-btn--light { width:100%; justify-content:center; }
    }
</style>
@stop

@section('content')
@php
    $formatPeso = function ($value) {
        if ($value === null || $value === '') {
            return 'N/D';
        }

        if (!is_numeric($value)) {
            return $value;
        }

        return number_format((float) $value, 1, '.', '');
    };

    $formatPuntajeZ = function ($value) {
        if ($value === null || $value === '') {
            return 'N/D';
        }

        if (!is_numeric($value)) {
            return $value;
        }

        return number_format((float) $value, 2, '.', '');
    };

    $formatTalla = function ($value) {
        if ($value === null || $value === '') {
            return 'N/D';
        }

        if (!is_numeric($value)) {
            return $value;
        }

        return number_format((float) $value, 1, '.', '');
    };
@endphp
<div class="container-fluid rvd-page">
    <section class="rvd-hero">
        <div class="rvd-brand">
            <div class="rvd-logo-wrap"><img src="{{ asset('img/logo.png') }}" alt="Escudo" class="rvd-logo"></div>
            <div>
                <h1 class="h4 mb-1" style="font-weight:800;">Detalle de Revision</h1>
                <p class="mb-0" style="color:rgba(255,255,255,.92);">Informacion ampliada del seguimiento seleccionado para auditoria clinica.</p>
            </div>
        </div>
        <span class="rvd-tag">Modulo {{ $modulo === '412' ? '412' : '113' }}</span>
    </section>

    @if($latest)
        <section class="rvd-card">
            <div class="rvd-card__head"><i class="fas fa-user-injured mr-1"></i> Resumen del seguimiento seleccionado</div>
            <div class="rvd-grid">
                <div class="rvd-item"><span class="rvd-item__label">Seguimiento ID</span><span class="rvd-item__value">{{ $latest->id }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Identificacion</span><span class="rvd-item__value">{{ $latest->num_ide_ }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Paciente</span><span class="rvd-item__value">{{ trim(($latest->pri_nom_ ?? '').' '.($latest->seg_nom_ ?? '').' '.($latest->pri_ape_ ?? '').' '.($latest->seg_ape_ ?? '')) }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Fecha consulta</span><span class="rvd-item__value">{{ $latest->fecha_consulta }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Peso (kg)</span><span class="rvd-item__value">{{ $formatPeso($latest->peso_kilos) }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Talla (cm)</span><span class="rvd-item__value">{{ $formatTalla($latest->talla_cm) }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Puntaje Z</span><span class="rvd-item__value">{{ $formatPuntajeZ($latest->puntajez) }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Clasificacion</span><span class="rvd-item__value">{{ $latest->clasificacion }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Perimetro braquial</span><span class="rvd-item__value">{{ $latest->perimetro_braqueal }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Energia FTLC</span><span class="rvd-item__value">{{ $latest->requerimiento_energia_ftlc }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Medicamentos</span><span class="rvd-item__value">{{ $latest->medicamento }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Observaciones</span><span class="rvd-item__value">{{ $latest->observaciones }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Estado menor</span><span class="rvd-item__value">{{ $latest->est_act_menor }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Proximo control</span><span class="rvd-item__value">{{ $latest->fecha_proximo_control }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Esquema PAI</span><span class="rvd-item__value">{{ $latest->Esquemq_complrto_pai_edad }}</span></div>
                <div class="rvd-item"><span class="rvd-item__label">Promocion y mantenimiento</span><span class="rvd-item__value">{{ $latest->Atecion_primocion_y_mantenimiento_res3280_2018 }}</span></div>
                @if($modulo === '113')
                    <div class="rvd-item"><span class="rvd-item__label">Fecha entrega FTLC</span><span class="rvd-item__value">{{ $latest->fecha_entrega_ftlc }}</span></div>
                    <div class="rvd-item"><span class="rvd-item__label">Tratamiento F75</span><span class="rvd-item__value">{{ $latest->tratamiento_f75 }}</span></div>
                    <div class="rvd-item"><span class="rvd-item__label">Fecha recibio F75</span><span class="rvd-item__value">{{ $latest->fecha_recibio_tratf75 }}</span></div>
                @endif
                <div class="rvd-item"><span class="rvd-item__label">Motivo reapertura</span><span class="rvd-item__value">{{ $latest->motivo_reapuertura }}</span></div>
            </div>
        </section>

        <section class="rvd-card">
            <div class="rvd-card__head"><i class="fas fa-history mr-1"></i> Historial completo del caso</div>
            <div class="rvd-table-wrap">
                <table class="table table-hover rvd-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha consulta</th>
                            <th>Peso</th>
                            <th>Talla</th>
                            <th>Puntaje Z</th>
                            <th>Clasificacion</th>
                            <th>Estado menor</th>
                            <th>Proximo control</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($segene as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->fecha_consulta }}</td>
                                <td>{{ $formatPeso($item->peso_kilos) }}</td>
                                <td>{{ $formatTalla($item->talla_cm) }}</td>
                                <td>{{ $formatPuntajeZ($item->puntajez) }}</td>
                                <td>{{ $item->clasificacion }}</td>
                                <td>{{ $item->est_act_menor }}</td>
                                <td>{{ $item->fecha_proximo_control }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rvd-actions">
                @if($isReviewed)
                    <button type="button" class="rvd-btn rvd-btn--done" disabled><i class="fas fa-check-circle mr-1"></i> Ya revisado</button>
                @else
                    <form action="{{ url('/revision') }}" method="POST" onsubmit="return confirm('Confirmas marcar este seguimiento como revisado?');">
                        @csrf
                        <input type="hidden" name="modulo" value="{{ $modulo }}">
                        @if($modulo === '412')
                            <input type="hidden" name="seguimiento_412_id" value="{{ $latest->id }}">
                        @else
                            <input type="hidden" name="seguimientos_id" value="{{ $latest->id }}">
                        @endif
                        <button type="submit" class="rvd-btn rvd-btn--primary"><i class="fas fa-clipboard-check mr-1"></i> Marcar como revisado</button>
                    </form>
                @endif

                <a href="{{ route('revision.index') }}" class="rvd-btn--light"><i class="fas fa-arrow-left mr-1"></i> Volver al centro de revision</a>
            </div>
        </section>
    @else
        <section class="rvd-card">
            <div class="p-4 text-center text-muted">No se encontro informacion de seguimiento para este caso.</div>
        </section>
        <div class="rvd-actions">
            <a href="{{ route('revision.index') }}" class="rvd-btn--light"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        </div>
    @endif
</div>
@stop
