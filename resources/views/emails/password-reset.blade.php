<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - MecxiHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
        }

        .header h1 .blue {
            color: #0039A6;
        }

        .header h1 .orange {
            color: #FF6B00;
        }

        .content {
            color: #333;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            background: #0039A6;
            color: white !important;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin: 10px 0;
        }

        .btn:hover {
            background: #002b80;
        }

        .footer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .warning {
            background: #fef9e7;
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #f39c12;
            margin: 15px 0;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><span class="blue">Mecxi</span><span class="orange">Hub</span></h1>
        </div>

        <div class="content">
            <h2 style="color: #0039A6; font-size: 20px;">Restablece tu contraseña</h2>

            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>MecxiHub</strong>.
            </p>

            <p>Haz clic en el siguiente enlace para continuar:</p>

            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="btn">
                    🔑 Restablecer Contraseña
                </a>
            </div>

            <div class="warning">
                ⚠️ Este enlace es válido por <strong>2 horas</strong>. Si no solicitaste este cambio, ignora este
                mensaje.
            </div>

            <p style="font-size: 14px; color: #666;">Si el botón no funciona, copia y pega este enlace en tu navegador:
            </p>
            <p style="font-size: 12px; background: #f0f0f0; padding: 10px; border-radius: 6px; word-break: break-all;">
                {{ $resetUrl }}
            </p>
        </div>

        <div class="footer">
            <p>MecxiHub - Sistema de Gestión IoT</p>
            <p style="font-size: 11px; color: #999;">Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>

</html>
