<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Compra - Orden #{{ $compra->id_pago_compra }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-left {
            width: 50%;
            float: left;
        }
        .header-right {
            width: 50%;
            float: right;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #f58634; /* Brand Primary color */
            margin: 0;
        }
        .tagline {
            font-size: 10px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-meta {
            font-size: 10px;
            color: #64748b;
            margin: 2px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            vertical-align: top;
            padding: 4px 0;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
            width: 110px;
        }
        .info-value {
            color: #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            color: #475569;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .totals-table td {
            padding: 6px 8px;
            font-size: 10px;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 12px !important;
            border-top: 2px solid #cbd5e1;
            color: #f58634;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <p class="logo">CAMBIALORD</p>
                <p class="tagline">Intercambios y Compras Seguras</p>
                <p class="tagline">RNC: 132-45678-9 | Santo Domingo, RD</p>
                <p class="tagline">soporte@cambialord.com</p>
            </div>
            <div class="header-right">
                <p class="doc-title">Factura de Compra</p>
                <p class="doc-meta"><strong>Factura #:</strong> {{ Str::limit($compra->id_pago_compra, 8, '') }}</p>
                <p class="doc-meta"><strong>Fecha de emisión:</strong> {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y h:i A') }}</p>
                <p class="doc-meta">
                    <strong>Estado de Pago:</strong>
                    @if($compra->estatus === 'aprobado' || $compra->estatus === 'enviado' || $compra->estatus === 'entregado')
                        <span class="badge badge-success">Pagado</span>
                    @elseif($compra->estatus === 'pendiente')
                        <span class="badge badge-warning">Pendiente</span>
                    @else
                        <span class="badge badge-danger">{{ $compra->estatus }}</span>
                    @endif
                </p>
            </div>
            <div class="clear"></div>
        </div>

        <!-- CLIENTE & ENVIO -->
        <div class="section">
            <div class="section-title">Detalles de Facturación y Envío</div>
            <div class="header-left">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Cliente:</td>
                        <td class="info-value">
                            @if($compra->comprador)
                                {{ $compra->comprador->nombres }} {{ $compra->comprador->apellidos ?? '' }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Email:</td>
                        <td class="info-value">{{ $compra->comprador->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Teléfono:</td>
                        <td class="info-value">{{ $compra->comprador->telefono ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="header-right">
                <table class="info-table">
                    @if($compra->direccion)
                        @php $dir = $compra->direccion; @endphp
                        <tr>
                            <td class="info-label">Dirección:</td>
                            <td class="info-value">
                                {{ $dir->calle }}
                                @if($dir->N_casa_edificio) #{{ $dir->N_casa_edificio }}@endif
                                @if($dir->apto), Apto {{ $dir->apto }}@endif
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Sector/Ciudad:</td>
                            <td class="info-value">
                                {{ $dir->sector ?? 'N/A' }}, 
                                {{ $dir->municipio->municipio ?? $dir->id_municipio }}
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Provincia:</td>
                            <td class="info-value">{{ $dir->provincia->provincia ?? $dir->id_provincia }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="info-label">Dirección:</td>
                            <td class="info-value">No registrada (Retiro en tienda o entrega directa)</td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="clear"></div>
        </div>

        <!-- PAGO -->
        <div class="section">
            <div class="section-title">Información del Pago Realizado</div>
            <div class="header-left">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Método de Pago:</td>
                        <td class="info-value">Tarjeta de Crédito/Débito</td>
                    </tr>
                    @if($compra->tarjeta)
                        <tr>
                            <td class="info-label">Titular Tarjeta:</td>
                            <td class="info-value">{{ $compra->tarjeta->nombre_titular }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Tarjeta:</td>
                            <td class="info-value">{{ strtoupper($compra->tarjeta->tipo_tarjeta) }} ending in {{ $compra->tarjeta->last4 }}</td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="header-right">
                <table class="info-table">
                    @if($compra->autorizacion_pago)
                        <tr>
                            <td class="info-label">Cód. Autorización:</td>
                            <td class="info-value"><strong>{{ $compra->autorizacion_pago }}</strong></td>
                        </tr>
                    @endif
                    @if($compra->transaction_id)
                        <tr>
                            <td class="info-label">Ref. Transacción:</td>
                            <td class="info-value">{{ $compra->transaction_id }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="info-label">Proveedor de Pago:</td>
                        <td class="info-value">{{ $compra->proveedorPago->proveedor ?? 'CardNet' }}</td>
                    </tr>
                </table>
            </div>
            <div class="clear"></div>
        </div>

        <!-- ITEMS -->
        <div class="section">
            <div class="section-title">Detalle de la Compra</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">Descuento</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->pagoItems as $pi)
                    <tr>
                        <td>
                            <strong>{{ $pi->nombre_item }}</strong>
                            @if($pi->item && $pi->item->id_categoria_item == 29)
                                <span style="font-size: 8px; color: #6b21a8; background: #faf5ff; padding: 1px 4px; border-radius: 3px; border: 1px solid #f3e8ff; margin-left: 5px;">Servicio</span>
                            @endif
                        </td>
                        <td class="text-right">RD$ {{ number_format($pi->precio_unitario, 2) }}</td>
                        <td class="text-center">{{ $pi->cantidad }}</td>
                        <td class="text-right" style="color: #16a34a;">-RD$ {{ number_format($pi->descuento, 2) }}</td>
                        <td class="text-right">RD$ {{ number_format($pi->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- TOTALS -->
        <div>
            <table class="totals-table">
                <tr>
                    <td>Subtotal de productos:</td>
                    <td class="text-right">RD$ {{ number_format($compra->total - $compra->impuestos - $compra->costo_envio, 2) }}</td>
                </tr>
                @if((float) $compra->costo_envio > 0)
                <tr>
                    <td>Costo de Envío:</td>
                    <td class="text-right">RD$ {{ number_format($compra->costo_envio, 2) }}</td>
                </tr>
                @endif
                @if((float) $compra->impuestos > 0)
                <tr>
                    <td>Impuestos:</td>
                    <td class="text-right">RD$ {{ number_format($compra->impuestos, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL PAGADO:</td>
                    <td class="text-right">RD$ {{ number_format($compra->total, 2) }}</td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>Esta factura sirve como comprobante de pago de la orden #{!! $compra->id_pago_compra !!}.</p>
            <p>¡Gracias por elegir Cambialord! Si tienes alguna pregunta sobre esta factura, ponte en contacto con nuestro equipo de soporte.</p>
        </div>
    </div>

</body>
</html>
