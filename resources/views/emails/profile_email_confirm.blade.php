<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Confirmacion de correo</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f7f9;padding:24px;">
    <div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e5ebef;border-radius:12px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#0f6f7e,#18a874);padding:16px 20px;color:#fff;">
            <h2 style="margin:0;font-size:20px;">Confirma tu nuevo correo</h2>
        </div>
        <div style="padding:20px;color:#2d3748;line-height:1.5;">
            <p style="margin-top:0;">Hola {{ $userName }},</p>
            <p>Recibimos una solicitud para cambiar tu correo de acceso a <strong>{{ $newEmail }}</strong>.</p>
            <p>Para confirmar el cambio, haz clic en el siguiente boton:</p>
            <p style="margin:24px 0;">
                <a href="{{ $confirmUrl }}" style="background:#0f7f8e;color:#fff;text-decoration:none;padding:12px 16px;border-radius:8px;display:inline-block;font-weight:700;">Confirmar cambio de correo</a>
            </p>
            <p>Este enlace vence el: <strong>{{ $expiresAt ?? 'sin fecha definida' }}</strong>.</p>
            <p>Si no realizaste esta solicitud, ignora este mensaje y conserva tu correo actual.</p>
        </div>
    </div>
</body>
</html>
