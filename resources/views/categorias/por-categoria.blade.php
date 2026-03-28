@extends('layouts.app')

@section('title', $categoria->categoria . ' - Cambialord')

@section('content')
<div class="container mx-auto px-2 py-8 max-w-6xl">

    {{-- Barra de búsqueda y filtros --}}
    <div class="bg-white p-4 rounded-md shadow-sm mb-6 sticky top-0 z-20 text-sm">
        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-4">
            <ul class="flex items-center space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-gray-700 hover:text-primary">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Inicio
                    </a>
                </li>
                <li>
                    <span class="mx-2">/</span>
                    <span class="text-gray-500">{{ $categoria->categoria }}</span>
                </li>
            </ul>
        </nav>

        {{-- Formulario de búsqueda --}}
        <form action="{{ route('categorias.show', $categoria->id_categoria_item) }}" method="GET" class="mb-3">
            <div class="flex">
                <input type="text" name="search" placeholder="Buscar productos..."
                       value="{{ request('search') }}"
                       class="flex-grow px-3 py-2 border border-gray-300 rounded-l-md focus:ring-1 focus:ring-primary focus:outline-none">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-r-md hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search mr-1"></i> Buscar
                </button>
            </div>
        </form>

        {{-- Filtros y orden --}}
        <div class="flex gap-0">
            <select name="sort" id="sort" class="flex-grow px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none">
                <option value="">Ordenar por...</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nombre: A-Z</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nombre: Z-A</option>
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Más antiguos</option>
            </select>
            <button id="applyFilters" class="px-4 py-2 bg-secondary text-white rounded-r-md hover:bg-orange-700 transition duration-200">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
        </div>
    </div>

    {{-- Lista de productos --}}
    @if($items->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                    
                    {{-- Imagen del producto --}}
                    <a href="{{ route('producto.detalle', $item->slug) }}" class="relative block h-64 w-full overflow-hidden">
                        <div class="skeleton-loader absolute inset-0"></div>
                        @php
                            $imagen = $item->imagenes->where('estado', 'aprobado')->first();
                            $rutaImagen = $imagen && file_exists(public_path('storage/imgs/articulos/items/'.$imagen->nombre))
                                           ? asset('storage/imgs/articulos/items/'.$imagen->nombre)
                                           : asset('storage/imgs/producto_default.png');
                        @endphp
                        <img src="{{ $rutaImagen }}" alt="{{ $item->item }}" 
                             class="w-full h-full object-cover transition-opacity duration-300 opacity-0"
                             loading="lazy"
                             onload="this.classList.remove('opacity-0'); this.previousElementSibling.style.display='none';">
                    </a>

                    {{-- Detalles --}}
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-lg mb-1">{{ $item->item }}</h3>
                            <p class="text-gray-600 text-sm mb-2">{{ Str::limit($item->presentacion, 50) }}</p>
                            <div class="flex justify-between items-center text-sm">
                                @if($item->valor)
                                    <span class="font-bold text-blue-600">RD$ {{ number_format($item->valor,2) }}</span>
                                @else
                                    <span class="text-green-500 font-semibold">Para intercambio</span>
                                @endif
                                <span class="text-gray-400">{{ \Carbon\Carbon::parse($item->fecha)->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="mt-3 flex justify-between items-center">
                            <a href="{{ route('producto.detalle', $item->slug) }}" 
                               class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                Ver detalles
                            </a>

                            @if($item->estatus == 1)
                                @auth
                                    <button onclick="agregarAlCarrito({{ $item->id_item }})"
                                        class="text-green-500 hover:text-green-700 text-sm font-medium flex items-center"
                                        id="add-to-cart-{{ $item->id_item }}" type="button">
                                        <i class="fas fa-cart-plus mr-1"></i>
                                        <span class="button-text">Agregar</span>
                                        <span class="loading hidden ml-1"><i class="fas fa-spinner fa-spin"></i></span>
                                    </button>
                                @endauth

                                @guest
                                    <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                        Inicia sesión
                                    </a>
                                @endguest
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if($items->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6 mt-6">
                {{ $items->appends(request()->except('page'))->links('vendor.pagination.custom') }}
            </div>
        @endif
    @else
        <div class="text-center py-10 bg-white rounded-lg shadow">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-600">No se encontraron productos en esta categoría.</p>
            @if(request()->has('search'))
                <a href="{{ route('categorias.show', $categoria->id_categoria_item) }}" class="text-blue-500 hover:underline mt-2 inline-block">
                    Limpiar búsqueda
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Skeleton loader */
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
    /* Notificaciones */
    @keyframes fade-in { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
    @keyframes fade-out { from {opacity:1; transform:translateY(0);} to {opacity:0; transform:translateY(20px);} }
    .animate-fade-in { animation: fade-in 0.3s ease-out; }
    .animate-fade-out { animation: fade-out 0.3s ease-in; }
    /* Contador carrito */
    @keyframes bounce { 0%,100%{transform:scale(1);}50%{transform:scale(1.2);} }
    .animate-bounce { animation: bounce 0.5s; }
</style>
@endpush

@push('scripts')
<script>
    // Agregar al carrito
    window.agregarAlCarrito = async function (id_item) {
        if (!id_item) return showNotification("Producto no válido", "error");
        const button = document.querySelector(`#add-to-cart-${id_item}`);
        const buttonText = button.querySelector(".button-text");
        const loadingIcon = button.querySelector(".loading");
        button.disabled = true;
        buttonText.textContent = "Agregando";
        loadingIcon.classList.remove("hidden");

        try {
            const response = await fetch("{{ url('/carrito_show/agregar') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ id_item: id_item, cantidad: 1 })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || "Error al agregar al carrito");
            showNotification("Producto agregado al carrito", "success");
            if (data.cart_count) updateCartCounter(data.cart_count);
        } catch (e) { showNotification(e.message, "error"); }
        finally { 
            button.disabled = false; 
            buttonText.textContent = "Agregar"; 
            loadingIcon.classList.add("hidden");
        }
    };

    window.updateCartCounter = function(count){
        const el = document.getElementById('cart-counter');
        if(el){ el.textContent = count; el.classList.add('animate-bounce'); setTimeout(()=>el.classList.remove('animate-bounce'),1000); }
    };

    window.showNotification = function(msg,type){
        const colors = {success:'bg-green-500',error:'bg-red-500',warning:'bg-yellow-500'};
        const notif = document.createElement('div');
        notif.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg flex items-center animate-fade-in`;
        notif.innerHTML = `<i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'} mr-2"></i>${msg}`;
        document.body.appendChild(notif);
        setTimeout(()=>{ notif.classList.add('animate-fade-out'); setTimeout(()=>notif.remove(),300); },3000);
    };

    document.addEventListener('DOMContentLoaded',function(){
        const filterBtn = document.getElementById('applyFilters');
        filterBtn?.addEventListener('click',function(e){
            e.preventDefault();
            const sort = document.getElementById('sort').value;
            const url = new URL(window.location.href);
            if(sort) url.searchParams.set('sort',sort);
            window.location.href = url.toString();
        });
    });
</script>
@endpush
