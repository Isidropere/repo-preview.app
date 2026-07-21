@extends('layouts.admin')

@section('title', 'Agregar Vacante | Recursos Humanos | CambialóRD')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.recursos-humanos.index')])

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Crear Nueva Vacante</h1>
            <p class="text-sm text-gray-500 mt-1">Completa los campos para publicar una nueva oferta de trabajo en el portal.</p>
        </div>

        <form action="{{ route('admin.recursos-humanos.store') }}" method="POST" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            @csrf

            <div class="mb-4">
                <label for="titulo" class="block text-xs font-semibold text-gray-600 mb-1.5">Título del Puesto / Vacante</label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required placeholder="Ej: Desarrollador Backend Laravel, Cajero, Asistente de Operaciones..."
                       class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">
                @error('titulo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label for="descripcion" class="block text-xs font-semibold text-gray-600 mb-1.5">Descripción de la Vacante</label>
                <textarea name="descripcion" id="descripcion" rows="6" required placeholder="Describe las responsabilidades del puesto, la misión del rol, etc..."
                          class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">{{ old('descripcion') }}</textarea>
                @error('descripcion')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label for="requisitos" class="block text-xs font-semibold text-gray-600 mb-1.5">Requisitos e Instrucciones de Postulación</label>
                <textarea name="requisitos" id="requisitos" rows="6" required placeholder="Requisitos del puesto y detalles de cómo postularse (Ej: 'Enviar CV a cambialord.com@gmail.com con el asunto...')"
                          class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary">{{ old('requisitos') }}</textarea>
                <p class="text-[10px] text-gray-400 mt-1">Escribe las instrucciones detalladas. Asegúrate de incluir el correo de contacto.</p>
                @error('requisitos')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="activo" value="1" checked
                           class="w-4.5 h-4.5 border border-gray-200 rounded text-primary focus:ring-primary">
                    <span class="text-xs font-semibold text-gray-600">Publicar de inmediato (Vacante Activa)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.recursos-humanos.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-hoverPrimary text-white text-xs font-semibold rounded-xl transition shadow-sm">
                    Crear Vacante
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
