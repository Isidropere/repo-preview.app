@extends('layouts.admin')

@section('title', 'Inventario - Panel ERP')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Control de Inventario</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de stock centralizado y movimientos de mercancía.</p>
            </div>
        </div>

        {{-- Almacén Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 flex items-center gap-4">
            <div class="bg-primary/10 p-3 rounded-lg text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800">{{ $almacen->nombre ?? 'Almacén Central' }}</h2>
                <p class="text-sm text-gray-500">Ubicación: {{ $almacen->ubicacion ?? 'Principal' }}</p>
            </div>
        </div>

        {{-- ══════════════ SECCIÓN 1: STOCK ACTUAL ══════════════ --}}

        {{-- Filtros del Stock --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <form method="GET" action="{{ route('admin.erp.inventario') }}" class="flex flex-wrap items-end gap-4">
                {{-- Mantener filtros del kardex al filtrar stock --}}
                @foreach(['kardex_desde','kardex_hasta','kardex_tipo','kardex_ref'] as $kf)
                    @if(request($kf))<input type="hidden" name="{{ $kf }}" value="{{ request($kf) }}">@endif
                @endforeach

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Buscar artículo</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre del producto..."
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-52">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Tipo</label>
                    <select name="tipo" class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white">
                        <option value="">— Todos —</option>
                        <option value="producto" {{ request('tipo') === 'producto' ? 'selected' : '' }}>Producto</option>
                        <option value="servicio" {{ request('tipo') === 'servicio' ? 'selected' : '' }}>Servicio</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Categoría</label>
                    <select name="categoria" class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white w-48">
                        <option value="">— Todas —</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id_categoria_item }}" {{ request('categoria') == $cat->id_categoria_item ? 'selected' : '' }}>
                                {{ $cat->categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Disponibilidad</label>
                    <select name="stock_filtro" class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white">
                        <option value="">— Todos —</option>
                        <option value="disponible" {{ request('stock_filtro') === 'disponible' ? 'selected' : '' }}>Con Stock (>0)</option>
                        <option value="bajo" {{ request('stock_filtro') === 'bajo' ? 'selected' : '' }}>Bajo Stock (1-3)</option>
                        <option value="agotado" {{ request('stock_filtro') === 'agotado' ? 'selected' : '' }}>Agotado (0)</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Estatus</label>
                    <select name="estatus" class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white">
                        <option value="">— Todos —</option>
                        <option value="1" {{ request('estatus') === '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ request('estatus') === '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-hoverPrimary transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                    @if(request('buscar') || request('tipo') || request('categoria') || request('stock_filtro') || request('estatus') !== null && request('estatus') !== '')
                        <a href="{{ route('admin.erp.inventario', array_filter(['kardex_desde' => request('kardex_desde'), 'kardex_hasta' => request('kardex_hasta'), 'kardex_tipo' => request('kardex_tipo'), 'kardex_ref' => request('kardex_ref')])) }}"
                            class="px-4 py-2 border border-gray-200 text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-50 transition-all flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            @if(request('buscar') || request('tipo') || request('categoria') || request('stock_filtro') || (request('estatus') !== null && request('estatus') !== ''))
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-gray-400 font-semibold">Filtros activos:</span>
                    @if(request('buscar'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">Buscar: "{{ request('buscar') }}"</span>
                    @endif
                    @if(request('tipo'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium capitalize">Tipo: {{ request('tipo') }}</span>
                    @endif
                    @if(request('categoria'))
                        @php $catFiltro = $categorias->firstWhere('id_categoria_item', request('categoria')); @endphp
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">Categoría: "{{ $catFiltro->categoria ?? request('categoria') }}"</span>
                    @endif
                    @if(request('stock_filtro'))
                        @php $stockLabels = ['disponible' => 'Con Stock (>0)', 'bajo' => 'Bajo Stock (1-3)', 'agotado' => 'Agotado (0)']; @endphp
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">Stock: {{ $stockLabels[request('stock_filtro')] ?? request('stock_filtro') }}</span>
                    @endif
                    @if(request('estatus') !== null && request('estatus') !== '')
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">Estatus: {{ request('estatus') == '1' ? 'Activo' : 'Inactivo' }}</span>
                    @endif
                    <span class="text-xs text-gray-400">· {{ $items->count() }} resultado(s)</span>
                </div>
            @endif
        </div>

        {{-- Tabla de Stock --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Stock Actual del Almacén</h2>
                <span class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-full font-semibold">
                    {{ $items->count() }} artículos
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Artículo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center">Stock</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                            @php $stock = $item->inventarios->cantidad ?? 0; @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-800">{{ $item->item }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $item->id_item }} · {{ $item->categoria->nombre ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->id_categoria_item == 29)
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">Servicio</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Producto</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($stock <= 0)
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Agotado</span>
                                    @elseif($stock <= 3)
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">{{ $stock }} (bajo)</span>
                                    @else
                                        <span class="text-sm font-bold text-green-600">{{ $stock }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">RD$ {{ number_format($item->valor ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ ($item->usuario->nombres ?? '') . ' ' . ($item->usuario->apellidos ?? '') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->estatus == 1)
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Activo</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    No se encontraron artículos con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══════════════ SECCIÓN 2: KARDEX ══════════════ --}}

        {{-- Filtros del Kardex --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <form method="GET" action="{{ route('admin.erp.inventario') }}" class="flex flex-wrap items-end gap-4">
                {{-- Mantener filtros del stock al filtrar kardex --}}
                @foreach(['buscar','tipo','categoria','stock_filtro','estatus'] as $sf)
                    @if(request($sf) !== null && request($sf) !== '')
                        <input type="hidden" name="{{ $sf }}" value="{{ request($sf) }}">
                    @endif
                @endforeach

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Fecha desde</label>
                    <input type="date" name="kardex_desde" value="{{ request('kardex_desde') }}"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Fecha hasta</label>
                    <input type="date" name="kardex_hasta" value="{{ request('kardex_hasta') }}"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Movimiento</label>
                    <select name="kardex_tipo" class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white">
                        <option value="">— Todos —</option>
                        <option value="entrada" {{ request('kardex_tipo') === 'entrada' ? 'selected' : '' }}>✅ Entrada</option>
                        <option value="salida"  {{ request('kardex_tipo') === 'salida'  ? 'selected' : '' }}>🔴 Salida</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Origen</label>
                    <select name="kardex_ref" class="px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-white">
                        <option value="">— Todos —</option>
                        <option value="item"         {{ request('kardex_ref') === 'item'         ? 'selected' : '' }}>Registro inicial</option>
                        <option value="pago_compra"  {{ request('kardex_ref') === 'pago_compra'  ? 'selected' : '' }}>Venta</option>
                        <option value="negociacion"  {{ request('kardex_ref') === 'negociacion'  ? 'selected' : '' }}>Intercambio</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-hoverPrimary transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filtrar
                    </button>
                    @if(request('kardex_desde') || request('kardex_hasta') || request('kardex_tipo') || request('kardex_ref'))
                        <a href="{{ route('admin.erp.inventario', array_filter(['buscar' => request('buscar'), 'tipo' => request('tipo'), 'categoria' => request('categoria'), 'stock_filtro' => request('stock_filtro'), 'estatus' => request('estatus')])) }}"
                            class="px-4 py-2 border border-gray-200 text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-50 transition-all flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            @if(request('kardex_desde') || request('kardex_hasta') || request('kardex_tipo') || request('kardex_ref'))
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-gray-400 font-semibold">Filtros activos:</span>
                    @if(request('kardex_desde'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                            Desde: {{ \Carbon\Carbon::parse(request('kardex_desde'))->format('d/m/Y') }}
                        </span>
                    @endif
                    @if(request('kardex_hasta'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                            Hasta: {{ \Carbon\Carbon::parse(request('kardex_hasta'))->format('d/m/Y') }}
                        </span>
                    @endif
                    @if(request('kardex_tipo'))
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium capitalize">
                            Tipo: {{ request('kardex_tipo') }}
                        </span>
                    @endif
                    @if(request('kardex_ref'))
                        @php $labels = ['item' => 'Registro inicial', 'pago_compra' => 'Venta', 'negociacion' => 'Intercambio']; @endphp
                        <span class="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                            Origen: {{ $labels[request('kardex_ref')] ?? request('kardex_ref') }}
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">· {{ $movimientos->total() }} movimiento(s)</span>
                </div>
            @endif
        </div>

        {{-- Tabla Kardex --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Historial de Movimientos (Kardex)</h2>
                <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full font-semibold">
                    {{ $movimientos->total() }} movimientos
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Artículo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center">Cantidad</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Motivo / Origen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movimientos as $mov)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-800">{{ $mov->item->item ?? 'Desconocido' }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $mov->id_item }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $mov->tipo === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $mov->tipo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center font-bold {{ $mov->tipo === 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $mov->tipo === 'entrada' ? '+' : '-' }}{{ number_format($mov->cantidad, 0) }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ $mov->motivo }}</p>
                                    @if($mov->referencia_tipo)
                                        @php
                                            $origenLabel = ['item' => 'Registro', 'pago_compra' => 'Venta', 'negociacion' => 'Intercambio'];
                                        @endphp
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            <span class="font-medium">{{ $origenLabel[$mov->referencia_tipo] ?? $mov->referencia_tipo }}</span>
                                            #{{ $mov->referencia_id }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    No hay movimientos con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movimientos->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $movimientos->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
