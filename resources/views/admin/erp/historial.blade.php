@extends('layouts.app')

@section('title', 'Historial ERP - Panel de Administración')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Historial de Ventas e Intercambios</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión y control de transacciones procesadas y terminadas.</p>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm flex items-center gap-4">
                <div class="bg-emerald-50 p-3 rounded-lg text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ventas Procesadas</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-0.5">{{ $ventas->total() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm flex items-center gap-4">
                <div class="bg-purple-50 p-3 rounded-lg text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4M5 17h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Intercambios Procesados</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-0.5">{{ $intercambios->total() }}</p>
                </div>
            </div>
        </div>

        {{-- Contenido Principal --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Tabs --}}
            <div class="border-b border-gray-100">
                <nav class="flex">
                    <a href="{{ route('admin.erp.historial', ['tab' => 'ventas', 'buscar' => request('buscar'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta'), 'estatus' => request('estatus')]) }}"
                       class="px-6 py-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2
                              {{ $tab === 'ventas' ? 'border-emerald-500 text-emerald-600 bg-emerald-50/20' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Ventas Procesadas
                        <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-bold">{{ $ventas->total() }}</span>
                    </a>
                    <a href="{{ route('admin.erp.historial', ['tab' => 'intercambios', 'buscar' => request('buscar'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta'), 'estatus' => request('estatus')]) }}"
                       class="px-6 py-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2
                              {{ $tab === 'intercambios' ? 'border-emerald-500 text-emerald-600 bg-emerald-50/20' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Intercambios Procesados
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-0.5 rounded-full font-bold">{{ $intercambios->total() }}</span>
                    </a>
                </nav>
            </div>

            {{-- Buscador y Filtro por Fechas --}}
            <div class="p-4 bg-gray-50/50 border-b border-gray-100">
                <form method="GET" action="{{ route('admin.erp.historial') }}" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Buscar</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" 
                               placeholder="{{ $tab === 'ventas' ? 'ID de orden, cliente o email...' : 'ID de negociación, artículo o usuario...' }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Estado</label>
                        <select name="estatus" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white w-40">
                            <option value="">Todos los estados</option>
                            @if($tab === 'ventas')
                                <option value="aprobado" {{ request('estatus') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                <option value="enviado" {{ request('estatus') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="entregado" {{ request('estatus') === 'entregado' ? 'selected' : '' }}>Entregado</option>
                            @else
                                <option value="aceptado" {{ request('estatus') === 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                                <option value="en_envio" {{ request('estatus') === 'en_envio' ? 'selected' : '' }}>En Envío</option>
                                <option value="completado" {{ request('estatus') === 'completado' ? 'selected' : '' }}>Completado</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-all">
                            Filtrar
                        </button>
                        <a href="{{ route('admin.erp.historial.pdf', request()->query()) }}" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Exportar PDF
                        </a>
                        @if(request('buscar') || request('fecha_desde') || request('fecha_hasta') || request('estatus'))
                            <a href="{{ route('admin.erp.historial', ['tab' => $tab]) }}" class="border border-gray-200 text-gray-600 hover:bg-gray-100 px-5 py-2 rounded-lg text-sm font-semibold transition-all">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Contenido de Ventas --}}
            @if($tab === 'ventas')
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Orden</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Detalle Pago</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Monto Total</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Estatus</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ventas as $pago)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-800">#{{ Str::limit($pago->id_pago_compra, 8, '...') }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $pago->fecha ? $pago->fecha->format('d/m/Y h:i A') : '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $pago->carrito?->usuario?->nombres ?? 'Desconocido' }} {{ $pago->carrito?->usuario?->apellidos ?? '' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $pago->carrito?->usuario?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        @if($pago->tarjeta)
                                            <span class="font-medium capitalize">{{ $pago->tarjeta->tipo_tarjeta }}</span> terminada en <span class="font-mono font-bold">{{ $pago->tarjeta->last4 }}</span>
                                            @if($pago->autorizacion_pago)
                                                <p class="text-[10px] text-gray-400 mt-0.5 font-mono">Aut: {{ $pago->autorizacion_pago }}</p>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Sin tarjeta registrada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-right text-gray-800">
                                        RD$ {{ number_format($pago->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            @if($pago->estatus === 'entregado') bg-emerald-100 text-emerald-800
                                            @elseif($pago->estatus === 'enviado') bg-blue-100 text-blue-800
                                            @elseif($pago->estatus === 'aprobado') bg-green-100 text-green-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $pago->estatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.compras.show', $pago->id_pago_compra) }}" 
                                               class="text-emerald-600 hover:text-emerald-800 text-xs font-bold border border-emerald-100 hover:bg-emerald-50 px-2.5 py-1 rounded-lg transition-all">
                                                Ver
                                            </a>
                                            <a href="{{ route('admin.compras.pdf', $pago->id_pago_compra) }}" 
                                               class="text-gray-600 hover:text-gray-800 text-xs font-bold border border-gray-200 hover:bg-gray-50 px-2.5 py-1 rounded-lg transition-all"
                                               title="Descargar hoja de envío">
                                                Envío PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        No se encontraron ventas finalizadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($ventas->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                        {{ $ventas->links() }}
                    </div>
                @endif
            @endif

            {{-- Contenido de Intercambios --}}
            @if($tab === 'intercambios')
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Intercambio</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Emisor (Propone)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Receptor (Recibe)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Pagos de Envío</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Estatus</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($intercambios as $neg)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-800">#{{ $neg->id_negociacion }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $neg->fecha_creacion ? $neg->fecha_creacion->format('d/m/Y h:i A') : '-' }}</p>
                                        <p class="text-xs font-medium text-gray-700 mt-1 truncate max-w-[180px]" title="{{ $neg->item->item ?? '' }}">
                                            Item: {{ $neg->item->item ?? 'Eliminado' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $neg->usuario?->nombres ?? 'Desconocido' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $neg->usuario?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $neg->usuarioReceptor?->nombres ?? 'Desconocido' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $neg->usuarioReceptor?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        @php
                                            $pagoEmisorObj = $neg->pagoEnvios->firstWhere('id_user', $neg->usuario_emisor_id);
                                            $pagoReceptorObj = $neg->pagoEnvios->firstWhere('id_user', $neg->usuario_receptor_id);
                                        @endphp
                                        <div class="flex flex-col gap-1">
                                            <div>
                                                <span class="font-medium text-gray-500">Emisor:</span>
                                                @if($pagoEmisorObj && $pagoEmisorObj->estado === 'pagado')
                                                    <span class="text-emerald-700 font-bold">RD$ {{ number_format($pagoEmisorObj->monto, 2) }}</span>
                                                @elseif($pagoEmisorObj && $pagoEmisorObj->estado === 'pagado_pull')
                                                    <span class="text-purple-700 font-bold">Pull</span>
                                                @else
                                                    <span class="text-gray-400">Pendiente</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-500">Receptor:</span>
                                                @if($pagoReceptorObj && $pagoReceptorObj->estado === 'pagado')
                                                    <span class="text-emerald-700 font-bold">RD$ {{ number_format($pagoReceptorObj->monto, 2) }}</span>
                                                @elseif($pagoReceptorObj && $pagoReceptorObj->estado === 'pagado_pull')
                                                    <span class="text-purple-700 font-bold">Pull</span>
                                                @else
                                                    <span class="text-gray-400">Pendiente</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            @if($neg->estado === 'completado') bg-emerald-100 text-emerald-800
                                            @elseif($neg->estado === 'en_envio') bg-blue-100 text-blue-800
                                            @elseif($neg->estado === 'aceptado') bg-green-100 text-green-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $neg->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.intercambios.show', $neg->id_negociacion) }}" 
                                           class="text-emerald-600 hover:text-emerald-800 text-xs font-bold border border-emerald-100 hover:bg-emerald-50 px-3 py-1.5 rounded-lg transition-all inline-block">
                                            Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        No se encontraron intercambios finalizados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($intercambios->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                        {{ $intercambios->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
@endsection
