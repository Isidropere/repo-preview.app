<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte ERP - Historial de {{ ucfirst($tab) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 15px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header-left {
            width: 60%;
            float: left;
        }
        .header-right {
            width: 40%;
            float: right;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #059669; /* Emerald brand accent for ERP */
            margin: 0;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            margin: 2px 0;
        }
        .doc-meta {
            font-size: 9px;
            color: #64748b;
            margin: 1px 0;
        }
        .filters-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .filters-title {
            font-weight: bold;
            color: #475569;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 15px;
            font-size: 9px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
            font-weight: bold;
            text-align: left;
            color: #334155;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 9px;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 4px;
            font-size: 8px;
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
        .badge-gray {
            background-color: #f1f5f9;
            color: #475569;
        }
        .total-box {
            float: right;
            margin-top: 15px;
            font-size: 11px;
            font-weight: bold;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 4px;
            color: #059669;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <p class="logo">CAMBIALORD ERP</p>
                <p class="doc-title">Reporte de Historial - {{ $tab === 'ventas' ? 'Ventas' : 'Intercambios' }}</p>
            </div>
            <div class="header-right">
                <p class="doc-meta"><strong>Fecha reporte:</strong> {{ date('d/m/Y h:i A') }}</p>
                <p class="doc-meta"><strong>Generado por:</strong> {{ auth()->user()->nombres }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <!-- FILTERS -->
        <div class="filters-box">
            <div class="filters-title">Filtros Aplicados</div>
            @if($buscar)
                <div class="filter-item"><strong>Búsqueda:</strong> "{{ $buscar }}"</div>
            @endif
            @if($estatus)
                <div class="filter-item"><strong>Estado:</strong> {{ ucfirst($estatus) }}</div>
            @endif
            @if($fecha_desde)
                <div class="filter-item"><strong>Desde:</strong> {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }}</div>
            @endif
            @if($fecha_hasta)
                <div class="filter-item"><strong>Hasta:</strong> {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}</div>
            @endif
            @if(!$buscar && !$estatus && !$fecha_desde && !$fecha_hasta)
                <div class="filter-item" style="color: #94a3b8;">Ninguno (Mostrando todos los registros finalizados)</div>
            @endif
        </div>

        <!-- TABLE -->
        @if($tab === 'ventas')
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 15%">Orden</th>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 25%">Cliente / Email</th>
                        <th style="width: 20%">Pago</th>
                        <th class="text-center" style="width: 10%">Estado</th>
                        <th class="text-right" style="width: 15%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse($data as $pago)
                        @php $grandTotal += $pago->total; @endphp
                        <tr>
                            <td class="font-mono">
                                @if(!empty($pago->is_talent_registration))
                                    {{ $pago->id_pago_compra }}
                                    <div style="font-size: 7px; color: #059669; font-weight: bold; margin-top: 2px;">REG. TALENTO</div>
                                @else
                                    #{{ Str::limit($pago->id_pago_compra, 8, '') }}
                                @endif
                            </td>
                            <td>{{ $pago->fecha ? $pago->fecha->format('d/m/Y h:i A') : '-' }}</td>
                            <td>
                                <strong>{{ $pago->carrito?->usuario?->nombres ?? 'Desconocido' }} {{ $pago->carrito?->usuario?->apellidos ?? '' }}</strong>
                                <div style="font-size: 8px; color: #64748b;">{{ $pago->carrito?->usuario?->email ?? '-' }}</div>
                                @if(!empty($pago->is_talent_registration))
                                    <div style="font-size: 8px; color: #0f766e; font-style: italic; margin-top: 2px;">Servicio: {{ $pago->talent_name }}</div>
                                @endif
                            </td>
                            <td>
                                @if($pago->azul_response)
                                    @php
                                        $azul = $pago->azul_response;
                                        $cardNumber = $azul['CardNumber'] ?? '';
                                        $brand = $azul['DataVaultBrand'] ?? '';
                                        if (empty($brand) && !empty($cardNumber)) {
                                            if (str_starts_with($cardNumber, '4')) {
                                                $brand = 'VISA';
                                            } elseif (str_starts_with($cardNumber, '5')) {
                                                $brand = 'MASTERCARD';
                                            } elseif (str_starts_with($cardNumber, '3')) {
                                                $brand = 'AMEX';
                                            }
                                        }
                                    @endphp
                                    @if($brand)
                                        <span style="font-weight: bold; color: #1e3a8a;">{{ strtoupper($brand) }}</span>
                                    @endif
                                    @if($cardNumber)
                                        <span class="font-mono">{{ $cardNumber }}</span>
                                    @endif
                                    @if(!empty($azul['AuthorizationCode']))
                                        <div style="font-size: 8px; color: #059669;">Aut: {{ $azul['AuthorizationCode'] }}</div>
                                    @endif
                                @elseif($pago->tarjeta)
                                    <span style="text-transform: capitalize;">{{ $pago->tarjeta->tipo_tarjeta }}</span> terminada en {{ $pago->tarjeta->last4 }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge 
                                    @if($pago->estatus === 'entregado') badge-success 
                                    @elseif($pago->estatus === 'enviado') badge-info 
                                    @else badge-warning @endif">
                                    {{ $pago->estatus }}
                                </span>
                            </td>
                            <td class="text-right font-mono" style="font-weight: bold;">
                                RD$ {{ number_format($pago->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">
                                No se encontraron registros de ventas con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if(count($data) > 0)
                <div class="total-box">
                    MONTO TOTAL: RD$ {{ number_format($grandTotal, 2) }}
                </div>
                <div class="clear"></div>
            @endif

        @else
            <!-- INTERCAMBIOS -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 25%">Participante Emisor</th>
                        <th style="width: 25%">Participante Receptor</th>
                        <th style="width: 15%">Monto Envío</th>
                        <th class="text-center" style="width: 10%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $neg)
                        <tr>
                            <td class="font-mono">#{{ $neg->id_negociacion }}</td>
                            <td>{{ $neg->fecha_creacion ? $neg->fecha_creacion->format('d/m/Y h:i A') : '-' }}</td>
                            <td>
                                <strong>{{ $neg->usuario?->nombres ?? 'Desconocido' }}</strong>
                                <div style="font-size: 8px; color: #64748b;">{{ $neg->usuario?->email ?? '-' }}</div>
                            </td>
                            <td>
                                <strong>{{ $neg->usuarioReceptor?->nombres ?? 'Desconocido' }}</strong>
                                <div style="font-size: 8px; color: #64748b;">{{ $neg->usuarioReceptor?->email ?? '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $pagoEmisorObj = $neg->pagoEnvios->firstWhere('id_user', $neg->usuario_emisor_id);
                                    $pagoReceptorObj = $neg->pagoEnvios->firstWhere('id_user', $neg->usuario_receptor_id);
                                @endphp
                                <div style="font-size: 8px; line-height: 1.2;">
                                    Emisor: 
                                    @if($pagoEmisorObj && $pagoEmisorObj->estado === 'pagado')
                                        <strong>RD$ {{ number_format($pagoEmisorObj->monto, 2) }}</strong>
                                    @elseif($pagoEmisorObj && $pagoEmisorObj->estado === 'pagado_pull')
                                        <strong>Pull</strong>
                                    @else
                                        <span style="color:#94a3b8;">Pend.</span>
                                    @endif
                                    <br>
                                    Receptor: 
                                    @if($pagoReceptorObj && $pagoReceptorObj->estado === 'pagado')
                                        <strong>RD$ {{ number_format($pagoReceptorObj->monto, 2) }}</strong>
                                    @elseif($pagoReceptorObj && $pagoReceptorObj->estado === 'pagado_pull')
                                        <strong>Pull</strong>
                                    @else
                                        <span style="color:#94a3b8;">Pend.</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge 
                                    @if($neg->estado === 'completado') badge-success 
                                    @elseif($neg->estado === 'en_envio') badge-info 
                                    @else badge-warning @endif">
                                    {{ $neg->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">
                                No se encontraron registros de intercambios con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <!-- FOOTER -->
        <div class="footer">
            <p>Reporte interno generado automáticamente por el Módulo de Control de Gestión Empresarial (ERP) Cambialord.</p>
        </div>
    </div>

</body>
</html>
