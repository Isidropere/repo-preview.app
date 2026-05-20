@extends('layouts.app')

@section('title', 'Próximamente')

@section('content')
<main class="min-h-[70vh] flex flex-col justify-center items-center bg-[#FAFAFA] px-4 py-16">
    <div class="text-center bg-white p-8 md:p-12 rounded-3xl shadow-lg max-w-2xl w-full border border-gray-100">
        
        <!-- Ícono decorativo tipo "construcción/campana" -->
        <div class="mx-auto w-24 h-24 mb-6 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center shadow-inner">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
            Descubre <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#479bd5] to-[#f58634]">lo que viene</span>
        </h1>
        
        <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-lg mx-auto leading-relaxed">
            Esta sección está siendo desarrollada con cariño. Muy pronto podrás disfrutar de todas estas increíbles opciones, preparadas especialmente para ti.
        </p>

        <a href="{{ url('/') }}" 
           class="inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-white transition-all duration-300 bg-secondary rounded-xl hover:bg-hoverSecondary hover:shadow-lg hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-orange-300">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Volver al Inicio
        </a>
    </div>
</main>
@endsection
