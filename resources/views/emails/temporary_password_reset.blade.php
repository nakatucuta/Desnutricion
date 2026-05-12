<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrasena temporal asignada</title>
</head>
<body style="margin:0;padding:0;background:#f3f8f7;font-family:Arial,Helvetica,sans-serif;color:#183b38;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f8f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:94%;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #d7e9e6;box-shadow:0 12px 28px rgba(15,76,71,.10);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#031a44,#0f766e);padding:26px 28px;color:#ffffff;">
                            <h1 style="margin:0;font-size:24px;line-height:1.25;color:#ffffff;">Contrasena temporal asignada</h1>
                            <p style="margin:8px 0 0;color:#e9fffb;font-size:14px;">ANAS WAYUU - Gestion de seguridad de cuenta</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 28px;">
                            <p style="margin:0 0 14px;font-size:15px;">Hola <strong>{{ $user->name ?? 'usuario' }}</strong>,</p>

                            <p style="margin:0 0 14px;font-size:15px;line-height:1.55;">
                                El superadministrador restablecio tu contrasena de acceso al sistema. Por seguridad, esta contrasena es temporal y el sistema te pedira crear una nueva al ingresar.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:18px 0;background:#f2fbf9;border:1px solid #cde9e4;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#0f766e;font-weight:bold;margin-bottom:8px;">Contrasena temporal</div>
                                        <div style="font-size:22px;font-weight:bold;color:#102f2c;background:#ffffff;border:1px dashed #8fcfc6;border-radius:10px;padding:12px 14px;word-break:break-all;">
                                            {{ $temporaryPassword }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px;font-size:15px;line-height:1.55;">
                                Para ingresar, haz clic en el siguiente enlace e inicia sesion con tu correo y la contrasena temporal:
                            </p>

                            <p style="margin:20px 0;">
                                <a href="{{ $loginUrl }}" style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;font-weight:bold;border-radius:10px;padding:12px 18px;">Ingresar al sistema</a>
                            </p>

                            <p style="margin:0 0 12px;font-size:15px;line-height:1.55;">
                                Despues de iniciar sesion, el sistema te llevara al perfil para cambiarla. Tambien puedes usar este enlace:
                                <br>
                                <a href="{{ $profileUrl }}" style="color:#0f766e;font-weight:bold;">Cambiar contrasena en mi perfil</a>
                            </p>

                            <div style="margin-top:18px;padding:14px 16px;border-left:4px solid #ef4444;background:#fff7f7;border-radius:10px;color:#5f2429;font-size:14px;line-height:1.5;">
                                No compartas esta contrasena. Si no solicitaste soporte o no reconoces esta accion, comunicate inmediatamente con el administrador del sistema.
                            </div>

                            <p style="margin:18px 0 0;font-size:13px;color:#6b7f7b;line-height:1.45;">
                                Fecha del restablecimiento: {{ $resetAt }}<br>
                                Administrador responsable: {{ $admin->name ?? 'Superadministrador' }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
