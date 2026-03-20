@extends('layouts.app')

@section('title', 'Envio - Cambialord')

@section('content')

    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">Responsabilidad Social</h1>
            </header>
            <article class="space-y-4 text-lg">
                <p class="antialiased">
                    La responsabilidad social es un pilar fundamental en CámbialoRD.com. Creemos en el poder de las
                    pequeñas acciones para generar grandes cambios. Al facilitar el intercambio de objetos y fomentar la
                    compraventa de productos en buen estado, contribuimos a la reducción de desechos y al
                    aprovechamiento de recursos.
                </p>
                <p class="antialiased">
                    Nos comprometemos a operar de manera ética y sostenible, apoyando iniciativas que promuevan la
                    ecología y el consumo consciente en la República Dominicana. Además, parte de nuestros esfuerzos se
                    orientan a colaborar con organizaciones locales para mejorar la calidad de vida en nuestras
                    comunidades.
                </p>
            </article>
            <section class="mt-12">

           </section>
        </section>
    </main>
@endsection
