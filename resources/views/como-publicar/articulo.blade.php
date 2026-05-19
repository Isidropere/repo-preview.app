@extends('layouts.app')

@section('title', 'Cómo publicar un artículo - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4 mb-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">¿Cómo publicar un artículo?</h1>
                <p class="text-lg mt-4">
                    Publicar un artículo en Cámbialo RD es muy sencillo. Sigue estos pasos para que tu producto esté disponible para miles de usuarios:
                </p>
            </header>
            <article class="space-y-8 text-lg">
                <!-- Paso 1 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <img src="{{ asset('imgs/como-publicar/art_paso1.png') }}" alt="Verifica tu cuenta" class="rounded-lg object-cover w-full h-64 shadow-sm border border-gray-200">
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">1. Verifica tu cuenta</h2>
                        <p>Asegúrate de estar registrado y tener tu cuenta verificada. Luego, dirígete a la sección <strong>Mis Productos</strong> y presiona el botón <strong>Crear</strong>.</p>
                    </div>
                </div>
                <!-- Paso 2 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <img src="{{ asset('imgs/como-publicar/art_paso2.png') }}" alt="Completa los detalles" class="rounded-lg object-cover w-full h-64 shadow-sm border border-gray-200">
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">2. Completa los detalles básicos</h2>
                        <p>Ingresa el nombre del producto, selecciona la categoría adecuada, establece el precio y elige la modalidad en la que deseas publicarlo (Venta, Intercambio o Ambos).</p>
                    </div>
                </div>
                <!-- Paso 3 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <img src="{{ asset('imgs/como-publicar/art_paso3.png') }}" alt="Sube fotos y videos" class="rounded-lg object-cover w-full h-64 shadow-sm border border-gray-200">
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">3. Sube tus fotos o videos</h2>
                        <p>Es obligatorio subir una imagen o video principal claro (soporta MP4/MOV de hasta 10MB). Te recomendamos añadir imágenes adicionales desde distintos ángulos para mayor visibilidad.</p>
                    </div>
                </div>
                <!-- Paso 4 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <img src="{{ asset('imgs/como-publicar/art_paso4.png') }}" alt="Añade especificaciones" class="rounded-lg object-cover w-full h-64 shadow-sm border border-gray-200">
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">4. Añade especificaciones (Opcional)</h2>
                        <p>Si aplica, puedes detallar los colores disponibles, cantidad en stock y las dimensiones del producto (peso, alto, ancho y profundo) para facilitar el cálculo de envío.</p>
                    </div>
                </div>
                <!-- Paso 5 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <img src="{{ asset('imgs/como-publicar/art_paso5.png') }}" alt="Publica tu artículo" class="rounded-lg object-cover w-full h-64 shadow-sm border border-gray-200">
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">5. Publica tu artículo</h2>
                        <p>Una vez completes el formulario, presiona "Guardar cambios". ¡Felicidades! Tu artículo se registrará en el sistema y estará visible para toda la comunidad.</p>
                    </div>
                </div>
            </article>
        </section>
    </main>
@endsection
