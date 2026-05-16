<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Balanza de Comprobación</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f0f0f0; border-bottom: 2px solid #333; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .total-row { font-weight: bold; font-size: 13px; background-color: #e0e0e0; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Balanza de Comprobación</h1>
        <p>Periodo: {{ $fecha_desde }} al {{ $fecha_hasta }}</p>
        <p>Fecha de impresión: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Cuenta</th>
                <th class="text-right">Débitos (Debe)</th>
                <th class="text-right">Créditos (Haber)</th>
            </tr>
        </thead>
        <tbody>
            @php $sumaDebe = 0; $sumaHaber = 0; @endphp
            @foreach($saldos as $s)
                @php 
                    $sumaDebe += $s['debe']; 
                    $sumaHaber += $s['haber'];
                @endphp
                <tr>
                    <td>{{ $s['cuenta']->codigo }}</td>
                    <td>{{ $s['cuenta']->nombre }}</td>
                    <td class="text-right">{{ number_format($s['debe'], 2) }}</td>
                    <td class="text-right">{{ number_format($s['haber'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTALES</td>
                <td class="text-right">{{ number_format($sumaDebe, 2) }}</td>
                <td class="text-right">{{ number_format($sumaHaber, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
