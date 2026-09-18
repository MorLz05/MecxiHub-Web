<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Califica nuestro servicio</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f3f4f6; color: #1f2937;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f3f4f6; padding: 30px 15px;">
        <tr>
            <td align="center">

                {{-- Contenedor --}}
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #0039A6 0%, #002677 100%); padding: 32px 24px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 900; color: #ffffff;">
                                <span style="color: #ffffff;">Mecxi</span><span style="color: #FF6B00;">Hub</span>
                            </h1>
                            <p style="margin: 8px 0 0; font-size: 13px; color: rgba(255,255,255,0.85);">
                                Conectamos tu auto con el mejor taller
                            </p>
                        </td>
                    </tr>

                    {{-- Cuerpo --}}
                    <tr>
                        <td style="padding: 40px 32px 32px;">

                            <h2 style="margin: 0 0 8px; font-size: 22px; font-weight: 800; color: #001B5E; text-align: center;">
                                ¡Gracias por tu confianza!
                            </h2>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #4b5563; text-align: center;">
                                Hola{{ !empty($clienteNombre) ? ' ' . $clienteNombre : '' }}, esperamos que tu experiencia con
                                <strong style="color: #0039A6;">{{ $tallerData['nombre'] ?? 'el taller' }}</strong> haya sido excelente.
                            </p>

                            {{-- Tarjeta de la orden --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0f4ff; border-radius: 12px; padding: 20px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                                            Orden de trabajo
                                        </p>
                                        <p style="margin: 0 0 12px; font-size: 18px; font-weight: 800; color: #001B5E; font-family: 'Courier New', monospace;">
                                            {{ $folio }}
                                        </p>
                                        @if (!empty($orden['vehiculo']))
                                            <p style="margin: 0; font-size: 14px; color: #4b5563;">
                                                <strong>Vehículo:</strong>
                                                {{ ($orden['vehiculo']['marca'] ?? '') }}
                                                {{ ($orden['vehiculo']['modelo'] ?? '') }}
                                                {{ ($orden['vehiculo']['anio'] ?? '') }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Llamado a la acción --}}
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #4b5563; text-align: center;">
                                Tu opinión nos ayuda a mejorar y a que otros conductores encuentren talleres de calidad.
                                <strong>Cuéntanos cómo te fue.</strong>
                            </p>

                            {{-- Botón --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0 24px;">
                                        <a href="{{ $urlCalificacion }}"
                                            style="display: inline-block; padding: 14px 40px; background-color: #FF6B00; color: #ffffff; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(255,107,0,0.3);">
                                            Calificar mi experiencia →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Aviso de expiración --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fff7ed; border-left: 4px solid #FF6B00; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.5;">
                                            ⏱️ <strong>Tienes 15 días</strong> para dejarnos tu comentario desde la fecha de entrega.
                                            Después de ese plazo el enlace ya no estará disponible.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Link alternativo --}}
                            <p style="margin: 24px 0 0; font-size: 12px; color: #9ca3af; text-align: center; line-height: 1.5;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                <a href="{{ $urlCalificacion }}" style="color: #0039A6; word-break: break-all;">
                                    {{ $urlCalificacion }}
                                </a>
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 32px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 8px; font-size: 12px; color: #6b7280;">
                                Este correo fue enviado automáticamente por MecxiHub.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {{ date('Y') }} MecxiHub · Todos los derechos reservados
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
