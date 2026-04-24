
<section class="w-full bg-primary overflow-hidden py-1">
    <div class="marquee-track whitespace-nowrap text-white text-sm font-medium">
        <span class="mx-8">✨ Encuentra lo que deseas cambiar</span>
        <span class="mx-8">🔄 Intercambia tus productos</span>
        <span class="mx-8">🛒 Compra lo que necesitas</span>
        <span class="mx-8">⭐ Publica tus talentos</span>
        <span class="mx-8">💡 Si no puedes venderlo ¡Cámbialo!</span>
        <span class="mx-8">✨ Encuentra lo que deseas cambiar</span>
        <span class="mx-8">🔄 Intercambia tus productos</span>
        <span class="mx-8">🛒 Compra lo que necesitas</span>
        <span class="mx-8">⭐ Publica tus talentos</span>
        <span class="mx-8">💡 Si no puedes venderlo ¡Cámbialo!</span>
    </div>
    <style>
    .marquee-track { display:inline-block; animation:marquee 20s linear infinite; }
    @keyframes marquee { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }
    </style>
</section>

<!-- Header principal -->
<header class="w-full bg-[#FAFAFA] text-xl py-1.5 md:py-2 shadow-lg sticky top-0 z-50" style="max-width:100vw; box-sizing:border-box;">

    <div class="max-w-[1400px] mx-auto px-4 md:px-4 lg:px-6" style="box-sizing:border-box; max-width:100%;">
        <!-- Contenedor principal del nav -->
        <nav class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4" style="min-width:0;" aria-label="Global">
          
            <!-- Primera fila (logo, buscador móvil y menú hamburguesa) -->
            <div class="flex items-center w-full lg:w-auto" style="gap:8px; min-width:0;">
                <!-- Logo -->
                <a class="flex-shrink-0" href="/" aria-label="Brand">
                    <img src="/imgs/logoTypes/header-logo.png" id="logoHeader" class="object-cover h-auto" style="width:120px;" alt="Brand Logo">
                </a>
                <!-- Buscador móvil con input + botón pegado -->
                <form method="GET" action="{{ route('items.search_header') }}" class="lg:hidden flex min-w-0" style="flex:0 1 140px; min-width:0;">
                    <label for="mobile-search" class="sr-only">Buscar</label>
                    <input type="search" id="mobile-search"
                           name="q"
                           class="border border-gray-300 rounded-l-md bg-gray-50 focus:ring-1 focus:ring-secondary focus:border-secondary"
                           style="flex:1 1 0%; min-width:0; padding:2px 5px; font-size:11px; width:100%;"
                           placeholder="Buscar..."
                           value="{{ request('q') }}"
                           required>
                    <button type="submit"
                            class="flex-shrink-0 bg-primary text-white rounded-r-md hover:bg-hoverPrimary"
                            style="padding:2px 5px;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>

                <!-- Botón menú hamburguesa (solo móvil) -->
                <button type="button"
                        class="lg:hidden flex-shrink-0 flex justify-center items-center rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100"
                        style="padding:6px; min-width:36px; min-height:36px;"
                        data-hs-collapse="#navbar-collapse-with-animation"
                        aria-controls="navbar-collapse-with-animation"
                        aria-label="Toggle navigation">
                    <svg class="hs-collapse-open:hidden flex-shrink-0 w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" x2="21" y1="6" y2="6"></line>
                        <line x1="3" x2="21" y1="12" y2="12"></line>
                        <line x1="3" x2="21" y1="18" y2="18"></line>
                    </svg>
                    <svg class="hs-collapse-open:block hidden flex-shrink-0 w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Contenido colapsable (menú completo) -->
            <div id="navbar-collapse-with-animation" class="hs-collapse hidden overflow-hidden lg:overflow-visible transition-all duration-300 basis-full lg:block">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pt-4 lg:pt-0">

                    <!-- Buscador escritorio (visible solo en desktop) -->
                    <div class="hidden lg:block w-full max-w-[500px]">

                         <form method="GET" action="{{ route('items.search_header') }}"  class="max-w-[700px] w-full px-4">
                            <label for="default-search" class="mb-1 text-sm font-medium text-white sr-only ">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4  text-secondary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path> </svg>
                                </div>

                            <input type="search" 
                                           name="q"
                                           id="default-search" 
                                           class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-secondary focus:border-secondary truncate" 
                                           placeholder="Buscar Productos, Marcas y más..."
                                           value="{{ request('q') }}"
                                           required>
                                    <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-primary hover:bg-hoverPrimary focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 hidden md:flex">
                                        Buscar
                                    </button>
                                 </div>
                        </form>

                    </div>


                    <!-- Menú principal -->
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:gap-5">
                            {{-- Links de navegación con iconos --}}
                            <a class="inline-flex items-center gap-1.5 text-gray-700 hover:text-primary transition-colors text-sm font-medium" href="{{ route('intercambio') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                Intercambiar
                            </a>
                            <a class="inline-flex items-center gap-1.5 text-gray-700 hover:text-primary transition-colors text-sm font-medium" href="{{ route('compra') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Comprar
                            </a>

                            @auth
                            {{-- Botones de creación --}}
                            <a href="{{ route('items.talento_create') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-hoverPrimary text-white text-xs font-bold rounded-lg transition-colors whitespace-nowrap shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                Crear Talento
                            </a>
                            <a href="{{ route('items.create') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-secondary hover:bg-hoverSecondary text-white text-xs font-bold rounded-lg transition-colors whitespace-nowrap shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Crear Producto
                            </a>
                            @endauth


                        <!-- Iconos de acciones -->
                        <div class="flex items-center gap-3 lg:gap-4">

                        <div class="flex items-center gap-x-2 h-full z-auto" data-astro-cid-pwmmw5ba>

                           {{-- Carrito --}}
                           <a class="relative flex items-center justify-center text-primary hover:text-hoverPrimary p-2 no-tooltip" data-astro-cid-pwmmw5ba
                           href="{{ route('carrito.show') }}" 
                           data-tooltip="Carrito" title="Mi carrito">
                          
                               @if($carrito && $carrito->itemsIntencionCompra)
                                <span id="cartBadge" class="absolute top-0 right-0 bg-secondary text-white rounded-full text-xs font-bold px-1.5 leading-none" style="font-size:10px;transition:transform .2s;">
                                    {{ $carrito->itemsIntencionCompra->where('es_seleccionado', 1)->count() }}
                                </span>
                            @else
                                <span id="cartBadge" class="absolute top-0 right-0 bg-secondary text-white rounded-full text-xs font-bold px-1.5 leading-none" style="font-size:10px;transition:transform .2s;">
                                    0
                                </span>
                            @endif

                                <svg class="h-6 w-6 fill-primary hover:fill-hoverPrimary"
                                     xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24"> 
                                    <path d="M21.822 7.431A1 1 0 0 0 21 7H7.333L6.179 4.23A1.994 1.994 0 0 0 4.333 3H2v2h2.333l4.744 11.385A1 1 0 0 0 10 17h8c.417 0 .79-.259.937-.648l3-8a1 1 0 0 0-.115-.921zM17.307 15h-6.64l-2.5-6h11.39l-2.25 6z"></path>
                                    <circle cx="10.5" cy="19.5" r="1.5"></circle>
                                    <circle cx="17.5" cy="19.5" r="1.5"></circle>
                                </svg>
                            </a>
        
                            @auth
                            {{-- Mis intercambios --}}
                            <a href="{{ route('negociaciones.mis') }}"
                               class="relative flex items-center justify-center p-2 text-primary hover:text-hoverPrimary"
                               title="Mis intercambios">
                                <span id="badgeIntercambios" class="absolute top-0 right-0 rounded-full text-xs font-bold px-1.5 leading-none" style="font-size:10px;transition:transform .2s;background:#f58634;color:#fff;">
                                    0
                                </span>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </a>
                            <x-notificaciones />
                            @endauth

                            <div class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false]"
                                data-astro-cid-pwmmw5ba>
                            @auth
        
                                <button id="hs-dropdown-floating-dark" type="button" 
                                        class="hs-dropdown-toggle flex items-center gap-2 text-sm text-primary hover:text-hoverPrimary p-2" 
                                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">

                                    @php
                                        $photoPath = Auth::user()->profile_photo_path;
                                        $defaultAvatar = asset('imgs/defaults/profile_default.svg');
                                        $photoUrl = $photoPath ? asset($photoPath) : $defaultAvatar;
                                    @endphp
                                    <img src="{{ $photoUrl }}"
                                         alt="Foto de perfil"
                                         onerror="this.onerror=null;this.src='{{ $defaultAvatar }}'"
                                         style="width:44px;height:44px;min-width:44px;min-height:44px;border-radius:50%;object-fit:cover;border:2px solid #f58634;flex-shrink:0;display:inline-block;">

                                    <span class="whitespace-nowrap hidden sm:inline">Hola, {{ Auth::user()->nombres }}</span>
                                </button>

                                    <div class="hs-dropdown-menu transition-opacity duration-150 hs-dropdown-open:opacity-100 opacity-0 md:w-auto px-4 hidden z-50 bg-white border border-gray-200 md:shadow-2xl rounded-xl before:absolute top-full before:-top-5 before:start-0 before:w-full before:h-5"
                                        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-floating-dark" data-astro-cid-pwmmw5ba>

                                    <div class="my-4 md:px-1 space-y-1 text-sm text-center text-primary flex flex-col gap-y-2 items-center" data-astro-cid-pwmmw5ba>

                                        {{-- Foto + nombre en el dropdown --}}
                                        <div class="flex flex-col items-center gap-2 pb-1">
                                            <img src="{{ $photoUrl }}"
                                                 alt="Foto de perfil"
                                                 onerror="this.onerror=null;this.src='{{ $defaultAvatar }}'"
                                                 style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #f58634;">
                                            <p class="font-medium text-gray-700 text-sm">{{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}</p>
                                            <p class="text-xs text-gray-400">Gestiona tu cuenta</p>
                                        </div>
                                        <hr class="border border-gray-200 w-full" data-astro-cid-pwmmw5ba>
                                            <a class="flex gap-x-3.5 py-2 md:px-3 w-full items-center justify-left rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all" href="{{ route('tu_cuenta') }}" data-astro-cid-pwmmw5ba>
                                                <img src="/imgs/dropdown_icons/account.png" class="h-8 w-8 rounded-full mr-4" alt="Mi cuenta">
                                                Mi cuenta
                                            </a>

                                            @if(Auth::user()->isAdmin)
                                            <a class="flex gap-x-3.5 py-2 md:px-3 w-full items-center justify-left rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all text-primary" href="{{ route('admin.index') }}">
                                                <svg class="h-6 w-6 mr-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                                </svg>
                                                Panel Admin
                                            </a>
                                            @endif

                                        <form method="POST" action="{{ route('logout') }}" class="flex items-center w-full"
                                                data-astro-cid-pwmmw5ba>
                                        @csrf
                                        <button type="submit" class="flex items-center gap-x-3.5 py-2 md:px-3 w-full justify-left rounded-lg text-red-600 hover:text-red-700 focus:outline-none hover:bg-gray-100 hover:underline transition-all">
                                            <img src="/imgs/dropdown_icons/logout1.png" class="h-8 w-8 rounded-full mr-4" alt="Cerrar sesion" data-astro-cid-pwmmw5ba>
                                            Cerrar Sesion
                                        </button>
                                        </form>
                                    </div>
                             </div>
                         @else
                            <button  data-tooltip="Login" data-astro-cid-pwmmw5ba id="hs-dropdown-floating-dark" type="button"
                                    class="relative text-primary hover:text-hoverPrimary py-1 md:px-1 no-tooltip" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">                    
                                <svg class="h-8 w-8" data-astro-cid-pwmmw5ba xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> 
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2">
                                    </path> 
                                    <circle cx="12" cy="7" r="4">
                                    </circle> 
                                </svg>
                               <!-- <span>Login</span>-->

                            </button>

                        <div class="hs-dropdown-menu transition-opacity duration-150 hs-dropdown-open:opacity-100 opacity-0 md:w-auto px-4 hidden z-50 bg-white border border-gray-200 md:shadow-2xl rounded-xl before:absolute top-full before:-top-5 before:start-0 before:w-full before:h-5"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-floating-dark">
                            <div class="my-4 md:px-1 space-y-1 text-sm text-center text-primary flex flex-col gap-y-2 items-center">
                                <a class="flex items-center gap-x-3.5 py-2 md:px-3 w-full justify-left rounded-lg hover:bg-gray-100 focus:outline-none hover:underline transition-all" href="{{ route('login') }}">
                                    <svg class="h-8 w-8 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    Login
                                </a>
                            </div>
        </div>         
        @endauth
         
        
    </div>

      
                            </div>
                            <!-- Botón categorías (solo desktop) -->
                            <div class="hidden lg:block ps-3 sm:ps-6 sm:my-3 sm:ms-6 sm:border-s sm:border-gray-300" data-astro-cid-pwmmw5ba>
                                <button type="button" class="size-10 flex justify-center items-center text-sm font-semibold rounded-lg disabled:opacity-50 disabled:pointer-events-none" data-hs-overlay="#navbar-secondary-content" aria-controls="navbar-secondary-content"
                                    aria-label="Toggle navigation" title="Categorías" data-astro-cid-pwmmw5ba>
                                    <svg class="h-12 w-12 fill-primary hover:fill-secondary" data-astro-cid-pwmmw5ba xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 11h12v2H4zm0-5h16v2H4zm0 12h7.235v-2H4z"></path></svg>
                                </button>
                            </div>
                        </div>


                </div>
            </div>

    </div>
        </nav>
    </div>

</header>

