<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta PI</title>
    <style>
        table { border-collapse: collapse; width:100%; font-family: Arial, sans-serif; font-size:12px; }
        th, td { border:1px solid #ddd; padding:6px 8px; }
        th { background:#f3f4f6; text-align:left; }
        .muted { color:#6b7280; }
    </style>
</head>
<body>
    <h3>Alerta PI — Actividades pendientes</h3>
    <p class="muted"><strong>Rango:</strong> {{ $desde }} al {{ $hasta }}</p>

    <table>
        <thead>
        <tr>
            <th>Identificación</th>
            <th>Nombre</th>
            <th>Edad (años)</th>
            <th>IPS Primaria</th>
            <th>Actividad</th>
            <th>Cód. Hab.</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $i)
            <tr>
                <td>{{ $i->tipoIdentificacion }} {{ $i->identificacion }}</td>
                <td>{{ trim(($i->primerApellido ?? '').' '.($i->segundoApellido ?? '').' '.($i->primerNombre ?? '').' '.($i->segundoNombre ?? '')) }}</td>
                <td>{{ $i->edadAnios }}</td>
                <td>{{ $i->ips_Prim }}</td>
                <td>{{ $i->descrip }}</td>
                <td>{{ $i->codigoHabilitacion }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
