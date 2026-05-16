<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Balance General</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
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
        <h1>Balance General</h1>
        <p>Al {{ $fecha_hasta }}</p>
        <p>Fecha de impresión: {{ date('Y-m-d H:i') }}</p>
    </div>

    @php
        $ingresos = 0; $gastos = 0;
        $activos = 0; $pasivos = 0; $capital = 0;

        foreach($saldos as $s) {
            if ($s['cuenta']->tipo == 'ingreso') $ingresos += $s['saldo'];
            if ($s['cuenta']->tipo == 'gasto' || $s['cuenta']->tipo == 'costo') $gastos += $s['saldo'];
            if ($s['cuenta']->tipo == 'activo') $activos += $s['saldo'];
            if ($s['cuenta']->tipo == 'pasivo') $pasivos += $s['saldo'];
            if ($s['cuenta']->tipo == 'capital') $capital += $s['saldo'];
        }

        $utilidad = $ingresos - $gastos;
        $totalPasivoCapital = $pasivos + $capital + $utilidad;
    @endphp

    <div class="section-title">ACTIVOS</div>
    <table>
        <tbody>
            @foreach($saldos as $s)
                @if($s['cuenta']->tipo == 'activo')
                    <tr>
                        <td>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</td>
                        <td class="text-right">{{ number_format($s['saldo'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL ACTIVOS</td>
                <td class="text-right">{{ number_format($activos, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">PASIVOS</div>
    <table>
        <tbody>
            @foreach($saldos as $s)
                @if($s['cuenta']->tipo == 'pasivo')
                    <tr>
                        <td>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</td>
                        <td class="text-right">{{ number_format($s['saldo'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL PASIVOS</td>
                <td class="text-right">{{ number_format($pasivos, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">CAPITAL</div>
    <table>
        <tbody>
            @foreach($saldos as $s)
                @if($s['cuenta']->tipo == 'capital')
                    <tr>
                        <td>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</td>
                        <td class="text-right">{{ number_format($s['saldo'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td><em>Utilidad (Pérdida) del Periodo</em></td>
                <td class="text-right">{{ number_format($utilidad, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL CAPITAL</td>
                <td class="text-right">{{ number_format($capital + $utilidad, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tbody>
            <tr class="net-row">
                <td>TOTAL PASIVO Y CAPITAL</td>
                <td class="text-right">{{ number_format($totalPasivoCapital, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
