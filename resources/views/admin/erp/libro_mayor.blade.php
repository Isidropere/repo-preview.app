@extends('layouts.admin')

@section('title', 'Libro Mayor - Panel ERP')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.erp.contabilidad')])

        <div class="mb-6 flex flex-wrap justify-between items-end gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Libro Mayor</h1>
                <p class="text-sm text-gray-500 mt-1">Cuenta: <span class="font-bold text-gray-800">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</span></p>
                <p class="text-xs text-gray-400 mt-0.5">Tipo: <span class="uppercase font-semibold text-gray-600">{{ $cuenta->tipo }}</span></p>
            </div>
            
            <form action="{{ route('admin.erp.contabilidad.cuentas.mayor', $cuenta->id) }}" method="GET" class="flex gap-4 items-end bg-white p-3 rounded-xl shadow-sm border border-gray-100">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-gray-900 transition-colors">Filtrar</button>
                    <a href="{{ route('admin.erp.contabilidad.cuentas.mayor', $cuenta->id) }}" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider border-b border-gray-700">Fecha</th>
                            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider border-b border-gray-700">Asiento #</th>
                            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider border-b border-gray-700">Concepto General</th>
                            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right border-b border-gray-700">Debe</th>
                            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right border-b border-gray-700">Haber</th>
                            <th class="px-6 py-3 text-xs font-bold uppercase tracking-wider text-right border-b border-gray-700">Saldo Acumulado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php 
                            $saldoAcumulado = 0; 
                            $totalDebe = 0;
                            $totalHaber = 0;
                        @endphp
                        @forelse($detalles as $det)
                            @php
                                $totalDebe += $det->debe;
                                $totalHaber += $det->haber;

                                // La lógica del saldo depende del tipo de cuenta
                                if (in_array($cuenta->tipo, ['activo', 'gasto', 'costo'])) {
                                    $saldoAcumulado += ($det->debe - $det->haber);
                                } else {
                                    // pasivo, capital, ingreso aumentan por el haber
                                    $saldoAcumulado += ($det->haber - $det->debe);
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $det->diario->fecha }}</td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                    <a href="{{ route('admin.erp.contabilidad', ['referencia_id' => $det->diario->id]) }}" class="text-primary hover:underline">
                                        #{{ str_pad($det->diario->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-800">{{ $det->diario->concepto }}</p>
                                    @if($det->nota)
                                        <p class="text-xs text-gray-500 mt-1 italic">{{ $det->nota }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-mono text-gray-600">{{ $det->debe > 0 ? number_format($det->debe, 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-right font-mono text-gray-600">{{ $det->haber > 0 ? number_format($det->haber, 2) : '-' }}</td>
                                <td class="px-6 py-4 text-sm text-right font-mono font-bold {{ $saldoAcumulado < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($saldoAcumulado, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    No hay movimientos registrados para esta cuenta en el periodo seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($detalles->count() > 0)
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-700">TOTALES DEL PERIODO</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-gray-800">{{ number_format($totalDebe, 2) }}</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-gray-800">{{ number_format($totalHaber, 2) }}</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-emerald-600 text-lg border-l-2 border-gray-200">
                                {{ number_format($saldoAcumulado, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
