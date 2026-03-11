<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Cargue PAI</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        .header { width: 100%; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; margin-bottom: 12px; }
        .header td { vertical-align: middle; }
        .logo { width: 52px; }
        .title { font-size: 16px; font-weight: 700; margin: 0; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        .meta { margin: 8px 0 10px; font-size: 10px; color: #334155; }
        .kpis { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .kpis td { border: 1px solid #cbd5e1; padding: 6px; }
        .kpi-label { font-size: 9px; color: #475569; text-transform: uppercase; }
        .kpi-value { font-size: 14px; font-weight: 700; margin-top: 2px; }
        table.report { width: 100%; border-collapse: collapse; }
        table.report th, table.report td { border: 1px solid #dbe3ee; padding: 6px; vertical-align: top; }
        table.report th { background: #eaf2ff; font-size: 10px; text-transform: uppercase; color: #0f172a; }
        .muted { color: #64748b; }
        ul { margin: 0; padding-left: 14px; }
        li { margin: 1px 0; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:60px;">
                <img src="{{ public_path('img/logo.png') }}" alt="Escudo" class="logo">
            </td>
            <td>
                <p class="title">Informe Dinamico de Cargue PAI</p>
                <div class="subtitle">Trazabilidad por usuario en rango de fechas (fecha de carga del registro)</div>
            </td>
            <td style="text-align:right; font-size:10px;">
                Generado: {{ $generatedAt ?? now()->format('d/m/Y H:i:s') }}
            </td>
        </tr>
    </table>

    <div class="meta">
        <strong>Rango consultado:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @if(!empty($filters['user_id']) || !empty($filters['only_without_load']))
            <br>
            <strong>Filtros aplicados:</strong>
            @if(!empty($filters['user_id']))
                Usuario #{{ $filters['user_id'] }}
            @else
                Todos los usuarios
            @endif
            @if(!empty($filters['only_without_load']))
                | Solo usuarios sin cargue
            @endif
        @endif
    </div>

    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-label">Usuarios</div>
                <div class="kpi-value">{{ number_format($totals['users_total'] ?? 0) }}</div>
            </td>
            <td>
                <div class="kpi-label">Con Cargue</div>
                <div class="kpi-value">{{ number_format($totals['users_with_load'] ?? 0) }}</div>
            </td>
            <td>
                <div class="kpi-label">Sin Cargue</div>
                <div class="kpi-value">{{ number_format($totals['users_without_load'] ?? 0) }}</div>
            </td>
            <td>
                <div class="kpi-label">Vacunas Cargadas</div>
                <div class="kpi-value">{{ number_format($totals['vacunas_total'] ?? 0) }}</div>
            </td>
            <td>
                <div class="kpi-label">Afiliados Impactados</div>
                <div class="kpi-value">{{ number_format($totals['afiliados_total'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th style="width:22%;">Usuario</th>
                <th style="width:28%;">Correo</th>
                <th style="width:10%;">Vacunas</th>
                <th style="width:10%;">Afiliados</th>
                <th style="width:8%;">Lotes</th>
                <th style="width:14%;">Ultimo Cargue</th>
                <th style="width:8%;">Solicitudes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['usuario'] ?? 'Sin nombre' }}</td>
                    <td>{{ $row['correo'] ?? 'Sin correo' }}</td>
                    <td style="text-align:center;">{{ number_format($row['vacunas_count'] ?? 0) }}</td>
                    <td style="text-align:center;">{{ number_format($row['afiliados_count'] ?? 0) }}</td>
                    <td style="text-align:center;">{{ number_format($row['lotes_count'] ?? 0) }}</td>
                    <td>{{ !empty($row['last_load_at']) ? \Carbon\Carbon::parse($row['last_load_at'])->format('d/m/Y H:i') : 'Sin cargue' }}</td>
                    <td style="text-align:center;">{{ number_format($row['solicitudes_count'] ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;" class="muted">No se encontraron registros para el rango indicado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
