@extends('layouts.app')

@section('title', 'Realizar Compra - Cambialord')

@section('content')
<main class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li>
                    <a href="{{ route('home') }}" 
                       class="flex items-center hover:text-primary transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 
                                     1 0 001.414 1.414L4 10.414V17a1 
                                     1 0 001 1h2a1 1 0 001-1v-2a1 
                                     1 0 011-1h2a1 1 0 011 1v2a1 
                                     1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 
                                     1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Inicio
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="font-medium text-primary">Compras</li>
            </ol>
        </nav>

        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                @if($noResults ?? false)
                    Resultados de búsqueda
                @else
                    Productos en venta
                @endif
            </h1>
            <span class="text-sm sm:text-lg text-primary font-medium">
                {{ $items->total() }} productos o servicios disponibles
            </span>
        </div>

        <!-- Mensaje sin resultados -->
        @if($noResults ?? false)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-800 mb-1">
                No encontramos resultados para "{{ $searchTerm ?? '' }}"
            </h2>
            <p class="text-sm text-gray-500">
                Intenta con otras palabras clave o revisa estos productos que podrían interesarte
            </p>
        </div>

        <!-- Productos relevantes cuando no hay resultados -->
        @if($relevantItems->isNotEmpty())
        <h3 class="text-base font-semibold text-gray-700 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Productos destacados
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @foreach($relevantItems as $item)
            @php
                $imgNombre = $item->imagenes->where('estado', 'aprobado')->first()?->nombre;
                $imgSrc    = $imgNombre
                    ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items',$imgNombre)
                    : asset('storage/imgs/producto_defaul.png');
                $esVenta   = in_array($item->tipo_trans, [1, 3]);
                $stock     = $item->inventarios?->cantidad ?? 1;
            @endphp
            <div class="bg-white rounded-2xl shadow hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col">
                <a href="{{ route('producto.detalle', $item->slug) }}" class="block overflow-hidden">
                    <img src="{{ $imgSrc }}"
                         class="w-full h-48 object-cover hover:scale-105 transition-transform duration-200"
                         alt="{{ $item->item }}" loading="lazy"
                         onerror="this.src='{{ asset('storage/imgs/producto_defaul.png') }}'">
                </a>
                <div class="p-4 flex flex-col flex-1">
                    <a href="{{ route('producto.detalle', $item->slug) }}" class="hover:text-blue-600 transition-colors">
                        <h2 class="text-sm font-semibold text-gray-900 line-clamp-2 mb-1 leading-snug">
                            {{ $item->item }}
                        </h2>
                    </a>
                    <div class="flex justify-between items-center text-xs text-gray-400 mb-3">
                        <span>
                            @if($item->direccionPredeterminada && $item->direccionPredeterminada->provincia)
                                {{ $item->direccionPredeterminada->provincia->nombre }} ·
                            @endif
                            {{ $item->condicion ?? 'Nuevo' }}
                        </span>
                        <span class="font-bold text-gray-800 text-sm">
                            RD$ {{ number_format($item->valor, 2) }}
                        </span>
                    </div>
                    <div class="mt-auto flex gap-2">
                        @if($esVenta && $stock > 0)
                        <button onclick="addToCart({{ $item->id_item }}, this)"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.3rem;background:#3b82f6;color:#fff;border:none;border-radius:0.5rem;padding:0.5rem 0.6rem;font-size:0.75rem;font-weight:700;cursor:pointer;transition:background .15s;"
                                onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                            <svg style="width:0.85rem;height:0.85rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="btn-txt">Agregar</span>
                        </button>
                        @else
                        <span style="flex:1;text-align:center;background:#f1f5f9;color:#94a3b8;border-radius:0.5rem;padding:0.5rem;font-size:0.75rem;font-weight:600;">Agotado</span>
                        @endif
                        <a href="{{ route('producto.detalle', $item->slug) }}"
                           style="display:flex;align-items:center;justify-content:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.5rem 0.6rem;transition:background .15s;"
                           onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'"
                           title="Ver detalle">
                            <svg style="width:0.85rem;height:0.85rem;color:#64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @else
        <!-- Grid de productos normales -->
        <div id="itemsContainer"
             class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
            @php
                $imgNombre = $item->imagenes->where('estado', 'aprobado')->first()?->nombre;
                $imgSrc    = $imgNombre
                    ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items',$imgNombre)
                    : asset('storage/imgs/producto_defaul.png');
                $esVenta   = in_array($item->tipo_trans, [1, 3]);
                $stock     = $item->inventarios?->cantidad ?? 1;
            @endphp
            <div class="bg-white rounded-2xl shadow hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col">

                <!-- Imagen clicable -->
                <a href="{{ route('producto.detalle', $item->slug) }}" class="block overflow-hidden">
                    <img src="{{ $imgSrc }}"
                         class="w-full h-48 object-cover hover:scale-105 transition-transform duration-200"
                         alt="{{ $item->item }}" loading="lazy"
                         onerror="this.src='{{ asset('storage/imgs/producto_defaul.png') }}'">
                </a>

                <!-- Info -->
                <div class="p-4 flex flex-col flex-1">
                    <a href="{{ route('producto.detalle', $item->slug) }}" class="hover:text-blue-600 transition-colors">
                        <h2 class="text-sm font-semibold text-gray-900 line-clamp-2 mb-1 leading-snug">
                            {{ $item->item }}
                        </h2>
                    </a>
                    <div class="flex justify-between items-center text-xs text-gray-400 mb-3">
                        <span>
                            @if($item->direccionPredeterminada && $item->direccionPredeterminada->provincia)
                                {{ $item->direccionPredeterminada->provincia->nombre }} ·
                            @endif
                            {{ $item->condicion ?? 'Nuevo' }}
                        </span>
                        <span class="font-bold text-gray-800 text-sm">
                            RD$ {{ number_format($item->valor, 2) }}
                        </span>
                    </div>

                    <!-- Botones -->
                    <div class="mt-auto flex gap-2">
                        @if($esVenta && $stock > 0)
                        <button onclick="addToCart({{ $item->id_item }}, this)"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.3rem;background:#3b82f6;color:#fff;border:none;border-radius:0.5rem;padding:0.5rem 0.6rem;font-size:0.75rem;font-weight:700;cursor:pointer;transition:background .15s;"
                                onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                            <svg style="width:0.85rem;height:0.85rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="btn-txt">Agregar</span>
                        </button>
                        @else
                        <span style="flex:1;text-align:center;background:#f1f5f9;color:#94a3b8;border-radius:0.5rem;padding:0.5rem;font-size:0.75rem;font-weight:600;">Agotado</span>
                        @endif
                        <a href="{{ route('producto.detalle', $item->slug) }}"
                           style="display:flex;align-items:center;justify-content:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.5rem 0.6rem;transition:background .15s;"
                           onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'"
                           title="Ver detalle">
                            <svg style="width:0.85rem;height:0.85rem;color:#64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Paginación -->
        @if($items->hasPages())
            <div class="flex justify-center mt-10">
                {{ $items->appends(request()->except('page'))->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>

    <!-- Carrusel (opcional) -->
    <div class="my-8">
        <!-- ... (código del carrusel) ... -->
    </div>
</main>

@push('scripts')
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
<script>
async function addToCart(id_item, btn) {
    @guest
    window.location.href = '{{ route("login") }}';
    return;
    @endguest

    const txt  = btn.querySelector('.btn-txt');
    const orig = txt?.textContent ?? 'Agregar';
    btn.disabled = true;
    if (txt) txt.textContent = '...';

    try {
        const res = await fetch('{{ route("carrito.agregar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id_item, cantidad: 1 })
        });
        const data = await res.json();
        if (data.success) {
            btn.style.background = '#22c55e';
            if (txt) txt.textContent = '✓ Listo';
            // Actualizar contador del carrito si existe
            const counter = document.getElementById('cart-counter');
            if (counter && data.cart_count) counter.textContent = data.cart_count;
            setTimeout(() => {
                btn.style.background = '#3b82f6';
                btn.disabled = false;
                if (txt) txt.textContent = orig;
            }, 1800);
        } else {
            alert(data.message ?? 'No se pudo agregar.');
            btn.disabled = false;
            if (txt) txt.textContent = orig;
        }
    } catch {
        alert('Error de red. Intenta de nuevo.');
        btn.disabled = false;
        if (txt) txt.textContent = orig;
    }
}
</script>
@endpush
@endsection
