@extends('layouts.app')

@section('title', 'Agregar Paso de Ayuda | CambialóRD')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.ayuda.edit_page', $pagina->id)])

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Agregar Paso: {{ $pagina->titulo }}</h1>
            <p class="text-sm text-gray-500 mt-1">Crea un nuevo paso secuencial para este tutorial de ayuda.</p>
        </div>

        <form action="{{ route('admin.ayuda.store_step', $pagina->id) }}" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-1">
                    <label for="orden" class="block text-xs font-semibold text-gray-600 mb-1.5">Orden Visual (Número)</label>
                    <input type="number" name="orden" id="orden" value="{{ old('orden', $siguienteOrden) }}" required min="1"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">
                    @error('orden')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="titulo" class="block text-xs font-semibold text-gray-600 mb-1.5">Título del Paso (Ej: 1. Regístrate)</label>
                    <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required placeholder="Escribe el título..."
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">
                    @error('titulo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="descripcion" class="block text-xs font-semibold text-gray-600 mb-1.5">Descripción / Instrucciones</label>
                <textarea name="descripcion" id="descripcion" rows="5" required placeholder="Escribe el contenido detallado del paso..."
                          class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">{{ old('descripcion') }}</textarea>
                @error('descripcion')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="imagen" class="block text-xs font-semibold text-gray-600 mb-1.5">Imagen Ilustrativa (Opcional)</label>
                <input type="file" name="imagen" id="imagen" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                <p class="text-[10px] text-gray-400 mt-1.5">Formatos permitidos: JPG, PNG, WEBP, GIF. Máximo 4MB.</p>
                @error('imagen')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.ayuda.edit_page', $pagina->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-hoverPrimary text-white text-xs font-semibold rounded-xl transition shadow-sm">
                    Guardar Paso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
