@extends('layouts.app')

@section('title', 'Como vender - Cambialord')

@section('content')
    <main class="min-h-screen">
        <section class="max-w-6xl mx-auto px-4 mb-4">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8">
                <h1 class="font-semibold text-primary text-4xl">{{ $pagina->titulo }}</h1>
                <p class="text-lg mt-4">
                    {{ $pagina->descripcion }}
                </p>
            </header>
            <article class="space-y-8 text-lg">
                @foreach($pagina->pasos as $paso)
                <div class="flex flex-col md:flex-row items-start">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-6 w-full md:w-1/3">
                        <div class="bg-gray-200 h-64 flex items-center justify-center overflow-hidden rounded-xl border border-gray-100">
                            @if($paso->imagen)
                            <img src="{{ $paso->imagen }}" alt="{{ $paso->titulo }}" class="w-full h-full object-cover">
                            @else
                            <span class="text-gray-500 text-center font-medium">Imagen del paso {{ $paso->orden }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-grow">
                        <h2 class="font-semibold text-primary text-2xl mb-2">{{ $paso->titulo }}</h2>
                        <p>
                            {{ $paso->descripcion }}
                        </p>
                    </div>
                </div>
                @endforeach
            </article>
        </section>
    </main> 
@endsection 
