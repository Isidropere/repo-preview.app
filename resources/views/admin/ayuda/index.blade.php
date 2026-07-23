@extends('layouts.admin')

@section('title', 'Administración de Ayuda | CambialóRD')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Páginas de Ayuda</h1>
            <p class="text-sm text-gray-500 mt-1">Configura y modifica los tutoriales informativos de la plataforma (¿Cómo realizar un intercambio?, ¿Cómo vender?, ¿Cómo comprar?).</p>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($paginas as $pag)
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow transition flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                        @if($pag->slug === 'realizar-intercambio')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        @elseif($pag->slug === 'como-vender')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @endif
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 mb-2">{{ $pag->titulo }}</h2>
                    <p class="text-xs text-gray-500 mb-6 leading-relaxed">{{ Str::limit($pag->descripcion, 120) }}</p>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <a href="{{ route('admin.ayuda.edit_page', $pag->id) }}" class="w-full text-center px-4 py-2 bg-primary hover:bg-hoverPrimary text-white text-xs font-semibold rounded-xl transition shadow-sm">
                        Editar Contenido
                    </a>
                    <a href="/{{ $pag->slug }}" target="_blank" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition flex items-center justify-center" title="Ver en Web">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
