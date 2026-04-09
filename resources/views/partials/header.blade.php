
<section class="flex justify-center items-center w-full bg-primary font-medium text-center py-1">
    Encuentra lo que deseas cambiar
</section>

<!-- Header principal -->
<header class="w-full bg-[#FAFAFA] text-xl py-3 md:py-4 shadow-lg sticky top-0 z-50">

    <div class="max-w-[1400px] mx-auto px-4 md:px-4 lg:px-6">
        <!-- Contenedor principal del nav -->
        <nav class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4" aria-label="Global">
          
            <!-- Primera fila (logo, buscador móvil y menú hamburguesa) -->
            <div class="flex items-center justify-between w-full lg:w-auto gap-2">
                 <!-- Logo -->
                <a class="h-auto flex-shrink-0" href="/" aria-label="Brand">
                    <img src="/imgs/logoTypes/header-logo.png" id="logoHeader" class="object-cover w-[160px] md:w-[100px] h-auto" alt="Brand Logo">
                </a>
                <!-- Buscador móvil con input + botón pegado -->
                <div class="block lg:hidden flex-1 min-w-0">
                    <form method="GET" action="{{ route('items.search_header') }}" class="w-full flex">
                        <label for="mobile-search" class="sr-only">Buscar</label>
                        <input type="search" id="mobile-search"
                               name="q"
                               class="flex-1 min-w-0 py-1.5 px-3 text-xs text-gray-900 border border-gray-300 rounded-l-md bg-gray-50 focus:ring-1 focus:ring-secondary focus:border-secondary"
                               placeholder="Buscar..."
                               value="{{ request('q') }}"
                               required>
                        <button type="submit"
                                class="flex-shrink-0 bg-primary text-white rounded-r-md text-xs px-3 py-1.5 hover:bg-hoverPrimary whitespace-nowrap">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Botón menú hamburguesa (solo móvil) -->
                <div class="lg:hidden flex-shrink-0">
                    <button type="button" class="flex justify-center items-center p-1.5 text-sm font-semibold rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none" data-hs-collapse="#navbar-collapse-with-animation" aria-controls="navbar-collapse-with-animation" aria-label="Toggle navigation">
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
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6 lg:gap-8">
                            <a class="text-gray-700 hover:text-primary transition-colors" href="{{ route('intercambio') }}">Intercambiar</a>
                            <a class="text-gray-700 hover:text-primary transition-colors" href="{{ route('compra') }}">Comprar</a>


                        <!-- Iconos de acciones -->
                        <div class="flex items-center gap-4 lg:gap-6">

                        <div class="flex items-center gap-x-2 h-full z-auto" data-astro-cid-pwmmw5ba>

                           <a class="relative flex items-center justify-center text-primary hover:text-hoverPrimary p-2 no-tooltip"   data-astro-cid-pwmmw5ba
                           href="{{ route('carrito.show') }}" 
                           data-tooltip="Carrito">
                          
                               @if($carrito && $carrito->itemsIntencionCompra)
                                <span class="absolute top-1 right-1 bg-secondary text-white rounded-full text-xs font-bold px-1.5 leading-none">
                                    {{ $carrito->itemsIntencionCompra->where('es_seleccionado', 1)->count() }}
                                </span>
                            @else
                                <span class="absolute top-1 right-1 bg-secondary text-white rounded-full text-xs font-bold px-1.5 leading-none">
                                    0
                                </span>
                            @endif

                                <svg class="h-7 w-7 fill-primary hover:fill-hoverPrimary"
                                     xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24" fill="none" 
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     stroke-linejoin="round"> 
                                    <path d="M21.822 7.431A1 1 0 0 0 21 7H7.333L6.179 4.23A1.994 1.994 0 0 0 4.333 3H2v2h2.333l4.744 11.385A1 1 0 0 0 10 17h8c.417 0 .79-.259.937-.648l3-8a1 1 0 0 0-.115-.921zM17.307 15h-6.64l-2.5-6h11.39l-2.25 6z"></path>
                                    <circle cx="10.5" cy="19.5" r="1.5"></circle>
                                    <circle cx="17.5" cy="19.5" r="1.5"></circle>
                                </svg>
                            </a>
        
                            <x-negociaciones-modal/>
                            <x-notificaciones />

                            <div class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false]"
                                data-astro-cid-pwmmw5ba>
                            @auth
        
                                <button id="hs-dropdown-floating-dark" type="button" 
                                        class="hs-dropdown-toggle flex items-center gap-2 text-sm text-primary hover:text-hoverPrimary p-2" 
                                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">

                                    @php
                                        $photoPath = Auth::user()->profile_photo_path;
                                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->nombres . ' ' . Auth::user()->apellidos) . '&background=f58634&color=fff&size=64&rounded=true';
                                        $photoUrl = $photoPath ? asset($photoPath) : $avatarUrl;
                                    @endphp
                                    <img src="{{ $photoUrl }}"
                                         alt="Foto de perfil"
                                         onerror="this.onerror=null;this.src='{{ $avatarUrl }}'"
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
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nombres . "+" . Auth::user()->apellidos) }}&background=f58634&color=fff&size=64'"
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

                                        <form method="POST" action="{{ route('logout') }}" class="hs-dropdown-toggle flex items-center w-full text-sm text-primary hover:text-hoverPrimary relative" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"
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

