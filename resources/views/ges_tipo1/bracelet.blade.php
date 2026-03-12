<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulsera Clinica Gestante</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(196, 138, 33, 0.18), transparent 28%),
                linear-gradient(180deg, #f4f7fb 0%, #ffffff 100%);
            color: #19324a;
        }

        .wrapper {
            max-width: 1080px;
            margin: 0 auto;
            padding: 24px 18px 40px;
        }

        .hero {
            background: linear-gradient(145deg, #16324f, #214d73);
            border-radius: 28px;
            color: #fff;
            padding: 24px;
            box-shadow: 0 18px 40px rgba(22, 50, 79, 0.22);
        }

        .hero-top {
            display: flex;
            gap: 18px;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo-box {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            background: rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            flex-shrink: 0;
        }

        .logo-box img {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.72rem;
            opacity: 0.78;
            margin-bottom: 6px;
            font-weight: 700;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            line-height: 1.05;
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            font-size: 0.95rem;
            opacity: 0.94;
        }

        .notice {
            margin-top: 18px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 0.95rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .metric, .panel, .record {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(18, 31, 45, 0.08);
            border: 1px solid rgba(22, 50, 79, 0.08);
        }

        .metric {
            padding: 16px;
        }

        .metric-label {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6d7b8a;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .metric-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: #16324f;
        }

        .section {
            margin-top: 22px;
        }

        .section-title {
            margin: 0 0 12px;
            font-size: 1.1rem;
            font-weight: 800;
            color: #16324f;
        }

        .panel {
            padding: 18px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .data-item {
            background: #f7fafc;
            border-radius: 14px;
            padding: 12px;
            border: 1px solid #e7edf3;
        }

        .data-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #7a8794;
            margin-bottom: 5px;
            font-weight: 800;
        }

        .data-value {
            font-weight: 700;
            color: #213547;
            word-break: break-word;
        }

        .record-list {
            display: grid;
            gap: 14px;
        }

        .record {
            padding: 16px;
        }

        .record h3 {
            margin: 0;
            font-size: 1rem;
            color: #16324f;
        }

        .record p {
            margin: 6px 0 0;
            color: #6d7b8a;
        }

        .empty {
            color: #7a8794;
            font-style: italic;
        }

        @media (max-width: 640px) {
            .wrapper {
                padding: 16px 12px 28px;
            }

            h1 {
                font-size: 1.55rem;
            }
        }
    </style>
</head>
<body>
@php
    $renderValue = function ($value) {
        $value = trim((string) $value);
        return $value !== '' ? $value : 'Sin dato';
    };
    $paciente = $expediente['paciente'] ?? [];
    $resumen = $expediente['resumen'] ?? [];
    $ficha = collect($expediente['gestanteFicha'] ?? [])->flatMap(fn ($section) => $section['values'] ?? [])->take(12)->values();
@endphp
<div class="wrapper">
    <section class="hero">
        <div class="hero-top">
            <div class="logo-box">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional">
            </div>
            <div>
                <div class="eyebrow">Pulsera clinica segura</div>
                <h1>{{ $renderValue($paciente['nombre'] ?? '') }}</h1>
                <div class="hero-meta">
                    <span><strong>Documento:</strong> {{ $renderValue($paciente['tipo_documento'] ?? '') }} {{ $renderValue($paciente['documento'] ?? '') }}</span>
                    <span><strong>Fecha nacimiento:</strong> {{ $renderValue($paciente['fecha_nacimiento'] ?? '') }}</span>
                    <span><strong>FPP:</strong> {{ $renderValue($paciente['fpp'] ?? '') }}</span>
                </div>
            </div>
        </div>
        <div class="notice">
            Este acceso fue generado para consulta rapida desde pulsera. Muestra datos clinicos resumidos y antecedentes clave
            para apoyar atencion y referencia institucional.
        </div>
    </section>

    <section class="grid">
        @foreach($resumen as $item)
            <article class="metric">
                <div class="metric-label">{{ $item['label'] ?? 'Indicador' }}</div>
                <div class="metric-value">{{ $item['value'] ?? 0 }}</div>
            </article>
        @endforeach
    </section>

    <section class="section">
        <h2 class="section-title"><i class="fas fa-id-card-alt mr-1"></i> Datos clave</h2>
        <div class="panel">
            <div class="data-grid">
                @foreach($ficha as $item)
                    <div class="data-item">
                        <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                        <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title"><i class="fas fa-user-md mr-1"></i> Ultimo seguimiento</h2>
        <div class="panel">
            @if($ultimoSeg)
                <div class="data-grid">
                    @foreach(array_slice($expediente['seguimientos'][0]['values'] ?? [], 0, 18) as $item)
                        <div class="data-item">
                            <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                            <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No hay seguimiento registrado en Tipo 2.</div>
            @endif
        </div>
    </section>

    <section class="section">
        <h2 class="section-title"><i class="fas fa-heartbeat mr-1"></i> Ultimo registro Tipo 3</h2>
        <div class="panel">
            @if($ultimoTipo3)
                <div class="data-grid">
                    @foreach(array_slice($expediente['tipo3'][0]['values'] ?? [], 0, 12) as $item)
                        <div class="data-item">
                            <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                            <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No hay registro Tipo 3 disponible.</div>
            @endif
        </div>
    </section>

    <section class="section">
        <h2 class="section-title"><i class="fas fa-bell mr-1"></i> Alertas recientes</h2>
        <div class="record-list">
            @forelse($alertas as $record)
                <article class="record">
                    <h3>{{ $record['title'] ?? 'Alerta' }}</h3>
                    <p>{{ $record['subtitle'] ?? '' }}</p>
                    <div class="data-grid" style="margin-top:12px;">
                        @foreach(array_slice($record['values'] ?? [], 0, 8) as $item)
                            <div class="data-item">
                                <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                                <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="panel empty">No hay alertas registradas.</div>
            @endforelse
        </div>
    </section>

    @if($preconcepcional)
        <section class="section">
            <h2 class="section-title"><i class="fas fa-seedling mr-1"></i> Historial preconcepcional</h2>
            <div class="panel">
                <div class="data-grid">
                    @foreach(array_slice($preconcepcional['values'] ?? [], 0, 16) as $item)
                        <div class="data-item">
                            <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                            <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($sivigila->isNotEmpty() || $maestro549->isNotEmpty())
        <section class="section">
            <h2 class="section-title"><i class="fas fa-clipboard-check mr-1"></i> Otros eventos institucionales</h2>
            <div class="record-list">
                @foreach($sivigila as $record)
                    <article class="record">
                        <h3>{{ $record['title'] ?? 'Sivigila' }}</h3>
                        <p>{{ $record['subtitle'] ?? '' }}</p>
                        <div class="data-grid" style="margin-top:12px;">
                            @foreach(array_slice($record['values'] ?? [], 0, 10) as $item)
                                <div class="data-item">
                                    <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach

                @foreach($maestro549 as $record)
                    <article class="record">
                        <h3>{{ $record['title'] ?? 'Caso SIV 549' }}</h3>
                        <p>{{ $record['subtitle'] ?? '' }}</p>
                        <div class="data-grid" style="margin-top:12px;">
                            @foreach(array_slice($record['values'] ?? [], 0, 10) as $item)
                                <div class="data-item">
                                    <div class="data-label">{{ $item['label'] ?? 'Campo' }}</div>
                                    <div class="data-value">{{ $renderValue($item['value'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
</body>
</html>
