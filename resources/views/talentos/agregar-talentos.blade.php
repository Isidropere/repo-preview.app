@extends('layouts.app')

@section('title', 'Cambialord - Agregar talento')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-xl mx-auto px-4">
        @include('components.btn-volver', ['backUrl' => route('items.admintalento')])

        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Publicar Talento</h1>
            <p class="text-gray-500 mt-1">Ofrece tus habilidades y servicios</p>
        </div>

        <div class="flex items-center justify-center mb-5 gap-0">
            <div class="flex items-center">
                <div id="step-icon-1" class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow">1</div>
                <span class="ml-2 text-sm font-medium text-primary hidden sm:inline">Info</span>
            </div>
            <div class="w-12 sm:w-20 h-0.5 bg-gray-300 mx-2" id="step-line-1"></div>
            <div class="flex items-center">
                <div id="step-icon-2" class="w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                <span class="ml-2 text-sm font-medium text-gray-400 hidden sm:inline">Multimedia</span>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <div class="flex items-center mb-1"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><span class="font-semibold">Corrige los siguientes errores:</span></div>
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('items.AddTalento') }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            <input type="hidden" name="condicion" value="1">
            <input type="hidden" name="id_tipo_item" value="2">
            <input type="hidden" name="peso_lbs" value="0">
            <input type="hidden" name="alto_cm" value="0">
            <input type="hidden" name="ancho_cm" value="0">
            <input type="hidden" name="profundo_cm" value="0">
            @foreach($categorias as $cat)
                @if($cat->id_categoria_item == 29)
                <input type="hidden" name="id_categoria_item" value="{{ $cat->id_categoria_item }}">
                @endif
            @endforeach

            {{-- PASO 1: Información --}}
            <div id="step-1" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                    Información del talento
                </h2>
                <div class="space-y-2.5">
                    <div>
                        <label for="item" class="block text-xs font-medium text-gray-700 mb-0.5">Nombre del talento <span class="text-red-500">*</span></label>
                        <input type="text" id="item" name="item" required value="{{ old('item') }}" placeholder="Ej: Clases de guitarra, Diseño gráfico"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        @error('item')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3" style="align-items:end">
                        <div>
                            <label for="valor" class="block text-xs font-medium text-gray-700 mb-0.5">Precio (DOP) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">RD$</span>
                                <input type="text" id="valor" name="valor" required value="{{ old('valor') }}" placeholder="0.00" inputmode="decimal" oninput="formatPrice(this)"
                                       class="w-full pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" style="padding-left:3rem">
                            </div>
                            @error('valor')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="tipo_trans" class="block text-xs font-medium text-gray-700 mb-0.5">Modalidad <span class="text-red-500">*</span></label>
                            <select id="tipo_trans" name="tipo_trans" required class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                                <option value="3" {{ old('tipo_trans') == 3 ? 'selected' : '' }}>Venta o Intercambio</option>
                                <option value="2" {{ old('tipo_trans') == 2 ? 'selected' : '' }}>Intercambio</option>
                                <option value="1" {{ old('tipo_trans') == 1 ? 'selected' : '' }}>Venta</option>
                            </select>
                            @error('tipo_trans')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="descuento" class="block text-xs font-medium text-gray-700 mb-0.5">Descuento <span class="text-gray-400">(opcional)</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">%</span>
                            <input type="number" id="descuento" name="descuento" value="{{ old('descuento', 0) }}" min="0" max="100" placeholder="0"
                                   class="w-full pr-3 py-1.5 border border-gray-300 rounded-lg text-sm" style="padding-left:1.75rem">
                        </div>
                    </div>
                    <div>
                        <label for="cantidad" class="block text-xs font-medium text-gray-700 mb-0.5">Cantidad de servicios <span class="text-red-500">*</span></label>
                        <input type="number" id="cantidad" name="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="999" required
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <p class="text-[11px] text-gray-400 mt-0.5">Cuántas veces se puede contratar este servicio. El costo de publicación es <span class="font-semibold text-primary">RD$ {{ number_format($montoRegistro, 2) }}</span> × cantidad.</p>
                        @error('cantidad')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="presentacion" class="block text-xs font-medium text-gray-700 mb-0.5">Descripción del talento <span class="text-red-500">*</span></label>
                        <textarea id="presentacion" name="presentacion" rows="2" maxlength="250" required placeholder="Describe tu talento o servicio..."
                                  oninput="contarCaracteres(this,'contadorTalento')"
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm resize-none">{{ old('presentacion') }}</textarea>
                        <div class="flex justify-between items-center mt-0.5">
                            <span id="msgTalento" class="text-xs text-red-500 hidden">Máximo 250 caracteres</span>
                            <span id="contadorTalento" class="text-xs text-gray-400 ml-auto">0/250</span>
                        </div>
                        @error('presentacion')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" onclick="validarPaso1Talento()" class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-hoverPrimary font-medium">
                        Siguiente <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- PASO 2: Multimedia --}}
            <div id="step-2" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 hidden">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                    Imágenes y video
                </h2>
                <div class="mb-6">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Imagen o video principal <span class="text-red-500">* La imagen o video principal es obligatoria</span>
                  </label>
                    @error('imagen_principal')<span class="text-red-500 text-xs mb-2 block">{{ $message }}</span>@enderror
                    
                    <div id="imgPrincipalContenedor" class="relative w-full rounded-xl overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 hover:border-primary/50 hover:bg-primary/5 transition-all" style="min-height:180px;">
                        {{-- Placeholder cuando no hay imagen --}}
                        <div id="imgPrincipalPlaceholder" class="flex flex-col items-center justify-center py-10 text-center pointer-events-none">
                            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Arrastra o haz clic para subir</p>
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP o MP4 (Máx. 10MB)</p>
                        </div>
                        {{-- Preview imagen --}}
                        <img id="imagen_principal_preview" class="hidden w-full rounded-xl object-cover" style="max-height:320px;" alt="Vista previa"/>
                        {{-- Preview video --}}
                        <video id="video_principal_preview" class="hidden w-full rounded-xl" style="max-height:320px;" controls></video>
                        {{-- Botón eliminar + nombre archivo --}}
                        <div id="imgPrincipalActions" class="hidden flex items-center justify-between px-3 py-2 bg-white/90 border-t border-gray-200">
                            <span id="imagen_principal_filename" class="text-xs text-gray-600 truncate max-w-[70%]"></span>
                            <button type="button" id="btnRemoveImgPrincipal" class="text-red-500 hover:text-red-700 text-xs font-semibold flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                        </div>
                        {{-- Label clickeable sobre todo --}}
                        <label for="imagen_principal" class="absolute inset-0 cursor-pointer" id="labelImgPrincipal"></label>
                        <input id="imagen_principal" name="imagen_principal" type="file" class="hidden imagen-principal-input" accept="image/jpeg,image/png,image/webp,video/mp4" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Imágenes adicionales <span class="text-gray-400">(opcional)</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="image-upload-container">
                        @for($i = 0; $i < 4; $i++)
                        <label class="block h-24 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 hover:border-primary/50 hover:bg-primary/5 overflow-hidden relative group">
                            <span class="file-name text-xs text-gray-700 absolute bottom-1 left-1 bg-white/80 px-1 rounded max-w-[90%] truncate hidden"></span>
                            <input type="file" name="imagenes[]" accept="image/jpeg,image/png,image/webp" class="hidden imagen-input" data-index="{{ $i }}">
                            <div class="flex flex-col items-center justify-center h-full pointer-events-none text-center preview-default p-2">
                                <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                <p class="text-xs text-gray-400">Imagen {{ $i + 1 }}</p>
                            </div>
                            <img class="preview-image hidden absolute inset-0 w-full h-full object-cover rounded-xl" alt="Vista previa"/>
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center hidden preview-actions">
                                <button type="button" class="text-white bg-red-500 rounded-full p-1.5 hover:bg-red-600" data-action="remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </label>
                        @endfor
                    </div>
                </div>
                <div class="flex justify-between mt-4">
                    <button type="button" onclick="goToStep(1)" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Anterior
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-secondary text-white rounded-lg hover:bg-hoverSecondary font-medium shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Publicar talento
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 1: Pago de registro --}}
<div id="modalPagoTalento" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Pago de registro</h3>
                    <p class="text-xs text-gray-400">Pago único para publicar tu talento</p>
                </div>
            </div>
            <button id="cerrarModalPago" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-4">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 flex items-center justify-between">
                <span class="text-blue-700 font-semibold text-sm">Monto de registro</span>
                <span id="montoTotal" class="text-xl font-bold text-blue-700">RD$ {{ number_format($montoRegistro, 2) }}</span>
            </div>
            <p class="text-xs text-gray-400 -mt-3 mb-4 text-right">RD$ {{ number_format($montoRegistro, 2) }} × <span id="montoCantidad">1</span> servicio(s)</p>
            @if(($direccionesCount ?? 0) === 0)
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-5">
                <p class="text-sm text-orange-700 font-medium mb-3">⚠️ Se requiere una dirección para proceder.</p>
                <a href="{{ route('direcciones.index', ['return_url' => url()->current()]) }}" 
                   class="inline-flex items-center gap-2 text-sm font-bold text-orange-800 hover:underline">
                    Crear mi primera dirección →
                </a>
            </div>
            @endif
            <div id="pagoError" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-red-700 text-sm"></div>
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Selecciona una tarjeta</p>
                <div id="listaTarjetasPago" class="space-y-2 max-h-48 overflow-y-auto">
                    @forelse($tarjetas as $tarjeta)
                    <div class="tarjeta-pago-wrapper flex items-center gap-1" data-id="{{ $tarjeta->id_tarjeta }}">
                        <label class="tarjeta-pago flex-1 flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all" data-id="{{ $tarjeta->id_tarjeta }}">
                            <input type="radio" name="tarjeta_pago_select" value="{{ $tarjeta->id_tarjeta }}" class="h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">**** **** **** {{ $tarjeta->last4 ?? substr($tarjeta->no_tarjeta ?? '', -4) }}</p>
                                <p class="text-xs text-gray-400">{{ $tarjeta->nombre_titular ?? 'Titular' }}</p>
                            </div>
                        </label>
                        <button type="button" class="btn-borrar-tarjeta p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" data-id="{{ $tarjeta->id_tarjeta }}" title="Eliminar tarjeta">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    @empty
                    <p id="sinTarjetasMsg" class="text-sm text-gray-400 text-center py-3">No tienes tarjetas guardadas</p>
                    @endforelse
                </div>
                <button type="button" id="btnAbrirNuevaTarjeta" class="w-full mt-3 flex items-center justify-center gap-2 border-2 border-dashed border-gray-300 hover:border-blue-400 hover:bg-blue-50 text-gray-500 hover:text-blue-600 py-2.5 rounded-xl text-sm font-medium transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar nueva tarjeta
                </button>
            </div>
            <div id="seccionCvvPago" class="mb-2 {{ $tarjetas->isEmpty() ? 'hidden' : '' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-1">CVV</label>
                <div class="flex items-center gap-3">
                    <input type="password" id="cvvPagoTalento" maxlength="4" placeholder="" class="w-28 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-center text-lg tracking-widest font-mono focus:outline-none focus:border-blue-500">
                    <span class="text-xs text-gray-400">3-4 dígitos al dorso</span>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
            <button type="button" id="cancelarPagoTalento" class="flex-1 border-2 border-gray-200 text-gray-600 hover:bg-gray-50 py-3 rounded-xl text-sm font-semibold">Cancelar</button>
            <button type="button" id="confirmarPagoTalento" 
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-bold shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                {{ ($direccionesCount ?? 0) === 0 ? 'disabled' : '' }}>
                Pagar y publicar
            </button>
        </div>
    </div>
