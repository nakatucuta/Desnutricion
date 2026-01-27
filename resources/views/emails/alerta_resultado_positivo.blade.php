<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f5f6f8; padding:20px;">
  <div style="max-width:720px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
    <div style="background:#dc2626;color:#fff;padding:16px 20px;">
      <h2 style="margin:0;font-size:18px;">⚠️ Alerta de Resultado Positivo</h2>
      <p style="margin:6px 0 0;font-size:13px;opacity:.9;">
        Se detectó un resultado que requiere atención.
      </p>
    </div>

    <div style="padding:18px 20px;">
      <p style="margin:0 0 12px;"><strong>Paciente:</strong> {{ $pacienteNombre }}</p>
      <p style="margin:0 0 12px;"><strong>Documento:</strong> {{ $pacienteDocumento }}</p>

      <hr style="border:none;border-top:1px solid #eee;margin:14px 0;">

      <p style="margin:0 0 8px;"><strong>Examen:</strong> {{ $alert->examen }}</p>
      <p style="margin:0 0 8px;"><strong>Resultado:</strong> {{ $alert->resultado }}</p>
      <p style="margin:0 0 8px;"><strong>Severidad:</strong> {{ strtoupper($alert->severidad ?? '') }}</p>
      {{-- <p style="margin:0 0 8px;"><strong>Módulo:</strong> {{ $alert->modulo }}</p> --}}
      <p style="margin:0 0 8px;"><strong>Campo:</strong> {{ $alert->campo }}</p>
      <p style="margin:0 0 8px;"><strong>Fecha:</strong> {{ optional($alert->created_at)->format('Y-m-d H:i:s') }}</p>

      @if(!empty($pdfUrl))
        <div style="margin-top:16px;">
          <a href="{{ $pdfUrl }}" target="_blank"
             style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;">
            📄 Ver PDF del resultado
          </a>
        </div>
      @else
        <p style="margin-top:16px;color:#6b7280;"><em>No hay PDF adjunto en esta alerta.</em></p>
      @endif

      <p style="margin-top:18px;color:#6b7280;font-size:12px;">
        Este correo fue generado automáticamente por el sistema.
      </p>
    </div>
  </div>
</body>
</html>
