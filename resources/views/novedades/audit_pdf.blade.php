<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Auditoria Novedad #{{ $novedad->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2d3d; }
        h1 { margin: 0 0 8px 0; font-size: 18px; }
        h2 { margin: 18px 0 8px 0; font-size: 14px; }
        .meta { margin-bottom: 12px; color: #415a77; }
        .box { border: 1px solid #c8d7ea; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .stats span { display: inline-block; margin-right: 12px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d3dfef; padding: 6px; text-align: left; font-size: 11px; }
        th { background: #edf4fc; }
    </style>
</head>
<body>
    <h1>Auditoria de Lectura de Novedad</h1>
    <div class="meta">
        Generado: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <div class="box">
        <strong>Titulo:</strong> {{ $novedad->title }}<br>
        <strong>Mensaje:</strong> {{ $novedad->message }}<br>
        <strong>Publicada:</strong> {{ optional($novedad->created_at)->format('Y-m-d H:i') }}
    </div>

    <div class="box stats">
        <span>Total usuarios: {{ $totalUsers }}</span>
        <span>Leida: {{ $totalReads }}</span>
        <span>Pendiente: {{ $totalPending }}</span>
    </div>

    <h2>Usuarios que leyeron ({{ $reads->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Codigo</th>
                <th>Fecha lectura</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reads as $read)
                <tr>
                    <td>{{ optional($read->user)->name ?? 'N/D' }}</td>
                    <td>{{ optional($read->user)->email ?? 'N/D' }}</td>
                    <td>{{ optional($read->user)->codigohabilitacion ?? 'N/D' }}</td>
                    <td>{{ optional($read->read_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Sin registros de lectura.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Usuarios pendientes ({{ $notReadUsers->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Codigo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notReadUsers as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->codigohabilitacion }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin pendientes. Todos leyeron la novedad.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
