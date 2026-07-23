@extends('layouts.admin')

@section('title', 'Caja - Panel ERP')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cuadre de Caja</h1>
                <p class="text-sm text-gray-500 mt-1">Conciliación de transacciones electrónicas (tarjeta) del período actual.</p>
            </div>
            <button onclick="document.getElementById('modalCerrarCaja').style.display='flex'"
                class="bg-red-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-red-700 transition-all transform hover:scale-105 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                Realizar Arqueo
            </button>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-xl text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- Indicador de caja abierta --}}
        <div class="flex items-center gap-2 mb-5">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-sm font-bold text-green-600 uppercase tracking-wider">Período Activo</span>
            <span class="text-xs text-gray-400">
                desde {{ $sesionAbierta->fecha_apertura ? \Carbon\Carbon::parse($sesionAbierta->fecha_apertura)->format('d/m/Y H:i') : '---' }}
            </span>
        </div>

        {{-- Resumen de Transacciones Electrónicas del Período --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

            {{-- Ventas por tarjeta --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-blue-50 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Ventas</p>
                </div>
                <p class="text-xl font-black text-gray-800">RD$ {{ number_format($resumenPeriodo['ventas_tarjeta']['monto'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $resumenPeriodo['ventas_tarjeta']['cant'] }} transacciones · tarjeta</p>
            </div>

            {{-- Envíos de intercambio --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-indigo-50 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Intercambios</p>
                </div>
                <p class="text-xl font-black text-gray-800">RD$ {{ number_format($resumenPeriodo['envios_intercambio']['monto'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $resumenPeriodo['envios_intercambio']['cant'] }} pagos de envío · tarjeta</p>
            </div>

            {{-- Talentos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-purple-50 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Talentos</p>
                </div>
                <p class="text-xl font-black text-gray-800">RD$ {{ number_format($resumenPeriodo['talentos']['monto'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $resumenPeriodo['talentos']['cant'] }} registros · tarjeta</p>
            </div>

            {{-- Total del Período --}}
            <div class="bg-primary rounded-2xl shadow-sm p-6 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-white/70 font-bold uppercase tracking-wide">Total Recaudado</p>
                </div>
                <p class="text-2xl font-black">RD$ {{ number_format($resumenPeriodo['total'], 2) }}</p>
                <p class="text-xs text-white/60 mt-1">100% transacciones electrónicas</p>
            </div>
        </div>

        {{-- Nota informativa --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-4 mb-8 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-blue-800">Conciliación Electrónica</p>
                <p class="text-xs text-blue-600 mt-0.5">
                    Todas las transacciones son procesadas por <strong>CardNet</strong>. El arqueo compara lo registrado en el sistema
                    con el reporte de tu procesador de pagos. No se maneja efectivo físico.
                </p>
            </div>
        </div>

        {{-- Filtros del Historial --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <form method="GET" action="{{ route('admin.erp.caja') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex flex-col gap-1 min-w-[160px]">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="flex flex-col gap-1 min-w-[160px]">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="flex flex-col gap-1 min-w-[200px]">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Realizado por</label>
                    <select name="admin_id"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white">
                        <option value="">— Todos los administradores —</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->nombres }} {{ $admin->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-hoverPrimary transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filtrar
                    </button>
                    @if(request('desde') || request('hasta') || request('admin_id'))
                        <a href="{{ route('admin.erp.caja') }}"
                            class="px-4 py-2 border border-gray-200 text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-50 transition-all flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            {{-- Resumen del filtro activo --}}
            @if(request('desde') || request('hasta') || request('admin_id'))
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-gray-400 font-semibold">Filtros activos:</span>
                    @if(request('desde'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                            Desde: {{ \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') }}
                        </span>
                    @endif
                    @if(request('hasta'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                            Hasta: {{ \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') }}
                        </span>
                    @endif
                    @if(request('admin_id'))
                        @php $adminSel = $admins->firstWhere('id', request('admin_id')); @endphp
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                            Admin: {{ $adminSel?->nombres }} {{ $adminSel?->apellidos }}
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">· {{ $sesiones->total() }} resultado(s)</span>
                </div>
            @endif
        </div>

        {{-- Historial de Arqueos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Historial de Arqueos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Apertura período</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Fecha arqueo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right">Total Sistema</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right">Confirmado CardNet</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right">Diferencia</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Realizado por</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sesiones as $s)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($s->fecha_apertura)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $s->fecha_cierre ? \Carbon\Carbon::parse($s->fecha_cierre)->format('d/m/Y H:i') : '---' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-mono font-bold text-gray-800">
                                    RD$ {{ number_format($s->monto_final_esperado, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-mono font-bold">
                                    RD$ {{ number_format($s->monto_final_real, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-mono font-bold {{ ($s->diferencia ?? 0) < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ ($s->diferencia ?? 0) >= 0 ? '+' : '' }}{{ number_format($s->diferencia ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $s->usuarioCierra->nombres ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-400 italic">
                                    {{ $s->nota ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    No hay arqueos registrados aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sesiones->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $sesiones->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de Arqueo (Conciliación Electrónica) --}}
<div id="modalCerrarCaja" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="mb-5 text-center">
            <div class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Arqueo de Caja</h3>
            <p class="text-sm text-gray-500 mt-1">Concilia el total del sistema con el reporte de CardNet.</p>
        </div>

        {{-- Resumen del período actual --}}
        <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-2">
            <p class="text-xs font-bold text-gray-400 uppercase mb-3">Recaudado en el sistema</p>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Ventas (tarjeta)</span>
                <span class="font-bold">RD$ {{ number_format($resumenPeriodo['ventas_tarjeta']['monto'], 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Envíos de intercambio</span>
                <span class="font-bold">RD$ {{ number_format($resumenPeriodo['envios_intercambio']['monto'], 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Registro de talentos</span>
                <span class="font-bold">RD$ {{ number_format($resumenPeriodo['talentos']['monto'], 2) }}</span>
            </div>
            <div class="flex justify-between text-sm font-black border-t border-gray-200 pt-2 mt-2">
                <span class="text-gray-800">Total Sistema</span>
                <span class="text-primary text-base">RD$ {{ number_format($resumenPeriodo['total'], 2) }}</span>
            </div>
        </div>

        <form action="{{ route('admin.erp.caja.cerrar') }}" method="POST">
            @csrf
            {{-- El "monto esperado" se pasa automáticamente del total del sistema --}}
            <input type="hidden" name="_total_sistema" value="{{ $resumenPeriodo['total'] }}">

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Total Confirmado en CardNet (RD$)
                    <span class="text-xs font-normal text-gray-400 ml-1">— según reporte del procesador</span>
                </label>
                <input type="number" name="monto_final_real" step="0.01" required
                    placeholder="{{ number_format($resumenPeriodo['total'], 2) }}"
                    value="{{ $resumenPeriodo['total'] }}"
                    class="w-full px-4 py-3 rounded-xl border-2 border-primary/20 focus:border-primary outline-none transition-all text-xl font-black text-primary">
                <p class="text-xs text-gray-400 mt-1">Si hay diferencia, ingresa el monto real que reporta CardNet.</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Observaciones</label>
                <textarea name="nota" rows="2"
                    class="w-full px-4 py-2 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20 resize-none text-sm"
                    placeholder="Ej: Sin diferencias. Cuadre perfecto con reporte CardNet..."></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modalCerrarCaja').style.display='none'"
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all text-sm">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 bg-primary text-white px-4 py-3 rounded-xl font-bold hover:bg-hoverPrimary shadow-lg transition-all text-sm">
                    Confirmar Arqueo
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('modalCerrarCaja').style.display = 'none';
});
</script>
@endpush

@endsection
