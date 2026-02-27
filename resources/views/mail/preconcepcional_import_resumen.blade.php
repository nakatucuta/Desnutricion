<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen Cargue Preconcepcional</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2 style="margin:0 0 12px 0;">Resumen de Cargue Preconcepcional</h2>

    <p style="margin:0 0 6px 0;"><strong>Usuario:</strong> {{ $usuario }}</p>
    <p style="margin:0 0 6px 0;"><strong>Estado:</strong> {{ $estado }}</p>
    <p style="margin:0 0 6px 0;"><strong>Lote:</strong> #{{ $batchId }}</p>
    <p style="margin:0 0 6px 0;"><strong>Archivo:</strong> {{ $originalFilename }}</p>
    <p style="margin:0 0 16px 0;"><strong>Mensaje:</strong> {{ $resumen }}</p>

    <table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse; font-size:13px;">
        <tr><td><strong>Filas leidas</strong></td><td>{{ (int)($counters['rows_total'] ?? 0) }}</td></tr>
        <tr><td><strong>Filas creadas</strong></td><td>{{ (int)($counters['rows_created'] ?? 0) }}</td></tr>
        <tr><td><strong>Duplicados detectados</strong></td><td>{{ (int)($counters['rows_duplicate'] ?? 0) }}</td></tr>
        <tr><td><strong>Filas omitidas</strong></td><td>{{ (int)($counters['rows_skipped_total'] ?? ($counters['rows_skipped'] ?? 0)) }}</td></tr>
        <tr><td><strong>Filas invalidas</strong></td><td>{{ (int)($counters['rows_invalid'] ?? 0) }}</td></tr>
        <tr><td><strong>Duracion (seg)</strong></td><td>{{ $counters['duration_seconds'] ?? '-' }}</td></tr>
    </table>

    @if(!empty($errors))
        <h3 style="margin:16px 0 8px 0;">Errores detectados (muestra):</h3>
        <ul style="padding-left:18px; margin:0;">
            @foreach($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
