@extends('layouts.app')

@section('title', 'Cambialord - Agregar Producto')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-xl mx-auto px-4">
        @include('components.btn-volver', ['backUrl' => route('items.user')])

        {{-- Header --}}
        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/</svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Publicar Producto</h1>
            <p class="text-gray-500 mt-1">Completa la información de tu artículo</p>
        </div>

        {{-- Stepper --}}
        <div class="flex items-center justify-center mb-5 gap-0">
            <div class="flex items-center">
                <div id="step-icon-1" class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow">1</div>
                <span class="ml-2 text-sm font-medium text-primary hidden sm:inline">Info</span>
            </div>
            <div class="w-10 sm:w-16 h-0.5 bg-gray-300 mx-2" id="step-line-1"></div>
            <div class="flex items-center">
                <div id="step-icon-2" class="w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                <span class="ml-2 text-sm font-medium text-gray-400 hidden sm:inline">Multimedia</span>
            </div>
            <div class="w-10 sm:w-16 h-0.5 bg-gray-300 mx-2" id="step-line-2"></div>
            <div class="flex items-center">
                <div id="step-icon-3" class="w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                <span class="ml-2 text-sm font-medium text-gray-400 hidden sm:inline">Detalles</span>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <div class="flex items-center mb-1"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><span class="font-semibold">Corrige los siguientes errores:</span></div>
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf

            {{-- ═══ PASO 1: Información básica ═══ --}}
            <div id="step-1" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                    Información básica
                </h2>

                <div class="space-y-2.5">
                    {{-- Nombre --}}
                    <div>
                        <label for="item" class="block text-xs font-medium text-gray-700 mb-0.5">Nombre del producto <span class="text-red-500">*</span></label>
                        <input type="text" id="item" name="item"  value="{{ old('item') }}" placeholder="Ej: iPhone 14 Pro Max"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                        @error('item')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                    {{-- Precio y Descuento en fila --}}
                    <div class="grid grid-cols-2 gap-3" style="align-items:end">
                        <div>
                            <label for="valor" class="block text-xs font-medium text-gray-700 mb-0.5">Precio (DOP) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">RD$</span>
                                <input type="text" id="valor" name="valor"  value="{{ old('valor') }}" placeholder="0.00" inputmode="decimal" oninput="formatPrice(this)"
                                       class="w-full pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors" style="padding-left:3rem">
                            </div>
                            @error('valor')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="descuento" class="block text-xs font-medium text-gray-700 mb-0.5">Descuento</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">%</span>
                                <input type="number" id="descuento" name="descuento" value="{{ old('descuento', 0) }}" min="0" max="100" placeholder="0"
                                       class="w-full pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors" style="padding-left:1.75rem">
                            </div>
                            @error('descuento')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Cantidad y Categoría --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="cantidad" class="block text-xs font-medium text-gray-700 mb-0.5">Cantidad <span class="text-red-500">*</span></label>
                            <input type="number" id="cantidad" name="cantidad"  min="1" value="{{ old('cantidad', 1) }}" placeholder="1"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            @error('cantidad')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="id_categoria_item" class="block text-xs font-medium text-gray-700 mb-0.5">Categoría <span class="text-red-500">*</span></label>
                            <select id="id_categoria_item" name="id_categoria_item" 
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id_categoria_item }}" {{ old('id_categoria_item') == $categoria->id_categoria_item ? 'selected' : '' }}>{{ $categoria->categoria }}</option>
                                @endforeach
                            </select>
                            @error('id_categoria_item')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" onclick="goToStep(2)" class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-hoverPrimary transition-colors font-medium">
                        Siguiente <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ═══ PASO 2: Multimedia ═══ --}}
            <div id="step-2" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 hidden">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                    Imágenes y video
                </h2>

                {{-- Imagen principal --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Imagen o video principal <span class="text-red-500">*</span></label>
                    @error('imagen_principal')<span class="text-red-500 text-xs mb-2 block">{{ $message }}</span>@enderror
                    <label for="imagen_principal"
                        class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 hover:border-primary/50 hover:bg-primary/5 overflow-hidden group transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 pointer-events-none text-center preview-default">
                            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Arrastra o haz clic para subir</p>
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP o MP4 (Máx. 10MB)</p>
                        </div>
                        <img id="imagen_principal_preview" class="hidden absolute inset-0 w-full h-full object-cover rounded-xl" alt="Vista previa"/>
                        <video id="video_principal_preview" class="hidden absolute inset-0 w-full h-full object-cover rounded-xl" controls></video>
                        <span id="imagen_principal_filename" class="file-name text-xs text-gray-700 absolute bottom-2 left-2 bg-white/90 px-2 py-0.5 rounded-full max-w-[90%] truncate hidden"></span>
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center hidden preview-actions transition-opacity">
                            <button type="button" class="text-white bg-red-500 rounded-full p-2.5 hover:bg-red-600 transition-colors shadow-lg" data-action="remove">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        <input id="imagen_principal" hidden name="imagen_principal" type="file" class="imagen-principal-input" accept="image/jpeg,image/png,image/webp,video/mp4">
                    </label>
                </div>

                {{-- Imágenes adicionales --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Imágenes adicionales <span class="text-gray-400">(opcional)</span></label>
                    @error('imagenes.*')<span class="text-red-500 text-xs mb-2 block">{{ $message }}</span>@enderror
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="image-upload-container">
                        @for($i = 0; $i < 4; $i++)
                        <label class="block h-24 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 hover:border-primary/50 hover:bg-primary/5 overflow-hidden relative group transition-all">
                            <span class="file-name text-xs text-gray-700 absolute bottom-1 left-1 bg-white/80 px-1 rounded max-w-[90%] truncate hidden"></span>
                            <input type="file" name="imagenes[]" accept="image/jpeg,image/png,image/webp" class="hidden imagen-input" data-index="{{ $i }}">
                            <div class="flex flex-col items-center justify-center h-full pointer-events-none text-center preview-default p-2">
                                <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                <p class="text-xs text-gray-400">Imagen {{ $i + 1 }}</p>
                            </div>
                            <img class="preview-image hidden absolute inset-0 w-full h-full object-cover rounded-xl" alt="Vista previa"/>
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center hidden preview-actions">
                                <button type="button" class="text-white bg-red-500 rounded-full p-1.5 hover:bg-red-600 transition-colors" data-action="remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </label>
                        @endfor
                    </div>
                </div>

                <div class="flex justify-between mt-4">
                    <button type="button" onclick="goToStep(1)" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Anterior
                    </button>
                    <button type="button" onclick="goToStep(3)" class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-hoverPrimary transition-colors font-medium">
                        Siguiente <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ═══ PASO 3: Detalles ═══ --}}
            <div id="step-3" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 hidden">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">3</span>
                    Detalles del producto
                </h2>

                <div class="space-y-2.5">
                    {{-- Descripción --}}
                    <div>
                        <label for="presentacion" class="block text-xs font-medium text-gray-700 mb-0.5">Descripción <span class="text-red-500">*</span></label>
                        <textarea id="presentacion" name="presentacion" rows="2" placeholder="Describe tu producto: estado, características, incluye accesorios, etc."
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors resize-none">{{ old('presentacion') }}</textarea>
                        @error('presentacion')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                    {{-- Estado y Tipo transacción --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="condicion" class="block text-xs font-medium text-gray-700 mb-0.5">Estado <span class="text-red-500">*</span></label>
                            <select id="condicion" name="condicion" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                                <option value="1" {{ old('condicion') == 1 ? 'selected' : '' }}>Nuevo</option>
                                <option value="2" {{ old('condicion') == 2 ? 'selected' : '' }}>Usado - Como nuevo</option>
                                <option value="3" {{ old('condicion') == 3 ? 'selected' : '' }}>Usado - Buen estado</option>
                                <option value="4" {{ old('condicion') == 4 ? 'selected' : '' }}>Usado - Aceptable</option>
                            </select>
                            @error('condicion')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="tipo_trans" class="block text-xs font-medium text-gray-700 mb-0.5">Modalidad <span class="text-red-500">*</span></label>
                            <select id="tipo_trans" name="tipo_trans" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                                <option value="1" {{ old('tipo_trans') == 1 ? 'selected' : '' }}>Venta</option>
                                <option value="2" {{ old('tipo_trans') == 2 ? 'selected' : '' }}>Intercambio</option>
                                <option value="3" {{ old('tipo_trans') == 3 ? 'selected' : '' }}>Venta o Intercambio</option>
                            </select>
                            @error('tipo_trans')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Tipo artículo --}}
                    <div>
                        <label for="id_tipo_item" class="block text-xs font-medium text-gray-700 mb-0.5">Tipo de artículo</label>
                        <select id="id_tipo_item" name="id_tipo_item" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                            <option value="1" {{ old('id_tipo_item') == 1 ? 'selected' : '' }}>Producto</option>
                            <option value="2" {{ old('id_tipo_item') == 2 ? 'selected' : '' }}>Servicio</option>
                        </select>
                    </div>

                    {{-- Dimensiones colapsable --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="toggleDimensions()" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                            <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                Dimensiones y peso <span class="text-gray-400 font-normal">(opcional)</span>
                            </span>
                            <svg id="dim-arrow" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="dimensions-panel" class="hidden px-4 py-4 space-y-3">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Peso (lbs)</label>
                                    <input type="number" id="peso_lbs" name="peso_lbs" step="0.01" min="0" value="{{ old('peso_lbs', 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Alto (cm)</label>
                                    <input type="number" id="alto_cm" name="alto_cm" step="0.1" min="0" value="{{ old('alto_cm', 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Ancho (cm)</label>
                                    <input type="number" id="ancho_cm" name="ancho_cm" step="0.1" min="0" value="{{ old('ancho_cm', 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Profundidad (cm)</label>
                                    <input type="number" id="profundo_cm" name="profundo_cm" step="0.1" min="0" value="{{ old('profundo_cm', 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                </div>
                            </div>
                            @error('peso_lbs')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Colores colapsable --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" id="colorDropdownBtn" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                            <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                Colores y stock <span class="text-gray-400 font-normal">(opcional)</span>
                            </span>
                            <svg id="color-arrow" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="colorDropdown" class="hidden px-4 py-4">
                            <div class="max-h-48 overflow-y-auto space-y-1">
                                @foreach($groupedColors as $family => $colorsInFamily)
                                    @if(count($colorsInFamily) > 0)
                                    <div>
                                        <button type="button" class="w-full flex justify-between items-center text-left text-xs font-semibold text-gray-500 uppercase tracking-wider py-1 group-toggle" data-target="family-{{ Str::slug($family) }}">
                                            {{ $family }}
                                            <svg class="w-3 h-3 transform transition-transform group-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div id="family-{{ Str::slug($family) }}" class="hidden space-y-1 ml-2">
                                            @foreach($colorsInFamily as $color)
                                            <div class="flex items-center justify-between py-1.5 px-2 hover:bg-gray-50 rounded">
                                                <label for="color-{{ $color->id_color }}" class="flex items-center cursor-pointer">
                                                    <input type="checkbox" id="color-{{ $color->id_color }}" name="colors[]" value="{{ $color->id_color }}" class="color-checkbox mr-2 h-4 w-4 text-primary rounded focus:ring-primary" {{ in_array($color->id_color, old('colors', [])) ? 'checked' : '' }}>
                                                    <span class="w-4 h-4 rounded-full border border-gray-300 mr-2" style="background-color: {{ $color->codigo_hex }}"></span>
                                                    <span class="text-sm text-gray-700">{{ $color->nombre }}</span>
                                                </label>
                                                <input type="number" name="stock[{{ $color->id_color }}]" min="0" placeholder="Stock" value="{{ old('stock.'.$color->id_color, 0) }}"
                                                       class="stock-input text-sm w-16 px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-primary {{ in_array($color->id_color, old('colors', [])) ? '' : 'hidden' }}">
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            <div id="selectedColorsPreview" class="mt-3 flex flex-wrap gap-2"></div>
                            @error('colors')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-4">
                    <button type="button" onclick="goToStep(2)" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Anterior
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-secondary text-white rounded-lg hover:bg-hoverSecondary transition-colors font-medium shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Publicar producto
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ═══ Stepper ═══
let currentStep = 1;
function goToStep(step) {
    document.getElementById('step-' + currentStep).classList.add('hidden');
    document.getElementById('step-' + step).classList.remove('hidden');
    for (let i = 1; i <= 3; i++) {
        const icon = document.getElementById('step-icon-' + i);
        if (i <= step) { icon.className = 'w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow'; }
        else { icon.className = 'w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold'; }
    }
    for (let i = 1; i <= 2; i++) {
        const line = document.getElementById('step-line-' + i);
        line.className = i < step ? 'w-10 sm:w-16 h-0.5 bg-primary mx-2' : 'w-10 sm:w-16 h-0.5 bg-gray-300 mx-2';
    }
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Si hay errores, mostrar todos los pasos y re-habilitar botón
@if($errors->any())
document.querySelectorAll('[id^="step-"]').forEach(el => el.classList.remove('hidden'));
const submitBtn = document.getElementById('submitBtn');
if (submitBtn) {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Publicar producto';
}
@endif

// ═══ Precio ═══
function formatPrice(input) {
    let v = input.value.replace(/[^0-9.]/g, '');
    input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ═══ Dimensiones toggle ═══
function toggleDimensions() {
    const panel = document.getElementById('dimensions-panel');
    const arrow = document.getElementById('dim-arrow');
    panel.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// ═══ Colores toggle ═══
document.getElementById('colorDropdownBtn').addEventListener('click', function() {
    document.getElementById('colorDropdown').classList.toggle('hidden');
    document.getElementById('color-arrow').classList.toggle('rotate-180');
});

// Familia toggle
document.querySelectorAll('.group-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        target.classList.toggle('hidden');
        this.querySelector('.group-arrow').classList.toggle('rotate-180');
    });
});

// Color checkbox → show/hide stock
document.querySelectorAll('.color-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const row = this.closest('.flex.items-center.justify-between');
        const stockInput = row ? row.querySelector('.stock-input') : null;
        if (stockInput) { this.checked ? stockInput.classList.remove('hidden') : stockInput.classList.add('hidden'); }
    });
});

// ═══ Imagen principal ═══
document.addEventListener('DOMContentLoaded', function() {
    const inp = document.getElementById('imagen_principal');
    const prevDef = document.querySelector('#step-2 .preview-default');
    const prevImg = document.getElementById('imagen_principal_preview');
    const prevVid = document.getElementById('video_principal_preview');
    const fname = document.getElementById('imagen_principal_filename');
    const prevAct = document.querySelector('#step-2 .preview-actions');

    inp.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const valid = ['image/jpeg','image/png','image/webp','video/mp4'];
        if (!valid.includes(file.type)) { alert('Solo JPEG, PNG, WebP o MP4'); this.value=''; return; }
        if (file.size > 10*1024*1024) { alert('Máximo 10MB'); this.value=''; return; }
        prevDef.classList.add('hidden'); prevImg.classList.add('hidden'); prevVid.classList.add('hidden');
        if (file.type.startsWith('image/')) {
            const r = new FileReader();
            r.onload = e => { prevImg.src=e.target.result; prevImg.classList.remove('hidden'); fname.textContent=file.name; fname.classList.remove('hidden'); if(prevAct) prevAct.classList.remove('hidden'); };
            r.readAsDataURL(file);
        } else {
            prevVid.src = URL.createObjectURL(file); prevVid.classList.remove('hidden'); fname.textContent=file.name; fname.classList.remove('hidden'); if(prevAct) prevAct.classList.remove('hidden');
        }
    });

    if (prevAct) {
        prevAct.querySelector('[data-action="remove"]').addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            inp.value=''; prevImg.src=''; prevImg.classList.add('hidden'); prevVid.src=''; prevVid.classList.add('hidden');
            prevDef.classList.remove('hidden'); fname.classList.add('hidden'); prevAct.classList.add('hidden');
        });
    }

    // ═══ Imágenes adicionales ═══
    document.querySelectorAll('.imagen-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0]; const label = input.closest('label'); if (!label || !file) return;
            const pi = label.querySelector('.preview-image'), pd = label.querySelector('.preview-default'), fn = label.querySelector('.file-name'), pa = label.querySelector('.preview-actions');
            if (!pi||!pd||!fn||!pa) return;
            const ok = ['image/jpeg','image/png','image/webp'];
            if (!ok.includes(file.type)) { alert('Solo JPG, PNG o WebP'); this.value=''; return; }
            if (file.size > 2*1024*1024) { alert('Máximo 2MB'); this.value=''; return; }
            const r = new FileReader();
            r.onload = e => { pd.classList.add('hidden'); pi.src=e.target.result; pi.classList.remove('hidden'); fn.textContent=file.name; fn.classList.remove('hidden'); pa.classList.remove('hidden'); };
            r.readAsDataURL(file);
        });
    });

    // Remove buttons for additional images
    document.querySelectorAll('#image-upload-container [data-action="remove"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            const label = this.closest('label'); if (!label) return;
            const inp = label.querySelector('.imagen-input'), pi = label.querySelector('.preview-image'), pd = label.querySelector('.preview-default'), fn = label.querySelector('.file-name'), pa = label.querySelector('.preview-actions');
            if (inp) inp.value=''; if (pi) { pi.src=''; pi.classList.add('hidden'); } if (pd) pd.classList.remove('hidden'); if (fn) fn.classList.add('hidden'); if (pa) pa.classList.add('hidden');
        });
    });
});

// ═══ Submit ═══
document.getElementById('productForm').addEventListener('submit', function(e) {
    let v = document.getElementById('valor'); v.value = v.value.replace(/,/g, '');
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Publicando...';
});
</script>
@endpush
