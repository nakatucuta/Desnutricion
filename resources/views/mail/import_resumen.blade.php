<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; font-size: 14px; color:#111;">
    <h2 style="margin:0 0 10px;">Importación PAI finalizada</h2>

    <p style="margin:0 0 6px;">
        <strong>Usuario:</strong> {{ $usuario }}
    </p>
    <p style="margin:0 0 14px;">
        <strong>Batch:</strong> #{{ $batchId }}
    </p>

    <h3 style="margin:14px 0 6px;">Resumen</h3>
    <ul style="margin:0 0 14px; padding-left:18px;">
        <li><strong>Afiliados nuevos:</strong> {{ $stats['newAfil'] ?? 0 }}</li>
        <li><strong>Afiliados ya existentes:</strong> {{ $stats['oldAfil'] ?? 0 }}</li>
        <li><strong>Vacunas nuevas:</strong> {{ $stats['newVacuna'] ?? 0 }}</li>
        <li><strong>Vacunas duplicadas:</strong> {{ $stats['oldVacuna'] ?? 0 }}</li>
        <li><strong>No afiliados (BD externa):</strong> {{ $stats['noAfiliados'] ?? 0 }}</li>
    </ul>

    @if(!empty($noAfiliados))
        <h3 style="margin:14px 0 6px;">No afiliados (muestra)</h3>
        <p style="margin:0 0 6px;">Se muestran máximo 20:</p>
        <ul style="margin:0 0 14px; padding-left:18px;">
            @foreach(array_slice($noAfiliados, 0, 20) as $na)
                <li>
                    Fila {{ $na['fila_excel'] ?? '?' }}:
                    {{ $na['tipo_identificacion'] ?? '' }} {{ $na['numero_identificacion'] ?? '' }}
                    ({{ $na['motivo'] ?? 'No existe' }})
                </li>
            @endforeach
        </ul>
    @endif

    @if(!empty($errores))
        <h3 style="margin:14px 0 6px;">Errores/Advertencias (muestra)</h3>
        <p style="margin:0 0 6px;">Se muestran máximo 20:</p>
        <ul style="margin:0; padding-left:18px;">
            @foreach(array_slice($errores, 0, 20) as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    @endif

    <hr style="margin:18px 0; border:none; border-top:1px solid #ddd;">
    <p style="margin:0; color:#444;">
        Este correo se generó automáticamente al finalizar el procesamiento.
    </p>
</body>
</html>
