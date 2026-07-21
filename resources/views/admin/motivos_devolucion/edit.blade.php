@extends('layouts.admin')

@section('title', 'Editar Motivo de Devolución')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.motivos_devolucion.index')])

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar Motivo</h1>
            <p class="text-sm text-gray-500 mt-1">Edita la descripción o cambia el estado del motivo seleccionado.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
            <form action="{{ route('admin.motivos_devolucion.update', $motivo->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <label for="motivo" class="block text-sm font-semibold text-gray-700 mb-2">Descripción del Motivo</label>
                    <input type="text" name="motivo" id="motivo" required
                           value="{{ old('motivo', $motivo->motivo) }}"
                           placeholder="Ej: El producto llegó en mal estado"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent @error('motivo') border-red-500 @enderror">
                    @error('motivo')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="activo" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <select name="activo" id="activo" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent w-full">
                        <option value="1" {{ old('activo', $motivo->activo) == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('activo', $motivo->activo) == '0' ? 'selected' : '' }}>Inactivo (Ocultar temporalmente)</option>
                    </select>
                    @error('activo')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 mt-6">
                    <a href="{{ route('admin.motivos_devolucion.index') }}" 
                       class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary hover:bg-hoverPrimary text-white rounded-lg text-sm transition-colors font-medium shadow-sm">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
