{{-- resources/views/vendor/mail/html/layout.blade.php --}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        /* Reset y estructura */
        body, html { margin:0; padding:0; background:#f4f4f7; font-family: 'Segoe UI', sans-serif; }
        .wrapper { width:100%; background:#f4f4f7; padding:20px 0; }
        .content { max-width:600px; margin:0 auto; }

        /* Cabecera */
        .header {
            background: #17a2b8;
            text-align:center;
            padding:20px 0;
            border-top-left-radius:8px;
            border-top-right-radius:8px;
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* Cuerpo */
        .body {
            background:#ffffff;
            margin:0;
            padding:0;
            border-radius:0 0 8px 8px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }
        .inner-body { width:100%; padding:30px; }
        .content-cell { color:#50575e; line-height:1.6; }

        /* Botones */
        .button {
            display: inline-block;
            background: #17a2b8;
            color: #ffffff !important;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .button:hover { background: #0f6674; }

        /* Pie */
        .footer {
            text-align:center;
            padding: 20px;
            font-size:12px;
            color:#a8adb3;
        }

        /* Adaptativo */
        @media only screen and (max-width: 600px) {
            .inner-body { padding:20px !important; }
        }
    </style>
    {{ $head ?? '' }}
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                {{-- Cabecera con fondo de color y nombre de la app --}}
                <tr>
                    <td class="header">
                       ANAS WAYUU E.P.S.I
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">
                        <table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                                    {{ $subcopy ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Pie personalizado --}}
                <tr>
                    <td>
                        <div class="footer">
                             © {{ date('Y') }} RUTAS INTEGRALES. Todos los derechos reservados.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    </table>
</body>
</html>
