@extends('layouts.app')

@section('title', 'Cambialord - Editar Producto')

@section('content')
    <main class="min-h-screen">
                @php
    // Define la ruta de la imagen por defecto
    $defaultImage = 'images/default-article.jpg'; 
    
    
    @endphp


         <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @include('components.btn-volver', ['backUrl' => route('items.user')])

            {{-- Mensaje de éxito --}}
                @if(session('success'))
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Mensaje de error general --}}
                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif                         

                    {{-- Errores de validación individuales --}}
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium">¡Hay problemas con tu envío!</h3>
                                    <div class="mt-2 text-sm">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            Inicio
                        </a>
                    </li>
                      <li>
                            <div class="flex items-center">
                                <!-- Flecha -->
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                          d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                          clip-rule="evenodd"></path>
                                </svg>

                                <!-- Enlace con ícono de ojo y texto -->
                                <a href="{{ route('items.VerDetalle', $item->slug) }}"
                                   class="ml-2 flex items-center text-sm font-medium text-gray-700 hover:text-primary">

                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>

                                    Ver
                                </a>
                            </div>
                        </li>
                        <li class="inline-flex items-center">
                            <button onclick="window.history.back()" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary focus:outline-none">
                                 <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                Volver
                            </button>
                            </li>
                </ol>
            </nav>
        </div>


            <div class="bg-white p-8 rounded-lg w-full max-w-lg">
                <h2 class="text-4xl text-primary font-semibold mb-6">Editar Producto</h2>
                
                <!-- Mostrar errores generales -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
               
                <form action="{{ route('items.update', $item->id_item) }}" method="POST" enctype="multipart/form-data" id="productForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Nombre del producto -->
                    <div class="mb-4">
                        <div class="relative z-0 w-full mb-4">
                            <input type="text" id="item" name="item" required placeholder="Nombre de producto"
                                   class="input-field relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                   value="{{ old('item', $item->item) }}">
                            @error('item')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Cantidad -->
                    <div class="mb-4">
                        <div class="relative z-0 w-full mb-4">
                            <input type="number" id="cantidad" name="cantidad" required min="1" step="1"
                                   class="input-field relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                   placeholder="Cantidad"
                                   value="{{ old('cantidad', $cantidad) }}">
                            @error('cantidad')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="mb-4">
                        <div class="relative z-0 w-full mb-4">
                            <input type="text" id="valor" name="valor" required  
                                   class="input-field relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                   placeholder="Precio (DOP)" inputmode="decimal" oninput="formatPrice(this)"
                                   value="{{ old('valor', number_format($item->valor, 2)) }}">
                            @error('valor')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                     <!-- descuento -->
                    <div class="mb-4">
                        <div class="relative z-0 w-full mb-4">
                            <input type="text" id="descuento" name="descuento" required  
                                   class="input-field relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                   placeholder="descuento (DOP)" inputmode="decimal" oninput="formatPrice(this)"
                                   value="{{ old('valor', number_format($item->descuento, 2)) }}">
                            @error('descuento')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                   @php
                            // Obtener imagen principal con orden_visualizacion = 1
                            $imagenPrincipal = $item->imagenes->firstWhere('orden_visualizacion', 1);
                        @endphp

                        <!-- Imagen Principal -->
                        <div class="mb-4">
                            <label for="imagen_principal" class="block text-gray-500">Imagen Principal</label>
                            @error('imagen_principal')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                            <div class="flex items-center justify-center w-full my-2">
                                <label for="imagen_principal"
                                    class="relative flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 border-gray-300 hover:bg-gray-100 overflow-hidden group">

                                    <!-- Vista previa por defecto -->
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 pointer-events-none text-center preview-default {{ $imagenPrincipal ? 'hidden' : '' }}">
                                        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 font-semibold">Click para cambiar imagen principal</p>
                                        <p class="text-xs text-gray-500">JPEG, PNG, WebP (Max. 5MB)</p>
                                    </div>

                                    <!-- Vista previa imagen principal -->
                               
                                    @if($imagenPrincipal)
                                        <img id="imagen_principal_preview"
                                            src="{{ asset('storage/' . $imagenPrincipal->ruta . '/' . $imagenPrincipal->nombre) }}"
                                            class="absolute inset-0 w-full h-full object-cover rounded-lg" />
                                    @else
                                        <img id="imagen_principal_preview"
                                            class="hidden absolute inset-0 w-full h-full object-cover rounded-lg" />
                                    @endif

                                    <!-- Nombre del archivo -->
                                    <!--<span id="imagen_principal_filename"
                                        class="file-name text-xs text-gray-700 absolute bottom-1 left-1 bg-white bg-opacity-80 px-1 rounded max-w-[90%] truncate {{ $imagenPrincipal ? '' : 'hidden' }}">
                                        {{ $imagenPrincipal?->nombre }}
                                    </span>-->

                                    <!-- Acciones (Eliminar imagen) -->
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center preview-actions {{ $imagenPrincipal ? '' : 'hidden' }}">
                                        <button type="button"
                                            class="text-white bg-red-500 rounded-full p-2 hover:bg-red-600 transition-colors"
                                            data-action="remove">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>

                                    <input id="imagen_principal" hidden name="imagen_principal" type="file"
                                        class="imagen-principal-input" accept="image/jpeg, image/png, image/webp">
                                </label>
                            </div>
                        </div>

                    
                    <!-- Botón para abrir el modal de dimensiones -->
                    <div class="mb-4">
                        <button type="button" id="dimensionsBtn" onclick="openDimensionsModal()" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 {{ $item->presentacion != null ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-600 hover:bg-gray-600' }} {{ $item->peso_lbs > 0 || $item->alto_cm > 0 ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-500 hover:bg-gray-600' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            {{ $item->peso_lbs > 0 || $item->alto_cm > 0 ? 'Dimensiones y Peso (Configurado)' : 'Especificar Dimensiones y Peso (Opcional)' }}
                        
                        </button>
                    </div>
                    
                    <!-- Campos ocultos para las dimensiones -->
                    <input type="hidden" id="peso_lbs" name="peso_lbs" value="{{ old('peso_lbs', $item->peso_lbs) }}">
                    <input type="hidden" id="alto_cm" name="alto_cm" value="{{ old('alto_cm', $item->alto_cm) }}">
                    <input type="hidden" id="ancho_cm" name="ancho_cm" value="{{ old('ancho_cm', $item->ancho_cm) }}">
                    <input type="hidden" id="profundo_cm" name="profundo_cm" value="{{ old('profundo_cm', $item->profundo_cm) }}">
                    <textarea id="presentacion" name="presentacion" class="hidden">{{ old('presentacion', $item->presentacion) }}</textarea>
                    
                    <!-- Mostrar errores de dimensiones juntos -->
                    <div class="mb-4">
                        @error('peso_lbs')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        @error('alto_cm')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        @error('ancho_cm')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        @error('profundo_cm')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        @error('presentacion')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                                        <!-- Selector de Colores -->
<div class="mb-4">
    <!-- Botón para abrir el dropdown -->
    <div class="relative">
        <button type="button" id="colorDropdownBtn"
                class="w-full flex justify-between items-center p-3 border border-gray-300 rounded-md bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary">
            <span class="text-gray-700">Seleccionar colores</span>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown de colores -->
        <div id="colorDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden">
            <div class="max-h-60 overflow-y-auto p-2">
                @foreach($groupedColors as $family => $colorsInFamily)
                    @if(count($colorsInFamily) > 0)
                        <div class="mt-3 first:mt-0">
                            <!-- CABECERA DEL GRUPO -->
                            <button type="button"
                                    class="w-full flex justify-between items-center text-left font-medium text-gray-500 text-xs uppercase tracking-wider mb-1 pb-1 border-b border-gray-200 group-toggle"
                                    data-target="family-{{ Str::slug($family) }}">
                                {{ $family }}
                                <svg class="w-4 h-4 transform transition-transform duration-200 group-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- COLORES DEL GRUPO -->
                            <div id="family-{{ Str::slug($family) }}" class="grid grid-cols-1 gap-1 hidden">
                                @foreach($colorsInFamily as $color)
                                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-md">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="color-{{ $color->id_color }}" name="colors[]" value="{{ $color->id_color }}"
                                                class="color-checkbox mr-2 h-4 w-4 text-primary rounded focus:ring-primary"
                                                {{ in_array($color->id_color, old('colors', $selectedColors)) ? 'checked' : '' }}>
                                            <label for="color-{{ $color->id_color }}" class="flex items-center cursor-pointer">
                                                <span class="w-5 h-5 rounded-full border border-gray-300 mr-2" style="background-color: {{ $color->codigo_hex }}"></span>
                                                <span class="text-sm text-gray-700">{{ $color->nombre }}</span>
                                            </label>
                                        </div>
                                        <input type="number" name="stock[{{ $color->id_color }}]" min="0" placeholder="Stock"
                                               class="stock-input text-sm w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary {{ in_array($color->id_color, old('colors', $selectedColors)) ? '' : 'hidden' }}"
                                               value="{{ old('stock.'.$color->id_color, $stockByColor[$color->id_color] ?? 0) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- BOTÓN DE CIERRE -->
            <div class="border-t border-gray-200 p-2 flex justify-end">
                <button type="button" id="closeColorDropdown" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- COLORES SELECCIONADOS -->
    <div id="selectedColorsPreview" class="mt-3 flex flex-wrap gap-2">
        @foreach($colors as $color)
            @if(in_array($color->id_color, old('colors', $selectedColors)))
                <div class="flex items-center bg-gray-100 rounded-full px-3 py-1" data-colorid="{{ $color->id_color }}">
                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: {{ $color->codigo_hex }}"></span>
                    <span class="text-xs">{{ $color->nombre }}</span>
                    <span class="ml-2 text-xs bg-gray-200 px-2 py-0.5 rounded-full">
                        Stock: {{ old('stock.'.$color->id_color, $stockByColor[$color->id_color] ?? 0) }}
                    </span>
                    <button type="button" class="ml-1 text-gray-500 hover:text-red-500 remove-color" data-colorid="{{ $color->id_color }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif
        @endforeach
    </div>

    <!-- MENSAJES DE ERROR -->
    @error('colors')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
    @error('stock.*')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>





                    <div class="mb-4">
                        <label for="condicion" class="block text-gray-500">Estatus producto / servicio</label>
                           <select id="estatus" name="estatus" class="text-gray-500 mt-1 block h-10 w-full border-0 border-b-2 border-gray-200 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                 
                           <option value="1" {{ old('estatus', $item->estatus) == 1 ? 'estatus' : '' }}>Activo</option>
                         <option value="2" {{ old('estatus', $item->estatus) == 2 ? 'estatus' : '' }}>Inactivo</option>
                         <option value="3" {{ old('estatus', $item->estatus) == 3 ? 'estatus' : '' }}>Pendiente</option>
                    </select>
                    </div>

                    <!-- Estado -->
                    <div class="mb-4">
                        <label for="condicion" class="block text-gray-500">Estado</label>
                        <select id="condicion" name="condicion" class="text-gray-500 mt-1 block h-10 w-full border-0 border-b-2 border-gray-200 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="1" {{ old('condicion', $item->condicion) == 1 ? 'selected' : '' }}>Nuevo</option>
                            <option value="2" {{ old('condicion', $item->condicion) == 2 ? 'selected' : '' }}>Usado - Como nuevo</option>
                            <option value="3" {{ old('condicion', $item->condicion) == 3 ? 'selected' : '' }}>Usado - Buen estado</option>
                            <option value="4" {{ old('condicion', $item->condicion) == 4 ? 'selected' : '' }}>Usado - Aceptable</option>
                        </select>
                        @error('condicion')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Tipo de transacción -->
                    <div class="mb-4">
                        <label for="tipo_trans" class="block text-gray-500">¿Qué desea hacer con este artículo?</label>
                        <select id="tipo_trans" name="tipo_trans" class="text-gray-500 mt-1 block h-10 w-full border-0 border-b-2 border-gray-200 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="1" {{ old('tipo_trans', $item->tipo_trans) == 1 ? 'selected' : '' }}>Venta</option>
                            <option value="2" {{ old('tipo_trans', $item->tipo_trans) == 2 ? 'selected' : '' }}>Intercambio</option>
                            <option value="3" {{ old('tipo_trans', $item->tipo_trans) == 3 ? 'selected' : '' }}>Intercambio / Venta</option>
                        </select>
                        @error('tipo_trans')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tipo de producto -->
                    <div class="mb-4">
                        <label for="id_tipo_item" class="block text-gray-500">Tipo de artículo</label>
                        <select id="id_tipo_item" name="id_tipo_item" class="text-gray-500 mt-1 block h-10 w-full border-0 border-b-2 border-gray-200 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="1" {{ old('id_tipo_item', $item->id_tipo_item) == 1 ? 'selected' : '' }}>Producto</option>
                            <option value="2" {{ old('id_tipo_item', $item->id_tipo_item) == 2 ? 'selected' : '' }}>Servicio</option>
                        </select>
                        @error('id_tipo_item')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Categoría -->
                    <div class="mb-4">
                        <label for="id_categoria_item" class="block text-gray-700">Categoría</label>
                        <select id="id_categoria_item" name="id_categoria_item" class="mt-1 h-10 block w-full text-gray-500 border-gray-200 border-0 border-b-2 focus:border-primary focus:ring-primary sm:text-sm">
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria_item }}" {{ old('id_categoria_item', $item->id_categoria_item) == $categoria->id_categoria_item ? 'selected' : '' }}>
                                    {{ $categoria->categoria }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_categoria_item')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Imágenes adicionales -->
                    <div class="my-4">
                        <label class="block text-gray-500 mb-2">Más Imágenes</label>
                        @error('imagenes.*')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                              @php
                            // Obtener todas las imágenes ordenadas por orden_visualizacion
                            $imagenesOrdenadas = $item->imagenes->sortBy('orden_visualizacion');

                          // Las imágenes secundarias son las demás (orden_visualizacion > 1)
                             $imagenesSecundarias = $imagenesOrdenadas->where('orden_visualizacion', '>', 1)->values();
                      @endphp
                 
                      <div class="grid grid-cols-2 gap-4" id="image-upload-container">
    @php
        $imagenesOrdenadas = $item->imagenes->sortBy('orden_visualizacion');
        $imagenesSecundarias = $imagenesOrdenadas->where('orden_visualizacion', '>', 1)->values();
    @endphp

    @for($i = 0; $i < 4; $i++)
        <div class="flex items-center justify-center w-full mb-4">
            <div class="w-full">
                <label class="block w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 border-gray-300 hover:bg-gray-100 overflow-hidden relative group">
                    
                    @php $imagen = $imagenesSecundarias[$i] ?? null; @endphp

                    <!--{{-- Nombre archivo (si lo hay) --}}
                    <span class="file-name text-xs text-gray-700 absolute bottom-1 left-1 bg-white bg-opacity-80 px-1 rounded max-w-[90%] truncate {{ $imagen ? '' : 'hidden' }}">
                        {{ $imagen ? $imagen->nombre : '' }}
                    </span>-->

                    {{-- Imagen preexistente --}}
                    @if($imagen)
                        <input type="hidden" name="imagenes_existentes[]" value="{{ $imagen->id_imagen }}">
                    @endif

                    {{-- Input de imagen --}}
                    <input type="file" name="imagenes[]" accept="image/jpeg, image/png, image/webp"
                           class="hidden imagen-input" data-index="{{ $i }}">

                    {{-- Imagen previa (una sola) --}}
                    <img src="{{ $imagen ? asset('storage/' . $imagen->ruta . '/' . $imagen->nombre) : '' }}"
                         class="preview-image {{ $imagen ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover rounded-lg" />

                    {{-- Fondo por defecto --}}
                    <div class="preview-default {{ $imagen ? 'hidden' : '' }} flex flex-col items-center justify-center h-full pointer-events-none text-center">
                        <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm text-gray-500 mb-1 font-semibold">Imagen {{ $i + 1 }}</p>
                        <p class="text-xs text-gray-500">JPEG, PNG (Max. 2MB)</p>
                    </div>

                    {{-- Acciones (botón eliminar) --}}
                    <div class="preview-actions {{ $imagen ? '' : 'hidden' }} absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <button type="button" class="text-white bg-red-500 rounded-full p-1 hover:bg-red-600 transition-colors" data-action="remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                </label>
            </div>
        </div>
    @endfor
</div>



                    </div>
                    
                    <!-- Botón de submit -->
                    <div class="my-4">
                        <button type="submit" id="submitBtn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-secondary hover:bg-hoverSecondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal de Dimensiones -->
        <div id="dimensionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-semibold mb-4">Dimensiones y Peso del Producto</h3>
                
                <div class="space-y-4">
                    <!-- Peso -->
                    <div>
                        <label for="modal_peso_lbs" class="block text-sm font-medium text-gray-700">Peso (lbs)</label>
                        <input type="number" id="modal_peso_lbs" step="0.01" min="0" value="{{ old('peso_lbs', $item->peso_lbs) }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    
                    <!-- Dimensiones -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="modal_alto_cm" class="block text-sm font-medium text-gray-700">Alto (cm)</label>
                            <input type="number" id="modal_alto_cm" step="0.1" min="0" value="{{ old('alto_cm', $item->alto_cm) }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="modal_ancho_cm" class="block text-sm font-medium text-gray-700">Ancho (cm)</label>
                            <input type="number" id="modal_ancho_cm" step="0.1" min="0" value="{{ old('ancho_cm', $item->ancho_cm) }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="modal_profundo_cm" class="block text-sm font-medium text-gray-700">Profundo (cm)</label>
                            <input type="number" id="modal_profundo_cm" step="0.1" min="0" value="{{ old('profundo_cm', $item->profundo_cm) }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                        </div>
                    </div>
                    
                    <!-- Presentación -->
                    <div>
                        <label for="modal_presentacion" class="block text-sm font-medium text-gray-700">Presentación/Descripción</label>
                        <textarea id="modal_presentacion" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary sm:text-sm">{{ old('presentacion', $item->presentacion) }}</textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeDimensionsModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancelar
                    </button>
                    <button type="button" onclick="saveDimensions()"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-hoverPrimary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    // Función para formatear el precio
    function formatPrice(input) {
    // Eliminar comas existentes
    let value = input.value.replace(/,/g, '');
    
    // Formatear con nuevas comas
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    input.value = value;
}

    // Manejo del formulario principal
 document.getElementById('productForm').addEventListener('submit', function(e) {
    let valorInput = document.getElementById('valor');
    // Limpiar comas y cualquier otro carácter no numérico excepto punto decimal
       valorInput.value = valorInput.value.replace(/[^0-9.]/g, '');

        let isValid = true;
        
        //// Validación básica
        //if (!document.getElementById('item').value || !valorInput.value) {
        //    isValid = false;
        //    alert('Por favor complete todos los campos requeridos');
     //}

       // Validar cantidad
    const cantidadInput = document.getElementById('cantidad');
    if (!cantidadInput.value || cantidadInput.value < 0) {
        isValid = false;
        alert('Por favor ingrese una cantidad válida');
    }

        if (!isValid) {
            e.preventDefault();
        } else {
            // Mostrar loader
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Actualizando...
            `;
        }
    });

document.addEventListener('DOMContentLoaded', () => {
       // Manejo de imágenes principales
document.querySelector('.imagen-principal-input').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const previewImage = document.getElementById('imagen_principal_preview');
    const previewDefault = this.closest('label').querySelector('.preview-default');
    const fileNameSpan = document.getElementById('imagen_principal_filename');
    const previewActions = this.closest('label').querySelector('.preview-actions');

    if (file) {
        // Validar tipo
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Solo se permiten imágenes JPG, PNG o WebP');
            this.value = '';
            return;
        }

        // Validar tamaño
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('La imagen principal no debe exceder los 5MB');
            this.value = '';
            return;
        }

        // Crear lector
        const reader = new FileReader();

        reader.onload = function (e) {
            try {
                // Ocultar vista por defecto
                previewDefault.classList.add('hidden');

                // Limpiar imagen anterior
                previewImage.removeAttribute('src');

                // Mostrar imagen cargada
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');

                // Mostrar nombre del archivo
                fileNameSpan.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                fileNameSpan.classList.remove('hidden');

                // Mostrar acciones
                previewActions.classList.remove('hidden');
            } catch (error) {
                console.error('Error al mostrar imagen principal:', error);
                e.target.value = '';
            }
        };

        reader.onerror = function () {
            console.error('Error al leer la imagen principal:', reader.error);
            alert('Error al cargar la imagen principal.');
            e.target.value = '';
        };

        // Leer imagen
        reader.readAsDataURL(file);
    }
});

   // Manejo de imágenes secundaria
document.querySelectorAll('.imagen-input').forEach((input) => {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            const label = input.closest('label');

            const previewImage = label.querySelector('.preview-image');
            const previewDefault = label.querySelector('.preview-default');
            const fileNameSpan = label.querySelector('.file-name');
            const previewActions = label.querySelector('.preview-actions');

            if (!previewImage || !previewDefault || !fileNameSpan || !previewActions) {
                console.warn('Elementos requeridos no encontrados en el DOM.');
                return;
            }

            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                const maxSize = 2 * 1024 * 1024;

                if (!allowedTypes.includes(file.type)) {
                    alert('Solo se permiten imágenes JPG, PNG o WebP');
                    input.value = '';
                    return;
                }

                if (file.size > maxSize) {
                    alert('La imagen no debe exceder los 2MB');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('hidden');
                    previewDefault.classList.add('hidden');

                    fileNameSpan.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    fileNameSpan.classList.remove('hidden');

                    previewActions.classList.remove('hidden');
                };

                reader.onerror = function () {
                    console.error('Error al leer el archivo:', reader.error);
                    alert('Error al cargar la imagen. Por favor, intente con otra.');
                    input.value = '';
                };

                reader.readAsDataURL(file);
            }
        });
    });

document.querySelectorAll('[data-action="remove"]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const container = this.closest('label');
            const input = container.querySelector('input[type="file"]');
            const previewImage = container.querySelector('.preview-image');
            const previewDefault = container.querySelector('.preview-default');
            const fileName = container.querySelector('.file-name');
            const previewActions = container.querySelector('.preview-actions');
            const hiddenInput = container.querySelector('input[type="hidden"][name="imagenes_existentes[]"]');

            if (input) input.value = '';
            if (previewImage) {
                previewImage.src = '';
                previewImage.classList.add('hidden');
            }
            if (previewDefault) previewDefault.classList.remove('hidden');
            if (fileName) {
                fileName.textContent = '';
                fileName.classList.add('hidden');
            }
            if (previewActions) previewActions.classList.add('hidden');
            if (hiddenInput) hiddenInput.remove();
        });
    });
});

    // Funciones para el modal de dimensiones
    function openDimensionsModal() {
        // Obtener valores actuales del formulario
        document.getElementById('modal_peso_lbs').value = document.getElementById('peso_lbs').value;
        document.getElementById('modal_alto_cm').value = document.getElementById('alto_cm').value;
        document.getElementById('modal_ancho_cm').value = document.getElementById('ancho_cm').value;
        document.getElementById('modal_profundo_cm').value = document.getElementById('profundo_cm').value;
        document.getElementById('modal_presentacion').value = document.getElementById('presentacion').value;
        
        document.getElementById('dimensionsModal').classList.remove('hidden');
    }
    
    function closeDimensionsModal() {
        document.getElementById('dimensionsModal').classList.add('hidden');
    }
    
    function saveDimensions() {
        // Validar que no haya valores negativos
        const peso = parseFloat(document.getElementById('modal_peso_lbs').value) || 0;
        const alto = parseFloat(document.getElementById('modal_alto_cm').value) || 0;
        const ancho = parseFloat(document.getElementById('modal_ancho_cm').value) || 0;
        const profundo = parseFloat(document.getElementById('modal_profundo_cm').value) || 0;
        
        if (peso < 0 || alto < 0 || ancho < 0 || profundo < 0) {
            alert('Las dimensiones y el peso no pueden ser negativos');
            return;
        }
        
        // Guardar valores en los campos ocultos del formulario
        document.getElementById('peso_lbs').value = peso;
        document.getElementById('alto_cm').value = alto;
        document.getElementById('ancho_cm').value = ancho;
        document.getElementById('profundo_cm').value = profundo;
        document.getElementById('presentacion').value = document.getElementById('modal_presentacion').value;
        
              // Cambiar el estilo del botón para indicar que hay dimensiones guardadas
        const btn = document.getElementById('dimensionsBtn');
        btn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
        btn.classList.add('bg-green-600', 'hover:bg-green-600');
        btn.textContent = 'Talento o servicio (Configurado)';
        
        closeDimensionsModal();
    }
    
    // Validar que las dimensiones no sean negativas
    document.querySelectorAll('#modal_peso_lbs, #modal_alto_cm, #modal_ancho_cm, #modal_profundo_cm').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 0) {
                this.value = 0;
                alert('Las dimensiones y el peso no pueden ser negativos');
            }
        });
    });
 
    document.addEventListener('DOMContentLoaded', function () {
    const dropdownBtn = document.getElementById('colorDropdownBtn');
    const dropdown = document.getElementById('colorDropdown');
    const closeBtn = document.getElementById('closeColorDropdown');
    const previewContainer = document.getElementById('selectedColorsPreview');

    // Abrir/cerrar dropdown
    dropdownBtn?.addEventListener('click', function (e) {
        e.stopPropagation(); // Previene que el click se propague al document
        dropdown?.classList.toggle('hidden');
    });

    // Botón de cerrar
    closeBtn?.addEventListener('click', function () {
        dropdown?.classList.add('hidden');
    });

    // Cierre automático al hacer clic fuera
    document.addEventListener('click', function (event) {
        if (dropdown && !dropdown.contains(event.target) && !dropdownBtn.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Manejar despliegue de grupos de colores por familia
document.querySelectorAll('.group-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
        const targetId = this.dataset.target;
        const target = document.getElementById(targetId);
        const arrowIcon = this.querySelector('.group-arrow');

        if (target) {
            target.classList.toggle('hidden');
            if (arrowIcon) {
                arrowIcon.classList.toggle('rotate-180'); // Para girar la flecha
            }
        }
    });
});
    // Manejo de selección de colores
    document.querySelectorAll('.color-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const colorId = this.value;
            const stockInput = document.querySelector(`.stock-input[name="stock[${colorId}]"]`);

            if (!stockInput) return;

            if (this.checked) {
                stockInput.classList.remove('hidden');
                stockInput.required = true;
                addColorPreview(colorId);
            } else {
                stockInput.classList.add('hidden');
                stockInput.required = false;
                removeColorPreview(colorId);
            }
        });
    });

    // Manejo de cambio en stock
    document.querySelectorAll('.stock-input').forEach(input => {
        input.addEventListener('change', function () {
            const colorId = this.name.match(/\[(.*?)\]/)[1];
            updateStockPreview(colorId, this.value);
        });
    });

    // Función para agregar vista previa
    function addColorPreview(colorId) {
        if (document.querySelector(`#selectedColorsPreview [data-colorid="${colorId}"]`)) return;

        const colorItem = document.querySelector(`#colorDropdown [value="${colorId}"]`);
        if (!colorItem) return;

     const checkbox = document.querySelector(`#colorDropdown input[type="checkbox"][value="${colorId}"]`);
            if (!checkbox) return;

            const wrapper = checkbox.closest('.flex'); // el div que contiene todo
            if (!wrapper) return;

            const colorNameSpan = wrapper.querySelector('label span.text-sm');
            const colorSwatch = wrapper.querySelector('label span[style]');

            const colorName = colorNameSpan ? colorNameSpan.textContent : 'Color';
            const colorHex = colorSwatch ? colorSwatch.style.backgroundColor : '#ccc';


        const preview = document.createElement('div');
        preview.className = 'flex items-center bg-gray-100 rounded-full px-3 py-1 mt-1';
        preview.setAttribute('data-colorid', colorId);
        preview.innerHTML = `
            <span class="w-3 h-3 rounded-full mr-1" style="background-color: ${colorHex}"></span>
            <span class="text-xs">${colorName}</span>
            <span class="ml-2 text-xs bg-gray-200 px-2 py-0.5 rounded-full stock-preview">
                Stock: 0
            </span>
            <button type="button" class="ml-1 text-gray-500 hover:text-red-500 remove-color" data-colorid="${colorId}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        // Añadir evento a botón eliminar
        preview.querySelector('.remove-color').addEventListener('click', function () {
            const colorId = this.getAttribute('data-colorid');
            removeColorPreview(colorId);

            const checkbox = document.querySelector(`.color-checkbox[value="${colorId}"]`);
            if (checkbox) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            }
        });

        previewContainer?.appendChild(preview);
        previewContainer?.classList.remove('hidden');
    }

    // Actualizar stock en la vista previa
    function updateStockPreview(colorId, stock) {
        const previewItem = document.querySelector(`#selectedColorsPreview [data-colorid="${colorId}"] .stock-preview`);
        if (previewItem) {
            previewItem.textContent = `Stock: ${stock}`;
        }
    }

    // Eliminar vista previa
    function removeColorPreview(colorId) {
        const preview = document.querySelector(`#selectedColorsPreview [data-colorid="${colorId}"]`);
        preview?.remove();

        if (previewContainer?.children.length === 0) {
            previewContainer.classList.add('hidden');
        }
    }

    // Inicializar vista previa con valores precargados
    document.querySelectorAll('.color-checkbox:checked').forEach(checkbox => {
        const colorId = checkbox.value;
        addColorPreview(colorId);

        const stockInput = document.querySelector(`.stock-input[name="stock[${colorId}]"]`);
        if (stockInput) {
            updateStockPreview(colorId, stockInput.value);
        }
    });
});



</script>


@endpush


@push('styles')

<style>
    /* Estilos para el dropdown de colores */
    #colorDropdown .color-family {
        position: relative;
    }

    #colorDropdown .color-family:not(:first-child)::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(to right, rgba(255,0,0,0.3), rgba(255,165,0,0.3), rgba(255,255,0,0.3), rgba(0,255,0,0.3), rgba(0,0,255,0.3), rgba(75,0,130,0.3), rgba(238,130,238,0.3));
        border-radius: 2px;
    }

    #colorDropdown .family-title {
        background-color: rgba(255, 255, 255, 0.9);
        position: relative;
        z-index: 1;
        padding: 0 8px;
        display: inline-block;
    }
   
    #colorDropdown {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    #colorDropdownBtn:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
    }

    .stock-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 1px #6366f1;
    }

    #selectedColorsPreview .remove-color {
        transition: all 0.2s ease;
    }

    #selectedColorsPreview .remove-color:hover {
        transform: scale(1.1);
    }
</style>
<style>
    /* Estilos para mejorar la experiencia de usuario */
    #image-upload-container label {
        transition: all 0.3s ease;
    }

    #image-upload-container label:hover {
        border-color: #6366f1;
    }

    .preview-actions {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #image-upload-container label:hover .preview-actions {
        opacity: 1;
    }

    .file-name {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    
    /* Estilos específicos para la imagen principal */
    .group:hover {
        border-color: #6366f1;
    }

    .preview-actions {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .group:hover .preview-actions {
        opacity: 1;
    }

    .file-name {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    /* Efecto de transición para la imagen */
    #imagen_principal_preview {
        transition: opacity 0.3s ease;
    }
    
    /* Estilo para inputs con contenido */
    .input-filled + .input-label,
    textarea.input-filled + .input-label {
        opacity: 0;
        transform: translateY(-10px);
    }
    
    /* Estilo para mensajes de error */
    .text-red-500 {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
    }
</style>
@endpush
