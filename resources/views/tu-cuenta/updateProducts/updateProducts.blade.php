<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Cambialord - Tienda Online">
    <meta name="viewport" content="width=device-width">
    <link rel="icon" type="image/svg+xml" href="/logoTypes/logoFooter.png">
    <meta name="generator" content="Astro v4.11.3">
    <title>Editar productos - Cambialord</title>
    <link rel="stylesheet" href="/_astro/index.D-AOIgCY.css">
    <link rel="stylesheet" href="/_astro/index.BneVErea.css">
    <style>
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
    </style>
    <script type="module" src="/_astro/hoisted.YWnzczOx.js"></script>
</head>

<body>
    <section class="flex justify-center items-center w-full bg-primary font-medium text-center py-1"
        data-astro-cid-pwmmw5ba>
        Encuentra lo que deseas cambiar
    </section>
    <header class="w-full bg-[#FAFAFA] text-xl py-3 md:py-4 shadow-lg" data-astro-cid-pwmmw5ba>
        <nav class="max-w-[1450px] h-full mx-auto px-4 md:px-6 lg:px-8 lg:py-2" aria-label="Global"
            data-astro-cid-pwmmw5ba>
            <div class="relative md:flex md:items-center md:justify-between" data-astro-cid-pwmmw5ba>
                <div class="flex items-center justify-between w-full lg:w-auto" data-astro-cid-pwmmw5ba> <a
                        class="h-auto" href="/" aria-label="Brand" data-astro-cid-pwmmw5ba> <img
                            src="/logoTypes/header-logo.png" id="logoHeader"
                            class="object-cover w-[160px] md:w-[210px] h-auto " alt="Brand Logo"
                            data-astro-cid-pwmmw5ba> </a> <!-- Seach moviles -->
                    <div class="block lg:hidden w-full" data-astro-cid-pwmmw5ba>
                        <form class="max-w-[750px] w-full px-4"> <label for="default-search"
                                class="mb-2 text-sm font-medium text-white sr-only ">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"> <svg
                                        class="w-6 h-6  text-secondary" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path>
                                    </svg> </div> <input type="search" id="default-search"
                                    class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-secondary focus:border-secondary truncate"
                                    placeholder="Buscar Productos, Marcas y más..." required> <button type="submit"
                                    class="text-white absolute end-2.5 bottom-2.5 bg-primary hover:bg-hoverPrimary focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 hidden md:flex">Buscar</button>
                            </div>
                        </form>
                    </div>
                    <div class="lg:hidden" data-astro-cid-pwmmw5ba> <button type="button"
                            class="hs-collapse-toggle flex justify-center items-center p-1.5 text-sm font-semibold rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
                            data-hs-collapse="#navbar-collapse-with-animation"
                            aria-controls="navbar-collapse-with-animation" aria-label="Toggle navigation"
                            data-astro-cid-pwmmw5ba> <svg class="hs-collapse-open:hidden flex-shrink-0 w-6 h-6"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" data-astro-cid-pwmmw5ba>
                                <line x1="3" x2="21" y1="6" y2="6" data-astro-cid-pwmmw5ba></line>
                                <line x1="3" x2="21" y1="12" y2="12" data-astro-cid-pwmmw5ba></line>
                                <line x1="3" x2="21" y1="18" y2="18" data-astro-cid-pwmmw5ba></line>
                            </svg> <svg class="hs-collapse-open:block hidden flex-shrink-0 w-6 h-6"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" data-astro-cid-pwmmw5ba>
                                <path d="M18 6 6 18" data-astro-cid-pwmmw5ba></path>
                                <path d="m6 6 12 12" data-astro-cid-pwmmw5ba></path>
                            </svg> </button> </div>
                </div> <!-- Search PC -->
                <div id="navbar-collapse-with-animation"
                    class="hs-collapse hidden overflow-hidden lg:overflow-visible transition-all duration-300 basis-full lg:block"
                    data-astro-cid-pwmmw5ba>
                    <div class="overflow-hidden lg:overflow-visible overflow-y-hidden max-h-[75vh] z-30 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300"
                        data-astro-cid-pwmmw5ba>
                        <div class="flex flex-col divide-y divide-dashed divide-gray-200 lg:flex-row lg:items-center lg:justify-end py-2 lg:py-0 lg:ps-7 lg:divide-y-0 lg:divide-solid"
                            data-astro-cid-pwmmw5ba>
                            <div class="relative py-3 md:px-8 lg:hidden flex items-center"
                                data-hs-overlay="#navbar-secondary-content" aria-controls="navbar-secondary-content"
                                data-astro-cid-pwmmw5ba> <span
                                    class="underline-animation font-medium cursor-pointer group"
                                    aria-label="Toggle navigation" data-astro-cid-pwmmw5ba>Categorías</span> <svg
                                    class="h-12 w-12 fill-primary group-hover:fill-secondary" data-astro-cid-pwmmw5ba
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M4 11h12v2H4zm0-5h16v2H4zm0 12h7.235v-2H4z"></path>
                                </svg> </div>
                            <div class="hidden lg:block w-full" data-astro-cid-pwmmw5ba>
                                <form class="max-w-[750px] w-full px-4"> <label for="default-search"
                                        class="mb-2 text-sm font-medium text-white sr-only ">Search</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                            <svg class="w-6 h-6  text-secondary" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path>
                                            </svg> </div> <input type="search" id="default-search"
                                            class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-secondary focus:border-secondary truncate"
                                            placeholder="Buscar Productos, Marcas y más..." required> <button
                                            type="submit"
                                            class="text-white absolute end-2.5 bottom-2.5 bg-primary hover:bg-hoverPrimary focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 hidden md:flex">Buscar</button>
                                    </div>
                                </form>
                            </div> <a class="relative py-3 md:px-8" href="{{ route('intercambio') }}" data-astro-cid-pwmmw5ba>
                                <span class="underline-animation font-medium"
                                    data-astro-cid-pwmmw5ba>Intercambiar</span> </a> <a class="relative py-3 md:px-5"
                                href="{{ route('compra') }}" data-astro-cid-pwmmw5ba> <span class="underline-animation font-medium"
                                    data-astro-cid-pwmmw5ba>Comprar</span> </a>
                            <div class="flex gap-x-4 h-full z-auto" data-astro-cid-pwmmw5ba> <a
                                    class="relative text-gray-500 hover:text-gray-400 py-3 md:px-3 no-tooltip"
                                    href="{{ route('carrito.show') }}" data-tooltip="Carrito" data-astro-cid-pwmmw5ba> <span
                                        class="absolute top-4 right-0 md:right-2 bg-secondary text-white rounded-full text-xs font-bold px-1.5 transform translate-x-1/2 -translate-y-1/2"
                                        data-astro-cid-pwmmw5ba>5</span> <svg
                                        class="h-8 w-8 fill-primary hover:fill-hoverPrimary" data-astro-cid-pwmmw5ba
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path
                                            d="M21.822 7.431A1 1 0 0 0 21 7H7.333L6.179 4.23A1.994 1.994 0 0 0 4.333 3H2v2h2.333l4.744 11.385A1 1 0 0 0 10 17h8c.417 0 .79-.259.937-.648l3-8a1 1 0 0 0-.115-.921zM17.307 15h-6.64l-2.5-6h11.39l-2.25 6z">
                                        </path>
                                        <circle cx="10.5" cy="19.5" r="1.5"></circle>
                                        <circle cx="17.5" cy="19.5" r="1.5"></circle>
                                    </svg> </a>
                                <div class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false] p-3 ps-px sm:px-3"
                                    data-astro-cid-pwmmw5ba> <button id="hs-dropdown-floating-dark" type="button"
                                        class="hs-dropdown-toggle flex items-center w-full text-sm text-primary hover:text-hoverPrimary relative"
                                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"
                                        data-astro-cid-pwmmw5ba> <span class="absolute bottom-5 left-5 z-auto flex"
                                            data-astro-cid-pwmmw5ba> <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"
                                                data-astro-cid-pwmmw5ba></span> <span
                                                class="relative inline-flex rounded-full px-1.5 text-white text-xs bg-secondary"
                                                data-astro-cid-pwmmw5ba>2</span> </span> <svg
                                            class="h-8 w-8 fill-primary hover:fill-hoverPrimary z-auto"
                                            data-astro-cid-pwmmw5ba xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M19 13.586V10c0-3.217-2.185-5.927-5.145-6.742C13.562 2.52 12.846 2 12 2s-1.562.52-1.855 1.258C7.185 4.074 5 6.783 5 10v3.586l-1.707 1.707A.996.996 0 0 0 3 16v2a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-2a.996.996 0 0 0-.293-.707L19 13.586zM19 17H5v-.586l1.707-1.707A.996.996 0 0 0 7 14v-4c0-2.757 2.243-5 5-5s5 2.243 5 5v4c0 .266.105.52.293.707L19 16.414V17zm-7 5a2.98 2.98 0 0 0 2.818-2H9.182A2.98 2.98 0 0 0 12 22z">
                                            </path>
                                        </svg> </button>
                                    <div class="hs-dropdown-menu transition-opacity duration-150 hs-dropdown-open:opacity-100 opacity-0 hidden z-50 bg-white border border-gray-200 md:shadow-2xl rounded-xl w-72 max-w-full"
                                        role="menu" aria-orientation="vertical"
                                        aria-labelledby="hs-dropdown-floating-dark" data-astro-cid-pwmmw5ba>
                                        <div class="rounded-xl space-y-1 text-sm text-primary" data-astro-cid-pwmmw5ba>
                                            <!-- Notificación 1 -->
                                            <div class="flex rounded-xl items-start p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer"
                                                data-astro-cid-pwmmw5ba> <img class="w-12 h-12 rounded-full mr-4"
                                                    src="/articulos/Perfume.jfif" alt="Imagen de Notificación 1"
                                                    data-astro-cid-pwmmw5ba>
                                                <div class="flex-1" data-astro-cid-pwmmw5ba>
                                                    <h3 class="text-sm font-semibold text-gray-700"
                                                        data-astro-cid-pwmmw5ba>
                                                        Perfume
                                                    </h3>
                                                    <p class="text-xs text-gray-500" data-astro-cid-pwmmw5ba>Tu artículo
                                                        fue comprado el 12 de Agosto de 2024</p>
                                                    <p class="text-xs text-secondary font-medium mt-1"
                                                        data-astro-cid-pwmmw5ba>Hace 2 horas</p>
                                                </div>
                                            </div> <!-- Notificación 2 -->
                                            <div class="flex rounded-xl items-start p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer"
                                                data-astro-cid-pwmmw5ba> <img class="w-12 h-12 rounded-full mr-4"
                                                    src="/articulos/Mochila.jfif" alt="Imagen de Notificación 2"
                                                    data-astro-cid-pwmmw5ba>
                                                <div class="flex-1" data-astro-cid-pwmmw5ba>
                                                    <h3 class="text-sm font-semibold text-gray-700"
                                                        data-astro-cid-pwmmw5ba>
                                                        Mochila
                                                    </h3>
                                                    <p class="text-xs text-gray-500" data-astro-cid-pwmmw5ba>Un usuario
                                                        est? interesado en intercambiar tu artículo el 10 de Agosto de
                                                        2024</p>
                                                    <p class="text-xs text-secondary font-medium mt-1"
                                                        data-astro-cid-pwmmw5ba>Hace 1 día</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- La configuración de cuenta no debe aparecer hasta que el usuario inicie o se registre en la p?gina -->
                                <div class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false] p-3 ps-px sm:px-3"
                                    data-astro-cid-pwmmw5ba> <button id="hs-dropdown-floating-dark" type="button"
                                        class="hs-dropdown-toggle flex items-center w-full text-sm text-primary hover:text-hoverPrimary"
                                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"
                                        data-astro-cid-pwmmw5ba> <svg class="h-8 w-8" data-astro-cid-pwmmw5ba
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg> </button>
                                    <div class="hs-dropdown-menu transition-opacity duration-150 hs-dropdown-open:opacity-100 opacity-0 md:w-auto px-4 hidden z-50 bg-white border border-gray-200 md:shadow-2xl rounded-xl before:absolute top-full before:-top-5 before:start-0 before:w-full before:h-5"
                                        role="menu" aria-orientation="vertical"
                                        aria-labelledby="hs-dropdown-floating-dark" data-astro-cid-pwmmw5ba>
                                        <div class="my-4 md:px-1 space-y-1 text-sm text-center text-primary flex flex-col gap-y-2 items-center"
                                            data-astro-cid-pwmmw5ba> <!-- --- Aqu? debe cargarse su foto de perfil
                                        y su nombre -- -->
                                            <div class="flex gap-4 items-center" data-astro-cid-pwmmw5ba> <img
                                                    src="/profilePlaceholder.jpg" alt="" class="h-12 w-12 rounded-full"
                                                    data-astro-cid-pwmmw5ba> <a href="/perfil"
                                                    class="py-2 md:px-3 rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all"
                                                    data-astro-cid-pwmmw5ba>Nombre de Usuario</a> </div>
                                            <hr class="border border-gray-200 w-full" data-astro-cid-pwmmw5ba>
                                            <!-- -------------------------------------------------------------------- -->
                                            <a class="gap-x-3.5 py-2 md:px-3 rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all"
                                                href="/tu-cuenta" data-astro-cid-pwmmw5ba>
                                                Tu cuenta
                                            </a> <a
                                                class="flex gap-x-3.5 py-2 md:px-3 w-full items-center justify-center rounded-lg text-red-600 hover:text-red-700 focus:outline-none hover:bg-gray-100 hover:underline transition-all"
                                                href="#" data-astro-cid-pwmmw5ba>
                                                Cerrar Sesión
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- ---------------------------------------------------------------------------------------------------------------------------------------------------- --> <!-- <div
                                class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false] p-3 ps-px sm:px-3"
                            >
                                <button
                                    id="hs-dropdown-floating-dark"
                                    type="button"
                                    class="hs-dropdown-toggle flex items-center w-full text-sm text-primary hover:text-hoverPrimary"
                                    aria-haspopup="menu"
                                    aria-expanded="false"
                                    aria-label="Dropdown"
                                >
                                    <Login class="h-8 w-8" />
                                </button>

                                <div
                                    class="hs-dropdown-menu transition-opacity duration-150 hs-dropdown-open:opacity-100 opacity-0 md:w-auto px-4 hidden z-50 bg-white border border-gray-200 md:shadow-2xl rounded-xl before:absolute top-full before:-top-5 before:start-0 before:w-full before:h-5"
                                    role="menu"
                                    aria-orientation="vertical"
                                    aria-labelledby="hs-dropdown-floating-dark"
                                >
                                    <div
                                        class="my-4 md:px-1 space-y-1 text-sm text-center text-primary flex flex-col gap-y-2 items-center"
                                    >
                                        <a
                                            class="gap-x-3.5 py-2 md:px-3 rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all"
                                            href="/iniciar-sesion"
                                        >
                                            Iniciar Sesión
                                        </a>
                                     
                                        <div
                                            class="border border-gray-200 w-full"
                                        >
                                        </div>
                                        <a
                                            class="gap-x-3.5 py-2 md:px-3 rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all"
                                            href="/registrarse"
                                        >
                                            Registrarse
                                        </a>
                                    </div>
                                </div>
                            </div> -->
                            </div>
                            <div class="hidden lg:block ps-3 sm:ps-6 sm:my-3 sm:ms-6 sm:border-s sm:border-gray-300"
                                data-astro-cid-pwmmw5ba> <button type="button"
                                    class="size-10 flex justify-center items-center text-sm font-semibold rounded-lg disabled:opacity-50 disabled:pointer-events-none"
                                    data-hs-overlay="#navbar-secondary-content" aria-controls="navbar-secondary-content"
                                    aria-label="Toggle navigation" title="Categorías" data-astro-cid-pwmmw5ba> <svg
                                        class="h-12 w-12 fill-primary hover:fill-secondary" data-astro-cid-pwmmw5ba
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M4 11h12v2H4zm0-5h16v2H4zm0 12h7.235v-2H4z"></path>
                                    </svg> </button> </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main id="content" data-astro-cid-pwmmw5ba>
        <div class="max-w-[85rem] mx-auto py-10 px-4 sm:px-6 lg:px-8" data-astro-cid-pwmmw5ba></div>
    </main> <!-- ========== END MAIN CONTENT ========== --> <!-- ========== SECONDARY CONTENT ========== -->
    <!-- Offcanvas -->
    <div id="navbar-secondary-content"
        class="hs-overlay hs-overlay-open:translate-x-0 hidden -translate-x-full fixed top-0 start-0 transition-all duration-300 transform h-full max-w-xs w-full z-[80] bg-white border-e overflow-y-auto"
        tabindex="-1" data-astro-cid-pwmmw5ba>
        <div class="flex justify-between items-center py-3 px-4 border-b" data-astro-cid-pwmmw5ba>
            <h3 class="font-bold text-primary" data-astro-cid-pwmmw5ba>Categorías</h3> <button type="button"
                class="inline-flex flex-shrink-0 justify-center items-center size-8 rounded-lg text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 focus:ring-offset-white text-sm"
                data-hs-overlay="#navbar-secondary-content" data-astro-cid-pwmmw5ba> <span class="sr-only"
                    data-astro-cid-pwmmw5ba>Close offcanvas</span> <svg class="flex-shrink-0 size-4"
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    data-astro-cid-pwmmw5ba>
                    <path d="M18 6 6 18" data-astro-cid-pwmmw5ba></path>
                    <path d="m6 6 12 12" data-astro-cid-pwmmw5ba></path>
                </svg> </button>
        </div>
        <div class="p-4" data-astro-cid-pwmmw5ba> <a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(13)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/cuidadoPersonal.svg" alt="Cuidado personal icon"
                        data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Cuidado personal </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(14)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/decoraciones.svg" alt="Decoraciones icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Decoraciones </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(15)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/deportes.svg" alt="Deportes icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Deportes </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(7)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/herramientas.svg" alt="Herramientas icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Herramientas </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(16)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/hogar.svg" alt="Hogar icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Hogar </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(1)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/instrumentos.svg" alt="Instrumentos musicales icon"
                        data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Instrumentos musicales </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(17)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/jardin.svg" alt="Jardín icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Jardín </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(4)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/juegos.svg" alt="Juegos icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Juegos </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(19)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/telefono.svg" alt="Teléfonos icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Teléfonos </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(20)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/Niños.svg" alt="Niños icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Niños </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(21)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/Antiguedades.svg" alt="Antigüedades icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Antigüedades </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(22)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/niños.svg" alt="Niñas icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Niñas </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(23)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/mascotas.svg" alt="Mascotas icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Mascotas </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(24)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/Tecnología.svg" alt="Tecnología icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Tecnología </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(25)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/Librería.svg" alt="Librería y Papelería icon"
                        data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Librería y Papelería </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(26)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/damas.svg" alt="Damas icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Damas </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(27)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/caballeros.svg" alt="Caballeros icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Caballeros </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(28)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/oficina.svg" alt="Oficina icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Oficina </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="/talentos" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/talentos.svg" alt="Talentos icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Talentos </p>
                </div>
            </a><a
                class="group flex gap-x-5 text-gray-800 transition-all duration-300  hover:bg-gray-200 rounded-lg p-4"
                href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(11)) }}" data-astro-cid-pwmmw5ba>
                <div class="grow flex gap-x-2 fill-secondary" data-astro-cid-pwmmw5ba> <img
                        src="/icons/side-bar-icons/age-limit.svg" alt="Adultos icon" data-astro-cid-pwmmw5ba>
                    <p class="font-normal group-hover:font-bold group-hover:underline-animation"
                        data-astro-cid-pwmmw5ba> Adultos </p>
                </div>
            </a> </div>
    </div>
    <script>
        function toggleDropdown(event) {
            const dropdownMenu = document.getElementById("dropdown-menu");
            const iconMenu = document.querySelector(".icon-menu");
            const iconClose = document.querySelector(".icon-close");

            if (dropdownMenu.classList.contains("hidden")) {
                dropdownMenu.classList.remove("hidden");
                dropdownMenu.classList.remove("opacity-0");
                dropdownMenu.classList.remove("scale-95");
                dropdownMenu.classList.add("opacity-100");
                dropdownMenu.classList.add("scale-100");
                iconMenu.classList.add("hidden");
                iconClose.classList.remove("hidden");
            } else {
                dropdownMenu.classList.add("opacity-0");
                dropdownMenu.classList.add("scale-95");
                dropdownMenu.classList.remove("opacity-100");
                dropdownMenu.classList.remove("scale-100");
                setTimeout(() => {
                    dropdownMenu.classList.add("hidden");
                }, 300); // La duración debe coincidir con la duración de la transici?n
                iconMenu.classList.remove("hidden");
                iconClose.classList.add("hidden");
            }
        }

        // Optional: Close dropdown when clicking outside
        document.addEventListener("click", function (event) {
            const dropdownMenu = document.getElementById("dropdown-menu");
            const iconMenu = document.querySelector(".icon-menu");
            const iconClose = document.querySelector(".icon-close");
            const isClickInside =
                dropdownMenu.contains(event.target) ||
                event.target.closest(".hs-dropdown button");

            if (!isClickInside) {
                dropdownMenu.classList.add("opacity-0");
                dropdownMenu.classList.add("scale-95");
                dropdownMenu.classList.remove("opacity-100");
                dropdownMenu.classList.remove("scale-100");
                setTimeout(() => {
                    dropdownMenu.classList.add("hidden");
                }, 300); // La duración debe coincidir con la duración de la transici?n
                iconMenu.classList.remove("hidden");
                iconClose.classList.add("hidden");
            }
        });
    </script>
    <main class="min-h-screen">
        <div class="max-w-[950px] mx-auto px-4 lg:px-0">
            <h1 class="text-4xl text-primary font-bold my-4">Gestionar productos</h1>
            <div id="itemsContainer" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mx-2 mb-12">
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/aretes-negros.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Aretes negros</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Nuevo</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/billetera-en-cuero.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Billetera en cuero</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Usado - Como nuevo</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/blusa-larga.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Blusa mangas larga</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Usado - Buen estado</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/aretes-negros.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Aretes negros</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Nuevo</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/billetera-en-cuero.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Billetera en cuero</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Usado - Como nuevo</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/blusa-larga.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Blusa mangas larga</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Usado - Buen estado</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/aretes-negros.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Aretes negros</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Nuevo</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/billetera-en-cuero.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Billetera en cuero</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Usado - Como nuevo</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4"> <img
                        src="/articulos/blusa-larga.jfif" alt="Product Image"
                        class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                    <div class="mt-2 mx-4">
                        <p class="text-secondary font-bold mt-1">RD $1000.00</p>
                        <h2 class="text-lg font-semibold text-gray-800">Blusa mangas larga</h2>
                        <div class="flex justify-between">
                            <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                            <p class="text-gray-500">Usado - Buen estado</p>
                        </div> <button class="mt-4 text-red-600 hover:text-red-800 focus:outline-none"
                            onclick="openModal('{product.id}')">
                            Borrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="deleteModal"
            class="fixed inset-0 flex items-center justify-center backdrop-blur-sm bg-black bg-opacity-50 transition-all z-50 hidden">
            <div class="bg-white rounded-lg w-[400px] p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">¿Seguro que quieres borrarlo?</h2>
                <div class="flex justify-end"> <button
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded mr-2 hover:bg-gray-400"
                        onclick="closeModal()">
                        Cancelar
                    </button> <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Borrar
                    </button> </div>
            </div>
        </div>
    </main> <!-- ========== FOOTER ========== -->
    <footer class="mt-auto  w-full shadow-2xl">
        <div class="mt-auto w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 lg:pt-20 mx-auto"> <!-- Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <div class="col-span-full lg:col-span-1"> <a class="flex-none text-xl font-semibold text-white" href="/"
                        aria-label="Brand"> <img src="/logoTypes/logoFooter.png" class="object-cover h-20" alt=""> </a>
                </div> <!-- End Col -->
                <div class="col-span-1">
                    <h4 class="font-semibold text-secondary">Secciones</h4>
                    <div class="mt-3 grid space-y-3">
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary" href="/">Inicio</a></p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary" href="/sobre-nosotros">Sobre
                                Nosotros</a></p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary"
                                href="/contactanos">Contáctanos</a></p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary" href="/envios">Información de
                                envíos</a></p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary" href="/empleos">Empleos</a>
                        </p>
                    </div>
                </div> <!-- End Col -->
                <div class="col-span-1"> <!-- <h4 class="font-semibold text-secondary">Categorías</h4> -->
                    <div class="mt-3 grid space-y-3">
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary"
                                href="/responsabilidad">Responsabilidad social</a></p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary"
                                href="/realizar-intercambio">¿Cómo realizar un intercambio?</a></p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary" href="/como-vender">¿Cómo
                                vender?</a> </p>
                        <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-primary" href="/realizar-compra">¿Cómo
                                realizar una compra?</a></p>
                    </div>
                </div> <!-- End Col -->
                <div class="col-span-2">
                    <form>
                        <div class="mt-4 flex flex-col items-center gap-2 sm:flex-row sm:gap-3 bg-white rounded-lg p-2">
                            <div class="w-full"> <input type="text" id="hero-input" name="hero-input"
                                    class="py-3 px-4 block w-full border-primary border-2 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                                    placeholder="Buscar productos, Marcas y más"> </div> <a
                                class="w-full sm:w-auto whitespace-nowrap p-3 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary disabled:opacity-50 disabled:pointer-events-none"
                                href="#">
                                Buscar
                            </a>
                        </div>
                    </form>
                </div> <!-- End Col -->
            </div> <!-- End Grid -->
            <div class="mt-5 sm:mt-12 grid gap-y-2 sm:gap-y-0 sm:flex sm:justify-between sm:items-center">
                <div class="flex justify-between items-center">
                    <p class="text-sm text-gray-400">© 2024 Cambialord. Todos los derechos reservados</p>
                </div> <!-- End Col --> <!-- Social Brands -->
                <div class="flex items-center"> <a
                        class="size-10 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-primary hover:bg-white/10 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:ring-1 focus:ring-gray-600"
                        href="#"> <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 fill-primary hover:fill-hoverPrimary" viewBox="0 0 24 24">
                            <path
                                d="M11.999 7.377a4.623 4.623 0 1 0 0 9.248 4.623 4.623 0 0 0 0-9.248zm0 7.627a3.004 3.004 0 1 1 0-6.008 3.004 3.004 0 0 1 0 6.008z">
                            </path>
                            <circle cx="16.806" cy="7.207" r="1.078"></circle>
                            <path
                                d="M20.533 6.111A4.605 4.605 0 0 0 17.9 3.479a6.606 6.606 0 0 0-2.186-.42c-.963-.042-1.268-.054-3.71-.054s-2.755 0-3.71.054a6.554 6.554 0 0 0-2.184.42 4.6 4.6 0 0 0-2.633 2.632 6.585 6.585 0 0 0-.419 2.186c-.043.962-.056 1.267-.056 3.71 0 2.442 0 2.753.056 3.71.015.748.156 1.486.419 2.187a4.61 4.61 0 0 0 2.634 2.632 6.584 6.584 0 0 0 2.185.45c.963.042 1.268.055 3.71.055s2.755 0 3.71-.055a6.615 6.615 0 0 0 2.186-.419 4.613 4.613 0 0 0 2.633-2.633c.263-.7.404-1.438.419-2.186.043-.962.056-1.267.056-3.71s0-2.753-.056-3.71a6.581 6.581 0 0 0-.421-2.217zm-1.218 9.532a5.043 5.043 0 0 1-.311 1.688 2.987 2.987 0 0 1-1.712 1.711 4.985 4.985 0 0 1-1.67.311c-.95.044-1.218.055-3.654.055-2.438 0-2.687 0-3.655-.055a4.96 4.96 0 0 1-1.669-.311 2.985 2.985 0 0 1-1.719-1.711 5.08 5.08 0 0 1-.311-1.669c-.043-.95-.053-1.218-.053-3.654 0-2.437 0-2.686.053-3.655a5.038 5.038 0 0 1 .311-1.687c.305-.789.93-1.41 1.719-1.712a5.01 5.01 0 0 1 1.669-.311c.951-.043 1.218-.055 3.655-.055s2.687 0 3.654.055a4.96 4.96 0 0 1 1.67.311 2.991 2.991 0 0 1 1.712 1.712 5.08 5.08 0 0 1 .311 1.669c.043.951.054 1.218.054 3.655 0 2.436 0 2.698-.043 3.654h-.011z">
                            </path>
                        </svg> </a> <a
                        class="size-10 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-primary hover:bg-white/10 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:ring-1 focus:ring-gray-600"
                        href="#"> <svg class="h-6 w-6 fill-primary hover:fill-hoverPrimary"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M12.001 2.002c-5.522 0-9.999 4.477-9.999 9.999 0 4.99 3.656 9.126 8.437 9.879v-6.988h-2.54v-2.891h2.54V9.798c0-2.508 1.493-3.891 3.776-3.891 1.094 0 2.24.195 2.24.195v2.459h-1.264c-1.24 0-1.628.772-1.628 1.563v1.875h2.771l-.443 2.891h-2.328v6.988C18.344 21.129 22 16.992 22 12.001c0-5.522-4.477-9.999-9.999-9.999z">
                            </path>
                        </svg> </a> <a
                        class="size-10 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-primary hover:bg-white/10 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:ring-1 focus:ring-gray-600"
                        href="#"> <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 fill-primary hover:fill-hoverPrimary" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M18.403 5.633A8.919 8.919 0 0 0 12.053 3c-4.948 0-8.976 4.027-8.978 8.977 0 1.582.413 3.126 1.198 4.488L3 21.116l4.759-1.249a8.981 8.981 0 0 0 4.29 1.093h.004c4.947 0 8.975-4.027 8.977-8.977a8.926 8.926 0 0 0-2.627-6.35m-6.35 13.812h-.003a7.446 7.446 0 0 1-3.798-1.041l-.272-.162-2.824.741.753-2.753-.177-.282a7.448 7.448 0 0 1-1.141-3.971c.002-4.114 3.349-7.461 7.465-7.461a7.413 7.413 0 0 1 5.275 2.188 7.42 7.42 0 0 1 2.183 5.279c-.002 4.114-3.349 7.462-7.461 7.462m4.093-5.589c-.225-.113-1.327-.655-1.533-.73-.205-.075-.354-.112-.504.112s-.58.729-.711.879-.262.168-.486.056-.947-.349-1.804-1.113c-.667-.595-1.117-1.329-1.248-1.554s-.014-.346.099-.458c.101-.1.224-.262.336-.393.112-.131.149-.224.224-.374s.038-.281-.019-.393c-.056-.113-.505-1.217-.692-1.666-.181-.435-.366-.377-.504-.383a9.65 9.65 0 0 0-.429-.008.826.826 0 0 0-.599.28c-.206.225-.785.767-.785 1.871s.804 2.171.916 2.321c.112.15 1.582 2.415 3.832 3.387.536.231.954.369 1.279.473.537.171 1.026.146 1.413.089.431-.064 1.327-.542 1.514-1.066.187-.524.187-.973.131-1.067-.056-.094-.207-.151-.43-.263">
                            </path>
                        </svg> </a> </div> <!-- End Social Brands -->
            </div>
        </div>
    </footer> <!-- ========== END FOOTER ========== -->
</body>

</html>
<script>
    function openModal(productId) {
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>