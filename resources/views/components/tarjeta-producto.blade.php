@php
    $imgNombre = $item->imagenes->where('estado', 'aprobado')->first()?->nombre;
    $imgSrc    = $imgNombre
        ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre)
        : asset('storage/imgs/producto_defaul.png');
    $esVenta       = in_array($item->tipo_trans, [1, 3]);
    $esIntercambio = in_array($item->tipo_trans, [2, 3]);
    $stock         = $item->inventarios?->cantidad ?? 1;
    $esMio = auth()->check() && auth()->id() == $item->id_user;
@endphp
<div class="bg-white rounded-2xl shadow hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col">

    <a href="{{ route('producto.detalle', $item->slug) }}" class="block overflow-hidden">
        <img src="{{ $imgSrc }}"
             class="w-full h-48 object-cover hover:scale-105 transition-transform duration-200"
             alt="{{ $item->item }}" loading="lazy" width="300" height="192"
             onerror="this.src='{{ asset('imgs/defaults/producto_default.svg') }}'">
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
            @if($item->valor)
            <span class="font-bold text-gray-800 text-sm">RD$ {{ number_format($item->valor, 2) }}</span>
            @endif
        </div>

        <div class="mt-auto flex gap-1.5">
            {{-- Agregar al carrito --}}
            @if($esVenta && $stock > 0)
            <button onclick="addToCart({{ $item->id_item }}, this)"
                    id="add-to-cart-{{ $item->id_item }}"
                    style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.3rem;background:#3b82f6;color:#fff;border:none;border-radius:0.5rem;padding:0.5rem 0.6rem;font-size:0.75rem;font-weight:700;cursor:pointer;transition:background .15s;"
                    onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                <svg style="width:0.85rem;height:0.85rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="btn-txt">Agregar</span>
            </button>
            @elseif(!$esVenta)
            {{-- Solo intercambio, no tiene botón de carrito --}}
            @else
            <span style="flex:1;text-align:center;background:#f1f5f9;color:#94a3b8;border-radius:0.5rem;padding:0.5rem;font-size:0.75rem;font-weight:600;">Agotado</span>
            @endif

            {{-- Botón intercambio --}}
            @if($esIntercambio && $stock > 0 && !$esMio)
            <button onclick="abrirModalIntercambio({{ $item->id_item }}, '{{ addslashes($item->item) }}')"
                    class="flex items-center gap-1 border border-orange-300 text-orange-700 rounded-lg transition-colors flex-shrink-0"
                    style="padding:0.4rem 0.6rem; font-size:0.7rem; font-weight:700; background:#fff7ed;"
                    onmouseover="this.style.background='#fed7aa'"
                    onmouseout="this.style.background='#fff7ed'"
                    title="Proponer intercambio">
                <svg style="width:0.75rem;height:0.75rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Intercambio
            </button>
            @endif

            {{-- Ver detalle --}}
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
