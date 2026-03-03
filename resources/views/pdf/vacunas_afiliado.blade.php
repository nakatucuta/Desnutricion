<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Vacunas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color:#1f2937; font-size:12px; }
        .head { border-bottom:2px solid #0f172a; padding-bottom:10px; margin-bottom:14px; }
        .head-table { width:100%; border-collapse:collapse; }
        .head-table td { vertical-align:middle; }
        .head-logo-wrap { width:72px; }
        .head-logo {
            width:56px;
            height:56px;
            object-fit:contain;
            border:1px solid #dbe4ef;
            border-radius:10px;
            padding:4px;
            background:#fff;
        }
        .title { font-size:18px; font-weight:700; color:#0f172a; margin:0; }
        .sub { margin-top:4px; color:#475569; }
        .grid { width:100%; border-collapse:collapse; margin-bottom:12px; }
        .grid td { padding:4px 6px; border:1px solid #dbe4ef; }
        .k { background:#f3f7fd; font-weight:700; width:25%; }
        table.list { width:100%; border-collapse:collapse; }
        table.list th, table.list td { border:1px solid #dbe4ef; padding:6px; }
        table.list th { background:#e8f0fb; text-align:left; font-weight:700; }
        .empty { margin-top:10px; padding:10px; border:1px dashed #94a3b8; color:#475569; }
        .foot { margin-top:12px; font-size:11px; color:#64748b; }
    </style>
</head>
<body>
    <div class="head">
        <table class="head-table">
            <tr>
                <td class="head-logo-wrap">
                    <img src="{{ public_path('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="head-logo">
                </td>
                <td>
                    <p class="title">Reporte de Vacunas del Afiliado</p>
                    <div class="sub">Generado: {{ $fechaGeneracion }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td class="k">Nombre</td><td>{{ $paciente['nombre'] ?? '' }}</td>
            <td class="k">Documento</td><td>{{ ($paciente['tipo_id'] ?? '') . ' ' . ($paciente['numero_id'] ?? '') }}</td>
        </tr>
        <tr>
            <td class="k">Sexo</td><td>{{ $paciente['sexo'] ?? '' }}</td>
            <td class="k">Edad</td><td>{{ $paciente['edad'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="k">Fecha nacimiento</td><td>{{ $paciente['fecha_nacimiento'] ?? '' }}</td>
            <td class="k">IPS</td><td>{{ $paciente['ips'] ?? '' }}</td>
        </tr>
    </table>

    @if(($vacunas ?? collect())->count() > 0)
        <table class="list">
            <thead>
                <tr>
                    <th>Biologico</th>
                    <th>Dosis</th>
                    <th>Fecha aplicacion</th>
                    <th>Edad anos</th>
                    <th>Edad meses</th>
                    <th>IPS vacunadora</th>
                    <th>Vacunador</th>
                    <th>Regimen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vacunas as $v)
                    <tr>
                        <td>{{ $v->nombre_vacuna ?? '' }}</td>
                        <td>{{ $v->docis_vacuna ?? '' }}</td>
                        <td>{{ $v->fecha_vacunacion ?? '' }}</td>
                        <td>{{ $v->edad_anos ?? '' }}</td>
                        <td>{{ $v->total_meses ?? '' }}</td>
                        <td>{{ $v->nombre_usuario ?? '' }}</td>
                        <td>{{ $v->responsable ?? '' }}</td>
                        <td>{{ $v->regimen_vacuna ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No hay vacunas registradas para este afiliado.</div>
    @endif

    <div class="foot">Sistema PAI - EPS IANAS WAYUU</div>
</body>
</html>
