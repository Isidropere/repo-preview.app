@extends('layouts.admin')

@section('title', 'Carrusel del Home - Admin')

@push('head_styles')
<link rel="stylesheet" href="{{ asset('css/cropper.min.css') }}">
<style>
    /* Estilos personalizados para el cropper y simulador */
    .cropper-view-box,
    .cropper-face {
        border-radius: 8px;
    }
    .cropper-line, .cropper-point {
        background-color: #f97316 !important; /* primary color */
    }
    .cropper-view-box {
        outline: 2px solid #f97316 !important;
        outline-color: rgba(249, 115, 22, 0.75) !important;
    }
    .aspect-carrusel {
        aspect-ratio: 1200 / 530;
    }
</style>
@endpush

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
                    Administra las imágenes panorámicas del banner principal, recórtalas a la proporción exacta (1200x530 px) y previsualiza cómo se verán en vivo.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-external-link-alt text-xs text-primary"></i>
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
                        Controla cada cuántos segundos cambia automáticamente de diapositiva en la página principal.
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

            {{-- ══════════════ 2. SUBIR NUEVA IMAGEN CON AJUSTE Y PREVIEW ══════════════ --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200/80 p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Subir y Formatear Imagen del Carrusel</h2>
                        <p class="text-xs text-gray-500">
                            Al seleccionar una imagen, se abrirá el modal para <strong>ajustar, encuadrar y ver cómo quedará en vivo</strong> (1200 x 530 px).
                        </p>
                    </div>
                </div>

                <form id="formSubirCarrusel" action="{{ route('admin.carrusel.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Campo oculto con la imagen recortada en Base64 a 1200x530 --}}
                    <input type="hidden" id="imagenRecortadaBase64" name="imagen_recortada_base64" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        {{-- Selector de Archivo / Dropzone --}}
                        <div class="flex flex-col">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                                Imagen del Banner <span class="text-red-500">*</span>
                            </label>
                            
                            {{-- Input de archivo real --}}
                            <input type="file" id="inputImagen" name="imagen" accept="image/*" class="hidden" onchange="alSeleccionarImagen(event)">

                            {{-- Estado: Sin imagen seleccionada --}}
                            <div id="dropzoneEmpty" 
                                 class="relative border-2 border-dashed border-gray-300 hover:border-primary rounded-2xl p-4 text-center cursor-pointer transition flex flex-col items-center justify-center min-h-[200px] bg-gray-50 hover:bg-orange-50/20 group"
                                 onclick="document.getElementById('inputImagen').click()"
                                 ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-orange-50/30');"
                                 ondragleave="this.classList.remove('border-primary', 'bg-orange-50/30');"
                                 ondrop="manejarDrop(event)">
                                
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 group-hover:text-primary group-hover:scale-110 transition mb-3">
                                    <i class="fas fa-crop-alt text-2xl"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Haz clic o arrastra tu imagen aquí</span>
                                <span class="text-[11px] text-gray-400 mt-1">Se abrirá el ajustador a 1200 x 530 px</span>
                                <span class="inline-block mt-2 px-2.5 py-1 bg-orange-100 text-primary text-[10px] font-bold rounded-lg">
                                    <i class="fas fa-magic mr-1"></i> Formato Automático Garantizado
                                </span>
                            </div>

                            {{-- Estado: Con imagen lista / formateada --}}
                            <div id="previewCard" class="hidden border border-gray-200 rounded-2xl p-3 bg-gray-50 flex flex-col gap-2">
                                <div class="relative w-full aspect-carrusel rounded-xl overflow-hidden shadow-sm bg-black group">
                                    <img id="imgPreviewFinal" src="#" alt="Vista previa" class="w-full h-full object-cover">
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-emerald-600/90 text-white text-[10px] font-bold flex items-center gap-1 backdrop-blur-xs">
                                        <i class="fas fa-check"></i> Formateada a 1200x530 px
                                    </div>
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                        <button type="button" onclick="abrirModalAjuste()" class="px-3 py-1.5 bg-white text-gray-800 text-xs font-bold rounded-lg shadow hover:bg-gray-100 transition flex items-center gap-1">
                                            <i class="fas fa-crop text-primary"></i> Reajustar
                                        </button>
                                        <button type="button" onclick="abrirSimuladorDesdeForm()" class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg shadow hover:bg-hoverPrimary transition flex items-center gap-1">
                                            <i class="fas fa-eye"></i> Simular
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-xs pt-1">
                                    <button type="button" onclick="abrirModalAjuste()" class="text-primary hover:text-hoverPrimary font-bold flex items-center gap-1 text-xs">
                                        <i class="fas fa-sliders-h"></i> Ajustar encuadre
                                    </button>
                                    <button type="button" onclick="document.getElementById('inputImagen').click()" class="text-gray-500 hover:text-gray-700 font-medium text-xs">
                                        <i class="fas fa-exchange-alt"></i> Cambiar imagen
                                    </button>
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
                                <button type="submit" id="btnSubmitBanner" class="w-full py-2.5 bg-secondary hover:bg-hoverSecondary text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-plus-circle"></i>
                                    Subir Imagen al Carrusel
                                </button>
                                <p class="text-[11px] text-gray-400 text-center mt-1.5">
                                    Se guardará optimizada automáticamente a 1200 x 530 px (calidad 90%).
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

        {{-- ══════════════ 3. LISTADO DE IMÁGENES ACTUALES ══════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
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
                        Actualmente el sistema está mostrando el banner de bienvenida predeterminado. Sube una imagen arriba para personalizar tu página de inicio.
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($slides as $slide)
                        <div class="p-5 flex flex-col md:flex-row items-center justify-between gap-5 hover:bg-gray-50/50 transition">
                            
                            {{-- Miniatura y Datos --}}
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <div class="relative w-40 h-20 flex-shrink-0 rounded-xl overflow-hidden shadow-sm border border-gray-200 bg-gray-100 group">
                                    <img src="{{ $slide->imagen_url }}" alt="{{ $slide->titulo ?? 'Slide' }}" class="w-full h-full object-cover">
                                    <button type="button" 
                                            onclick="verSlideEnVivo('{{ $slide->imagen_url }}', '{{ addslashes($slide->titulo ?? 'Slide #' . $slide->orden) }}')" 
                                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <i class="fas fa-desktop"></i> Ver en Carrusel
                                    </button>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                            Orden #{{ $slide->orden }}
                                        </span>
                                        @if($slide->activo)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Visible en Home
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Pausada
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
                            <div class="flex items-center gap-2.5 w-full md:w-auto justify-end flex-wrap">
                                
                                {{-- Botón Ver en Carrusel --}}
                                <button type="button" 
                                        onclick="verSlideEnVivo('{{ $slide->imagen_url }}', '{{ addslashes($slide->titulo ?? 'Slide #' . $slide->orden) }}')" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-blue-200 text-blue-600 bg-blue-50 hover:bg-blue-100 transition flex items-center gap-1.5">
                                    <i class="fas fa-desktop"></i> Ver en Carrusel
                                </button>

                                {{-- Toggle Activo / Pausado --}}
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

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- ══════ MODAL 1: AJUSTAR, ENCUADRAR Y PREVISUALIZAR IMAGEN ══════ --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="modalAjusteCarrusel" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-xs transition-opacity" onclick="cerrarModalAjuste()"></div>

    <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-4xl border border-gray-200 my-8">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-orange-100 text-primary flex items-center justify-center font-bold text-base">
                        <i class="fas fa-crop-alt"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="modal-title">
                            Ajustar Imagen para el Carrusel
                        </h3>
                        <p class="text-xs text-gray-500">
                            Proporción oficial fija: <strong class="text-primary font-bold">1200 x 530 px</strong> (2.26 : 1)
                        </p>
                    </div>
                </div>
                <button type="button" onclick="cerrarModalAjuste()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            {{-- Tabs de navegación --}}
            <div class="border-b border-gray-200 bg-white px-6">
                <nav class="-mb-px flex space-x-6">
                    <button type="button" id="tabBtnAjustar" onclick="cambiarTabModal('ajustar')" 
                            class="py-3 px-1 border-b-2 font-bold text-xs flex items-center gap-2 border-primary text-primary transition">
                        <i class="fas fa-crop"></i> 1. Ajustar y Encuadrar
                    </button>
                    <button type="button" id="tabBtnPreview" onclick="cambiarTabModal('preview')" 
                            class="py-3 px-1 border-b-2 font-bold text-xs flex items-center gap-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition">
                        <i class="fas fa-eye"></i> 2. Vista Previa en Carrusel
                    </button>
                </nav>
            </div>

            {{-- Contenido del Modal --}}
            <div class="p-6">
                
                {{-- TAB 1: AJUSTE / CROPPER --}}
                <div id="tabContentAjustar" class="space-y-4">
                    <div class="relative w-full bg-gray-900 rounded-xl overflow-hidden flex items-center justify-center min-h-[340px] max-h-[480px]">
                        <img id="imagenParaCropper" src="#" alt="Para recortar" class="max-w-full block">
                    </div>

                    {{-- Barra de herramientas de ajuste --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold text-gray-500 uppercase mr-1">Zoom:</span>
                            <button type="button" onclick="cropperZoom(0.1)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 flex items-center justify-center text-xs transition shadow-xs" title="Acercar">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" onclick="cropperZoom(-0.1)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 flex items-center justify-center text-xs transition shadow-xs" title="Alejar">
                                <i class="fas fa-search-minus"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold text-gray-500 uppercase mr-1">Girar:</span>
                            <button type="button" onclick="cropperRotate(-90)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 flex items-center justify-center text-xs transition shadow-xs" title="Girar 90° izquierda">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" onclick="cropperRotate(90)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 flex items-center justify-center text-xs transition shadow-xs" title="Girar 90° derecha">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" onclick="cropperReset()" class="px-2.5 h-8 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 flex items-center justify-center text-xs font-semibold transition shadow-xs" title="Centrar y reiniciar">
                                <i class="fas fa-sync-alt mr-1"></i> Centrar
                            </button>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-orange-100 text-primary font-bold text-[11px]">
                                <i class="fas fa-lock text-[10px]"></i> Ratio 1200:530
                            </span>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: VISTA PREVIA SIMULADOR EN VIVO --}}
                <div id="tabContentPreview" class="hidden space-y-4">
                    
                    {{-- Opciones del Simulador --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50 p-3 rounded-xl border border-gray-200">
                        {{-- Selector Dispositivo --}}
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-600 uppercase">Dispositivo:</span>
                            <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5">
                                <button type="button" id="btnPreviewDesktop" onclick="setDispositivoPreview('desktop')" class="px-3 py-1 text-xs font-bold rounded-md bg-primary text-white shadow-xs transition">
                                    <i class="fas fa-desktop mr-1"></i> Computadora
                                </button>
                                <button type="button" id="btnPreviewMobile" onclick="setDispositivoPreview('mobile')" class="px-3 py-1 text-xs font-semibold rounded-md text-gray-600 hover:text-gray-900 transition">
                                    <i class="fas fa-mobile-alt mr-1"></i> Teléfono Móvil
                                </button>
                            </div>
                        </div>

                        {{-- Toggle Overlay Tarjeta de Categorías --}}
                        <div class="flex items-center gap-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggleCategoriasOverlay" checked onchange="actualizarOverlaySimulador()" class="w-4 h-4 rounded text-primary focus:ring-primary/30 border-gray-300">
                                <span class="ml-2 text-xs text-gray-700 font-semibold select-none">
                                    Simular panel de "Categorías Populares" del Home
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Marco Computadora (Desktop 1200x530) --}}
                    <div id="contenedorSimuladorDesktop" class="w-full">
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>Simulación Pantalla Grande (Home Real)</span>
                            <span class="text-primary font-bold">1200 x 530 px</span>
                        </div>

                        <div class="relative w-full aspect-carrusel bg-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-300 select-none">
                            {{-- Imagen del banner en el carrusel --}}
                            <img id="imgSimuladorDesktop" src="#" alt="Simulación Desktop" class="w-full h-full object-cover">

                            {{-- Flechas simuladas del carrusel --}}
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <div class="w-9 h-9 rounded-full bg-white/80 backdrop-blur-xs text-gray-700 flex items-center justify-center shadow-md">
                                    <i class="fas fa-chevron-left text-sm"></i>
                                </div>
                            </div>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                <div class="w-9 h-9 rounded-full bg-white/80 backdrop-blur-xs text-gray-700 flex items-center justify-center shadow-md">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </div>
                            </div>

                            {{-- Superposición de Categorías Populares (Idéntico a home.blade.php) --}}
                            <div id="simuladorOverlayDesktop" class="absolute inset-0 z-20 flex flex-col justify-center items-center p-4 sm:p-6 pointer-events-none transition-opacity duration-200">
                                <div class="text-center bg-orange-200/65 backdrop-blur-xs p-3 sm:p-4 rounded-2xl shadow-lg w-full max-w-[90%] border border-orange-300/40">
                                    <h4 class="text-sm sm:text-base md:text-xl font-black text-gray-900 mb-2">
                                        Categorías Populares
                                    </h4>
                                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 justify-items-center opacity-90">
                                        <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                            <span class="text-sm sm:text-lg">👗</span>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Damas</span>
                                        </div>
                                        <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                            <span class="text-sm sm:text-lg">👔</span>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Caballeros</span>
                                        </div>
                                        <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                            <span class="text-sm sm:text-lg">🧸</span>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Niños</span>
                                        </div>
                                        <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                            <span class="text-sm sm:text-lg">📱</span>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Teléfonos</span>
                                        </div>
                                        <div class="hidden sm:flex bg-white/70 rounded-xl p-1.5 flex-col items-center w-full">
                                            <span class="text-sm sm:text-lg">💻</span>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Laptops</span>
                                        </div>
                                        <div class="hidden sm:flex bg-white/70 rounded-xl p-1.5 flex-col items-center w-full">
                                            <span class="text-sm sm:text-lg">🚗</span>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Vehículos</span>
                                        </div>
                                    </div>
                                    <p class="text-[9px] text-orange-950 font-semibold mt-1">
                                        * Este panel se muestra en la portada sobre el banner. Asegúrate que tu texto importante no quede oculto.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Marco Teléfono Móvil --}}
                    <div id="contenedorSimuladorMobile" class="hidden w-full flex-col items-center">
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">
                            Simulación en Smartphone (Pantalla vertical)
                        </div>

                        {{-- Dispositivo Mockup --}}
                        <div class="w-full max-w-[320px] bg-gray-900 rounded-[36px] p-3 shadow-2xl border-4 border-gray-800">
                            {{-- Barra de estado del móvil --}}
                            <div class="flex items-center justify-between text-white text-[10px] px-3 pt-1 pb-2">
                                <span>9:41</span>
                                <div class="w-16 h-3.5 bg-black rounded-full"></div>
                                <span><i class="fas fa-wifi text-[9px] mr-1"></i><i class="fas fa-battery-full text-[9px]"></i></span>
                            </div>

                            {{-- Pantalla móvil --}}
                            <div class="bg-gray-50 rounded-[24px] overflow-hidden">
                                {{-- Mini carrusel en móvil (250px alto proporcional) --}}
                                <div class="relative w-full h-36 bg-gray-900 overflow-hidden">
                                    <img id="imgSimuladorMobile" src="#" alt="Simulación Mobile" class="w-full h-full object-cover">
                                    <div class="absolute bottom-2 left-0 right-0 flex justify-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                                    </div>
                                </div>

                                {{-- Tarjeta de categorías móvil (en móvil va debajo o semi-superpuesto) --}}
                                <div id="simuladorOverlayMobile" class="p-3 bg-orange-100/70 m-2 rounded-xl text-center border border-orange-200">
                                    <span class="text-[10px] font-bold text-gray-800 block">Categorías</span>
                                    <div class="grid grid-cols-3 gap-1 mt-1 text-[8px] text-gray-600 font-bold">
                                        <div class="bg-white rounded p-1">👗 Damas</div>
                                        <div class="bg-white rounded p-1">👔 Caballeros</div>
                                        <div class="bg-white rounded p-1">📱 Teléfonos</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Barra inferior home indicator --}}
                            <div class="w-24 h-1 bg-white/40 rounded-full mx-auto mt-2"></div>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Footer con Botones de Acción --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <button type="button" onclick="cerrarModalAjuste()" class="px-4 py-2 border border-gray-200 text-gray-600 text-xs font-semibold rounded-xl hover:bg-white transition">
                    Cancelar
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" id="btnIrAPreview" onclick="cambiarTabModal('preview')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                        <i class="fas fa-eye"></i> Ver Preview
                    </button>
                    <button type="button" onclick="aplicarRecorteYConfirmar()" class="px-5 py-2 bg-primary hover:bg-hoverPrimary text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        Confirmar y Formatear (1200x530)
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- ══════ MODAL 2: VER CUALQUIER SLIDE EXISTENTE EN VIVO ══════════ --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="modalSlideEnVivo" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-xs transition-opacity" onclick="cerrarModalSlideEnVivo()"></div>

    <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-4xl border border-gray-200 my-8">
            
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-base">
                        <i class="fas fa-desktop"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="tituloSlideEnVivo">
                            Previsualización en el Carrusel del Home
                        </h3>
                        <p class="text-xs text-gray-500">
                            Así es exactamente como se visualiza esta diapositiva en la tienda.
                        </p>
                    </div>
                </div>
                <button type="button" onclick="cerrarModalSlideEnVivo()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Opciones --}}
                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggleOverlaySlideExistente" checked onchange="toggleOverlaySlideFunc()" class="w-4 h-4 rounded text-primary focus:ring-primary/30 border-gray-300">
                        <span class="ml-2 text-xs text-gray-700 font-semibold select-none">
                            Mostrar panel flotante de "Categorías Populares"
                        </span>
                    </label>
                    <a href="{{ route('home') }}" target="_blank" class="text-xs font-bold text-primary hover:text-hoverPrimary flex items-center gap-1">
                        Abrir Home real <i class="fas fa-external-link-alt text-[10px]"></i>
                    </a>
                </div>

                {{-- Marco Simulación Desktop --}}
                <div class="relative w-full aspect-carrusel bg-gray-900 rounded-2xl overflow-hidden shadow-lg border border-gray-300 select-none">
                    <img id="imgSlideEnVivo" src="#" alt="Slide en vivo" class="w-full h-full object-cover">

                    {{-- Flechas --}}
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <div class="w-9 h-9 rounded-full bg-white/80 backdrop-blur-xs text-gray-700 flex items-center justify-center shadow-md">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </div>
                    </div>
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                        <div class="w-9 h-9 rounded-full bg-white/80 backdrop-blur-xs text-gray-700 flex items-center justify-center shadow-md">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </div>

                    {{-- Overlay Categorías --}}
                    <div id="overlaySlideExistente" class="absolute inset-0 z-20 flex flex-col justify-center items-center p-4 sm:p-6 pointer-events-none transition-opacity duration-200">
                        <div class="text-center bg-orange-200/65 backdrop-blur-xs p-3 sm:p-4 rounded-2xl shadow-lg w-full max-w-[90%] border border-orange-300/40">
                            <h4 class="text-sm sm:text-base md:text-xl font-black text-gray-900 mb-2">
                                Categorías Populares
                            </h4>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 justify-items-center opacity-90">
                                <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                    <span class="text-sm sm:text-lg">👗</span>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Damas</span>
                                </div>
                                <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                    <span class="text-sm sm:text-lg">👔</span>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Caballeros</span>
                                </div>
                                <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                    <span class="text-sm sm:text-lg">🧸</span>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Niños</span>
                                </div>
                                <div class="bg-white/70 rounded-xl p-1.5 flex flex-col items-center w-full">
                                    <span class="text-sm sm:text-lg">📱</span>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Teléfonos</span>
                                </div>
                                <div class="hidden sm:flex bg-white/70 rounded-xl p-1.5 flex-col items-center w-full">
                                    <span class="text-sm sm:text-lg">💻</span>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Laptops</span>
                                </div>
                                <div class="hidden sm:flex bg-white/70 rounded-xl p-1.5 flex-col items-center w-full">
                                    <span class="text-sm sm:text-lg">🚗</span>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-800">Vehículos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
                <button type="button" onclick="cerrarModalSlideEnVivo()" class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-xl transition">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/cropper.min.js') }}"></script>
