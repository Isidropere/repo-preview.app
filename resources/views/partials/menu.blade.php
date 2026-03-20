<div id="navbar-secondary-content" class="hs-overlay hs-overlay-open:translate-x-0 hidden -translate-x-full fixed top-0 start-0 transition-all duration-300 transform h-full max-w-xs w-full z-[80] bg-white border-e overflow-y-auto" tabindex="-1">
    <div class="flex justify-between items-center py-3 px-4 border-b">
        <div class="flex flex-col gap-2 w-full">
            <!--<h3 class="font-bold text-primary">Categorías</h3>-->
            <input 
                type="text" 
                id="buscarCategoria" 
                placeholder="Buscar categoría..." 
                class="border rounded-lg px-3 py-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
        </div>
        <button type="button" class="inline-flex flex-shrink-0 justify-center items-center size-8 rounded-lg text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 focus:ring-offset-white text-sm"
            data-hs-overlay="#navbar-secondary-content"> 
            <span class="sr-only">Cerrar menú</span> 
            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> 
                <path d="M18 6 6 18"></path> 
                <path d="m6 6 12 12"></path> 
            </svg> 
        </button>
    </div>

    <div class="p-4">
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 6) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/vehiculos.svg" alt="Vehículos icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Vehículos</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 11) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/age-limit.svg" alt="Adultos icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Adultos</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 21) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/Antiguedades.svg" alt="Antigüedades icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Antigüedades</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 27) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/caballeros.svg" alt="Caballeros icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Caballeros</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 9) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/lecciones.svg" alt="Clases/Lecciones icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Clases/Lecciones</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 13) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/cuidadoPersonal.svg" alt="Cuidado personal icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Cuidado personal</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 26) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/damas.svg" alt="Damas icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Damas</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 14) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/decoraciones.svg" alt="Decoraciones icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Decoraciones</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 15) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/deportes.svg" alt="Deportes icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Deportes</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 2) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/electrodomestico.svg" alt="Electrodoméstico icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Electrodoméstico</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 3) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/electronico.svg" alt="Electrónico icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Electrónico</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 7) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/herramientas.svg" alt="Herramientas icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Herramientas</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 16) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/hogar.svg" alt="Hogar icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Hogar</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 1) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/instrumentos.svg" alt="Instrumentos musicales icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Instrumentos musicales</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 17) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/jardin.svg" alt="Jardín icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Jardín</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 8) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/joya.svg" alt="Joya icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Joya</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 4) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/juegos.svg" alt="Juegos icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Juegos</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 25) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/librería.svg" alt="Librería y Papelería icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Librería y Papelería</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 23) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/mascotas.svg" alt="Mascotas icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Mascotas</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 20) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/niños.svg" alt="Niños icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Niños</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 28) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/oficina.svg" alt="Oficina icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Oficina</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 29) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/talentos.svg" alt="Talentos icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Talentos</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 24) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/tecnología.svg" alt="Tecnología icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Tecnología</p>
            </div>
        </a>
        <a class="group flex gap-x-5 text-gray-800 transition-all duration-300 hover:bg-gray-200 rounded-lg p-4" href="{{ route('categorias.show', 19) }}">
            <div class="grow flex gap-x-2 fill-secondary"> 
                <img src="/imgs/icons/side-bar-icons/telefono.svg" alt="Teléfonos icon">
                <p class="font-normal group-hover:font-bold group-hover:underline-animation">Teléfonos</p>
            </div>
        </a>
    </div>
</div>

<!-- Script de filtro -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.getElementById("buscarCategoria");
        const categorias = document.querySelectorAll("#navbar-secondary-content a");

        input.addEventListener("keyup", function () {
            const filtro = input.value.toLowerCase();

            categorias.forEach(cat => {
                const texto = cat.innerText.toLowerCase();
                cat.style.display = texto.includes(filtro) ? "flex" : "none";
            });
        });
    });
</script>
