<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos de Envío - Orden #{{ $compra->id_pago_compra }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1a1a1a;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background: #f4f4f4;
            padding: 5px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }
        .grid {
            width: 100%;
        }
        .grid td {
            vertical-align: top;
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
            width: 120px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background: #f9f9f9;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            background: #eee;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Hoja de Envío</h1>
        <p>Orden #{{ $compra->id_pago_compra }} | Fecha: {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Datos del Comprador</div>
        <table class="grid">
            <tr>
                <td class="label">Nombre:</td>
                <td>
                    @if($compra->comprador)
                        {{ $compra->comprador->nombres }} {{ $compra->comprador->apellidos ?? '' }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="label">Email:</td>
                <td>{{ $compra->comprador->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Teléfono:</td>
                <td>{{ $compra->comprador->telefono ?? 'N/A' }}</td>
                <td class="label">Estado Orden:</td>
                <td><span class="badge">{{ strtoupper($compra->estatus) }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Puntos de Recogida (Vendedores)</div>
        @php
            $sellersList = collect();
            foreach($compra->pagoItems as $pi) {
                if ($pi->item && $pi->item->usuario) {
                    $seller = $pi->item->usuario;
                    if (!$sellersList->has($seller->id)) {
                        $dir = $seller->direcciones->first();
                        $sellersList->put($seller->id, [
                            'seller' => $seller,
                            'dir' => $dir
                        ]);
                    }
                }
            }
        @endphp

        @if($sellersList->count() > 0)
            @foreach($sellersList as $data)
                @php 
                    $seller = $data['seller'];
                    $dir = $data['dir']; 
                @endphp
                <p style="margin: 5px 0; font-weight:bold; color: #444;">Vendedor: {{ $seller->nombres }} {{ $seller->apellidos ?? '' }} (Tel: {{ $seller->telefono ?? 'N/A' }})</p>
                @if($dir)
                <table class="grid" style="margin-bottom: 10px;">
                    <tr>
                        <td class="label">Calle:</td>
                        <td colspan="3">
                            {{ $dir->calle }}
                            @if($dir->N_casa_edificio) #{{ $dir->N_casa_edificio }}@endif
                            @if($dir->apto), Apto {{ $dir->apto }}@endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Sector:</td>
                        <td>{{ $dir->sector ?? 'N/A' }}</td>
                        <td class="label">Municipio:</td>
                        <td>{{ $dir->municipio->municipio ?? $dir->id_municipio ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Provincia:</td>
                        <td>{{ $dir->provincia->provincia ?? $dir->id_provincia ?? 'N/A' }}</td>
                        <td class="label">Referencia:</td>
                        <td>{{ $dir->referencia ?? 'N/A' }}</td>
                    </tr>
                </table>
                @else
                <p style="font-style: italic; color: #999; margin-bottom: 10px;">El vendedor no tiene dirección registrada.</p>
                @endif
            @endforeach
        @else
            <p>No se encontraron vendedores para esta orden.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Dirección de Entrega (Comprador)</div>
        @if($compra->direccion)
            @php $dir = $compra->direccion; @endphp
            <table class="grid">
                <tr>
                    <td class="label">Calle:</td>
                    <td colspan="3">
                        {{ $dir->calle }}
                        @if($dir->N_casa_edificio) #{{ $dir->N_casa_edificio }}@endif
                        @if($dir->apto), Apto {{ $dir->apto }}@endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Sector:</td>
                    <td>{{ $dir->sector ?? 'N/A' }}</td>
                    <td class="label">Municipio:</td>
                    <td>{{ $dir->municipio->municipio ?? $dir->id_municipio }}</td>
                </tr>
                <tr>
                    <td class="label">Provincia:</td>
                    <td>{{ $dir->provincia->provincia ?? $dir->id_provincia }}</td>
                    <td class="label">Tel. Contacto:</td>
                    <td>{{ $dir->telefono_contacto ?? 'N/A' }}</td>
                </tr>
            </table>
        @else
            <p>No se registró dirección de envío para esta orden.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Detalle de Productos y Dimensiones</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Dimensiones (cm)</th>
                    <th>Peso (lbs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compra->pagoItems as $pi)
                <tr>
                    <td>{{ $pi->nombre_item }}</td>
                    <td>{{ $pi->cantidad }}</td>
                    <td>
                        @if($pi->item)
                            {{ $pi->item->ancho_cm ?? 0 }} x {{ $pi->item->alto_cm ?? 0 }} x {{ $pi->item->profundo_cm ?? 0 }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $pi->item->peso_lbs ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" style="text-align: right;">TOTAL ORDEN:</th>
                    <th colspan="2" style="text-align: left; background: #eee; font-size: 14px;">
                        RD$ {{ number_format($compra->total, 2) }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Generado el {{ date('d/m/Y H:i:s') }} - Cambialord Admin
    </div>

</body>
</html>
