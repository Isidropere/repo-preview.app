@extends('layouts.app')

@section('title', 'Cambialord - Mis talentos')

@section('content')
    <main class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
               @auth
                 <div class="mb-6">
                         <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                            <button onclick="window.history.back()" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary focus:outline-none">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </button>
                            </li>
                   
                        </ol>
                    </nav>
                    </div>
                  @endauth


            <div class="flex justify-between items-center mb-6">
                 <h3 class="text-4xl text-primary font-semibold mb-6">Mis Talentos</h3>
                <a href="{{ route('items.talento_create') }}" class="bg-primary hover:bg-hoverPrimary text-white px-4 py-2 rounded-md flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nuevo talento
                </a>
            </div>
        

                            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

           {{-- Mensaje general --}}
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Errores de validación individuales --}}
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="text-danger">{{ $error }}</div>
                @endforeach
            @endif

            

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Filtros -->
                <div class="p-4 border-b">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                  <div class="relative">
                       <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4  text-secondary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path> </svg>
                                </div>
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary" 
                                           placeholder="Buscar Productos o servicio...">
                         
                    </div>

                        <div class="flex items-center space-x-4">
                            <div>
                                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select id="statusFilter" class="text-gray-500 border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="all">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="2">Inactivos</option>
                                    <option value="3">Pausados</option>
                                </select>
                            </div>
                            <div>
                                <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select id="typeFilter" class="text-gray-500 border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="all">Todos</option>
                                    <option value="1">Venta</option>
                                    <option value="2">Intercambio</option>
                                    <option value="3">Venta/Intercambio</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                       

                <!-- Lista de talento -->
                <div class="divide-y divide-gray-200">

                    @forelse($items as $item)

                      @continue($item->id_categoria_item != 29)
                            <div class="p-4 hover:bg-gray-50 transition-colors product-item" 
                                 data-status="{{ $item->estatus }}" 
                                 data-type="{{ $item->tipo_trans }}">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <!-- Imagen del producto -->
                                                   <div class="w-24 flex-shrink-0">
                                    @if($item->imagenes->isNotEmpty())
                                        @php
                                            $image = $item->imagenes->firstWhere('orden_visualizacion', 1);
                                            // Default image URL in case we don't have a valid image
                                            $defaultImage = asset('storage/images/default-image.jpg');
                                            $imageUrl = $defaultImage;
                                            if ($image) {
                                                // Check that both ruta and nombre are not empty
                                                if (!empty($image->ruta) && !empty($image->nombre)) {
                                                    $directory = trim(str_replace('\\', '/', $image->ruta), '/');
                                                    $imageUrl = asset('storage/' . $directory . '/' . $image->nombre);
                                                }
                                            }
                                        @endphp
                                        <img src="{{ $imageUrl }}"
                                             alt="{{ $item->item }}"
                                             class="w-full h-24 object-cover rounded-lg"
                                             onerror="this.onerror=null;this.src='{{ $defaultImage }}'">
                                    @else
                                        <div class="w-full h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                    <!-- Contenedor principal -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col md:flex-row md:items-start gap-4">
                                            <!-- Información del producto -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <div class="min-w-0">
                                                        <h3 class="text-lg font-semibold text-gray-800 truncate">{{ $item->item }}</h3>
                                                        <p class="text-gray-600 truncate">{{ $item->categoria->categoria }}</p>
                                                    </div>
                                                 <span class="px-2 py-1 text-xs rounded-full flex-shrink-0 
                                                    {{ $item->estatus == 1 ? 'bg-green-100 text-green-800' : 
                                                       ($item->estatus == 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $item->estatus == 1 ? 'Activo' : 
                                                       ($item->estatus == 2 ? 'Inactivo' : 'Pausado') }}
                                                </span>

                                                </div>
                                            
                                                <div class="mt-2 flex flex-wrap items-center gap-4">
                                                    <div class="flex items-center text-gray-700">
                                                        <svg class="w-5 h-5 mr-1 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="font-medium">RD$ {{ number_format($item->valor, 2) }}</span>
                                                    </div>
                                                
                                                    <div class="flex items-center text-gray-700">
                                                        <svg class="w-5 h-5 mr-1 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                        <span>{{ $item->views_count ?? 0 }} visualizaciones</span>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Acciones - Siempre visibles -->
                                            <div class="flex-shrink-0">
                                                <div class="flex flex-row flex-wrap gap-2">
                                                    <a href="{{ route('items.VerDetalle', $item->id_item) }}" 
                                                       class="px-3 py-1.5 bg-blue-100 hover:text-primary rounded-md hover:bg-blue-200 flex items-center justify-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                        <abbr class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary"> Ver </abbr> 
                                                    </a>
                                                
                                                    <a href="{{ route('items.talentoedit', $item->id_item) }}" 
                                                       class="px-3 py-1.5 bg-yellow-100 hover:text-primary rounded-md hover:bg-yellow-200 flex items-center justify-center "  fill="currentColor">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        <abbr class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary"> Editar </abbr> 
                                                     
                                                    </a>
                                                
                                                    <form action="{{ route('items.destroy', $item->id_item) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="confirmDelete(this)" 
                                                                class="px-3 py-1.5 bg-red-100 hover:text-red-700 rounded-md hover:bg-red-200 flex items-center justify-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                              <abbr class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-700"> Eliminar </abbr> 
                                                        
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    
                                        <!-- Fecha - La movemos aquí para mejor flujo -->
                                        <div class="mt-2 flex items-center text-gray-700">
                                            <svg class="w-5 h-5 mr-1 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                       
                    @empty
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">No tienes mas talentos</h3>
                            <p class="mt-1 text-gray-500">Comienza agregando tu primer talento.</p>
                            <div class="mt-6">
                                <a href="{{ route('items.talento_create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-hoverPrimary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Nuevo Talento
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
                
                <!-- Paginación -->
            @if($items->hasPages())
                <div class="mt-8 px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                    {{ $items->appends(request()->except('page'))->links('vendor.pagination.custom') }}
                </div>
            @endif
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    // Filtrado de productos
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        const productItems = document.querySelectorAll('.product-item');
        
        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const typeValue = typeFilter.value;
            
            productItems.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const status = item.getAttribute('data-status');
                const type = item.getAttribute('data-type');
                
                const matchesSearch = title.includes(searchTerm);
                const matchesStatus = statusValue === 'all' || status === statusValue;
                const matchesType = typeValue === 'all' || type === typeValue;
                
                if (matchesSearch && matchesStatus && matchesType) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        searchInput.addEventListener('input', filterProducts);
        statusFilter.addEventListener('change', filterProducts);
        typeFilter.addEventListener('change', filterProducts);
    });
    
    // Confirmación para eliminar
    function confirmDelete(button) {
        if (confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) {
            button.closest('form').submit();
        }
    }
</script>
@endpush

@push('styles')
<style>
    .product-item {
        transition: all 0.2s ease;
    }
    
    .product-item:hover {
        transform: translateY(-2px);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        padding: 0;
    }
    
    .pagination li {
        margin: 0 4px;
    }
    
    .pagination a, .pagination span {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    
    .pagination a:hover {
        background-color: #f7fafc;
    }
    
    .pagination .active span {
        background-color: #4299e1;
        color: white;
        border-color: #4299e1;
    }
    
    .pagination .disabled span {
        color: #a0aec0;
        cursor: not-allowed;
    }

    /* Estilos adicionales para mejorar la visualización */
    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    @media (max-width: 768px) {
        .product-actions {
            flex-direction: row !important;
            justify-content: flex-start !important;
            margin-top: 1rem;
        }
    }
</style>
@endpush
