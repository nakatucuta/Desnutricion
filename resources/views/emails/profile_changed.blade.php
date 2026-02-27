<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cambio de Perfil</title>
</head>
<body style="font-family: Arial, sans-serif; color:#1c2d44;">
    <h3>Notificacion de cambio de perfil</h3>
    <p>Se detecto una actualizacion en el perfil de usuario.</p>
    <ul>
        <li><strong>Usuario ID:</strong> {{ $userId }}</li>
        <li><strong>Accion:</strong> {{ $action }}</li>
        <li><strong>Campos afectados:</strong> {{ implode(', ', $fields) }}</li>
        <li><strong>Fecha:</strong> {{ $changedAt }}</li>
    </ul>
    <p>Si no reconoces este cambio, contacta al administrador inmediatamente.</p>
</body>
</html>