<script>
    // Variables de control de Cropper y Simulador
    let cropperInstancia = null;
    let urlImagenOriginal = null;
    let imagenRecortadaDataUrl = null;

    // Proporción exacta del carrusel: 1200 / 530 (~2.26415)
    const CAROUSEL_ASPECT_RATIO = 1200 / 530;
    const CAROUSEL_WIDTH = 1200;
    const CAROUSEL_HEIGHT = 530;

    // Manejador al arrastrar archivo a la zona dropzone
    function manejarDrop(event) {
        event.preventDefault();
        event.currentTarget.classList.remove('border-primary', 'bg-orange-50/30');
        if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
            document.getElementById('inputImagen').files = event.dataTransfer.files;
            alSeleccionarImagen({ target: document.getElementById('inputImagen') });
        }
    }

    // Manejador cuando se selecciona una imagen
    function alSeleccionarImagen(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                urlImagenOriginal = e.target.result;
                abrirModalAjuste();
            };

            reader.readAsDataURL(file);
        }
    }

    // Abrir Modal de Ajuste
    function abrirModalAjuste() {
        if (!urlImagenOriginal) {
            document.getElementById('inputImagen').click();
            return;
        }

        const modal = document.getElementById('modalAjusteCarrusel');
        const imgCropper = document.getElementById('imagenParaCropper');
        
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Ir por defecto al tab 1
        cambiarTabModal('ajustar');

        // Inicializar Cropper.js
        imgCropper.src = urlImagenOriginal;

        if (cropperInstancia) {
            cropperInstancia.destroy();
        }

        setTimeout(() => {
            cropperInstancia = new Cropper(imgCropper, {
                aspectRatio: CAROUSEL_ASPECT_RATIO,
                viewMode: 1, // Restringe el recorte al tamaño de la imagen
                dragMode: 'move',
                autoCropArea: 1, // Ocupar el 100% posible manteniendo proporción
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                ready: function() {
                    // Actualizar imagen para el preview
                    actualizarPreviewDesdeCropper();
                },
                cropend: function() {
                    actualizarPreviewDesdeCropper();
                }
            });
        }, 150);
    }

    // Cerrar Modal de Ajuste
    function cerrarModalAjuste() {
        const modal = document.getElementById('modalAjusteCarrusel');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Cambiar entre tabs del modal (Ajustar vs Previsualizar)
    function cambiarTabModal(tab) {
        const btnAjustar = document.getElementById('tabBtnAjustar');
        const btnPreview = document.getElementById('tabBtnPreview');
        const contentAjustar = document.getElementById('tabContentAjustar');
        const contentPreview = document.getElementById('tabContentPreview');

        if (tab === 'ajustar') {
            btnAjustar.className = 'py-3 px-1 border-b-2 font-bold text-xs flex items-center gap-2 border-primary text-primary transition';
            btnPreview.className = 'py-3 px-1 border-b-2 font-bold text-xs flex items-center gap-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition';
            contentAjustar.classList.remove('hidden');
            contentPreview.classList.add('hidden');
        } else {
            btnPreview.className = 'py-3 px-1 border-b-2 font-bold text-xs flex items-center gap-2 border-primary text-primary transition';
            btnAjustar.className = 'py-3 px-1 border-b-2 font-bold text-xs flex items-center gap-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition';
            contentAjustar.classList.add('hidden');
            contentPreview.classList.remove('hidden');

            actualizarPreviewDesdeCropper();
        }
    }

    // Actualiza la imagen del simulador a partir de la selección actual del cropper
    function actualizarPreviewDesdeCropper() {
        if (!cropperInstancia) return;

        try {
            const canvas = cropperInstancia.getCroppedCanvas({
                width: CAROUSEL_WIDTH,
                height: CAROUSEL_HEIGHT,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (canvas) {
                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                document.getElementById('imgSimuladorDesktop').src = dataUrl;
                document.getElementById('imgSimuladorMobile').src = dataUrl;
            }
        } catch (e) {
            console.error('Error al generar preview de cropper:', e);
        }
    }

    // Botón: Confirmar y Formatear
    function aplicarRecorteYConfirmar() {
        if (!cropperInstancia) return;

        const canvas = cropperInstancia.getCroppedCanvas({
            width: CAROUSEL_WIDTH,
            height: CAROUSEL_HEIGHT,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (canvas) {
            imagenRecortadaDataUrl = canvas.toDataURL('image/jpeg', 0.92);

            // Guardar en el input oculto que se envía en el POST
            document.getElementById('imagenRecortadaBase64').value = imagenRecortadaDataUrl;

            // Mostrar la card formateada en el formulario
            document.getElementById('imgPreviewFinal').src = imagenRecortadaDataUrl;
            document.getElementById('dropzoneEmpty').classList.add('hidden');
            document.getElementById('previewCard').classList.remove('hidden');

            // Quitar el 'required' del file input porque ya tenemos la versión recortada en base64
            document.getElementById('inputImagen').removeAttribute('required');

            cerrarModalAjuste();
        }
    }

    // Abrir simulador directamente desde la card del formulario
    function abrirSimuladorDesdeForm() {
        abrirModalAjuste();
        setTimeout(() => {
            cambiarTabModal('preview');
        }, 200);
    }

    // Controles de Cropper
    function cropperZoom(delta) {
        if (cropperInstancia) cropperInstancia.zoom(delta);
    }

    function cropperRotate(degrees) {
        if (cropperInstancia) cropperInstancia.rotate(degrees);
    }

    function cropperReset() {
        if (cropperInstancia) cropperInstancia.reset();
    }

    // Switch de Dispositivo en el Simulador
    function setDispositivoPreview(tipo) {
        const btnDesktop = document.getElementById('btnPreviewDesktop');
        const btnMobile = document.getElementById('btnPreviewMobile');
        const contDesktop = document.getElementById('contenedorSimuladorDesktop');
        const contMobile = document.getElementById('contenedorSimuladorMobile');

        if (tipo === 'desktop') {
            btnDesktop.className = 'px-3 py-1 text-xs font-bold rounded-md bg-primary text-white shadow-xs transition';
            btnMobile.className = 'px-3 py-1 text-xs font-semibold rounded-md text-gray-600 hover:text-gray-900 transition';
            contDesktop.classList.remove('hidden');
            contMobile.classList.add('hidden');
        } else {
            btnMobile.className = 'px-3 py-1 text-xs font-bold rounded-md bg-primary text-white shadow-xs transition';
            btnDesktop.className = 'px-3 py-1 text-xs font-semibold rounded-md text-gray-600 hover:text-gray-900 transition';
            contDesktop.classList.add('hidden');
            contMobile.classList.remove('hidden');
            contMobile.classList.add('flex');
        }
    }

    // Toggle Overlay en el simulador
    function actualizarOverlaySimulador() {
        const checked = document.getElementById('toggleCategoriasOverlay').checked;
        const overlayDesktop = document.getElementById('simuladorOverlayDesktop');
        const overlayMobile = document.getElementById('simuladorOverlayMobile');

        if (checked) {
            overlayDesktop.classList.remove('hidden');
            overlayMobile.classList.remove('hidden');
        } else {
            overlayDesktop.classList.add('hidden');
            overlayMobile.classList.add('hidden');
        }
    }

    // ══════════════ MODAL SLIDE EN VIVO EXISTENTE ══════════════
    function verSlideEnVivo(url, titulo) {
        document.getElementById('imgSlideEnVivo').src = url;
        document.getElementById('tituloSlideEnVivo').innerText = titulo ? ('Carrusel: ' + titulo) : 'Previsualización en el Carrusel';
        document.getElementById('modalSlideEnVivo').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function cerrarModalSlideEnVivo() {
        document.getElementById('modalSlideEnVivo').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function toggleOverlaySlideFunc() {
        const checked = document.getElementById('toggleOverlaySlideExistente').checked;
        const overlay = document.getElementById('overlaySlideExistente');
        if (checked) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    }

    // ══════════════ CONFIGURACIÓN DE VELOCIDAD ══════════════
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
@endpush
@endsection
