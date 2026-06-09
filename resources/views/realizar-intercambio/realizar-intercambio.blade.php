@extends('layouts.app')

@section('title', 'Envio - Cambialord')

@section('content')
 <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4 mb-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">¿Cómo realizar un intercambio?</h1>
                <p class="text-lg mt-4">
                    Realizar un intercambio en Cámbialo RD es muy fácil y rápido. Sigue estos simples pasos:
                </p>
            </header>
            <article class="space-y-8 text-lg"> <!-- Paso 1 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 1</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">1. Publica tu artículo</h2>
                        <p>
                            Crea una cuenta y sube fotos junto con una descripción del objeto que deseas intercambiar.
                        </p>
                    </div>
                </div> <!-- Paso 2 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center"> Imagen paso 2 </span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">2. Busca un intercambio</h2>
                        <p>
                            Explora los artículos disponibles en nuestra plataforma y encuentra uno que te interese.
                        </p>
                    </div>
                </div> <!-- Paso 3 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 3</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">3. Propuesta de intercambio</h2>
                        <p>
                            Contacta al usuario que ofrece el artículo que deseas y propón el intercambio.
                        </p>
                    </div>
                </div> <!-- Paso 4 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 4</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">4. Aceptación y envío</h2>
                        <p>
                            Una vez que ambos acuerden el intercambio, coordinaremos los detalles de envío. Recuerda que
                            cada usuario cubrirá el costo de enviar su respectivo artículo.
                        </p>
                    </div>
                </div> <!-- Paso 5 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 5</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">5. Confirmación</h2>
                        <p>
                            Confirma que recibiste el artículo en buen estado y completa el intercambio.
                        </p>
                    </div>
                </div>
            </article>
        </section>
    </main>

@endsection
