
@extends('layouts.app')

@section('title', 'Página de Inicio')

@section('content')

<main class="min-h-screen">
        <!--<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden ">
            <div class="relative bg-white p-6 rounded-lg shadow-lg max-w-md sm:mx-auto mx-5 text-center "> <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"> <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path> </svg> </button>
                <h2 class="text-2xl font-bold mb-4">Espacio reservado para publicidad</h2>
                <p class="mb-6">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum ducimus rerum rem tenetur aperiam praesentium odio eaque aspernatur magnam, totam, ratione officia fuga vitae! Repellat sunt quam nostrum eum rem.</p>
            </div>
        </div>-->
        <section class="relative">
            <div class="h-auto w-full bg-white rounded-lg shadow-md relative">
                <div data-hs-carousel="{
            &quot;loadingClasses&quot;: &quot;opacity-0&quot;,
            &quot;isAutoPlay&quot;: &quot;true&quot;
            }" class="relative">
                    <div class="hs-carousel relative overflow-hidden w-full h-[530px] bg-white">
                        <div class="hs-carousel-body absolute top-0 bottom-0 left-0 flex flex-nowrap transition-transform duration-700">
                            <div class="hs-carousel-slide"> <img src="/imgs/1.jpg" class="h-full w-full object-cover" alt="Promoción Cambialord - Intercambia y vende productos"> </div>
                            <div class="hs-carousel-slide"> <img src="/imgs/2.jpg" class="h-full w-full object-cover" alt="Ofertas destacadas en Cambialord"> </div>
                            <div class="hs-carousel-slide"> <img src="/imgs/3.jpg" class="h-full w-full object-cover" alt="Encuentra lo que necesitas en Cambialord"> </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute inset-0 z-20 flex flex-col justify-center items-center m-4">
                <section class="text-center bg-orange-200/65 p-4 sm:p-6 rounded-2xl shadow-lg w-full h-[530px] flex items-center justify-center">
                    <div>
                        <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-bold my-2">
                            Categorías Populares
                                                </h1>
                        <div class="p-0 lg:p-6 rounded-2xl grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-x-2 lg:gap-x-8 gap-y-1 md:gap-y-4 lg:max-w-[900px] mt-0 lg:mt-4 justify-items-center mx-auto">
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(26)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/women.svg" alt="Damas alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Damas</h2>
                            </a>
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(27)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/ropa.svg" alt="Caballeros alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Caballeros</h2>
                            </a>
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(20)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/juegos.svg" alt="Niños alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Niños</h2>
                            </a>
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(19)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/celulares.svg" alt="Teléfonos alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Teléfonos</h2>
                            </a>
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(16)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/hogar.svg" alt="Hogar alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Hogar</h2>
                            </a>
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(4)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/consolas.svg" alt="Gamer alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Gamer</h2>
                            </a>
                            <a href="{{ route('categorias.show', \App\Helpers\HashIdHelper::encode(29)) }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105 "> <img src="/imgs/icons/antiguedades.svg" alt="Talentos alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Talentos</h2>
                            </a>
                                 <a href="{{ route('categorias.otras') }}" class="rounded-3xl flex flex-col items-center p-2 transition-all duration-300 hover:bg-primary/80 hover:shadow-lg hover:scale-105">
                                <img src="/imgs/icons/otros.svg" alt="Otras categorías alt" class="h-14 lg:h-24 lg:mb-2">
                                <h2 class="font-medium text-center">Otras categorías</h2>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </section>
        <section class="section lg:mt-0 md:mt-12 mt-4 mb-24  mx-auto lg:max-w-[1250px] md:max-w-[750px] max-w-[325px] ">
            <div class="flex flex-col text-center mt-12">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-normal text-gray-950">
                    Si no puedes venderlo
                    <span class="font-bold text-gradient">¡Cámbialo!</span> </h1>
                <div class="my-6">
               <!-- <a class="bg-secondary hover:bg-hoverSecondary px-6 py-1 rounded-md font-medium text-white text-4xl" href="/addProduct">Solicitar cambio</a>--> 
                       @auth
                    <a class="bg-secondary hover:bg-hoverSecondary px-6 py-1 rounded-md font-medium text-white text-xl sm:text-2xl md:text-4xl" 
                       href="{{ route('items.create') }}">Solicitar cambio</a>
                @else
                    <a class="bg-secondary hover:bg-hoverSecondary px-6 py-1 rounded-md font-medium text-white text-xl sm:text-2xl md:text-4xl" 
                       href="{{ route('login') }}">Solicitar cambio</a>
                @endauth
                </div>
            </div>
        </section>
        {{-- keen-slider CSS ya cargado en layout --}}
        <section class="bg-[#EEEEEE] mb-12">
            <div class="relative w-full px-4 py-12 sm:px-6 lg:me-0 lg:py-16 lg:pe-0 lg:ps-8 xl:py-24">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:items-center lg:gap-16">
                    <div class="max-w-xl text-center ltr:sm:text-left rtl:sm:text-right">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Productos de intercambio
                        </h2>
                        <p class="mt-4 text-2xl text-gray-700">
                            Intercambia lo que tienes por algo que quieres
                        </p>
                        <div class="hidden lg:mt-8 lg:flex justify-center lg:gap-4"> <button aria-label="Previous slide" id="keen-slider-previous2-desktop" class="rounded-full border bg-secondary border-secondary p-4 text-white transition hover:bg-hoverSecondary hover:text-white"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 rtl:rotate-180"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path> </svg> </button>                            <button aria-label="Next slide" id="keen-slider-next2-desktop" class="rounded-full border bg-secondary border-secondary p-4 text-white transition hover:bg-hoverSecondary hover:text-white"> <svg class="size-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> </svg> </button>                            </div>
                    </div>
                    <div class="lg:col-span-2 lg:mx-0 relative">
                        <div id="my-keen-slider" class="keen-slider">
                            @forelse($productosIntercambio as $prod)
                            <div class="keen-slider__slide">
                                <article class="overflow-hidden rounded-lg shadow transition hover:shadow-lg">
                                    <a href="{{ route('producto.detalle', $prod->slug) }}">
                                        @php $imgProd = $prod->imagenes->where('estado', 'aprobado')->first(); $imgValida = $imgProd && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $imgProd->nombre); @endphp
                                        @if($imgValida)
                                            <img alt="{{ $prod->item }}" src="{{ \App\Helpers\ImageHelper::urlItem($imgProd, $prod->id_categoria_item ?? 0) }}" class="h-56 w-full object-cover" loading="lazy">
                                        @else
                                            <div class="h-56 w-full bg-gray-200 flex items-center justify-center text-gray-400">Sin imagen</div>
                                        @endif
                                    </a>
                                    <div class="bg-white p-4 sm:p-6">
                                        <a href="{{ route('producto.detalle', $prod->slug) }}">
                                            <h3 class="mt-0.5 text-lg text-gray-900">{{ $prod->item }}</h3>
                                        </a>
                                        <div class="flex justify-between">
                                            <h3 class="mt-2 line-clamp-3 text-md text-gray-500">
                                                {{ $prod->direccionPredeterminada->municipio->municipio ?? 'República Dominicana' }} | {{ match($prod->condicion) { 1 => 'Nuevo', 2 => 'Como nuevo', default => 'Usado' } }}
                                            </h3>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            @empty
                            <div class="keen-slider__slide">
                                <p class="text-gray-500 p-4">No hay productos de intercambio disponibles.</p>
                            </div>
                            @endforelse
                        <div class="absolute inset-0 pointer-events-none z-10">
                            <div class="absolute top-0 bottom-0 right-0 w-1/12 bg-gradient-to-l from-[#EEEEEE] via-transparent to-transparent"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-center gap-4 lg:hidden"> <button aria-label="Previous slide" id="keen-slider-previous2" class="rounded-full border border-secondary p-4 text-secondary transition hover:bg-secondary hover:text-white"> <svg class="size-5 -rotate-180 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> </svg> </button>                    <button aria-label="Next slide" id="keen-slider-next2" class="rounded-full border border-secondary p-4 text-secondary transition hover:bg-secondary hover:text-white"> <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> </svg> </button>                    </div>
            </div>
        </section>
        {{-- keen-slider JS ya cargado en layout --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            var slider = new KeenSlider("#my-keen-slider", {
                loop: true,
                slides: {
                    origin: "center",
                    perView: 1.25,
                    spacing: 16,
                },
                breakpoints: {
                    "(min-width: 640px)": {
                        // Tablets in portrait mode
                        slides: {
                            perView: 1.5,
                            spacing: 16,
                        },
                    },
                    "(min-width: 768px)": {
                        // Tablets in landscape mode
                        slides: {
                            perView: 2,
                            spacing: 16,
                        },
                    },
                    "(min-width: 1024px)": {
                        // Desktops
                        slides: {
                            perView: 2.5,
                            spacing: 32,
                        },
                    },
                },
            });

            const keenSliderPrevious = document.getElementById("keen-slider-previous2");
            const keenSliderNext = document.getElementById("keen-slider-next2");

            const keenSliderPreviousDesktop = document.getElementById(
                "keen-slider-previous2-desktop",
            );
            const keenSliderNextDesktop = document.getElementById(
                "keen-slider-next2-desktop",
            );

            keenSliderPrevious.addEventListener("click", () => slider.prev());
            keenSliderNext.addEventListener("click", () => slider.next());

            keenSliderPreviousDesktop.addEventListener("click", () => slider.prev());
            keenSliderNextDesktop.addEventListener("click", () => slider.next());
            }); // end DOMContentLoaded
        </script>
        <section class="my-12">
            <div>
                <div data-hs-carousel="{
        &quot;loadingClasses&quot;: &quot;opacity-0&quot;,
       &quot;isAutoPlay&quot;: &quot;true&quot;
      }" class="relative ">
                    <div class="hs-carousel relative overflow-hidden w-full min-h-[250px] lg:min-h-[250px] bg-white ">
                        <div class="hs-carousel-body absolute top-0 bottom-0 start-0 flex flex-nowrap transition-transform duration-700 opacity-0">
                            <div class="hs-carousel-slide">
                            <img src="/imgs/1.jpg" class="h-full w-full object-cover" alt="Banner publicitario Cambialord"> </div>
                            <div class="hs-carousel-slide"> <img src="/imgs/2.jpg" class="h-full w-full object-cover" alt="Promociones Cambialord"> </div>
                            <div class="hs-carousel-slide"> <img src="/imgs/3.jpg" class="h-full w-full object-cover" alt="Descubre productos en Cambialord"> </div>
                        </div>
                    </div> <button type="button" class="hs-carousel-prev hs-carousel:disabled:opacity-50 disabled:pointer-events-none absolute inset-y-0 start-0 inline-flex justify-center items-center w-[46px] h-full text-gray-800 hover:bg-gray-800/10 "> <span class="text-2xl" aria-hidden="true"> <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="m15 18-6-6 6-6"></path> </svg> </span> <span class="sr-only">Previous</span> </button>                    <button type="button" class="hs-carousel-next hs-carousel:disabled:opacity-50 disabled:pointer-events-none absolute inset-y-0 end-0 inline-flex justify-center items-center w-[46px] h-full text-gray-800 hover:bg-gray-800/10 "> <span class="sr-only">Next</span> <span class="text-2xl" aria-hidden="true"> <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="m9 18 6-6-6-6"></path> </svg> </span> </button>
                    <div class="hs-carousel-pagination flex justify-center absolute bottom-3 start-0 end-0 space-x-2"> <span class="hs-carousel-active:bg-primary hs-carousel-active:border-blue-700 size-3 border border-gray-400 rounded-full cursor-pointer"></span> <span class="hs-carousel-active:bg-primary hs-carousel-active:border-blue-700 size-3 border border-gray-400 rounded-full cursor-pointer"></span>                        <span class="hs-carousel-active:bg-primary hs-carousel-active:border-blue-700 size-3 border border-gray-400 rounded-full cursor-pointer"></span> </div>
                </div>
            </div>
        </section>
        <section class="bg-[#EEEEEE] mb-12">
            <div class="relative mx-auto w-full px-4 py-12 sm:px-6 lg:me-0 lg:py-16 lg:pe-0 lg:ps-8 xl:py-24">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:items-center lg:gap-16">
                    <div class="w-full text-center ltr:sm:text-left rtl:sm:text-right">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Productos de venta
                        </h2>
                        <p class="mt-4 text-2xl text-gray-700">
                            Aquí puedes vender lo que quieras:
                        </p>
                        <div class="hidden lg:mt-8 lg:flex justify-center lg:gap-4"> <button aria-label="Previous slide" id="keen-slider-previous-desktop" class="rounded-full border bg-secondary border-secondary p-4 text-white transition hover:bg-hoverSecondary hover:text-white"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 rtl:rotate-180"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path> </svg> </button>                            <button aria-label="Next slide" id="keen-slider-next-desktop" class="rounded-full border bg-secondary border-secondary p-4 text-white transition hover:bg-hoverSecondary hover:text-white"> <svg class="size-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> </svg> </button>                            </div>
                    </div>
                    <div class=" lg:col-span-2 lg:mx-0 relative">
                        <div id="keen-slider" class="keen-slider">
                            @forelse($productosVenta as $prod)
                            <div class="keen-slider__slide">
                                <article class="overflow-hidden rounded-lg shadow transition hover:shadow-lg">
                                    <a href="{{ route('producto.detalle', $prod->slug) }}">
                                        @php $imgProd2 = $prod->imagenes->where('estado', 'aprobado')->first(); $imgValida2 = $imgProd2 && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $imgProd2->nombre); @endphp
                                        @if($imgValida2)
                                            <img alt="{{ $prod->item }}" src="{{ \App\Helpers\ImageHelper::urlItem($imgProd2, $prod->id_categoria_item ?? 0) }}" class="h-56 w-full object-cover" loading="lazy">
                                        @else
                                            <div class="h-56 w-full bg-gray-200 flex items-center justify-center text-gray-400">Sin imagen</div>
                                        @endif
                                    </a>
                                    <div class="bg-white p-4 sm:p-6">
                                        <a href="{{ route('producto.detalle', $prod->slug) }}">
                                            <h3 class="mt-0.5 text-lg text-gray-900">{{ $prod->item }}</h3>
                                        </a>
                                        <div class="flex justify-between">
                                            <h3 class="mt-2 line-clamp-3 text-md text-gray-500">
                                                RD$ {{ number_format($prod->valor, 2) }}
                                            </h3>
                                            <a href="{{ route('producto.detalle', $prod->slug) }}">
                                                <svg class="h-8 w-8 fill-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="10.5" cy="19.5" r="1.5"></circle><circle cx="17.5" cy="19.5" r="1.5"></circle><path d="M13 13h2v-2.99h2.99v-2H15V5.03h-2v2.98h-2.99v2H13V13z"></path><path d="M10 17h8a1 1 0 0 0 .93-.64L21.76 9h-2.14l-2.31 6h-6.64L6.18 4.23A2 2 0 0 0 4.33 3H2v2h2.33l4.75 11.38A1 1 0 0 0 10 17z"></path></svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            @empty
                            <div class="keen-slider__slide">
                                <p class="text-gray-500 p-4">No hay productos de venta disponibles.</p>
                            </div>
                            @endforelse
                        <div class="absolute inset-0 pointer-events-none z-10">
                            <div class="absolute top-0 bottom-0 right-0 w-1/12 bg-gradient-to-l from-[#EEEEEE] via-transparent to-transparent"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-center gap-4 lg:hidden"> <button aria-label="Previous slide" id="keen-slider-previous" class="rounded-full border border-secondary p-4 text-secondary transition hover:bg-secondary hover:text-white"> <svg class="size-5 -rotate-180 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> </svg> </button>                    <button aria-label="Next slide" id="keen-slider-next" class="rounded-full border border-secondary p-4 text-secondary transition hover:bg-secondary hover:text-white"> <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> </svg> </button>                    </div>
            </div>
        </section>
        <section class="bg-primary px-2 py-16 flex flex-col sm:flex-row justify-center items-center md:gap-x-8">
            <h1 class="text-3xl md:text-2xl lg:text-3xl font-normal text-center sm:text-left">
                ¿Quieres intercambiar o vender un producto? <br> <span class="font-bold">¡Hazlo con nosotros!</span> </h1>
            <div class="mt-6 sm:mt-0 flex gap-3">
                @auth
                <a href="{{ route('items.create') }}" class="bg-secondary hover:bg-hoverSecondary px-4 py-2 text-base md:text-2xl lg:text-3xl font-medium text-white rounded-lg">Vender</a>
                <a href="{{ route('items.create') }}" class="bg-secondary hover:bg-hoverSecondary px-4 py-2 text-base md:text-2xl lg:text-3xl font-medium text-white rounded-lg">Cambiar</a>
                @else
                <a href="{{ route('login') }}" class="bg-secondary hover:bg-hoverSecondary px-4 py-2 text-base md:text-2xl lg:text-3xl font-medium text-white rounded-lg">Vender</a>
                <a href="{{ route('login') }}" class="bg-secondary hover:bg-hoverSecondary px-4 py-2 text-base md:text-2xl lg:text-3xl font-medium text-white rounded-lg">Cambiar</a>
                @endauth
            </div>
        </section>
    </main>
@endsection

