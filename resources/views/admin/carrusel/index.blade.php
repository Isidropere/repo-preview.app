@extends('layouts.admin')

@section('title', 'Carrusel del Home - Admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-images text-primary"></i>
                    Carrusel del Home
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Administra las imágenes panorámicas del banner principal de la página de inicio y configura la velocidad de transición.
                </p>
            </div>
            <div>
                <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    Ver Home en Vivo
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm font-medium flex items-center gap-2 animate-fade-in shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-xl text-sm font-medium shadow-sm">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-500"></i> Por favor corrige los siguientes errores:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

            {{-- ══════════════ 1. CONFIGURACIÓN DE VELOCIDAD ══════════════ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-primary flex items-center justify-center font-bold text-sm">
                            <i class="fas fa-stopwatch"></i>
                        </span>
                        <h2 class="text-lg font-bold text-gray-800">Velocidad del Carrusel</h2>
                    </div>
                    <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                        Controla cada cuántos segundos cambia automáticamente de imagen en la página principal.
                    </p>

                    <form action="{{ route('admin.carrusel.velocidad') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                                Tiempo por Diapositiva: <span id="labelSegundos" class="text-primary font-black text-sm">{{ $velocidadSegundos }}</span> segundos
                            </label>
                            
                            {{-- Range Slider --}}
                            <input type="range" id="rangeVelocidad" min="1" max="15" step="1" value="{{ $velocidadSegundos }}" 
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary mb-3"
                                oninput="actualizarInput(this.value)">

                            {{-- Numeric Input --}}
                            <div class="flex items-center gap-2">
                                <input type="number" id="inputSegundos" name="velocidad_segundos" min="1" max="60" value="{{ $velocidadSegundos }}"
                                    class="w-24 px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 text-center focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"
                                    oninput="actualizarSlider(this.value)">
                                <span class="text-xs text-gray-500 font-medium">segundos ({{ $velocidadSegundos * 1000 }} ms)</span>
                            </div>
                        </div>

                        {{-- Botones de preajuste rápido --}}
                        <div class="mb-5">
                            <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Ajustes rápidos:</span>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="setVelocidad(3)" class="px-2 py-1.5 text-xs font-medium rounded-lg border border-gray-200 hover:border-primary hover:text-primary transition bg-gray-50 text-center">
                                    ⚡ 3 seg<br><span class="text-[10px] text-gray-400">Rápido</span>
                                </button>
                                <button type="button" onclick="setVelocidad(5)" class="px-2 py-1.5 text-xs font-medium rounded-lg border border-gray-200 hover:border-primary hover:text-primary transition bg-gray-50 text-center">
                                    ⏱️ 5 seg<br><span class="text-[10px] text-gray-400">Normal</span>
                                </button>
                                <button type="button" onclick="setVelocidad(8)" class="px-2 py-1.5 text-xs font-medium rounded-lg border border-gray-200 hover:border-primary hover:text-primary transition bg-gray-50 text-center">
                                    🐢 8 seg<br><span class="text-[10px] text-gray-400">Lento</span>
                                </button>
                            </div>
                        </div>

                        <div class="mb-5 flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <input type="checkbox" id="is_autoplay" name="is_autoplay" value="1" {{ $isAutoplay ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-primary focus:ring-primary/30 border-gray-300">
                            <label for="is_autoplay" class="text-xs text-gray-700 font-medium cursor-pointer">
                                Reproducción automática activada (Autoplay)
                            </label>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-primary hover:bg-hoverPrimary text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            Guardar Velocidad
                        </button>
                    </form>
                </div>
            </div>

            {{-- ══════════════ 2. SUBIR NUEVA IMAGEN ══════════════ --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200/80 p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </span>
                    <h2 class="text-lg font-bold text-gray-800">Subir Nueva Imagen al Carrusel</h2>
                </div>
                <p class="text-xs text-gray-500 mb-5">
                    Sube banners panorámicos atractivos. Recomendación: formato horizontal <strong>1200 x 530 px</strong> o proporción similar (JPG, PNG o WebP, máx 10 MB).
                </p>

                <form action="{{ route('admin.carrusel.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Selector de Archivo / Dropzone --}}
                        <div class="flex flex-col">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                                Imagen del Banner <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="relative border-2 border-dashed border-gray-300 hover:border-primary rounded-2xl p-4 text-center cursor-pointer transition flex flex-col items-center justify-center min-h-[180px] bg-gray-50 hover:bg-orange-50/20 group"
                                 onclick="document.getElementById('inputImagen').click()">
                                
                                <input type="file" id="inputImagen" name="imagen" accept="image/*" class="hidden" required onchange="previsualizarImagen(event)">
                                
                                <div id="previewContainer" class="hidden w-full h-full">
                                    <img id="imgPreview" src="#" alt="Vista previa" class="w-full h-36 object-cover rounded-xl shadow-sm mb-2 border border-gray-200">
                                    <p class="text-[11px] text-primary font-bold">Haz clic para cambiar imagen</p>
                                </div>

                                <div id="placeholderUpload" class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-400 group-hover:text-primary transition mb-2">
                                        <i class="fas fa-image text-xl"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">Seleccionar o arrastrar imagen</span>
                                    <span class="text-[11px] text-gray-400 mt-1">JPG, PNG o WebP (1200x530 px recomendado)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Campos de Configuración --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                    Título / Texto Alternativo (Opcional)
                                </label>
                                <input type="text" name="titulo" placeholder="Ej: Gran feria de trueques"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                    Subtítulo (Opcional)
                                </label>
                                <input type="text" name="subtitulo" placeholder="Ej: Miles de artículos disponibles"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                        Orden
                                    </label>
                                    <input type="number" name="orden" value="{{ ($slides->max('orden') ?? 0) + 1 }}" min="1"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                        Enlace URL (Opcional)
                                    </label>
                                    <input type="text" name="enlace" placeholder="/categorias/..."
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-hoverSecondary text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-plus-circle"></i>
                                    Subir Imagen al Carrusel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

        {{-- ══════════════ 3. LISTADO DE IMÁGENES ACTUALES ══════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Imágenes del Carrusel en Activo ({{ $slides->count() }})</h2>
                    <p class="text-xs text-gray-500">Estas son las diapositivas que rotan en el carrusel de la página de inicio.</p>
                </div>
            </div>

            @if($slides->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-images text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-700">No hay diapositivas registradas</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                        Actualmente el sistema está mostrando las 3 imágenes predeterminadas. Sube una imagen arriba para personalizar tu banner.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($slides as $slide)
                        <div class="p-5 flex flex-col md:flex-row items-center justify-between gap-5 hover:bg-gray-50/50 transition">
                            
                            {{-- Miniatura y Datos --}}
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <div class="relative w-36 h-20 flex-shrink-0 rounded-xl overflow-hidden shadow-sm border border-gray-200 bg-gray-100 group">
                                    <img src="{{ $slide->imagen_url }}" alt="{{ $slide->titulo ?? 'Slide' }}" class="w-full h-full object-cover">
                                    <a href="{{ $slide->imagen_url }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
                                        <i class="fas fa-search-plus"></i>
                                    </a>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                            Orden #{{ $slide->orden }}
                                        </span>
                                        @if($slide->activo)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Visible
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Oculta
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800 truncate">
                                        {{ $slide->titulo ?: 'Diapositiva sin título' }}
                                    </h3>
                                    @if($slide->subtitulo)
                                        <p class="text-xs text-gray-500 truncate">{{ $slide->subtitulo }}</p>
                                    @endif
                                    @if($slide->enlace)
                                        <p class="text-[11px] text-blue-500 truncate flex items-center gap-1 mt-0.5">
                                            <i class="fas fa-link text-[9px]"></i> {{ $slide->enlace }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                                
                                {{-- Toggle Activo / Inactivo --}}
                                <form action="{{ route('admin.carrusel.toggle', $slide->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition flex items-center gap-1.5 {{ $slide->activo ? 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100' : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                        @if($slide->activo)
                                            <i class="fas fa-eye-slash"></i> Pausar
                                        @else
                                            <i class="fas fa-eye"></i> Activar
                                        @endif
                                    </button>
                                </form>

                                {{-- Botón Eliminar --}}
                                <form action="{{ route('admin.carrusel.destroy', $slide->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta imagen del carrusel?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition flex items-center gap-1.5">
                                        <i class="fas fa-trash-alt"></i> Eliminar
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

<script>
    function previsualizarImagen(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imgPreview').src = e.target.result;
                document.getElementById('previewContainer').classList.remove('hidden');
                document.getElementById('placeholderUpload').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function actualizarInput(valor) {
        document.getElementById('inputSegundos').value = valor;
        document.getElementById('labelSegundos').innerText = valor;
    }

    function actualizarSlider(valor) {
        if (valor >= 1 && valor <= 15) {
            document.getElementById('rangeVelocidad').value = valor;
        }
        document.getElementById('labelSegundos').innerText = valor;
    }

    function setVelocidad(segundos) {
        document.getElementById('rangeVelocidad').value = segundos;
        document.getElementById('inputSegundos').value = segundos;
        document.getElementById('labelSegundos').innerText = segundos;
    }
</script>
@endsection
