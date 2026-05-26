
@extends('layouts.app')

@section('title', 'Como vender - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4 mb-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">¿Cómo vender?</h1>
                <p class="text-lg mt-4">
                    Vender en Cámbialo RD es simple y seguro. Aquí te mostramos cómo hacerlo:
                </p>
            </header>
            <article class="space-y-8 text-lg"> <!-- Paso 1 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center">
                            <span class="text-gray-500 text-center">
                                <img 
                                    src="/imgs/tutorial/comovender/01.png" 
                                    alt="Imagen paso 1"
                                    class="w-50 h-30"
                                >
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">1. Regístrate</h2>
                        <p>
                            Crea una cuenta en nuestra plataforma para comenzar a vender.
                        </p>
                    </div>
                </div> <!-- Paso 2 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center">
                            <span class="text-gray-500 text-center">
                            <img 
                                src="/imgs/tutorial/comovender/02.jpeg" 
                                alt="Imagen paso 2"
                                class="w-50 h-30"
                            >
                        </span>
                            
                            </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">2. Publica tu artículo</h2>
                        <p>
                            Sube fotos claras y detalladas del objeto que deseas vender, junto con una descripción
                            precisa y el precio.
                        </p>
                    </div>
                </div> <!-- Paso 3 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center">
                             <span class="text-gray-500 text-center">
                            <img 
                                src="/imgs/tutorial/comovender/03.jpeg" 
                                alt="Imagen paso 3"
                                class="w-50 h-30"
                            >
                        </span>
                            
                            </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">3. Vende de forma directa</h2>
                        <p>
                            Los compradores añadirán tu artículo a su carrito y realizarán el pago de forma segura a través de la plataforma sin complicaciones.
                        </p>
                    </div>
                </div> <!-- Paso 4 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> 
                              <span class="text-gray-500 text-center">
                            <img 
                                src="/imgs/tutorial/comovender/04.jpeg" 
                                alt="Imagen paso 4"
                                class="w-50 h-30"
                            >
                        </span>
                            
                            </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">4. Envío</h2>
                        <p>
                            Una vez que se concrete la venta, coordinaremos el envío del producto al comprador.
                        </p>
                    </div>
                </div> <!-- Paso 5 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center">
                             <span class="text-gray-500 text-center">
                            <img 
                                src="/imgs/tutorial/comovender/05.jpeg" 
                                alt="Imagen paso 5"
                                class="w-50 h-30"
                            >
                        </span>
                            
                            </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">5. Recibe tu pago</h2>
                        <p>
                            Tras la confirmación de recepción por parte del comprador, recibirás tu pago a través de la
                            plataforma.
                        </p>
                    </div>
                </div>
            </article>
        </section>
    </main> 
  @endsection 

