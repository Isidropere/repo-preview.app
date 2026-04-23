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
                @include('components.tarjeta-producto', ['item' => $item])
            @endforeach
        </div>
        @endif
        @else
        <!-- Grid de productos normales -->
        <div id="itemsContainer" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
                @include('components.tarjeta-producto', ['item' => $item])
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

    @include('components.modal-intercambio')
</main>

@push('scripts')
<script>
window._urlLogin = "{{ route('login') }}";
@auth
window._urlItemsUsuario = "{{ route('carrito.items_usuario') }}";
window._urlNegStore = "{{ route('negociaciones.store') }}";
@endauth
</script>
<script src="{{ asset('js/modal-intercambio.js') }}"></script>
@endpush

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
            // Actualizar contador del carrito
            if (data.cart_count !== undefined) window.updateCartBadge(data.cart_count);
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
