<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Intercambio - #{{ $hashedId }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #7c3aed; /* Purple brand color */
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #f3f4f6;
            padding: 5px 10px;
            margin-bottom: 8px;
            border-left: 4px solid #7c3aed;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            vertical-align: top;
            padding: 4px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
            width: 110px;
        }
        .half-width {
            width: 48%;
            float: left;
        }
        .clear {
            clear: both;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .table th, .table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        .table th {
            background: #f9fafb;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
            background: #e0e7ff;
            color: #3730a3;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Hoja de Intercambio</h1>
        <p>Intercambio #{{ $hashedId }} | Fecha: {{ $intercambio->fecha_creacion ? \Carbon\Carbon::parse($intercambio->fecha_creacion)->format('d/m/Y H:i') : 'N/A' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Información General</div>
        <table class="grid">
            <tr>
                <td class="label">Estado:</td>
                <td><span class="badge">{{ strtoupper($intercambio->estado) }}</span></td>
                <td class="label">Modo de Entrega:</td>
                <td>{{ ucfirst($intercambio->modo_entrega ?? 'No especificado') }}</td>
            </tr>
            @if($intercambio->tracking_code)
            <tr>
                <td class="label">Código Rastreo:</td>
                <td><code>{{ $intercambio->tracking_code }}</code></td>
                <td class="label">URL Rastreo:</td>
                <td>{{ $intercambio->tracking_url }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="section-title">Participantes</div>
        <div class="half-width" style="margin-right: 4%;">
            <h4 style="margin: 0 0 5px 0; color:#7c3aed;">EMISOR (Propone)</h4>
            <table class="grid" style="font-size: 10px;">
                <tr>
                    <td class="label" style="width: 60px;">Nombre:</td>
                    <td>{{ $intercambio->usuario?->nombres }} {{ $intercambio->usuario?->apellidos }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 60px;">Email:</td>
                    <td>{{ $intercambio->usuario?->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 60px;">Teléfono:</td>
                    <td>{{ $intercambio->usuario?->telefono ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        <div class="half-width">
            <h4 style="margin: 0 0 5px 0; color:#7c3aed;">RECEPTOR (Recibe)</h4>
            <table class="grid" style="font-size: 10px;">
                <tr>
                    <td class="label" style="width: 60px;">Nombre:</td>
                    <td>{{ $intercambio->usuarioReceptor?->nombres }} {{ $intercambio->usuarioReceptor?->apellidos }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 60px;">Email:</td>
                    <td>{{ $intercambio->usuarioReceptor?->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label" style="width: 60px;">Teléfono:</td>
                    <td>{{ $intercambio->usuarioReceptor?->telefono ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <div class="section-title">Flujo de Intercambio</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%;">Artículo / Servicio Solicitado (Receptor)</th>
                    <th style="width: 50%;">Artículo / Servicio Ofrecido (Emisor)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $intercambio->item?->item ?? 'Eliminado' }}</strong><br>
                        Valor estimado: RD$ {{ number_format($intercambio->item?->valor ?? 0, 2) }}
                    </td>
                    <td>
                        @if($itemsOfrecidos->count())
                            @foreach($itemsOfrecidos as $io)
                            @php $qtyPdf = $intercambio->getCantidadOfrecida($io->id_item); @endphp
                                • {{ $io->item }}@if($qtyPdf > 1) (× {{ $qtyPdf }})@endif (RD$ {{ number_format($io->valor ?? 0, 2) }})<br>
                            @endforeach
                        @else
                            • Solo oferta económica
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="table" style="margin-top: 10px; width: 50%; float: right;">
            <tr>
                <td class="label" style="width: 150px; text-align: right; border: none;">Monto de oferta económica:</td>
                <td style="font-weight: bold; border: none; text-align: right;">RD$ {{ number_format($intercambio->monto_oferta ?? 0, 2) }}</td>
            </tr>
            @if($intercambio->monto_contra_oferta)
            <tr>
                <td class="label" style="width: 150px; text-align: right; border: none; color:#b91c1c;">Monto de contraoferta:</td>
                <td style="font-weight: bold; border: none; text-align: right; color:#b91c1c;">RD$ {{ number_format($intercambio->monto_contra_oferta, 2) }}</td>
            </tr>
            @endif
        </table>
        <div class="clear"></div>
    </div>

    @php
        $dirEmisor = $intercambio->usuario?->direcciones->firstWhere('es_predeterminada', 1) ?? $intercambio->usuario?->direcciones->first();
        $dirReceptor = $intercambio->usuarioReceptor?->direcciones->firstWhere('es_predeterminada', 1) ?? $intercambio->usuarioReceptor?->direcciones->first();
    @endphp
    @if($dirEmisor || $dirReceptor)
    <div class="section">
        <div class="section-title">Direcciones de Envío</div>
        <div class="half-width" style="margin-right: 4%;">
            <h4 style="margin: 0 0 5px 0;">Emisor (Recibe Solicitado)</h4>
            @if($dirEmisor)
                <p style="margin: 0; line-height: 1.3; font-size: 10px;">
                    Calle: {{ $dirEmisor->calle }}@if($dirEmisor->N_casa_edificio) #{{ $dirEmisor->N_casa_edificio }}@endif @if($dirEmisor->apto), Apto {{ $dirEmisor->apto }}@endif<br>
                    Sector: {{ $dirEmisor->sector ?? 'N/A' }}<br>
                    Municipio: {{ $dirEmisor->municipio->municipio ?? $dirEmisor->id_municipio }}<br>
                    Provincia: {{ $dirEmisor->provincia->provincia ?? $dirEmisor->id_provincia }}<br>
                    Tel. contacto: {{ $dirEmisor->telefono_contacto ?? 'N/A' }}
                </p>
            @else
                <p style="margin: 0; color: #999; font-size: 10px;">Sin dirección registrada</p>
            @endif
        </div>
        <div class="half-width">
            <h4 style="margin: 0 0 5px 0;">Receptor (Recibe Ofrecidos)</h4>
            @if($dirReceptor)
                <p style="margin: 0; line-height: 1.3; font-size: 10px;">
                    Calle: {{ $dirReceptor->calle }}@if($dirReceptor->N_casa_edificio) #{{ $dirReceptor->N_casa_edificio }}@endif @if($dirReceptor->apto), Apto {{ $dirReceptor->apto }}@endif<br>
                    Sector: {{ $dirReceptor->sector ?? 'N/A' }}<br>
                    Municipio: {{ $dirReceptor->municipio->municipio ?? $dirReceptor->id_municipio }}<br>
                    Provincia: {{ $dirReceptor->provincia->provincia ?? $dirReceptor->id_provincia }}<br>
                    Tel. contacto: {{ $dirReceptor->telefono_contacto ?? 'N/A' }}
                </p>
            @else
                <p style="margin: 0; color: #999; font-size: 10px;">Sin dirección registrada</p>
            @endif
        </div>
        <div class="clear"></div>
    </div>
    @endif

    @php
        $pagoEmisorObj = $intercambio->pagoEnvios->firstWhere('id_user', $intercambio->usuario_emisor_id);
        $pagoReceptorObj = $intercambio->pagoEnvios->firstWhere('id_user', $intercambio->usuario_receptor_id);
    @endphp
    @if($pagoEmisorObj || $pagoReceptorObj)
    <div class="section">
        <div class="section-title">Información de Pago de Envíos</div>
        <div class="half-width" style="margin-right: 4%;">
            <h4 style="margin: 0 0 5px 0;">Pago del Emisor</h4>
            @if($pagoEmisorObj)
                <table class="grid" style="font-size: 9px;">
                    <tr>
                        <td class="label" style="width: 70px;">Monto:</td>
                        <td>RD$ {{ number_format($pagoEmisorObj->monto, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="width: 70px;">Estado:</td>
                        <td>{{ strtoupper($pagoEmisorObj->estado) }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="width: 70px;">Canal:</td>
                        <td>{{ $pagoEmisorObj->tipo_pago === 'pull' ? 'Descuento de Pull' : 'Tarjeta' }}</td>
                    </tr>
                    @if($pagoEmisorObj->transaction_id)
                    <tr>
                        <td class="label" style="width: 70px;">Transacción:</td>
                        <td>{{ $pagoEmisorObj->transaction_id }}</td>
                    </tr>
                    @endif
                </table>
            @else
                <p style="margin: 0; color: #999; font-size: 10px;">Pago pendiente / No registrado</p>
            @endif
        </div>
        <div class="half-width">
            <h4 style="margin: 0 0 5px 0;">Pago del Receptor</h4>
            @if($pagoReceptorObj)
                <table class="grid" style="font-size: 9px;">
                    <tr>
                        <td class="label" style="width: 70px;">Monto:</td>
                        <td>RD$ {{ number_format($pagoReceptorObj->monto, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="width: 70px;">Estado:</td>
                        <td>{{ strtoupper($pagoReceptorObj->estado) }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="width: 70px;">Canal:</td>
                        <td>{{ $pagoReceptorObj->tipo_pago === 'pull' ? 'Descuento de Pull' : 'Tarjeta' }}</td>
                    </tr>
                    @if($pagoReceptorObj->transaction_id)
                    <tr>
                        <td class="label" style="width: 70px;">Transacción:</td>
                        <td>{{ $pagoReceptorObj->transaction_id }}</td>
                    </tr>
                    @endif
                </table>
            @else
                <p style="margin: 0; color: #999; font-size: 10px;">Pago pendiente / No registrado</p>
            @endif
        </div>
        <div class="clear"></div>
    </div>
    @endif

    <div class="footer">
        Generado el {{ date('d/m/Y H:i:s') }} - Cambialord Admin
    </div>

</body>
</html>
