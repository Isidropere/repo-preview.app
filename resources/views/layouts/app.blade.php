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
    {{-- keen-slider (no crítico) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/keen-slider.min.css" media="print" onload="this.media='all'">
    {{-- Font Awesome (no crítico) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/keen-slider.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </noscript>
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
            position: relative;
            overflow: visible
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
                padding: 4px 8px;
                border-radius: .25rem;
                top: calc(100% + 8px);
                left: 50%;
                transform: translate(-50%) translateY(.5rem);
                white-space: nowrap;
                overflow: visible
            }

            .relative[data-astro-cid-pwmmw5ba][data-tooltip]:after {
                content: "";
                border-width: .5rem;
                border-style: solid;
                border-color: transparent transparent #f58634 transparent;
                top: calc(100% - 2px);
                left: 50%;
                transform: translate(-50%) translateY(.5rem)
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

        *, *::before, *::after {
            box-sizing: border-box;
        }

        input, button, select, textarea {
            max-width: 100%;
        }

        html {
            scroll-behavior: smooth;
            max-width: 100%;
        }

        body {
            font-family: Roboto, sans-serif;
            max-width: 100%;
            overflow-x: hidden;
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
        .hs-carousel.h-\[530px\] {
            height: 250px !important;
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
    {{-- Modal de confirmación para categoría Adultos --}}
    <div id="modalAdultos" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem;">
        <div style="background:#fff;border-radius:1.25rem;width:100%;max-width:28rem;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;margin:auto;">
            <div style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:0.75rem;">
                <div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.2);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 style="color:#fff;font-size:1rem;font-weight:700;margin:0;">Contenido para adultos</h3>
                    <p style="color:rgba(255,255,255,0.8);font-size:0.75rem;margin:0;">Debes aceptar los terminos para continuar</p>
                </div>
            </div>
            <div style="padding:1.25rem 1.5rem;max-height:300px;overflow-y:auto;">
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:0.75rem;padding:1rem;margin-bottom:1rem;">
                    <p style="font-size:0.85rem;color:#991b1b;font-weight:600;margin:0 0 0.5rem;">Aviso importante:</p>
                    <ul style="font-size:0.8rem;color:#7f1d1d;margin:0;padding-left:1.25rem;line-height:1.6;">
                        <li>Esta seccion contiene productos y servicios destinados exclusivamente a personas mayores de 18 años.</li>
                        <li>Al acceder, confirmas que eres mayor de edad segun las leyes de tu pais.</li>
                        <li>Cambialord no se hace responsable del uso indebido de esta seccion por menores de edad.</li>
                        <li>El contenido puede incluir articulos de naturaleza sensible.</li>
                        <li>Queda prohibida la publicacion de contenido ilegal o que viole los derechos de terceros.</li>
                    </ul>
                </div>
                <label style="display:flex;align-items:flex-start;gap:0.5rem;cursor:pointer;font-size:0.85rem;color:#374151;margin-bottom:1.25rem;">
                    <input type="checkbox" id="checkAdultos" style="margin-top:3px;width:18px;height:18px;accent-color:#dc2626;">
                    <span>He leido y acepto los <strong>Terminos y Condiciones</strong> de la seccion de adultos. Confirmo que soy mayor de 18 años.</span>
                </label>

                <div id="reauthAdultos" style="display:none;border-top:1px solid #eee;padding-top:1.25rem;">
                    <p style="font-size:0.85rem;font-weight:600;color:#374151;margin:0 0 1rem;">Re-confirmación de identidad:</p>
                    <div style="margin-bottom:0.75rem;">
                        <input type="email" id="adultosEmail" placeholder="Correo electrónico" 
                               value="{{ auth()->user()->email ?? '' }}" readonly
                               style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.85rem;background:#f9fafb;color:#6b7280;outline:none;">
                    </div>
                    <div style="margin-bottom:0.5rem;">
                        <input type="password" id="adultosPass" placeholder="Tu contraseña" 
                               style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.85rem;outline:none;focus:border-red-500;">
                    </div>
                    <div id="errorAdultos" style="color:#dc2626;font-size:0.75rem;font-weight:500;margin-top:0.25rem;display:none;"></div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex gap-3" style="padding:0.75rem 1.5rem 1.25rem;display:flex;gap:0.75rem;">
                <button onclick="cerrarModalAdultos()" style="flex:1;padding:0.6rem;border:1px solid #d1d5db;background:#fff;color:#6b7280;border-radius:0.75rem;font-size:0.85rem;font-weight:600;cursor:pointer;">Cancelar</button>
                <button id="btnAceptarAdultos" onclick="aceptarAdultos()" disabled style="flex:1;padding:0.6rem;border:none;background:#d1d5db;color:#fff;border-radius:0.75rem;font-size:0.85rem;font-weight:600;cursor:not-allowed;">Confirmar acceso</button>
            </div>
        </div>
    </div>
    <script>
    var _adultosUrl = '';
    function confirmarAdultos(e, el) {
        if (sessionStorage.getItem('adultos_aceptado') === '1') return true;
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        _adultosUrl = el ? (el.href || window.location.href) : window.location.href;
        
        const modal = document.getElementById('modalAdultos');
        const check = document.getElementById('checkAdultos');
        const reauth = document.getElementById('reauthAdultos');
        const pass = document.getElementById('adultosPass');
        const error = document.getElementById('errorAdultos');
        const btn = document.getElementById('btnAceptarAdultos');

        if (check) check.checked = false;
        if (reauth) reauth.style.display = 'none';
        if (pass) pass.value = '';
        if (error) error.style.display = 'none';
        if (btn) {
            btn.disabled = true;
            btn.style.background = '#d1d5db';
            btn.style.cursor = 'not-allowed';
        }
        if (modal) {
            modal.style.display = 'flex';
        }
        return false;
    }
    function cerrarModalAdultos() {
        const modal = document.getElementById('modalAdultos');
        if (modal) modal.style.display = 'none';
    }
    async function aceptarAdultos() {
        const pass = document.getElementById('adultosPass').value;
        const email = document.getElementById('adultosEmail').value;
        const errDiv = document.getElementById('errorAdultos');
        const btn = document.getElementById('btnAceptarAdultos');

        if (!pass) {
            errDiv.textContent = 'Por favor ingresa tu contraseña';
            errDiv.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Verificando...';
        errDiv.style.display = 'none';

        try {
            const res = await fetch('{{ route("adultos.verificar", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email, password: pass })
            });

            const data = await res.json();
            if (data.success) {
                sessionStorage.setItem('adultos_aceptado', '1');
                document.getElementById('modalAdultos').style.display = 'none';
                if (_adultosUrl) window.location.href = _adultosUrl;
            } else {
                errDiv.textContent = data.message || 'Error de verificación';
                errDiv.style.display = 'block';
            }
        } catch (e) {
            errDiv.textContent = 'Error de conexión';
            errDiv.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Confirmar acceso';
        }
    }
    document.addEventListener('change', function(e) {
        if (e.target.id === 'checkAdultos') {
            var btn = document.getElementById('btnAceptarAdultos');
            var reauth = document.getElementById('reauthAdultos');
            if (btn) {
                btn.disabled = !e.target.checked;
                btn.style.background = e.target.checked ? '#dc2626' : '#d1d5db';
                btn.style.cursor = e.target.checked ? 'pointer' : 'not-allowed';
            }
            if (reauth) {
                reauth.style.display = e.target.checked ? 'block' : 'none';
            }
        }
    });
    </script>


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

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/keen-slider.min.js" defer></script>
      <script src="{{ asset('js/global-loader.js') }}" defer></script>

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
    window._usuarioAutenticado = {{ auth()->check() ? 'true' : 'false' }};
    
    @if(session('alerta'))
        alert("{{ session('alerta') }}");
    @endif

    // Función global para actualizar el badge del carrito
    window.updateCartBadge = function(count) {
        var badge = document.getElementById('cartBadge');
        if (badge && count !== undefined) {
            badge.textContent = count;
            // Animación de pulso
            badge.style.transform = 'scale(1.4)';
            setTimeout(function() { badge.style.transform = 'scale(1)'; }, 200);
        }
    };

    // Función global para sincronizar indicadores de carrito (sin parpadeo)
    window.syncCartIndicators = async function() {
        @guest return; @endguest
        try {
            const res = await fetch('{{ route("carrito.item_ids", [], false) }}');
            const itemIds = await res.json();
            
            document.querySelectorAll('[id^="add-to-cart-"]').forEach(btn => {
                const itemId = parseInt(btn.id.replace('add-to-cart-', ''));
                const btnText = btn.querySelector('.button-text') || btn.querySelector('.btn-txt');
                const originalText = btn.dataset.originalText || (btnText ? btnText.textContent : 'Agregar');
                
                if (!btn.dataset.originalText) btn.dataset.originalText = originalText;
                
                if (itemIds.includes(itemId)) {
                    btn.classList.add('in-cart');
                    if (btnText) btnText.textContent = '✓ En carrito';
                } else {
                    btn.classList.remove('in-cart');
                    if (btnText) btnText.textContent = originalText;
                }
            });
        } catch (e) { /* silencioso */ }
    };

    // Ejecutar después de que la página esté completamente cargada (no en DOMContentLoaded)
    window.addEventListener('load', function() {
        setTimeout(window.syncCartIndicators, 500);
    });

    // Función global para compartir items
    window.compartirItem = function(titulo, url) {
        if (navigator.share) {
            navigator.share({
                title: titulo,
                text: 'Mira este artículo en Cambialord: ' + titulo,
                url: url
            }).catch(function(err) {
                // Fallback si navigator.share falla (ej. bloqueado en HTTP o cancelado por usuario)
                ejecutarCopia(url);
            });
        } else {
            ejecutarCopia(url);
        }
    };

    function ejecutarCopia(url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                alert('¡Enlace copiado al portapapeles!');
            }).catch(function(err) {
                fallbackCopiar(url);
            });
        } else {
            fallbackCopiar(url);
        }
    }

    function fallbackCopiar(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                alert('¡Enlace copiado al portapapeles!');
            } else {
                alert('No se pudo copiar el enlace. URL: ' + text);
            }
        } catch (err) {
            alert('No se pudo copiar el enlace. URL: ' + text);
        }
        document.body.removeChild(textArea);
    }

