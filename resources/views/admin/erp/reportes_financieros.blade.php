@extends('layouts.app')

@section('title', 'Reportes Financieros - Panel ERP')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.erp.contabilidad')])

        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Reportes Financieros</h1>
                <p class="text-sm text-gray-500 mt-1">Estado de Resultados, Balance General y Balanza de Comprobación.</p>
            </div>
            
            <form action="{{ route('admin.erp.contabilidad.reportes') }}" method="GET" class="flex gap-4 items-end bg-white p-3 rounded-xl shadow-sm border border-gray-100">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-1.5 rounded-lg text-sm font-bold hover:bg-hoverPrimary transition-all">Generar</button>
            </form>
        </div>

        @php
            $ingresos = 0;
            $gastos = 0;
            $activos = 0;
            $pasivos = 0;
            $capital = 0;

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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- ESTADO DE RESULTADOS -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-800 p-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-white">Estado de Resultados</h2>
                        <p class="text-xs text-gray-400">Del {{ $fecha_desde }} al {{ $fecha_hasta }}</p>
                    </div>
                    <a href="{{ route('admin.erp.contabilidad.reportes.pdf', ['tipo' => 'estado_resultados', 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta]) }}" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded flex items-center gap-1 transition-colors" target="_blank" title="Descargar PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> PDF
                    </a>
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Ingresos</h3>
                    @foreach($saldos as $s)
                        @if($s['cuenta']->tipo == 'ingreso')
                            <div class="flex justify-between py-1 text-sm">
                                <span>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</span>
                                <span class="font-mono text-gray-700">{{ number_format($s['saldo'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between py-2 font-bold text-emerald-600 mb-6 border-t mt-2">
                        <span>Total Ingresos</span>
                        <span class="font-mono">{{ number_format($ingresos, 2) }}</span>
                    </div>

                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Gastos y Costos</h3>
                    @foreach($saldos as $s)
                        @if($s['cuenta']->tipo == 'gasto' || $s['cuenta']->tipo == 'costo')
                            <div class="flex justify-between py-1 text-sm text-gray-600">
                                <span>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</span>
                                <span class="font-mono">{{ number_format($s['saldo'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between py-2 font-bold text-red-600 mb-6 border-t mt-2">
                        <span>Total Gastos</span>
                        <span class="font-mono">{{ number_format($gastos, 2) }}</span>
                    </div>

                    <div class="flex justify-between py-4 border-t-4 border-gray-800 text-lg font-black">
                        <span>Utilidad (Pérdida) Neta</span>
                        <span class="font-mono {{ $utilidad >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($utilidad, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- BALANCE GENERAL -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-800 p-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-white">Balance General</h2>
                        <p class="text-xs text-gray-400">Al {{ $fecha_hasta }}</p>
                    </div>
                    <a href="{{ route('admin.erp.contabilidad.reportes.pdf', ['tipo' => 'balance_general', 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta]) }}" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded flex items-center gap-1 transition-colors" target="_blank" title="Descargar PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> PDF
                    </a>
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Activos</h3>
                    @foreach($saldos as $s)
                        @if($s['cuenta']->tipo == 'activo')
                            <div class="flex justify-between py-1 text-sm">
                                <span>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</span>
                                <span class="font-mono text-gray-700">{{ number_format($s['saldo'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between py-2 font-bold text-blue-700 mb-6 border-t mt-2">
                        <span>Total Activos</span>
                        <span class="font-mono">{{ number_format($activos, 2) }}</span>
                    </div>

                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Pasivos</h3>
                    @foreach($saldos as $s)
                        @if($s['cuenta']->tipo == 'pasivo')
                            <div class="flex justify-between py-1 text-sm text-gray-600">
                                <span>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</span>
                                <span class="font-mono">{{ number_format($s['saldo'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between py-2 font-bold text-orange-600 mb-6 border-t mt-2">
                        <span>Total Pasivos</span>
                        <span class="font-mono">{{ number_format($pasivos, 2) }}</span>
                    </div>

                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Capital</h3>
                    @foreach($saldos as $s)
                        @if($s['cuenta']->tipo == 'capital')
                            <div class="flex justify-between py-1 text-sm text-gray-600">
                                <span>{{ $s['cuenta']->codigo }} - {{ $s['cuenta']->nombre }}</span>
                                <span class="font-mono">{{ number_format($s['saldo'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between py-1 text-sm text-gray-600">
                        <span class="italic">Utilidad del Periodo</span>
                        <span class="font-mono">{{ number_format($utilidad, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 font-bold text-purple-700 mb-6 border-t mt-2">
                        <span>Total Capital</span>
                        <span class="font-mono">{{ number_format($capital + $utilidad, 2) }}</span>
                    </div>

                    <div class="flex justify-between py-4 border-t-4 border-gray-800 text-lg font-black bg-gray-50 px-2 rounded">
                        <span>Total Pasivo + Capital</span>
                        <span class="font-mono {{ abs($activos - $totalPasivoCapital) < 0.01 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($totalPasivoCapital, 2) }}</span>
                    </div>
                    @if(abs($activos - $totalPasivoCapital) > 0.01)
                        <div class="text-xs text-red-500 font-bold mt-2 text-center bg-red-50 p-2 rounded">
                            ¡ALERTA! El Balance no cuadra. Diferencia: {{ number_format($activos - $totalPasivoCapital, 2) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- BALANZA DE COMPROBACIÓN -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Balanza de Comprobación</h2>
                    <p class="text-xs text-gray-500">Periodo: {{ $fecha_desde }} al {{ $fecha_hasta }}</p>
                </div>
                <a href="{{ route('admin.erp.contabilidad.reportes.pdf', ['tipo' => 'balanza_comprobacion', 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta]) }}" class="text-sm bg-primary hover:bg-hoverPrimary text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 transition-colors" target="_blank" title="Descargar PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Descargar PDF
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b">Código</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b">Cuenta</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right border-b">Débitos (Debe)</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right border-b">Créditos (Haber)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $sumaDebe = 0; $sumaHaber = 0; @endphp
                        @foreach($saldos as $s)
                            @php 
                                $sumaDebe += $s['debe']; 
                                $sumaHaber += $s['haber'];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 text-sm font-mono text-gray-500">{{ $s['cuenta']->codigo }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-gray-800">{{ $s['cuenta']->nombre }}</td>
                                <td class="px-6 py-3 text-sm text-right font-mono text-gray-600">{{ number_format($s['debe'], 2) }}</td>
                                <td class="px-6 py-3 text-sm text-right font-mono text-gray-600">{{ number_format($s['haber'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-800 text-white font-bold">
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-right">TOTALES</td>
                            <td class="px-6 py-4 text-right font-mono text-emerald-400">{{ number_format($sumaDebe, 2) }}</td>
                            <td class="px-6 py-4 text-right font-mono text-emerald-400">{{ number_format($sumaHaber, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
