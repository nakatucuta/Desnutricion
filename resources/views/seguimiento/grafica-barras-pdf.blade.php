<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Dashboard Seguimiento</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        .header { width: 100%; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; margin-bottom: 10px; }
        .header td { vertical-align: middle; }
        .logo { width: 52px; }
        .title { font-size: 16px; font-weight: 700; margin: 0; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        .meta { margin: 8px 0 10px; font-size: 10px; color: #334155; }
        .kpis { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .kpis td { border: 1px solid #dbe3ee; padding: 6px; }
        .kpi-label { font-size: 9px; color: #475569; text-transform: uppercase; }
        .kpi-value { font-size: 14px; font-weight: 700; margin-top: 2px; }
        table.report { width: 100%; border-collapse: collapse; }
        table.report th, table.report td { border: 1px solid #dbe3ee; padding: 6px; vertical-align: middle; }
        table.report th { background: #eaf2ff; font-size: 10px; text-transform: uppercase; color: #0f172a; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:60px;">
                <img src="{{ public_path('img/logo.png') }}" alt="Escudo" class="logo">
            </td>
            <td>
                <p class="title">Reporte Ejecutivo de Seguimientos</p>
                <div class="subtitle">Prestadores con y sin seguimiento por evento</div>
            </td>
            <td style="text-align:right; font-size:10px;">
                Generado: {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}
            </td>
        </tr>
    </table>

    <div class="meta">
        <strong>Anio:</strong> {{ $filters['anio'] ?? '' }}
        | <strong>Evento:</strong> {{ strtoupper($filters['evento'] ?? 'todos') }}
        | <strong>Estado:</strong> {{ str_replace('_', ' ', $filters['estado'] ?? 'con_sin') }}
        @if(!empty($filters['q']))
            | <strong>Busqueda:</strong> {{ $filters['q'] }}
        @endif
    </div>

    <table class="kpis">
        <tr>
            <td><div class="kpi-label">Prestadores</div><div class="kpi-value">{{ number_format($kpis['total_prestadores'] ?? 0) }}</div></td>
            <td><div class="kpi-label">Asignados</div><div class="kpi-value">{{ number_format($kpis['total_asignados'] ?? 0) }}</div></td>
            <td><div class="kpi-label">Con Seguimiento</div><div class="kpi-value">{{ number_format($kpis['total_con_seguimiento'] ?? 0) }}</div></td>
            <td><div class="kpi-label">Sin Seguimiento</div><div class="kpi-value">{{ number_format($kpis['total_sin_seguimiento'] ?? 0) }}</div></td>
            <td><div class="kpi-label">Con Alerta</div><div class="kpi-value">{{ number_format($kpis['prestadores_con_alerta'] ?? 0) }}</div></td>
            <td><div class="kpi-label">Cobertura</div><div class="kpi-value">{{ number_format($kpis['cobertura_global_pct'] ?? 0, 2) }}%</div></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>ID</th>
                <th>Prestador</th>
                <th>Evento</th>
                <th>Asignados</th>
                <th>Con seg.</th>
                <th>Sin seg.</th>
                <th>Cobertura %</th>
                <th>Riesgo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->evento }}</td>
                    <td style="text-align:right;">{{ number_format($row->cant_casos_asignados) }}</td>
                    <td style="text-align:right;">{{ number_format($row->casos_con_seguimiento) }}</td>
                    <td style="text-align:right;">{{ number_format($row->total_sin_seguimientos) }}</td>
                    <td style="text-align:right;">{{ number_format($row->cobertura_pct, 2) }}%</td>
                    <td>{{ $row->nivel_riesgo }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No hay datos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
