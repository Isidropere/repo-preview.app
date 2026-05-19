<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Transporte #{{ $solicitud->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { border-bottom: 2px solid #004085; padding-bottom: 15px; margin-bottom: 15px; }
        .logo-text { font-size: 24px; font-weight: bold; color: #004085; }
        .title { text-align: center; font-size: 18px; margin: 15px 0; text-transform: uppercase; color: #333; font-weight: bold; }
        .info-box { border: 1px solid #ddd; padding: 12px; margin-bottom: 15px; border-radius: 6px; background: #f9f9f9; page-break-inside: avoid; }
        .info-title { font-weight: bold; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 8px; color: #004085; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; vertical-align: top; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { width: 65%; }
        .badge { display: inline-block; padding: 3px 8px; font-size: 11px; font-weight: bold; color: #fff; background-color: #f0ad4e; border-radius: 10px; text-transform: uppercase; }
        .badge.aprobada { background-color: #28a745; }
        .badge.rechazada { background-color: #dc3545; }
        
        /* Estilos de la tabla de artículos */
        .articulos-table { width: 100%; border: 1px solid #ddd; border-collapse: collapse; margin-top: 5px; }
        .articulos-table th { padding: 6px 10px; background-color: #f2f2f2; font-size: 11px; font-weight: bold; color: #004085; border: 1px solid #ddd; text-align: left; text-transform: uppercase; }
        .articulos-table td { padding: 6px 10px; font-size: 11px; border: 1px solid #ddd; }
        .articulos-table tr:nth-child(even) { background-color: #fcfcfc; }
        
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 8px; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="logo-text">CAMBIALORD</div>
                    <div style="font-size: 11px; color: #777;">Plataforma de Intercambios y Ventas</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Fecha Emisión:</strong> {{ date('d/m/Y h:i A') }}<br>
                    <strong>Solicitud No.:</strong> #{{ str_pad($solicitud->id, 5, '0', STR_PAD_LEFT) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="title">
        Detalles de Solicitud de Transporte/Mudanza
    </div>

    <div class="info-box">
        <div class="info-title">Estado y Tipo de Servicio</div>
        <table>
            <tr>
                <td class="label">Estado de la Solicitud:</td>
                <td class="value">
                    <span class="badge {{ strtolower($solicitud->estado) }}">{{ $solicitud->estado }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Tipo de Servicio:</td>
                <td class="value">
                    <strong style="color: {{ $solicitud->tipo_servicio == 'mudanza' ? '#6b21a8' : '#1e1b4b' }}; text-transform: uppercase;">
                        {{ $solicitud->tipo_servicio == 'mudanza' ? 'Mudanza Residencial / Comercial' : 'Transporte de Carga' }}
                    </strong>
                </td>
            </tr>
            <tr>
                <td class="label">Fecha Solicitada:</td>
                <td class="value">{{ $solicitud->fecha_servicio->format('d de F, Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="info-box">
        <div class="info-title">Datos del Cliente</div>
        <table>
            <tr>
                <td class="label">Nombre Completo:</td>
                <td class="value">{{ $solicitud->nombre }} {{ $solicitud->apellido }}</td>
            </tr>
            <tr>
                <td class="label">Cédula:</td>
                <td class="value">{{ $solicitud->cedula }}</td>
            </tr>
            <tr>
                <td class="label">Teléfono:</td>
                <td class="value">{{ $solicitud->telefono }}</td>
            </tr>
            <tr>
                <td class="label">Correo Electrónico:</td>
                <td class="value">{{ $solicitud->correo }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Usuario:</td>
                <td class="value">{{ $solicitud->id_usuario ? 'Registrado en plataforma' : 'Usuario Invitado' }}</td>
            </tr>
        </table>
    </div>

    <!-- Artículos Declarados -->
    @if(count($solicitud->articulos) > 0)
    <div class="info-box">
        <div class="info-title">Artículos Declarados en el Checklist</div>
        <table class="articulos-table">
            <thead>
                <tr>
                    <th>Nombre del Artículo</th>
                    <th style="width: 150px;">Categoría de Servicio</th>
                    <th style="width: 80px; text-align: center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitud->articulos as $art)
                    <tr>
                        <td>{{ $art->nombre }}</td>
                        <td style="text-transform: capitalize; color: #666;">
                            {{ $art->categoria == 'ambos' ? 'Ambos servicios' : $art->categoria }}
                        </td>
                        <td style="text-align: center; font-weight: bold; color: #004085;">
                            {{ $art->pivot->cantidad }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="info-box">
        <div class="info-title">Detalles de Carga y Ubicación</div>
        <table>
            <tr>
                <td class="label">Dirección Principal:</td>
                <td class="value">{{ $solicitud->direccion }}</td>
            </tr>
            <tr>
                <td class="label">Coordenadas GPS:</td>
                <td class="value">{{ $solicitud->ubicacion_geologica ?: 'No proporcionadas' }}</td>
            </tr>
            <tr>
                <td class="label" style="padding-top: 10px;">Dimensiones y Detalles Adicionales:</td>
                <td class="value"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="background: #fff; border: 1px solid #ccc; padding: 8px; border-radius: 4px; min-height: 60px; margin-top: 4px; white-space: pre-wrap; font-size: 11px;">{{ $solicitud->dimensiones_carga }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Este documento es un comprobante interno generado por la plataforma CambialoRD. <br>
        Generado automáticamente por el administrador.
    </div>

</body>
</html>
