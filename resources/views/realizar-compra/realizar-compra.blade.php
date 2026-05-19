
@extends('layouts.app')

@section('title', 'Realizar compra - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4 mb-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">¿Cómo realizar una compra?</h1>
                <p class="text-lg mt-4">
                    Comprar en Cámbialo RD es seguro y sencillo. Sigue estos pasos:
                </p>
            </header>
            <article class="space-y-8 text-lg"> <!-- Paso 1 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 1</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">1. Registrate</h2>
                        <p>
                            Crea una cuenta en nuestra plataforma.
                        </p>
                    </div>
                </div> <!-- Paso 2 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 2</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">2. Busca lo que necesitas: </h2>
                        <p>
                            Explora las categorías o utiliza la barra de búsqueda para encontrar lo que estás buscando.
                        </p>
                    </div>
                </div> <!-- Paso 3 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 3</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">3. Compra</h2>
                        <p>
                            Una vez que encuentres un producto de tu interés, revisa la descripción y fotos
                            proporcionadas. Si estás satisfecho, realiza la compra a través de la plataforma.
                        </p>
                    </div>
                </div> <!-- Paso 4 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 4</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">4. Pago</h2>
                        <p>
                            Elige tu método de pago preferido y completa la transacción. El costo de envío se sumará al
                            precio del producto.
                        </p>
                    </div>
                </div> <!-- Paso 5 -->
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center"> <span
                                class="text-gray-500 text-center">Imagen paso 5</span> </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">5. Recibe tu compra</h2>
                        <p>
                            Coordinaremos la entrega recibirás tu producto en la comodidad de tu hogar.
                        </p>
                    </div>
                </div>
            </article>
        </section>
    </main>
 @endsection 
