<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Resultados</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #666; }
        table { w-full; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f9f9f9; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 15px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .total-row { font-weight: bold; font-size: 13px; background-color: #f0f0f0; }
        .net-row { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estado de Resultados</h1>
        <p>Periodo: {{ $fecha_desde }} al {{ $fecha_hasta }}</p>
        <p>Fecha de impresión: {{ date('Y-m-d H:i') }}</p>
    </div>

    @php
        $ingresos = 0;
        $gastos = 0;
        foreach($saldos as $s) {
            if ($s['cuenta']->tipo == 'ingreso') $ingresos += $s['saldo'];
            if ($s['cuenta']->tipo == 'gasto' || $s['cuenta']->tipo == 'costo') $gastos += $s['saldo'];
        }
        $utilidad = $ingresos - $gastos;
    @endphp

    <div class="section-title">INGRESOS</div>
    <table>
        <tbody>
            @foreach($saldos as $s)
                @if($s['cuenta']->tipo == 'ingreso')
                    <tr>
                        <td>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</td>
                        <td class="text-right">{{ number_format($s['saldo'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL INGRESOS</td>
                <td class="text-right">{{ number_format($ingresos, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">GASTOS Y COSTOS</div>
    <table>
        <tbody>
            @foreach($saldos as $s)
                @if($s['cuenta']->tipo == 'gasto' || $s['cuenta']->tipo == 'costo')
                    <tr>
                        <td>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</td>
                        <td class="text-right">{{ number_format($s['saldo'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL GASTOS Y COSTOS</td>
                <td class="text-right">{{ number_format($gastos, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tbody>
            <tr class="net-row">
                <td>UTILIDAD (PÉRDIDA) NETA</td>
                <td class="text-right">{{ number_format($utilidad, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
