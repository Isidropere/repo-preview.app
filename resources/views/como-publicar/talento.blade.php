@extends('layouts.app')

@section('title', 'Cómo publicar un talento - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4 mb-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">¿Cómo publicar un talento o servicio?</h1>
                <p class="text-lg mt-4">
                    Ofrecer tus servicios profesionales en Cámbialo RD te conecta con clientes potenciales. Sigue estos pasos para publicar tu talento:
                </p>
            </header>
            <article class="space-y-8 text-lg">
                <!-- Paso 1 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg"> <span class="text-gray-500 text-center">Imagen paso 1</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">1. Crea tu Hoja de Vida</h2>
                        <p>Antes de ofrecer tus talentos, es obligatorio llenar tu "Hoja de Vida" (perfil profesional) en la plataforma para generar confianza en los usuarios que te contratarán.</p>
                    </div>
                </div>
                <!-- Paso 2 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg"> <span class="text-gray-500 text-center">Imagen paso 2</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">2. Describe tu servicio</h2>
                        <p>Ve a la sección de publicación de talentos, describe a detalle el servicio profesional que ofreces, selecciona la categoría correspondiente e indica tu precio u honorarios.</p>
                    </div>
                </div>
                <!-- Paso 3 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg"> <span class="text-gray-500 text-center">Imagen paso 3</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">3. Agrega contenido visual</h2>
                        <p>Sube imágenes o un video de presentación (muy recomendado) que muestren la calidad de tu trabajo o testimonios de tu experiencia.</p>
                    </div>
                </div>
                <!-- Paso 4 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg"> <span class="text-gray-500 text-center">Imagen paso 4</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">4. Pago de tarifa de registro</h2>
                        <p>Si publicas tu talento para generar ganancias comerciales (venta o intercambio), deberás realizar un pequeño pago único de registro mediante tarjeta vía CardNet directamente en la plataforma.</p>
                    </div>
                </div>
                <!-- Paso 5 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg"> <span class="text-gray-500 text-center">Imagen paso 5</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">5. ¡Empieza a recibir ofertas!</h2>
                        <p>Una vez procesado el pago, tu talento quedará publicado inmediatamente y los usuarios podrán empezar a solicitar tus servicios.</p>
                    </div>
                </div>
            </article>
        </section>
    </main>
@endsection
