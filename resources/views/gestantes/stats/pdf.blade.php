<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Integral Gestantes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        .header { width: 100%; border-bottom: 1px solid #dbe3ee; padding-bottom: 8px; margin-bottom: 10px; }
        .header td { vertical-align: middle; }
        .logo { width: 52px; }
        .title { font-size: 15px; font-weight: 700; margin: 0; }
        .subtitle { color: #475569; font-size: 9px; }
        .meta { margin: 6px 0 10px; color: #334155; }
        .kpis { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .kpis td { border: 1px solid #dbe3ee; padding: 6px; width: 25%; }
        .kpi-l { font-size: 8px; color: #64748b; text-transform: uppercase; }
        .kpi-v { font-size: 13px; font-weight: 700; margin-top: 2px; }
        table.t { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.t th, table.t td { border: 1px solid #dbe3ee; padding: 5px; vertical-align: top; }
        table.t th { background: #eaf2ff; text-transform: uppercase; font-size: 8px; }
        .charts img { width: 100%; border: 1px solid #dbe3ee; margin-bottom: 8px; }
        .section { font-size: 12px; font-weight: 700; margin: 10px 0 6px; }
    </style>
</head>
<body>
    @php
        $k = $payload['kpis'] ?? [];
        $f = $payload['filters'] ?? [];
        $insights = $payload['insights'] ?? [];
        $mods = $payload['modules'] ?? [];
    @endphp

    <table class="header">
        <tr>
            <td style="width:60px;"><img src="{{ public_path('img/logo.png') }}" class="logo" alt="Escudo empresa"></td>
            <td>
                <p class="title">Reporte Integral de Estadisticas de Gestantes</p>
                <div class="subtitle">Tablero filtrado para toma de decisiones</div>
            </td>
            <td style="text-align:right; font-size:9px;">Generado: {{ $generatedAt ?? now()->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <div class="meta">
        <strong>Modulo:</strong> {{ $f['module'] ?? 'todos' }} |
        <strong>Desde:</strong> {{ $f['from'] ?? 'N/A' }} |
        <strong>Hasta:</strong> {{ $f['to'] ?? 'N/A' }} |
        <strong>Identificacion:</strong> {{ $f['identificacion'] ?? 'N/A' }} |
        <strong>Municipio:</strong> {{ $f['municipio'] ?? 'N/A' }}
    </div>

    <table class="kpis">
        <tr>
            <td><div class="kpi-l">Total General</div><div class="kpi-v">{{ number_format($k['total_general'] ?? 0) }}</div></td>
            <td><div class="kpi-l">FPP vencidas</div><div class="kpi-v">{{ number_format($k['fpp_vencidas'] ?? 0) }}</div></td>
            <td><div class="kpi-l">Precon alto riesgo</div><div class="kpi-v">{{ number_format($k['precon_alto_riesgo'] ?? 0) }}</div></td>
            <td><div class="kpi-l">SIV notificados hoy</div><div class="kpi-v">{{ number_format($k['siv_notificados_hoy'] ?? 0) }}</div></td>
        </tr>
    </table>

    <div class="section">Alertas de gestion</div>
    <table class="t">
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Valor</th>
                <th>Prioridad</th>
                <th>Accion</th>
            </tr>
        </thead>
        <tbody>
            @forelse($insights as $i)
                <tr>
                    <td>{{ $i['indicador'] ?? '' }}</td>
                    <td>{{ $i['valor'] ?? '' }}</td>
                    <td>{{ $i['prioridad'] ?? '' }}</td>
                    <td>{{ $i['accion'] ?? ($i['accion_sugerida'] ?? '') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">Sin hallazgos para estos filtros.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Graficas del tablero</div>
    <div class="charts">
        @if(!empty($chartImages['chartModules']))<img src="{{ $chartImages['chartModules'] }}" alt="Distribucion por modulo">@endif
        @if(!empty($chartImages['chartMonthly']))<img src="{{ $chartImages['chartMonthly'] }}" alt="Tendencia comparada">@endif
        @if(!empty($chartImages['chartPrecon']))<img src="{{ $chartImages['chartPrecon'] }}" alt="Riesgo preconcepcional">@endif
        @if(!empty($chartImages['chartTipo1']))<img src="{{ $chartImages['chartTipo1'] }}" alt="Estado FPP Tipo 1">@endif
        @if(!empty($chartImages['chartTipo3']))<img src="{{ $chartImages['chartTipo3'] }}" alt="Riesgo Tipo 3">@endif
        @if(!empty($chartImages['chartSiv']))<img src="{{ $chartImages['chartSiv'] }}" alt="SIV por semana">@endif
    </div>

    <div class="section">Resumen por modulo</div>
    <table class="t">
        <thead>
            <tr>
                <th>Modulo</th>
                <th>Total</th>
                <th>Indicador 1</th>
                <th>Indicador 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Preconcepcional</td>
                <td>{{ number_format($mods['preconcepcional']['summary']['total'] ?? 0) }}</td>
                <td>Alto riesgo: {{ number_format($mods['preconcepcional']['summary']['alto_riesgo'] ?? 0) }}</td>
                <td>Sin telefono: {{ number_format($mods['preconcepcional']['summary']['sin_telefono'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>Tipo 1</td>
                <td>{{ number_format($mods['tipo1']['summary']['total'] ?? 0) }}</td>
                <td>FPP vencidas: {{ number_format($mods['tipo1']['summary']['fpp_vencidas'] ?? 0) }}</td>
                <td>Sin FPP: {{ number_format($mods['tipo1']['summary']['sin_fpp'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>Tipo 3</td>
                <td>{{ number_format($mods['tipo3']['summary']['total'] ?? 0) }}</td>
                <td>Con CUPS: {{ number_format($mods['tipo3']['summary']['con_cups'] ?? 0) }}</td>
                <td>Con riesgo: {{ number_format($mods['tipo3']['summary']['con_riesgo_gestacional'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>SIV549</td>
                <td>{{ number_format($mods['siv549']['summary']['total'] ?? 0) }}</td>
                <td>Notificados hoy: {{ number_format($mods['siv549']['summary']['notificados_hoy'] ?? 0) }}</td>
                <td>Sin telefono: {{ number_format($mods['siv549']['summary']['sin_telefono'] ?? 0) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
