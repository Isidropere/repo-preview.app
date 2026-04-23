@extends('layouts.app')

@section('title', 'Realizar intercambio - Cambialord')

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
                <li class="font-medium text-primary">Intercambios</li>
            </ol>
        </nav>

        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                Productos para Intercambios
            </h1>
            <span class="text-sm sm:text-lg text-primary font-medium">
                {{ $items->total() }} productos o servicios disponibles
            </span>
        </div>

        <!-- Grid de productos -->
        <div id="itemsContainer"
             class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
                @include('components.tarjeta-producto', ['item' => $item])
            @endforeach
        </div>

         <!-- Paginación -->
    @if($items->hasPages())
        <div class="flex justify-center mt-10">
            {{ $items->appends(request()->except('page'))->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
    </div>

    <!-- Carrusel (opcional) -->
    <div class="my-8">
        <!-- ... (código del carrusel) ... -->
    </div>

    {{-- Modal intercambio --}}
    @include('components.modal-intercambio')
</main>

@push('scripts')
<script>
window._urlLogin = "{{ route('login') }}";
window._urlItemsUsuario = "{{ route('carrito.items_usuario') }}";
window._urlNegStore = "{{ route('negociaciones.store') }}";
</script>
<script src="{{ asset('js/modal-intercambio.js') }}"></script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
<script>
async function addToCart(id_item, btn) {
    @guest window.location.href = '{{ route("login") }}'; return; @endguest
    const txt = btn.querySelector('.btn-txt'), orig = txt?.textContent ?? 'Agregar';
    btn.disabled = true; if (txt) txt.textContent = '...';
    try {
        const res = await fetch('{{ route("carrito.agregar") }}', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body: JSON.stringify({ id_item, cantidad: 1 })
        });
        const data = await res.json();
        if (data.success) { btn.style.background='#22c55e'; if(txt) txt.textContent='✓ Listo'; if(data.cart_count!==undefined) window.updateCartBadge(data.cart_count); setTimeout(()=>{btn.style.background='#3b82f6';btn.disabled=false;if(txt)txt.textContent=orig;},1800); }
        else { alert(data.message??'No se pudo agregar.'); btn.disabled=false; if(txt) txt.textContent=orig; }
    } catch { alert('Error de red.'); btn.disabled=false; if(txt) txt.textContent=orig; }
}
</script>
@endpush

@endsection
