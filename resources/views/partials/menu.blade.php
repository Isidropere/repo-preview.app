<div id="navbar-secondary-content" class="hs-overlay hs-overlay-open:translate-x-0 hidden -translate-x-full fixed top-0 start-0 transition-all duration-300 transform h-full w-full max-w-xs z-[80] bg-white border-e shadow-2xl" tabindex="-1">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#ea580c 0%,#f58634 60%,#fb923c 100%);padding:1rem 1.25rem;">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-white font-bold text-base">📂 Categorías</h3>
            <button type="button" class="flex items-center justify-center w-7 h-7 rounded-full bg-white/20 text-white hover:bg-white/30 transition-colors"
                data-hs-overlay="#navbar-secondary-content">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <input type="text" id="buscarCategoria" placeholder="🔍 Buscar categoría..."
               class="w-full px-3 py-2 rounded-lg text-sm border-0 focus:ring-2 focus:ring-white/50 focus:outline-none"
               style="background:rgba(255,255,255,0.9);color:#374151;">
    </div>

    {{-- Lista de categorías (alfabético) --}}
    <div class="p-3 overflow-y-auto" style="max-height:calc(100vh - 120px);">

        @auth
        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary link-adultos"
           href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(11)) }}"
           onclick="return confirmarAdultos(event, this)">
            <img src="/imgs/icons/side-bar-icons/age-limit.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Adultos</span>
        </a>
        @endauth

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(21)) }}">
            <img src="/imgs/icons/side-bar-icons/Antiguedades.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Antigüedades</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(27)) }}">
            <img src="/imgs/icons/side-bar-icons/caballeros.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Caballeros</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(9)) }}">
            <img src="/imgs/icons/side-bar-icons/lecciones.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Clases / Lecciones</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(13)) }}">
            <img src="/imgs/icons/side-bar-icons/cuidadoPersonal.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Cuidado personal</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(26)) }}">
            <img src="/imgs/icons/side-bar-icons/damas.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Damas</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(14)) }}">
            <img src="/imgs/icons/side-bar-icons/decoraciones.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Decoraciones</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(15)) }}">
            <img src="/imgs/icons/side-bar-icons/deportes.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Deportes</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(2)) }}">
            <img src="/imgs/icons/side-bar-icons/electrodomestico.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Electrodomésticos</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(3)) }}">
            <img src="/imgs/icons/side-bar-icons/electronico.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Electrónicos</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(7)) }}">
            <img src="/imgs/icons/side-bar-icons/herramientas.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Herramientas</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(16)) }}">
            <img src="/imgs/icons/side-bar-icons/hogar.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Hogar</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(1)) }}">
            <img src="/imgs/icons/side-bar-icons/instrumentos.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Instrumentos musicales</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(17)) }}">
            <img src="/imgs/icons/side-bar-icons/jardin.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Jardín</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(8)) }}">
            <img src="/imgs/icons/side-bar-icons/joya.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Joyas</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(4)) }}">
            <img src="/imgs/icons/side-bar-icons/juegos.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Juegos</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(25)) }}">
            <img src="/imgs/icons/side-bar-icons/librería.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Librería y Papelería</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(23)) }}">
            <img src="/imgs/icons/side-bar-icons/mascotas.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Mascotas</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(5)) }}">
            <img src="/imgs/icons/side-bar-icons/muebles.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Muebles</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(22)) }}">
            <img src="/imgs/icons/side-bar-icons/niños.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Niñas</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(20)) }}">
            <img src="/imgs/icons/side-bar-icons/niños.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Niños</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(28)) }}">
            <img src="/imgs/icons/side-bar-icons/oficina.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Oficina</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(29)) }}">
            <img src="/imgs/icons/side-bar-icons/talentos.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Talentos-Servicios</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(24)) }}">
            <img src="/imgs/icons/side-bar-icons/tecnología.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Tecnología</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(19)) }}">
            <img src="/imgs/icons/side-bar-icons/telefono.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Teléfonos</span>
        </a>

        <a class="cat-item group flex items-center gap-3 text-gray-700 rounded-xl p-3 mb-1 transition-all hover:bg-orange-50 hover:text-primary" href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(6)) }}">
            <img src="/imgs/icons/side-bar-icons/vehiculos.svg" alt="" class="w-6 h-6 flex-shrink-0 opacity-70 group-hover:opacity-100" loading="lazy" width="24" height="24">
            <span class="text-sm font-medium group-hover:font-bold">Vehículos</span>
        </a>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var input = document.getElementById("buscarCategoria");
    var items = document.querySelectorAll("#navbar-secondary-content .cat-item");
    function normalizar(texto) {
        return (texto || '').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }
    input.addEventListener("keyup", function () {
        var filtro = normalizar(input.value);
        items.forEach(function(el) {
            var content = normalizar(el.textContent);
            el.style.display = content.includes(filtro) ? "flex" : "none";
        });
    });
});
</script>