</div>

{{-- MODAL 2: Registrar nueva tarjeta --}}
<div id="modalNuevaTarjeta" class="hidden fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <h3 class="font-bold text-gray-800">Nueva tarjeta</h3>
            </div>
            <button id="cerrarModalTarjeta" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div id="tarjetaError" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 text-red-700 text-sm"></div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre del titular</label>
                <input type="text" id="nt_nombre" placeholder="Ej: Juan Rodríguez" autocomplete="cc-name"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Número de tarjeta</label>
                <input type="text" id="nt_numero" placeholder="4111 1111 1111 1111" maxlength="19" autocomplete="cc-number"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-mono tracking-widest focus:outline-none focus:border-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mes</label>
                    <input type="text" id="nt_mes" placeholder="MM" maxlength="2" autocomplete="cc-exp-month"
                           class="w-full border-2 border-gray-200 rounded-xl px-3 py-3 text-sm text-center focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Año</label>
                    <input type="text" id="nt_anio" placeholder="YYYY" maxlength="4" autocomplete="cc-exp-year"
                           class="w-full border-2 border-gray-200 rounded-xl px-3 py-3 text-sm text-center focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Banco <span class="font-normal text-gray-400">(opcional)</span></label>
                <input type="text" id="nt_banco" placeholder="Ej: Banco Popular, BHD León..."
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" id="cancelarNuevaTarjeta" class="flex-1 border-2 border-gray-200 text-gray-600 hover:bg-gray-50 py-3 rounded-xl text-sm font-semibold">Cancelar</button>
                <button type="button" id="guardarNuevaTarjeta" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-bold shadow-md transition-all">Guardar tarjeta</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentStep = 1;
