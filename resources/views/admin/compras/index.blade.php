@extends('layouts.app')

@section('title', 'Administración de Compras')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Administración de Compras</h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona y da seguimiento a todas las órdenes de compra.</p>
        </div>

        {{-- Spinner de carga --}}
        <div id="pageLoader" class="hidden">
            <div class="flex flex-col items-center justify-center py-16 gap-3">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                </svg>
                <span class="text-gray-500 text-sm">Cargando...</span>
            </div>
        </div>

        {{-- Contenido principal --}}
        <div id="mainContent">

            {{-- Alerta de éxito --}}
            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
            @endif

            {{-- Tarjetas de estadísticas --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total órdenes</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $compras->total() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-yellow-600 uppercase tracking-wide">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">
                        {{ \App\Models\PagoCompra::where('estatus', 'pendiente')->count() }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-green-600 uppercase tracking-wide">Aprobadas</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        {{ \App\Models\PagoCompra::where('estatus', 'aprobado')->count() }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-blue-600 uppercase tracking-wide">Enviadas</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">
                        {{ \App\Models\PagoCompra::where('estatus', 'enviado')->count() }}
                    </p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <form method="GET" action="{{ route('admin.compras.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <input
                        type="text"
                        name="buscar"
                        value="{{ request('buscar') }}"
                        placeholder="Buscar por ID, nombre o email..."
                        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                    <select
                        name="estatus"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="">Todos los estados</option>
                        @foreach($estados as $estado)
                            <option value="{{ $estado }}" {{ request('estatus') === $estado ? 'selected' : '' }}>
                                {{ ucfirst($estado) }}
                            </option>
                        @endforeach
                    </select>
                    <button
                        type="submit"
                        class="bg-primary hover:bg-hoverPrimary text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        Filtrar
                    </button>
                    @if(request('buscar') || request('estatus'))
                    <a
                        href="{{ route('admin.compras.index') }}"
                        class="border border-gray-200 text-gray-600 hover:bg-gray-50 px-5 py-2 rounded-lg text-sm font-medium transition-colors text-center"
                    >
                        Limpiar
                    </a>
                    @endif
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @if($compras->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm font-medium">No se encontraron órdenes</p>
                        <p class="text-xs mt-1">Intenta ajustar los filtros de búsqueda.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Orden</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Comprador</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Items</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Monto Total</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($compras as $compra)
                                @php
                                    $items = $compra->pagoItems ?? collect();
                                    $badgeClass = match($compra->estatus) {
                                        'pendiente'  => 'bg-yellow-100 text-yellow-700',
                                        'aprobado'   => 'bg-green-100 text-green-700',
                                        'rechazado'  => 'bg-red-100 text-red-700',
                                        'enviado'    => 'bg-blue-100 text-blue-700',
                                        'entregado'  => 'bg-emerald-100 text-emerald-700',
                                        'cancelado'  => 'bg-gray-100 text-gray-600',
                                        default      => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                        #{{ $compra->id_pago_compra }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($compra->comprador)
                                            <p class="font-medium text-gray-800">{{ $compra->comprador->nombres }} {{ $compra->comprador->apellidos }}</p>
                                            <p class="text-xs text-gray-400">{{ $compra->comprador->email }}</p>
                                        @else
                                            <span class="text-gray-400 text-xs">Sin datos</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $items->count() ?: ($compra->cantidad_items ?? 0) }} {{ ($items->count() ?: ($compra->cantidad_items ?? 0)) === 1 ? 'artículo' : 'artículos' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        RD$ {{ number_format($compra->total ?? $items->sum('subtotal'), 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                            {{ ucfirst($compra->estatus ?? 'sin estado') }}
                                        </span>
                                        @if($compra->tracking_url)
                                        <a href="{{ $compra->tracking_url }}" target="_blank"
                                           class="mt-1 flex items-center gap-1 text-xs text-blue-500 hover:underline">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            {{ $compra->tracking_code }}
                                        </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a
                                            href="{{ route('admin.compras.show', $compra->id_pago_compra) }}"
                                            class="inline-flex items-center gap-1 text-primary hover:text-hoverPrimary text-xs font-medium transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    @if($compras->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $compras->appends(request()->except('page'))->links('vendor.pagination.custom') }}
                    </div>
                    @endif
                @endif
            </div>

        </div>{{-- /mainContent --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loader = document.getElementById('pageLoader');
        const content = document.getElementById('mainContent');
        loader.classList.remove('hidden');
        content.classList.add('hidden');
        setTimeout(function () {
            loader.classList.add('hidden');
            content.classList.remove('hidden');
        }, 300);
    });
</script>
@endpush
