
@extends('layouts.app')

@section('title', 'Envio - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
            <h1 class="font-semibold text-primary text-4xl">Información de Envíos</h1>

                </header> <article class="space-y-4 text-lg">
                <p class="antialiased">
                    En Cámbialo RD, facilitamos el proceso de intercambio, compra y venta de manera rápida y sencilla. Los envíos de productos se realizan a través de nuestros partners logísticos de confianza, que podrás elegir al momento de realizar el pago.
                </p> <p class="antialiased">
                    En el caso de intercambios, los usuarios solo deberán cubrir el costo del envío. Para compras, el cliente pagará tanto el precio del objeto como el costo de envío, que será claramente especificado al momento de la transacción.
                </p> <p class="antialiased">
                    Si estás vendiendo, recibirás el pago por tu producto una vez que el comprador haya confirmado la recepción de este en perfectas condiciones.
                </p>
            </article>
        </section>
    </main>

@endsection


    

