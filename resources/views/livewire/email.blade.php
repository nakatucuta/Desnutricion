<!-- resources/views/livewire/afiliado.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Correo de Solicitud</title>
</head>
<body>
    <h1>{{ $details['subject'] }}</h1>
    <p>Estimado/a,</p>
    <p>{{ $details['message'] }}</p>
    <p>Enviado por: {{ $details['fromEmail'] }}</p>
    <p>Paciente: {{ $details['patientName'] }}</p>
</body>
</html>
