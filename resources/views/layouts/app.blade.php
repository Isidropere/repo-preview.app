<!DOCTYPE html>
<html lang="es">
<head>
     <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Cambialord - Tienda Online">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('imgs/logoTypes/logoFooter.png') }}">
    <link rel="stylesheet" href="{{ asset('css/_astro/index.D-AOIgCY.css') }}">
    <link rel="stylesheet" href="{{ asset('css/_astro/index.BneVErea.css') }}">
    <title>@yield('title', 'Cambialord - Inicio')</title>
    {{-- keen-slider: una sola vez, con preload --}}
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/keen-slider.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/keen-slider.min.css"></noscript>
    {{-- Font Awesome diferido --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></noscript>
    {{-- Leaflet solo si la página lo necesita --}}
    @stack('head_styles')
 

           <?php
           //$allowedReferer = 'http://localhost:40364'; // O usa 127.0.0.1 si aplica
           
           //if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $allowedReferer) !== 0) {
           //    http_response_code(403); // Prohibido
           //    exit('Acceso no autorizado - Solo desde localhost');
           //}
           ?>



    <style>
        #map {
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
        }
        .item-checkbox {
            transform: scale(1.3);
        }
        #applyFilters {
            display: block !important;
            visibility: visible !important;
        }
       /* Contenedor principal con tamaño fijo */
.product-image-container {
    width: 100%;
    height: 280px; /* Altura fija en píxeles */
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    border-radius: 8px;
}

/* Imagen principal */
.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
    transform: scale(0.98);
}

.product-image.loaded {
    opacity: 1;
    transform: scale(1);
}

/* Efecto hover sutil */
.product-image:hover {
    transform: scale(1.02);
}

/* Skeleton loader */
.skeleton-loader {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite linear;
}

/* Placeholder para errores */
.image-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none;
    align-items: center;
    justify-content: center;
    background: #f1f3f5;
}

.placeholder-icon {
    width: 48px;
    height: 48px;
    fill: none;
    stroke: #adb5bd;
    stroke-width: 1;
}

/* Estilo cuando no hay imagen */
.no-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f3f5;
    color: #adb5bd;


}

