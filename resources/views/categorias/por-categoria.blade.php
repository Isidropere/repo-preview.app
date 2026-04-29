@extends('layouts.app')

@section('title', $categoria->categoria . ' - Cambialord')

@section('content')
<div style="max-width:1200px;margin:0 auto;padding:1.5rem 1rem;">

    {{-- Breadcrumbs + Título --}}
    <div style="margin-bottom:1.5rem;">
        <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:#6b7280;margin-bottom:0.75rem;">
            <a href="{{ route('home') }}" style="color:#6b7280;text-decoration:none;display:flex;align-items:center;gap:0.25rem;">
                <svg style="width:16px;height:16px;" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                Inicio
            </a>
            <span>/</span>
            <span style="color:#111827;font-weight:500;">{{ $categoria->categoria }}</span>
        </nav>
        <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-bold my-2">{{ $categoria->categoria }}</h1>
        <p style="color:#6b7280;margin-top:0.25rem;font-size:0.9rem;">{{ $items->total() }} producto{{ $items->total() != 1 ? 's' : '' }}</p>
    </div>

    {{-- Barra de búsqueda y filtros --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem;margin-bottom:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:stretch;">
            <form action="{{ route('categorias.show', $categoria->slug) }}" method="GET" style="flex:1;min-width:200px;display:flex;">
                <input type="text" name="search" placeholder="Buscar en {{ $categoria->categoria }}..."
                       value="{{ request('search') }}"
                       style="flex:1;padding:0.6rem 0.75rem;border:1px solid #d1d5db;border-radius:0.5rem 0 0 0.5rem;font-size:0.875rem;outline:none;">
                <button type="submit" style="padding:0.6rem 1rem;background:#479bd5;color:#fff;border:none;border-radius:0 0.5rem 0.5rem 0;font-size:0.875rem;cursor:pointer;">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div style="display:flex;gap:0;">
                <select name="sort" id="sort" style="padding:0.6rem 0.75rem;border:1px solid #d1d5db;border-radius:0.5rem 0 0 0.5rem;font-size:0.875rem;background:#fff;cursor:pointer;outline:none;">
                    <option value="">Ordenar por...</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Más antiguos</option>
                </select>
                <button id="applyFilters" style="padding:0.6rem 1rem;background:#f58634;color:#fff;border:none;border-radius:0 0.5rem 0.5rem 0;font-size:0.875rem;cursor:pointer;">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid de productos --}}
    @if($items->count() > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem;">
            @foreach($items as $item)
            @php
                $imagen = $item->imagenes->where('estado', 'aprobado')->first();
                $rutaImagen = \App\Helpers\ImageHelper::urlItem($imagen, $item->id_categoria_item ?? 0);
            @endphp
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;transition:box-shadow .3s,transform .2s;display:flex;flex-direction:column;"
                 onmouseover="this.style.boxShadow='0 8px 25px rgba(0,0,0,.12)';this.style.transform='translateY(-4px)'"
                 onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.05)';this.style.transform='none'">

                {{-- Imagen --}}
                <a href="{{ route('producto.detalle', $item->slug) }}" style="display:block;position:relative;height:220px;overflow:hidden;background:#f3f4f6;">
                    <img src="{{ $rutaImagen }}" alt="{{ $item->item }}"
                         style="width:100%;height:100%;object-fit:cover;transition:transform .4s;"
                         loading="lazy" width="300" height="220"
                         onmouseover="this.style.transform='scale(1.08)'"
                         onmouseout="this.style.transform='scale(1)'">
                    @if($item->estatus != 1)
                        <span style="position:absolute;top:8px;right:8px;background:#ef4444;color:#fff;font-size:0.7rem;font-weight:700;padding:3px 8px;border-radius:4px;">Agotado</span>
                    @endif
                    @if($item->descuento && $item->descuento > 0)
                        <span style="position:absolute;top:8px;left:8px;background:#dc2626;color:#fff;font-size:0.7rem;font-weight:700;padding:3px 8px;border-radius:4px;">-{{ $item->descuento }}%</span>
                    @endif
                </a>

                {{-- Info --}}
                <div style="padding:0.875rem;flex:1;display:flex;flex-direction:column;">
                    <a href="{{ route('producto.detalle', $item->slug) }}" style="text-decoration:none;color:#111827;">
                        <h3 style="font-size:0.95rem;font-weight:600;margin:0 0 0.35rem;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $item->item }}</h3>
                    </a>
                    <p style="font-size:0.8rem;color:#9ca3af;margin:0 0 0.5rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ Str::limit($item->presentacion, 70) }}</p>

                    <div style="margin-top:auto;">
                        {{-- Precio --}}
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                            @if($item->valor)
                                <span style="font-size:1.15rem;font-weight:700;color:#2563eb;">RD$ {{ number_format($item->valor, 0) }}</span>
                            @else
                                <span style="font-size:0.8rem;font-weight:600;color:#059669;background:#d1fae5;padding:3px 10px;border-radius:999px;">Intercambio</span>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:0.65rem;border-top:1px solid #f3f4f6;">
                            <a href="{{ route('producto.detalle', $item->slug) }}"
                               style="font-size:0.8rem;font-weight:500;color:#479bd5;text-decoration:none;display:flex;align-items:center;gap:4px;">
                                Ver detalles
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <div style="display:flex;align-items:center;gap:6px;">
                            @if(in_array($item->tipo_trans, [2, 3]) && ($item->inventarios?->cantidad ?? 0) > 0 && auth()->id() != $item->id_user)
                                <button onclick="abrirModalIntercambio({{ $item->id_item }}, '{{ addslashes($item->item) }}')"
                                    style="font-size:0.75rem;font-weight:600;color:#c2410c;background:#fff7ed;border:1px solid #fed7aa;padding:4px 10px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:3px;transition:background .2s;"
                                    onmouseover="this.style.background='#fed7aa'" onmouseout="this.style.background='#fff7ed'">
                                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    Intercambio
                                </button>
                            @endif
                            @if($item->estatus == 1 && ($item->inventarios?->cantidad ?? 0) > 0)
                                @auth
                                    <button onclick="agregarAlCarrito({{ $item->id_item }})"
                                        id="add-to-cart-{{ $item->id_item }}"
                                        data-original-text="Agregar"
                                        style="font-size:0.8rem;font-weight:500;color:#059669;background:none;border:1px solid #d1fae5;padding:4px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:4px;transition:background .2s;"
                                        onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='transparent'">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        <span class="button-text">Agregar</span>
                                        <span class="loading" style="display:none;"><svg style="width:14px;height:14px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                                    </button>
                                @endauth
                            @elseif($item->estatus == 1 && ($item->inventarios?->cantidad ?? 0) <= 0)
                                <span style="font-size:0.8rem;font-weight:600;color:#94a3b8;">Agotado</span>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if($items->hasPages())
            <div style="margin-top:2rem;">
                {{ $items->appends(request()->except('page'))->links('vendor.pagination.custom') }}
            </div>
        @endif
    @else
        {{-- Estado vacío --}}
        <div style="text-align:center;padding:4rem 1rem;background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;">
            <div style="width:80px;height:80px;background:#f3f4f6;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
                <svg style="width:40px;height:40px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <h3 style="font-size:1.15rem;font-weight:600;color:#111827;margin:0 0 0.5rem;">No se encontraron productos</h3>
            <p style="color:#6b7280;font-size:0.9rem;max-width:400px;margin:0 auto 1.5rem;">
                @if(request()->has('search'))
                    No hay resultados para "{{ request('search') }}".
                    <a href="{{ route('categorias.show', $categoria->slug) }}" style="color:#479bd5;">Ver todos</a>
                @else
                    Esta categoría aún no tiene productos.
                @endif
            </p>
            <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.6rem 1.5rem;background:#479bd5;color:#fff;text-decoration:none;border-radius:0.5rem;font-size:0.9rem;font-weight:500;">
                Volver al inicio
            </a>
        </div>
    @endif
