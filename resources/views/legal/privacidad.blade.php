@extends('layouts.app')

@section('title', 'Política de Privacidad - Cambialo RD')

@section('content')
    <main class="min-h-screen py-10 bg-gray-50">
        <section class="max-w-4xl mx-auto px-4 bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8 border-b border-gray-100 pb-4">
                <h1 class="font-bold text-primary text-3xl">Política de Privacidad y Seguridad</h1>
                <p class="text-sm text-gray-500 mt-1">Última actualización: {{ date('d/m/Y') }}</p>
            </header>
            
            <article class="space-y-6 text-gray-700 leading-relaxed text-base">
                @include('legal.partials.privacidad')
            </article>
        </section>
    </main>
@endsection