function goToStep(step) {
    document.getElementById('step-' + currentStep).classList.add('hidden');
    document.getElementById('step-' + step).classList.remove('hidden');
    for (let i = 1; i <= 2; i++) {
        const icon = document.getElementById('step-icon-' + i);
        icon.className = i <= step
            ? 'w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow'
            : 'w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold';
    }
    document.getElementById('step-line-1').className = step > 1 ? 'w-12 sm:w-20 h-0.5 bg-primary mx-2' : 'w-12 sm:w-20 h-0.5 bg-gray-300 mx-2';
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
@if($errors->any()) document.querySelectorAll('[id^="step-"]').forEach(el => el.classList.remove('hidden')); @endif

function formatPrice(input) {
    let v = input.value.replace(/[^0-9.]/g, '');
    input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function validarPaso1Talento() {
    const campos = [
        { id: 'item', label: 'Nombre del talento' },
        { id: 'valor', label: 'Precio' },
        { id: 'cantidad', label: 'Cantidad de servicios' },
        { id: 'presentacion', label: 'Descripción' },
    ];
    let valido = true;
    campos.forEach(c => {
        const el = document.getElementById(c.id);
        if (!el) return;
        const err = el.parentElement.querySelector('.campo-error') || el.closest('div').querySelector('.campo-error');
        if (!el.value.trim()) {
            valido = false;
            el.classList.add('border-red-400');
            // Mostrar mensaje si no existe
            let msg = el.parentElement.querySelector('.campo-error');
            if (!msg) {
                msg = document.createElement('span');
                msg.className = 'campo-error text-red-500 text-xs mt-1 block';
                el.parentElement.appendChild(msg);
            }
            msg.textContent = c.label + ' es obligatorio.';
        } else {
            el.classList.remove('border-red-400');
            const msg = el.parentElement.querySelector('.campo-error');
            if (msg) msg.remove();
        }
    });
    if (valido) goToStep(2);
}

function contarCaracteres(textarea, contadorId) {
    const max = 250;
    const len = textarea.value.length;
    const contador = document.getElementById(contadorId);
    const msg = document.getElementById(contadorId.replace('contador', 'msg'));
    if (contador) contador.textContent = len + '/' + max;
    if (len >= max) {
        if (contador) contador.classList.replace('text-gray-400', 'text-red-500');
        if (msg) msg.classList.remove('hidden');
    } else {
        if (contador) contador.classList.replace('text-red-500', 'text-gray-400');
        if (msg) msg.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // -- Imagen principal --
    const inp = document.getElementById('imagen_principal');
    const placeholder = document.getElementById('imgPrincipalPlaceholder');
    const prevImg = document.getElementById('imagen_principal_preview');
    const prevVid = document.getElementById('video_principal_preview');
    const fname = document.getElementById('imagen_principal_filename');
    const actions = document.getElementById('imgPrincipalActions');
    const labelClick = document.getElementById('labelImgPrincipal');

    function mostrarPreviewPrincipal(file) {
        placeholder.classList.add('hidden');
        labelClick.style.pointerEvents = 'none';
        // Limpiar error si existía
        const msgErr = document.getElementById('error-imagen-talento');
        if (msgErr) msgErr.classList.add('hidden');
        const cont = document.getElementById('imgPrincipalContenedor');
        if (cont) cont.classList.remove('border-red-400'); // no abrir file picker al hacer click en la imagen
        if (file.type.startsWith('image/')) {
            prevVid.classList.add('hidden');
            const r = new FileReader();
            r.onload = e => { prevImg.src = e.target.result; prevImg.classList.remove('hidden'); };
            r.readAsDataURL(file);
        } else {
            prevImg.classList.add('hidden');
            prevVid.src = URL.createObjectURL(file);
            prevVid.classList.remove('hidden');
        }
        fname.textContent = file.name;
        actions.classList.remove('hidden');
    }

    function limpiarPreviewPrincipal() {
        inp.value = '';
        prevImg.src = ''; prevImg.classList.add('hidden');
        prevVid.src = ''; prevVid.classList.remove('hidden'); prevVid.classList.add('hidden');
        placeholder.classList.remove('hidden');
        actions.classList.add('hidden');
        labelClick.style.pointerEvents = '';
    }

    inp.addEventListener('change', function(e) {
        const file = e.target.files[0]; if (!file) return;
        if (!['image/jpeg','image/png','image/webp','video/mp4'].includes(file.type)) { alert('Solo JPEG, PNG, WebP o MP4'); this.value=''; return; }
        if (file.size > 10*1024*1024) { alert('Máximo 10MB'); this.value=''; return; }
        mostrarPreviewPrincipal(file);
    });

    document.getElementById('btnRemoveImgPrincipal').addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        limpiarPreviewPrincipal();
    });

    // -- Imágenes adicionales --
    document.querySelectorAll('.imagen-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0]; const label = input.closest('label'); if (!label || !file) return;
            const pi = label.querySelector('.preview-image'), pd = label.querySelector('.preview-default'), fn = label.querySelector('.file-name'), pa = label.querySelector('.preview-actions');
            if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { alert('Solo JPG, PNG o WebP'); this.value=''; return; }
            if (file.size > 2*1024*1024) { alert('Máximo 2MB'); this.value=''; return; }
            const r = new FileReader();
            r.onload = e => { pd.classList.add('hidden'); pi.src=e.target.result; pi.classList.remove('hidden'); fn.textContent=file.name; fn.classList.remove('hidden'); pa.classList.remove('hidden'); };
            r.readAsDataURL(file);
        });
    });
    document.querySelectorAll('#image-upload-container [data-action="remove"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            const label = this.closest('label'); if (!label) return;
            const i2 = label.querySelector('.imagen-input'), pi = label.querySelector('.preview-image'), pd = label.querySelector('.preview-default'), fn = label.querySelector('.file-name'), pa = label.querySelector('.preview-actions');
            if (i2) i2.value=''; if (pi) { pi.src=''; pi.classList.add('hidden'); } if (pd) pd.classList.remove('hidden'); if (fn) fn.classList.add('hidden'); if (pa) pa.classList.add('hidden');
        });
    });

    // -- Submit: abrir modal de pago --
    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar imagen principal obligatoria
        const inp = document.getElementById('imagen_principal');
        const contenedor = document.getElementById('imgPrincipalContenedor');
        const msgImg = document.getElementById('error-imagen-talento');

        if (!inp || !inp.files || inp.files.length === 0) {
            if (msgImg) msgImg.classList.remove('hidden');
            if (contenedor) contenedor.classList.add('border-red-400');
            return;
        }
        if (msgImg) msgImg.classList.add('hidden');
        if (contenedor) contenedor.classList.remove('border-red-400');

        document.getElementById('valor').value = document.getElementById('valor').value.replace(/,/g, '');

        // Actualizar monto en el modal según cantidad
        const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
        const precioBase = {{ $montoRegistro }};
        const total = precioBase * cantidad;
        document.getElementById('montoTotal').textContent = 'RD$ ' + total.toLocaleString('es-DO', {minimumFractionDigits: 2});
        document.getElementById('montoCantidad').textContent = cantidad;

        document.getElementById('modalPagoTalento').classList.remove('hidden');
    });

    // --------------------------------------
    // MODAL DE PAGO
    // --------------------------------------
    const modalPago = document.getElementById('modalPagoTalento');
    const errorDiv = document.getElementById('pagoError');
    const confirmar = document.getElementById('confirmarPagoTalento');
    const csrfToken = document.querySelector('input[name="_token"]').value;

    function cerrarModalPago() { modalPago.classList.add('hidden'); ocultarError(); }
    document.getElementById('cerrarModalPago').addEventListener('click', cerrarModalPago);
    document.getElementById('cancelarPagoTalento').addEventListener('click', cerrarModalPago);
    modalPago.addEventListener('click', e => { if (e.target === modalPago) cerrarModalPago(); });

    function mostrarError(msg) { errorDiv.textContent = msg; errorDiv.classList.remove('hidden'); }
    function ocultarError() { errorDiv.classList.add('hidden'); }

    // Seleccionar tarjeta
    function bindTarjetaClick(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.tarjeta-pago').forEach(t => {
                t.classList.remove('border-blue-500', 'bg-blue-50');
                t.classList.add('border-gray-200');
            });
            this.classList.add('border-blue-500', 'bg-blue-50');
            this.classList.remove('border-gray-200');
            this.querySelector('input[type=radio]').checked = true;
            document.getElementById('seccionCvvPago').classList.remove('hidden');
        });
    }
    document.querySelectorAll('.tarjeta-pago').forEach(bindTarjetaClick);
    const primera = document.querySelector('.tarjeta-pago');
    if (primera) primera.click();

    // Eliminar tarjeta
    function bindBorrarTarjeta(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = this.dataset.id;
            const wrapper = this.closest('.tarjeta-pago-wrapper');
            
            if (!confirm('¿Estás seguro de que deseas eliminar esta tarjeta?')) return;
            
            fetch('/carrito/tarjetas/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(function(resp) {
                if (resp.ok) {
                    wrapper.remove();
                    if (!document.querySelector('.tarjeta-pago')) {
                        document.getElementById('listaTarjetasPago').innerHTML = '<p id="sinTarjetasMsg" class="text-sm text-gray-400 text-center py-3">No tienes tarjetas guardadas</p>';
                        document.getElementById('seccionCvvPago').classList.add('hidden');
                    }
                } else {
                    alert('No se pudo eliminar la tarjeta.');
                }
            }).catch(function() {
                alert('Error de red al eliminar.');
            });
        });
    }
    document.querySelectorAll('.btn-borrar-tarjeta').forEach(bindBorrarTarjeta);

    // Confirmar pago
    confirmar.addEventListener('click', async function() {
        const selected = document.querySelector('input[name="tarjeta_pago_select"]:checked');
        if (!selected) { mostrarError('Selecciona una tarjeta.'); return; }
        const cvv = document.getElementById('cvvPagoTalento').value.trim();
        if (!cvv || cvv.length < 3) { mostrarError('Ingresa el CVV de tu tarjeta.'); return; }

        confirmar.disabled = true;
        const textoOriginal = confirmar.textContent;
        confirmar.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        ocultarError();

        try {
            const form = document.getElementById('productForm');
            const fd = new FormData(form);
            fd.append('id_tarjeta', selected.value);
            fd.append('cvv', cvv);

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 90000); // 90s timeout

            const resp = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            const ct = resp.headers.get('content-type') || '';
            let data;
            if (ct.includes('application/json')) {
                data = await resp.json();
            } else {
                const html = await resp.text();
                console.error('Respuesta no-JSON (status ' + resp.status + '):', html.substring(0, 500));
                mostrarError('Error del servidor (código ' + resp.status + '). Revisa la consola del navegador.');
                confirmar.disabled = false;
                confirmar.textContent = textoOriginal;
                return;
            }

            if (data.success) {
                confirmar.innerHTML = '✓ Publicado';
                confirmar.classList.remove('bg-blue-600');
                confirmar.classList.add('bg-green-600');
                setTimeout(() => { window.location.href = data.redirect || '{{ route("items.admintalento") }}'; }, 1000);
            } else {
                mostrarError(data.message || 'Error al procesar el pago.');
                confirmar.disabled = false;
                confirmar.textContent = textoOriginal;
            }
        } catch (err) {
            console.error('Error fetch:', err);
            if (err.name === 'AbortError') {
                mostrarError('El pago tardó demasiado. Verifica tu historial antes de intentar de nuevo.');
            } else {
                mostrarError('Error de conexión: ' + err.message);
            }
            confirmar.disabled = false;
            confirmar.textContent = textoOriginal;
        }
    });

    // --------------------------------------
    // MODAL DE NUEVA TARJETA
    // --------------------------------------
    const modalTarjeta = document.getElementById('modalNuevaTarjeta');
    const tarjetaError = document.getElementById('tarjetaError');

    document.getElementById('btnAbrirNuevaTarjeta').addEventListener('click', () => {
        modalTarjeta.classList.remove('hidden');
    });
    function cerrarModalTarjeta() { modalTarjeta.classList.add('hidden'); tarjetaError.classList.add('hidden'); }
    document.getElementById('cerrarModalTarjeta').addEventListener('click', cerrarModalTarjeta);
    document.getElementById('cancelarNuevaTarjeta').addEventListener('click', cerrarModalTarjeta);
    modalTarjeta.addEventListener('click', e => { if (e.target === modalTarjeta) cerrarModalTarjeta(); });

    // Formato Número tarjeta
    document.getElementById('nt_numero').addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').substring(0, 16);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });

    // Guardar tarjeta
    document.getElementById('guardarNuevaTarjeta').addEventListener('click', async function() {
        const nombre = document.getElementById('nt_nombre').value.trim();
        const numero = document.getElementById('nt_numero').value.replace(/\s/g, '');
        const mes = document.getElementById('nt_mes').value.trim();
        const anio = document.getElementById('nt_anio').value.trim();
        const banco = document.getElementById('nt_banco').value.trim();

        if (!nombre || !numero || numero.length < 13 || !mes) {
            tarjetaError.textContent = 'Completa nombre, Número de tarjeta y mes.';
            tarjetaError.classList.remove('hidden');
            return;
        }

        this.disabled = true;
        this.textContent = 'Guardando...';

        try {
            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('nombre_titular', nombre);
            fd.append('no_tarjeta', numero);
            fd.append('mes_expiracion', mes);
            if (anio) fd.append('anio_expiracion', anio);
            if (banco) fd.append('banco_tarjeta', banco);

            const resp = await fetch('{{ route("carrito.tarjetas_store") }}', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            const ct = resp.headers.get('content-type') || '';
            let data;
            if (ct.includes('application/json')) {
                data = await resp.json();
            } else {
                const html = await resp.text();
                console.error('Respuesta no-JSON al guardar tarjeta:', resp.status, html.substring(0, 300));
                tarjetaError.textContent = 'Error del servidor (código ' + resp.status + '). Revisa la consola.';
                tarjetaError.classList.remove('hidden');
                this.disabled = false;
                this.textContent = 'Guardar tarjeta';
                return;
            }

            // Si es error de validación (422), mostrar el primer error
            if (!resp.ok && data.errors) {
                const firstError = Object.values(data.errors).flat()[0];
                tarjetaError.textContent = firstError || data.message || 'Datos inválidos.';
                tarjetaError.classList.remove('hidden');
                this.disabled = false;
                this.textContent = 'Guardar tarjeta';
                return;
            }

            if (data.success) {
                const t = data.data;
                const last4 = t.last4 || numero.slice(-4);

                // Quitar mensaje "no tienes tarjetas"
                document.getElementById('sinTarjetasMsg')?.remove();

                // Agregar tarjeta a la lista con botón de eliminar
                const html = `<div class="tarjeta-pago-wrapper flex items-center gap-1" data-id="${t.id_tarjeta}">
                    <label class="tarjeta-pago flex-1 flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all" data-id="${t.id_tarjeta}">
                        <input type="radio" name="tarjeta_pago_select" value="${t.id_tarjeta}" class="h-4 w-4 text-blue-600">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">**** **** **** ${last4}</p>
                            <p class="text-xs text-gray-400">${nombre}</p>
                        </div>
                    </label>
                    <button type="button" class="btn-borrar-tarjeta p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" data-id="${t.id_tarjeta}" title="Eliminar tarjeta">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>`;
                document.getElementById('listaTarjetasPago').insertAdjacentHTML('beforeend', html);

                const nuevoWrapper = document.getElementById('listaTarjetasPago').lastElementChild;
                const nuevoLabel = nuevoWrapper.querySelector('.tarjeta-pago');
                bindTarjetaClick(nuevoLabel);
                bindBorrarTarjeta(nuevoWrapper.querySelector('.btn-borrar-tarjeta'));
                nuevoLabel.click();

                // Limpiar y cerrar
                ['nt_nombre','nt_numero','nt_mes','nt_anio','nt_banco'].forEach(id => document.getElementById(id).value = '');
                cerrarModalTarjeta();
                return; // ← evitar que el finally re-habilite el botón con texto incorrecto
            } else {
                tarjetaError.textContent = data.message || 'Error al guardar la tarjeta.';
                tarjetaError.classList.remove('hidden');
            }
        } catch {
            tarjetaError.textContent = 'Error de red. Intenta de nuevo.';
            tarjetaError.classList.remove('hidden');
        }
        this.disabled = false;
        this.textContent = 'Guardar tarjeta';
    });
});
</script>
@endpush