</div>

@include('components.modal-intercambio')
@endsection

@push('styles')
<style>
    @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    @keyframes fade-in { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fade-out { from{opacity:1;transform:translateY(0)} to{opacity:0;transform:translateY(20px)} }
    .animate-fade-in { animation:fade-in .3s ease-out; }
    .animate-fade-out { animation:fade-out .3s ease-in; }
</style>
@endpush

@push('scripts')
<script>
window._urlLogin = "{{ route('login') }}";
window._urlItemsUsuario = "{{ route('carrito.items_usuario') }}";
window._urlNegStore = "{{ route('negociaciones.store') }}";
</script>
<script src="{{ asset('js/modal-intercambio.js') }}"></script>
<script>
    window.agregarAlCarrito = async function(id_item) {
        if (!id_item) return showNotification("Producto no válido", "error");
        const button = document.querySelector(`#add-to-cart-${id_item}`);
        const buttonText = button.querySelector(".button-text");
        const loadingIcon = button.querySelector(".loading");
        const originalText = button.dataset.originalText || "Agregar";
        button.disabled = true;
        buttonText.textContent = "Agregando";
        loadingIcon.style.display = "inline";

        try {
            const response = await fetch("{{ route('carrito.agregar') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
                body: JSON.stringify({ id_item: id_item, cantidad: 1 })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || "Error al agregar al carrito");
            showNotification("Producto agregado al carrito", "success");
            if (data.cart_count !== undefined) window.updateCartBadge(data.cart_count);
            if (window.syncCartIndicators) window.syncCartIndicators();
        } catch (e) { showNotification(e.message, "error"); }
        finally { button.disabled = false; buttonText.textContent = originalText; loadingIcon.style.display = "none"; }
    };

    window.updateCartCounter = function(count) {
        const el = document.getElementById('cart-counter');
        if (el) { el.textContent = count; }
    };

    window.showNotification = function(msg, type) {
        const colors = { success: '#22c55e', error: '#ef4444', warning: '#eab308' };
        const notif = document.createElement('div');
        notif.style.cssText = `position:fixed;bottom:1rem;right:1rem;background:${colors[type]};color:#fff;padding:0.75rem 1.25rem;border-radius:0.5rem;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:9999;font-size:0.875rem;display:flex;align-items:center;gap:0.5rem;`;
        notif.className = 'animate-fade-in';
        notif.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'}"></i>${msg}`;
        document.body.appendChild(notif);
        setTimeout(() => { notif.className = 'animate-fade-out'; setTimeout(() => notif.remove(), 300); }, 3000);
    };

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('applyFilters')?.addEventListener('click', function(e) {
            e.preventDefault();
            const sort = document.getElementById('sort').value;
            const url = new URL(window.location.href);
            if (sort) url.searchParams.set('sort', sort);
            window.location.href = url.toString();
        });
    });
</script>
@endpush
