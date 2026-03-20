@extends('layouts.app')

@section('title', 'Sobre Nosotros - Cambialord')

@section('content')
  <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <section class="py-12">
                <header class="mb-8">
                    <h1 class="font-semibold text-primary text-6xl">Sobre nosotros</h1>
                </header>
                <article class="my-6">
                    <p class="antialiased text-lg">
                        Cámbialo RD nace con la visión de ofrecer una solución innovadora y sostenible en la República Dominicana. Somos una plataforma en línea dedicada a facilitar el intercambio, compra y venta de objetos nuevos o usados en buen estado. Nuestra misión es promover
                        un estilo de vida más ecológico y consciente, brindando a nuestros usuarios la posibilidad de darle una segunda vida a esos artículos que ya no utilizan. Con nuestro eslogan: “Si no puedes venderlo, ¡cámbialo!", queremos incentivar
                        el reciclaje y el ahorro, proporcionando una alternativa práctica para quienes desean obtener nuevos artículos sin necesidad de comprarlos, o simplemente desean vender lo que ya no usan.
                    </p>
                </article>
                <section class="mt-12">
                    <header class="mb-4">
                        <h2 class="font-semibold text-primary text-2xl">Misión</h2>
                    </header>
                    <p class="antialiased text-lg">
                        En Cámbialo RD, nuestra misión es transformar la forma en que las personas en la República Dominicana intercambian, compran y venden artículos, promoviendo un consumo consciente y sostenible. Nos dedicamos a ofrecer una plataforma en línea segura y accesible
                        que facilite el aprovechamiento de recursos, reduciendo el desperdicio y fomentando un estilo de vida más ecológico. Buscamos conectar a las personas, dándoles la oportunidad de encontrar nuevas utilidades para los objetos que
                        ya no usan, contribuyendo así a un mundo más responsable y sostenible.
                    </p>
                </section>
                <section class="mt-12">
                    <header class="mb-4">
                        <h2 class="font-semibold text-primary text-2xl">Visión</h2>
                    </header>
                    <p class="antialiased text-lg">
                        Nuestra visión es ser la plataforma líder en la República Dominicana para el intercambio, compra y venta de artículos, posicionándonos como un referente en el consumo sostenible y consciente. Aspiramos a expandir nuestra comunidad, creando un impacto
                        positivo tanto en el medio ambiente como en la economía local. Queremos ser reconocidos por nuestra capacidad de conectar a las personas, ofreciendo soluciones innovadoras que faciliten una vida más equilibrada y respetuosa con
                        el entorno.
                    </p>
                </section>
                <section class="mt-12">
                    <header class="mb-4">
                        <h2 class="font-semibold text-primary text-2xl">Valores</h2>
                    </header>
                    <ul class="list-disc pl-5 space-y-3 antialiased text-lg">
                        <li> <span class="font-bold">Sostenibilidad:</span> Fomentamos prácticas que contribuyen a la reducción de desechos y al cuidado del medio ambiente, promoviendo el intercambio y la reutilización de objetos.
                        </li>
                        <li> <span class="font-bold">Responsabilidad:</span> Operamos de manera ética y transparente, garantizando que nuestras acciones beneficien a la comunidad y respeten el entorno.
                        </li>
                        <li> <span class="font-bold">Innovación:</span> Nos esforzamos por ofrecer soluciones tecnológicas que faciliten la vida de nuestros usuarios, mejorando constantemente nuestra plataforma para adaptarnos a sus necesidades.
                        </li>
                        <li> <span class="font-bold">Confianza:</span> Brindamos un entorno seguro y confiable donde nuestros usuarios pueden realizar intercambios, compras y ventas con total tranquilidad.
                        </li>
                        <li> <span class="font-bold">Comunidad:</span> Valoramos y fortalecemos las conexiones entre nuestros usuarios, creando un espacio donde todos pueden beneficiarse mutuamente y contribuir al bien común.
                        </li>
                    </ul>
                </section>
            </section>
        </section>
    </main>
@endsection