</script>

  


<script>
    function abrirNegociacionesModal() {
        var m = document.getElementById('negociacionesNotificacionesModal');
        if (m) { m.classList.remove('hidden'); m.style.display = 'flex'; }
        document.body.classList.add('overflow-hidden');
    }

    function cerrarNegociacionesModal() {
        var m = document.getElementById('negociacionesNotificacionesModal');
        if (m) { m.classList.add('hidden'); m.style.display = 'none'; }
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

<script>
    // Global image error handler using capturing phase to catch errors on any <img> tag
    window.addEventListener('error', function (event) {
        var target = event.target;
        if (target && target.tagName && target.tagName.toLowerCase() === 'img') {
            if (!target.getAttribute('data-fallback-tried')) {
                target.setAttribute('data-fallback-tried', 'true');
                
                var fallback = '/imgs/defaults/producto_default.svg';
                var src = target.src || '';
                var className = target.className || '';
                
                // profile / avatar / rounded-full
                if (src.includes('perfil') || src.includes('profile') || src.includes('avatar') || className.includes('perfil') || className.includes('avatar') || className.includes('rounded-full')) {
                    fallback = '/imgs/defaults/profile_default.svg';
                } else if (src.includes('talento') || src.includes('servicio') || className.includes('talento') || className.includes('servicio')) {
                    fallback = '/imgs/defaults/servicio_default.svg';
                }
                
                target.src = fallback;
            }
        }
    }, true); // capturing phase is crucial as error events do not bubble
</script>

<!-- Sistema de Notificaciones Premium (Toasts) -->
<div id="toast-container"></div>

<style>
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 350px;
    pointer-events: none;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.toast-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background-color: #ffffff;
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border: 1px solid #f3f4f6;
    transform: translateX(120%);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
    opacity: 0;
    pointer-events: auto;
}
.toast-card.show {
    transform: translateX(0);
    opacity: 1;
}
.toast-card.hide {
    transform: translateX(120%);
    opacity: 0;
}
.toast-card-success { border-left: 4px solid #10b981; }
.toast-card-error { border-left: 4px solid #ef4444; }
.toast-card-warning { border-left: 4px solid #f59e0b; }
.toast-card-info { border-left: 4px solid #3b82f6; }

.toast-icon { flex-shrink: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
.toast-icon svg { width: 100%; height: 100%; }
.toast-icon-success { color: #10b981; }
.toast-icon-error { color: #ef4444; }
.toast-icon-warning { color: #f59e0b; }
.toast-icon-info { color: #3b82f6; }

.toast-content { flex-grow: 1; }
.toast-title { font-weight: 700; color: #111827; font-size: 14px; margin: 0; }
.toast-message { color: #4b5563; font-size: 12px; margin: 3px 0 0 0; line-height: 1.4; }

.toast-close {
    background: transparent;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    padding: 0;
    margin-left: 4px;
    transition: color 0.15s ease;
}
.toast-close:hover { color: #4b5563; }
</style>

<script>
window.showToast = function(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    
    let iconClass = 'toast-icon-success';
    let iconSvg = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
    let cardClass = 'toast-card-success';
    let title = 'Éxito';

    if (type === 'error') {
        iconClass = 'toast-icon-error';
        iconSvg = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
        cardClass = 'toast-card-error';
        title = 'Error';
    } else if (type === 'warning' || type === 'alerta') {
        iconClass = 'toast-icon-warning';
        iconSvg = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
        cardClass = 'toast-card-warning';
        title = 'Atención';
    } else if (type === 'info') {
        iconClass = 'toast-icon-info';
        iconSvg = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
        cardClass = 'toast-card-info';
        title = 'Información';
    }

    toast.className = `toast-card ${cardClass}`;
    toast.innerHTML = `
        <div class="toast-icon ${iconClass}">
            ${iconSvg}
        </div>
        <div class="toast-content">
            <p class="toast-title">${title}</p>
            <p class="toast-message">${message}</p>
        </div>
        <button class="toast-close">&times;</button>
    `;

    // Click to close
    toast.querySelector('.toast-close').onclick = () => {
        toast.classList.replace('show', 'hide');
        setTimeout(() => toast.remove(), 450);
    };

    container.appendChild(toast);

    // Trigger reflow to start transition
    toast.offsetHeight;
    toast.classList.add('show');

    // Auto-dismiss after 4.5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.replace('show', 'hide');
            setTimeout(() => toast.remove(), 450);
        }
    }, 4500);
};

// Check for session flash messages and display them
document.addEventListener("DOMContentLoaded", function() {
    @if(session('success'))
        window.showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        window.showToast(@json(session('error')), 'error');
    @endif
    @if(session('alerta'))
        window.showToast(@json(session('alerta')), 'alerta');
    @endif
    @if(session('status'))
        window.showToast(@json(session('status')), 'info');
    @endif
});
</script>

@include('legal.partials.modal')

@stack('scripts')

</body>
</html>
