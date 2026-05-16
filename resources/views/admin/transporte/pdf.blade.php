<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Transporte #{{ $solicitud->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { border-bottom: 2px solid #004085; padding-bottom: 20px; margin-bottom: 20px; }
        .logo-text { font-size: 24px; font-weight: bold; color: #004085; }
        .title { text-align: center; font-size: 20px; margin: 20px 0; text-transform: uppercase; color: #333; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px; background: #f9f9f9; }
        .info-title { font-weight: bold; font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; color: #004085; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { width: 65%; }
        .badge { display: inline-block; padding: 3px 8px; font-size: 12px; font-weight: bold; color: #fff; background-color: #f0ad4e; border-radius: 10px; }
        .badge.aprobada { background-color: #28a745; }
        .badge.rechazada { background-color: #dc3545; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="logo-text">CAMBIALORD</div>
                    <div style="font-size: 12px; color: #777;">Plataforma de Intercambios y Ventas</div>
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
        <div class="info-title">Estado Actual</div>
        <table>
            <tr>
                <td class="label">Estado de la Solicitud:</td>
                <td class="value">
                    <span class="badge {{ strtolower($solicitud->estado) }}">{{ strtoupper($solicitud->estado) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Fecha Solicitada del Servicio:</td>
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

    <div class="info-box">
        <div class="info-title">Detalles de la Carga y Ubicación</div>
        <table>
            <tr>
                <td class="label">Dirección / Referencia:</td>
                <td class="value">{{ $solicitud->direccion }}</td>
            </tr>
            <tr>
                <td class="label">Coordenadas GPS:</td>
                <td class="value">{{ $solicitud->ubicacion_geologica ?: 'No proporcionadas' }}</td>
            </tr>
            <tr>
                <td class="label" style="padding-top: 15px;">Dimensiones y Descripción de la Carga:</td>
                <td class="value"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="background: #fff; border: 1px solid #ccc; padding: 10px; border-radius: 4px; min-height: 80px; margin-top: 5px; white-space: pre-wrap;">{{ $solicitud->dimensiones_carga }}</div>
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
