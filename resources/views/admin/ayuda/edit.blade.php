@extends('layouts.admin')

@section('title', 'Editar Página de Ayuda | CambialóRD')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.ayuda.index')])

        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar: {{ $pagina->titulo }}</h1>
                <p class="text-sm text-gray-500 mt-1">Modifica los detalles generales de la página y su flujo de pasos.</p>
            </div>
            <a href="/{{ $pagina->slug }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 text-xs font-semibold rounded-xl transition shadow-sm">
                Ver en Web
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Formulario Página --}}
            <div class="lg:col-span-1">
                <form action="{{ route('admin.ayuda.update_page', $pagina->id) }}" method="POST" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    @csrf
                    @method('PUT')
                    
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Información General</h2>

                    <div class="mb-4">
                        <label for="titulo" class="block text-xs font-semibold text-gray-600 mb-1.5">Título de la Página</label>
                        <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $pagina->titulo) }}" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">
                        @error('titulo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label for="descripcion" class="block text-xs font-semibold text-gray-600 mb-1.5">Introducción / Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="5" required
                                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">{{ old('descripcion', $pagina->descripcion) }}</textarea>
                        @error('descripcion')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-primary hover:bg-hoverPrimary text-white text-xs font-semibold rounded-xl transition shadow-sm">
                        Guardar Cambios
                    </button>
                </form>
            </div>

            {{-- Listado de Pasos --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Pasos del Tutorial</h2>
                        <a href="{{ route('admin.ayuda.create_step', $pagina->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-secondary hover:bg-secondary/90 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Agregar Paso
                        </a>
                    </div>

                    @if($pagina->pasos->isEmpty())
                    <div class="text-center py-12 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-sm">No hay pasos agregados para este tutorial.</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($pagina->pasos as $paso)
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 border border-gray-100 rounded-2xl hover:border-gray-200 transition gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    #{{ $paso->orden }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-800 text-sm truncate">{{ $paso->titulo }}</h3>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ Str::limit($paso->descripcion, 160) }}</p>
                                    @if($paso->imagen)
                                    <div class="mt-2.5 flex items-center gap-2">
                                        <span class="text-[10px] text-gray-400 font-semibold uppercase">Imagen:</span>
                                        <a href="{{ $paso->imagen }}" target="_blank" class="block w-12 h-8 border border-gray-200 rounded overflow-hidden bg-gray-50">
                                            <img src="{{ $paso->imagen }}" class="w-full h-full object-cover">
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end md:self-center">
                                <a href="{{ route('admin.ayuda.edit_step', $paso->id) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                    Editar
                                </a>
                                <form action="{{ route('admin.ayuda.destroy_step', $paso->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este paso?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
