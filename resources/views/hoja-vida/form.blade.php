@extends('layouts.app')
@section('title', ($esEdicion ? 'Editar' : 'Crear') . ' Hoja de Vida - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-xl mx-auto px-4">

        @include('components.btn-volver', ['backUrl' => route('tu_cuenta')])

        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $esEdicion ? 'Editar' : 'Crear' }} Hoja de Vida</h1>
            <p class="text-gray-500 mt-1 text-sm">Tu perfil profesional para ofrecer talentos y servicios</p>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.85rem;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('warning'))
        <div style="background:#fefce8;border:1px solid #fde68a;color:#92400e;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.85rem;">
            {{ session('warning') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.85rem;">
            <ul style="list-style:disc;padding-left:1.25rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('hoja-vida.save') }}" method="POST">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                    Datos personales
                </h2>
                <div class="space-y-2.5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" name="nombres" required value="{{ old('nombres', $hojaVida->nombres) }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Apellidos <span class="text-red-500">*</span></label>
                            <input type="text" name="apellidos" required value="{{ old('apellidos', $hojaVida->apellidos) }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Titulo profesional <span class="text-red-500">*</span></label>
                        <input type="text" name="titulo_profesional" required maxlength="150"
                               value="{{ old('titulo_profesional', $hojaVida->titulo_profesional) }}"
                               placeholder="Ej: Disenador grafico, Mecanico automotriz, Profesor de musica"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Ubicacion <span class="text-red-500">*</span></label>
                        <input type="text" name="ubicacion" required maxlength="200"
                               value="{{ old('ubicacion', $hojaVida->ubicacion) }}"
                               placeholder="Ej: Santo Domingo, La Romana, Santiago"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                    Perfil profesional
                </h2>
                <div class="space-y-2.5">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Descripcion / Bio <span class="text-red-500">*</span></label>
                        <textarea name="descripcion_bio" rows="3" required maxlength="2000"
                                  placeholder="Describe brevemente quien eres y que haces profesionalmente..."
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none">{{ old('descripcion_bio', $hojaVida->descripcion_bio) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Habilidades <span class="text-red-500">*</span></label>
                        <textarea name="habilidades" rows="2" required maxlength="2000"
                                  placeholder="Ej: Diseno web, Photoshop, Ilustracion, Reparacion de motores..."
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none">{{ old('habilidades', $hojaVida->habilidades) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Experiencia <span class="text-red-500">*</span></label>
                        <textarea name="experiencia" rows="3" required maxlength="2000"
                                  placeholder="Describe tu experiencia laboral o proyectos relevantes..."
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none">{{ old('experiencia', $hojaVida->experiencia) }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 text-sm font-semibold rounded-lg bg-secondary text-white hover:bg-hoverSecondary transition-colors">
                {{ $esEdicion ? 'Actualizar hoja de vida' : 'Guardar hoja de vida' }}
            </button>
        </form>
    </div>
</div>
@endsection
