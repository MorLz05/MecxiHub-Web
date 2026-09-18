<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden {{ $orden['folio'] ?? '' }} - {{ $tallerData['nombre'] ?? 'Taller' }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #e5e7eb;
            color: #000;
            font-size: 11pt;
            line-height: 1.4;
            padding: 20px;
        }

        /* Barra flotante de acciones (solo visible en pantalla) */
        .action-bar {
            position: fixed;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 1000;
        }

        .action-bar button,
        .action-bar a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-print {
            background: #0066FF;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
        }

        .btn-print:hover {
            background: #0055DD;
            transform: translateY(-1px);
        }

        .btn-close {
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .btn-close:hover {
            background: #f3f4f6;
        }

        /* Hoja */
        .hoja {
            width: 21.59cm;
            min-height: 27.94cm;
            margin: 60px auto 40px;
            padding: 1.5cm;
            background: #fff;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
        }

        /* Encabezado */
        .hoja-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding-bottom: 12px;
            border-bottom: 2.5px solid #001B5E;
            margin-bottom: 18px;
        }

        .hoja-logo {
            flex-shrink: 0;
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hoja-logo img {
            max-width: 90px;
            max-height: 90px;
            object-fit: contain;
        }

        .hoja-logo-fallback {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: #001B5E;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 32px;
            letter-spacing: 1px;
        }

        .hoja-taller-info {
            flex: 1;
            min-width: 0;
        }

        .hoja-taller-info h1 {
            font-size: 18pt;
            font-weight: 800;
            margin: 0 0 4px;
            color: #001B5E;
            line-height: 1.1;
        }

        .hoja-taller-info p {
            margin: 2px 0;
            font-size: 9.5pt;
            color: #333;
        }

        .hoja-taller-info i {
            color: #FF8800;
            margin-right: 4px;
            font-size: 9pt;
        }

        .hoja-folio {
            flex-shrink: 0;
            text-align: right;
            padding: 8px 12px;
            border: 1.5px solid #001B5E;
            border-radius: 6px;
            min-width: 150px;
        }

        .hoja-folio-label {
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 1px;
            color: #001B5E;
            margin: 0 0 2px;
        }

        .hoja-folio-num {
            font-family: 'Courier New', monospace;
            font-size: 12pt;
            font-weight: 800;
            color: #000;
            margin: 0;
        }

        .hoja-folio-fecha {
            font-size: 8pt;
            color: #555;
            margin: 3px 0 0;
        }

        /* Secciones */
        .hoja-seccion {
            margin-bottom: 14px;
        }

        .hoja-titulo-seccion {
            font-size: 10pt;
            font-weight: 800;
            color: #001B5E;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #001B5E;
            padding-bottom: 3px;
            margin: 0 0 8px;
        }

        .hoja-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .hoja-caja {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 8px 10px;
            background: #fafafa;
        }

        .hoja-caja h3 {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #001B5E;
            margin: 0 0 6px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #ccc;
        }

        .hoja-tabla-datos {
            width: 100%;
            border-collapse: collapse;
        }

        .hoja-tabla-datos td {
            padding: 2px 0;
            font-size: 10pt;
            vertical-align: top;
        }

        .hoja-label {
            font-weight: 600;
            color: #444;
            width: 35%;
        }

        .hoja-tabla-fallas {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .hoja-tabla-fallas thead {
            background: #001B5E;
            color: #fff;
        }

        .hoja-tabla-fallas th {
            padding: 6px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .hoja-tabla-fallas td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .hoja-tabla-fallas tbody tr:nth-child(even) {
            background: #f7f8fa;
        }

        .hoja-tabla-fallas .center {
            text-align: center;
        }

        .hoja-tabla-fallas .right {
            text-align: right;
        }

        .hoja-tabla-fallas .italic {
            font-style: italic;
            color: #888;
        }

        .hoja-totales {
            display: flex;
            justify-content: flex-end;
            margin-top: 6px;
        }

        .hoja-tabla-totales {
            width: 45%;
            border-collapse: collapse;
        }

        .hoja-tabla-totales td {
            padding: 4px 8px;
            font-size: 10pt;
        }

        .hoja-tabla-totales .right {
            text-align: right;
            font-weight: 600;
        }

        .hoja-total-final {
            border-top: 2px solid #001B5E;
            background: #f0f4ff;
        }

        .hoja-total-final td {
            font-size: 12pt;
            font-weight: 800;
            color: #001B5E;
            padding: 8px !important;
        }

        .hoja-saldo td {
            font-weight: 700;
            color: #b91c1c;
            font-size: 10.5pt;
        }

        .hoja-fecha-grande {
            font-size: 14pt;
            font-weight: 800;
            color: #001B5E;
            text-align: center;
            margin: 4px 0 0;
        }

        .hoja-terminos {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px dashed #999;
        }

        .hoja-terminos-texto {
            font-size: 8.5pt;
            color: #444;
            line-height: 1.45;
            margin: 0;
        }

        .hoja-firmas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }

        .hoja-firma {
            text-align: center;
        }

        .hoja-firma-linea {
            border-top: 1px solid #000;
            margin-bottom: 4px;
        }

        .hoja-firma p {
            font-size: 9pt;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .hoja-footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #888;
        }

        /* ==== IMPRESIÓN ==== */
        @page {
            size: letter;
            margin: 0;
        }

        @media print {

            html,
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .action-bar {
                display: none !important;
            }

            .hoja {
                width: 21.59cm;
                min-height: 27.94cm;
                margin: 0;
                padding: 1.5cm;
                box-shadow: none;
                border-radius: 0;
                page-break-after: avoid;
            }

            .hoja-impresion * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    {{-- Barra de acciones flotante (solo pantalla) --}}
    <div class="action-bar">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir / Guardar PDF
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fa-solid fa-xmark"></i> Cerrar
        </button>
    </div>

    @php
        $tallerNombre = $tallerData['nombre'] ?? 'Mi Taller';
        $tallerDireccion = $tallerData['direccion'] ?? null;
        $tallerTelefono = $tallerData['telefono'] ?? null;
        $tallerEmail = $tallerData['email'] ?? null;
        $tallerLogo = $tallerData['logo_url'] ?? null;

        // Iniciales para fallback
        $iniciales = '';
        foreach (preg_split('/\s+/', trim($tallerNombre)) as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
            }
            if (mb_strlen($iniciales) >= 2) {
                break;
            }
        }
        if (empty($iniciales)) {
            $iniciales = 'T';
        }

        $presupuesto = $orden['presupuesto'] ?? [];
        $vehiculo = $orden['vehiculo'] ?? [];
        $estado = $orden['estado'] ?? 'Pendiente';

        $anticipo = (float) ($presupuesto['anticipo'] ?? 0);
        $precioFinal = (float) ($orden['precio_final'] ?? 0);
        $saldo = max(0, $precioFinal - $anticipo);
    @endphp

    <div class="hoja hoja-impresion">

        {{-- ENCABEZADO --}}
        <div class="hoja-header">
            <div class="hoja-logo">
                @if (!empty($tallerLogo))
                    <img src="{{ $tallerLogo }}" alt="{{ $tallerNombre }}">
                @else
                    <div class="hoja-logo-fallback">{{ $iniciales }}</div>
                @endif
            </div>

            <div class="hoja-taller-info">
                <h1>{{ $tallerNombre }}</h1>
                @if ($tallerDireccion)
                    <p><i class="fa-solid fa-location-dot"></i> {{ $tallerDireccion }}</p>
                @endif
                @if ($tallerTelefono)
                    <p><i class="fa-solid fa-phone"></i> {{ $tallerTelefono }}</p>
                @endif
                @if ($tallerEmail)
                    <p><i class="fa-solid fa-envelope"></i> {{ $tallerEmail }}</p>
                @endif
            </div>

            <div class="hoja-folio">
                <p class="hoja-folio-label">ORDEN DE TRABAJO</p>
                <p class="hoja-folio-num">{{ $orden['folio'] ?? 'S/F' }}</p>
                <p class="hoja-folio-fecha">
                    {{ isset($orden['fecha_orden']) ? \Carbon\Carbon::parse($orden['fecha_orden'])->format('d/m/Y') : '-' }}
                </p>
            </div>
        </div>

        {{-- CLIENTE Y VEHÍCULO --}}
        <div class="hoja-seccion">
            <div class="hoja-grid-2">
                <div class="hoja-caja">
                    <h3>DATOS DEL CLIENTE</h3>
                    <table class="hoja-tabla-datos">
                        <tr>
                            <td class="hoja-label">Nombre:</td>
                            <td>{{ $orden['cliente_nombre'] ?? '-' }}</td>
                        </tr>
                        @if (!empty($orden['cliente_telefono']))
                            <tr>
                                <td class="hoja-label">Teléfono:</td>
                                <td>{{ $orden['cliente_telefono'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($orden['cliente_email']))
                            <tr>
                                <td class="hoja-label">Email:</td>
                                <td>{{ $orden['cliente_email'] }}</td>
                            </tr>
                        @endif
                    </table>
                </div>

                <div class="hoja-caja">
                    <h3>DATOS DEL VEHÍCULO</h3>
                    <table class="hoja-tabla-datos">
                        <tr>
                            <td class="hoja-label">Vehículo:</td>
                            <td>{{ $vehiculo['marca'] ?? '' }} {{ $vehiculo['modelo'] ?? '' }}
                                {{ $vehiculo['anio'] ?? '' }}</td>
                        </tr>
                        @if (!empty($vehiculo['placas']))
                            <tr>
                                <td class="hoja-label">Placas:</td>
                                <td>{{ strtoupper($vehiculo['placas']) }}</td>
                            </tr>
                        @endif
                        @if (!empty($vehiculo['color']))
                            <tr>
                                <td class="hoja-label">Color:</td>
                                <td>{{ $vehiculo['color'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($vehiculo['kilometraje']))
                            <tr>
                                <td class="hoja-label">Kilometraje:</td>
                                <td>{{ number_format($vehiculo['kilometraje']) }} km</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- FALLAS --}}
        <div class="hoja-seccion">
            <h3 class="hoja-titulo-seccion">FALLAS Y SERVICIOS</h3>
            <table class="hoja-tabla-fallas">
                <thead>
                    <tr>
                        <th style="width: 6%;">#</th>
                        <th style="width: 25%;">Categoría</th>
                        <th style="width: 40%;">Descripción</th>
                        <th style="width: 17%; text-align: right;">Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fallas as $i => $falla)
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td>{{ $falla['categoria'] ?? '-' }}</td>
                            <td>{{ $falla['descripcion'] ?? '-' }}</td>
                            <td class="right">${{ number_format($falla['precio'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="center italic">Sin fallas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TOTALES --}}
        <div class="hoja-seccion">
            <div class="hoja-totales">
                <table class="hoja-tabla-totales">
                    <tr>
                        <td class="hoja-label">Costo estimado:</td>
                        <td class="right">${{ number_format($presupuesto['costo_estimado'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="hoja-label">Anticipo:</td>
                        <td class="right">${{ number_format($anticipo, 2) }}</td>
                    </tr>
                    @if (!empty($presupuesto['metodo_pago']))
                        <tr>
                            <td class="hoja-label">Método de pago:</td>
                            <td class="right">{{ $presupuesto['metodo_pago'] }}</td>
                        </tr>
                    @endif
                    <tr class="hoja-total-final">
                        <td class="hoja-label">PRECIO FINAL:</td>
                        <td class="right">${{ number_format($precioFinal, 2) }}</td>
                    </tr>
                    @if ($anticipo > 0)
                        <tr class="hoja-saldo">
                            <td class="hoja-label">Saldo pendiente:</td>
                            <td class="right">${{ number_format($saldo, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- FECHAS Y ESTADO --}}
        @php
            $estaListo = $estado === 'Listo';
            $fechaReal = $orden['fecha_entrega_real'] ?? null;
            $usarFechaReal = $estaListo && !empty($fechaReal);
        @endphp

        <div class="hoja-seccion">
            <div class="hoja-grid-2">
                <div class="hoja-caja">
                    @if ($usarFechaReal)
                        <h3>FECHA DE ENTREGA</h3>
                        <p class="hoja-fecha-grande">
                            {{ \Carbon\Carbon::parse($fechaReal)->format('d/m/Y') }}
                        </p>
                        <p style="font-size: 8pt; color: #666; text-align: center; margin-top: 4px;">
                            Entregado el {{ \Carbon\Carbon::parse($fechaReal)->format('d/m/Y H:i') }}
                        </p>
                    @else
                        <h3>FECHA DE ENTREGA ESTIMADA</h3>
                        <p class="hoja-fecha-grande">
                            {{ isset($orden['fecha_entrega_estimada']) ? \Carbon\Carbon::parse($orden['fecha_entrega_estimada'])->format('d/m/Y') : '-' }}
                        </p>
                    @endif
                </div>
                <div class="hoja-caja">
                    <h3>ESTADO ACTUAL</h3>
                    <p class="hoja-fecha-grande">{{ $estado }}</p>
                </div>
            </div>
        </div>

        {{-- TÉRMINOS --}}
        <div class="hoja-seccion hoja-terminos">
            <p class="hoja-terminos-texto">
                <strong>Términos y condiciones:</strong> El taller no se hace responsable por objetos de valor dejados
                dentro del vehículo.
                El presupuesto puede variar si se encuentran fallas adicionales no contempladas en el diagnóstico
                inicial.
                El cliente autoriza la realización de los trabajos descritos en esta orden.
            </p>
        </div>

        {{-- FIRMAS --}}
        <div class="hoja-firmas">
            <div class="hoja-firma">
                <div class="hoja-firma-linea"></div>
                <p>Firma del Cliente</p>
            </div>
            <div class="hoja-firma">
                <div class="hoja-firma-linea"></div>
                <p>Firma del Taller</p>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="hoja-footer">
            <p>Documento generado por <strong>MecxiHub</strong> · {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <script>
        // Auto-abrir el diálogo de impresión al cargar (opcional)
        // Descomenta si quieres que se abra automáticamente:
        // window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    </script>
</body>

</html>
