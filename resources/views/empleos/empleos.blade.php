
@extends('layouts.app')

@section('title', 'Envio - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8"> <h1 class="font-semibold text-primary text-4xl">Empleos</h1> </header> <article class="space-y-4 text-lg">
                <p class="antialiased">
                    En Cámbialo RD, estamos en constante crecimiento y buscamos personas apasionadas que deseen formar parte de nuestro equipo. Si compartes nuestra visión de un mundo más sostenible y te entusiasma la idea de transformar la manera en que las personas intercambian, compran y venden, ¡nos encantaría conocerte!
                </p> <p class="antialiased">
                    Ofrecemos un ambiente dinámico, oportunidades de desarrollo profesional y la posibilidad de contribuir a un proyecto con impacto positivo en la comunidad. Consulta nuestras vacantes actuales o envíanos tu CV a <a href="mailto:correo@cambialord.com" class="text-primary font-semibold">correo@cambialord.com</a> para unirte a nuestro equipo.
                </p>
            </article> <section class="mt-12">
                <h2 class="font-semibold text-primary text-2xl mb-6">Vacantes Disponibles</h2> <ul class="space-y-4">
                    <li class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="font-semibold text-xl text-gray-800">Nombre de vacante</h3> <p class="text-gray-700 mt-2">
                            Lorem ipsum dolor sit, amet consectetur adipisicing elit. Dolorem accusantium aut maxime perspiciatis eligendi aspernatur voluptatibus reprehenderit debitis, porro cupiditate exercitationem vero in laborum tenetur vel facere nisi maiores recusandae.
                        </p>
                    </li>
                    <li class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="font-semibold text-xl text-gray-800">Nombre de vacante</h3> <p class="text-gray-700 mt-2">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Consectetur incidunt neque, error consequatur beatae hic, perferendis possimus ex enim culpa placeat ab et. Totam nobis sapiente nam beatae cumque debitis?
                        </p>
                    </li>
                </ul>
            </section>
        </section>
    </main>
@endsection