/* Animaciones */
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
        img, video, canvas {
            overflow: hidden;
        }
        .item-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

            .item-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            }

        .item-image-container {
            overflow: hidden;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-img-top {
            transition: transform 0.5s ease;
        }

        .item-card:hover .card-img-top {
            transform: scale(1.05);
        }
        #logoHeader[data-astro-cid-pwmmw5ba] {
            overflow: visible
        }

        header[data-astro-cid-pwmmw5ba] {
            position: relative
        }

        .relative[data-astro-cid-pwmmw5ba][data-tooltip] {
            position: relative
        }

            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:before,
            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:after {
                text-align: center;
                opacity: 0;
                pointer-events: none;
                position: absolute;
                transition: all .2s ease-in-out;
                z-index: 9999
            }

            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:before {
                content: attr(data-tooltip);
                background-color: #f58634;
                color: #fff;
                font-size: 12px;
                padding: 4px;
                border-radius: .25rem;
                bottom: -32px;
                left: 50%;
                transform: translate(-50%) translateY(-.5rem);
                white-space: nowrap;
                overflow: visible
            }

            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:after {
                content: "";
                border-width: .5rem;
                border-style: solid;
                border-color: #f58634 transparent transparent transparent;
                bottom: calc(100% - .25rem);
                left: 50%;
                transform: translate(-50%)
            }

            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:hover:before,
            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:hover:after {
                opacity: 1;
                transform: translate(-50%) translateY(0);
                z-index: 10000
            }

        @media (max-width: 1024px) {

            .no-tooltip[data-astro-cid-pwmmw5ba]:before,
            .no-tooltip[data-astro-cid-pwmmw5ba]:after {
                display: none
            }
        }

        .underline-animation[data-astro-cid-pwmmw5ba] {
            position: relative;
            display: inline-block
        }

            .underline-animation[data-astro-cid-pwmmw5ba]:after {
                content: "";
                position: absolute;
                width: 100%;
                transform: scaleX(0);
                height: 2px;
                bottom: 0;
                left: 0;
                background-color: #f58634;
                transform-origin: bottom right;
                transition: transform .25s ease-out
            }

            .underline-animation[data-astro-cid-pwmmw5ba]:hover:after {
                transform: scaleX(1);
                transform-origin: bottom left
            }

        html {
            scroll-behavior: smooth
        }

        body {
            font-family: Roboto, sans-serif
        }

        .number-slide1 {
            background: #40afff;
            background: linear-gradient(128deg, #40afff, #3f61ff)
        }

        .number-slide2 {
            background: #ff4b40;
            background: linear-gradient(128deg, #ff9a3f, #ff4b40)
        }

        .number-slide3 {
            background: #b6ff40;
            background: linear-gradient(128deg, #b6ff40, #3fff47);
            background: linear-gradient(128deg, #bdff53, #2bfa52)
        }

        .number-slide4 {
            background: #40fff2;
            background: linear-gradient(128deg, #40fff2, #3fbcff)
        }

        .number-slide5 {
            background: #ff409c;
            background: linear-gradient(128deg, #ff409c, #ff3f3f)
        }

        .number-slide6 {
            background: #404cff;
            background: linear-gradient(128deg, #404cff, #ae3fff)
        }

        .text-gradient {
            background: linear-gradient(to right, #479bd5, #f58634);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

            .pagination li {
                margin: 0 5px;
                list-style: none;
            }

                .pagination li a,
                .pagination li span {
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    color: #333;
                    text-decoration: none;
                }

                .pagination li.active span {
                    background-color: #007bff;
                    color: white;
                    border-color: #007bff;
                }

                .pagination li a:hover {
                    background-color: #f0f0f0;
                }
                #image-upload-container label {
        transition: all 0.3s ease;
    }

    #image-upload-container label:hover {
        border-color: #6366f1;
    }

    .preview-actions {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .group:hover .preview-actions {
        opacity: 1;
    }

    .file-name {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    #imagen_principal_preview {
        transition: opacity 0.3s ease;
    }

    .input-filled + .input-label,
    textarea.input-filled + .input-label {
        opacity: 0;
        transform: translateY(-10px);
    }

    .text-red-500 {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
    }

    /* Responsive grids para producto-detalle */
    @media (min-width: 768px) {
        .md\:grid-cols-product-detail {
            grid-template-columns: 320px 1fr !important;
        }
        .md\:grid-cols-desc-specs {
            grid-template-columns: 1fr 310px !important;
        }
    }
    @media (min-width: 640px) {
        .sm\:grid-cols-related-products {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }
    @media (min-width: 1024px) {
        .sm\:grid-cols-related-products {
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        }
    }

    /* Hero home responsive */
    @media (max-width: 640px) {
        .hs-carousel .h-\[530px\] {
            height: 420px;
        }
    }

    /* Menú hamburguesa: cuando está abierto en móvil, permitir scroll si el contenido es largo */
    @media (max-width: 1023px) {
        #navbar-collapse-with-animation.hs-collapse-open {
            overflow-y: auto !important;
            max-height: calc(100vh - 80px);
        }
    }
    
    /* Indicador de producto en carrito */
    .in-cart {
        background-color: #10b981 !important; /* emerald-500 */
        border-color: #059669 !important; /* emerald-600 */
        color: white !important;
    }
    </style>
    <script type="module" src="/js/hoisted.D4SCdckR.js"></script>
</head>

<body>

    <!-- Encabezado -->
    <header>
        @include('partials.header')
        @include('partials.menu')
    </header>
    @auth
    <x-negociaciones-modal />
    @endauth
    <!-- Contenido dinámico -->
   <main class="py-0">
        @yield('content')
        <!-- Loader global -->
<div id="globalLoader" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
    <div class="bg-white rounded-lg p-6 flex flex-col items-center shadow">
        <svg class="animate-spin h-8 w-8 text-blue-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
        </svg>
        <span class="text-gray-700 font-semibold">Procesando...</span>
    </div>
</div>
    </main>

    <!-- Pie de página -->
    <footer >
        @include('partials.footer')
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/keen-slider.min.js" defer></script>
    @stack('scripts')
      <script src="{{ asset('js/global-loader.js') }}"></script>

        <!-- Carga tus librerías base aquí -->
   <!--<script>
       document.addEventListener("DOMContentLoaded", function () {
    
    function convertAllImagesToWebP(selector = "img", quality = 0.8) {
        document.querySelectorAll(selector).forEach((imgElement) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.src = imgElement.src;

            img.onload = function () {
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");

                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0, img.width, img.height);

                canvas.toBlob((blob) => {
                    if (blob) {
                        const webpURL = URL.createObjectURL(blob);
                        imgElement.src = webpURL;
                          console.log(`Imagen convertida a WebP: ${imgElement.src}`);
                    }
                }, "image/webp", quality);
            };
        });
    }

    convertAllImagesToWebP(); // Convierte todas las imágenes en la página
       });
    //   //console.log("Termino la ejecucion");
 </script>-->
    <script>
    // Define la URL para agregar al carrito
    window.urlAgregarCarrito = "{{ route('carrito.agregar') }}";

    // Define el token CSRF globalmente
        window.csrfToken = "{{ csrf_token() }}";
    
    @if(session('alerta'))
        alert("{{ session('alerta') }}");
    @endif

    // Función global para sincronizar indicadores de carrito
    window.syncCartIndicators = async function() {
        @guest return; @endguest
        try {
            const res = await fetch('{{ route("carrito.item_ids") }}');
            const itemIds = await res.json();
            
            // Buscar todos los botones de "Agregar al carrito"
            document.querySelectorAll('[id^="add-to-cart-"]').forEach(btn => {
                const itemId = parseInt(btn.id.replace('add-to-cart-', ''));
                const btnText = btn.querySelector('.button-text');
                const originalText = btn.dataset.originalText || (btnText ? btnText.textContent : 'Agregar');
                
                // Guardar texto original si no existe
                if (!btn.dataset.originalText) btn.dataset.originalText = originalText;
                
                if (itemIds.includes(itemId)) {
                    btn.classList.add('in-cart');
                    if (btnText) {
                        btnText.innerHTML = '<i class="fas fa-check mr-1"></i> En el carrito';
                    }
                } else {
                    btn.classList.remove('in-cart');
                    if (btnText) btnText.textContent = originalText;
                }
            });
        } catch (e) {
            console.error('Error sincronizando carrito:', e);
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.syncCartIndicators();
    });

</script>

  

</body>
<script>
    function abrirNegociacionesModal() {
        document.getElementById('negociacionesNotificacionesModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function cerrarNegociacionesModal() {
        document.getElementById('negociacionesNotificacionesModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Cerrar con ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarNegociacionesModal();
        }
    });

    document.addEventListener("DOMContentLoaded",function(){

@auth
        const userId = {{ Auth::id() }};

        if (typeof Echo !== 'undefined') {
            Echo.channel('notificaciones.'+userId)
            .listen('NuevaNotificacion',(e)=>{
                const contador = document.getElementById("contadorNotificaciones");
                if (contador) contador.textContent = parseInt(contador.textContent)+1;
                const ping = document.getElementById("pingNotificacion");
                if (ping) ping.classList.remove("hidden");
            });
        }
@endauth

        });

</script>

</html>
