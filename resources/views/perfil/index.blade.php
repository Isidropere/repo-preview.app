@extends('layouts.app')

@section('title', 'perfil usuario' .'- Cambialord')

@section('content')
    <main class="min-h-screen">
        <div class="max-w-6xl mx-auto px-4"> <!-- Información del Perfil -->
            @include('components.btn-volver', ['backUrl' => route('tu_cuenta')])
            <section class="relative flex items-center w-full"> <!-- Foto de Perfil -->
                <div class="w-32  md:w-52 md:h-52 mr-2 md:mr-5"> <img class="w-full h-full object-cover rounded-full"
                        src="/profilePlaceholder.jpg" alt="Foto de Perfil"> </div> <!-- Información del Usuario -->
                <div class="bg-white p-6 max-w-md">
                    <div class="w-full flex justify-between">
                        <h1 class="text-xl md:text-3xl font-bold text-gray-800 mb-4">Nombre del Usuario</h1>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-700 mb-2"><span class="font-semibold">Provincia:</span> Santo Domingo</p>
                        <p class="text-gray-700 mb-2"><span class="font-semibold">Sexo:</span> Masculino</p>
                        <p class="text-gray-700 mb-2"><span class="font-semibold">Fecha de nacimiento:</span>
                            01/Enero/1999</p>
                    </div>
                </div>
            </section>
            <div class="mt-5 w-full flex justify-end items-center gap-x-4"> <a href="/perfil/editarPerfil"
                    class="text-primary hover:text-hoverPrimary transition-all hover:underline">
                    <div class="flex"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            class="fill-primary hover:fill-hoverPrimary h-6 w-6 transition-all">
                            <path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path>
                        </svg> <span> Editar Perfil</span> </div>
                </a> <a href="/tu-cuenta" class="text-primary hover:text-hoverPrimary transition-all hover:underline">
                    <div class="flex"> <svg xmlns="http://www.w3.org/2000/svg"
                            class="fill-primary hover:fill-hoverPrimary h-6 w-6 transition-all" viewBox="0 0 24 24"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg> <span>Tu cuenta</span> </div>
                </a> </div>
            <section class="mt-28">
                <h1 class="text-4xl font-bold mb-8">Tus articulos</h1>
                <div id="itemsContainer" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mx-2 mb-12">
                    <!-- Card 1 --> <a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/aretes-negros.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Aretes negros </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Intercambio</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Nuevo</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/billetera-en-cuero.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Billetera en cuero </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Venta</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Usado - Como nuevo</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/blusa-larga.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Blusa mangas larga </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Venta</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Usado - Buen estado</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/aretes-negros.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Aretes negros </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Intercambio</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Nuevo</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/billetera-en-cuero.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Billetera en cuero </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Venta</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Usado - Como nuevo</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/blusa-larga.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Blusa mangas larga </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Venta</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Usado - Buen estado</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/aretes-negros.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Aretes negros </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Intercambio</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Nuevo</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/billetera-en-cuero.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Billetera en cuero </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Venta</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Usado - Como nuevo</p>
                            </div>
                        </div>
                    </a><a href="/product"
                        class="bg-white rounded-lg product hover:bg-gray-100 transition-all duration-200 pb-4 relative">
                        <img src="/articulos/blusa-larga.jfif" alt="Product 1"
                            class="w-full h-[260px] object-cover rounded-md" loading="lazy">
                        <div class="mt-2 mx-4">
                            <p class="text-secondary font-bold mt-1">
                                RD $1000.00
                            </p>
                            <div class="flex justify-between mb-1">
                                <h2 class="text-lg font-semibold text-gray-800"> Blusa mangas larga </h2>
                                <div> <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-white">Intercambio</span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <!--Sustituir por provincia a la que pertenece el usuario que subió el producto -->
                                <p class="text-gray-500">Santo Domingo</p> <span class="font-semibold">|</span>
                                <p class="text-gray-500">Usado - Buen estado</p>
                            </div>
                        </div>
                    </a> </div>
            </section>
        </div>
    </main> 
@endsection
