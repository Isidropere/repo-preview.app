@extends('layouts.app')

@section('title', 'Cambialord - Detalle del Producto')

@section('content')
<main class="min-h-screen py-8 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!--  @include('components.btn-volver', ['backUrl' => route('home')])-->
        <!-- Breadcrumbs -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                            Inicio 
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            <a href="{{ route('categorias.show', $item->categoria->slug) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary md:ml-2">{{ $item->categoria->categoria }}</a>
                        </div>
                    </li>
                   
                      <li class="inline-flex items-center">
                            <a href="{{ route('items.admintalento') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary focus:outline-none">
                                 <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                Volver talentos administrador
                            </a>
                            </li>
                </ol>
            </nav>
        </div>

        <!-- Product Detail Section -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="md:flex">
                <!-- Image Gallery -->
                <div class="md:w-1/2 p-4">
                    <!-- Main Image -->
                    @php 
                        $hasApproved = $item && $item->imagenes && $item->imagenes->where('estado', 'aprobado')->isNotEmpty();
                        $imgsToShow = $hasApproved ? $item->imagenes->where('estado', 'aprobado') : ($item->imagenes ?? collect());
                    @endphp
                    <div style="position:relative;background:#f8fafc;border-radius:1rem;overflow:hidden;min-height:300px;display:flex;align-items:center;justify-content:center;border:1px solid #f1f5f9;">
                        @if($imgsToShow->isNotEmpty())
                            @php $firstImg = $imgsToShow->first(); @endphp
                                <img id="mainImage"
                                     src="{{ \App\Helpers\ImageHelper::urlMedia($firstImg->ruta, $firstImg->nombre) }}"
                                     alt="{{ $item->item }}" class="w-full h-full object-contain"
                                     style="cursor:zoom-in;"
                                     onclick="openZoom(currentMainImageIndex)">
                                
                                <button onclick="openZoom(currentMainImageIndex)"
                                        style="position:absolute;bottom:0.6rem;right:0.6rem;background:rgba(255,255,255,.92);border:none;border-radius:9999px;padding:0.35rem;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.12);z-index:10;">
                                    <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                <span class="text-gray-500">Imagen en espera de aprobación</span>
                            </div>
                        @endif
                    </div>
              
             <!-- Thumbnails -->
                @if($imgsToShow->count() > 0)
                <div style="display:flex;gap:0.75rem;overflow-x:auto;padding-bottom:0.5rem;scrollbar-width:none;-ms-overflow-style:none;">
                    @foreach($imgsToShow as $index => $imagen)


                </div>

                <!-- Product Info -->
                <div class="md:w-1/2 p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $item->item }}</h1>
                            <div class="flex items-center mt-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $item->categoria->categoria }}</span>
                                @if($item->condicion == 1)
                                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Nuevo</span>
                                @elseif($item->condicion == 2)
                                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">Usado - Como nuevo</span>
                                @elseif($item->condicion == 3)
                                    <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded">Usado - Buen estado</span>
                                @else
                                    <span class="ml-2 bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Usado - Aceptable</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-bold text-gray-900">RD$ {{ number_format($item->valor, 2) }}</span>
                            <div class="text-sm text-gray-500 mt-1">
                                @if($item->tipo_trans == 1)
                                    Solo venta
                                @elseif($item->tipo_trans == 2)
                                    Solo intercambio
                                @else
                                    Venta/Intercambio
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-lg font-medium text-gray-900">Descripción</h2>
                        <p class="mt-2 text-gray-600 whitespace-pre-line">{{ $item->presentacion ?? 'No hay descripción disponible' }}</p>
                    </div>

                    @if($item->peso_lbs > 0 || $item->alto_cm > 0 || $item->ancho_cm > 0 || $item->profundo_cm > 0)
                        <div class="mt-6">
                            <h2 class="text-lg font-medium text-gray-900">Dimensiones y Peso</h2>
                            <div class="mt-2 grid grid-cols-2 gap-4">
                                @if($item->peso_lbs > 0)
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-500">Peso</p>
                                        <p class="font-medium">{{ $item->peso_lbs }} lbs</p>
                                    </div>
                                @endif
                                @if($item->alto_cm > 0)
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-500">Alto</p>
                                        <p class="font-medium">{{ $item->alto_cm }} cm</p>
                                    </div>
                                @endif
                                @if($item->ancho_cm > 0)
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-500">Ancho</p>
                                        <p class="font-medium">{{ $item->ancho_cm }} cm</p>
                                    </div>
                                @endif
                                @if($item->profundo_cm > 0)
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-500">Profundo</p>
                                        <p class="font-medium">{{ $item->profundo_cm }} cm</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-medium text-gray-900">Información del vendedor</h2>
                        <div class="mt-4 flex items-center">
                            <div class="flex-shrink-0">
                                <img class="h-10 w-10 rounded-full" src="{{ $item->usuario && $item->usuario->foto_perfil && ($item->usuario->foto_perfil_estado ?? 'pendiente') === 'aprobado' ? \App\Helpers\ImageHelper::urlPerfil($item->usuario->foto_perfil ?? null) : asset(\App\Helpers\ImageHelper::DEFAULT_PROFILE) }}" alt="Foto del vendedor" onerror="this.src='{{ asset(\App\Helpers\ImageHelper::DEFAULT_PROFILE) }}'">
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $item->usuario->nombres ?? 'N/A' }} {{ $item->usuario->apellidos ?? '' }}</p>
                                <p class="text-sm text-gray-500">Miembro desde {{ $item->usuario->created_at->format('M Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-2">
                            <a href="{{ $item->id_categoria_item == 29 ? route('items.talentoedit', $item->slug) : route('items.edit', $item->slug) }}" class="bg-blue-600 hover:bg-gray-400 text-white py-2 px-4 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center">
                                Editar
                            </a>
                            <button type="button" onclick="openContactModal()" class="bg-gray-600 hover:bg-gray-400 text-white py-2 px-4 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-center">
                                Contactar al Administrador
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @auth
            @if(auth()->user()->id === $item->user_id && $relatedItems->count() > 0)
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900">Tus productos o servicios relacionados</h2>
                    <div class="mt-6 grid gap-y-10 gap-x-6" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));">
                      @foreach($relatedItems as $relatedItem)
                        <div class="group relative bg-white shadow rounded-lg overflow-hidden">
                            @php 
                                    $hasApp = $relatedItem->imagenes->where('estado', 'aprobado')->isNotEmpty();
                                    $relImgs = $hasApp ? $relatedItem->imagenes->where('estado', 'aprobado') : $relatedItem->imagenes;
                                    $displayImage = $relImgs->firstWhere('orden_visualizacion', 1) ?? $relImgs->first();
                                @endphp

                            @if($displayImage && $displayImage->nombre != null)
                                <div class="min-h-80 aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-lg bg-gray-200 group-hover:opacity-75 lg:aspect-none lg:h-80">
                                    <img src="{{ \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $displayImage->nombre) }}" 
                                         alt="{{ $relatedItem->item }}" 
                                         class="h-full w-full object-cover object-center lg:h-full lg:w-full"
                                         onerror="this.onerror=null;this.src='{{ asset('imgs/defaults/producto_default.svg') }}'">
                                </div>
                            @else
                                <div class="min-h-80 aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-lg bg-gray-200 group-hover:opacity-75 lg:aspect-none lg:h-80 flex items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
        
                            <div class="p-4">
                                <div class="flex justify-between">
                                    <div>
                                        <h3 class="text-sm text-gray-700">
                                            <a href="{{ route('items.VerDetalle', $relatedItem->slug) }}">
                                                <span aria-hidden="true" class="absolute inset-0"></span>
                                                {{ Str::limit($relatedItem->item, 30) }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $relatedItem->categoria->categoria }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">RD$ {{ number_format($relatedItem->valor, 2) }}</p>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-xs px-2 py-1 rounded-full 
                                        @if($relatedItem->condicion == 1) bg-green-100 hover:bg-hoverPrimary
                                        @elseif($relatedItem->condicion == 2) bg-yellow-100 hover:bg-hoverPrimary
                                        @elseif($relatedItem->condicion == 3) bg-orange-100 hover:bg-hoverPrimary
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $relatedItem->condicion_text }}
                                    </span>
                                    <a href="{{ route('items.VerDetalle', $relatedItem->slug) }}" 
                                       class="text-xs font-bold text-gray-500 hover:text-orange-600 flex items-center gap-1">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            @endif
        @endauth
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Contactar a {{ $item->usuario->Nombre }}</h3>
            <form id="contactForm" action="{{ route('messages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $item->usuario->id }}">
                <input type="hidden" name="item_id" value="{{ $item->id_item }}">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700">Mensaje</label>
                    <textarea id="message" name="message" rows="4" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeContactModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Cancelar</button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-hoverPrimary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Enviar mensaje</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ZOOM --}}
    <div id="zoomModal" onclick="closeZoom()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);z-index:9999;overflow-x:auto;overflow-y:hidden;align-items:center;justify-content:flex-start;cursor:zoom-out;user-select:none;scroll-snap-type:x mandatory;">
        <button onclick="closeZoom()" style="position:fixed;top:1rem;right:1rem;background:rgba(255,255,255,.2);border:none;color:#fff;cursor:pointer;font-size:1.5rem;width:2.5rem;height:2.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:10000;transition:background .2s;outline:none;" onmouseover="this.style.backgroundColor='rgba(255,255,255,.4)'" onmouseout="this.style.backgroundColor='rgba(255,255,255,.2)'">✕</button>
        
        @if($imgsToShow->count() > 1)
        <button onclick="prevZoomImage(event)" id="btnZoomPrev" style="position:fixed;left:1rem;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.2);border:none;color:#fff;cursor:pointer;font-size:1.5rem;width:2.5rem;height:2.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:10000;outline:none;">‹</button>
        <button onclick="nextZoomImage(event)" id="btnZoomNext" style="position:fixed;right:1rem;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.2);border:none;color:#fff;cursor:pointer;font-size:1.5rem;width:2.5rem;height:2.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:10000;outline:none;">›</button>
        @endif

        <div id="zoomTrack" style="display:inline-flex;align-items:center;height:100%;" onclick="event.stopPropagation()">
            @foreach($imgsToShow as $imagen)
                <div class="zoom-slide" style="width:100vw;height:100vh;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;scroll-snap-align:center;overflow:hidden;position:relative;">
                    <img class="zoom-img-el" src="{{ \App\Helpers\ImageHelper::urlMedia($imagen->ruta, $imagen->nombre) }}" alt="Zoom" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:0.5rem;cursor:zoom-in;transition:transform 0.25s ease, max-width 0.25s ease, max-height 0.25s ease;" onclick="toggleZoom(event, this)">
                </div>
            @endforeach
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    let currentMainImageIndex = 0;
    function changeMainImage(element, index) {
        document.getElementById('mainImage').src = element.src;
        if (index !== undefined) {
            currentMainImageIndex = index;
        }
    }

    function openContactModal() {
        @auth
            document.getElementById('contactModal').classList.remove('hidden');
        @else
            window.location.href = "{{ route('login') }}";
        @endauth
    }

    function closeContactModal() {
        document.getElementById('contactModal').classList.add('hidden');
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('contactModal');
        if (event.target === modal) {
            closeContactModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
         const img = document.getElementById('mainImage');
         if (img) {
             img.style.width = '400px';
             img.style.height = '400px';
             img.style.objectFit = 'cover';
         }
    });

    let isZoomed = false;
    let currentZoomIndex = 0;

    function openZoom(index) {
        const m = document.getElementById('zoomModal');
        currentZoomIndex = index || 0;
        
        isZoomed = false;
        m.querySelectorAll('.zoom-slide').forEach(slide => {
            slide.style.overflow = 'hidden';
            slide.scrollLeft = 0;
            slide.scrollTop = 0;
            const img = slide.querySelector('.zoom-img-el');
            if (img) {
                img.style.maxWidth = '90vw';
                img.style.maxHeight = '90vh';
                img.style.width = 'auto';
                img.style.height = 'auto';
                img.style.cursor = 'zoom-in';
            }
        });

        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            const slides = m.querySelectorAll('.zoom-slide');
            if (slides[currentZoomIndex]) {
                slides[currentZoomIndex].scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'start' });
            }
            updateZoomButtons();
        }, 50);
    }

    function closeZoom() {
        document.getElementById('zoomModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function updateZoomButtons() {
        const m = document.getElementById('zoomModal');
        const slides = m.querySelectorAll('.zoom-slide');
        const prev = document.getElementById('btnZoomPrev');
        const next = document.getElementById('btnZoomNext');
        if (prev) prev.style.display = currentZoomIndex > 0 ? 'flex' : 'none';
        if (next) next.style.display = currentZoomIndex < slides.length - 1 ? 'flex' : 'none';
    }

    function prevZoomImage(e) {
        e.stopPropagation();
        if (currentZoomIndex > 0) {
            currentZoomIndex--;
            const m = document.getElementById('zoomModal');
            const slides = m.querySelectorAll('.zoom-slide');
            slides[currentZoomIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            updateZoomButtons();
        }
    }

    // Corregido el nombre a nextZoomImage
    function nextZoomImage(e) {
        e.stopPropagation();
        const m = document.getElementById('zoomModal');
        const slides = m.querySelectorAll('.zoom-slide');
        if (currentZoomIndex < slides.length - 1) {
            currentZoomIndex++;
            slides[currentZoomIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            updateZoomButtons();
        }
    }

    function toggleZoom(e, img) {
        if (e) e.stopPropagation();
        const slide = img.parentElement;
        
        if (!isZoomed) {
            img.style.maxWidth = 'none';
            img.style.maxHeight = 'none';
            img.style.width = '180%';
            img.style.height = 'auto';
            img.style.cursor = 'zoom-out';
            slide.style.overflow = 'auto';
            isZoomed = true;
        } else {
            img.style.maxWidth = '90vw';
            img.style.maxHeight = '90vh';
            img.style.width = 'auto';
            img.style.height = 'auto';
            img.style.cursor = 'zoom-in';
            slide.style.overflow = 'hidden';
            slide.scrollLeft = 0;
            slide.scrollTop = 0;
            isZoomed = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('zoomModal');
        if (!modal) return;
        
        modal.addEventListener('scroll', function() {
            const width = modal.clientWidth;
            if (width === 0) return;
            const slides = modal.querySelectorAll('.zoom-slide');
            const index = Math.round(modal.scrollLeft / width);
            if (index !== currentZoomIndex && index >= 0 && index < slides.length) {
                currentZoomIndex = index;
                updateZoomButtons();
            }
        });

        let isDragging = false;
        let startX, startY;
        let scrollLeft, scrollTop;
        
        modal.addEventListener('mousedown', function(e) {
            if (!isZoomed || e.target.tagName === 'BUTTON') return;
            const img = e.target;
            if (!img.classList.contains('zoom-img-el')) return;
            const slide = img.parentElement;
            
            isDragging = true;
            startX = e.pageX - slide.offsetLeft;
            startY = e.pageY - slide.offsetTop;
            scrollLeft = slide.scrollLeft;
            scrollTop = slide.scrollTop;
            img.style.cursor = 'grabbing';
        });
        
        modal.addEventListener('mouseleave', function() {
            if (isDragging) {
                isDragging = false;
                const img = modal.querySelector('.zoom-img-el');
                if (img) img.style.cursor = 'zoom-out';
            }
        });
        
        modal.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                const img = modal.querySelector('.zoom-img-el');
                if (img) img.style.cursor = 'zoom-out';
            }
        });
        
        modal.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
            const img = e.target;
            if (!img.classList.contains('zoom-img-el')) return;
            const slide = img.parentElement;
            
            const x = e.pageX - slide.offsetLeft;
            const y = e.pageY - slide.offsetTop;
            const walkX = (x - startX) * 1.5;
            const walkY = (y - startY) * 1.5;
            slide.scrollLeft = scrollLeft - walkX;
            slide.scrollTop = scrollTop - walkY;
        });
    });
</script>
@endpush

@push('styles')
<style>
    #mainImage {
        transition: opacity 0.3s ease;
    }

    .thumbnail:hover {
        border-color: #6366f1;
    }
</style>
@endpush
